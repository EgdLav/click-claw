<?php
/**
 * Authentication API: login, register, logout
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

$action = getGetField('action') ?: getPostField('action');

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'register':
        handleRegister();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'status':
        handleStatus();
        break;
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
        'id'   => $user['id'],
        'name' => $user['name'],
        'role' => $user['role'],
    ]);
}

function handleRegister(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $name     = sanitize($_POST['name'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $phone    = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $agree    = !empty($_POST['agree']);

    if (empty($name) || empty($email) || empty($password)) {
        jsonResponse(false, null, 'Заполните все обязательные поля', 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, null, 'Некорректный email', 400);
    }

    if (strlen($password) < 6) {
        jsonResponse(false, null, 'Пароль должен содержать минимум 6 символов', 400);
    }

    if ($password !== $confirm) {
        jsonResponse(false, null, 'Пароли не совпадают', 400);
    }

    if (!$agree) {
        jsonResponse(false, null, 'Необходимо согласиться с политикой конфиденциальности', 400);
    }

    $pdo = getDB();

    // Check if email already exists
    $exists = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $exists->execute([$email]);
    if ($exists->fetchColumn() > 0) {
        jsonResponse(false, null, 'Пользователь с таким email уже существует', 409);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $hash]);
    $userId = (int)$pdo->lastInsertId();

    $_SESSION['user_id']   = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = 'user';

    jsonResponse(true, [
        'id'   => $userId,
        'name' => $name,
        'role' => 'user',
    ]);
}

function handleLogout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    jsonResponse(true, ['redirect' => '/index.html']);
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
