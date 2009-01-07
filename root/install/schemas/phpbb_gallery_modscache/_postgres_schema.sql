/*

 $Id$

*/

BEGIN;


/*
	Table: 'phpbb_gallery_modscache'
*/
CREATE TABLE phpbb_gallery_modscache (
	album_id INT4 DEFAULT '0' NOT NULL CHECK (album_id >= 0),
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	username varchar(255) DEFAULT '' NOT NULL,
	group_id INT4 DEFAULT '0' NOT NULL CHECK (group_id >= 0),
	group_name varchar(255) DEFAULT '' NOT NULL,
	display_on_index INT2 DEFAULT '1' NOT NULL
);

CREATE INDEX phpbb_gallery_modscache_disp_idx ON phpbb_gallery_modscache (display_on_index);
CREATE INDEX phpbb_gallery_modscache_album_id ON phpbb_gallery_modscache (album_id);


COMMIT;