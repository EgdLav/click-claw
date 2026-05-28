<?php
// список желаний: список, добавление, удаление, проверка
ob_start();
session_start();
include('../includes/db.php');
include('../includes/functions.php');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// список желаний
if($action == 'list'){
    if(!isset($_SESSION['uid'])){
        jsonResponse(false, null, 'Необходима авторизация');
    }

    $UID = $_SESSION['uid'];
    $sql = "SELECT p.*, c.name AS category_name, w.id AS wish_id,
                   (SELECT pi.image FROM product_images pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.sort_order ASC LIMIT 1) AS image
            FROM wishlist w
            JOIN products p ON w.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE w.user_id = $UID
            ORDER BY w.created_at DESC";
    $items = $connect->query($sql)->fetchAll();
    jsonResponse(true, $items);
}

// добавление в список желаний
if($action == 'add'){
    if(!isset($_SESSION['uid'])){
        jsonResponse(false, null, 'Необходима авторизация');
    }

    $UID = $_SESSION['uid'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    if(!$product_id){
        jsonResponse(false, null, 'Не указан ID товара');
    }

    $sql = "SELECT id FROM products WHERE id = $product_id";
    $check = $connect->query($sql)->fetch();
    if(!$check){
        jsonResponse(false, null, 'Товар не найден');
    }

    $stmt = $connect->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$UID, $product_id]);
    jsonResponse(true, ['product_id' => $product_id]);
}

// удаление из списка желаний
if($action == 'remove'){
    if(!isset($_SESSION['uid'])){
        jsonResponse(false, null, 'Необходима авторизация');
    }

    $UID = $_SESSION['uid'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    if(!$product_id){
        jsonResponse(false, null, 'Не указан ID товара');
    }

    $stmt = $connect->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$UID, $product_id]);
    jsonResponse(true, ['product_id' => $product_id]);
}

// проверка наличия в списке желаний
if($action == 'check'){
    $product_id = (int)($_GET['product_id'] ?? 0);
    if(!$product_id){
        jsonResponse(false, null, 'Не указан ID товара');
    }

    if(!isset($_SESSION['uid'])){
        jsonResponse(true, ['in_wishlist' => false]);
    }

    $UID = $_SESSION['uid'];
    $sql = "SELECT id FROM wishlist WHERE user_id = $UID AND product_id = $product_id";
    $check = $connect->query($sql)->fetch();
    jsonResponse(true, ['in_wishlist' => (bool)$check]);
}

jsonResponse(false, null, 'Неизвестное действие');
