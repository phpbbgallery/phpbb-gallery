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
	Table: 'phpbb_album'
*/
CREATE SEQUENCE phpbb_album_seq;

CREATE TABLE phpbb_album (
	pic_id INT4 DEFAULT nextval('phpbb_album_seq'),
	pic_filename varchar(255) DEFAULT '' NOT NULL,
	pic_thumbnail varchar(255) DEFAULT '' NOT NULL,
	pic_title varchar(255) DEFAULT '' NOT NULL,
	pic_desc TEXT DEFAULT '' NOT NULL,
	pic_desc_bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	pic_desc_bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	pic_user_id INT4 DEFAULT '0' NOT NULL CHECK (pic_user_id >= 0),
	pic_username varchar(32) DEFAULT '' NOT NULL,
	pic_user_ip varchar(40) DEFAULT '' NOT NULL,
	pic_time INT4 DEFAULT '0' NOT NULL CHECK (pic_time >= 0),
	pic_cat_id INT4 DEFAULT '0' NOT NULL CHECK (pic_cat_id >= 0),
	pic_view_count INT4 DEFAULT '0' NOT NULL CHECK (pic_view_count >= 0),
	pic_lock INT2 DEFAULT '0' NOT NULL CHECK (pic_lock >= 0),
	pic_approval INT2 DEFAULT '0' NOT NULL CHECK (pic_approval >= 0),
	PRIMARY KEY (pic_id)
);


/*
	Table: 'phpbb_album_cat'
*/
CREATE SEQUENCE phpbb_album_cat_seq;

CREATE TABLE phpbb_album_cat (
	cat_id INT4 DEFAULT nextval('phpbb_album_cat_seq'),
	cat_title varchar(255) DEFAULT '' NOT NULL,
	cat_desc TEXT DEFAULT '' NOT NULL,
	cat_desc_bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	cat_desc_bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	cat_order INT4 DEFAULT '0' NOT NULL CHECK (cat_order >= 0),
	cat_view_level INT2 DEFAULT '1' NOT NULL CHECK (cat_view_level >= 0),
	cat_upload_level INT2 DEFAULT '0' NOT NULL CHECK (cat_upload_level >= 0),
	cat_rate_level INT2 DEFAULT '0' NOT NULL CHECK (cat_rate_level >= 0),
	cat_comment_level INT2 DEFAULT '0' NOT NULL CHECK (cat_comment_level >= 0),
	cat_edit_level INT2 DEFAULT '0' NOT NULL CHECK (cat_edit_level >= 0),
	cat_delete_level INT2 DEFAULT '2' NOT NULL CHECK (cat_delete_level >= 0),
	cat_view_groups varchar(255) DEFAULT '' NOT NULL,
	cat_upload_groups varchar(255) DEFAULT '' NOT NULL,
	cat_rate_groups varchar(255) DEFAULT '' NOT NULL,
	cat_comment_groups varchar(255) DEFAULT '' NOT NULL,
	cat_edit_groups varchar(255) DEFAULT '' NOT NULL,
	cat_delete_groups varchar(255) DEFAULT '' NOT NULL,
	cat_moderator_groups varchar(255) DEFAULT '' NOT NULL,
	cat_approval INT2 DEFAULT '0' NOT NULL CHECK (cat_approval >= 0),
	PRIMARY KEY (cat_id)
);


/*
	Table: 'phpbb_album_comment'
*/
CREATE SEQUENCE phpbb_album_comment_seq;

CREATE TABLE phpbb_album_comment (
	comment_id INT4 DEFAULT nextval('phpbb_album_comment_seq'),
	comment_pic_id INT4 NOT NULL CHECK (comment_pic_id >= 0),
	comment_user_id INT4 DEFAULT '0' NOT NULL CHECK (comment_user_id >= 0),
	comment_username varchar(32) DEFAULT '' NOT NULL,
	comment_user_ip varchar(40) DEFAULT '' NOT NULL,
	comment_time INT4 DEFAULT '0' NOT NULL CHECK (comment_time >= 0),
	comment_text TEXT DEFAULT '' NOT NULL,
	comment_text_bbcode_uid varchar(8) DEFAULT '' NOT NULL,
	comment_text_bbcode_bitfield varchar(255) DEFAULT '' NOT NULL,
	comment_edit_time INT4 DEFAULT '0' NOT NULL CHECK (comment_edit_time >= 0),
	comment_edit_count INT2 DEFAULT '0' NOT NULL CHECK (comment_edit_count >= 0),
	comment_edit_user_id INT4 DEFAULT '0' NOT NULL CHECK (comment_edit_user_id >= 0),
	PRIMARY KEY (comment_id)
);


/*
	Table: 'phpbb_album_config'
*/
CREATE TABLE phpbb_album_config (
	config_name varchar(255) DEFAULT '' NOT NULL,
	config_value varchar(255) DEFAULT '' NOT NULL,
	PRIMARY KEY (config_name)
);


/*
	Table: 'phpbb_album_rate'
*/
CREATE SEQUENCE phpbb_album_rate_seq;

CREATE TABLE phpbb_album_rate (
	rate_pic_id INT4 DEFAULT nextval('phpbb_album_rate_seq'),
	rate_user_id INT4 DEFAULT '0' NOT NULL CHECK (rate_user_id >= 0),
	rate_user_ip varchar(40) DEFAULT '' NOT NULL,
	rate_point INT2 DEFAULT '0' NOT NULL CHECK (rate_point >= 0),
	PRIMARY KEY (rate_pic_id)
);



COMMIT;