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
	image_username varchar(255) NOT NULL DEFAULT '',
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


COMMIT;