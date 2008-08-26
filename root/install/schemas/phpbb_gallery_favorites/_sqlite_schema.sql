#
# $Id$
#

BEGIN TRANSACTION;

# Table: 'phpbb_gallery_favorites'
CREATE TABLE phpbb_gallery_favorites (
	favorite_id INTEGER PRIMARY KEY NOT NULL ,
	user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_id INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_gallery_favorites_user_id ON phpbb_gallery_favorites (user_id);
CREATE INDEX phpbb_gallery_favorites_image_id ON phpbb_gallery_favorites (image_id);


COMMIT;