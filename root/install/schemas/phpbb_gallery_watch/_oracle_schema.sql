/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_watch'
*/
CREATE TABLE phpbb_gallery_watch (
	watch_id number(8) NOT NULL,
	album_id number(8) DEFAULT '0' NOT NULL,
	image_id number(8) DEFAULT '0' NOT NULL,
	user_id number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_gallery_watch PRIMARY KEY (watch_id)
)
/

CREATE INDEX phpbb_gallery_watch_user_id ON phpbb_gallery_watch (user_id)
/
CREATE INDEX phpbb_gallery_watch_image_id ON phpbb_gallery_watch (image_id)
/
CREATE INDEX phpbb_gallery_watch_album_id ON phpbb_gallery_watch (album_id)
/

CREATE SEQUENCE phpbb_gallery_watch_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_gallery_watch
BEFORE INSERT ON phpbb_gallery_watch
FOR EACH ROW WHEN (
	new.watch_id IS NULL OR new.watch_id = 0
)
BEGIN
	SELECT phpbb_gallery_watch_seq.nextval
	INTO :new.watch_id
	FROM dual;
END;
/


