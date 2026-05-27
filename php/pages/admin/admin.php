<?php
    // статистика
    $totalProducts  = $connect->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totalCats      = $connect->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $totalOrders    = $connect->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $revenue        = $connect->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
    $recentOrders   = $connect->query("
        SELECT o.*, u.name AS user_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ")->fetchAll();

    $statusLabels = [
        'new'        => ['text' => 'Новый',       'cls' => 'admin__badge--new'],
        'processing' => ['text' => 'В обработке', 'cls' => 'admin__badge--processing'],
        'completed'  => ['text' => 'Выполнен',    'cls' => 'admin__badge--completed'],
        'cancelled'  => ['text' => 'Отменён',     'cls' => 'admin__badge--cancelled'],
    ];
?>

<div class="admin">
    <?php include('php/components/admin_sidebar.php'); ?>

    <main class="admin__main">
        <div class="admin__header">
            <div>
                <h1 class="admin__title">Админ-панель</h1>
                <p class="admin__breadcrumbs">
                    <a href="?">Главная</a> › Админ-панель
                </p>
            </div>
            <a href="?page=admin&section=create_product" class="admin__btn">Добавить товар</a>
        </div>

        <div class="admin__stats">
            <div class="admin__stat-card" onclick="location.href='?page=admin&section=catalog'">
                <div class="admin__stat-card-label">Всего товаров</div>
                <div class="admin__stat-card-value"><?=$totalProducts?></div>
            </div>
            <div class="admin__stat-card" onclick="location.href='?page=admin&section=category'">
                <div class="admin__stat-card-label">Категорий</div>
                <div class="admin__stat-card-value"><?=$totalCats?></div>
            </div>
            <div class="admin__stat-card" onclick="location.href='?page=admin&section=orders'">
                <div class="admin__stat-card-label">Заказов</div>
                <div class="admin__stat-card-value"><?=$totalOrders?></div>
            </div>
            <div class="admin__stat-card">
                <div class="admin__stat-card-label">Выручка</div>
                <div class="admin__stat-card-value"><?=number_format($revenue, 0, '.', ' ')?> ₽</div>
            </div>
        </div>

        <div class="admin__table-wrapper">
            <div class="admin__table-header">
                <h2 class="admin__table-title">Последние заказы</h2>
                <a href="?page=admin&section=orders" class="admin__btn admin__btn-outline admin__btn-sm">Все заказы</a>
            </div>
            <table class="admin__table">
                <thead>
                    <tr>
                        <th>№ заказа</th>
                        <th>Клиент</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($recentOrders)): ?>
                        <tr><td colspan="6" style="text-align:center;color:#888;padding:30px;">Заказов пока нет</td></tr>
                    <?php else: ?>
                        <?php foreach($recentOrders as $o):
                            $st    = $statusLabels[$o['status']] ?? ['text' => $o['status'], 'cls' => ''];
                            $total = number_format($o['total'], 0, '.', ' ') . ' ₽';
                            $date  = date('d.m.Y', strtotime($o['created_at']));
                        ?>
                            <tr>
                                <td>#<?=$o['id']?></td>
                                <td><?=$o['user_name'] ?? $o['name']?></td>
                                <td><?=$total?></td>
                                <td><span class="admin__badge <?=$st['cls']?>"><?=$st['text']?></span></td>
                                <td><?=$date?></td>
                                <td class="admin__actions">
                                    <?php if($o['status'] == 'new'): ?>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?=$o['id']?>">
                                            <input type="hidden" name="new_status" value="processing">
                                            <button type="submit" name="update_order_status" class="admin__btn admin__btn-sm admin__btn-success">Принять</button>
                                        </form>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?=$o['id']?>">
                                            <input type="hidden" name="new_status" value="cancelled">
                                            <button type="submit" name="update_order_status" class="admin__btn admin__btn-sm admin__btn-danger">Отклонить</button>
                                        </form>
                                    <?php elseif($o['status'] == 'processing'): ?>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?=$o['id']?>">
                                            <input type="hidden" name="new_status" value="completed">
                                            <button type="submit" name="update_order_status" class="admin__btn admin__btn-sm admin__btn-success">Завершить</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color:#888;font-size:13px;"><?=$st['text']?></span>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
