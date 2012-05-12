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

abstract class phpbb_ext_nickvergessen_toolio_config_base implements phpbb_ext_nickvergessen_toolio_config_interface
{
	/**
	* Array with all configs from the loaded sets and their current values
	*/
	private $configs = array();

	/**
	* Array with all configs from the loaded sets and their default values
	*/
	private $defaults = array();

	/**
	* Array with all config names from the loaded sets, which are dynamic
	*/
	private $dynamics = array();

	/**
	* Global $config and $db
	*/
	private $phpbb_config = null;
	private $phpbb_db = null;

	/**
	* Class name prefix for the config sets
	*/
	private $class_prefix = '';

	public function __construct(phpbb_config $config, dbal $db, $table)
	{
		$this->phpbb_config = $config;
		$this->phpbb_config_table = $table;
		$this->phpbb_db = $db;
		$this->class_prefix = get_class($this) . '_sets_';

		$sets = $this->default_sets();
		foreach ($sets as $set)
		{
			$this->load_set($set);
		}
	}

	/**
	* Returns a list of all config sets, which should be loaded when the object is constructed
	*
	* @return	array		Returns an array with the set names
	*/
	abstract public function default_sets();

	/**
	* Checks, whether the given config is set in the config array.
	*
	* @param	mixed	$config_name	Either string (name) or an array of two strings (set, name)
	* @return	boolean		true if the value exists, false otherwise
	*/
	final public function exists($config_name)
	{
		return !empty($this->configs[$this->get_config_name($config_name)]);
	}

	/**
	* Returns the value of the given config
	*
	* @param	mixed	$config_name		Either string (name) or an array of two strings (set, name)
	* @param	mixed	$return_default		If true and the value does not exist, the default value will be returned.
	* @return	mixed		Returns a boolean, integer or string, depending on the default value of the config
	*/
	final public function get($config_name, $return_default = false)
	{
		if ($return_default && !$this->exists($config_name))
		{
			return $this->defaults[$this->get_config_name($config_name)];
		}
		return $this->configs[$this->get_config_name($config_name)];
	}

	/**
	* Set the value for the given config
	*
	* @param	mixed	$config_name	Either string (name) or an array of two strings (set, name)
	* @param	mixed	$config_value	The value is casted to boolean, integer or string, depending on the default value of the config
	* @return	void
	*/
	final public function set($config_name, $config_value)
	{
		settype($config_value, gettype($this->defaults[$this->get_config_name($config_name)]));
		$this->configs[$this->get_config_name($config_name)] = $config_value;

		if ((gettype($this->defaults[$this->get_config_name($config_name)]) == 'bool') || (gettype($this->defaults[$this->get_config_name($config_name)]) == 'boolean'))
		{
			$update_config = ($this->configs[$this->get_config_name($config_name)]) ? '1' : '0';
			set_config($this->get_config_name($config_name), $update_config, $this->is_dynamic($config_name));
		}
		else
		{
			set_config($this->get_config_name($config_name), $this->configs[$this->get_config_name($config_name)], $this->is_dynamic($config_name));
		}
	}

	/**
	* Increment the value for the given config
	* NOTE: This function is only allowed for configs whose default value is an integer
	*
	* @param	mixed	$config_name	Either string (name) or an array of two strings (set, name)
	* @param	int		$increment		The value that is added to the current value
	* @return	boolean		True if the config is int and the value was incremented, false otherwise
	*/
	final public function inc($config_name, $increment)
	{
		if ((gettype($this->defaults[$this->get_config_name($config_name)]) != 'int') && (gettype($this->defaults[$this->get_config_name($config_name)]) != 'integer'))
		{
			return false;
		}

		set_config_count($this->get_config_name($config_name), (int) $increment, $this->is_dynamic($config_name));
		$this->configs[$this->get_config_name($config_name)] += (int) $increment;
		return true;
	}

	/**
	* Decrement the value for the given config
	* NOTE: This function is only allowed for configs whose default value is an integer
	*
	* @param	mixed	$config_name	Either string (name) or an array of two strings (set, name)
	* @param	int		$increment		The value that is subtracted from the current value
	* @return	boolean		True if the config is int and the value was decremented, false otherwise
	*/
	final public function dec($config_name, $decrement)
	{
		if ((gettype($this->defaults[$this->get_config_name($config_name)]) != 'int') && (gettype($this->defaults[$this->get_config_name($config_name)]) != 'integer'))
		{
			return false;
		}

		set_config_count($this->get_config_name($config_name), 0 - (int) $decrement, $this->is_dynamic($config_name));
		$this->configs[$this->get_config_name($config_name)] -= (int) $decrement;
		return true;
	}

	/**
	* Checks, whether the given config is set in the config array.
	*
	* @param	mixed	$config_name	Either string (name) or an array of two strings (set, name)
	* @return	boolean		true if the config is dynamic, false otherwise
	*/
	final public function is_dynamic($config_name)
	{
		return isset($this->dynamics[$this->get_config_name($config_name)]);
	}

	/**
	* Returns an array with all current values
	*
	* @return	array		Returns an array of "name => value" tuples
	*/
	final public function get_array()
	{
		return $this->configs;
	}

	/**
	* Returns an array with all default values
	*
	* @return	array		Returns an array of "name => value" tuples
	*/
	final public function get_default()
	{
		return $this->defaults;
	}

	/**
	* Add config set to the database and load it
	*
	* @param	string	$set_name			The name of the config set
	* @return	void
	*/
	final public function install_set($set_name)
	{
		$set_defaults = call_user_func(array($this->class_prefix . $set_name, 'get_configs'));
		foreach ($set_defaults as $name => $default_value)
		{
			if ((gettype($default_value) == 'bool') || (gettype($default_value) == 'boolean'))
			{
				$update_config = ($default_value) ? '1' : '0';
				set_config($this->get_config_name(array($set_name, $name)), $update_config, $this->is_dynamic(array($set_name, $name)));
				$this->phpbb_config[$this->get_config_name(array($set_name, $name))] = $update_config;
			}
			else
			{
				set_config($this->get_config_name(array($set_name, $name)), $default_value, $this->is_dynamic(array($set_name, $name)));
				$this->phpbb_config[$this->get_config_name(array($set_name, $name))] = $default_value;
			}
		}

		// Automatically load the set we just installed.
		$this->load_set($set_name);
	}

	/**
	* Remove config set to the database
	*
	* @param	string	$set_name			The name of the config set
	* @return	void
	*/
	final public function uninstall_set($set_name)
	{
		$delete_configs = array();

		$set_defaults = call_user_func(array($this->class_prefix . $set_name, 'get_configs'));
		foreach ($set_defaults as $name => $default_value)
		{
			$config_name = $this->get_config_name(array($set_name, $name));
			$delete_configs[] = $config_name;

			unset($this->configs[$config_name]);
			unset($this->defaults[$config_name]);
			unset($this->dynamics[$config_name]);
		}

		if (sizeof($delete_configs))
		{
			$sql = 'DELETE FROM ' . $this->phpbb_config_table . '
				WHERE ' . $this->phpbb_db->sql_in_set('config_name', $delete_configs);
			$this->phpbb_db->sql_query($sql);
		}
	}

	/**
	* Load config set into the class
	*
	* @param	string	$set_name			The name of the config set
	* @return	void
	*/
	final public function load_set($set_name)
	{
		$set_defaults = call_user_func(array($this->class_prefix . $set_name, 'get_configs'));
		foreach ($set_defaults as $name => $default_value)
		{
			$this->defaults[$this->get_config_name(array($set_name, $name))] = $default_value;

			// Set the config value if it exists in the database
			if (isset($this->phpbb_config[$this->get_config_name(array($set_name, $name))]))
			{
				$config_value = $this->phpbb_config[$this->get_config_name(array($set_name, $name))];
				settype($config_value, gettype($default_value));
				$this->configs[$this->get_config_name(array($set_name, $name))] = $config_value;
			}
		}

		$set_dynamics = call_user_func(array($this->class_prefix . $set_name, 'get_dynamics'));
		foreach ($set_dynamics as $name)
		{
			// Only allow setting configs as dynamic, which are from this set
			if (isset($set_defaults[$name]))
			{
				$this->dynamics[$this->get_config_name(array($set_name, $name))] = true;
			}
		}
	}

	/**
	* Convert config_name to string
	*
	* @param	mixed	$config_name		Either string (name) or an array of two strings (set, name)
	* @return	string		The name of the config in the database
	*/
	final public function get_config_name($config_name)
	{
		static $config_names;

		if (is_array($config_name))
		{
			$tmp_name = $config_name[0] . '-' . $config_name[1];
			if (isset($config_names[$tmp_name]))
			{
				return $config_names[$tmp_name];
			}

			$config_names[$tmp_name] = $this->get_prefix_for_set($config_name[0]) . $config_name[1];
			return $config_names[$tmp_name];
		}

		$tmp_name = 'core-' . $config_name;
		if (isset($config_names[$tmp_name]))
		{
			return $config_names[$tmp_name];
		}

		$config_names[$tmp_name] = $this->get_prefix_for_set('core') . $config_name;
		return $config_names[$tmp_name];
	}

	/**
	* Returns the config prefix for a given set
	*
	* @param	string	$set_name		The short name of the set
	* @return	string		The set's prefix
	*/
	final public function get_prefix_for_set($set_name)
	{
		static $prefix_list;

		if (isset($prefix_list[$set_name]))
		{
			return $prefix_list[$set_name];
		}

		$prefix_list[$set_name] = call_user_func(array($this->class_prefix . $set_name, 'get_prefix'));
		return $prefix_list[$set_name];
	}
}
