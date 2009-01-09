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

/**
* Display albums
*
* borrowed from phpBB3
* @author: phpBB Group
* @function: display_forums
*/
function display_albums($root_data = '', $display_moderators = true, $return_moderators = false)
{
	global $db, $auth, $user, $template, $album_access_array;
	global $phpbb_root_path, $gallery_root_path, $phpEx, $config, $gallery_config;

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
		$mode_personal = true;

		$start = request_var('start', 0);
		$limit = ceil($config['topics_per_page'] / 2);
		$total_galleries = $gallery_config['personal_counter'];
		$pagination = generate_pagination("{$phpbb_root_path}{$gallery_root_path}index.$phpEx", 'mode=$mode', $gallery_config['personal_counter'], $limit, $start);
		$template->assign_vars(array(
			'PAGINATION'			=> generate_pagination("{$phpbb_root_path}{$gallery_root_path}index.$phpEx?mode=$mode", $gallery_config['personal_counter'], $limit, $start),
		));
	}
	else
	{
		$sql_where = 'left_id > ' . $root_data['left_id'] . ' AND left_id < ' . $root_data['right_id'] . ' AND album_user_id = ' . $root_data['album_user_id'];
	}

	$sql_array = array(
		'SELECT'	=> 'a.*',
		'FROM'		=> array(
			GALLERY_ALBUMS_TABLE		=> 'a',
		),
		'LEFT_JOIN'	=> array(),
		'ORDER_BY'	=> 'a.album_user_id, a.left_id',
	);

	if (isset($mode_personal))
	{
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(USERS_TABLE => 'u'),
			'ON'	=> 'u.user_id = a.album_user_id'
		);
		$sql_array['ORDER_BY'] = 'u.username_clean, a.left_id';
	}

	$sql = $db->sql_build_query('SELECT', array(
		'SELECT'	=> $sql_array['SELECT'],
		'FROM'		=> $sql_array['FROM'],
		'LEFT_JOIN'	=> $sql_array['LEFT_JOIN'],
		'WHERE'		=> $sql_where,
		'ORDER_BY'	=> $sql_array['ORDER_BY'],
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

		if (!gallery_acl_check('a_list', $album_id, $row['album_user_id']))
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
						'name'		=> $subalbum_row['name'],
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
			$lastimage_name = $row['album_last_image_name'];
			$lastimage_time = $user->format_date($row['album_last_image_time']);
			$lastimage_image_id = $row['album_last_image_id'];
			$lastimage_album_id = $row['album_id_last_image'];
			$lastimage_uc_thumbnail = generate_image_link('fake_thumbnail', $gallery_config['link_thumbnail'], $lastimage_image_id, $lastimage_name, $lastimage_album_id);
			$lastimage_uc_name = generate_image_link('image_name', $gallery_config['link_image_name'], $lastimage_image_id, $lastimage_name, $lastimage_album_id);
			$lastimage_uc_icon = generate_image_link('lastimage_icon', $gallery_config['link_image_icon'], $lastimage_image_id, $lastimage_name, $lastimage_album_id);
		}
		else
		{
			$lastimage_time = $lastimage_image_id = $lastimage_album_id = 0;
			$lastimage_name = $lastimage_uc_thumbnail = $lastimage_uc_name = $lastimage_uc_icon = '';
		}

		// Output moderator listing ... if applicable
		$l_moderator = $moderators_list = '';
		if ($display_moderators && !empty($album_moderators[$album_id]))
		{
			$l_moderator = (sizeof($album_moderators[$album_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
			$moderators_list = implode(', ', $album_moderators[$album_id]);
		}

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
			//'S_LOCKED_ALBUM'	=> ($row['album_status'] == ITEM_LOCKED) ? true : false,
			'S_LIST_SUBALBUMS'	=> ($row['display_subalbum_list']) ? true : false,
			'S_SUBALBUMS'		=> (sizeof($subalbums_list)) ? true : false,

			'ALBUM_ID'				=> $row['album_id'],
			'ALBUM_NAME'			=> $row['album_name'],
			'ALBUM_DESC'			=> generate_text_for_display($row['album_desc'], $row['album_desc_uid'], $row['album_desc_bitfield'], $row['album_desc_options']),
			'IMAGES'				=> $row['album_images'],
			'UNAPPROVED_IMAGES'		=> (gallery_acl_check('m_status', $album_id, $row['album_user_id'])) ? ($row['album_images_real'] - $row['album_images']) : 0,
			'ALBUM_FOLDER_IMG'		=> $user->img($folder_image, $folder_alt),
			'ALBUM_FOLDER_IMG_SRC'	=> $user->img($folder_image, $folder_alt, false, '', 'src'),
			'ALBUM_FOLDER_IMG_ALT'	=> isset($user->lang[$folder_alt]) ? $user->lang[$folder_alt] : '',
			'ALBUM_IMAGE'			=> $row['album_image'],
			'LAST_IMAGE_TIME'		=> $lastimage_time,
			'LAST_USER_FULL'		=> get_username_string('full', $row['album_last_user_id'], $row['album_last_username'], $row['album_last_user_colour']),
			'UC_FAKE_THUMBNAIL'		=> ($gallery_config['disp_fake_thumb']) ? $lastimage_uc_thumbnail : '',
			'UC_IMAGE_NAME'			=> $lastimage_uc_name,
			'UC_LASTIMAGE_ICON'		=> $lastimage_uc_icon,
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
		'FAKE_THUMB_SIZE'	=> $gallery_config['fake_thumb_size'],
	));

	if ($return_moderators)
	{
		return array($active_album_ary, $album_moderators);
	}

	return array($active_album_ary, array());
}

/**
* Create album navigation links for given album, create parent
* list if currently null, assign basic album info to template
*
* borrowed from phpBB3
* @author: phpBB Group
* @function: generate_forum_nav
*/
function generate_album_nav(&$album_data)
{
	global $db, $user, $template;
	global $phpEx, $phpbb_root_path, $gallery_root_path;

	// Get album parents
	$album_parents = get_album_parents($album_data);

	// Display username for personal albums
	if ($album_data['album_user_id'] > 0)
	{
		$sql = 'SELECT user_id, username, user_colour
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $album_data['album_user_id'];
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $user->lang['PERSONAL_ALBUMS'],
				'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx", 'mode=personal'),
			));
		}
		$db->sql_freeresult($result);
	}

	// Build navigation links
	if (!empty($album_parents))
	{
		foreach ($album_parents as $parent_album_id => $parent_data)
		{
			list($parent_name, $parent_type) = array_values($parent_data);

			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $parent_name,
				'FORUM_ID'		=> $parent_album_id,
				'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $parent_album_id),
			));
		}
	}

	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $album_data['album_name'],
		'FORUM_ID'		=> $album_data['album_id'],
		'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $album_data['album_id']),
	));

	$template->assign_vars(array(
		'ALBUM_ID' 		=> $album_data['album_id'],
		'ALBUM_NAME'	=> $album_data['album_name'],
		'ALBUM_DESC'	=> generate_text_for_display($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options']),
	));

	return;
}

/**
* Returns album parents as an array. Get them from album_data if available, or update the database otherwise
*
* borrowed from phpBB3
* @author: phpBB Group
* @function: get_forum_parents
*/
function get_album_parents(&$album_data)
{
	global $db;

	$album_parents = array();
	if ($album_data['parent_id'] > 0)
	{
		if ($album_data['album_parents'] == '')
		{
			$sql = 'SELECT album_id, album_name, album_type
				FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE left_id < ' . $album_data['left_id'] . '
					AND right_id > ' . $album_data['right_id'] . '
					AND album_user_id = ' . $album_data['album_user_id'] . '
				ORDER BY left_id ASC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$album_parents[$row['album_id']] = array($row['album_name'], (int) $row['album_type']);
			}
			$db->sql_freeresult($result);

			$album_data['album_parents'] = serialize($album_parents);

			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
				SET album_parents = '" . $db->sql_escape($album_data['album_parents']) . "'
				WHERE parent_id = " . $album_data['parent_id'];
			$db->sql_query($sql);
		}
		else
		{
			$album_parents = unserialize($album_data['album_parents']);
		}
	}

	return $album_parents;
}

/**
* Obtain list of moderators of each album
*
* borrowed from phpBB3
* @author: phpBB Group
* @function: get_forum_moderators
*/
function get_album_moderators(&$album_moderators, $album_id = false)
{
	global $config, $template, $db, $phpbb_root_path, $phpEx, $user;

	// Have we disabled the display of moderators? If so, then return
	// from whence we came ...
	if (!$config['load_moderators'])
	{
		return;
	}

	$album_sql = '';

	if ($album_id !== false)
	{
		if (!is_array($album_id))
		{
			$album_id = array($album_id);
		}

		// If we don't have a forum then we can't have a moderator
		if (!sizeof($album_id))
		{
			return;
		}

		$album_sql = 'AND m.' . $db->sql_in_set('album_id', $album_id);
	}

	$sql_array = array(
		'SELECT'	=> 'm.*, u.user_colour, g.group_colour, g.group_type',

		'FROM'		=> array(
			GALLERY_MODSCACHE_TABLE	=> 'm',
		),

		'LEFT_JOIN'	=> array(
			array(
				'FROM'	=> array(USERS_TABLE => 'u'),
				'ON'	=> 'm.user_id = u.user_id',
			),
			array(
				'FROM'	=> array(GROUPS_TABLE => 'g'),
				'ON'	=> 'm.group_id = g.group_id',
			),
		),

		'WHERE'		=> "m.display_on_index = 1 $album_sql",
	);

	$sql = $db->sql_build_query('SELECT', $sql_array);
	$result = $db->sql_query($sql, 3600);

	while ($row = $db->sql_fetchrow($result))
	{
		if (!empty($row['user_id']))
		{
			$album_moderators[$row['album_id']][] = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
		}
		else
		{
			$album_moderators[$row['album_id']][] = '<a' . (($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . ';"' : '') . ' href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']) . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</a>';
		}
	}
	$db->sql_freeresult($result);

	return;
}
?>