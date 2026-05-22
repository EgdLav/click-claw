<?php
/**
 * Cart API: get, add, update, remove, clear
 * Cart is stored in $_SESSION['cart'] as [product_id => quantity]
 */
ob_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

$action = getGetField('action') ?: getPostField('action');
if (empty($action)) {
    $action = 'get';
}

switch ($action) {
    case 'get':
        handleGet();
        break;
    case 'add':
        requireAuth();
        handleAdd();
        break;
    case 'update':
        requireAuth();
        handleUpdate();
        break;
    case 'remove':
        requireAuth();
        handleRemove();
        break;
    case 'clear':
        requireAuth();
        handleClear();
        break;
    case 'count':
        handleCount();
        break;
    default:
        jsonResponse(false, null, 'Неизвестное действие', 400);
}

function getCartItems(): array {
    if (empty($_SESSION['cart'])) {
        return [];
    }

    $pdo        = getDB();
    $ids        = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt       = $pdo->prepare("SELECT * FROM products WHERE id IN ({$placeholders})");
    $stmt->execute($ids);
    $products   = $stmt->fetchAll();

    $items = [];
    foreach ($products as $product) {
        $qty     = (int)($_SESSION['cart'][$product['id']] ?? 0);
        $items[] = [
            'product_id' => (int)$product['id'],
            'name'       => $product['name'],
            'brand'      => $product['brand'],
            'price'      => (float)$product['price'],
            'image'      => $product['image'],
            'stock'      => (int)$product['stock'],
            'quantity'   => $qty,
            'subtotal'   => round((float)$product['price'] * $qty, 2),
        ];
    }

    return $items;
}

function cartTotal(array $items): float {
    return array_sum(array_column($items, 'subtotal'));
}

function handleGet(): void {
    $items = getCartItems();
    jsonResponse(true, [
        'items' => $items,
        'total' => cartTotal($items),
        'count' => array_sum(array_column($items, 'quantity')),
    ]);
}

function handleAdd(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $productId = (int)($_POST['product_id'] ?? 0);
    $qty       = max(1, (int)($_POST['quantity'] ?? 1));

    if (!$productId) {
        jsonResponse(false, null, 'Не указан ID товара', 400);
    }

    // Verify product exists
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT id, stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        jsonResponse(false, null, 'Товар не найден', 404);
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $current = (int)($_SESSION['cart'][$productId] ?? 0);
    $newQty  = min($current + $qty, (int)$product['stock']);
    $_SESSION['cart'][$productId] = $newQty;

    $items = getCartItems();
    jsonResponse(true, [
        'items' => $items,
        'total' => cartTotal($items),
        'count' => array_sum(array_column($items, 'quantity')),
    ]);
}

function handleUpdate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $productId = (int)($_POST['product_id'] ?? 0);
    $qty       = (int)($_POST['quantity'] ?? 0);

    if (!$productId) {
        jsonResponse(false, null, 'Не указан ID товара', 400);
    }

    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $qty;
    }

    $items = getCartItems();
    jsonResponse(true, [
        'items' => $items,
        'total' => cartTotal($items),
        'count' => array_sum(array_column($items, 'quantity')),
    ]);
}

function handleRemove(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $productId = (int)($_POST['product_id'] ?? 0);
    if (!$productId) {
        jsonResponse(false, null, 'Не указан ID товара', 400);
    }

    unset($_SESSION['cart'][$productId]);

    $items = getCartItems();
    jsonResponse(true, [
        'items' => $items,
        'total' => cartTotal($items),
        'count' => array_sum(array_column($items, 'quantity')),
    ]);
}

function handleClear(): void {
    $_SESSION['cart'] = [];
    jsonResponse(true, ['items' => [], 'total' => 0, 'count' => 0]);
}

function handleCount(): void {
    $count = 0;
    if (!empty($_SESSION['cart'])) {
        $count = array_sum($_SESSION['cart']);
    }
    jsonResponse(true, ['count' => $count]);
}
