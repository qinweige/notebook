第四章:用GoConvey做测试和mock

我们应该怎样做微服务的测试?这有什么特别的挑战么.这节,我们将看下面几点:
* 单元测试
* 用Goconey写行为模式的单元测试
* 介绍mocking技巧
因为这章不会改变核心服务代码,所以没有基测

###微服务测试简介
----
首先,你必须记住测试金字塔:

[!img](img/part4-pyramid.png)

单元测试必须作为你集成,e2e的基础,验收测试更不容易开发和维护
微服务有一些不同的测试难点,构建软件架构用到的准则和做测试一样.微服务的单元测试和传统的不太一样,我们会在这里讲一下.
总之,我强调几点:
* 做正常地单元测试-你的商业逻辑,验证器等等不因为在微服务上运行而不同.
* 集成的部分,例如和其他服务沟通,发送信息,使用数据库,这些必须用依赖注入的方法来设计,这样才能用上mock
* 许多微服务的特性:配置文件,其他服务的沟通,弹力测试等.花很多的时间才能做一点测试.这些测试可以做集成测试,你把docker容器整体的做测试.这样性价比会比较高.

###代码
----
完整代码
```
git checkout P4
```
###介绍
----
go的单元测试又go的作者设计的遵从语言习惯的模式.测试文件由命名来识别.如果我们想测试handler.go中的东西,我们创建文件handlers_test.go,在同一个文件夹下.
我们从悲观测试开始,断言404,当我们请求不存在的地址
```
package service

import (
        . "github.com/smartystreets/goconvey/convey"
        "testing"
        "net/http/httptest"
)

func TestGetAccountWrongPath(t *testing.T) {

        Convey("Given a HTTP request for /invalid/123", t, func() {
                req := httptest.NewRequest("GET", "/invalid/123", nil)
                resp := httptest.NewRecorder()

                Convey("When the request is handled by the Router", func() {
                        NewRouter().ServeHTTP(resp, req)

                        Convey("Then the response should be a 404", func() {
                                So(resp.Code, ShouldEqual, 404)
                        })
                })
        })
}
```
这个测试显示"Given-when-then"(如果-当-推断)的模式.我们也用httptest包,我们用它来声明请求的object也用做回复的object用来作为断言的条件.
去accountservice下运行他:
```
> go test ./...
?   	github.com/callistaenterprise/goblog/accountservice	[no test files]
?   	github.com/callistaenterprise/goblog/accountservice/dbclient	[no test files]
?   	github.com/callistaenterprise/goblog/accountservice/model	[no test files]
ok  	github.com/callistaenterprise/goblog/accountservice/service	0.012s
```
./...会运行当前文件夹和所有子文件夹下的测试文件.我们也可以进入service文件夹下go test,这会运行这个文件夹下的测试.

###Mocking
----
上面的测试不需要mock,因为我们不会用到GetAccount里面的DBClient.对于好的请求,我们需要返回结果,我们就需要mock客户端来连接BoltDb.有许多mocking的方法.我最喜欢的是stretchr/testify/mock这个包
在/dbclient文件夹下,创建mockclient.go来实现IBoltClient接口:
```
package dbclient

import (
        "github.com/stretchr/testify/mock"
        "github.com/callistaenterprise/goblog/accountservice/model"
)

// MockBoltClient is a mock implementation of a datastore client for testing purposes.
// Instead of the bolt.DB pointer, we're just putting a generic mock object from
// strechr/testify
type MockBoltClient struct {
        mock.Mock
}

// From here, we'll declare three functions that makes our MockBoltClient fulfill the interface IBoltClient that we declared in part 3.
func (m *MockBoltClient) QueryAccount(accountId string) (model.Account, error) {
        args := m.Mock.Called(accountId)
        return args.Get(0).(model.Account), args.Error(1)
}

func (m *MockBoltClient) OpenBoltDb() {
        // Does nothing
}

func (m *MockBoltClient) Seed() {
        // Does nothing
}
```
MockBoltClient现在可以作为我们可以编写的mock.向上边那样,我们隐式的定义了所有的函数,实现IBoltClient接口.
如果你不喜欢这样的mock方法,可以看一下mockery,他可以产生任何go接口的mock
QueryAccount函数里有点奇怪.但这就是testify的做法,这样能让我们有一个全面的内部控制的mock.

###编写mock
----
我们创建下一个测试函数在handlers_test.go中:
```
func TestGetAccount(t *testing.T) {
        // Create a mock instance that implements the IBoltClient interface
        mockRepo := &dbclient.MockBoltClient{}

        // Declare two mock behaviours. For "123" as input, return a proper Account struct and nil as error.
        // For "456" as input, return an empty Account object and a real error.
        mockRepo.On("QueryAccount", "123").Return(model.Account{Id:"123", Name:"Person_123"}, nil)
        mockRepo.On("QueryAccount", "456").Return(model.Account{}, fmt.Errorf("Some error"))
        
        // Finally, assign mockRepo to the DBClient field (it's in _handlers.go_, e.g. in the same package)
        DBClient = mockRepo
        Convey("Given a HTTP request for /accounts/123", t, func() {
        req := httptest.NewRequest("GET", "/accounts/123", nil)
        resp := httptest.NewRecorder()

        Convey("When the request is handled by the Router", func() {
                NewRouter().ServeHTTP(resp, req)

                Convey("Then the response should be a 200", func() {
                        So(resp.Code, ShouldEqual, 200)

                        account := model.Account{}
                        json.Unmarshal(resp.Body.Bytes(), &account)
                        So(account.Id, ShouldEqual, "123")
                        So(account.Name, ShouldEqual, "Person_123")
                })
        })
})
}
```
这段测试请求path/accounts/123,我们的mock实现了这个.在when中,我们断言http状态,反序列化Account结构,同时段验结果和我们的mock的结果相同.
我喜欢Goconvey因为这种"Given-when-then"的方式很容易读
我们也请求一个悲观地址/accounts/456,断言会得到http404:
```
Convey("Given a HTTP request for /accounts/456", t, func() {
        req := httptest.NewRequest("GET", "/accounts/456", nil)
        resp := httptest.NewRecorder()

        Convey("When the request is handled by the Router", func() {
                NewRouter().ServeHTTP(resp, req)

                Convey("Then the response should be a 404", func() {
                        So(resp.Code, ShouldEqual, 404)
                })
        })
})

```
跑一下.
```
> go test ./...
?   	github.com/callistaenterprise/goblog/accountservice	[no test files]
?   	github.com/callistaenterprise/goblog/accountservice/dbclient	[no test files]
?   	github.com/callistaenterprise/goblog/accountservice/model	[no test files]
ok  	github.com/callistaenterprise/goblog/accountservice/service	0.026s
```
全绿!goconvey有一个GUI能在我们每次保存文件时自动执行所有测试.我不细讲了,这里看一下代码覆盖度报告:
[!img](img/)

###其他测试
---
goconvey这种用行为测试方式写的单元测试并不是每个人都喜欢.有许多其他的测试框架.你能搜索到很多.
如果我们看测试金字塔上面,我们会想写集成测试,或者最后的验收测试.我们之后会启动真正的boltDb,之后来讲一讲集成测试.也许会用go docker的远程api和写好的boltdb镜像

###总结
---
这部分我们用goconvey写第一个单元测试.同事用mock包帮我们模拟.
下一节,我们会启动docker swarm并且部署我们的服务进swarm中





















