-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas generowania: 09 Cze 2021, 05:46
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
CREATE DATABASE IF NOT EXISTS `mosim` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `mosim`;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `adminroles`
--

CREATE TABLE `adminroles` (
  `userid` bigint(20) UNSIGNED NOT NULL,
  `role` set('admin','tool editor','user manager','MMU Library manager') COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `avatars`
--

CREATE TABLE `avatars` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `engineid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
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
-- Struktura tabeli dla tabeli `avatar_param`
--

CREATE TABLE `avatar_param` (
  `avatarid` bigint(20) UNSIGNED NOT NULL,
  `paramvalid` bigint(20) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `avatar_param_types`
--

CREATE TABLE `avatar_param_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `projectid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `name` tinytext COLLATE utf8_bin NOT NULL,
  `optional` tinyint(1) NOT NULL DEFAULT '1',
  `type` enum('singlevalue','multiplevalues') COLLATE utf8_bin NOT NULL DEFAULT 'singlevalue',
  `language` enum('mosim','en','de','fr') COLLATE utf8_bin NOT NULL DEFAULT 'mosim',
  `sortorder` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `avatar_param_values`
--

CREATE TABLE `avatar_param_values` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `typeid` bigint(20) UNSIGNED NOT NULL,
  `value` tinytext COLLATE utf8_bin NOT NULL,
  `language` enum('mosim','en','de','fr') COLLATE utf8_bin NOT NULL DEFAULT 'mosim',
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `defaulttooltype`
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
-- Struktura tabeli dla tabeli `highleveltasks`
--

CREATE TABLE `highleveltasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stationid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `workerid` bigint(20) UNSIGNED NOT NULL DEFAULT '1',
  `tasktype` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `partid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `subpartid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `markerid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `toolid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `positionname` text COLLATE utf8_bin NOT NULL,
  `description` text COLLATE utf8_bin NOT NULL,
  `esttime` time DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `msg` longtext COLLATE utf8_bin NOT NULL,
  `stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `markers`
--

CREATE TABLE `markers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `projectid` bigint(20) UNSIGNED NOT NULL,
  `stationid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `name` char(255) COLLATE utf8_bin NOT NULL,
  `constraintName` char(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `constraintID` char(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `type` enum('InitialLocation','FinalLocation','WalkTarget') COLLATE utf8_bin NOT NULL,
  `engineid` bigint(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID in target engine'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `mmiObjectTypes`
--

CREATE TABLE `mmiObjectTypes` (
  `mmiType` tinytext COLLATE utf8_bin NOT NULL,
  `mmiGroup` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `mmus`
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
-- Struktura tabeli dla tabeli `mmutasks`
--

CREATE TABLE `mmutasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mmuid` tinytext COLLATE utf8_bin NOT NULL,
  `name` tinytext COLLATE utf8_bin NOT NULL,
  `motiontype` tinytext COLLATE utf8_bin NOT NULL,
  `properties` text COLLATE utf8_bin NOT NULL,
  `constraints` text COLLATE utf8_bin NOT NULL,
  `startrule` tinytext COLLATE utf8_bin NOT NULL,
  `endrule` tinytext COLLATE utf8_bin NOT NULL,
  `projectid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `resultset` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `success` tinyint(2) NOT NULL DEFAULT '-1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `mmu_project`
--

CREATE TABLE `mmu_project` (
  `projectid` bigint(20) UNSIGNED NOT NULL,
  `mmuid` bigint(20) UNSIGNED NOT NULL,
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `partcat`
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
-- Struktura tabeli dla tabeli `parts`
--

CREATE TABLE `parts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `projectid` bigint(20) UNSIGNED NOT NULL,
  `parent` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `isGroup` tinyint(1) NOT NULL DEFAULT '0',
  `name` char(255) COLLATE utf8_bin NOT NULL,
  `engineid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `picture` mediumblob,
  `cad` longblob,
  `description` text COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `partstation`
--

CREATE TABLE `partstation` (
  `partid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `stationid` bigint(20) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `part_cat`
--

CREATE TABLE `part_cat` (
  `cat` bigint(20) UNSIGNED NOT NULL,
  `part` bigint(20) UNSIGNED NOT NULL,
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `part_station`
--

CREATE TABLE `part_station` (
  `station` bigint(20) UNSIGNED NOT NULL,
  `part` bigint(20) UNSIGNED NOT NULL,
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `positions`
--

CREATE TABLE `positions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` text COLLATE utf8_bin NOT NULL,
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `language` char(100) COLLATE utf8_bin NOT NULL DEFAULT 'mosim'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` text COLLATE utf8_bin NOT NULL,
  `description` text COLLATE utf8_bin NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `scene`
--

CREATE TABLE `scene` (
  `id` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `engineid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `parent` bigint(20) NOT NULL DEFAULT '0',
  `station` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `name` tinytext COLLATE utf8_bin NOT NULL,
  `type` enum('MSceneObject','InitialLocation','FinalLocation','WalkTarget','Area','Part','Tool','Group','Station','StationResult') COLLATE utf8_bin NOT NULL DEFAULT 'MSceneObject',
  `projectid` bigint(20) UNSIGNED NOT NULL,
  `savename` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'current',
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `scene_temp`
--

CREATE TABLE `scene_temp` (
  `id` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `engineid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `parent` bigint(20) NOT NULL DEFAULT '0',
  `station` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `name` tinytext COLLATE utf8_bin NOT NULL,
  `type` enum('MSceneObject','InitialLocation','FinalLocation','WalkTarget','Area','Part','Tool','Group','Station','StationResult') COLLATE utf8_bin NOT NULL DEFAULT 'MSceneObject',
  `projectid` bigint(20) UNSIGNED NOT NULL,
  `savename` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'current',
  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `settings`
--

CREATE TABLE `settings` (
  `projectid` bigint(20) UNSIGNED NOT NULL,
  `userid` bigint(20) UNSIGNED NOT NULL,
  `property` tinytext COLLATE utf8_bin NOT NULL,
  `value` bigint(20) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `stations`
--

CREATE TABLE `stations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `engineid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
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
-- Struktura tabeli dla tabeli `tasktypes`
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
-- Struktura tabeli dla tabeli `tokens`
--

CREATE TABLE `tokens` (
  `token` text COLLATE utf8_bin NOT NULL,
  `userid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `projectid` bigint(20) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `toolcat`
--

CREATE TABLE `toolcat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` text COLLATE utf8_bin NOT NULL,
  `parent` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `defaulttool` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `icon` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT 'tools-solid.svg',
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `language` char(100) COLLATE utf8_bin NOT NULL DEFAULT 'mosim'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `tools`
--

CREATE TABLE `tools` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` text COLLATE utf8_bin NOT NULL,
  `sortorder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `language` char(100) COLLATE utf8_bin NOT NULL DEFAULT 'mosim'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `tool_cat`
--

CREATE TABLE `tool_cat` (
  `cat` bigint(20) UNSIGNED NOT NULL,
  `tool` bigint(20) UNSIGNED NOT NULL,
  `sortorder` bigint(20) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `tool_icon`
--

CREATE TABLE `tool_icon` (
  `tool` bigint(20) UNSIGNED NOT NULL,
  `icon` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT 'tools-solid.svg'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `upload`
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
-- Struktura tabeli dla tabeli `userroles`
--

CREATE TABLE `userroles` (
  `userid` bigint(20) UNSIGNED NOT NULL,
  `projectid` bigint(20) UNSIGNED NOT NULL,
  `laststation` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `role` set('owner','editor','viewer','reviewer') COLLATE utf8_bin NOT NULL DEFAULT 'owner'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` text COLLATE utf8_bin NOT NULL,
  `email` text COLLATE utf8_bin NOT NULL,
  `pass` text COLLATE utf8_bin NOT NULL,
  `lastprojectid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `workers`
--

CREATE TABLE `workers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` tinytext COLLATE utf8_bin NOT NULL,
  `description` text COLLATE utf8_bin NOT NULL,
  `stationid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `projectid` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `avatarid` bigint(20) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Indeksy dla zrzutów tabel
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
-- Indexes for table `avatar_param`
--
ALTER TABLE `avatar_param`
  ADD UNIQUE KEY `mainindex` (`avatarid`,`paramvalid`);

--
-- Indexes for table `avatar_param_types`
--
ALTER TABLE `avatar_param_types`
  ADD UNIQUE KEY `mainindex` (`id`,`language`);

--
-- Indexes for table `avatar_param_values`
--
ALTER TABLE `avatar_param_values`
  ADD UNIQUE KEY `mainindex` (`id`,`typeid`,`language`);

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
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `markers`
--
ALTER TABLE `markers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mmus`
--
ALTER TABLE `mmus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mmutasks`
--
ALTER TABLE `mmutasks`
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
-- Indexes for table `scene`
--
ALTER TABLE `scene`
  ADD UNIQUE KEY `EngineProject` (`engineid`,`projectid`);

--
-- Indexes for table `scene_temp`
--
ALTER TABLE `scene_temp`
  ADD UNIQUE KEY `EngineProject` (`engineid`,`projectid`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD UNIQUE KEY `projectuserproperty` (`projectid`,`userid`,`property`(30)),
  ADD KEY `projectuser` (`projectid`,`userid`);

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
-- Indexes for table `workers`
--
ALTER TABLE `workers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT dla tabeli `avatars`
--
ALTER TABLE `avatars`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT dla tabeli `highleveltasks`
--
ALTER TABLE `highleveltasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=438;
--
-- AUTO_INCREMENT dla tabeli `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT dla tabeli `markers`
--
ALTER TABLE `markers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
--
-- AUTO_INCREMENT dla tabeli `mmus`
--
ALTER TABLE `mmus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
--
-- AUTO_INCREMENT dla tabeli `mmutasks`
--
ALTER TABLE `mmutasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `parts`
--
ALTER TABLE `parts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=759;
--
-- AUTO_INCREMENT dla tabeli `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;
--
-- AUTO_INCREMENT dla tabeli `stations`
--
ALTER TABLE `stations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;
--
-- AUTO_INCREMENT dla tabeli `upload`
--
ALTER TABLE `upload`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;
--
-- AUTO_INCREMENT dla tabeli `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;
--
-- AUTO_INCREMENT dla tabeli `workers`
--
ALTER TABLE `workers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
