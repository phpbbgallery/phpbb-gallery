#
# $Id$
#

BEGIN TRANSACTION;

# Table: 'phpbb_gallery_albums'
CREATE TABLE phpbb_gallery_albums (
	album_id INTEGER PRIMARY KEY NOT NULL ,
	parent_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	left_id INTEGER UNSIGNED NOT NULL DEFAULT '1',
	right_id INTEGER UNSIGNED NOT NULL DEFAULT '2',
	album_parents mediumtext(16777215) NOT NULL DEFAULT '',
	album_type INTEGER UNSIGNED NOT NULL DEFAULT '1',
	album_status INTEGER UNSIGNED NOT NULL DEFAULT '1',
	album_contest INTEGER UNSIGNED NOT NULL DEFAULT '0',
	album_name varchar(255) NOT NULL DEFAULT '',
	album_desc mediumtext(16777215) NOT NULL DEFAULT '',
	album_desc_options INTEGER UNSIGNED NOT NULL DEFAULT '7',
	album_desc_uid varchar(8) NOT NULL DEFAULT '',
	album_desc_bitfield varchar(255) NOT NULL DEFAULT '',
	album_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	album_images INTEGER UNSIGNED NOT NULL DEFAULT '0',
	album_images_real INTEGER UNSIGNED NOT NULL DEFAULT '0',
	album_last_image_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	album_image varchar(255) NOT NULL DEFAULT '',
	album_last_image_time int(11) NOT NULL DEFAULT '0',
	album_last_image_name varchar(255) NOT NULL DEFAULT '',
	album_last_username varchar(255) NOT NULL DEFAULT '',
	album_last_user_colour varchar(6) NOT NULL DEFAULT '',
	album_last_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	album_watermark INTEGER UNSIGNED NOT NULL DEFAULT '1',
	album_sort_key varchar(8) NOT NULL DEFAULT '',
	album_sort_dir varchar(8) NOT NULL DEFAULT '',
	display_in_rrc INTEGER UNSIGNED NOT NULL DEFAULT '1',
	display_on_index INTEGER UNSIGNED NOT NULL DEFAULT '1',
	display_subalbum_list INTEGER UNSIGNED NOT NULL DEFAULT '1'
);



COMMIT;