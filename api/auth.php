<?php
// авторизация, регистрация, выход
ob_start();
session_start();
include('../includes/db.php');
include('../includes/functions.php');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// вход
if($action == 'login'){
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if(empty($email) || empty($password)){
        jsonResponse(false, null, 'Введите все данные');
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        jsonResponse(false, null, 'Неверный формат почты');
    }

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $check = $connect->query($sql)->fetch();

    if(!$check){
        jsonResponse(false, null, 'Нет такого пользователя');
    }

    if(!password_verify($password, $check['password'])){
        jsonResponse(false, null, 'Неверный пароль');
    }

    $_SESSION['uid'] = $check['id'];
    $_SESSION['user_name'] = $check['name'];
    $_SESSION['user_role'] = $check['role'];

    jsonResponse(true, [
        'id' => $check['id'],
        'user_name' => $check['name'],
        'user_role' => $check['role'],
        'logged_in' => true,
    ]);
}

// регистрация
if($action == 'register'){
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $agree = $_POST['agree'] ?? '';

    if(empty($name) || empty($email) || empty($password)){
        jsonResponse(false, null, 'Заполните все обязательные поля');
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        jsonResponse(false, null, 'Неверный формат почты');
    }

    if(mb_strlen($password) < 6){
        jsonResponse(false, null, 'Пароль минимум 6 символов');
    }

    if($password != $confirm){
        jsonResponse(false, null, 'Пароли не совпадают');
    }

    if(empty($agree)){
        jsonResponse(false, null, 'Необходимо принять политику конфиденциальности');
    }

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $check = $connect->query($sql)->fetch();
    if($check){
        jsonResponse(false, null, 'Пользователь с таким email уже существует');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $connect->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->execute([$name, $email, $hash]);
    $userId = (int)$connect->lastInsertId();

    $_SESSION['uid'] = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = 'user';

    jsonResponse(true, [
        'id' => $userId,
        'user_name' => $name,
        'user_role' => 'user',
        'logged_in' => true,
    ]);
}

// выход
if($action == 'logout'){
    session_unset();
    session_destroy();
    jsonResponse(true, ['logged_in' => false]);
}

// статус
if($action == 'status'){
    if(isset($_SESSION['uid'])){
        jsonResponse(true, [
            'logged_in' => true,
            'user_id' => $_SESSION['uid'],
            'user_name' => $_SESSION['user_name'] ?? '',
            'user_role' => $_SESSION['user_role'] ?? '',
        ]);
    }else{
        jsonResponse(true, ['logged_in' => false]);
    }
}

jsonResponse(false, null, 'Неизвестное действие');
