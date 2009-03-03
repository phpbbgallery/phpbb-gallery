/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_albums_track'
*/
CREATE TABLE phpbb_gallery_albums_track (
	user_id number(8) DEFAULT '0' NOT NULL,
	album_id number(8) DEFAULT '0' NOT NULL,
	mark_time number(11) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_gallery_albums_track PRIMARY KEY (user_id, album_id)
)
/


