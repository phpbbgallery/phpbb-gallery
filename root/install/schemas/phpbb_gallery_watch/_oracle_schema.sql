/*

 $Id$

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


