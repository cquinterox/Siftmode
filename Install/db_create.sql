-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2013 at 08:40 AM
-- Server version: 5.5.32
-- PHP Version: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `siftmode`
--
CREATE DATABASE IF NOT EXISTS `siftmode` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `siftmode`;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `CATEGORY_NAME` varchar(50) NOT NULL,
  `COMMON_WORDS` text NOT NULL,
  `UPDATED_ON` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `CREATED_ON` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LAST_RUN_ON` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  KEY `ID_2` (`ID`,`CATEGORY_NAME`,`LAST_RUN_ON`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `feeds`
--

CREATE TABLE IF NOT EXISTS `feeds` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `CATEGORY_ID` int(11) NOT NULL,
  `FEED_URL` varchar(2048) NOT NULL,
  `NAME` varchar(32) NOT NULL,
  `DESCRIPTION` varchar(256) NOT NULL,
  `UPDATED_ON` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `CREATED_ON` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `feeds_data`
--

CREATE TABLE IF NOT EXISTS `feeds_data` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FEED_ID` int(11) NOT NULL,
  `POST_LINK` text NOT NULL,
  `PUBLISHED_ON` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `POST_HEADLINE` text NOT NULL,
  `POST_HEADLINE_ARRAY` text,
  `POST_SUMMARY` text NOT NULL,
  `POST_SUMMARY_ARRAY` text,
  `POST_BODY` text,
  `POST_BODY_ARRAY` text,
  `UPDATED_ON` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `CREATED_ON` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `feeds_day_summary`
--

CREATE TABLE IF NOT EXISTS `feeds_day_summary` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `YEAR` int(11) NOT NULL,
  `DAY_OF_YEAR` int(11) NOT NULL,
  `CATEGORY_ID` int(11) NOT NULL,
  `DAY_SUMMARY` text NOT NULL,
  `UPDATED_ON` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `CREATED_ON` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`YEAR`,`DAY_OF_YEAR`,`CATEGORY_ID`),
  UNIQUE KEY `ID` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `feeds_month_summary`
--

CREATE TABLE IF NOT EXISTS `feeds_month_summary` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `YEAR` int(11) NOT NULL,
  `MONTH_IN_YEAR` int(11) NOT NULL,
  `CATEGORY_ID` int(11) NOT NULL,
  `MONTH_SUMMARY` longtext NOT NULL,
  `UPDATED_ON` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `CREATED_ON` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`YEAR`,`MONTH_IN_YEAR`,`CATEGORY_ID`),
  UNIQUE KEY `ID` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `feeds_week_summary`
--

CREATE TABLE IF NOT EXISTS `feeds_week_summary` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `YEAR` int(11) NOT NULL,
  `WEEK_OF_YEAR` int(11) NOT NULL,
  `CATEGORY_ID` int(11) NOT NULL,
  `WEEK_SUMMARY` mediumtext NOT NULL,
  `UPDATED_ON` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `CREATED_ON` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`YEAR`,`WEEK_OF_YEAR`,`CATEGORY_ID`),
  UNIQUE KEY `ID` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `feeds_year_summary`
--

CREATE TABLE IF NOT EXISTS `feeds_year_summary` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `YEAR` int(11) NOT NULL,
  `CATEGORY_ID` int(11) NOT NULL,
  `YEAR_SUMMARY` longtext NOT NULL,
  `CREATED_ON` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `UPDATED_ON` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`YEAR`,`CATEGORY_ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `YEAR` (`YEAR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USERNAME` varchar(30) NOT NULL,
  `PASSWORD` text NOT NULL,
  `KEY` text NOT NULL,
  `EMAIL` varchar(128) NOT NULL,
  `NAME` varchar(30) NOT NULL,
  `UPDATED_ON` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `CREATED_ON` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
