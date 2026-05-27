<section class="hero">
    <img class="hero__bg" alt="" src="public/bg-header.png">
    <a href="?page=catalog" class="hero__cta">
        <span>Купить сейчас</span>
        <img class="cta-arrow" alt="" src="public/arrow.png">
    </a>
</section>

<div class="marquee">
    <div class="marquee__content">
        <p>Уважаемый покупатель! Добро пожаловать в официальный магазин КЛИК-КЛАВ! Уважаемый покупатель! Добро пожаловать в официальный магазин КЛИК-КЛАВ!</p>
    </div>
</div>

<main>
    <section id="popular">
        <div class="container">
            <h2 class="title">Популярные товары</h2>
        </div>
        <div class="slider-container">
            <button class="slider-btn slider-prev">
                <img src="public/arrow-slider.png" alt="Назад">
            </button>
            <div class="slider-wrapper">
                <div class="items-grid">
                    <?php
                        $sql = "SELECT p.*, 
                                    (SELECT pi.image FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order ASC LIMIT 1) AS image
                                FROM products p
                                ORDER BY p.id DESC
                                LIMIT 8";
                        $products = $connect->query($sql);
                        foreach($products as $product):
                            $image = $product['image'] ?: 'public/clava.png';
                            $price = number_format($product['price'], 0, '.', ' ') . ' ₽';
                    ?>
                        <div class="item_card">
                            <a href="?page=item&id=<?=$product['id']?>">
                                <img src="<?=$image?>" alt="<?=$product['name']?>" onerror="this.src='public/clava.png'">
                            </a>
                            <h3><?=$product['name']?></h3>
                            <p><?=$price?></p>
                            <?php if(isset($UID)): ?>
                                <form method="post" action="?page=cart">
                                    <input type="hidden" name="product_id" value="<?=$product['id']?>">
                                    <input type="submit" name="add_to_cart" value="В корзину" class="btn">
                                </form>
                            <?php else: ?>
                                <a href="?page=login" class="btn">В корзину</a>
                            <?php endif ?>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
            <button class="slider-btn slider-next">
                <img src="public/arrow-slider-right.png" alt="Вперед">
            </button>
        </div>
    </section>

    <section class="container">
        <h2 class="title">Для чего подходят</h2>
        <div class="appointment_wrapper">
            <div class="appointment_card" style="background-image: url('public/business.png');">
                <div class="card_content"><h3>Бизнес</h3></div>
            </div>
            <div class="appointment_card" style="background-image: url('public/education.jpg')">
                <div class="card_content"><h3>Образование</h3></div>
            </div>
            <div class="appointment_card" style="background-image: url('public/games.jpg')">
                <div class="card_content"><h3>Игры</h3></div>
            </div>
        </div>
    </section>

    <section class="blog_see">
        <div class="container">
            <div class="blog_see-wrapper">
                <div class="blog_see-1">
                    <div class="overlay"></div>
                    <a href="?page=blog" class="link_blog">Смотреть блог</a>
                </div>
                <div class="blog_see-right">
                    <div class="blog_card blog_see-2"></div>
                    <div class="blog_see-bottom">
                        <div class="blog_card blog_see-3"></div>
                        <div class="blog_card blog_see-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container">
        <h2 class="title">Поиск по категориям</h2>
        <div class="categories_wrapper">
            <?php
                $sql = "SELECT * FROM categories ORDER BY name ASC";
                $cats = $connect->query($sql);
                $catIcons = [1=>'cat-1.png',2=>'cat-2.png',3=>'cat-3.png',4=>'cat-4.png',5=>'cat-5.png',6=>'cat-6.png',7=>'cat-7.png',8=>'cat-8.png'];
                foreach($cats as $cat):
                    $icon = $catIcons[$cat['id']] ?? 'cat-1.png';
            ?>
                <a href="?page=catalog&category_id=<?=$cat['id']?>" class="category_card">
                    <img src="public/<?=$icon?>" alt="<?=$cat['name']?>">
                    <p><?=$cat['name']?></p>
                </a>
            <?php endforeach ?>
        </div>
    </section>

    <section class="why">
        <div class="container">
            <div class="why_wrapper">
                <div class="why_wrapper-1">
                    <h2 class="title">Почему покупают на Клик-Клав</h2>
                    <p>Мы хотим, чтобы покупка периферии была быстрой, выгодной и безопасной.</p>
                </div>
                <div class="why_wrapper-2">
                    <div class="why-card">
                        <img src="public/why-1.png" alt="">
                        <h3>Долями или в рассрочку</h3>
                        <p>Покупайте без лишней нагрузки на бюджет.</p>
                    </div>
                    <div class="why-card">
                        <img src="public/why-2.png" alt="">
                        <h3>Бесплатная доставка</h3>
                        <p>Для заказов от 3 000 ₽ по всей России.</p>
                    </div>
                    <div class="why-card">
                        <img src="public/why-3.png" alt="">
                        <h3>Скидка 10% за подписку</h3>
                        <p>Подпишитесь и получайте эксклюзивные промокоды.</p>
                    </div>
                    <div class="why-card">
                        <img src="public/why-4.png" alt="">
                        <h3>Гарантия и тест-драйв</h3>
                        <p>14 дней на тестирование без лишних вопросов.</p>
                    </div>
                    <div class="why-card">
                        <img src="public/why-5.png" alt="">
                        <h3>Поддержка знает своё дело</h3>
                        <p>Быстрый ответ и реальные рекомендации.</p>
                    </div>
                    <div class="why-card">
                        <img src="public/why-6.png" alt="">
                        <h3>Эксклюзивы для своих</h3>
                        <p>Редкие модели и лимитированные серии.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
