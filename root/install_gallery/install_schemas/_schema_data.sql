#
# $Id: schema_data.sql,v 1.257 2007/09/20 21:19:00 stoffel04 Exp $
#

# POSTGRES BEGIN #

# -- Config
INSERT INTO phpbb_album_config VALUES ('max_pics', '1024');
INSERT INTO phpbb_album_config VALUES ('user_pics_limit', '50');
INSERT INTO phpbb_album_config VALUES ('mod_pics_limit', '250');
INSERT INTO phpbb_album_config VALUES ('max_file_size', '128000');
INSERT INTO phpbb_album_config VALUES ('max_width', '800');
INSERT INTO phpbb_album_config VALUES ('max_height', '600');
INSERT INTO phpbb_album_config VALUES ('rows_per_page', '3');
INSERT INTO phpbb_album_config VALUES ('cols_per_page', '4');
INSERT INTO phpbb_album_config VALUES ('fullpic_popup', '1');
INSERT INTO phpbb_album_config VALUES ('thumbnail_quality', '50');
INSERT INTO phpbb_album_config VALUES ('thumbnail_size', '125');
INSERT INTO phpbb_album_config VALUES ('thumbnail_cache', '1');
INSERT INTO phpbb_album_config VALUES ('sort_method', 'pic_time');
INSERT INTO phpbb_album_config VALUES ('sort_order', 'DESC');
INSERT INTO phpbb_album_config VALUES ('jpg_allowed', '1');
INSERT INTO phpbb_album_config VALUES ('png_allowed', '1');
INSERT INTO phpbb_album_config VALUES ('gif_allowed', '0');
INSERT INTO phpbb_album_config VALUES ('desc_length', '512');
INSERT INTO phpbb_album_config VALUES ('hotlink_prevent', '0');
INSERT INTO phpbb_album_config VALUES ('hotlink_allowed', 'phpbbgallery.ph.funpic.de');
INSERT INTO phpbb_album_config VALUES ('personal_gallery', '0');
INSERT INTO phpbb_album_config VALUES ('personal_gallery_private', '0');
INSERT INTO phpbb_album_config VALUES ('personal_gallery_limit', '10');
INSERT INTO phpbb_album_config VALUES ('personal_gallery_view', '1');
INSERT INTO phpbb_album_config VALUES ('rate', '1');
INSERT INTO phpbb_album_config VALUES ('rate_scale', '10');
INSERT INTO phpbb_album_config VALUES ('comment', '1');
INSERT INTO phpbb_album_config VALUES ('gd_version', '2');
INSERT INTO phpbb_album_config VALUES ('album_version', '0.1.3');
INSERT INTO phpbb_album_config VALUES ('watermark_images', 1);
INSERT INTO phpbb_album_config VALUES ('watermark_source', 'gallery/mark.png');

# -- Style-Data for the button
INSERT INTO phpbb_styles_imageset_data (`image_name`, `image_filename`, `image_lang`, `image_height`, `image_width`, `imageset_id`) VALUES ('button_upload_image', 'button_upload_image.gif', 'en', 25, 119, 1);

# POSTGRES COMMIT #






