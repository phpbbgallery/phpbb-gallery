/*

 $Id$

*/

BEGIN;


/*
	Table: 'phpbb_gallery_copyts_albums'
*/
CREATE SEQUENCE phpbb_gallery_copyts_albums_seq;

CREATE TABLE phpbb_gallery_copyts_albums (
	album_id INT4 DEFAULT nextval('phpbb_gallery_copyts_albums_seq'),
	parent_id INT4 DEFAULT '0' NOT NULL CHECK (parent_id >= 0),
	left_id INT4 DEFAULT '1' NOT NULL CHECK (left_id >= 0),
	right_id INT4 DEFAULT '2' NOT NULL CHECK (right_id >= 0),
	album_name varchar(255) DEFAULT '' NOT NULL,
	album_desc TEXT DEFAULT '' NOT NULL,
	album_user_id INT4 DEFAULT '0' NOT NULL CHECK (album_user_id >= 0),
	PRIMARY KEY (album_id)
);



COMMIT;