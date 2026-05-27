<?php
// пользователи: получение данных, обновление профиля
ob_start();
session_start();
include('../includes/db.php');
include('../includes/functions.php');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// получение данных пользователя
if($action == 'get'){
    if(!isset($_SESSION['uid'])){
        jsonResponse(false, null, 'Необходима авторизация');
    }

    $UID = $_SESSION['uid'];
    $sql = "SELECT id, name, email, phone, role, created_at FROM users WHERE id = $UID";
    $user = $connect->query($sql)->fetch();

    if(!$user){
        jsonResponse(false, null, 'Пользователь не найден');
    }

    jsonResponse(true, $user);
}

// обновление профиля
if($action == 'update'){
    if(!isset($_SESSION['uid'])){
        jsonResponse(false, null, 'Необходима авторизация');
    }

    $UID = $_SESSION['uid'];
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';

    if(empty($name)){
        jsonResponse(false, null, 'Имя не может быть пустым');
    }

    $stmt = $connect->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
    $stmt->execute([$name, $phone, $UID]);

    $_SESSION['user_name'] = $name;

    jsonResponse(true, ['name' => $name, 'phone' => $phone]);
}

jsonResponse(false, null, 'Неизвестное действие');
