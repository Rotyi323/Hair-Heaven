-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2023. Ápr 18. 17:47
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
-- Adatbázis: `telefonfix`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `megrendelesek`
--

CREATE TABLE `megrendelesek` (
  `id` int(11) NOT NULL,
  `kosar` longtext NOT NULL,
  `nev` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cim` varchar(255) NOT NULL,
  `orszag` varchar(255) NOT NULL,
  `megye` varchar(255) NOT NULL,
  `iranyitoszam` varchar(255) NOT NULL,
  `fizetesimod` varchar(255) NOT NULL,
  `datum` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- A tábla adatainak kiíratása `megrendelesek`
--

INSERT INTO `megrendelesek` (`id`, `kosar`, `nev`, `email`, `cim`, `orszag`, `megye`, `iranyitoszam`, `fizetesimod`, `datum`) VALUES
(10, '{\"num_of_items\":1,\"Total\":269999,\"items\":[{\"name\":\"Samsung galaxy S21 ultra\",\"quantity\":1,\"id\":2,\"Item_Total\":269999,\"Item_Price_default\":269999}]}', 'szemely', 'szemely@gmail.com', 'Békés Hajnal utca 60', 'Magyarország', 'Békés', '5610', 'Bankkártya', '2023-03-09 09:51:43'),
(12, '{\"num_of_items\":2,\"Total\":519998,\"items\":[{\"name\":\"Huawei P30 lite\",\"quantity\":1,\"id\":3,\"Item_Total\":69999,\"Item_Price_default\":69999},{\"name\":\"iPhone 14\",\"quantity\":1,\"id\":4,\"Item_Total\":449999,\"Item_Price_default\":449999}]}', 'yxy', 'r@r', 'Békéscsaba Luther utca 6', 'Magyarország', 'Békés', '5600', 'PayPal', '2023-03-15 17:39:36'),
(13, '{\"num_of_items\":5,\"Total\":955994,\"items\":[{\"name\":\"iPhone 7\",\"quantity\":2,\"id\":7,\"Item_Total\":107998,\"Item_Price_default\":53999},{\"name\":\"iPhone 14\",\"quantity\":1,\"id\":4,\"Item_Total\":449999,\"Item_Price_default\":449999},{\"name\":\"Huawei P30 lite\",\"quantity\":1,\"id\":3,\"Item_Total\":69999,\"Item_Price_default\":69999},{\"name\":\"Xiaomi Redmi Note 5\",\"quantity\":1,\"id\":8,\"Item_Total\":57999,\"Item_Price_default\":57999},{\"name\":\"Samsung galaxy S21 ultra\",\"quantity\":1,\"id\":2,\"Item_Total\":269999,\"Item_Price_default\":269999}]}', 'Knyihár Roland János', 'knyiharroland@gmail.com', 'Békéscsaba Kertész utca 34', 'Magyarország', 'Békés', '5600', 'Hitelkártya', '2023-03-16 08:02:41'),
(16, '{\"num_of_items\":2,\"Total\":519998,\"items\":[{\"name\":\"Huawei P30 lite\",\"quantity\":1,\"id\":3,\"Item_Total\":69999,\"Item_Price_default\":69999},{\"name\":\"iPhone 14\",\"quantity\":1,\"id\":4,\"Item_Total\":449999,\"Item_Price_default\":449999}]}', 'Roland', 'valami@valami', 'Temesvár Strada Platanilor 30', 'Románia', 'Temes', '5000', 'Hitelkártya', '2023-03-18 11:24:30'),
(18, '{\"num_of_items\":3,\"Total\":500997,\"items\":[{\"name\":\"Samsung galaxy S21 ultra\",\"quantity\":1,\"id\":2,\"Item_Total\":269999,\"Item_Price_default\":269999},{\"name\":\"Xiaomi Redmi Note 5\",\"quantity\":1,\"id\":8,\"Item_Total\":57999,\"Item_Price_default\":57999},{\"name\":\"Samsung Galaxy A72\",\"quantity\":1,\"id\":9,\"Item_Total\":172999,\"Item_Price_default\":172999}]}', 'teszt', 'teszt@teszt', 'Békéscsaba Luther utca 7', 'Magyarország', 'Békés', '5600', 'PayPal', '2023-03-18 11:30:43'),
(19, '{\"num_of_items\":2,\"Total\":339998,\"items\":[{\"name\":\"Huawei P30 lite\",\"quantity\":1,\"id\":3,\"Item_Total\":69999,\"Item_Price_default\":69999},{\"name\":\"Samsung galaxy S21 ultra\",\"quantity\":1,\"id\":2,\"Item_Total\":269999,\"Item_Price_default\":269999}]}', 'Szegedi Ember', 'szeged@szeged', 'Szeged', 'Magyarország', 'Csongrád', '6700', 'Hitelkártya', '2023-03-18 11:44:28'),
(20, '{\"num_of_items\":1,\"Total\":69999,\"items\":[{\"name\":\"Huawei P30 lite\",\"quantity\":1,\"id\":3,\"Item_Total\":69999,\"Item_Price_default\":69999}]}', 'Knyihár Roland János', 'roland@roland', 'Békéscsaba Rózsa utca 6', 'Magyarország', 'Békés', '5600', 'Bankkártya', '2023-03-28 15:38:18'),
(21, '{\"num_of_items\":1,\"Total\":69999,\"items\":[{\"name\":\"Huawei P30 lite\",\"quantity\":1,\"id\":3,\"Item_Total\":69999,\"Item_Price_default\":69999}]}', 'Knyihár Roland', 'roland@roland', 'Békéscsaba Kertész utca 15', 'Magyarország', 'Békés', '100', 'Bankkártya', '2023-03-28 15:52:49'),
(22, '{\"num_of_items\":2,\"Total\":442998,\"items\":[{\"name\":\"Samsung galaxy S21 ultra\",\"quantity\":1,\"id\":2,\"Item_Total\":269999,\"Item_Price_default\":269999},{\"name\":\"Samsung Galaxy A72\",\"quantity\":1,\"id\":9,\"Item_Total\":172999,\"Item_Price_default\":172999}]}', 'admin', 'admin@admin', 'Békéscsaba Ihász utca 5', 'Magyarország', 'Békés', '5600', 'PayPal', '2023-04-03 16:10:25'),
(23, '{\"num_of_items\":1,\"Total\":247998,\"items\":[{\"name\":\"LG G7 ThinQ\",\"quantity\":2,\"id\":10,\"Item_Total\":247998,\"Item_Price_default\":123999}]}', 'admin', 'admin@admin', 'Békéscsaba Őr utca 5', 'Magyarország', 'Békés', '5600', 'PayPal', '2023-04-04 14:59:32'),
(24, '{\"num_of_items\":2,\"Total\":67998,\"items\":[{\"name\":\"Xiaomi Redmi Note 5\",\"quantity\":1,\"id\":8,\"Item_Total\":57999,\"Item_Price_default\":57999},{\"name\":\"Nokia 3310\",\"quantity\":1,\"id\":13,\"Item_Total\":9999,\"Item_Price_default\":9999}]}', 'Guest Vendég', 'guest@vendeg', 'Békéscsaba', 'Magyarország', 'Békés', '5600', 'Hitelkártya', '2023-04-09 13:38:59');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `szerviz`
--

CREATE TABLE `szerviz` (
  `id` int(11) NOT NULL,
  `telefon` varchar(255) NOT NULL,
  `hiba` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `elerhetoseg` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- A tábla adatainak kiíratása `szerviz`
--

INSERT INTO `szerviz` (`id`, `telefon`, `hiba`, `email`, `elerhetoseg`) VALUES
(13, 'Huawei P30 lite', 'Hangszóró', 'r@r', '+3241241'),
(14, 'Xiaomi Redmi Note 5', 'Egyéb', 'r@r', '+22'),
(15, 'Xiaomi Redmi Note 5', 'Kijelző', 'kk@gmail.com', '+0'),
(16, 'LG G7 ThinQ', 'Kamera', 'knyiharroland@gmail.com', '06306751894'),
(17, 'Samsung galaxy S21 ultra', 'Hangszóró', 'random@random', '+3650145645445'),
(18, 'Nokia 3310', 'Hangszóró', 'admin@admin', '+3241241');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `termekek`
--

CREATE TABLE `termekek` (
  `id` int(255) NOT NULL,
  `nev` varchar(255) NOT NULL,
  `marka` varchar(100) NOT NULL,
  `ar` int(10) NOT NULL,
  `leiras` varchar(255) NOT NULL,
  `kep` longtext NOT NULL,
  `letrehozva` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `termekek`
--

INSERT INTO `termekek` (`id`, `nev`, `marka`, `ar`, `leiras`, `kep`, `letrehozva`) VALUES
(2, 'Samsung galaxy S21 ultra', 'Samsung', 269999, 'Kamera: 108 megapixel Memória: 128GB Processzor: 8 magos Akkumulátor: 5000mAh', '/feltoltesek/samsung galaxy s21.png', '2023-03-07 09:47:41'),
(3, 'Huawei P30 lite', 'Huawei', 69999, ' Kamera: 48 megapixel Memória: 128GB Processzor: HiSilicon Kirin 710 Akkumulátor: 3340mAh', '/feltoltesek/huawei p30 lite.jpg', '2023-03-07 09:47:41'),
(4, 'iPhone 14', 'Apple', 449999, 'Kamera:12 megapixel Memória:128GB Processzor: A15 Bionic chip Akkumulátor: 3279mAh  ', '/feltoltesek/IPHONE14.jpg', '2023-03-07 10:35:50'),
(7, 'iPhone 7', 'Apple', 53999, 'Kamera: 12 megapixeles  Memória: 32GB Akkumulátor:1960mAh Processzor: Quad-core 2.34 GHz (2x Hurricane + 2x Zephyr)', '/feltoltesek/iphone7.jpg', '2023-03-15 15:33:17'),
(8, 'Xiaomi Redmi Note 5', 'Xiaomi', 57999, 'Kamera: 12 megapixel Processzor:4 x 1,6 GHz + 4 x 1,8 GHz-es Kryo 260 Akkumulátor: 4000mAh Memória: 64GB', '/feltoltesek/xiaomi redmi note 5.jpg', '2023-03-15 15:45:25'),
(9, 'Samsung Galaxy A72', 'Samsung', 172999, 'Kamera: 64 megapixel Processzor: Qualcomm Snapdragon 720G Memória: 128GB  Akkumulátor: 5000mAh', '/feltoltesek/samsung galaxy a72.jpg', '2023-03-16 08:15:11'),
(10, 'LG G7 ThinQ', 'LG', 123999, 'Kamera: 16 megapixel Processzor: Qualcomm Snapdragon 845 Memória:128GB  Akkumulátor: 3000mAh', '/feltoltesek/lg g7 thinQ.jpg', '2023-03-16 08:24:57'),
(13, 'Nokia 3310', 'Nokia', 9999, 'Kamera: 2 megapixel Akkumulátor: 900mAh  ', '/feltoltesek/Nokia3310.png', '2023-03-20 14:36:22');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `jog` varchar(255) NOT NULL DEFAULT 'guest'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `jog`) VALUES
(3, 'Roland', 'r@r', '$2y$10$PWp242fWhq7dsKWwXW4ob.tx9mkAM0GNheH8QyR3JnTFuzruJ4SpS', 'admin'),
(5, 'roland', 'roland@roland', '$2y$10$ggreg6vHcf06BvK5OGbLIurTb4PGDLt/ULj2Hi3FExAUpHVMdv7SG', 'guest'),
(6, 'Knyihár Roland János', 'knyiharroland@gmail.com', '$2y$10$cc0uAFLibYQBe4OoE7zXfOJipoOZvlsQpylTCRJJhQR.dneRz.aKi', 'guest'),
(7, 'Randomember', 'randomember@gmail.com', '$2y$10$7ViQSZPsg0WLxZRNjwh9O.WYiWMoh4KrmAZH7r0JMbGv1uMcqUqz.', 'guest'),
(8, 'guest', 'guest@gmail.com', '$2y$10$KqtEIB4JFoZ7ZxcvOk7rZuKYZcQ8/ESlt9FTmlZyuDzay7cdwMJWG', 'guest'),
(9, 'Teszt Elek', 'tesztelek@freemail.hu', '$2y$10$nmNvQvgTc1XpDl5i.5Du0OHQUtt9PBbLaLvamnwb.dnJJNiAeZpjO', 'guest'),
(11, 'valaki', 'valaki@valaki', '$2y$10$gua7/tBVN4VJ9KSdsc3MN.Qr0jbsPgc.j0RGAIOm8Yl/b56tQDzwW', 'guest'),
(13, 'guestvendeg', 'guest@vendeg', '$2y$10$i9fHrSH3PauGxJtJZw.GcOsDliFUGQS9qLKg1nC8lqoesFDZcIXgi', 'guest'),
(14, 'admin', 'admin@admin', '$2y$10$s7dZVF665dMcuaO92.7JTuhoi2j7lj86WgdyGbRW7NeDe9TVLPhVy', 'admin');

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `megrendelesek`
--
ALTER TABLE `megrendelesek`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `szerviz`
--
ALTER TABLE `szerviz`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `termekek`
--
ALTER TABLE `termekek`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `megrendelesek`
--
ALTER TABLE `megrendelesek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT a táblához `szerviz`
--
ALTER TABLE `szerviz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT a táblához `termekek`
--
ALTER TABLE `termekek`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
