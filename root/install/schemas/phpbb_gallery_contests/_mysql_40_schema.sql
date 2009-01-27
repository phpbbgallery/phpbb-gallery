#
# $Id$
#

# Table: 'phpbb_gallery_contests'
CREATE TABLE phpbb_gallery_contests (
	contest_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	contest_album_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	contest_start int(11) UNSIGNED DEFAULT '0' NOT NULL,
	contest_rating int(11) UNSIGNED DEFAULT '0' NOT NULL,
	contest_end int(11) UNSIGNED DEFAULT '0' NOT NULL,
	contest_marked tinyint(1) DEFAULT '0' NOT NULL,
	contest_first mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	contest_second mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	contest_third mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (contest_id)
);


