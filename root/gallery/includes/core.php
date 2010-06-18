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
	// We still need this, as we can not guess that.
	private static $phpbb_root_path = '../';

	static $config = false;
	static $auth = false;

	/**
	* Constructor: setup() also creates a phpbb-session, if you already have one, be sure to use init()
	*/
	public static function setup($lang_set = false, $force_root_path = false)
	{
		global $auth, $config, $db, $template, $user, $cache;
		global $phpbb_admin_path, $phpbb_root_path, $phpEx;

		if ($force_root_path)
		{
			self::$phpbb_root_path = $force_root_path;
		}
		$phpbb_root_path = self::$phpbb_root_path;

		global $starttime;
		include($phpbb_root_path . 'common.' . $phpEx);

		if (!class_exists('phpbb_gallery_url'))
		{
			include($phpbb_root_path . GALLERY_ROOT_PATH . 'includes/core/url.' . $phpEx);
		}

		phpbb_gallery_url::_include_core(array('auth', 'config', 'plugins'));
		phpbb_gallery_url::_include(array('constants', 'functions', 'functions_phpbb'));
		phpbb_gallery_plugins::init(phpbb_gallery_url::path());

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

		// Little precaution.
		$user->data['user_id'] = (int) $user->data['user_id'];

		self::$auth = new phpbb_gallery_auth();
		self::$auth->init(($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from']);

		$user->gallery = self::load_user($user->data['user_id']);

		if (phpbb_gallery_config::get('mvc_time') < time())
		{
			// Check the version, do we need to update?
			if (!function_exists('mod_version_check'))
			{
				phpbb_gallery_url::_include('functions_version_check');
			}
			phpbb_gallery_config::set('mvc_time', time() + 86400);
			phpbb_gallery_config::set('mvc_version', mod_version_check(true));
		}

		if ($lang_set != 'no_setup')
		{
			if ($auth->acl_get('a_') && version_compare(phpbb_gallery_config::get('version'), phpbb_gallery_config::get('mvc_version'), '<'))
			{
				$user->add_lang('mods/gallery_acp');
				$template->assign_var('GALLERY_VERSION_CHECK', sprintf($user->lang['NOT_UP_TO_DATE'], $user->lang['GALLERY']));
			}

			$template->assign_vars(array(
				'S_IN_GALLERY'					=> true,
				'U_GALLERY_SEARCH'				=> phpbb_gallery_url::append_sid('search'),
				'GALLERY_TRANSLATION_INFO'		=> (!empty($user->lang['GALLERY_TRANSLATION_INFO'])) ? $user->lang['GALLERY_TRANSLATION_INFO'] : '',
			));

			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $user->lang['GALLERY'],
				'U_VIEW_FORUM'	=> phpbb_gallery_url::append_sid('index'),
			));
		}
	}

	/**
	* Sets up some basic stuff for the gallery.
	*/
	public static function init($force_root_path = false)
	{
		global $user, $phpbb_root_path, $phpEx;

		if ($force_root_path)
		{
			self::$phpbb_root_path = $force_root_path;
		}
		$phpbb_root_path = self::$phpbb_root_path;

		if (!class_exists('phpbb_gallery_url'))
		{
			include($phpbb_root_path . GALLERY_ROOT_PATH . 'includes/core/url.' . $phpEx);
		}

		phpbb_gallery_url::_include_core(array('auth', 'config', 'hookup', 'plugins'));
		phpbb_gallery_url::_include(array('constants', 'functions', 'functions_phpbb'));
		phpbb_gallery_plugins::init(phpbb_gallery_url::path());

		// Little precaution.
		$user->data['user_id'] = (int) $user->data['user_id'];

		self::$auth = new phpbb_gallery_auth();
		self::$auth->init(($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from']);

		$user->gallery = self::load_user($user->data['user_id']);

		if (phpbb_gallery_config::get('mvc_time') < time())
		{
			// Check the version, do we need to update?
			if (!function_exists('mod_version_check'))
			{
				phpbb_gallery_url::_include('functions_version_check');
			}
			phpbb_gallery_config::set('mvc_time', time() + 86400);
			phpbb_gallery_config::set('mvc_version', mod_version_check(true));
		}
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
