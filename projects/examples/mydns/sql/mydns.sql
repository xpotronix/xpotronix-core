-- MySQL dump 10.11
--
-- Host: localhost    Database: mydns
-- ------------------------------------------------------
-- Server version	5.0.45-Debian_1ubuntu3.4-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `rr`
--

DROP TABLE IF EXISTS `rr`;
CREATE TABLE `rr` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `zone` int(10) unsigned NOT NULL,
  `name` char(64) NOT NULL,
  `type` enum('A','AAAA','ALIAS','CNAME','HINFO','MX','NAPTR','NS','PTR','RP','SRV','TXT') default NULL,
  `data` char(128) NOT NULL,
  `aux` int(10) unsigned NOT NULL,
  `ttl` int(10) unsigned NOT NULL default '86400',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `rr` (`zone`,`name`,`type`,`data`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rr`
--

LOCK TABLES `rr` WRITE;
/*!40000 ALTER TABLE `rr` DISABLE KEYS */;
INSERT INTO `rr` VALUES (1,1,'','A','200.59.145.76',0,86400),(9,1,'www','A','200.59.145.76',0,86400),(3,2,'','A','209.40.195.10',0,86400),(4,2,'www','A','209.40.195.10',0,86400),(5,3,'','A','209.40.195.10',0,86400),(6,3,'www','A','209.40.195.10',0,86400),(7,1,'bris','A','67.223.226.189',0,86400),(8,1,'mail','A','200.59.145.76',0,86400),(12,3,'ejf','A','209.40.195.10',0,86400),(11,1,'bris2008','A','67.223.226.189',0,86400),(13,3,'estrategico','A','209.40.195.10',0,86400),(14,3,'red','A','190.245.88.237',0,86400),(15,3,'pampagrill','A','209.40.195.10',0,86400),(16,3,'www.pampagrill','A','209.40.195.10',0,86400),(17,3,'www.estrategico','A','209.40.195.10',0,86400),(18,3,'admin.ejf','A','209.40.195.10',0,86400),(19,3,'soap.ejf','A','209.40.195.10',0,86400),(20,3,'webdav.ejf','A','209.40.195.10',0,86400),(21,3,'svn','A','209.40.195.10',0,86400),(22,1,'wiki','A','209.40.195.10',0,86400),(23,3,'trac','A','209.40.195.10',0,86400);
/*!40000 ALTER TABLE `rr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `soa`
--

DROP TABLE IF EXISTS `soa`;
CREATE TABLE `soa` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `origin` char(255) NOT NULL,
  `ns` char(255) NOT NULL,
  `mbox` char(255) NOT NULL,
  `serial` int(10) unsigned NOT NULL default '1',
  `refresh` int(10) unsigned NOT NULL default '28800',
  `retry` int(10) unsigned NOT NULL default '7200',
  `expire` int(10) unsigned NOT NULL default '604800',
  `minimum` int(10) unsigned NOT NULL default '86400',
  `ttl` int(10) unsigned NOT NULL default '86400',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `origin` (`origin`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `soa`
--

LOCK TABLES `soa` WRITE;
/*!40000 ALTER TABLE `soa` DISABLE KEYS */;
INSERT INTO `soa` VALUES (1,'presencia.net.','ns1.presencia.net','admin.presencia.net',1,28800,7200,604800,86400,86400),(2,'haitiargentina.org.','ns1.presencia.net','admin.presencia.net',1,28800,7200,604800,86400,86400),(3,'justamente.net.','ns1.presencia.net','admin.presencia.net',1,28800,7200,604800,86400,86400);
/*!40000 ALTER TABLE `soa` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-05-01  6:26:18
