<?php
namespace EasySwoole\Server\Http;

use EasySwoole\Message\Http\Request as HttpRequest;
use EasySwoole\Server\Server;
use Swoole\Http\Server as SwooleServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

class HttpServer extends Server
{
    protected function creatServer()
    {
        $this->swooleServer = new SwooleServer($this->host,$this->port);
    }


    protected function initEvent()
    {
        $this->setEvent('sub',[
            'request'=>'onRequest'
        ]);
    }

    public function onRequest(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse)
    {
        $uri = $swooleRequest->server['request_uri'];
        if($uri == '/favicon.ico'){
            $swooleResponse->status(404);
            $swooleResponse->end('');
            return null;
        }
        $httpReques  =  HttpRequest::init($swooleRequest);

        //执行控制器的方法
        $return = app('route')->setFlag('Http')->setMethod($httpReques->getMethod())->match($httpReques->getUriPath());
        $swooleResponse->end($return);
    }

}