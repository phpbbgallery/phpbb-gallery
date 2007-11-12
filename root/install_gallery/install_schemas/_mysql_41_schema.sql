#
# $Id: $
#

# Table: 'phpbb_album'
CREATE TABLE phpbb_album (
	pic_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	pic_filename varchar(255) DEFAULT '' NOT NULL,
	pic_thumbnail varchar(255) DEFAULT '' NOT NULL,
	pic_title varchar(255) DEFAULT '' NOT NULL,
	pic_desc mediumtext NOT NULL,
	pic_desc_bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	pic_desc_bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	pic_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	pic_username varchar(32) DEFAULT '' NOT NULL,
	pic_user_ip varchar(40) DEFAULT '' NOT NULL,
	pic_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	pic_cat_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	pic_view_count int(11) UNSIGNED DEFAULT '0' NOT NULL,
	pic_lock tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	pic_approval tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (pic_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


# Table: 'phpbb_album_cat'
CREATE TABLE phpbb_album_cat (
	cat_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	cat_title varchar(255) DEFAULT '' NOT NULL,
	cat_desc mediumtext NOT NULL,
	cat_desc_bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	cat_desc_bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	cat_order mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	cat_view_level tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
	cat_upload_level tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	cat_rate_level tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	cat_comment_level tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	cat_edit_level tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	cat_delete_level tinyint(1) UNSIGNED DEFAULT '2' NOT NULL,
	cat_view_groups varchar(255) DEFAULT '' NOT NULL,
	cat_upload_groups varchar(255) DEFAULT '' NOT NULL,
	cat_rate_groups varchar(255) DEFAULT '' NOT NULL,
	cat_comment_groups varchar(255) DEFAULT '' NOT NULL,
	cat_edit_groups varchar(255) DEFAULT '' NOT NULL,
	cat_delete_groups varchar(255) DEFAULT '' NOT NULL,
	cat_moderator_groups varchar(255) DEFAULT '' NOT NULL,
	cat_approval tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (cat_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


# Table: 'phpbb_album_comment'
CREATE TABLE phpbb_album_comment (
	comment_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	comment_pic_id mediumint(8) UNSIGNED NOT NULL,
	comment_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	comment_username varchar(32) DEFAULT '' NOT NULL,
	comment_user_ip varchar(40) DEFAULT '' NOT NULL,
	comment_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	comment_text mediumtext NOT NULL,
	comment_text_bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	comment_text_bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	comment_edit_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	comment_edit_count smallint(4) UNSIGNED DEFAULT '0' NOT NULL,
	comment_edit_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (comment_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


# Table: 'phpbb_album_config'
CREATE TABLE phpbb_album_config (
	config_name varchar(255) DEFAULT '' NOT NULL,
	config_value varchar(255) DEFAULT '' NOT NULL,
	PRIMARY KEY (config_name)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


# Table: 'phpbb_album_rate'
CREATE TABLE phpbb_album_rate (
	rate_pic_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	rate_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	rate_user_ip varchar(40) DEFAULT '' NOT NULL,
	rate_point tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (rate_pic_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


