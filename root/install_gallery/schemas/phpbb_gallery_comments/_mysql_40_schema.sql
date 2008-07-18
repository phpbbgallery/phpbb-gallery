#
# $Id: $
#

# Table: 'phpbb_gallery_comments'
CREATE TABLE phpbb_gallery_comments (
	comment_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	comment_image_id mediumint(8) UNSIGNED NOT NULL,
	comment_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	comment_username varbinary(32) DEFAULT '' NOT NULL,
	comment_user_colour varbinary(6) DEFAULT '' NOT NULL,
	comment_user_ip varbinary(40) DEFAULT '' NOT NULL,
	comment_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	comment mediumblob NOT NULL,
	comment_uid varbinary(8) DEFAULT '' NOT NULL,
	comment_bitfield varbinary(255) DEFAULT '' NOT NULL,
	comment_edit_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	comment_edit_count smallint(4) UNSIGNED DEFAULT '0' NOT NULL,
	comment_edit_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (comment_id),
	KEY comment_image_id (comment_image_id),
	KEY comment_user_id (comment_user_id),
	KEY comment_user_ip (comment_user_ip),
	KEY comment_time (comment_time)
);


