-- MySQL dump 10.13  Distrib 5.1.49, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: patrocinio
-- ------------------------------------------------------
-- Server version	5.1.49-1ubuntu8.1

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `home`
--

LOCK TABLES `home` WRITE;
/*!40000 ALTER TABLE `home` DISABLE KEYS */;
INSERT INTO `home` VALUES ('fa1348f78ddc23bde6381b3145cbd4e1','banner','<div style=\"height: 70 px;background-color:#f0efed;\"><img src=\"http://www.derecho.uba.ar/imagenes/logofacu_blanco1.jpg\"/></div><div>Sistema de Patrocinio -- Facultad de Derecho / UBA</div>');
/*!40000 ALTER TABLE `home` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `help`
--

LOCK TABLES `help` WRITE;
/*!40000 ALTER TABLE `help` DISABLE KEYS */;
INSERT INTO `help` VALUES ('2ef37d8e8a3e5e5534a8e42bc9c8ab71','detail-mi-agenda','<h3>Mi Agenda</h3>\n                <p>Utilice esta vista en formato calendario para organizar sus actividades.\n                El espacio disponible quedará para la asignación de nuevas audiencias.<br/>\n                <br/>Utilice las vistas de:</p>\n                <ul>\n                        <li>Vista Dia</li>\n                        <li>Vista Semanal</li>\n                        <li>Vista Mensual</li>\n                </ul>\n                <p>y asi disponer de la mejor vista para la organización de las actividades.</p>\n'),('364bb12d718a63b56e137ac6e5803113','detail-atencion','Módulo de Atención a los requirentes'),('4b4604577ad545e307fc0b80d1833129','detail-consultas','Módulos de Consultas'),('65145e5b5edfbec241d042074bc05d66','detail-procesos','<h3>Ingreso de un nuevo proceso</h3>\n                <p>Utilice esta opción para cargar toda la información relativa a un nuevo proceso, como ser:</p>\n                <br/>\n                <ul>\n                        <li>Información descriptiva del proceso</li>\n                        <li>Actores Judiciales</li>\n                        <li>Involucrados</li>\n                        <li>Infracciones por Imputado</li>\n                </ul>\n                <br/>\n                <p>entre otras opciones.</p>\n'),('6bb71d0b196c9683cb651f50ab21081f','consulta-proceso','<h3>Consulta de Procesos</h3>\n                <p>Utilice esta opción para buscar procesos a través de distintos criterios:</p>\n                <br/>\n                <ul>\n                        <li>Carátulas</li>\n                        <li>Involucrados</li>\n                        <li>Infracciones</li>\n                </ul>\n                <br/>\n                <p>entre otras alternativas.</p>\n'),('7d133717daae40b645ff4cec365a7dab','audiencia','<h3>Pendientes de Coordinación de Agenda</h3>\n                <p>\n                En esta opción le permitirá identificar que audiencias están pendientes para la\n                coordinación de un horario específico.<br/>\n                Acceda a esta opción para realizar los pasos necesarios para la coordinación de una\n                nueva audiencia.\n                </p>\n'),('f44ff8928f66555e909c7a12e132958b','traslados','<h3>Pendientes de Traslado de Imputados</h3>\n                <p>Utilice esta opción para realizar los avisos de traslado de imputados por fecha y localización del Centro de Internación</p>\n');
/*!40000 ALTER TABLE `help` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tip`
--

LOCK TABLES `tip` WRITE;
/*!40000 ALTER TABLE `tip` DISABLE KEYS */;
INSERT INTO `tip` VALUES ('08ee8c3e069eddf640a66628f7c1355a','tip-5','<h2>Seleccionar Varios</h2>Mientras presione CTRL, haga click en cada uno de los registros. Puede hacer operaciones sobre ellos como \"Borrar\" o las opciones que ofrece \"Sobre Selección\" (si están permitidas para ese módulo). Para volver, haga click sin ctrl en cualquier registro.'),('09fc4d91fec65bed94416b75406d2e06','tip-6','<h3>Tip del dia: Barra de Herramientas</h3>\nPase el puntero del ratón por sobre los íconos de la barra de herramientas para obtener una breve descripción de la función que estos proveen.'),('106e100c2fe1ff2713d24bbf5aecc5b4','tip-1','<h1>Paginador</h1>Los datos de las grillas se presentan en páginas de 50 elementos cada una para acelerar la vista. El paginador permite ir hacia adelante, atrás a la última y a la primera página de la información\n'),('3d8f3cc4752cc722462685d05d85c9aa','tip-7','<h2>Zoom!</h2>Presione la tecla CTRL y gire la rueda del ratón para alejar y acercar la aplicación. Presion CTRL y 0 (cero) para volver al tamaño original'),('4f262821aac1fbaf01d646105900a04c','tip-3','<h2>Pantallas Personalizadas</h2>Agrande o achique las columnas arrastrando de dónde terminan los títulos. Cámbielas de órden arrastrandolas desde el título a la posición que más desee. La aplicación recordará sus preferencias'),('b14d22190167251f453450cddb6873b4','tip-4','<h2>Pestañas y Ver</h2>Las pestañas en el panel central pueden mostrar más datos relacionados al panel principal. El ícono \"Ver\" permite inspeccionar el registro seleccionado.'),('dfd0982c95b5343cbdc84272811bde23','tip-2','<h2>Buscar</h2>Escriba sobre el campo Buscar lo que desea obtener. Si no encuentra, pruebe utilizando los asteriscos (*) al principio y final de la(s) palabra(s) para obtener más resultados');
/*!40000 ALTER TABLE `tip` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-08-08 21:21:03
