# План реализации: «КЛИК-КЛАВ» — Интернет-магазин

## Задачи

- [x] 1. Настройка базы данных и PHP-инфраструктуры
  - [x] 1.1 Создать `includes/db.php` — подключение к SQLite, инициализация схемы и seed-данных
  - [x] 1.2 Создать `includes/auth_check.php` — функции проверки авторизации
  - [x] 1.3 Создать `includes/functions.php` — вспомогательные функции (jsonResponse, sanitize и др.)

- [x] 2. PHP API — авторизация
  - [x] 2.1 Создать `api/auth.php` — обработчики login, register, logout

- [x] 3. PHP API — товары и категории
  - [x] 3.1 Создать `api/products.php` — list, get, create, update, delete
  - [x] 3.2 Создать `api/categories.php` — list, create, update, delete

- [-] 4. PHP API — корзина и заказы
  - [x] 4.1 Создать `api/cart.php` — get, add, update, remove, clear
  - [ ] 4.2 Создать `api/orders.php` — create, list (user), all (admin), update_status

- [ ] 5. PHP API — профиль
  - [ ] 5.1 Создать `api/profile.php` — get, update

- [ ] 6. JavaScript-компоненты
  - [ ] 6.1 Создать `js/burger.js` — бургер-меню (вынести из inline-скриптов)
  - [ ] 6.2 Создать `js/slider.js` — слайдер карточек товаров
  - [ ] 6.3 Создать `js/accordion.js` — аккордеон характеристик
  - [ ] 6.4 Создать `js/modal.js` — управление модальными окнами
  - [ ] 6.5 Создать `js/tabs.js` — вкладки профиля
  - [ ] 6.6 Создать `js/cart.js` — логика корзины (количество, удаление, пересчёт)
  - [ ] 6.7 Создать `js/catalog.js` — фильтрация каталога
  - [ ] 6.8 Создать `js/auth.js` — валидация форм входа и регистрации

- [ ] 7. Скачиваемые документы
  - [ ] 7.1 Создать папку `docs/` с файлами-шаблонами (privacy-policy.pdf, warranty-policy.pdf, return-policy.pdf)

- [ ] 8. Обновление HTML-страниц — подключение JS и PHP
  - [ ] 8.1 Обновить `index.html` — подключить `burger.js`, `slider.js`, добавить `navOverlay`
  - [ ] 8.2 Обновить `catalog.html` — подключить `burger.js`, `catalog.js`, убрать Google Fonts
  - [ ] 8.3 Обновить `item.html` — подключить `burger.js`, `accordion.js`, `cart.js`
  - [ ] 8.4 Обновить `cart.html` — подключить `burger.js`, `cart.js`, добавить `navOverlay`
  - [ ] 8.5 Обновить `make-order.html` — форма оформления заказа с валидацией и отправкой в API
  - [ ] 8.6 Обновить `login-modal.html` — форма входа с отправкой в `api/auth.php`
  - [ ] 8.7 Обновить `register-modal.html` — форма регистрации с чекбоксом и ссылкой на скачивание политики
  - [ ] 8.8 Обновить `profile.html` — подключить `tabs.js`, `burger.js`, добавить `navOverlay`
  - [ ] 8.9 Обновить `about_us.html` — добавить блок документов со ссылками на скачивание
  - [ ] 8.10 Обновить `orders.html` и `profile-settings.html` — подключить `tabs.js`

- [ ] 9. Адаптивные стили
  - [ ] 9.1 Добавить в `css/style.css` медиазапросы для брейкпоинта ≤1200px
  - [ ] 9.2 Добавить в `css/style.css` медиазапросы для брейкпоинта ≤428px
  - [ ] 9.3 Добавить CSS-переменные и стили для `navOverlay`

- [ ] 10. Административная панель — финализация
  - [ ] 10.1 Обновить `admin/admin-products.html` — таблица товаров с кнопками действий
  - [ ] 10.2 Обновить `admin/admin-add-product.html` — форма добавления товара с отправкой в API
  - [ ] 10.3 Обновить `admin/admin-edit-product.html` — форма редактирования товара
  - [ ] 10.4 Обновить `admin/admin-categories.html` — управление категориями
  - [ ] 10.5 Обновить `admin/admin-orders.html` — управление заказами с кнопками статусов
