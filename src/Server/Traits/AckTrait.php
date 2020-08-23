<?php
/********************************************************
 *   Copyright (C) 2020 All rights reserved.
 *
 *   Filename: AckTrait.php
 *   Date    : 2020/8/16
 *   Describe: 文件描述
 *
 ********************************************************/
namespace EasyCloud\Server\Traits;
use Co;
use Swoole\Coroutine\Http\Client;
use Swoole\Table;


trait AckTrait
{
    protected $table;

    protected function creatTable()
    {
        $this->table = new Table(1024);
        $this->table->column('ack',Table::TYPE_INT,1);
        $this->table->column('num',Table::TYPE_INT,1);
        $this->table->create();
    }


    protected function confirmGo($uniqid,$data,Client $client)
    {
        go(function()use($uniqid,$data,$client){
            while(true){
                Co::sleep(1);
                $ackData = $client->recv(0.2);
                $ack = json_decode($ackData->data,true);
                if(isset($ack['method']) && $ack['method'] == 'ack'){
                    $this->table->incr($ack['msg_id'],'ack');
                    dd('确认消息',$ack['msg_id']);
                }
                $task = $this->table->get($uniqid);
                if($task['ack'] > 0 ||$task['num'] > 3){
                    dd('清空任务',$uniqid);
                    $this->table->del($uniqid);
                    $client->close();
                    break;
                }else{
                    $client->push(json_encode($data));
                }
                $this->table->incr($uniqid,'num');
                dd($uniqid.'尝试一次');
            }
        });

    }

}