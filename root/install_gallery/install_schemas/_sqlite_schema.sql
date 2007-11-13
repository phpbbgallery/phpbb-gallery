#
# $Id: $
#

BEGIN TRANSACTION;

# Table: 'phpbb_album'
CREATE TABLE phpbb_album (
	pic_id INTEGER PRIMARY KEY NOT NULL ,
	pic_filename varchar(255) NOT NULL DEFAULT '',
	pic_thumbnail varchar(255) NOT NULL DEFAULT '',
	pic_title varchar(255) NOT NULL DEFAULT '',
	pic_desc mediumtext(16777215) NOT NULL DEFAULT '',
	pic_desc_bbcode_uid varchar(8) NOT NULL DEFAULT '',
	pic_desc_bbcode_bitfield varchar(255) NOT NULL DEFAULT '',
	pic_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	pic_username varchar(32) NOT NULL DEFAULT '',
	pic_user_ip varchar(40) NOT NULL DEFAULT '',
	pic_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	pic_cat_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	pic_view_count INTEGER UNSIGNED NOT NULL DEFAULT '0',
	pic_lock INTEGER UNSIGNED NOT NULL DEFAULT '0',
	pic_approval INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_album_pic_cat_id ON phpbb_album (pic_cat_id);
CREATE INDEX phpbb_album_pic_user_id ON phpbb_album (pic_user_id);
CREATE INDEX phpbb_album_pic_time ON phpbb_album (pic_time);

# Table: 'phpbb_album_cat'
CREATE TABLE phpbb_album_cat (
	cat_id INTEGER PRIMARY KEY NOT NULL ,
	cat_title varchar(255) NOT NULL DEFAULT '',
	cat_desc mediumtext(16777215) NOT NULL DEFAULT '',
	cat_desc_bbcode_uid varchar(8) NOT NULL DEFAULT '',
	cat_desc_bbcode_bitfield varchar(255) NOT NULL DEFAULT '',
	cat_order INTEGER UNSIGNED NOT NULL DEFAULT '0',
	cat_view_level INTEGER UNSIGNED NOT NULL DEFAULT '1',
	cat_upload_level INTEGER UNSIGNED NOT NULL DEFAULT '0',
	cat_rate_level INTEGER UNSIGNED NOT NULL DEFAULT '0',
	cat_comment_level INTEGER UNSIGNED NOT NULL DEFAULT '0',
	cat_edit_level INTEGER UNSIGNED NOT NULL DEFAULT '0',
	cat_delete_level INTEGER UNSIGNED NOT NULL DEFAULT '2',
	cat_view_groups varchar(255) NOT NULL DEFAULT '',
	cat_upload_groups varchar(255) NOT NULL DEFAULT '',
	cat_rate_groups varchar(255) NOT NULL DEFAULT '',
	cat_comment_groups varchar(255) NOT NULL DEFAULT '',
	cat_edit_groups varchar(255) NOT NULL DEFAULT '',
	cat_delete_groups varchar(255) NOT NULL DEFAULT '',
	cat_moderator_groups varchar(255) NOT NULL DEFAULT '',
	cat_approval INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_album_cat_cat_order ON phpbb_album_cat (cat_order);

# Table: 'phpbb_album_comment'
CREATE TABLE phpbb_album_comment (
	comment_id INTEGER PRIMARY KEY NOT NULL ,
	comment_pic_id INTEGER UNSIGNED NOT NULL ,
	comment_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	comment_username varchar(32) NOT NULL DEFAULT '',
	comment_user_ip varchar(40) NOT NULL DEFAULT '',
	comment_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	comment_text mediumtext(16777215) NOT NULL DEFAULT '',
	comment_text_bbcode_uid varchar(8) NOT NULL DEFAULT '',
	comment_text_bbcode_bitfield varchar(255) NOT NULL DEFAULT '',
	comment_edit_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	comment_edit_count INTEGER UNSIGNED NOT NULL DEFAULT '0',
	comment_edit_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_album_comment_comment_pic_id ON phpbb_album_comment (comment_pic_id);
CREATE INDEX phpbb_album_comment_comment_user_id ON phpbb_album_comment (comment_user_id);
CREATE INDEX phpbb_album_comment_comment_user_ip ON phpbb_album_comment (comment_user_ip);
CREATE INDEX phpbb_album_comment_comment_time ON phpbb_album_comment (comment_time);

# Table: 'phpbb_album_config'
CREATE TABLE phpbb_album_config (
	config_name varchar(255) NOT NULL DEFAULT '',
	config_value varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (config_name)
);


# Table: 'phpbb_album_rate'
CREATE TABLE phpbb_album_rate (
	rate_pic_id INTEGER PRIMARY KEY NOT NULL ,
	rate_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	rate_user_ip varchar(40) NOT NULL DEFAULT '',
	rate_point INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_album_rate_rate_user_id ON phpbb_album_rate (rate_user_id);
CREATE INDEX phpbb_album_rate_rate_user_ip ON phpbb_album_rate (rate_user_ip);
CREATE INDEX phpbb_album_rate_rate_point ON phpbb_album_rate (rate_point);


COMMIT;