<?php
/**
 * Users API: get current user, update profile
 */
ob_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

$action = getGetField('action') ?: getPostField('action');

switch ($action) {
    case 'get':
        requireAuth();
        handleGet();
        break;
    case 'update':
        requireAuth();
        handleUpdate();
        break;
    default:
        jsonResponse(false, null, 'Неизвестное действие', 400);
}

function handleGet(): void {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?");
    $stmt->execute([currentUserId()]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(false, null, 'Пользователь не найден', 404);
    }

    jsonResponse(true, $user);
}

function handleUpdate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $name  = sanitize($_POST['name']  ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    if (empty($name)) {
        jsonResponse(false, null, 'Имя не может быть пустым', 400);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
    $stmt->execute([$name, $phone, currentUserId()]);

    // Update session name
    $_SESSION['user_name'] = $name;

    jsonResponse(true, ['name' => $name, 'phone' => $phone]);
}
