#
# $Id: schema_data.sql,v 1.257 2007/09/20 21:19:00 stoffel04 Exp $
#

# POSTGRES BEGIN #

# -- Config

INSERT INTO phpbb_styles_imageset_data (`image_name`,`image_filename`,`image_lang`,`image_height`,`image_width`,`imageset_id`) VALUES ('button_upload_image','button_upload_image.gif','en',25,119,1);
UPDATE `phpbb_album_config` SET `config_value` = '0.1.3' WHERE `config_name` = 'album_version';
INSERT INTO phpbb_album_config VALUES ('watermark_images', 1);
INSERT INTO phpbb_album_config VALUES ('watermark_source', 'gallery/mark.png');
UPDATE `phpbb_album_cat` SET cat_view_level = 4 WHERE cat_view_level = 1;
UPDATE `phpbb_album_cat` SET cat_upload_level = 4 WHERE cat_upload_level = 1;
UPDATE `phpbb_album_cat` SET cat_rate_level = 4 WHERE cat_rate_level = 1;
UPDATE `phpbb_album_cat` SET cat_comment_level = 4 WHERE cat_comment_level = 1;
UPDATE `phpbb_album_cat` SET cat_edit_level = 4 WHERE cat_edit_level = 1;
UPDATE `phpbb_album_cat` SET cat_delete_level = 4 WHERE cat_delete_level = 1;
UPDATE `phpbb_album_cat` SET cat_view_level = 1 WHERE cat_view_level = -1;
UPDATE `phpbb_album_cat` SET cat_upload_level = 1 WHERE cat_upload_level = -1;
UPDATE `phpbb_album_cat` SET cat_rate_level = 1 WHERE cat_rate_level = -1;
UPDATE `phpbb_album_cat` SET cat_comment_level = 1 WHERE cat_comment_level = -1;
UPDATE `phpbb_album_cat` SET cat_edit_level = 1 WHERE cat_edit_level = -1;
UPDATE `phpbb_album_cat` SET cat_delete_level = 1 WHERE cat_delete_level = -1;
UPDATE `phpbb_album_config` SET config_value = '0.1.1' WHERE config_name = 'album_version';
UPDATE `phpbb_album_config` SET config_value = '1' WHERE config_name = 'personal_gallery_view' AND config_value = '-1';

# POSTGRES COMMIT #






