<?php if(isset($_SESSION['success'])): ?>
    <div style="background:#e8f8e8;color:#27ae60;padding:12px 20px;margin:10px 0;border-radius:6px;">
        <?=$_SESSION['success']?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif ?>

<?php if(isset($_SESSION['error'])): ?>
    <div style="background:#fde8e8;color:#e74c3c;padding:12px 20px;margin:10px 0;border-radius:6px;">
        <?=$_SESSION['error']?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif ?>
