<?php
    if(isset($_SESSION['uid'])){
        $UID = $_SESSION['uid'];
        $sql = "SELECT * FROM users WHERE id = $UID";
        $USER = $connect->query($sql)->fetch();
    }
    if(isset($_POST['exit'])){
        session_unset();
        echo '<script>location.href="?"</script>';
    }
?>