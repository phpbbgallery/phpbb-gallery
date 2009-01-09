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
function recent_gallery_images($rows, $columns, &$display, $modes)
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
	$album_access_array = get_album_access_array();

	$albums = $cache->obtain_album_list();
	$view_albums = gallery_acl_album_ids('i_view', 'array');
	$moderate_albums = gallery_acl_album_ids('m_status', 'array');
	$comment_albums = gallery_acl_album_ids('c_read', 'array');
	$limit_sql = $rows * $columns;

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
		$limit_sql = $rows * $columns;

		if ($recent)
		{
			$recent_images = array();
			$sql = "SELECT i.*, a.album_name, a.album_id, a.album_user_id
				FROM " . GALLERY_IMAGES_TABLE . " i
				LEFT JOIN " . GALLERY_ALBUMS_TABLE . " a
					ON i.image_album_id = a.album_id
				WHERE (" . $db->sql_in_set('i.image_album_id', $view_albums) . '
						AND i.image_status = 1)' . 
					(($moderate_albums) ? 'OR (' . $db->sql_in_set('i.image_album_id', $moderate_albums) . ')' : '') . '
				GROUP BY i.image_id
				ORDER BY i.image_time DESC';
			$result = $db->sql_query_limit($sql, $limit_sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$recent_images[] = $row;
			}
			$db->sql_freeresult($result);

			for ($i = 0; $i < count($recent_images); $i += $columns)
			{
				$template->assign_block_vars('recent', array());

				for ($j = $i; $j < ($i + $columns); $j++)
				{
					if ($j >= count($recent_images))
					{
						$template->assign_block_vars('recent.no_image', array());
						continue;
					}
					$album_id = $recent_images[$j]['image_album_id'];

					if ($display['ratings'] && !$recent_images[$j]['image_rates'])
					{
						$recent_images[$j]['rating'] = $user->lang['NOT_RATED'];
					}
					else if ($display['ratings'])
					{
						$recent_images[$j]['rating'] = sprintf((($recent_images[$j]['image_rates'] == 1) ? $user->lang['RATE_STRING'] : $user->lang['RATES_STRING']), $recent_images[$j]['image_rate_avg'] / 100, $recent_images[$j]['image_rates']);
					}
					$perm_user_id = ($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from'];
					$allow_edit = ((gallery_acl_check('i_edit', $album_id, $recent_images[$j]['album_user_id']) && ($recent_images[$j]['image_user_id'] == $perm_user_id)) || gallery_acl_check('m_edit', $album_id, $recent_images[$j]['album_user_id'])) ? true : false;
					$allow_delete = ((gallery_acl_check('i_delete', $album_id, $recent_images[$j]['album_user_id']) && ($recent_images[$j]['image_user_id'] == $perm_user_id)) || gallery_acl_check('m_delete', $album_id, $recent_images[$j]['album_user_id'])) ? true : false;

					$template->assign_block_vars('recent.image', array(
						'IMAGE_ID'		=> $recent_images[$j]['image_id'],
						'UC_IMAGE_NAME'	=> ($display['name']) ? (generate_image_link('image_name', $gallery_config['link_image_name'], $recent_images[$j]['image_id'], $recent_images[$j]['image_name'], $recent_images[$j]['image_album_id'])) : '',
						'UC_THUMBNAIL'	=> generate_image_link('thumbnail', $gallery_config['link_thumbnail'], $recent_images[$j]['image_id'], $recent_images[$j]['image_name'], $recent_images[$j]['image_album_id']),
						'U_ALBUM'		=> ($display['album']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $recent_images[$j]['image_album_id']) : '',
						'S_UNAPPROVED'	=> (gallery_acl_check('m_status', $album_id, $recent_images[$j]['album_user_id']) && (!$recent_images[$j]['image_status'])) ? true : false,
						'S_LOCKED'		=> (gallery_acl_check('m_status', $album_id) && ($recent_images[$j]['image_status'] == 2)) ? true : false,
						'S_REPORTED'	=> (gallery_acl_check('m_report', $album_id, $recent_images[$j]['album_user_id']) && $recent_images[$j]['image_reported']) ? true : false,

						'ALBUM_NAME'	=> ($display['album']) ? ((utf8_strlen(htmlspecialchars_decode($recent_images[$j]['album_name'])) > $gallery_config['shorted_imagenames'] + 3 ) ? htmlspecialchars(utf8_substr(htmlspecialchars_decode($recent_images[$j]['album_name']), 0, $gallery_config['shorted_imagenames']) . '...') : ($recent_images[$j]['album_name'])) : '',
						'POSTER'		=> ($display['poster']) ? get_username_string('full', $recent_images[$j]['image_user_id'], ($recent_images[$j]['image_user_id'] <> ANONYMOUS) ? $recent_images[$j]['image_username'] : $user->lang['GUEST'], $recent_images[$j]['image_user_colour']) : '',
						'TIME'			=> ($display['time']) ? $user->format_date($recent_images[$j]['image_time']) : '',
						'VIEW'			=> ($display['views']) ? $recent_images[$j]['image_view_count'] : -1,

						'S_RATINGS'		=> ($display['ratings']) ? (($gallery_config['allow_rates'] == 1) && gallery_acl_check('i_rate', $album_id, $recent_images[$j]['album_user_id'])) ? $recent_images[$j]['rating'] : '' : '',
						'U_RATINGS'		=> ($display['ratings']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $recent_images[$j]['image_album_id'] . "&amp;image_id=" . $recent_images[$j]['image_id']) . '#rating' : '',
						'L_COMMENTS'	=> ($display['comments']) ? ($recent_images[$j]['image_comments'] == 1) ? $user->lang['COMMENT'] : $user->lang['COMMENTS'] : '',
						'S_COMMENTS'	=> ($display['comments']) ? (($gallery_config['allow_comments'] == 1) && gallery_acl_check('c_read', $album_id, $recent_images[$j]['album_user_id'])) ? (($recent_images[$j]['image_comments']) ? $recent_images[$j]['image_comments'] : $user->lang['NO_COMMENTS']) : '' : '',
						'U_COMMENTS'	=> ($display['comments']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $recent_images[$j]['image_album_id'] . "&amp;image_id=" . $recent_images[$j]['image_id']) . '#comments' : '',

						'S_IP'		=> ($auth->acl_get('a_')) ? $recent_images[$j]['image_user_ip'] : '',
						'U_WHOIS'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'mode=whois&amp;ip=' . $recent_images[$j]['image_user_ip']),
						'U_REPORT'	=> (gallery_acl_check('m_report', $album_id, $recent_images[$j]['album_user_id']) && $recent_images[$j]['image_reported']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=report_details&amp;album_id=$album_id&amp;option_id=" . $recent_images[$j]['image_reported']) : '',
						'U_STATUS'	=> (gallery_acl_check('m_status', $album_id, $recent_images[$j]['album_user_id'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=queue_details&amp;album_id=$album_id&amp;option_id=" . $recent_images[$j]['image_id']) : '',
						'L_STATUS'	=> (!$recent_images[$j]['image_status']) ? $user->lang['APPROVE_IMAGE'] : (($recent_images[$j]['image_status'] == 1) ? $user->lang['CHANGE_IMAGE_STATUS'] : $user->lang['UNLOCK_IMAGE']),
						'U_MOVE'	=> (gallery_acl_check('m_move', $album_id, $recent_images[$j]['album_user_id'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "action=images_move&amp;album_id=$album_id&amp;image_id=" . $recent_images[$j]['image_id'] . "&amp;redirect=redirect") : '',
						'U_EDIT'	=> $allow_edit ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=edit&amp;album_id=$album_id&amp;image_id=" . $recent_images[$j]['image_id']) : '',
						'U_DELETE'	=> $allow_delete ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=delete&amp;album_id=$album_id&amp;image_id=" . $recent_images[$j]['image_id']) : '',
					));
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
			$sql = "SELECT i.*, a.album_name, a.album_id, a.album_user_id
				FROM " . GALLERY_IMAGES_TABLE . " i
				LEFT JOIN " . GALLERY_ALBUMS_TABLE . " a
					ON i.image_album_id = a.album_id
				WHERE (" . $db->sql_in_set('i.image_album_id', $view_albums) . '
						AND i.image_status = 1)' . 
					(($moderate_albums) ? 'OR (' . $db->sql_in_set('i.image_album_id', $moderate_albums) . ')' : '') . "
				GROUP BY i.image_id
				ORDER BY $random";
			$result = $db->sql_query_limit($sql, $limit_sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$random_images[] = $row;
			}
			$db->sql_freeresult($result);

			for ($i = 0; $i < count($random_images); $i += $columns)
			{
				$template->assign_block_vars('random', array());

				for ($j = $i; $j < ($i + $columns); $j++)
				{
					if ($j >= count($random_images))
					{
						$template->assign_block_vars('random.no_image', array());
						continue;
					}
					$album_id = $random_images[$j]['image_album_id'];

					if ($display['ratings'] && !$random_images[$j]['image_rates'])
					{
						$random_images[$j]['rating'] = $user->lang['NOT_RATED'];
					}
					else if ($display['ratings'])
					{
						$random_images[$j]['rating'] = sprintf((($random_images[$j]['image_rates'] == 1) ? $user->lang['RATE_STRING'] : $user->lang['RATES_STRING']), $random_images[$j]['image_rate_avg'] / 100, $random_images[$j]['image_rates']);
					}
					$perm_user_id = ($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from'];
					$allow_edit = ((gallery_acl_check('i_edit', $album_id, $random_images[$j]['album_user_id']) && ($random_images[$j]['image_user_id'] == $perm_user_id)) || gallery_acl_check('m_edit', $album_id, $random_images[$j]['album_user_id'])) ? true : false;
					$allow_delete = ((gallery_acl_check('i_delete', $album_id, $random_images[$j]['album_user_id']) && ($random_images[$j]['image_user_id'] == $perm_user_id)) || gallery_acl_check('m_delete', $album_id, $random_images[$j]['album_user_id'])) ? true : false;

					$template->assign_block_vars('random.image', array(
						'IMAGE_ID'		=> $random_images[$j]['image_id'],
						'UC_IMAGE_NAME'	=> ($display['name']) ? (generate_image_link('image_name', $gallery_config['link_image_name'], $random_images[$j]['image_id'], $random_images[$j]['image_name'], $random_images[$j]['image_album_id'])) : '',
						'UC_THUMBNAIL'	=> generate_image_link('thumbnail', $gallery_config['link_thumbnail'], $random_images[$j]['image_id'], $random_images[$j]['image_name'], $random_images[$j]['image_album_id']),
						'U_ALBUM'		=> ($display['album']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $random_images[$j]['image_album_id']) : '',
						'S_UNAPPROVED'	=> (gallery_acl_check('m_status', $album_id, $random_images[$j]['album_user_id']) && (!$random_images[$j]['image_status'])) ? true : false,
						'S_LOCKED'		=> (gallery_acl_check('m_status', $album_id) && ($random_images[$j]['image_status'] == 2)) ? true : false,
						'S_REPORTED'	=> (gallery_acl_check('m_report', $album_id, $random_images[$j]['album_user_id']) && $random_images[$j]['image_reported']) ? true : false,

						'ALBUM_NAME'	=> ($display['album']) ? ((utf8_strlen(htmlspecialchars_decode($random_images[$j]['album_name'])) > $gallery_config['shorted_imagenames'] + 3 ) ? htmlspecialchars(utf8_substr(htmlspecialchars_decode($random_images[$j]['album_name']), 0, $gallery_config['shorted_imagenames']) . '...') : ($random_images[$j]['album_name'])) : '',
						'POSTER'		=> ($display['poster']) ? get_username_string('full', $random_images[$j]['image_user_id'], ($random_images[$j]['image_user_id'] <> ANONYMOUS) ? $random_images[$j]['image_username'] : $user->lang['GUEST'], $random_images[$j]['image_user_colour']) : '',
						'TIME'			=> ($display['time']) ? $user->format_date($random_images[$j]['image_time']) : '',
						'VIEW'			=> ($display['views']) ? $random_images[$j]['image_view_count'] : -1,

						'S_RATINGS'		=> ($display['ratings']) ? (($gallery_config['allow_rates'] == 1) && gallery_acl_check('i_rate', $album_id, $random_images[$j]['album_user_id'])) ? $random_images[$j]['rating'] : '' : '',
						'U_RATINGS'		=> ($display['ratings']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $random_images[$j]['image_album_id'] . "&amp;image_id=" . $random_images[$j]['image_id']) . '#rating' : '',
						'L_COMMENTS'	=> ($display['comments']) ? ($random_images[$j]['image_comments'] == 1) ? $user->lang['COMMENT'] : $user->lang['COMMENTS'] : '',
						'S_COMMENTS'	=> ($display['comments']) ? (($gallery_config['allow_comments'] == 1) && gallery_acl_check('c_read', $album_id, $random_images[$j]['album_user_id'])) ? (($random_images[$j]['image_comments']) ? $random_images[$j]['image_comments'] : $user->lang['NO_COMMENTS']) : '' : '',
						'U_COMMENTS'	=> ($display['comments']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $random_images[$j]['image_album_id'] . "&amp;image_id=" . $random_images[$j]['image_id']) . '#comments' : '',

						'S_IP'		=> ($auth->acl_get('a_')) ? $random_images[$j]['image_user_ip'] : '',
						'U_WHOIS'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'mode=whois&amp;ip=' . $random_images[$j]['image_user_ip']),
						'U_REPORT'	=> (gallery_acl_check('m_report', $album_id, $random_images[$j]['album_user_id']) && $random_images[$j]['image_reported']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=report_details&amp;album_id=$album_id&amp;option_id=" . $random_images[$j]['image_reported']) : '',
						'U_STATUS'	=> (gallery_acl_check('m_status', $album_id, $random_images[$j]['album_user_id']) && ($random_images[$j]['image_status'] || ($user->data['user_id'] <> $random_images[$j]['image_user_id']))) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=queue_details&amp;album_id=$album_id&amp;option_id=" . $random_images[$j]['image_id']) : '',
						'L_STATUS'	=> (!$random_images[$j]['image_status']) ? $user->lang['APPROVE_IMAGE'] : (($random_images[$j]['image_status'] == 1) ? $user->lang['CHANGE_IMAGE_STATUS'] : $user->lang['UNLOCK_IMAGE']),
						'U_MOVE'	=> (gallery_acl_check('m_move', $album_id, $random_images[$j]['album_user_id'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "action=images_move&amp;album_id=$album_id&amp;image_id=" . $random_images[$j]['image_id'] . "&amp;redirect=redirect") : '',
						'U_EDIT'	=> $allow_edit ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=edit&amp;album_id=$album_id&amp;image_id=" . $random_images[$j]['image_id']) : '',
						'U_DELETE'	=> $allow_delete ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=delete&amp;album_id=$album_id&amp;image_id=" . $random_images[$j]['image_id']) : '',
					));
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
			LEFT JOIN ' . GALLERY_IMAGES_TABLE . " i
				ON c.comment_image_id = i.image_id
			WHERE " . $db->sql_in_set('i.image_album_id', $comment_albums) . "
			ORDER BY c.comment_id DESC";
		$result = $db->sql_query_limit($sql, $limit_sql);

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
				'IMAGE_AUTHOR'			=> get_username_string('full', $commentrow['image_user_id'], ($commentrow['image_user_id'] <> ANONYMOUS) ? $commentrow['image_username'] : ($user->lang['GUEST'] . ': ' . $commentrow['image_comment_username']), $commentrow['image_user_colour']),
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
		));
	}

	$template->assign_vars(array(
		'S_THUMBNAIL_SIZE'			=> $gallery_config['thumbnail_size'] + 20 + (($gallery_config['thumbnail_info_line']) ? 16 : 0),
		'S_COL_WIDTH'			=> (100/$gallery_config['cols_per_page']) . '%',
		'S_COLS'				=> $columns,
		'S_RANDOM'				=> $random,
		'S_RECENT'				=> $recent,
	));
}

?>