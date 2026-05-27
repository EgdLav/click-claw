<nav>
    <a href="?">Главная</a>
    <a href="?page=catalog">Каталог</a>
    <?php if(isset($UID)): ?>
        <?php if($USER['role'] == 'admin'): ?>
            <a href="?page=admin">Админ панель</a>
        <?php endif ?>
        Привет, <?=$USER['name']?>
        <?php if($USER['role'] == 'user'): ?>
            <a href="?page=busket">Корзина</a>
            <a href="?page=profile">Профиль</a>
        <?php endif ?>
        <form method="post">
            <input type="submit" name="exit" value="exit">
        </form>
    <?php else: ?>
        <a href="?page=login">Вход</a>
        <a href="?page=reg">Создать аккаунт</a>
    <?php endif ?>
</nav>
<hr>