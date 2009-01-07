/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_modscache'
*/
CREATE TABLE phpbb_gallery_modscache (
	album_id number(8) DEFAULT '0' NOT NULL,
	user_id number(8) DEFAULT '0' NOT NULL,
	username varchar2(255) DEFAULT '' ,
	group_id number(8) DEFAULT '0' NOT NULL,
	group_name varchar2(255) DEFAULT '' ,
	display_on_index number(1) DEFAULT '1' NOT NULL
)
/

CREATE INDEX phpbb_gallery_modscache_disp_idx ON phpbb_gallery_modscache (display_on_index)
/
CREATE INDEX phpbb_gallery_modscache_album_id ON phpbb_gallery_modscache (album_id)
/

