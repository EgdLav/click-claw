<?php 
    // опеределение фильтрации по категриям
    if(isset($_GET['category'])){
        $category_id = $_GET['category'];
        $dop_sql = "AND category_id = $category_id";
    }else{
        $dop_sql = '';
    }
    // определить текст поиска
    if(isset($_POST['search'])){
        $search_name = $_POST['name'];
    }else{
        $search_name = '';
    }
    // выбор всех курсов
    $sql = "SELECT * FROM courses WHERE name LIKE '%$search_name%' $dop_sql ";
    $courses = $connect->query($sql);

    // выбор всех категорий
    $sql = "SELECT * FROM category";
    $cats = $connect->query($sql);
?>
<h1>Каталог</h1>
<form method="post" name="search">
    Поиск:
    <input type="text" name="name">
    <input type="submit" name="search">
</form>
<a href="?page=courses">Все</a>
<!-- вывод списка категорий для фильтрации -->
<?php foreach($cats as $cat): ?>
    <a href="?page=courses&category=<?=$cat['id']?>">
        <?=$cat['name']?>
    </a>
<?php endforeach ?>

<div class="list">
    <!-- вывод списка курсов -->
    <?php foreach($courses as $course): ?>
        <?php
            $course_id = $course['id'];
            $sql = "SELECT COUNT(*) FROM requests 
                WHERE course_id = $course_id AND status = 'accept'";
            $student_count = $connect->query($sql)->fetchColumn();
        ?>
        <div class="item">
            <h3><?=$course['name']?></h3>
            <img src="<?=$course['cover']?>" width="200px"><br>
            Количество участников: <?=$student_count?> <br>
            <a href="?page=item&id=<?=$course['id']?>">Подробнее</a>
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
        </div><hr>
    <?php endforeach ?>
</div>