#
# $Id$
#

BEGIN TRANSACTION;

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