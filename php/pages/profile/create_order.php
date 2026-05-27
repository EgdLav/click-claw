<?php
    // загрузка корзины
    $stmt = $connect->prepare("
        SELECT c.product_id, c.quantity, p.name, p.price, p.stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$UID]);
    $items = $stmt->fetchAll();

    if(empty($items)){
        $_SESSION['error'] = 'Корзина пуста';
        echo '<script>location.href="?page=cart"</script>';
        exit;
    }

    $itog = 0;
    foreach($items as $item){
        $itog += $item['price'] * $item['quantity'];
    }

    // оформление заказа
    if(isset($_POST['new_order'])){
        $phone   = $_POST['phone']   ?? '';
        $email   = $_POST['email']   ?? '';
        $city    = $_POST['city']    ?? '';
        $street  = $_POST['street']  ?? '';
        $house   = $_POST['house']   ?? '';
        $apt     = $_POST['apt']     ?? '';
        $entrance = $_POST['entrance'] ?? '';

        if(empty($phone) || empty($city) || empty($street) || empty($house)){
            $error = 'Заполните все обязательные поля';
        }else{
            $address = "г. $city, ул. $street, д. $house";
            if($entrance) $address .= ", подъезд $entrance";
            if($apt)      $address .= ", кв. $apt";

            // создаём заказ
            $orderStmt = $connect->prepare("
                INSERT INTO orders (user_id, name, phone, email, address, total)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $orderStmt->execute([$UID, $USER['name'], $phone, $email, $address, $itog]);
            $order_id = (int)$connect->lastInsertId();

            // позиции заказа
            $itemStmt = $connect->prepare("
                INSERT INTO order_items (order_id, product_id, name, price, quantity)
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach($items as $item){
                $itemStmt->execute([$order_id, $item['product_id'], $item['name'], $item['price'], $item['quantity']]);
            }

            // очищаем корзину
            $connect->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$UID]);

            $_SESSION['success'] = 'Заказ оформлен';
            echo '<script>location.href="?page=profile&tab=orders"</script>';
            exit;
        }
    }
?>

<main class="make">
    <div class="container cart-page">
        <h1 class="cart-title">Оформление заказа</h1>

        <div class="order-info_layout">
            <div class="checkout-form">
                <?php if(isset($error)): ?>
                    <i style="color:red"><?=$error?></i>
                <?php endif ?>
                <form method="post">
                    <div class="form-section">
                        <h2 class="form-title">Данные покупателя</h2>
                        <div class="form-row">
                            <div class="input-container">
                                <label>Телефон *</label>
                                <input type="tel" name="phone" placeholder="+7 (___) ___-__-__" value="<?=$USER['phone'] ?? ''?>" required>
                            </div>
                            <div class="input-container">
                                <label>Почта</label>
                                <input type="email" name="email" placeholder="example@mail.ru" value="<?=$USER['email']?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2 class="form-title">Доставка по адресу</h2>
                        <div class="form-row">
                            <div class="input-container">
                                <label>Город *</label>
                                <input type="text" name="city" placeholder="Казань" required>
                            </div>
                            <div class="input-container">
                                <label>Улица *</label>
                                <input type="text" name="street" placeholder="ул. Пушкина" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="input-container">
                                <label>Дом *</label>
                                <input type="text" name="house" placeholder="1" required>
                            </div>
                            <div class="input-container">
                                <label>Подъезд</label>
                                <input type="text" name="entrance" placeholder="2">
                            </div>
                            <div class="input-container">
                                <label>Квартира</label>
                                <input type="text" name="apt" placeholder="42">
                            </div>
                        </div>
                    </div>

                    <div class="make_order-card" id="make_pay_card">
                        <h2 class="form-title">Товары в заказе</h2>
                        <?php foreach($items as $item):
                            $subtotal = number_format($item['price'] * $item['quantity'], 0, '.', ' ') . ' ₽';
                        ?>
                            <div class="cart-item make-order_item-header">
                                <div class="cart-item__info">
                                    <div class="cart-item__header make-order_item-header">
                                        <div>
                                            <h3><?=$item['name']?></h3>
                                            <p style="margin-top:8px;color:#888;"><?=$item['quantity']?> шт.</p>
                                        </div>
                                        <span class="item-price"><?=$subtotal?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <div class="summary-card" style="margin-top:20px;">
                        <h3>Краткое содержание</h3>
                        <div class="summary-row total-row">
                            <span>Итого</span>
                            <span><?=number_format($itog, 0, '.', ' ')?> ₽</span>
                        </div>
                        <div class="summary-row final-total">
                            <span>Общее</span>
                            <span><?=number_format($itog, 0, '.', ' ')?> ₽</span>
                        </div>
                        <button type="submit" name="new_order" class="btn">Оплатить</button>
                    </div>
                </form>
            </div>

            <div class="make_order-badges">
                <div class="badge">
                    <img src="public/car.png" alt="">
                    <div>
                        <strong>Бесплатная доставка</strong>
                        <p>при первом заказе от 2000₽</p>
                    </div>
                </div>
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
        </div>
    </div>
</main>
