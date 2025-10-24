<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\EventPublisher;

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$pdo = new PDO(
    'mysql:host=mysql-order;dbname=order_db',
    'root',
    'root',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

if ($path === '/' || $path === '/health') {
    echo json_encode(['service' => 'order-service', 'status' => 'healthy']);
    exit;
}

if ($path === '/api/orders' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $orderId = 'order_' . uniqid();
    $totalPrice = $data['total_price'] ?? 25.00;

    $stmt = $pdo->prepare("
        INSERT INTO orders (id, customer_id, product_id, quantity, total_price, delivery_address, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    $stmt->execute([
        $orderId,
        $data['customer_id'] ?? 'customer_1',
        $data['product_id'] ?? 'product_1',
        $data['quantity'] ?? 1,
        $totalPrice,
        $data['delivery_address'] ?? '123 Main St'
    ]);

    $publisher = new EventPublisher();
    $publisher->publish('events', 'order.created', [
        'event_id' => uniqid('evt_'),
        'event_type' => 'order.created',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'order_id' => $orderId,
            'customer_id' => $data['customer_id'] ?? 'customer_1',
            'product_id' => $data['product_id'] ?? 'product_1',
            'quantity' => $data['quantity'] ?? 1,
            'total_price' => $totalPrice,
            'delivery_address' => $data['delivery_address'] ?? '123 Main St'
        ]
    ]);

    echo json_encode([
        'success' => true,
        'data' => [
            'order_id' => $orderId,
            'status' => 'pending',
            'message' => 'Order created and event published'
        ]
    ]);
    exit;
}

if (preg_match('/^\/api\/orders\/(.+)$/', $path, $matches) && $method === 'GET') {
    $orderId = $matches[1];

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        echo json_encode([
            'success' => true,
            'data' => $order
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
    }
    exit;
}

if (preg_match('/^\/api\/orders\/(.+)\/update-status$/', $path, $matches) && $method === 'PUT') {
    $orderId = $matches[1];
    $data = json_decode(file_get_contents('php://input'), true);

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$data['status'], $orderId]);

    echo json_encode([
        'success' => true,
        'message' => 'Order status updated'
    ]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
