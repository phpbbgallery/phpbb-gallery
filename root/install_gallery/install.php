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
include($phpbb_root_path . 'install_gallery/install_functions.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();
$user->add_lang('mods/gallery_install');
$user->add_lang('mods/info_acp_gallery');

$new_mod_version = '0.4.0-RC2';
$page_title = 'phpBB Gallery v' . $new_mod_version;
$log_name = 'Modification "phpBB Gallery"' . ((request_var('update', 0) > 0) ? '-Update' : '') . ' v' . $new_mod_version;

$mode = request_var('mode', '', true);

$delete = request_var('delete', 0);
$install = request_var('install', 0);
$update = request_var('update', 0);
$version = request_var('v', '0.0.0', true);
$convert = request_var('convert', 0);
$convert_prefix = request_var('convert_prefix', '', true);
$choosen_acp_module = request_var('select_acp_module', 0);
$choosen_ucp_module = request_var('select_ucp_module', 0);
// if $choosen_acp_module is '-1' user wants to rebuild the ".MODs"-tab
if ($choosen_acp_module < 0)
{
	$acp_mods_tab = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => 0,	'module_class' => 'acp',	'module_langname'=> 'ACP_CAT_DOT_MODS',	'module_mode' => '',	'module_auth' => '');
	add_module($acp_mods_tab);
	$choosen_acp_module = $db->sql_nextid();
}

//Check some Dirs for the right CHMODs
$chmod_dirs = array(
	array('name' => GALLERY_ROOT_PATH . 'import/', 'chmod' => is_writable($phpbb_root_path . GALLERY_ROOT_PATH . 'import/')),
	array('name' => GALLERY_ROOT_PATH . 'upload/', 'chmod' => is_writable($phpbb_root_path . GALLERY_ROOT_PATH . 'upload/')),
	array('name' => GALLERY_ROOT_PATH . 'upload/cache/', 'chmod' => is_writable($phpbb_root_path . GALLERY_ROOT_PATH . 'upload/cache/')),
);
$module_names = array('ACP_GALLERY_MANAGE_USER', 'ACP_GALLERY_MANAGE_RESTS', 'ACP_GALLERY_CLEANUP', 'ACP_IMPORT_ALBUMS', 'ACP_GALLERY_ALBUM_PERSONAL_PERMISSIONS', 'ACP_GALLERY_ALBUM_PERMISSIONS', 'ACP_GALLERY_CONFIGURE_GALLERY', 'ACP_GALLERY_MANAGE_CACHE', 'ACP_GALLERY_MANAGE_ALBUMS', 'ACP_GALLERY_OVERVIEW', 'PHPBB_GALLERY');
$module_names = array_merge($module_names, array('UCP_GALLERY_PERSONAL_ALBUMS', 'UCP_GALLERY_FAVORITES', 'UCP_GALLERY_WATCH', 'UCP_GALLERY_SETTINGS', 'UCP_GALLERY'));
$old_configs = array('user_pics_limit', 'mod_pics_limit', 'fullpic_popup', 'personal_gallery', 'personal_gallery_private', 'personal_gallery_limit', 'personal_gallery_view');
$create_new_modules = false;

switch ($mode)
{
	case 'install':
		$installed = false;
		if ($install == 1)
		{
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

			//fill the GALLERY_CONFIG_TABLE with some values
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

			//creating an example album and image
			//get some group_ids
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

			//remove the old modules:
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

			//add the modules new:
			// ->ACP
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

			// -> UCP
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

			$gd_check = function_exists('gd_info') ? gd_info() : array();
			$gd_success = isset($gd_check['GD Version']);
			if (!$gd_success && ($album_config['gd_version'] > 0))
			{
				$sql = 'UPDATE ' . GALLERY_CONFIG_TABLE . "SET config_value = 0 WHERE config_name = 'gd_version'";
				$result = $db->sql_query($sql);
				$album_config['gd_version'] = 0;
			}

			add_bbcode('album');
			nv_add_column(SESSIONS_TABLE, 'session_album_id', array('UINT', 0));
			gallery_config_value('album_version', $new_mod_version, true);

			// clear cache and log what we did
			$cache->purge();
			add_log('admin', 'LOG_INSTALL_INSTALLED', $log_name);
			add_log('admin', 'LOG_PURGE_CACHE');
			$installed = true;
		}
		else
		{
			$get_acp_module = select_parent_module('acp', 31, 'ACP_CAT_DOT_MODS');
			$select_acp_module = $get_acp_module['list'];
			$default_acp_module = $get_acp_module['default'];
			$get_ucp_module = select_parent_module('ucp', 0, '');
			$select_ucp_module = $get_ucp_module['list'];
			$default_ucp_module = $get_ucp_module['default'];
		}
	break;
	case 'update':
		$updated = false;
		if ($update == 1)
		{
			switch ($version)
			{
				case '0.1.2':
					nv_add_column(GALLERY_IMAGES_TABLE, 'pic_desc_bbcode_bitfield', array('VCHAR:255', ''));
					nv_add_column(GALLERY_IMAGES_TABLE, 'pic_desc_bbcode_uid', array('VCHAR:8', ''));
					nv_add_column(GALLERY_ALBUMS_TABLE, 'cat_desc_bbcode_bitfield', array('VCHAR:255', ''));
					nv_add_column(GALLERY_ALBUMS_TABLE, 'cat_desc_bbcode_uid', array('VCHAR:8', ''));
					nv_add_column(GALLERY_COMMENTS_TABLE, 'comment_text_bbcode_bitfield', array('VCHAR:255', ''));
					nv_add_column(GALLERY_COMMENTS_TABLE, 'comment_text_bbcode_uid', array('VCHAR:8', ''));
				//no break;

				case '0.1.3':
					nv_create_table('phpbb_gallery_albums', true);
					nv_create_table('phpbb_gallery_comments', true);
					nv_create_table('phpbb_gallery_config', true);
					nv_create_table('phpbb_gallery_images', true);
					nv_create_table('phpbb_gallery_rates', true);

					// first lets make the albums...
					$left_id = 1;
					$sql = 'SELECT *
						FROM ' . ALBUM_CAT_TABLE . '
						ORDER BY cat_order';
					$result = $db->sql_query($sql);
					while( $row = $db->sql_fetchrow($result) )
					{
						$album_data = array(
							'album_id'						=> $row['cat_id'],
							'album_name'					=> $row['cat_title'],
							'parent_id'						=> 0,
							'left_id'						=> $left_id,
							'right_id'						=> $left_id + 1,
							'album_parents'					=> '',
							'album_type'					=> 2,
							'album_desc'					=> $row['cat_desc'],
							'album_desc_uid'				=> $row['cat_desc_bbcode_uid'],
							'album_desc_bitfield'			=> $row['cat_desc_bbcode_bitfield'],
							'album_desc_options'			=> 7,
							'album_view_level'				=> $row['cat_view_level'],
							'album_upload_level'			=> $row['cat_upload_level'],
							'album_rate_level'				=> $row['cat_rate_level'],
							'album_comment_level'			=> $row['cat_comment_level'],
							'album_edit_level'				=> $row['cat_edit_level'],
							'album_delete_level'			=> $row['cat_delete_level'],
							'album_view_groups'				=> $row['cat_view_groups'],
							'album_upload_groups'			=> $row['cat_upload_groups'],
							'album_rate_groups'				=> $row['cat_rate_groups'],
							'album_comment_groups'			=> $row['cat_comment_groups'],
							'album_edit_groups'				=> $row['cat_edit_groups'],
							'album_delete_groups'			=> $row['cat_delete_groups'],
							'album_moderator_groups'		=> $row['cat_moderator_groups'],
							'album_approval'				=> $row['cat_approval'],
						);
						generate_text_for_storage($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options'], true, true, true);
						$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
						$left_id = $left_id + 2;
					}
					$db->sql_freeresult($result);

					// second the rates...
					$sql = 'SELECT *
						FROM ' . ALBUM_RATE_TABLE . '
						ORDER BY rate_pic_id';
					$result = $db->sql_query($sql);
					while( $row = $db->sql_fetchrow($result) )
					{
						$rate_data = array(
							'rate_image_id'					=> $row['rate_pic_id'],
							'rate_user_id'					=> $row['rate_user_id'],
							'rate_user_ip'					=> $row['rate_user_ip'],
							'rate_point'					=> $row['rate_point'],
						);
						$db->sql_query('INSERT INTO ' . GALLERY_RATES_TABLE . ' ' . $db->sql_build_array('INSERT', $rate_data));
					}
					$db->sql_freeresult($result);

					// third the comments...
					$sql = 'SELECT *
						FROM ' . ALBUM_COMMENT_TABLE . '
						ORDER BY comment_id';
					$result = $db->sql_query($sql);
					while( $row = $db->sql_fetchrow($result) )
					{
						$comment_data = array(
							'comment_id'			=> $row['comment_id'],
							'comment_image_id'		=> $row['comment_pic_id'],
							'comment_user_id'		=> $row['comment_user_id'],
							'comment_username'		=> $row['comment_username'],
							'comment_user_ip'		=> $row['comment_user_ip'],
							'comment_time'			=> $row['comment_time'],
							'comment'				=> $row['comment_text'],
							'comment_uid'			=> $row['comment_text_bbcode_uid'],
							'comment_bitfield'		=> $row['comment_text_bbcode_bitfield'],
							'comment_options'		=> 7,
							'comment_edit_time'		=> (isset($row['comment_edit_time']) ? $row['comment_edit_time'] : 0),
							'comment_edit_count'	=> (isset($row['comment_edit_count']) ? $row['comment_edit_count'] : 0),
							'comment_edit_user_id'	=> (isset($row['comment_edit_user_id']) ? $row['comment_edit_user_id'] : 0),
						);
						generate_text_for_storage($comment_data['comment'], $comment_data['comment_uid'], $comment_data['comment_bitfield'], $comment_data['comment_options'], true, true, true);
						unset($comment_data['comment_options']);
						$db->sql_query('INSERT INTO ' . GALLERY_COMMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $comment_data));
					}
					$db->sql_freeresult($result);

					// fourth the configs...
					// because there might be some problems, we create them new...
					$sql_query = file_get_contents('schemas/_schema_data.sql');
					switch ($db->sql_layer)
					{
						case 'mssql':
						case 'mssql_odbc':
							$sql_query = preg_replace('#\# MSSQL IDENTITY (phpbb_[a-z_]+) (ON|OFF) \##s', 'SET IDENTITY_INSERT \1 \2;', $sql_query);
						break;
						case 'postgres':
							$sql_query = preg_replace('#\# POSTGRES (BEGIN|COMMIT) \##s', '\1; ', $sql_query);
						break;
					}
					$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);
					$sql_query = preg_replace('/\n{2,}/', "\n", preg_replace('/^#.*$/m', "\n", $sql_query));
					$sql_query = split_sql_file($sql_query, ';');
					foreach ($sql_query as $sql)
					{
						if (!$db->sql_query($sql))
						{
							$error = $db->sql_error();
							$this->p_master->db_error($error['message'], $sql, __LINE__, __FILE__);
						}
					}
					unset($sql_query);

					// last and least the images...
					$sql = 'SELECT *
						FROM ' . ALBUM_TABLE . '
						ORDER BY pic_id';
					$result = $db->sql_query($sql);
					while( $row = $db->sql_fetchrow($result) )
					{
						$image_data = array(
							'image_id'				=> $row['pic_id'],
							'image_filename'		=> $row['pic_filename'],
							'image_thumbnail'		=> $row['pic_thumbnail'],
							'image_name'			=> $row['pic_title'],
							'image_desc'			=> $row['pic_desc'],
							'image_desc_uid'		=> $row['pic_desc_bbcode_uid'],
							'image_desc_bitfield'	=> $row['pic_desc_bbcode_bitfield'],
							'image_desc_options'	=> 7,
							'image_user_id'			=> $row['pic_user_id'],
							'image_username'		=> $row['pic_username'],
							'image_user_ip'			=> $row['pic_user_ip'],
							'image_time'			=> $row['pic_time'],
							'image_album_id'		=> $row['pic_cat_id'],
							'image_view_count'		=> $row['pic_view_count'],
							'image_lock'			=> $row['pic_lock'],
							'image_approval'		=> $row['pic_approval'],
						);
						generate_text_for_storage($image_data['image_desc'], $image_data['image_desc_uid'], $image_data['image_desc_bitfield'], $image_data['image_desc_options'], true, true, true);
						unset($image_data['image_desc_options']);
						$db->sql_query('INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $image_data));
					}
					$db->sql_freeresult($result);

				//no break;

				case '0.2.0':
				//no break;

				case '0.2.1':
				//no break;

				case '0.2.2':
					add_bbcode('album');
				//no break;

				case '0.2.3':
					//add new config-values
					add_bbcode('album');
					gallery_config_value('preview_rsz_height', 600);
					gallery_config_value('preview_rsz_width', 800);
					gallery_config_value('upload_images', 10);
					gallery_config_value('thumbnail_info_line', 1);

					//create some new columns
					$phpbb_db_tools = new phpbb_db_tools($db);
					$phpbb_db_tools->sql_column_change(GALLERY_IMAGES_TABLE, 'image_username', array('VCHAR:255', ''));

					nv_add_column(GALLERY_IMAGES_TABLE, 'image_user_colour', array('VCHAR:6', ''));
					nv_add_column(GALLERY_ALBUMS_TABLE, 'album_user_id', array('UINT', 0));
					nv_add_column(USERS_TABLE, 'album_id', array('UINT', 0));
					//update the new columns image_username and image_user_colour
					$sql = 'SELECT i.image_user_id, i.image_id, u.username, u.user_colour
						FROM ' . GALLERY_IMAGES_TABLE . ' AS i
						LEFT JOIN ' . USERS_TABLE . " AS u
							ON i.image_user_id = u.user_id
						ORDER BY i.image_id DESC";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$image_id = $row['image_id'];

						if ($row['image_user_id'] == 1 || empty($row['username']))
						{
							continue;
						}

						$sql_ary = array(
							'image_username'		=> $row['username'],
							'image_user_colour'		=> $row['user_colour'],
						);

						$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE ' . $db->sql_in_set('image_id', $image_id);
						$db->sql_query($sql);
					}
					$db->sql_freeresult($result);

					//now create the new personal albums
					$sql = 'SELECT i.image_id, i.image_username, image_user_id
						FROM ' . GALLERY_IMAGES_TABLE . " AS i
						WHERE image_album_id = 0
						GROUP BY i.image_user_id DESC";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$album_data = array(
							'album_name'					=> $row['image_username'],
							'parent_id'						=> 0,
							//left_id and right_id are created some lines later
							'album_desc_options'			=> 7,
							'album_desc'					=> '',
							'album_parents'					=> '',
							'album_type'					=> 2,
							'album_user_id'					=> $row['image_user_id'],
						);
						$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));

						$sql2 = 'SELECT album_id FROM ' . GALLERY_ALBUMS_TABLE . ' WHERE parent_id = 0 AND album_user_id = ' . $row['image_user_id'] . ' LIMIT 1';
						$result2 = $db->sql_query($sql2);
						$row2 = $db->sql_fetchrow($result2);

						$sql3 = 'UPDATE ' . USERS_TABLE . ' 
								SET album_id = ' . (int) $row2['album_id'] . '
								WHERE user_id  = ' . (int) $row['image_user_id'];
						$db->sql_query($sql3);

						$sql3 = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
								SET image_album_id = ' . (int) $row2['album_id'] . '
								WHERE image_album_id = 0
									AND image_user_id  = ' . (int) $row['image_user_id'];
						$db->sql_query($sql3);
					}
					$db->sql_freeresult($result);
				//no break;

				case '0.3.0':
				//no break;

				case '0.3.1':
					$album_config = load_album_config();

					//add new tables:
					nv_create_table('phpbb_gallery_favorites', true);
					nv_create_table('phpbb_gallery_modscache', true);
					nv_create_table('phpbb_gallery_permissions', true);
					nv_create_table('phpbb_gallery_reports', true);
					nv_create_table('phpbb_gallery_roles', true);
					nv_create_table('phpbb_gallery_users', true);
					nv_create_table('phpbb_gallery_watch', true);

					//add new columns:
					nv_add_column(GALLERY_ALBUMS_TABLE, 'album_images', array('UINT', 0));
					nv_add_column(GALLERY_ALBUMS_TABLE, 'album_images_real', array('UINT', 0));
					nv_add_column(GALLERY_ALBUMS_TABLE, 'album_last_image_id', array('UINT', 0));
					nv_add_column(GALLERY_ALBUMS_TABLE, 'album_image', array('VCHAR', ''));
					nv_add_column(GALLERY_ALBUMS_TABLE, 'album_last_image_time', array('INT:11', 0));
					nv_add_column(GALLERY_ALBUMS_TABLE, 'album_last_image_name', array('VCHAR', ''));
					nv_add_column(GALLERY_ALBUMS_TABLE, 'album_last_username', array('VCHAR', ''));
					nv_add_column(GALLERY_ALBUMS_TABLE, 'album_last_user_colour', array('VCHAR:6', ''));
					nv_add_column(GALLERY_ALBUMS_TABLE, 'album_last_user_id', array('UINT', 0));
					nv_add_column(GALLERY_ALBUMS_TABLE, 'display_on_index', array('UINT:1', 1));
					nv_add_column(GALLERY_ALBUMS_TABLE, 'display_subalbum_list', array('UINT:1', 1));
					nv_add_column(GALLERY_COMMENTS_TABLE, 'comment_user_colour', array('VCHAR:6', ''));
					nv_add_column(GALLERY_IMAGES_TABLE, 'image_comments', array('UINT', 0));
					nv_add_column(GALLERY_IMAGES_TABLE, 'image_last_comment', array('UINT', 0));
					nv_add_column(GALLERY_IMAGES_TABLE, 'image_filemissing', array('UINT:3', 0));
					nv_add_column(GALLERY_IMAGES_TABLE, 'image_rates', array('UINT', 0));
					nv_add_column(GALLERY_IMAGES_TABLE, 'image_rate_points', array('UINT', 0));
					nv_add_column(GALLERY_IMAGES_TABLE, 'image_rate_avg', array('UINT', 0));
					nv_add_column(GALLERY_IMAGES_TABLE, 'image_status', array('UINT:3', 1));
					nv_add_column(GALLERY_IMAGES_TABLE, 'image_has_exif', array('UINT:3', 2));
					nv_add_column(GALLERY_IMAGES_TABLE, 'image_favorited', array('UINT', 0));
					//we didn't add this to all updates, so we just do it again. the function saves to be double
					nv_add_column(SESSIONS_TABLE, 'session_album_id', array('UINT', 0));

					//change some current columns:
					nv_change_column(GALLERY_COMMENTS_TABLE, 'comment_username', array('VCHAR', ''));

					//add some new config's:
					// -> general phpbb_config
						$num_images = 0;
						$sql = 'SELECT u.album_id, u.user_id, count(i.image_id) as images
							FROM ' . USERS_TABLE . ' u
							LEFT JOIN ' . GALLERY_IMAGES_TABLE . ' i
								ON i.image_user_id = u.user_id
								AND i.image_status = 1
							GROUP BY i.image_user_id';
						$result = $db->sql_query($sql);
						while ($row = $db->sql_fetchrow($result))
						{
							$sql_ary = array(
								'user_id'				=> $row['user_id'],
								'personal_album_id'		=> $row['album_id'],
								'user_images'			=> $row['images'],
							);
							$num_images = $num_images + $row['images'];
							$db->sql_query('INSERT INTO ' . GALLERY_USERS_TABLE . $db->sql_build_array('INSERT', $sql_ary));
						}
						$db->sql_freeresult($result);
					set_config('num_images', $num_images, true);
					set_config('gallery_total_images', 1);
					set_config('gallery_user_images_profil', 1);
					set_config('gallery_personal_album_profil', 1);
					// -> gallery_config
					gallery_config_value('fake_thumb_size', 70);
					gallery_config_value('disp_fake_thumb', 1);
					gallery_config_value('exif_data', 1);
					gallery_config_value('watermark_height', 50);
					gallery_config_value('watermark_width', 200);
					//count the number of personal_gallerys in the config to reduce sqls
						$sql = 'SELECT COUNT(album_id) AS albums
							FROM ' . GALLERY_ALBUMS_TABLE . "
							WHERE parent_id = 0
								AND album_user_id <> 0";
						$result = $db->sql_query($sql);
						$total_galleries = 0;
						if ($row = $db->sql_fetchrow($result))
						{
							$total_galleries = $row['albums'];
						}
						$db->sql_freeresult($result);
					gallery_config_value('personal_counter', $total_galleries);
					//change the sort_method if it is sepcial
					if ($album_config['sort_method'] == 'rating')
					{
						gallery_config_value('sort_method', 'image_rate_avg', true);
					}
					else if ($album_config['sort_method'] == 'comments')
					{
						gallery_config_value('sort_method', 'image_comments', true);
					}
					else if ($album_config['sort_method'] == 'new_comment')
					{
						gallery_config_value('sort_method', 'image_last_comment', true);
					}
					//regenerate BB-Code
					add_bbcode('album');

					// Update the data
					// -> Albums
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET album_type = 1';
					$db->sql_query($sql);
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET album_type = 0 WHERE album_user_id = 0';
					$db->sql_query($sql);
					//add the information for the last_image to the albums part 1: last_image_id, image_count
					$sql = 'SELECT COUNT(i.image_id) images, MAX(i.image_id) last_image_id, i.image_album_id
						FROM ' . GALLERY_IMAGES_TABLE . " i
						WHERE i.image_approval = 1
						GROUP BY i.image_album_id";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$sql_ary = array(
							'album_images'			=> $row['images'],
							'album_last_image_id'	=> $row['last_image_id'],
						);
						$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE ' . $db->sql_in_set('album_id', $row['image_album_id']);
						$db->sql_query($sql);
					}
					$db->sql_freeresult($result);
					//add the information for the last_image to the albums part 2: correct album_type, images_real are all images, even unapproved
					$sql = 'SELECT COUNT(i.image_id) images, i.image_album_id
						FROM ' . GALLERY_IMAGES_TABLE . " i
						GROUP BY i.image_album_id";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$sql_ary = array(
							'album_images_real'	=> $row['images'],
							'album_type'		=> 1,
						);
						$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE ' . $db->sql_in_set('album_id', $row['image_album_id']);
						$db->sql_query($sql);
					}
					$db->sql_freeresult($result);
					//add the information for the last_image to the albums part 3: user_id, username, user_colour, time, image_name
					$sql = 'SELECT a.album_id, a.album_last_image_id, i.image_time, i.image_name, i.image_user_id, i.image_username, i.image_user_colour, u.user_colour
						FROM ' . GALLERY_ALBUMS_TABLE . " a
						LEFT JOIN " . GALLERY_IMAGES_TABLE . " i
							ON a.album_last_image_id = i.image_id
						LEFT JOIN " . USERS_TABLE . " u
							ON a.album_user_id = u.user_colour
						WHERE a.album_last_image_id > 0";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$sql_ary = array(
							'album_last_image_time'		=> $row['image_time'],
							'album_last_image_name'		=> $row['image_name'],
							'album_last_username'		=> $row['image_username'],
							'album_last_user_colour'	=> isset($row['user_colour']) ? $row['user_colour'] : '',
							'album_last_user_id'		=> $row['image_user_id'],
						);
						$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE ' . $db->sql_in_set('album_id', $row['album_id']);
						$db->sql_query($sql);
					}
					$db->sql_freeresult($result);
					// -> Images
					$sql = 'SELECT rate_image_id, COUNT(rate_user_ip) image_rates, AVG(rate_point) image_rate_avg, SUM(rate_point) image_rate_points
						FROM ' . GALLERY_RATES_TABLE . '
						GROUP BY rate_image_id';
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
							SET image_rates = ' . $row['image_rates'] . ',
								image_rate_points = ' . $row['image_rate_points'] . ',
								image_rate_avg = ' . round($row['image_rate_avg'], 2) * 100 . '
							WHERE image_id = ' . $row['rate_image_id'];
						$db->sql_query($sql);
					}
					$db->sql_freeresult($result);
					$sql = 'SELECT COUNT(comment_id) comments, MAX(comment_id) image_last_comment, comment_image_id
						FROM ' . GALLERY_COMMENTS_TABLE . "
						GROUP BY comment_image_id";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET image_comments = ' . $row['comments'] . ',
							image_last_comment = ' . $row['image_last_comment'] . '
							WHERE ' . $db->sql_in_set('image_id', $row['comment_image_id']);
						$db->sql_query($sql);
					}
					$db->sql_freeresult($result);
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET image_status = 2
						WHERE image_lock = 1';
					$db->sql_query($sql);
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET image_status = 0
						WHERE image_lock = 0
							AND image_approval = 0';
					$db->sql_query($sql);
					// -> Comments
					$sql = 'SELECT u.user_colour, c.comment_id
						FROM ' . GALLERY_COMMENTS_TABLE . ' c
						LEFT JOIN ' . USERS_TABLE . ' u
							ON c.comment_user_id = u.user_id';
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						if (isset($row['user_colour']))
						{
							$sql = 'UPDATE ' . GALLERY_COMMENTS_TABLE . "
								SET comment_user_colour = '" . $row['user_colour'] . "'
								WHERE comment_id = " . $row['comment_id'];
							$db->sql_query($sql);
						}
					}
					$db->sql_freeresult($result);

					//remove some old columns
					nv_remove_column(GROUPS_TABLE, 'personal_subalbums');
					nv_remove_column(GROUPS_TABLE, 'allow_personal_albums');
					nv_remove_column(GROUPS_TABLE, 'view_personal_albums');
					nv_remove_column(USERS_TABLE, 'album_id');
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_approval');
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_order');

					//remove columns of the old permission-system
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_view_level');
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_upload_level');
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_rate_level');
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_comment_level');
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_edit_level');
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_delete_level');
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_view_groups');
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_upload_groups');
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_rate_groups');
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_comment_groups');
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_edit_groups');
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_delete_groups');
					nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_moderator_groups');

					//remove the old configs:
					$sql = 'DELETE FROM ' .GALLERY_CONFIG_TABLE . '
						WHERE ' . $db->sql_in_set('config_name', $old_configs);
					$db->sql_query($sql);

				case '0.4.0-RC1':
					$album_config = load_album_config();

					//remove the old modules:
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

					if ($version == '0.4.0-RC1')
					{
						//sorry, i crashed your modules
						//but u found a way to fix it again
						//we'll find every empty spaces in the left_id and right_id-row
						//and than we'll close them.
						$sql = 'SELECT *
							FROM ' . MODULES_TABLE . "
							WHERE module_class IN ('acp', 'ucp')
							ORDER BY module_class, left_id";
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							$id_row[$row['module_class']][] = $row['left_id'];
							$id_row[$row['module_class']][] = $row['right_id'];
						}
						$db->sql_freeresult($result);

						//first we reorder the ACP:
						//you'll have troubles here, if you installed the MOD twice
						//or updated from any previous version
						$test_id = $last_id = $id = 0;
						sort($id_row['acp']);
						foreach ($id_row['acp'] AS $id)
						{
							$test_id = $id - 1;
							if ($test_id && !in_array($test_id, $id_row['acp']))
							{
								$diff = ($id - $last_id - 1);
								$sql = 'UPDATE ' . MODULES_TABLE . "
									SET left_id = left_id - $diff
									WHERE left_id >= $id
										AND module_class = 'acp'";
								$db->sql_query($sql);
								$sql = 'UPDATE ' . MODULES_TABLE . "
									SET right_id = right_id - $diff
									WHERE right_id >= $id
										AND module_class = 'acp'";
								$db->sql_query($sql);
								//echo 'last_id: ' . $last_id . ' id: ' . $id . ' diff: ' . ($id - $last_id - 1);
							}
							$last_id = $id;
						}

						//now the UCP:
						//troubles only occure on double install
						$test_id = $last_id = $id = 0;
						sort($id_row['ucp']);
						foreach ($id_row['ucp'] AS $id)
						{
							$test_id = $id - 1;
							if ($test_id && !in_array($test_id, $id_row['ucp']))
							{
								$diff = ($id - $last_id - 1);
								$sql = 'UPDATE ' . MODULES_TABLE . "
									SET left_id = left_id - $diff
									WHERE left_id >= $id
										AND module_class = 'ucp'";
								$db->sql_query($sql);
								$sql = 'UPDATE ' . MODULES_TABLE . "
									SET right_id = right_id - $diff
									WHERE right_id >= $id
										AND module_class = 'ucp'";
								$db->sql_query($sql);
								//echo 'last_id: ' . $last_id . ' id: ' . $id . ' diff: ' . ($id - $last_id - 1);
							}
							$last_id = $id;
						}
					}

					//add the modules new:
					// ->ACP
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

					// -> UCP
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

					$total_images = 0;
					$sql = 'SELECT COUNT(gi.image_id) AS num_images, u.user_id
						FROM ' . USERS_TABLE . ' u
						LEFT JOIN  ' . GALLERY_IMAGES_TABLE . ' gi ON (u.user_id = gi.image_user_id AND gi.image_status = 1)
						GROUP BY u.user_id';
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$total_images += $row['num_images'];
						$db->sql_query('UPDATE ' . GALLERY_USERS_TABLE . " SET user_images = {$row['num_images']} WHERE user_id = {$row['user_id']}");
					}
					$db->sql_freeresult($result);
					set_config('num_images', $total_images, true);
				case 'svn':
					$album_config = load_album_config();

				break;
			}


			// clear cache and log what we did
			$gd_check = function_exists('gd_info') ? gd_info() : array();;
			$gd_success = isset($gd_check['GD Version']);
			if (!$gd_success && ($album_config['gd_version'] > 0))
			{
				gallery_config_value('gd_version', 0, true);
				$album_config['gd_version'] = 0;
			}
			gallery_config_value('album_version', $new_mod_version, true);
			$cache->purge();
			add_log('admin', 'LOG_INSTALL_INSTALLED', $log_name);
			add_log('admin', 'LOG_PURGE_CACHE');
			$updated = true;
		}
		else
		{
			switch ($version)
			{
				case '0.1.2':
				case '0.1.3':
				case '0.2.0':
				case '0.2.1':
				case '0.2.2':
				case '0.2.3':
				case '0.3.0':
				case '0.3.1':
				case '0.4.0-RC1':
				case 'svn':
					$create_new_modules = true;
				break;
			}
			if ($create_new_modules)
			{
				$get_acp_module = select_parent_module('acp', 31, 'ACP_CAT_DOT_MODS');
				$select_acp_module = $get_acp_module['list'];
				$default_acp_module = $get_acp_module['default'];
				$get_ucp_module = select_parent_module('ucp', 0, '');
				$select_ucp_module = $get_ucp_module['list'];
				$default_ucp_module = $get_ucp_module['default'];
			}
		}
	break;
	case 'convert':
		$converted = false;
		if ($convert == 1)
		{
			function decode_ip($int_ip)
			{
				$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
				return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
			}
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

			// first lets make the albums...
			$personal_album = array();
			$left_id = 1;
			$sql = 'SELECT *
				FROM ' . $convert_prefix . 'album_cat
				ORDER BY cat_order';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				//we have to do this for the add on with the categories in the personal albums
				$row['cat_user_id'] = (isset($row['cat_user_id']) ? $row['cat_user_id'] : 0);
				if ($row['cat_user_id'] != 0)
				{
					$personal_album[] = $row['cat_id'];
				}
				else
				{
					$row['album_desc_uid'] = $row['album_desc_options'] = $row['album_desc_bitfield'] = '';
					$row['album_desc'] = $row['cat_desc'];
					$album_desc_data = generate_text_for_edit($row['album_desc'], $row['album_desc_uid'], $row['album_desc_options']);
					$album_data = array(
						'album_id'						=> $row['cat_id'],
						'album_name'					=> $row['cat_title'],
						'parent_id'						=> 0,
						'left_id'						=> $left_id,
						'right_id'						=> $left_id + 1,
						'album_parents'					=> '',
						'album_type'					=> 1,
						'album_desc'					=> $album_desc_data['text'],
						'album_desc_uid'				=> '',
						'album_desc_bitfield'			=> '',
						'album_desc_options'			=> 7,
					);
					generate_text_for_storage($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options'], true, true, true);
					$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
					$left_id = $left_id + 2;
				}
			}
			$db->sql_freeresult($result);

			// second the rates...
			$sql = 'SELECT *
				FROM ' . $convert_prefix . 'album_rate
				ORDER BY rate_pic_id';
			$result = $db->sql_query($sql);
			while( $row = $db->sql_fetchrow($result) )
			{
				$rate_data = array(
					'rate_image_id'					=> $row['rate_pic_id'],
					'rate_user_id'					=> ($row['rate_user_id'] < 0) ? 1 : $row['rate_user_id'],
					'rate_user_ip'					=> decode_ip($row['rate_user_ip']),
					'rate_point'					=> $row['rate_point'],
				);
				$db->sql_query('INSERT INTO ' . GALLERY_RATES_TABLE . ' ' . $db->sql_build_array('INSERT', $rate_data));
			}
			$db->sql_freeresult($result);

			// third the comments...
			$sql = 'SELECT c.*, u.user_colour
				FROM ' . $convert_prefix . 'album_comment c
				LEFT JOIN ' . USERS_TABLE . ' u
					ON c.comment_user_id = u.user_id
				ORDER BY c.comment_id';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$row['comment_uid'] = $row['comment_options'] = $row['comment_bitfield'] = '';
				$row['comment'] = $row['comment_text'];
				$comment_text_data = generate_text_for_edit($row['comment'], $row['comment_uid'], $row['comment_options']);
				$comment_data = array(
					'comment_id'			=> $row['comment_id'],
					'comment_image_id'		=> $row['comment_pic_id'],
					'comment_user_id'		=> ($row['comment_user_id'] < 0) ? 1 : $row['comment_user_id'],
					'comment_username'		=> $row['comment_username'],
					'comment_user_colour'	=> (isset($row['user_colour'])) ? $row['user_colour'] : '',
					'comment_user_ip'		=> decode_ip($row['comment_user_ip']),
					'comment_time'			=> $row['comment_time'],
					'comment'				=> $comment_text_data['text'],
					'comment_uid'			=> '',
					'comment_bitfield'		=> '',
					'comment_options'		=> 7,
					'comment_edit_time'		=> (isset($row['comment_edit_time']) ? $row['comment_edit_time'] : 0),
					'comment_edit_count'	=> (isset($row['comment_edit_count']) ? $row['comment_edit_count'] : 0),
					'comment_edit_user_id'	=> (isset($row['comment_edit_user_id']) ? ($row['comment_edit_user_id'] < 0) ? 1 : $row['comment_edit_user_id'] : 0),
				);
				generate_text_for_storage($comment_data['comment'], $comment_data['comment_uid'], $comment_data['comment_bitfield'], $comment_data['comment_options'], 1, 1, 1);
				unset($comment_data['comment_options']);
				$db->sql_query('INSERT INTO ' . GALLERY_COMMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $comment_data));
			}
			$db->sql_freeresult($result);

			// fourth the configs...
			// because there might be some problems, we create them new...
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
			set_config('num_images', 0, true);
			set_config('gallery_total_images', 1);
			set_config('gallery_user_images_profil', 1);
			set_config('gallery_personal_album_profil', 1);

			// last and least the images...
			$sql = 'SELECT i.*, u.user_colour, u.username
				FROM ' . $convert_prefix . 'album AS i
				LEFT JOIN ' . USERS_TABLE . ' AS u
					ON i.pic_user_id = u.user_id
				ORDER BY i.pic_id';
			$result = $db->sql_query($sql);
			while( $row = $db->sql_fetchrow($result) )
			{
				$row['image_desc_uid'] = $row['image_desc_options'] = $row['image_desc_bitfield'] = '';
				$row['image_desc'] = $row['pic_desc'];
				$image_desc_data = generate_text_for_edit($row['image_desc'], $row['image_desc_uid'], $row['image_desc_options']);
				$image_data = array(
					'image_id'				=> $row['pic_id'],
					'image_filename'		=> $row['pic_filename'],
					'image_thumbnail'		=> $row['pic_thumbnail'],
					'image_name'			=> $row['pic_title'],
					'image_desc'			=> $image_desc_data['text'],
					'image_desc_uid'		=> '',
					'image_desc_bitfield'	=> '',
					'image_desc_options'	=> 7,
					'image_user_id'			=> ($row['pic_user_id'] < 0) ? 1 : $row['pic_user_id'],
					'image_username'		=> (isset($row['username'])) ? $row['username'] : $row['pic_username'],
					'image_user_colour'		=> (isset($row['user_colour'])) ? $row['user_colour'] : '',
					'image_user_ip'			=> decode_ip($row['pic_user_ip']),
					'image_time'			=> $row['pic_time'],
					'image_album_id'		=> (in_array($row['pic_cat_id'], $personal_album) ? 0 : $row['pic_cat_id']),
					'image_view_count'		=> $row['pic_view_count'],
					'image_status'			=> ($row['pic_lock']) ? 2 : $row['pic_approval'],
				);
				generate_text_for_storage($image_data['image_desc'], $image_data['image_desc_uid'], $image_data['image_desc_bitfield'], $image_data['image_desc_options'], true, true, true);
				unset($image_data['image_desc_options']);
				$db->sql_query('INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $image_data));
			}
			$db->sql_freeresult($result);

			nv_add_column(SESSIONS_TABLE, 'session_album_id', array('UINT', 0));

			//now create the new personal albums
			$sql = 'SELECT i.image_id, i.image_username, image_user_id
				FROM ' . GALLERY_IMAGES_TABLE . " AS i
				WHERE image_album_id = 0
				GROUP BY i.image_user_id DESC";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$album_data = array(
					'album_name'					=> $row['image_username'],
					'parent_id'						=> 0,
					//left_id and right_id are created some lines later
					'album_desc_options'			=> 7,
					'album_desc'					=> '',
					'album_parents'					=> '',
					'album_type'					=> 1,
					'album_user_id'					=> ($row['image_user_id'] < 0) ? 1 : $row['image_user_id'],
				);
				$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));

				$sql2 = 'SELECT album_id FROM ' . GALLERY_ALBUMS_TABLE . ' WHERE parent_id = 0 AND album_user_id = ' . $row['image_user_id'] . ' LIMIT 1';
				$result2 = $db->sql_query($sql2);
				$row2 = $db->sql_fetchrow($result2);
				$db->sql_freeresult($result2);

				$user_data = array(
					'personal_album_id'		=> $row2['album_id'],
					'user_id'				=> $row['image_user_id'],
				);
				$db->sql_query('INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $user_data));

				$sql3 = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
						SET image_album_id = ' . (int) $row2['album_id'] . '
						WHERE image_album_id = 0
							AND image_user_id  = ' . (int) $row['image_user_id'];
				$db->sql_query($sql3);
			}
			$db->sql_freeresult($result);


			//remove the old modules:
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

			//add the modules new:
			// ->ACP
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

			// -> UCP
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

			//album_type needs to be "album" for personal albums
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET album_type = 1';
			$db->sql_query($sql);
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET album_type = 0 WHERE album_user_id = 0';
			$db->sql_query($sql);

			//add the information for the last_image to the albums part 1: last_image_id, image_count
			$sql = 'SELECT COUNT(i.image_id) images, MAX(i.image_id) last_image_id, i.image_album_id
				FROM ' . GALLERY_IMAGES_TABLE . " i
				WHERE i.image_status = 1
				GROUP BY i.image_album_id";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$sql_ary = array(
					'album_images'			=> $row['images'],
					'album_last_image_id'	=> $row['last_image_id'],
				);
				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $db->sql_in_set('album_id', $row['image_album_id']);
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);

			//add the information for the last_image to the albums part 2: correct album_type, images_real are all images, even unapproved
			$sql = 'SELECT COUNT(i.image_id) images, i.image_album_id
				FROM ' . GALLERY_IMAGES_TABLE . " i
				GROUP BY i.image_album_id";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$sql_ary = array(
					'album_images_real'	=> $row['images'],
					'album_type'		=> 1,
				);
				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $db->sql_in_set('album_id', $row['image_album_id']);
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);

			//add the information for the last_image to the albums part 3: user_id, username, user_colour, time, image_name
			$sql = 'SELECT a.album_id, a.album_last_image_id, i.image_time, i.image_name, i.image_user_id, i.image_username, i.image_user_colour, u.user_colour
				FROM ' . GALLERY_ALBUMS_TABLE . " a
				LEFT JOIN " . GALLERY_IMAGES_TABLE . " i
					ON a.album_last_image_id = i.image_id
				LEFT JOIN " . USERS_TABLE . " u
					ON a.album_user_id = u.user_colour
				WHERE a.album_last_image_id > 0";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$sql_ary = array(
					'album_last_image_time'		=> $row['image_time'],
					'album_last_image_name'		=> $row['image_name'],
					'album_last_username'		=> $row['image_username'],
					'album_last_user_colour'	=> isset($row['user_colour']) ? $row['user_colour'] : '',
					'album_last_user_id'		=> $row['image_user_id'],
				);
				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $db->sql_in_set('album_id', $row['album_id']);
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);

			//count the number of personal_gallerys in the config to reduce sqls
			$sql = 'SELECT COUNT(album_id) AS albums
				FROM ' . GALLERY_ALBUMS_TABLE . "
				WHERE parent_id = 0
					AND album_user_id <> 0";
			$result = $db->sql_query($sql);
			$total_galleries = 0;
			if ($row = $db->sql_fetchrow($result))
			{
				$total_galleries = $row['albums'];
			}
			$db->sql_freeresult($result);
			gallery_config_value('personal_counter', $total_galleries);
			$num_images = 0;
			$sql = 'SELECT u.user_id, count(i.image_id) as images, gu.personal_album_id
				FROM ' . USERS_TABLE . ' u
				LEFT JOIN ' . GALLERY_IMAGES_TABLE . ' i
					ON i.image_user_id = u.user_id
					AND i.image_status = 1
				LEFT JOIN ' . GALLERY_USERS_TABLE . ' gu
					ON gu.user_id = u.user_id
				GROUP BY i.image_user_id';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$sql_ary = array(
					'user_id'				=> $row['user_id'],
					'user_images'			=> $row['images'],
				);
				$num_images = $num_images + $row['images'];
				if ($row['personal_album_id'])
				{
					$sql = 'UPDATE ' . GALLERY_USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE ' . $db->sql_in_set('user_id', $row['user_id']);
				}
				else
				{
					$db->sql_query('INSERT INTO ' . GALLERY_USERS_TABLE . $db->sql_build_array('INSERT', $sql_ary));
				}
			}
			$db->sql_freeresult($result);
			set_config('num_images', $album_config['num_images'], true);

			$gd_check = function_exists('gd_info') ? gd_info() : array();
			$gd_success = isset($gd_check['GD Version']);
			if (!$gd_success && ($album_config['gd_version'] > 0))
			{
				$sql = 'UPDATE ' . GALLERY_CONFIG_TABLE . "SET config_value = 0 WHERE config_name = 'gd_version'";
				$result = $db->sql_query($sql);
				$album_config['gd_version'] = 0;
			}

			add_bbcode('album');
			gallery_config_value('album_version', $new_mod_version, true);

			// clear cache and log what we did
			$cache->purge();
			add_log('admin', 'LOG_INSTALL_INSTALLED', $log_name);
			add_log('admin', 'LOG_PURGE_CACHE');
			$converted = true;
		}
		else
		{
			$get_acp_module = select_parent_module('acp', 31, 'ACP_CAT_DOT_MODS');
			$select_acp_module = $get_acp_module['list'];
			$default_acp_module = $get_acp_module['default'];
			$get_ucp_module = select_parent_module('ucp', 0, '');
			$select_ucp_module = $get_ucp_module['list'];
			$default_ucp_module = $get_ucp_module['default'];
		}
	break;
	case 'delete':
		$deleted = false;
		if ($delete == 1)
		{
			//drop the tables
			nv_drop_table('phpbb_gallery_albums');
			nv_drop_table('phpbb_gallery_comments');
			nv_drop_table('phpbb_gallery_config');
			nv_drop_table('phpbb_gallery_favorites');
			nv_drop_table('phpbb_gallery_images');
			nv_drop_table('phpbb_gallery_modscache');
			nv_drop_table('phpbb_gallery_permissions');
			nv_drop_table('phpbb_gallery_rates');
			nv_drop_table('phpbb_gallery_reports');
			nv_drop_table('phpbb_gallery_roles');
			nv_drop_table('phpbb_gallery_users');
			nv_drop_table('phpbb_gallery_watch');

			$bbcode_id = request_var('bbcode_id', 0);
			$sql = 'DELETE FROM ' . BBCODES_TABLE . " WHERE bbcode_id = $bbcode_id";
			$db->sql_query($sql);

			//remove the modules:
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

			//remove the columns
			nv_remove_column(USERS_TABLE, 'album_id');
			nv_remove_column(GROUPS_TABLE, 'allow_personal_albums');
			nv_remove_column(GROUPS_TABLE, 'view_personal_albums');
			nv_remove_column(GROUPS_TABLE, 'personal_subalbums');
			nv_remove_column(SESSIONS_TABLE, 'session_album_id');

			$cache->purge();
			add_log('admin', 'LOG_PURGE_CACHE');
			$deleted = true;
		}
		else
		{
			$select_bbcode = '';
			$sql = 'SELECT bbcode_id, bbcode_tag
				FROM ' . BBCODES_TABLE;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$select_bbcode .= ($select_bbcode == '') ? '<select name="bbcode_id"><option value="0">' . $user->lang['INSTALLER_DELETE_BBCODE'] . '</option>' : '';
				$select_bbcode .= '<option value="' . $row['bbcode_id'] . '">[' . $row['bbcode_tag'] . ']</option>';
			}
			$db->sql_freeresult($result);
			if ($select_bbcode)
			{
				$select_bbcode .= '</select>';
			}
		}
	break;

	default:
		//we had a little cheater
	break;
}

include($phpbb_root_path . 'install_gallery/layout.'.$phpEx);
?>