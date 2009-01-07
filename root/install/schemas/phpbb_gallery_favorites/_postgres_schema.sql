/*

 $Id$

*/

BEGIN;


/*
	Table: 'phpbb_gallery_favorites'
*/
CREATE SEQUENCE phpbb_gallery_favorites_seq;

CREATE TABLE phpbb_gallery_favorites (
	favorite_id INT4 DEFAULT nextval('phpbb_gallery_favorites_seq'),
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	image_id INT4 DEFAULT '0' NOT NULL CHECK (image_id >= 0),
	PRIMARY KEY (favorite_id)
);

CREATE INDEX phpbb_gallery_favorites_user_id ON phpbb_gallery_favorites (user_id);
CREATE INDEX phpbb_gallery_favorites_image_id ON phpbb_gallery_favorites (image_id);


COMMIT;