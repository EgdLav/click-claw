<header class="header">
    <div class="log">
        LOGO
    </div>
    <div class="nav">
        <a href="">Главная</a>
        <a href="?page=courses">Курсы</a>
        <a href="">Контакты</a>
    </div>
    <div class="buttons">
        <?php if(isset($_SESSION['uid'])): ?>
            <!-- если пользователь авторизован -->
            Привет, <?=$USER['email']?>
            <a href="?page=profile">Профиль</a>
            <form method="post" name="exit">
                <input type="submit" name="exit" value="exit">
            </form>
            <?php if($USER['role'] == 'admin'): ?>
                <!-- если это админ -->
                <a href="?page=admin">Админ панель</a>
            <?php endif ?>
        <?php else: ?>
            <a href="?page=auth">Войти</a>
            <a href="?page=reg">Регистрация</a>
        <?php endif ?>
        
    </div>
</header><hr>