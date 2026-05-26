<?php
/**
 * Seeder for КЛИК-КЛАВ — MySQL
 * Open in browser: http://localhost/seed.php
 * Clears products & categories, re-inserts fresh data.
 * Does NOT touch existing users or orders.
 */

require_once __DIR__ . '/includes/db.php';

if (!isset($_GET['run'])) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Seeder</title></head><body>';
    echo '<h2>КЛИК-КЛАВ — Seeder</h2>';
    echo '<p>Удалит все товары и категории, заполнит базу тестовыми данными.</p>';
    echo '<a href="?run=1" style="padding:10px 20px;background:#000;color:#fff;text-decoration:none;border-radius:6px;">Запустить</a>';
    echo '</body></html>';
    exit;
}

$pdo = getDB();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Seeder</title>';
echo '<style>body{font-family:monospace;padding:30px;background:#f5f5f5;}.box{background:#fff;padding:24px;border-radius:8px;max-width:620px;box-shadow:0 2px 8px rgba(0,0,0,.1);}</style>';
echo '</head><body><div class="box"><h2>КЛИК-КЛАВ — Seeder</h2><pre>';

// ── Schema ────────────────────────────────────────────────────────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name       VARCHAR(120) NOT NULL,
        email      VARCHAR(180) NOT NULL UNIQUE,
        phone      VARCHAR(30),
        password   VARCHAR(255) NOT NULL,
        role       ENUM('user','admin') NOT NULL DEFAULT 'user',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS categories (
        id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS products (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name        VARCHAR(200) NOT NULL,
        brand       VARCHAR(100),
        description TEXT,
        price       DECIMAL(10,2) NOT NULL,
        image       VARCHAR(300),
        category_id INT UNSIGNED,
        stock       INT NOT NULL DEFAULT 0,
        badge       VARCHAR(50),
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS orders (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id    INT UNSIGNED,
        name       VARCHAR(120) NOT NULL,
        phone      VARCHAR(30)  NOT NULL,
        email      VARCHAR(180),
        address    VARCHAR(300),
        total      DECIMAL(10,2) NOT NULL,
        status     ENUM('new','processing','completed','cancelled') NOT NULL DEFAULT 'new',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$pdo->exec("
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

$pdo->exec("
    CREATE TABLE IF NOT EXISTS order_items (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id   INT UNSIGNED,
        product_id INT UNSIGNED,
        name       VARCHAR(200) NOT NULL,
        price      DECIMAL(10,2) NOT NULL,
        quantity   INT NOT NULL,
        FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

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

$pdo->exec("
    CREATE TABLE IF NOT EXISTS product_images (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT UNSIGNED NOT NULL,
        image      VARCHAR(300) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

echo "✓ Таблицы созданы / проверены\n";

// ── Wipe products & categories ────────────────────────────────────────────────
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("TRUNCATE TABLE order_items");
$pdo->exec("TRUNCATE TABLE product_images");
$pdo->exec("TRUNCATE TABLE products");
$pdo->exec("TRUNCATE TABLE categories");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
echo "✓ Старые товары и категории удалены\n";

// ── Categories ────────────────────────────────────────────────────────────────
$categories = [
    'Клавиатуры',
    'Мыши',
    'Наушники',
    'Микрофоны',
    'Коврики',
    'Веб-камеры',
    'Кастом',
    'Прочее',
];

$stmtCat = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
foreach ($categories as $cat) {
    $stmtCat->execute([$cat]);
}
echo "✓ Категорий добавлено: " . count($categories) . "\n";

// Fetch IDs by name so we don't hardcode them
$catMap = $pdo->query("SELECT name, id FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);
// $catMap['Клавиатуры'] => id, etc.

// ── Admin user ────────────────────────────────────────────────────────────────
$adminExists = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
if (!$adminExists) {
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'admin')")
        ->execute(['Администратор', 'admin@gmail.com', '+7 (999) 000-00-00', $hash]);
    echo "✓ Администратор создан: admin@klik-klav.ru / admin123\n";
} else {
    echo "✓ Администратор уже существует\n";
}

// ── Products ──────────────────────────────────────────────────────────────────
$products = [
    // Клавиатуры
    ['Alto Keys K98M',              'Alto Keys',   'Беспроводная механическая клавиатура с прокладкой UniCushion. Поглощает вибрации, усиливая насыщенный и приятный звук каждого нажатия. Bluetooth 5.0 и USB-C.',                                          10999, '/public/clava.png',      'Клавиатуры', 35, 'Новинка'],
    ['Keychron K2 Pro',             'Keychron',    'Компактная механическая клавиатура 75% с горячей заменой свитчей и RGB-подсветкой. Совместима с Windows и macOS.',                                                                                         8990,  '/public/clava.png',      'Клавиатуры', 18, null],
    ['ASUS ROG Strix Flare II',     'ASUS',        'Игровая механическая клавиатура с RGB-подсветкой, съёмной подставкой для запястий и свитчами ROG NX Red.',                                                                                                12990, '/public/clava.png',      'Клавиатуры',  8, null],
    ['Razer BlackWidow V4 Pro',     'Razer',       'Флагманская игровая клавиатура с механическими свитчами Razer Yellow, мультифункциональным колесом и RGB Chroma.',                                                                                         18990, '/public/clava.png',      'Клавиатуры', 12, 'Хит'],
    ['Logitech G915 TKL',           'Logitech',    'Тонкая беспроводная механическая клавиатура без цифрового блока. Ультратонкие свитчи GL, до 40 часов работы.',                                                                                            16490, '/public/clava.png',      'Клавиатуры', 14, null],

    // Мыши
    ['Logitech MX Master 3S',       'Logitech',    'Эргономичная беспроводная мышь с тихими кнопками и точным сенсором 8000 DPI. Идеальна для работы и творчества.',                                                                                          10990, '/public/mouse.png',      'Мыши',       20, 'Новинка'],
    ['Logitech G Pro X Superlight 2','Logitech',   'Ультралёгкая игровая мышь весом 60 г. Сенсор HERO 2 с точностью до 32000 DPI. Беспроводная технология LIGHTSPEED.',                                                                                       14990, '/public/mouse.png',      'Мыши',       25, null],
    ['Razer DeathAdder V3 Pro',     'Razer',       'Эргономичная беспроводная игровая мышь с сенсором Focus Pro 30K и весом всего 64 г.',                                                                                                                      12490, '/public/mouse.png',      'Мыши',       17, null],
    ['SteelSeries Aerox 5 Wireless','SteelSeries', 'Лёгкая игровая мышь с перфорированным корпусом, 9 программируемыми кнопками и зарядкой USB-C.',                                                                                                            9990, '/public/mouse.png',      'Мыши',       30, null],

    // Наушники
    ['Sony WH-1000XM5',             'Sony',        'Беспроводные наушники с лучшим в классе шумоподавлением. До 30 часов работы. Быстрая зарядка: 3 мин = 3 часа.',                                                                                           29990, '/public/headphones.png', 'Наушники',   15, 'Хит'],
    ['SteelSeries Arctis Nova Pro', 'SteelSeries', 'Игровые наушники с активным шумоподавлением, сменными аккумуляторами и Hi-Res аудио.',                                                                                                                     24990, '/public/headphones.png', 'Наушники',   10, null],
    ['HyperX Cloud Alpha Wireless', 'HyperX',      'Игровые беспроводные наушники с временем работы до 300 часов. Двухкамерные динамики для чистого звука.',                                                                                                   14990, '/public/headphones.png', 'Наушники',   22, null],

    // Микрофоны
    ['Blue Yeti X',                 'Blue',        'Профессиональный USB-микрофон с четырьмя режимами записи и светодиодным индикатором уровня. Идеален для стриминга.',                                                                                       15990, '/public/mouse.png',      'Микрофоны',  12, 'Хит'],
    ['HyperX QuadCast S',           'HyperX',      'USB-микрофон с RGB-подсветкой, встроенным антивибрационным креплением и четырьмя полярными диаграммами.',                                                                                                  11990, '/public/mouse.png',      'Микрофоны',   9, null],

    // Коврики
    ['Razer Gigantus V2 XXL',       'Razer',       'Игровой коврик XXL (940×410 мм) с оптимизированной поверхностью для максимальной точности и нескользящей основой.',                                                                                         3990, '/public/mouse.png',      'Коврики',    40, null],
    ['SteelSeries QcK Heavy XXL',   'SteelSeries', 'Толстый тканевый коврик XXL с микротекстурированной поверхностью. Толщина 6 мм для максимального комфорта.',                                                                                                4490, '/public/mouse.png',      'Коврики',    28, null],

    // Веб-камеры
    ['Logitech C920 HD Pro',        'Logitech',    'Веб-камера Full HD 1080p с автофокусом и стереомикрофонами. Совместима с Zoom, Teams и другими платформами.',                                                                                               6990, '/public/monitor.png',    'Веб-камеры', 22, null],
    ['Razer Kiyo Pro',              'Razer',       'Стриминговая веб-камера с адаптивным световым сенсором и разрешением 1080p/60fps.',                                                                                                                         9990, '/public/monitor.png',    'Веб-камеры', 11, 'Новинка'],

    // Кастом
    ['Набор свитчей Gateron Yellow (110 шт.)', 'Gateron', 'Линейные свитчи Gateron Yellow с лёгким актуационным усилием 35 г. Подходят для большинства механических клавиатур.',                                                                               1990, '/public/clava.png',      'Кастом',     50, null],
    ['Кейкапы PBT Double-Shot (ANSI)',         'Akko',    'Набор кейкапов из PBT-пластика с двойным литьём. Профиль Cherry, совместимы с большинством механических клавиатур.',                                                                                 3490, '/public/clava.png',      'Кастом',     33, null],

    // Прочее
    ['Подставка для ноутбука Baseus', 'Baseus',    'Алюминиевая подставка для ноутбука с регулируемым углом наклона. Совместима с ноутбуками до 17 дюймов.',                                                                                                    2490, '/public/mouse.png',      'Прочее',     45, null],
    ['USB-хаб Ugreen 7-в-1',          'Ugreen',    'Компактный USB-C хаб с 3×USB-A, HDMI 4K, SD/microSD и PD 100W. Идеален для MacBook и ноутбуков.',                                                                                                         3990, '/public/mouse.png',      'Прочее',     38, null],
];

$stmtProd = $pdo->prepare("
    INSERT INTO products (name, brand, description, price, category_id, stock, badge)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

foreach ($products as [$name, $brand, $desc, $price, $image, $catName, $stock, $badge]) {
    $catId = $catMap[$catName] ?? null;
    $stmtProd->execute([$name, $brand, $desc, $price, $catId, $stock, $badge]);
}

echo "✓ Товаров добавлено: " . count($products) . "\n";

// ── Product images (multiple per product) ─────────────────────────────────────
// Map: category name => array of image sets (one set per product, cycling)
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

$stmtImg  = $pdo->prepare("INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)");
$allProds = $pdo->query("SELECT p.id, c.name AS cat FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC")->fetchAll();

// Track index per category to cycle through sets
$catIndex = [];
foreach ($allProds as $prod) {
    $cat  = $prod['cat'];
    $sets = $imageSets[$cat] ?? [['/public/clava.png', '/public/clava.png', '/public/clava.png']];
    $idx  = $catIndex[$cat] ?? 0;
    $imgs = $sets[$idx % count($sets)];
    $catIndex[$cat] = $idx + 1;

    foreach ($imgs as $order => $img) {
        $stmtImg->execute([$prod['id'], $img, $order]);
    }
}
echo "✓ Изображения товаров добавлены (3 на каждый)\n";

// ── Wishlist table ────────────────────────────────────────────────────────────
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

// Seed wishlist for the first non-admin user (first 4 products)
$testUser = $pdo->query("SELECT id FROM users WHERE role='user' LIMIT 1")->fetch();
if ($testUser) {
    $pdo->exec("DELETE FROM wishlist WHERE user_id = {$testUser['id']}");
    $firstProducts = $pdo->query("SELECT id FROM products LIMIT 4")->fetchAll();
    $stmtWish = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
    foreach ($firstProducts as $prod) {
        $stmtWish->execute([$testUser['id'], $prod['id']]);
    }
    echo "✓ Вишлист заполнен для пользователя ID {$testUser['id']} (4 товара)\n";
} else {
    echo "✓ Вишлист: нет обычных пользователей, пропущено\n";
}

// ── Summary ───────────────────────────────────────────────────────────────────
echo "\n=== Готово ===\n";
echo "Категорий:     " . $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() . "\n";
echo "Товаров:       " . $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() . "\n";
echo "Пользователей: " . $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() . "\n";
echo "\nВход в админку: admin@klik-klav.ru / admin123\n";

echo '</pre>';
echo '<br><a href="/catalog.html" style="padding:10px 20px;background:#000;color:#fff;text-decoration:none;border-radius:6px;margin-right:8px;">Открыть каталог</a>';
echo '<a href="/admin/admin.html" style="padding:10px 20px;background:#444;color:#fff;text-decoration:none;border-radius:6px;">Открыть админку</a>';
echo '</div></body></html>';
