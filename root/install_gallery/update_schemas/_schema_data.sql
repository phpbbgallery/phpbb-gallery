#
# $Id: schema_data.sql,v 1.257 2007/09/20 21:19:00 stoffel04 Exp $
#

# POSTGRES BEGIN #

# -- Config

UPDATE  `phpbb_album_config` SET `config_value` = '0.1.3' WHERE `config_name` = 'album_version';
INSERT INTO phpbb_album_config VALUES ('watermark_images', 1);
INSERT INTO phpbb_album_config VALUES ('watermark_source', 'gallery/mark.png');

# POSTGRES COMMIT #






