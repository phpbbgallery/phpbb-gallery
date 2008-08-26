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
$mode = request_var('mode', 'smartor');

$message = $select_ucp_module = $default_ucp_module = $select_acp_module = $default_acp_module = '';
$convert_prefix = request_var('convert_prefix', '', true);
$personal_album = request_var('personal_album', '');
$choosen_acp_module = request_var('select_acp_module', 0);
$choosen_ucp_module = request_var('select_ucp_module', 0);
$full_convert_steps = 14;

$template->assign_vars(array(
	'S_IN_CONVERT'			=> true,
	'U_ACTION'				=> append_sid("{$phpbb_root_path}install/convert.php", "mode=$mode"),
	'MODE'					=> $mode,
	'CONVERT_NOTE_SMARTOR'	=> $user->lang['INSTALLER_CONVERT_SMARTOR'],
	'CONVERT_NOTE'			=> sprintf($user->lang['INSTALLER_CONVERT_NOTE'], $new_mod_version),
));

if ($submit && ($convert_prefix == ''))
{
	$message = sprintf($user->lang['INSTALLER_CONVERT_UNSUCCESSFUL2'], $new_mod_version);
	$message .= install_back_link(append_sid("{$phpbb_root_path}install/convert.$phpEx", "mode=$mode"));
	trigger_error($message, E_USER_WARNING);
	$submit = false;
}

if ($submit)
{
	/*
	* convert IP's from phpBB2-System to phpBB3-System
	*/
	function decode_ip($int_ip)
	{
		$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
		$phpbb3_ip = hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);

		return $phpbb3_ip;
	}

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
			$message = sprintf($user->lang['STEP_LOG'], $full_convert_steps, 1, $user->lang['STEPS_DBSCHEMA'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 2:
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

			$choosen_ucp_module = $choosen_acp_module = 0;
			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_convert_steps, 2, $user->lang['STEPS_MODULES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 3:
			/*
			* import the rates
			*/
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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_convert_steps, 3, $user->lang['STEPS_IMPORT_RATES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 4:
			/*
			* import the comments
			*/
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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_convert_steps, 4, $user->lang['STEPS_IMPORT_COMMENTS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 5:
			/*
			* import the albums
			*/
			// to make a better convertor form FAP, i need a volunter to give me a full backup
			// so long we loose all the sub-systems
			$personal_album = '0';
			$left_id = 1;
			$sql = 'SELECT *
				FROM ' . $convert_prefix . 'album_cat
				ORDER BY cat_order';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$row['cat_user_id'] = (isset($row['cat_user_id']) ? $row['cat_user_id'] : 0);
				if ($row['cat_user_id'] != 0)
				{
					$personal_album .= '_' . $row['cat_id'];
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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_convert_steps, 5, $user->lang['STEPS_IMPORT_ALBUMS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 6:
			/*
			* import the images
			*/
			$personal_album = explode('_', $personal_album);
			$sql = 'SELECT i.*, u.user_colour, u.username
				FROM ' . $convert_prefix . 'album i
				LEFT JOIN ' . USERS_TABLE . ' u
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
					'image_reported'		=> 0,
				);
				generate_text_for_storage($image_data['image_desc'], $image_data['image_desc_uid'], $image_data['image_desc_bitfield'], $image_data['image_desc_options'], true, true, true);
				unset($image_data['image_desc_options']);
				$db->sql_query('INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $image_data));
			}
			$db->sql_freeresult($result);
			$personal_album = '';

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_convert_steps, 6, $user->lang['STEPS_IMPORT_IMAGES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 7:
			/*
			* generate personal albums
			*/
			$personal_albums = 0;
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
				$new_personal_album_id = $db->sql_nextid();
				$personal_albums++;

				$user_data = array(
					'personal_album_id'		=> $new_personal_album_id,
					'user_id'				=> $row['image_user_id'],
				);
				$db->sql_query('INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $user_data));

				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . " 
						SET image_album_id = $new_personal_album_id
						WHERE image_album_id = 0
							AND image_user_id  = " . (int) $row['image_user_id'];
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);
			gallery_config_value('personal_counter', $personal_albums);

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_convert_steps, 7, $user->lang['STEPS_ADD_PERSONALS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 8:
			/*
			* Resyncronize Album-Stats
			*/
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET album_type = 1';
			$db->sql_query($sql);
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET album_type = 0 WHERE album_user_id = 0';
			$db->sql_query($sql);

			//Step 8.1: Number of public images and last_image_id
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

			//Step 8.2: Number of real images and album_type
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

			//Step 8.3: Last image data
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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_convert_steps, 8, $user->lang['STEPS_RESYN_ALBUMS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 9:
			/*
			* Resyncronize Image-counters
			*/
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
			set_config('num_images', $num_images, true);

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_convert_steps, 9, $user->lang['STEPS_RESYN_COUNTERS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 10:
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
			set_config('num_images', 0, true);
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
			$message = sprintf($user->lang['STEP_LOG'], $full_convert_steps, 10, $user->lang['STEPS_ADD_CONFIGS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 11:
			/*
			* Add BBCode
			*/
			add_bbcode('album');

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_convert_steps, 11, $user->lang['STEPS_ADD_BBCODE'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 12:
			/*
			* Update image rate-data
			*/
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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_convert_steps, 12, $user->lang['STEPS_UPDATE_IMAGES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 13:
			/*
			* Update image comment-data
			*/
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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], $full_convert_steps, 13, $user->lang['STEPS_UPDATE_IMAGES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 14:
			/*
			* Final Step
			*/
			gallery_config_value('album_version', $new_mod_version, true);
			$cache->purge();
			add_log('admin', 'LOG_INSTALL_INSTALLED', $log_name);
			add_log('admin', 'LOG_PURGE_CACHE');
			$message = sprintf($user->lang['INSTALLER_CONVERT_SUCCESSFUL'], $new_mod_version);
			trigger_error($message);

		break;
	}
	$refresh_ary = array(
		'mode'	=> $mode,
		'step'	=> $load_new_step,
		'convert_prefix'	=> $convert_prefix,
	);
	if ($personal_album)
	{
		$refresh_ary['personal_album'] = $personal_album;
	}
	if ($choosen_ucp_module)
	{
		$refresh_ary['select_ucp_module'] = $choosen_ucp_module;
	}
	if ($choosen_acp_module)
	{
		$refresh_ary['select_acp_module'] = $choosen_acp_module;
	}
	meta_refresh(3, append_sid("{$phpbb_root_path}install/convert.php", $refresh_ary));
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
	'body' => 'convert_body.html')
);

page_footer();

?>