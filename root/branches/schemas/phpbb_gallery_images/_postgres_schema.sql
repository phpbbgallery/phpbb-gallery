/*

 $Id: $

*/

BEGIN;

/*
	Domain definition
*/
CREATE DOMAIN varchar_ci AS varchar(255) NOT NULL DEFAULT ''::character varying;

/*
	Operation Functions
*/
CREATE FUNCTION _varchar_ci_equal(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) = LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_not_equal(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) != LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_less_than(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) < LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_less_equal(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) <= LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_greater_than(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) > LOWER($2)' LANGUAGE SQL STRICT;
CREATE FUNCTION _varchar_ci_greater_equals(varchar_ci, varchar_ci) RETURNS boolean AS 'SELECT LOWER($1) >= LOWER($2)' LANGUAGE SQL STRICT;

/*
	Operators
*/
CREATE OPERATOR <(
  PROCEDURE = _varchar_ci_less_than,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = >,
  NEGATOR = >=,
  RESTRICT = scalarltsel,
  JOIN = scalarltjoinsel);

CREATE OPERATOR <=(
  PROCEDURE = _varchar_ci_less_equal,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = >=,
  NEGATOR = >,
  RESTRICT = scalarltsel,
  JOIN = scalarltjoinsel);

CREATE OPERATOR >(
  PROCEDURE = _varchar_ci_greater_than,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = <,
  NEGATOR = <=,
  RESTRICT = scalargtsel,
  JOIN = scalargtjoinsel);

CREATE OPERATOR >=(
  PROCEDURE = _varchar_ci_greater_equals,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = <=,
  NEGATOR = <,
  RESTRICT = scalargtsel,
  JOIN = scalargtjoinsel);

CREATE OPERATOR <>(
  PROCEDURE = _varchar_ci_not_equal,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = <>,
  NEGATOR = =,
  RESTRICT = neqsel,
  JOIN = neqjoinsel);

CREATE OPERATOR =(
  PROCEDURE = _varchar_ci_equal,
  LEFTARG = varchar_ci,
  RIGHTARG = varchar_ci,
  COMMUTATOR = =,
  NEGATOR = <>,
  RESTRICT = eqsel,
  JOIN = eqjoinsel,
  HASHES,
  MERGES,
  SORT1= <);

/*
	Table: 'phpbb_gallery_images'
*/
CREATE SEQUENCE phpbb_gallery_images_seq;

CREATE TABLE phpbb_gallery_images (
	image_id INT4 DEFAULT nextval('phpbb_gallery_images_seq'),
	image_filename varchar(255) DEFAULT '' NOT NULL,
	image_thumbnail varchar(255) DEFAULT '' NOT NULL,
	image_name varchar(255) DEFAULT '' NOT NULL,
	image_desc TEXT DEFAULT '' NOT NULL,
	image_desc_uid varchar(8) DEFAULT '' NOT NULL,
	image_desc_bitfield varchar(255) DEFAULT '' NOT NULL,
	image_user_id INT4 DEFAULT '0' NOT NULL CHECK (image_user_id >= 0),
	image_username varchar(255) DEFAULT '' NOT NULL,
	image_user_colour varchar(6) DEFAULT '' NOT NULL,
	image_user_ip varchar(40) DEFAULT '' NOT NULL,
	image_time INT4 DEFAULT '0' NOT NULL CHECK (image_time >= 0),
	image_album_id INT4 DEFAULT '0' NOT NULL CHECK (image_album_id >= 0),
	image_view_count INT4 DEFAULT '0' NOT NULL CHECK (image_view_count >= 0),
	image_status INT4 DEFAULT '0' NOT NULL CHECK (image_status >= 0),
	image_filemissing INT4 DEFAULT '0' NOT NULL CHECK (image_filemissing >= 0),
	image_has_exif INT4 DEFAULT '2' NOT NULL CHECK (image_has_exif >= 0),
	image_rates INT4 DEFAULT '0' NOT NULL CHECK (image_rates >= 0),
	image_rate_points INT4 DEFAULT '0' NOT NULL CHECK (image_rate_points >= 0),
	image_rate_avg INT4 DEFAULT '0' NOT NULL CHECK (image_rate_avg >= 0),
	image_comments INT4 DEFAULT '0' NOT NULL CHECK (image_comments >= 0),
	image_last_comment INT4 DEFAULT '0' NOT NULL CHECK (image_last_comment >= 0),
	image_favorited INT4 DEFAULT '0' NOT NULL CHECK (image_favorited >= 0),
	image_reported INT4 DEFAULT '0' NOT NULL CHECK (image_reported >= 0),
	PRIMARY KEY (image_id)
);

CREATE INDEX phpbb_gallery_images_image_album_id ON phpbb_gallery_images (image_album_id);
CREATE INDEX phpbb_gallery_images_image_user_id ON phpbb_gallery_images (image_user_id);
CREATE INDEX phpbb_gallery_images_image_time ON phpbb_gallery_images (image_time);


COMMIT;