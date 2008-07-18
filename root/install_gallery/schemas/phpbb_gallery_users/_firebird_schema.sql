#
# $Id: $
#


# Table: 'phpbb_gallery_users'
CREATE TABLE phpbb_gallery_users (
	user_id INTEGER DEFAULT 0 NOT NULL,
	watch_own INTEGER DEFAULT 0 NOT NULL,
	watch_favo INTEGER DEFAULT 0 NOT NULL,
	watch_com INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_gallery_users ADD PRIMARY KEY (user_id);;


