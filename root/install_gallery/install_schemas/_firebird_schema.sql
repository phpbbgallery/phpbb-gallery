#
# $Id: $
#


# Table: 'phpbb_album'
CREATE TABLE phpbb_album (
	pic_id INTEGER NOT NULL,
	pic_filename VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	pic_thumbnail VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	pic_title VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	pic_desc BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	pic_desc_bbcode_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	pic_desc_bbcode_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	pic_user_id INTEGER DEFAULT 0 NOT NULL,
	pic_username VARCHAR(32) CHARACTER SET NONE DEFAULT '' NOT NULL,
	pic_user_ip VARCHAR(40) CHARACTER SET NONE DEFAULT '' NOT NULL,
	pic_time INTEGER DEFAULT 0 NOT NULL,
	pic_cat_id INTEGER DEFAULT 0 NOT NULL,
	pic_view_count INTEGER DEFAULT 0 NOT NULL,
	pic_lock INTEGER DEFAULT 0 NOT NULL,
	pic_approval INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_album ADD PRIMARY KEY (pic_id);;

CREATE INDEX phpbb_album_pic_cat_id ON phpbb_album(pic_cat_id);;
CREATE INDEX phpbb_album_pic_user_id ON phpbb_album(pic_user_id);;
CREATE INDEX phpbb_album_pic_time ON phpbb_album(pic_time);;

CREATE GENERATOR phpbb_album_gen;;
SET GENERATOR phpbb_album_gen TO 0;;

CREATE TRIGGER t_phpbb_album FOR phpbb_album
BEFORE INSERT
AS
BEGIN
	NEW.pic_id = GEN_ID(phpbb_album_gen, 1);
END;;


# Table: 'phpbb_album_cat'
CREATE TABLE phpbb_album_cat (
	cat_id INTEGER NOT NULL,
	cat_title VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	cat_desc BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	cat_desc_bbcode_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	cat_desc_bbcode_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	cat_order INTEGER DEFAULT 0 NOT NULL,
	cat_view_level INTEGER DEFAULT 1 NOT NULL,
	cat_upload_level INTEGER DEFAULT 0 NOT NULL,
	cat_rate_level INTEGER DEFAULT 0 NOT NULL,
	cat_comment_level INTEGER DEFAULT 0 NOT NULL,
	cat_edit_level INTEGER DEFAULT 0 NOT NULL,
	cat_delete_level INTEGER DEFAULT 2 NOT NULL,
	cat_view_groups VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	cat_upload_groups VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	cat_rate_groups VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	cat_comment_groups VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	cat_edit_groups VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	cat_delete_groups VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	cat_moderator_groups VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	cat_approval INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_album_cat ADD PRIMARY KEY (cat_id);;

CREATE INDEX phpbb_album_cat_cat_order ON phpbb_album_cat(cat_order);;

CREATE GENERATOR phpbb_album_cat_gen;;
SET GENERATOR phpbb_album_cat_gen TO 0;;

CREATE TRIGGER t_phpbb_album_cat FOR phpbb_album_cat
BEFORE INSERT
AS
BEGIN
	NEW.cat_id = GEN_ID(phpbb_album_cat_gen, 1);
END;;


# Table: 'phpbb_album_comment'
CREATE TABLE phpbb_album_comment (
	comment_id INTEGER NOT NULL,
	comment_pic_id INTEGER NOT NULL,
	comment_user_id INTEGER DEFAULT 0 NOT NULL,
	comment_username VARCHAR(32) CHARACTER SET NONE DEFAULT '' NOT NULL,
	comment_user_ip VARCHAR(40) CHARACTER SET NONE DEFAULT '' NOT NULL,
	comment_time INTEGER DEFAULT 0 NOT NULL,
	comment_text BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	comment_text_bbcode_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	comment_text_bbcode_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	comment_edit_time INTEGER DEFAULT 0 NOT NULL,
	comment_edit_count INTEGER DEFAULT 0 NOT NULL,
	comment_edit_user_id INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_album_comment ADD PRIMARY KEY (comment_id);;

CREATE INDEX phpbb_album_comment_comment_pic_id ON phpbb_album_comment(comment_pic_id);;
CREATE INDEX phpbb_album_comment_comment_user_id ON phpbb_album_comment(comment_user_id);;
CREATE INDEX phpbb_album_comment_comment_user_ip ON phpbb_album_comment(comment_user_ip);;
CREATE INDEX phpbb_album_comment_comment_time ON phpbb_album_comment(comment_time);;

CREATE GENERATOR phpbb_album_comment_gen;;
SET GENERATOR phpbb_album_comment_gen TO 0;;

CREATE TRIGGER t_phpbb_album_comment FOR phpbb_album_comment
BEFORE INSERT
AS
BEGIN
	NEW.comment_id = GEN_ID(phpbb_album_comment_gen, 1);
END;;


# Table: 'phpbb_album_config'
CREATE TABLE phpbb_album_config (
	config_name VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	config_value VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL
);;

ALTER TABLE phpbb_album_config ADD PRIMARY KEY (config_name);;


# Table: 'phpbb_album_rate'
CREATE TABLE phpbb_album_rate (
	rate_pic_id INTEGER NOT NULL,
	rate_user_id INTEGER DEFAULT 0 NOT NULL,
	rate_user_ip VARCHAR(40) CHARACTER SET NONE DEFAULT '' NOT NULL,
	rate_point INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_album_rate ADD PRIMARY KEY (rate_pic_id);;

CREATE INDEX phpbb_album_rate_rate_user_id ON phpbb_album_rate(rate_user_id);;
CREATE INDEX phpbb_album_rate_rate_user_ip ON phpbb_album_rate(rate_user_ip);;
CREATE INDEX phpbb_album_rate_rate_point ON phpbb_album_rate(rate_point);;

CREATE GENERATOR phpbb_album_rate_gen;;
SET GENERATOR phpbb_album_rate_gen TO 0;;

CREATE TRIGGER t_phpbb_album_rate FOR phpbb_album_rate
BEFORE INSERT
AS
BEGIN
	NEW.rate_pic_id = GEN_ID(phpbb_album_rate_gen, 1);
END;;


