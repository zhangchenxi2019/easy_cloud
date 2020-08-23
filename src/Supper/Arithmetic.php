<?php
/********************************************************
 *   Copyright (C) 2020 All rights reserved.
 *
 *   Filename: Arithmetic.php
 *   Author  :
 *   Date    : 2020/8/9
 *   Describe: 文件描述
 *
 ********************************************************/

namespace EasyCloud\Supper;

class Arithmetic{

    protected static $roundLastIndex = 0;

    public static function round(array $list)
    {
        $index = self::$roundLastIndex;

        $url  = $list[$index];

        if($index + 1 > count($list) - 1){
            self::$roundLastIndex = 0;
        }else{
            self::$roundLastIndex++;
        }

        return $url;
    }


}