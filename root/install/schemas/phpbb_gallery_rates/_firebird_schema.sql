#
# $Id$
#


# Table: 'phpbb_gallery_rates'
CREATE TABLE phpbb_gallery_rates (
	rate_image_id INTEGER NOT NULL,
	rate_user_id INTEGER DEFAULT 0 NOT NULL,
	rate_user_ip VARCHAR(40) CHARACTER SET NONE DEFAULT '' NOT NULL,
	rate_point INTEGER DEFAULT 0 NOT NULL
);;

CREATE INDEX phpbb_gallery_rates_rate_image_id ON phpbb_gallery_rates(rate_image_id);;
CREATE INDEX phpbb_gallery_rates_rate_user_id ON phpbb_gallery_rates(rate_user_id);;
CREATE INDEX phpbb_gallery_rates_rate_user_ip ON phpbb_gallery_rates(rate_user_ip);;
CREATE INDEX phpbb_gallery_rates_rate_point ON phpbb_gallery_rates(rate_point);;

CREATE GENERATOR phpbb_gallery_rates_gen;;
SET GENERATOR phpbb_gallery_rates_gen TO 0;;

CREATE TRIGGER t_phpbb_gallery_rates FOR phpbb_gallery_rates
BEFORE INSERT
AS
BEGIN
	NEW.rate_image_id = GEN_ID(phpbb_gallery_rates_gen, 1);
END;;


