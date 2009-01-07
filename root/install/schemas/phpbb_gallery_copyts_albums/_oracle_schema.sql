/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_copyts_albums'
*/
CREATE TABLE phpbb_gallery_copyts_albums (
	album_id number(8) NOT NULL,
	parent_id number(8) DEFAULT '0' NOT NULL,
	left_id number(8) DEFAULT '1' NOT NULL,
	right_id number(8) DEFAULT '2' NOT NULL,
	album_name varchar2(255) DEFAULT '' ,
	album_desc clob DEFAULT '' ,
	album_user_id number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_gallery_copyts_albums PRIMARY KEY (album_id)
)
/


CREATE SEQUENCE phpbb_gallery_copyts_albums_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_gallery_copyts_albums
BEFORE INSERT ON phpbb_gallery_copyts_albums
FOR EACH ROW WHEN (
	new.album_id IS NULL OR new.album_id = 0
)
BEGIN
	SELECT phpbb_gallery_copyts_albums_seq.nextval
	INTO :new.album_id
	FROM dual;
END;
/


