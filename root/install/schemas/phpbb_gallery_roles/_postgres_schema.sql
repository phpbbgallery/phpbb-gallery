/*

 $Id$

*/

BEGIN;


/*
	Table: 'phpbb_gallery_roles'
*/
CREATE SEQUENCE phpbb_gallery_roles_seq;

CREATE TABLE phpbb_gallery_roles (
	role_id INT4 DEFAULT nextval('phpbb_gallery_roles_seq'),
	a_list INT4 DEFAULT '0' NOT NULL CHECK (a_list >= 0),
	i_view INT4 DEFAULT '0' NOT NULL CHECK (i_view >= 0),
	i_watermark INT4 DEFAULT '0' NOT NULL CHECK (i_watermark >= 0),
	i_upload INT4 DEFAULT '0' NOT NULL CHECK (i_upload >= 0),
	i_edit INT4 DEFAULT '0' NOT NULL CHECK (i_edit >= 0),
	i_delete INT4 DEFAULT '0' NOT NULL CHECK (i_delete >= 0),
	i_rate INT4 DEFAULT '0' NOT NULL CHECK (i_rate >= 0),
	i_approve INT4 DEFAULT '0' NOT NULL CHECK (i_approve >= 0),
	i_lock INT4 DEFAULT '0' NOT NULL CHECK (i_lock >= 0),
	i_report INT4 DEFAULT '0' NOT NULL CHECK (i_report >= 0),
	i_count INT4 DEFAULT '0' NOT NULL CHECK (i_count >= 0),
	i_unlimited INT4 DEFAULT '0' NOT NULL CHECK (i_unlimited >= 0),
	c_read INT4 DEFAULT '0' NOT NULL CHECK (c_read >= 0),
	c_post INT4 DEFAULT '0' NOT NULL CHECK (c_post >= 0),
	c_edit INT4 DEFAULT '0' NOT NULL CHECK (c_edit >= 0),
	c_delete INT4 DEFAULT '0' NOT NULL CHECK (c_delete >= 0),
	m_comments INT4 DEFAULT '0' NOT NULL CHECK (m_comments >= 0),
	m_delete INT4 DEFAULT '0' NOT NULL CHECK (m_delete >= 0),
	m_edit INT4 DEFAULT '0' NOT NULL CHECK (m_edit >= 0),
	m_move INT4 DEFAULT '0' NOT NULL CHECK (m_move >= 0),
	m_report INT4 DEFAULT '0' NOT NULL CHECK (m_report >= 0),
	m_status INT4 DEFAULT '0' NOT NULL CHECK (m_status >= 0),
	album_count INT4 DEFAULT '0' NOT NULL CHECK (album_count >= 0),
	album_unlimited INT4 DEFAULT '0' NOT NULL CHECK (album_unlimited >= 0),
	PRIMARY KEY (role_id)
);



COMMIT;