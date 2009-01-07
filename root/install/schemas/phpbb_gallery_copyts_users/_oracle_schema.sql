/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_copyts_users'
*/
CREATE TABLE phpbb_gallery_copyts_users (
	user_id number(8) DEFAULT '0' NOT NULL,
	personal_album_id number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_gallery_copyts_users PRIMARY KEY (user_id)
)
/


