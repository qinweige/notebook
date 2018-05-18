第七节: 服务发现和负载均衡

这篇文章将关注两个微服务架构的重要部分:服务发现和负载均衡.和他们是如何帮助我们2017年经常要求的横向扩展容量的

###简介
----
负载均衡和出名.服务发现需要一些解释,从一个问题开始:
"服务A如何请求服务B,如果不知道怎么找到B"
换句话说,如果你有10个服务B在随机的集群节点上运行,有人要记录这些实例,所以当A需要和B联系时,至少一个IP地址或者主机名可以用(用户负载均衡),或者说,服务A必须能从第三方得到服务B的逻辑名字(服务器负载均衡).在微服务架构下,这两种方法都需要服务发现这一功能.简单来说,服务发现就是一个各种服务的注册器
如果这听起来像dns,确实是.不同是,这个服务发现用在你集群的内部,帮助服务找到彼此.然而,dns通常更静态,是帮助外部来请求你的服务.同时,dns服务器和dns协议不适合控制微服务多变的环境,容器和节点经常增加和减少.
大部分为服务框架提供一个或多个选择给服务发现.默认下,spring cloud/netflix OSS用netflix eureka(同时支持consul, etcd, zooKeeper),每个服务会在eureka实例中注册,之后发送heartbeats来让eureka知道他们还在工作.另一个有名的是consul,他提供很多功能还包括集成的DNS.其他有名的选择使用键值对存储注册服务,例如etcd.
这里,我们主要看一下Swarm中的机制.同时,我们看一下用unit test(gock)模拟http请求,因为我们要做服务到服务的沟通.

###两种负载均衡
----
为服务实现中,我们把负载均衡分为两种:
* 客户端:客户端自己请求一个发现服务来得到地址(iP, 主机名,端口).从这里面,他们可以随机或者round-robin方法来选择一个地址.为了不用每次都从发现服务里提取,每个客户端会保存一些缓存,同时随着发现服务更新.客户端负载均衡在spring cloud生态里的例子是netflix ribbon.在go-kit中相似的是etcd.客户端负载均衡的优势是去除中心化,没有中心的瓶颈,因为每个服务保存他们自己的注册器.缺点是内部服务复杂化和本地注册器包含不良路径的风险.
[!img](img/)

* 服务器段:这个模型中,客户端依赖负载均衡器来找到想请求服务的名字.这个模型通常成为代理模式,因为它的作用可以使负载均衡也可以是反向代理.这边的有点是简单,负载均衡和服务发现机制通常包含在容器部署里,你不需要安装和管理这些部分.同样,我们的服务不需要知道服务注册器,负载均衡器会帮助我们.所有的请求都通过复杂均衡器将会使他成为瓶颈.
[!img](img/)

当我们用docker swarm的服务,服务器端真正的服务(producer service)注册是完全透明给开发者的.也就是说,我们的服务不知道他们在服务器端负载均衡下运行,docker swarm完成整个注册/heartbeat/解除注册.
###使用服务发现信息
----
假设你想创建一个定制的监控应用,需要请求所有部署的服务的/health路径,你的监控应用怎样知道这些IP和端口.你需要得到服务请求的细节.对于swarm保存这些信息,你怎样得到他们.对于客户端的方法,例如eureka,你可以直接用api,然而,对于依赖于部署的服务发现,这不容易,我可以说有一个方法来做,同时有好多方法针对于不同的情形.

####docker远程api
我推荐用docker远程api,用docker api在你的服务中来向swarm manager请求其他服务的信息.毕竟,如果你用你的容器部署的内置服务发现机制,这也是你应该请求的地方.如果有问题,别人也能写一个适配器给你的部署.然而,用部署api也有限制:你紧紧以来容器的api,你也要确定你的应用可以和docker manager交流.

####其他方案
* 用其他的服务发现机制-netflix eureka, consul等.用这些服务的api来注册/查询/heartbeat等.我不喜欢这种方式,因为这让我们的服务更复杂,而且swarm也可以做这些事.我认为这是反设计模式的,所以一般情况不要做.
* 具体应用的token发现:这种方法下,每个服务发送他们自己的token,带有IP,服务名等.使用者可以订阅这些服务,同时更新他们的注册器.我们看netflix turbine without eureka,我们会用这种机制.这种方法因为不用注册所有服务而稍有不同,毕竟,这种情况下我们只关心一部分服务.

###代码
----
```
git checkout P7
```
###扩展和负载均衡
----
我们看一下能否启动多个accountservice实例实现扩展同时看我们swarm自动做到负载均衡请求.
为了知道哪个实例回复我们的请求,我们加入一个新的Account结构,我们可以输出ip地址.打开account.go
```
type Account struct {
	Id string `json:"id"`
	Name string `json:"name"`
	//new
	ServedBy string `json:"servedBy"
}
```
打开handlers.go,加入GetIp()函数,让他输出ServedBy的值:
```
code
```
getIp()函数应该用一些utils包,因为这些可以重复用,当我们需要判断一个运行服务的non-loopback ip地址.
重新编译和部署我们的服务
```
> ./copyall.sh
```
等到结束,输入
```
code
```
用curl
```
code
```
现在我们看到回复中有容器的ip地址,然我们扩展这些服务
```
> Docker service scale accountservice=3
accountservice scaled to 3
```
等一会运行
```
code
```
现在有三个实例,我们curl几次,看一看得到的ip地址
```
code
```
我们看到四次请求用round-robin的方法分给每一个实例.这种swarm提供的服务很好,因为它很方便,我们也不需要像客户端发现服务那样从一堆ip地址中选择一个.而且,swarm不会把请求发送给那些拥有healthcheck方法,却没有报告他们健康的节点.当你扩容和缩减很频繁时,同时你的服务很复杂,需要比accountservice启动多很多的时间的时候,这将会很重要.

###性能
----
看一看扩容后的延迟和cpu/内存使用吧.会不会增加?
```
> docker service scale accountservice=4
```

####cpu和内存使用率
gatling测试(1k req/s)
[!img](img/)

我们的四个实例平分这些工作,这三个新的实例用低于10mb的内存,在低于250 req/s情况下.
####性能
一个实例的gatling测试
[!img](img/)

四个实例的gatling测试
[!img](img/)

区别不大,本该这样.因为我们的四个实例也是在同一个虚拟机硬件上运行的.如果我们给swarm分配一些主机还没用的资源,我们会看到延迟下降的.我们看到一点小小的提升,在95和99平均延迟上.我们可以说,swarm负载均衡没有对性能有负面影响.

###加入quotes
----
记得我们的基于java的quotes-service么?让我们扩容他并且从accountservice请求他,用服务名quotes-service.目的是看一看我们只知道名字的时候,服务发现和负载均衡好不好用.
我们先修改一下account.go
```
code
```
我们用json标签来转换名称,从quote到text,ipAddress到ServedBy.
更改handler.go.我们加一个简单的getQuote函数来请求http://quotes-service:8080/api/quote,返回值用来输出新的Quote结构.我们在GetAccount函数中请求他.
首先,我们处理连接,keep-alive将会有负载均衡的问题,除非我们更改go的http客户端.在handler.go中,加入:
```
code
```
init方法确保发送的http请求有合适的头信息,能使swarm的负载均衡正常工作.在GetAccount函数下,加入getQuote函数
```
code
```
没什么特别的,?strength=4是让quotes-service api用多少cpu.如果请求错误,返回一个错误.
我们从GetAccount函数中请求getQuote函数,把Account实例返回的值附给Quote.
```
code
```
###unit testing发送的http请求
----
如果我们跑handlers_test.go的unit test,我们会失败.GetAccount函数会试着请求一个quote,但是这个URL上没有quotes的服务.
我们有两个办法来解决这个问题
1) 提取getQuote函数为一个interface,提供一个真的和一个假的方法.
2) 用http特定的mcking框架处理发送的请求同时返回一个写好的答案.内置的httptest包可以帮我们开启一个内置的http服务器用于unit test.但是我喜欢用第三方gock框架.
在handlers_test.go中,在TestGetAccount(t *testing)加入init函数.这会使我们的http客户端实例被gock获取
```
func inti() {
	gock.InterceptClient(client)
}
```
gock DSL提供很好地控制给期待的外部http请求和回复.在下面的例子中,我们用New(), Get()和MatchParam()来让gock期待http://quotes-service:8080/api/quote?strength=4 Get 请求,回复http 200和json字符串.
```
code
```
defer gock.Off()确保我们的test会停止http获取,因为gock.New()会开启http获取,这可能会是后来的测试失败.
然我们断言返回的quote
```
code
```

###跑测试
是指跑一下accountservice下所有的测试
重新部署用./copyall.sh,试着curl
```
code
```
扩容quotes-service
```
> docker service scale quotes-service=2
```
对于spring boot的quotes-service来说,需要15-30s,不像go那样快.我们curl几次
```
code
```
我们看到我们的servedBy循环用accountservice实例.我们也看到quote的ip地址有两个.如果我们没有关闭keep-alive,我们可能只会看到一个quote-service实例

###总结
这篇我们接触了服务发现和负载均衡和怎样用服务名称来请求其他服务
下一篇,我们会继续微服务的知识点,中心化配置.

























