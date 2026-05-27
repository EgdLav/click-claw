<?php
    if(isset($_POST['login'])){
        $email    = $_POST['email'];
        $password = $_POST['password'];

        if(empty($email) || empty($password)){
            $error = 'Введите все данные';
        }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $error = 'Неверный формат почты';
        }else{
            $stmt = $connect->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $check = $stmt->fetch();
            if(!$check){
                $error = 'Нет такого пользователя';
            }elseif(!password_verify($password, $check['password'])){
                $error = 'Неверный пароль';
            }
        }

        if(!isset($error)){
            $_SESSION['uid'] = $check['id'];
            $_SESSION['success'] = 'Успешный вход';
            if($check['role'] == 'admin'){
                echo '<script>location.href="?page=admin"</script>';
            }else{
                echo '<script>location.href="?page=profile"</script>';
            }
            exit;
        }
    }
?>

<main>
    <div class="container">
        <div class="modal-content" style="max-width:480px;margin:60px auto;">
            <h2 class="modal-title">Авторизация</h2>
            <form method="post" class="auth-form">
                <?php if(isset($error)): ?>
                    <i style="color:red"><?=$error?></i><br>
                <?php endif ?>
                <div class="input-group">
                    <label>Электронная почта</label>
                    <input type="email" name="email" placeholder="Электронная почта*" value="<?=$email ?? ''?>" required>
                </div>
                <div class="input-group">
                    <label>Пароль</label>
                    <input type="password" name="password" placeholder="Пароль*" required>
                </div>
                <div class="auth-buttons">
                    <button type="submit" name="login" class="btn-login">Авторизоваться</button>
                    <div class="btn-register-wrapper">
                        <a href="?page=reg" class="btn-register">Новый клиент? Создайте учетную запись</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>
