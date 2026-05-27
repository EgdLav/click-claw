<?php
    // массив ошибок
    $errors = [];
    // обработка формы решистрации
    if(isset($_POST['reg'])){
        $email = $_POST['email'];
        $password = $_POST['password'];
        $password2 = $_POST['password2'];

        // валидация почты
        if(empty($email)){
            $errors['email'] = 'Введите почту';
        }elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
            $errors['email'] = 'Неверный формат';
        }else{
            // проверка на уникальность
            $sql = "SELECT * FROM users WHERE email = '$email'";
            $check = $connect->query($sql)->fetch();
            if($check != false){
                $errors['email'] = 'Почта занята';
            }
        }

        // валидация пароля
        if(empty($password)){
            $errors['password'] = 'Введите пароль';
        }elseif(mb_strlen($password) < 6){
            $errors['password'] = 'Не менее 6 символов';
        }elseif($password != $password2){
            $errors['password2'] = 'Пароли не совопдают';
        }

        // если нет ршибок
        if(empty($errors)){
            // шифрование пароля
            $hash = password_hash($password,PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (email,password,role)
                VALUES ('$email','$hash','user')";
            $connect->query($sql);
            $success = 'Успешная регистрация';
        }
    }
?>
<h1>Регистрация</h1>
<?php if(isset($success)): ?>
    <h3 style="color:green"><?=$success?></h3>
    <a href="?page=auth">Войдите в аккаунт</a>
<?php endif ?>
<form method="post" name="reg">
    email:
    <input type="text" name="email" value="<?=$email ?? ''?>"><br>
    <?php if(isset($errors['email'])): ?>
        <i style="color:red"><?=$errors['email']?></i>
    <?php endif ?>
    <br>
    password:
    <input type="password" name="password"><br>
    <?php if(isset($errors['password'])): ?>
        <i style="color:red"><?=$errors['password']?></i>
    <?php endif ?><br>
    password2:
    <input type="password" name="password2"><br>
    <?php if(isset($errors['password2'])): ?>
        <i style="color:red"><?=$errors['password2']?></i>
    <?php endif ?><br>
    <input type="submit" name="reg">
</form>
