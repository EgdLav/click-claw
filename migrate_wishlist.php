<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Миграция — Wishlist</title></head>
<body style="font-family:monospace;padding:30px;">
<?php
require_once __DIR__ . '/includes/db.php';

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

echo '<p style="color:green;font-size:18px;">✓ Таблица wishlist создана / уже существует</p>';
echo '<p><a href="/favorite.html">Открыть список желаний</a> · <a href="/profile.html">Профиль</a></p>';
?>
</body></html>
