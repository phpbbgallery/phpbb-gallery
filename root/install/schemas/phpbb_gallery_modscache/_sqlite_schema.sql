#
# $Id$
#

BEGIN TRANSACTION;

# Table: 'phpbb_gallery_modscache'
CREATE TABLE phpbb_gallery_modscache (
	album_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	username varchar(255) NOT NULL DEFAULT '',
	group_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	group_name varchar(255) NOT NULL DEFAULT '',
	display_on_index tinyint(1) NOT NULL DEFAULT '1'
);

CREATE INDEX phpbb_gallery_modscache_disp_idx ON phpbb_gallery_modscache (display_on_index);
CREATE INDEX phpbb_gallery_modscache_album_id ON phpbb_gallery_modscache (album_id);


COMMIT;