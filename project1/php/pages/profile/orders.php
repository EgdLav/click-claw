<?php
    $sql = "SELECT 
                orders.*, 
                address.city, 
                address.street, 
                address.home, 
                address.appart,
                statuses.value
            FROM orders 
            JOIN address ON orders.address_id = address.id
            JOIN statuses ON orders.status = statuses.id
            WHERE orders.user_id = $UID";
    $orders = $connect->query($sql);
?>

<h2>Мои заказы</h2>

<?php foreach($orders as $order):?>
    id: <?=$order['id']?><br>
    status: <?=$order['value']?><br>
    date: <?=$order['date']?><br>
    Адрес доставки: 
    г. <?=$order['city']?>, ул.<?=$order['street']?>, д.<?=$order['home']?>
    <?php if($order['appart'] !== ''): ?>
        кв. <?=$order['home']?>
    <?php endif ?>
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
    <hr>
<?php endforeach ?>