<?php

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/' || $path === '/health') {
    echo json_encode(['service' => 'delivery-service', 'status' => 'healthy']);
    exit;
}

if ($path === '/api/delivery/assign' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    echo json_encode([
        'success' => true,
        'message' => 'Delivery assigned',
        'data' => [
            'order_id' => $data['order_id'],
            'status' => 'delivering',
            'courier' => 'Courier-' . rand(1, 10)
        ]
    ]);
    exit;
}

if ($path === '/api/delivery/active' && $method === 'GET') {
    echo json_encode([
        'success' => true,
        'data' => []
    ]);
    exit;
}

if (preg_match('/^\/api\/delivery\/orders\/(.+)\/delivered$/', $path, $matches) && $method === 'POST') {
    echo json_encode([
        'success' => true,
        'message' => 'Order delivered',
        'data' => [
            'order_id' => $matches[1],
            'status' => 'delivered'
        ]
    ]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
