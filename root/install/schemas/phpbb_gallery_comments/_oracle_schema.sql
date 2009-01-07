/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_comments'
*/
CREATE TABLE phpbb_gallery_comments (
	comment_id number(8) NOT NULL,
	comment_image_id number(8) NOT NULL,
	comment_user_id number(8) DEFAULT '0' NOT NULL,
	comment_username varchar2(255) DEFAULT '' ,
	comment_user_colour varchar2(6) DEFAULT '' ,
	comment_user_ip varchar2(40) DEFAULT '' ,
	comment_time number(11) DEFAULT '0' NOT NULL,
	comment clob DEFAULT '' ,
	comment_uid varchar2(8) DEFAULT '' ,
	comment_bitfield varchar2(255) DEFAULT '' ,
	comment_edit_time number(11) DEFAULT '0' NOT NULL,
	comment_edit_count number(4) DEFAULT '0' NOT NULL,
	comment_edit_user_id number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_gallery_comments PRIMARY KEY (comment_id)
)
/

CREATE INDEX phpbb_gallery_comments_comment_image_id ON phpbb_gallery_comments (comment_image_id)
/
CREATE INDEX phpbb_gallery_comments_comment_user_id ON phpbb_gallery_comments (comment_user_id)
/
CREATE INDEX phpbb_gallery_comments_comment_user_ip ON phpbb_gallery_comments (comment_user_ip)
/
CREATE INDEX phpbb_gallery_comments_comment_time ON phpbb_gallery_comments (comment_time)
/

CREATE SEQUENCE phpbb_gallery_comments_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_gallery_comments
BEFORE INSERT ON phpbb_gallery_comments
FOR EACH ROW WHEN (
	new.comment_id IS NULL OR new.comment_id = 0
)
BEGIN
	SELECT phpbb_gallery_comments_seq.nextval
	INTO :new.comment_id
	FROM dual;
END;
/


