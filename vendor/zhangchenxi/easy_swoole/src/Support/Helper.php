<?php
use EasySwoole\Foundation\Application;

use \EasySwoole\Console\Input;
if(!function_exists('app')){

    function app($abstract = null){
        if(is_null($abstract)){
            return Application::getInstance();
        }
        return Application::getInstance()->make($abstract);
    }

}

if(!function_exists('dd')){

    function dd ($message,$descripition = null){

        Input::info($message,$descripition);
    }
}