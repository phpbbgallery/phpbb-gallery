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

class phpbb_ext_gallery_core
{
	// We still need this, as we can not guess that.

	private $phpbb_auth;
	private $phpbb_cache;
	private $phpbb_config;
	private $phpbb_db;
	private $phpbb_template;
	private $phpbb_user;

	private $phpbb_phpEx;
	private $phpbb_root_path;

	public $auth;
	public $user;
	public $cache;
	const SEARCH_PAGES_NUMBER = 10;

	//@todo: public $display_popup = false;

	/**
	* Constructor
	*/
	public function __construct(phpbb_auth $auth, phpbb_cache_service $cache,
		phpbb_config $config, dbal $db, phpbb_template $template,
		phpbb_user $user, $phpEx, $phpbb_root_path)
	{
		$this->phpbb_auth = $auth;
		$this->phpbb_cache = $cache;
		$this->phpbb_config = $config;
		$this->phpbb_db = $db;
		$this->phpbb_template = $template;
		$this->phpbb_user = $user;

		$this->phpbb_phpEx = $phpEx;
		$this->phpbb_root_path = $phpbb_root_path;

		$this->url = new phpbb_ext_gallery_core_url($this->phpbb_root_path, $this->phpbb_phpEx);
		$this->config = new phpbb_ext_gallery_core_config($this->phpbb_config, $this->phpbb_db, CONFIG_TABLE);
		$this->cache = new phpbb_ext_gallery_core_cache($this->phpbb_cache, $this->phpbb_db);
	}

	/**
	* setup() also creates a phpbb-session, if you already have one, be sure to use init()
	*/
	public function setup($lang_set = false, $update_session = true)
	{
		$lang_sets = array('common');
		if (is_array($lang_set))
		{
			$lang_sets = array_merge($lang_sets, $lang_set);
		}
		elseif (is_string($lang_set))
		{
			$lang_sets[] = $lang_set;
		}

		// Start session management
		$this->phpbb_user->session_begin($update_session);
		$this->phpbb_auth->acl($this->phpbb_user->data);
		$this->phpbb_user->setup($lang_sets);

		$this->phpbb_user->add_lang_ext('gallery/core', array('gallery', 'info_acp_gallery'));

		//@todo: $this->url->_include('functions_phpbb', 'phpbb', 'includes/gallery/');
		//@todo: phpbb_gallery_plugins::init($this->url->path());

		// Little precaution.
		$this->phpbb_user->data['user_id'] = (int) $this->phpbb_user->data['user_id'];
		$user_id = ($this->phpbb_user->data['user_perm_from'] == 0) ? $this->phpbb_user->data['user_id'] : (int) $this->phpbb_user->data['user_perm_from'];

		$this->user = new phpbb_ext_gallery_core_user($this->phpbb_db, $this->phpbb_user->data['user_id']);
		$this->auth = new phpbb_ext_gallery_core_auth($this->cache, $this->user, $this->phpbb_db, $this->phpbb_template, $this->phpbb_user, $user_id);

		if ($this->config->get('mvc_time') < time())
		{
			// Check the version, do we need to update?
			//@todo: $this->config->set('mvc_version', phpbb_gallery_modversioncheck::check(true));
			$this->config->set('mvc_time', time() + 86400);
		}

		if ($this->config->get('prune_orphan_time') < time())
		{
			// Delete orphan uploaded files, which are older than half an hour...
			//@todo: phpbb_gallery_upload::prune_orphan();
			$this->config->set('prune_orphan_time', time() + 1800);
		}

		if ($this->phpbb_auth->acl_get('a_'))
		{
			$mvc_ignore = request_var('mvc_ignore', '');
			if (!$this->config->get('mvc_ignore') && check_link_hash($mvc_ignore, 'mvc_ignore'))
			{
				// Ignore the warning for 7 days
				$this->config->set('mvc_ignore', time() + 3600 * 24 * 7);
			}
			else if (!$this->config->get('mvc_ignore') || $this->config->get('mvc_ignore') < time())
			{
				if (version_compare($this->config->get('version'), $this->config->get('mvc_version'), '<'))
				{
					$this->phpbb_user->add_lang('mods/gallery_acp');
					$this->phpbb_template->assign_var('GALLERY_VERSION_CHECK', sprintf($this->phpbb_user->lang['NOT_UP_TO_DATE'], $this->phpbb_user->lang['GALLERY']));
				}
				if ($this->config->get('mvc_ignore'))
				{
					$this->config->set('mvc_ignore', 0);
				}
			}
		}

		if (request_var('display', '') == 'popup')
		{
			$this->init_popup();
		}

		$this->phpbb_template->assign_vars(array(
			'S_IN_GALLERY'					=> true,
			'U_GALLERY_SEARCH'				=> $this->url->append_sid('search'),
			'U_MVC_IGNORE'					=> ($this->phpbb_auth->acl_get('a_') && !$this->config->get('mvc_ignore')) ? $this->url->append_sid('index', 'mvc_ignore=' . generate_link_hash('mvc_ignore')) : '',
			'GALLERY_TRANSLATION_INFO'		=> (!empty($this->user->lang['GALLERY_TRANSLATION_INFO'])) ? $this->user->lang['GALLERY_TRANSLATION_INFO'] : '',
		));

		$this->phpbb_template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> 'Gallery Ext',//@todo: $this->phpbb_user->lang['GALLERY'],
			'U_VIEW_FORUM'	=> $this->url->append_sid('index'),
		));

		global $phpbb_dispatcher;
		$phpbb_dispatcher->trigger_event('gallery.core.setup');
	}

	/**
	* Sets up some basic stuff for the gallery.
	*/
	public function init()
	{
		//@todo: phpbb_gallery_url::_include('functions_phpbb', 'phpbb', 'includes/gallery/');
		//@todo: phpbb_gallery_plugins::init(phpbb_gallery_url::path());

		// Little precaution.
		$this->phpbb_user->data['user_id'] = (int) $this->phpbb_user->data['user_id'];
		$user_id = ($this->phpbb_user->data['user_perm_from'] == 0) ? $this->phpbb_user->data['user_id'] : (int) $this->phpbb_user->data['user_perm_from'];

		$this->user = new phpbb_ext_gallery_core_user($this->phpbb_db, $this->phpbb_user->data['user_id']);
		$this->auth = new phpbb_ext_gallery_core_auth($this->cache, $this->user, $this->phpbb_db, $this->phpbb_template, $this->phpbb_user, $user_id);

		if ($this->config->get('mvc_time') < time())
		{
			// Check the version, do we need to update?
			$this->config->set('mvc_time', time() + 86400);
			//@todo: $this->config->set('mvc_version', phpbb_gallery_modversioncheck::check(true));
		}

		if (request_var('display', '') == 'popup')
		{
			$this->init_popup();
		}

		global $phpbb_dispatcher;
		$phpbb_dispatcher->trigger_event('gallery.core.init');
	}

	/**
	* Sets up some basic stuff for the gallery.
	*/
	public function init_popup()
	{
		global $template, $user;

		self::$display_popup = '&amp;display=popup';

		$can_upload = $this->auth->acl_album_ids('i_upload', 'bool');

		$template->assign_vars(array(
			'S_IN_GALLERY_POPUP'			=> (request_var('display', '') == 'popup') ? true : false,

			'U_POPUP_OWN'		=> $this->url->append_sid('search', 'user_id=' . (int) $user->data['user_id']),
			'U_POPUP_RECENT'	=> $this->url->append_sid('search', 'search_id=recent'),
			'U_POPUP_UPLOAD'	=> ($can_upload) ? $this->url->append_sid('posting', 'mode=upload') : '',
		));

		global $phpbb_dispatcher;
		$phpbb_dispatcher->trigger_event('gallery.core.init_popup');
	}
}
