<?php

header('Content-Type: application/json');

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
    echo json_encode(['app' => 'PlovExpress Monolit', 'status' => 'running']);
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

    echo json_encode([
        'success' => true,
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
        echo json_encode(['success' => true, 'data' => $order]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
    }
    exit;
}

if ($path === '/api/kitchen/queue' && $method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM orders WHERE status = 'preparing' ORDER BY created_at");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $orders]);
    exit;
}

if ($path === '/api/delivery/active' && $method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM orders WHERE status = 'delivering' ORDER BY created_at");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $orders]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
