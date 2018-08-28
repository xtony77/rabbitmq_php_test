<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPTimeoutException;

function receiveQueue($queuesName) 
{
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel    = $connection->channel();

    // 持久消息不会因为重开机不见true
    $durable = true;

    $channel->queue_declare($queuesName, false, $durable, false, false);

    echo " [*] Waiting for messages. To exit press CTRL+C\n";

    $callback = function (AMQPMessage $req) use ($channel, $queuesName) {
        $data = $req->body;
        echo ' [x] Received '.$data."\n";

        // 手动回覆处理结果
        $req->delivery_info['channel']->basic_ack($req->delivery_info['delivery_tag']);
    };

    // 处理并确认前一个消息之前，不要发送新消息
    // $channel->basic_qos(null, 1, null);

    // 手动回覆处理结果false，自动回覆true
    $autoAck = false;
    $channel->basic_consume($queuesName, '', false, $autoAck, false, false, $callback);

    while (count($channel->callbacks)) {
        try {
            $channel->wait(null, false, 5);
        } catch (AMQPTimeoutException $e) {
            break;
        }
    }

    $channel->close();
    $connection->close();
    return true;
}

$queuesName = 'testQueue';

$run = true;
while ( $run == true ) {
    $run = receiveQueue($queuesName);
}