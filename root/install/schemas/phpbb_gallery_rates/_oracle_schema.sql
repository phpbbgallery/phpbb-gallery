/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_rates'
*/
CREATE TABLE phpbb_gallery_rates (
	rate_image_id number(8) NOT NULL,
	rate_user_id number(8) DEFAULT '0' NOT NULL,
	rate_user_ip varchar2(40) DEFAULT '' ,
	rate_point number(3) DEFAULT '0' NOT NULL
)
/

CREATE INDEX phpbb_gallery_rates_rate_image_id ON phpbb_gallery_rates (rate_image_id)
/
CREATE INDEX phpbb_gallery_rates_rate_user_id ON phpbb_gallery_rates (rate_user_id)
/
CREATE INDEX phpbb_gallery_rates_rate_user_ip ON phpbb_gallery_rates (rate_user_ip)
/
CREATE INDEX phpbb_gallery_rates_rate_point ON phpbb_gallery_rates (rate_point)
/

CREATE SEQUENCE phpbb_gallery_rates_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_gallery_rates
BEFORE INSERT ON phpbb_gallery_rates
FOR EACH ROW WHEN (
	new.rate_image_id IS NULL OR new.rate_image_id = 0
)
BEGIN
	SELECT phpbb_gallery_rates_seq.nextval
	INTO :new.rate_image_id
	FROM dual;
END;
/


