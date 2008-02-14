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
	image_lock INT4 DEFAULT '0' NOT NULL CHECK (image_lock >= 0),
	image_approval INT4 DEFAULT '0' NOT NULL CHECK (image_approval >= 0),
	PRIMARY KEY (image_id)
);

CREATE INDEX phpbb_gallery_images_image_album_id ON phpbb_gallery_images (image_album_id);
CREATE INDEX phpbb_gallery_images_image_user_id ON phpbb_gallery_images (image_user_id);
CREATE INDEX phpbb_gallery_images_image_time ON phpbb_gallery_images (image_time);

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


/*
	Table: 'phpbb_gallery_comments'
*/
CREATE SEQUENCE phpbb_gallery_comments_seq;

CREATE TABLE phpbb_gallery_comments (
	comment_id INT4 DEFAULT nextval('phpbb_gallery_comments_seq'),
	comment_image_id INT4 NOT NULL CHECK (comment_image_id >= 0),
	comment_user_id INT4 DEFAULT '0' NOT NULL CHECK (comment_user_id >= 0),
	comment_username varchar(32) DEFAULT '' NOT NULL,
	comment_user_ip varchar(40) DEFAULT '' NOT NULL,
	comment_time INT4 DEFAULT '0' NOT NULL CHECK (comment_time >= 0),
	comment TEXT DEFAULT '' NOT NULL,
	comment_uid varchar(8) DEFAULT '' NOT NULL,
	comment_bitfield varchar(255) DEFAULT '' NOT NULL,
	comment_edit_time INT4 DEFAULT '0' NOT NULL CHECK (comment_edit_time >= 0),
	comment_edit_count INT2 DEFAULT '0' NOT NULL CHECK (comment_edit_count >= 0),
	comment_edit_user_id INT4 DEFAULT '0' NOT NULL CHECK (comment_edit_user_id >= 0),
	PRIMARY KEY (comment_id)
);

CREATE INDEX phpbb_gallery_comments_comment_image_id ON phpbb_gallery_comments (comment_image_id);
CREATE INDEX phpbb_gallery_comments_comment_user_id ON phpbb_gallery_comments (comment_user_id);
CREATE INDEX phpbb_gallery_comments_comment_user_ip ON phpbb_gallery_comments (comment_user_ip);
CREATE INDEX phpbb_gallery_comments_comment_time ON phpbb_gallery_comments (comment_time);

/*
	Table: 'phpbb_gallery_config'
*/
CREATE TABLE phpbb_gallery_config (
	config_name varchar(255) DEFAULT '' NOT NULL,
	config_value varchar(255) DEFAULT '' NOT NULL,
	PRIMARY KEY (config_name)
);


/*
	Table: 'phpbb_gallery_rates'
*/
CREATE SEQUENCE phpbb_gallery_rates_seq;

CREATE TABLE phpbb_gallery_rates (
	rate_image_id INT4 DEFAULT nextval('phpbb_gallery_rates_seq'),
	rate_user_id INT4 DEFAULT '0' NOT NULL CHECK (rate_user_id >= 0),
	rate_user_ip varchar(40) DEFAULT '' NOT NULL,
	rate_point INT4 DEFAULT '0' NOT NULL CHECK (rate_point >= 0)
);

CREATE INDEX phpbb_gallery_rates_rate_image_id ON phpbb_gallery_rates (rate_image_id);
CREATE INDEX phpbb_gallery_rates_rate_user_id ON phpbb_gallery_rates (rate_user_id);
CREATE INDEX phpbb_gallery_rates_rate_user_ip ON phpbb_gallery_rates (rate_user_ip);
CREATE INDEX phpbb_gallery_rates_rate_point ON phpbb_gallery_rates (rate_point);


COMMIT;