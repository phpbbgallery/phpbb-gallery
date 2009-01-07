/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_favorites'
*/
CREATE TABLE phpbb_gallery_favorites (
	favorite_id number(8) NOT NULL,
	user_id number(8) DEFAULT '0' NOT NULL,
	image_id number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_gallery_favorites PRIMARY KEY (favorite_id)
)
/

CREATE INDEX phpbb_gallery_favorites_user_id ON phpbb_gallery_favorites (user_id)
/
CREATE INDEX phpbb_gallery_favorites_image_id ON phpbb_gallery_favorites (image_id)
/

CREATE SEQUENCE phpbb_gallery_favorites_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_gallery_favorites
BEFORE INSERT ON phpbb_gallery_favorites
FOR EACH ROW WHEN (
	new.favorite_id IS NULL OR new.favorite_id = 0
)
BEGIN
	SELECT phpbb_gallery_favorites_seq.nextval
	INTO :new.favorite_id
	FROM dual;
END;
/


