<?php
/**
 * Database connection — MySQL via PDO
 * Host: localhost | DB: clickclaw | User: root | Pass: (empty)
 */

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=localhost;dbname=clickclaw;charset=utf8mb4';
        $pdo = new PDO($dsn, 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec("SET NAMES 'utf8mb4'");
    }
    return $pdo;
}
