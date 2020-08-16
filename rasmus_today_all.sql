-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: rasmus.today.mysql.service.one.com:3306
-- Generation Time: Aug 09, 2020 at 08:27 PM
-- Server version: 10.3.23-MariaDB-1:10.3.23+maria~bionic
-- PHP Version: 7.2.24-0ubuntu0.18.04.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rasmus_today`
--

-- --------------------------------------------------------

--
-- Table structure for table `archivedGames`
--

CREATE TABLE `archivedGames` (
  `x` int(11) NOT NULL DEFAULT 0,
  `y` int(11) NOT NULL DEFAULT 0,
  `action` enum('playStone','pass','giveUp','') NOT NULL,
  `moveIndex` int(11) NOT NULL,
  `matchIndex` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `challenges`
--

CREATE TABLE `challenges` (
  `user1ID` int(11) NOT NULL,
  `user2ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `credentials`
--

CREATE TABLE `credentials` (
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `currentGames`
--

CREATE TABLE `currentGames` (
  `x` int(11) NOT NULL DEFAULT 0,
  `y` int(11) NOT NULL DEFAULT 0,
  `action` enum('playStone','pass','giveUp','') NOT NULL,
  `moveIndex` int(11) NOT NULL,
  `matchIndex` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `DEBUG_archivedGames`
--

CREATE TABLE `DEBUG_archivedGames` (
  `x` int(11) NOT NULL DEFAULT 0,
  `y` int(11) NOT NULL DEFAULT 0,
  `action` enum('playStone','pass','giveUp','') NOT NULL,
  `moveIndex` int(11) NOT NULL,
  `matchIndex` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `DEBUG_challenges`
--

CREATE TABLE `DEBUG_challenges` (
  `user1ID` int(11) NOT NULL,
  `user2ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `DEBUG_credentials`
--

CREATE TABLE `DEBUG_credentials` (
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `DEBUG_currentGames`
--

CREATE TABLE `DEBUG_currentGames` (
  `x` int(11) NOT NULL DEFAULT 0,
  `y` int(11) NOT NULL DEFAULT 0,
  `action` enum('playStone','pass','giveUp','') NOT NULL,
  `moveIndex` int(11) NOT NULL,
  `matchIndex` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `DEBUG_error`
--

CREATE TABLE `DEBUG_error` (
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `message` varchar(255) NOT NULL DEFAULT '"No error"'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `DEBUG_matchList`
--

CREATE TABLE `DEBUG_matchList` (
  `player1ID` int(11) NOT NULL,
  `player2ID` int(11) NOT NULL,
  `matchIndex` int(11) NOT NULL,
  `winner` int(11) DEFAULT NULL,
  `endCause` enum('pass','surrender') DEFAULT NULL,
  `points1` decimal(4,1) DEFAULT NULL,
  `points2` decimal(4,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `error`
--

CREATE TABLE `error` (
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `message` varchar(255) NOT NULL DEFAULT '"No error"'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `matchList`
--

CREATE TABLE `matchList` (
  `player1ID` int(11) NOT NULL,
  `player2ID` int(11) NOT NULL,
  `matchIndex` int(11) NOT NULL,
  `winner` int(11) DEFAULT NULL,
  `endCause` enum('pass','surrender') DEFAULT NULL,
  `points1` decimal(4,1) DEFAULT NULL,
  `points2` decimal(4,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `credentials`
--
ALTER TABLE `credentials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `DEBUG_credentials`
--
ALTER TABLE `DEBUG_credentials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `DEBUG_matchList`
--
ALTER TABLE `DEBUG_matchList`
  ADD PRIMARY KEY (`matchIndex`);

--
-- Indexes for table `matchList`
--
ALTER TABLE `matchList`
  ADD PRIMARY KEY (`matchIndex`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `credentials`
--
ALTER TABLE `credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `DEBUG_credentials`
--
ALTER TABLE `DEBUG_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `DEBUG_matchList`
--
ALTER TABLE `DEBUG_matchList`
  MODIFY `matchIndex` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matchList`
--
ALTER TABLE `matchList`
  MODIFY `matchIndex` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
