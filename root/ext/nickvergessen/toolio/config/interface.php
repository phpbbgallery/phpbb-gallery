<?php
/**
*
* @package - Toolio
* @copyright (c) 2011 nickvergessen <nickvergessen@gmx.de> http://www.flying-bits.org/
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, v2 or later
*
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

interface phpbb_ext_nickvergessen_toolio_config_interface
{
	/**
	* Returns a list of all config sets, which should be loaded when the object is constructed
	*
	* @return	array		Returns an array with the set names
	*/
	//abstract public function default_sets();

	/**
	* Checks, whether the given config is set in the config array.
	*
	* @param	mixed	$config_name	Either string (name) or an array of two strings (set, name)
	* @return	boolean		true if the value exists, false otherwise
	*/
	public function exists($config_name);

	/**
	* Returns the value of the given config
	*
	* @param	mixed	$config_name		Either string (name) or an array of two strings (set, name)
	* @param	mixed	$return_default		If true and the value does not exist, the default value will be returned.
	* @return	mixed		Returns a boolean, integer or string, depending on the default value of the config
	*/
	public function get($config_name, $return_default = false);

	/**
	* Set the value for the given config
	*
	* @param	mixed	$config_name	Either string (name) or an array of two strings (set, name)
	* @param	mixed	$config_value	The value is casted to boolean, integer or string, depending on the default value of the config
	* @return	void
	*/
	public function set($config_name, $config_value);

	/**
	* Increment the value for the given config
	* NOTE: This function is only allowed for configs whose default value is an integer
	*
	* @param	mixed	$config_name	Either string (name) or an array of two strings (set, name)
	* @param	int		$increment		The value that is added to the current value
	* @return	boolean		True if the config is int and the value was incremented, false otherwise
	*/
	public function inc($config_name, $increment);

	/**
	* Decrement the value for the given config
	* NOTE: This function is only allowed for configs whose default value is an integer
	*
	* @param	mixed	$config_name	Either string (name) or an array of two strings (set, name)
	* @param	int		$increment		The value that is subtracted from the current value
	* @return	boolean		True if the config is int and the value was decremented, false otherwise
	*/
	public function dec($config_name, $decrement);

	/**
	* Checks, whether the given config is set in the config array.
	*
	* @param	mixed	$config_name	Either string (name) or an array of two strings (set, name)
	* @return	boolean		true if the config is dynamic, false otherwise
	*/
	public function is_dynamic($config_name);

	/**
	* Returns an array with all current values
	*
	* @return	array		Returns an array of "name => value" tuples
	*/
	public function get_array();

	/**
	* Returns an array with all default values
	*
	* @return	array		Returns an array of "name => value" tuples
	*/
	public function get_default();

	/**
	* Add config set to the database and load it
	*
	* @param	string	$name			The name of the config set
	* @return	void
	*/
	public function install_set($name);

	/**
	* Remove config set to the database
	*
	* @param	string	$name			The name of the config set
	* @return	void
	*/
	public function uninstall_set($name);

	/**
	* Load config set into the class
	*
	* @param	string	$name			The name of the config set
	* @return	void
	*/
	public function load_set($name);

	/**
	* Convert config_name to string
	*
	* @param	mixed	$config			Either string (name) or an array of two strings (set, name)
	* @return	string		The name of the config in the database
	*/
	public function get_config_name($config);

	/**
	* Returns the config prefix for a given set
	*
	* @param	string	$set_name		The short name of the set
	* @return	string		The set's prefix
	*/
	public function get_prefix_for_set($set_name);
}
