<?php
/**
*
* @package Toolio - Config
* @copyright (c) 2012 nickvergessen - http://www.flying-bits.org/
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

interface phpbb_ext_nickvergessen_toolio_config_sets_interface
{
	/**
	* Returns the prefix that should be used for the set.
	* All config names will be prefixed with this prefix:
	* Example:	prefix:			myprefix_
	*			config_name:	myconfig
	*			stored in db:	myprefix_myconfig
	* Please remember the length limit of 255 characters for config names
	*
	* @return	string		The set's prefix
	*/
	static public function get_prefix();

	/**
	* Returns the array with all configs and their default values.
	* NOTE:	The values on set() and get() will be casted to the same type as the default value.
	*		The functions inc() and dec() are only available for type integer.
	*
	* @return	array		The array of the configs
	*/
	static public function get_configs();

	/**
	* Returns an array of all config names, that are dynamic.
	* Dynamic values are not cached, but always pulled from the database.
	*
	* @return	array		The array of dynamic configs
	*/
	static public function get_dynamics();
}
