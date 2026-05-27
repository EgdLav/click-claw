<?php
// заказы: создание, список пользователя, все (для админа), обновление статуса
ob_start();
session_start();
include('../includes/db.php');
include('../includes/functions.php');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// создание заказа
if($action == 'create'){
    if(!isset($_SESSION['uid'])){
        jsonResponse(false, null, 'Необходима авторизация');
    }

    $UID = $_SESSION['uid'];
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';

    if(empty($name) || empty($phone)){
        jsonResponse(false, null, 'Заполните обязательные поля: имя и телефон');
    }

    // читаем корзину
    $sql = "SELECT c.product_id, c.quantity, p.name, p.price, p.stock
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = $UID";
    $cartRows = $connect->query($sql)->fetchAll();

    if(empty($cartRows)){
        jsonResponse(false, null, 'Корзина пуста');
    }

    // считаем итог
    $total = 0;
    $orderItems = [];
    foreach($cartRows as $row){
        $subtotal = (float)$row['price'] * (int)$row['quantity'];
        $total += $subtotal;
        $orderItems[] = [
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'price' => (float)$row['price'],
            'quantity' => (int)$row['quantity'],
        ];
    }

    // создаём заказ
    $stmt = $connect->prepare("
        INSERT INTO orders (user_id, name, phone, email, address, total)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$UID, $name, $phone, $email, $address, $total]);
    $order_id = (int)$connect->lastInsertId();

    // позиции заказа
    $itemStmt = $connect->prepare("
        INSERT INTO order_items (order_id, product_id, name, price, quantity)
        VALUES (?, ?, ?, ?, ?)
    ");
    foreach($orderItems as $item){
        $itemStmt->execute([$order_id, $item['product_id'], $item['name'], $item['price'], $item['quantity']]);
    }

    // очищаем корзину
    $connect->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$UID]);

    jsonResponse(true, ['order_id' => $order_id, 'total' => $total]);
}

// список заказов пользователя
if($action == 'list'){
    if(!isset($_SESSION['uid'])){
        jsonResponse(false, null, 'Необходима авторизация');
    }

    $UID = $_SESSION['uid'];
    $sql = "SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS items_count
            FROM orders o
            WHERE o.user_id = $UID
            ORDER BY o.created_at DESC";
    $orders = $connect->query($sql)->fetchAll();
    jsonResponse(true, $orders);
}

// получение одного заказа
if($action == 'get'){
    if(!isset($_SESSION['uid'])){
        jsonResponse(false, null, 'Необходима авторизация');
    }

    $UID = $_SESSION['uid'];
    $id = (int)($_GET['id'] ?? 0);
    if(!$id){
        jsonResponse(false, null, 'Не указан ID заказа');
    }

    $sql = "SELECT * FROM orders WHERE id = $id AND user_id = $UID";
    $order = $connect->query($sql)->fetch();

    if(!$order){
        jsonResponse(false, null, 'Заказ не найден');
    }

    $itemStmt = $connect->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemStmt->execute([$id]);
    $order['items'] = $itemStmt->fetchAll();

    jsonResponse(true, $order);
}

// все заказы (для админа)
if($action == 'all'){
    if(!isset($_SESSION['uid']) || $_SESSION['user_role'] != 'admin'){
        jsonResponse(false, null, 'Нет доступа');
    }

    $orders = $connect->query("
        SELECT o.*, u.name AS user_name, u.email AS user_email,
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS items_count
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ")->fetchAll();
    jsonResponse(true, $orders);
}

// обновление статуса заказа
if($action == 'update_status'){
    if(!isset($_SESSION['uid']) || $_SESSION['user_role'] != 'admin'){
        jsonResponse(false, null, 'Нет доступа');
    }

    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $allowed = ['new', 'processing', 'completed', 'cancelled'];

    if(!$id || !in_array($status, $allowed)){
        jsonResponse(false, null, 'Неверные параметры');
    }

    $stmt = $connect->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    jsonResponse(true, ['id' => $id, 'status' => $status]);
}

jsonResponse(false, null, 'Неизвестное действие');
