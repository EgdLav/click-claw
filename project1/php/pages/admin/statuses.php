<?php 
    if(isset($_POST['new_status'])){
        $value = $_POST['value'];
        if(empty($value)){
            $error = 'Введите значение';
        }else{
            $sql = "INSERT INTO statuses (value) VALUES ('$value')";
            $connect->query($sql);
            $_SESSION['success'] = 'Статус создан';
            echo '<script>location.href="?page=admin&statuses"</script>';
        }
    }
    $sql = "SELECT * FROM statuses";
    $categories = $connect->query($sql);
?>

<h2>Статусы</h2>
<?php if(isset($_GET['edit'])): ?>
    <?php
        $edit_id = $_GET['edit'];
        $sql = "SELECT * FROM statuses WHERE id = $edit_id";
        $cat = $connect->query($sql)->fetch();
        if(isset($_POST['update_status'])){
            $value = $_POST['value'];
            if(empty($value)){
                $error = 'Не может быть пустым';
            }else{
                $sql = "UPDATE statuses 
                        SET value = '$value' 
                    WHERE id = '$edit_id'";
                $connect->query($sql);
                $_SESSION['success'] = 'Статус изменен';
                echo '<script>location.href="?page=admin&statuses"</script>';
            }
        }
    ?>
    <form method="POST">
        Измнеить статуса<br>
        <?php if(isset($error)): ?>
            <i style="color:red"><?=$error?></i><br>
        <?php endif ?>
        <input type="text" name="value" value="<?=$cat['value']?>">
        <input type="submit" name="update_status">
    </form>
<?php else: ?>
    <form method="POST">
        Новый статус<br>
        <?php if(isset($error)): ?>
            <i style="color:red"><?=$error?></i><br>
        <?php endif ?>
        <input type="text" name="value">
        <input type="submit" name="new_status">
    </form>
<?php endif ?>

<hr>

<?php if(isset($_GET['delete'])):?>
    <?php
        $delete_id = $_GET['delete'];
        $sql = "SELECT * FROM statuses WHERE id = $delete_id";
        $cat =$connect->query($sql)->fetch();
        if(isset($_POST['delete'])){
            $sql = "DELETE FROM statuses WHERE id = $delete_id";
            $connect->query($sql);
            $_SESSION['success'] = 'Статус удален';
            echo '<script>location.href="?page=admin&statuses"</script>';
        }
    ?>
    
    <div style="background-color:orange; padding:10px">
        <h2>Вы точно хотите удалить категорию "<?=$cat['value']?>"?</h2>
        <form method="POST">
            <input type="submit" name="delete" value="Подтвердить">
        </form>
        <a href="?page=admin&statuses">Отмена</a>
    </div>
            
<?php endif ?>

<hr>

<?php foreach($categories as $category): ?>
    <?=$category['value']?>
    <a href="?page=admin&statuses&edit=<?=$category['id']?>">
        Изменить
    </a>
    <a href="?page=admin&statuses&delete=<?=$category['id']?>">
        Удалить
    </a>
    <br>
<?php endforeach ?>

