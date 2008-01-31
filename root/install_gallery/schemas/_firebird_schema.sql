#
# $Id: $
#


# Table: 'phpbb_gallery_images'
CREATE TABLE phpbb_gallery_images (
	image_id INTEGER NOT NULL,
	image_filename VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	image_thumbnail VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	image_name VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	image_desc BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	image_desc_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	image_desc_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	image_user_id INTEGER DEFAULT 0 NOT NULL,
	image_username VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	image_user_colour VARCHAR(6) CHARACTER SET NONE DEFAULT '' NOT NULL,
	image_user_ip VARCHAR(40) CHARACTER SET NONE DEFAULT '' NOT NULL,
	image_time INTEGER DEFAULT 0 NOT NULL,
	image_album_id INTEGER DEFAULT 0 NOT NULL,
	image_view_count INTEGER DEFAULT 0 NOT NULL,
	image_lock INTEGER DEFAULT 0 NOT NULL,
	image_approval INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_gallery_images ADD PRIMARY KEY (image_id);;

CREATE INDEX phpbb_gallery_images_image_album_id ON phpbb_gallery_images(image_album_id);;
CREATE INDEX phpbb_gallery_images_image_user_id ON phpbb_gallery_images(image_user_id);;
CREATE INDEX phpbb_gallery_images_image_time ON phpbb_gallery_images(image_time);;

CREATE GENERATOR phpbb_gallery_images_gen;;
SET GENERATOR phpbb_gallery_images_gen TO 0;;

CREATE TRIGGER t_phpbb_gallery_images FOR phpbb_gallery_images
BEFORE INSERT
AS
BEGIN
	NEW.image_id = GEN_ID(phpbb_gallery_images_gen, 1);
END;;


# Table: 'phpbb_gallery_albums'
CREATE TABLE phpbb_gallery_albums (
	album_id INTEGER NOT NULL,
	parent_id INTEGER DEFAULT 0 NOT NULL,
	left_id INTEGER DEFAULT 1 NOT NULL,
	right_id INTEGER DEFAULT 2 NOT NULL,
	album_parents BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	album_type INTEGER DEFAULT 1 NOT NULL,
	album_name VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_desc BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	album_desc_options INTEGER DEFAULT 7 NOT NULL,
	album_desc_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_desc_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_order INTEGER DEFAULT 0 NOT NULL,
	album_view_level INTEGER DEFAULT 1 NOT NULL,
	album_upload_level INTEGER DEFAULT 0 NOT NULL,
	album_rate_level INTEGER DEFAULT 0 NOT NULL,
	album_comment_level INTEGER DEFAULT 0 NOT NULL,
	album_edit_level INTEGER DEFAULT 0 NOT NULL,
	album_delete_level INTEGER DEFAULT 2 NOT NULL,
	album_view_groups VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_upload_groups VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_rate_groups VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_comment_groups VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_edit_groups VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_delete_groups VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_moderator_groups VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_approval INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_gallery_albums ADD PRIMARY KEY (album_id);;


CREATE GENERATOR phpbb_gallery_albums_gen;;
SET GENERATOR phpbb_gallery_albums_gen TO 0;;

CREATE TRIGGER t_phpbb_gallery_albums FOR phpbb_gallery_albums
BEFORE INSERT
AS
BEGIN
	NEW.album_id = GEN_ID(phpbb_gallery_albums_gen, 1);
END;;


# Table: 'phpbb_gallery_comments'
CREATE TABLE phpbb_gallery_comments (
	comment_id INTEGER NOT NULL,
	comment_image_id INTEGER NOT NULL,
	comment_user_id INTEGER DEFAULT 0 NOT NULL,
	comment_username VARCHAR(32) CHARACTER SET NONE DEFAULT '' NOT NULL,
	comment_user_ip VARCHAR(40) CHARACTER SET NONE DEFAULT '' NOT NULL,
	comment_time INTEGER DEFAULT 0 NOT NULL,
	comment BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	comment_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	comment_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	comment_edit_time INTEGER DEFAULT 0 NOT NULL,
	comment_edit_count INTEGER DEFAULT 0 NOT NULL,
	comment_edit_user_id INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_gallery_comments ADD PRIMARY KEY (comment_id);;

CREATE INDEX phpbb_gallery_comments_comment_image_id ON phpbb_gallery_comments(comment_image_id);;
CREATE INDEX phpbb_gallery_comments_comment_user_id ON phpbb_gallery_comments(comment_user_id);;
CREATE INDEX phpbb_gallery_comments_comment_user_ip ON phpbb_gallery_comments(comment_user_ip);;
CREATE INDEX phpbb_gallery_comments_comment_time ON phpbb_gallery_comments(comment_time);;

CREATE GENERATOR phpbb_gallery_comments_gen;;
SET GENERATOR phpbb_gallery_comments_gen TO 0;;

CREATE TRIGGER t_phpbb_gallery_comments FOR phpbb_gallery_comments
BEFORE INSERT
AS
BEGIN
	NEW.comment_id = GEN_ID(phpbb_gallery_comments_gen, 1);
END;;


# Table: 'phpbb_gallery_config'
CREATE TABLE phpbb_gallery_config (
	config_name VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	config_value VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL
);;

ALTER TABLE phpbb_gallery_config ADD PRIMARY KEY (config_name);;


# Table: 'phpbb_gallery_rates'
CREATE TABLE phpbb_gallery_rates (
	rate_image_id INTEGER NOT NULL,
	rate_user_id INTEGER DEFAULT 0 NOT NULL,
	rate_user_ip VARCHAR(40) CHARACTER SET NONE DEFAULT '' NOT NULL,
	rate_point INTEGER DEFAULT 0 NOT NULL
);;

CREATE INDEX phpbb_gallery_rates_rate_image_id ON phpbb_gallery_rates(rate_image_id);;
CREATE INDEX phpbb_gallery_rates_rate_user_id ON phpbb_gallery_rates(rate_user_id);;
CREATE INDEX phpbb_gallery_rates_rate_user_ip ON phpbb_gallery_rates(rate_user_ip);;
CREATE INDEX phpbb_gallery_rates_rate_point ON phpbb_gallery_rates(rate_point);;

CREATE GENERATOR phpbb_gallery_rates_gen;;
SET GENERATOR phpbb_gallery_rates_gen TO 0;;

CREATE TRIGGER t_phpbb_gallery_rates FOR phpbb_gallery_rates
BEFORE INSERT
AS
BEGIN
	NEW.rate_image_id = GEN_ID(phpbb_gallery_rates_gen, 1);
END;;


