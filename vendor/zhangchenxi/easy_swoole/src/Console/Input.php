<?php
namespace EasySwoole\Console;

class Input{

    public static function info($message,$descripition = null)
    {

        $return = '========>>>'.$descripition." start\n";
        if(is_array($message)){
            $return = $return.var_export($message,true);
        }else{
            $return .= $message."\n";
        }
        $return .= '========>>>'.$descripition." end\n";

        echo $return;
    }



}