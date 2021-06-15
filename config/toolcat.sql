-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas generowania: 09 Cze 2021, 06:20
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
-- Tabela Truncate przed wstawieniem `toolcat`
--

TRUNCATE TABLE `toolcat`;
--
-- Zrzut danych tabeli `toolcat`
--

INSERT INTO `toolcat` (`id`, `name`, `parent`, `defaulttool`, `icon`, `sortorder`, `language`) VALUES
(7, 'Cutting tool', 0, 6, 'cut.png', 0, 'mosim'),
(8, 'By hand', 0, 0, 'hand.png', 0, 'mosim'),
(6, 'Cleaning tool', 0, 0, 'clean.png', 0, 'mosim'),
(5, 'Insertion tool', 0, 0, 'clip.png', 0, 'mosim'),
(1, 'Screwing tool', 0, 0, 'fasten.png', 0, 'mosim'),
(2, 'Hammer', 0, 0, 'hammer.png', 0, 'mosim'),
(3, 'Glueing tool', 0, 0, 'glue.png', 0, 'mosim');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
