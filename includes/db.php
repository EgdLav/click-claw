<?php
/**
 * Database connection and schema initialization for КЛИК-КЛАВ
 */

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dbPath = __DIR__ . '/../database.db';
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        initSchema($pdo);
    }
    return $pdo;
}

function initSchema(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            name       TEXT    NOT NULL,
            email      TEXT    NOT NULL UNIQUE,
            phone      TEXT,
            password   TEXT    NOT NULL,
            role       TEXT    NOT NULL DEFAULT 'user',
            created_at TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id   INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            name        TEXT    NOT NULL,
            brand       TEXT,
            description TEXT,
            price       REAL    NOT NULL,
            image       TEXT,
            category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
            stock       INTEGER NOT NULL DEFAULT 0,
            badge       TEXT,
            created_at  TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id    INTEGER REFERENCES users(id) ON DELETE SET NULL,
            name       TEXT    NOT NULL,
            phone      TEXT    NOT NULL,
            email      TEXT,
            address    TEXT,
            total      REAL    NOT NULL,
            status     TEXT    NOT NULL DEFAULT 'new',
            created_at TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id   INTEGER REFERENCES orders(id) ON DELETE CASCADE,
            product_id INTEGER REFERENCES products(id) ON DELETE SET NULL,
            name       TEXT    NOT NULL,
            price      REAL    NOT NULL,
            quantity   INTEGER NOT NULL
        )
    ");

    seedData($pdo);
}

function seedData(PDO $pdo): void {
    // Seed admin user
    $adminExists = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
    if (!$adminExists) {
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'admin')")
            ->execute(['Администратор', 'admin@klik-klav.ru', '+7 (999) 000-00-00', $hash]);
    }

    // Seed categories
    $catCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if (!$catCount) {
        $categories = [
            'Клавиатуры', 'Мыши', 'Наушники', 'Микрофоны',
            'Коврики', 'Веб-камеры', 'Кастом', 'Прочее'
        ];
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        foreach ($categories as $cat) {
            $stmt->execute([$cat]);
        }
    }

    // Seed products
    $prodCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if (!$prodCount) {
        $products = [
            [
                'name' => 'Alto Keys K98M',
                'brand' => 'Alto Keys',
                'description' => 'Беспроводная механическая клавиатура с прокладкой UniCushion. Поглощает вибрации, усиливая насыщенный и приятный звук каждого нажатия клавиши.',
                'price' => 10999,
                'image' => '/public/clava.png',
                'category_id' => 1,
                'stock' => 35,
                'badge' => 'Новинка'
            ],
            [
                'name' => 'Logitech MX Master 3S',
                'brand' => 'Logitech',
                'description' => 'Эргономичная беспроводная мышь с тихими кнопками и точным сенсором 8000 DPI. Идеальна для работы и творчества.',
                'price' => 10990,
                'image' => '/public/mouse.png',
                'category_id' => 2,
                'stock' => 20,
                'badge' => 'Новинка'
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'brand' => 'Sony',
                'description' => 'Беспроводные наушники с лучшим в классе шумоподавлением. До 30 часов работы от аккумулятора.',
                'price' => 29990,
                'image' => '/public/headphones.png',
                'category_id' => 3,
                'stock' => 15,
                'badge' => 'Хит'
            ],
            [
                'name' => 'Logitech G Pro X Superlight 2',
                'brand' => 'Logitech',
                'description' => 'Ультралёгкая игровая мышь весом 60 г. Сенсор HERO 2 с точностью до 32000 DPI.',
                'price' => 14990,
                'image' => '/public/mouse.png',
                'category_id' => 2,
                'stock' => 25,
                'badge' => null
            ],
            [
                'name' => 'Keychron K2 Pro',
                'brand' => 'Keychron',
                'description' => 'Компактная механическая клавиатура 75% с горячей заменой свитчей и RGB-подсветкой.',
                'price' => 8990,
                'image' => '/public/clava.png',
                'category_id' => 1,
                'stock' => 18,
                'badge' => null
            ],
            [
                'name' => 'SteelSeries Arctis Nova Pro',
                'brand' => 'SteelSeries',
                'description' => 'Игровые наушники с активным шумоподавлением и сменными аккумуляторами.',
                'price' => 24990,
                'image' => '/public/headphones.png',
                'category_id' => 3,
                'stock' => 10,
                'badge' => null
            ],
            [
                'name' => 'Razer Gigantus V2 XXL',
                'brand' => 'Razer',
                'description' => 'Игровой коврик XXL размера с оптимизированной поверхностью для максимальной точности.',
                'price' => 3990,
                'image' => '/public/mouse.png',
                'category_id' => 5,
                'stock' => 40,
                'badge' => null
            ],
            [
                'name' => 'Blue Yeti X',
                'brand' => 'Blue',
                'description' => 'Профессиональный USB-микрофон с четырьмя режимами записи и светодиодным индикатором уровня.',
                'price' => 15990,
                'image' => '/public/mouse.png',
                'category_id' => 4,
                'stock' => 12,
                'badge' => 'Хит'
            ],
            [
                'name' => 'Logitech C920 HD Pro',
                'brand' => 'Logitech',
                'description' => 'Веб-камера Full HD 1080p с автофокусом и стереомикрофонами.',
                'price' => 6990,
                'image' => '/public/mouse.png',
                'category_id' => 6,
                'stock' => 22,
                'badge' => null
            ],
            [
                'name' => 'ASUS ROG Strix Flare II',
                'brand' => 'ASUS',
                'description' => 'Игровая механическая клавиатура с RGB-подсветкой и съёмной подставкой для запястий.',
                'price' => 12990,
                'image' => '/public/clava.png',
                'category_id' => 1,
                'stock' => 8,
                'badge' => null
            ],
        ];

        $stmt = $pdo->prepare("
            INSERT INTO products (name, brand, description, price, image, category_id, stock, badge)
            VALUES (:name, :brand, :description, :price, :image, :category_id, :stock, :badge)
        ");
        foreach ($products as $p) {
            $stmt->execute($p);
        }
    }
}
