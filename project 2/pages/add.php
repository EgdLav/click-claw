<?php
// проверка на авторизованность и нароль админа
    if(!isset($_SESSION['uid']) OR $USER['role'] != 'admin'){
        echo 'Нет доступа';
        exit;
    }

    // выбираем все категории
    $sql = "SELECT * FROM category";
    $cats = $connect->query($sql);

    // обрабкотка формы добавления
    if(isset($_POST['add'])){
        $cat_id = $_POST['cat_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        // если не указана категория
        if($cat_id == 0){
            echo 'Выберите категорию';
            exit;
        }
        // подготовка файла для загрузки
        $types = ['image/jpeg','image/png','image/webp'];
        if(!in_array($_FILES['cover']['type'], $types)){
            echo 'Неверный формат файла';
            exit;
        }
        $dir = 'courses_img/';
        $file_name = time().'_'.$_FILES['cover']['name'];
        $path = $dir . $file_name;
        // сам процесс загрузки фото
        if(move_uploaded_file($_FILES['cover']['tmp_name'],$path)){
            $sql = "INSERT INTO courses (name, description, cover, category_id)
                VALUES ('$name','$description','$path','$cat_id')";
            $connect->query($sql);
            // echo'<script>location.href="?"</script>';
        }
    }
?>

<h1>Добавить курс</h1>
<form method="post" name="add" enctype="multipart/form-data">
    Category:
    <select name="cat_id">
        <option value="0">-- Select category --</option>
        <?php foreach($cats as $cat): ?>
            <option value="<?=$cat['id']?>"><?=$cat['name']?></option>
        <?php endforeach ?>
    </select><br><br>
    Name:
    <input type="text" name="name"><br><br>
    Description:<br>
    <textarea name="description"></textarea><br><br>
    <input type="file" name="cover"><br><br>
    <input type="submit" name="add">
</form>