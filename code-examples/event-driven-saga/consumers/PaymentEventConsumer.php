<?php

namespace App\Consumers;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

class PaymentEventConsumer
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
            queue: 'payment_service_queue',
            passive: false,
            durable: true,
            exclusive: false,
            auto_delete: false
        );

        $this->channel->queue_bind(
            queue: 'payment_service_queue',
            exchange: 'plov_express_events',
            routing_key: 'order.created'
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

                Log::info('Processing order.created event', [
                    'order_id' => $event['data']['order_id']
                ]);

                $this->processPayment($event['data']);

                $msg->ack();

                Log::info('Successfully processed payment', [
                    'order_id' => $event['data']['order_id']
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to process payment', [
                    'error' => $e->getMessage(),
                    'order_id' => $event['data']['order_id'] ?? 'unknown'
                ]);

                $msg->nack(requeue: true);
            }
        };

        $this->channel->basic_consume(
            queue: 'payment_service_queue',
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

    private function processPayment(array $orderData): void
    {
        sleep(1);

        $success = rand(1, 100) > 5;

        if (!$success) {
            throw new \Exception('Payment processing failed');
        }

        Log::info('Payment processed successfully', [
            'order_id' => $orderData['order_id'],
            'amount' => $orderData['total_price']
        ]);
    }

    public function close(): void
    {
        $this->channel->close();
        $this->connection->close();
    }
}
