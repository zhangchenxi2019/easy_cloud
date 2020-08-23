<?php

namespace EasySwoole\Server;

/**
 * 所有服务的父类，写一些公共的操作
 * Class Server
 * @package EasySwoole\Server
 */

use EasySwoole\RPC\Rpc;
use Swoole\Coroutine;
use Swoole\Server as SwooleServer;
use Redis;
abstract class Server
{

    //属性
    protected $swooleServer;

    protected $app;

    protected $inotify = null;

    protected $port = 9600;

    protected $host = '0.0.0.0';

    protected $watchFile = false;

    protected $config = [
        'task_worker_num' => 0,
    ];

    protected $redis = null;
    /**
     * 用于记录pid的信息
     */
    protected $pidMap = [
        'masterPid'  => 0,
        'managerPid' => 0,
        'workerPids' => [],
        'taskPids'   => []
    ];

    /**
     * 注册的回调事件
     * @var array
     */
    protected $event = [
        //所有服务都会注册的事件
        "server" => [
            "start"        => "onStart",
            "managerStart" => "onManagerStart",
            "managerStop"  => "onManagerStop",
            "shutdown"     => "onShutdown",
            "workerStart"  => "onWorkerStart",
            "workerStop"   => "onWorkerStop",
            "workerError"  => "onWorkerError"
        ],
        //子类的服务
        "sub"=>[],
        //额外扩展的回调函数
        "ext"=>[],
    ];

    /*
     * 创建服务
     */
    protected abstract function creatServer();

    /**
     * 初始化监听的事件
     * @return mixed
     */
    protected abstract function initEvent();


    public function __construct()
    {
        $this->app = app();
        $this->initConfig();
        //创建swoole server
        $this->creatServer();
        //设置需要注册的回调函数
        $this->initEvent();
        //设置swoole的回调函数
        $this->setSwooleEvent();
    }

    public function initConfig()
    {
        $this->port = app('config')->get('Server.http.port');
        $this->host = app('config')->get('Server.http.host');
    }
    //通用的方法
    public function start()
    {
        if(!empty($this->swooleServer)){
            $this->swooleServer->set($this->config);
            if(app('config')->get('Server.http.tcpable')){
                new Rpc($this->swooleServer,app('config')->get('Server.http.rpc'));
            }
            $this->swooleServer->start();
        }
    }
    //回调方法

    public function setSwooleEvent()
    {
        foreach ($this->event as $type=>$events){
            foreach ($events as $event => $func){
                $this->swooleServer->on($event,[$this,$func]);
            }
        }
    }

    public function onStart(SwooleServer $server)
    {
        $this->app->make('event')->trigger('start',[$this]);

    }

    public function onManagerStart(SwooleServer $server)
    {

    }

    public function onManagerStop(SwooleServer $server)
    {

    }

    public function onShutdown(SwooleServer $server)
    {

    }

    public function onWorkerStart(SwooleServer $server)
    {
        $this->redis = new Redis();
        $host = $this->app->make('config')->get('Databases.redis.host');
        $port = $this->app->make('config')->get('Databases.redis.port');
        $this->redis->pconnect($host,$port);
    }

    public function onWorkerStop(SwooleServer $server)
    {

    }

    public function onWorkerError(SwooleServer $server)
    {


    }


    public function setEvent($type,$event)
    {
        if($type == 'server'){
            return $this;
        }
        $this->event[$type] = $event;
        return $this;
    }


    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getRedis()
    {
        return $this->redis;
    }
}