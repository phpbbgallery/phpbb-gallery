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

	static private $phpbb_gallery_relative = '';
	static private $phpbb_gallery_full_path = '';

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
		self::$phpbb_gallery_relative = self::beautiful_path(self::$phpbb_root_path . self::$phpbb_gallery_path);
		self::$phpbb_gallery_full_path = self::beautiful_path(generate_board_url() . '/' . self::$phpbb_gallery_path, true);

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
				return self::$phpbb_gallery_relative;
			case 'phpbb':
				return self::$phpbb_root_path;
			case 'admin':
				return self::$phpbb_admin_path;
			case 'relative':
				return self::$phpbb_gallery_path;
			case 'full':
				return self::$phpbb_gallery_full_path;
			case 'board':
				return generate_board_url() . '/';
			case 'images':
				return self::$phpbb_gallery_relative . self::IMAGE_PATH;
			case 'upload':
				return self::$phpbb_gallery_relative . self::IMAGE_PATH . self::UPLOAD_PATH;
			case 'upload_noroot':
				// stupid phpbb-upload class prepends the rootpath itself.
				return self::$phpbb_gallery_path . self::IMAGE_PATH . self::UPLOAD_PATH;
			case 'thumbnail':
				return self::$phpbb_gallery_relative . self::IMAGE_PATH . self::THUMBNAIL_PATH;
			case 'thumbnail_noroot':
				return self::$phpbb_gallery_path . self::IMAGE_PATH . self::THUMBNAIL_PATH;
			case 'medium':
				return self::$phpbb_gallery_relative . self::IMAGE_PATH . self::MEDIUM_PATH;
			case 'medium_noroot':
				return self::$phpbb_gallery_path . self::IMAGE_PATH . self::MEDIUM_PATH;
			case 'import':
				return self::$phpbb_gallery_relative . self::IMAGE_PATH . self::IMPORT_PATH;
			case 'import_noroot':
				return self::$phpbb_gallery_path . self::IMAGE_PATH . self::IMPORT_PATH;
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

		if (in_array($args[0], array('phpbb', 'admin', 'relative', 'full', 'board')))
		{
			$mode = array_shift($args);
			$args[0] = self::path($mode) . self::phpEx_file($args[0]);
		}
		else
		{
			$args[0] = self::path() . self::phpEx_file($args[0]);
		}
		if (isset($args[1]))
		{
			$args[1] .= phpbb_gallery::$display_popup;
		}

		$params = $args + array(
			0	=> '',
			1	=> phpbb_gallery::$display_popup,
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
			// Trying to break less MODs by populating the needed variables for inclusions
			global $phpbb_admin_path, $phpbb_root_path, $phpEx;

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

	/**
	* Creates beautiful relative path from ugly relative path
	* Resolves .. (up directory)
	*
	* @author	bantu		based on phpbb_own_realpath() by Chris Smith
	* @license	http://opensource.org/licenses/gpl-license.php GNU Public License
	*
	* @param	string		ugly path e.g. "../community/../gallery/"
	* @param	bool		is it a full url, so we need to fix teh http:// at the beginning?
	* @return	string		beautiful path e.g. "../gallery/"
	*/
	static public function beautiful_path($path, $is_full_url = false)
	{
		// Remove any repeated slashes
		$path = preg_replace('#/{2,}#', '/', $path);

		if ($is_full_url)
		{
			// Fix the double slash, which we just removed.
			if (strpos($path, 'https:/') === 0)
			{
				$path = 'https://' . substr($path, 7);
			}
			else if (strpos($path, 'http:/') === 0)
			{
				$path = 'http://' . substr($path, 6);
			}
		}

		// Break path into pieces
		$bits = explode('/', $path);

		// Lets get looping, run over and resolve any .. (up directory)
		for ($i = 0, $max = sizeof($bits); $i < $max; $i++)
		{
			if ($bits[$i] == '..' && isset($bits[$i - 1]) && $bits[$i - 1][0] != '.')
			{
				// We found a .. and we are able to traverse upwards ...
				unset($bits[$i]);
				unset($bits[$i - 1]);

				$i -= 2;
				$max -= 2;

				$bits = array_values($bits);
			}
		}

		return implode('/', $bits);
	}
}
