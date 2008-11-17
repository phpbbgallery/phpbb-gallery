#
# $Id$
#


# Table: 'phpbb_gallery_roles'
CREATE TABLE phpbb_gallery_roles (
	role_id INTEGER NOT NULL,
	a_list INTEGER DEFAULT 0 NOT NULL,
	a_moderate INTEGER DEFAULT 0 NOT NULL,
	i_view INTEGER DEFAULT 0 NOT NULL,
	i_upload INTEGER DEFAULT 0 NOT NULL,
	i_edit INTEGER DEFAULT 0 NOT NULL,
	i_delete INTEGER DEFAULT 0 NOT NULL,
	i_rate INTEGER DEFAULT 0 NOT NULL,
	i_approve INTEGER DEFAULT 0 NOT NULL,
	i_lock INTEGER DEFAULT 0 NOT NULL,
	i_report INTEGER DEFAULT 0 NOT NULL,
	i_count INTEGER DEFAULT 0 NOT NULL,
	c_read INTEGER DEFAULT 0 NOT NULL,
	c_post INTEGER DEFAULT 0 NOT NULL,
	c_edit INTEGER DEFAULT 0 NOT NULL,
	c_delete INTEGER DEFAULT 0 NOT NULL,
	m_comments INTEGER DEFAULT 0 NOT NULL,
	m_delete INTEGER DEFAULT 0 NOT NULL,
	m_edit INTEGER DEFAULT 0 NOT NULL,
	m_move INTEGER DEFAULT 0 NOT NULL,
	m_report INTEGER DEFAULT 0 NOT NULL,
	m_status INTEGER DEFAULT 0 NOT NULL,
	album_count INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_gallery_roles ADD PRIMARY KEY (role_id);;


CREATE GENERATOR phpbb_gallery_roles_gen;;
SET GENERATOR phpbb_gallery_roles_gen TO 0;;

CREATE TRIGGER t_phpbb_gallery_roles FOR phpbb_gallery_roles
BEFORE INSERT
AS
BEGIN
	NEW.role_id = GEN_ID(phpbb_gallery_roles_gen, 1);
END;;


