/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_contests'
*/
CREATE TABLE phpbb_gallery_contests (
	contest_id number(8) NOT NULL,
	contest_album_id number(8) DEFAULT '0' NOT NULL,
	contest_start number(11) DEFAULT '0' NOT NULL,
	contest_rating number(11) DEFAULT '0' NOT NULL,
	contest_end number(11) DEFAULT '0' NOT NULL,
	contest_marked number(1) DEFAULT '0' NOT NULL,
	contest_first number(8) DEFAULT '0' NOT NULL,
	contest_second number(8) DEFAULT '0' NOT NULL,
	contest_third number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_gallery_contests PRIMARY KEY (contest_id)
)
/


CREATE SEQUENCE phpbb_gallery_contests_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_gallery_contests
BEFORE INSERT ON phpbb_gallery_contests
FOR EACH ROW WHEN (
	new.contest_id IS NULL OR new.contest_id = 0
)
BEGIN
	SELECT phpbb_gallery_contests_seq.nextval
	INTO :new.contest_id
	FROM dual;
END;
/


