<?php
namespace EasySwoole\Config;

Class Config
{

    protected $items = [];

    protected $configPath = '';

    function __construct()
    {
        $this->configPath = app()->getBasePath().'/config';

        $this->items = $this->phpParser();
    }


    public function phpParser()
    {

        $files = scandir($this->configPath);
        $data = null;

        foreach ($files as $key => $file) {

            if ($file == '.' || $file == '..') {
                continue;
            }

            $filename = stristr($file, '.php', true);

            $data[$filename] = include $this->configPath . "/" . $file;
        }
        return $data;
    }


    public function get($keys)
    {
        $data = $this->items;
        foreach (explode('.', $keys) as $key => $value) {
            $data = $data[$value];
        }
        return $data;
    }
}