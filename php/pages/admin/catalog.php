<?php
    // удаление товара
    if(isset($_POST['delete_product'])){
        $id = (int)$_POST['product_id'];
        $connect->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        $_SESSION['success'] = 'Товар удалён';
        echo '<script>location.href="?page=admin&section=catalog"</script>';
        exit;
    }

    $search = $_POST['search'] ?? '';
    if($search){
        $stmt = $connect->prepare("
            SELECT p.*, c.name AS category_name,
                   (SELECT pi.image FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order ASC LIMIT 1) AS image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.name LIKE ? OR p.brand LIKE ?
            ORDER BY p.id DESC
        ");
        $stmt->execute(["%$search%", "%$search%"]);
    }else{
        $stmt = $connect->query("
            SELECT p.*, c.name AS category_name,
                   (SELECT pi.image FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order ASC LIMIT 1) AS image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.id DESC
        ");
    }
    $products = $stmt->fetchAll();
?>

<div class="admin">
    <?php include('php/components/admin_sidebar.php'); ?>

    <main class="admin__main">
        <div class="admin__header">
            <div>
                <h1 class="admin__title">Товары</h1>
                <p class="admin__breadcrumbs">
                    <a href="?page=admin">Админ-панель</a> › Товары
                </p>
            </div>
            <a href="?page=admin&section=create_product" class="admin__btn">Добавить товар</a>
        </div>

        <div class="admin__table-wrapper">
            <div class="admin__table-header">
                <h2 class="admin__table-title">Список товаров (<?=count($products)?>)</h2>
                <form method="post">
                    <input type="text" name="search" class="admin__form-input" style="max-width:250px;" placeholder="Поиск..." value="<?=$search?>">
                </form>
            </div>
            <table class="admin__table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Изображение</th>
                        <th>Название</th>
                        <th>Категория</th>
                        <th>Цена</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($products)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:30px;color:#888;">Товары не найдены</td></tr>
                    <?php else: ?>
                        <?php foreach($products as $p):
                            $img   = $p['image'] ?: 'public/clava.png';
                            $price = number_format($p['price'], 0, '.', ' ') . ' ₽';
                        ?>
                            <tr>
                                <td><?=$p['id']?></td>
                                <td><img src="<?=$img?>" style="width:40px;height:40px;object-fit:contain;" alt="" onerror="this.src='public/clava.png'"></td>
                                <td><?=$p['name']?></td>
                                <td><?=$p['category_name'] ?? '—'?></td>
                                <td><?=$price?></td>
                                <td class="admin__actions">
                                    <a href="?page=admin&section=edit_product&id=<?=$p['id']?>" class="admin__btn admin__btn-sm admin__btn-outline">
                                        <img src="public/edit_profile.png" alt="Ред.">
                                    </a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Удалить товар?')">
                                        <input type="hidden" name="product_id" value="<?=$p['id']?>">
                                        <button type="submit" name="delete_product" class="admin__btn admin__btn-sm admin__btn-danger">
                                            <img src="public/x-black.svg" alt="Удалить">
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
