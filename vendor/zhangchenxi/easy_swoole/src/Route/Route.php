<?php

namespace EasySwoole\Route;

use EasySwoole\Console\Input;

class Route
{

    protected static $instance;

    protected $routeMap = [];

    protected $routes = [];

    protected $verbs = ['GET', 'POST', 'PUT', 'PATH', 'DELETE'];

    //记录请求的方式
    protected $method = null;

    //标识
    protected $flag;

    protected function __construct()
    {
        $this->routeMap = [
            'Http'      => app()->getBasePath() . "/route/http.php",
            'WebSocket' => app()->getBasePath() . "/route/web_socket.php"
        ];
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function get($uri, $action)
    {
        $this->addRoute(['GET'], $uri, $action);
    }


    public function post($uri, $action)
    {
        $this->addRoute(['POST'], $uri, $action);
    }


    public function any($uri, $action)
    {
        $this->addRoute($this->verbs, $uri, $action);

    }

    public function addRoute($methods, $uri, $action)
    {
        foreach ($methods as $method) {
            $this->routes[$this->flag][$method][$uri] = $action;
        }

        return $this;
    }

    public function match($path,$params = [])
    {
        //1,获取uripath
        //2,根据请求的方式获取路由

        $routes = $this->routes[$this->flag][$this->method];
        $action = null;
        foreach ($routes as $uri => $value) {
            $uri = ($uri && substr($uri, 0, 1) != '/') ? "/" . $uri : $uri;
            if ($path == $uri) {
                $action = $value;
                break;
            }
        }
        if (!empty($action)) {
            return $this->runAction($action,$params);
        }
        Input::info("没有找到方法");
        return "404";
    }

    //运行方法
    private function runAction($action,$params = [])
    {
        if ($action instanceof \Closure) {
            return $action(...$params);
        } else {
            //控制器解析
            //控制器命名空间
            $namespace = "\App\\".$this->flag."\Controller\\";
            //IndexController@hello
            $arr        = explode('@', $action);
            $controller = $namespace . $arr[0];
            $class      = new $controller();
            return $class->{$arr[1]}(...$params);
        }
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function registerRoute()
    {
        foreach ($this->routeMap as $key => $path) {
            $this->flag = $key;
            require_once $path;
        }
        return $this;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function setRouteMap($map)
    {
        $this->routeMap = $map;
    }

    public function getRouteMap()
    {
        return $this->routeMap;
    }

    public function setFlag($flag)
    {
        $this->flag = $flag;
        return $this;
    }

    public function wsController($uri, $controller)
    {

        $actions = [
            'open',
            'message',
            'close'
        ];

        foreach ($actions as $kye => $action) {
            $this->addRoute([$action], $uri, $controller . '@' . $action);
        }

    }
}