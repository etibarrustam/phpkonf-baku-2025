<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\EventConsumer;
use App\EventPublisher;

$pdo = new PDO(
    'mysql:host=mysql-delivery;dbname=delivery_db',
    'root',
    'root',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$consumer = new EventConsumer();
$publisher = new EventPublisher();

echo "Delivery Service: Waiting for kitchen.ready events...\n";

$consumer->consume('delivery.queue', 'events', ['kitchen.ready'], function ($event) use ($pdo, $publisher) {
    echo "Assigning delivery for order: {$event['data']['order_id']}\n";

    $orderId = $event['data']['order_id'];
    $courierId = 'courier_' . rand(1, 10);

    $stmt = $pdo->prepare("
        INSERT INTO deliveries (order_id, courier_id, status, created_at)
        VALUES (?, ?, 'assigned', NOW())
    ");
    $stmt->execute([$orderId, $courierId]);

    sleep(2);

    $stmt = $pdo->prepare("UPDATE deliveries SET status = 'in_transit' WHERE order_id = ?");
    $stmt->execute([$orderId]);

    echo "Order in transit: {$orderId}\n";

    sleep(2);

    $stmt = $pdo->prepare("UPDATE deliveries SET status = 'delivered', delivered_at = NOW() WHERE order_id = ?");
    $stmt->execute([$orderId]);

    echo "Order delivered: {$orderId}\n";

    $publisher->publish('events', 'delivery.completed', [
        'event_id' => uniqid('evt_'),
        'event_type' => 'delivery.completed',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'order_id' => $orderId,
            'courier_id' => $courierId,
            'status' => 'delivered'
        ]
    ]);
});
