<?php

namespace App\Consumers;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

class KitchenEventConsumer
{
    private AMQPStreamConnection $connection;
    private $channel;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            host: env('RABBITMQ_HOST', 'localhost'),
            port: env('RABBITMQ_PORT', 5672),
            user: env('RABBITMQ_USER', 'guest'),
            password: env('RABBITMQ_PASSWORD', 'guest')
        );

        $this->channel = $this->connection->channel();

        $this->channel->exchange_declare(
            exchange: 'plov_express_events',
            type: 'topic',
            passive: false,
            durable: true,
            auto_delete: false
        );

        $this->channel->queue_declare(
            queue: 'kitchen_service_queue',
            passive: false,
            durable: true,
            exclusive: false,
            auto_delete: false
        );

        $this->channel->queue_bind(
            queue: 'kitchen_service_queue',
            exchange: 'plov_express_events',
            routing_key: 'payment.processed'
        );

        $this->channel->basic_qos(
            prefetch_size: 0,
            prefetch_count: 1,
            global: false
        );
    }

    public function consume(): void
    {
        $callback = function (AMQPMessage $msg) {
            try {
                $event = json_decode($msg->body, true);

                if ($event['data']['status'] === 'paid') {
                    Log::info('Adding order to kitchen queue', [
                        'order_id' => $event['data']['order_id']
                    ]);

                    $this->addToKitchenQueue($event['data']);
                    $msg->ack();
                } else {
                    Log::info('Payment not successful, skipping kitchen', [
                        'order_id' => $event['data']['order_id']
                    ]);
                    $msg->ack();
                }

            } catch (\Exception $e) {
                Log::error('Failed to process kitchen event', [
                    'error' => $e->getMessage()
                ]);
                $msg->nack(requeue: true);
            }
        };

        $this->channel->basic_consume(
            queue: 'kitchen_service_queue',
            consumer_tag: '',
            no_local: false,
            no_ack: false,
            exclusive: false,
            nowait: false,
            callback: $callback
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    private function addToKitchenQueue(array $paymentData): void
    {
        Log::info('Order added to kitchen queue', [
            'order_id' => $paymentData['order_id']
        ]);
    }

    public function close(): void
    {
        $this->channel->close();
        $this->connection->close();
    }
}
