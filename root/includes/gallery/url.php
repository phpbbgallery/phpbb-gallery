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
	/**
	* Path from the gallery root, back to phpbb's root
	*/
	static private $phpbb_root_path = '../';

	/**
	* Path from the phpbb root, into admin's root
	*/
	static private $phpbb_admin_path = 'adm/';

	/**
	* Path from the phpbb root, into gallery's root
	*/
	static private $phpbb_gallery_path = 'gallery/';

	/**
	* php-file extension
	*/
	static private $phpEx = '.php';


	const IMAGE_PATH = 'images/';
	const UPLOAD_PATH = 'upload/';
	const THUMBNAIL_PATH = 'cache/';
	const MEDIUM_PATH = 'medium/';
	const IMPORT_PATH = 'import/';

	static private $loaded = false;

	/**
	* Static Constructor.
	*/
	static public function init($force_root_path = false)
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
		self::$phpEx = '.' . $phpEx;

		self::$loaded = true;
	}

	static public function path($directory = 'gallery')
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
			case 'image':
				return self::$phpbb_root_path . self::$phpbb_gallery_path . self::IMAGE_PATH;
			case 'upload':
				return self::$phpbb_root_path . self::$phpbb_gallery_path . self::IMAGE_PATH . self::UPLOAD_PATH;
			case 'upload_noroot':
				// stupid phpbb-upload class prepends the rootpath itself.
				return self::$phpbb_gallery_path . self::IMAGE_PATH . self::UPLOAD_PATH;
			case 'thumbnail':
				return self::$phpbb_root_path . self::$phpbb_gallery_path . self::IMAGE_PATH . self::THUMBNAIL_PATH;
			case 'medium':
				return self::$phpbb_root_path . self::$phpbb_gallery_path . self::IMAGE_PATH . self::MEDIUM_PATH;
			case 'import':
				return self::$phpbb_root_path . self::$phpbb_gallery_path . self::IMAGE_PATH . self::IMPORT_PATH;
		}

		return false;
	}

	static public function append_sid()
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

	static public function create_link($path, $file, $params = false, $is_amp = true)
	{
		// No ?sid=
		return self::append_sid($path, $file, $params, $is_amp, '');
	}

	static public function redirect()
	{
		redirect(self::append_sid(func_get_args()));
	}

	static public function phpEx_file($file)
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

		/*if ($file == 'image_page')
		{
			//@todo
			$file = 'viewimage';
		}*/

		return $file . self::$phpEx;
	}

	static public function _include($file, $path = 'gallery', $sub_directory = 'includes/')
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

	static public function _file_exists($file, $path = 'gallery', $sub_directory = 'includes/')
	{
		return file_exists(self::path($path) . $sub_directory . self::phpEx_file($file));
	}

	static public function _is_writable($file, $path = 'gallery', $sub_directory = 'includes/')
	{
		return phpbb_is_writable(self::path($path) . $sub_directory . self::phpEx_file($file));
	}

	static public function _return_file($file, $path = 'gallery', $sub_directory = 'includes/')
	{
		return self::path($path) . $sub_directory . self::phpEx_file($file);
	}
}
