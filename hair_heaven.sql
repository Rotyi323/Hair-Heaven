-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2026. Jan 02. 15:09
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
(1, 'Őszi ápolás', 'uploads/banners/fall.jpg', '/aruhaz.php?type=mask', 1),
(2, 'Top ajánlatok', 'uploads/banners/top.jpg', '/#ajanlatok', 1);

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
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `products`
--

INSERT INTO `products` (`id`, `brand`, `name`, `type`, `description`, `price`, `image`, `is_active`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 'Garnier', 'Fructis Goodbye Damage', 'conditioner', 'Erősítő balzsam a károsult hajra', '3490.00', 'uploads/products/garnier fructis.jpg', 1, 1, '2025-11-13 19:06:29', '2025-11-13 21:06:29'),
(2, 'Schwarzkopf', 'Deep Cleanse Shampoo', 'shampoo', 'Mélytisztító sampon zsíros fejbőrre.', '4190.00', 'uploads/products/2.jpg', 1, 0, '2025-11-13 19:06:29', '2025-11-13 19:06:29'),
(3, 'L\'Oréal', 'Color Protect Mask', 'mask', 'Színvédő hajpakolás festett hajra.', '5990.00', 'uploads/products/loreal protect mask.jpg', 1, 1, '2025-11-13 19:06:29', '2025-11-13 20:00:45'),
(4, 'Kérastase', 'Scalp Elixir Treatment', 'treatment', 'Fejbőrerősítő, kúrakezeléshez.', '8990.00', 'uploads/products/4.jpg', 1, 0, '2025-11-13 19:06:29', '2025-11-13 19:06:29');

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
(1, 'Női hajvágás', 45, '6900.00', 'Konzultáció + vágás + szárítás.', 1),
(2, 'Férfi hajvágás', 30, '4900.00', 'Gyors vágás és formázás.', 1),
(3, 'Fejbőrkezelés', 40, '8900.00', 'Kíméletes fejbőrápoló kúra.', 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('owner','customer') NOT NULL DEFAULT 'customer',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `avatar`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Lakatos Brendon', 'lakatos@gmail.com', '$2y$10$c/G2RWUjlqlDJoc436BNduAwN5vHFsPnXXvFm6xGxpW7UZWiACIze', NULL, 'customer', '2026-01-02 14:41:21', '2026-01-02 14:41:47'),
(3, 'Knyihár Roland', 'knyiharroland@gmail.com', '$2y$10$7V/mwnWtgshqYBbQSjIy1.ayx9KX.oxB0s37LXPmGKK.RdM9jMNCm', NULL, 'customer', '2026-01-02 14:55:56', '2026-01-02 14:55:56'),
(4, 'Admin', 'admin@gmail.com', '$2y$10$jb5qW.5z7LWDkBH6jWxhROn0LgpwII0KTCIOz79/dPoH8Xh0XU26W', NULL, 'customer', '2026-01-02 14:58:34', '2026-01-02 14:58:34');

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
  ADD KEY `idx_products_name` (`name`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT a táblához `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
