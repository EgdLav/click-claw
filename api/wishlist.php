<?php
/**
 * Wishlist API: list, add, remove, check
 */
ob_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

// Create table if not exists
$pdo = getDB();
$pdo->exec("
    CREATE TABLE IF NOT EXISTS wishlist (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id    INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_wish (user_id, product_id),
        FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$action = getGetField('action') ?: getPostField('action');
if (empty($action)) $action = 'list';

switch ($action) {
    case 'list':
        requireAuth();
        handleList();
        break;
    case 'add':
        requireAuth();
        handleAdd();
        break;
    case 'remove':
        requireAuth();
        handleRemove();
        break;
    case 'check':
        // No requireAuth — guests just get false
        handleCheck();
        break;
    default:
        jsonResponse(false, null, 'Неизвестное действие', 400);
}

function handleList(): void {
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name, w.id AS wish_id
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([currentUserId()]);
    jsonResponse(true, $stmt->fetchAll());
}

function handleAdd(): void {
    $productId = (int)($_POST['product_id'] ?? 0);
    if (!$productId) jsonResponse(false, null, 'Не указан ID товара', 400);

    $pdo  = getDB();
    // Check product exists
    $check = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $check->execute([$productId]);
    if (!$check->fetch()) jsonResponse(false, null, 'Товар не найден', 404);

    // INSERT IGNORE — safe if already exists
    $stmt = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([currentUserId(), $productId]);

    jsonResponse(true, ['product_id' => $productId]);
}

function handleRemove(): void {
    $productId = (int)($_POST['product_id'] ?? 0);
    if (!$productId) jsonResponse(false, null, 'Не указан ID товара', 400);

    $pdo  = getDB();
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([currentUserId(), $productId]);

    jsonResponse(true, ['product_id' => $productId]);
}

function handleCheck(): void {
    $productId = (int)getGetField('product_id');
    if (!$productId) jsonResponse(false, null, 'Не указан ID товара', 400);

    // Not logged in — just return false
    if (!isLoggedIn()) {
        jsonResponse(true, ['in_wishlist' => false]);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([currentUserId(), $productId]);

    jsonResponse(true, ['in_wishlist' => (bool)$stmt->fetch()]);
}
