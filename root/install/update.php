<?php

/**
*
* @package NV Install
* @version $Id$
* @copyright (c) 2008 nickvergessen http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/

define('IN_PHPBB', true);
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'install/common.' . $phpEx);

$load_step = request_var('step', 1);
$confirm = request_var('confirm', 0);
$submit = ((isset($_POST['submit']) && $confirm) || ($load_step > 1)) ? true : false;

$message = $aiming_version = $select_ucp_module = $default_ucp_module = $select_acp_module = $default_acp_module = $personal_album = '';
$choosen_acp_module = request_var('select_acp_module', 0);
$choosen_ucp_module = request_var('select_ucp_module', 0);
$version = request_var('v', "$last_mod_version");
$full_update_steps = 26;
$steps_offset = $start_step = 0;
$create_new_modules = false;

switch ($version)
{
	case '0.1.2':
		$steps_offset = 0;
		$start_step = 1;
		$create_new_modules = true;
	break;
	case '0.1.3':
		$steps_offset = 1;
		$start_step = 2;
		$create_new_modules = true;
	break;
	case '0.2.0':
	case '0.2.1':
	case '0.2.2':
	case '0.2.3':
		$steps_offset = 7;
		$start_step = 8;
		$create_new_modules = true;
	break;
	case '0.3.0':
	case '0.3.1':
		$steps_offset = 10;
		$start_step = 11;
		$create_new_modules = true;
	break;
	case '0.4.0-RC1':
		$steps_offset = 21;
		$start_step = 22;
		$create_new_modules = true;
	break;
	case '0.4.0-RC2':
		$steps_offset = 24;
		$start_step = 25;
	break;
	case 'svn':
	break;
}
if ($load_step == 1)
{
	$load_step = $start_step;
}
	$major_versions = array('0.4.x', '0.3.x', '0.2.x', '0.1.x');
	$minor_versions['0.4.x'] = array('0.4.0-RC2', '0.4.0-RC1');
	$minor_versions['0.3.x'] = array('0.3.1', '0.3.0');
	$minor_versions['0.2.x'] = array('0.2.3', '0.2.2', '0.2.1', '0.2.0');
	$minor_versions['0.1.x'] = array('0.1.3', '0.1.2');
	foreach ($major_versions as $major)
	{
		$template->assign_block_vars('menu_row', array(
			'VERSION'		=> $major,
		));
		foreach ($minor_versions[$major] as $minor)
		{
			$template->assign_block_vars('menu_row.update', array(
				'VERSION'		=> $minor,
				'U_VERSION'		=> append_sid("{$phpbb_root_path}install/update.$phpEx", "v=$minor"),
			));
		}
	}

$template->assign_vars(array(
	'S_IN_UPDATE'			=> true,
	'U_ACTION'				=> append_sid("{$phpbb_root_path}install/update.php", "v=$version"),
	'VERSION'				=> $version,
	'UPDATE_NOTE'			=> sprintf($user->lang['UPDATE_NOTE'], $version, $new_mod_version),
));

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
			* Create the db-structure: >0.1.2
			*/
			define('ALBUM_TABLE',					$table_prefix.'album');
			define('ALBUM_CAT_TABLE',				$table_prefix.'album_cat');
			define('ALBUM_COMMENT_TABLE',			$table_prefix.'album_comment');

			nv_add_column(ALBUM_TABLE, 'pic_desc_bbcode_bitfield', array('VCHAR:255', ''));
			nv_add_column(ALBUM_TABLE, 'pic_desc_bbcode_uid', array('VCHAR:8', ''));
			nv_add_column(ALBUM_CAT_TABLE, 'cat_desc_bbcode_bitfield', array('VCHAR:255', ''));
			nv_add_column(ALBUM_CAT_TABLE, 'cat_desc_bbcode_uid', array('VCHAR:8', ''));
			nv_add_column(ALBUM_COMMENT_TABLE, 'comment_text_bbcode_bitfield', array('VCHAR:255', ''));
			nv_add_column(ALBUM_COMMENT_TABLE, 'comment_text_bbcode_uid', array('VCHAR:8', ''));

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (1 - $steps_offset), $user->lang['STEPS_DBSCHEMA'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 2:
			/*
			* Create the db-structure: >0.1.3
			*/
			nv_create_table('phpbb_gallery_albums', true);
			nv_create_table('phpbb_gallery_comments', true);
			nv_create_table('phpbb_gallery_config', true);
			nv_create_table('phpbb_gallery_images', true);
			nv_create_table('phpbb_gallery_rates', true);

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (2 - $steps_offset), $user->lang['STEPS_DBSCHEMA'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 3:
			/*
			* Copy albums to new table: >0.1.3
			*/
			define('ALBUM_CAT_TABLE',				$table_prefix.'album_cat');
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
				);
				generate_text_for_storage($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options'], true, true, true);
				$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
				$left_id = $left_id + 2;
			}
			$db->sql_freeresult($result);

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (3 - $steps_offset), $user->lang['STEPS_COPY_ALBUMS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 4:
			/*
			* Copy rates to new table: >0.1.3
			*/
			define('ALBUM_RATE_TABLE',				$table_prefix.'album_rate');
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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (4 - $steps_offset), $user->lang['STEPS_COPY_RATES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 5:
			/*
			* Copy comments to new table: >0.1.3
			*/
			define('ALBUM_COMMENT_TABLE',			$table_prefix.'album_comment');
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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (5 - $steps_offset), $user->lang['STEPS_COPY_COMMENTS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 6:
			/*
			* Copy images to new table: >0.1.3
			*/
			nv_add_column(GALLERY_IMAGES_TABLE, 'image_lock');
			nv_add_column(GALLERY_IMAGES_TABLE, 'image_approval');
			define('ALBUM_TABLE',					$table_prefix.'album');
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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (6 - $steps_offset), $user->lang['STEPS_COPY_IMAGES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 7:
			/*
			* Create gallery-config: >0.1.3
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
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (7 - $steps_offset), $user->lang['STEPS_ADD_CONFIG'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 8:
			/*
			* Add gallery-config: >0.2.3
			*/
			gallery_config_value('preview_rsz_height', 600);
			gallery_config_value('preview_rsz_width', 800);
			gallery_config_value('upload_images', 10);
			gallery_config_value('thumbnail_info_line', 1);

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (8 - $steps_offset), $user->lang['STEPS_ADD_CONFIGS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 9:
			/*
			* Update image_username and image_user_colour: >0.2.3
			*/
			nv_add_column(GALLERY_IMAGES_TABLE, 'image_user_colour', array('VCHAR:6', ''));
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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (9 - $steps_offset), $user->lang['STEPS_UPDATE_IMAGES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 10:
			/*
			* Create personal albums: >0.2.3
			*/
			nv_add_column(GALLERY_ALBUMS_TABLE, 'album_user_id', array('UINT', 0));
			nv_add_column(USERS_TABLE, 'album_id', array('UINT', 0));
			$sql = 'SELECT image_id, image_username, image_user_id
				FROM ' . GALLERY_IMAGES_TABLE . "
				WHERE image_album_id = 0
				GROUP BY image_user_id DESC";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$album_data = array(
					'album_name'					=> $row['image_username'],
					'parent_id'						=> 0,
					'album_desc_options'			=> 7,
					'album_desc'					=> '',
					'album_parents'					=> '',
					'album_type'					=> 2,
					'album_user_id'					=> $row['image_user_id'],
				);
				$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
				$new_personal_album_id = $db->sql_nextid();

				$sql = 'UPDATE ' . USERS_TABLE . " 
					SET album_id = $new_personal_album_id
					WHERE user_id  = " . $row['image_user_id'];
				$db->sql_query($sql);

				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . " 
					SET image_album_id = $new_personal_album_id
					WHERE image_album_id = 0
						AND image_user_id  = " . $row['image_user_id'];
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (10 - $steps_offset), $user->lang['STEPS_ADD_PERSONALS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 11:
			/*
			* Add BBCode: >0.3.1
			*/
			add_bbcode('album');

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (11 - $steps_offset), $user->lang['STEPS_ADD_PERSONALS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 12:
			/*
			* Create the db-structure: >0.3.1
			*/
			nv_create_table('phpbb_gallery_favorites', true);
			nv_create_table('phpbb_gallery_modscache', true);
			nv_create_table('phpbb_gallery_permissions', true);
			nv_create_table('phpbb_gallery_reports', true);
			nv_create_table('phpbb_gallery_roles', true);
			nv_create_table('phpbb_gallery_users', true);
			nv_create_table('phpbb_gallery_watch', true);

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (12 - $steps_offset), $user->lang['STEPS_DBSCHEMA'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 13:
			/*
			* Add and change some columns: >0.3.1
			*/
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
			nv_add_column(SESSIONS_TABLE, 'session_album_id', array('UINT', 0));
			nv_change_column(GALLERY_COMMENTS_TABLE, 'comment_username', array('VCHAR', ''));

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (13 - $steps_offset), $user->lang['STEPS_DBSCHEMA'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 14:
			/*
			* Add gallery-config: >0.3.1
			*/

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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (14 - $steps_offset), $user->lang['STEPS_ADD_CONFIGS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 15:
			/*
			* Resyncronize Album-Stats: >0.3.1
			*/
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET album_type = 1';
			$db->sql_query($sql);
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET album_type = 0 WHERE album_user_id = 0';
			$db->sql_query($sql);

			//Step 15.1: Number of public images and last_image_id
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

			//Step 15.2: Number of real images and album_type
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

			//Step 15.3: Last image data
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
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (15 - $steps_offset), $user->lang['STEPS_RESYN_ALBUMS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 16:
			/*
			* Update image rate-data: >0.3.1
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
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (16 - $steps_offset), $user->lang['STEPS_UPDATE_IMAGES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 17:
			/*
			* Update image comment-data: >0.3.1
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
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (17 - $steps_offset), $user->lang['STEPS_UPDATE_IMAGES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 18:
			/*
			* Update imagestatus: >0.3.1
			*/
			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
				SET image_status = 2
				WHERE image_lock = 1';
			$db->sql_query($sql);
			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
				SET image_status = 0
				WHERE image_lock = 0
					AND image_approval = 0';
			$db->sql_query($sql);

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (18 - $steps_offset), $user->lang['STEPS_UPDATE_IMAGES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 19:
			/*
			* Update comment_user_colour: >0.3.1
			*/
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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (19 - $steps_offset), $user->lang['STEPS_UPDATE_COMMENTS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 20:
			/*
			* Remove some columns: >0.3.1
			*/
			nv_remove_column(GROUPS_TABLE, 'personal_subalbums');
			nv_remove_column(GROUPS_TABLE, 'allow_personal_albums');
			nv_remove_column(GROUPS_TABLE, 'view_personal_albums');
			nv_remove_column(USERS_TABLE, 'album_id');
			nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_approval');
			nv_remove_column(GALLERY_ALBUMS_TABLE, 'album_order');
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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (20 - $steps_offset), $user->lang['STEPS_REMOVE_COLUMNS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 21:
			/*
			* Remove some configs: >0.3.1
			*/
			$sql = 'DELETE FROM ' .GALLERY_CONFIG_TABLE . '
				WHERE ' . $db->sql_in_set('config_name', $old_configs);
			$db->sql_query($sql);

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (21 - $steps_offset), $user->lang['STEPS_REMOVE_CONFIGS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 22:
			/*
			* handle the modules: >0.4.0-RC1
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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (22 - $steps_offset), $user->lang['STEPS_RESYN_MODULES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 23:
			/*
			* handle the modules: >0.4.0-RC1
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
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (23 - $steps_offset), $user->lang['STEPS_MODULES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 24:
			/*
			* Resyncronize Image-counters: >0.4.0-RC1
			*/
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

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (24 - $steps_offset), $user->lang['STEPS_RESYN_COUNTERS'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 25:
			/*
			* Update image_reported: >0.4.0-RC2
			*/
			nv_add_column(GALLERY_IMAGES_TABLE, 'image_reported', array('UINT', 0));
			$sql = 'SELECT report_image_id, report_id
				FROM ' . GALLERY_REPORTS_TABLE . "
				WHERE report_status = 1";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET image_reported = ' . $row['report_id'] . '
					WHERE ' . $db->sql_in_set('image_id', $row['report_image_id']);
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);

			$load_new_step = $load_step + 1;
			$message = sprintf($user->lang['STEP_LOG'], ($full_update_steps - $steps_offset), (25 - $steps_offset), $user->lang['STEPS_UPDATE_IMAGES'], $user->lang['STEP_SUCCESSFUL']);
		break;

		case 26:
			/*
			* Final step
			*/
			gallery_config_value('album_version', $new_mod_version, true);
			$cache->purge();
			add_log('admin', 'LOG_INSTALL_INSTALLED', $log_name);
			add_log('admin', 'LOG_PURGE_CACHE');
			$message = sprintf($user->lang['UPDATE_SUCCESSFUL'], $new_mod_version);
			trigger_error($message);

		break;
	}
	$refresh_ary = array(
		'v'		=> $version,
		'step'	=> $load_new_step,
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
	meta_refresh(3, append_sid("{$phpbb_root_path}install/update.php", $refresh_ary));
	trigger_error($message);

}
else
{
	check_chmods();
	if ($create_new_modules)
	{
		$get_acp_module = select_parent_module('acp', 31, 'ACP_CAT_DOT_MODS');
		$select_acp_module = $get_acp_module['list'];
		$default_acp_module = $get_acp_module['default'];
		$get_ucp_module = select_parent_module('ucp', 0, '');
		$select_ucp_module = $get_ucp_module['list'];
		$default_ucp_module = $get_ucp_module['default'];
	}

	$template->assign_vars(array(
		'CREATE_MODULES'		=> $create_new_modules,
		'S_UCP_MODULE'			=> $create_new_modules,
		'DEFAULT_UCP_MODULE'	=> $default_ucp_module,
		'SELECT_UCP_MODULE'		=> $select_ucp_module,
		'S_ACP_MODULE'			=> $create_new_modules,
		'DEFAULT_ACP_MODULE'	=> $default_acp_module,
		'SELECT_ACP_MODULE'		=> $select_acp_module,
	));
}


page_header($page_title);

$template->set_filenames(array(
	'body' => 'update_body.html')
);

page_footer();

?>