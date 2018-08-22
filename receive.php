<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$queuesName = 'hello4';

// 持久消息不会因为重开机不见true
$durable = true;
$channel->queue_declare($queuesName, false, $durable, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function ($msg) {
    echo ' [x] Received ', $msg->body, "\n";

    // 手动回覆处理完毕
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

// 处理并确认前一个消息之前，不要发送新消息
$channel->basic_qos(null, 1, null);

// 手动回覆处理结果false，自动回覆true
$autoAck = false;
$channel->basic_consume($queuesName, '', false, $autoAck, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();