-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas generowania: 09 Cze 2021, 05:57
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
-- Tabela Truncate przed wstawieniem `tasktypes`
--

TRUNCATE TABLE `tasktypes`;
--
-- Zrzut danych tabeli `tasktypes`
--

INSERT INTO `tasktypes` (`id`, `name`, `icon`, `parent`, `language`, `sortorder`) VALUES
(1, 'Position', 'cogs-solid.svg', 0, 'mosim', 0),
(2, 'Tighten', 'cogs-solid.svg', 0, 'mosim', 1),
(3, 'Check', 'inspect.png', 0, 'mosim', 2),
(4, 'Insert', 'pressin.png', 0, 'mosim', 3),
(5, 'Apply', 'glue.png', 0, 'mosim', 4),
(7, 'Clean', 'clean.png', 0, 'mosim', 6),
(8, 'Cut', 'cut.png', 0, 'mosim', 7),
(9, 'Position', '', 1, 'mosim', 0),
(11, 'Stick on', '', 1, 'mosim', 0),
(12, 'Tighten loose', '', 2, 'mosim', 0),
(13, 'Tighten fully', '', 2, 'mosim', 0),
(14, 'Tighten with torque', '', 2, 'mosim', 0),
(15, 'Untighten', '', 2, 'mosim', 0),
(16, 'Visual check', '', 3, 'mosim', 0),
(17, 'Manual check', '', 3, 'mosim', 0),
(18, 'Insert part', '', 4, 'mosim', 0),
(19, 'Press in part', '', 4, 'mosim', 0),
(20, 'Glue', '', 5, 'mosim', 0),
(21, 'Snap clip', '', 4, 'mosim', 0),
(22, 'Unsnap clip', '', 4, 'mosim', 0),
(23, 'Clean', '', 7, 'mosim', 0),
(24, 'Cut to length', '', 8, 'mosim', 0),
(25, 'Make a cut', '', 8, 'mosim', 0),
(26, 'Cut to shape', '', 8, 'mosim', 0),
(27, 'Subassembly', '', 0, 'mosim', 8),
(28, 'Assemble', '', 27, 'mosim', 0),
(29, 'Remove', '', 1, 'mosim', 0),
(30, 'Check and adapt', '', 3, 'mosim', 0),
(31, 'Insert electrical connector', '', 4, 'mosim', 0),
(32, 'Grease', '', 5, 'mosim', 0),
(33, 'Route cable', '', 0, 'mosim', 0),
(34, 'Open/close', '', 0, 'mosim', 0),
(35, 'Control', '', 0, 'mosim', 0),
(36, 'Move', '', 0, 'mosim', 0),
(37, 'Document/read', '', 0, 'mosim', 0),
(38, 'Fix', '', 0, 'mosim', 0),
(39, 'Lay cable', '', 33, 'mosim', 0),
(40, 'Thread cable', '', 33, 'mosim', 0),
(41, 'Open', '', 34, 'mosim', 0),
(42, 'Close', '', 34, 'mosim', 0),
(43, 'Press momentarly', '', 35, 'mosim', 0),
(44, 'Press persistently', '', 35, 'mosim', 0),
(45, 'Tilt forward', '', 35, 'mosim', 0),
(46, 'Tilt backward', '', 35, 'mosim', 0),
(47, 'Tilt left', '', 35, 'mosim', 0),
(48, 'Tilt right', '', 35, 'mosim', 0),
(49, 'Tilt random', '', 35, 'mosim', 0),
(50, 'Walk', '', 36, 'mosim', 0),
(51, 'Jog', '', 36, 'mosim', 0),
(52, 'Run', '', 36, 'mosim', 0),
(53, 'Jump', '', 36, 'mosim', 0),
(54, 'Sit down', '', 36, 'mosim', 0),
(55, 'Stand up', '', 36, 'mosim', 0),
(56, 'Squat', '', 36, 'mosim', 0),
(57, 'Enter', '', 36, 'mosim', 0),
(58, 'Exit', '', 36, 'mosim', 0),
(59, 'Wave hand', '', 36, 'mosim', 0),
(60, 'Grab handrail', '', 36, 'mosim', 0),
(61, 'Release handrail', '', 36, 'mosim', 0),
(62, 'Write', '', 37, 'mosim', 0),
(63, 'Read', '', 37, 'mosim', 0),
(64, 'Stamp', '', 37, 'mosim', 0),
(65, 'Scan', '', 37, 'mosim', 0),
(66, 'Tie', '', 38, 'mosim', 0),
(67, 'Clamp', '', 38, 'mosim', 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
