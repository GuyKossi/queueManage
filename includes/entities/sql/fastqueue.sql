-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 01, 2014 at 06:50 PM
-- Server version: 5.5.38
-- PHP Version: 5.4.4-14+deb7u14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `fastqueue`
--

-- --------------------------------------------------------

--
-- Table structure for table `ban`
--

CREATE TABLE IF NOT EXISTS `ban` (
  `ban_id` int(11) NOT NULL AUTO_INCREMENT,
  `ban_source` varchar(10) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `ban_trash_count` int(11) NOT NULL DEFAULT '0',
  `ban_time_end` int(11) DEFAULT '0',
  `ban_source_id` varchar(50) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ban_id`),
  UNIQUE KEY `ban_source_id` (`ban_source_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `desk`
--

CREATE TABLE IF NOT EXISTS `desk` (
  `desk_id` int(11) NOT NULL AUTO_INCREMENT,
  `desk_number` tinyint(4) NOT NULL,
  `desk_ip_address` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `desk_op_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `desk_last_activity_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`desk_id`),
  UNIQUE KEY `desk_number` (`desk_number`,`desk_ip_address`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `device`
--

CREATE TABLE IF NOT EXISTS `device` (
  `dev_id` int(11) NOT NULL AUTO_INCREMENT,
  `dev_ip_address` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `dev_desk_number` tinyint(4) NOT NULL,
  `dev_td_code` char(1) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`dev_id`),
  UNIQUE KEY `dev_ip_address` (`dev_ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `display_main`
--

CREATE TABLE IF NOT EXISTS `display_main` (
  `dm_id` int(11) NOT NULL AUTO_INCREMENT,
  `dm_ticket` varchar(4) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `dm_desk` text NOT NULL,
  PRIMARY KEY (`dm_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='display_main' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `office`
--

CREATE TABLE IF NOT EXISTS `office` (
  `of_id` int(11) NOT NULL AUTO_INCREMENT,
  `of_code` varchar(30) COLLATE utf8_bin NOT NULL,
  `of_name` varchar(100) COLLATE utf8_bin NOT NULL,
  `of_city` varchar(50) COLLATE utf8_bin NOT NULL,
  `of_search` varchar(50) CHARACTER SET ascii NOT NULL,
  `of_address` varchar(100) COLLATE utf8_bin DEFAULT '',
  `of_latitude` double DEFAULT NULL,
  `of_longitude` double DEFAULT NULL,
  `of_host` varchar(100) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`of_id`),
  UNIQUE KEY `of_code` (`of_code`),
  KEY `of_search` (`of_search`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `operator`
--

CREATE TABLE IF NOT EXISTS `operator` (
  `op_id` int(11) NOT NULL AUTO_INCREMENT,
  `op_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `op_name` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `op_surname` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `op_password` char(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`op_id`),
  UNIQUE KEY `op_code` (`op_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_exec`
--

CREATE TABLE IF NOT EXISTS `ticket_exec` (
  `te_id` int(11) NOT NULL AUTO_INCREMENT,
  `te_code` char(1) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `te_number` int(11) NOT NULL,
  `te_time_in` int(11) NOT NULL,
  `te_source` varchar(10) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `te_source_id` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `te_time_exec` int(11) NOT NULL,
  `te_op_code` varchar(30) COLLATE utf8_bin NOT NULL,
  `te_desk_number` tinyint(4) NOT NULL,
  PRIMARY KEY (`te_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_in`
--

CREATE TABLE IF NOT EXISTS `ticket_in` (
  `ti_id` int(11) NOT NULL AUTO_INCREMENT,
  `ti_code` char(1) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `ti_number` int(11) NOT NULL,
  `ti_time_in` int(11) NOT NULL,
  `ti_source` varchar(10) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `ti_source_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `ti_notice_counter` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`ti_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_stats`
--

CREATE TABLE IF NOT EXISTS `ticket_stats` (
  `ts_id` int(11) NOT NULL AUTO_INCREMENT,
  `ts_code` char(1) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `ts_time_in` int(11) NOT NULL,
  `ts_time_exec` int(11) DEFAULT NULL,
  `ts_time_out` int(11) NOT NULL,
  `ts_source` varchar(10) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `ts_op_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `ts_is_trash` tinyint(4) NOT NULL,
  `ts_desk_number` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`ts_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `topical_domain`
--

CREATE TABLE IF NOT EXISTS `topical_domain` (
  `td_id` int(11) NOT NULL AUTO_INCREMENT,
  `td_code` char(1) COLLATE utf8_bin NOT NULL,
  `td_name` varchar(100) COLLATE utf8_bin NOT NULL,
  `td_description` varchar(300) COLLATE utf8_bin DEFAULT NULL,
  `td_active` tinyint(4) NOT NULL,
  `td_icon` tinyint(4) NOT NULL,
  `td_color` tinyint(4) NOT NULL COMMENT 'indexed colors',
  `td_eta` int(11) DEFAULT NULL COMMENT 'seconds',
  `td_next_generated_ticket` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`td_id`),
  UNIQUE KEY `td_code` (`td_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
