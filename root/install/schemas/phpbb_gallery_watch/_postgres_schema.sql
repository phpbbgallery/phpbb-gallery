/*

 $Id$

*/

BEGIN;


/*
	Table: 'phpbb_gallery_watch'
*/
CREATE SEQUENCE phpbb_gallery_watch_seq;

CREATE TABLE phpbb_gallery_watch (
	watch_id INT4 DEFAULT nextval('phpbb_gallery_watch_seq'),
	album_id INT4 DEFAULT '0' NOT NULL CHECK (album_id >= 0),
	image_id INT4 DEFAULT '0' NOT NULL CHECK (image_id >= 0),
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	PRIMARY KEY (watch_id)
);

CREATE INDEX phpbb_gallery_watch_user_id ON phpbb_gallery_watch (user_id);
CREATE INDEX phpbb_gallery_watch_image_id ON phpbb_gallery_watch (image_id);
CREATE INDEX phpbb_gallery_watch_album_id ON phpbb_gallery_watch (album_id);


COMMIT;