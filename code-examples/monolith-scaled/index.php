<?php

header('Content-Type: application/json');

$instanceId = getenv('INSTANCE_ID') ?: 'app-1';
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_DATABASE') ?: 'plov_express';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: 'secret';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/' || $path === '/index.php') {
    echo json_encode([
        'app' => 'PlovExpress Scalable Monolit',
        'instance' => $instanceId,
        'status' => 'running'
    ]);
    exit;
}

if ($path === '/health') {
    echo json_encode(['status' => 'healthy', 'instance' => $instanceId]);
    exit;
}

if ($path === '/api/orders' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $stmt = $pdo->prepare("
        INSERT INTO orders (customer_id, product_id, quantity, total_price, status, payment_status, delivery_address, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'pending', 'pending', ?, NOW(), NOW())
    ");

    $totalPrice = 12.00 * $data['quantity'];
    $stmt->execute([
        $data['customer_id'],
        $data['product_id'],
        $data['quantity'],
        $totalPrice,
        $data['delivery_address']
    ]);

    $orderId = $pdo->lastInsertId();

    error_log("Order created by instance: $instanceId");

    echo json_encode([
        'success' => true,
        'instance' => $instanceId,
        'data' => [
            'id' => $orderId,
            'status' => 'pending',
            'payment_status' => 'pending'
        ]
    ]);
    exit;
}

if (preg_match('/^\/api\/orders\/(\d+)$/', $path, $matches) && $method === 'GET') {
    $orderId = $matches[1];

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        echo json_encode(['success' => true, 'instance' => $instanceId, 'data' => $order]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
    }
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
