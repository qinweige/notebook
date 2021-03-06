标题: 部署docker swarm

这部分,我们启动我们的accountservice,运行在本地的docker swarm集群中.同时讨论几个容器部署的重要概念
这篇博客主要讲一下几点:
* docker swarm和容器部署
* 用docker作为容器运行accountservice
* 建立一个本地的docker swarm集群
* 将accountservice作为swarm服务部署
* 基测和结果
其实写完这一章,我发现这一章和go没有关系.但希望你喜欢.

###什么是容器部署
----
在实践开始之前,一个容器部署的简单介绍:
当一个应用越来越复杂,并且开始有更高的负载,将会有成百个服务在很多硬件上运行.容器部署让我们在一个节点上管理我们的硬件
一篇文章上总结的:
> 抽象主机的基础结构,部署工具允许用户在一个部署目标上控制整个集群.

这总结的很好.用kubernetes或者docker swarm这种容器部署工具来部署我们的各个服务在不同的节点上.对于docker来说,swarm模式管理docker engine的集群.kubernetes用一种稍微不同的抽象方法,但是整体概念上是一致的.
容器部署不仅控制我们服务的生命周期,也提供其他服务,例如:服务发现,负载均衡,内部地址和日志.

###docker swarm核心概念
----
在docker swarm中,有三个核心概念:
* 节点: 一个节点就是一个docker engine的实例.理论上讲,他是一个拥有cpu资源,内存和网络接口的主机.一个节点可以是一个manager节点或者worker节点.
* 服务: 服务就是worker节点上运行的指令.一个服务可以是复制的或者全局的.一个服务可以抽象的看成是任意数量的容器组成的逻辑上的服务.这个服务可以用它的名字来调用,而不需要知道它内部的网络结构.
* 任务: 一个任务可以是docker容器,docker文件定义任务为:拥有并运行docker容器和指令.manager节点分发任务给worker节点的服务.
下图展示一个简单的微服务框架.两个服务accountservice和quotes-service抽象为两个节点,运行在五个容器的实例中.

[!img](img/part5-swarm-overview.png)

###代码
---
这部分没有改go方面的代码.我们加入新的文件来运行docker.你可以得到这一章完整的代码
```
git checkout P5
```
###容器化我们的accountservice
----
####docker安装
----
你需要安装docker.我用的是docker toolbox 和vitualbox.但是你可以直接用docker.
####创建dockerfile
----
一个dockerfile可以看成是你想创建什么样的docker镜像的配方.让我们在accountservice文件夹中创建文件Dockerfile:
```
FROM iron/base

EXPOSE 6767
ADD accountservice-linux-amd64 /
ENTRYPOINT ["./accountservice-linux-amd64"]
```
解释：
* FROM-定义我们的基础镜像.我们会在这个镜像上开始.iron/base是一个可以运行go程序的小巧的镜像
* EXPOSE-定义一个端口,作为外部请求的端口
* ADD-增加一个文件accountservice-linux-amd64到root(/)目录下
* ENTRYPOINT-定义启动哪一个程序,当docker开启这个镜像容器

###不同操作系统下的编译
----
我们的文件名字包含linux-amd64.我们可以叫他任何名字,但是我喜欢把操作系统和cpu型号放进执行文件名字中.我用的是mac OSX 系统.所以我如果直接编译go的执行文件,用go build的话,这产生一个执行文件在同一个文件夹中.然而这个执行文件不能再docker上运行,因为docker容器的环境是linux.因此,我们需要设定一些环境参数,这样我们的编译器才知道我们要给其他的系统或者cpu环境编译文件.
在goblog/accountservice文件夹下面运行:
```
export GOOS=linux
go build -o accountservice-linux-amd64
export GOOS=darwin
```
-o表示产生二进制执行文件.我经常用脚本文件自动做这些东西(后面会有)
因为OS X和linux容器都在AMD64 cpu架构上,我们不需要设置GOARCH参数.但是你如果用32位系统,或者ARM处理器,你要设置GOARCH参数

###创建docker镜像
----
现在我们创建第一个docker镜像,包含我们的执行文件.去accountservice的上层文件夹,就是$GOPATH/src/github.com/callistaenterprise/goblog.
对于docker镜像,我们经常用一个前缀来标注名字.我经常用我的github名字作为前缀,例如eriklupander/myservicename.这里,我用someprefix作为前缀.执行下面的命令来创建Docker镜像:
```
> docker build -t someprefix/accountservice accountservice/

Sending build context to Docker daemon 13.17 MB
Step 1/4 : FROM iron/base
 ---> b65946736b2c
Step 2/4 : EXPOSE 6767
 ---> Using cache
 ---> f1147fd9abcf
Step 3/4 : ADD accountservice-linux-amd64 /
 ---> 0841289965c6
Removing intermediate container db3176c5e1e1
Step 4/4 : ENTRYPOINT ./accountservice-linux-amd64
 ---> Running in f99a911fd551
 ---> e5700191acf2
Removing intermediate container f99a911fd551
Successfully built e5700191acf2
```

好了,我们有啦一个someprefix/accountservice镜像.如果我们要在多节点下运行或者分享镜像,我们可以用docker push来使我们的镜像被别的主机pull.
我们现在运行镜像:

```
> docker run --rm someprefix/accountservice
Starting accountservice
Seeded 100 fake accounts...
2017/02/05 11:52:01 Starting HTTP service at 6767
```

然而,我们的容器不是在你的主机系统下运行,他运行在自己的网络中,我们不能直接从我们的主机来请求他.有办法来解决这个问题,但现在我们放一放,我们继续组建我们的docker swarm和部署accountservice.

###创建单节点Docker swarm集群
----
一个docker swarm集群包括至少一个swarm manager和零到多个swarm worker.我的例子会包含一个swarm manager.这节过后,你会有一个swarm manager运行.
你可以参考别的文章来看如何运行swarm.下面这条命令初始化docker主机为swarm-manager-1作为一个swarm节点,同时让swarm-manager-1节点地址和主机一样
```
> docker $(docker-machine config swarm-manager-1) swarm init --advertise-addr $(docker-machine ip swarm-manager-1)
```
如果我们要创建多节点的swarm集群,我们要把这条命令产生的join-token记录下来,这样我们可以加入其他的节点到这个swarm中.

###创建网络
---
一个docker网络的用处是,当我们想请求同一个swarm集群上的其他的容器,并不需要知道真实的集群分布.
```
docker network create --driver overlay my_network
```
my_network是我们的网络名称

###部署accountservice
----
现在我们要部署我们的accountservice进Docker swarm服务中.这个docker服务命令有很多参数设置,但不要怕.这里我们来部署accountservice
```
docker service create --name=accountservice --replicas=1 --network=my_network -p=6767:6767 someprefix/accountservice
ntg3zsgb3f7ah4l90sfh43kud
```
快速看一下这些参数
* -name:给服务的名字.这也是集群中其他服务请求我们的名字.所以另一个服务来请求accountservice的话,这个服务只需要Get请求http://accountservice:6767/accounts/10000
* -replicas: 我们服务的实例数量.如果我们有多节点的docker swarm集群,swarm engine会自动分发实例到不同的节点上.
* -network: 这里我们告诉我们的服务用刚刚我们创建的网络my_network
* -p: 映射[内部端口]:[外部端口].这里我们用6767:6767.如果我们用6767:80,从外部请求要用80端口.注意这部分使我们的服务可以被外界请求.大多数情况,你不应该让你的服务暴露给外界.你应该用一个EDGE-server(例如反向代理),包括路由机制和安全检查,所以外界不能随意请求你的服务
* someprefix/accountservice: 指明我们想让容器运行哪一个镜像.
让我们看看我们的服务是否运行了
```
> docker service ls
```
太好了,我们应该可以curl或者用浏览器请求我们的api.唯一要知道的就是我们swarm的ip地址.即使我们只运行一个服务实例,我们的网络和swarm也会需要我们的服务外部端口,这意味着两个服务不能用同一个外部端口.他们可以有同样的内部端口,但对于外部来说,swarm是一个整体.
```
> echo $ManagerIP
192.168.99.100
```
如果你换了terminal,你可以重新导出:
```
> export ManagerIP=`docker-machine ip swarm-manager-0`
```
curl请求:
```
> curl $ManagerIP:6767/accounts/10000
{"id":"10000","name":"Person_0"}
```

###部署可视化
----
用docker的命令来查看swarm的状态不容易看,一个图形化的方法比较好.例如manomarks docker swarm visualizer可以被部署为一个Docker swarm的服务.这可以给我们提供我们集群分布的图形,同事确保我们在集群中外部暴露的服务可不可以请求到.
初始化这个可视器在容器镜像中
```
docker service create \
 --name=viz \
 --publish=8080:8000/tcp \
 --constraint=node.role==manager \
 --mount=type=bind,src=/var/run/docker.sock,dst=/var/run/docker.sock \
 manomarks/visualizer
```
这将在8000端口产生一个服务.让我们用浏览器浏览http://$ManagerIP:8000

[!img](img/part5-viz.png)

###额外内容
----
我也做了一个swarm的可视化界面叫做dvizz,展示docker 远程api和D3.js force图.你可以安装她
```
docker service create \
 --constraint=node.role==manager \
 --replicas 1 --name dvizz -p 6969:6969 \
 --mount=type=bind,src=/var/run/docker.sock,dst=/var/run/docker.sock \
 --network my_network \
 eriklupander/dvizz
```
浏览 http://$ManagerIP:6969

[!img](img/part5-dvizz.png)

###加入quote-service
只有一个服务的微服务不能看出微服务的全貌.让我们部署一个基于spring boot的quotes-service.我把这个容器镜像放在docker hub中的eriklupander/quotes-service.
```
> docker service create --name=quotes-service --replicas=1 --network=my_network eriklupander/quotes-service
```
如果你输入docker ps来看那些docker容器在运行:
```
> docker ps
CONTAINER ID    IMAGE                       COMMAND                 CREATED         STATUS                           PORTS                                           NAMES
98867f3514a1    eriklupander/quotes-service "java -Djava.security"  12 seconds ago  Up 10 seconds
```
注意,我们没有暴露一个外界端口给这个服务,所以我们只能在集群内部的端口8080内请求.我们会集成这个服务在第七部分同时看一下服务探索和负载均衡.
如果你加入了dvizz,你应该能看到quotes-service和accountservice

###copyall.sh脚本
我们来做一个脚本帮助我们编译和部署.在root/goblog文件夹中,创建一个脚本文件叫做copyall.sh

```
#!/bin/bash
export GOOS=linux
export CGO_ENABLE=0

cd accountservice; go get; go build -o accountservice-linux-amd64;echo build `pwd`;cd ..

export GOOS=darwin
docker build -t someprefix/accountservice accountservice/

docker service rm accountservice
docker service create --name=accountservice --replicas=1 --network=my_network -p=6767:6767
someprefix/accountservice
```
这段脚本编译执行文件,重新编译docker镜像,部署到docker swarm服务上.
我喜欢脚本的简化,虽然有时我用gradle plugin.

###性能
----
现在开始,所有的基测都在docker swarm上进行.这意味之前的结果不能用来和之后的比较
cpu使用率和内存使用会用 docker stats来收集.我们也会用gatling测试.
如果你喜欢压力测试,第二节的仍然可以用,但需要改变-baseUrl参数
```
> mvn gatling:execute -dusers=1000 -Dduration=30 -DbaseUrl=http://$ManagerIP:6767
```
####内存使用率
```
> docker stats $(docker ps | awk '{if(NR>1) print $NF}')

CONTAINER                                    CPU %               MEM USAGE / LIMIT    
accountservice.1.k8vyt3dulvng9l6y4mj14ncw9   0.00%               5.621 MiB / 1.955 GiB
quotes-service.1.h07fde0ejxru4pqwwgms9qt00   0.06%               293.9 MiB / 1.955 GiB
```
启动后,包含linux和我们accountservice的容器用5.6mb的内存,java开发的quotes-service用了300mb.虽然这可以通过调整jvm来降低.

###cpu和内存使用压力测试
----
```
CONTAINER                                    CPU %               MEM USAGE / LIMIT   
accountservice.1.k8vyt3dulvng9l6y4mj14ncw9   25.50%              35.15 MiB / 1.955 GiBB
```
----
在1K req/s下,虚拟机中运行的swarm和在第二三节中的OS x系统相比,内存稍微升高,cpu大略相同.

####性能
----
[!img](img/part5-performance.png)
----
延迟上升到4ms.这和直接运行有所升高,原因有几点.我认为gatling测试通过桥接的网络和swarm上的路由会有一些延迟.但是4ms的延迟也不错.毕竟我们从boltDB读数据,序列化到json并输出到HTTP.

###总结
----
我们学习如何启动docker swarm和部署accountservice到swarm上.下一节,我们会给我们的微服务加入healthcheck.


















