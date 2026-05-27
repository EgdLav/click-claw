<?php
    $sql = "SELECT requests.*, courses.name 
        FROM requests 
        JOIN courses ON requests.course_id = courses.id 
        WHERE user_id = '$UID'";
    $requests = $connect->query($sql);
?>

<?php foreach($requests as $request): ?>
    id: <?= $request['id'] ?><br>
    course_id: <?= $request['name'] ?><br>
    start_date: <?= $request['start_date'] ?><br>
    phone: <?= $request['phone'] ?><br>
    status: 
    <?php
        // переписать используя таблицу
        if($request['status'] == 'new') echo 'Ожидает';
        if($request['status'] == 'accept') echo 'Принято';
        if($request['status'] == 'cansel') echo 'Отменено';
    ?>
    
    <br>
    <hr>
<?php endforeach ?>