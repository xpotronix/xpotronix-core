-- MySQL dump 10.11
--
-- Host: localhost    Database: xpotronix
-- ------------------------------------------------------
-- Server version	5.0.67-0ubuntu6-log

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
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `config` (
  `config_id` int(11) NOT NULL auto_increment,
  `config_name` varchar(255) NOT NULL default '',
  `config_value` varchar(255) NOT NULL default '',
  `config_group` varchar(255) NOT NULL default '',
  `config_type` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`config_id`),
  UNIQUE KEY `config_name` (`config_name`)
) ENGINE=MyISAM AUTO_INCREMENT=115 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES (101,'mail_transport','php','mail','select'),(48,'host_locale','en','','text'),(49,'check_overallocation','false','','checkbox'),(50,'currency_symbol','$','','text'),(51,'host_style','default','','text'),(52,'company_name','xpotronix','','text'),(53,'page_title','xpotronix','','text'),(54,'site_domain','xpotronix.com','','text'),(55,'email_prefix','xpotronix.com','','text'),(56,'admin_username','admin','','text'),(57,'username_min_len','4','','text'),(58,'password_min_len','4','','text'),(60,'enable_gantt_charts','true','','checkbox'),(61,'jpLocale','','','text'),(62,'log_changes','false','','checkbox'),(63,'check_tasks_dates','true','','checkbox'),(64,'locale_warn','false','','checkbox'),(65,'locale_alert','^','','text'),(66,'daily_working_hours','8.0','','text'),(67,'display_debug','false','','checkbox'),(68,'link_tickets_kludge','false','','checkbox'),(69,'show_all_task_assignees','false','','checkbox'),(70,'direct_edit_assignment','false','','checkbox'),(71,'restrict_color_selection','false','','checkbox'),(72,'cal_day_start','8','','text'),(73,'cal_day_end','17','','text'),(74,'cal_day_increment','15','','text'),(75,'cal_working_days','1,2,3,4,5','','text'),(114,'reset_memory_limit','8M','','text'),(77,'restrict_task_time_editing','false','','checkbox'),(78,'default_view_m','config','','text'),(79,'default_view_a','index','','text'),(80,'default_view_tab','1','','text'),(81,'index_max_file_size','-1','','text'),(82,'session_handling','app','session','select'),(83,'session_idle_time','2d','session','text'),(84,'session_max_lifetime','1m','session','text'),(85,'debug','1','','text'),(87,'parser_default','/usr/bin/strings','','text'),(88,'parser_application/msword','/usr/bin/strings','','text'),(89,'parser_text/html','/usr/bin/strings','','text'),(90,'parser_application/pdf','/usr/bin/pdftotext','','text'),(91,'files_ci_preserve_attr','true','','checkbox'),(92,'files_show_versions_edit','false','','checkbox'),(113,'cal_day_view_show_minical','true','','checkbox'),(94,'auth_method','sql','auth','select'),(95,'ldap_host','localhost','ldap','text'),(96,'ldap_port','389','ldap','text'),(97,'ldap_version','3','ldap','text'),(98,'ldap_base_dn','dc=saki,dc=com,dc=au','ldap','text'),(99,'ldap_user_filter','(uid=%USERNAME%)','ldap','text'),(100,'postnuke_allow_login','true','auth','checkbox'),(102,'mail_host','localhost','mail','text'),(103,'mail_port','25','mail','text'),(104,'mail_auth','false','mail','checkbox'),(105,'mail_user','','mail','text'),(106,'mail_pass','','mail','text'),(107,'mail_defer','false','mail','checkbox'),(108,'mail_timeout','30','mail','text'),(109,'session_gc_scan_queue','false','session','checkbox'),(110,'ldap_search_user','Manager','ldap','text'),(111,'ldap_search_pass','secret','ldap','text'),(112,'ldap_allow_login','true','ldap','checkbox');
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config_list`
--

DROP TABLE IF EXISTS `config_list`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `config_list` (
  `config_list_id` int(11) NOT NULL auto_increment,
  `config_id` int(11) NOT NULL default '0',
  `config_list_name` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`config_list_id`),
  KEY `config_id` (`config_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `config_list`
--

LOCK TABLES `config_list` WRITE;
/*!40000 ALTER TABLE `config_list` DISABLE KEYS */;
INSERT INTO `config_list` VALUES (1,94,'sql'),(2,94,'ldap'),(3,94,'pn'),(4,82,'app'),(5,82,'php'),(6,101,'php'),(7,101,'smtp');
/*!40000 ALTER TABLE `config_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `contacts` (
  `contact_id` int(11) NOT NULL auto_increment,
  `contact_first_name` varchar(30) default NULL,
  `contact_last_name` varchar(30) default NULL,
  `contact_order_by` varchar(30) default NULL,
  `contact_title` varchar(50) default NULL,
  `contact_birthday` date default NULL,
  `contact_job` varchar(255) default NULL,
  `contact_company` varchar(100) default NULL,
  `contact_department` tinytext character set latin1,
  `contact_type` varchar(20) default NULL,
  `contact_email` varchar(255) default NULL,
  `contact_email2` varchar(255) default NULL,
  `contact_url` varchar(255) default NULL,
  `contact_phone` varchar(30) default NULL,
  `contact_phone2` varchar(30) default NULL,
  `contact_fax` varchar(30) default NULL,
  `contact_mobile` varchar(30) default NULL,
  `contact_address1` varchar(60) default NULL,
  `contact_address2` varchar(60) default NULL,
  `contact_city` varchar(30) default NULL,
  `contact_state` varchar(30) default NULL,
  `contact_zip` varchar(11) default NULL,
  `contact_country` varchar(30) default NULL,
  `contact_jabber` varchar(255) default NULL,
  `contact_icq` varchar(20) default NULL,
  `contact_msn` varchar(255) default NULL,
  `contact_yahoo` varchar(255) default NULL,
  `contact_aol` varchar(30) default NULL,
  `contact_notes` text character set latin1,
  `contact_project` int(11) NOT NULL default '0',
  `contact_icon` varchar(20) default NULL,
  `contact_owner` int(10) unsigned default '0',
  `contact_private` tinyint(3) unsigned default '0',
  PRIMARY KEY  (`contact_id`),
  KEY `idx_oby` (`contact_order_by`),
  KEY `idx_co` (`contact_company`),
  KEY `idx_prp` (`contact_project`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
INSERT INTO `contacts` VALUES (1,'Admin','Person','',NULL,'0000-00-00',NULL,'0','',NULL,'admin@localhost',NULL,NULL,'','',NULL,'','','','','','','',NULL,'',NULL,NULL,'',NULL,0,'obj/contact',0,0);
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_acl`
--

DROP TABLE IF EXISTS `gacl_acl`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_acl` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default 'system',
  `allow` int(11) NOT NULL default '0',
  `enabled` int(11) NOT NULL default '0',
  `return_value` longtext,
  `note` longtext,
  `updated_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `gacl_enabled_acl` (`enabled`),
  KEY `gacl_section_value_acl` (`section_value`),
  KEY `gacl_updated_date_acl` (`updated_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_acl`
--

LOCK TABLES `gacl_acl` WRITE;
/*!40000 ALTER TABLE `gacl_acl` DISABLE KEYS */;
INSERT INTO `gacl_acl` VALUES (10,'user',1,1,'','',1152198060),(11,'user',1,1,'','',1152214331),(12,'user',1,1,'','',1152198060);
/*!40000 ALTER TABLE `gacl_acl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_acl_sections`
--

DROP TABLE IF EXISTS `gacl_acl_sections`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_acl_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_value_acl_sections` (`value`),
  KEY `gacl_hidden_acl_sections` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_acl_sections`
--

LOCK TABLES `gacl_acl_sections` WRITE;
/*!40000 ALTER TABLE `gacl_acl_sections` DISABLE KEYS */;
INSERT INTO `gacl_acl_sections` VALUES (1,'system',1,'System',0),(2,'user',2,'User',0);
/*!40000 ALTER TABLE `gacl_acl_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_acl_seq`
--

DROP TABLE IF EXISTS `gacl_acl_seq`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_acl_seq` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_acl_seq`
--

LOCK TABLES `gacl_acl_seq` WRITE;
/*!40000 ALTER TABLE `gacl_acl_seq` DISABLE KEYS */;
INSERT INTO `gacl_acl_seq` VALUES (15);
/*!40000 ALTER TABLE `gacl_acl_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aco`
--

DROP TABLE IF EXISTS `gacl_aco`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_aco` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_section_value_value_aco` (`section_value`,`value`),
  KEY `gacl_hidden_aco` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

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
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_aco_map` (
  `acl_id` int(11) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_aco_map`
--

LOCK TABLES `gacl_aco_map` WRITE;
/*!40000 ALTER TABLE `gacl_aco_map` DISABLE KEYS */;
INSERT INTO `gacl_aco_map` VALUES (10,'system','login'),(11,'application','access'),(11,'application','add'),(11,'application','delete'),(11,'application','edit'),(11,'application','list'),(11,'application','view'),(12,'application','access');
/*!40000 ALTER TABLE `gacl_aco_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aco_sections`
--

DROP TABLE IF EXISTS `gacl_aco_sections`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_aco_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_value_aco_sections` (`value`),
  KEY `gacl_hidden_aco_sections` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_aco_sections`
--

LOCK TABLES `gacl_aco_sections` WRITE;
/*!40000 ALTER TABLE `gacl_aco_sections` DISABLE KEYS */;
INSERT INTO `gacl_aco_sections` VALUES (10,'system',1,'System',0),(11,'application',2,'Application',0);
/*!40000 ALTER TABLE `gacl_aco_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aco_sections_seq`
--

DROP TABLE IF EXISTS `gacl_aco_sections_seq`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_aco_sections_seq` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_aco_sections_seq`
--

LOCK TABLES `gacl_aco_sections_seq` WRITE;
/*!40000 ALTER TABLE `gacl_aco_sections_seq` DISABLE KEYS */;
INSERT INTO `gacl_aco_sections_seq` VALUES (11);
/*!40000 ALTER TABLE `gacl_aco_sections_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aco_seq`
--

DROP TABLE IF EXISTS `gacl_aco_seq`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_aco_seq` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_aco_seq`
--

LOCK TABLES `gacl_aco_seq` WRITE;
/*!40000 ALTER TABLE `gacl_aco_seq` DISABLE KEYS */;
INSERT INTO `gacl_aco_seq` VALUES (16);
/*!40000 ALTER TABLE `gacl_aco_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aro`
--

DROP TABLE IF EXISTS `gacl_aro`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_aro` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_section_value_value_aro` (`section_value`,`value`),
  KEY `gacl_hidden_aro` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_aro`
--

LOCK TABLES `gacl_aro` WRITE;
/*!40000 ALTER TABLE `gacl_aro` DISABLE KEYS */;
INSERT INTO `gacl_aro` VALUES (10,'user','1',1,'admin',0);
/*!40000 ALTER TABLE `gacl_aro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aro_groups`
--

DROP TABLE IF EXISTS `gacl_aro_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_aro_groups` (
  `id` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `value` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`id`,`value`),
  KEY `gacl_parent_id_aro_groups` (`parent_id`),
  KEY `gacl_value_aro_groups` (`value`),
  KEY `gacl_lft_rgt_aro_groups` (`lft`,`rgt`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_aro_groups`
--

LOCK TABLES `gacl_aro_groups` WRITE;
/*!40000 ALTER TABLE `gacl_aro_groups` DISABLE KEYS */;
INSERT INTO `gacl_aro_groups` VALUES (10,0,1,4,'Roles','role'),(11,10,2,3,'Administrator','admin');
/*!40000 ALTER TABLE `gacl_aro_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aro_groups_id_seq`
--

DROP TABLE IF EXISTS `gacl_aro_groups_id_seq`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_aro_groups_id_seq` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_aro_groups_id_seq`
--

LOCK TABLES `gacl_aro_groups_id_seq` WRITE;
/*!40000 ALTER TABLE `gacl_aro_groups_id_seq` DISABLE KEYS */;
INSERT INTO `gacl_aro_groups_id_seq` VALUES (14);
/*!40000 ALTER TABLE `gacl_aro_groups_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aro_groups_map`
--

DROP TABLE IF EXISTS `gacl_aro_groups_map`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_aro_groups_map` (
  `acl_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`acl_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_aro_groups_map`
--

LOCK TABLES `gacl_aro_groups_map` WRITE;
/*!40000 ALTER TABLE `gacl_aro_groups_map` DISABLE KEYS */;
INSERT INTO `gacl_aro_groups_map` VALUES (10,10),(11,11),(12,11);
/*!40000 ALTER TABLE `gacl_aro_groups_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aro_map`
--

DROP TABLE IF EXISTS `gacl_aro_map`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_aro_map` (
  `acl_id` int(11) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(230) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

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
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_aro_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_value_aro_sections` (`value`),
  KEY `gacl_hidden_aro_sections` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_aro_sections`
--

LOCK TABLES `gacl_aro_sections` WRITE;
/*!40000 ALTER TABLE `gacl_aro_sections` DISABLE KEYS */;
INSERT INTO `gacl_aro_sections` VALUES (10,'user',1,'Users',0);
/*!40000 ALTER TABLE `gacl_aro_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_aro_sections_seq`
--

DROP TABLE IF EXISTS `gacl_aro_sections_seq`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_aro_sections_seq` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_aro_sections_seq`
--

LOCK TABLES `gacl_aro_sections_seq` WRITE;
/*!40000 ALTER TABLE `gacl_aro_sections_seq` DISABLE KEYS */;
INSERT INTO `gacl_aro_sections_seq` VALUES (10);
/*!40000 ALTER TABLE `gacl_aro_sections_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_axo`
--

DROP TABLE IF EXISTS `gacl_axo`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_axo` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_section_value_value_axo` (`section_value`,`value`),
  KEY `gacl_hidden_axo` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_axo`
--

LOCK TABLES `gacl_axo` WRITE;
/*!40000 ALTER TABLE `gacl_axo` DISABLE KEYS */;
INSERT INTO `gacl_axo` VALUES (10,'sys','acl',1,'ACL Administration',0),(11,'app','admin',1,'User Administration',0),(15,'app','contacts',4,'Contacts',0),(21,'app','system',10,'System Administration',0),(26,'app','roles',14,'Roles Administration',0),(27,'app','users',15,'User Table',0);
/*!40000 ALTER TABLE `gacl_axo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_axo_groups`
--

DROP TABLE IF EXISTS `gacl_axo_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_axo_groups` (
  `id` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `value` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`id`,`value`),
  KEY `gacl_parent_id_axo_groups` (`parent_id`),
  KEY `gacl_value_axo_groups` (`value`),
  KEY `gacl_lft_rgt_axo_groups` (`lft`,`rgt`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_axo_groups`
--

LOCK TABLES `gacl_axo_groups` WRITE;
/*!40000 ALTER TABLE `gacl_axo_groups` DISABLE KEYS */;
INSERT INTO `gacl_axo_groups` VALUES (10,0,1,8,'Modules','mod'),(11,10,2,3,'All Modules','all'),(12,10,4,5,'Admin Modules','admin'),(13,10,6,7,'Non-Admin Modules','non_admin');
/*!40000 ALTER TABLE `gacl_axo_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_axo_groups_id_seq`
--

DROP TABLE IF EXISTS `gacl_axo_groups_id_seq`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_axo_groups_id_seq` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_axo_groups_id_seq`
--

LOCK TABLES `gacl_axo_groups_id_seq` WRITE;
/*!40000 ALTER TABLE `gacl_axo_groups_id_seq` DISABLE KEYS */;
INSERT INTO `gacl_axo_groups_id_seq` VALUES (13);
/*!40000 ALTER TABLE `gacl_axo_groups_id_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_axo_groups_map`
--

DROP TABLE IF EXISTS `gacl_axo_groups_map`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_axo_groups_map` (
  `acl_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`acl_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_axo_groups_map`
--

LOCK TABLES `gacl_axo_groups_map` WRITE;
/*!40000 ALTER TABLE `gacl_axo_groups_map` DISABLE KEYS */;
INSERT INTO `gacl_axo_groups_map` VALUES (11,11);
/*!40000 ALTER TABLE `gacl_axo_groups_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_axo_map`
--

DROP TABLE IF EXISTS `gacl_axo_map`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_axo_map` (
  `acl_id` int(11) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(230) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_axo_map`
--

LOCK TABLES `gacl_axo_map` WRITE;
/*!40000 ALTER TABLE `gacl_axo_map` DISABLE KEYS */;
INSERT INTO `gacl_axo_map` VALUES (12,'sys','acl');
/*!40000 ALTER TABLE `gacl_axo_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_axo_sections`
--

DROP TABLE IF EXISTS `gacl_axo_sections`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_axo_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_value_axo_sections` (`value`),
  KEY `gacl_hidden_axo_sections` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_axo_sections`
--

LOCK TABLES `gacl_axo_sections` WRITE;
/*!40000 ALTER TABLE `gacl_axo_sections` DISABLE KEYS */;
INSERT INTO `gacl_axo_sections` VALUES (10,'sys',1,'System',0),(11,'app',2,'Application',0);
/*!40000 ALTER TABLE `gacl_axo_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_axo_sections_seq`
--

DROP TABLE IF EXISTS `gacl_axo_sections_seq`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_axo_sections_seq` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_axo_sections_seq`
--

LOCK TABLES `gacl_axo_sections_seq` WRITE;
/*!40000 ALTER TABLE `gacl_axo_sections_seq` DISABLE KEYS */;
INSERT INTO `gacl_axo_sections_seq` VALUES (11);
/*!40000 ALTER TABLE `gacl_axo_sections_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_axo_seq`
--

DROP TABLE IF EXISTS `gacl_axo_seq`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_axo_seq` (
  `id` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_axo_seq`
--

LOCK TABLES `gacl_axo_seq` WRITE;
/*!40000 ALTER TABLE `gacl_axo_seq` DISABLE KEYS */;
INSERT INTO `gacl_axo_seq` VALUES (40);
/*!40000 ALTER TABLE `gacl_axo_seq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_groups_aro_map`
--

DROP TABLE IF EXISTS `gacl_groups_aro_map`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_groups_aro_map` (
  `group_id` int(11) NOT NULL default '0',
  `aro_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`aro_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_groups_aro_map`
--

LOCK TABLES `gacl_groups_aro_map` WRITE;
/*!40000 ALTER TABLE `gacl_groups_aro_map` DISABLE KEYS */;
INSERT INTO `gacl_groups_aro_map` VALUES (11,10),(15,14),(15,15),(15,16),(15,18),(15,19),(15,20),(15,26),(15,27),(15,28),(15,29),(15,33),(15,48),(16,21),(16,22),(16,23),(16,24),(16,25),(16,30),(16,31),(16,35),(16,36),(16,39),(16,40),(16,41),(16,42),(16,47),(17,12),(17,13),(17,17),(17,29),(17,32),(17,34),(17,37),(17,46),(17,49),(19,37),(19,38),(21,43),(21,44),(21,45),(21,46),(21,48);
/*!40000 ALTER TABLE `gacl_groups_aro_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_groups_axo_map`
--

DROP TABLE IF EXISTS `gacl_groups_axo_map`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_groups_axo_map` (
  `group_id` int(11) NOT NULL default '0',
  `axo_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`axo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_groups_axo_map`
--

LOCK TABLES `gacl_groups_axo_map` WRITE;
/*!40000 ALTER TABLE `gacl_groups_axo_map` DISABLE KEYS */;
INSERT INTO `gacl_groups_axo_map` VALUES (11,11),(11,12),(11,13),(11,14),(11,15),(11,16),(11,17),(11,18),(11,19),(11,20),(11,21),(11,22),(11,23),(11,24),(11,25),(11,26),(11,27),(11,28),(11,29),(11,30),(11,31),(11,32),(11,33),(11,34),(11,35),(11,36),(11,37),(11,38),(11,39),(11,40),(12,11),(12,21),(12,26),(12,27),(13,12),(13,13),(13,14),(13,15),(13,16),(13,17),(13,18),(13,19),(13,20),(13,22),(13,23),(13,24),(13,25),(13,28),(13,29),(13,30),(13,31),(13,32),(13,33),(13,34),(13,35),(13,36),(13,37),(13,38),(13,39),(13,40);
/*!40000 ALTER TABLE `gacl_groups_axo_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gacl_phpgacl`
--

DROP TABLE IF EXISTS `gacl_phpgacl`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `gacl_phpgacl` (
  `name` varchar(230) NOT NULL default '',
  `value` varchar(230) NOT NULL default '',
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `gacl_phpgacl`
--

LOCK TABLES `gacl_phpgacl` WRITE;
/*!40000 ALTER TABLE `gacl_phpgacl` DISABLE KEYS */;
INSERT INTO `gacl_phpgacl` VALUES ('version','3.3.2'),('schema_version','2.1');
/*!40000 ALTER TABLE `gacl_phpgacl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL auto_increment,
  `permission_user` int(11) NOT NULL default '0',
  `permission_grant_on` varchar(12) NOT NULL default '',
  `permission_item` int(11) NOT NULL default '0',
  `permission_value` int(11) NOT NULL default '0',
  PRIMARY KEY  (`permission_id`),
  UNIQUE KEY `idx_pgrant_on` (`permission_grant_on`,`permission_item`,`permission_user`),
  KEY `idx_puser` (`permission_user`),
  KEY `idx_pvalue` (`permission_value`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,1,'all',-1,-1);
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sessions` (
  `session_id` varchar(40) NOT NULL default '',
  `session_data` longblob,
  `session_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `session_created` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`session_id`),
  KEY `session_updated` (`session_updated`),
  KEY `session_created` (`session_created`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `syskeys`
--

DROP TABLE IF EXISTS `syskeys`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `syskeys` (
  `syskey_id` int(10) unsigned NOT NULL auto_increment,
  `syskey_name` varchar(48) NOT NULL default '',
  `syskey_label` varchar(255) NOT NULL default '',
  `syskey_type` int(1) unsigned NOT NULL default '0',
  `syskey_sep1` char(2) default '\n',
  `syskey_sep2` char(2) NOT NULL default '|',
  PRIMARY KEY  (`syskey_id`),
  UNIQUE KEY `syskey_name` (`syskey_name`),
  UNIQUE KEY `idx_syskey_name` (`syskey_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `syskeys`
--

LOCK TABLES `syskeys` WRITE;
/*!40000 ALTER TABLE `syskeys` DISABLE KEYS */;
INSERT INTO `syskeys` VALUES (1,'SelectList','Enter values for list',0,'\n','|'),(2,'CustomField','Serialized array in the following format:\r\n<KEY>|<SERIALIZED ARRAY>\r\n\r\nSerialized Array:\r\n[type] => text | checkbox | select | textarea | label\r\n[name] => <Field\'s name>\r\n[options] => <html capture options>\r\n[selects] => <options for select and checkbox>',0,'\n','|'),(3,'ColorSelection','Hex color values for type=>color association.',0,'\n','|');
/*!40000 ALTER TABLE `syskeys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sysvals`
--

DROP TABLE IF EXISTS `sysvals`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sysvals` (
  `sysval_id` int(10) unsigned NOT NULL auto_increment,
  `sysval_key_id` int(10) unsigned NOT NULL default '0',
  `sysval_title` varchar(48) NOT NULL default '',
  `sysval_value` text NOT NULL,
  PRIMARY KEY  (`sysval_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `sysvals`
--

LOCK TABLES `sysvals` WRITE;
/*!40000 ALTER TABLE `sysvals` DISABLE KEYS */;
INSERT INTO `sysvals` VALUES (1,1,'ProjectStatus','0|Not Defined\r\n1|Proposed\r\n2|In Planning\r\n3|In Progress\r\n4|On Hold\r\n5|Complete\r\n6|Template'),(2,1,'CompanyType','0|Not Applicable\n1|Client\n2|Vendor\n3|Supplier\n4|Consultant\n5|Government\n6|Internal'),(3,1,'TaskDurationType','1|hours\n24|days'),(4,1,'EventType','0|General\n1|Appointment\n2|Meeting\n3|All Day Event\n4|Anniversary\n5|Reminder'),(5,1,'TaskStatus','0|Active\n-1|Inactive'),(6,1,'TaskType','0|Unknown\n1|Administrative\n2|Operative'),(7,1,'ProjectType','0|Unknown\n1|Administrative\n2|Operative'),(8,3,'ProjectColors','Web|FFE0AE\nEngineering|AEFFB2\nHelpDesk|FFFCAE\nSystem Administration|FFAEAE'),(9,1,'FileType','0|Unknown\n1|Document\n2|Application'),(10,1,'TaskPriority','-1|low\n0|normal\n1|high'),(11,1,'ProjectPriority','-1|low\n0|normal\n1|high'),(12,1,'ProjectPriorityColor','-1|#E5F7FF\n0|\n1|#FFDCB3'),(13,1,'TaskLogReference','0|Not Defined\n1|Email\n2|Helpdesk\n3|Phone Call\n4|Fax'),(14,1,'TaskLogReferenceImage','0| 1|./images/obj/email.gif 2|./modules/helpdesk/images/helpdesk.png 3|./images/obj/phone.gif 4|./images/icons/stock_print-16.png');
/*!40000 ALTER TABLE `sysvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_access_log`
--

DROP TABLE IF EXISTS `user_access_log`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user_access_log` (
  `user_access_log_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `user_ip` varchar(15) NOT NULL default '',
  `date_time_in` datetime default '0000-00-00 00:00:00',
  `date_time_out` datetime default '0000-00-00 00:00:00',
  `date_time_last_action` datetime default '0000-00-00 00:00:00',
  PRIMARY KEY  (`user_access_log_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_events`
--

DROP TABLE IF EXISTS `user_events`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user_events` (
  `user_id` int(11) NOT NULL default '0',
  `event_id` int(11) NOT NULL default '0',
  KEY `uek1` (`user_id`,`event_id`),
  KEY `uek2` (`event_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `user_events`
--

LOCK TABLES `user_events` WRITE;
/*!40000 ALTER TABLE `user_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user_preferences` (
  `pref_user` varchar(12) NOT NULL default '',
  `pref_name` varchar(72) NOT NULL default '',
  `pref_value` varchar(32) NOT NULL default '',
  KEY `pref_user` (`pref_user`,`pref_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `user_preferences`
--

LOCK TABLES `user_preferences` WRITE;
/*!40000 ALTER TABLE `user_preferences` DISABLE KEYS */;
INSERT INTO `user_preferences` VALUES ('0','LOCALE','es_AR'),('0','TABVIEW','0'),('0','SHDATEFORMAT','%d/%m/%Y'),('0','TIMEFORMAT','%I:%M %p'),('0','UISTYLE','default'),('0','TASKASSIGNMAX','100'),('0','CURRENCYFORM','en_AU'),('0','EVENTFILTER','my'),('0','MAILALL','0'),('0','TASKLOGEMAIL','0'),('0','TASKLOGSUBJ',''),('0','TASKLOGNOTE','0'),('2','LOCALE','es_AR'),('2','TABVIEW','0'),('2','SHDATEFORMAT','%d/%m/%Y'),('2','TIMEFORMAT','%I:%M %p'),('2','CURRENCYFORM','en_AU'),('2','UISTYLE','default'),('2','TASKASSIGNMAX','100'),('2','EVENTFILTER','my'),('2','MAILALL','0'),('2','TASKLOGEMAIL','0'),('2','TASKLOGSUBJ',''),('2','TASKLOGNOTE','0'),('1','LOCALE','es_AR'),('1','TABVIEW','0'),('1','SHDATEFORMAT','%d/%m/%Y'),('1','TIMEFORMAT','%I:%M %p'),('1','CURRENCYFORM','en_AU'),('1','UISTYLE','Default clean style'),('1','TASKASSIGNMAX','100'),('1','EVENTFILTER','my'),('1','MAILALL','0'),('1','TASKLOGEMAIL','0'),('1','TASKLOGSUBJ',''),('1','TASKLOGNOTE','0');
/*!40000 ALTER TABLE `user_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `user_contact` int(11) NOT NULL default '0',
  `user_username` varchar(255) NOT NULL default '',
  `user_password` varchar(32) NOT NULL default '',
  `user_parent` int(11) NOT NULL default '0',
  `user_type` tinyint(3) NOT NULL default '0',
  `user_company` int(11) default '0',
  `user_department` int(11) default '0',
  `user_owner` int(11) NOT NULL default '0',
  `user_signature` text,
  `t_actor_ID` int(11) default NULL,
  `actor_ID` int(11) default NULL,
  PRIMARY KEY  (`user_id`),
  KEY `idx_uid` (`user_username`),
  KEY `idx_pwd` (`user_password`),
  KEY `idx_user_parent` (`user_parent`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'admin','b85ecb25b11f0af4ce55c80b23a61703',0,1,0,0,0,'',NULL,NULL);
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

-- Dump completed on 2009-04-22 12:24:45
