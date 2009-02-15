#
# $Id$
#


# Table: 'phpbb_gallery_albums'
CREATE TABLE phpbb_gallery_albums (
	album_id INTEGER NOT NULL,
	parent_id INTEGER DEFAULT 0 NOT NULL,
	left_id INTEGER DEFAULT 1 NOT NULL,
	right_id INTEGER DEFAULT 2 NOT NULL,
	album_parents BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	album_type INTEGER DEFAULT 1 NOT NULL,
	album_status INTEGER DEFAULT 1 NOT NULL,
	album_contest INTEGER DEFAULT 0 NOT NULL,
	album_name VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_desc BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	album_desc_options INTEGER DEFAULT 7 NOT NULL,
	album_desc_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_desc_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_user_id INTEGER DEFAULT 0 NOT NULL,
	album_images INTEGER DEFAULT 0 NOT NULL,
	album_images_real INTEGER DEFAULT 0 NOT NULL,
	album_last_image_id INTEGER DEFAULT 0 NOT NULL,
	album_image VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_last_image_time INTEGER DEFAULT 0 NOT NULL,
	album_last_image_name VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_last_username VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_last_user_colour VARCHAR(6) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_last_user_id INTEGER DEFAULT 0 NOT NULL,
	display_on_index INTEGER DEFAULT 1 NOT NULL,
	display_subalbum_list INTEGER DEFAULT 1 NOT NULL
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


