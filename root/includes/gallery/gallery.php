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
	static private $phpbb_root_path = '../';

	static public $auth = false;

	/**
	* Constructor: setup() also creates a phpbb-session, if you already have one, be sure to use init()
	*/
	static public function setup($lang_set = false)
	{
		global $auth, $db, $template, $user, $cache;

		phpbb_gallery_url::_include('functions_phpbb');
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

		$user->gallery = phpbb_gallery_user::get_settings($user->data['user_id']);

		if (phpbb_gallery_config::get('mvc_time') < time())
		{
			// Check the version, do we need to update?
			phpbb_gallery_config::set('mvc_time', time() + 86400);
			phpbb_gallery_config::set('mvc_version', phpbb_gallery_modversioncheck::check(true));
		}

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

	/**
	* Sets up some basic stuff for the gallery.
	*/
	static public function init()
	{
		global $user;

		phpbb_gallery_url::_include('functions_phpbb');
		phpbb_gallery_plugins::init(phpbb_gallery_url::path());

		// Little precaution.
		$user->data['user_id'] = (int) $user->data['user_id'];

		self::$auth = new phpbb_gallery_auth();
		self::$auth->init(($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from']);

		$user->gallery = phpbb_gallery_user::get_settings($user->data['user_id']);

		if (phpbb_gallery_config::get('mvc_time') < time())
		{
			// Check the version, do we need to update?
			phpbb_gallery_config::set('mvc_time', time() + 86400);
			phpbb_gallery_config::set('mvc_version', phpbb_gallery_modversioncheck::check(true));
		}
	}
}
