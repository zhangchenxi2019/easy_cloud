<?php
namespace EasyCloud\Server;

use EasySwoole\Console\Input;
use Firebase\JWT\JWT;
use Redis;
use Swoole\Coroutine\Http\Client;
use Swoole\Server as SwooleServer;
use Swoole\WebSocket\Server as SwooleWebSocketServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;


class Route extends Server{

    protected $redis;

    protected $dispatcher = null;

    protected $redis_key  = 'im_server';

    protected $arithmetic = 'round';

    protected function initEvent()
    {
        $this->setEvent('sub',[
            'request'=>'onRequest',
            'open'   =>'onOpen',
            'message'=>'onMessage',
            'close'  => 'onClose'
        ]);
    }
    public function onWorkerStart(SwooleServer $server)
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1',6379);
    }
    public function creatServer()
    {
        $this->swooleServer = new SwooleWebSocketServer($this->host,$this->port);
        Input::info("ws://".$this->host.":".$this->port);
    }

    public function onOpen(SwooleServer $server,$request)
    {
    }

    public function onMessage(SwooleServer $server,$frame)
    {

        $data = json_decode($frame->data,true);
        $fd  = $frame->fd;
        if(!empty($data['method'])){
            $this->getDispatcher()->{$data['method']}($this,$server,...[$fd,$data]);
        }
    }

    public function onClose(SwooleServer $server,$request)
    {
    }

    public function onRequest(SwooleRequest $request,SwooleResponse $response)
    {
        $uri = $request->server['request_uri'];
        if($uri == '/favicon.ico'){
            $request->status(404);
            $response->end('');
            return null;
        }
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST OPTIONS');
        $this->getDispatcher()->{$request->post['method']}($this,$request,$response);
    }

    public function getDispatcher()
    {
        if(empty($this->dispatcher)){
            $this->dispatcher = new Dispatcher();
        }
        return $this->dispatcher;
    }


    public function getRedis()
    {
        return $this->redis;
    }

    public function getRedisKey()
    {
        return $this->redis_key;
    }


    public function getArithmetic()
    {
        return $this->arithmetic;
    }


    public function getServers()
    {
        return $this->getRedis()->smembers($this->getRedisKey());
    }


    public function send($ip,$port,$data,$header = null,$uniqid = null)
    {
        $cli = new Client($ip,$port);
        $cli->setHeaders(['sec-websocket-protocol'=>$header]);
        $cli = new Client($ip,$port);
        if(!empty($header)){
            $cli->setHeaders($header);
        }
        if($cli->upgrade('/')){
            $cli->push(json_encode($data));
        }
        $this->confirmGo($uniqid,$data,$cli);
    }


}
