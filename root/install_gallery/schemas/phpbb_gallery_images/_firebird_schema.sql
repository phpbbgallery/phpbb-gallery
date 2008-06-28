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
	image_status INTEGER DEFAULT 0 NOT NULL,
	image_rates INTEGER DEFAULT 0 NOT NULL,
	image_rate_points INTEGER DEFAULT 0 NOT NULL,
	image_rate_avg INTEGER DEFAULT 0 NOT NULL,
	image_comments INTEGER DEFAULT 0 NOT NULL,
	image_last_comment INTEGER DEFAULT 0 NOT NULL
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


