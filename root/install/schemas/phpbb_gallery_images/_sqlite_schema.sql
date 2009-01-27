#
# $Id$
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
	image_username varchar(255) NOT NULL DEFAULT '',
	image_user_colour varchar(6) NOT NULL DEFAULT '',
	image_user_ip varchar(40) NOT NULL DEFAULT '',
	image_time INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_album_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_view_count INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_status INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_contest INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_filemissing INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_has_exif INTEGER UNSIGNED NOT NULL DEFAULT '2',
	image_exif_data text(65535) NOT NULL DEFAULT '',
	image_rates INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_rate_points INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_rate_avg INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_comments INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_last_comment INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_favorited INTEGER UNSIGNED NOT NULL DEFAULT '0',
	image_reported INTEGER UNSIGNED NOT NULL DEFAULT '0',
	filesize_upload INTEGER UNSIGNED NOT NULL DEFAULT '0',
	filesize_medium INTEGER UNSIGNED NOT NULL DEFAULT '0',
	filesize_cache INTEGER UNSIGNED NOT NULL DEFAULT '0'
);

CREATE INDEX phpbb_gallery_images_image_album_id ON phpbb_gallery_images (image_album_id);
CREATE INDEX phpbb_gallery_images_image_user_id ON phpbb_gallery_images (image_user_id);
CREATE INDEX phpbb_gallery_images_image_time ON phpbb_gallery_images (image_time);


COMMIT;