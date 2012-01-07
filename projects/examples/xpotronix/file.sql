DROP TABLE IF EXISTS `file`;
CREATE TABLE `file` (
  `ID` char(32) NOT NULL,
  `parent_ID` char(32) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `content` longblob NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  KEY `parentId` (`parent_ID`),
  KEY `shortName` (`file_name`),
  KEY `fileCls` (`mime_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

