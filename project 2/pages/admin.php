<h1>Админ панель</h1>
<!-- навигация админ панели -->
<nav>
    <a href="?page=admin">Курсы</a>
    <a href="?page=admin&requests">Заявки</a>
    <a href="?page=admin&users">Пользователи</a>
</nav>

<?php
// маршрутизация админ панели
if(isset($_GET['requests'])){
    include('pages/admin/requests.php');
}elseif(isset($_GET['users'])){
    include('pages/admin/users.php');
}else{
    include('pages/admin/start.php');
}
?>