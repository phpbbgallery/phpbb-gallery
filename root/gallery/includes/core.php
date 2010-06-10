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

class phpbb_gallery
{
	/**
	* Path from the gallery root, back to phpbb's root
	*/
	static $phpbb_root_path = '../';

	/**
	* Path from the phpbb root, into admin's root
	*/
	static $phpbb_admin_path = 'adm/';

	static $config = false;
	static $auth = false;

	/**
	* Static Constructor.
	*/
	public static function init($lang_set = false, $force_root_path = false)
	{
		global $auth, $config, $db, $template, $user, $cache;
		global $phpbb_admin_path, $phpbb_root_path, $phpEx;

		if ($force_root_path)
		{
			self::$phpbb_root_path = $force_root_path;
		}
		$phpbb_root_path = self::$phpbb_root_path;
		$phpbb_admin_path = self::$phpbb_root_path . self::$phpbb_admin_path;

		if ($lang_set != 'no_setup')
		{
			global $starttime;
			include($phpbb_root_path . 'common.' . $phpEx);
		}
		self::_include(array('auth', 'config', 'constants', 'functions', 'functions_phpbb', 'plugins'));
		gallery_plugins::init(self::path());

		if ($lang_set != 'no_setup')
		{
			$lang_sets = array('mods/info_acp_gallery');
			if (is_array($lang_set))
			{
				$lang_sets = array_merge($lang_sets, $lang_set);
			}
			elseif (is_string($lang_set))
			{
				$lang_sets[] = $lang_set;
			}

			// Start session management
			$user->session_begin();
			$auth->acl($user->data);
			$user->setup($lang_sets);
		}

		// Little precaution.
		$user->data['user_id'] = (int) $user->data['user_id'];

		self::$auth = new phpbb_gallery_auth();
		self::$auth->init(($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from']);

		$user->gallery = self::load_user($user->data['user_id']);

		if ($lang_set != 'no_setup')
		{
			if (phpbb_gallery_config::get('mvc_version') + 86400 < time())
			{
				// Scare the user of outdated versions
				if (!function_exists('mod_version_check'))
				{
					self::_include('functions_version_check');
				}
				phpbb_gallery_config::set('mvc_time', time());
				phpbb_gallery_config::set('mvc_version', mod_version_check(true));
			}

			if ($auth->acl_get('a_') && version_compare(phpbb_gallery_config::get('version'), phpbb_gallery_config::get('mvc_version'), '<'))
			{
				$user->add_lang('mods/gallery_acp');
				$template->assign_var('GALLERY_VERSION_CHECK', sprintf($user->lang['NOT_UP_TO_DATE'], $user->lang['GALLERY']));
			}

			$template->assign_vars(array(
				'S_IN_GALLERY'					=> true,
				'U_GALLERY_SEARCH'				=> self::append_sid('search'),
				'GALLERY_TRANSLATION_INFO'		=> (!empty($user->lang['GALLERY_TRANSLATION_INFO'])) ? $user->lang['GALLERY_TRANSLATION_INFO'] : '',
			));

			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $user->lang['GALLERY'],
				'U_VIEW_FORUM'	=> self::append_sid('index'),
			));
		}
	}

	public static function path($directory = 'gallery')
	{
		switch ($directory)
		{
			case 'gallery':
				global $phpbb_root_path;
				return $phpbb_root_path . GALLERY_ROOT_PATH;
			case 'phpbb':
				global $phpbb_root_path;
				return $phpbb_root_path;
			case 'admin':
				global $phpbb_admin_path;
				return $phpbb_admin_path;
			case 'relative':
				return GALLERY_ROOT_PATH;
			case 'full':
				return generate_board_url() . '/' . GALLERY_ROOT_PATH;
		}

		return false;
	}

	public static function phpEx_file($file)
	{
		if ((substr($file, -1) == '/') || (strlen($file) == 0))
		{
			// it's no file, so no .php here.
			return $file;
		}

		global $phpEx;
		return $file . '.' . $phpEx;
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
		return self::append_sid($path, $file, $params, $is_amp, '');
	}

	public static function redirect()
	{
		redirect(self::append_sid(func_get_args()));
	}

	public static function load_user($user_id)
	{
		global $db;

		$sql = 'SELECT *
			FROM ' . GALLERY_USERS_TABLE . '
			WHERE user_id = ' . (int) $user_id;
		$result = $db->sql_query($sql);
		$array = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($db->sql_affectedrows())
		{
			$array = array_map('intval', $array);
			$array['exists'] = true;
		}

		return $array;
	}
}
