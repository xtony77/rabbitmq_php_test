<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$queuesName = 'hello4';

// 持久消息不会因为重开机不见true
$durable = true;
$channel->queue_declare($queuesName, false, $durable, false, false);

// 一般讯息
// $msg = new AMQPMessage('Hello World 2!');
// 持久讯息
$msg = new AMQPMessage('Hello World 2!', array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

// 讯息发送
$channel->basic_publish($msg, '', $queuesName);

$channel->close();
$connection->close();