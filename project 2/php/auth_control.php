<?php
    // если пользователь авторизован
    if(isset($_SESSION['uid'])){
        // ID пользователя для дальнейшего использования в проекте
        $UID = $_SESSION['uid'];
        $sql = "SELECT * FROM users WHERE id = $UID";
        // Вся инфа о пользовтаеле
        $USER = $connect->query($sql)->fetch();
    }

    // если пользователь нажал "выйти"
    if(isset($_POST['exit'])){
        session_unset();
        echo'<script>location.href="?"</script>';
    }
?>