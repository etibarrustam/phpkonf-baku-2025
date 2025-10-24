<?php

namespace App\Producers;

use App\Events\OrderCreatedEvent;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OrderEventProducer
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
    }

    public function publishOrderCreated(OrderCreatedEvent $event): void
    {
        $message = new AMQPMessage(
            body: json_encode($event->toArray()),
            properties: [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'timestamp' => time(),
            ]
        );

        $this->channel->basic_publish(
            msg: $message,
            exchange: 'plov_express_events',
            routing_key: 'order.created'
        );

        logger()->info('Published order.created event', [
            'order_id' => $event->orderId
        ]);
    }

    public function close(): void
    {
        $this->channel->close();
        $this->connection->close();
    }

    public function __destruct()
    {
        $this->close();
    }
}
