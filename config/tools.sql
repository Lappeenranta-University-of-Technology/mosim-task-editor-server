-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas generowania: 09 Cze 2021, 06:01
-- Wersja serwera: 5.7.33-0ubuntu0.16.04.1
-- Wersja PHP: 7.0.33-0ubuntu0.16.04.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `mosim`
--

--
-- Tabela Truncate przed wstawieniem `tools`
--

TRUNCATE TABLE `tools`;
--
-- Zrzut danych tabeli `tools`
--

INSERT INTO `tools` (`id`, `name`, `sortorder`, `language`) VALUES
(1, 'Left hand', 0, 'mosim'),
(2, 'Right hand', 3, 'mosim'),
(32, 'Both hands', 0, 'mosim'),
(4, 'Scissors', 6, 'mosim'),
(5, 'Wire cutter', 2, 'mosim'),
(6, 'Cutter', 4, 'mosim'),
(18, 'Wrench', 0, 'mosim'),
(7, 'Cloth', 13, 'mosim'),
(27, 'Compression tool', 0, 'mosim'),
(10, 'Glue gun', 12, 'mosim'),
(11, 'Soft hammer', 10, 'mosim'),
(12, 'Rubber hammer', 11, 'mosim'),
(13, 'Fitter\'s hammer', 17, 'mosim'),
(28, 'Broom', 0, 'mosim'),
(25, 'Clip tool', 0, 'mosim'),
(26, 'Glue tube', 0, 'mosim'),
(19, 'Screwdriver', 0, 'mosim'),
(20, 'Ratchet', 0, 'mosim'),
(21, 'EC screwdriver', 0, 'mosim'),
(22, 'Cordless screwdriver', 0, 'mosim'),
(29, 'Camera', 0, 'mosim'),
(30, 'Eyes', 0, 'mosim'),
(31, 'Template', 0, 'mosim'),
(33, 'Scanner', 0, 'mosim'),
(34, 'Vacuum Cleaner', 0, 'mosim'),
(35, 'Hand', 0, 'mosim'),
(36, 'RightHand', 0, 'mosim'),
(37, 'LeftHand', 0, 'mosim'),
(38, 'BothHands', 0, 'mosim');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
