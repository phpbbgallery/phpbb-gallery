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
	Table: 'phpbb_gallery_permissions'
*/
CREATE SEQUENCE phpbb_gallery_permissions_seq;

CREATE TABLE phpbb_gallery_permissions (
	perm_id INT4 DEFAULT nextval('phpbb_gallery_permissions_seq'),
	perm_role_id INT4 DEFAULT '0' NOT NULL CHECK (perm_role_id >= 0),
	perm_album_id INT4 DEFAULT '0' NOT NULL CHECK (perm_album_id >= 0),
	perm_user_id INT4 DEFAULT '0' NOT NULL CHECK (perm_user_id >= 0),
	perm_group_id INT4 DEFAULT '0' NOT NULL CHECK (perm_group_id >= 0),
	perm_system INT4 DEFAULT '0' NOT NULL CHECK (perm_system >= 0),
	PRIMARY KEY (perm_id)
);



COMMIT;