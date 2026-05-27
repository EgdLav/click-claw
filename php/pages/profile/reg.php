<?php
    $errors = [];
    if(isset($_POST['reg'])){
        $name      = $_POST['name'];
        $email     = $_POST['email'];
        $password  = $_POST['password'];
        $password2 = $_POST['password2'];

        if(empty($name)){
            $errors['name'] = 'Введите имя';
        }elseif(mb_strlen($name) < 2){
            $errors['name'] = 'Не менее 2 символов';
        }

        if(empty($email)){
            $errors['email'] = 'Введите почту';
        }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $errors['email'] = 'Неверный формат почты';
        }else{
            $stmt = $connect->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if($stmt->fetch()){
                $errors['email'] = 'Почта уже занята';
            }
        }

        if(empty($password)){
            $errors['password'] = 'Введите пароль';
        }elseif(mb_strlen($password) < 6){
            $errors['password'] = 'Не менее 6 символов';
        }elseif($password != $password2){
            $errors['password2'] = 'Пароли не совпадают';
        }

        if(empty($_POST['confirm'])){
            $errors['confirm'] = 'Примите условия';
        }

        if(empty($errors)){
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $connect->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$name, $email, $hash]);
            $_SESSION['success'] = 'Успешная регистрация';
            echo '<script>location.href="?page=login"</script>';
            exit;
        }
    }
?>

<main>
    <div class="container">
        <div class="modal-content" style="max-width:480px;margin:60px auto;">
            <h2 class="modal-title">Регистрация</h2>
            <form method="post" class="auth-form">
                <div class="input-group">
                    <label>Имя</label>
                    <input type="text" name="name" placeholder="Имя" value="<?=$name ?? ''?>" required>
                    <?php if(isset($errors['name'])): ?>
                        <i style="color:red"><?=$errors['name']?></i>
                    <?php endif ?>
                </div>
                <div class="input-group">
                    <label>Электронная почта</label>
                    <input type="email" name="email" placeholder="Электронная почта*" value="<?=$email ?? ''?>" required>
                    <?php if(isset($errors['email'])): ?>
                        <i style="color:red"><?=$errors['email']?></i>
                    <?php endif ?>
                </div>
                <div class="input-group">
                    <label>Пароль</label>
                    <input type="password" name="password" placeholder="Пароль*" required>
                    <?php if(isset($errors['password'])): ?>
                        <i style="color:red"><?=$errors['password']?></i>
                    <?php endif ?>
                </div>
                <div class="input-group">
                    <label>Повтор пароля</label>
                    <input type="password" name="password2" placeholder="Повтор пароля*" required>
                    <?php if(isset($errors['password2'])): ?>
                        <i style="color:red"><?=$errors['password2']?></i>
                    <?php endif ?>
                </div>
                <div class="input-polit">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="confirm">
                        <span>Я согласен(а) с политикой конфиденциальности</span>
                    </label>
                    <?php if(isset($errors['confirm'])): ?>
                        <i style="color:red"><?=$errors['confirm']?></i>
                    <?php endif ?>
                </div>
                <div class="auth-buttons">
                    <button type="submit" name="reg" class="btn-login">Зарегистрироваться</button>
                    <div class="btn-register-wrapper">
                        <a href="?page=login" class="btn-register">Уже есть аккаунт? Войдите здесь</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>
