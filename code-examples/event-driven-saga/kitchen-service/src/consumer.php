<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\EventConsumer;
use App\EventPublisher;

$pdo = new PDO(
    'mysql:host=mysql-kitchen;dbname=kitchen_db',
    'root',
    'root',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$consumer = new EventConsumer();
$publisher = new EventPublisher();

echo "Kitchen Service: Waiting for payment.completed events...\n";

$consumer->consume('kitchen.queue', 'events', ['payment.completed'], function ($event) use ($pdo, $publisher) {
    echo "Preparing order: {$event['data']['order_id']}\n";

    $orderId = $event['data']['order_id'];
    $productId = $event['data']['product_id'];
    $quantity = $event['data']['quantity'];

    $stmt = $pdo->prepare("
        INSERT INTO kitchen_orders (order_id, product_id, quantity, status, created_at)
        VALUES (?, ?, ?, 'preparing', NOW())
    ");
    $stmt->execute([$orderId, $productId, $quantity]);

    sleep(2);

    $stmt = $pdo->prepare("UPDATE kitchen_orders SET status = 'ready' WHERE order_id = ?");
    $stmt->execute([$orderId]);

    echo "Order ready: {$orderId}\n";

    $publisher->publish('events', 'kitchen.ready', [
        'event_id' => uniqid('evt_'),
        'event_type' => 'kitchen.ready',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'status' => 'ready'
        ]
    ]);
});
