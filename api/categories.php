<?php
/**
 * Categories API: list, create, update, delete
 */
ob_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

$action = getGetField('action') ?: getPostField('action');
if (empty($action)) {
    $action = 'list';
}

switch ($action) {
    case 'list':
        handleList();
        break;
    case 'create':
        requireAdmin();
        handleCreate();
        break;
    case 'update':
        requireAdmin();
        handleUpdate();
        break;
    case 'delete':
        requireAdmin();
        handleDelete();
        break;
    default:
        jsonResponse(false, null, 'Неизвестное действие', 400);
}

function handleList(): void {
    $pdo  = getDB();
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) AS product_count
        FROM categories c
        LEFT JOIN products p ON p.category_id = c.id
        GROUP BY c.id
        ORDER BY c.name ASC
    ");
    jsonResponse(true, $stmt->fetchAll());
}

function handleCreate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $name = sanitize($_POST['name'] ?? '');
    if (empty($name)) {
        jsonResponse(false, null, 'Введите название категории', 400);
    }

    $pdo = getDB();
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        jsonResponse(true, ['id' => (int)$pdo->lastInsertId(), 'name' => $name]);
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Категория с таким названием уже существует', 409);
    }
}

function handleUpdate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $id   = (int)($_POST['id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');

    if (!$id || empty($name)) {
        jsonResponse(false, null, 'Укажите ID и новое название', 400);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->execute([$name, $id]);

    jsonResponse(true, ['id' => $id, 'name' => $name]);
}

function handleDelete(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Метод не поддерживается', 405);
    }

    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        jsonResponse(false, null, 'Не указан ID категории', 400);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);

    jsonResponse(true, ['deleted' => $id]);
}
