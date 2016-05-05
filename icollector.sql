-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Počítač: localhost
-- Vytvořeno: Čtv 05. kvě 2016, 16:34
-- Verze serveru: 5.6.28
-- Verze PHP: 5.5.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `icollector`
--
CREATE DATABASE IF NOT EXISTS `icollector` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `icollector`;

-- --------------------------------------------------------

--
-- Struktura tabulky `categories`
--

CREATE TABLE `categories` (
  `category_id` int(10) NOT NULL DEFAULT '0',
  `name` varchar(40) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `collectibles`
--

CREATE TABLE `collectibles` (
  `collectible_id` int(10) NOT NULL,
  `name` varchar(40) NOT NULL,
  `origin` text,
  `description` text,
  `user_id` int(10) NOT NULL,
  `category_id` int(10) DEFAULT NULL,
  `picture` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Vypisuji data pro tabulku `collectibles`
--

INSERT INTO `collectibles` (`collectible_id`, `name`, `origin`, `description`, `user_id`, `category_id`, `picture`) VALUES
(1, 'Bradavičák', 'Nevim', 'Toho chci', 1, NULL, '/icollector/www/images/location/icollector1.jpg'),
(3, 'Gulliver', 'Zakoupen na aukro.cz. Reklamní předmět Kofoly.', 'Největší ze všech.', 1, NULL, '/icollector/www/images/location/icollector3.JPG'),
(4, 'Motýl', 'Chycen na poli.', 'Krásný motýlek.', 1, NULL, '/icollector/www/images/location/icollector4.jpg'),
(5, 'Tigr', 'Z buše.', 'Hezký tygr.', 1, NULL, '/icollector/www/images/location/icollector5.jpg'),
(6, 'Motýl', 'z pole', 'motýlek', 1, NULL, '/icollector/www/images/location/icollector6.jpg'),
(7, 'Motýl', 'z lessa', 'pěkný', 1, NULL, '/icollector/www/images/location/icollector7.jpg'),
(8, 'Pavel', 'ze školy', 'kluk', 1, NULL, '/icollector/www/images/location/icollector8.jpg');

-- --------------------------------------------------------

--
-- Struktura tabulky `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(10) NOT NULL,
  `text` text NOT NULL,
  `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(10) NOT NULL,
  `topic_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `reciever_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `topics`
--

CREATE TABLE `topics` (
  `topic_id` int(10) NOT NULL,
  `title` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE `users` (
  `user_id` int(10) NOT NULL,
  `firstName` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `surname` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `username` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  `confirmedEmail` varchar(40) COLLATE utf8_czech_ci DEFAULT NULL,
  `security` varchar(40) COLLATE utf8_czech_ci DEFAULT NULL,
  `notification` varchar(40) COLLATE utf8_czech_ci DEFAULT NULL,
  `regDateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text COLLATE utf8_czech_ci,
  `phone` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
  `picture` text COLLATE utf8_czech_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `users`
--

INSERT INTO `users` (`user_id`, `firstName`, `surname`, `username`, `password`, `email`, `confirmedEmail`, `security`, `notification`, `regDateTime`, `description`, `phone`, `picture`) VALUES
(1, 'Richard', 'Šouta', 'icollector', '$2y$10$9vVo5UU6CaSGRTx1G1vASOImn.oW2XIugul/q7l1MIQI8yw0/xfmm', 'riky@souta.cz', 'fotz8muyhb', NULL, NULL, '2016-04-29 16:41:03', 'Sbírám známky.', '777777777777', '/icollector/www/images/user/icollector.jpg'),
(4, 'Zina', 'Tsapiv', 'zinkamay', '$2y$10$AYHtzTtV7BDOLAmJeKhjNO7gDwRiT7uWolKw30mMQC.eA1oEgBHTO', 'driger.miska@seznam.cz', 'hao3eisig1', NULL, NULL, '2016-05-02 03:30:14', NULL, NULL, NULL),
(5, 'Richard', 'Král', 'kral', '$2y$10$v29PmN2Ns0Ru4aopEzTGF.1X/PL.XNK28wIV1qx26C9tsLpOis0Ue', 'richard@souta.cz', 'wb9l2ymfct', NULL, NULL, '2016-05-02 03:32:36', NULL, NULL, NULL);

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Klíče pro tabulku `collectibles`
--
ALTER TABLE `collectibles`
  ADD PRIMARY KEY (`collectible_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Klíče pro tabulku `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Klíče pro tabulku `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `reciever_id` (`reciever_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Klíče pro tabulku `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`topic_id`);

--
-- Klíče pro tabulku `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username_2` (`username`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `collectibles`
--
ALTER TABLE `collectibles`
  MODIFY `collectible_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT pro tabulku `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pro tabulku `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pro tabulku `topics`
--
ALTER TABLE `topics`
  MODIFY `topic_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT pro tabulku `categories`
--  
ALTER TABLE `categories`
  MODIFY `category_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `collectibles`
--
ALTER TABLE `collectibles`
  ADD CONSTRAINT `collectibles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `collectibles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Omezení pro tabulku `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`topic_id`);

--
-- Omezení pro tabulku `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`reciever_id`) REFERENCES `users` (`user_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
