<?php 
    if(isset($_POST['new_category'])){
        $name = $_POST['name'];
        if(empty($name)){
            $error = 'Введите название';
        }else{
            $sql = "INSERT INTO category (name) VALUES ('$name')";
            $connect->query($sql);
            $_SESSION['success'] = 'Категория создана';
            echo '<script>location.href="?page=admin&category"</script>';
        }
    }
    $sql = "SELECT * FROM category";
    $categories = $connect->query($sql);
?>

<h2>Категории</h2>
<?php if(isset($_GET['edit'])): ?>
    <?php
        $edit_id = $_GET['edit'];
        $sql = "SELECT * FROM category WHERE id = $edit_id";
        $cat = $connect->query($sql)->fetch();
        if(isset($_POST['update_category'])){
            $name = $_POST['name'];
            if(empty($name)){
                $error = 'Не может быть пустым';
            }else{
                $sql = "UPDATE category 
                        SET name = '$name' 
                    WHERE id = '$edit_id'";
                $connect->query($sql);
                $_SESSION['success'] = 'Категория изменена';
                echo '<script>location.href="?page=admin&category"</script>';
            }
        }
    ?>
    <form method="POST">
        Измнеить категорию<br>
        <?php if(isset($error)): ?>
            <i style="color:red"><?=$error?></i><br>
        <?php endif ?>
        <input type="text" name="name" value="<?=$cat['name']?>">
        <input type="submit" name="update_category">
    </form>
<?php else: ?>
    <form method="POST">
        Новая категория<br>
        <?php if(isset($error)): ?>
            <i style="color:red"><?=$error?></i><br>
        <?php endif ?>
        <input type="text" name="name">
        <input type="submit" name="new_category">
    </form>
<?php endif ?>

<hr>

<?php if(isset($_GET['delete'])):?>
    <?php
        $delete_id = $_GET['delete'];
        $sql = "SELECT * FROM category WHERE id = $delete_id";
        $cat =$connect->query($sql)->fetch();
        if(isset($_POST['delete'])){
            $sql = "DELETE FROM category WHERE id = $delete_id";
            $connect->query($sql);
            $_SESSION['success'] = 'Категория удалена';
            echo '<script>location.href="?page=admin&category"</script>';
        }
    ?>
    
    <div style="background-color:orange; padding:10px">
        <h2>Вы точно хотите удалить категорию "<?=$cat['name']?>"?</h2>
        <form method="POST">
            <input type="submit" name="delete" value="Подтвердить">
        </form>
        <a href="?page=admin&category">Отмена</a>
    </div>
            
<?php endif ?>

<hr>

<?php foreach($categories as $category): ?>
    <?=$category['name']?>
    <a href="?page=admin&category&edit=<?=$category['id']?>">
        Изменить
    </a>
    <a href="?page=admin&category&delete=<?=$category['id']?>">
        Удалить
    </a>
    <br>
<?php endforeach ?>

