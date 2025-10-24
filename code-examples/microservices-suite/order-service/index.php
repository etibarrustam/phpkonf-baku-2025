<?php

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/' || $path === '/health') {
    echo json_encode(['service' => 'order-service', 'status' => 'healthy']);
    exit;
}

if ($path === '/api/orders' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $orderId = 'order_' . uniqid();

    $paymentResponse = file_get_contents('http://payment-service/api/payments', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode([
                'order_id' => $orderId,
                'amount' => $data['total_price'] ?? 25.00
            ])
        ]
    ]));

    $payment = json_decode($paymentResponse, true);

    if ($payment['status'] === 'paid') {
        file_get_contents('http://kitchen-service/api/kitchen/queue', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode([
                    'order_id' => $orderId,
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity']
                ])
            ]
        ]));
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'order_id' => $orderId,
            'status' => 'pending',
            'payment_status' => $payment['status']
        ]
    ]);
    exit;
}

if (preg_match('/^\/api\/orders\/(.+)$/', $path, $matches) && $method === 'GET') {
    echo json_encode([
        'success' => true,
        'data' => [
            'order_id' => $matches[1],
            'status' => 'preparing',
            'payment_status' => 'paid'
        ]
    ]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
