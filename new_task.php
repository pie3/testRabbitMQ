<?php
/**
 * Created by PhpStorm.
 * User: Pie
 * Date: 2021/3/25
 * Time: 4:57 PM
 */
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'root', 'rabbitmq1810');
$channel = $connection->channel();

/**
 *
 * $channel->queue_declare('hello', false, false, false, false);
 *
 * $data = implode(' ', array_slice($argv, 1));
 * if(empty($data)){
 * $data = "Hello World!";
 * }
 * $msg = new AMQPMessage($data);
 * $channel->basic_publish($msg, '', 'hello');
 */
//将第三个参数传递给 queue_declare 为 true,表示宣布消息是持久的
$channel->queue_declare('task_queue', false, true, false, false);
$data = implode(' ', array_slice($argv, 1));
if (empty($data)) {
    $data = "Hello World!";
}
//将消息标记为持久消息，通过设置 AMQPMessage 的属性 delivery_mode = 2
$msg = new AMQPMessage($data,
    array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
);

$channel->basic_publish($msg, '', 'task_queue');

echo " [x] Sent ", $data, "\n";

$channel->close();
$connection->close();