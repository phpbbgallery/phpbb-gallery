/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_permissions'
*/
CREATE TABLE phpbb_gallery_permissions (
	perm_id number(8) NOT NULL,
	perm_role_id number(8) DEFAULT '0' NOT NULL,
	perm_album_id number(8) DEFAULT '0' NOT NULL,
	perm_user_id number(8) DEFAULT '0' NOT NULL,
	perm_group_id number(8) DEFAULT '0' NOT NULL,
	perm_system number(3) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_gallery_permissions PRIMARY KEY (perm_id)
)
/


CREATE SEQUENCE phpbb_gallery_permissions_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_gallery_permissions
BEFORE INSERT ON phpbb_gallery_permissions
FOR EACH ROW WHEN (
	new.perm_id IS NULL OR new.perm_id = 0
)
BEGIN
	SELECT phpbb_gallery_permissions_seq.nextval
	INTO :new.perm_id
	FROM dual;
END;
/


