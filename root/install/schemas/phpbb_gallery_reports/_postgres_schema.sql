/*

 $Id$

*/

BEGIN;


/*
	Table: 'phpbb_gallery_reports'
*/
CREATE SEQUENCE phpbb_gallery_reports_seq;

CREATE TABLE phpbb_gallery_reports (
	report_id INT4 DEFAULT nextval('phpbb_gallery_reports_seq'),
	report_album_id INT4 DEFAULT '0' NOT NULL CHECK (report_album_id >= 0),
	report_image_id INT4 DEFAULT '0' NOT NULL CHECK (report_image_id >= 0),
	reporter_id INT4 DEFAULT '0' NOT NULL CHECK (reporter_id >= 0),
	report_manager INT4 DEFAULT '0' NOT NULL CHECK (report_manager >= 0),
	report_note TEXT DEFAULT '' NOT NULL,
	report_time INT4 DEFAULT '0' NOT NULL CHECK (report_time >= 0),
	report_status INT4 DEFAULT '0' NOT NULL CHECK (report_status >= 0),
	PRIMARY KEY (report_id)
);



COMMIT;