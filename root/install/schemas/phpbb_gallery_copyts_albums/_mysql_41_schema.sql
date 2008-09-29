#
# $Id$
#

# Table: 'phpbb_gallery_copyts_albums'
CREATE TABLE phpbb_gallery_copyts_albums (
	album_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	parent_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	left_id mediumint(8) UNSIGNED DEFAULT '1' NOT NULL,
	right_id mediumint(8) UNSIGNED DEFAULT '2' NOT NULL,
	album_name varchar(255) DEFAULT '' NOT NULL,
	album_desc mediumtext NOT NULL,
	album_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (album_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


