#
# $Id$
#


# Table: 'phpbb_gallery_modscache'
CREATE TABLE phpbb_gallery_modscache (
	album_id INTEGER DEFAULT 0 NOT NULL,
	user_id INTEGER DEFAULT 0 NOT NULL,
	username VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	group_id INTEGER DEFAULT 0 NOT NULL,
	group_name VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	display_on_index INTEGER DEFAULT 1 NOT NULL
);;

CREATE INDEX phpbb_gallery_modscache_disp_idx ON phpbb_gallery_modscache(display_on_index);;
CREATE INDEX phpbb_gallery_modscache_album_id ON phpbb_gallery_modscache(album_id);;

