# Документ дизайна: «КЛИК-КЛАВ» — Интернет-магазин

## Обзор

Веб-приложение «КЛИК-КЛАВ» — упрощённый интернет-магазин компьютерной периферии. Реализован на чистом HTML, CSS, JavaScript и PHP без сторонних библиотек и фреймворков. Проект уже содержит HTML-страницы и статические ресурсы; данный документ описывает архитектуру, структуру данных и детали реализации.

---

## 1. Архитектура системы

### 1.1 Стек технологий

| Слой | Технология |
|---|---|
| Разметка | HTML5 |
| Стили | CSS3 (переменные, медиазапросы) |
| Интерактивность | Vanilla JavaScript (ES6+) |
| Бэкенд | PHP 8+ |
| Хранилище данных | SQLite (через PDO) |
| Сессии | PHP Sessions |

### 1.2 Структура файлов

```
/
├── index.html                  # Главная страница
├── catalog.html                # Каталог товаров
├── item.html                   # Страница товара
├── cart.html                   # Корзина
├── make-order.html             # Оформление заказа
├── orders.html                 # История заказов
├── profile.html                # Профиль (обзор)
├── profile-settings.html       # Настройки профиля
├── about_us.html               # О нас
├── blog.html                   # Блог
├── contacts.html               # Контакты
├── login-modal.html            # Страница входа
├── register-modal.html         # Страница регистрации
├── search-modal.html           # Поиск
├── edit_profile-modal.html     # Редактирование профиля
├── add_adress-modal.html       # Добавление адреса
├── favorite.html               # Избранное
├── favorite-empty.html         # Избранное (пусто)
│
├── admin/
│   ├── admin.html              # Дашборд администратора
│   ├── admin-products.html     # Список товаров
│   ├── admin-add-product.html  # Добавление товара
│   ├── admin-edit-product.html # Редактирование товара
│   ├── admin-categories.html   # Управление категориями
│   └── admin-orders.html       # Управление заказами
│
├── api/                        # PHP-обработчики (создать)
│   ├── auth.php                # Вход / регистрация / выход
│   ├── products.php            # CRUD товаров
│   ├── categories.php          # CRUD категорий
│   ├── cart.php                # Управление корзиной
│   ├── orders.php              # Создание и управление заказами
│   └── profile.php             # Обновление профиля
│
├── includes/                   # PHP-хелперы (создать)
│   ├── db.php                  # Подключение к SQLite
│   ├── auth_check.php          # Проверка авторизации
│   └── functions.php           # Общие функции
│
├── css/
│   ├── style.css               # Основные стили
│   └── modal.css               # Стили модальных окон
│
├── js/                         # JavaScript-модули (создать)
│   ├── slider.js               # Компонент слайдера
│   ├── accordion.js            # Компонент аккордеона
│   ├── modal.js                # Управление модальными окнами
│   ├── tabs.js                 # Компонент вкладок
│   ├── burger.js               # Бургер-меню
│   ├── cart.js                 # Логика корзины
│   ├── catalog.js              # Фильтрация каталога
│   └── auth.js                 # Формы входа/регистрации
│
├── public/                     # Статические ресурсы (изображения, SVG)
├── fonts/                      # Шрифты (IgraSans, Inter)
└── docs/                       # Скачиваемые документы (создать)
    ├── privacy-policy.pdf
    ├── warranty-policy.pdf
    └── return-policy.pdf
```

### 1.3 Взаимодействие клиент–сервер

```
Браузер (HTML/CSS/JS)
        │
        │  fetch() / form submit
        ▼
PHP API (/api/*.php)
        │
        │  PDO
        ▼
SQLite (database.db)
```

- Страницы рендерятся на стороне клиента (статический HTML).
- PHP-обработчики принимают POST/GET запросы, работают с БД и возвращают JSON или выполняют редирект.
- Состояние авторизации хранится в PHP-сессии (`$_SESSION`).
- Корзина хранится в `$_SESSION['cart']` (массив `product_id => quantity`).

---

## 2. Модели данных (SQLite)

### 2.1 Таблица `users`

```sql
CREATE TABLE users (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    NOT NULL,
    email       TEXT    NOT NULL UNIQUE,
    phone       TEXT,
    password    TEXT    NOT NULL,  -- bcrypt hash
    role        TEXT    NOT NULL DEFAULT 'user',  -- 'user' | 'admin'
    created_at  TEXT    NOT NULL DEFAULT (datetime('now'))
);
```

### 2.2 Таблица `categories`

```sql
CREATE TABLE categories (
    id    INTEGER PRIMARY KEY AUTOINCREMENT,
    name  TEXT NOT NULL UNIQUE
);
```

### 2.3 Таблица `products`

```sql
CREATE TABLE products (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    name         TEXT    NOT NULL,
    brand        TEXT,
    description  TEXT,
    price        REAL    NOT NULL,
    image        TEXT,
    category_id  INTEGER REFERENCES categories(id),
    stock        INTEGER NOT NULL DEFAULT 0,
    badge        TEXT,
    created_at   TEXT    NOT NULL DEFAULT (datetime('now'))
);
```

### 2.4 Таблица `orders`

```sql
CREATE TABLE orders (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id      INTEGER REFERENCES users(id),
    name         TEXT    NOT NULL,
    phone        TEXT    NOT NULL,
    email        TEXT,
    address      TEXT,
    total        REAL    NOT NULL,
    status       TEXT    NOT NULL DEFAULT 'new',
    -- 'new' | 'processing' | 'completed' | 'cancelled'
    created_at   TEXT    NOT NULL DEFAULT (datetime('now'))
);
```

### 2.5 Таблица `order_items`

```sql
CREATE TABLE order_items (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id    INTEGER REFERENCES orders(id),
    product_id  INTEGER REFERENCES products(id),
    name        TEXT    NOT NULL,
    price       REAL    NOT NULL,
    quantity    INTEGER NOT NULL
);
```

### 2.6 Сессионная корзина

Корзина хранится в `$_SESSION['cart']` как ассоциативный массив:

```php
$_SESSION['cart'] = [
    product_id => quantity,
    // ...
];
```

---

## 3. PHP API — эндпоинты

### `api/auth.php`

| action | Метод | Описание |
|---|---|---|
| `login` | POST | Проверяет email+password, создаёт сессию |
| `register` | POST | Создаёт пользователя, создаёт сессию |
| `logout` | POST | Уничтожает сессию, редирект на главную |

**Поля для login:** `email`, `password`  
**Поля для register:** `name`, `email`, `phone`, `password`, `agree` (чекбокс)

### `api/products.php`

| action | Метод | Доступ | Описание |
|---|---|---|---|
| `list` | GET | Все | Список товаров с фильтрами |
| `get` | GET | Все | Один товар по `id` |
| `create` | POST | Admin | Создать товар |
| `update` | POST | Admin | Обновить товар |
| `delete` | POST | Admin | Удалить товар |

**Параметры фильтрации (GET):** `category_id`, `brand`, `price_min`, `price_max`

### `api/categories.php`

| action | Метод | Доступ | Описание |
|---|---|---|---|
| `list` | GET | Все | Список категорий |
| `create` | POST | Admin | Создать категорию |
| `update` | POST | Admin | Обновить категорию |
| `delete` | POST | Admin | Удалить категорию |

### `api/cart.php`

| action | Метод | Доступ | Описание |
|---|---|---|---|
| `get` | GET | User | Получить содержимое корзины |
| `add` | POST | User | Добавить товар |
| `update` | POST | User | Изменить количество |
| `remove` | POST | User | Удалить товар |
| `clear` | POST | User | Очистить корзину |

### `api/orders.php`

| action | Метод | Доступ | Описание |
|---|---|---|---|
| `create` | POST | User | Создать заказ из корзины |
| `list` | GET | User | Заказы текущего пользователя |
| `all` | GET | Admin | Все заказы |
| `update_status` | POST | Admin | Изменить статус заказа |

### `api/profile.php`

| action | Метод | Доступ | Описание |
|---|---|---|---|
| `get` | GET | User | Данные профиля |
| `update` | POST | User | Обновить имя, телефон |

---

## 4. Описание страниц и компонентов

### 4.1 Общие компоненты (присутствуют на всех страницах)

**Header** (`.header`):
- Логотип (ссылка на главную)
- Навигация с выпадающими меню (`.nav-dropdown`)
- Кнопки действий: поиск, аккаунт, корзина
- Бургер-кнопка (`.burger-btn`) — видна только на мобильных
- Мобильное меню (`.mobile-menu`) — управляется `burger.js`
- Оверлей (`.nav-overlay`) — закрывает меню при клике вне

**Footer** (`.footer`):
- Логотип, адрес, контакты, соцсети
- Колонки: «Узнать больше», «Поддержка», форма подписки
- Нижняя строка: копирайт, иконки платёжных систем
- ФИО студента и год

### 4.2 Главная страница (`index.html`)

**Секции:**
1. **Hero / Баннер** — полноэкранное изображение с кнопкой «Купить сейчас»
2. **Бегущая строка** (`.marquee`) — анимированный текст через CSS
3. **Популярные товары** — горизонтальный слайдер карточек (`.slider-container`)
4. **Для чего подходят** — сетка из 3 карточек с фоновыми изображениями
5. **Блог** — сетка превью статей
6. **Поиск по категориям** — сетка категорий (`.categories_wrapper`)
7. **Почему выбирают нас** — сетка преимуществ (`.why`)

**JS на странице:** `slider.js`, `burger.js`

### 4.3 Каталог (`catalog.html`)

**Компоненты:**
- Заголовок с количеством найденных товаров
- Панель быстрых фильтров (сортировка, подключение)
- Боковая панель фильтров (`.filters-block`): бренд (чекбоксы), цвет, цена (range + inputs)
- Сетка товаров (`.products-grid`, 3 колонки)
- Карточка товара (`.product-card`): бейдж, изображение, цвета, бренд, название, цена, кнопка «В корзину»

**JS на странице:** `catalog.js` (фильтрация), `burger.js`

**Логика фильтрации (catalog.js):**
```javascript
// Читает параметры фильтров
// Отправляет GET /api/products.php?category_id=&brand=&price_min=&price_max=
// Перерисовывает .products-grid
```

### 4.4 Страница товара (`item.html`)

**Компоненты:**
- Галерея: миниатюры + главное изображение
- Аккордеон характеристик (`.accordion`) — управляется через CSS checkbox hack + `accordion.js`
- Сайдбар: название, описание, цена, выбор цвета, количество, кнопки «В корзину» и «В избранное»
- Бейджи гарантии и безопасной оплаты
- Блок доставки

**JS на странице:** `accordion.js`, `burger.js`, `cart.js` (добавление в корзину)

### 4.5 Корзина (`cart.html`)

**Компоненты:**
- Список товаров (`.cart-card`): изображение, название, описание, цена, цвета, наличие, управление количеством, кнопка удаления, блок «Дополнительно»
- Блок «Вам также может понравиться» — горизонтальный слайдер
- Сайдбар (`.cart-sidebar`): блок доставки, сводка заказа (промокод, скидка, доставка, итого), кнопка «Перейти к оформлению», иконки оплаты, бейджи

**JS на странице:** `cart.js` (изменение количества, удаление, пересчёт суммы), `burger.js`

### 4.6 Оформление заказа (`make-order.html`)

**Компоненты:**
- Форма: имя, телефон, email, адрес доставки
- Сводка заказа (товары из корзины)
- Кнопка «Подтвердить заказ»
- Валидация полей на стороне клиента

**JS на странице:** валидация формы, отправка POST `/api/orders.php?action=create`

### 4.7 Авторизация (`login-modal.html`) и Регистрация (`register-modal.html`)

**Вход:**
- Поля: email, пароль
- Ссылка «Нет аккаунта? Зарегистрироваться»
- Отправка POST `/api/auth.php?action=login`

**Регистрация:**
- Поля: имя, email, телефон, пароль, подтверждение пароля
- Чекбокс согласия с политикой конфиденциальности
- Ссылка на скачивание `docs/privacy-policy.pdf`
- Ссылка «Уже есть аккаунт? Войти»
- Отправка POST `/api/auth.php?action=register`

**JS на странице:** `auth.js` (валидация, блокировка кнопки без чекбокса)

### 4.8 Профиль (`profile.html`, `orders.html`, `profile-settings.html`)

**Вкладки (Tabs):**
- «Обзор» → `profile.html`
- «Мои заказы» → `orders.html`
- «Настройка учётной записи» → `profile-settings.html`

**Обзор:**
- Карточка пользователя: аватар, кнопка редактирования, кнопка выхода
- Приветствие с именем
- Блок «Последний заказ»
- Сетка: «Мои заказы», «Список желаний»

**Мои заказы:**
- Поиск по заказам
- Список заказов или пустое состояние

**Настройки:**
- Информация профиля (имя, email, телефон)
- Секция адресов доставки

**JS на странице:** `tabs.js`, `burger.js`

### 4.9 Страница «О нас» (`about_us.html`)

**Компоненты:**
- Баннер с фоновым изображением
- Информационный блок о компании
- Блок «Цели и миссия»
- Блок официальных документов с кнопками скачивания:
  - Политика конфиденциальности → `docs/privacy-policy.pdf`
  - Политика гарантии → `docs/warranty-policy.pdf`
  - Политика возврата → `docs/return-policy.pdf`

### 4.10 Административная панель (`admin/`)

**Общий макет:**
- Сайдбар (`.admin__sidebar`): логотип, ссылки навигации, кнопка выхода
- Основной контент (`.admin__main`)

**Дашборд (`admin.html`):**
- Статистика: товары, категории, заказы, выручка
- Таблица последних заказов с кнопками «Принять» / «Отклонить»

**Товары (`admin-products.html`):**
- Таблица товаров: изображение, название, категория, цена, наличие, действия
- Кнопки: «Добавить товар», «Редактировать», «Удалить»

**Добавление/редактирование товара (`admin-add-product.html`, `admin-edit-product.html`):**
- Форма: название, бренд, описание, цена, категория (select), наличие, изображение (URL)
- Валидация обязательных полей

**Категории (`admin-categories.html`):**
- Таблица категорий с кнопками редактирования и удаления
- Форма добавления/редактирования категории (inline или модальное окно)

**Заказы (`admin-orders.html`):**
- Таблица всех заказов: №, клиент, сумма, статус, дата, действия
- Кнопки: «Принять» (→ processing), «Завершить» (→ completed), «Отклонить» (→ cancelled)

---

## 5. JavaScript-компоненты

### 5.1 Слайдер (`js/slider.js`)

```javascript
class Slider {
    constructor(containerSelector) {
        // Находит .slider-wrapper внутри контейнера
        // Привязывает кнопки .slider-prev и .slider-next
        // Прокручивает на ширину карточки + gap
        // Обновляет opacity кнопок на границах
    }
    scrollPrev() { /* scrollBy(-amount) */ }
    scrollNext() { /* scrollBy(+amount) */ }
    checkButtons() { /* opacity 0.5 на границах */ }
}
// Инициализация: new Slider('.slider-container')
```

### 5.2 Аккордеон (`js/accordion.js`)

Реализован через CSS checkbox hack (уже присутствует в `item.html`). JS-версия для страниц без checkbox:

```javascript
class Accordion {
    constructor(selector) {
        // Находит все .accordion-item
        // При клике на .accordion-header — toggle класса .open
        // Анимирует высоту .accordion-content через max-height
    }
}
```

### 5.3 Модальные окна (`js/modal.js`)

```javascript
class Modal {
    constructor(modalId) {
        this.modal = document.getElementById(modalId);
        this.overlay = this.modal.querySelector('.modal-overlay');
    }
    open() {
        this.modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    close() {
        this.modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}
// Закрытие по клику на оверлей и кнопку ×
// Закрытие по Escape
```

**Используемые модальные окна:**
- Поиск (`.search-modal`)
- Вход (`.login-modal`) — открывается при клике на иконку аккаунта для гостя
- Регистрация (`.register-modal`)
- Редактирование профиля (`.edit-profile-modal`)
- Добавление адреса (`.add-address-modal`)

### 5.4 Вкладки (`js/tabs.js`)

```javascript
class Tabs {
    constructor(tabsSelector, contentSelector) {
        // При клике на .tab-item — добавляет .active
        // Показывает соответствующий .tab-content
        // Скрывает остальные
    }
}
```

Используется на странице профиля (`.profile__tabs`).

### 5.5 Бургер-меню (`js/burger.js`)

```javascript
// Уже реализован inline в HTML-страницах.
// Вынести в отдельный файл burger.js:
function initBurgerMenu() {
    const burgerBtn = document.querySelector('.burger-btn');
    const mobileMenu = document.getElementById('mobileMenu');
    const navOverlay = document.getElementById('navOverlay');
    // openMenu() / closeMenu()
    // Обработчики: click, resize, touchstart/touchend (swipe)
    // Выпадающие меню на мобильных (toggle .open)
}
document.addEventListener('DOMContentLoaded', initBurgerMenu);
```

### 5.6 Корзина (`js/cart.js`)

```javascript
// Управление количеством товара
// Пересчёт итоговой суммы
// Удаление товара (fetch POST /api/cart.php?action=remove)
// Обновление счётчика в шапке
```

### 5.7 Каталог (`js/catalog.js`)

```javascript
// Сбор значений фильтров
// fetch GET /api/products.php с параметрами
// Перерисовка .products-grid
// Кнопка «Сбросить фильтры»
```

---

## 6. CSS-архитектура

### 6.1 CSS-переменные (добавить в начало `style.css`)

```css
:root {
    --color-black: #000;
    --color-white: #fff;
    --color-grey-light: #D9E0E3;
    --color-grey-mid: #CDCBCB;
    --color-text-muted: #888;
    --font-igra: 'igra', sans-serif;
    --font-inter: 'inter', sans-serif;
    --container-max: 1600px;
    --border-radius: 16px;
    --transition: 0.3s ease;
}
```

### 6.2 Брейкпоинты

```css
/* Планшет */
@media (max-width: 1200px) {
    /* Уменьшение колонок сеток */
    /* Скрытие части элементов */
}

/* Мобильные */
@media (max-width: 428px) {
    /* Одноколоночный макет */
    /* Бургер-меню вместо навигации */
    /* Уменьшение шрифтов */
}
```

### 6.3 Адаптивные изменения по брейкпоинтам

**≤1200px (планшет):**
- `.footer_top`: 2 колонки вместо 4
- `.why_wrapper`: 1 колонка
- `.why_wrapper-2`: 2 колонки вместо 3
- `.catalog-main`: 1 колонка (фильтры скрываются, открываются по кнопке)
- `.products-grid`: 2 колонки
- `.profile__top`: flex-wrap

**≤428px (мобильный):**
- `.header__nav` и `.desktop-actions`: `display: none`
- `.burger-btn`: `display: flex`
- `.products-grid`: 1 колонка
- `.footer_top`: 1 колонка
- `.appointment_wrapper`: 1 колонка
- `.blog_see-wrapper`: 1 колонка
- `.categories_wrapper`: 2 колонки
- `.profile__top`: flex-direction column
- `.order-empty-card`: width 100%

---

## 7. Хранилище данных

### 7.1 Инициализация БД (`includes/db.php`)

```php
<?php
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . __DIR__ . '/../database.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        initSchema($pdo);
    }
    return $pdo;
}

function initSchema(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (...)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (...)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (...)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (...)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (...)");
    seedData($pdo); // Начальные данные
}
```

### 7.2 Начальные данные (seed)

- 1 администратор: `admin@klik-klav.ru` / `admin123`
- 8 категорий: Клавиатуры, Мыши, Наушники, Микрофоны, Коврики, Веб-камеры, Кастом, Прочее
- 10+ товаров с реальными данными

### 7.3 Проверка авторизации (`includes/auth_check.php`)

```php
<?php
session_start();

function requireAuth(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /login-modal.html');
        exit;
    }
}

function requireAdmin(): void {
    requireAuth();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: /index.html');
        exit;
    }
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}
```

---

## 8. Скачиваемые документы

Создать папку `docs/` с файлами-шаблонами:
- `docs/privacy-policy.pdf` — Политика конфиденциальности
- `docs/warranty-policy.pdf` — Политика гарантии
- `docs/return-policy.pdf` — Политика возврата

Ссылки используют атрибут `download`:
```html
<a href="/docs/privacy-policy.pdf" download>Политика конфиденциальности</a>
```

---

## 9. Корректность и тестируемые свойства

### Свойство 1: Контроль доступа
- Страницы `cart.html`, `make-order.html`, `profile.html`, `orders.html`, `profile-settings.html` должны перенаправлять неавторизованного пользователя на `/login-modal.html`.
- Страницы `admin/*.html` должны перенаправлять не-администратора на `/index.html`.

### Свойство 2: Целостность корзины
- Сумма в корзине всегда равна `Σ(price_i × quantity_i)` для всех товаров.
- Количество товара не может быть меньше 1.

### Свойство 3: Валидация форм
- Форма регистрации не отправляется без чекбокса согласия.
- Форма оформления заказа не отправляется с пустыми обязательными полями.
- Форма добавления товара (admin) не отправляется без названия, цены и категории.

### Свойство 4: Согласованность данных
- После создания заказа корзина очищается.
- После удаления категории товары этой категории не теряются (category_id становится NULL или категория не удаляется при наличии товаров).

### Свойство 5: Адаптивность
- При ширине ≤428px элемент `.burger-btn` видим, `.desktop-actions` скрыт.
- При ширине ≤1200px `.products-grid` содержит не более 2 колонок.
