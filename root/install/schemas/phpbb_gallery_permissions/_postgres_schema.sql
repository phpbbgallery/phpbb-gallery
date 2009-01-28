/*

 $Id$

*/

BEGIN;


/*
	Table: 'phpbb_gallery_permissions'
*/
CREATE SEQUENCE phpbb_gallery_permissions_seq;

CREATE TABLE phpbb_gallery_permissions (
	perm_id INT4 DEFAULT nextval('phpbb_gallery_permissions_seq'),
	perm_role_id INT4 DEFAULT '0' NOT NULL CHECK (perm_role_id >= 0),
	perm_album_id INT4 DEFAULT '0' NOT NULL CHECK (perm_album_id >= 0),
	perm_user_id INT4 DEFAULT '0' NOT NULL CHECK (perm_user_id >= 0),
	perm_group_id INT4 DEFAULT '0' NOT NULL CHECK (perm_group_id >= 0),
	perm_system INT4 DEFAULT '0' NOT NULL,
	PRIMARY KEY (perm_id)
);



COMMIT;