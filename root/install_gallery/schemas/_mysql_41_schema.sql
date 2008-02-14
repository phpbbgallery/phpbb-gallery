#
# $Id: $
#

# Table: 'phpbb_gallery_images'
CREATE TABLE phpbb_gallery_images (
	image_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	image_filename varchar(255) DEFAULT '' NOT NULL,
	image_thumbnail varchar(255) DEFAULT '' NOT NULL,
	image_name varchar(255) DEFAULT '' NOT NULL,
	image_desc mediumtext NOT NULL,
	image_desc_uid varchar(8) DEFAULT '' NOT NULL,
	image_desc_bitfield varchar(255) DEFAULT '' NOT NULL,
	image_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	image_username varchar(255) DEFAULT '' NOT NULL,
	image_user_colour varchar(6) DEFAULT '' NOT NULL,
	image_user_ip varchar(40) DEFAULT '' NOT NULL,
	image_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	image_album_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	image_view_count int(11) UNSIGNED DEFAULT '0' NOT NULL,
	image_lock int(3) UNSIGNED DEFAULT '0' NOT NULL,
	image_approval int(3) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (image_id),
	KEY image_album_id (image_album_id),
	KEY image_user_id (image_user_id),
	KEY image_time (image_time)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


# Table: 'phpbb_gallery_albums'
CREATE TABLE phpbb_gallery_albums (
	album_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	parent_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	left_id mediumint(8) UNSIGNED DEFAULT '1' NOT NULL,
	right_id mediumint(8) UNSIGNED DEFAULT '2' NOT NULL,
	album_parents mediumtext NOT NULL,
	album_type int(3) UNSIGNED DEFAULT '1' NOT NULL,
	album_name varchar(255) DEFAULT '' NOT NULL,
	album_desc mediumtext NOT NULL,
	album_desc_options int(3) UNSIGNED DEFAULT '7' NOT NULL,
	album_desc_uid varchar(8) DEFAULT '' NOT NULL,
	album_desc_bitfield varchar(255) DEFAULT '' NOT NULL,
	album_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	album_order mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	album_view_level int(3) UNSIGNED DEFAULT '1' NOT NULL,
	album_upload_level int(3) UNSIGNED DEFAULT '0' NOT NULL,
	album_rate_level int(3) UNSIGNED DEFAULT '0' NOT NULL,
	album_comment_level int(3) UNSIGNED DEFAULT '0' NOT NULL,
	album_edit_level int(3) UNSIGNED DEFAULT '0' NOT NULL,
	album_delete_level int(3) UNSIGNED DEFAULT '2' NOT NULL,
	album_view_groups varchar(255) DEFAULT '' NOT NULL,
	album_upload_groups varchar(255) DEFAULT '' NOT NULL,
	album_rate_groups varchar(255) DEFAULT '' NOT NULL,
	album_comment_groups varchar(255) DEFAULT '' NOT NULL,
	album_edit_groups varchar(255) DEFAULT '' NOT NULL,
	album_delete_groups varchar(255) DEFAULT '' NOT NULL,
	album_moderator_groups varchar(255) DEFAULT '' NOT NULL,
	album_approval int(3) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (album_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


# Table: 'phpbb_gallery_comments'
CREATE TABLE phpbb_gallery_comments (
	comment_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	comment_image_id mediumint(8) UNSIGNED NOT NULL,
	comment_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	comment_username varchar(32) DEFAULT '' NOT NULL,
	comment_user_ip varchar(40) DEFAULT '' NOT NULL,
	comment_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	comment mediumtext NOT NULL,
	comment_uid varchar(8) DEFAULT '' NOT NULL,
	comment_bitfield varchar(255) DEFAULT '' NOT NULL,
	comment_edit_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	comment_edit_count smallint(4) UNSIGNED DEFAULT '0' NOT NULL,
	comment_edit_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (comment_id),
	KEY comment_image_id (comment_image_id),
	KEY comment_user_id (comment_user_id),
	KEY comment_user_ip (comment_user_ip),
	KEY comment_time (comment_time)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


# Table: 'phpbb_gallery_config'
CREATE TABLE phpbb_gallery_config (
	config_name varchar(255) DEFAULT '' NOT NULL,
	config_value varchar(255) DEFAULT '' NOT NULL,
	PRIMARY KEY (config_name)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


# Table: 'phpbb_gallery_rates'
CREATE TABLE phpbb_gallery_rates (
	rate_image_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	rate_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	rate_user_ip varchar(40) DEFAULT '' NOT NULL,
	rate_point int(3) UNSIGNED DEFAULT '0' NOT NULL,
	KEY rate_image_id (rate_image_id),
	KEY rate_user_id (rate_user_id),
	KEY rate_user_ip (rate_user_ip),
	KEY rate_point (rate_point)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


