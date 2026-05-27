<?php
// товары: список, получение, создание, обновление, удаление
ob_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

$action = getGetField('action') ?: getPostField('action');
if (empty($action)) $action = 'list';

switch ($action) {
    case 'list':   handleList();   break;
    case 'get':    handleGet();    break;
    case 'create': requireAdmin(); handleCreate(); break;
    case 'update': requireAdmin(); handleUpdate(); break;
    case 'delete': requireAdmin(); handleDelete(); break;
    default:
        jsonResponse(false, null, 'Неизвестное действие', 400);
}

function handleList(): void {
    $pdo = getDB();

    $where  = [];
    $params = [];

    $categoryId = getGetField('category_id');
    if ($categoryId !== '') {
        $where[]  = 'p.category_id = ?';
        $params[] = (int)$categoryId;
    }

    $search = getGetField('search');
    if ($search !== '') {
        $where[]  = '(p.name LIKE ? OR p.brand LIKE ?)';
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    $brand = getGetField('brand');
    if ($brand !== '') {
        $where[]  = 'p.brand = ?';
        $params[] = $brand;
    }

    $priceMin = getGetField('price_min');
    if ($priceMin !== '') {
        $where[]  = 'p.price >= ?';
        $params[] = (float)$priceMin;
    }

    $priceMax = getGetField('price_max');
    if ($priceMax !== '') {
        $where[]  = 'p.price <= ?';
        $params[] = (float)$priceMax;
    }

    $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sortMap = [
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'name_asc'   => 'p.name ASC',
    ];
    $sort = getGetField('sort');
    $orderBy = isset($sortMap[$sort]) ? $sortMap[$sort] : 'p.id DESC';

    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name,
               (SELECT pi.image FROM product_images pi
                WHERE pi.product_id = p.id
                ORDER BY pi.sort_order ASC LIMIT 1) AS image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        {$whereStr}
        ORDER BY {$orderBy}
    ");
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    jsonResponse(true, $products);
}

function handleGet(): void {
    $id = (int)getGetField('id');
    if (!$id) jsonResponse(false, null, 'Не указан ID товара', 400);

    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) jsonResponse(false, null, 'Товар не найден', 404);

    // изображения
    $imgStmt = $pdo->prepare("SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
    $imgStmt->execute([$id]);
    $product['images'] = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($product['images']) && !empty($product['image'])) {
        $product['images'] = [$product['image']];
    }

    jsonResponse(true, $product);
}

function handleCreate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $name       = sanitize($_POST['name']        ?? '');
    $brand      = sanitize($_POST['brand']       ?? '');
    $desc       = sanitize($_POST['description'] ?? '');
    $price      = (float)($_POST['price']        ?? 0);
    $categoryId = (int)($_POST['category_id']    ?? 0);
    $stock      = (int)($_POST['stock']          ?? 0);
    $badge      = sanitize($_POST['badge']       ?? '');
    $imagesStr  = $_POST['images'] ?? '';

    if (empty($name) || !$price || !$categoryId) {
        jsonResponse(false, null, 'Заполните обязательные поля', 400);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare("
        INSERT INTO products (name, brand, description, price, category_id, stock, badge)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $brand, $desc, $price, $categoryId, $stock, $badge]);
    $productId = (int)$pdo->lastInsertId();

    // сохранение изображений
    if ($imagesStr) {
        $images = array_filter(array_map('trim', explode(',', $imagesStr)));
        $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)");
        foreach ($images as $i => $img) {
            $imgStmt->execute([$productId, $img, $i]);
        }
    }

    jsonResponse(true, ['id' => $productId]);
}

function handleUpdate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $id         = (int)($_POST['id']             ?? 0);
    $name       = sanitize($_POST['name']        ?? '');
    $brand      = sanitize($_POST['brand']       ?? '');
    $desc       = sanitize($_POST['description'] ?? '');
    $price      = (float)($_POST['price']        ?? 0);
    $categoryId = (int)($_POST['category_id']    ?? 0);
    $stock      = (int)($_POST['stock']          ?? 0);
    $badge      = sanitize($_POST['badge']       ?? '');
    $imagesStr  = $_POST['images'] ?? '';

    if (!$id || empty($name) || !$price || !$categoryId) {
        jsonResponse(false, null, 'Заполните обязательные поля', 400);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare("
        UPDATE products
        SET name = ?, brand = ?, description = ?, price = ?, category_id = ?, stock = ?, badge = ?
        WHERE id = ?
    ");
    $stmt->execute([$name, $brand, $desc, $price, $categoryId, $stock, $badge, $id]);

    // обновление изображений
    $pdo->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
    if ($imagesStr) {
        $images  = array_filter(array_map('trim', explode(',', $imagesStr)));
        $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)");
        foreach ($images as $i => $img) {
            $imgStmt->execute([$id, $img, $i]);
        }
    }

    jsonResponse(true, ['id' => $id]);
}

function handleDelete(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $id = (int)($_POST['id'] ?? 0);
    if (!$id) jsonResponse(false, null, 'Не указан ID товара', 400);

    $pdo  = getDB();
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    jsonResponse(true, ['deleted' => $id]);
}
