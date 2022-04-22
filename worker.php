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

//$channel->queue_declare('hello', false, false, false, false);
//将第三个参数传递给 queue_declare 为 true,表示宣布消息是持久的
$channel->queue_declare('task_queue', false, true, false, false);
echo ' [*] Waiting for message. To exit press CTRL+C', "\n";

$callback = function ($msg) {
    echo " [x] Received ", $msg->body, "\n";
    sleep(substr_count($msg->body, '.'));//模拟任务执行时间
    echo " [x] Done", "\n";
    //消息确认，消费者发回询问
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};


//$channel->basic_consume('hello', '', false, true, false, false, $callback);
$channel->basic_qos(null, 1, null);
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();