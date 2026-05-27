<?php
    $errors = [];
    if(isset($_POST['create_product'])){
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];

        if(empty($name)){
            $errors['name'] = 'Введите название товара';
        }
        if(empty($description)){
            $errors['description'] = 'Введите описание товара';
        }
        if(empty($price)){
            $errors['price'] = 'Введите цену товара';
        }elseif($price > 100000){
            $errors['price'] = 'Не более 100000 рублей';
        }

        if($category == 0){
            $errors['category'] = 'Укажите категорию';
        }
        
        if(empty($_FILES['cover']['name'])){
            $errors['cover'] = 'Прикрепите обложку';
        }else{
            $types = ['jpg','png'];
            $file_name = $_FILES['cover']['name'];
            $file_type = pathinfo($file_name,PATHINFO_EXTENSION);
            if(!in_array($file_type,$types)){
                $errors['cover'] = 'Неверный формат';
            }elseif($_FILES['cover']['size'] > 5000000){
                $errors['cover'] = 'Слижком большой файл';
            }
        }

        if(empty($errors)){
            $dir = 'assets/images/products/';
            $new_name = $dir.time().'_'.$file_name;
            if(move_uploaded_file($_FILES['cover']['tmp_name'],$new_name)){
                $sql = "INSERT INTO products (name,description,price,category_id,cover)
                    VALUES ('$name','$description','$price','$category','$new_name')";
                $connect->query($sql);
                $_SESSION['success'] = 'Товар добавлен';
                echo'<script>location.href="?page=admin&catalog"</script>';
            }
        }
    }
?>

<h2>Добавление товара</h2>
<form method="post" enctype="multipart/form-data">
    <div class="input_group">
        <label>Название</label>
        <input type="text" name="name" value="<?=$name ?? ''?>">
        <?php if(isset($errors['name'])): ?>
            <i style="color:red"><?=$errors['name']?></i>
        <?php endif ?>
    </div>
    <div class="input_group">
        <label>Описание</label>
        <textarea name="description"><?=$description ?? ''?></textarea>
        <?php if(isset($errors['description'])): ?>
            <i style="color:red"><?=$errors['description']?></i>
        <?php endif ?>
    </div>
    <div class="input_group">
        <label>Цена</label>
        <input type="number" name="price" value="<?=$price ?? ''?>">
        <?php if(isset($errors['price'])): ?>
            <i style="color:red"><?=$errors['price']?></i>
        <?php endif ?>
    </div>
    <div class="input_group">
        <label>Категория</label>
        <select name="category">
            <option value="0">-- Выберите категорию --</option>
            <?php
                $sql = "SELECT * FROM category";
                $cats = $connect->query($sql);
                foreach($cats as $cat):
            ?>
                <option 
                    value="<?=$cat['id']?>"

                    <?php
                    if(isset($category)){
                        if($category == $cat['id']): 
                    ?>
                        selected
                    <?php endif?>
                    <?}?>
                >
                    <?=$cat['name']?>            
                </option>
            <?php endforeach ?>
        </select>
        <?php if(isset($errors['category'])): ?>
            <i style="color:red"><?=$errors['category']?></i>
        <?php endif ?>
    </div>
    <div class="input_group">
        <label>Обложка</label>
        <input type="file" name="cover">
        <?php if(isset($errors['cover'])): ?>
            <i style="color:red"><?=$errors['cover']?></i>
        <?php endif ?>
    </div>
    <input type="submit" name="create_product" value="Добавить"> 
</form>