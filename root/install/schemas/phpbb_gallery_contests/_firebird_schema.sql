#
# $Id$
#


# Table: 'phpbb_gallery_contests'
CREATE TABLE phpbb_gallery_contests (
	contest_id INTEGER NOT NULL,
	contest_album_id INTEGER DEFAULT 0 NOT NULL,
	contest_start INTEGER DEFAULT 0 NOT NULL,
	contest_rating INTEGER DEFAULT 0 NOT NULL,
	contest_end INTEGER DEFAULT 0 NOT NULL,
	contest_marked INTEGER DEFAULT 0 NOT NULL,
	contest_first INTEGER DEFAULT 0 NOT NULL,
	contest_second INTEGER DEFAULT 0 NOT NULL,
	contest_third INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_gallery_contests ADD PRIMARY KEY (contest_id);;


CREATE GENERATOR phpbb_gallery_contests_gen;;
SET GENERATOR phpbb_gallery_contests_gen TO 0;;

CREATE TRIGGER t_phpbb_gallery_contests FOR phpbb_gallery_contests
BEFORE INSERT
AS
BEGIN
	NEW.contest_id = GEN_ID(phpbb_gallery_contests_gen, 1);
END;;


