#
# $Id$
#

# Table: 'phpbb_gallery_albums'
CREATE TABLE phpbb_gallery_albums (
	album_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	parent_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	left_id mediumint(8) UNSIGNED DEFAULT '1' NOT NULL,
	right_id mediumint(8) UNSIGNED DEFAULT '2' NOT NULL,
	album_parents mediumblob NOT NULL,
	album_type int(3) UNSIGNED DEFAULT '1' NOT NULL,
	album_name varbinary(255) DEFAULT '' NOT NULL,
	album_desc mediumblob NOT NULL,
	album_desc_options int(3) UNSIGNED DEFAULT '7' NOT NULL,
	album_desc_uid varbinary(8) DEFAULT '' NOT NULL,
	album_desc_bitfield varbinary(255) DEFAULT '' NOT NULL,
	album_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	album_images mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	album_images_real mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	album_last_image_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	album_image varbinary(255) DEFAULT '' NOT NULL,
	album_last_image_time int(11) DEFAULT '0' NOT NULL,
	album_last_image_name varbinary(255) DEFAULT '' NOT NULL,
	album_last_username varbinary(255) DEFAULT '' NOT NULL,
	album_last_user_colour varbinary(6) DEFAULT '' NOT NULL,
	album_last_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	display_on_index int(1) UNSIGNED DEFAULT '1' NOT NULL,
	display_subalbum_list int(1) UNSIGNED DEFAULT '1' NOT NULL,
	PRIMARY KEY (album_id)
);


