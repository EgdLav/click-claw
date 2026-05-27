<?php
// подключение к базе данных

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=localhost;dbname=clickclaw;charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec("SET NAMES 'utf8mb4'");
        } catch (\PDOException $e) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error'   => 'Ошибка подключения к базе данных: ' . $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    return $pdo;
}
