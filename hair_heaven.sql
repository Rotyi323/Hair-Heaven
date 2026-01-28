-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2026. Jan 28. 19:31
-- Kiszolgáló verziója: 10.4.27-MariaDB
-- PHP verzió: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `hair_heaven`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `action`, `entity`, `entity_id`, `created_at`) VALUES
(1, 2, 'insert', 'products', 5, '2026-01-06 16:57:27'),
(2, 2, 'delete', 'services', 3, '2026-01-06 16:58:53'),
(3, 2, 'insert', 'services', 4, '2026-01-06 16:59:57'),
(4, 2, 'delete', 'products', 5, '2026-01-06 17:00:00'),
(5, 2, 'insert', 'services', 5, '2026-01-06 17:44:24'),
(6, 2, 'delete', 'services', 5, '2026-01-06 17:44:38'),
(7, 2, 'update', 'products', 3, '2026-01-06 17:49:53'),
(8, 2, 'insert', 'products', 6, '2026-01-06 18:52:55'),
(9, 2, 'delete', 'products', 6, '2026-01-06 18:52:59'),
(10, 2, 'update', 'products', 3, '2026-01-13 20:56:34'),
(11, 2, 'update', 'services', 2, '2026-01-28 17:13:46'),
(12, 2, 'update', 'services', 2, '2026-01-28 17:13:57'),
(13, 2, 'insert', 'services', 6, '2026-01-28 17:51:35'),
(14, 2, 'insert', 'services', 7, '2026-01-28 17:55:27'),
(15, 2, 'insert', 'services', 8, '2026-01-28 17:56:19'),
(16, 2, 'update', 'services', 2, '2026-01-28 17:56:43'),
(17, 2, 'update', 'services', 1, '2026-01-28 17:56:51'),
(18, 2, 'purchase', 'products', 1, '2026-01-28 18:51:46'),
(19, 2, 'purchase', 'products', 1, '2026-01-28 18:52:19'),
(20, 2, 'purchase', 'products', 1, '2026-01-28 18:52:28'),
(21, 2, 'purchase', 'products', 1, '2026-01-28 18:52:58'),
(22, 2, 'purchase', 'products', 4, '2026-01-28 18:53:19'),
(23, 2, 'purchase', 'products', 3, '2026-01-28 18:53:59'),
(24, 2, 'purchase', 'products', 3, '2026-01-28 19:08:01'),
(25, 2, 'purchase', 'products', 2, '2026-01-28 19:08:32'),
(26, 2, 'purchase', 'products', 2, '2026-01-28 19:18:57'),
(27, 2, 'purchase', 'products', 3, '2026-01-28 19:19:00'),
(28, 2, 'purchase', 'products', 4, '2026-01-28 19:19:20');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `title` varchar(120) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `banners`
--

INSERT INTO `banners` (`id`, `title`, `image_path`, `link_url`, `is_active`) VALUES
(1, 'Őszi ápolás', 'assets/hero/hero-1.jpg', '/aruhaz.php?type=mask', 1),
(2, 'Top ajánlatok', 'assets/hero/hero-2.png', '/aruhaz.php?type=mask', 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `appointment_datetime` datetime NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `service_id`, `appointment_datetime`, `status`, `note`, `created_at`) VALUES
(1, 2, 1, '2026-01-09 08:15:00', 'cancelled', NULL, '2026-01-04 15:32:40'),
(2, 2, 1, '2026-01-08 08:15:00', 'pending', NULL, '2026-01-04 15:52:10'),
(3, 2, 1, '2026-01-09 08:00:00', 'pending', NULL, '2026-01-04 16:07:40'),
(4, 2, 1, '2026-01-09 08:15:00', 'pending', NULL, '2026-01-04 16:08:00');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('new','confirmed','cancelled') NOT NULL DEFAULT 'new',
  `customer_name` varchar(120) NOT NULL,
  `customer_email` varchar(120) NOT NULL,
  `customer_address` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `customer_name`, `customer_email`, `customer_address`, `created_at`) VALUES
(2, 2, '53580.00', 'new', 'Admin', 'admin@gmail.com', '6720 Szeged Dugonics tér 3', '2026-01-05 17:32:30'),
(3, 2, '61440.00', 'new', 'Admin', 'admin@gmail.com', '6720 Szeged Dugonics tér 3', '2026-01-06 00:31:28'),
(4, 2, '25130.00', 'new', 'Admin', 'admin@gmail.com', '6720 Szeged Dugonics tér 3', '2026-01-06 19:06:33'),
(5, 2, '117896.00', 'new', 'Admin', 'admin@gmail.com', '6720 Szeged Dugonics tér 3', '2026-01-13 20:55:19'),
(6, 2, '11998.00', 'new', 'Admin', 'admin@gmail.com', '6720 Szeged Dugonics tér 3', '2026-01-28 18:54:57'),
(7, 2, '67508.00', 'new', 'Admin', 'admin@gmail.com', '6720 Szeged Dugonics tér 3', '2026-01-28 19:19:07');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `unit_price`, `qty`) VALUES
(4, 2, 3, 'Color Protect Mask', '5990.00', 3),
(5, 2, 2, 'Deep Cleanse Shampoo', '4190.00', 6),
(6, 2, 1, 'Fructis Goodbye Damage', '3490.00', 3),
(7, 3, 2, 'Deep Cleanse Shampoo', '4190.00', 8),
(8, 3, 1, 'Fructis Goodbye Damage', '3490.00', 8),
(9, 4, 2, 'Deep Cleanse Shampoo', '4190.00', 1),
(10, 4, 1, 'Fructis Goodbye Damage', '3490.00', 6),
(11, 5, 1, 'Fructis Goodbye Damage', '3490.00', 7),
(12, 5, 3, 'Color Protect Mask', '5991.00', 6),
(13, 5, 2, 'Deep Cleanse Shampoo', '4190.00', 3),
(14, 5, 4, 'Scalp Elixir Treatment', '8990.00', 5),
(15, 6, 3, 'Color Protect Mask', '5999.00', 2),
(16, 7, 2, 'Deep Cleanse Shampoo', '4190.00', 3),
(17, 7, 3, 'Color Protect Mask', '5999.00', 2),
(18, 7, 1, 'Fructis Goodbye Damage', '3490.00', 2),
(19, 7, 4, 'Scalp Elixir Treatment', '8990.00', 4);

--
-- Eseményindítók `order_items`
--
DELIMITER $$
CREATE TRIGGER `trg_oi_after_delete` AFTER DELETE ON `order_items` FOR EACH ROW BEGIN
  UPDATE products SET stock_qty = stock_qty + OLD.qty WHERE id = OLD.product_id;
  INSERT INTO stock_movements(product_id, qty_change, reason, ref_id)
  VALUES(OLD.product_id, +OLD.qty, 'order', OLD.order_id);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_oi_after_insert` AFTER INSERT ON `order_items` FOR EACH ROW BEGIN
  UPDATE products SET stock_qty = stock_qty - NEW.qty WHERE id = NEW.product_id;
  INSERT INTO stock_movements(product_id, qty_change, reason, ref_id)
  VALUES(NEW.product_id, -NEW.qty, 'order', NEW.order_id);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_oi_after_update` AFTER UPDATE ON `order_items` FOR EACH ROW BEGIN
  DECLARE d INT;
  SET d = NEW.qty - OLD.qty;               -- ha +, több fogyott; ha -, visszakerül
  IF d <> 0 THEN
    UPDATE products SET stock_qty = stock_qty - d WHERE id = NEW.product_id;
    INSERT INTO stock_movements(product_id, qty_change, reason, ref_id)
    VALUES(NEW.product_id, -d, 'order', NEW.order_id);
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `brand` varchar(80) NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` enum('shampoo','conditioner','mask','treatment','styling','other') NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `products`
--

INSERT INTO `products` (`id`, `brand`, `name`, `type`, `description`, `price`, `cost_price`, `stock_qty`, `image`, `is_active`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 'Garnier', 'Fructis Goodbye Damage', 'conditioner', 'Erősítő balzsam a károsult hajra', '3490.00', '1500.00', 259, 'uploads/products/garnier fructis.jpg', 1, 1, '2025-11-13 19:06:29', '2026-01-28 19:19:07'),
(2, 'Schwarzkopf', 'Deep Cleanse Shampoo', 'shampoo', 'Mélytisztító sampon zsíros fejbőrre.', '4190.00', '3000.00', 18, 'uploads/products/2.jpg', 1, 0, '2025-11-13 19:06:29', '2026-01-28 19:19:07'),
(3, 'L\'Oréal', 'Color Protect Mask', 'mask', 'Színvédő hajpakolás festett hajra.', '5999.00', '2000.00', 19, 'uploads/products/loreal protect mask.jpg', 1, 1, '2025-11-13 19:06:29', '2026-01-28 19:19:07'),
(4, 'Kérastase', 'Scalp Elixir Treatment', 'treatment', 'Fejbőrerősítő, kúrakezeléshez.', '8990.00', '6000.00', 21, 'uploads/products/4.jpg', 1, 0, '2025-11-13 19:06:29', '2026-01-28 19:19:20');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `path`, `is_primary`) VALUES
(1, 3, 'uploads/products/3_alt1.jpg', 0),
(2, 3, 'uploads/products/3_alt2.jpg', 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `public_profiles`
--

CREATE TABLE `public_profiles` (
  `id` int(11) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` varchar(255) DEFAULT NULL,
  `favorite_brand` varchar(80) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `public_profiles`
--

INSERT INTO `public_profiles` (`id`, `display_name`, `avatar`, `bio`, `favorite_brand`, `created_at`) VALUES
(1, 'Anna K.', 'uploads/profiles/anna.jpg', 'Színkezelt haj, heti pakolás.', 'L\'Oréal', '2025-11-13 19:06:29'),
(2, 'Bence', 'uploads/profiles/bence.jpg', 'Sportos fazon, mélytisztítás.', 'Schwarzkopf', '2025-11-13 19:06:29'),
(3, 'Luca', 'uploads/profiles/luca.jpg', 'Hajerősítő kúra.', 'Kérastase', '2025-11-13 19:06:29');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `public_profile_photos`
--

CREATE TABLE `public_profile_photos` (
  `id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `role` enum('before','after','other') NOT NULL DEFAULT 'other'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `public_profile_photos`
--

INSERT INTO `public_profile_photos` (`id`, `profile_id`, `path`, `role`) VALUES
(1, 1, 'uploads/profiles/anna_before.jpg', 'before'),
(2, 1, 'uploads/profiles/anna_after.jpg', 'after'),
(3, 2, 'uploads/profiles/bence.jpg', 'other'),
(4, 3, 'uploads/profiles/luca.jpg', 'other');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `public_profile_recos`
--

CREATE TABLE `public_profile_recos` (
  `id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `public_profile_recos`
--

INSERT INTO `public_profile_recos` (`id`, `profile_id`, `product_id`, `note`) VALUES
(1, 1, 3, 'Színvédő heti 1x'),
(2, 2, 2, 'Zsíros fejbőrre'),
(3, 3, 4, 'Hajerősítő kúra');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `services`
--

INSERT INTO `services` (`id`, `name`, `duration_minutes`, `price`, `description`, `is_active`) VALUES
(1, 'Női hajvágás', 45, '6999.00', 'Konzultáció + vágás + szárítás.', 1),
(2, 'Férfi hajvágás', 30, '4999.00', 'Gyors vágás és formázás.', 1),
(4, 'Fejbőr kezelő kúra', 60, '7999.00', 'Fejbőr hidratáló és élénkítő kezelés', 1),
(6, 'Szakáll vágás', 20, '3299.00', 'Szakáll vágás és formázás.', 1),
(7, 'Gyermek hajvágás (fiú)', 30, '3799.00', 'Gyerek hajvágás 14 éven aluli fiúknak', 1),
(8, 'Gyermek hajvágás (lány)', 45, '5199.00', 'Gyerek hajvágás 14 éven aluli lányoknak', 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty_change` int(11) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `reason` enum('purchase','order','adjustment','return','correction') NOT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `product_id`, `qty_change`, `unit_cost`, `reason`, `ref_id`, `created_at`) VALUES
(1, 1, 1, '0.00', 'purchase', NULL, '2026-01-28 18:51:46'),
(2, 1, 200, '3000.00', 'purchase', NULL, '2026-01-28 18:52:19'),
(3, 1, 10, '4000.00', 'purchase', NULL, '2026-01-28 18:52:28'),
(4, 1, 50, '1500.00', 'purchase', NULL, '2026-01-28 18:52:58'),
(5, 4, 21, '6000.00', 'purchase', NULL, '2026-01-28 18:53:19'),
(6, 3, 20, '2000.00', 'purchase', NULL, '2026-01-28 18:53:59'),
(7, 3, -2, NULL, 'order', 6, '2026-01-28 18:54:57'),
(8, 3, 2, '0.00', 'purchase', NULL, '2026-01-28 19:08:01'),
(9, 2, 18, '3000.00', 'purchase', NULL, '2026-01-28 19:08:32'),
(10, 2, 3, '0.00', 'purchase', NULL, '2026-01-28 19:18:57'),
(11, 3, 1, '0.00', 'purchase', NULL, '2026-01-28 19:19:00'),
(12, 2, -3, NULL, 'order', 7, '2026-01-28 19:19:07'),
(13, 3, -2, NULL, 'order', 7, '2026-01-28 19:19:07'),
(14, 1, -2, NULL, 'order', 7, '2026-01-28 19:19:07'),
(15, 4, -4, NULL, 'order', 7, '2026-01-28 19:19:07'),
(16, 4, 4, '0.00', 'purchase', NULL, '2026-01-28 19:19:20');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('owner','customer') NOT NULL DEFAULT 'customer',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `address`, `password_hash`, `avatar`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Lakatos Brendon', 'lakatos@gmail.com', NULL, '$2y$10$c/G2RWUjlqlDJoc436BNduAwN5vHFsPnXXvFm6xGxpW7UZWiACIze', NULL, 'customer', '2026-01-02 14:41:21', '2026-01-02 14:41:47'),
(2, 'Admin', 'admin@gmail.com', '6720 Szeged Dugonics tér 3', '$2y$10$Fc9z1QVMu.KZksP3y62Vze0X9SN4y1/0E4mY3aAQQaoeapadmHUT.', '/uploads/avatars/u2_1767622454.png', 'owner', '2026-01-02 14:58:34', '2026-01-05 17:06:45'),
(3, 'Knyihár Roland', 'knyiharroland@gmail.com', NULL, '$2y$10$7V/mwnWtgshqYBbQSjIy1.ayx9KX.oxB0s37LXPmGKK.RdM9jMNCm', NULL, 'customer', '2026-01-02 14:55:56', '2026-01-02 14:55:56'),
(5, 'Brendon az úr', 'lakatos.b@gmail.com', NULL, '$2y$10$D9HFaGmMZSfyDjTW9PkQMuSCVw5wQ6tbF3WpkbaJOORKKuUU8npLe', '/uploads/avatars/u5_1767540046.png', 'customer', '2026-01-04 16:19:42', '2026-01-04 16:20:46'),
(6, 'Teszt Elek123', 'teszt123@gmail.com', NULL, '$2y$10$D83xinVKs1kN1tlJNS9LUuMlya2gj5wHcq60Sk97APduUiegvMYfO', NULL, 'customer', '2026-01-04 16:30:48', '2026-01-04 16:30:48'),
(7, 'Stan', 'stan@gmail.com', NULL, '$2y$10$WRvv6fl/Pd8UW4A0M/yT6.SLSfjDW2.lYSg6wn7LOKWa8hmppoGYK', NULL, 'customer', '2026-01-05 17:07:56', '2026-01-05 17:07:56');

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_a_user` (`user_id`),
  ADD KEY `idx_a_entity` (`entity`,`entity_id`);

--
-- A tábla indexei `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_b_user` (`user_id`),
  ADD KEY `fk_b_service` (`service_id`),
  ADD KEY `idx_bookings_dt` (`appointment_datetime`);

--
-- A tábla indexei `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_o_user` (`user_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_created` (`created_at`);

--
-- A tábla indexei `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_oi_order` (`order_id`),
  ADD KEY `fk_oi_product` (`product_id`);

--
-- A tábla indexei `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_brand` (`brand`),
  ADD KEY `idx_products_type` (`type`),
  ADD KEY `idx_products_name` (`name`),
  ADD KEY `idx_products_stock` (`stock_qty`),
  ADD KEY `idx_products_prices` (`price`,`cost_price`);

--
-- A tábla indexei `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pi_product` (`product_id`);

--
-- A tábla indexei `public_profiles`
--
ALTER TABLE `public_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pp_name` (`display_name`),
  ADD KEY `idx_pp_brand` (`favorite_brand`);

--
-- A tábla indexei `public_profile_photos`
--
ALTER TABLE `public_profile_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ppp_profile` (`profile_id`);

--
-- A tábla indexei `public_profile_recos`
--
ALTER TABLE `public_profile_recos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_profile_product` (`profile_id`,`product_id`),
  ADD KEY `fk_ppr_product` (`product_id`);

--
-- A tábla indexei `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sm_prod` (`product_id`),
  ADD KEY `idx_sm_reason` (`reason`,`ref_id`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT a táblához `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT a táblához `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT a táblához `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT a táblához `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT a táblához `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT a táblához `public_profiles`
--
ALTER TABLE `public_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT a táblához `public_profile_photos`
--
ALTER TABLE `public_profile_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `public_profile_recos`
--
ALTER TABLE `public_profile_recos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT a táblához `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT a táblához `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `fk_a_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Megkötések a táblához `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_b_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_b_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Megkötések a táblához `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_o_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Megkötések a táblához `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;

--
-- Megkötések a táblához `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_pi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Megkötések a táblához `public_profile_photos`
--
ALTER TABLE `public_profile_photos`
  ADD CONSTRAINT `fk_ppp_profile` FOREIGN KEY (`profile_id`) REFERENCES `public_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Megkötések a táblához `public_profile_recos`
--
ALTER TABLE `public_profile_recos`
  ADD CONSTRAINT `fk_ppr_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ppr_profile` FOREIGN KEY (`profile_id`) REFERENCES `public_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Megkötések a táblához `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `fk_sm_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
