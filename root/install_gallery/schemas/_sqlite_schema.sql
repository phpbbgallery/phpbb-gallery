#
# $Id: $
#

BEGIN TRANSACTION;

# Table: 'phpbb_gallery_images'
CREATE TABLE phpbb_gallery_images (
	image_id INTEGER PRIMARY KEY NOT NULL ,
	image_filename varchar(255) NOT NULL DEFAULT '',
	image_thumbnail varchar(255) NOT NULL DEFAULT '',
	image_name varchar(255) NOT NULL DEFAULT '',
	image_desc mediumtext(16777215) NOT NULL DEFAULT '',
	image_desc_uid varchar(8) NOT NULL DEFAULT '',
	image_desc_bitfield varchar(255) NOT NULL DEFAULT '',
	image_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_username varchar(32) NOT NULL DEFAULT '',
	image_user_colour varchar(6) NOT NULL DEFAULT '',
	image_user_ip varchar(40) NOT NULL DEFAULT '',
	image_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_album_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_view_count INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_lock INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_approval INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_gallery_images_image_album_id ON phpbb_gallery_images (image_album_id);
CREATE INDEX phpbb_gallery_images_image_user_id ON phpbb_gallery_images (image_user_id);
CREATE INDEX phpbb_gallery_images_image_time ON phpbb_gallery_images (image_time);

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

# Table: 'phpbb_gallery_config'
CREATE TABLE phpbb_gallery_config (
	config_name varchar(255) NOT NULL DEFAULT '',
	config_value varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (config_name)
);


# Table: 'phpbb_gallery_rates'
CREATE TABLE phpbb_gallery_rates (
	rate_image_id INTEGER PRIMARY KEY NOT NULL ,
	rate_user_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	rate_user_ip varchar(40) NOT NULL DEFAULT '',
	rate_point INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_gallery_rates_rate_image_id ON phpbb_gallery_rates (rate_image_id);
CREATE INDEX phpbb_gallery_rates_rate_user_id ON phpbb_gallery_rates (rate_user_id);
CREATE INDEX phpbb_gallery_rates_rate_user_ip ON phpbb_gallery_rates (rate_user_ip);
CREATE INDEX phpbb_gallery_rates_rate_point ON phpbb_gallery_rates (rate_point);


COMMIT;