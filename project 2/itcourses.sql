-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Апр 08 2026 г., 10:12
-- Версия сервера: 8.0.30
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `itcourses`
--

-- --------------------------------------------------------

--
-- Структура таблицы `category`
--

CREATE TABLE `category` (
  `id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `category`
--

INSERT INTO `category` (`id`, `name`) VALUES
(1, 'Web'),
(2, 'робототехника'),
(3, 'Дроны');

-- --------------------------------------------------------

--
-- Структура таблицы `courses`
--

CREATE TABLE `courses` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cover` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `courses`
--

INSERT INTO `courses` (`id`, `name`, `description`, `cover`, `category_id`) VALUES
(1, 'rrrrt', 'rrrrty', 'courses_img/1774419714_2026.png', 3),
(2, 'е657выв', 'екгенг', 'courses_img/1774419977_certificate.jpg', 1),
(3, '_1774259341_i.webp', '345', 'courses_img/_1774259341_i.webp', 3),
(4, 'rrrr', 'rrrr', 'courses_img/1774419132_2026.png', 2),
(5, 'PHP MySQL', ';g', 'courses_img/1774417358_certificate.jpg', 1),
(6, 'Микросхемы', 'Пайка', 'courses_img/1774417387_2026 (1).png', 2),
(7, 'Специалист по тех обслуживанию', 'ывдлопдуыла ыуа ыуаыуа ', 'courses_img/1774417433_2026.png', 3),
(8, 'Руслан', 'ыва', 'courses_img/1774861098_certificate.jpg', 2);

-- --------------------------------------------------------

--
-- Структура таблицы `course_images`
--

CREATE TABLE `course_images` (
  `id` int NOT NULL,
  `course_id` int DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `course_images`
--

INSERT INTO `course_images` (`id`, `course_id`, `url`) VALUES
(8, 3, 'courses_img/3_1775631735_pngtree-picture-of-a-blue-bird-on-a-black-background-image_2937385.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `requests`
--

CREATE TABLE `requests` (
  `id` int NOT NULL,
  `course_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `start_date` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pay_method` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `requests`
--

INSERT INTO `requests` (`id`, `course_id`, `user_id`, `status`, `start_date`, `pay_method`, `name`, `phone`) VALUES
(1, 2, 2, 'accept', '2026-04-01', 'cash', 'Руслан', '+7 (917) 235-08-36'),
(2, 1, 2, 'close', '2026-04-01', 'card', 'Руслан', '+79172350836'),
(3, 1, 2, 'accept', '2026-04-03', 'card', 'Руслан123ячсяч', '+7 (917) 235-08-36'),
(4, 1, 2, 'work', '2026-04-02', 'cash', 'Руслан123ячсяч', '+7 (917) 235-08-36');

-- --------------------------------------------------------

--
-- Структура таблицы `request_statuses`
--

CREATE TABLE `request_statuses` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `color` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `request_statuses`
--

INSERT INTO `request_statuses` (`id`, `name`, `value`, `color`) VALUES
(1, 'new', 'Новая заявка', 'yellow'),
(2, 'work', 'В работе', 'orange'),
(3, 'cancel', 'Отмена', 'red'),
(4, 'accept', 'Принято', 'green'),
(5, 'close', 'Завершено', 'blue');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bio` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `name`, `bio`, `avatar`, `email`, `password`, `role`) VALUES
(1, NULL, NULL, NULL, 'test@mail.ru', 'sldkuhg', 'user'),
(2, 'Руслан123ячсяч', 'О себе\r\n123ячсяч', 'avatars/2_1774256542_15b7179a4088749c786988d15cc9f9e3.jpg', '123@mail.ru', '$2y$10$LMKeYSZE.G4VChA9iYayX.7SBMdpGVf9RGTqW31gtbGTMxIImVgyy', 'admin'),
(3, NULL, NULL, NULL, '123123@mail.ru', '$2y$10$j0XSkSf5G7S3cVyHvVm.eu/dAbQZkSMZy9YAzD9BtqeZeaNoyX0xe', 'user');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `course_images`
--
ALTER TABLE `course_images`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `request_statuses`
--
ALTER TABLE `request_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `category`
--
ALTER TABLE `category`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `course_images`
--
ALTER TABLE `course_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `request_statuses`
--
ALTER TABLE `request_statuses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
