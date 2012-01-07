-- MySQL dump 10.13  Distrib 5.1.41, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: xpotronix
-- ------------------------------------------------------
-- Server version	5.1.41-3ubuntu12.6-log

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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_acl`
--

LOCK TABLES `gacl_acl` WRITE;
/*!40000 ALTER TABLE `gacl_acl` DISABLE KEYS */;
INSERT INTO `gacl_acl` VALUES (10,'user',1,1,'','Permiso de Login',1152198060),(11,'user',1,1,'','Permisos de Administrador',1152214331),(12,'user',1,1,'','Permisos de Anónimo',1152198060);
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
INSERT INTO `gacl_acl_sections` VALUES (1,'system',1,'System',0),(2,'user',2,'User',0);
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
INSERT INTO `gacl_aco` VALUES (10,'system','login',1,'Login',0),(11,'application','access',1,'Access',0),(12,'application','view',2,'View',0),(13,'application','add',3,'Add',0),(14,'application','edit',4,'Edit',0),(15,'application','delete',5,'Delete',0),(16,'application','list',6,'List',0);
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
INSERT INTO `gacl_aco_map` VALUES (10,'system','login'),(11,'application','access'),(11,'application','add'),(11,'application','delete'),(11,'application','edit'),(11,'application','list'),(11,'application','view'),(12,'application','access'),(12,'application','add'),(12,'application','list'),(12,'application','view');
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
INSERT INTO `gacl_aco_sections` VALUES (10,'system',1,'System',0),(11,'application',2,'Application',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_aro`
--

LOCK TABLES `gacl_aro` WRITE;
/*!40000 ALTER TABLE `gacl_aro` DISABLE KEYS */;
INSERT INTO `gacl_aro` VALUES (10,'user','1',1,'admin',0),(11,'user','2',1,'anon',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_aro_groups`
--

LOCK TABLES `gacl_aro_groups` WRITE;
/*!40000 ALTER TABLE `gacl_aro_groups` DISABLE KEYS */;
INSERT INTO `gacl_aro_groups` VALUES (10,0,1,14,'Roles','role'),(11,10,2,3,'Administrator','admin'),(12,10,4,5,'Anon','anon');
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
INSERT INTO `gacl_aro_groups_map` VALUES (10,11),(11,11),(12,12);
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
) ENGINE=InnoDB AUTO_INCREMENT=241 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_axo`
--

LOCK TABLES `gacl_axo` WRITE;
/*!40000 ALTER TABLE `gacl_axo` DISABLE KEYS */;
INSERT INTO `gacl_axo` VALUES (55,'app','gacl_acl',1,'gacl_acl',0),(56,'app','gacl_acl_sections',1,'gacl_acl_sections',0),(58,'app','gacl_aco',1,'gacl_aco',0),(59,'app','gacl_aco_map',1,'gacl_aco_map',0),(60,'app','gacl_aco_sections',1,'gacl_aco_sections',0),(63,'app','gacl_aro',1,'gacl_aro',0),(64,'app','gacl_aro_groups',1,'gacl_aro_groups',0),(66,'app','gacl_aro_groups_map',1,'gacl_aro_groups_map',0),(67,'app','gacl_aro_map',1,'gacl_aro_map',0),(68,'app','gacl_aro_sections',1,'gacl_aro_sections',0),(70,'app','gacl_axo',1,'gacl_axo',0),(71,'app','gacl_axo_groups',1,'gacl_axo_groups',0),(73,'app','gacl_axo_groups_map',1,'gacl_axo_groups_map',0),(74,'app','gacl_axo_map',1,'gacl_axo_map',0),(75,'app','gacl_axo_sections',1,'gacl_axo_sections',0),(78,'app','gacl_groups_aro_map',1,'gacl_groups_aro_map',0),(79,'app','gacl_groups_axo_map',1,'gacl_groups_axo_map',0),(82,'app','sessions',1,'sessions',0),(87,'app','user_preferences',1,'user_preferences',0),(88,'app','users',1,'users',0),(91,'app','sms',1,'sms',0),(229,'app','audit',1,'audit',0),(230,'app','mailer',1,'mailer',0),(235,'app','file_utils',1,'file_utils',0),(237,'app','contador',1,'contador',0),(240,'app','gacl_phpgacl',1,'gacl_phpgacl',0);
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
INSERT INTO `gacl_axo_groups` VALUES (10,0,1,12,'Modules','mod'),(11,10,2,3,'All Modules','all'),(12,10,4,5,'Admin Modules','admin'),(13,10,6,7,'Non-Admin Modules','non_admin'),(16,10,0,0,'Anon Modules','');
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
INSERT INTO `gacl_axo_groups_map` VALUES (11,11),(12,16);
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
INSERT INTO `gacl_axo_sections` VALUES (10,'sys',1,'System',0),(11,'app',2,'Application',0);
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
INSERT INTO `gacl_groups_aro_map` VALUES (11,10),(12,11);
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
INSERT INTO `gacl_groups_axo_map` VALUES (11,55),(11,56),(11,58),(11,59),(11,60),(11,63),(11,64),(11,66),(11,67),(11,68),(11,70),(11,71),(11,73),(11,74),(11,75),(11,78),(11,79),(11,82),(11,87),(11,88),(11,91),(11,229),(11,230),(11,235),(11,237),(11,240);
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
INSERT INTO `gacl_phpgacl` VALUES ('schema_version','2.1'),('version','3.3.2-xpotronix');
/*!40000 ALTER TABLE `gacl_phpgacl` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-10-05 18:45:55
