<?php
    // измнение о себе
    if(isset($_POST['edit'])){
        $name = $_POST['name'];
        $bio = $_POST['bio'];
        $sql = "UPDATE users SET name = '$name', bio = '$bio' WHERE id = $UID";
        $connect->query($sql);
        echo'<script>location.href="?page=profile"</script>';
    }
    // обновление аватарки
    if(isset($_POST['avatar'])){
        $dir = 'avatars/';
        $name = $UID.'_'.time().'_'.$_FILES['image']['name'];
        $path = $dir . $name;
        if(move_uploaded_file($_FILES['image']['tmp_name'],$path)){
            $sql = "UPDATE users SET avatar = '$path' WHERE id = $UID";
            $connect->query($sql);
            echo'<script>location.href="?page=profile"</script>';
        }
    }
?>
<p>
    <?php if(isset($USER['avatar'])): ?>
        <img src="<?=$USER['avatar']?>" width="200px">
    <?php else: ?>
        🤦‍♂️ 
    <?php endif ?>
    <form method="post" name="avatar" enctype="multipart/form-data">
        <input type="file" name="image"><br>
        <input type="submit" name="avatar">
    </form>
</p>
<p>Имя: <?=$USER['name']?></p>
<p>О себе: <?=$USER['bio']?></p>
<a href="?page=profile&edit">Изменить данные</a>
<hr>
<?php if(isset($_GET['edit'])): ?>
    <form method="post" name="edit">
        name:
        <input type="text" name="name" value="<?=$USER['name']?>"><br><br>
        bio<br>
        <textarea cols="30" rows="5" name="bio"><?=$USER['bio']?></textarea><br><br>
        <input type="submit" name="edit">
    </form>
<?php endif ?>