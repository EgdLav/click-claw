<?php session_start() ?> 
<!-- подключение к БД -->
<?php include('php/connect.php'); ?>
<!-- Контроль авторизации -->
<?php include('php/auth_control.php'); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>itcourses</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- навигация -->
    <?php include('php/header.php'); ?>
    <?php
        // маршрутизация
        if(isset($_GET['page'])){
            $page = $_GET['page'];
            if($page == 'reg'){include('pages/reg.php');}
            if($page == 'auth'){include('pages/auth.php');}
            if($page == 'profile'){include('pages/profile.php');}
            if($page == 'add'){include('pages/add.php');}
            if($page == 'courses'){include('pages/courses.php');}
            if($page == 'edit'){include('pages/edit.php');}
            if($page == 'request'){include('pages/request.php');}
            if($page == 'admin'){include('pages/admin.php');}
            if($page == 'item'){include('pages/item.php');}
        }else{
            include('pages/start.php');
        }
        
    ?>
</body>
</html>