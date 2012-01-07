ALTER TABLE  `sessions` ADD  `session_cookies` LONGBLOB NOT NULL AFTER  `session_data`;
ALTER TABLE  `sessions` ADD  `user_id` char(32) NOT NULL AFTER `session_data`;

