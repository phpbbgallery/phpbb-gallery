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
	Table: 'phpbb_gallery_roles'
*/
CREATE TABLE phpbb_gallery_roles (
	role_id number(8) NOT NULL,
	i_view number(3) DEFAULT '0' NOT NULL,
	i_upload number(3) DEFAULT '0' NOT NULL,
	i_edit number(3) DEFAULT '0' NOT NULL,
	i_delete number(3) DEFAULT '0' NOT NULL,
	i_rate number(3) DEFAULT '0' NOT NULL,
	i_approve number(3) DEFAULT '0' NOT NULL,
	i_lock number(3) DEFAULT '0' NOT NULL,
	i_report number(3) DEFAULT '0' NOT NULL,
	i_count number(8) DEFAULT '0' NOT NULL,
	c_post number(3) DEFAULT '0' NOT NULL,
	c_edit number(3) DEFAULT '0' NOT NULL,
	c_delete number(3) DEFAULT '0' NOT NULL,
	a_moderate number(3) DEFAULT '0' NOT NULL,
	album_count number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_gallery_roles PRIMARY KEY (role_id)
)
/


CREATE SEQUENCE phpbb_gallery_roles_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_gallery_roles
BEFORE INSERT ON phpbb_gallery_roles
FOR EACH ROW WHEN (
	new.role_id IS NULL OR new.role_id = 0
)
BEGIN
	SELECT phpbb_gallery_roles_seq.nextval
	INTO :new.role_id
	FROM dual;
END;
/


