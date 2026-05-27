<?php
    // обновление профиля
    if(isset($_POST['update_profile'])){
        $name  = $_POST['name'];
        $phone = $_POST['phone'] ?? '';

        if(empty($name)){
            $error = 'Введите имя';
        }else{
            $stmt = $connect->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $UID]);
            $_SESSION['success'] = 'Профиль обновлён';
            echo '<script>location.href="?page=profile"</script>';
            exit;
        }
    }

    // последний заказ
    $lastOrderStmt = $connect->prepare("
        SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS items_count
        FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT 1
    ");
    $lastOrderStmt->execute([$UID]);
    $lastOrder = $lastOrderStmt->fetch();

    $statusLabels = [
        'new'        => 'Новый',
        'processing' => 'В обработке',
        'completed'  => 'Выполнен',
        'cancelled'  => 'Отменён',
    ];
?>

<main>
    <div class="container">
        <section class="profile">
            <div class="profile__top">
                <div class="profile__user-card">
                    <div class="profile__avatar">
                        <img src="public/avatar.png" alt="Аватар">
                    </div>
                    <button class="profile__edit-link" onclick="document.getElementById('editModal').style.display='flex'">
                        РЕДАКТИРОВАТЬ ПРОФИЛЬ <img src="public/edit_profile.png" alt="">
                    </button>
                    <div class="profile__divider"></div>
                    <form method="post">
                        <input type="submit" name="exit" value="Выйти" class="profile__logout-btn">
                    </form>
                </div>

                <div class="profile__vertical-line"></div>

                <div class="profile__welcome">
                    <h1>Привет, <?=$USER['name']?>!</h1>
                    <p>Добро пожаловать в ваш аккаунт. Проверяйте заказы, обновляйте список желаний и редактируйте свои данные.</p>
                </div>

                <div class="profile__last-order">
                    <h3>ВАШ ПОСЛЕДНИЙ ЗАКАЗ</h3>
                    <?php if($lastOrder): ?>
                        <div class="order-empty-card" style="text-align:left;">
                            <p><strong>Заказ №<?=$lastOrder['id']?></strong></p>
                            <p>Товаров: <?=$lastOrder['items_count']?> · Сумма: <?=number_format($lastOrder['total'], 0, '.', ' ')?> ₽</p>
                            <p>Статус: <?=$statusLabels[$lastOrder['status']] ?? $lastOrder['status']?></p>
                        </div>
                    <?php else: ?>
                        <div class="order-empty-card">
                            <p>У вас пока нет заказов.</p>
                            <a href="?page=catalog" class="order-search-link">ПЕРЕЙТИ В КАТАЛОГ</a>
                        </div>
                    <?php endif ?>
                </div>
            </div>

            <nav class="profile__tabs">
                <a href="?page=profile" class="tab-item <?=!isset($_GET['tab']) ? 'active' : ''?>">Мои данные</a>
                <a href="?page=profile&tab=orders" class="tab-item <?=isset($_GET['tab']) && $_GET['tab']=='orders' ? 'active' : ''?>">Мои заказы</a>
                <a href="?page=profile&tab=wishlist" class="tab-item <?=isset($_GET['tab']) && $_GET['tab']=='wishlist' ? 'active' : ''?>">Список желаний</a>
            </nav>

            <?php if(!isset($_GET['tab'])): ?>
                <?php include('php/pages/profile/profile_info.php'); ?>
            <?php elseif($_GET['tab'] == 'orders'): ?>
                <?php include('php/pages/profile/orders.php'); ?>
            <?php elseif($_GET['tab'] == 'wishlist'): ?>
                <?php include('php/pages/profile/wishlist.php'); ?>
            <?php endif ?>
        </section>
    </div>
</main>

<!-- модалка редактирования -->
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <button class="search-close" onclick="document.getElementById('editModal').style.display='none'">
            <img src="public/close.svg" alt="">
        </button>
        <h2 class="modal-title">Редактировать профиль</h2>
        <form method="post" class="auth-form">
            <?php if(isset($error)): ?>
                <i style="color:red"><?=$error?></i>
            <?php endif ?>
            <div class="input-group">
                <label>Имя *</label>
                <input type="text" name="name" value="<?=$USER['name']?>" required>
            </div>
            <div class="input-group">
                <label>Телефон</label>
                <input type="tel" name="phone" value="<?=$USER['phone'] ?? ''?>" placeholder="+7 (___) ___-__-__">
            </div>
            <div class="auth-button">
                <button type="submit" name="update_profile" class="btn-login">Сохранить</button>
            </div>
        </form>
    </div>
</div>
