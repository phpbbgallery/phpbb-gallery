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
	Table: 'phpbb_gallery_images'
*/
CREATE TABLE phpbb_gallery_images (
	image_id number(8) NOT NULL,
	image_filename varchar2(255) DEFAULT '' ,
	image_thumbnail varchar2(255) DEFAULT '' ,
	image_name varchar2(255) DEFAULT '' ,
	image_desc clob DEFAULT '' ,
	image_desc_uid varchar2(8) DEFAULT '' ,
	image_desc_bitfield varchar2(255) DEFAULT '' ,
	image_user_id number(8) DEFAULT '0' NOT NULL,
	image_username varchar2(32) DEFAULT '' ,
	image_user_colour varchar2(6) DEFAULT '' ,
	image_user_ip varchar2(40) DEFAULT '' ,
	image_time number(11) DEFAULT '0' NOT NULL,
	image_album_id number(8) DEFAULT '0' NOT NULL,
	image_view_count number(11) DEFAULT '0' NOT NULL,
	image_lock number(3) DEFAULT '0' NOT NULL,
	image_approval number(3) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_gallery_images PRIMARY KEY (image_id)
)
/

CREATE INDEX phpbb_gallery_images_image_album_id ON phpbb_gallery_images (image_album_id)
/
CREATE INDEX phpbb_gallery_images_image_user_id ON phpbb_gallery_images (image_user_id)
/
CREATE INDEX phpbb_gallery_images_image_time ON phpbb_gallery_images (image_time)
/

CREATE SEQUENCE phpbb_gallery_images_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_gallery_images
BEFORE INSERT ON phpbb_gallery_images
FOR EACH ROW WHEN (
	new.image_id IS NULL OR new.image_id = 0
)
BEGIN
	SELECT phpbb_gallery_images_seq.nextval
	INTO :new.image_id
	FROM dual;
END;
/


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


/*
	Table: 'phpbb_gallery_comments'
*/
CREATE TABLE phpbb_gallery_comments (
	comment_id number(8) NOT NULL,
	comment_image_id number(8) NOT NULL,
	comment_user_id number(8) DEFAULT '0' NOT NULL,
	comment_username varchar2(32) DEFAULT '' ,
	comment_user_ip varchar2(40) DEFAULT '' ,
	comment_time number(11) DEFAULT '0' NOT NULL,
	comment clob DEFAULT '' ,
	comment_uid varchar2(8) DEFAULT '' ,
	comment_bitfield varchar2(255) DEFAULT '' ,
	comment_edit_time number(11) DEFAULT '0' NOT NULL,
	comment_edit_count number(4) DEFAULT '0' NOT NULL,
	comment_edit_user_id number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_gallery_comments PRIMARY KEY (comment_id)
)
/

CREATE INDEX phpbb_gallery_comments_comment_image_id ON phpbb_gallery_comments (comment_image_id)
/
CREATE INDEX phpbb_gallery_comments_comment_user_id ON phpbb_gallery_comments (comment_user_id)
/
CREATE INDEX phpbb_gallery_comments_comment_user_ip ON phpbb_gallery_comments (comment_user_ip)
/
CREATE INDEX phpbb_gallery_comments_comment_time ON phpbb_gallery_comments (comment_time)
/

CREATE SEQUENCE phpbb_gallery_comments_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_gallery_comments
BEFORE INSERT ON phpbb_gallery_comments
FOR EACH ROW WHEN (
	new.comment_id IS NULL OR new.comment_id = 0
)
BEGIN
	SELECT phpbb_gallery_comments_seq.nextval
	INTO :new.comment_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_gallery_config'
*/
CREATE TABLE phpbb_gallery_config (
	config_name varchar2(255) DEFAULT '' ,
	config_value varchar2(255) DEFAULT '' ,
	CONSTRAINT pk_phpbb_gallery_config PRIMARY KEY (config_name)
)
/


/*
	Table: 'phpbb_gallery_rates'
*/
CREATE TABLE phpbb_gallery_rates (
	rate_image_id number(8) NOT NULL,
	rate_user_id number(8) DEFAULT '0' NOT NULL,
	rate_user_ip varchar2(40) DEFAULT '' ,
	rate_point number(3) DEFAULT '0' NOT NULL
)
/

CREATE INDEX phpbb_gallery_rates_rate_image_id ON phpbb_gallery_rates (rate_image_id)
/
CREATE INDEX phpbb_gallery_rates_rate_user_id ON phpbb_gallery_rates (rate_user_id)
/
CREATE INDEX phpbb_gallery_rates_rate_user_ip ON phpbb_gallery_rates (rate_user_ip)
/
CREATE INDEX phpbb_gallery_rates_rate_point ON phpbb_gallery_rates (rate_point)
/

CREATE SEQUENCE phpbb_gallery_rates_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_gallery_rates
BEFORE INSERT ON phpbb_gallery_rates
FOR EACH ROW WHEN (
	new.rate_image_id IS NULL OR new.rate_image_id = 0
)
BEGIN
	SELECT phpbb_gallery_rates_seq.nextval
	INTO :new.rate_image_id
	FROM dual;
END;
/


