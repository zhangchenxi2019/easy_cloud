<?php

namespace EasySwoole\Event;


use EasySwoole\Foundation\Application;

abstract class Listener{

    protected $name = 'interface';

    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    public abstract function handler();

    public function getName(){
        return $this->name;
    }
}