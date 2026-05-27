<?php
// корзина: получение, добавление, обновление, удаление
ob_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

// создание таблицы если не существует
getDB()->exec("
    CREATE TABLE IF NOT EXISTS cart (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id    INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        quantity   INT NOT NULL DEFAULT 1,
        UNIQUE KEY unique_cart (user_id, product_id),
        FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$action = getGetField('action') ?: getPostField('action');
if (empty($action)) $action = 'get';

switch ($action) {
    case 'get':    handleGet();                    break;
    case 'add':    requireAuth(); handleAdd();     break;
    case 'update': requireAuth(); handleUpdate();  break;
    case 'remove': requireAuth(); handleRemove();  break;
    case 'clear':  requireAuth(); handleClear();   break;
    case 'count':  handleCount();                  break;
    default:
        jsonResponse(false, null, 'Неизвестное действие', 400);
}

function getCartItems(): array {
    if (!isLoggedIn()) return [];

    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT c.product_id, c.quantity,
               p.name, p.brand, p.price, p.stock,
               (SELECT pi.image FROM product_images pi
                WHERE pi.product_id = p.id
                ORDER BY pi.sort_order ASC LIMIT 1) AS image
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.id ASC
    ");
    $stmt->execute([currentUserId()]);
    $rows = $stmt->fetchAll();

    $items = [];
    foreach ($rows as $row) {
        $items[] = [
            'product_id' => (int)$row['product_id'],
            'name'       => $row['name'],
            'brand'      => $row['brand'],
            'price'      => (float)$row['price'],
            'image'      => $row['image'] ?? null,
            'stock'      => (int)$row['stock'],
            'quantity'   => (int)$row['quantity'],
            'subtotal'   => round((float)$row['price'] * (int)$row['quantity'], 2),
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

    if (!$productId) jsonResponse(false, null, 'Не указан ID товара', 400);

    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT id, stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) jsonResponse(false, null, 'Товар не найден', 404);

    $userId = currentUserId();

    $cur = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $cur->execute([$userId, $productId]);
    $existing = $cur->fetchColumn();

    $newQty = min(($existing ?: 0) + $qty, (int)$product['stock']);

    $pdo->prepare("
        INSERT INTO cart (user_id, product_id, quantity)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE quantity = ?
    ")->execute([$userId, $productId, $newQty, $newQty]);

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
    $qty       = (int)($_POST['quantity']   ?? 0);

    if (!$productId) jsonResponse(false, null, 'Не указан ID товара', 400);

    $pdo    = getDB();
    $userId = currentUserId();

    if ($qty <= 0) {
        $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?")->execute([$userId, $productId]);
    } else {
        $pdo->prepare("
            INSERT INTO cart (user_id, product_id, quantity)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = ?
        ")->execute([$userId, $productId, $qty, $qty]);
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
    if (!$productId) jsonResponse(false, null, 'Не указан ID товара', 400);

    $pdo = getDB();
    $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?")->execute([currentUserId(), $productId]);

    $items = getCartItems();
    jsonResponse(true, [
        'items' => $items,
        'total' => cartTotal($items),
        'count' => array_sum(array_column($items, 'quantity')),
    ]);
}

function handleClear(): void {
    getDB()->prepare("DELETE FROM cart WHERE user_id = ?")->execute([currentUserId()]);
    jsonResponse(true, ['items' => [], 'total' => 0, 'count' => 0]);
}

function handleCount(): void {
    if (!isLoggedIn()) {
        jsonResponse(true, ['count' => 0]);
    }
    $stmt = getDB()->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?");
    $stmt->execute([currentUserId()]);
    jsonResponse(true, ['count' => (int)$stmt->fetchColumn()]);
}
