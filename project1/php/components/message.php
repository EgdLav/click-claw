<?php if(isset($_SESSION['success'])): ?>
    <h2 style="color:green"><?=$_SESSION['success']?></h2>
    <?php unset($_SESSION['success']); ?>
<?php endif ?>