#
# $Id: $
#

# Table: 'phpbb_album'
CREATE TABLE phpbb_album (
	pic_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	pic_filename varbinary(255) DEFAULT '' NOT NULL,
	pic_thumbnail varbinary(255) DEFAULT '' NOT NULL,
	pic_title varbinary(255) DEFAULT '' NOT NULL,
	pic_desc mediumblob NOT NULL,
	pic_desc_bbcode_uid varbinary(8) DEFAULT '' NOT NULL,
	pic_desc_bbcode_bitfield varbinary(255) DEFAULT '' NOT NULL,
	pic_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	pic_username varbinary(32) DEFAULT '' NOT NULL,
	pic_user_ip varbinary(40) DEFAULT '' NOT NULL,
	pic_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	pic_cat_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	pic_view_count int(11) UNSIGNED DEFAULT '0' NOT NULL,
	pic_lock tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	pic_approval tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (pic_id),
	KEY pic_cat_id (pic_cat_id),
	KEY pic_user_id (pic_user_id),
	KEY pic_time (pic_time)
);


# Table: 'phpbb_album_cat'
CREATE TABLE phpbb_album_cat (
	cat_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	cat_title varbinary(255) DEFAULT '' NOT NULL,
	cat_desc mediumblob NOT NULL,
	cat_desc_bbcode_uid varbinary(8) DEFAULT '' NOT NULL,
	cat_desc_bbcode_bitfield varbinary(255) DEFAULT '' NOT NULL,
	cat_order mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	cat_view_level tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
	cat_upload_level tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	cat_rate_level tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	cat_comment_level tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	cat_edit_level tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	cat_delete_level tinyint(1) UNSIGNED DEFAULT '2' NOT NULL,
	cat_view_groups varbinary(255) DEFAULT '' NOT NULL,
	cat_upload_groups varbinary(255) DEFAULT '' NOT NULL,
	cat_rate_groups varbinary(255) DEFAULT '' NOT NULL,
	cat_comment_groups varbinary(255) DEFAULT '' NOT NULL,
	cat_edit_groups varbinary(255) DEFAULT '' NOT NULL,
	cat_delete_groups varbinary(255) DEFAULT '' NOT NULL,
	cat_moderator_groups varbinary(255) DEFAULT '' NOT NULL,
	cat_approval tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (cat_id),
	KEY cat_order (cat_order)
);


# Table: 'phpbb_album_comment'
CREATE TABLE phpbb_album_comment (
	comment_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	comment_pic_id mediumint(8) UNSIGNED NOT NULL,
	comment_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	comment_username varbinary(32) DEFAULT '' NOT NULL,
	comment_user_ip varbinary(40) DEFAULT '' NOT NULL,
	comment_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	comment_text mediumblob NOT NULL,
	comment_text_bbcode_uid varbinary(8) DEFAULT '' NOT NULL,
	comment_text_bbcode_bitfield varbinary(255) DEFAULT '' NOT NULL,
	comment_edit_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	comment_edit_count smallint(4) UNSIGNED DEFAULT '0' NOT NULL,
	comment_edit_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (comment_id),
	KEY comment_pic_id (comment_pic_id),
	KEY comment_user_id (comment_user_id),
	KEY comment_user_ip (comment_user_ip),
	KEY comment_time (comment_time)
);


# Table: 'phpbb_album_config'
CREATE TABLE phpbb_album_config (
	config_name varbinary(255) DEFAULT '' NOT NULL,
	config_value varbinary(255) DEFAULT '' NOT NULL,
	PRIMARY KEY (config_name)
);


# Table: 'phpbb_album_rate'
CREATE TABLE phpbb_album_rate (
	rate_pic_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	rate_user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	rate_user_ip varbinary(40) DEFAULT '' NOT NULL,
	rate_point tinyint(1) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (rate_pic_id),
	KEY rate_user_id (rate_user_id),
	KEY rate_user_ip (rate_user_ip),
	KEY rate_point (rate_point)
);


