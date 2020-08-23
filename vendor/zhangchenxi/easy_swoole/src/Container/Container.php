<?php

namespace EasySwoole\Container;


use Exception;

class Container{

    //单例
    protected static $instance;
    //容器
    protected $bingdings = [];
    protected $instances = [];

    public function bind($abstract,$object)
    {
        //标识要绑定
        $this->bingdings[$abstract] = $object;
    }

    public function make($abstract,$parameters = [])
    {
        if(isset($this->instances[$abstract])){
            return $this->instances[$abstract];
        }
        if(!$this->has($abstract)){
            throw new Exception('没有找到这个对象',$abstract,500);
        }
        $object = $this->bingdings[$abstract];
        if($object instanceof Closure){
            return $object();
        }
        return $this->instances[$abstract] = (is_object($object))?$object: new $object(...$parameters);
    }
    public function has($abstract)
    {
        return isset($this->bingdings[$abstract]);
    }


    public static  function getInstance()
    {
        if(is_null(static::$instance)){
            static::$instance = new static();
        }
        return static::$instance;
    }


    public function setInstance($container = null)
    {
        return static::$instance = $container;
    }



}