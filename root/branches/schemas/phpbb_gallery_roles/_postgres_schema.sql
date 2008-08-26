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
	Table: 'phpbb_gallery_roles'
*/
CREATE SEQUENCE phpbb_gallery_roles_seq;

CREATE TABLE phpbb_gallery_roles (
	role_id INT4 DEFAULT nextval('phpbb_gallery_roles_seq'),
	i_view INT4 DEFAULT '0' NOT NULL CHECK (i_view >= 0),
	i_upload INT4 DEFAULT '0' NOT NULL CHECK (i_upload >= 0),
	i_edit INT4 DEFAULT '0' NOT NULL CHECK (i_edit >= 0),
	i_delete INT4 DEFAULT '0' NOT NULL CHECK (i_delete >= 0),
	i_rate INT4 DEFAULT '0' NOT NULL CHECK (i_rate >= 0),
	i_approve INT4 DEFAULT '0' NOT NULL CHECK (i_approve >= 0),
	i_lock INT4 DEFAULT '0' NOT NULL CHECK (i_lock >= 0),
	i_report INT4 DEFAULT '0' NOT NULL CHECK (i_report >= 0),
	i_count INT4 DEFAULT '0' NOT NULL CHECK (i_count >= 0),
	c_post INT4 DEFAULT '0' NOT NULL CHECK (c_post >= 0),
	c_edit INT4 DEFAULT '0' NOT NULL CHECK (c_edit >= 0),
	c_delete INT4 DEFAULT '0' NOT NULL CHECK (c_delete >= 0),
	a_moderate INT4 DEFAULT '0' NOT NULL CHECK (a_moderate >= 0),
	album_count INT4 DEFAULT '0' NOT NULL CHECK (album_count >= 0),
	PRIMARY KEY (role_id)
);



COMMIT;