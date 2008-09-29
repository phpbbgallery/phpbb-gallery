#
# $Id$
#


# Table: 'phpbb_gallery_copyts_albums'
CREATE TABLE phpbb_gallery_copyts_albums (
	album_id INTEGER NOT NULL,
	parent_id INTEGER DEFAULT 0 NOT NULL,
	left_id INTEGER DEFAULT 1 NOT NULL,
	right_id INTEGER DEFAULT 2 NOT NULL,
	album_name VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	album_desc BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	album_user_id INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_gallery_copyts_albums ADD PRIMARY KEY (album_id);;


CREATE GENERATOR phpbb_gallery_copyts_albums_gen;;
SET GENERATOR phpbb_gallery_copyts_albums_gen TO 0;;

CREATE TRIGGER t_phpbb_gallery_copyts_albums FOR phpbb_gallery_copyts_albums
BEFORE INSERT
AS
BEGIN
	NEW.album_id = GEN_ID(phpbb_gallery_copyts_albums_gen, 1);
END;;


