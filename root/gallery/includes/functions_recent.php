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
function recent_gallery_images(&$ints, $display, $modes, $collapse_comments = false, $user_id = 0)
{
	global $db, $phpEx, $user, $cache, $auth;
	global $phpbb_root_path, $gallery_config, $config, $template;

	$gallery_root_path = GALLERY_ROOT_PATH;
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

	$albums = $cache->obtain_album_list();
	$view_albums = gallery_acl_album_ids('i_view', 'array');
	$moderate_albums = gallery_acl_album_ids('m_status', 'array');
	$comment_albums = gallery_acl_album_ids('c_read', 'array');
	$limit_sql = $ints['rows'] * $ints['columns'];

	switch ($modes)
	{
		case 'recent':
			$recent = true;
			$random = false;
			$comment = false;
		break;

		case 'random':
			$recent = false;
			$random = true;
			$comment = false;
		break;

		case 'comment':
			$recent = false;
			$random = false;
			$comment = true;
		break;

		case '!recent':
			$recent = false;
			$random = true;
			$comment = true;
		break;

		case '!random':
			$recent = true;
			$random = false;
			$comment = true;
		break;

		case '!comment':
			$recent = true;
			$random = true;
			$comment = false;
		break;

		case 'all':
		case 'both':
		default:
			$recent = true;
			$random = true;
			$comment = true;
		break;
	}

	if (($view_albums != array()) || ($moderate_albums != array()))
	{
		if ($recent)
		{
			$recent_images = array();
			$sql = 'SELECT i.*, a.album_name, a.album_status, a.album_id, a.album_user_id
				FROM ' . GALLERY_IMAGES_TABLE . ' i
				LEFT JOIN ' . GALLERY_ALBUMS_TABLE . ' a
					ON i.image_album_id = a.album_id
				WHERE ((' . $db->sql_in_set('i.image_album_id', $view_albums) . '
						AND i.image_status = ' . IMAGE_APPROVED . (($user_id) ? ' AND i.image_contest = ' . IMAGE_NO_CONTEST : '') . ')' . 
					(($moderate_albums) ? 'OR (' . $db->sql_in_set('i.image_album_id', $moderate_albums) . ')' : '') . '
					' . (($user_id) ? ') AND i.image_user_id = ' . $user_id : ')') . '
					AND a.display_in_rrc = 1
				GROUP BY i.image_id
				ORDER BY i.image_time DESC';
			$result = $db->sql_query_limit($sql, $limit_sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$recent_images[] = $row;
			}
			$db->sql_freeresult($result);

			for ($i = 0; $i < count($recent_images); $i += $ints['columns'])
			{
				$template->assign_block_vars('recent', array());

				for ($j = $i; $j < ($i + $ints['columns']); $j++)
				{
					if ($j >= count($recent_images))
					{
						$template->assign_block_vars('recent.no_image', array());
						continue;
					}

					// Assign the image to the template-block
					assign_image_block('recent.image', $recent_images[$j], $recent_images[$j]['album_status'], $display);
				}
			}
		}

		if ($random)
		{
			switch ($db->sql_layer)
			{
				case 'postgres':
					$random = 'RANDOM()';
				break;

				case 'mssql':
				case 'mssql_odbc':
					$random = 'NEWID()';
				break;

				default:
					$random = 'RAND()';
				break;
			}

			$random_images = array();
			$sql = 'SELECT i.*, a.album_name, a.album_status, a.album_id, a.album_user_id
				FROM ' . GALLERY_IMAGES_TABLE . ' i
				LEFT JOIN ' . GALLERY_ALBUMS_TABLE . ' a
					ON i.image_album_id = a.album_id
				WHERE ((' . $db->sql_in_set('i.image_album_id', $view_albums) . '
						AND i.image_status = ' . IMAGE_APPROVED . (($user_id) ? ' AND i.image_contest = ' . IMAGE_NO_CONTEST : '') . ')' . 
					(($moderate_albums) ? 'OR (' . $db->sql_in_set('i.image_album_id', $moderate_albums) . ')' : '') . '
					' . (($user_id) ? ') AND i.image_user_id = ' . $user_id : ')') . '
					AND a.display_in_rrc = 1
				GROUP BY i.image_id
				ORDER BY ' . $random;
			$result = $db->sql_query_limit($sql, $limit_sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$random_images[] = $row;
			}
			$db->sql_freeresult($result);

			for ($i = 0; $i < count($random_images); $i += $ints['columns'])
			{
				$template->assign_block_vars('random', array());

				for ($j = $i; $j < ($i + $ints['columns']); $j++)
				{
					if ($j >= count($random_images))
					{
						$template->assign_block_vars('random.no_image', array());
						continue;
					}

					// Assign the image to the template-block
					assign_image_block('random.image', $random_images[$j], $random_images[$j]['album_status'], $display);
				}
			}
		}

		if ($ints['contests'])
		{
			$contest_columns = 3;
			$contest_images = array();
			$sql = 'SELECT i.*, a.album_name, a.album_status, a.album_id, a.album_user_id
				FROM ' . GALLERY_IMAGES_TABLE . ' i
				LEFT JOIN ' . GALLERY_ALBUMS_TABLE . ' a
					ON i.image_album_id = a.album_id
				WHERE ((' . $db->sql_in_set('i.image_album_id', $view_albums) . ' AND i.image_status = ' . IMAGE_APPROVED . ')' . 
					(($moderate_albums) ? 'OR (' . $db->sql_in_set('i.image_album_id', $moderate_albums) . ')' : '') . ')
					AND i.image_contest_rank > 0
				GROUP BY i.image_id
				ORDER BY image_contest_end DESC, image_contest_rank ASC';
			$result = $db->sql_query_limit($sql, $ints['contests'] * $contest_columns);

			while ($row = $db->sql_fetchrow($result))
			{
				$contest_images[] = $row;
			}
			$db->sql_freeresult($result);

			for ($i = 0; $i < count($contest_images); $i += $contest_columns)
			{
				$template->assign_block_vars('contest', array());

				for ($j = $i; $j < ($i + $contest_columns); $j++)
				{
					if ($j >= count($contest_images))
					{
						$template->assign_block_vars('contest.no_image', array());
						continue;
					}

					// Assign the image to the template-block
					assign_image_block('contest.image', $contest_images[$j], $contest_images[$j]['album_status'], $display);
				}
			}
		}
	}

	if ($gallery_config['allow_comments'] && $comment && ($comment_albums != array()))
	{
		$user->add_lang('viewtopic');
		$template->assign_vars(array(
			'S_COMMENTS'	=> true,
		));

		$sql = 'SELECT c.*, i.*
			FROM ' . GALLERY_COMMENTS_TABLE . ' c
			LEFT JOIN ' . GALLERY_IMAGES_TABLE . ' i
				ON c.comment_image_id = i.image_id
			WHERE ((' . $db->sql_in_set('i.image_album_id', $view_albums) . ' AND i.image_status = ' . IMAGE_APPROVED . ')' . 
					(($moderate_albums) ? 'OR (' . $db->sql_in_set('i.image_album_id', $moderate_albums) . ')' : '') . ')
				AND (' . $db->sql_in_set('i.image_album_id', $comment_albums) . '
				' . (($user_id) ? ') AND i.image_user_id = ' . $user_id : ')') .'
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
			'DELETE_IMG'		=> $user->img('icon_post_delete', 'DELETE_COMMENT'),
			'EDIT_IMG'			=> $user->img('icon_post_edit', 'EDIT_COMMENT'),
			'INFO_IMG'			=> $user->img('icon_post_info', 'VIEW_INFO'),
			'MINI_POST_IMG'		=> $user->img('icon_post_target_unread', 'COMMENT'),
			'PROFILE_IMG'		=> $user->img('icon_user_profile', 'READ_PROFILE'),
			'COLLAPSE_COMMENTS'	=> $collapse_comments,
		));
	}

	$template->assign_vars(array(
		'S_THUMBNAIL_SIZE'			=> $gallery_config['thumbnail_size'] + 20 + (($gallery_config['thumbnail_info_line']) ? 16 : 0),
		'S_COL_WIDTH'			=> (100 / $gallery_config['cols_per_page']) . '%',
		'S_COLS'				=> $ints['columns'],
		'S_RANDOM'				=> $random,
		'S_RECENT'				=> $recent,
	));
}

?>