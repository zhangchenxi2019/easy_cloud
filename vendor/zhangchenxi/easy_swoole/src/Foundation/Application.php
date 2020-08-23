<?php
namespace EasySwoole\Foundation;

use EasySwoole\Config\Config;
use EasySwoole\Container\Container;
use EasySwoole\Event\Event;
use EasySwoole\Index;
use EasySwoole\Message\Http\Request;
use EasySwoole\Route\Route;
use EasySwoole\Server\Http\HttpServer;
use EasySwoole\Server\WebSocket\WebSocketServer;

class Application extends Container
{


    const WELCOME = "
      ______                          _____                              _        
     |  ____|                        / ____|                            | |       
     | |__      __ _   ___   _   _  | (___   __      __   ___     ___   | |   ___ 
     |  __|    / _` | / __| | | | |  \___ \  \ \ /\ / /  / _ \   / _ \  | |  / _ \
     | |____  | (_| | \__ \ | |_| |  ____) |  \ V  V /  | (_) | | (_) | | | |  __/
     |______|  \__,_| |___/  \__, | |_____/    \_/\_/    \___/   \___/  |_|  \___|
                              __/ |                                                
                             |___/                                                
    ";

    protected $basePath = "";

    public function __construct($path = null)
    {
        if(!empty($path)){
            $this->setBasePath($path);
        }
        $this->registerBaseBindings();
        $this->init();
        dd( self::WELCOME);
    }


    public function run($arg)
    {
        $server = null;
        switch ($arg[1]){
            case "http:start":
                $server = new HttpServer();
                break;
            case "ws:start":
                $server = new WebSocketServer();
                break;
        }

        $server->start();
    }

    public function registerBaseBindings()
    {
        self::setInstance($this);
        $binds = [
            'config'=> (new Config()),
            'index'=> (new Index()),
            'httpRequest'=>(new Request()),
        ];

        foreach ($binds as $key=>$value){
            $this->bind($key,$value);
        }
    }

    public function init()
    {
        $this->bind('route',Route::getInstance()->registerRoute());
        $this->bind('event',$this->registerEvent());

    }

    protected function registerEvent()
    {
        $event = new Event();
        $files = scandir($this->getBasePath().'/app/Listener');
        foreach ($files as $key => $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $class = 'App\\Listener\\'.explode('.',$file)[0];
            if(class_exists($class)){
                $listener = new $class($this);
                $event->register($listener->getName(),[$listener,'handler']);
            }
        }
        return $event;
    }
    public function setBasePath($path)
    {
        $this->basePath = rtrim($path,'\/');
    }

    public function getBasePath()
    {
        return $this->basePath;
    }
}
