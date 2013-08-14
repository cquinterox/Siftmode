-- phpMyAdmin SQL Dump
-- version 4.0.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 14, 2013 at 03:28 AM
-- Server version: 5.6.12
-- PHP Version: 5.5.0

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

DROP TABLE IF EXISTS `categories`;
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

DROP TABLE IF EXISTS `feeds`;
CREATE TABLE IF NOT EXISTS `feeds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
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
-- Table structure for table `feeds_day_summary`
--

DROP TABLE IF EXISTS `feeds_day_summary`;
CREATE TABLE IF NOT EXISTS `feeds_day_summary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `day_of_year` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `day_summary` text NOT NULL,
  `updated_on` timestamp NULL DEFAULT NULL,
  `created_on` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`year`,`day_of_year`,`category_id`),
  UNIQUE KEY `ID` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `feeds_month_summary`
--

DROP TABLE IF EXISTS `feeds_month_summary`;
CREATE TABLE IF NOT EXISTS `feeds_month_summary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `month_in_year` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `month_summary` longtext NOT NULL,
  `updated_on` timestamp NULL DEFAULT NULL,
  `created_on` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`year`,`month_in_year`,`category_id`),
  UNIQUE KEY `ID` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `feeds_week_summary`
--

DROP TABLE IF EXISTS `feeds_week_summary`;
CREATE TABLE IF NOT EXISTS `feeds_week_summary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `week_of_year` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `week_summary` mediumtext NOT NULL,
  `updated_on` timestamp NULL DEFAULT NULL,
  `created_on` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`year`,`week_of_year`,`category_id`),
  UNIQUE KEY `ID` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=183 ;

-- --------------------------------------------------------

--
-- Table structure for table `req_min_word_count`
--

DROP TABLE IF EXISTS `req_min_word_count`;
CREATE TABLE IF NOT EXISTS `req_min_word_count` (
  `word_count` int(11) NOT NULL,
  `min` int(11) NOT NULL,
  UNIQUE KEY `word_count` (`word_count`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
