#
# $Id$
#

BEGIN TRANSACTION;

# Table: 'phpbb_gallery_contests'
CREATE TABLE phpbb_gallery_contests (
	contest_id INTEGER PRIMARY KEY NOT NULL ,
	contest_album_id INTEGER UNSIGNED NOT NULL DEFAULT '0',
	contest_start INTEGER UNSIGNED NOT NULL DEFAULT '0',
	contest_rating INTEGER UNSIGNED NOT NULL DEFAULT '0',
	contest_end INTEGER UNSIGNED NOT NULL DEFAULT '0',
	contest_marked tinyint(1) NOT NULL DEFAULT '0',
	contest_first INTEGER UNSIGNED NOT NULL DEFAULT '0',
	contest_second INTEGER UNSIGNED NOT NULL DEFAULT '0',
	contest_third INTEGER UNSIGNED NOT NULL DEFAULT '0'
);



COMMIT;