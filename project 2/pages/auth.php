<?php
// массив ошибок для валидацияя
$error = '';
// обработка формы авторизации
if(isset($_POST['auth'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    // валидация
    if(empty($email) OR empty($password)){
        $error = 'Введите все данные';
    }elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $error = 'Неверный формат почты';
    }else{
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $user = $connect->query($sql)->fetch();
        if($user == false){
            $error = 'Нет такого пользователя';
        }elseif(!password_verify($password,$user['password'])){
            $error = 'Неверный пароль';
        }
    }

    if(empty($error)){
        // процесс авторизации
        $_SESSION['uid'] = $user['id'];
        // скрипт
        echo'<script>location.href="?"</script>';
    }
}
?>
<h1>Вход</h1>
<?php if(isset($error)): ?>
    <i style="color:red"><?=$error?></i>
<?php endif ?>
<form method="post" name="auth">
    email:
    <input type="text" name="email"><br><br>
    password:
    <input type="password" name="password"><br><br>
    <input type="submit" name="auth">
</form>