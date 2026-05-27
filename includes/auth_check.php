<?php
// проверка авторизации
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// загрузка данных пользователя
if(isset($_SESSION['uid'])){
    $UID = $_SESSION['uid'];
    $sql = "SELECT * FROM users WHERE id = $UID";
    $USER = $connect->query($sql)->fetch();
}

// выход
if(isset($_POST['exit'])){
    session_unset();
    session_destroy();
    echo '<script>location.href="?"</script>';
    exit;
}
