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
	Table: 'phpbb_gallery_comments'
*/
CREATE TABLE phpbb_gallery_comments (
	comment_id number(8) NOT NULL,
	comment_image_id number(8) NOT NULL,
	comment_user_id number(8) DEFAULT '0' NOT NULL,
	comment_username varchar2(32) DEFAULT '' ,
	comment_user_colour varchar2(6) DEFAULT '' ,
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


