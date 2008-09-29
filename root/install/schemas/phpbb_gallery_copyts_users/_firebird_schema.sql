#
# $Id$
#


# Table: 'phpbb_gallery_copyts_users'
CREATE TABLE phpbb_gallery_copyts_users (
	user_id INTEGER DEFAULT 0 NOT NULL,
	personal_album_id INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_gallery_copyts_users ADD PRIMARY KEY (user_id);;


