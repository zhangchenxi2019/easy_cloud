# easy_cloud
启动cloud服务：
 php  easy_cloud/bin easycloud.php
 
 easy_cloud 作为 Im Servers的 route 服务器。
 
 IM server 启动后对在 route 服务其中注册服务器信息（注册到redis中），同时route服务器会对im server进行心跳检测，监控im的服务器状态，如某一台server发生宕机，从该redis删除服务器信息。
 
用户client登录请求route服务器，route服务器会对已经注册完毕的imserver进行一个负载均衡算法（轮询，加权轮询，随机等）给client 返回imserver 信息。client 根据route返回的信息与server连接。
 
