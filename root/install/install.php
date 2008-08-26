<?php

/**
*
* @package phpBB3 - phpBB Gallery database updater
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'install/common.' . $phpEx);

$load_step = request_var('step', 1);
$confirm = request_var('confirm', 0);
$submit = ((isset($_POST['submit']) && $confirm) || ($load_step > 1)) ? true : false;
$message = $select_ucp_module = $default_ucp_module = $select_acp_module = $default_acp_module = '';
$choosen_acp_module = request_var('select_acp_module', 0);
$choosen_ucp_module = request_var('select_ucp_module', 0);
$full_install_steps = 6;

$template->assign_vars(array(
	'S_IN_INSTALL'			=> true,
	'U_ACTION'				=> append_sid("{$phpbb_root_path}install/install.php"),
));

if ($submit)
{
	switch ($load_step)
	{
		case 1:
			/*
			* create the db-structure
			*/
			nv_create_table('phpbb_gallery_albums', true);
			nv_create_table('phpbb_gallery_comments', true);
			nv_create_table('phpbb_gallery_config', true);
			nv_create_table('phpbb_gallery_favorites', true);
			nv_create_table('phpbb_gallery_images', true);
			nv_create_table('phpbb_gallery_modscache', true);
			nv_create_table('phpbb_gallery_permissions', true);
			nv_create_table('phpbb_gallery_rates', true);
			nv_create_table('phpbb_gallery_reports', true);
			nv_create_table('phpbb_gallery_roles', true);
			nv_create_table('phpbb_gallery_users', true);
			nv_create_table('phpbb_gallery_watch', true);

			// session_album_id to handle viewonline.php
			nv_add_column(SESSIONS_TABLE, 'session_album_id', array('UINT', 0));

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_install_steps, 1, $user->lang['STEPS_DBSCHEMA'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 2:
			/*
			* Create Gallery Config
			*/
			gallery_config_value('max_pics', '1024');
			gallery_config_value('max_file_size', '128000');
			gallery_config_value('max_width', '800');
			gallery_config_value('max_height', '600');
			gallery_config_value('rows_per_page', '3');
			gallery_config_value('cols_per_page', '4');
			gallery_config_value('thumbnail_quality', '50');
			gallery_config_value('thumbnail_size', '125');
			gallery_config_value('thumbnail_cache', '1');
			gallery_config_value('sort_method', 'image_time');
			gallery_config_value('sort_order', 'DESC');
			gallery_config_value('jpg_allowed', '1');
			gallery_config_value('png_allowed', '1');
			gallery_config_value('gif_allowed', '0');
			gallery_config_value('desc_length', '512');
			gallery_config_value('hotlink_prevent', '0');
			gallery_config_value('hotlink_allowed', 'flying-bits.org');
			gallery_config_value('rate', '1');
			gallery_config_value('rate_scale', '10');
			gallery_config_value('comment', '1');
			gallery_config_value('gd_version', '2');
			gallery_config_value('watermark_images', 1);
			gallery_config_value('watermark_source', 'gallery/mark.png');
			gallery_config_value('preview_rsz_height', 600);
			gallery_config_value('preview_rsz_width', 800);
			gallery_config_value('upload_images', 10);
			gallery_config_value('thumbnail_info_line', 1);
			gallery_config_value('fake_thumb_size', 70);
			gallery_config_value('disp_fake_thumb', 1);
			gallery_config_value('personal_counter', 0);
			gallery_config_value('exif_data', 1);
			gallery_config_value('watermark_height', 50);
			gallery_config_value('watermark_width', 200);
			set_config('num_images', 1, true);
			set_config('gallery_total_images', 1);
			set_config('gallery_user_images_profil', 1);
			set_config('gallery_personal_album_profil', 1);

			$album_config = load_album_config();
			$gd_check = function_exists('gd_info') ? gd_info() : array();
			$gd_success = isset($gd_check['GD Version']);
			if (!$gd_success && ($album_config['gd_version'] > 0))
			{
				$sql = 'UPDATE ' . GALLERY_CONFIG_TABLE . "SET config_value = 0 WHERE config_name = 'gd_version'";
				$result = $db->sql_query($sql);
				$album_config['gd_version'] = 0;
			}

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_install_steps, 2, $user->lang['STEPS_ADD_CONFIGS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 3:
			/*
			* handle the modules
			*/
			// delete old ones
			$sql = 'SELECT module_id, module_class, left_id, right_id
				FROM ' . MODULES_TABLE . '
				WHERE ' . $db->sql_in_set('module_langname', $module_names) . '
				ORDER BY left_id DESC';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				remove_module($row['module_id'], $row['module_class']);
			}
			$db->sql_freeresult($result);

			// where to add the new ones?
			$choosen_acp_module = request_var('select_acp_module', 0);
			$choosen_ucp_module = request_var('select_ucp_module', 0);
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
			gallery_config_value('acp_parent_module', $acp_module_id);

			$acp_gallery_overview = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_OVERVIEW',	'module_mode' => 'overview',	'module_auth' => '');
			add_module($acp_gallery_overview);
			$acp_configure_gallery = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_CONFIGURE_GALLERY',	'module_mode' => 'configure_gallery',	'module_auth' => '');
			add_module($acp_configure_gallery);
			$acp_gallery_manage_albums = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_MANAGE_ALBUMS',	'module_mode' => 'manage_albums',	'module_auth' => '');
			add_module($acp_gallery_manage_albums);
			$album_permissions = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_ALBUM_PERMISSIONS',	'module_mode' => 'album_permissions',	'module_auth' => '');
			add_module($album_permissions);
			$import_images = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname'=> 'ACP_IMPORT_ALBUMS',	'module_mode' => 'import_images',	'module_auth' => '');
			add_module($import_images);
			$cleanup = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_module_id,	'module_class' => 'acp',	'module_langname' => 'ACP_GALLERY_CLEANUP',	'module_mode' => 'cleanup',	'module_auth' => '');
			add_module($cleanup);

			// UCP
			$ucp_gallery_overview = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $choosen_ucp_module,	'module_class' => 'ucp',	'module_langname'=> 'UCP_GALLERY',	'module_mode' => 'overview',	'module_auth' => '');
			add_module($ucp_gallery_overview);
			$ucp_module_id = $db->sql_nextid();
			gallery_config_value('ucp_parent_module', $ucp_module_id);

			$ucp_gallery = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $ucp_module_id,	'module_class' => 'ucp',	'module_langname' => 'UCP_GALLERY_SETTINGS',	'module_mode' => 'manage_settings',	'module_auth' => '');
			add_module($ucp_gallery);
			$ucp_gallery = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $ucp_module_id,	'module_class' => 'ucp',	'module_langname' => 'UCP_GALLERY_PERSONAL_ALBUMS',	'module_mode' => 'manage_albums',	'module_auth' => '');
			add_module($ucp_gallery);
			$ucp_gallery = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $ucp_module_id,	'module_class' => 'ucp',	'module_langname' => 'UCP_GALLERY_WATCH',	'module_mode' => 'manage_subscriptions',	'module_auth' => '');
			add_module($ucp_gallery);
			$ucp_gallery = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $ucp_module_id,	'module_class' => 'ucp',	'module_langname' => 'UCP_GALLERY_FAVORITES',	'module_mode' => 'manage_favorites',	'module_auth' => '');
			add_module($ucp_gallery);

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_install_steps, 3, $user->lang['STEPS_MODULES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 4:
			/*
			* Add BBCode
			*/
			add_bbcode('album');

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_install_steps, 4, $user->lang['STEPS_ADD_BBCODE'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 5:
			/*
			* Insert Examples
			*/
			$moderators_group_id = $registered_group_id = $guests_group_id = 0;
			$sql = 'SELECT *
				FROM ' . GROUPS_TABLE . '
				WHERE group_type = ' . GROUP_SPECIAL;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['group_name'] == 'GLOBAL_MODERATORS')
				{
					$moderators_group_id = $row['group_id'];
				}
				if ($row['group_name'] == 'REGISTERED')
				{
					$registered_group_id = $row['group_id'];
				}
				if ($row['group_name'] == 'GUESTS')
				{
					$guests_group_id = $row['group_id'];
				}
			}
			$db->sql_freeresult($result);
			$album_data = array(
				'album_id'				=> 1,
				'parent_id'				=> 0,
				'album_parents'			=> '',
				'left_id'				=> 1,
				'right_id'				=> 4,
				'album_desc'			=> '',
				'album_type'			=> 0,
				'album_name'			=> $user->lang['EXAMPLE_ALBUM1'],
				'album_desc_options'	=> 7,
			);
			$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
			$album_data = array(
				'album_id'				=> 2,
				'parent_id'				=> 1,
				'album_parents'			=> '',
				'left_id'				=> 2,
				'right_id'				=> 3,
				'album_type'			=> 1,
				'album_name'			=> $user->lang['EXAMPLE_ALBUM2'],
				'album_desc'			=> $user->lang['EXAMPLE_ALBUM2_DESC'],
				'album_desc_options'	=> 7,
				'album_images'			=> 1,
				'album_images_real'		=> 1,
				'album_last_image_id'	=> 1,
				'album_last_image_time'	=> time(),
				'album_last_image_name'	=> 'DB-Bird',
				'album_last_username'	=> 'nickvergessen',
				'album_last_user_id'	=> 1,
			);
			$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
			$image_data = array(
				'image_id'				=> 1,
				'image_filename'		=> 'e9572ef3661a7ae1c35ba09a067e57ae.jpg',
				'image_thumbnail'		=> 'e9572ef3661a7ae1c35ba09a067e57ae.jpg',
				'image_name'			=> 'DB-Bird',
				'image_desc'			=> sprintf($user->lang['EXAMPLE_DESC'], $new_mod_version),
				'image_desc_uid'		=> $user->lang['EXAMPLE_DESC_UID'],
				'image_desc_bitfield'	=> '',
				'image_user_id'			=> 1,
				'image_username'		=> 'nickvergessen',
				'image_user_colour'		=> '',
				'image_user_ip'			=> '127.0.0.1',
				'image_time'			=> time(),
				'image_album_id'		=> 2,
				'image_view_count'		=> 1,
				'image_has_exif'		=> 1,
				'image_status'			=> 1,
				'image_reported'		=> 0,
			);
			$db->sql_query('INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $image_data));
			$modscache_data = array(
				'album_id'				=> 1,
				'group_id'				=> $moderators_group_id,
				'group_name'			=> 'GLOBAL_MODERATORS',
				'display_on_index'		=> 1,
			);
			$db->sql_query('INSERT INTO ' . GALLERY_MODSCACHE_TABLE . ' ' . $db->sql_build_array('INSERT', $modscache_data));
			$modscache_data = array(
				'album_id'				=> 2,
				'group_id'				=> $moderators_group_id,
				'group_name'			=> 'GLOBAL_MODERATORS',
				'display_on_index'		=> 1,
			);
			$db->sql_query('INSERT INTO ' . GALLERY_MODSCACHE_TABLE . ' ' . $db->sql_build_array('INSERT', $modscache_data));
			$permissions_data = array(
				'perm_id'				=> 1,
				'perm_role_id'			=> 1,
				'perm_album_id'			=> 1,
				'perm_group_id'			=> $moderators_group_id,
			);
			$db->sql_query('INSERT INTO ' . GALLERY_PERMISSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $permissions_data));
			$permissions_data = array(
				'perm_id'				=> 2,
				'perm_role_id'			=> 1,
				'perm_album_id'			=> 2,
				'perm_group_id'			=> $moderators_group_id,
			);
			$db->sql_query('INSERT INTO ' . GALLERY_PERMISSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $permissions_data));
			$permissions_data = array(
				'perm_id'				=> 3,
				'perm_role_id'			=> 2,
				'perm_album_id'			=> 1,
				'perm_group_id'			=> $registered_group_id,
			);
			$db->sql_query('INSERT INTO ' . GALLERY_PERMISSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $permissions_data));
			$permissions_data = array(
				'perm_id'				=> 4,
				'perm_role_id'			=> 2,
				'perm_album_id'			=> 2,
				'perm_group_id'			=> $registered_group_id,
			);
			$db->sql_query('INSERT INTO ' . GALLERY_PERMISSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $permissions_data));
			$permissions_data = array(
				'perm_id'				=> 5,
				'perm_role_id'			=> 3,
				'perm_album_id'			=> 1,
				'perm_group_id'			=> $guests_group_id,
			);
			$db->sql_query('INSERT INTO ' . GALLERY_PERMISSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $permissions_data));
			$permissions_data = array(
				'perm_id'				=> 6,
				'perm_role_id'			=> 3,
				'perm_album_id'			=> 2,
				'perm_group_id'			=> $guests_group_id,
			);
			$db->sql_query('INSERT INTO ' . GALLERY_PERMISSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $permissions_data));
			$roles_data = array(
				'role_id'				=> 1,
				'i_view'				=> 1,
				'i_upload'				=> 1,
				'i_edit'				=> 1,
				'i_delete'				=> 1,
				'i_rate'				=> 1,
				'i_approve'				=> 1,
				'i_lock'				=> 1,
				'i_report'				=> 1,
				'i_count'				=> 500,
				'c_post'				=> 1,
				'c_edit'				=> 1,
				'c_delete'				=> 1,
				'a_moderate'			=> 1,
				'album_count'			=> 0,
			);
			$db->sql_query('INSERT INTO ' . GALLERY_ROLES_TABLE . ' ' . $db->sql_build_array('INSERT', $roles_data));
			$roles_data = array(
				'role_id'				=> 2,
				'i_view'				=> 1,
				'i_upload'				=> 1,
				'i_edit'				=> 0,
				'i_delete'				=> 0,
				'i_rate'				=> 1,
				'i_approve'				=> 1,
				'i_lock'				=> 0,
				'i_report'				=> 1,
				'i_count'				=> 250,
				'c_post'				=> 1,
				'c_edit'				=> 0,
				'c_delete'				=> 0,
				'a_moderate'			=> 0,
				'album_count'			=> 0,
			);
			$db->sql_query('INSERT INTO ' . GALLERY_ROLES_TABLE . ' ' . $db->sql_build_array('INSERT', $roles_data));
			$roles_data = array(
				'role_id'				=> 3,
				'i_view'				=> 1,
				'i_upload'				=> 0,
				'i_edit'				=> 0,
				'i_delete'				=> 0,
				'i_rate'				=> 0,
				'i_approve'				=> 0,
				'i_lock'				=> 0,
				'i_report'				=> 0,
				'i_count'				=> 0,
				'c_post'				=> 0,
				'c_edit'				=> 0,
				'c_delete'				=> 0,
				'a_moderate'			=> 0,
				'album_count'			=> 0,
			);
			$db->sql_query('INSERT INTO ' . GALLERY_ROLES_TABLE . ' ' . $db->sql_build_array('INSERT', $roles_data));

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_install_steps, 5, $user->lang['STEPS_CREATE_EXAMPLES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 6:
			/*
			* Final Step
			*/
			gallery_config_value('album_version', $new_mod_version, true);
			$cache->purge();
			add_log('admin', 'LOG_INSTALL_INSTALLED', $log_name);
			add_log('admin', 'LOG_PURGE_CACHE');
			$message = sprintf($user->lang['INSTALLER_INSTALL_SUCCESSFUL'], $new_mod_version);
			trigger_error($message);

		break;
	}
	$refresh_ary = array(
		'step'	=> $load_new_step,
	);
	if ($choosen_ucp_module)
	{
		$refresh_ary['select_ucp_module'] = $choosen_ucp_module;
	}
	if ($choosen_acp_module)
	{
		$refresh_ary['select_acp_module'] = $choosen_acp_module;
	}
	meta_refresh(3, append_sid("{$phpbb_root_path}install/install.php", $refresh_ary));
	trigger_error($message);

}
else
{
	check_chmods();
	$get_acp_module = select_parent_module('acp', 31, 'ACP_CAT_DOT_MODS');
	$select_acp_module = $get_acp_module['list'];
	$default_acp_module = $get_acp_module['default'];
	$get_ucp_module = select_parent_module('ucp', 0, '');
	$select_ucp_module = $get_ucp_module['list'];
	$default_ucp_module = $get_ucp_module['default'];

	$template->assign_vars(array(
		'CREATE_MODULES'		=> true,
		'DEFAULT_UCP_MODULE'	=> $default_ucp_module,
		'SELECT_UCP_MODULE'		=> $select_ucp_module,
		'DEFAULT_ACP_MODULE'	=> $default_acp_module,
		'SELECT_ACP_MODULE'		=> $select_acp_module,
	));
}


page_header($page_title);

$template->set_filenames(array(
	'body' => 'install_body.html')
);

page_footer();

?>