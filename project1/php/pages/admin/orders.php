<?php
    $sql = "SELECT 
                orders.*, 
                address.city, 
                address.street, 
                address.home, 
                address.appart, 
                users.name, 
                users.email, 
                statuses.value
            FROM orders 
            JOIN address ON orders.address_id = address.id
            JOIN users ON orders.user_id = users.id
            JOIN statuses ON orders.status = statuses.id";
            
    $orders = $connect->query($sql);

    if(isset($_POST['update_status'])){
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];
        $sql = "UPDATE orders SET status = '$status' WHERE id = '$order_id'";
        $connect->query($sql);
        $_SESSION['success'] = 'Статус заказа изменен';
        echo '<script>location.href="?page=admin&orders"</script>';
    }
?>


<h2>Заказы</h2>

<?php foreach($orders as $order):?>
    id: <?=$order['id']?><br>
    status: <?=$order['value']?><br>
    date: <?=$order['date']?><br>
    Адрес доставки: 
    г. <?=$order['city']?>, ул.<?=$order['street']?>, д.<?=$order['home']?>
    <?php if($order['appart'] !== ''): ?>
        кв. <?=$order['home']?>
    <?php endif ?>
    <br>
    Покупатель: <?=$order['name']?> <?=$order['email']?>
    <hr>
    Содержимое заказа:<br>
    <?php $itog = 0 ?>
    <?php
        $order_id = $order['id'];
        $sql = "SELECT orders_item.*, products.name 
                FROM orders_item JOIN products ON orders_item.product_id = products.id
                WHERE orders_item.order_id = $order_id";
        $products = $connect->query($sql);
        foreach($products as $product):
    ?>
        Название: <?=$product['name']?>
        Стоимость: <?=$product['price']?>
        Количество: <?=$product['count']?>
        Общая стоимость: <?php echo($product['count'] * $product['price'])?> р.
        <?php $itog = $itog + ($product['count'] * $product['price'])?>
        <br>
    <?php endforeach ?>
    <h3>Итого <?php echo number_format($itog, 0, '', ' ');?> р</h3>
    
    <form method="post">
        <input type="hidden" name="order_id" value="<?=$order_id?>">
        <select name="status">  
            <?php
                $sql = "SELECT * FROM statuses";
                $statuses = $connect->query($sql);
                foreach($statuses as $status):
            ?>
                <option 
                    value="<?=$status['id']?>"
                    <?php if($order['status'] == $status['id']) {echo 'selected';} ?>
                >
                    <?=$status['value']?>
                </option>
            <?php endforeach ?>
        </select>
        <input type="submit" name="update_status">
    </form>
    <hr>
<?php endforeach ?>