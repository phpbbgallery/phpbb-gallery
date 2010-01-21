/*

 $Id$

*/

BEGIN;


/*
	Table: 'phpbb_gallery_albums'
*/
CREATE SEQUENCE phpbb_gallery_albums_seq;

CREATE TABLE phpbb_gallery_albums (
	album_id INT4 DEFAULT nextval('phpbb_gallery_albums_seq'),
	parent_id INT4 DEFAULT '0' NOT NULL CHECK (parent_id >= 0),
	left_id INT4 DEFAULT '1' NOT NULL CHECK (left_id >= 0),
	right_id INT4 DEFAULT '2' NOT NULL CHECK (right_id >= 0),
	album_parents TEXT DEFAULT '' NOT NULL,
	album_type INT4 DEFAULT '1' NOT NULL CHECK (album_type >= 0),
	album_status INT4 DEFAULT '1' NOT NULL CHECK (album_status >= 0),
	album_contest INT4 DEFAULT '0' NOT NULL CHECK (album_contest >= 0),
	album_name varchar(255) DEFAULT '' NOT NULL,
	album_desc TEXT DEFAULT '' NOT NULL,
	album_desc_options INT4 DEFAULT '7' NOT NULL CHECK (album_desc_options >= 0),
	album_desc_uid varchar(8) DEFAULT '' NOT NULL,
	album_desc_bitfield varchar(255) DEFAULT '' NOT NULL,
	album_user_id INT4 DEFAULT '0' NOT NULL CHECK (album_user_id >= 0),
	album_images INT4 DEFAULT '0' NOT NULL CHECK (album_images >= 0),
	album_images_real INT4 DEFAULT '0' NOT NULL CHECK (album_images_real >= 0),
	album_last_image_id INT4 DEFAULT '0' NOT NULL CHECK (album_last_image_id >= 0),
	album_image varchar(255) DEFAULT '' NOT NULL,
	album_last_image_time INT4 DEFAULT '0' NOT NULL,
	album_last_image_name varchar(255) DEFAULT '' NOT NULL,
	album_last_username varchar(255) DEFAULT '' NOT NULL,
	album_last_user_colour varchar(6) DEFAULT '' NOT NULL,
	album_last_user_id INT4 DEFAULT '0' NOT NULL CHECK (album_last_user_id >= 0),
	album_watermark INT4 DEFAULT '1' NOT NULL CHECK (album_watermark >= 0),
	album_sort_key varchar(8) DEFAULT '' NOT NULL,
	album_sort_dir varchar(8) DEFAULT '' NOT NULL,
	display_in_rrc INT4 DEFAULT '1' NOT NULL CHECK (display_in_rrc >= 0),
	display_on_index INT4 DEFAULT '1' NOT NULL CHECK (display_on_index >= 0),
	display_subalbum_list INT4 DEFAULT '1' NOT NULL CHECK (display_subalbum_list >= 0),
	PRIMARY KEY (album_id)
);



COMMIT;