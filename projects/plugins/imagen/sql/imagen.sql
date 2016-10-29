CREATE TABLE `imagen` (
  `ID` char(32) NOT NULL DEFAULT '',
  `imagen` varchar(45) NOT NULL DEFAULT '',
  `usuario_id` varchar(45) NOT NULL DEFAULT '',
  `fecha` date NOT NULL DEFAULT '0000-00-00',
  `dirname` varchar(255) NOT NULL DEFAULT '',
  `basename` varchar(255) NOT NULL DEFAULT '',
  `extension` varchar(5) NOT NULL DEFAULT '',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `encontrada` tinyint(1) NOT NULL DEFAULT '0',
  `cache` tinyint(1) NOT NULL DEFAULT '0',
  `filesize` int(11) NOT NULL DEFAULT '0',
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `exim_info` mediumtext,
  PRIMARY KEY (`ID`),
  KEY `imagen` (`imagen`),
  KEY `usuario_id` (`usuario_id`),
  KEY `seleccion1` (`fecha`,`usuario_id`,`filename`),
  KEY `seleccion2` (`usuario_id`,`filename`),
  KEY `seleccion3` (`fecha`,`filename`),
  KEY `encontrada` (`encontrada`),
  KEY `filename` (`filename`),
  KEY `cache` (`cache`),
  KEY `filesize` (`filesize`),
  KEY `dirname` (`dirname`),
  KEY `basename` (`basename`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE `scan_imagen` (
  `ID` char(32) NOT NULL DEFAULT '',
  `fecha_desde` date NOT NULL DEFAULT '0000-00-00',
  `existentes` int(13) NOT NULL DEFAULT '0',
  `encontradas` int(13) NOT NULL DEFAULT '0',
  `faltantes` int(13) NOT NULL DEFAULT '0',
  `inicio` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(255) NOT NULL DEFAULT '',
  `tiempo` float NOT NULL DEFAULT '0',
  `observaciones` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
