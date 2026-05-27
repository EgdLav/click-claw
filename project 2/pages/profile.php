<h1>Профиль</h1>
<!-- навигация профиля -->
<nav>
    <a href="?page=profile">Мои данные</a>
    <a href="?page=profile&my_requests">Мои заявки</a>
</nav><hr>

<?php
    // маршрутизация профиля
    if(isset($_GET['my_requests'])){
        include('pages/profile/my_requests.php');
    }else{
        include('pages/profile/start.php');
    }
?>