DROP TABLE IF EXISTS `user_preferences`;
CREATE TABLE `user_preferences` (
  `id` char(32) NOT NULL,
  `user_id` char(32) NOT NULL,
  `var_name` varchar(255) NOT NULL,
  `var_value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `name` (`var_name`)
) ENGINE=InnoDB

