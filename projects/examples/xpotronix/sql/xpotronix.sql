-- MySQL dump 10.13  Distrib 5.7.22, for Linux (x86_64)
--
-- Host: mysql1.jusbaires.gov.ar    Database: xpotronix
-- ------------------------------------------------------
-- Server version	5.7.22-0ubuntu0.16.04.1-log

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
-- Table structure for table `audit`
--

DROP TABLE IF EXISTS `audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit` (
  `ID` char(32) NOT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `session_id` varchar(32) DEFAULT NULL,
  `xpid` char(32) DEFAULT NULL,
  `when` datetime DEFAULT NULL,
  `source_ip` varchar(50) DEFAULT NULL,
  `URL` longtext NOT NULL,
  `module` varchar(255) NOT NULL,
  `action` varchar(255) DEFAULT NULL,
  `proc_required` varchar(255) DEFAULT NULL,
  `transac_data` longtext,
  `type` enum('GET','HEAD','POST','PUT') NOT NULL,
  `messages` text NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `user_id` (`user_id`),
  KEY `session_id` (`session_id`),
  KEY `source_ip` (`source_ip`),
  KEY `action` (`action`),
  KEY `process` (`proc_required`),
  KEY `when` (`when`),
  KEY `type` (`type`),
  KEY `module` (`module`),
  KEY `xpid` (`xpid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gacl_acl`
--

DROP TABLE IF EXISTS `gacl_acl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_acl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_value` varchar(80) NOT NULL DEFAULT 'system',
  `allow` int(11) NOT NULL DEFAULT '0',
  `enabled` int(11) NOT NULL DEFAULT '0',
  `return_value` longtext,
  `note` longtext,
  `updated_date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `gacl_enabled_acl` (`enabled`),
  KEY `gacl_section_value_acl` (`section_value`),
  KEY `gacl_updated_date_acl` (`updated_date`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_acl`
--

LOCK TABLES `gacl_acl` WRITE;
/*!40000 ALTER TABLE `gacl_acl` DISABLE KEYS */;
INSERT INTO `gacl_acl` VALUES (10,'user',1,1,'','Permiso de Login',1152198060);
INSERT INTO `gacl_acl` VALUES (11,'user',1,1,'','Permisos de Administrador',1152214331);
INSERT INTO `gacl_acl` VALUES (12,'user',1,1,'','Permisos de Anónimo',1152198060);
/*!40000 ALTER TABLE `gacl_acl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_acl_sections`
--

DROP TABLE IF EXISTS `gacl_acl_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_acl_sections` (
  `id` int(11) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(11) NOT NULL DEFAULT '0',
  `name` varchar(230) NOT NULL DEFAULT '',
  `hidden` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gacl_value_acl_sections` (`value`),
  KEY `gacl_hidden_acl_sections` (`hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_acl_sections`
--

LOCK TABLES `gacl_acl_sections` WRITE;
/*!40000 ALTER TABLE `gacl_acl_sections` DISABLE KEYS */;
INSERT INTO `gacl_acl_sections` VALUES (1,'system',1,'System',0);
INSERT INTO `gacl_acl_sections` VALUES (2,'user',2,'User',0);
/*!40000 ALTER TABLE `gacl_acl_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aco`
--

DROP TABLE IF EXISTS `gacl_aco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_aco` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_value` varchar(80) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `hidden` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gacl_section_value_value_aco` (`section_value`,`value`),
  KEY `gacl_hidden_aco` (`hidden`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_aco`
--

LOCK TABLES `gacl_aco` WRITE;
/*!40000 ALTER TABLE `gacl_aco` DISABLE KEYS */;
INSERT INTO `gacl_aco` VALUES (10,'system','login',1,'Login',0);
INSERT INTO `gacl_aco` VALUES (11,'application','access',1,'Access',0);
INSERT INTO `gacl_aco` VALUES (12,'application','view',2,'View',0);
INSERT INTO `gacl_aco` VALUES (13,'application','add',3,'Add',0);
INSERT INTO `gacl_aco` VALUES (14,'application','edit',4,'Edit',0);
INSERT INTO `gacl_aco` VALUES (15,'application','delete',5,'Delete',0);
INSERT INTO `gacl_aco` VALUES (16,'application','list',6,'List',0);
/*!40000 ALTER TABLE `gacl_aco` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aco_map`
--

DROP TABLE IF EXISTS `gacl_aco_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_aco_map` (
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `section_value` varchar(80) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`acl_id`,`section_value`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_aco_map`
--

LOCK TABLES `gacl_aco_map` WRITE;
/*!40000 ALTER TABLE `gacl_aco_map` DISABLE KEYS */;
INSERT INTO `gacl_aco_map` VALUES (10,'system','login');
INSERT INTO `gacl_aco_map` VALUES (11,'application','access');
INSERT INTO `gacl_aco_map` VALUES (11,'application','add');
INSERT INTO `gacl_aco_map` VALUES (11,'application','delete');
INSERT INTO `gacl_aco_map` VALUES (11,'application','edit');
INSERT INTO `gacl_aco_map` VALUES (11,'application','list');
INSERT INTO `gacl_aco_map` VALUES (11,'application','view');
INSERT INTO `gacl_aco_map` VALUES (12,'application','access');
INSERT INTO `gacl_aco_map` VALUES (12,'application','add');
INSERT INTO `gacl_aco_map` VALUES (12,'application','list');
INSERT INTO `gacl_aco_map` VALUES (12,'application','view');
/*!40000 ALTER TABLE `gacl_aco_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aco_sections`
--

DROP TABLE IF EXISTS `gacl_aco_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_aco_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(11) NOT NULL DEFAULT '0',
  `name` varchar(230) NOT NULL DEFAULT '',
  `hidden` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gacl_value_aco_sections` (`value`),
  KEY `gacl_hidden_aco_sections` (`hidden`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_aco_sections`
--

LOCK TABLES `gacl_aco_sections` WRITE;
/*!40000 ALTER TABLE `gacl_aco_sections` DISABLE KEYS */;
INSERT INTO `gacl_aco_sections` VALUES (10,'system',1,'System',0);
INSERT INTO `gacl_aco_sections` VALUES (11,'application',2,'Application',0);
/*!40000 ALTER TABLE `gacl_aco_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aro`
--

DROP TABLE IF EXISTS `gacl_aro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_aro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_value` varchar(80) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `hidden` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gacl_section_value_value_aro` (`section_value`,`value`),
  KEY `gacl_hidden_aro` (`hidden`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_aro`
--

LOCK TABLES `gacl_aro` WRITE;
/*!40000 ALTER TABLE `gacl_aro` DISABLE KEYS */;
INSERT INTO `gacl_aro` VALUES (10,'user','1',1,'admin',0);
INSERT INTO `gacl_aro` VALUES (11,'user','2',1,'anon',0);
/*!40000 ALTER TABLE `gacl_aro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aro_groups`
--

DROP TABLE IF EXISTS `gacl_aro_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_aro_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`value`),
  UNIQUE KEY `value` (`value`),
  KEY `gacl_value_aro_groups` (`value`),
  KEY `gacl_lft_rgt_aro_groups` (`lft`,`rgt`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_aro_groups`
--

LOCK TABLES `gacl_aro_groups` WRITE;
/*!40000 ALTER TABLE `gacl_aro_groups` DISABLE KEYS */;
INSERT INTO `gacl_aro_groups` VALUES (10,0,1,14,'Roles','role');
INSERT INTO `gacl_aro_groups` VALUES (11,10,2,3,'Administrator','admin');
INSERT INTO `gacl_aro_groups` VALUES (12,10,4,5,'Anon','anon');
/*!40000 ALTER TABLE `gacl_aro_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aro_groups_map`
--

DROP TABLE IF EXISTS `gacl_aro_groups_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_aro_groups_map` (
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`acl_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_aro_groups_map`
--

LOCK TABLES `gacl_aro_groups_map` WRITE;
/*!40000 ALTER TABLE `gacl_aro_groups_map` DISABLE KEYS */;
INSERT INTO `gacl_aro_groups_map` VALUES (10,11);
INSERT INTO `gacl_aro_groups_map` VALUES (11,11);
INSERT INTO `gacl_aro_groups_map` VALUES (12,12);
/*!40000 ALTER TABLE `gacl_aro_groups_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aro_map`
--

DROP TABLE IF EXISTS `gacl_aro_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_aro_map` (
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `section_value` varchar(80) NOT NULL DEFAULT '0',
  `value` varchar(230) NOT NULL DEFAULT '',
  PRIMARY KEY (`acl_id`,`section_value`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_aro_map`
--

LOCK TABLES `gacl_aro_map` WRITE;
/*!40000 ALTER TABLE `gacl_aro_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `gacl_aro_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aro_sections`
--

DROP TABLE IF EXISTS `gacl_aro_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_aro_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(11) NOT NULL DEFAULT '0',
  `name` varchar(230) NOT NULL DEFAULT '',
  `hidden` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gacl_value_aro_sections` (`value`),
  KEY `gacl_hidden_aro_sections` (`hidden`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_aro_sections`
--

LOCK TABLES `gacl_aro_sections` WRITE;
/*!40000 ALTER TABLE `gacl_aro_sections` DISABLE KEYS */;
INSERT INTO `gacl_aro_sections` VALUES (10,'user',1,'Users',0);
/*!40000 ALTER TABLE `gacl_aro_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_axo`
--

DROP TABLE IF EXISTS `gacl_axo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_axo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_value` varchar(80) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `hidden` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gacl_section_value_value_axo` (`section_value`,`value`),
  KEY `gacl_hidden_axo` (`hidden`)
) ENGINE=InnoDB AUTO_INCREMENT=250 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_axo`
--

LOCK TABLES `gacl_axo` WRITE;
/*!40000 ALTER TABLE `gacl_axo` DISABLE KEYS */;
INSERT INTO `gacl_axo` VALUES (55,'app','gacl_acl',1,'gacl_acl',0);
INSERT INTO `gacl_axo` VALUES (56,'app','gacl_acl_sections',1,'gacl_acl_sections',0);
INSERT INTO `gacl_axo` VALUES (58,'app','gacl_aco',1,'gacl_aco',0);
INSERT INTO `gacl_axo` VALUES (59,'app','gacl_aco_map',1,'gacl_aco_map',0);
INSERT INTO `gacl_axo` VALUES (60,'app','gacl_aco_sections',1,'gacl_aco_sections',0);
INSERT INTO `gacl_axo` VALUES (63,'app','gacl_aro',1,'gacl_aro',0);
INSERT INTO `gacl_axo` VALUES (64,'app','gacl_aro_groups',1,'gacl_aro_groups',0);
INSERT INTO `gacl_axo` VALUES (66,'app','gacl_aro_groups_map',1,'gacl_aro_groups_map',0);
INSERT INTO `gacl_axo` VALUES (67,'app','gacl_aro_map',1,'gacl_aro_map',0);
INSERT INTO `gacl_axo` VALUES (68,'app','gacl_aro_sections',1,'gacl_aro_sections',0);
INSERT INTO `gacl_axo` VALUES (70,'app','gacl_axo',1,'gacl_axo',0);
INSERT INTO `gacl_axo` VALUES (71,'app','gacl_axo_groups',1,'gacl_axo_groups',0);
INSERT INTO `gacl_axo` VALUES (73,'app','gacl_axo_groups_map',1,'gacl_axo_groups_map',0);
INSERT INTO `gacl_axo` VALUES (74,'app','gacl_axo_map',1,'gacl_axo_map',0);
INSERT INTO `gacl_axo` VALUES (75,'app','gacl_axo_sections',1,'gacl_axo_sections',0);
INSERT INTO `gacl_axo` VALUES (78,'app','gacl_groups_aro_map',1,'gacl_groups_aro_map',0);
INSERT INTO `gacl_axo` VALUES (79,'app','gacl_groups_axo_map',1,'gacl_groups_axo_map',0);
INSERT INTO `gacl_axo` VALUES (82,'app','sessions',1,'sessions',0);
INSERT INTO `gacl_axo` VALUES (87,'app','user_preferences',1,'user_preferences',0);
INSERT INTO `gacl_axo` VALUES (88,'app','users',1,'users',0);
INSERT INTO `gacl_axo` VALUES (91,'app','sms',1,'sms',0);
INSERT INTO `gacl_axo` VALUES (229,'app','audit',1,'audit',0);
INSERT INTO `gacl_axo` VALUES (230,'app','mailer',1,'mailer',0);
INSERT INTO `gacl_axo` VALUES (235,'app','file_utils',1,'file_utils',0);
INSERT INTO `gacl_axo` VALUES (237,'app','contador',1,'contador',0);
INSERT INTO `gacl_axo` VALUES (240,'app','gacl_phpgacl',1,'gacl_phpgacl',0);
INSERT INTO `gacl_axo` VALUES (241,'app','tip',1,'tip',0);
INSERT INTO `gacl_axo` VALUES (242,'app','file',1,'file',0);
INSERT INTO `gacl_axo` VALUES (243,'app','test',1,'test',0);
INSERT INTO `gacl_axo` VALUES (244,'app','forgot_password',1,'forgot_password',0);
INSERT INTO `gacl_axo` VALUES (245,'app','register',1,'register',0);
INSERT INTO `gacl_axo` VALUES (246,'app','home',1,'home',0);
INSERT INTO `gacl_axo` VALUES (247,'app','login',1,'login',0);
INSERT INTO `gacl_axo` VALUES (248,'app','help',1,'help',0);
INSERT INTO `gacl_axo` VALUES (249,'app','test_detalle',1,'test_detalle',0);
/*!40000 ALTER TABLE `gacl_axo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_axo_groups`
--

DROP TABLE IF EXISTS `gacl_axo_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_axo_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`value`),
  KEY `gacl_parent_id_axo_groups` (`parent_id`),
  KEY `gacl_value_axo_groups` (`value`),
  KEY `gacl_lft_rgt_axo_groups` (`lft`,`rgt`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_axo_groups`
--

LOCK TABLES `gacl_axo_groups` WRITE;
/*!40000 ALTER TABLE `gacl_axo_groups` DISABLE KEYS */;
INSERT INTO `gacl_axo_groups` VALUES (10,0,1,12,'Modules','mod');
INSERT INTO `gacl_axo_groups` VALUES (11,10,2,3,'All Modules','all');
INSERT INTO `gacl_axo_groups` VALUES (12,10,4,5,'Admin Modules','admin');
INSERT INTO `gacl_axo_groups` VALUES (13,10,6,7,'Non-Admin Modules','non_admin');
INSERT INTO `gacl_axo_groups` VALUES (16,10,0,0,'Anon Modules','');
/*!40000 ALTER TABLE `gacl_axo_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_axo_groups_map`
--

DROP TABLE IF EXISTS `gacl_axo_groups_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_axo_groups_map` (
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`acl_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_axo_groups_map`
--

LOCK TABLES `gacl_axo_groups_map` WRITE;
/*!40000 ALTER TABLE `gacl_axo_groups_map` DISABLE KEYS */;
INSERT INTO `gacl_axo_groups_map` VALUES (11,11);
INSERT INTO `gacl_axo_groups_map` VALUES (12,16);
/*!40000 ALTER TABLE `gacl_axo_groups_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_axo_map`
--

DROP TABLE IF EXISTS `gacl_axo_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_axo_map` (
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `section_value` varchar(80) NOT NULL DEFAULT '0',
  `value` varchar(230) NOT NULL DEFAULT '',
  PRIMARY KEY (`acl_id`,`section_value`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_axo_map`
--

LOCK TABLES `gacl_axo_map` WRITE;
/*!40000 ALTER TABLE `gacl_axo_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `gacl_axo_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_axo_sections`
--

DROP TABLE IF EXISTS `gacl_axo_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_axo_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(11) NOT NULL DEFAULT '0',
  `name` varchar(230) NOT NULL DEFAULT '',
  `hidden` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gacl_value_axo_sections` (`value`),
  KEY `gacl_hidden_axo_sections` (`hidden`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_axo_sections`
--

LOCK TABLES `gacl_axo_sections` WRITE;
/*!40000 ALTER TABLE `gacl_axo_sections` DISABLE KEYS */;
INSERT INTO `gacl_axo_sections` VALUES (10,'sys',1,'System',0);
INSERT INTO `gacl_axo_sections` VALUES (11,'app',2,'Application',0);
/*!40000 ALTER TABLE `gacl_axo_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_groups_aro_map`
--

DROP TABLE IF EXISTS `gacl_groups_aro_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_groups_aro_map` (
  `group_id` int(11) NOT NULL DEFAULT '0',
  `aro_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`aro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_groups_aro_map`
--

LOCK TABLES `gacl_groups_aro_map` WRITE;
/*!40000 ALTER TABLE `gacl_groups_aro_map` DISABLE KEYS */;
INSERT INTO `gacl_groups_aro_map` VALUES (11,10);
INSERT INTO `gacl_groups_aro_map` VALUES (12,11);
/*!40000 ALTER TABLE `gacl_groups_aro_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_groups_axo_map`
--

DROP TABLE IF EXISTS `gacl_groups_axo_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_groups_axo_map` (
  `group_id` int(11) NOT NULL DEFAULT '0',
  `axo_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`axo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_groups_axo_map`
--

LOCK TABLES `gacl_groups_axo_map` WRITE;
/*!40000 ALTER TABLE `gacl_groups_axo_map` DISABLE KEYS */;
INSERT INTO `gacl_groups_axo_map` VALUES (11,55);
INSERT INTO `gacl_groups_axo_map` VALUES (11,56);
INSERT INTO `gacl_groups_axo_map` VALUES (11,58);
INSERT INTO `gacl_groups_axo_map` VALUES (11,59);
INSERT INTO `gacl_groups_axo_map` VALUES (11,60);
INSERT INTO `gacl_groups_axo_map` VALUES (11,63);
INSERT INTO `gacl_groups_axo_map` VALUES (11,64);
INSERT INTO `gacl_groups_axo_map` VALUES (11,66);
INSERT INTO `gacl_groups_axo_map` VALUES (11,67);
INSERT INTO `gacl_groups_axo_map` VALUES (11,68);
INSERT INTO `gacl_groups_axo_map` VALUES (11,70);
INSERT INTO `gacl_groups_axo_map` VALUES (11,71);
INSERT INTO `gacl_groups_axo_map` VALUES (11,73);
INSERT INTO `gacl_groups_axo_map` VALUES (11,74);
INSERT INTO `gacl_groups_axo_map` VALUES (11,75);
INSERT INTO `gacl_groups_axo_map` VALUES (11,78);
INSERT INTO `gacl_groups_axo_map` VALUES (11,79);
INSERT INTO `gacl_groups_axo_map` VALUES (11,82);
INSERT INTO `gacl_groups_axo_map` VALUES (11,87);
INSERT INTO `gacl_groups_axo_map` VALUES (11,88);
INSERT INTO `gacl_groups_axo_map` VALUES (11,91);
INSERT INTO `gacl_groups_axo_map` VALUES (11,229);
INSERT INTO `gacl_groups_axo_map` VALUES (11,230);
INSERT INTO `gacl_groups_axo_map` VALUES (11,235);
INSERT INTO `gacl_groups_axo_map` VALUES (11,237);
INSERT INTO `gacl_groups_axo_map` VALUES (11,240);
INSERT INTO `gacl_groups_axo_map` VALUES (11,241);
INSERT INTO `gacl_groups_axo_map` VALUES (11,242);
INSERT INTO `gacl_groups_axo_map` VALUES (11,243);
INSERT INTO `gacl_groups_axo_map` VALUES (11,244);
INSERT INTO `gacl_groups_axo_map` VALUES (11,245);
INSERT INTO `gacl_groups_axo_map` VALUES (11,246);
INSERT INTO `gacl_groups_axo_map` VALUES (11,247);
INSERT INTO `gacl_groups_axo_map` VALUES (11,248);
INSERT INTO `gacl_groups_axo_map` VALUES (11,249);
/*!40000 ALTER TABLE `gacl_groups_axo_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_phpgacl`
--

DROP TABLE IF EXISTS `gacl_phpgacl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_phpgacl` (
  `name` varchar(230) NOT NULL DEFAULT '',
  `value` varchar(230) NOT NULL DEFAULT '',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_phpgacl`
--

LOCK TABLES `gacl_phpgacl` WRITE;
/*!40000 ALTER TABLE `gacl_phpgacl` DISABLE KEYS */;
INSERT INTO `gacl_phpgacl` VALUES ('schema_version','2.1');
INSERT INTO `gacl_phpgacl` VALUES ('version','3.3.2-xpotronix');
/*!40000 ALTER TABLE `gacl_phpgacl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `help`
--

DROP TABLE IF EXISTS `help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help` (
  `ID` char(32) NOT NULL,
  `div_id` char(32) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `div_id` (`div_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help`
--

LOCK TABLES `help` WRITE;
/*!40000 ALTER TABLE `help` DISABLE KEYS */;
/*!40000 ALTER TABLE `help` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `home`
--

DROP TABLE IF EXISTS `home`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `home` (
  `ID` char(32) NOT NULL,
  `div_id` char(32) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `div_id` (`div_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `home`
--

LOCK TABLES `home` WRITE;
/*!40000 ALTER TABLE `home` DISABLE KEYS */;
INSERT INTO `home` VALUES ('fa1348f78ddc23bde6381b3145cbd4e1','banner','<div style=\"height: 60px;\"><img src=\"images/xpotronix.png\"/><div  style=\"float: right;align:left;vertical-align:top;font-size:24px;font-family:verdana\">xpotronix :: App de Ejemplo</div></div>');
/*!40000 ALTER TABLE `home` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `session_id` varchar(40) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `session_data` longblob,
  `session_cookies` longblob NOT NULL,
  `session_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `session_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` char(32) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `session_updated` (`session_updated`),
  KEY `session_created` (`session_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `test`
--

DROP TABLE IF EXISTS `test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test` (
  `ID` char(32) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `monto` double(11,2) DEFAULT NULL,
  `veces` int(11) DEFAULT NULL,
  `observaciones` text,
  `hora` time DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `fechahora` datetime DEFAULT NULL,
  `sino` tinyint(4) DEFAULT NULL,
  `archivo` blob,
  `enumeracion` enum('','blanco','amarillo','azul','rojo','verde') DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test`
--

LOCK TABLES `test` WRITE;
/*!40000 ALTER TABLE `test` DISABLE KEYS */;
INSERT INTO `test` VALUES ('D5WAT898igQPx9Foj9CyTJRE22XBigWu','Eduardo','Monzó',1000.00,NULL,'Observaciones\n',NULL,'2018-07-11','2018-07-10 00:00:00',1,NULL,'amarillo');
/*!40000 ALTER TABLE `test` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_detalle`
--

DROP TABLE IF EXISTS `test_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_detalle` (
  `ID` char(32) NOT NULL,
  `test_ID` char(32) DEFAULT NULL,
  `linea` int(11) DEFAULT NULL,
  `detalle` varchar(100) DEFAULT NULL,
  `monto` double(12,2) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_detalle`
--

LOCK TABLES `test_detalle` WRITE;
/*!40000 ALTER TABLE `test_detalle` DISABLE KEYS */;
INSERT INTO `test_detalle` VALUES ('6FVwdVX1H3NN3UQ6WAOp65vojjeZahkY','D5WAT898igQPx9Foj9CyTJRE22XBigWu',1,'prueba',300.00);
/*!40000 ALTER TABLE `test_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tip`
--

DROP TABLE IF EXISTS `tip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tip` (
  `ID` char(32) NOT NULL,
  `div_id` char(32) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `div_id` (`div_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tip`
--

LOCK TABLES `tip` WRITE;
/*!40000 ALTER TABLE `tip` DISABLE KEYS */;
/*!40000 ALTER TABLE `tip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_preferences` (
  `id` char(32) NOT NULL,
  `user_id` char(32) NOT NULL,
  `var_name` varchar(255) NOT NULL,
  `var_value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `name` (`var_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_preferences`
--

LOCK TABLES `user_preferences` WRITE;
/*!40000 ALTER TABLE `user_preferences` DISABLE KEYS */;
INSERT INTO `user_preferences` VALUES ('wqUBYEBnckmChKPDFYn8fBwNZJhrnlYZ','1','users_xpGrid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A230%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A7%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A8%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A9%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A10%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A11%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1');
INSERT INTO `user_preferences` VALUES ('l3blXQgR7jhPO5tS8fMTbKxvNEAARejJ','1','audit_xpGrid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A225%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A310%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A305%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A7%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A8%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A9%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A10%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A11%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A12%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1');
INSERT INTO `user_preferences` VALUES ('OssYfGf5l75KzxeDWtX4iOPWdUELOUY7','1','ex','s%3Ahome');
INSERT INTO `user_preferences` VALUES ('OMVYZEgbYMTA8LjwTpdnOgDppkp5GCCU','2','ex','s%3Ahome');
INSERT INTO `user_preferences` VALUES ('zTE4jBaAYDGpvA2np8xHM9xHIzGR2ZQM','1','inspect_test_xpForm','o%3Awidth%3Dn%253A600%5Eheight%3Dn%253A586%5Ex%3Dn%253A460%5Ey%3Dn%253A187');
INSERT INTO `user_preferences` VALUES ('w1gNQzT5h1UIfkRlOUzjxrPVNNA7vVES','1','test_detalle_xpGrid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A252%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1');
INSERT INTO `user_preferences` VALUES ('bygDXww8YyEgPnn8mExRuQFtlrHhaEqT','1','test_xpGrid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A198%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A7%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A8%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A9%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A10%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A11%25255Ewidth%25253Dn%2525253A100%25255Esortable%25253Db%2525253A1');
/*!40000 ALTER TABLE `user_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_contact` int(11) NOT NULL DEFAULT '0',
  `user_username` varchar(255) NOT NULL DEFAULT '',
  `user_password` char(40) DEFAULT NULL,
  `user_parent` int(11) NOT NULL DEFAULT '0',
  `user_type` tinyint(3) NOT NULL DEFAULT '0',
  `user_company` int(11) DEFAULT '0',
  `user_department` int(11) DEFAULT '0',
  `user_owner` int(11) NOT NULL DEFAULT '0',
  `user_signature` text,
  `t_actor_ID` int(11) DEFAULT NULL,
  `actor_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `idx_uid` (`user_username`),
  KEY `idx_pwd` (`user_password`),
  KEY `idx_user_parent` (`user_parent`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'admin','21232f297a57a5a743894a0e4a801fc3',0,1,0,0,0,'',NULL,NULL);
INSERT INTO `users` VALUES (2,0,'anon','2ae66f90b7788ab8950e8f81b829c947',0,1,0,0,0,NULL,NULL,NULL);
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

-- Dump completed on 2018-08-01  8:48:47
