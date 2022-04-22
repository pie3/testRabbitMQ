<?php
/**
 * Created by PhpStorm.
 * User: Pie
 * Date: 2021/3/25
 * Time: 5:29 PM
 */
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'root', 'rabbitmq1810');
$channel = $connection->channel();

$channel->queue_declare('hello', false, false, false, false);
echo ' [*] Waiting for message. To exit press CTRL+C', "\n";

$callback = function ($msg) {
    echo " [x] Received ", $msg->body, "\n";
};

$channel->basic_consume('hello', '', false, true, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();