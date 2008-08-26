#
# $Id$
#


# Table: 'phpbb_gallery_reports'
CREATE TABLE phpbb_gallery_reports (
	report_id INTEGER NOT NULL,
	report_album_id INTEGER DEFAULT 0 NOT NULL,
	report_image_id INTEGER DEFAULT 0 NOT NULL,
	reporter_id INTEGER DEFAULT 0 NOT NULL,
	report_manager INTEGER DEFAULT 0 NOT NULL,
	report_note BLOB SUB_TYPE TEXT CHARACTER SET UTF8 DEFAULT '' NOT NULL,
	report_time INTEGER DEFAULT 0 NOT NULL,
	report_status INTEGER DEFAULT 0 NOT NULL
);;

ALTER TABLE phpbb_gallery_reports ADD PRIMARY KEY (report_id);;


CREATE GENERATOR phpbb_gallery_reports_gen;;
SET GENERATOR phpbb_gallery_reports_gen TO 0;;

CREATE TRIGGER t_phpbb_gallery_reports FOR phpbb_gallery_reports
BEFORE INSERT
AS
BEGIN
	NEW.report_id = GEN_ID(phpbb_gallery_reports_gen, 1);
END;;


