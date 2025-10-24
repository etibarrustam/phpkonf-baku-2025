<?php

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/' || $path === '/health') {
    echo json_encode(['service' => 'payment-service', 'status' => 'healthy']);
    exit;
}

if ($path === '/api/payments' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    sleep(1);

    $success = rand(1, 100) > 5;

    if ($success) {
        echo json_encode([
            'success' => true,
            'status' => 'paid',
            'transaction_id' => 'txn_' . uniqid(),
            'order_id' => $data['order_id'],
            'amount' => $data['amount']
        ]);
    } else {
        http_response_code(402);
        echo json_encode([
            'success' => false,
            'status' => 'failed',
            'order_id' => $data['order_id']
        ]);
    }
    exit;
}

if (preg_match('/^\/api\/payments\/(.+)\/refund$/', $path, $matches) && $method === 'POST') {
    echo json_encode([
        'success' => true,
        'status' => 'refunded',
        'order_id' => $matches[1]
    ]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
