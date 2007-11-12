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
	Table: 'phpbb_album'
*/
CREATE TABLE phpbb_album (
	pic_id number(8) NOT NULL,
	pic_filename varchar2(255) DEFAULT '' ,
	pic_thumbnail varchar2(255) DEFAULT '' ,
	pic_title varchar2(255) DEFAULT '' ,
	pic_desc clob DEFAULT '' ,
	pic_desc_bbcode_uid varchar2(8) DEFAULT '' ,
	pic_desc_bbcode_bitfield varchar2(255) DEFAULT '' ,
	pic_user_id number(8) DEFAULT '0' NOT NULL,
	pic_username varchar2(32) DEFAULT '' ,
	pic_user_ip varchar2(40) DEFAULT '' ,
	pic_time number(11) DEFAULT '0' NOT NULL,
	pic_cat_id number(8) DEFAULT '0' NOT NULL,
	pic_view_count number(11) DEFAULT '0' NOT NULL,
	pic_lock number(1) DEFAULT '0' NOT NULL,
	pic_approval number(1) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_album PRIMARY KEY (pic_id)
)
/


CREATE SEQUENCE phpbb_album_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_album
BEFORE INSERT ON phpbb_album
FOR EACH ROW WHEN (
	new.pic_id IS NULL OR new.pic_id = 0
)
BEGIN
	SELECT phpbb_album_seq.nextval
	INTO :new.pic_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_album_cat'
*/
CREATE TABLE phpbb_album_cat (
	cat_id number(8) NOT NULL,
	cat_title varchar2(255) DEFAULT '' ,
	cat_desc clob DEFAULT '' ,
	cat_desc_bbcode_uid varchar2(8) DEFAULT '' ,
	cat_desc_bbcode_bitfield varchar2(255) DEFAULT '' ,
	cat_order number(8) DEFAULT '0' NOT NULL,
	cat_view_level number(1) DEFAULT '1' NOT NULL,
	cat_upload_level number(1) DEFAULT '0' NOT NULL,
	cat_rate_level number(1) DEFAULT '0' NOT NULL,
	cat_comment_level number(1) DEFAULT '0' NOT NULL,
	cat_edit_level number(1) DEFAULT '0' NOT NULL,
	cat_delete_level number(1) DEFAULT '2' NOT NULL,
	cat_view_groups varchar2(255) DEFAULT '' ,
	cat_upload_groups varchar2(255) DEFAULT '' ,
	cat_rate_groups varchar2(255) DEFAULT '' ,
	cat_comment_groups varchar2(255) DEFAULT '' ,
	cat_edit_groups varchar2(255) DEFAULT '' ,
	cat_delete_groups varchar2(255) DEFAULT '' ,
	cat_moderator_groups varchar2(255) DEFAULT '' ,
	cat_approval number(1) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_album_cat PRIMARY KEY (cat_id)
)
/


CREATE SEQUENCE phpbb_album_cat_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_album_cat
BEFORE INSERT ON phpbb_album_cat
FOR EACH ROW WHEN (
	new.cat_id IS NULL OR new.cat_id = 0
)
BEGIN
	SELECT phpbb_album_cat_seq.nextval
	INTO :new.cat_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_album_comment'
*/
CREATE TABLE phpbb_album_comment (
	comment_id number(8) NOT NULL,
	comment_pic_id number(8) NOT NULL,
	comment_user_id number(8) DEFAULT '0' NOT NULL,
	comment_username varchar2(32) DEFAULT '' ,
	comment_user_ip varchar2(40) DEFAULT '' ,
	comment_time number(11) DEFAULT '0' NOT NULL,
	comment_text clob DEFAULT '' ,
	comment_text_bbcode_uid varchar2(8) DEFAULT '' ,
	comment_text_bbcode_bitfield varchar2(255) DEFAULT '' ,
	comment_edit_time number(11) DEFAULT '0' NOT NULL,
	comment_edit_count number(4) DEFAULT '0' NOT NULL,
	comment_edit_user_id number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_album_comment PRIMARY KEY (comment_id)
)
/


CREATE SEQUENCE phpbb_album_comment_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_album_comment
BEFORE INSERT ON phpbb_album_comment
FOR EACH ROW WHEN (
	new.comment_id IS NULL OR new.comment_id = 0
)
BEGIN
	SELECT phpbb_album_comment_seq.nextval
	INTO :new.comment_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_album_config'
*/
CREATE TABLE phpbb_album_config (
	config_name varchar2(255) DEFAULT '' ,
	config_value varchar2(255) DEFAULT '' ,
	CONSTRAINT pk_phpbb_album_config PRIMARY KEY (config_name)
)
/


/*
	Table: 'phpbb_album_rate'
*/
CREATE TABLE phpbb_album_rate (
	rate_pic_id number(8) NOT NULL,
	rate_user_id number(8) DEFAULT '0' NOT NULL,
	rate_user_ip varchar2(40) DEFAULT '' ,
	rate_point number(1) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_album_rate PRIMARY KEY (rate_pic_id)
)
/


CREATE SEQUENCE phpbb_album_rate_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_album_rate
BEFORE INSERT ON phpbb_album_rate
FOR EACH ROW WHEN (
	new.rate_pic_id IS NULL OR new.rate_pic_id = 0
)
BEGIN
	SELECT phpbb_album_rate_seq.nextval
	INTO :new.rate_pic_id
	FROM dual;
END;
/


