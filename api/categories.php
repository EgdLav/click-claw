<?php
// категории: список, создание, обновление, удаление
ob_start();
session_start();
include('../includes/db.php');
include('../includes/functions.php');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// список категорий
if($action == 'list'){
    $cats = $connect->query("
        SELECT c.*, COUNT(p.id) AS product_count
        FROM categories c
        LEFT JOIN products p ON p.category_id = c.id
        GROUP BY c.id
        ORDER BY c.name ASC
    ")->fetchAll();
    jsonResponse(true, $cats);
}

// добавление категории
if($action == 'create'){
    if(!isset($_SESSION['uid']) || $_SESSION['user_role'] != 'admin'){
        jsonResponse(false, null, 'Нет доступа');
    }

    $name = $_POST['name'] ?? '';
    if(empty($name)){
        jsonResponse(false, null, 'Введите название категории');
    }

    $stmt = $connect->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$name]);
    jsonResponse(true, ['id' => (int)$connect->lastInsertId(), 'name' => $name]);
}

// обновление категории
if($action == 'update'){
    if(!isset($_SESSION['uid']) || $_SESSION['user_role'] != 'admin'){
        jsonResponse(false, null, 'Нет доступа');
    }

    $id = (int)($_POST['id'] ?? 0);
    $name = $_POST['name'] ?? '';

    if(!$id || empty($name)){
        jsonResponse(false, null, 'Укажите ID и новое название');
    }

    $stmt = $connect->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->execute([$name, $id]);
    jsonResponse(true, ['id' => $id, 'name' => $name]);
}

// удаление категории
if($action == 'delete'){
    if(!isset($_SESSION['uid']) || $_SESSION['user_role'] != 'admin'){
        jsonResponse(false, null, 'Нет доступа');
    }

    $id = (int)($_POST['id'] ?? 0);
    if(!$id){
        jsonResponse(false, null, 'Не указан ID категории');
    }

    $stmt = $connect->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    jsonResponse(true, ['deleted' => $id]);
}

jsonResponse(false, null, 'Неизвестное действие');
