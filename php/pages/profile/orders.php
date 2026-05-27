<?php
    $stmt = $connect->prepare("
        SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS items_count
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$UID]);
    $orders = $stmt->fetchAll();

    $statusLabels = [
        'new'        => ['text' => 'Новый',       'cls' => 'status--new'],
        'processing' => ['text' => 'В обработке', 'cls' => 'status--processing'],
        'completed'  => ['text' => 'Выполнен',    'cls' => 'status--completed'],
        'cancelled'  => ['text' => 'Отменён',     'cls' => 'status--cancelled'],
    ];
?>

<div class="profile__grid" id="tab-orders">
    <div id="pfOrders" class="pf__orders">
        <?php if(empty($orders)): ?>
            <div class="pf__empty">
                <p>У вас пока нет заказов</p>
                <a href="?page=catalog" class="btn" style="margin-top:16px;display:inline-block;">Перейти в каталог</a>
            </div>
        <?php else: ?>
            <?php foreach($orders as $o):
                $st    = $statusLabels[$o['status']] ?? ['text' => $o['status'], 'cls' => ''];
                $total = number_format($o['total'], 0, '.', ' ') . ' ₽';
                $date  = date('d.m.Y', strtotime($o['created_at']));
            ?>
                <div class="pf__order-card">
                    <div class="pf__order-top">
                        <div>
                            <span class="pf__order-num">Заказ №<?=$o['id']?></span>
                            <span class="pf__order-date"><?=$date?></span>
                        </div>
                        <span class="pf__status <?=$st['cls']?>"><?=$st['text']?></span>
                    </div>
                    <?php if($o['address']): ?>
                        <p class="ord__card-addr"><?=$o['address']?></p>
                    <?php endif ?>
                    <div class="pf__order-bottom">
                        <span><?=$o['items_count']?> товар(а)</span>
                        <span class="pf__order-total"><?=$total?></span>
                    </div>
                </div>
            <?php endforeach ?>
        <?php endif ?>
    </div>
</div>
