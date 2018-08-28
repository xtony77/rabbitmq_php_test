<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel    = $connection->channel();

$queuesName = 'testQueue';

// 持久消息不会因为重开机不见true
$durable = true;

$channel->queue_declare($queuesName, false, $durable, false, false);

// 讯息内容
$data = json_encode(['status' => 'success', 'data' => ['name' => 'xtony77', 'message' => 'hello world!']]);

// 一般讯息
// $msg = new AMQPMessage($data);
// 持久讯息
$msg = new AMQPMessage($data, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

// 讯息发送
$channel->basic_publish($msg, '', $queuesName);

$channel->close();
$connection->close();