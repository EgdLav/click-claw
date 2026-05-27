<header class="header">
    <div class="container header__inner">
        <div class="header__logo">
            <a href="?"><img class="logo-img" loading="lazy" alt="Logo" src="public/Frame 4.svg"></a>
        </div>

        <button class="burger-btn" aria-label="Открыть меню">
            <span></span><span></span><span></span>
        </button>

        <div class="mobile-menu" id="mobileMenu">
            <nav class="header__nav">
                <a href="?" class="nav-link">Главная</a>

                <div class="nav-dropdown">
                    <span class="nav-link">Каталог</span>
                    <img class="icon-arrow" alt="" src="public/arrow.png">
                    <ul class="dropdown-menu">
                        <li><a href="?page=catalog">Все товары</a></li>
                        <li><a href="?page=catalog&category_id=1">Клавиатуры</a></li>
                        <li><a href="?page=catalog&category_id=2">Мыши</a></li>
                        <li><a href="?page=catalog&category_id=3">Наушники</a></li>
                        <li><a href="?page=catalog&category_id=8">Прочее</a></li>
                    </ul>
                </div>

                <div class="nav-dropdown">
                    <span class="nav-link">Прочее</span>
                    <img class="icon-arrow" alt="" src="public/arrow.png">
                    <ul class="dropdown-menu">
                        <li><a href="?page=about">О нас</a></li>
                        <li><a href="?page=blog">Блог</a></li>
                        <li><a href="?page=contacts">Контакты</a></li>
                    </ul>
                </div>
            </nav>

            <div class="mobile-actions">
                <?php if(isset($UID)): ?>
                    <a href="?page=profile" class="mobile-action-link">
                        <img src="public/acount.svg" alt="Профиль">
                        <span><?=$USER['name']?></span>
                    </a>
                <?php else: ?>
                    <a href="?page=login" class="mobile-action-link">
                        <img src="public/acount.svg" alt="Аккаунт">
                        <span>Войти / Регистрация</span>
                    </a>
                <?php endif ?>
                <a href="?page=cart" class="mobile-action-link">
                    <img src="public/cart.svg" alt="Корзина">
                    <span>Корзина</span>
                </a>
            </div>
        </div>

        <div class="header__actions desktop-actions">
            <a href="?page=search" class="action-btn">
                <img src="public/loop.svg" alt="Поиск">
            </a>
            <?php if(isset($UID)): ?>
                <a href="?page=profile" class="action-btn">
                    <img src="public/acount.svg" alt="Профиль">
                    <span><?=$USER['name']?></span>
                </a>
            <?php else: ?>
                <a href="?page=login" class="action-btn">
                    <img src="public/acount.svg" alt="Аккаунт">
                </a>
            <?php endif ?>
            <a href="?page=cart" class="action-btn">
                <img src="public/cart.svg" alt="Корзина">
            </a>
        </div>
    </div>
</header>
