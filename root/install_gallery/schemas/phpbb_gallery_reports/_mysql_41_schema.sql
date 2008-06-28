#
# $Id$
#

# Table: 'phpbb_gallery_reports'
CREATE TABLE phpbb_gallery_reports (
	report_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	report_album_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	report_image_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	reporter_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	report_manager mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	report_note mediumtext NOT NULL,
	report_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	report_status int(3) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (report_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


