/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_albums'
*/
CREATE TABLE phpbb_gallery_albums (
	album_id number(8) NOT NULL,
	parent_id number(8) DEFAULT '0' NOT NULL,
	left_id number(8) DEFAULT '1' NOT NULL,
	right_id number(8) DEFAULT '2' NOT NULL,
	album_parents clob DEFAULT '' ,
	album_type number(3) DEFAULT '1' NOT NULL,
	album_status number(1) DEFAULT '1' NOT NULL,
	album_contest number(8) DEFAULT '0' NOT NULL,
	album_name varchar2(255) DEFAULT '' ,
	album_desc clob DEFAULT '' ,
	album_desc_options number(3) DEFAULT '7' NOT NULL,
	album_desc_uid varchar2(8) DEFAULT '' ,
	album_desc_bitfield varchar2(255) DEFAULT '' ,
	album_user_id number(8) DEFAULT '0' NOT NULL,
	album_images number(8) DEFAULT '0' NOT NULL,
	album_images_real number(8) DEFAULT '0' NOT NULL,
	album_last_image_id number(8) DEFAULT '0' NOT NULL,
	album_image varchar2(255) DEFAULT '' ,
	album_last_image_time number(11) DEFAULT '0' NOT NULL,
	album_last_image_name varchar2(255) DEFAULT '' ,
	album_last_username varchar2(255) DEFAULT '' ,
	album_last_user_colour varchar2(6) DEFAULT '' ,
	album_last_user_id number(8) DEFAULT '0' NOT NULL,
	display_in_rrc number(1) DEFAULT '1' NOT NULL,
	display_on_index number(1) DEFAULT '1' NOT NULL,
	display_subalbum_list number(1) DEFAULT '1' NOT NULL,
	CONSTRAINT pk_phpbb_gallery_albums PRIMARY KEY (album_id)
)
/


CREATE SEQUENCE phpbb_gallery_albums_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_gallery_albums
BEFORE INSERT ON phpbb_gallery_albums
FOR EACH ROW WHEN (
	new.album_id IS NULL OR new.album_id = 0
)
BEGIN
	SELECT phpbb_gallery_albums_seq.nextval
	INTO :new.album_id
	FROM dual;
END;
/


