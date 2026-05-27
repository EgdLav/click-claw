<?php
$host = 'localhost';
$dbname = 'clothesstore325';
$login = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$dbname";

try{
    $connect = new PDO ($dsn,$login,$pass);
    echo 'db ok';
}catch(PDOException $error){
    echo $error;
}
?>