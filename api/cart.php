<?php
// корзина: получение, добавление, обновление, удаление
ob_start();
session_start();
include('../includes/db.php');
include('../includes/functions.php');

$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

// получение корзины
if($action == 'get'){
    if(!isset($_SESSION['uid'])){
        jsonResponse(true, ['items' => [], 'total' => 0, 'count' => 0]);
    }

    $UID = $_SESSION['uid'];
    $sql = "SELECT c.product_id, c.quantity,
                   p.name, p.brand, p.price, p.stock,
                   (SELECT pi.image FROM product_images pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.sort_order ASC LIMIT 1) AS image
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = $UID
            ORDER BY c.id ASC";
    $rows = $connect->query($sql)->fetchAll();

    $items = [];
    $total = 0;
    $count = 0;
    foreach($rows as $row){
        $subtotal = (float)$row['price'] * (int)$row['quantity'];
        $items[] = [
            'product_id' => (int)$row['product_id'],
            'name' => $row['name'],
            'brand' => $row['brand'],
            'price' => (float)$row['price'],
            'image' => $row['image'] ?? null,
            'stock' => (int)$row['stock'],
            'quantity' => (int)$row['quantity'],
            'subtotal' => $subtotal,
        ];
        $total += $subtotal;
        $count += (int)$row['quantity'];
    }

    jsonResponse(true, ['items' => $items, 'total' => $total, 'count' => $count]);
}

// добавление в корзину
if($action == 'add'){
    if(!isset($_SESSION['uid'])){
        jsonResponse(false, null, 'Необходима авторизация');
    }

    $UID = $_SESSION['uid'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['quantity'] ?? 1));

    if(!$product_id){
        jsonResponse(false, null, 'Не указан ID товара');
    }

    $sql = "SELECT id, stock FROM products WHERE id = $product_id";
    $product = $connect->query($sql)->fetch();

    if(!$product){
        jsonResponse(false, null, 'Товар не найден');
    }

    $sql = "SELECT quantity FROM cart WHERE user_id = $UID AND product_id = $product_id";
    $existing = $connect->query($sql)->fetchColumn();

    $newQty = min(($existing ?: 0) + $qty, (int)$product['stock']);

    $stmt = $connect->prepare("
        INSERT INTO cart (user_id, product_id, quantity)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE quantity = ?
    ");
    $stmt->execute([$UID, $product_id, $newQty, $newQty]);

    // возвращаем обновлённую корзину
    $sql = "SELECT c.product_id, c.quantity,
                   p.name, p.brand, p.price, p.stock,
                   (SELECT pi.image FROM product_images pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.sort_order ASC LIMIT 1) AS image
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = $UID
            ORDER BY c.id ASC";
    $rows = $connect->query($sql)->fetchAll();

    $items = [];
    $total = 0;
    $count = 0;
    foreach($rows as $row){
        $subtotal = (float)$row['price'] * (int)$row['quantity'];
        $items[] = [
            'product_id' => (int)$row['product_id'],
            'name' => $row['name'],
            'brand' => $row['brand'],
            'price' => (float)$row['price'],
            'image' => $row['image'] ?? null,
            'stock' => (int)$row['stock'],
            'quantity' => (int)$row['quantity'],
            'subtotal' => $subtotal,
        ];
        $total += $subtotal;
        $count += (int)$row['quantity'];
    }

    jsonResponse(true, ['items' => $items, 'total' => $total, 'count' => $count]);
}

// обновление количества
if($action == 'update'){
    if(!isset($_SESSION['uid'])){
        jsonResponse(false, null, 'Необходима авторизация');
    }

    $UID = $_SESSION['uid'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 0);

    if(!$product_id){
        jsonResponse(false, null, 'Не указан ID товара');
    }

    if($qty <= 0){
        $stmt = $connect->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$UID, $product_id]);
    }else{
        $stmt = $connect->prepare("
            INSERT INTO cart (user_id, product_id, quantity)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = ?
        ");
        $stmt->execute([$UID, $product_id, $qty, $qty]);
    }

    // возвращаем обновлённую корзину
    $sql = "SELECT c.product_id, c.quantity,
                   p.name, p.brand, p.price, p.stock,
                   (SELECT pi.image FROM product_images pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.sort_order ASC LIMIT 1) AS image
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = $UID
            ORDER BY c.id ASC";
    $rows = $connect->query($sql)->fetchAll();

    $items = [];
    $total = 0;
    $count = 0;
    foreach($rows as $row){
        $subtotal = (float)$row['price'] * (int)$row['quantity'];
        $items[] = [
            'product_id' => (int)$row['product_id'],
            'name' => $row['name'],
            'brand' => $row['brand'],
            'price' => (float)$row['price'],
            'image' => $row['image'] ?? null,
            'stock' => (int)$row['stock'],
            'quantity' => (int)$row['quantity'],
            'subtotal' => $subtotal,
        ];
        $total += $subtotal;
        $count += (int)$row['quantity'];
    }

    jsonResponse(true, ['items' => $items, 'total' => $total, 'count' => $count]);
}

// удаление из корзины
if($action == 'remove'){
    if(!isset($_SESSION['uid'])){
        jsonResponse(false, null, 'Необходима авторизация');
    }

    $UID = $_SESSION['uid'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    if(!$product_id){
        jsonResponse(false, null, 'Не указан ID товара');
    }

    $stmt = $connect->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$UID, $product_id]);

    // возвращаем обновлённую корзину
    $sql = "SELECT c.product_id, c.quantity,
                   p.name, p.brand, p.price, p.stock,
                   (SELECT pi.image FROM product_images pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.sort_order ASC LIMIT 1) AS image
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = $UID
            ORDER BY c.id ASC";
    $rows = $connect->query($sql)->fetchAll();

    $items = [];
    $total = 0;
    $count = 0;
    foreach($rows as $row){
        $subtotal = (float)$row['price'] * (int)$row['quantity'];
        $items[] = [
            'product_id' => (int)$row['product_id'],
            'name' => $row['name'],
            'brand' => $row['brand'],
            'price' => (float)$row['price'],
            'image' => $row['image'] ?? null,
            'stock' => (int)$row['stock'],
            'quantity' => (int)$row['quantity'],
            'subtotal' => $subtotal,
        ];
        $total += $subtotal;
        $count += (int)$row['quantity'];
    }

    jsonResponse(true, ['items' => $items, 'total' => $total, 'count' => $count]);
}

// очистка корзины
if($action == 'clear'){
    if(!isset($_SESSION['uid'])){
        jsonResponse(false, null, 'Необходима авторизация');
    }

    $UID = $_SESSION['uid'];
    $connect->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$UID]);
    jsonResponse(true, ['items' => [], 'total' => 0, 'count' => 0]);
}

// количество товаров в корзине
if($action == 'count'){
    if(!isset($_SESSION['uid'])){
        jsonResponse(true, ['count' => 0]);
    }

    $UID = $_SESSION['uid'];
    $sql = "SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = $UID";
    $count = (int)$connect->query($sql)->fetchColumn();
    jsonResponse(true, ['count' => $count]);
}

jsonResponse(false, null, 'Неизвестное действие');
