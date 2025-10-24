<?php

require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/' || $path === '/health') {
    echo json_encode(['service' => 'kitchen-service', 'status' => 'healthy']);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
