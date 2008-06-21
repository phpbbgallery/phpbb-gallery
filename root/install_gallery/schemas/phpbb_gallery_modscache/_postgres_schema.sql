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
	Table: 'phpbb_gallery_modscache'
*/
CREATE TABLE phpbb_gallery_modscache (
	album_id INT4 DEFAULT '0' NOT NULL CHECK (album_id >= 0),
	user_id INT4 DEFAULT '0' NOT NULL CHECK (user_id >= 0),
	username varchar(255) DEFAULT '' NOT NULL,
	group_id INT4 DEFAULT '0' NOT NULL CHECK (group_id >= 0),
	group_name varchar(255) DEFAULT '' NOT NULL,
	display_on_index INT2 DEFAULT '1' NOT NULL
);

CREATE INDEX phpbb_gallery_modscache_disp_idx ON phpbb_gallery_modscache (display_on_index);
CREATE INDEX phpbb_gallery_modscache_album_id ON phpbb_gallery_modscache (album_id);


COMMIT;