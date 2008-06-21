/*

 $Id: $

*/

/*

  This first section is optional, however its probably the best method

  of running phpBB on Oracle. If you already have a tablespace and user created

  for phpBB you can leave this section commented out!



  The first set of statements create a phpBB tablespace and a phpBB user,

  make sure you change the password of the phpBB user before you run this script!!

*/



/*

CREATE TABLESPACE "PHPBB"

	LOGGING 

	DATAFILE 'E:\ORACLE\ORADATA\LOCAL\PHPBB.ora' 

	SIZE 10M

	AUTOEXTEND ON NEXT 10M

	MAXSIZE 100M;



CREATE USER "PHPBB" 

	PROFILE "DEFAULT" 

	IDENTIFIED BY "phpbb_password" 

	DEFAULT TABLESPACE "PHPBB" 

	QUOTA UNLIMITED ON "PHPBB" 

	ACCOUNT UNLOCK;



GRANT ANALYZE ANY TO "PHPBB";

GRANT CREATE SEQUENCE TO "PHPBB";

GRANT CREATE SESSION TO "PHPBB";

GRANT CREATE TABLE TO "PHPBB";

GRANT CREATE TRIGGER TO "PHPBB";

GRANT CREATE VIEW TO "PHPBB";

GRANT "CONNECT" TO "PHPBB";



COMMIT;

DISCONNECT;



CONNECT phpbb/phpbb_password;

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
	display_on_index number(1) DEFAULT '1' NOT NULL,
	display_subalbum_list number(1) DEFAULT '1' NOT NULL,
	album_order number(8) DEFAULT '0' NOT NULL,
	album_view_level number(3) DEFAULT '1' NOT NULL,
	album_upload_level number(3) DEFAULT '0' NOT NULL,
	album_rate_level number(3) DEFAULT '0' NOT NULL,
	album_comment_level number(3) DEFAULT '0' NOT NULL,
	album_edit_level number(3) DEFAULT '0' NOT NULL,
	album_delete_level number(3) DEFAULT '2' NOT NULL,
	album_view_groups varchar2(255) DEFAULT '' ,
	album_upload_groups varchar2(255) DEFAULT '' ,
	album_rate_groups varchar2(255) DEFAULT '' ,
	album_comment_groups varchar2(255) DEFAULT '' ,
	album_edit_groups varchar2(255) DEFAULT '' ,
	album_delete_groups varchar2(255) DEFAULT '' ,
	album_moderator_groups varchar2(255) DEFAULT '' ,
	album_approval number(3) DEFAULT '0' NOT NULL,
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


