#
# $Id$
#

# Table: 'phpbb_gallery_modscache'
CREATE TABLE phpbb_gallery_modscache (
	album_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	username varbinary(255) DEFAULT '' NOT NULL,
	group_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	group_name varbinary(255) DEFAULT '' NOT NULL,
	display_on_index tinyint(1) DEFAULT '1' NOT NULL,
	KEY disp_idx (display_on_index),
	KEY album_id (album_id)
);


