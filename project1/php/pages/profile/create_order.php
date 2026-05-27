<?php 
    $sql = "SELECT products.name, products.price, busket.* 
        FROM busket JOIN products ON busket.product_id = products.id
        WHERE user_id = $UID";
    $baskets = $connect->query($sql);
?>

<h1>Оформление заказа</h1>

<h2>Детали заказа</h2>
<?php $itog = 0 ?>
<?php foreach($baskets as $basket): ?>
    <h4><?=$basket['name']?></h4>
    Количество :<?=$basket['count']?>
    <p>Цена товара: <?=$basket['price']?> Р.</p>
    <p>Общая стоимость: <?php echo($basket['count'] * $basket['price'])?> р.</p>
    <?php $itog = $itog + ($basket['count'] * $basket['price'])?>
    <hr>
<?php endforeach ?>

<h3>Итого <?php echo number_format($itog, 0, '', ' ');?> р</h3>
<?php
    if(isset($_POST['new_order'])){
        $address = $_POST['address'];
        
        if($address == 0 || empty($_POST['pay'])){
            $error = 'Введите все данные';
        }else{
            $pay = $_POST['pay'];
            $date = date('d.m.Y H:i:s');
            // создать заказ
            $sql = "INSERT INTO orders (user_id,status,date,address_id,pay)
                VALUES ('$UID','1','$date','$address','$pay')";
            $connect->query($sql);

            // узнать айди заказа
            $order_id = $connect->lastInsertId();

            // цикл всех товаров в корзине
            $sql = "SELECT products.name, products.price, busket.* 
                FROM busket JOIN products ON busket.product_id = products.id
                WHERE user_id = $UID";
            $baskets = $connect->query($sql);
            foreach($baskets as $basket){
                $product_id = $basket['product_id'];
                $count = $basket['count'];
                $price = $basket['price'];
                // создать элемент заказа
                $sql = "INSERT INTO orders_item (order_id,product_id,count,price)
                    VALUES ('$order_id','$product_id','$count','$price')";
                $connect->query($sql);
                // удаление товара из корзины
                $basket_id = $basket['id'];
                $sql = "DELETE FROM busket WHERE id = $basket_id";
                $connect->query($sql);
            }
                
            // завершение
            $_SESSION['success'] = 'Заказ оформлен';
            echo'<script>location.href="?page=profile&orders"</script>';
        }

    }
?>
<?php if(isset($error)): ?>
    <i style="color:red"><?=$error?></i>
<?php endif ?>
<form method="post">
    Выберите адрес
    <select name="address">
        <option value="0">-- Выберите адрес --</option>
        <?php
            $sql = "SELECT * FROM address WHERE user_id = $UID";    
            $addreses = $connect->query($sql);
            foreach($addreses as $address):
        ?>
            <option value="<?=$address['id']?>">
                г. <?=$address['city']?>, ул.<?=$address['street']?>, д.<?=$address['home']?>
                <?php if($address['appart'] !== ''): ?>
                    кв. <?=$address['appart']?>
                <?php endif ?>
            </option>
        <?php endforeach ?>
    </select><br><br>
    Способ оплаты:
    <input type="radio" name="pay" value="sbp"> - СБП
    <input type="radio" name="pay" value="cash"> - Наличные
    <input type="radio" name="pay" value="card"> - Картой
    <br><br>
    <input type="submit" name="new_order" value="Заказать">
</form>