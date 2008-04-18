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
	Table: 'phpbb_gallery_albums'
*/
CREATE SEQUENCE phpbb_gallery_albums_seq;

CREATE TABLE phpbb_gallery_albums (
	album_id INT4 DEFAULT nextval('phpbb_gallery_albums_seq'),
	parent_id INT4 DEFAULT '0' NOT NULL CHECK (parent_id >= 0),
	left_id INT4 DEFAULT '1' NOT NULL CHECK (left_id >= 0),
	right_id INT4 DEFAULT '2' NOT NULL CHECK (right_id >= 0),
	album_parents TEXT DEFAULT '' NOT NULL,
	album_type INT4 DEFAULT '1' NOT NULL CHECK (album_type >= 0),
	album_name varchar(255) DEFAULT '' NOT NULL,
	album_desc TEXT DEFAULT '' NOT NULL,
	album_desc_options INT4 DEFAULT '7' NOT NULL CHECK (album_desc_options >= 0),
	album_desc_uid varchar(8) DEFAULT '' NOT NULL,
	album_desc_bitfield varchar(255) DEFAULT '' NOT NULL,
	album_user_id INT4 DEFAULT '0' NOT NULL CHECK (album_user_id >= 0),
	album_order INT4 DEFAULT '0' NOT NULL CHECK (album_order >= 0),
	album_view_level INT4 DEFAULT '1' NOT NULL CHECK (album_view_level >= 0),
	album_upload_level INT4 DEFAULT '0' NOT NULL CHECK (album_upload_level >= 0),
	album_rate_level INT4 DEFAULT '0' NOT NULL CHECK (album_rate_level >= 0),
	album_comment_level INT4 DEFAULT '0' NOT NULL CHECK (album_comment_level >= 0),
	album_edit_level INT4 DEFAULT '0' NOT NULL CHECK (album_edit_level >= 0),
	album_delete_level INT4 DEFAULT '2' NOT NULL CHECK (album_delete_level >= 0),
	album_view_groups varchar(255) DEFAULT '' NOT NULL,
	album_upload_groups varchar(255) DEFAULT '' NOT NULL,
	album_rate_groups varchar(255) DEFAULT '' NOT NULL,
	album_comment_groups varchar(255) DEFAULT '' NOT NULL,
	album_edit_groups varchar(255) DEFAULT '' NOT NULL,
	album_delete_groups varchar(255) DEFAULT '' NOT NULL,
	album_moderator_groups varchar(255) DEFAULT '' NOT NULL,
	album_approval INT4 DEFAULT '0' NOT NULL CHECK (album_approval >= 0),
	PRIMARY KEY (album_id)
);



COMMIT;