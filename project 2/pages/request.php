<?php
// узнаем курс по ID
if(isset($_GET['id'])){
    $get_id = $_GET['id'];
    $sql = "SELECT * FROM courses WHERE id = $get_id";
    $course = $connect->query($sql)->fetch();
}
// обработка формы подачи заявки
if(isset($_POST['request'])){
    $start_date = $_POST['start_date'];
    $pay_method = $_POST['pay_method'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];

    // здесь должна быть валидация формы

    $sql = "INSERT INTO requests (course_id,user_id,status,start_date,pay_method,name,phone)
        VALUES ('$get_id','$UID','new','$start_date','$pay_method','$name','$phone')";
    $connect->query($sql);
    echo '<script>location.href="?page=profile"</script>';
}
?>

<h1>Форма подачи заявки на курс: <?= $course['name'] ?></h1>
<form method="post" name="request">
    Укажите даут начала обучения:<br>
    <input type="date" name="start_date"><br><br>
    Укажите форму оплаты:<br>
    <input type="radio" name="pay_method" value="cash"> - Наличка
    <input type="radio" name="pay_method" value="card"> - Карта
    <br><br>
    Имя студента:<br>
    <input type="text" name="name" value="<?=$USER['name']?>"><br><br>
    Номер телефона:<br>
    <input type="text" name="phone"><br><br>
    <input type="submit" name="request">
</form>