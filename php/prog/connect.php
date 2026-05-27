<?php
$host   = 'localhost';
$dbname = 'clickclaw';
$login  = 'root';
$pass   = '';
$dsn    = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $connect = new PDO($dsn, $login, $pass);
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connect->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $error) {
    die('Ошибка подключения к БД: ' . $error->getMessage());
}
