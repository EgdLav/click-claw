<?php
if(isset($_POST['new_address'])){
    $city = $_POST['city'];
    $street = $_POST['street'];
    $home = $_POST['home'];
    $appart = $_POST['appart'];
    if(empty($city) || empty($street) || empty($home)){
        $error = 'Введите все данные';
    }else{
        $sql = "INSERT INTO address (user_id,city,street,home,appart)
            VALUES ('$UID','$city','$street','$home','$appart')";
        $connect->query($sql);
        $_SESSION['success'] = 'Адрес добавлен';
        echo'<script>location.href="?page=profile&addreses"</script>';
    }
}
?>

<h2>Мои адреса</h2>
<?php if(isset($error)): ?>
    <i style="color:red"><?=$error?></i>
<?php endif ?>
<form method="post">
    Город: <input type="text" name="city"><br>
    Улица: <input type="text" name="street"><br>
    Дом: <input type="text" name="home"><br>
    Квартира: <input type="text" name="appart"><br>
    <input type="submit" name="new_address">
</form>

<?php
    $sql = "SELECT * FROM address WHERE user_id = $UID";
    $addreses = $connect->query($sql);
    foreach($addreses as $address):
?>
    г. <?=$address['city']?>, ул.<?=$address['street']?>, д.<?=$address['home']?>
    <?php if($address['appart'] !== ''): ?>
        кв. <?=$address['home']?>
    <?php endif ?>
    <br>
<?php endforeach ?>