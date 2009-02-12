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
if (!defined('IN_INSTALL'))
{
	exit;
}

if (!empty($setmodules))
{
	$module[] = array(
		'module_type'		=> 'install',
		'module_title'		=> 'INSTALL',
		'module_filename'	=> substr(basename(__FILE__), 0, -strlen($phpEx)-1),
		'module_order'		=> 10,
		'module_subs'		=> '',
		'module_stages'		=> array('INTRO', 'REQUIREMENTS', 'CREATE_TABLE', 'ADVANCED', 'FINAL'),
		'module_reqs'		=> ''
	);
}

/**
* Installation
* @package install
*/
class install_install extends module
{
	function install_install(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $user, $template, $phpbb_root_path, $cache, $phpEx;

		switch ($sub)
		{
			case 'intro':
				$this->page_title = $user->lang['SUB_INTRO'];

				$template->assign_vars(array(
					'TITLE'			=> $user->lang['INSTALL_INTRO'],
					'BODY'			=> $user->lang['INSTALL_INTRO_BODY'],
					'L_SUBMIT'		=> $user->lang['NEXT_STEP'],
					'U_ACTION'		=> $this->p_master->module_url . "?mode=$mode&amp;sub=requirements",
				));

			break;

			case 'requirements':
				$this->check_server_requirements($mode, $sub);

			break;

			case 'create_table':
				$this->load_schema($mode, $sub);
			break;

			case 'advanced':
				$this->obtain_advanced_settings($mode, $sub);

			break;

			case 'final':
				set_gallery_config('phpbb_gallery_version', NEWEST_PG_VERSION, true);
				$cache->purge();

				$template->assign_vars(array(
					'TITLE'		=> $user->lang['INSTALL_CONGRATS'],
					'BODY'		=> sprintf($user->lang['INSTALL_CONGRATS_EXPLAIN'], NEWEST_PG_VERSION),
					'L_SUBMIT'	=> $user->lang['GOTO_GALLERY'],
					'U_ACTION'	=> append_sid($phpbb_root_path . GALLERY_ROOT_PATH . 'index.' . $phpEx),
				));


			break;
		}

		$this->tpl_name = 'install_install';
	}

	/**
	* Checks that the server we are installing on meets the requirements for running phpBB
	*/
	function check_server_requirements($mode, $sub)
	{
		global $user, $template, $phpbb_root_path, $phpEx;

		$this->page_title = $user->lang['STAGE_REQUIREMENTS'];

		$template->assign_vars(array(
			'TITLE'		=> $user->lang['REQUIREMENTS_TITLE'],
			'BODY'		=> $user->lang['REQUIREMENTS_EXPLAIN'],
		));

		$passed = array('php' => false, 'files' => false,);

		// Test for basic PHP settings
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $user->lang['PHP_SETTINGS'],
		));

		// Check for GD-Library
		if (@extension_loaded('gd') || can_load_dll('gd'))
		{
			$passed['php'] = true;
			$result = '<strong style="color:green">' . $user->lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . $user->lang['NO'] . '</strong>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $user->lang['REQ_GD_LIBRARY'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> false,
			'S_LEGEND'		=> false,
		));

		// Check permissions on files/directories we need access to
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $user->lang['FILES_REQUIRED'],
			'LEGEND_EXPLAIN'	=> $user->lang['FILES_REQUIRED_EXPLAIN'],
		));

		$directories = array(GALLERY_IMPORT_PATH, GALLERY_UPLOAD_PATH, GALLERY_MEDIUM_PATH, GALLERY_CACHE_PATH);

		umask(0);

		$passed['files'] = true;
		foreach ($directories as $dir)
		{
			$write = false;

			// Now really check
			if (file_exists($phpbb_root_path . $dir) && is_dir($phpbb_root_path . $dir))
			{
				if (!@is_writable($phpbb_root_path . $dir))
				{
					@chmod($phpbb_root_path . $dir, 0777);
				}
			}

			// Now check if it is writable by storing a simple file
			$fp = @fopen($phpbb_root_path . $dir . 'test_lock', 'wb');
			if ($fp !== false)
			{
				$write = true;
			}
			@fclose($fp);

			@unlink($phpbb_root_path . $dir . 'test_lock');

			$passed['files'] = ($write && $passed['files']) ? true : false;

			$write = ($write) ? '<strong style="color:green">' . $user->lang['WRITABLE'] . '</strong>' : '<strong style="color:red">' . $user->lang['UNWRITABLE'] . '</strong>';

			$template->assign_block_vars('checks', array(
				'TITLE'		=> $dir,
				'RESULT'	=> $write,

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}

		$url = (!in_array(false, $passed)) ? $this->p_master->module_url . "?mode=$mode&amp;sub=create_table" : $this->p_master->module_url . "?mode=$mode&amp;sub=requirements";
		$submit = (!in_array(false, $passed)) ? $user->lang['INSTALL_START'] : $user->lang['INSTALL_TEST'];

		$template->assign_vars(array(
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $url,
		));
	}


	/**
	* Load the contents of the schema into the database and then alter it based on what has been input during the installation
	*/
	function load_schema($mode, $sub)
	{
		global $user, $template, $phpbb_root_path, $phpEx, $cache;
		include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);

		$this->page_title = $user->lang['STAGE_CREATE_TABLE'];
		$s_hidden_fields = '';

		$dbms_data = get_dbms_infos();
		$db_schema = $dbms_data['db_schema'];
		$delimiter = $dbms_data['delimiter'];

		// Create the tables
		nv_create_table('phpbb_gallery_albums', $dbms_data);
		nv_create_table('phpbb_gallery_comments', $dbms_data);
		nv_create_table('phpbb_gallery_config', $dbms_data);
		nv_create_table('phpbb_gallery_contests', $dbms_data);
		nv_create_table('phpbb_gallery_favorites', $dbms_data);
		nv_create_table('phpbb_gallery_images', $dbms_data);
		nv_create_table('phpbb_gallery_modscache', $dbms_data);
		nv_create_table('phpbb_gallery_permissions', $dbms_data);
		nv_create_table('phpbb_gallery_rates', $dbms_data);
		nv_create_table('phpbb_gallery_reports', $dbms_data);
		nv_create_table('phpbb_gallery_roles', $dbms_data);
		nv_create_table('phpbb_gallery_users', $dbms_data);
		nv_create_table('phpbb_gallery_watch', $dbms_data);

		// Create session column
		nv_add_column(SESSIONS_TABLE, 'session_album_id', array('UINT', 0));

		nv_add_index(GALLERY_USERS_TABLE, 'pg_palbum_id', array('personal_album_id'));
		nv_add_index(SESSIONS_TABLE, 'session_aid', array('session_album_id'));

		// Set default config
		set_config('num_images', 0, true);
		set_config('gallery_total_images', 1);

		set_gallery_config('max_pics', '1024');
		set_gallery_config('max_file_size', '128000');
		set_gallery_config('max_width', '800');
		set_gallery_config('max_height', '600');
		set_gallery_config('rows_per_page', '3');
		set_gallery_config('cols_per_page', '4');
		set_gallery_config('thumbnail_quality', '50');
		set_gallery_config('thumbnail_size', '125');
		set_gallery_config('thumbnail_cache', '1');
		set_gallery_config('sort_method', 't');
		set_gallery_config('sort_order', 'd');
		set_gallery_config('jpg_allowed', '1');
		set_gallery_config('png_allowed', '1');
		set_gallery_config('gif_allowed', '0');
		set_gallery_config('desc_length', '512');
		set_gallery_config('hotlink_prevent', '0');
		set_gallery_config('hotlink_allowed', 'flying-bits.org');
		set_gallery_config('rate', '1');
		set_gallery_config('rate_scale', '10');
		set_gallery_config('comment', '1');
		set_gallery_config('gd_version', '2');
		set_gallery_config('watermark_images', 1);
		set_gallery_config('watermark_source', GALLERY_IMAGE_PATH . 'watermark.png');
		set_gallery_config('preview_rsz_height', 600);
		set_gallery_config('preview_rsz_width', 800);
		set_gallery_config('upload_images', 10);

		// Added 0.3.0
		set_gallery_config('thumbnail_info_line', 1);

		// Added 0.3.2-RC1
		set_gallery_config('fake_thumb_size', 70);
		set_gallery_config('disp_fake_thumb', 1);
		set_gallery_config('personal_counter', 0);
		set_gallery_config('exif_data', 1);
		set_gallery_config('watermark_height', 50);
		set_gallery_config('watermark_width', 200);

		// Added 0.4.0-RC3
		set_gallery_config('shorted_imagenames', 25);

		// Added 0.4.0
		set_gallery_config('comment_length', 1024);
		set_gallery_config('description_length', 1024);
		set_gallery_config('allow_rates', 1);
		set_gallery_config('allow_comments', 1);
		set_gallery_config('link_thumbnail', 'lytebox');
		set_gallery_config('link_image_name', 'image_page');
		set_gallery_config('link_image_icon', 'image_page');
		set_gallery_config('resize_images', 1);
		set_gallery_config('personal_album_index', 0);
		set_gallery_config('view_image_url', 1);
		set_gallery_config('medium_cache', 1);

		// Added 0.4.1
		set_gallery_config('link_imagepage', 'lytebox');

		// Added 0.5.0
		set_gallery_config('rrc_gindex_mode', 'all');
		set_gallery_config('rrc_gindex_rows', 1);
		set_gallery_config('rrc_gindex_columns', 4);
		set_gallery_config('rrc_gindex_comments', 0);

		// Added //@todo: 
		set_gallery_config('user_images_profil', 1);
		set_gallery_config('personal_album_profil', 1);
		set_gallery_config('rrc_profile_mode', '!comment');
		set_gallery_config('rrc_profile_columns', 4);
		set_gallery_config('rrc_profile_rows', 1);

		$auth_admin = new auth_admin();
		$auth_admin->acl_add_option(array(
			'local'			=> array(),
			'global'		=> array('a_gallery_manage', 'a_gallery_albums', 'a_gallery_import', 'a_gallery_cleanup')
		));
		$cache->destroy('acl_options');

		$submit = $user->lang['NEXT_STEP'];

		$url = $this->p_master->module_url . "?mode=$mode&amp;sub=advanced";

		$template->assign_vars(array(
			'BODY'		=> $user->lang['STAGE_CREATE_TABLE_EXPLAIN'],
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Provide an opportunity to customise some advanced settings during the install
	* in case it is necessary for them to be set to access later
	*/
	function obtain_advanced_settings($mode, $sub)
	{
		global $user, $template, $phpEx, $db;

		$create = request_var('create', '');
		if ($create)
		{
			// Add modules
			$choosen_acp_module = request_var('acp_module', 0);
			$choosen_log_module = request_var('log_module', 0);
			$choosen_ucp_module = request_var('ucp_module', 0);
			if ($choosen_acp_module < 0)
			{
				$acp_mods_tab = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => 0,	'module_class' => 'acp',	'module_langname'=> 'ACP_CAT_DOT_MODS',	'module_mode' => '',	'module_auth' => '');
				add_module($acp_mods_tab);
				$choosen_acp_module = $db->sql_nextid();
			}
			// ACP
			$acp_gallery = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $choosen_acp_module,	'module_class' => 'acp',	'module_langname'=> 'PHPBB_GALLERY',	'module_mode' => '',	'module_auth' => '');
			add_module($acp_gallery);
			$acp_module_id = $db->sql_nextid();
			set_gallery_config('acp_parent_module', $acp_module_id);

			$acp_gallery_overview = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_OVERVIEW',	'module_mode' => 'overview',	'module_auth' => 'acl_a_gallery_manage');
			add_module($acp_gallery_overview);
			$acp_configure_gallery = array('module_basename' => 'gallery_config',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_CONFIGURE_GALLERY',	'module_mode' => 'main',	'module_auth' => 'acl_a_gallery_manage');
			add_module($acp_configure_gallery);
			$acp_gallery_manage_albums = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_MANAGE_ALBUMS',	'module_mode' => 'manage_albums',	'module_auth' => 'acl_a_gallery_albums');
			add_module($acp_gallery_manage_albums);
			$album_permissions = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_ALBUM_PERMISSIONS',	'module_mode' => 'album_permissions',	'module_auth' => 'acl_a_gallery_albums');
			add_module($album_permissions);
			$import_images = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname'=> 'ACP_IMPORT_ALBUMS',	'module_mode' => 'import_images',	'module_auth' => 'acl_a_gallery_import');
			add_module($import_images);
			$cleanup = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_GALLERY_CLEANUP',	'module_mode' => 'cleanup',	'module_auth' => 'acl_a_gallery_cleanup');
			add_module($cleanup);

			// UCP
			$ucp_gallery_overview = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $choosen_ucp_module,	'module_class' => 'ucp',	'module_langname'=> 'UCP_GALLERY',	'module_mode' => 'overview',	'module_auth' => '');
			add_module($ucp_gallery_overview);
			$ucp_module_id = $db->sql_nextid();
			set_gallery_config('ucp_parent_module', $ucp_module_id);

			$ucp_gallery = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $ucp_module_id,	'module_class' => 'ucp',	'module_langname' => 'UCP_GALLERY_SETTINGS',	'module_mode' => 'manage_settings',	'module_auth' => '');
			add_module($ucp_gallery);
			$ucp_gallery = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $ucp_module_id,	'module_class' => 'ucp',	'module_langname' => 'UCP_GALLERY_PERSONAL_ALBUMS',	'module_mode' => 'manage_albums',	'module_auth' => '');
			add_module($ucp_gallery);
			$ucp_gallery = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $ucp_module_id,	'module_class' => 'ucp',	'module_langname' => 'UCP_GALLERY_WATCH',	'module_mode' => 'manage_subscriptions',	'module_auth' => '');
			add_module($ucp_gallery);
			$ucp_gallery = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $ucp_module_id,	'module_class' => 'ucp',	'module_langname' => 'UCP_GALLERY_FAVORITES',	'module_mode' => 'manage_favorites',	'module_auth' => '');
			add_module($ucp_gallery);

			// Logs
			$gallery_log = array('module_basename' => 'logs',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $choosen_log_module,	'module_class' => 'acp',	'module_langname' => 'ACP_GALLERY_LOGS',	'module_mode' => 'gallery',	'module_auth' => 'acl_a_viewlogs');
			add_module($gallery_log);

			// Add album-BBCode
			add_bbcode('album');
			$s_hidden_fields = '';
			$url = $this->p_master->module_url . "?mode=$mode&amp;sub=final";
		}
		else
		{
			$data = array(
				'acp_module'		=> 31,
				'ucp_module'		=> 0,
			);

			foreach ($this->gallery_config_options as $config_key => $vars)
			{
				if (!is_array($vars) && strpos($config_key, 'legend') === false)
				{
					continue;
				}

				if (strpos($config_key, 'legend') !== false)
				{
					$template->assign_block_vars('options', array(
						'S_LEGEND'		=> true,
						'LEGEND'		=> $user->lang[$vars])
					);

					continue;
				}

				$options = isset($vars['options']) ? $vars['options'] : '';
				$template->assign_block_vars('options', array(
					'KEY'			=> $config_key,
					'TITLE'			=> $user->lang[$vars['lang']],
					'S_EXPLAIN'		=> $vars['explain'],
					'S_LEGEND'		=> false,
					'TITLE_EXPLAIN'	=> ($vars['explain']) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '',
					'CONTENT'		=> $this->p_master->input_field($config_key, $vars['type'], $data[$config_key], $options),
					)
				);
			}
			$s_hidden_fields = '<input type="hidden" name="create" value="true" />';
			$url = $this->p_master->module_url . "?mode=$mode&amp;sub=advanced";
		}

		$submit = $user->lang['NEXT_STEP'];

		$template->assign_vars(array(
			'TITLE'		=> $user->lang['STAGE_ADVANCED'],
			'BODY'		=> $user->lang['STAGE_ADVANCED_EXPLAIN'],
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* The information below will be used to build the input fields presented to the user
	*/
	var $gallery_config_options = array(
		'legend1'				=> 'MODULES_PARENT_SELECT',
		'acp_module'			=> array('lang' => 'MODULES_SELECT_4ACP', 'type' => 'select', 'options' => 'module_select(\'acp\', 31, \'ACP_CAT_DOT_MODS\')', 'explain' => false),
		'log_module'			=> array('lang' => 'MODULES_SELECT_4LOG', 'type' => 'select', 'options' => 'module_select(\'acp\', 25, \'ACP_FORUM_LOGS\')', 'explain' => false),
		'ucp_module'			=> array('lang' => 'MODULES_SELECT_4UCP', 'type' => 'select', 'options' => 'module_select(\'ucp\', 0, \'\')', 'explain' => false),
	);
}

?>