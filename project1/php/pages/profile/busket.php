<h1>Корзина</h1>

<?php 
    $sql = "SELECT products.name, products.price, busket.* 
        FROM busket JOIN products ON busket.product_id = products.id
        WHERE user_id = $UID";
    $baskets = $connect->query($sql);

    if(isset($_POST['minus'])){
        $id = $_POST['basket_id'];
        $count = $_POST['count'];
        if($count == 1){
            // удаление
            $sql = "DELETE FROM busket WHERE id = $id";
            $connect->query($sql);
            $_SESSION['success'] = 'Товар удален из корзины';
        }else{
            // уменьшение
            $count = $count - 1;
            $sql = "UPDATE busket SET count = $count WHERE id = $id";
            $connect->query($sql);
        }
        echo'<script>location.href="?page=busket"</script>';
    }

    if(isset($_POST['plus'])){
        $busket_id = $_POST['basket_id'];
        $busket_count = $_POST['count'];
        $count = $busket_count + 1;
        $sql = "UPDATE busket SET count = $count WHERE id = $busket_id";
        $connect -> query($sql);
        echo '<script>location.href="?page=busket"</script>';
    }
?>
<?php $itog = 0 ?>
<?php foreach($baskets as $basket): ?>
    <h4><?=$basket['name']?></h4>
    <div style="display:flex;gap:10px">
        <form method="post">
            <input type="hidden" name="basket_id" value="<?=$basket['id']?>">
            <input type="hidden" name="count" value="<?=$basket['count']?>">
            <input type="submit" name="minus" value="-">
        </form>
        <div>
            <?=$basket['count']?>
        </div>
        <form method="post">
            <input type="hidden" name="basket_id" value="<?=$basket['id']?>">
            <input type="hidden" name="count" value="<?=$basket['count']?>">
            <input type="submit" name="plus" value="+">
        </form>
    </div>
    <p>Цена товара: <?=$basket['price']?> Р.</p>
    <p>Общая стоимость: <?php echo($basket['count'] * $basket['price'])?> р.</p>
    <?php $itog = $itog + ($basket['count'] * $basket['price'])?>
    <hr>
<?php endforeach ?>

<h3>Итого <?php echo number_format($itog, 0, '', ' ');?> р</h3>

<hr>
<h2><a href="?page=create_order">Оформить заказ</a></h2>