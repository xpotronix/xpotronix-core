DROP  TABLE gacl_acl_seq;
ALTER TABLE gacl_acl CHANGE id id INT( 11 ) NOT NULL AUTO_INCREMENT;

DROP  TABLE gacl_aco_sections_seq;
ALTER TABLE gacl_aco_sections CHANGE id id INT( 11 ) NOT NULL AUTO_INCREMENT;

DROP  TABLE gacl_aco_seq;
ALTER TABLE gacl_aco CHANGE id id INT( 11 ) NOT NULL AUTO_INCREMENT;

DROP  TABLE gacl_aro_groups_id_seq;
ALTER TABLE gacl_aro_groups CHANGE id id INT( 11 ) NOT NULL AUTO_INCREMENT;

DROP  TABLE gacl_aro_sections_seq;
ALTER TABLE gacl_aro_sections CHANGE id id INT( 11 ) NOT NULL AUTO_INCREMENT;

DROP  TABLE gacl_aro_seq;
ALTER TABLE gacl_aro CHANGE id id INT( 11 ) NOT NULL AUTO_INCREMENT;

DROP  TABLE gacl_axo_groups_id_seq;
ALTER TABLE gacl_axo_groups CHANGE id id INT( 11 ) NOT NULL AUTO_INCREMENT;

DROP  TABLE gacl_axo_sections_seq;
ALTER TABLE gacl_axo_sections CHANGE id id INT( 11 ) NOT NULL AUTO_INCREMENT;

DROP  TABLE gacl_axo_seq;
ALTER TABLE gacl_axo CHANGE id id INT( 11 ) NOT NULL AUTO_INCREMENT;
