/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_config'
*/
CREATE TABLE phpbb_gallery_config (
	config_name varchar2(255) DEFAULT '' ,
	config_value varchar2(255) DEFAULT '' ,
	CONSTRAINT pk_phpbb_gallery_config PRIMARY KEY (config_name)
)
/


