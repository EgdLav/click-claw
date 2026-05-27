<h1>Админ панель</h1>
<nav>
    <a href="?page=admin&category">Категории</a>
    <a href="?page=admin&catalog">Товары</a>
    <a href="?page=admin&orders">Заказы</a>
    <a href="?page=admin&users">Пользователи</a>
    <a href="?page=admin&statuses">Статусы</a>
</nav>

<?php
    if(isset($_GET['category'])){
        include('php/pages/admin/category.php');
    }
    if(isset($_GET['catalog'])){
        include('php/pages/admin/catalog.php');
    }
    if(isset($_GET['orders'])){
        include('php/pages/admin/orders.php');
    }
    if(isset($_GET['users'])){
        include('php/pages/admin/users.php');
    }
    if(isset($_GET['create_product'])){
        include('php/pages/admin/create_product.php');
    }
    if(isset($_GET['statuses'])){
        include('php/pages/admin/statuses.php');
    }
?>