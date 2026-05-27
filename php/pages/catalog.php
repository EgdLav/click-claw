<?php
    // фильтры
    $where  = [];
    $params = [];

    if(isset($_GET['category_id']) && $_GET['category_id'] !== ''){
        $where[]  = 'p.category_id = ?';
        $params[] = (int)$_GET['category_id'];
    }

    if(isset($_POST['search']) && $_POST['search'] !== ''){
        $search   = '%' . $_POST['search'] . '%';
        $where[]  = '(p.name LIKE ? OR p.brand LIKE ?)';
        $params[] = $search;
        $params[] = $search;
    }

    $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT p.*, c.name AS category_name,
                (SELECT pi.image FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order ASC LIMIT 1) AS image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            $whereStr
            ORDER BY p.id DESC";
    $stmt = $connect->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // все категории для фильтра
    $cats = $connect->query("SELECT * FROM categories ORDER BY name ASC");
?>

<main>
    <div class="container">
        <div class="catalog-text">
            <h2 class="title">
                <?php
                    if(isset($_GET['category_id'])){
                        $catStmt = $connect->prepare("SELECT name FROM categories WHERE id = ?");
                        $catStmt->execute([(int)$_GET['category_id']]);
                        $catRow = $catStmt->fetch();
                        echo $catRow ? $catRow['name'] : 'Каталог';
                    }else{
                        echo 'Все товары';
                    }
                ?>
            </h2>
            <p>Найдено товаров: <?=count($products)?></p>
        </div>

        <form method="post" class="filters">
            <input type="text" name="search" placeholder="Поиск..." value="<?=$_POST['search'] ?? ''?>">
            <button type="submit" class="btn filter">Найти</button>
        </form>

        <div class="catalog-main">
            <div class="filters-block">
                <h3>Категории</h3>
                <ul>
                    <li><a href="?page=catalog">Все товары</a></li>
                    <?php foreach($cats as $cat): ?>
                        <li>
                            <a href="?page=catalog&category_id=<?=$cat['id']?>"
                               <?php if(isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) echo 'style="font-weight:bold;"' ?>>
                                <?=$cat['name']?>
                            </a>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>

            <div class="catalog-content">
                <div class="products-grid">
                    <?php if(empty($products)): ?>
                        <p style="padding:20px;color:#888;">Товары не найдены.</p>
                    <?php else: ?>
                        <?php foreach($products as $p):
                            $image = $p['image'] ?: 'public/clava.png';
                            $price = number_format($p['price'], 0, '.', ' ') . ' ₽';
                        ?>
                            <div class="product-card">
                                <?php if($p['badge']): ?>
                                    <div class="product-badge"><?=$p['badge']?></div>
                                <?php endif ?>
                                <div class="product-image">
                                    <a href="?page=item&id=<?=$p['id']?>">
                                        <img src="<?=$image?>" alt="<?=$p['name']?>" onerror="this.src='public/clava.png'">
                                    </a>
                                </div>
                                <div class="product-info">
                                    <p class="product-brand"><?=$p['brand'] ?? ''?></p>
                                    <h3 class="product-name"><?=$p['name']?></h3>
                                    <p class="product-price"><?=$price?></p>
                                </div>
                                <?php if(isset($UID)): ?>
                                    <form method="post" action="?page=cart">
                                        <input type="hidden" name="product_id" value="<?=$p['id']?>">
                                        <button type="submit" name="add_to_cart" class="add-to-cart btn">
                                            <span>В корзину</span>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="?page=login" class="add-to-cart btn"><span>В корзину</span></a>
                                <?php endif ?>
                            </div>
                        <?php endforeach ?>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</main>
