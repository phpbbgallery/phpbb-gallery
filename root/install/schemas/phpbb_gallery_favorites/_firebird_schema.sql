#
# $Id$
#


# Table: 'phpbb_gallery_favorites'
CREATE TABLE phpbb_gallery_favorites (
	favorite_id INTEGER NOT NULL,
	user_id INTEGER DEFAULT 0 NOT NULL,
	image_id INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_gallery_favorites ADD PRIMARY KEY (favorite_id);;

CREATE INDEX phpbb_gallery_favorites_user_id ON phpbb_gallery_favorites(user_id);;
CREATE INDEX phpbb_gallery_favorites_image_id ON phpbb_gallery_favorites(image_id);;

CREATE GENERATOR phpbb_gallery_favorites_gen;;
SET GENERATOR phpbb_gallery_favorites_gen TO 0;;

CREATE TRIGGER t_phpbb_gallery_favorites FOR phpbb_gallery_favorites
BEFORE INSERT
AS
BEGIN
	NEW.favorite_id = GEN_ID(phpbb_gallery_favorites_gen, 1);
END;;


