/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_roles'
*/
CREATE TABLE phpbb_gallery_roles (
	role_id number(8) NOT NULL,
	a_list number(3) DEFAULT '0' NOT NULL,
	i_view number(3) DEFAULT '0' NOT NULL,
	i_watermark number(3) DEFAULT '0' NOT NULL,
	i_upload number(3) DEFAULT '0' NOT NULL,
	i_edit number(3) DEFAULT '0' NOT NULL,
	i_delete number(3) DEFAULT '0' NOT NULL,
	i_rate number(3) DEFAULT '0' NOT NULL,
	i_approve number(3) DEFAULT '0' NOT NULL,
	i_lock number(3) DEFAULT '0' NOT NULL,
	i_report number(3) DEFAULT '0' NOT NULL,
	i_count number(8) DEFAULT '0' NOT NULL,
	i_unlimited number(3) DEFAULT '0' NOT NULL,
	c_read number(3) DEFAULT '0' NOT NULL,
	c_post number(3) DEFAULT '0' NOT NULL,
	c_edit number(3) DEFAULT '0' NOT NULL,
	c_delete number(3) DEFAULT '0' NOT NULL,
	m_comments number(3) DEFAULT '0' NOT NULL,
	m_delete number(3) DEFAULT '0' NOT NULL,
	m_edit number(3) DEFAULT '0' NOT NULL,
	m_move number(3) DEFAULT '0' NOT NULL,
	m_report number(3) DEFAULT '0' NOT NULL,
	m_status number(3) DEFAULT '0' NOT NULL,
	album_count number(8) DEFAULT '0' NOT NULL,
	album_unlimited number(3) DEFAULT '0' NOT NULL,
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


