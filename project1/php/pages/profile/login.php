<?php
    if(isset($_POST['login'])){
        $email = $_POST['email'];
        $password = $_POST['password'];
        if(empty($email) || empty($password)){
            $error = 'Введите все данные';
        }elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
            $error = 'Неверный формат почты';
        }else{
            $sql = "SELECT * FROM users WHERE email = '$email'";
            $check = $connect->query($sql)->fetch();
            if(!$check){
                $error = 'Нет такого пользователя';
            }elseif(!password_verify($password,$check['password'])){
                $error = 'Неверный пароль';
            }
        }
        if(!isset($error)){
            $_SESSION['uid'] = $check['id'];
            $_SESSION['success'] = 'Успешный вход';
            echo '<script>location.href="?"</script>';
        }
    }
?>
<h1>Вход</h1>
<form method="post">
    <?php if(isset($error)): ?>
        <i style="color:red"><?=$error?></i><br>
    <?php endif ?>
    Почта:
    <input type="text" name="email" value="<?= $email ?? ''?>">
    <?php if(isset($errors['email'])): ?>
        <i style="color:red"><?=$errors['email']?></i>
    <?php endif ?>
    <br><br>
    Пароль:
    <input type="password" name="password">
    <?php if(isset($errors['password'])): ?>
        <i style="color:red"><?=$errors['password']?></i>
    <?php endif ?>
    <br><br>
    <input type="submit" name="login">
</form>