###概览

----

下面这张图就是我们将要搭建的项目的概览图。我们将开始写第一个微服务之后我们会一点点完成这张图的所有内容。

![img](img/part1-overview.png)


讲解：
白色虚线的方框内：　docker swarm集群，运行在一个或多个节点上。
蓝色方框内：　Spring cloud/Netflix OSS提供的支持系统，或者其他服务，比如zipkin
黄色方框/白色方框：　一个微服务。

###运行资源消耗
----
为什么我们要用go来写微服务？除啦有意思和有效率，另一个主要原因是go在运行时消耗的内存非常小。下面这张图对比了Spring boot,Spring cloud和go.(都是基于运行在docker swarm上的微服务)

[!img](img/part-1-stats.png)

quotes-service是基于spring boot.compservice和accountservice是基于go.两个都是基于http服务器并且有许多库来和spring cloud集成。

但是内存消耗现在还要考虑么？我们现在不是有ＧＢ级别的RAM能轻松负载一个java应用么？
可以这么说，但是对于一个大企业，他们不止运行几十个微服务，甚者成百上千的微服务。这样的话减少资源消耗就能省下很多钱。

来看一下亚马逊主机的价格：

[!img](part1-amazon-ec2.png)

第二列为cpu数量，第四列为RAM大小。
我们看到当RAM增加一倍，价钱也随着增加一倍。如果你的cpu足够，就不必为缺少内存而要多花一倍的钱，后面我们也会看到go的服务在处理请求时甚至比spring boot 在闲置的时候少。

###微服务非功能性要求
----

这篇博客不仅仅关于go搭建微服务，更是如何在spring cloud环境下运行和搭建符合真正用于生产环境的微服务产品。包括：
* 中心化配置
* 服务发现
* 日志
* 分布式探寻
* 熔断
* 负载均衡
* 边缘
* 监测
* 安全

这些内容都应该在为服务里实现，不仅仅是用go,其他的语言，例如，java,python,写微服务产品时也应该实现这些功能．在这篇博客中我会尽量从go语言角度覆盖这些内容。

另一方面要考虑的是关于微服务的实现，你可能听过：
* HTTP/RPC/REST/SOAP/任何形式的APIS
* 持久化数据的API (DB clients, JDBC, O/R mappers)
* 消息处理API (MQTT, AMQP, JMS)
* 测试　（单元测试，integration, system, acceptance test)
* 编译工具/CI/CD

我会讲解这其中的一些。

###在docker swarm上运行

概览中我们看到我们的服务会运行在docker swarm中，这意味着我们所有的服务，包括支持服务（服务器配置，边缘等）和微服务程序都会运行在docker swarm中。在这个项目结束时，我们运行：

```
docker service ls
```
我们会看到下面这些服务

[!img](img/part1-swarm-services.png)

注意：上面这些服务远多于我们在第五章里搭建的swarm集群

###性能

go微服务占用很小的内存－但是性能怎么样？对编程语言做有意义的基测很难。从基测网站上提交的算法上看，go比java8大部分时间会快。go大部分情况和c++差不多快，但在一些基测上，要慢很多。就是说，对于普通的微服务工作－负载HTTP/RPC,序列化/反序列化数据结构，网络吞吐方面，go可以表现的不错。

另一个重要的特性是go具有垃圾回收功能，在go 1.5GC的垃圾回收中只需要停顿几微秒。go的垃圾回收也许不是那么成熟，但是在1.2之后，他表现的很稳定。更为惊奇的是，你可以更改通过更改整个栈相对于类的大小，来更改垃圾回收的性能。

然而，我们在测试性能的同时会写我们的第一个微服务，之后我们会加入熔断，追踪，日志等功能。在我们加入越来越多的功能之后，我们会用Gatling来测试我们的性能。

###启动时间

----

另一个go的特性是它的启动速度非常快。一个普通的HTTP服务器加上一些路由和json序列化功能的服务在几百毫秒就可以启动。当我们在docker中运行时，我们可以运行它们在几秒之内。然而，一个spring boot的微服务需要至少10秒。这个看起来好像没什么影响，但是在你需要快速应对大流量的处理时将会非常有用。


###静态链接二进制

----

另一个优点go的静态链接的二进制包含所有的依赖在一个可执行的二进制文件中，我们可以把这个包放在docker容器中。同时这个文件不大，一般来说10-20MB。这样我们就能得到一个很简单的dokerfile.我们可以用一个很基本的docker镜像来开始。我用的是　iron/base，这个镜像大约6MB。

```
FROM iron/base
EXPOSE 6868
ADD eventservice-linux-amd64 \
ENTRYPOINT ["./eventservice-linux-amd64", "-profile=test"]
```
换句话说，不需要JVM或者其他运行的组件，只需要这个镜像里标准的ｃ库
我们之后会讲解如何编译二进制文件和-profile=test是什么意思。

###总结

----

这篇博客中，我们介绍为什么要用go来做微服务。主要原因是：
* 小的内存消耗
* 性能良好
* 静态链接二进制文件的方便

下一篇文章中，我们会开始写第一个微服务。

























