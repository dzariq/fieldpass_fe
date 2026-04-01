<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;

class ConsumerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consumer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RabbitMQ Consumer';

    /**
     * Execute the console command.
     *
     * @return int
     */

    /**
     * queue_declare function: 
     * Declares queue, creates if needed
     *
     * @param string $queue
     * @param bool $passive
     * @param bool $durable
     * @param bool $exclusive
     * @param bool $auto_delete
     * @param bool $nowait
     * @param array|AMQPTable $arguments
     * @param int|null $ticket
     * @return array|null
     *@throws \PhpAmqpLib\Exception\AMQPTimeoutException if the specified operation timeout was exceeded
    */

    /**
     * basic_consume function: 
     * @param string consumer_tag: Consumer identifier
     * @param bool no_local: Don't receive messages published by this consumer.
     * @param bool no_ack: If set to true, automatic acknowledgement mode will be used by this consumer. See https://www.rabbitmq.com/confirms.html for details.
     * @param bool exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
     * @param bool nowait:
     * callback: A PHP Callback
    */

    public function handle() 
    {
        $connection = new AMQPStreamConnection(
            config('queue.connections.rabbitmq.host'),
            config('queue.connections.rabbitmq.port'), 
            config('queue.connections.rabbitmq.user'), 
            config('queue.connections.rabbitmq.password'),
            config('queue.connections.rabbitmq.vhost')
        );
        
        $channel = $connection->channel();

        $channel->queue_declare(
            'board',
            false,
            true,
            false,
            false
        );

        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        $callback = function ($msg) {
            Log::info('BOARD EVENT RECEIVED: '.$msg->body);
            echo ' [x] Received ', $msg->body, "\n";
            $data = json_decode($msg->body);

            Log::info('BOARD EVENT ACTION: '.$data->action);

            // if($data->action == 'invitation')
            //     UserService::invite($data);

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);

        $channel->basic_consume(
            'board',
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

        $channel->close();

        return Command::SUCCESS;
    }
}