<?php

namespace App;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class EventPublisher
{
    private AMQPStreamConnection $connection;
    private $channel;

    public function __construct(
        private string $host = 'rabbitmq',
        private int $port = 5672,
        private string $user = 'guest',
        private string $password = 'guest'
    ) {
        $this->connect();
    }

    private function connect(): void
    {
        $maxRetries = 30;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                $this->connection = new AMQPStreamConnection(
                    $this->host,
                    $this->port,
                    $this->user,
                    $this->password
                );
                $this->channel = $this->connection->channel();
                return;
            } catch (\Exception $e) {
                $retryCount++;
                if ($retryCount >= $maxRetries) {
                    throw new \RuntimeException("Failed to connect to RabbitMQ after {$maxRetries} attempts");
                }
                sleep(2);
            }
        }
    }

    public function publish(string $exchange, string $routingKey, array $data): void
    {
        $this->channel->exchange_declare($exchange, 'topic', false, true, false);

        $message = new AMQPMessage(
            json_encode($data),
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );

        $this->channel->basic_publish($message, $exchange, $routingKey);
    }

    public function __destruct()
    {
        if (isset($this->channel)) {
            $this->channel->close();
        }
        if (isset($this->connection)) {
            $this->connection->close();
        }
    }
}
