<h1>Профиль</h1>

<nav>
    <a href="?page=profile&addreses">Адреса</a>
    <a href="?page=profile&orders">Заказы</a>
</nav>

<?php
    if(isset($_GET['addreses'])){
        include('php/pages/profile/addreses.php');
    }
    if(isset($_GET['orders'])){
        include('php/pages/profile/orders.php');
    }

?>