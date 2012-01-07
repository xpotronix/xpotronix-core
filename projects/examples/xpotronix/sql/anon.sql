-- Consulta para el phpGacl para el usuario anonimo:
-- en gacl_aro, se define el usuario dentro del phpgacl. El campo value (para el usuario anon = 0 en este ejemplo) es el user_id de la tabla users.
-- en gacl_aro_group, son los grupos de usuario, en este caso creamos un grupo con el id = 12
-- por ultimo, en gacl_aro_groups_map, creamos la asociacion del usuario en el phpgacl (gacl_aro) y el grupo al que pertence

INSERT INTO `users` VALUES (0,0,'anon','*** no password ***',0,0,0,0,0,NULL,NULL,NULL);
INSERT INTO `gacl_aro` VALUES (11,'user','0',1,'anon',0);
INSERT INTO `gacl_aro_groups` VALUES (12,10,1,4,'Anon','anon');
INSERT INTO `gacl_aro_groups_map` VALUES (12,11);

