-- MySQL dump 10.13  Distrib 8.0.25, for Linux (x86_64)
--
-- Host: localhost    Database: xpay
-- ------------------------------------------------------
-- Server version	8.0.25-0ubuntu0.20.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `notificacion`
--

DROP TABLE IF EXISTS `notificacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificacion` (
  `ID` char(32) NOT NULL DEFAULT '',
  `legajo` char(12) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  `titulo` varchar(200) DEFAULT NULL,
  `contenido` text,
  `enviar_email` tinyint(1) NOT NULL DEFAULT '1',
  `enviada` datetime DEFAULT NULL,
  `vista` datetime DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL,
  `key` varchar(50) DEFAULT NULL,
  `seccion` varchar(20) DEFAULT NULL,
  `remitente` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `email_test` varchar(100) DEFAULT NULL,
  `xml_data` text,
  `estatus` varchar(255) DEFAULT NULL,
  `reintento` int NOT NULL DEFAULT '0',
  `flow_ID` char(32) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `legajo` (`legajo`),
  KEY `fecha_hora` (`fecha_hora`),
  KEY `enviada` (`enviada`),
  KEY `vista` (`vista`),
  KEY `module` (`module`),
  KEY `key` (`key`),
  KEY `email` (`email`),
  KEY `flow_ID` (`flow_ID`),
  KEY `seccion` (`seccion`),
  KEY `email_test` (`email_test`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificacion`
--

LOCK TABLES `notificacion` WRITE;
/*!40000 ALTER TABLE `notificacion` DISABLE KEYS */;
INSERT INTO `notificacion` VALUES ('4HNY6jxdeC0tDtfTpWMsABsZeONQjlLO','        1082','2021-01-12 09:56:25','miPortal/Licencias: #1082 SPOTORNO, EDUARDO ORLANDO,  Examen','<!DOCTYPE html PUBLIC \\\"-//W3C//DTD XHTML 1.0 Transitional//EN\\\" \\\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\\\"><html xmlns=\\\"http://www.w3.org/1999/xhtml\\\"><head><meta name=\\\"viewport\\\" content=\\\"width=device-width\\\"></meta><meta http-equiv=\\\"Content-Type\\\" content=\\\"text/html; charset=UTF-8\\\"></meta><title></title><style>\n*{margin:0;padding:0}*{font-family:\\\"Helvetica Neue\\\",\\\"Helvetica\\\",Helvetica,Arial,sans-serif}img{max-width:100%}.collapse{margin:0;padding:0}body{-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:none;width:100% !important;height:100%}a{color:#2ba6cb} .btn{display: inline-block;padding: 6px 12px;margin-bottom: 0;font-size: 14px;font-weight: normal;line-height: 1.428571429;text-align: center;white-space: nowrap;vertical-align: middle;cursor: pointer;border: 1px solid transparent;border-radius: 4px;-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;-o-user-select: none;user-select: none;color: #333;background-color: white;border-color: #CCC;} p.callout{padding:15px;background-color:#ecf8ff;margin-bottom:15px}.callout a{font-weight:bold;color:#2ba6cb}table.social{background-color:#ebebeb}.social .soc-btn{padding:3px 7px;border-radius:2px; -webkit-border-radius:2px; -moz-border-radius:2px; font-size:12px;margin-bottom:10px;text-decoration:none;color:#FFF;font-weight:bold;display:block;text-align:center}a.fb{background-color:#3b5998 !important}a.tw{background-color:#1daced !important}a.gp{background-color:#db4a39 !important}a.ms{background-color:#000 !important}.sidebar .soc-btn{display:block;width:100%}table.head-wrap{width:100%}.header.container table td.logo{padding:15px}.header.container table td.label{padding:15px;padding-left:0}table.body-wrap{width:100%}table.footer-wrap{width:100%;clear:both !important}.footer-wrap .container td.content p{border-top:1px solid #d7d7d7;padding-top:15px}.footer-wrap .container td.content p{font-size:10px;font-weight:bold}h1,h2,h3,h4,h5,h6{font-family:\\\"HelveticaNeue-Light\\\",\\\"Helvetica Neue Light\\\",\\\"Helvetica Neue\\\",Helvetica,Arial,\\\"Lucida Grande\\\",sans-serif;line-height:1.1;margin-bottom:15px;color:#000}h1 small,h2 small,h3 small,h4 small,h5 small,h6 small{font-size:60%;color:#6f6f6f;line-height:0;text-transform:none}h1{font-weight:200;font-size:44px}h2{font-weight:200;font-size:37px}h3{font-weight:500;font-size:27px}h4{font-weight:500;font-size:23px}h5{font-weight:900;font-size:17px}h6{font-weight:900;font-size:14px;text-transform:uppercase;color:#444}.collapse{margin:0 !important}p,ul{margin-bottom:10px;font-weight:normal;font-size:14px;line-height:1.6}p.lead{font-size:17px}p.last{margin-bottom:0}ul li{margin-left:5px;list-style-position:inside}ul.sidebar{background:#ebebeb;display:block;list-style-type:none}ul.sidebar li{display:block;margin:0}ul.sidebar li a{text-decoration:none;color:#666;padding:10px 16px;margin-right:10px;cursor:pointer;border-bottom:1px solid #777;border-top:1px solid #fff;display:block;margin:0}ul.sidebar li a.last{border-bottom-width:0}ul.sidebar li a h1,ul.sidebar li a h2,ul.sidebar li a h3,ul.sidebar li a h4,ul.sidebar li a h5,ul.sidebar li a h6,ul.sidebar li a p{margin-bottom:0 !important}.container{display:block !important;max-width:600px !important;margin:0 auto !important;clear:both !important}.content{padding:15px;max-width:600px;margin:0 auto;display:block}.content table{width:100%}.column{width:300px;float:left}.column tr td{padding:15px}.column-wrap{padding:0 !important;margin:0 auto;max-width:600px !important}.column table{width:100%}.social .column{width:280px;min-width:279px;float:left}.clear{display:block;clear:both}@media only screen and (max-width:600px){a[class=\\\"btn\\\"]{display:block !important;margin-bottom:10px !important;background-image:none !important;margin-right:0 !important}div[class=\\\"column\\\"]{width:auto !important;float:none !important}table.social div[class=\\\"column\\\"]{width:auto !important}}\n</style></head><body bgcolor=\\\"#FFFFFF\\\"><table class=\\\"head-wrap\\\" background=\\\"border.png\\\"><tr><td></td><td class=\\\"header container\\\"><div class=\\\"content\\\"><img src=\\\"cid:header-email\\\"></img></div></td><td></td></tr></table><table class=\\\"body-wrap\\\"><tr><td class=\\\"container\\\" bgcolor=\\\"#FFFFFF\\\"><div class=\\\"content\\\"><table><tr><td><p class=\\\"lead\\\">Ud. ha recibido el siguiente email en respuesta a la solicitud de una licencia de tipo <strong> Examen</strong> que se inicia en la fecha <strong>19/01/2021</strong> hasta la fecha <strong>19/01/2021</strong> contabilizándose un total de <strong>1 día(s) hábiles</strong>. Informamos que se ha procesado su solicitud y que la misma se encuentra en el estado de <strong>Pendiente para la revisión del Jefe Inmediato</strong>. Para ver el estado de sus licencias, <a href=\\\"http://miportal.jusbaires.gob.ar/#?m=_licencia&amp;v=miportal/licencia/historial&amp;s[_licencia][legajo]=%20%20%20%20%20%20%20%201082\\\">por favor haga click aquí</a></p><p class=\\\"callout\\\"><a href=\\\"http://miportal.jusbaires.gob.ar\\\"> \n										No deje de visitar Mi Portal para ver la información personal en su legajo y solicitar licencias »\n									</a></p><table class=\\\"social\\\" width=\\\"100%\\\"><tr><td><table align=\\\"left\\\" class=\\\"column\\\"><tr><td><h5 class=\\\"\\\">Vías de Contacto:</h5><br></br>Email: <strong><a href=\\\"emailto:licencias@jusbaires.gob.ar\\\">licencias@jusbaires.gob.ar</a></strong></td></tr></table><span class=\\\"clear\\\"></span></td></tr></table></td></tr></table></div></td><td></td></tr></table><table class=\\\"footer-wrap\\\"><tr><td></td><td class=\\\"container\\\"><div class=\\\"content\\\"><table><tr><td align=\\\"center\\\"><p><a href=\\\"#\\\">Términos y Condiciones de Uso</a> |\n								<a href=\\\"#\\\">Política de Privacidad</a></p></td></tr></table></div></td><td></td></tr></table></body></html>',1,NULL,NULL,'_licencia','ohgfQX9UdAP9gGdXh0suolkq1tpY571z','empleado','licencias','espotorno',NULL,'<?xml version=\\\"1.0\\\"?>\n<_licencia ID=\\\"ohgfQX9UdAP9gGdXh0suolkq1tpY571z\\\" edit=\\\"1\\\" delete=\\\"\\\" new=\\\"\\\"><actual>false</actual><ID>ohgfQX9UdAP9gGdXh0suolkq1tpY571z</ID><t_licencia_ID label=\\\" Examen\\\">3</t_licencia_ID><legajo label=\\\"#1082 SPOTORNO, EDUARDO ORLANDO               \\\">        1082</legajo><dias_habiles>true</dias_habiles><fec_desde value=\\\"2021-01-19\\\">19/01/2021</fec_desde><fec_hasta value=\\\"2021-01-19\\\">19/01/2021</fec_hasta><dias>1</dias><usuario>espotorno</usuario><obs_empleado/><timestamp value=\\\"2021-01-12 09:56:24\\\">12/01/2021 09:56:24</timestamp><autoriza_ID label=\\\"#4094 DE GIORGIO, MATIAS                      \\\">        4094</autoriza_ID><autoriza_sup_ID label=\\\"#4094 DE GIORGIO, MATIAS                      \\\">        4094</autoriza_sup_ID><estado>pend_inmediato</estado><certs_presentados>0</certs_presentados><organismo label=\\\"CONSEJO DE LA MAGISTRATURA DE LA CABA\\\">  1</organismo><categoria label=\\\"Funcionario\\\">1</categoria><clasifica label=\\\"Excluidos Ley Ganancias (Ant 2017)\\\">2</clasifica><unidad label=\\\"#260 Dir. Gral. de Factor Humano\\\">260</unidad><area label=\\\"#260000 Dir. Gral. de Factor Humano\\\">260000</area><cargo label=\\\"#138 Secretario de 1&#xBA; Instancia\\\">138</cargo><planta label=\\\"Planta Permanente\\\">108</planta><accion>?</accion><datadiv>\n&lt;obj xmlns=\\\"http://www.w3.org/1999/xhtml\\\" name=\\\"_licencia\\\"&gt;&lt;attr name=\\\"accion\\\"&gt;&lt;span id=\\\"raction_ohgfQX9UdAP9gGdXh0suolkq1tpY571z\\\"&gt;&lt;a class=\\\"btn btn-danger btn-sm\\\" href=\\\"#\\\" onclick=\\\"licencia_cambia_estado(\\\'ohgfQX9UdAP9gGdXh0suolkq1tpY571z\\\',\\\'anulada\\\')\\\" title=\\\"Anular Licencia\\\"&gt;&lt;span class=\\\"oi oi-trash\\\" aria-hidden=\\\"true\\\"/&gt;&lt;/a&gt;&lt;a class=\\\"btn btn-warning btn-sm\\\" href=\\\"#\\\" onclick=\\\"imprime_formulario(\\\'ohgfQX9UdAP9gGdXh0suolkq1tpY571z\\\')\\\" title=\\\"Imprimir Licencia\\\"&gt;&lt;span class=\\\"oi oi-print\\\" aria-hidden=\\\"true\\\"/&gt;&lt;/a&gt;&lt;a class=\\\"btn btn-success btn-sm\\\" href=\\\"?m=v_solicitud_licencia&amp;amp;v=miportal/licencia/solicitud&amp;amp;tab=s3&amp;amp;f[include_dataset]=6&amp;amp;s[_licencia][ID]=ohgfQX9UdAP9gGdXh0suolkq1tpY571z&amp;amp;s[v_solicitud_licencia][legajo]=        1082\\\" title=\\\"Ver/Adjuntar Certificados Digitales\\\"&gt;&lt;span class=\\\"oi oi-paperclip\\\" aria-hidden=\\\"true\\\"/&gt;&lt;/a&gt;&lt;/span&gt;&lt;/attr&gt;&lt;attr name=\\\"estado\\\"&gt;pend_inmediato&lt;/attr&gt;&lt;attr name=\\\"legajo_label\\\"&gt;#1082 SPOTORNO, EDUARDO ORLANDO               &lt;/attr&gt;&lt;attr name=\\\"autoriza_ID_label\\\"&gt;&lt;div/&gt;&lt;/attr&gt;&lt;attr name=\\\"t_licencia_ID_label\\\"&gt;&lt;div id=\\\"licencia_ID_ohgfQX9UdAP9gGdXh0suolkq1tpY571z\\\"&gt;&lt;a href=\\\"#?m=v_solicitud_licencia&amp;amp;v=miportal/licencia/solicitud&amp;amp;f[include_dataset]=6&amp;amp;s[v_solicitud_licencia][legajo]=        1082&amp;amp;s[_licencia][ID]=ohgfQX9UdAP9gGdXh0suolkq1tpY571z\\\" target=\\\"_blank\\\"&gt;&lt;strong&gt; Examen&lt;/strong&gt;&lt;br/&gt;\n\n				Solicitada el 12/01/2021 a las  09:56&lt;br/&gt;&lt;/a&gt;&lt;/div&gt;&lt;/attr&gt;&lt;/obj&gt;\n</datadiv><canal>miportal</canal><vigente>1</vigente><titulo>miPortal/Licencias: #1082 SPOTORNO, EDUARDO ORLANDO,  Examen</titulo><link>http://miportal.jusbaires.gob.ar</link></_licencia>\n',NULL,0,'sBFld1cttpV048Z8ONbLVIqpa1Ej3p2C'),('Q9D0onTSUP9kRN7CssdXU626ew5L3z31','        4094','2021-01-12 09:56:25','miPortal/Licencias: #1082 SPOTORNO, EDUARDO ORLANDO,  Examen','<!DOCTYPE html PUBLIC \\\"-//W3C//DTD XHTML 1.0 Transitional//EN\\\" \\\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\\\"><html xmlns=\\\"http://www.w3.org/1999/xhtml\\\"><head><meta name=\\\"viewport\\\" content=\\\"width=device-width\\\"></meta><meta http-equiv=\\\"Content-Type\\\" content=\\\"text/html; charset=UTF-8\\\"></meta><title></title><style>\n*{margin:0;padding:0}*{font-family:\\\"Helvetica Neue\\\",\\\"Helvetica\\\",Helvetica,Arial,sans-serif}img{max-width:100%}.collapse{margin:0;padding:0}body{-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:none;width:100% !important;height:100%}a{color:#2ba6cb} .btn{display: inline-block;padding: 6px 12px;margin-bottom: 0;font-size: 14px;font-weight: normal;line-height: 1.428571429;text-align: center;white-space: nowrap;vertical-align: middle;cursor: pointer;border: 1px solid transparent;border-radius: 4px;-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;-o-user-select: none;user-select: none;color: #333;background-color: white;border-color: #CCC;} p.callout{padding:15px;background-color:#ecf8ff;margin-bottom:15px}.callout a{font-weight:bold;color:#2ba6cb}table.social{background-color:#ebebeb}.social .soc-btn{padding:3px 7px;border-radius:2px; -webkit-border-radius:2px; -moz-border-radius:2px; font-size:12px;margin-bottom:10px;text-decoration:none;color:#FFF;font-weight:bold;display:block;text-align:center}a.fb{background-color:#3b5998 !important}a.tw{background-color:#1daced !important}a.gp{background-color:#db4a39 !important}a.ms{background-color:#000 !important}.sidebar .soc-btn{display:block;width:100%}table.head-wrap{width:100%}.header.container table td.logo{padding:15px}.header.container table td.label{padding:15px;padding-left:0}table.body-wrap{width:100%}table.footer-wrap{width:100%;clear:both !important}.footer-wrap .container td.content p{border-top:1px solid #d7d7d7;padding-top:15px}.footer-wrap .container td.content p{font-size:10px;font-weight:bold}h1,h2,h3,h4,h5,h6{font-family:\\\"HelveticaNeue-Light\\\",\\\"Helvetica Neue Light\\\",\\\"Helvetica Neue\\\",Helvetica,Arial,\\\"Lucida Grande\\\",sans-serif;line-height:1.1;margin-bottom:15px;color:#000}h1 small,h2 small,h3 small,h4 small,h5 small,h6 small{font-size:60%;color:#6f6f6f;line-height:0;text-transform:none}h1{font-weight:200;font-size:44px}h2{font-weight:200;font-size:37px}h3{font-weight:500;font-size:27px}h4{font-weight:500;font-size:23px}h5{font-weight:900;font-size:17px}h6{font-weight:900;font-size:14px;text-transform:uppercase;color:#444}.collapse{margin:0 !important}p,ul{margin-bottom:10px;font-weight:normal;font-size:14px;line-height:1.6}p.lead{font-size:17px}p.last{margin-bottom:0}ul li{margin-left:5px;list-style-position:inside}ul.sidebar{background:#ebebeb;display:block;list-style-type:none}ul.sidebar li{display:block;margin:0}ul.sidebar li a{text-decoration:none;color:#666;padding:10px 16px;margin-right:10px;cursor:pointer;border-bottom:1px solid #777;border-top:1px solid #fff;display:block;margin:0}ul.sidebar li a.last{border-bottom-width:0}ul.sidebar li a h1,ul.sidebar li a h2,ul.sidebar li a h3,ul.sidebar li a h4,ul.sidebar li a h5,ul.sidebar li a h6,ul.sidebar li a p{margin-bottom:0 !important}.container{display:block !important;max-width:600px !important;margin:0 auto !important;clear:both !important}.content{padding:15px;max-width:600px;margin:0 auto;display:block}.content table{width:100%}.column{width:300px;float:left}.column tr td{padding:15px}.column-wrap{padding:0 !important;margin:0 auto;max-width:600px !important}.column table{width:100%}.social .column{width:280px;min-width:279px;float:left}.clear{display:block;clear:both}@media only screen and (max-width:600px){a[class=\\\"btn\\\"]{display:block !important;margin-bottom:10px !important;background-image:none !important;margin-right:0 !important}div[class=\\\"column\\\"]{width:auto !important;float:none !important}table.social div[class=\\\"column\\\"]{width:auto !important}}\n</style></head><body bgcolor=\\\"#FFFFFF\\\"><table class=\\\"head-wrap\\\" background=\\\"border.png\\\"><tr><td></td><td class=\\\"header container\\\"><div class=\\\"content\\\"><img src=\\\"cid:header-email\\\"></img></div></td><td></td></tr></table><table class=\\\"body-wrap\\\"><tr><td class=\\\"container\\\" bgcolor=\\\"#FFFFFF\\\"><div class=\\\"content\\\"><table><tr><td><p class=\\\"lead\\\">Ud. ha recibido el siguiente email en respuesta a la solicitud de una licencia de tipo <strong> Examen</strong> que se inicia en la fecha <strong>19/01/2021</strong> hasta la fecha <strong>19/01/2021</strong> contabilizándose un total de <strong>1 día(s) hábiles</strong>. Informamos que se ha procesado su solicitud y que la misma se encuentra en el estado de <strong>Pendiente para la revisión del Jefe Inmediato</strong>. Para ver el estado de sus licencias, <a href=\\\"http://miportal.jusbaires.gob.ar/#?m=_licencia&amp;v=miportal/licencia/historial&amp;s[_licencia][legajo]=%20%20%20%20%20%20%20%201082\\\">por favor haga click aquí</a></p><p class=\\\"callout\\\"><a href=\\\"http://miportal.jusbaires.gob.ar\\\"> \n										No deje de visitar Mi Portal para ver la información personal en su legajo y solicitar licencias »\n									</a></p><table class=\\\"social\\\" width=\\\"100%\\\"><tr><td><table align=\\\"left\\\" class=\\\"column\\\"><tr><td><h5 class=\\\"\\\">Vías de Contacto:</h5><br></br>Email: <strong><a href=\\\"emailto:licencias@jusbaires.gob.ar\\\">licencias@jusbaires.gob.ar</a></strong></td></tr></table><span class=\\\"clear\\\"></span></td></tr></table></td></tr></table></div></td><td></td></tr></table><table class=\\\"footer-wrap\\\"><tr><td></td><td class=\\\"container\\\"><div class=\\\"content\\\"><table><tr><td align=\\\"center\\\"><p><a href=\\\"#\\\">Términos y Condiciones de Uso</a> |\n								<a href=\\\"#\\\">Política de Privacidad</a></p></td></tr></table></div></td><td></td></tr></table></body></html>',0,NULL,NULL,'_licencia','ohgfQX9UdAP9gGdXh0suolkq1tpY571z','supervisor','licencias','mdegiorgio',NULL,'<?xml version=\\\"1.0\\\"?>\n<_licencia ID=\\\"ohgfQX9UdAP9gGdXh0suolkq1tpY571z\\\" edit=\\\"1\\\" delete=\\\"\\\" new=\\\"\\\"><actual>false</actual><ID>ohgfQX9UdAP9gGdXh0suolkq1tpY571z</ID><t_licencia_ID label=\\\" Examen\\\">3</t_licencia_ID><legajo label=\\\"#1082 SPOTORNO, EDUARDO ORLANDO               \\\">        1082</legajo><dias_habiles>true</dias_habiles><fec_desde value=\\\"2021-01-19\\\">19/01/2021</fec_desde><fec_hasta value=\\\"2021-01-19\\\">19/01/2021</fec_hasta><dias>1</dias><usuario>espotorno</usuario><obs_empleado/><timestamp value=\\\"2021-01-12 09:56:24\\\">12/01/2021 09:56:24</timestamp><autoriza_ID label=\\\"#4094 DE GIORGIO, MATIAS                      \\\">        4094</autoriza_ID><autoriza_sup_ID label=\\\"#4094 DE GIORGIO, MATIAS                      \\\">        4094</autoriza_sup_ID><estado>pend_inmediato</estado><certs_presentados>0</certs_presentados><organismo label=\\\"CONSEJO DE LA MAGISTRATURA DE LA CABA\\\">  1</organismo><categoria label=\\\"Funcionario\\\">1</categoria><clasifica label=\\\"Excluidos Ley Ganancias (Ant 2017)\\\">2</clasifica><unidad label=\\\"#260 Dir. Gral. de Factor Humano\\\">260</unidad><area label=\\\"#260000 Dir. Gral. de Factor Humano\\\">260000</area><cargo label=\\\"#138 Secretario de 1&#xBA; Instancia\\\">138</cargo><planta label=\\\"Planta Permanente\\\">108</planta><accion>?</accion><datadiv>\n&lt;obj xmlns=\\\"http://www.w3.org/1999/xhtml\\\" name=\\\"_licencia\\\"&gt;&lt;attr name=\\\"accion\\\"&gt;&lt;span id=\\\"raction_ohgfQX9UdAP9gGdXh0suolkq1tpY571z\\\"&gt;&lt;a class=\\\"btn btn-danger btn-sm\\\" href=\\\"#\\\" onclick=\\\"licencia_cambia_estado(\\\'ohgfQX9UdAP9gGdXh0suolkq1tpY571z\\\',\\\'anulada\\\')\\\" title=\\\"Anular Licencia\\\"&gt;&lt;span class=\\\"oi oi-trash\\\" aria-hidden=\\\"true\\\"/&gt;&lt;/a&gt;&lt;a class=\\\"btn btn-warning btn-sm\\\" href=\\\"#\\\" onclick=\\\"imprime_formulario(\\\'ohgfQX9UdAP9gGdXh0suolkq1tpY571z\\\')\\\" title=\\\"Imprimir Licencia\\\"&gt;&lt;span class=\\\"oi oi-print\\\" aria-hidden=\\\"true\\\"/&gt;&lt;/a&gt;&lt;a class=\\\"btn btn-success btn-sm\\\" href=\\\"?m=v_solicitud_licencia&amp;amp;v=miportal/licencia/solicitud&amp;amp;tab=s3&amp;amp;f[include_dataset]=6&amp;amp;s[_licencia][ID]=ohgfQX9UdAP9gGdXh0suolkq1tpY571z&amp;amp;s[v_solicitud_licencia][legajo]=        1082\\\" title=\\\"Ver/Adjuntar Certificados Digitales\\\"&gt;&lt;span class=\\\"oi oi-paperclip\\\" aria-hidden=\\\"true\\\"/&gt;&lt;/a&gt;&lt;/span&gt;&lt;/attr&gt;&lt;attr name=\\\"estado\\\"&gt;pend_inmediato&lt;/attr&gt;&lt;attr name=\\\"legajo_label\\\"&gt;#1082 SPOTORNO, EDUARDO ORLANDO               &lt;/attr&gt;&lt;attr name=\\\"autoriza_ID_label\\\"&gt;&lt;div/&gt;&lt;/attr&gt;&lt;attr name=\\\"t_licencia_ID_label\\\"&gt;&lt;div id=\\\"licencia_ID_ohgfQX9UdAP9gGdXh0suolkq1tpY571z\\\"&gt;&lt;a href=\\\"#?m=v_solicitud_licencia&amp;amp;v=miportal/licencia/solicitud&amp;amp;f[include_dataset]=6&amp;amp;s[v_solicitud_licencia][legajo]=        1082&amp;amp;s[_licencia][ID]=ohgfQX9UdAP9gGdXh0suolkq1tpY571z\\\" target=\\\"_blank\\\"&gt;&lt;strong&gt; Examen&lt;/strong&gt;&lt;br/&gt;\n\n				Solicitada el 12/01/2021 a las  09:56&lt;br/&gt;&lt;/a&gt;&lt;/div&gt;&lt;/attr&gt;&lt;/obj&gt;\n</datadiv><canal>miportal</canal><vigente>1</vigente><titulo>miPortal/Licencias: #1082 SPOTORNO, EDUARDO ORLANDO,  Examen</titulo><link>http://miportal.jusbaires.gob.ar</link></_licencia>\n',NULL,0,'sBFld1cttpV048Z8ONbLVIqpa1Ej3p2C');
/*!40000 ALTER TABLE `notificacion` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-05-24 20:24:20
