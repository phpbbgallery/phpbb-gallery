#
# $Id$
#

# Table: 'phpbb_gallery_comments'
CREATE TABLE phpbb_gallery_comments (
	comment_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	comment_image_id mediumint(8) UNSIGNED NOT NULL,
	comment_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	comment_username varchar(255) DEFAULT '' NOT NULL,
	comment_user_colour varchar(6) DEFAULT '' NOT NULL,
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


