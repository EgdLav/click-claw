<?php
    $errors = [];
    $editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // загрузка данных для редактирования
    $p = [];
    if($editId){
        $stmt = $connect->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$editId]);
        $p = $stmt->fetch();
        if(!$p){ include('php/components/error.php'); return; }

        $imgStmt = $connect->prepare("SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
        $imgStmt->execute([$editId]);
        $currentImages = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
    }else{
        $currentImages = [];
    }

    // сохранение
    if(isset($_POST['save_product'])){
        $name       = $_POST['name']        ?? '';
        $brand      = $_POST['brand']       ?? '';
        $desc       = $_POST['description'] ?? '';
        $price      = $_POST['price']       ?? '';
        $category   = (int)($_POST['category_id'] ?? 0);
        $stock      = (int)($_POST['stock']  ?? 0);
        $badge      = $_POST['badge']       ?? '';
        $imagesStr  = $_POST['images']      ?? '';

        if(empty($name))  $errors['name']     = 'Введите название';
        if(empty($price)) $errors['price']    = 'Введите цену';
        if(!$category)    $errors['category'] = 'Выберите категорию';

        if(empty($errors)){
            $images = array_filter(array_map('trim', explode(',', $imagesStr)));

            if($editId){
                $stmt = $connect->prepare("
                    UPDATE products SET name=?, brand=?, description=?, price=?, category_id=?, stock=?, badge=?
                    WHERE id=?
                ");
                $stmt->execute([$name, $brand, $desc, $price, $category, $stock, $badge, $editId]);
                $connect->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$editId]);
                $imgStmt = $connect->prepare("INSERT INTO product_images (product_id, image, sort_order) VALUES (?,?,?)");
                foreach($images as $i => $img){ $imgStmt->execute([$editId, $img, $i]); }
                $_SESSION['success'] = 'Товар обновлён';
            }else{
                $stmt = $connect->prepare("
                    INSERT INTO products (name, brand, description, price, category_id, stock, badge)
                    VALUES (?,?,?,?,?,?,?)
                ");
                $stmt->execute([$name, $brand, $desc, $price, $category, $stock, $badge]);
                $newId = (int)$connect->lastInsertId();
                $imgStmt = $connect->prepare("INSERT INTO product_images (product_id, image, sort_order) VALUES (?,?,?)");
                foreach($images as $i => $img){ $imgStmt->execute([$newId, $img, $i]); }
                $_SESSION['success'] = 'Товар добавлен';
            }
            echo '<script>location.href="?page=admin&section=catalog"</script>';
            exit;
        }
    }

    // удаление товара
    if(isset($_POST['delete_product']) && $editId){
        $connect->prepare("DELETE FROM products WHERE id = ?")->execute([$editId]);
        $_SESSION['success'] = 'Товар удалён';
        echo '<script>location.href="?page=admin&section=catalog"</script>';
        exit;
    }

    $cats = $connect->query("SELECT * FROM categories ORDER BY name ASC");
?>

<div class="admin">
    <?php include('php/components/admin_sidebar.php'); ?>

    <main class="admin__main">
        <div class="admin__header">
            <div>
                <h1 class="admin__title"><?=$editId ? 'Редактировать товар' : 'Добавить товар'?></h1>
                <p class="admin__breadcrumbs">
                    <a href="?page=admin">Админ-панель</a> › <a href="?page=admin&section=catalog">Товары</a> › <?=$editId ? 'Редактировать' : 'Добавить'?>
                </p>
            </div>
        </div>

        <form method="post" class="admin__form">
            <div class="admin__form-group">
                <label class="admin__form-label">Название *</label>
                <input type="text" name="name" class="admin__form-input" value="<?=$p['name'] ?? ''?>" required>
                <?php if(isset($errors['name'])): ?>
                    <i style="color:red"><?=$errors['name']?></i>
                <?php endif ?>
            </div>
            <div class="admin__form-group">
                <label class="admin__form-label">Бренд</label>
                <input type="text" name="brand" class="admin__form-input" value="<?=$p['brand'] ?? ''?>" placeholder="Например: Logitech">
            </div>
            <div class="admin__form-group">
                <label class="admin__form-label">Категория *</label>
                <select name="category_id" class="admin__form-select" required>
                    <option value="0">Выберите категорию</option>
                    <?php foreach($cats as $cat): ?>
                        <option value="<?=$cat['id']?>" <?=isset($p['category_id']) && $p['category_id']==$cat['id'] ? 'selected' : ''?>>
                            <?=$cat['name']?>
                        </option>
                    <?php endforeach ?>
                </select>
                <?php if(isset($errors['category'])): ?>
                    <i style="color:red"><?=$errors['category']?></i>
                <?php endif ?>
            </div>
            <div class="admin__form-group">
                <label class="admin__form-label">Цена (₽) *</label>
                <input type="number" name="price" class="admin__form-input" value="<?=$p['price'] ?? ''?>" min="0" required>
                <?php if(isset($errors['price'])): ?>
                    <i style="color:red"><?=$errors['price']?></i>
                <?php endif ?>
            </div>
            <div class="admin__form-group">
                <label class="admin__form-label">Количество на складе</label>
                <input type="number" name="stock" class="admin__form-input" value="<?=$p['stock'] ?? 0?>" min="0">
            </div>
            <div class="admin__form-group">
                <label class="admin__form-label">Бейдж (необязательно)</label>
                <input type="text" name="badge" class="admin__form-input" value="<?=$p['badge'] ?? ''?>" placeholder="Новинка / Хит">
            </div>
            <div class="admin__form-group">
                <label class="admin__form-label">Описание</label>
                <textarea name="description" class="admin__form-textarea"><?=$p['description'] ?? ''?></textarea>
            </div>
            <div class="admin__form-group">
                <label class="admin__form-label">Изображения (через запятую)</label>
                <input type="text" name="images" class="admin__form-input"
                       value="<?=implode(',', $currentImages)?>"
                       placeholder="/public/clava.png,/public/mouse.png">
                <p style="font-size:12px;color:#888;margin-top:4px;">Пути к файлам через запятую</p>
            </div>
            <div class="admin__form-actions">
                <button type="submit" name="save_product" class="admin__btn">
                    <?=$editId ? 'Сохранить изменения' : 'Добавить товар'?>
                </button>
                <a href="?page=admin&section=catalog" class="admin__btn admin__btn-outline">Отмена</a>
                <?php if($editId): ?>
                    <button type="submit" name="delete_product" class="admin__btn admin__btn-danger"
                            onclick="return confirm('Удалить товар навсегда?')">Удалить товар</button>
                <?php endif ?>
            </div>
        </form>
    </main>
</div>
