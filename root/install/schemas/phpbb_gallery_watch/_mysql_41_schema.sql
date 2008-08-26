#
# $Id$
#

# Table: 'phpbb_gallery_watch'
CREATE TABLE phpbb_gallery_watch (
	watch_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	album_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	image_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (watch_id),
	KEY user_id (user_id),
	KEY image_id (image_id),
	KEY album_id (album_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


