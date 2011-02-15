/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_users'
*/
CREATE TABLE phpbb_gallery_users (
	user_id number(8) DEFAULT '0' NOT NULL,
	watch_own number(3) DEFAULT '0' NOT NULL,
	watch_favo number(3) DEFAULT '0' NOT NULL,
	watch_com number(3) DEFAULT '0' NOT NULL,
	user_images number(8) DEFAULT '0' NOT NULL,
	personal_album_id number(8) DEFAULT '0' NOT NULL,
	user_lastmark number(11) DEFAULT '0' NOT NULL,
	user_last_update number(11) DEFAULT '0' NOT NULL,
	user_viewexif number(1) DEFAULT '0' NOT NULL,
	user_permissions clob DEFAULT '' ,
	CONSTRAINT pk_phpbb_gallery_users PRIMARY KEY (user_id)
)
/


