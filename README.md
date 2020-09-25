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
