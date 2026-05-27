<?php
    // добавление категории
    if(isset($_POST['add_category'])){
        $name = trim($_POST['name'] ?? '');
        if(empty($name)){
            $cat_error = 'Введите название';
        }else{
            $connect->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$name]);
            $_SESSION['success'] = 'Категория добавлена';
            echo '<script>location.href="?page=admin&section=category"</script>';
            exit;
        }
    }

    // обновление категории
    if(isset($_POST['update_category'])){
        $id   = (int)$_POST['cat_id'];
        $name = trim($_POST['name'] ?? '');
        if($id && $name){
            $connect->prepare("UPDATE categories SET name = ? WHERE id = ?")->execute([$name, $id]);
            $_SESSION['success'] = 'Категория обновлена';
        }
        echo '<script>location.href="?page=admin&section=category"</script>';
        exit;
    }

    // удаление категории
    if(isset($_POST['delete_category'])){
        $id = (int)$_POST['cat_id'];
        $connect->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        $_SESSION['success'] = 'Категория удалена';
        echo '<script>location.href="?page=admin&section=category"</script>';
        exit;
    }

    $cats = $connect->query("
        SELECT c.*, COUNT(p.id) AS product_count
        FROM categories c
        LEFT JOIN products p ON p.category_id = c.id
        GROUP BY c.id
        ORDER BY c.name ASC
    ")->fetchAll();
?>

<div class="admin">
    <?php include('php/components/admin_sidebar.php'); ?>

    <main class="admin__main">
        <div class="admin__header">
            <div>
                <h1 class="admin__title">Категории</h1>
                <p class="admin__breadcrumbs">
                    <a href="?page=admin">Админ-панель</a> › Категории
                </p>
            </div>
            <button class="admin__btn" onclick="document.getElementById('addCatForm').style.display='block'">Добавить категорию</button>
        </div>

        <!-- форма добавления -->
        <div id="addCatForm" class="admin__form" style="display:none;">
            <h3 style="font-family:'igra',sans-serif;font-size:20px;">Новая категория</h3>
            <?php if(isset($cat_error)): ?>
                <i style="color:red"><?=$cat_error?></i>
            <?php endif ?>
            <form method="post">
                <div class="admin__form-group">
                    <label class="admin__form-label">Название *</label>
                    <input type="text" name="name" class="admin__form-input" placeholder="Введите название" required>
                </div>
                <div class="admin__form-actions">
                    <button type="submit" name="add_category" class="admin__btn">Добавить</button>
                    <button type="button" class="admin__btn admin__btn-outline"
                            onclick="document.getElementById('addCatForm').style.display='none'">Отмена</button>
                </div>
            </form>
        </div>

        <div class="admin__table-wrapper">
            <div class="admin__table-header">
                <h2 class="admin__table-title">Список категорий (<?=count($cats)?>)</h2>
            </div>
            <table class="admin__table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Кол-во товаров</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($cats)): ?>
                        <tr><td colspan="4" style="text-align:center;padding:30px;color:#888;">Категорий нет</td></tr>
                    <?php else: ?>
                        <?php foreach($cats as $c): ?>
                            <tr>
                                <td><?=$c['id']?></td>
                                <td>
                                    <span class="cat-name-<?=$c['id']?>"><?=$c['name']?></span>
                                    <input class="admin__form-input cat-edit-input" id="cat-input-<?=$c['id']?>"
                                           value="<?=$c['name']?>" style="display:none;max-width:200px;padding:6px 10px;">
                                </td>
                                <td><?=$c['product_count']?></td>
                                <td class="admin__actions">
                                    <button type="button" class="admin__btn admin__btn-sm admin__btn-outline"
                                            onclick="toggleCatEdit(<?=$c['id']?>)">
                                        <img src="public/edit_profile.png" alt="Ред.">
                                    </button>
                                    <form method="post" style="display:inline;" id="save-form-<?=$c['id']?>" style="display:none;">
                                        <input type="hidden" name="cat_id" value="<?=$c['id']?>">
                                        <input type="hidden" name="name" id="save-name-<?=$c['id']?>">
                                        <button type="submit" name="update_category" class="admin__btn admin__btn-sm admin__btn-success"
                                                id="save-btn-<?=$c['id']?>" style="display:none;"
                                                onclick="document.getElementById('save-name-<?=$c['id']?>').value=document.getElementById('cat-input-<?=$c['id']?>').value">
                                            Сохранить
                                        </button>
                                    </form>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Удалить категорию?')">
                                        <input type="hidden" name="cat_id" value="<?=$c['id']?>">
                                        <button type="submit" name="delete_category" class="admin__btn admin__btn-sm admin__btn-danger">
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

<script>
function toggleCatEdit(id) {
    const nameEl  = document.querySelector('.cat-name-' + id);
    const input   = document.getElementById('cat-input-' + id);
    const saveBtn = document.getElementById('save-btn-' + id);
    nameEl.style.display  = nameEl.style.display  === 'none' ? '' : 'none';
    input.style.display   = input.style.display   === 'none' ? 'inline-block' : 'none';
    saveBtn.style.display = saveBtn.style.display === 'none' ? 'inline-flex' : 'none';
}
</script>
