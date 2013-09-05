-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 05, 2013 at 04:39 AM
-- Server version: 5.6.13
-- PHP Version: 5.5.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `Siftmode`
--
CREATE DATABASE IF NOT EXISTS `Siftmode` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `Siftmode`;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `CATEGORY_NAME` varchar(50) NOT NULL,
  `COMMON_WORDS` text NOT NULL,
  `UPDATED_ON` timestamp NULL DEFAULT NULL,
  `CREATED_ON` timestamp NULL DEFAULT NULL,
  `LAST_RUN_ON` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  KEY `ID_2` (`ID`,`CATEGORY_NAME`,`LAST_RUN_ON`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `feeds`
--

CREATE TABLE IF NOT EXISTS `feeds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `feed_url` varchar(2048) NOT NULL,
  `save_articles` bit(1) DEFAULT b'0',
  `name` varchar(32) NOT NULL,
  `description` varchar(256) NOT NULL,
  `updated_on` timestamp NULL DEFAULT NULL,
  `created_on` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ID` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feed_id` int(11) NOT NULL,
  `link` text NOT NULL,
  `pubdate` timestamp NULL DEFAULT NULL,
  `title` text,
  `title_words` text,
  `description` text,
  `description_words` text,
  `article` text,
  `title_description_words` text,
  `updated_on` timestamp NULL DEFAULT NULL,
  `created_on` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  FULLTEXT KEY `title_description_words` (`title_description_words`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=145 ;

-- --------------------------------------------------------

--
-- Table structure for table `summary_data`
--

CREATE TABLE IF NOT EXISTS `summary_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `summary_id` int(11) NOT NULL,
  `feed_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `match_priority` int(11) NOT NULL,
  `match_string` varchar(155) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`summary_id`,`feed_id`,`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Table structure for table `summary_list`
--

CREATE TABLE IF NOT EXISTS `summary_list` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `STARTTIME` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ENDTIME` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `CATEGORY_ID` int(11) NOT NULL DEFAULT '0',
  `USER_ID` int(11) NOT NULL DEFAULT '0',
  `RUN_ON` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`STARTTIME`,`ENDTIME`,`CATEGORY_ID`,`USER_ID`),
  UNIQUE KEY `ID` (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` text NOT NULL,
  `key` text NOT NULL,
  `email` varchar(128) NOT NULL,
  `name` varchar(30) NOT NULL,
  `updated_on` timestamp NULL DEFAULT NULL,
  `created_on` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `word_counts`
--

CREATE TABLE IF NOT EXISTS `word_counts` (
  `word_count` int(11) NOT NULL,
  `month_min` smallint(6) NOT NULL,
  `day_min` smallint(6) NOT NULL,
  `week_min` smallint(6) NOT NULL,
  `year_min` smallint(6) NOT NULL,
  UNIQUE KEY `word_count` (`word_count`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
