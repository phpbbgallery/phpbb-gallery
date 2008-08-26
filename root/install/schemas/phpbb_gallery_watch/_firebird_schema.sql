#
# $Id$
#


# Table: 'phpbb_gallery_watch'
CREATE TABLE phpbb_gallery_watch (
	watch_id INTEGER NOT NULL,
	album_id INTEGER DEFAULT 0 NOT NULL,
	image_id INTEGER DEFAULT 0 NOT NULL,
	user_id INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_gallery_watch ADD PRIMARY KEY (watch_id);;

CREATE INDEX phpbb_gallery_watch_user_id ON phpbb_gallery_watch(user_id);;
CREATE INDEX phpbb_gallery_watch_image_id ON phpbb_gallery_watch(image_id);;
CREATE INDEX phpbb_gallery_watch_album_id ON phpbb_gallery_watch(album_id);;

CREATE GENERATOR phpbb_gallery_watch_gen;;
SET GENERATOR phpbb_gallery_watch_gen TO 0;;

CREATE TRIGGER t_phpbb_gallery_watch FOR phpbb_gallery_watch
BEFORE INSERT
AS
BEGIN
	NEW.watch_id = GEN_ID(phpbb_gallery_watch_gen, 1);
END;;


