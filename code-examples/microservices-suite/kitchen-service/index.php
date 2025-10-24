<?php

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/' || $path === '/health') {
    echo json_encode(['service' => 'kitchen-service', 'status' => 'healthy']);
    exit;
}

if ($path === '/api/kitchen/queue' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    echo json_encode([
        'success' => true,
        'message' => 'Order added to kitchen queue',
        'data' => [
            'order_id' => $data['order_id'],
            'status' => 'preparing'
        ]
    ]);
    exit;
}

if ($path === '/api/kitchen/queue' && $method === 'GET') {
    echo json_encode([
        'success' => true,
        'data' => []
    ]);
    exit;
}

if (preg_match('/^\/api\/kitchen\/orders\/(.+)\/ready$/', $path, $matches) && $method === 'POST') {
    file_get_contents('http://delivery-service/api/delivery/assign', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode(['order_id' => $matches[1]])
        ]
    ]));

    echo json_encode([
        'success' => true,
        'message' => 'Order ready and delivery assigned'
    ]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
