<?php
    // удаление из желаний
    if(isset($_POST['remove_wish'])){
        $product_id = (int)$_POST['product_id'];
        $connect->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$UID, $product_id]);
        echo '<script>location.href="?page=profile&tab=wishlist"</script>';
        exit;
    }

    // добавление в корзину из желаний
    if(isset($_POST['wish_to_cart'])){
        $product_id = (int)$_POST['product_id'];
        $connect->prepare("
            INSERT INTO cart (user_id, product_id, quantity)
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE quantity = quantity + 1
        ")->execute([$UID, $product_id]);
        $_SESSION['success'] = 'Товар добавлен в корзину';
        echo '<script>location.href="?page=profile&tab=wishlist"</script>';
        exit;
    }

    $stmt = $connect->prepare("
        SELECT p.*, c.name AS category_name,
               (SELECT pi.image FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order ASC LIMIT 1) AS image
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$UID]);
    $items = $stmt->fetchAll();
?>

<div class="profile__grid" id="tab-wishlist">
    <div id="pfWishlist" class="pf__wish-preview">
        <?php if(empty($items)): ?>
            <div class="pf__empty">
                <p>Список желаний пуст</p>
                <a href="?page=catalog" class="btn" style="margin-top:16px;display:inline-block;">Перейти в каталог</a>
            </div>
        <?php else: ?>
            <div class="pf__wish-grid">
                <?php foreach($items as $p):
                    $price = number_format($p['price'], 0, '.', ' ') . ' ₽';
                    $image = $p['image'] ?: 'public/clava.png';
                ?>
                    <div class="pf__wish-item-wrap">
                        <a href="?page=item&id=<?=$p['id']?>" class="pf__wish-item">
                            <div class="pf__wish-img">
                                <img src="<?=$image?>" alt="<?=$p['name']?>" onerror="this.src='public/clava.png'">
                            </div>
                            <p class="pf__wish-name"><?=$p['name']?></p>
                            <p class="pf__wish-price"><?=$price?></p>
                        </a>
                        <div class="pf__wish-actions">
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?=$p['id']?>">
                                <button type="submit" name="wish_to_cart" class="btn pf__wish-cart">В корзину</button>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?=$p['id']?>">
                                <button type="submit" name="remove_wish" class="pf__wish-remove" title="Удалить">✕</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
</div>
