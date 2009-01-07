/*

 $Id$

*/

BEGIN;


/*
	Table: 'phpbb_gallery_rates'
*/
CREATE SEQUENCE phpbb_gallery_rates_seq;

CREATE TABLE phpbb_gallery_rates (
	rate_image_id INT4 DEFAULT nextval('phpbb_gallery_rates_seq'),
	rate_user_id INT4 DEFAULT '0' NOT NULL CHECK (rate_user_id >= 0),
	rate_user_ip varchar(40) DEFAULT '' NOT NULL,
	rate_point INT4 DEFAULT '0' NOT NULL CHECK (rate_point >= 0)
);

CREATE INDEX phpbb_gallery_rates_rate_image_id ON phpbb_gallery_rates (rate_image_id);
CREATE INDEX phpbb_gallery_rates_rate_user_id ON phpbb_gallery_rates (rate_user_id);
CREATE INDEX phpbb_gallery_rates_rate_user_ip ON phpbb_gallery_rates (rate_user_ip);
CREATE INDEX phpbb_gallery_rates_rate_point ON phpbb_gallery_rates (rate_point);


COMMIT;