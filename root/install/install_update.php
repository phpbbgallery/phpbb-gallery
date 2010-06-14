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
		'module_type'		=> 'update',
		'module_title'		=> 'UPDATE',
		'module_filename'	=> substr(basename(__FILE__), 0, -strlen($phpEx)-1),
		'module_order'		=> 20,
		'module_subs'		=> '',
		'module_stages'		=> array('INTRO', 'REQUIREMENTS', 'UPDATE_DB', 'ADVANCED', 'FINAL'),
		'module_reqs'		=> ''
	);
}

/**
* Installation
* @package install
*/
class install_update extends module
{
	function install_update(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $cache, $template, $user;
		global $phpbb_root_path, $phpEx;

		if ($user->data['user_type'] != USER_FOUNDER)
		{
			trigger_error('FOUNDER_NEEDED', E_USER_ERROR);
		}

		$gallery_version = get_gallery_version();
		if (version_compare($gallery_version, '0.0.0', '>'))
		{
			if (!class_exists('phpbb_gallery_config'))
			{
				include($phpbb_root_path . GALLERY_ROOT_PATH . 'includes/config.' . $phpEx);
			}
		}
		else
		{
			trigger_error('NO_INSTALL_FOUND', E_USER_ERROR);
		}

		switch ($sub)
		{
			case 'intro':
				$this->page_title = $user->lang['SUB_INTRO'];

				$template->assign_vars(array(
					'TITLE'			=> $user->lang['UPDATE_INSTALLATION'],
					'BODY'			=> $user->lang['UPDATE_INSTALLATION_EXPLAIN'],
					'L_SUBMIT'		=> $user->lang['NEXT_STEP'],
					'U_ACTION'		=> append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=requirements"),
				));

			break;

			case 'requirements':
				$this->check_server_requirements($mode, $sub);

			break;

			case 'update_db':
				$database_step = request_var('step', 0);
				switch ($database_step)
				{
					case 0:
						$this->update_db_schema($mode, $sub);
					break;
					// updates starting from 0.2.0 up to 0.3.1
					// case 1: unsupported
					// updates starting from 0.3.2-RC1 up to 0.4.1
					case 2:
					// from 0.5.0
					case 3:
						$this->update_db_data($mode, $sub);
					break;
					case 4:
						$this->thinout_db_schema($mode, $sub);
					break;
				}
			break;

			case 'advanced':
				$this->obtain_advanced_settings($mode, $sub);

			break;

			case 'final':
				phpbb_gallery_config::set('version', NEWEST_PG_VERSION);
				$cache->purge();

				$template->assign_vars(array(
					'TITLE'		=> $user->lang['INSTALL_CONGRATS'],
					'BODY'		=> sprintf($user->lang['INSTALL_CONGRATS_EXPLAIN'], NEWEST_PG_VERSION) . $user->lang['PAYPAL_DEV_SUPPORT'],
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

		$passed = array('php' => false, 'files' => false, 'dirs' => false,);

		// Test for basic PHP settings
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $user->lang['PHP_SETTINGS'],
			'LEGEND_EXPLAIN'	=> $user->lang['PHP_SETTINGS_EXP'],
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

		// Test for optional PHP settings
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $user->lang['PHP_SETTINGS_OPTIONAL'],
			'LEGEND_EXPLAIN'	=> $user->lang['PHP_SETTINGS_OPTIONAL_EXP'],
		));

		// Image rotate
		if (function_exists('imagerotate'))
		{
			$result = '<strong style="color:green">' . $user->lang['YES'] . '</strong>';
		}
		else
		{
			$gd_info = gd_info();
			$result = '<strong style="color:red">' . $user->lang['NO'] . '</strong><br />' . sprintf($user->lang['OPTIONAL_IMAGEROTATE_EXP'], $gd_info['GD Version']);
		}
		$template->assign_block_vars('checks', array(
			'TITLE'			=> $user->lang['OPTIONAL_IMAGEROTATE'],
			'TITLE_EXPLAIN'	=> $user->lang['OPTIONAL_IMAGEROTATE_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
			'S_LEGEND'		=> false,
		));

		// Exif data
		if (function_exists('exif_read_data'))
		{
			$result = '<strong style="color:green">' . $user->lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . $user->lang['NO'] . '</strong><br />' . $user->lang['OPTIONAL_EXIFDATA_EXP'];
		}
		$template->assign_block_vars('checks', array(
			'TITLE'			=> $user->lang['OPTIONAL_EXIFDATA'],
			'TITLE_EXPLAIN'	=> $user->lang['OPTIONAL_EXIFDATA_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
			'S_LEGEND'		=> false,
		));

		// Check permissions on files/directories we need access to
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $user->lang['FILES_REQUIRED'],
			'LEGEND_EXPLAIN'	=> $user->lang['FILES_REQUIRED_EXPLAIN'],
		));

		$directories = array(
			GALLERY_IMPORT_PATH,
			GALLERY_UPLOAD_PATH,
			GALLERY_MEDIUM_PATH,
			GALLERY_CACHE_PATH,
		);

		umask(0);

		$passed['dirs'] = true;
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

			$passed['dirs'] = ($write && $passed['dirs']) ? true : false;

			$write = ($write) ? '<strong style="color:green">' . $user->lang['WRITABLE'] . '</strong>' : '<strong style="color:red">' . $user->lang['UNWRITABLE'] . '</strong>';

			$template->assign_block_vars('checks', array(
				'TITLE'		=> $dir,
				'RESULT'	=> $write,

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}

		// Check whether all old files are deleted
		include($phpbb_root_path . 'install/outdated_files.' . $phpEx);

		umask(0);

		$passed['files'] = true;
		$delete = (isset($_POST['delete'])) ? true : false;
		foreach ($oudated_files as $file)
		{
			// Replace gallery root path with the constant.
			if (strpos($file, 'gallery/') == 0)
			{
				$file = substr_replace($file, GALLERY_ROOT_PATH, 0, 8);
			}
			$file = preg_replace('/\.php$/i', ".$phpEx", $file);

			if ($delete)
			{
				if (@file_exists($phpbb_root_path . $file))
				{
					// Try to set CHMOD and then delete it
					@chmod($phpbb_root_path . $file, 0777);
					@unlink($phpbb_root_path . $file);
					// Delete failed, tell the user to delete it manually
					if (@file_exists($phpbb_root_path . $file))
					{
						if ($passed['files'])
						{
							$template->assign_block_vars('checks', array(
								'S_LEGEND'			=> true,
								'LEGEND'			=> $user->lang['FILES_OUTDATED'],
								'LEGEND_EXPLAIN'	=> $user->lang['FILES_OUTDATED_EXPLAIN'],
							));
						}
						$template->assign_block_vars('checks', array(
							'TITLE'		=> $file,
							'RESULT'	=> '<strong style="color:red">' . $user->lang['FILE_DELETE_FAIL'] . '</strong>',

							'S_EXPLAIN'	=> false,
							'S_LEGEND'	=> false,
						));
						$passed['files'] = false;
					}
				}
			}
			elseif (@file_exists($phpbb_root_path . $file))
			{
				if ($passed['files'])
				{
					$template->assign_block_vars('checks', array(
						'S_LEGEND'			=> true,
						'LEGEND'			=> $user->lang['FILES_OUTDATED'],
						'LEGEND_EXPLAIN'	=> $user->lang['FILES_OUTDATED_EXPLAIN'],
					));
				}
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $file,
					'RESULT'	=> '<strong style="color:red">' . $user->lang['FILE_STILL_EXISTS'] . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
				$passed['files'] = false;
			}
		}
		if (!$passed['files'])
		{
			$template->assign_block_vars('checks', array(
				'TITLE'			=> '<strong>' . $user->lang['FILES_DELETE_OUTDATED'] . '</strong>',
				'TITLE_EXPLAIN'	=> $user->lang['FILES_DELETE_OUTDATED_EXPLAIN'],
				'RESULT'		=> '<input class="button1" type="submit" id="delete" onclick="this.className = \'button1 disabled\';" name="delete" value="' . $user->lang['FILES_DELETE_OUTDATED'] . '" />',

				'S_EXPLAIN'	=> true,
				'S_LEGEND'	=> false,
			));
		}

		$url = (!in_array(false, $passed)) ? append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=update_db") : append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=requirements");
		$submit = (!in_array(false, $passed)) ? $user->lang['INSTALL_START'] : $user->lang['INSTALL_TEST'];

		$template->assign_vars(array(
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Add some Tables, Columns and Index to the database-schema
	*/
	function update_db_schema($mode, $sub)
	{
		global $db, $user, $template, $table_prefix;
		global $phpbb_root_path, $phpEx;

		$this->page_title = $user->lang['STAGE_UPDATE_DB'];

		$phpbb_gallery_version = get_gallery_version();

		phpbb_gallery_config::set('version', $phpbb_gallery_version);

		$dbms_data = get_dbms_infos();
		$db_schema = $dbms_data['db_schema'];
		$delimiter = $dbms_data['delimiter'];

		switch (phpbb_gallery_config::get('version'))
		{
			case '0.1.2':
			case '0.1.3':

			case '0.2.0':
			case '0.2.1':
			case '0.2.2':
			case '0.2.3':

			case '0.3.0':
			case '0.3.1':
			case '0.3.2-RC1':
			case '0.3.2-RC2':

			case '0.4.0-RC1':
			case '0.4.0-RC2':
			case '0.4.0-RC3':
			case '0.4.0':
				trigger_error('VERSION_NOT_SUPPORTED', E_USER_ERROR);
			break;

			case '0.4.1':
				nv_add_column(GALLERY_ALBUMS_TABLE,	'album_contest',			array('UINT', 0));

				nv_add_column(GALLERY_IMAGES_TABLE,	'filesize_upload',			array('UINT:20', 0));
				nv_add_column(GALLERY_IMAGES_TABLE,	'filesize_medium',			array('UINT:20', 0));
				nv_add_column(GALLERY_IMAGES_TABLE,	'filesize_cache',			array('UINT:20', 0));
				nv_add_column(GALLERY_IMAGES_TABLE,	'image_contest',			array('UINT:1', 0));
				nv_add_column(GALLERY_IMAGES_TABLE,	'image_exif_data',			array('TEXT', ''));

				nv_change_column(GALLERY_PERMISSIONS_TABLE,	'perm_system',	array('INT:3', 0));

				nv_create_table('phpbb_gallery_contests',	$dbms_data);

			case '0.5.0':
				nv_add_column(GALLERY_ALBUMS_TABLE,	'album_status',			array('UINT:1', 0));
				nv_add_column(GALLERY_ALBUMS_TABLE,	'display_in_rrc',		array('UINT:1', 1));
				nv_add_column(GALLERY_IMAGES_TABLE,	'image_contest_end',	array('TIMESTAMP', 0));
				nv_add_column(GALLERY_IMAGES_TABLE,	'image_contest_rank',	array('UINT:3', 0));
			case '0.5.1':
			case '0.5.2':
				nv_create_table('phpbb_gallery_albums_track',	$dbms_data);
				nv_add_column(GALLERY_USERS_TABLE,	'user_lastmark',		array('TIMESTAMP', 0));
			case '0.5.3':
				nv_add_column(LOG_TABLE,			'album_id',				array('UINT', 0));
				nv_add_column(LOG_TABLE,			'image_id',				array('UINT', 0));
			case '0.5.4':

			case '1.0.0-dev':
				nv_add_column(GALLERY_ROLES_TABLE,	'i_unlimited',			array('UINT:3', 0));
				nv_add_column(GALLERY_ROLES_TABLE,	'album_unlimited',		array('UINT:3', 0));
			case '1.0.0-RC1':
			case '1.0.0-RC2':
			case '1.0.0':

			case '1.0.1-dev':
				nv_add_column(GALLERY_IMAGES_TABLE,	'image_name_clean',		array('VCHAR:255', ''));
				nv_add_column(GALLERY_IMAGES_TABLE,	'image_username_clean',	array('VCHAR:255', ''));
			case '1.0.1':

			case '1.0.2-dev':
			case '1.0.2-RC1':
			case '1.0.2':

			case '1.0.3-RC1':
			case '1.0.3-RC2':
			case '1.0.3':

			case '1.0.4':
				nv_add_column(GALLERY_ALBUMS_TABLE,	'album_watermark',	array('UINT:1', 1));
				nv_add_column(GALLERY_ALBUMS_TABLE,	'album_sort_key',	array('VCHAR:8', ''));
				nv_add_column(GALLERY_ALBUMS_TABLE,	'album_sort_dir',	array('VCHAR:8', ''));
				nv_add_column(GALLERY_USERS_TABLE,	'user_viewexif',	array('UINT:1', 0));

			case '1.0.5-RC1':
				// Only allow update from 1.0.5
				trigger_error('VERSION_NOT_SUPPORTED', E_USER_ERROR);
			break;


			case '1.0.5':
			break;
		}

		$template->assign_vars(array(
			'BODY'		=> $user->lang['STAGE_CREATE_TABLE_EXPLAIN'],
			'L_SUBMIT'	=> $user->lang['NEXT_STEP'],
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=update_db&amp;step=2"),
		));
	}

	/**
	* Edit the data in the tables
	*/
	function update_db_data($mode, $sub)
	{
		global $cache, $db, $template, $user;
		global $phpbb_root_path, $phpEx, $table_prefix;
		include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);

		$database_step = request_var('step', 0);

		$this->page_title = $user->lang['STAGE_UPDATE_DB'];
		$next_update_url = '';

		switch (phpbb_gallery_config::get('version'))
		{
			case '0.1.2':
			case '0.1.3':

			case '0.2.0':
			case '0.2.1':
			case '0.2.2':
			case '0.2.3':

			case '0.3.0':
			case '0.3.1':
			case '0.3.2-RC1':
			case '0.3.2-RC2':

			case '0.4.0-RC1':
			case '0.4.0-RC2':
			case '0.4.0-RC3':
			case '0.4.0':
			case '0.4.1':
			case '0.5.0':
			case '0.5.1-dev':
			case '0.5.1':
			case '0.5.2-dev':
			case '0.5.2':
			case '0.5.3-dev':
			case '0.5.3':
			case '0.5.4':
			case '1.0.0-dev':
			case '1.0.0-RC1':
			case '1.0.0-RC2':
			case '1.0.0':
			case '1.0.1-dev':
			case '1.0.1':
			case '1.0.2-dev':
			case '1.0.2-RC1':
			case '1.0.2':
			case '1.0.3-RC1':
			case '1.0.3-RC2':
			case '1.0.3':
			case '1.0.4':
			case '1.0.5-RC1':
				/**
				* Cheating?
				*/
				trigger_error('VERSION_NOT_SUPPORTED', E_USER_ERROR);
			break;

			case '1.0.5':
				$sql = 'SELECT *
					FROM ' . GALLERY_CONFIG_TABLE;
				$result = $db->sql_query($sql);
				$old_config = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$old_config[$row['config_name']] = $row['config_value'];
				}
				$db->sql_freeresult($result);

				$others = array('gallery_total_images', 'gallery_viewtopic_icon', 'gallery_viewtopic_images', 'gallery_viewtopic_link');
				foreach ($others as $config_name)
				{
					if (isset($config[$config_name]))
					{
						$old_config[$config_name] = $config[$config_name];
					}
				}
				$db->sql_freeresult($result);


				$config_map = config_mapping();
				foreach ($config_map as $old_name => $new_name)
				{
					if (isset($old_config[$old_name]))
					{
						phpbb_gallery_config::set($new_name, $old_config[$old_name]);
					}
				}

				// Add new configs:
				$default_config = phpbb_gallery_config::get_default();
				foreach ($default_config as $name => $value)
				{
					if (!phpbb_gallery_config::exists($name))
					{
						phpbb_gallery_config::set($name, $value);
					}
				}

				$next_update_url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=update_db&amp;step=4");
			break;
		}

		$next_update_url = (!$next_update_url) ? append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=update_db&amp;step=4") : $next_update_url;

		$template->assign_vars(array(
			'BODY'		=> $user->lang['UPDATING_DATA'],
			'L_SUBMIT'	=> $user->lang['NEXT_STEP'],
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $next_update_url,
		));
	}

	/**
	* Remove some old columns, config values, permission-masks
	*/
	function thinout_db_schema($mode, $sub)
	{
		global $user, $template, $db, $phpbb_root_path, $phpEx;

		$this->page_title = $user->lang['STAGE_UPDATE_DB'];
		$reparse_modules_bbcode = false;

		switch (phpbb_gallery_config::get('version'))
		{
/*			case '0.1.2':
			case '0.1.3':
			case '0.2.0':
			case '0.2.1':
			case '0.2.2':
			case '0.2.3':
			case '0.3.0':
			case '0.3.1':
			case '0.3.2-RC1':
			case '0.3.2-RC2':
			case '0.4.0-RC1':
			case '0.4.0-RC2':
				nv_remove_column(GROUPS_TABLE,			'personal_subalbums');
				nv_remove_column(GROUPS_TABLE,			'allow_personal_albums');
				nv_remove_column(GROUPS_TABLE,			'view_personal_albums');
				nv_remove_column(USERS_TABLE,			'album_id');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_approval');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_order');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_view_level');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_upload_level');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_rate_level');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_comment_level');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_edit_level');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_delete_level');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_view_groups');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_upload_groups');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_rate_groups');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_comment_groups');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_edit_groups');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_delete_groups');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_moderator_groups');

			case '0.4.0-RC3':
			case '0.4.0':
			case '0.4.1':

			case '0.5.0':
			case '0.5.1':
			case '0.5.2':
			case '0.5.3':
			case '0.5.4':

			case '1.0.0-dev':
				nv_remove_column(GALLERY_ROLES_TABLE,	'a_moderate');

			case '1.0.0-RC1':
			case '1.0.0-RC2':
			case '1.0.0':

			case '1.0.1-dev':
			case '1.0.1':

			case '1.0.2-dev':
			case '1.0.2-RC1':
			case '1.0.2':

			case '1.0.3-RC1':
			case '1.0.3-RC2':
			case '1.0.3':

			case '1.0.4':

			case '1.0.5-RC1':*/

				//@todo: Move on bbcode-change or creating all modules
				//$reparse_modules_bbcode = true;
			case '1.0.5':
			break;
		}

		// Remove some old configs
		$old_configs = array('gallery_user_images_profil', 'gallery_personal_album_profil');
		$sql = 'DELETE FROM ' . CONFIG_TABLE . '
			WHERE ' . $db->sql_in_set('config_name', $old_configs);
		$db->sql_query($sql);

		// Remove some old p_masks
		$sql = 'SELECT perm_role_id
			FROM ' . GALLERY_PERMISSIONS_TABLE;
		$result = $db->sql_query($sql);

		$p_masks = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$p_masks[] = $row['perm_role_id'];
		}
		$db->sql_freeresult($result);

		$sql = 'DELETE FROM ' . GALLERY_ROLES_TABLE . '
			WHERE ' . $db->sql_in_set('role_id', $p_masks, true, true);
		$db->sql_query($sql);

		if ($reparse_modules_bbcode)
		{
			$next_update_url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=advanced");
		}
		else
		{
			$next_update_url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=final");
		}

		$template->assign_vars(array(
			'BODY'		=> $user->lang['UPDATE_DATABASE_SCHEMA'],
			'L_SUBMIT'	=> $user->lang['NEXT_STEP'],
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $next_update_url,
		));
	}

	/**
	* Provide an opportunity to customise some advanced settings during the install
	* in case it is necessary for them to be set to access later
	*/
	function obtain_advanced_settings($mode, $sub)
	{
		global $user, $template, $phpbb_root_path, $phpEx, $db;

		$gallery_config = load_gallery_config();

		$create = request_var('create', '');
		if ($create)
		{
			// Add modules
			$choosen_acp_module = request_var('acp_module', 0);
			$choosen_ucp_module = request_var('ucp_module', 0);
			$choosen_log_module = request_var('log_module', 0);

			switch (phpbb_gallery_config::get('version'))
			{
				case '0.1.2':
				case '0.1.3':

				case '0.2.0':
				case '0.2.1':
				case '0.2.2':
				case '0.2.3':

				case '0.3.0':
				case '0.3.1':
				case '0.3.2-RC1':
				case '0.3.2-RC2':

				case '0.4.0-RC1':
				case '0.4.0-RC2':
				case '0.4.0-RC3':
				case '0.4.0':
				break;

				case '0.4.1':
					// Logs
					$gallery_log = array('module_basename' => 'logs',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $choosen_log_module,	'module_class' => 'acp',	'module_langname' => 'ACP_GALLERY_LOGS',	'module_mode' => 'gallery',	'module_auth' => 'acl_a_viewlogs');
					add_module($gallery_log);

				case '0.5.0':
				case '0.5.1':
				case '0.5.2':
				case '0.5.3':
				case '0.5.4':

				case '1.0.0-dev':
				case '1.0.0-RC1':
				case '1.0.0-RC2':
				case '1.0.0':

				case '1.0.1-dev':
				case '1.0.1':

				case '1.0.2-dev':
				case '1.0.2-RC1':
					// Add album-BBCode
					add_bbcode('album');

				case '1.0.2':

				case '1.0.3-RC1':
				case '1.0.3-RC2':
				case '1.0.3':

				case '1.0.4':

				case '1.0.5-RC1':
					trigger_error('VERSION_NOT_SUPPORTED', E_USER_ERROR);
				case '1.0.5':
				break;
			}

			$s_hidden_fields = '';
			$url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=final");
		}
		else
		{
			$data = array(
				'acp_module'		=> MODULE_DEFAULT_ACP,
				'log_module'		=> MODULE_DEFAULT_LOG,
				'ucp_module'		=> MODULE_DEFAULT_UCP,
			);
			$modules = $this->gallery_config_options;
			switch (phpbb_gallery_config::get('version'))
			{
				case '1.0.5-RC1':
				case '1.0.5':

				case '1.0.4':

				case '1.0.3':
				case '1.0.3-RC2':
				case '1.0.3-RC1':

				case '1.0.2':
				case '1.0.2-RC1':
				case '1.0.2-dev':

				case '1.0.1':
					$template->assign_block_vars('checks', array(
						'S_LEGEND'			=> true,
						'LEGEND'			=> '',
						'LEGEND_EXPLAIN'	=> $user->lang['BBCODES_NEEDS_REPARSE'],
					));
				case '1.0.1-dev':

				case '1.0.0':
				case '1.0.0-RC2':
				case '1.0.0-RC1':
				case '1.0.0-dev':

				case '0.5.4':
				case '0.5.3':
				case '0.5.2':
				case '0.5.1':
				case '0.5.0':
					// needs to be moved before the first unset.
					unset($modules['legend1']);
					unset($modules['log_module']);

				case '0.4.1':
					unset($modules['acp_module']);
					unset($modules['ucp_module']);
					// We need to build all modules before this version
				break;
			}

			foreach ($modules as $config_key => $vars)
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
			$url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=advanced");
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