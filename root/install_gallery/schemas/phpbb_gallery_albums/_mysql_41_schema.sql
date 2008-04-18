#
# $Id: $
#

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


