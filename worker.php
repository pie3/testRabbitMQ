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
//定义 持久消息队列 - 将第三个参数传递给 queue_declare 为 true,表示宣布消息是持久的
$channel->queue_declare('task_queue', false, true, false, false);
echo ' [*] Waiting for message. To exit press CTRL+C', "\n";

$callback = function ($msg) {
    echo " [x] Received ", $msg->body, "\n";
    sleep(substr_count($msg->body, '.'));//模拟任务执行时间
    echo " [x] Done", "\n";
    // 在完成任务后向工作人员发送适当的 消息确认，消费者发回询问
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};


//$channel->basic_consume('hello', '', false, true, false, false, $callback);

// 设置 负载均衡 - 使用 basic_qos 方法和 prefetch_count = 1 设置。 
// 这告诉 RabbitMQ 一次不要向工作人员发送多个消息。 或者换句话说，不要向工作人员发送新消息，直到它处理并确认了前一个消息
$channel->basic_qos(null, 1, null);

// 启用 消息确认 - 消息确认 默认关闭，将 basic_consume 第四个参数设置为 false(true 表示不询问)即可开启
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();