#
# $Id: schema_data.sql,v 1.257 2007/09/20 21:19:00 stoffel04 Exp $
#

# POSTGRES BEGIN #

# -- Config
INSERT INTO phpbb_gallery_config VALUES ('max_pics', '1024');
INSERT INTO phpbb_gallery_config VALUES ('user_pics_limit', '50');
INSERT INTO phpbb_gallery_config VALUES ('mod_pics_limit', '250');
INSERT INTO phpbb_gallery_config VALUES ('max_file_size', '128000');
INSERT INTO phpbb_gallery_config VALUES ('max_width', '800');
INSERT INTO phpbb_gallery_config VALUES ('max_height', '600');
INSERT INTO phpbb_gallery_config VALUES ('rows_per_page', '3');
INSERT INTO phpbb_gallery_config VALUES ('cols_per_page', '4');
INSERT INTO phpbb_gallery_config VALUES ('fullpic_popup', '0');
INSERT INTO phpbb_gallery_config VALUES ('thumbnail_quality', '50');
INSERT INTO phpbb_gallery_config VALUES ('thumbnail_size', '125');
INSERT INTO phpbb_gallery_config VALUES ('thumbnail_cache', '1');
INSERT INTO phpbb_gallery_config VALUES ('sort_method', 'image_time');
INSERT INTO phpbb_gallery_config VALUES ('sort_order', 'DESC');
INSERT INTO phpbb_gallery_config VALUES ('jpg_allowed', '1');
INSERT INTO phpbb_gallery_config VALUES ('png_allowed', '1');
INSERT INTO phpbb_gallery_config VALUES ('gif_allowed', '0');
INSERT INTO phpbb_gallery_config VALUES ('desc_length', '512');
INSERT INTO phpbb_gallery_config VALUES ('hotlink_prevent', '0');
INSERT INTO phpbb_gallery_config VALUES ('hotlink_allowed', 'phpbbgallery.ph.funpic.de');
INSERT INTO phpbb_gallery_config VALUES ('personal_gallery', '0');
INSERT INTO phpbb_gallery_config VALUES ('personal_gallery_private', '0');
INSERT INTO phpbb_gallery_config VALUES ('personal_gallery_limit', '10');
INSERT INTO phpbb_gallery_config VALUES ('personal_gallery_view', '1');
INSERT INTO phpbb_gallery_config VALUES ('rate', '1');
INSERT INTO phpbb_gallery_config VALUES ('rate_scale', '10');
INSERT INTO phpbb_gallery_config VALUES ('comment', '1');
INSERT INTO phpbb_gallery_config VALUES ('gd_version', '2');
INSERT INTO phpbb_gallery_config VALUES ('album_version', '0.2.4');
INSERT INTO phpbb_gallery_config VALUES ('watermark_images', 1);
INSERT INTO phpbb_gallery_config VALUES ('watermark_source', 'gallery/mark.png');
INSERT INTO phpbb_gallery_config VALUES ('preview_rsz_height', 600);
INSERT INTO phpbb_gallery_config VALUES ('preview_rsz_width', 800);

# POSTGRES COMMIT #
