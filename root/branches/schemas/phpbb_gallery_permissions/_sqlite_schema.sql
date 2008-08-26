#
# $Id$
#

BEGIN TRANSACTION;

# Table: 'phpbb_gallery_permissions'
CREATE TABLE phpbb_gallery_permissions (
	perm_id INTEGER PRIMARY KEY NOT NULL ,
	perm_role_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	perm_album_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	perm_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	perm_group_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	perm_system INTEGER UNSIGNED NOT NULL DEFAULT '0'
);



COMMIT;