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
	static $phpbb_root_path = '../';
	static $phpbb_admin_path = 'adm/';
	static $gallery_config_prefix = 'phpbb_gallery_';

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
			include($phpbb_root_path . 'common.' . $phpEx);
		}
		self::_include(array('auth', 'constants', 'functions', 'functions_phpbb', 'plugins'));
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
			if (self::config('version_check_time') + 86400 < time())
			{
				// Scare the user of outdated versions
				if (!function_exists('mod_version_check'))
				{
					self::_include('functions_version_check');
				}
				self::set_config('version_check_time', time());
				self::set_config('version_check_version', mod_version_check(true));
			}

			if ($auth->acl_get('a_') && version_compare(self::config('phpbb_gallery_version'), self::config('version_check_version'), '<'))
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

	public static function _include($file, $path = 'gallery', $sub_directory = 'includes/')
	{
		if (!is_array($file))
		{
			global $phpEx;
			include(self::path($path) . $sub_directory . $file . '.' . $phpEx);
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
		global $phpEx;
		return file_exists(self::path($path) . $sub_directory . $file . '.' . $phpEx);
	}

	public static function _return_file($file, $path = 'gallery', $sub_directory = 'includes/')
	{
		global $phpEx;
		return self::path($path) . $sub_directory . $file . '.' . $phpEx;
	}

	public static function append_sid()
	{
		global $phpEx;

		$args = func_get_args();
		if (is_array($args[0]))
		{
			// Little problem from the duplicated call to func_get_args();
			$args = $args[0];
		}

		if ($args[0] == 'phpbb')
		{
			$mode = array_shift($args);
			$args[0] = self::path('phpbb') . $args[0] . '.' . $phpEx;
		}
		else if ($args[0] == 'admin')
		{
			$mode = array_shift($args);
			$args[0] = self::path('admin') . $args[0] . '.' . $phpEx;
		}
		else if ($args[0] == 'relative')
		{
			$mode = array_shift($args);
			$args[0] = self::path('relative') . $args[0] . '.' . $phpEx;
		}
		else
		{
			$args[0] = self::path() . $args[0] . '.' . $phpEx;
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

	public static function config($key)
	{
		if (self::$config === false)
		{
			self::load_config();
		}

		return self::$config[$key];
	}

	public static function load_config()
	{
		global $config;

		foreach ($config as $config_name => $config_value)
		{
			// Load all config values of the gallery
			if (strpos($config_name, self::$gallery_config_prefix) === 0)
			{
				self::$config[substr($config_name, strlen(self::$gallery_config_prefix))] = $config_value;
			}
		}

		// Drop when the config is moved:
		global $db;

		// When addons are installed, before the install script is run, this would through an error.
		$db->sql_return_on_error(true);
		$sql = 'SELECT *
			FROM ' . GALLERY_CONFIG_TABLE;
		$result = $db->sql_query($sql);
		$db->sql_return_on_error(false);

		if ($result === false)
		{
			trigger_error('INVALID_CONFIG_CALL');
		}

		self::$config = array();
		while ($row = $db->sql_fetchrow($result))
		{
			self::$config[$row['config_name']] = $row['config_value'];
		}
		$db->sql_freeresult($result);
	}

	public static function set_config($config_name, $config_value, $is_dynamic = false)
	{
		set_config(self::$gallery_config_prefix . $config_name, $config_value, $is_dynamic);
		self::$config[$config_name] = $config_value;
	}

	public static function set_config_count($config_name, $increment, $is_dynamic = false)
	{
		set_config_count(self::$gallery_config_prefix . $config_name, $increment, $is_dynamic);
		self::$config[$config_name] += $config_value;
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
