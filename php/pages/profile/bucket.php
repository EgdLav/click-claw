<?php
    // добавление в корзину (из каталога/главной)
    if(isset($_POST['add_to_cart'])){
        $product_id = (int)$_POST['product_id'];
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        $cur = $connect->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $cur->execute([$UID, $product_id]);
        $existing = $cur->fetchColumn();

        $stockStmt = $connect->prepare("SELECT stock FROM products WHERE id = ?");
        $stockStmt->execute([$product_id]);
        $stock = (int)$stockStmt->fetchColumn();

        $newQty = min(($existing ?: 0) + $qty, $stock);

        $connect->prepare("
            INSERT INTO cart (user_id, product_id, quantity)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = ?
        ")->execute([$UID, $product_id, $newQty, $newQty]);

        $_SESSION['success'] = 'Товар добавлен в корзину';
        echo '<script>location.href="?page=cart"</script>';
        exit;
    }

    // изменение количества
    if(isset($_POST['update_qty'])){
        $product_id = (int)$_POST['product_id'];
        $qty = (int)$_POST['quantity'];
        if($qty <= 0){
            $connect->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?")->execute([$UID, $product_id]);
        }else{
            $connect->prepare("
                INSERT INTO cart (user_id, product_id, quantity)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = ?
            ")->execute([$UID, $product_id, $qty, $qty]);
        }
        echo '<script>location.href="?page=cart"</script>';
        exit;
    }

    // удаление из корзины
    if(isset($_POST['remove_item'])){
        $product_id = (int)$_POST['product_id'];
        $connect->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?")->execute([$UID, $product_id]);
        $_SESSION['success'] = 'Товар удалён из корзины';
        echo '<script>location.href="?page=cart"</script>';
        exit;
    }

    // загрузка корзины
    $stmt = $connect->prepare("
        SELECT c.product_id, c.quantity,
               p.name, p.brand, p.price, p.stock,
               (SELECT pi.image FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order ASC LIMIT 1) AS image
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.id ASC
    ");
    $stmt->execute([$UID]);
    $items = $stmt->fetchAll();

    $itog = 0;
    foreach($items as $item){
        $itog += $item['price'] * $item['quantity'];
    }
?>

<main>
    <div class="container cart-page">
        <div class="wishlist-header">
            <h1>Корзина</h1>
        </div>

        <div class="cart-layout">
            <div class="cart-main">
                <div class="cart-card">
                    <?php if(empty($items)): ?>
                        <div style="padding:60px 20px;text-align:center;">
                            <p style="font-size:18px;margin-bottom:16px;">Корзина пуста</p>
                            <a href="?page=catalog" class="btn" style="display:inline-block;">Перейти в каталог</a>
                        </div>
                    <?php else: ?>
                        <?php foreach($items as $i => $item):
                            $image    = $item['image'] ?: 'public/clava.png';
                            $subtotal = number_format($item['price'] * $item['quantity'], 0, '.', ' ') . ' ₽';
                        ?>
                            <?php if($i > 0): ?><hr style="margin:12px 0;border:none;border-top:1px solid #eee;"><?php endif ?>
                            <div class="cart-item">
                                <div class="cart-item__img">
                                    <img src="<?=$image?>" alt="<?=$item['name']?>" onerror="this.src='public/clava.png'">
                                </div>
                                <div class="cart-item__info">
                                    <div class="cart-item__header">
                                        <div>
                                            <h3><?=$item['name']?></h3>
                                            <p class="item-desc"><?=$item['brand'] ?? ''?></p>
                                        </div>
                                        <span class="item-price"><?=$subtotal?></span>
                                    </div>
                                    <p class="item-stock">В наличии: <span class="red"><?=$item['stock']?> шт.</span></p>
                                    <div class="item-actions">
                                        <form method="post" style="display:flex;align-items:center;gap:8px;">
                                            <input type="hidden" name="product_id" value="<?=$item['product_id']?>">
                                            <div class="quantity-control">
                                                <button type="submit" name="update_qty" onclick="this.form.quantity.value=Math.max(1,<?=$item['quantity']?>-1)">−</button>
                                                <input type="number" name="quantity" value="<?=$item['quantity']?>" min="1" max="<?=$item['stock']?>" readonly>
                                                <button type="submit" name="update_qty" onclick="this.form.quantity.value=Math.min(<?=$item['stock']?>,<?=$item['quantity']?>+1)">+</button>
                                            </div>
                                        </form>
                                        <form method="post">
                                            <input type="hidden" name="product_id" value="<?=$item['product_id']?>">
                                            <button type="submit" name="remove_item" class="remove-item">
                                                <img src="public/adress-x.png" alt="Удалить">
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    <?php endif ?>
                </div>
            </div>

            <?php if(!empty($items)): ?>
            <aside class="cart-sidebar">
                <div class="delivery-promo">
                    <img src="public/car.png" alt="">
                    <p>Бесплатная доставка при первом заказе от 2000₽ и бесплатный возврат</p>
                </div>
                <div class="summary-card">
                    <h3>Краткое содержание</h3>
                    <div class="summary-row total-row">
                        <span>Итого</span>
                        <span><?=number_format($itog, 0, '.', ' ')?> ₽</span>
                    </div>
                    <div class="summary-row final-total">
                        <span>Общее</span>
                        <span><?=number_format($itog, 0, '.', ' ')?> ₽</span>
                    </div>
                    <a href="?page=create_order" class="checkout-btn">Перейти к оформлению заказа</a>
                </div>
            </aside>
            <?php endif ?>
        </div>
    </div>
</main>
