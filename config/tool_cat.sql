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
-- Tabela Truncate przed wstawieniem `tool_cat`
--

TRUNCATE TABLE `tool_cat`;
--
-- Zrzut danych tabeli `tool_cat`
--

INSERT INTO `tool_cat` (`cat`, `tool`, `sortorder`) VALUES
(1, 22, 0),
(1, 21, 1),
(1, 20, 2),
(1, 19, 3),
(10, 27, 0),
(10, 25, 1),
(12, 2, 2),
(2, 13, 0),
(2, 11, 2),
(3, 26, 0),
(3, 10, 1),
(6, 28, 0),
(2, 12, 1),
(6, 7, 2),
(1, 18, 4),
(7, 6, 2),
(7, 5, 1),
(7, 4, 0),
(12, 1, 1),
(8, 38, 0),
(12, 32, 0),
(11, 29, 3),
(11, 30, 2),
(11, 31, 0),
(0, 35, 0),
(8, 37, 1),
(8, 36, 2),
(6, 34, 1),
(11, 33, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
