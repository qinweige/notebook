
第六节:health check
[原文地址](http://callistaenterprise.se/blogg/teknik/2017/02/17/go-blog-series-part1/)
转载请注明原文及[翻译地址](https://segmentfault.com/u/shoushouya)

当我们的微服务越来越复杂,让docker swarm知道我们的服务运行良好与否很重要.下面我们来看一下如何查看服务运行状况.
例如,我们的accountservice服务将没用如果不能 服务http或者链接数据库.
最好的办法就是提供一个healthcheck接入点.我们基于http,所以映射到/health,如果运行良好,返回http 200同事一些解释什么是良好的信息.如果有问题,非http 200返回,并解释哪里不好.有人认为都应该返回200,之后返回错误信息.我同意,但是这个简单例子中,我会用非200返回.

###代码
----
一样,你可以直接branch到这部分
```
git checkout P6
```

###加入BoltDB的检查
我们的服务如果不能连接database将没有用,因此我们加入函数 Check()
```
type IBoltClient interface {b
	OpenBoltDb()
	QueryAccount(accountId string) (model.Account, error)
	Seed()
	Check() bool //new
}
```
这个函数可能很简单,但是足够了,他将根据BoltDb能否连接而返回true/false.
```
func (bc *BoltClient) Check() bool {
	return bc.boltDB != nil
}
```
mocked代码在mackclient.go遵从stretchr/testify的形式
```
func (m *MockBoltClient) Check() bool {
	args := m.Mock.Called()
	return args.Get(0).(bool)
```
###加入/health路径
----
很直接,我们在routes.go中加入
```
Route{
	"HealthCheck",
	"GET",
	"/health",
	HealthCheck
},
```
我们用函数HealthCheck来处理请求,我们把这个函数加到handler.go中:
```
func HealthCheck(w http.ResponseWriter, r *http.Request) {
        // Since we're here, we already know that HTTP service is up. Let's just check the state of the boltdb connection
        dbUp := DBClient.Check()
        if dbUp {
                data, _ := json.Marshal(healthCheckResponse{Status: "UP"})
                writeJsonResponse(w, http.StatusOK, data)
        } else {
                data, _ := json.Marshal(healthCheckResponse{Status: "Database unaccessible"})
                writeJsonResponse(w, http.StatusServiceUnavailable, data)
        }
}

func writeJsonResponse(w http.ResponseWriter, status int, data []byte) {
        w.Header().Set("Content-Type", "application/json")
        w.Header().Set("Content-Length", strconv.Itoa(len(data)))
        w.WriteHeader(status)
        w.Write(data)
}

type healthCheckResponse struct {
        Status string `json:"status"`
}
```
HealthCheck函数用Check()函数来检查数据库情况.如果正常,我们返回healthCheckResponse结构的实例.注意这个小写的首字母,这样只有在这个package中才能用这个结构.我们也提取出返回结果的代码进一个函数来让我们不重复代码.

###运行
----
在blog/accountservice文件夹中,运行:
```
> go run *.go
Starting accountservice
Seeded 100 fake accounts...
2017/03/03 21:00:31 Starting HTTP service at 6767
```
curl这个/health路径
```
> curl localhost:6767/health
{"status":"UP"}
```
###docker healthcheck
----
![img](img/part6-healthcheck(1))

接下来,我们用docker的健康检查机制来检查我们的服务.加入下面命令在Dockerfile:
```
HEALTHCHECK --interval=5s --timeout=5s CMD["./healthchecker-linux-amd64", "-port=6767"] || exit 1
```
healthchecker-linux-amd64是什么?docker自己不知道怎样做这个健康检查,我们需要帮一下,我们在CMD命令输入来指引到/health路径.根据exit code,docker会判断服务良好与否.如果太多的检查失败,swarm会关掉容器并开启新的实例
最常见的健康检查使用curl,然而这要求我们的docker镜像安装curl.这里我们会用go来执行这个小程序.

###创建helathchecker程序
----
在goblog下增加文件夹
```
mkdir healthchecker
```
加入main.go
```
package main

import (
	"flag"
	"net/http"
	"os"
)

func main() {
	port := flag.String("port", "80", "port on localhost to check") 
	flag.Parse()

	resp, err := http.Get("http://127.0.0.1:" + *port + "/health")    // Note pointer dereference using *
	
	// If there is an error or non-200 status, exit with 1 signaling unsuccessful check.
	if err != nil || resp.StatusCode != 200 {
		os.Exit(1)
	}
	os.Exit(0)
}
```
代码不多,主要做:
* 用内置flags读取-port=NNNN命令参数,如果没有,用默认端口80
* 开始http get请求127.0.0.1:[port]/health
* 如果有错误或者返回状态非200,退出同一个非0值,0==成功,>0==失败

试一下,如果你停止了accountservice,用go run *.go启动,或者编译它go build ./accountservice
之后回到后台运行healthchecker
```
> cd $GOPATH/src/github.com/callistaenterprise/goblog/healthchecker
> go run *.go
exit status 1
```
哎呀!我们忘记给端口号了.再试一次
```
> go run *.go -port=6767
>
```
没有输出表示我们成功了.好,让我们编译一个linux/amd64二进制并加入到accountservice中,通过加入healthchecker在dockerfile中. 我们用copyall.sh脚本来做:
```
#!/bin/bash
export GOOS=linux
export CGO_ENABLED=0

cd accountservice;go get;go build -o accountservice-linux-amd64;echo built `pwd`;cd ..

// NEW, builds the healthchecker binary
cd healthchecker;go get;go build -o healthchecker-linux-amd64;echo built `pwd`;cd ..

export GOOS=darwin
   
// NEW, copies the healthchecker binary into the accountservice/ folder
cp healthchecker/healthchecker-linux-amd64 accountservice/

docker build -t someprefix/accountservice accountservice/
```
同时,我们更新accountservice的dockerfile:
```
FROM iron/base
EXPOSE 6767

ADD accountservice-linux-amd64 /

# NEW!! 
ADD healthchecker-linux-amd64 /
HEALTHCHECK --interval=3s --timeout=3s CMD ["./healthchecker-linux-amd64", "-port=6767"] || exit 1

ENTRYPOINT ["./accountservice-linux-amd64"]
```
加入的部分
* 加入一个ADD语句来确定healthchecker加入到镜像中.
* HEALTHCHECK语句告诉docker每3s执行一次,超时为3s

###部署healthcheck
----
现在我们能部署带有healthchecking的accountservice了.自动化来做这些事,加入两行到copyall.sh中:
```
docker service rm accountservice
docker service create --name=accountservice --replica=1 --network=my_network -p=6767:6767
someprefix/accountservice
```
运行./copyall.sh等几秒,之后检查容器状态,docker ps:
```
> docker ps
CONTAINER ID        IMAGE                             COMMAND                 CREATED        STATUS                
1d9ec8122961        someprefix/accountservice:latest  "./accountservice-lin"  8 seconds ago  Up 6 seconds (healthy)
107dc2f5e3fc        manomarks/visualizer              "npm start"             7 days ago     Up 7 days
```
我们看到(healthy)字段在status栏,没有健康检查的服务不会有这个提示.

###看一下失败的情形
----
让我们加入可以测试的api来让路径表现的不健康.在routes.go中,加入新路径:

```
Route{
        "Testability",
        "GET",
        "/testability/healthy/{state}",
        SetHealthyState,
},    
```
这个路径(你不应该包括他在生产环境)提供我们一个让健康检查失败的方法.SetHealthyState函数在handlers.go中:
```
var isHealthy = true // NEW

func SetHealthyState(w http.ResponseWriter, r *http.Request) {

        // Read the 'state' path parameter from the mux map and convert to a bool
        var state, err = strconv.ParseBool(mux.Vars(r)["state"])
        
        // If we couldn't parse the state param, return a HTTP 400
        if err != nil {
                fmt.Println("Invalid request to SetHealthyState, allowed values are true or false")
                w.WriteHeader(http.StatusBadRequest)
                return
        }
        
        // Otherwise, mutate the package scoped "isHealthy" variable.
        isHealthy = state
        w.WriteHeader(http.StatusOK)
}
```
重启accountservice
```
func HealthCheck(w http.ResponseWriter, r *http.Request) {
        // Since we're here, we already know that HTTP service is up. Let's just check the state of the boltdb connection
        dbUp := DBClient.Check()
        
        if dbUp && isHealthy {              // NEW condition here!
                data, _ := json.Marshal(
                ...
        ...        
}
```
重新请求healthcheck
```
> cd $GOPATH/src/github.com/callistaenterprise/goblog/accountservice
> go run *.go
Starting accountservice
Seeded 100 fake accounts...
2017/03/03 21:19:24 Starting HTTP service at 6767
```
第一次尝试成功,现在改变accountservice用curl请求到测试路径
```
> curl localhost:6767/testability/healthy/false
> go run *.go -port=6767
exit status 1
```
工作正常,让我们在docker swarm中运行,用copyall.sh重新编译和部署
```
> cd $GOPATH/src/github.com/callistaenterprise/goblog
> ./copyall.sh
```
等一会,之后运行docker ps来看我们的健康服务
```
> docker ps
CONTAINER ID    IMAGE                            COMMAND                CREATED         STATUS 
8640f41f9939    someprefix/accountservice:latest "./accountservice-lin" 19 seconds ago  Up 18 seconds (healthy)
```
注意CONTAINER ID和CREATED.请求测试api,我的是192.168.99.100
```
> curl $ManagerIP:6767/testability/healthy/false
>
```
现在,运行docker ps
```
> docker ps
CONTAINER ID        IMAGE                            COMMAND                CREATED         STATUS                                                             NAMES
0a6dc695fc2d        someprefix/accountservice:latest "./accountservice-lin" 3 seconds ago  Up 2 seconds (healthy)
```
看,一个新的CONTAINER ID和新的CREATED和STATUS时间戳.因为swarm每三秒会检查一次,之后发现服务不健康,所以用一个新的服务替换掉,并且不需要管理员的插手

###总结
---
我们加入一个简单的/health路径和一些docker的健康检查机制.展示swarm是如何控制非健康服务的.
下一节,我们会深入swarm,我们会关注微服务两个架构:服务发现和负载均衡. 























