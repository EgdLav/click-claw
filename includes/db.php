<?php
// подключение к базе данных
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

// для обратной совместимости с seed.php и migrate_wishlist.php
function getDB(){
    global $connect;
    return $connect;
}
