<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

class RabbitMQService
{
    private $connection;
    private $channel;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            config('queue.connections.rabbitmq.host'),
            config('queue.connections.rabbitmq.port'),
            config('queue.connections.rabbitmq.user'),
            config('queue.connections.rabbitmq.password'),
            config('queue.connections.rabbitmq.vhost')
        );

        $this->channel = $this->connection->channel();
    }

    public function directQueue($callback,$action = '')
    {

        $channel = $this->connection->channel();

        $channel->queue_declare(
            $action,
            false,
            true,
            false,
            false
        );

        $channel->basic_qos(null, 1, null);

        $channel->basic_consume(
            $action,
            '',
            false,
            false,
            false,
            false,
            $callback
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        // $channel->close();
        $this->connection->close();
    }

    public function sendMessage(string $queue, string $message)
    {
        Log::info('BOARD PUBLISH EVENT: ' . $queue . '|' . $message);

        $msg = new AMQPMessage(
            $message,
            array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
        );

        $this->channel->basic_publish($msg, '', $queue);

        Log::info('BOARD PUBLISH EVENT SENT: ' . $queue . '|' . $message);

        $this->channel->close();

        $this->connection->close();
    }
}
