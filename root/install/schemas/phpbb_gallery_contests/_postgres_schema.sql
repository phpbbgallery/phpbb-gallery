/*

 $Id$

*/

BEGIN;


/*
	Table: 'phpbb_gallery_contests'
*/
CREATE SEQUENCE phpbb_gallery_contests_seq;

CREATE TABLE phpbb_gallery_contests (
	contest_id INT4 DEFAULT nextval('phpbb_gallery_contests_seq'),
	contest_album_id INT4 DEFAULT '0' NOT NULL CHECK (contest_album_id >= 0),
	contest_start INT4 DEFAULT '0' NOT NULL CHECK (contest_start >= 0),
	contest_rating INT4 DEFAULT '0' NOT NULL CHECK (contest_rating >= 0),
	contest_end INT4 DEFAULT '0' NOT NULL CHECK (contest_end >= 0),
	contest_marked INT2 DEFAULT '0' NOT NULL,
	contest_first INT4 DEFAULT '0' NOT NULL CHECK (contest_first >= 0),
	contest_second INT4 DEFAULT '0' NOT NULL CHECK (contest_second >= 0),
	contest_third INT4 DEFAULT '0' NOT NULL CHECK (contest_third >= 0),
	PRIMARY KEY (contest_id)
);



COMMIT;