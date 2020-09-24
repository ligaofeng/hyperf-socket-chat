# 介绍

基于Hyperf项目官方样例( https://github.com/hyperf/hyperf-skeleton )开发的一个基于socket.io与前端socket.io.js( https://socket.io )库Web IM聊天demo

# 安装环境要求
安装说明与 https://github.com/hyperf/hyperf-skeleton 上要求的一致

# 安装

检出代码后直接：
$ composer update

本项目演示所用的SQL文件在要目录下的hyperf.sql中。

再启动即可：
$ php bin/hyperf.php start

web页面监听的是9501端口：
`http://localhost:9501/`

socket协议监听的是9502端口：
`ws://127.0.0.1:9502`

测试9501端口服务是否正常：
`http://127.0.0.1:9501/index`

模拟uid为1的在房间1的Demo样例：
`http://127.0.0.1:9501/view/socket1`

模拟uid为2的在房间1的Demo样例：
`http://127.0.0.1:9501/view/socket2`

以上两个Demo样例正常情况如下：
![image](https://github.com/ligaofeng/hyperf-socket-chat/blob/master/public/1600936293417.jpg)

特别提示：hyperf启动后，在shell上的启动页面，目前可能偶尔会报类似的如下错误，此错误会导致长连接服务暂时断开：
````
PHP Fatal error:  Uncaught Swoole\Error: Socket#99 has already been bound to another coroutine#2, reading of the same socket in coroutine#3 at the same time is not allowed in /Users/mac/www/ktalk/vendor/hyperf/redis/src/RedisConnection.php:67
Stack trace:
#0 /Users/mac/www/ktalk/vendor/hyperf/redis/src/RedisConnection.php(67): Redis->multi()
#1 /Users/mac/www/ktalk/vendor/hyperf/redis/src/Redis.php(49): Hyperf\Redis\RedisConnection->__call()
#2 /Users/mac/www/ktalk/vendor/hyperf/redis/src/RedisProxy.php(32): Hyperf\Redis\Redis->__call()
#3 /Users/mac/www/ktalk/vendor/hyperf/socketio-server/src/Room/RedisAdapter.php(69): Hyperf\Redis\RedisProxy->__call()
#4 /Users/mac/www/ktalk/vendor/hyperf/socketio-server/src/Socket.php(70): Hyperf\SocketIOServer\Room\RedisAdapter->add()
#5 /Users/mac/www/ktalk/runtime/container/proxy/App_Controller_WebSocketController.proxy.php(100): Hyperf\SocketIOServer\Socket->join()
#6 /Users/mac/www/ktalk/runtime/container/proxy/App_Controller_WebSocketController.proxy.php(69): App\Controller\WebSocketC in /Users/mac/www/ktalk/vendor/hyperf/redis/src/RedisConnection.php on line 67
````

![image](https://github.com/ligaofeng/hyperf-socket-chat/blob/master/public/1600936770847.jpg)

目前初步判断是测试hyperf异步队列功能，而引入`https://hyperf.wiki/2.0/#/zh-cn/async-queue`库，
此库与Redis协程客户端库`https://hyperf.wiki/2.0/#/zh-cn/redis`有冲突。    

如果使用不到Redis协程客户端库直接composer remove hyperf/async-queue    

然后把config/autoload/async_queue.php中的return中的配置给注释掉    

再把config/autoload/processes.php中的引入的Hyperf\AsyncQueue\Process\ConsumerProcess::class给注释掉    

再把/app/Controller/WebSocketController.php中注解引入的QueueService相关的代码，包括onSay方法中的相关代码给注释掉即可。


