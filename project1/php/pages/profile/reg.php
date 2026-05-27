<?php
$errors = [];
if(isset($_POST['reg'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if(empty($name)){
        $errors['name'] = 'Введите имя';
    }elseif(mb_strlen($name) < 2){
        $errors['name'] = 'Не менее 2х символов';
    }elseif(mb_strlen($name) > 20){
        $errors['name'] = 'Не более 20 символов';
    }

    if(empty($email)){
        $errors['email'] = 'Введите почту';
    }elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $errors['email'] = 'Неверный формат почты';
    }else{
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $check = $connect->query($sql)->fetch();
        if(!empty($check)){
            $errors['email'] = 'Почта занята';
        }
    }

    if(empty($password)){
        $errors['password'] = 'Введите пароль';
    }elseif(mb_strlen($password) < 6){
        $errors['password'] = 'Не менее 6 символов';
    }elseif($password != $password2){
        $errors['password2'] = 'Пароли не одинковы';
    }

    if(empty($_POST['confirm'])){
        $errors['confirm'] = 'Примите условия';
    }
    
    if(empty($errors)){
        $hash = password_hash($password,PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name,email,password,role)
            VALUES ('$name','$email','$hash','user')";
        $connect->query($sql);
        // авто авторизация
        
        $_SESSION['success'] = 'Успешная регистрация';
        echo '<script>location.href="?page=login"</script>';
    }
}
?>
<h1>Регистрация</h1>
<form method="post">
    Имя:
    <input type="text" name="name" value="<?= $name ?? ''?>">
    <?php if(isset($errors['name'])): ?>
        <i style="color:red"><?=$errors['name']?></i>
    <?php endif ?>
    <br><br>
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
    Повтор паполя:
    <input type="password" name="password2">
    <?php if(isset($errors['password2'])): ?>
        <i style="color:red"><?=$errors['password2']?></i>
    <?php endif ?>
    <br><br>
    <input type="checkbox" name="confirm">
    Я согл
    <?php if(isset($errors['confirm'])): ?>
        <i style="color:red"><?=$errors['confirm']?></i>
    <?php endif ?>
    <br><br>
    <input type="submit" name="reg">
</form>