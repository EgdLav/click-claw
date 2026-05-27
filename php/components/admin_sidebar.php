<?php
    $section = $_GET['section'] ?? '';
    $page    = $_GET['page']    ?? '';
    $activeSection = $section;
?>
<aside class="admin__sidebar">
    <div class="admin__sidebar-logo">
        КЛИК-КЛАВ
        <span>админ-панель</span>
    </div>
    <a href="?page=admin" class="admin__sidebar-link <?=($page=='admin' && !$section) ? 'active' : ''?>">Главная</a>
    <a href="?page=admin&section=catalog" class="admin__sidebar-link <?=in_array($section,['catalog','create_product','edit_product']) ? 'active' : ''?>">Товары</a>
    <a href="?page=admin&section=category" class="admin__sidebar-link <?=($section=='category') ? 'active' : ''?>">Категории</a>
    <a href="?page=admin&section=orders" class="admin__sidebar-link <?=($section=='orders') ? 'active' : ''?>">Заказы</a>
    <form method="post" style="margin-top:auto;">
        <input type="submit" name="exit" value="Выйти" class="admin__sidebar-link logout" style="background:none;border:none;cursor:pointer;width:100%;text-align:left;">
    </form>
</aside>
