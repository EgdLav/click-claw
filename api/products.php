<?php
// товары: список, получение, создание, обновление, удаление
ob_start();
session_start();
include('../includes/db.php');
include('../includes/functions.php');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// список товаров
if($action == 'list'){
    $where = [];
    $params = [];

    $category_id = $_GET['category_id'] ?? '';
    if($category_id !== ''){
        $where[] = 'p.category_id = ?';
        $params[] = (int)$category_id;
    }

    $search = $_GET['search'] ?? '';
    if($search !== ''){
        $where[] = '(p.name LIKE ? OR p.brand LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $brand = $_GET['brand'] ?? '';
    if($brand !== ''){
        $where[] = 'p.brand = ?';
        $params[] = $brand;
    }

    $price_min = $_GET['price_min'] ?? '';
    if($price_min !== ''){
        $where[] = 'p.price >= ?';
        $params[] = (float)$price_min;
    }

    $price_max = $_GET['price_max'] ?? '';
    if($price_max !== ''){
        $where[] = 'p.price <= ?';
        $params[] = (float)$price_max;
    }

    $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sortMap = [
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'name_asc'   => 'p.name ASC',
    ];
    $sort = $_GET['sort'] ?? '';
    $orderBy = isset($sortMap[$sort]) ? $sortMap[$sort] : 'p.id DESC';

    $stmt = $connect->prepare("
        SELECT p.*, c.name AS category_name,
               (SELECT pi.image FROM product_images pi
                WHERE pi.product_id = p.id
                ORDER BY pi.sort_order ASC LIMIT 1) AS image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        $whereStr
        ORDER BY $orderBy
    ");
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    jsonResponse(true, $products);
}

// получение одного товара
if($action == 'get'){
    $id = (int)($_GET['id'] ?? 0);
    if(!$id){
        jsonResponse(false, null, 'Не указан ID товара');
    }

    $sql = "SELECT p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = $id";
    $product = $connect->query($sql)->fetch();

    if(!$product){
        jsonResponse(false, null, 'Товар не найден');
    }

    // изображения
    $imgStmt = $connect->prepare("SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
    $imgStmt->execute([$id]);
    $product['images'] = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

    if(empty($product['images']) && !empty($product['image'])){
        $product['images'] = [$product['image']];
    }

    jsonResponse(true, $product);
}

// создание товара
if($action == 'create'){
    if(!isset($_SESSION['uid']) || $_SESSION['user_role'] != 'admin'){
        jsonResponse(false, null, 'Нет доступа');
    }

    $name = $_POST['name'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $desc = $_POST['description'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $badge = $_POST['badge'] ?? '';
    $imagesStr = $_POST['images'] ?? '';

    if(empty($name) || !$price || !$category_id){
        jsonResponse(false, null, 'Заполните обязательные поля');
    }

    $stmt = $connect->prepare("
        INSERT INTO products (name, brand, description, price, category_id, stock, badge)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $brand, $desc, $price, $category_id, $stock, $badge]);
    $productId = (int)$connect->lastInsertId();

    if($imagesStr){
        $images = array_filter(array_map('trim', explode(',', $imagesStr)));
        $imgStmt = $connect->prepare("INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)");
        foreach($images as $i => $img){
            $imgStmt->execute([$productId, $img, $i]);
        }
    }

    jsonResponse(true, ['id' => $productId]);
}

// обновление товара
if($action == 'update'){
    if(!isset($_SESSION['uid']) || $_SESSION['user_role'] != 'admin'){
        jsonResponse(false, null, 'Нет доступа');
    }

    $id = (int)($_POST['id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $desc = $_POST['description'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $badge = $_POST['badge'] ?? '';
    $imagesStr = $_POST['images'] ?? '';

    if(!$id || empty($name) || !$price || !$category_id){
        jsonResponse(false, null, 'Заполните обязательные поля');
    }

    $stmt = $connect->prepare("
        UPDATE products
        SET name = ?, brand = ?, description = ?, price = ?, category_id = ?, stock = ?, badge = ?
        WHERE id = ?
    ");
    $stmt->execute([$name, $brand, $desc, $price, $category_id, $stock, $badge, $id]);

    $connect->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
    if($imagesStr){
        $images = array_filter(array_map('trim', explode(',', $imagesStr)));
        $imgStmt = $connect->prepare("INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)");
        foreach($images as $i => $img){
            $imgStmt->execute([$id, $img, $i]);
        }
    }

    jsonResponse(true, ['id' => $id]);
}

// удаление товара
if($action == 'delete'){
    if(!isset($_SESSION['uid']) || $_SESSION['user_role'] != 'admin'){
        jsonResponse(false, null, 'Нет доступа');
    }

    $id = (int)($_POST['id'] ?? 0);
    if(!$id){
        jsonResponse(false, null, 'Не указан ID товара');
    }

    $stmt = $connect->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    jsonResponse(true, ['deleted' => $id]);
}

jsonResponse(false, null, 'Неизвестное действие');
