/*

 $Id$

*/


/*
	Table: 'phpbb_gallery_reports'
*/
CREATE TABLE phpbb_gallery_reports (
	report_id number(8) NOT NULL,
	report_album_id number(8) DEFAULT '0' NOT NULL,
	report_image_id number(8) DEFAULT '0' NOT NULL,
	reporter_id number(8) DEFAULT '0' NOT NULL,
	report_manager number(8) DEFAULT '0' NOT NULL,
	report_note clob DEFAULT '' ,
	report_time number(11) DEFAULT '0' NOT NULL,
	report_status number(3) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_gallery_reports PRIMARY KEY (report_id)
)
/


CREATE SEQUENCE phpbb_gallery_reports_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_gallery_reports
BEFORE INSERT ON phpbb_gallery_reports
FOR EACH ROW WHEN (
	new.report_id IS NULL OR new.report_id = 0
)
BEGIN
	SELECT phpbb_gallery_reports_seq.nextval
	INTO :new.report_id
	FROM dual;
END;
/


