/*
SQLyog Community Edition- MySQL GUI v7.12 
MySQL - 5.5.25a : Database - ferret_cms
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`ferret_cms` /*!40100 DEFAULT CHARACTER SET latin1 */;

/*Table structure for table `board` */

DROP TABLE IF EXISTS `board`;

CREATE TABLE `board` (
  `ID` int(16) NOT NULL AUTO_INCREMENT,
  `UserID` int(16) DEFAULT NULL,
  `Comment` text,
  `ReplyTo` int(16) DEFAULT NULL,
  `DatePosted` datetime DEFAULT NULL,
  `LastUpdated` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;