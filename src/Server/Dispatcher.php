<?php

namespace EasyCloud\Server;

use EasyCloud\Supper\Arithmetic;
use Swoole\Coroutine\Http\Client;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Server as swooleServer;
use Redis;
use Firebase\JWT\JWT;

class Dispatcher
{

    public function register(Route $route, swooleServer $server, $fd, $data)
    {
        $redis     = $route->getRedis();
        $redis_key = $route->getRedisKey();
        $value     = [
            'ip'   => $data['ip'],
            'port' => $data['port'],
        ];
        $value     = json_encode($value);
        $res       = $redis->sadd($redis_key, $value);
        $server->tick(3000, function ($timer_id, swooleServer $server, Redis $redis, $redis_key, $fd, $value) {
            if (!$server->exist($fd)) {
                $redis->sRem($redis_key, $value);
                $server->clearTimer($timer_id);
                dd("im server 宕机，主动清空");
            }
        }, $server, $redis, $redis_key, $fd, $value);
    }


    public function login(Route $route, Request $request, Response $response)
    {
        $data = $request->post;

        $imServer = json_decode($this->getImServer($route), true);
        $url = $imServer['ip'] . ":" . $imServer['port'];
        $token = $this->getJwtToken($imServer['ip'],$data['id'],$url);
        $response->end(json_encode(['token'=>$token,'url'=>$url]));
    }

    public function getJwtToken($sid, $uid, $url)
    {
        $key   = 'easycloud';
        $time  = time();
        $payload = [
            'iss'  => 'http://',
            'aud'  => 'http://',
            'iat'  => $time,
            'nbf'  => $time,
            'exp'  => $time + 7200,
            'data' =>[
                "uid"=>$uid,
                "name"=>'client'.$time.$sid,
                "service_url"=>$url,
            ]
        ];
        return Jwt::encode($payload,$key);
    }

    public function getImServer(Route $route)
    {
        $servers = $route->getRedis()->smembers($route->getRedisKey());
        if (!empty($servers)) {
            return Arithmetic::{$route->getArithmetic()}($servers);
        }
    }


    public function routeBroadcast(Route $route,swooleServer $swooleServer,$fd,$data)
    {
        $servers = $route->getServers();

        $token = $this->getJwtToken(0,0,$route->getHost().":".$route->getPort());

        foreach ($servers as $server){
            $serverInfo = json_decode($server,true);

//            $this->send($route,$serverInfo['ip'],$serverInfo['port'],$data);
            $uniqid = session_create_id();
            var_dump($uniqid);
            $route->send($serverInfo['ip'],$serverInfo['port'],[
                'msg'=>$data['msg'],
                'method'=>'routeBroadcast',
                'msg_id'=>$uniqid
            ],['sec-websocket-protocol'=>$token],$uniqid);

        }
    }


    private function send($route,$ip,$port,$data)
    {
        $token = $this->getJwtToken(0,0,$route->getHost().":".$route->getPort());
        $cli = new Client($ip,$port);
        $cli->setHeaders(['sec-websocket-protocol'=>$token]);
        if($cli->upgrade('/')){
            $data['method'] = 'routeBroadcast';
            $cli->push(json_encode($data));
        }
    }
}