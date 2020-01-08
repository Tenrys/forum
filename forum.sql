-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 08, 2020 at 05:34 PM
-- Server version: 5.7.26
-- PHP Version: 7.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `forum`
--
CREATE DATABASE IF NOT EXISTS `forum` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `forum`;

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
CREATE TABLE IF NOT EXISTS `conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `id_auteur` int(11) NOT NULL,
  `id_topic` int(11) NOT NULL,
  `creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `verrouillage` tinyint(1) NOT NULL DEFAULT '0',
  `epingle` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_topic` (`id_topic`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `nom`, `id_auteur`, `id_topic`, `creation`, `verrouillage`, `epingle`) VALUES
(1, 'Premiere conversation', 1, 1, '2019-12-17 19:39:41', 1, 1),
(2, 'Seconde conversation Ã©ditÃ©e', 1, 1, '2019-12-17 20:13:15', 1, 1),
(6, 'TroisiÃ¨me topic!', 1, 1, '2020-01-06 07:48:39', 0, 0),
(9, 'Conversation crÃ©e par un membre', 2, 1, '2020-01-08 18:18:52', 0, 0),
(10, 'C\'est pas mal par ici', 3, 4, '2020-01-08 18:21:32', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contenu` text NOT NULL,
  `id_auteur` int(11) NOT NULL,
  `id_conversation` int(11) NOT NULL,
  `creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_conversation` (`id_conversation`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `contenu`, `id_auteur`, `id_conversation`, `creation`) VALUES
(1, 'Premier message', 1, 1, '2019-12-17 19:39:51'),
(6, 'Bruh!!!', 1, 2, '2020-01-05 11:02:44'),
(8, 'Message de test ...', 1, 6, '2020-01-06 07:48:40'),
(10, 'Test 123\r\n', 2, 6, '2020-01-06 08:35:13'),
(13, 'Bwaah\r\n', 2, 9, '2020-01-08 18:18:52'),
(14, 'HÃ© oui !', 3, 10, '2020-01-08 18:21:32'),
(15, 'Un autre message', 2, 10, '2020-01-08 18:23:53');

-- --------------------------------------------------------

--
-- Table structure for table `reactions`
--

DROP TABLE IF EXISTS `reactions`;
CREATE TABLE IF NOT EXISTS `reactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_message` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_message` (`id_message`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `reactions`
--

INSERT INTO `reactions` (`id`, `id_message`, `type`, `id_utilisateur`) VALUES
(12, 6, 1, 1),
(16, 6, 1, 2),
(21, 14, 1, 2),
(22, 15, 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

DROP TABLE IF EXISTS `topics`;
CREATE TABLE IF NOT EXISTS `topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `rang_min` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `nom`, `description`, `rang_min`) VALUES
(1, 'Test', 'Description', 1),
(4, 'Visiteurs', 'Bienvenue !', 0),
(5, 'Admins seulement', 'Top secret!', 3),
(6, 'Coucou', 'Toto', 1);

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_rang` int(11) NOT NULL DEFAULT '1',
  `naissance` date DEFAULT NULL,
  `bio` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `login`, `password`, `id_rang`, `naissance`, `bio`, `email`, `inscription`) VALUES
(1, 'admin', '$2y$10$g2nhKiO1qrlEB1SsEPAzYuQ7qU1bI2Tz50i3TCDrk7nd55Tb5nH0y', 3, '1999-11-15', 'Une trÃ¨s longue biographie...', 'test@email.com', '2019-12-17 17:32:50'),
(2, 'test', '$2y$10$rgotvUSTBv6uZl5I2gLM8Ox4BO6uS4GTXspEd.4XljNO01opkdsjS', 1, NULL, NULL, NULL, '2019-12-17 18:19:49'),
(3, 'enzo', '$2y$10$cD1mowulsxUEelQRX.YuWeeNUZ7igYQrbtYCMH2Kdvg.gm8xZFb6K', 3, NULL, 'Blablabla', NULL, '2020-01-08 18:20:30');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`id_topic`) REFERENCES `topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`id_conversation`) REFERENCES `conversations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reactions`
--
ALTER TABLE `reactions`
  ADD CONSTRAINT `reactions_ibfk_1` FOREIGN KEY (`id_message`) REFERENCES `messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
