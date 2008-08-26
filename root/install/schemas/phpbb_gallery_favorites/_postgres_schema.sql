/*

 $Id$

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
	Table: 'phpbb_gallery_favorites'
*/
CREATE SEQUENCE phpbb_gallery_favorites_seq;

CREATE TABLE phpbb_gallery_favorites (
	favorite_id INT4 DEFAULT nextval('phpbb_gallery_favorites_seq'),
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	image_id INT4 DEFAULT '0' NOT NULL CHECK (image_id >= 0),
	PRIMARY KEY (favorite_id)
);

CREATE INDEX phpbb_gallery_favorites_user_id ON phpbb_gallery_favorites (user_id);
CREATE INDEX phpbb_gallery_favorites_image_id ON phpbb_gallery_favorites (image_id);


COMMIT;