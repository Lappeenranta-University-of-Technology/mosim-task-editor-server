-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- Generated: 22.01.2021, 11:12
-- PHP version: 7.0.33-0ubuntu0.16.04.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mosim`
--
CREATE DATABASE IF NOT EXISTS `mosim` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `mosim`;

-- --------------------------------------------------------

--
-- Table `adminroles`
--

CREATE TABLE `adminroles` (
  `userid` bigint(20) UNSIGNED NOT NULL,
  `role` set('admin','tool editor','user manager','MMU Library manager') COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `avatars`
--

CREATE TABLE `avatars` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` tinytext COLLATE utf8_bin NOT NULL,
  `projectid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `age` tinyint(3) UNSIGNED NOT NULL DEFAULT '25',
  `height` int(11) NOT NULL DEFAULT '174',
  `weight` int(11) NOT NULL DEFAULT '70',
  `gender` enum('male','female') COLLATE utf8_bin NOT NULL DEFAULT 'male',
  `sortorder` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `defaulttooltype`
--

CREATE TABLE `defaulttooltype` (
  `tasktype` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `tooltype` bigint(20) UNSIGNED NOT NULL,
  `parttype` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `restricttool` enum('none','notool','onlylisted','exceptlisted') COLLATE utf8_bin NOT NULL DEFAULT 'none',
  `restrictpart` enum('none','nopart','onlylisted','exceptlisted') COLLATE utf8_bin NOT NULL DEFAULT 'none',
  `toollist` text COLLATE utf8_bin NOT NULL,
  `partlist` text COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `highleveltasks`
--

CREATE TABLE `highleveltasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stationid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `tasktype` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `partid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `subpartid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `toolid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `positionname` text COLLATE utf8_bin NOT NULL,
  `description` text COLLATE utf8_bin NOT NULL,
  `esttime` time DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `mmus`
--

CREATE TABLE `mmus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` tinytext COLLATE utf8_bin NOT NULL,
  `author` tinytext COLLATE utf8_bin NOT NULL,
  `vendorID` tinytext COLLATE utf8_bin NOT NULL,
  `motiontype` tinytext COLLATE utf8_bin NOT NULL,
  `version` tinytext COLLATE utf8_bin NOT NULL,
  `longdescription` text COLLATE utf8_bin NOT NULL,
  `shortdescription` text COLLATE utf8_bin NOT NULL,
  `package` longblob
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `mmu_project`
--

CREATE TABLE `mmu_project` (
  `projectid` bigint(20) UNSIGNED NOT NULL,
  `mmuid` bigint(20) UNSIGNED NOT NULL,
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `partcat`
--

CREATE TABLE `partcat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` text COLLATE utf8_bin NOT NULL,
  `grouptype` enum('parttype','station') COLLATE utf8_bin NOT NULL DEFAULT 'parttype',
  `projectid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `syncwith` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `defaultpart` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `icon` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT 'puzzle-piece-solid.svg',
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `language` char(100) COLLATE utf8_bin NOT NULL DEFAULT 'mosim'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `parts`
--

CREATE TABLE `parts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `projectid` bigint(20) UNSIGNED NOT NULL,
  `name` char(255) COLLATE utf8_bin NOT NULL,
  `engineid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `picture` mediumblob,
  `cad` longblob,
  `description` text COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `part_cat`
--

CREATE TABLE `part_cat` (
  `cat` bigint(20) UNSIGNED NOT NULL,
  `part` bigint(20) UNSIGNED NOT NULL,
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `part_station`
--

CREATE TABLE `part_station` (
  `station` bigint(20) UNSIGNED NOT NULL,
  `part` bigint(20) UNSIGNED NOT NULL,
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `positions`
--

CREATE TABLE `positions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` text COLLATE utf8_bin NOT NULL,
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `language` char(100) COLLATE utf8_bin NOT NULL DEFAULT 'mosim'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Table `positions` content
--

INSERT INTO `positions` (`id`, `name`, `sortorder`, `language`) VALUES
(1, 'In hands', 1, 'mosim'),
(2, 'In main part location', 2, 'mosim'),
(3, 'On current table', 3, 'mosim');

-- --------------------------------------------------------

--
-- Table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` text COLLATE utf8_bin NOT NULL,
  `description` text COLLATE utf8_bin NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `stations`
--

CREATE TABLE `stations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `projectid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `name` text COLLATE utf8_bin NOT NULL,
  `mainpart` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `main` enum('part','station') COLLATE utf8_bin NOT NULL DEFAULT 'part',
  `position` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `avatarid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `lastchange` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `tasktypes`
--

CREATE TABLE `tasktypes` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` text COLLATE utf8_bin NOT NULL,
  `icon` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT '',
  `parent` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `language` char(100) COLLATE utf8_bin NOT NULL DEFAULT 'mosim',
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `tokens`
--

CREATE TABLE `tokens` (
  `token` text COLLATE utf8_bin NOT NULL,
  `userid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `projectid` bigint(20) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `toolcat`
--

CREATE TABLE `toolcat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` text COLLATE utf8_bin NOT NULL,
  `defaulttool` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `icon` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT 'tools-solid.svg',
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `language` char(100) COLLATE utf8_bin NOT NULL DEFAULT 'mosim'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `tools`
--

CREATE TABLE `tools` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` text COLLATE utf8_bin NOT NULL,
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `language` char(100) COLLATE utf8_bin NOT NULL DEFAULT 'mosim'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `tool_cat`
--

CREATE TABLE `tool_cat` (
  `cat` bigint(20) UNSIGNED NOT NULL,
  `tool` bigint(20) UNSIGNED NOT NULL,
  `sortorder` bigint(20) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `tool_icon`
--

CREATE TABLE `tool_icon` (
  `tool` bigint(20) UNSIGNED NOT NULL,
  `icon` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT 'tools-solid.svg'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `upload`
--

CREATE TABLE `upload` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` tinytext COLLATE utf8_bin NOT NULL,
  `sessionID` tinytext COLLATE utf8_bin NOT NULL,
  `chunkno` int(10) UNSIGNED NOT NULL,
  `chunk` longblob NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `userroles`
--

CREATE TABLE `userroles` (
  `userid` bigint(20) UNSIGNED NOT NULL,
  `projectid` bigint(20) UNSIGNED NOT NULL,
  `laststation` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `role` set('owner','editor','viewer','reviewer') COLLATE utf8_bin NOT NULL DEFAULT 'owner'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` text COLLATE utf8_bin NOT NULL,
  `email` text COLLATE utf8_bin NOT NULL,
  `pass` text COLLATE utf8_bin NOT NULL,
  `lastprojectid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Table indecies
--

--
-- Indexes for table `adminroles`
--
ALTER TABLE `adminroles`
  ADD UNIQUE KEY `UserProject` (`userid`,`role`);

--
-- Indexes for table `avatars`
--
ALTER TABLE `avatars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defaulttooltype`
--
ALTER TABLE `defaulttooltype`
  ADD UNIQUE KEY `tasktype` (`tasktype`) USING BTREE;

--
-- Indexes for table `highleveltasks`
--
ALTER TABLE `highleveltasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mmus`
--
ALTER TABLE `mmus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mmu_project`
--
ALTER TABLE `mmu_project`
  ADD UNIQUE KEY `ProjectMMU` (`projectid`,`mmuid`);

--
-- Indexes for table `partcat`
--
ALTER TABLE `partcat`
  ADD UNIQUE KEY `MainINdex` (`id`,`language`);

--
-- Indexes for table `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `part_cat`
--
ALTER TABLE `part_cat`
  ADD UNIQUE KEY `catpart` (`cat`,`part`);

--
-- Indexes for table `part_station`
--
ALTER TABLE `part_station`
  ADD UNIQUE KEY `stationpart` (`station`,`part`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD UNIQUE KEY `MainINdex` (`id`,`language`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stations`
--
ALTER TABLE `stations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasktypes`
--
ALTER TABLE `tasktypes`
  ADD UNIQUE KEY `MainIndex` (`id`,`language`);

--
-- Indexes for table `tokens`
--
ALTER TABLE `tokens`
  ADD UNIQUE KEY `token` (`token`(42));

--
-- Indexes for table `toolcat`
--
ALTER TABLE `toolcat`
  ADD UNIQUE KEY `MainIndex` (`id`,`language`);

--
-- Indexes for table `tools`
--
ALTER TABLE `tools`
  ADD UNIQUE KEY `MainINdex` (`id`,`language`);

--
-- Indexes for table `tool_cat`
--
ALTER TABLE `tool_cat`
  ADD UNIQUE KEY `catpart` (`cat`,`tool`);

--
-- Indexes for table `upload`
--
ALTER TABLE `upload`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `userroles`
--
ALTER TABLE `userroles`
  ADD UNIQUE KEY `UserProject` (`userid`,`projectid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`(100));

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `avatars`
--
ALTER TABLE `avatars`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `highleveltasks`
--
ALTER TABLE `highleveltasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `mmus`
--
ALTER TABLE `mmus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `parts`
--
ALTER TABLE `parts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `stations`
--
ALTER TABLE `stations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `upload`
--
ALTER TABLE `upload`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
