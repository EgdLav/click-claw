<?php
// авторизация, регистрация, выход
ob_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

$action = getGetField('action') ?: getPostField('action');

switch ($action) {
    case 'login':    handleLogin();    break;
    case 'register': handleRegister(); break;
    case 'logout':   handleLogout();   break;
    case 'status':   handleStatus();   break;
    default:
        jsonResponse(false, null, 'Неизвестное действие', 400);
}

function handleLogin(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        jsonResponse(false, null, 'Введите email и пароль', 400);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        jsonResponse(false, null, 'Неверный email или пароль', 401);
    }

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];

    jsonResponse(true, [
        'id'        => $user['id'],
        'user_name' => $user['name'],
        'user_role' => $user['role'],
        'logged_in' => true,
    ]);
}

function handleRegister(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $name     = sanitize($_POST['name']     ?? '');
    $email    = strtolower(trim($_POST['email']    ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $agree    = $_POST['agree'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        jsonResponse(false, null, 'Заполните все обязательные поля', 400);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, null, 'Неверный формат email', 400);
    }
    if (strlen($password) < 6) {
        jsonResponse(false, null, 'Пароль минимум 6 символов', 400);
    }
    if ($password !== $confirm) {
        jsonResponse(false, null, 'Пароли не совпадают', 400);
    }
    if (empty($agree)) {
        jsonResponse(false, null, 'Необходимо принять политику конфиденциальности', 400);
    }

    $pdo  = getDB();
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        jsonResponse(false, null, 'Пользователь с таким email уже существует', 409);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->execute([$name, $email, $hash]);
    $userId = (int)$pdo->lastInsertId();

    $_SESSION['user_id']   = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = 'user';

    jsonResponse(true, [
        'id'        => $userId,
        'user_name' => $name,
        'user_role' => 'user',
        'logged_in' => true,
    ]);
}

function handleLogout(): void {
    session_unset();
    session_destroy();
    jsonResponse(true, ['logged_in' => false]);
}

function handleStatus(): void {
    if (isLoggedIn()) {
        jsonResponse(true, [
            'logged_in' => true,
            'user_id'   => $_SESSION['user_id'],
            'user_name' => $_SESSION['user_name'],
            'user_role' => $_SESSION['user_role'],
        ]);
    } else {
        jsonResponse(true, ['logged_in' => false]);
    }
}
