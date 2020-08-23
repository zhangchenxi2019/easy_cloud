<?php
namespace EasyCloud\Server;

class EasyCloud{
    public function run()
    {
        $routeServer = new Route();

        $routeServer->start();
    }
}