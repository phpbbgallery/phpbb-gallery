/*

 $Id$

*/

BEGIN;


/*
	Table: 'phpbb_gallery_config'
*/
CREATE TABLE phpbb_gallery_config (
	config_name varchar(255) DEFAULT '' NOT NULL,
	config_value varchar(255) DEFAULT '' NOT NULL,
	PRIMARY KEY (config_name)
);



COMMIT;