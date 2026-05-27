<h2>Каталог</h2>
<?php
    $sql = "SELECT * FROM products";
    $products = $connect->query($sql);
?>

<?php foreach($products as $product): ?>
    <div class="item">
        <p><img src="<?=$product['cover']?>" width="100px"></p>
        <h4><?=$product['name']?></h4>
        <?php if(isset($UID) && $USER['role'] == 'user'): ?>
            <?php
                $product_id = $product['id'];
                $sql = "SELECT * FROM busket WHERE product_id = $product_id AND user_id = $UID";
                $check = $connect->query($sql)->fetch();
                if($check):
            ?>
                <form method="post">
                    Товар в корзине (<?=$check['count']?>)
                    <input type="hidden" name="busket_id" value="<?=$check['id']?>">
                    <input type="hidden" name="count" value="<?=$check['count']?>">
                    <input type="submit" name="plus" value="+1">
                </form>
            <?php else: ?>
                <form method="post">
                    <input type="hidden" name="product_id" value="<?=$product['id']?>">
                    <input type="submit" name="basket" value="Добавить в корзину">
                </form>
            <?php endif ?>
        <?php endif ?>
        <hr>
    </div>
<?php endforeach ?>

<?php
    if(isset($_POST['basket'])){
        $product_id = $_POST['product_id'];
        $sql = "INSERT INTO busket (user_id,product_id,count)
            VALUES ('$UID','$product_id','1')";
        $connect->query($sql);
        $_SESSION['success'] = 'Товар добавлен в корзину';
        echo '<script>location.href="?page=catalog"</script>';
    }

    if(isset($_POST['plus'])){
        $busket_id = $_POST['busket_id'];
        $busket_count = $_POST['count'];
        $count = $busket_count + 1;
        $sql = "UPDATE busket SET count = $count WHERE id = $busket_id";
        $connect -> query($sql);
        $_SESSION['success'] = 'Товар добавлен';
        echo '<script>location.href="?page=catalog"</script>';
    }
?>