#
# $Id$
#

BEGIN TRANSACTION;

# Table: 'phpbb_gallery_watch'
CREATE TABLE phpbb_gallery_watch (
	watch_id INTEGER PRIMARY KEY NOT NULL ,
	album_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	user_id INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_gallery_watch_user_id ON phpbb_gallery_watch (user_id);
CREATE INDEX phpbb_gallery_watch_image_id ON phpbb_gallery_watch (image_id);
CREATE INDEX phpbb_gallery_watch_album_id ON phpbb_gallery_watch (album_id);


COMMIT;