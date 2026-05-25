<?php
/**
 * Orders API: create, list (user), all (admin), update_status
 */
ob_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

$action = getGetField('action') ?: getPostField('action');
if (empty($action)) {
    $action = 'list';
}

switch ($action) {
    case 'create':
        requireAuth();
        handleCreate();
        break;
    case 'list':
        requireAuth();
        handleList();
        break;
    case 'all':
        requireAdmin();
        handleAll();
        break;
    case 'update_status':
        requireAdmin();
        handleUpdateStatus();
        break;
    case 'get':
        requireAuth();
        handleGet();
        break;
    default:
        jsonResponse(false, null, 'Неизвестное действие', 400);
}

function handleCreate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $name    = sanitize($_POST['name'] ?? '');
    $phone   = sanitize($_POST['phone'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    if (empty($name) || empty($phone)) {
        jsonResponse(false, null, 'Заполните обязательные поля: имя и телефон', 400);
    }

    $pdo    = getDB();
    $userId = currentUserId();

    // Read cart from DB
    $cartStmt = $pdo->prepare("
        SELECT c.product_id, c.quantity, p.name, p.price, p.stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $cartStmt->execute([$userId]);
    $cartRows = $cartStmt->fetchAll();

    if (empty($cartRows)) {
        jsonResponse(false, null, 'Корзина пуста', 400);
    }

    // Calculate total
    $total      = 0;
    $orderItems = [];
    foreach ($cartRows as $row) {
        $subtotal    = (float)$row['price'] * (int)$row['quantity'];
        $total      += $subtotal;
        $orderItems[] = [
            'product_id' => $row['product_id'],
            'name'       => $row['name'],
            'price'      => (float)$row['price'],
            'quantity'   => (int)$row['quantity'],
        ];
    }

    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, name, phone, email, address, total)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $name, $phone, $email, $address, $total]);
    $orderId = (int)$pdo->lastInsertId();

    // Create order items
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, name, price, quantity)
        VALUES (?, ?, ?, ?, ?)
    ");
    foreach ($orderItems as $item) {
        $itemStmt->execute([$orderId, $item['product_id'], $item['name'], $item['price'], $item['quantity']]);
    }

    // Clear cart from DB
    $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]);

    jsonResponse(true, ['order_id' => $orderId, 'total' => $total]);
}

function handleList(): void {
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT o.*,
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS items_count
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([currentUserId()]);
    $orders = $stmt->fetchAll();

    jsonResponse(true, $orders);
}

function handleAll(): void {
    $pdo  = getDB();
    $stmt = $pdo->query("
        SELECT o.*,
               u.name AS user_name, u.email AS user_email,
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS items_count
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ");
    jsonResponse(true, $stmt->fetchAll());
}

function handleUpdateStatus(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $id     = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');

    $allowed = ['new', 'processing', 'completed', 'cancelled'];
    if (!$id || !in_array($status, $allowed, true)) {
        jsonResponse(false, null, 'Неверные параметры', 400);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    jsonResponse(true, ['id' => $id, 'status' => $status]);
}

function handleGet(): void {
    $id  = (int)getGetField('id');
    if (!$id) {
        jsonResponse(false, null, 'Не указан ID заказа', 400);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, currentUserId()]);
    $order = $stmt->fetch();

    if (!$order) {
        jsonResponse(false, null, 'Заказ не найден', 404);
    }

    $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemStmt->execute([$id]);
    $order['items'] = $itemStmt->fetchAll();

    jsonResponse(true, $order);
}
