<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\EventConsumer;
use App\EventPublisher;

$pdo = new PDO(
    'mysql:host=mysql-payment;dbname=payment_db',
    'root',
    'root',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$consumer = new EventConsumer();
$publisher = new EventPublisher();

echo "Payment Service: Waiting for order.created events...\n";

$consumer->consume('payment.queue', 'events', ['order.created'], function ($event) use ($pdo, $publisher) {
    echo "Processing payment for order: {$event['data']['order_id']}\n";

    $orderId = $event['data']['order_id'];
    $amount = $event['data']['total_price'];

    sleep(1);

    $success = rand(1, 100) > 10;

    if ($success) {
        $transactionId = 'txn_' . uniqid();

        $stmt = $pdo->prepare("
            INSERT INTO payments (transaction_id, order_id, amount, status, created_at)
            VALUES (?, ?, ?, 'completed', NOW())
        ");
        $stmt->execute([$transactionId, $orderId, $amount]);

        echo "Payment successful: {$transactionId}\n";

        $publisher->publish('events', 'payment.completed', [
            'event_id' => uniqid('evt_'),
            'event_type' => 'payment.completed',
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => [
                'order_id' => $orderId,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'status' => 'completed',
                'product_id' => $event['data']['product_id'],
                'quantity' => $event['data']['quantity']
            ]
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO payments (order_id, amount, status, created_at)
            VALUES (?, ?, 'failed', NOW())
        ");
        $stmt->execute([$orderId, $amount]);

        echo "Payment failed for order: {$orderId}\n";

        $publisher->publish('events', 'payment.failed', [
            'event_id' => uniqid('evt_'),
            'event_type' => 'payment.failed',
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => [
                'order_id' => $orderId,
                'amount' => $amount,
                'reason' => 'Insufficient funds'
            ]
        ]);

        throw new \Exception("Payment failed for order {$orderId}");
    }
});
