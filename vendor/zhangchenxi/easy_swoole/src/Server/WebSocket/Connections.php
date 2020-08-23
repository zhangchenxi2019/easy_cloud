<?php
namespace EasySwoole\Server\WebSocket;

class Connections{

    private static $connections = [];


    public static function init($fd,$path)
    {
        self::$connections[$fd]['path'] = $path;
    }


    /**
     * @param array $connections
     */
    public static function get($fd = null)
    {
        if($fd === null){
            return null;
        }
        return self::$connections[$fd];
    }

    /**
     * @param array $connections
     */
    public static function del($fd = null)
    {
        if($fd == null){
            return false;
        }
        if(isset(self::$connections[$fd])){
            unset(self::$connections[$fd]);
            return true;
        }
        return false;
    }
}