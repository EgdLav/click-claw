<?php session_start() ?>
<?php include('php/prog/connect.php'); ?>
<?php include('php/prog/login_control.php'); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>clothes</title>
</head>
<body>
    <?php include('php/components/header.php'); ?>
    <?php include('php/components/message.php'); ?>
    <?php
        if(isset($_GET['page'])){
            $page = $_GET['page'];
            if($page == 'catalog'){include('php/pages/catalog.php');}

            elseif(isset($UID)){
                // если пользователь авторизован
                if($page == 'profile'){include('php/pages/profile/profile.php');}
                elseif($USER['role'] == 'admin'){
                    if($page == 'admin'){include('php/pages/admin/admin.php');}
                }elseif($USER['role'] == 'user'){
                    if($page == 'busket'){include('php/pages/profile/busket.php');}
                    if($page == 'create_order'){include('php/pages/profile/create_order.php');}
                    if($page == 'profile'){include('php/pages/profile/profile.php');}
                }
                else include('php/components/error.php');
            }else{
                if($page == 'login'){include('php/pages/profile/login.php');}
                elseif($page == 'reg'){include('php/pages/profile/reg.php');}
                else include('php/components/error.php');
            }
        }else{
            include('php/pages/home.php');
        }
    ?>
</body>
</html>