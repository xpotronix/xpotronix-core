-- MySQL dump 10.13  Distrib 5.1.54, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: patrocinio
-- ------------------------------------------------------
-- Server version	5.1.54-1ubuntu4-log

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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_acl`
--

LOCK TABLES `gacl_acl` WRITE;
/*!40000 ALTER TABLE `gacl_acl` DISABLE KEYS */;
INSERT INTO `gacl_acl` VALUES (10,'user',1,1,'','Ingreso al sistema',1212197023),(11,'user',1,1,'','Permisos Administrador',1146434990),(23,'user',1,1,NULL,'Solo Lectura Usuario',0),(24,'user',1,1,NULL,'Recepción Escritura',0),(25,'user',1,1,NULL,'Permisos Anon',0);
/*!40000 ALTER TABLE `gacl_acl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_acl_sections`
--

DROP TABLE IF EXISTS `gacl_acl_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gacl_acl_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(11) NOT NULL DEFAULT '0',
  `name` varchar(230) NOT NULL DEFAULT '',
  `hidden` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gacl_value_acl_sections` (`value`),
  KEY `gacl_hidden_acl_sections` (`hidden`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_aco`
--

LOCK TABLES `gacl_aco` WRITE;
/*!40000 ALTER TABLE `gacl_aco` DISABLE KEYS */;
INSERT INTO `gacl_aco` VALUES (10,'system','login',1,'Login',0),(11,'application','access',1,'Access',0),(12,'application','view',2,'View',0),(13,'application','add',3,'Add',0),(14,'application','edit',4,'Edit',0),(15,'application','delete',5,'Delete',0),(16,'application','list',6,'List',0),(17,'application','sign',7,'Sign',0);
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
INSERT INTO `gacl_aco_map` VALUES (10,'system','login'),(11,'application','access'),(11,'application','add'),(11,'application','delete'),(11,'application','edit'),(11,'application','list'),(11,'application','view'),(23,'application','access'),(23,'application','list'),(23,'application','view'),(24,'application','add'),(24,'application','edit'),(25,'application','access'),(25,'application','list'),(25,'application','view');
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
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_aro`
--

LOCK TABLES `gacl_aro` WRITE;
/*!40000 ALTER TABLE `gacl_aro` DISABLE KEYS */;
INSERT INTO `gacl_aro` VALUES (10,'user','1',1,'admin',0),(18,'user','9',1,'anon',0),(19,'user','10',1,'eduardo',0),(20,'user','11',1,'mvquiroga',0),(21,'user','12',1,'vroberto',0),(22,'user','',1,'cgiuffre',0),(23,'user','13',1,'vbouza',0),(24,'user','16',1,'cborda',0),(25,'user','17',1,'asibileau',0),(26,'user','18',1,'ahusni',0),(27,'user','19',1,'sserisier',0),(28,'user','20',1,'cadrian',0),(29,'user','21',1,'grodriguez',0),(30,'user','22',1,'mvtarzia',0),(31,'user','23',1,'dlovizolo',0),(32,'user','24',1,'gchamorro',0),(33,'user','25',1,'pplohn',0),(34,'user','26',1,'jzechillo',0),(35,'user','27',1,'mmassone',0),(36,'user','28',1,'jogauna',0),(37,'user','29',1,'mgiavarino',0),(38,'user','30',1,'ecorsiglia',0),(40,'user','34',1,'grizzo',0),(41,'user','35',1,'vrossa',0),(42,'user','36',1,'gabba',0),(44,'user','38',1,'slarregina',0),(45,'user','39',1,'mquinteros',0),(46,'user','40',1,'msartori',0),(47,'user','41',1,'festevez',0),(48,'user','42',1,'ecasal',0),(49,'user','43',1,'cgiuffre',0),(50,'user','44',1,'scruzado',0),(51,'user','45',1,'eleopardo',0),(52,'user','46',1,'ahildt',0),(53,'user','47',1,'rfunes',0),(54,'user','48',1,'dlovisolo',0),(55,'user','49',1,'ksanchez',0),(56,'user','50',1,'jcasas',0),(57,'user','51',1,'palvarado',0),(58,'user','63',1,'rgenoves',0),(60,'user','68',1,'cacosta',0),(61,'user','69',1,'agrossman',0),(62,'user','70',1,'kgimenez',0),(63,'user','71',1,'vtarzia',0),(64,'user','72',1,'cadorna',0);
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
  PRIMARY KEY (`id`),
  KEY `gacl_parent_id_aro_groups` (`parent_id`),
  KEY `gacl_value_aro_groups` (`value`),
  KEY `gacl_lft_rgt_aro_groups` (`lft`,`rgt`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_aro_groups`
--

LOCK TABLES `gacl_aro_groups` WRITE;
/*!40000 ALTER TABLE `gacl_aro_groups` DISABLE KEYS */;
INSERT INTO `gacl_aro_groups` VALUES (10,0,1,12,'Roles','role'),(11,10,2,3,'Administrator','admin'),(12,10,4,5,'Anonymous','anon'),(13,10,6,7,'Invitado','guest'),(19,10,8,9,'Recepción','recepcion'),(20,10,10,11,'Comisión','comision'),(21,10,0,0,'Coordinador','coordinador'),(22,10,0,0,'Servicio Social','servicio_social');
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
INSERT INTO `gacl_aro_groups_map` VALUES (10,10),(11,11),(23,19),(23,20),(23,21),(23,22),(24,19),(24,20),(24,22),(25,12);
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
  `value` varchar(80) NOT NULL DEFAULT '',
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
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_axo`
--

LOCK TABLES `gacl_axo` WRITE;
/*!40000 ALTER TABLE `gacl_axo` DISABLE KEYS */;
INSERT INTO `gacl_axo` VALUES (1,'app','users',1,'users',0),(2,'app','t_ocupacion',1,'t_ocupacion',0),(3,'app','audiencia',1,'audiencia',0),(4,'app','modules',1,'modules',0),(5,'app','comision',1,'comision',0),(6,'app','gacl_aco',1,'gacl_aco',0),(7,'app','gacl_axo_groups',1,'gacl_axo_groups',0),(8,'app','gacl_aro_groups',1,'gacl_aro_groups',0),(9,'app','consulta',1,'consulta',0),(10,'app','gacl_groups_aro_map',1,'gacl_groups_aro_map',0),(11,'app','sessions',1,'sessions',0),(12,'app','nac',1,'nac',0),(13,'app','v_consulta_t_asunto',1,'v_consulta_t_asunto',0),(14,'app','gacl_axo_map',1,'gacl_axo_map',0),(15,'app','gacl_aco_map',1,'gacl_aco_map',0),(16,'app','gacl_acl',1,'gacl_acl',0),(17,'app','sms',1,'sms',0),(18,'app','file_utils',1,'file_utils',0),(19,'app','user_preferences',1,'user_preferences',0),(20,'app','gacl_aro',1,'gacl_aro',0),(21,'app','gacl_axo_groups_map',1,'gacl_axo_groups_map',0),(22,'app','gacl_aco_sections',1,'gacl_aco_sections',0),(23,'app','resultado',1,'resultado',0),(24,'app','gacl_axo_sections',1,'gacl_axo_sections',0),(25,'app','v_consulta_t_rechazo',1,'v_consulta_t_rechazo',0),(26,'app','t_educacion',1,'t_educacion',0),(27,'app','t_rechazo',1,'t_rechazo',0),(28,'app','atendido',1,'atendido',0),(29,'app','persona_audiencia',1,'persona_audiencia',0),(30,'app','nacionalidad',1,'nacionalidad',0),(31,'app','user_access_log',1,'user_access_log',0),(32,'app','t_ingreso',1,'t_ingreso',0),(33,'app','mediador',1,'mediador',0),(34,'app','t_turno',1,'t_turno',0),(35,'app','mailer',1,'mailer',0),(36,'app','audit',1,'audit',0),(37,'app','t_doc',1,'t_doc',0),(38,'app','gacl_aro_groups_map',1,'gacl_aro_groups_map',0),(39,'app','gacl_aro_sections',1,'gacl_aro_sections',0),(40,'app','v_consulta_t_estado',1,'v_consulta_t_estado',0),(41,'app','t_estado',1,'t_estado',0),(43,'app','gacl_axo',1,'gacl_axo',0),(44,'app','t_orient',1,'t_orient',0),(45,'app','gacl_acl_sections',1,'gacl_acl_sections',0),(46,'app','gacl_groups_axo_map',1,'gacl_groups_axo_map',0),(47,'app','v_consulta_comision',1,'v_consulta_comision',0),(48,'app','mediacion',1,'mediacion',0),(49,'app','gacl_aro_map',1,'gacl_aro_map',0),(50,'app','user_events',1,'user_events',0),(51,'app','localidad',1,'localidad',0),(52,'app','contador',1,'contador',0),(53,'app','file',1,'file',0),(54,'app','t_asunto',1,'t_asunto',0),(55,'app','persona',1,'persona',0),(56,'app','t_sexo',1,'t_sexo',0),(57,'app','consultante',1,'consultante',0),(60,'app','provincia',1,'provincia',0),(61,'app','departamento',1,'departamento',0),(62,'app','usuario',1,'usuario',0),(63,'app','gacl_phpgacl',1,'gacl_phpgacl',0),(64,'app','gacl_acl_seq',1,'gacl_acl_seq',0),(65,'app','gacl_aro_groups_id_seq',1,'gacl_aro_groups_id_seq',0),(66,'app','gacl_aro_seq',1,'gacl_aro_seq',0),(67,'app','gacl_axo_groups_id_seq',1,'gacl_axo_groups_id_seq',0),(68,'app','gacl_aco_seq',1,'gacl_aco_seq',0),(69,'app','gacl_axo_seq',1,'gacl_axo_seq',0),(70,'app','gacl_aco_sections_seq',1,'gacl_aco_sections_seq',0),(71,'app','gacl_axo_sections_seq',1,'gacl_axo_sections_seq',0),(72,'app','comision_usuario',1,'comision_usuario',0),(73,'app','gacl_aro_sections_seq',1,'gacl_aro_sections_seq',0),(74,'app','login',1,'login',0),(75,'app','forgot_password',1,'forgot_password',0),(76,'app','tip',1,'tip',0),(77,'app','help',1,'help',0),(78,'app','home',1,'home',0),(79,'app','register',1,'register',0);
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
  PRIMARY KEY (`id`),
  KEY `gacl_parent_id_axo_groups` (`parent_id`),
  KEY `gacl_value_axo_groups` (`value`),
  KEY `gacl_lft_rgt_axo_groups` (`lft`,`rgt`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gacl_axo_groups`
--

LOCK TABLES `gacl_axo_groups` WRITE;
/*!40000 ALTER TABLE `gacl_axo_groups` DISABLE KEYS */;
INSERT INTO `gacl_axo_groups` VALUES (10,0,1,10,'Modules','mod'),(11,10,2,3,'All Modules','all'),(12,10,4,5,'Admin Modules','admin'),(13,10,6,7,'User Modules','non_admin'),(14,10,8,9,'Recepcion Escritura','recepcion_write'),(15,10,0,0,'Anon Modules','anon');
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
INSERT INTO `gacl_axo_groups_map` VALUES (11,11),(23,13),(24,14),(25,15);
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
  `value` varchar(80) NOT NULL DEFAULT '',
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
INSERT INTO `gacl_groups_aro_map` VALUES (11,10),(11,20),(12,18),(13,47),(19,21),(19,22),(19,37),(19,40),(19,41),(19,42),(19,44),(19,45),(19,46),(19,48),(19,50),(19,52),(19,57),(19,60),(19,62),(19,64),(20,19),(20,25),(20,29),(20,30),(20,32),(20,33),(20,55),(20,58),(20,63),(21,61);
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
INSERT INTO `gacl_groups_axo_map` VALUES (11,1),(11,2),(11,3),(11,4),(11,5),(11,6),(11,7),(11,8),(11,9),(11,10),(11,11),(11,12),(11,13),(11,14),(11,15),(11,16),(11,17),(11,18),(11,19),(11,20),(11,21),(11,22),(11,23),(11,24),(11,25),(11,26),(11,27),(11,28),(11,29),(11,30),(11,31),(11,32),(11,33),(11,34),(11,35),(11,36),(11,37),(11,38),(11,39),(11,40),(11,41),(11,42),(11,43),(11,44),(11,45),(11,46),(11,47),(11,48),(11,49),(11,50),(11,51),(11,52),(11,53),(11,54),(11,55),(11,56),(11,57),(11,58),(11,59),(11,60),(11,61),(11,62),(11,63),(11,64),(11,65),(11,66),(11,67),(11,68),(11,69),(11,70),(11,71),(11,72),(11,73),(11,74),(11,75),(11,76),(11,77),(11,78),(11,79),(12,3),(12,4),(12,5),(12,6),(12,7),(12,8),(12,9),(12,10),(12,11),(12,12),(12,13),(12,14),(12,15),(12,16),(12,17),(12,18),(12,19),(12,20),(12,21),(12,22),(12,23),(12,24),(12,25),(12,26),(12,27),(12,28),(12,29),(12,30),(12,31),(12,32),(12,33),(12,34),(12,35),(12,36),(12,37),(12,38),(12,39),(12,40),(12,41),(12,43),(12,44),(12,45),(12,46),(12,47),(12,48),(12,49),(12,50),(12,51),(12,52),(12,53),(12,54),(12,55),(12,56),(12,57),(13,2),(13,3),(13,5),(13,9),(13,12),(13,13),(13,23),(13,25),(13,26),(13,27),(13,28),(13,29),(13,30),(13,32),(13,33),(13,34),(13,37),(13,40),(13,41),(13,44),(13,47),(13,48),(13,51),(13,54),(13,55),(13,56),(13,57),(13,60),(13,61),(14,3),(14,9),(14,29),(14,48),(14,52),(14,55),(14,57),(15,77),(15,78);
/*!40000 ALTER TABLE `gacl_groups_axo_map` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-08-29 22:24:38
