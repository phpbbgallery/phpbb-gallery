#
# $Id: $
#

BEGIN TRANSACTION;

# Table: 'phpbb_gallery_albums'
CREATE TABLE phpbb_gallery_albums (
	album_id INTEGER PRIMARY KEY NOT NULL ,
	parent_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	left_id INTEGER UNSIGNED NOT NULL DEFAULT '1',
	right_id INTEGER UNSIGNED NOT NULL DEFAULT '2',
	album_parents mediumtext(16777215) NOT NULL DEFAULT '',
	album_type INTEGER UNSIGNED NOT NULL DEFAULT '1',
	album_name varchar(255) NOT NULL DEFAULT '',
	album_desc mediumtext(16777215) NOT NULL DEFAULT '',
	album_desc_options INTEGER UNSIGNED NOT NULL DEFAULT '7',
	album_desc_uid varchar(8) NOT NULL DEFAULT '',
	album_desc_bitfield varchar(255) NOT NULL DEFAULT '',
	album_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	album_order INTEGER UNSIGNED NOT NULL DEFAULT '0',
	album_view_level INTEGER UNSIGNED NOT NULL DEFAULT '1',
	album_upload_level INTEGER UNSIGNED NOT NULL DEFAULT '0',
	album_rate_level INTEGER UNSIGNED NOT NULL DEFAULT '0',
	album_comment_level INTEGER UNSIGNED NOT NULL DEFAULT '0',
	album_edit_level INTEGER UNSIGNED NOT NULL DEFAULT '0',
	album_delete_level INTEGER UNSIGNED NOT NULL DEFAULT '2',
	album_view_groups varchar(255) NOT NULL DEFAULT '',
	album_upload_groups varchar(255) NOT NULL DEFAULT '',
	album_rate_groups varchar(255) NOT NULL DEFAULT '',
	album_comment_groups varchar(255) NOT NULL DEFAULT '',
	album_edit_groups varchar(255) NOT NULL DEFAULT '',
	album_delete_groups varchar(255) NOT NULL DEFAULT '',
	album_moderator_groups varchar(255) NOT NULL DEFAULT '',
	album_approval INTEGER UNSIGNED NOT NULL DEFAULT '0'
);



COMMIT;