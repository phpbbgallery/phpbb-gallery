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

class phpbb_gallery_url
{
	private static $loaded = false;

	/**
	* Path from the gallery root, back to phpbb's root
	*/
	private static $phpbb_root_path = '../';

	/**
	* Path from the phpbb root, into admin's root
	*/
	private static $phpbb_admin_path = 'adm/';

	/**
	* Path from the phpbb root, into gallery's root
	*/
	private static $phpbb_gallery_path = 'gallery/';

	/**
	* php-file extension
	*/
	private static $phpEx = '.php';

	/**
	* Static Constructor.
	*/
	public static function init($force_root_path = false)
	{
		global $phpbb_admin_path, $phpbb_root_path, $phpEx;

		if ($force_root_path)
		{
			self::$phpbb_root_path = $force_root_path;
		}
		else
		{
			self::$phpbb_root_path = $phpbb_root_path;
		}
		$phpbb_admin_path = self::$phpbb_root_path . self::$phpbb_admin_path;
		self::$phpbb_admin_path = $phpbb_admin_path;
		self::$phpbb_gallery_path = GALLERY_ROOT_PATH;
		self::$phpEx = '.' . $phpEx;

		self::$loaded = true;
	}

	public static function path($directory = 'gallery')
	{
		if (!self::$loaded)
		{
			self::init();
		}

		switch ($directory)
		{
			case 'gallery':
				return self::$phpbb_root_path . self::$phpbb_gallery_path;
			case 'phpbb':
				return self::$phpbb_root_path;
			case 'admin':
				return self::$phpbb_admin_path;
			case 'relative':
				return self::$phpbb_gallery_path;
			case 'full':
				return generate_board_url() . '/' . self::$phpbb_gallery_path;
			case 'board':
				return generate_board_url();
		}

		return false;
	}

	public static function append_sid()
	{
		$args = func_get_args();
		if (is_array($args[0]))
		{
			// Little problem from the duplicated call to func_get_args();
			$args = $args[0];
		}

		if ($args[0] == 'phpbb')
		{
			$mode = array_shift($args);
			$args[0] = self::path('phpbb') . self::phpEx_file($args[0]);
		}
		else if ($args[0] == 'admin')
		{
			$mode = array_shift($args);
			$args[0] = self::path('admin') . self::phpEx_file($args[0]);
		}
		else if ($args[0] == 'relative')
		{
			$mode = array_shift($args);
			$args[0] = self::path('relative') . self::phpEx_file($args[0]);
		}
		else
		{
			$args[0] = self::path() . self::phpEx_file($args[0]);
		}

		$params = $args + array(
			0	=> '',
			1	=> false,
			2	=> true,
			3	=> false,
		);

		return append_sid($params[0], $params[1], $params[2], $params[3]);
	}

	public static function create_link($path, $file, $params = false, $is_amp = true)
	{
		// No ?sid=
		return self::append_sid($path, $file, $params, $is_amp, '');
	}

	public static function redirect()
	{
		redirect(self::append_sid(func_get_args()));
	}

	public static function phpEx_file($file)
	{
		if ((substr($file, -1) == '/') || (strlen($file) == 0))
		{
			// it's no file, so no .php here.
			return $file;
		}

		if (!self::$loaded)
		{
			self::init();
		}

		return $file . self::$phpEx;
	}

	public static function _include($file, $path = 'gallery', $sub_directory = 'includes/')
	{
		if (!is_array($file))
		{
			include(self::path($path) . $sub_directory . self::phpEx_file($file));
		}
		else
		{
			foreach ($file as $real_file)
			{
				self::_include($real_file, $path, $sub_directory);
			}
		}
	}

	public static function _include_core($classes)
	{
		if (!is_array($classes))
		{
			if (!class_exists('phpbb_gallery_' . $classes))
			{
				include(self::path('gallery') . 'includes/core/' . self::phpEx_file($classes));
			}
		}
		else
		{
			foreach ($classes as $class)
			{
				self::_include_core($class);
			}
		}
	}

	public static function _file_exists($file, $path = 'gallery', $sub_directory = 'includes/')
	{
		return file_exists(self::path($path) . $sub_directory . self::phpEx_file($file));
	}

	public static function _is_writable($file, $path = 'gallery', $sub_directory = 'includes/')
	{
		return phpbb_is_writable(self::path($path) . $sub_directory . self::phpEx_file($file));
	}

	public static function _return_file($file, $path = 'gallery', $sub_directory = 'includes/')
	{
		return self::path($path) . $sub_directory . self::phpEx_file($file);
	}
}
