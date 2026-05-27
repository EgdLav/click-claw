<?php
$host = 'localhost';
$dbname = 'clickclaw';
$login = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try{
    $connect = new PDO($dsn,$login,$pass);
    // echo 'db ok';
}catch(PDOException $error){
    echo $error;
    exit;
}
