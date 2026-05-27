<?php
    // обновление статуса
    if(isset($_POST['update_order_status'])){
        $order_id  = (int)$_POST['order_id'];
        $newStatus = $_POST['new_status'];
        $allowed   = ['new', 'processing', 'completed', 'cancelled'];
        if(in_array($newStatus, $allowed)){
            $connect->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $order_id]);
            $_SESSION['success'] = 'Статус заказа изменён';
        }
        echo '<script>location.href="?page=admin&section=orders"</script>';
        exit;
    }

    // фильтр по статусу
    $filterStatus = $_GET['status'] ?? '';
    if($filterStatus){
        $stmt = $connect->prepare("
            SELECT o.*, u.name AS user_name, u.email AS user_email,
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS items_count
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.status = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$filterStatus]);
    }else{
        $stmt = $connect->query("
            SELECT o.*, u.name AS user_name, u.email AS user_email,
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS items_count
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
        ");
    }
    $orders = $stmt->fetchAll();

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
                <h1 class="admin__title">Заказы</h1>
                <p class="admin__breadcrumbs">
                    <a href="?page=admin">Админ-панель</a> › Заказы
                </p>
            </div>
            <select class="admin__form-select" style="width:auto;padding:12px 20px;"
                    onchange="location.href='?page=admin&section=orders&status='+this.value">
                <option value="" <?=!$filterStatus ? 'selected' : ''?>>Все заказы</option>
                <option value="new"        <?=$filterStatus=='new'        ? 'selected' : ''?>>Новые</option>
                <option value="processing" <?=$filterStatus=='processing' ? 'selected' : ''?>>В обработке</option>
                <option value="completed"  <?=$filterStatus=='completed'  ? 'selected' : ''?>>Выполненные</option>
                <option value="cancelled"  <?=$filterStatus=='cancelled'  ? 'selected' : ''?>>Отменённые</option>
            </select>
        </div>

        <div class="admin__table-wrapper">
            <div class="admin__table-header">
                <h2 class="admin__table-title">Список заказов (<?=count($orders)?>)</h2>
            </div>
            <table class="admin__table">
                <thead>
                    <tr>
                        <th>№ заказа</th>
                        <th>Клиент</th>
                        <th>Товары</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orders)): ?>
                        <tr><td colspan="7" style="text-align:center;padding:30px;color:#888;">Заказов нет</td></tr>
                    <?php else: ?>
                        <?php foreach($orders as $o):
                            $st    = $statusLabels[$o['status']] ?? ['text' => $o['status'], 'cls' => ''];
                            $total = number_format($o['total'], 0, '.', ' ') . ' ₽';
                            $date  = date('d.m.Y', strtotime($o['created_at']));
                        ?>
                            <tr>
                                <td>#<?=$o['id']?></td>
                                <td>
                                    <strong><?=$o['user_name'] ?? $o['name']?></strong><br>
                                    <small style="color:#888;"><?=$o['phone']?></small>
                                </td>
                                <td><?=$o['items_count']?> шт.</td>
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
