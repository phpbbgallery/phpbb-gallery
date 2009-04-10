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
* Display recent images & comments and random images
*/
function recent_gallery_images($ints, $display, $mode, $collapse_comments = false, $user_id = 0)
{
	global $auth, $cache, $config, $db, $gallery_config, $template, $user;
	global $gallery_root_path, $phpbb_root_path, $phpEx;

	$gallery_root_path = (!$gallery_root_path) ? GALLERY_ROOT_PATH : $gallery_root_path;
	$user->add_lang('mods/gallery');

	if (!function_exists('generate_text_for_display'))
	{
		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
	}
	if (!function_exists('load_gallery_config'))
	{
		$recent_image_addon = true;
		include($phpbb_root_path . $gallery_root_path . 'includes/common.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/permissions.' . $phpEx);
	}
	if (!function_exists('assign_image_block'))
	{
		include($phpbb_root_path . $gallery_root_path . 'includes/functions_display.' . $phpEx);
	}
	$album_access_array = get_album_access_array();

	$limit_sql = $ints['rows'] * $ints['columns'];
	$albums = $cache->obtain_album_list();

	$moderate_albums = gallery_acl_album_ids('m_status', 'array', true, $gallery_config['rrc_gindex_pgalleries']);
	$view_albums = array_diff(gallery_acl_album_ids('i_view', 'array', true, $gallery_config['rrc_gindex_pgalleries']), $moderate_albums);
	$comment_albums = gallery_acl_album_ids('c_read', 'array', true, $gallery_config['rrc_gindex_pgalleries']);

	$sql_permission_where = '(';
	$sql_permission_where .= ((sizeof($view_albums)) ? '(' . $db->sql_in_set('image_album_id', $view_albums) . ' AND image_status <> ' . IMAGE_UNAPPROVED . (($user_id) ? ' AND image_contest = ' . IMAGE_NO_CONTEST : '') . ')' : '');
	$sql_permission_where .= (((sizeof($moderate_albums)) ? (sizeof($view_albums)) ? ' OR ' : '') . '(' . $db->sql_in_set('image_album_id', $moderate_albums, false, true) . ')' : '');
	$sql_permission_where .= ($user_id) ? ') AND image_user_id = ' . $user_id : ')';

	if (sizeof($view_albums) || sizeof($moderate_albums))
	{
		$images = $recent_images = $random_images = $contest_images = array();
		// First step: grab all the IDs we are going to display ...
		if ($mode & RRC_MODE_RECENT)
		{
			$sql = 'SELECT image_id
				FROM ' . GALLERY_IMAGES_TABLE . "
				WHERE $sql_permission_where
				ORDER BY image_time DESC";
			$result = $db->sql_query_limit($sql, $limit_sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$images[] = $row['image_id'];
				$recent_images[] = $row['image_id'];
			}
			$db->sql_freeresult($result);
		}
		if ($mode & RRC_MODE_RANDOM)
		{
			switch ($db->sql_layer)
			{
				case 'postgres':
					$random_sql = 'RANDOM()';
				break;
				case 'mssql':
				case 'mssql_odbc':
					$random_sql = 'NEWID()';
				break;
				default:
					$random_sql = 'RAND()';
				break;
			}

			$sql = 'SELECT image_id
				FROM ' . GALLERY_IMAGES_TABLE . "
				WHERE $sql_permission_where
				ORDER BY " . $random_sql;
			$result = $db->sql_query_limit($sql, $limit_sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$images[] = $row['image_id'];
				$random_images[] = $row['image_id'];
			}
			$db->sql_freeresult($result);
		}
		if ($ints['contests'])
		{
			$sql = 'SELECT *
				FROM ' . GALLERY_CONTESTS_TABLE . '
				WHERE ' . $db->sql_in_set('contest_album_id', array_unique(array_merge($view_albums, $moderate_albums))) . '
					AND contest_marked = ' . IMAGE_NO_CONTEST . '
				ORDER BY contest_start + contest_end DESC';
			$result = $db->sql_query_limit($sql, $ints['contests']);

			while ($row = $db->sql_fetchrow($result))
			{
				$images[] = $row['contest_first'];
				$images[] = $row['contest_second'];
				$images[] = $row['contest_third'];
				$contest_images[$row['contest_id']] = array($row['contest_first'], $row['contest_second'], $row['contest_third']);
			}
			$db->sql_freeresult($result);
		}

		// Second step: grab the data ...
		$images = array_unique($images);
		if (sizeof($images))
		{
			$sql = 'SELECT i.*, a.album_name, a.album_status, a.album_id, a.album_user_id
				FROM ' . GALLERY_IMAGES_TABLE . ' i
				LEFT JOIN ' . GALLERY_ALBUMS_TABLE . ' a
					ON i.image_album_id = a.album_id
				WHERE ' . $db->sql_in_set('i.image_id', $images, false, true) . '
				ORDER BY i.image_time DESC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$images_data[$row['image_id']] = $row;
			}
			$db->sql_freeresult($result);
		}

		// Third step: put the images
		if (sizeof($recent_images))
		{
			$num = 0;
			foreach ($recent_images as $recent_image)
			{
				if (($num % $ints['columns']) == 0)
				{
					$template->assign_block_vars('recent', array());
				}
				assign_image_block('recent.image', $images_data[$recent_image], $images_data[$recent_image]['album_status'], $display);
				$num++;
			}
			while (($num % $ints['columns']) > 0)
			{
				$template->assign_block_vars('recent.no_image', array());
				$num++;
			}
		}
		if (sizeof($random_images))
		{
			$num = 0;
			foreach ($random_images as $random_image)
			{
				if (($num % $ints['columns']) == 0)
				{
					$template->assign_block_vars('random', array());
				}
				assign_image_block('random.image', $images_data[$random_image], $images_data[$random_image]['album_status'], $display);
				$num++;
			}
			while (($num % $ints['columns']) > 0)
			{
				$template->assign_block_vars('random.no_image', array());
				$num++;
			}
		}
		if (sizeof($contest_images))
		{
			foreach ($contest_images as $contest => $this_contests_images)
			{
				$num = 0;
				foreach ($this_contests_images as $contest_image)
				{
					if (($num % 3) == 0)
					{
						$template->assign_block_vars('contest', array());
					}
					assign_image_block('contest.image', $images_data[$contest_image], $images_data[$contest_image]['album_status'], $display);
					$num++;
				}
				while (($num % 3) > 0)
				{
					$template->assign_block_vars('contest.no_image', array());
					$num++;
				}
			}
		}
	}

	if ($gallery_config['allow_comments'] && ($mode & RRC_MODE_COMMENT) && sizeof($comment_albums))
	{
		$user->add_lang('viewtopic');

		$sql = 'SELECT c.*, i.*
			FROM ' . GALLERY_COMMENTS_TABLE . ' c
			LEFT JOIN ' . GALLERY_IMAGES_TABLE . " i
				ON c.comment_image_id = i.image_id
			WHERE $sql_permission_where
				AND " . $db->sql_in_set('i.image_album_id', $comment_albums, false, true) . '
			ORDER BY c.comment_id DESC';
		$result = $db->sql_query_limit($sql, $ints['comments']);

		while ($commentrow = $db->sql_fetchrow($result))
		{
			$image_id = $commentrow['image_id'];
			$album_id = $commentrow['image_album_id'];

			$template->assign_block_vars('commentrow', array(
				'U_COMMENT'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id") . '#' . $commentrow['comment_id'],
				'COMMENT_ID'	=> $commentrow['comment_id'],
				'TIME'			=> $user->format_date($commentrow['comment_time']),
				'TEXT'			=> generate_text_for_display($commentrow['comment'], $commentrow['comment_uid'], $commentrow['comment_bitfield'], 7),
				'U_DELETE'		=> (gallery_acl_check('m_comments', $album_id) || (gallery_acl_check('c_delete', $album_id) && ($commentrow['comment_user_id'] == $user->data['user_id']) && $user->data['is_registered'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=delete&amp;comment_id=" . $commentrow['comment_id']) : '',
				'U_EDIT'		=> (gallery_acl_check('m_comments', $album_id) || (gallery_acl_check('c_edit', $album_id) && ($commentrow['comment_user_id'] == $user->data['user_id']) && $user->data['is_registered'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=edit&amp;comment_id=" . $commentrow['comment_id']) : '',
				'U_INFO'		=> ($auth->acl_get('a_')) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'mode=whois&amp;ip=' . $commentrow['comment_user_ip']) : '',

				'UC_THUMBNAIL'			=> generate_image_link('thumbnail', $gallery_config['link_thumbnail'], $commentrow['image_id'], $commentrow['image_name'], $commentrow['image_album_id']),
				'UC_IMAGE_NAME'			=> generate_image_link('image_name', $gallery_config['link_image_name'], $commentrow['image_id'], $commentrow['image_name'], $commentrow['image_album_id']),
				'IMAGE_AUTHOR'			=> get_username_string('full', $commentrow['image_user_id'], ($commentrow['image_user_id'] <> ANONYMOUS) ? $commentrow['image_username'] : ($user->lang['GUEST'] . ': ' . $commentrow['image_username']), $commentrow['image_user_colour']),
				'IMAGE_TIME'			=> $user->format_date($commentrow['image_time']),

				'POST_AUTHOR_FULL'		=> get_username_string('full', $commentrow['comment_user_id'], ($commentrow['comment_user_id'] <> ANONYMOUS) ? $commentrow['comment_username'] : ($user->lang['GUEST'] . ': ' . $commentrow['comment_username']), $commentrow['comment_user_colour']),
				'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $commentrow['comment_user_id'], ($commentrow['comment_user_id'] <> ANONYMOUS) ? $commentrow['comment_username'] : ($user->lang['GUEST'] . ': ' . $commentrow['comment_username']), $commentrow['comment_user_colour']),
				'POST_AUTHOR'			=> get_username_string('username', $commentrow['comment_user_id'], ($commentrow['comment_user_id'] <> ANONYMOUS) ? $commentrow['comment_username'] : ($user->lang['GUEST'] . ': ' . $commentrow['comment_username']), $commentrow['comment_user_colour']),
				'U_POST_AUTHOR'			=> get_username_string('profile', $commentrow['comment_user_id'], ($commentrow['comment_user_id'] <> ANONYMOUS) ? $commentrow['comment_username'] : ($user->lang['GUEST'] . ': ' . $commentrow['comment_username']), $commentrow['comment_user_colour']),
			));
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'S_COMMENTS'	=> true,

			'DELETE_IMG'		=> $user->img('icon_post_delete', 'DELETE_COMMENT'),
			'EDIT_IMG'			=> $user->img('icon_post_edit', 'EDIT_COMMENT'),
			'INFO_IMG'			=> $user->img('icon_post_info', 'VIEW_INFO'),
			'MINI_POST_IMG'		=> $user->img('icon_post_target_unread', 'COMMENT'),
			'PROFILE_IMG'		=> $user->img('icon_user_profile', 'READ_PROFILE'),
			'COLLAPSE_COMMENTS'	=> $collapse_comments,
		));
	}

	$template->assign_vars(array(
		'S_THUMBNAIL_SIZE'		=> $gallery_config['thumbnail_size'] + 20 + (($gallery_config['thumbnail_info_line']) ? 16 : 0),
		'S_COL_WIDTH'			=> (100 / $ints['columns']) . '%',
		'S_COLS'				=> $ints['columns'],
		'S_RANDOM'				=> ($mode & RRC_MODE_RANDOM) ? true : false,
		'S_RECENT'				=> ($mode & RRC_MODE_RECENT) ? true : false,
	));
}

?>