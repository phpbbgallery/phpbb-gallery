#
# $Id: $
#


# Table: 'phpbb_gallery_comments'
CREATE TABLE phpbb_gallery_comments (
	comment_id INTEGER NOT NULL,
	comment_image_id INTEGER NOT NULL,
	comment_user_id INTEGER DEFAULT 0 NOT NULL,
	comment_username VARCHAR(32) CHARACTER SET NONE DEFAULT '' NOT NULL,
	comment_user_ip VARCHAR(40) CHARACTER SET NONE DEFAULT '' NOT NULL,
	comment_time INTEGER DEFAULT 0 NOT NULL,
	comment BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	comment_uid VARCHAR(8) CHARACTER SET NONE DEFAULT '' NOT NULL,
	comment_bitfield VARCHAR(255) CHARACTER SET NONE DEFAULT '' NOT NULL,
	comment_edit_time INTEGER DEFAULT 0 NOT NULL,
	comment_edit_count INTEGER DEFAULT 0 NOT NULL,
	comment_edit_user_id INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_gallery_comments ADD PRIMARY KEY (comment_id);;

CREATE INDEX phpbb_gallery_comments_comment_image_id ON phpbb_gallery_comments(comment_image_id);;
CREATE INDEX phpbb_gallery_comments_comment_user_id ON phpbb_gallery_comments(comment_user_id);;
CREATE INDEX phpbb_gallery_comments_comment_user_ip ON phpbb_gallery_comments(comment_user_ip);;
CREATE INDEX phpbb_gallery_comments_comment_time ON phpbb_gallery_comments(comment_time);;

CREATE GENERATOR phpbb_gallery_comments_gen;;
SET GENERATOR phpbb_gallery_comments_gen TO 0;;

CREATE TRIGGER t_phpbb_gallery_comments FOR phpbb_gallery_comments
BEFORE INSERT
AS
BEGIN
	NEW.comment_id = GEN_ID(phpbb_gallery_comments_gen, 1);
END;;


