<?php

namespace App;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class EventConsumer
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

    public function consume(string $queue, string $exchange, array $routingKeys, callable $callback): void
    {
        $this->channel->exchange_declare($exchange, 'topic', false, true, false);

        $this->channel->queue_declare(
            $queue,
            false,
            true,
            false,
            false,
            false,
            [
                'x-dead-letter-exchange' => ['S', 'dlx'],
                'x-message-ttl' => ['I', 86400000]
            ]
        );

        foreach ($routingKeys as $routingKey) {
            $this->channel->queue_bind($queue, $exchange, $routingKey);
        }

        $this->channel->exchange_declare('dlx', 'fanout', false, true, false);
        $dlqName = $queue . '.dlq';
        $this->channel->queue_declare($dlqName, false, true, false, false);
        $this->channel->queue_bind($dlqName, 'dlx');

        $messageCallback = function (AMQPMessage $msg) use ($callback) {
            $maxRetries = 3;
            $retryCount = (int) ($msg->get('application_headers')['x-retry-count'] ?? 0);

            try {
                $data = json_decode($msg->body, true);
                $callback($data);
                $msg->ack();
            } catch (\Exception $e) {
                if ($retryCount < $maxRetries) {
                    $headers = $msg->get('application_headers') ?? [];
                    $headers['x-retry-count'] = $retryCount + 1;

                    $newMsg = new AMQPMessage(
                        $msg->body,
                        [
                            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                            'application_headers' => $headers
                        ]
                    );

                    $msg->ack();
                    sleep(pow(2, $retryCount));
                    $this->channel->basic_publish($newMsg, '', $msg->getRoutingKey());
                } else {
                    $msg->nack(false, false);
                    error_log("Message moved to DLQ after {$maxRetries} retries: " . $e->getMessage());
                }
            }
        };

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($queue, '', false, false, false, false, $messageCallback);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
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
