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
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `board_postId` int(16) DEFAULT NULL,
  `board_authorId` int(16) DEFAULT NULL,
  `board_comment` text,
  `board_replyTo` int(16) DEFAULT NULL,
  `board_datePosted` datetime DEFAULT NULL,
  `board_lastUpdated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

/*Table structure for table `pages` */

DROP TABLE IF EXISTS `pages`;

CREATE TABLE `pages` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `page_template` int(16) DEFAULT NULL,
  `page_safeLink` varchar(32) DEFAULT NULL,
  `page_meta` text,
  `page_title` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `posts` */

DROP TABLE IF EXISTS `posts`;

CREATE TABLE `posts` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `post_id` int(16) DEFAULT NULL,
  `post_authorId` int(16) DEFAULT NULL,
  `post_date` datetime DEFAULT NULL,
  `post_title` varchar(150) DEFAULT NULL,
  `post_content` text,
  `post_lastModified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `templates` */

DROP TABLE IF EXISTS `templates`;

CREATE TABLE `templates` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `template_path` varchar(128) DEFAULT NULL,
  `template_name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `user_login` varchar(64) DEFAULT NULL,
  `user_pass` varchar(64) DEFAULT NULL,
  `user_nick` varchar(64) DEFAULT NULL,
  `user_email` varchar(128) DEFAULT NULL,
  `user_registerDate` datetime DEFAULT NULL,
  `user_isRegistered` bit(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;