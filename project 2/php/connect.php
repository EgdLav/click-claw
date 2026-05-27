<?php
try{
    $connect = new PDO ('mysql:host=localhost;dbname=itcourses','root','');
    // $connect = new PDO ('mysql:host=localhost;dbname=9172350836_zarruslan','046446404_zarrus','***');
    echo 'успех';
}catch(PDOException $error){
    echo $error;
}
?>