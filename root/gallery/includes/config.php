<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_gallery_config
{
	/**
	* Prefix which is prepend to the configs before they are stored in the config table.
	*/
	static $prefix = 'phpbb_gallery_';

	static $config = false;

	public static function get($key)
	{
		if (self::$config === false)
		{
			self::load();
		}

		return self::$config[$key];
	}

	public static function load()
	{
		global $config;

		foreach ($config as $config_name => $config_value)
		{
			// Load all config values of the gallery
			if (strpos($config_name, self::$prefix) === 0)
			{
				$config_name = substr($config_name, strlen(self::$prefix));
				self::$config[$config_name] = settype($config_value, gettype(self::$default_config[$config_name]));
			}
		}

		// Should we load the default-config?
		self::$config = self::$config + self::$default_config;
	}

	public static function set($config_name, $config_value, $is_dynamic = false)
	{
		set_config(self::$prefix . $config_name, $config_value, $is_dynamic);
		self::$config[$config_name] = settype($config_value, gettype(self::$default_config[$config_name]));
	}

	public static function inc($config_name, $increment, $is_dynamic = false)
	{
		set_config_count(self::$prefix . $config_name, (int) $increment, $is_dynamic);
		self::$config[$config_name] += (int) $increment;
	}

	public static function dec($config_name, $increment, $is_dynamic = false)
	{
		set_config_count(self::$prefix . $config_name, 0 - (int) $increment, $is_dynamic);
		self::$config[$config_name] -= (int) $increment;
	}

	static $default_config = array(
	);
}
