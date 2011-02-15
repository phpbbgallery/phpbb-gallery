#
# $Id$
#

# Table: 'phpbb_gallery_users'
CREATE TABLE phpbb_gallery_users (
	user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	watch_own int(3) UNSIGNED DEFAULT '0' NOT NULL,
	watch_favo int(3) UNSIGNED DEFAULT '0' NOT NULL,
	watch_com int(3) UNSIGNED DEFAULT '0' NOT NULL,
	user_images mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	personal_album_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	user_lastmark int(11) UNSIGNED DEFAULT '0' NOT NULL,
	user_last_update int(11) UNSIGNED DEFAULT '0' NOT NULL,
	user_viewexif int(1) UNSIGNED DEFAULT '0' NOT NULL,
	user_permissions mediumblob NOT NULL,
	PRIMARY KEY (user_id)
);


