<?php

namespace EasySwoole\Server\WebSocket;

use EasySwoole\Console\Input;
use EasySwoole\Server\WebSocket\Connections;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Server as SwooleServer;
use EasySwoole\Server\Http\HttpServer;

class WebSocketServer extends HttpServer
{

    protected $connections;

    protected function creatServer()
    {
        $this->swooleServer = new SwooleServer($this->host, $this->port);

        Input::info('ws://' . $this->host . ":" . $this->port, 'websocket服务');
    }

    protected function initEvent()
    {

        $event = [
            'request'   => 'onRequest',
            'open'      => 'onOpen',
            'message'   => 'onMessage',
            'close'     => 'onClose',
            'handshake' => 'onHandShake'
        ];
        $this->setEvent('sub', $event);
    }

    public function onOpen(SwooleServer $server, $request)
    {
        Connections::init($request->fd, $request->server['path_info']);
        app('route')->setFlag('WebSocket')->setMethod('open')->match($request->server['path_info'],
            [$server, $request]);
    }


    public function onMessage(SwooleServer $server, $frame)
    {
        $path = (Connections::get($frame->fd))['path'];

        $return = app('route')->setFlag('WebSocket')->setMethod('message')->match($path, [$server, $frame]);
    }

    public function onClose($ser, $fd)
    {
        $path = (Connections::get($fd))['path'];

        $return = app('route')->setFlag('WebSocket')->setMethod('close')->match($path, [$ser, $fd]);

        Connections::del($fd);
    }

    public function onHandShake(Request $request, Response $response)
    {
        $this->app->make('event')->trigger('ws.handshake', [$this, $request, $response]);
        //设置onHandShanke后，就不会触发onOpen事件

        $this->onOpen($this->swooleServer,$request);
    }


}