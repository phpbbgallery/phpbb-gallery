#
# $Id$
#

# Table: 'phpbb_gallery_copyts_users'
CREATE TABLE phpbb_gallery_copyts_users (
	user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	personal_album_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (user_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


