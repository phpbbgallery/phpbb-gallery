#
# $Id$
#


# Table: 'phpbb_gallery_permissions'
CREATE TABLE phpbb_gallery_permissions (
	perm_id INTEGER NOT NULL,
	perm_role_id INTEGER DEFAULT 0 NOT NULL,
	perm_album_id INTEGER DEFAULT 0 NOT NULL,
	perm_user_id INTEGER DEFAULT 0 NOT NULL,
	perm_group_id INTEGER DEFAULT 0 NOT NULL,
	perm_system INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_gallery_permissions ADD PRIMARY KEY (perm_id);;


CREATE GENERATOR phpbb_gallery_permissions_gen;;
SET GENERATOR phpbb_gallery_permissions_gen TO 0;;

CREATE TRIGGER t_phpbb_gallery_permissions FOR phpbb_gallery_permissions
BEFORE INSERT
AS
BEGIN
	NEW.perm_id = GEN_ID(phpbb_gallery_permissions_gen, 1);
END;;


