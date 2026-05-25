<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Миграция</title></head>
<body style="font-family:monospace;padding:30px;">
<?php
require_once __DIR__ . '/includes/db.php';

$pdo = getDB();

// Wishlist table
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
echo '<p style="color:green;">✓ Таблица wishlist создана / уже существует</p>';

// Product images table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS product_images (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT UNSIGNED NOT NULL,
        image      VARCHAR(300) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo '<p style="color:green;">✓ Таблица product_images создана / уже существует</p>';

// Seed images for existing products if table is empty
$count = $pdo->query("SELECT COUNT(*) FROM product_images")->fetchColumn();
if ($count == 0) {
    $imageSets = [
        'Клавиатуры' => [
            ['/public/clava.png',      '/public/clava 2.png',      '/public/clava 3.png'],
            ['/public/clava 2.png',    '/public/clava.png',        '/public/clava 3.png'],
            ['/public/clava 3.png',    '/public/clava 2.png',      '/public/clava.png'],
            ['/public/clava.png',      '/public/clava 3.png',      '/public/clava 2.png'],
            ['/public/clava 2.png',    '/public/clava 3.png',      '/public/clava.png'],
        ],
        'Мыши' => [
            ['/public/mouse.png',      '/public/mouse 2.png',      '/public/mouse 3.png'],
            ['/public/mouse 2.png',    '/public/mouse 3.png',      '/public/mouse 4.png'],
            ['/public/mouse 3.png',    '/public/mouse 4.png',      '/public/mouse 5.png'],
            ['/public/mouse 4.png',    '/public/mouse 5.png',      '/public/mouse.png'],
        ],
        'Наушники' => [
            ['/public/headphones.png',   '/public/headphones 2.png', '/public/headphones 3.png'],
            ['/public/headphones 2.png', '/public/headphones 3.png', '/public/headphones 4.png'],
            ['/public/headphones 3.png', '/public/headphones 4.png', '/public/headphones 5.png'],
        ],
        'Микрофоны' => [
            ['/public/mouse.png',      '/public/mouse 2.png',      '/public/mouse 3.png'],
            ['/public/mouse 4.png',    '/public/mouse 5.png',      '/public/mouse.png'],
        ],
        'Коврики' => [
            ['/public/mouse 2.png',    '/public/mouse 3.png',      '/public/mouse.png'],
            ['/public/mouse 4.png',    '/public/mouse 5.png',      '/public/mouse 2.png'],
        ],
        'Веб-камеры' => [
            ['/public/monitor.png',    '/public/monitor 2.png',    '/public/monitor 3.png'],
            ['/public/monitor 2.png',  '/public/monitor 3.png',    '/public/monitor 4.png'],
        ],
        'Кастом' => [
            ['/public/clava.png',      '/public/clava 2.png',      '/public/clava 3.png'],
            ['/public/clava 2.png',    '/public/clava 3.png',      '/public/clava.png'],
        ],
        'Прочее' => [
            ['/public/monitor 4.png',  '/public/monitor 5.png',    '/public/monitor.png'],
            ['/public/monitor 5.png',  '/public/monitor.png',      '/public/monitor 2.png'],
        ],
    ];

    $stmt  = $pdo->prepare("INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)");
    $prods = $pdo->query("SELECT p.id, c.name AS cat FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC")->fetchAll();

    $catIndex = [];
    foreach ($prods as $prod) {
        $cat  = $prod['cat'];
        $sets = $imageSets[$cat] ?? [['/public/clava.png', '/public/clava.png', '/public/clava.png']];
        $idx  = $catIndex[$cat] ?? 0;
        $imgs = $sets[$idx % count($sets)];
        $catIndex[$cat] = $idx + 1;
        foreach ($imgs as $order => $img) {
            $stmt->execute([$prod['id'], $img, $order]);
        }
    }
    echo '<p style="color:green;">✓ Изображения добавлены для ' . count($prods) . ' товаров</p>';
} else {
    echo '<p style="color:#888;">✓ Изображения уже существуют (' . $count . ' записей)</p>';
}

echo '<br><a href="/catalog.html" style="padding:10px 20px;background:#000;color:#fff;text-decoration:none;border-radius:6px;margin-right:8px;">Каталог</a>';
echo '<a href="/profile.html" style="padding:10px 20px;background:#444;color:#fff;text-decoration:none;border-radius:6px;">Профиль</a>';
?>
</body></html>
