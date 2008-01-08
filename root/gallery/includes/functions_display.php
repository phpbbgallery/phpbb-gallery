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
if (1 > 2)
{
		if (($album[$i]['album_approval'] == ALBUM_ADMIN) || ($album[$i]['album_approval'] == ALBUM_MOD))
		{
			$pic_approval_sql = 'AND p.image_approval = 1';
		}
		else
		{
			$pic_approval_sql = '';
		}


		// ----------------------------
		// OK, we may do a query now...
		// ----------------------------

		$sql = 'SELECT p.image_id, p.image_name, p.image_user_id, p.image_username, p.image_time, p.image_album_id, u.user_id, u.username, u.user_colour
				FROM ' . GALLERY_IMAGES_TABLE . ' AS p
				LEFT JOIN ' . USERS_TABLE . ' AS u
					ON p.image_user_id = u.user_id
				WHERE p.image_id = ' . $album[$i]['last_image'] . ' ' . $pic_approval_sql . ' 
				ORDER BY p.image_time DESC';
		$result = $db->sql_query($sql);
}
$sql = 'SELECT a.*, COUNT(pc.image_id) AS count, MAX(pc.image_id) as last_image
	FROM ' . GALLERY_ALBUMS_TABLE . ' AS a
	LEFT JOIN ' . GALLERY_ALBUMS_TABLE . ' AS sa
		ON ( sa.left_id < a.right_id
			AND sa.left_id > a.left_id )
	LEFT JOIN ' . GALLERY_IMAGES_TABLE . ' AS pc
		ON ( pc.image_album_id = sa.album_id
			OR pc.image_album_id = a.album_id )
	WHERE a.album_id <> 0
		AND a.parent_id = ' . $album_id . '
	GROUP BY a.album_id
	ORDER BY a.left_id ASC';
$result = $db->sql_query($sql);

$album = array();

while( $row = $db->sql_fetchrow($result) )
{
	$album_user_access = album_user_access($row['album_id'], $row, 1, 0, 0, 0, 0, 0);
	if ($album_user_access['view'] == 1)
	{
		$album[] = $row;
	}
}

for ($i = 0; $i < count($album); $i++)
{
	/**
	* Build moderators list
	*/
	$l_moderators = '';
	$moderators_list = '';
	$grouprows= array();

	if ($album[$i]['album_moderator_groups'] != 0)
	{
		// We have usergroup_ID, now we need usergroup name
		$sql = 'SELECT group_id, group_name, group_type
				FROM ' . GROUPS_TABLE . '
				WHERE group_type <> ' . GROUP_HIDDEN . '
					AND group_id IN (' . $album[$i]['album_moderator_groups'] . ')
				ORDER BY group_name ASC';
		$result = $db->sql_query($sql);

		while( $row = $db->sql_fetchrow($result) )
		{
			$grouprows[] = $row;
		}
		if (count($grouprows) > 1)
		{
			$l_moderators = $user->lang['MODERATORS'];

			for ($j = 0; $j < count($grouprows); $j++)
			{
				$group_name = ($grouprows[$j]['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $grouprows[$j]['group_name']] : $grouprows[$j]['group_name'];
				$group_link = '<a href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx?mode=group&g=" . $grouprows[$j]['group_id']) . '">' . $group_name . '</a>';

				$moderators_list .= ($moderators_list == '') ? $group_link : ', ' . $group_link;
			}
		}
		else if (count($grouprows) > 0)
		{
			$l_moderators = $user->lang['MODERATOR'];
			for ($j = 0; $j < count($grouprows); $j++)
			{
				$group_name = ($grouprows[$j]['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $grouprows[$j]['group_name']] : $grouprows[$j]['group_name'];
				$moderators_list = '<a href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx?mode=group&g=" . $grouprows[$j]['group_id']) . '">' . $group_name . '</a>';
			}
		}
	}


	// ------------------------------------------
	// Get Last Pic of this Category
	// ------------------------------------------

	if ($album[$i]['count'] == 0)
	{
		//
		// Oh, this category is empty
		//
		$last_pic_info = $user->lang['NO_IMAGES'];
		$u_last_pic = '';
		$last_pic_title = '';
	}
	else
	{
		// ----------------------------
		// Check Pic Approval
		// ----------------------------

		if (($album[$i]['album_approval'] == ALBUM_ADMIN) || ($album[$i]['album_approval'] == ALBUM_MOD))
		{
			$pic_approval_sql = 'AND p.image_approval = 1';
		}
		else
		{
			$pic_approval_sql = '';
		}


		// ----------------------------
		// OK, we may do a query now...
		// ----------------------------

		$sql = 'SELECT p.image_id, p.image_name, p.image_user_id, p.image_username, p.image_time, p.image_album_id, u.user_id, u.username, u.user_colour
				FROM ' . GALLERY_IMAGES_TABLE . ' AS p
				LEFT JOIN ' . USERS_TABLE . ' AS u
					ON p.image_user_id = u.user_id
				WHERE p.image_id = ' . $album[$i]['last_image'] . ' ' . $pic_approval_sql . ' 
				ORDER BY p.image_time DESC';
		$result = $db->sql_query($sql);
		$lastrow = $db->sql_fetchrow($result);

		$last_pic_info = '';
		$last_pic_info .= '<dfn>' . $user->lang['LAST_IMAGE'] . '</dfn> ';
		if( !isset($album_config['last_pic_title_length']) )
		{
			$album_config['last_pic_title_length'] = 25;
		}
		if (utf8_strlen($lastrow['image_name']) > $album_config['last_pic_title_length'])
		{
			$lastrow['image_name'] = utf8_substr($lastrow['image_name'], 0, $album_config['last_pic_title_length']) . '...';
		}
		$last_pic_info .= '<a href="' . append_sid("{$phpbb_root_path}gallery/image_page.$phpEx?image_id=" . $lastrow['image_id']) . '" style="font-weight: bold;">';
		$last_pic_info .= $lastrow['image_name'] . '</a><br />' . $user->lang['POST_BY_AUTHOR'] . ' ';
		$last_pic_info .= get_username_string('full', $lastrow['user_id'], ($lastrow['user_id'] <> ANONYMOUS) ? $lastrow['username'] : $user->lang['GUEST'], $lastrow['user_colour']);
//		$last_pic_info .= '<a href="' . append_sid("{$phpbb_root_path}/memberlist.$phpEx?mode=viewprofile&amp;u=". $lastrow['user_id']) .'" style="color: #' . $user->data['user_colour'] . ';" class="username-coloured">'. $lastrow['username'] .'</a> ';
		$last_pic_info .= '<a href="' . append_sid("{$phpbb_root_path}gallery/image_page.$phpEx?image_id=" . $lastrow['image_id']) . '"><img src="' . $phpbb_root_path . 'styles/prosilver/imageset/icon_topic_latest.gif" width="11" height="9" alt="' . $user->lang['VIEW_THE_LATEST_IMAGE'] . '" title="' . $user->lang['VIEW_THE_LATEST_IMAGE'] . '" /></a><br />';
		/*uh, hardcoded image*/
		$last_pic_info .= $user->lang['POSTED_ON_DATE'] . ' ' . $user->format_date($lastrow['image_time']);
	}
	if ($album[$i]['left_id'] + 1 != $album[$i]['right_id'])
	{
		$folder_image = 'forum_read_subforum';
		$folder_alt = 'no';
		$l_subalbums = $user->lang['SUBALBUM'];
		if ($album[$i]['left_id'] + 3 != $album[$i]['right_id'])
		{
			$l_subalbums = $user->lang['SUBALBUMS'];
		}
	}
	else
	{
		$folder_image = 'forum_read';
		$l_subalbums = '';
		$folder_alt = 'no';
	}
	//$folder_image = ($album[$i]['left_id'] + 1 != $album[$i]['right_id']) ? '<img src="images/icon_subfolder.gif" alt="' . $user->lang['SUBFORUM'] . '" />' : '<img src="images/icon_folder.gif" alt="' . $user->lang['FOLDER'] . '" />';
	// END of Last Pic

	// ------------------------------------------
	// Parse to template the info of the current Category
	// ------------------------------------------

	$template->assign_block_vars('albumrow', array(
		'U_VIEW_ALBUM' 			=> append_sid($phpbb_root_path . "gallery/album.$phpEx?id=" . $album[$i]['album_id']),
		'ALBUM_NAME' 			=> $album[$i]['album_name'],
		'ALBUM_FOLDER_IMG_SRC'	=> $user->img($folder_image, $folder_alt, false, '', 'src'),
		'SUBALBUMS'				=> get_album_children($album[$i]['album_id']),
		'ALBUM_DESC' 			=> generate_text_for_display($album[$i]['album_desc'], $album[$i]['album_desc_uid'], $album[$i]['album_desc_bitfield'], $album[$i]['album_desc_options']),
		'L_MODERATORS' 			=> $l_moderators,
		'L_SUBALBUMS' 			=> $l_subalbums,
		'MODERATORS' 			=> $moderators_list,
		'IMAGES' 					=> $album[$i]['count'],
		'LAST_IMAGE_INFO' 		=> $last_pic_info)
	);
}

?>