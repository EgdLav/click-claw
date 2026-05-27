<?php
    // добавление адреса
    if(isset($_POST['add_address'])){
        $city    = $_POST['city']    ?? '';
        $street  = $_POST['street']  ?? '';
        $house   = $_POST['house']   ?? '';
        $apt     = $_POST['apt']     ?? '';
        $entrance = $_POST['entrance'] ?? '';

        if(empty($city) || empty($street) || empty($house)){
            $addr_error = 'Заполните обязательные поля';
        }else{
            $stmt = $connect->prepare("
                INSERT INTO addresses (user_id, city, street, house, apt, entrance)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$UID, $city, $street, $house, $apt, $entrance]);
            $_SESSION['success'] = 'Адрес добавлен';
            echo '<script>location.href="?page=addresses"</script>';
            exit;
        }
    }

    // удаление адреса
    if(isset($_POST['delete_address'])){
        $addr_id = (int)$_POST['addr_id'];
        $connect->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?")->execute([$addr_id, $UID]);
        echo '<script>location.href="?page=addresses"</script>';
        exit;
    }

    $stmt = $connect->prepare("SELECT * FROM addresses WHERE user_id = ?");
    $stmt->execute([$UID]);
    $addresses = $stmt->fetchAll();
?>

<main>
    <div class="container">
        <div class="orders_wrapper">
            <div class="settings_top">
                <h3>Адресная книга</h3>
                <div class="orders_line"></div>
            </div>

            <div class="address-grid">
                <?php foreach($addresses as $addr): ?>
                    <div class="address-card">
                        <p>г. <?=$addr['city']?></p>
                        <p>ул. <?=$addr['street']?></p>
                        <p>д. <?=$addr['house']?></p>
                        <?php if($addr['apt']): ?>
                            <p>кв. <?=$addr['apt']?></p>
                        <?php endif ?>
                        <?php if($addr['entrance']): ?>
                            <p>подъезд <?=$addr['entrance']?></p>
                        <?php endif ?>
                        <form method="post">
                            <input type="hidden" name="addr_id" value="<?=$addr['id']?>">
                            <button type="submit" name="delete_address" class="delete-address">
                                <img src="public/adress-x.png" alt="">
                            </button>
                        </form>
                    </div>
                <?php endforeach ?>

                <div class="add-address-card" onclick="document.getElementById('addAddrForm').style.display='block'">
                    <span class="plus"><img src="public/adress-+.png" alt=""></span>
                    <span class="label">ДОБАВИТЬ АДРЕС</span>
                </div>
            </div>

            <div id="addAddrForm" style="display:none;margin-top:24px;">
                <?php if(isset($addr_error)): ?>
                    <i style="color:red"><?=$addr_error?></i>
                <?php endif ?>
                <form method="post" class="checkout-form">
                    <div class="form-row">
                        <div class="input-container">
                            <label>Город *</label>
                            <input type="text" name="city" placeholder="Казань" required>
                        </div>
                        <div class="input-container">
                            <label>Улица *</label>
                            <input type="text" name="street" placeholder="ул. Пушкина" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="input-container">
                            <label>Дом *</label>
                            <input type="text" name="house" placeholder="1" required>
                        </div>
                        <div class="input-container">
                            <label>Подъезд</label>
                            <input type="text" name="entrance" placeholder="2">
                        </div>
                        <div class="input-container">
                            <label>Квартира</label>
                            <input type="text" name="apt" placeholder="42">
                        </div>
                    </div>
                    <button type="submit" name="add_address" class="btn">Добавить адрес</button>
                    <button type="button" class="btn" style="background:#fff;color:#000;border:2px solid #000;margin-left:8px;"
                            onclick="document.getElementById('addAddrForm').style.display='none'">Отмена</button>
                </form>
            </div>
        </div>
    </div>
</main>
