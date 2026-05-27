<?php
    // узнаем курс по ID
    if(isset($_GET['id'])){
        $get_id = $_GET['id'];
        $sql = "SELECT * FROM courses WHERE id = $get_id";
        $course = $connect->query($sql)->fetch();
    }
?>
<!-- основная информация -->
<h1>Курс: <?=$course['name']?></h1>
<img src="<?=$course['cover']?>" width="200px"><br>

<!-- галерея фотографий -->
<?php
    $sql = "SELECT * FROM course_images WHERE course_id = $get_id";
    $images = $connect->query($sql);
?>
<?php foreach($images as $image): ?>
    <img src="<?=$image['url']?>" width="100px">
<?php endforeach ?>
<!-- галерея фотографий -->
<hr>

<?php if(isset($_SESSION['uid'])): ?>
    <!-- если авторизован -->
    <a href="?page=request&id=<?=$course['id']?>">Записаться</a>
    <?php if($USER['role'] == 'admin'): ?>
        <!-- если админ -->
        <a href="?page=edit&id=<?=$course['id']?>">Изменить</a>
    <?php endif ?>
<?php else: ?>
    Для записи нужно 
    <a href="?page=auth">авторизоваться</a>
<?php endif ?>

