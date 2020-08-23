<?php

namespace EasySwoole\Event;

class Event{

    protected $events =[];

    public function register($event,$callback)
    {
        $event = strtolower($event);

        $this->events[$event] = ['callback'=>$callback];
    }


    public function trigger($event,$params = [])
    {
        $event = strtolower($event);
        if(isset($this->events[$event])){
            ($this->events[$event]['callback'])(...$params);
            dd('事件执行成功');
            return true;
        }
        return false;
    }

    public function getEvents($event = null)
    {
        $event = strtolower($event);

        return empty($event) ? $this->events :$this->events[$event];
    }

}
