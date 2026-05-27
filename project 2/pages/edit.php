<?php 
    // узнаем курс по ID
    if(isset($_GET['id'])){
        $get_id = $_GET['id'];
        $sql = "SELECT * FROM courses WHERE id = $get_id";
        $course = $connect->query($sql)->fetch();
    }
    // выбираем все категрии
    $sql = "SELECT * FROM category";
    $cats = $connect->query($sql);

    // ОБРАБОТКА POST ЗАПРОСА НА ИЗМЕНЕНИЕ
    if(isset($_POST['edit'])){
        // берем данные из формы
        $cat_id = $_POST['cat_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        // если не выбрана категория
        if($cat_id == 0){
            echo 'Выберите категорию';
            exit;
        }

        // если не выбрано изображение
        if(empty($_FILES['cover']['name'])){
            // записываем путь к старой фотографии
            $path = $course['cover'];
        }else{
            // ЗАГРУЗКА НОВОЙ ФОТОГРАФИИ
            $types = ['image/jpeg','image/png','image/webp'];
            if(!in_array($_FILES['cover']['type'], $types)){
                echo 'Неверный формат файла';
                exit;
            }
            $dir = 'courses_img/';
            $file_name = time().'_'.$_FILES['cover']['name'];
            $path = $dir . $file_name;
            move_uploaded_file($_FILES['cover']['tmp_name'],$path);
        }
        // обновление данных в БД
        $sql = "UPDATE courses SET
                name = '$name',
                description = '$description',
                cover = '$path',
                category_id = '$cat_id'
            WHERE id = $get_id";
        $connect->query($sql);
        echo'<script>location.href="?page=courses"</script>';
    }
?>

<!-- форма изменения курса -->
<h1>Изменение курса: <?=$course['name']?></h1>
<form method="post" name="edit" enctype="multipart/form-data">
    Category:
    <select name="cat_id">
        <option value="0">-- Select category --</option>
        <?php foreach($cats as $cat): ?>
            <option 
                value="<?=$cat['id']?>"
                <?php if($cat['id'] == $course['category_id']) echo 'selected'; ?>
            >
                <?=$cat['name']?>
            </option>
        <?php endforeach ?>
    </select><br><br>
    Name:
    <input type="text" name="name" value="<?=$course['name']?>"><br><br>
    Description:<br>
    <textarea name="description"><?=$course['description']?></textarea><br><br>
    <img src="<?=$course['cover']?>" width="200px">
    <input type="file" name="cover"><br><br>
    <input type="submit" name="edit">
</form>
<!-- форма изменения курса -->

<hr>

<!-- вывод галереии с фозможностью удаления -->
<?php
    $sql = "SELECT * FROM course_images WHERE course_id = $get_id";
    $images = $connect->query($sql);
?>
<div style="display:flex;flex-wrap:wrap;gap:10px;">
<?php foreach($images as $image): ?>
    <img src="<?=$image['url']?>" width="100px">
    <form method="post" name="del_img">
        <input type="hidden" name="img_id" value="<?=$image['id']?>">
        <!-- отправляем url чтобы удалить из папки -->
        <input type="hidden" name="img_url" value="<?=$image['url']?>">
        <input type="submit" name="del_img" value="X">
    </form>
<?php endforeach ?>
</div>
<!-- вывод галереии с фозможностью удаления -->

<?php
    // обработка формы удаления
    if(isset($_POST['del_img'])){
        $img_id = $_POST['img_id'];
        $sql = "DELETE FROM course_images WHERE id = $img_id";
        $connect->query($sql);
        // удаление фото из папки
        $file_del = $_POST['img_url'];
        if(file_exists($file_del)){
            unlink($file_del);
        }
        echo '<script>location.href="?page=edit&id='.$get_id.'"</script>';
    }
?>


<h3>Добавить фотографии</h3>
<!-- Форма множественной загрузки -->
<form method="post" name="gallery" enctype="multipart/form-data">
    <input type="file" name="images[]" accept="image/*" multiple>
    <input type="submit" name="gallery">
</form>
<!-- Форма множественной загрузки -->

<?php
    // ОБРАБОТКА ФОРМЫ МНОЖЕСТВЕННОЕ ЗАГРУЗКИ
    if(isset($_POST['gallery'])){
        // папка для загрузки
        $dir = 'courses_img/';
        // массив форматов для валидации
        $types = ['jpg','png','webp','jpeg']; 

        // перебор выбранных файлов
        foreach($_FILES['images']['tmp_name'] as $key => $tmp_name){
            // имя файла
            $file_name = $_FILES['images']['name'][$key];
            // разширение файла
            $file_path = pathinfo($file_name, PATHINFO_EXTENSION);

            // валидация
            if(!in_array($file_path,$types)){
                echo 'Неверный формат';
                exit;
            }
            
            // Новое название файла
            $file_name = $get_id.'_'.time().'_'.$file_name;
            // путь закгрузки
            $path = $dir . $file_name;
            // процесс загрузки файла
            if(move_uploaded_file($tmp_name,$path)){
                // Запись в БД
                $sql = "INSERT INTO course_images (course_id,url)
                    VALUES ('$get_id','$path')";
                $connect->query($sql);
            } 
        }

        echo '<script>location.href="?page=edit&id='.$get_id.'"</script>';
    }
?>