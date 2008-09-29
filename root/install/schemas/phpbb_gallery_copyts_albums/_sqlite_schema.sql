#
# $Id$
#

BEGIN TRANSACTION;

# Table: 'phpbb_gallery_copyts_albums'
CREATE TABLE phpbb_gallery_copyts_albums (
	album_id INTEGER PRIMARY KEY NOT NULL ,
	parent_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	left_id INTEGER UNSIGNED NOT NULL DEFAULT '1',
	right_id INTEGER UNSIGNED NOT NULL DEFAULT '2',
	album_name varchar(255) NOT NULL DEFAULT '',
	album_desc mediumtext(16777215) NOT NULL DEFAULT '',
	album_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0'
);



COMMIT;