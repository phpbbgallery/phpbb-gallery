/*

 $Id$

*/

BEGIN;


/*
	Table: 'phpbb_gallery_comments'
*/
CREATE SEQUENCE phpbb_gallery_comments_seq;

CREATE TABLE phpbb_gallery_comments (
	comment_id INT4 DEFAULT nextval('phpbb_gallery_comments_seq'),
	comment_image_id INT4 NOT NULL CHECK (comment_image_id >= 0),
	comment_user_id INT4 DEFAULT '0' NOT NULL CHECK (comment_user_id >= 0),
	comment_username varchar(255) DEFAULT '' NOT NULL,
	comment_user_colour varchar(6) DEFAULT '' NOT NULL,
	comment_user_ip varchar(40) DEFAULT '' NOT NULL,
	comment_time INT4 DEFAULT '0' NOT NULL CHECK (comment_time >= 0),
	comment TEXT DEFAULT '' NOT NULL,
	comment_uid varchar(8) DEFAULT '' NOT NULL,
	comment_bitfield varchar(255) DEFAULT '' NOT NULL,
	comment_edit_time INT4 DEFAULT '0' NOT NULL CHECK (comment_edit_time >= 0),
	comment_edit_count INT2 DEFAULT '0' NOT NULL CHECK (comment_edit_count >= 0),
	comment_edit_user_id INT4 DEFAULT '0' NOT NULL CHECK (comment_edit_user_id >= 0),
	PRIMARY KEY (comment_id)
);

CREATE INDEX phpbb_gallery_comments_comment_image_id ON phpbb_gallery_comments (comment_image_id);
CREATE INDEX phpbb_gallery_comments_comment_user_id ON phpbb_gallery_comments (comment_user_id);
CREATE INDEX phpbb_gallery_comments_comment_user_ip ON phpbb_gallery_comments (comment_user_ip);
CREATE INDEX phpbb_gallery_comments_comment_time ON phpbb_gallery_comments (comment_time);


COMMIT;