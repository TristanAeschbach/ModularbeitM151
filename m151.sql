-- MariaDB dump 10.19  Distrib 10.4.22-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: m151
-- ------------------------------------------------------
-- Server version	10.4.22-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `m151`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `m151` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `m151`;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `tag_ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`tag_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (1,'Englisch'),(2,'Französisch'),(3,'Mathematik'),(4,'Technik und Umwelt'),(5,'Wirtschaft und Recht'),(6,'Finanz und Rechnungswesen'),(7,'Informatik Duc'),(8,'Informatik Lurati'),(9,'Deutsch');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category_has_users`
--

DROP TABLE IF EXISTS `category_has_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_has_users` (
  `category_tag_ID` int(11) NOT NULL,
  `users_ID` int(11) NOT NULL,
  PRIMARY KEY (`category_tag_ID`,`users_ID`),
  KEY `fk_category_has_users_users1` (`users_ID`),
  CONSTRAINT `fk_category_has_users_category1` FOREIGN KEY (`category_tag_ID`) REFERENCES `category` (`tag_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_category_has_users_users1` FOREIGN KEY (`users_ID`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category_has_users`
--

LOCK TABLES `category_has_users` WRITE;
/*!40000 ALTER TABLE `category_has_users` DISABLE KEYS */;
INSERT INTO `category_has_users` VALUES (1,2),(1,3),(2,3),(3,3),(4,3),(5,3),(6,3),(7,3),(8,3),(9,3);
/*!40000 ALTER TABLE `category_has_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `todo`
--

DROP TABLE IF EXISTS `todo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `todo` (
  `todo_ID` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(45) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `createDate` datetime DEFAULT NULL,
  `dueDate` datetime DEFAULT NULL,
  `progress` int(11) DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `archived` int(11) DEFAULT NULL,
  `category_tag_ID` int(11) NOT NULL,
  `users_ID` int(11) NOT NULL,
  PRIMARY KEY (`todo_ID`),
  KEY `fk_todo_category` (`category_tag_ID`),
  KEY `fk_todo_users1` (`users_ID`),
  CONSTRAINT `fk_todo_category` FOREIGN KEY (`category_tag_ID`) REFERENCES `category` (`tag_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_todo_users1` FOREIGN KEY (`users_ID`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `todo`
--

LOCK TABLES `todo` WRITE;
/*!40000 ALTER TABLE `todo` DISABLE KEYS */;
INSERT INTO `todo` VALUES (1,'Lorem ipsum dolor sit amet, consetetur sadips','Lorem ipsum dolor sit amet, consetetur sadipsLorem ipsum dolor sit amet, consetetur sadipsLorem ipsum dolor sit amet, consetetur sadipsLorem ipsum dolor sit amet, consetetur sadipsLorem ipsum dolor sit amet, consetetur sadipsLorem ipsum dolor sit amet, consetetur sadipsLorem ipsum dolor sit amet, consetetur sadips','2022-02-19 06:41:20','2022-03-24 06:41:00',69,5,NULL,2,3),(3,'eörgjlweörklgj','ödkjgbhwöekrjh','2022-02-22 11:14:18','2022-03-12 11:13:00',76,3,NULL,1,2),(4,'Bottas fights back for last-gasp podium in','qweg','2022-02-25 09:06:44','2022-02-26 09:06:00',5,4,NULL,5,3);
/*!40000 ALTER TABLE `todo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) DEFAULT NULL,
  `firstName` varchar(45) DEFAULT NULL,
  `lastName` varchar(45) DEFAULT NULL,
  `hash` varchar(255) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','Tristan','Aeschbach','$2y$10$9UwUd3yoUP3eqqn.H0QR/OL7Cv0KB3lP2KLJD9ATkhT6EF2kj0VnC',1),(2,'ValtteriBottas','Valtteri','Bottas','$2y$10$NlQBPtIkA1MXBLLR2.j44O8O6akpuIb6Tju1HGm7GA5OoVbkz5uSi',0),(3,'User01','AA','AA','$2y$10$DTJpdzEi7beqwvtHIF6FPOvR5jXCKp6u/040/fGwGTvYS07i2rTES',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-02-25  9:31:14
