-- phpMyAdmin SQL Dump
-- version 2.11.3deb1ubuntu1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 15, 2008 at 05:25 PM
-- Server version: 5.0.67
-- PHP Version: 5.2.6-0.1+b1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `viper7`
--

-- --------------------------------------------------------

--
-- Table structure for table `flvTickets`
--

CREATE TABLE IF NOT EXISTS `flvTickets` (
  `ID` int(11) NOT NULL auto_increment,
  `Filename` varchar(255) NOT NULL,
  `Ticket` varchar(20) NOT NULL,
  `Quality` varchar(8) default NULL,
  `Timestamp` datetime default NULL,
  `Running` tinyint(1) NOT NULL default '0',
  `Resolution` varchar(16) NOT NULL,
  `Percent` float NOT NULL default '0',
  `keyed` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=796 ;

-- --------------------------------------------------------

--
-- Table structure for table `imdb`
--

CREATE TABLE IF NOT EXISTS `imdb` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(80) NOT NULL,
  `Title` varchar(200) NOT NULL,
  `Plot` text NOT NULL,
  `IMDBURL` varchar(70) NOT NULL,
  `Tagline` varchar(200) NOT NULL,
  `ReleaseDate` int(12) NOT NULL,
  `Rating` float NOT NULL,
  `BoxURL` varchar(400) NOT NULL,
  `Duration` int(11) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1465 ;

-- --------------------------------------------------------

--
-- Table structure for table `imdbfiles`
--

CREATE TABLE IF NOT EXISTS `imdbfiles` (
  `ID` int(11) NOT NULL auto_increment,
  `IMDBID` int(11) NOT NULL,
  `Filename` varchar(120) NOT NULL,
  `Duration` float NOT NULL,
  `Part` int(11) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1690 ;

-- --------------------------------------------------------

--
-- Table structure for table `imdbtags`
--

CREATE TABLE IF NOT EXISTS `imdbtags` (
  `ID` int(11) NOT NULL auto_increment,
  `IMDBID` int(11) NOT NULL,
  `Tag` varchar(80) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `IMDBID` (`IMDBID`),
  KEY `Tag` (`Tag`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=68327 ;

-- --------------------------------------------------------

--
-- Table structure for table `series`
--

CREATE TABLE IF NOT EXISTS `series` (
  `ID` int(11) NOT NULL auto_increment,
  `TheTVDBID` bigint(20) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `Folder` varchar(200) NOT NULL,
  `Summary` text NOT NULL,
  `ImageURL` varchar(200) NOT NULL,
  `IMDBURL` varchar(200) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=85 ;

-- --------------------------------------------------------

--
-- Table structure for table `seriestags`
--

CREATE TABLE IF NOT EXISTS `seriestags` (
  `ID` int(11) NOT NULL auto_increment,
  `SeriesID` int(11) NOT NULL,
  `Tag` varchar(80) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `SeriesID` (`SeriesID`),
  KEY `Tag` (`Tag`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2347 ;

-- --------------------------------------------------------

--
-- Table structure for table `toons`
--

CREATE TABLE IF NOT EXISTS `toons` (
  `ID` int(11) NOT NULL auto_increment,
  `TheTVDBID` bigint(20) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `Folder` varchar(200) NOT NULL,
  `Summary` text NOT NULL,
  `ImageURL` varchar(200) NOT NULL,
  `IMDBURL` varchar(200) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;

-- --------------------------------------------------------

--
-- Table structure for table `toontags`
--

CREATE TABLE IF NOT EXISTS `toontags` (
  `ID` int(11) NOT NULL auto_increment,
  `ToonsID` int(11) NOT NULL,
  `Tag` varchar(80) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ToonsID` (`ToonsID`),
  KEY `Tag` (`Tag`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=633 ;
