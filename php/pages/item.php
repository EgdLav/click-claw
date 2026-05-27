<?php
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if(!$id){ include('php/components/error.php'); return; }

    $stmt = $connect->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $p = $stmt->fetch();

    if(!$p){ include('php/components/error.php'); return; }

    // изображения
    $imgStmt = $connect->prepare("SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
    $imgStmt->execute([$id]);
    $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
    if(empty($images) && $p['image']) $images = [$p['image']];
    if(empty($images)) $images = ['public/clava.png'];

    $price = number_format($p['price'], 0, '.', ' ') . ' ₽';

    // добавление в корзину
    if(isset($_POST['add_to_cart']) && isset($UID)){
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        $cur = $connect->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $cur->execute([$UID, $id]);
        $existing = $cur->fetchColumn();
        $newQty = min(($existing ?: 0) + $qty, (int)$p['stock']);
        $connect->prepare("
            INSERT INTO cart (user_id, product_id, quantity)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = ?
        ")->execute([$UID, $id, $newQty, $newQty]);
        $_SESSION['success'] = 'Товар добавлен в корзину';
        echo '<script>location.href="?page=item&id=' . $id . '"</script>';
        exit;
    }

    // добавление в список желаний
    if(isset($_POST['toggle_wish']) && isset($UID)){
        $check = $connect->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check->execute([$UID, $id]);
        if($check->fetch()){
            $connect->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$UID, $id]);
        }else{
            $connect->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)")->execute([$UID, $id]);
        }
        echo '<script>location.href="?page=item&id=' . $id . '"</script>';
        exit;
    }

    // проверяем в желаниях ли товар
    $inWishlist = false;
    if(isset($UID)){
        $wCheck = $connect->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $wCheck->execute([$UID, $id]);
        $inWishlist = (bool)$wCheck->fetch();
    }
?>

<main>
    <div class="container product-page">
        <div class="product-main">
            <div>
                <div class="product-gallery">
                    <div class="thumbnails">
                        <?php foreach($images as $i => $src): ?>
                            <img src="<?=$src?>" alt="<?=$p['name']?>" class="<?=$i === 0 ? 'active' : ''?>"
                                 onerror="this.src='public/clava.png'">
                        <?php endforeach ?>
                    </div>
                    <div class="main-image">
                        <img src="<?=$images[0]?>" alt="<?=$p['name']?>" onerror="this.src='public/clava.png'">
                    </div>
                </div>
            </div>

            <aside class="product-sidebar">
                <h1 class="product-title-3"><?=$p['name']?></h1>
                <p class="product-short-desc"><?=$p['description'] ?? ''?></p>
                <div class="product-price-3"><?=$price?></div>
                <p class="stock-status">В наличии: <span class="count"><?=$p['stock']?> шт.</span></p>

                <?php if(isset($UID)): ?>
                    <form method="post" class="purchase-controls">
                        <div class="quantity-control">
                            <button type="button" onclick="this.nextElementSibling.value=Math.max(1,+this.nextElementSibling.value-1)">−</button>
                            <input type="number" name="quantity" value="1" min="1" max="<?=$p['stock']?>">
                            <button type="button" onclick="this.previousElementSibling.value=Math.min(<?=$p['stock']?>,+this.previousElementSibling.value+1)">+</button>
                        </div>
                        <button type="submit" name="add_to_cart" class="btn-primary add-to-cart">Добавить в корзину</button>
                    </form>
                    <form method="post">
                        <button type="submit" name="toggle_wish" class="wishlist-toggle <?=$inWishlist ? 'wishlist-toggle--active' : ''?>">
                            <?=$inWishlist ? '♥ В желаниях' : '♡ В желания'?>
                        </button>
                    </form>
                <?php else: ?>
                    <div class="purchase-controls">
                        <a href="?page=login" class="btn-primary add-to-cart">Добавить в корзину</a>
                        <a href="?page=login" class="wishlist-toggle">♡ В желания</a>
                    </div>
                <?php endif ?>

                <div class="info-badges">
                    <div class="badge">
                        <img src="public/guarantee-2.png" alt="">
                        <div>
                            <strong>Гарантия возврата денег</strong>
                            <p>С возможностью легкого возврата</p>
                        </div>
                    </div>
                    <div class="badge">
                        <img src="public/card-2.png" alt="">
                        <div>
                            <strong>Безопасная оплата</strong>
                            <p>Покупки всегда безопасные и надежные</p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<script>
    // переключение главного изображения по клику на миниатюру
    document.querySelectorAll('.thumbnails img').forEach(thumb => {
        thumb.addEventListener('click', () => {
            document.querySelector('.main-image img').src = thumb.src;
            document.querySelectorAll('.thumbnails img').forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
        });
    });
</script>
