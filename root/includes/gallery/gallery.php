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

	static public $auth = null;
	static public $user = null;

	static public $display_popup = '';
	static public $loaded = false;

	/**
	* Constructor: setup() also creates a phpbb-session, if you already have one, be sure to use init()
	*/
	static public function setup($lang_set = false, $update_session = true)
	{
		global $auth, $db, $template, $user, $cache;

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
		$user->session_begin($update_session);
		/**
		* Maybe we need this for the feed
		if (!empty($config['feed_http_auth']) && request_var('auth', '') == 'http')
		{
			phpbb_http_login(array(
				'auth_message'	=> 'Feed',
				'viewonline'	=> request_var('viewonline', true),
			));
		}
		*/
		$auth->acl($user->data);
		$user->setup($lang_sets);

		phpbb_gallery_url::_include('functions_phpbb', 'phpbb', 'includes/gallery/');
		phpbb_gallery_plugins::init(phpbb_gallery_url::path());

		// Little precaution.
		$user->data['user_id'] = (int) $user->data['user_id'];

		self::$user = new phpbb_gallery_user($db, $user->data['user_id']);

		$user_id = ($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from'];
		self::$auth = new phpbb_gallery_auth($user_id);

		if (phpbb_gallery_config::get('mvc_time') < time())
		{
			// Check the version, do we need to update?
			phpbb_gallery_config::set('mvc_version', phpbb_gallery_modversioncheck::check(true));
			phpbb_gallery_config::set('mvc_time', time() + 86400);
		}

		if (phpbb_gallery_config::get('prune_orphan_time') < time())
		{
			// Delete orphan uploaded files, which are older than half an hour...
			phpbb_gallery_upload::prune_orphan();
			phpbb_gallery_config::set('prune_orphan_time', time() + 1800);
		}

		if ($auth->acl_get('a_') && version_compare(phpbb_gallery_config::get('version'), phpbb_gallery_config::get('mvc_version'), '<'))
		{
			$user->add_lang('mods/gallery_acp');
			$template->assign_var('GALLERY_VERSION_CHECK', sprintf($user->lang['NOT_UP_TO_DATE'], $user->lang['GALLERY']));
		}

		if (request_var('display', '') == 'popup')
		{
			self::init_popup();
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

		self::$loaded = true;
	}

	/**
	* Sets up some basic stuff for the gallery.
	*/
	static public function init()
	{
		global $db, $user;

		phpbb_gallery_url::_include('functions_phpbb', 'phpbb', 'includes/gallery/');
		phpbb_gallery_plugins::init(phpbb_gallery_url::path());

		// Little precaution.
		$user->data['user_id'] = (int) $user->data['user_id'];

		self::$user = new phpbb_gallery_user($db, $user->data['user_id']);

		$user_id = ($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from'];
		self::$auth = new phpbb_gallery_auth($user_id);

		if (phpbb_gallery_config::get('mvc_time') < time())
		{
			// Check the version, do we need to update?
			phpbb_gallery_config::set('mvc_time', time() + 86400);
			phpbb_gallery_config::set('mvc_version', phpbb_gallery_modversioncheck::check(true));
		}

		self::$loaded = true;
		if (request_var('display', '') == 'popup')
		{
			self::init_popup();
		}

	}

	/**
	* Sets up some basic stuff for the gallery.
	*/
	static public function init_popup()
	{
		global $template, $user;

		self::$display_popup = '&amp;display=popup';
		$template->assign_vars(array(
			'S_IN_GALLERY_POPUP'			=> (request_var('display', '') == 'popup') ? true : false,

			'U_POPUP_OWN'		=> phpbb_gallery_url::append_sid('search', 'user_id=' . (int) $user->data['user_id'] . '&amp;display=popup'),
			'U_POPUP_RECENT'	=> phpbb_gallery_url::append_sid('search', 'search_id=recent&amp;display=popup'),
//			'U_POPUP_UPLOAD'	=> phpbb_gallery_url::append_sid('posting', 'mode=upload&amp;display=popup'),
		));
	}
}
