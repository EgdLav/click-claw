<?php
    // обработка формы смены статуса
    if(isset($_POST['status_update'])){
        $request_id = $_POST['request_id'];
        $status = $_POST['status'];
        // если админ не выбрал статус
        if($status == 0){
            echo "<script>alert('Вы не выбрали категорию')</script>";
        }else{
            $sql = "UPDATE requests SET status = '$status' WHERE id = '$request_id'";
            $connect->query($sql);
            echo '<script>location.href="?page=admin&requests"</script>';
        }
    }

    // выбираем все статусы
    $sql = "SELECT * FROM request_statuses";
    $statuses = $connect->query($sql);

    // определяем наличие филтрации по статусу
    if(isset($_GET['status'])){
        $get_status = $_GET['status'];
        $dop_sql = "WHERE status = '$get_status'";
    }else{
        $dop_sql = '';
    }

    // SQL запрос чтение всех заявок включая название курса и данные статуса (имя, цвет)
    $sql = "SELECT 
            requests.*, 
            courses.name as course_name,
            request_statuses.value,
            request_statuses.color
        FROM requests 
        JOIN courses ON requests.course_id = courses.id 
        JOIN request_statuses ON requests.status = request_statuses.name
        $dop_sql ##фильтрация по статусам
        ORDER BY id ASC
    ";
    $requests = $connect->query($sql);
?>

<h2>Заявки</h2>
<!-- вывод списка статусов для фильтрации -->
<?php foreach($statuses as $status): ?>
    <a href="?page=admin&requests&status=<?=$status['name']?>">
        <?=$status['value']?>
    </a>
<?php endforeach ?>
<hr>

<!-- вывод списка заявок -->
<?php foreach($requests as $request): ?>
    id: <?= $request['id'] ?><br>
    course_id: <?= $request['course_name'] ?><br>
    start_date: <?= $request['start_date'] ?><br>
    phone: <?= $request['phone'] ?><br>
    name: <?= $request['name'] ?><br>
    status:
    <!-- вывод статуса с цветом -->
    <b style="color:<?= $request['color'] ?>">
        <?= $request['value'] ?>
    </b><br>
    <!-- форма изменение статусы -->
    <form method="post" name="status_update">
        <input type="hidden" name="request_id" value="<?=$request['id']?>">
        <select name="status">
            <option value="0">-- Выберите статус --</option>
            <?php $statuses = $connect->query("SELECT * FROM request_statuses"); ?>
            <?php foreach($statuses as $status): ?>
                <option value="<?=$status['name']?>"><?=$status['value']?></option>
            <?php endforeach ?>
        </select>
        <input type="submit" name="status_update">
    </form>
    <br>
    <hr>
<?php endforeach ?>