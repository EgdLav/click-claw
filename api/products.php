<?php
/**
 * Products API: list, get, create, update, delete
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

$action = getGetField('action') ?: getPostField('action');
if (empty($action)) {
    $action = 'list';
}

switch ($action) {
    case 'list':
        handleList();
        break;
    case 'get':
        handleGet();
        break;
    case 'create':
        requireAdmin();
        handleCreate();
        break;
    case 'update':
        requireAdmin();
        handleUpdate();
        break;
    case 'delete':
        requireAdmin();
        handleDelete();
        break;
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

    $search = getGetField('search');
    if ($search !== '') {
        $where[]  = '(p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)';
        $like     = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sort = getGetField('sort');
    $orderBy = match ($sort) {
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'name_asc'   => 'p.name ASC',
        default      => 'p.id DESC',
    };

    $sql = "
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        {$whereClause}
        ORDER BY {$orderBy}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    jsonResponse(true, $products);
}

function handleGet(): void {
    $id = (int)getGetField('id');
    if (!$id) {
        jsonResponse(false, null, 'Не указан ID товара', 400);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        jsonResponse(false, null, 'Товар не найден', 404);
    }

    jsonResponse(true, $product);
}

function handleCreate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $name       = sanitize($_POST['name'] ?? '');
    $brand      = sanitize($_POST['brand'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price      = (float)($_POST['price'] ?? 0);
    $image      = sanitize($_POST['image'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $stock      = (int)($_POST['stock'] ?? 0);
    $badge      = sanitize($_POST['badge'] ?? '');

    if (empty($name) || $price <= 0 || !$categoryId) {
        jsonResponse(false, null, 'Заполните обязательные поля: название, цена, категория', 400);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare("
        INSERT INTO products (name, brand, description, price, image, category_id, stock, badge)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $brand, $description, $price, $image, $categoryId, $stock, $badge ?: null]);
    $id = (int)$pdo->lastInsertId();

    jsonResponse(true, ['id' => $id]);
}

function handleUpdate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $id         = (int)($_POST['id'] ?? 0);
    $name       = sanitize($_POST['name'] ?? '');
    $brand      = sanitize($_POST['brand'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price      = (float)($_POST['price'] ?? 0);
    $image      = sanitize($_POST['image'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $stock      = (int)($_POST['stock'] ?? 0);
    $badge      = sanitize($_POST['badge'] ?? '');

    if (!$id || empty($name) || $price <= 0 || !$categoryId) {
        jsonResponse(false, null, 'Заполните обязательные поля', 400);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare("
        UPDATE products
        SET name=?, brand=?, description=?, price=?, image=?, category_id=?, stock=?, badge=?
        WHERE id=?
    ");
    $stmt->execute([$name, $brand, $description, $price, $image, $categoryId, $stock, $badge ?: null, $id]);

    jsonResponse(true, ['id' => $id]);
}

function handleDelete(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        jsonResponse(false, null, 'Не указан ID товара', 400);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    jsonResponse(true, ['deleted' => $id]);
}
