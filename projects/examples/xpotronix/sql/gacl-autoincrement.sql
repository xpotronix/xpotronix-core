ALTER TABLE `gacl_acl` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gacl_acl` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gacl_acl_sections` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gacl_aco` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gacl_aco_sections` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gacl_aro` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gacl_aro_groups` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gacl_aro_sections` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gacl_axo` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gacl_axo_groups` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gacl_axo_sections` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
DROP TABLE `gacl_acl_seq`, `gacl_aco_sections_seq`, `gacl_aco_seq`, `gacl_aro_groups_id_seq`, `gacl_aro_sections_seq`, `gacl_aro_seq`, `gacl_axo_groups_id_seq`, `gacl_axo_sections_seq`, `gacl_axo_seq`, `gacl_phpgacl`;
