CREATE TABLE `contador` (
	  `ID` char(32) NOT NULL DEFAULT '',
	  `nombre` char(50) DEFAULT NULL,
	  `numero` int(13) NOT NULL,
	  PRIMARY KEY (`ID`),
	  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
