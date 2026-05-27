<h2>Каталог</h2>
<a href="?page=admin&create_product">Добавить</a>
<hr>
<?php
    $sql = "SELECT * FROM products";
    $products = $connect->query($sql);
?>

<?php foreach($products as $product): ?>
    <div class="item">
        <p><img src="<?=$product['cover']?>" width="100px"></p>
        <h4><?=$product['name']?></h4>
        <hr>
    </div>
<?php endforeach ?>