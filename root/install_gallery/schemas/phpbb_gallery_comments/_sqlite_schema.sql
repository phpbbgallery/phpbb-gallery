#
# $Id: $
#

BEGIN TRANSACTION;

# Table: 'phpbb_gallery_comments'
CREATE TABLE phpbb_gallery_comments (
	comment_id INTEGER PRIMARY KEY NOT NULL ,
	comment_image_id INTEGER UNSIGNED NOT NULL ,
	comment_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	comment_username varchar(32) NOT NULL DEFAULT '',
	comment_user_ip varchar(40) NOT NULL DEFAULT '',
	comment_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	comment mediumtext(16777215) NOT NULL DEFAULT '',
	comment_uid varchar(8) NOT NULL DEFAULT '',
	comment_bitfield varchar(255) NOT NULL DEFAULT '',
	comment_edit_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	comment_edit_count INTEGER UNSIGNED NOT NULL DEFAULT '0',
	comment_edit_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_gallery_comments_comment_image_id ON phpbb_gallery_comments (comment_image_id);
CREATE INDEX phpbb_gallery_comments_comment_user_id ON phpbb_gallery_comments (comment_user_id);
CREATE INDEX phpbb_gallery_comments_comment_user_ip ON phpbb_gallery_comments (comment_user_ip);
CREATE INDEX phpbb_gallery_comments_comment_time ON phpbb_gallery_comments (comment_time);


COMMIT;