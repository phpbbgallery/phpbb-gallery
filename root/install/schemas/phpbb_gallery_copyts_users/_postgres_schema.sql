/*

 $Id$

*/

BEGIN;


/*
	Table: 'phpbb_gallery_copyts_users'
*/
CREATE TABLE phpbb_gallery_copyts_users (
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	personal_album_id INT4 DEFAULT '0' NOT NULL CHECK (personal_album_id >= 0),
	PRIMARY KEY (user_id)
);



COMMIT;