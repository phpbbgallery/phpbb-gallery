<?php

/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}
function display_albums($root_data = '', $display_moderators = true, $return_moderators = false)
{
	global $db, $auth, $user, $template, $album_access_array;
	global $phpbb_root_path, $gallery_root_path, $phpEx, $config, $album_config;

	$album_rows = $subalbums = $album_ids = $album_ids_moderator = $album_moderators = $active_album_ary = array();
	$parent_id = $visible_albums = 0;
	$sql_from = '';
	$mode = request_var('mode', '');

	if (!$root_data)
	{
		$root_data = array('album_id' => 0);
		$sql_where = 'album_user_id = 0';
	}
	else if ($root_data == 'personal')
	{
		$root_data = array('album_id' => 0);
		$sql_where = 'album_user_id > 0';

		$start = request_var('start', 0);
		$limit = ceil($config['topics_per_page'] / 2);
		$total_galleries = $album_config['personal_counter'];
		$pagination = generate_pagination("{$phpbb_root_path}{$gallery_root_path}index.$phpEx", 'mode=$mode', $album_config['personal_counter'], $limit, $start);
		$template->assign_vars(array(
			'PAGINATION'			=> generate_pagination("{$phpbb_root_path}{$gallery_root_path}index.$phpEx?mode=$mode", $album_config['personal_counter'], $limit, $start),
		));
	}
	else
	{
		$sql_where = 'left_id > ' . $root_data['left_id'] . ' AND left_id < ' . $root_data['right_id'] . ' AND album_user_id = ' . $root_data['album_user_id'];
	}

	$sql_array = array(
		'SELECT'	=> 'a.*',
		'FROM'		=> array(
			GALLERY_ALBUMS_TABLE		=> 'a'
		),
		'LEFT_JOIN'	=> array(),
	);

	$sql = $db->sql_build_query('SELECT', array(
		'SELECT'	=> $sql_array['SELECT'],
		'FROM'		=> $sql_array['FROM'],
		'LEFT_JOIN'	=> $sql_array['LEFT_JOIN'],

		'WHERE'		=> $sql_where,

		'ORDER_BY'	=> 'a.album_user_id, a.left_id',
	));

	$result = $db->sql_query($sql);

	$album_tracking_info = array();
	$branch_root_id = $root_data['album_id'];
	while ($row = $db->sql_fetchrow($result))
	{
		$album_id = $row['album_id'];

		// Category with no members
		if (!$row['album_type'] && ($row['left_id'] + 1 == $row['right_id']))
		{
			continue;
		}

		// Skip branch
		if (isset($right_id))
		{
			if ($row['left_id'] < $right_id)
			{
				continue;
			}
			unset($right_id);
		}

		if (!gallery_acl_check('i_view', $album_id, $row['album_user_id']))
		{
			// if the user does not have permissions to list this forum, skip everything until next branch
			$right_id = $row['right_id'];
			continue;
		}

		$album_ids[] = $album_id;

		$row['album_images'] = $row['album_images'];
		$row['album_images_real'] = $row['album_images_real'];

		if ($row['parent_id'] == $root_data['album_id'] || $row['parent_id'] == $branch_root_id)
		{
			if ($row['album_type'])
			{
				$album_ids_moderator[] = (int) $album_id;
			}

			// Direct child of current branch
			$parent_id = $album_id;
			$album_rows[$album_id] = $row;

			if (!$row['album_type'] && $row['parent_id'] == $root_data['album_id'])
			{
				$branch_root_id = $album_id;
			}
			$album_rows[$parent_id]['album_id_last_image'] = $row['album_id'];
			$album_rows[$parent_id]['orig_album_last_image_time'] = $row['album_last_image_time'];
		}
		else if ($row['album_type'])
		{
			$subalbums[$parent_id][$album_id]['display'] = ($row['display_on_index']) ? true : false;
			$subalbums[$parent_id][$album_id]['name'] = $row['album_name'];
			$subalbums[$parent_id][$album_id]['orig_album_last_image_time'] = $row['album_last_image_time'];

			$album_rows[$parent_id]['album_images'] += $row['album_images'];
			$album_rows[$parent_id]['album_images_real'] += $row['album_images_real'];

			if ($row['album_last_image_time'] > $album_rows[$parent_id]['album_last_image_time'])
			{
				$album_rows[$parent_id]['album_last_image_id'] = $row['album_last_image_id'];
				$album_rows[$parent_id]['album_last_image_name'] = $row['album_last_image_name'];
				$album_rows[$parent_id]['album_last_image_time'] = $row['album_last_image_time'];
				$album_rows[$parent_id]['album_last_user_id'] = $row['album_last_user_id'];
				$album_rows[$parent_id]['album_last_username'] = $row['album_last_username'];
				$album_rows[$parent_id]['album_last_user_colour'] = $row['album_last_user_colour'];
				$album_rows[$parent_id]['album_id_last_image'] = $album_id;
			}
		}
	}
	$db->sql_freeresult($result);

	// Grab moderators ... if necessary
	if ($display_moderators)
	{
		if ($return_moderators)
		{
			$album_ids_moderator[] = $root_data['album_id'];
		}
		get_album_moderators($album_moderators, $album_ids_moderator);
	}

	// Used to tell whatever we have to create a dummy category or not.
	$last_catless = true;
	foreach ($album_rows as $row)
	{
		// Empty category
		if ($row['parent_id'] == $root_data['album_id'] && !$row['album_type'])
		{
			$template->assign_block_vars('albumrow', array(
				'S_IS_CAT'				=> true,
				'ALBUM_ID'				=> $row['album_id'],
				'ALBUM_NAME'			=> $row['album_name'],
				'ALBUM_DESC'			=> generate_text_for_display($row['album_desc'], $row['album_desc_uid'], $row['album_desc_bitfield'], $row['album_desc_options']),
				'ALBUM_FOLDER_IMG'		=> '',
				'ALBUM_FOLDER_IMG_SRC'	=> '',
				'ALBUM_IMAGE'			=> $row['album_image'],
				'U_VIEWALBUM'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $row['album_id']))
			);

			continue;
		}

		$visible_albums++;
		if (($mode == 'personal') && (($visible_albums < $start) || ($visible_albums > ($start + $limit))))
		{
			continue;
		}

		$album_id = $row['album_id'];
		$folder_image = $folder_alt = $l_subalbums = '';
		$subalbums_list = array();

		// Generate list of subforums if we need to
		if (isset($subalbums[$album_id]))
		{
			foreach ($subalbums[$album_id] as $subalbum_id => $subalbum_row)
			{
				if ($subalbum_row['display'] && $subalbum_row['name'])
				{
					$subalbums_list[] = array(
						'link'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $subalbum_id),
						'name'		=> utf8_substr($subalbum_row['name'], 0, 5),//REMOVE
					);
				}
				else
				{
					unset($subalbums[$album_id][$subalbum_id]);
				}
			}

			$l_subalbums = (sizeof($subalbums[$album_id]) == 1) ? $user->lang['SUBALBUM'] . ': ' : $user->lang['SUBALBUMS'] . ': ';
			$folder_image = 'forum_read_subforum';
		}
		else
		{
			$folder_image = 'forum_read';
		}

		$folder_alt = '';

		// Create last post link information, if appropriate
		if ($row['album_last_image_id'])
		{
			$last_image_name = (utf8_strlen(htmlspecialchars_decode($row['album_last_image_name'])) > $album_config['shorted_imagenames'] + 3 )? (utf8_substr(htmlspecialchars_decode($row['album_last_image_name']), 0, $album_config['shorted_imagenames']) . '...') : ($row['album_last_image_name']);
			$last_image_time = $user->format_date($row['album_last_image_time']);
			$last_image_url = append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", 'album_id=' . $row['album_id_last_image'] . '&amp;image_id=' . $row['album_last_image_id']);
			$last_image_page_url = append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $row['album_id_last_image'] . '&amp;image_id=' . $row['album_last_image_id']);
			$last_thumb_url = append_sid("{$phpbb_root_path}{$gallery_root_path}thumbnail.$phpEx", 'album_id=' . $row['album_id_last_image'] . '&amp;image_id=' . $row['album_last_image_id']);
		}
		else
		{
			$last_image_name = $last_image_time = $last_image_url = $last_image_page_url = $last_thumb_url = '';
		}

		// Output moderator listing ... if applicable
		$l_moderator = $moderators_list = '';
		if ($display_moderators && !empty($album_moderators[$album_id]))
		{
			$l_moderator = (sizeof($album_moderators[$album_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
			$moderators_list = implode(', ', $album_moderators[$album_id]);
		}

		#$l_post_click_count = ($row['forum_type'] == FORUM_LINK) ? 'CLICKS' : 'POSTS';
		#$post_click_count = ($row['forum_type'] != FORUM_LINK || $row['forum_flags'] & FORUM_FLAG_LINK_TRACK) ? $row['forum_posts'] : '';

		$s_subalbums_list = array();
		foreach ($subalbums_list as $subalbum)
		{
			$s_subalbums_list[] = '<a href="' . $subalbum['link'] . '" class="subforum read">' . $subalbum['name'] . '</a>';
		}
		$s_subalbums_list = (string) implode(', ', $s_subalbums_list);
		$catless = ($row['parent_id'] == $root_data['album_id']) ? true : false;

		$u_viewalbum = append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $row['album_id']);

		$template->assign_block_vars('albumrow', array(
			'S_IS_CAT'			=> false,
			'S_NO_CAT'			=> $catless && !$last_catless,
			#'S_LOCKED_ALBUM'	=> ($row['album_status'] == ITEM_LOCKED) ? true : false,
			'S_LIST_SUBALBUMS'	=> ($row['display_subalbum_list']) ? true : false,
			'S_SUBALBUMS'		=> (sizeof($subalbums_list)) ? true : false,

			'ALBUM_ID'				=> $row['album_id'],
			'ALBUM_NAME'			=> $row['album_name'],
			'ALBUM_DESC'			=> generate_text_for_display($row['album_desc'], $row['album_desc_uid'], $row['album_desc_bitfield'], $row['album_desc_options']),
			'IMAGES'				=> $row['album_images'],
			'UNAPPROVED_IMAGES'		=> (gallery_acl_check('a_moderate', $album_id, $row['album_user_id'])) ? ($row['album_images_real'] - $row['album_images']) : 0,
			'ALBUM_FOLDER_IMG'		=> $user->img($folder_image, $folder_alt),
			'ALBUM_FOLDER_IMG_SRC'	=> $user->img($folder_image, $folder_alt, false, '', 'src'),
			'ALBUM_FOLDER_IMG_ALT'	=> isset($user->lang[$folder_alt]) ? $user->lang[$folder_alt] : '',
			'ALBUM_IMAGE'			=> $row['album_image'],
			'U_LAST_THUMB'			=> $last_thumb_url,
			'U_LAST_IMAGE'			=> $last_image_url,
			'U_LAST_IMAGE_PAGE'		=> $last_image_page_url,
			'LAST_IMAGE_NAME'		=> censor_text($last_image_name),
			'LAST_IMAGE_TIME'		=> $last_image_time,
			'LAST_USER_FULL'		=> get_username_string('full', $row['album_last_user_id'], $row['album_last_username'], $row['album_last_user_colour']),
			'ALBUM_COLOUR'			=> get_username_string('colour', $row['album_last_user_id'], $row['album_last_username'], $row['album_last_user_colour']),
			'MODERATORS'			=> $moderators_list,
			'SUBALBUMS'				=> $s_subalbums_list,

			'L_SUBALBUM_STR'		=> $l_subalbums,
			'L_ALBUM_FOLDER_ALT'	=> $folder_alt,
			'L_MODERATOR_STR'		=> $l_moderator,

			'U_VIEWALBUM'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $row['album_id']),
		));

		// Assign subforums loop for style authors
		foreach ($subalbums_list as $subalbum)
		{
			$template->assign_block_vars('albumrow.subalbum', array(
				'U_SUBALBUM'	=> $subalbum['link'],
				'SUBALBUM_NAME'	=> $subalbum['name'],
			));
		}

		$last_catless = $catless;
	}

	$template->assign_vars(array(
		'U_MARK_ALBUMS'		=> ($user->data['is_registered'] || $config['load_anon_lastread']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $root_data['album_id'] . '&amp;mark=albums') : '',
		'S_HAS_SUBALBUM'	=> ($visible_albums) ? true : false,
		'L_SUBFORUM'		=> ($visible_albums == 1) ? $user->lang['SUBALBUM'] : $user->lang['SUBALBUMS'],
		'LAST_POST_IMG'		=> $user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
		'DISP_FAKE_THUMB'	=> (empty($album_config['disp_fake_thumb'])) ? 0 : $album_config['disp_fake_thumb'],
		'FAKE_THUMB_SIZE'	=> (empty($album_config['fake_thumb_size'])) ? 50 : $album_config['fake_thumb_size'],
	));

	if ($return_moderators)
	{
		return array($active_album_ary, $album_moderators);
	}

	return array($active_album_ary, array());
}
?>