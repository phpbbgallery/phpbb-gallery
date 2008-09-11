<?php

/**
*
* @package phpBB3
* @version $Id: functions_display.php 225 2008-01-13 13:35:16Z nickvergessen $
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}
function recent_gallery_images($rows, $columns, &$display, $modes)
{
	global $db, $phpEx, $user, $cache, $auth;
	global $phpbb_root_path, $album_config, $config, $template;

	include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

	$user->add_lang('mods/gallery');
	$recent_image_addon = true;
	$gallery_root_path = GALLERY_ROOT_PATH;
	include_once("{$phpbb_root_path}{$gallery_root_path}includes/common.$phpEx");
	include_once("{$phpbb_root_path}{$gallery_root_path}includes/permissions.$phpEx");
	$album_access_array = get_album_access_array();

	$albums = $cache->obtain_album_list();
	$view_albums = gallery_acl_album_ids('i_view', 'array');
	$moderate_albums = gallery_acl_album_ids('a_moderate', 'array');
	$limit_sql = $rows * $columns;
	switch ($modes)
	{
		case 'recent':
			$recent = true;
			$random = false;
		break;

		case 'random':
			$recent = false;
			$random = true;
		break;

		case 'both':
		default:
			$recent = true;
			$random = true;
		break;
	}

	if (($view_albums != array()) || ($moderate_albums != array()))
	{
		$limit_sql = $rows * $columns;

		if ($recent)
		{
			$sql = "SELECT i.*, a.album_name, a.album_id, a.album_user_id
				FROM " . GALLERY_IMAGES_TABLE . " i
				LEFT JOIN " . GALLERY_ALBUMS_TABLE . " a
					ON i.image_album_id = a.album_id
				WHERE (" . $db->sql_in_set('i.image_album_id', $view_albums) . '
						AND i.image_status = 1)' . 
					(($moderate_albums) ? 'OR (' . $db->sql_in_set('i.image_album_id', $moderate_albums) . ')' : '') . "
				GROUP BY i.image_id
				ORDER BY i.image_time DESC
				LIMIT $limit_sql";
			$result = $db->sql_query($sql);

			$picrow = array();

			while( $row = $db->sql_fetchrow($result) )
			{
				$picrow[] = $row;
			}
			for ($i = 0; $i < count($picrow); $i += $columns)
			{
				$template->assign_block_vars('recent', array());

				for ($j = $i; $j < ($i + $columns); $j++)
				{
					if( $j >= count($picrow) )
					{
						$template->assign_block_vars('recent.no_image', array());
						continue;
					}
					$album_id = $picrow[$j]['image_album_id'];

					if ($display['ratings'] && !$picrow[$j]['image_rates'])
					{
						$picrow[$j]['rating'] = $user->lang['NOT_RATED'];
					}
					else if ($display['ratings'])
					{
						$picrow[$j]['rating'] = $picrow[$j]['image_rate_avg'] / 100;
					}
					$perm_user_id = ($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from'];
					$allow_edit = ((gallery_acl_check('i_edit', $album_id, $picrow[$j]['album_user_id']) && ($picrow[$j]['image_user_id'] == $perm_user_id)) || gallery_acl_check('a_moderate', $album_id, $picrow[$j]['album_user_id'])) ? true : false;
					$allow_delete = ((gallery_acl_check('i_delete', $album_id, $picrow[$j]['album_user_id']) && ($picrow[$j]['image_user_id'] == $perm_user_id)) || gallery_acl_check('a_moderate', $album_id, $picrow[$j]['album_user_id'])) ? true : false;

					$template->assign_block_vars('recent.image', array(
						'IMAGE_ID'		=> $picrow[$j]['image_id'],
						'U_IMAGE'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
						'U_THUMBNAIL'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}thumbnail.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
						'U_IMAGE_PAGE'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
						'U_ALBUM'		=> ($display['album']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $picrow[$j]['image_album_id']) : '',
						'S_UNAPPROVED'	=> (gallery_acl_check('a_moderate', $album_id, $picrow[$j]['album_user_id']) && (!$picrow[$j]['image_status'])) ? true : false,
						'S_REPORTED'	=> (gallery_acl_check('a_moderate', $album_id, $picrow[$j]['album_user_id']) && $picrow[$j]['image_reported']) ? true : false,

						'IMAGE_NAME'	=> ($display['name']) ? $picrow[$j]['image_name'] : '',
						'ALBUM_NAME'	=> ($display['album']) ? $picrow[$j]['album_name'] : '',
						'POSTER'		=> ($display['poster']) ? get_username_string('full', $picrow[$j]['image_user_id'], ($picrow[$j]['image_user_id'] <> ANONYMOUS) ? $picrow[$j]['image_username'] : $user->lang['GUEST'], $picrow[$j]['image_user_colour']) : '',
						'TIME'			=> ($display['time']) ? $user->format_date($picrow[$j]['image_time']) : '',
						'VIEW'			=> ($display['views']) ? $picrow[$j]['image_view_count'] : '',

						'S_RATINGS'		=> ($display['ratings']) ? (($album_config['rate'] == 1) && gallery_acl_check('i_rate', $album_id, $picrow[$j]['album_user_id'])) ? $picrow[$j]['rating'] : '' : '',
						'U_RATINGS'		=> ($display['ratings']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . "&amp;image_id=" . $picrow[$j]['image_id']) . '#rating' : '',
						'L_COMMENTS'	=> ($display['comments']) ? ($picrow[$j]['image_comments'] == 1) ? $user->lang['COMMENT'] : $user->lang['COMMENTS'] : '',
						'S_COMMENTS'	=> ($display['comments']) ? (($album_config['comment'] == 1) && gallery_acl_check('c_post', $album_id, $picrow[$j]['album_user_id'])) ? (($picrow[$j]['image_comments']) ? $picrow[$j]['image_comments'] : $user->lang['NO_COMMENTS']) : '' : '',
						'U_COMMENTS'	=> ($display['comments']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . "&amp;image_id=" . $picrow[$j]['image_id']) . '#comments' : '',

						'S_IP'		=> ($auth->acl_get('a_')) ? $picrow[$j]['image_user_ip'] : '',
						'U_WHOIS'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'mode=whois&amp;ip=' . $picrow[$j]['image_user_ip']),
						'U_REPORT'	=> (gallery_acl_check('a_moderate', $album_id, $picrow[$j]['album_user_id']) && $picrow[$j]['image_reported']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=report_details&amp;album_id=$album_id&amp;option_id=" . $picrow[$j]['image_reported']) : '',
						'U_STATUS'	=> (gallery_acl_check('a_moderate', $album_id, $picrow[$j]['album_user_id'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=queue_details&amp;album_id=$album_id&amp;option_id=" . $picrow[$j]['image_id']) : '',
						'L_STATUS'	=> (!$picrow[$j]['image_status']) ? $user->lang['APPROVE_IMAGE'] : $user->lang['CHANGE_IMAGE_STATUS'],
						'U_MOVE'	=> (gallery_acl_check('a_moderate', $album_id, $picrow[$j]['album_user_id'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "action=images_move&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id'] . "&amp;redirect=redirect") : '',
						'U_EDIT'	=> $allow_edit ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=edit&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id']) : '',
						'U_DELETE'	=> $allow_delete ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=delete&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id']) : '',
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
			$sql = "SELECT i.*, a.album_name, a.album_id, a.album_user_id
				FROM " . GALLERY_IMAGES_TABLE . " i
				LEFT JOIN " . GALLERY_ALBUMS_TABLE . " a
					ON i.image_album_id = a.album_id
				WHERE (" . $db->sql_in_set('i.image_album_id', $view_albums) . '
						AND i.image_status = 1)' . 
					(($moderate_albums) ? 'OR (' . $db->sql_in_set('i.image_album_id', $moderate_albums) . ')' : '') . "
				GROUP BY i.image_id
				ORDER BY $random
				LIMIT $limit_sql";
			$result = $db->sql_query($sql);

			$picrow = array();

			while( $row = $db->sql_fetchrow($result) )
			{
				$picrow[] = $row;
			}
			for ($i = 0; $i < count($picrow); $i += $columns)
			{
				$template->assign_block_vars('random', array());

				for ($j = $i; $j < ($i + $columns); $j++)
				{
					if( $j >= count($picrow) )
					{
						$template->assign_block_vars('random.no_image', array());
						continue;
					}
					$album_id = $picrow[$j]['image_album_id'];

					if ($display['ratings'] && !$picrow[$j]['image_rates'])
					{
						$picrow[$j]['rating'] = $user->lang['NOT_RATED'];
					}
					else if ($display['ratings'])
					{
						$picrow[$j]['rating'] = $picrow[$j]['image_rate_avg'] / 100;
					}
					$perm_user_id = ($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from'];
					$allow_edit = ((gallery_acl_check('i_edit', $album_id, $picrow[$j]['album_user_id']) && ($picrow[$j]['image_user_id'] == $perm_user_id)) || gallery_acl_check('a_moderate', $album_id, $picrow[$j]['album_user_id'])) ? true : false;
					$allow_delete = ((gallery_acl_check('i_delete', $album_id, $picrow[$j]['album_user_id']) && ($picrow[$j]['image_user_id'] == $perm_user_id)) || gallery_acl_check('a_moderate', $album_id, $picrow[$j]['album_user_id'])) ? true : false;

					$template->assign_block_vars('random.image', array(
						'IMAGE_ID'		=> $picrow[$j]['image_id'],
						'U_IMAGE'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
						'U_THUMBNAIL'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}thumbnail.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
						'U_IMAGE_PAGE'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
						'U_ALBUM'		=> ($display['album']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $picrow[$j]['image_album_id']) : '',
						'S_UNAPPROVED'	=> (gallery_acl_check('a_moderate', $album_id, $picrow[$j]['album_user_id']) && (!$picrow[$j]['image_status'])) ? true : false,
						'S_REPORTED'	=> (gallery_acl_check('a_moderate', $album_id, $picrow[$j]['album_user_id']) && $picrow[$j]['image_reported']) ? true : false,

						'IMAGE_NAME'	=> ($display['name']) ? $picrow[$j]['image_name'] : '',
						'ALBUM_NAME'	=> ($display['album']) ? $picrow[$j]['album_name'] : '',
						'POSTER'		=> ($display['poster']) ? get_username_string('full', $picrow[$j]['image_user_id'], ($picrow[$j]['image_user_id'] <> ANONYMOUS) ? $picrow[$j]['image_username'] : $user->lang['GUEST'], $picrow[$j]['image_user_colour']) : '',
						'TIME'			=> ($display['time']) ? $user->format_date($picrow[$j]['image_time']) : '',
						'VIEW'			=> ($display['views']) ? $picrow[$j]['image_view_count'] : '',

						'S_RATINGS'		=> ($display['ratings']) ? (($album_config['rate'] == 1) && gallery_acl_check('i_rate', $album_id, $picrow[$j]['album_user_id'])) ? $picrow[$j]['rating'] : '' : '',
						'U_RATINGS'		=> ($display['ratings']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . "&amp;image_id=" . $picrow[$j]['image_id']) . '#rating' : '',
						'L_COMMENTS'	=> ($display['comments']) ? ($picrow[$j]['image_comments'] == 1) ? $user->lang['COMMENT'] : $user->lang['COMMENTS'] : '',
						'S_COMMENTS'	=> ($display['comments']) ? (($album_config['comment'] == 1) && gallery_acl_check('c_post', $album_id, $picrow[$j]['album_user_id'])) ? (($picrow[$j]['image_comments']) ? $picrow[$j]['image_comments'] : $user->lang['NO_COMMENTS']) : '' : '',
						'U_COMMENTS'	=> ($display['comments']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . "&amp;image_id=" . $picrow[$j]['image_id']) . '#comments' : '',

						'S_IP'		=> ($auth->acl_get('a_')) ? $picrow[$j]['image_user_ip'] : '',
						'U_WHOIS'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'mode=whois&amp;ip=' . $picrow[$j]['image_user_ip']),
						'U_REPORT'	=> (gallery_acl_check('a_moderate', $album_id, $picrow[$j]['album_user_id']) && $picrow[$j]['image_reported']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=report_details&amp;album_id=$album_id&amp;option_id=" . $picrow[$j]['image_reported']) : '',
						'U_STATUS'	=> (gallery_acl_check('a_moderate', $album_id, $picrow[$j]['album_user_id'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=queue_details&amp;album_id=$album_id&amp;option_id=" . $picrow[$j]['image_id']) : '',
						'L_STATUS'	=> (!$picrow[$j]['image_status']) ? $user->lang['APPROVE_IMAGE'] : $user->lang['CHANGE_IMAGE_STATUS'],
						'U_MOVE'	=> (gallery_acl_check('a_moderate', $album_id, $picrow[$j]['album_user_id'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "action=images_move&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id'] . "&amp;redirect=redirect") : '',
						'U_EDIT'	=> $allow_edit ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=edit&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id']) : '',
						'U_DELETE'	=> $allow_delete ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=delete&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id']) : '',
					));
				}
			}
		}
	}

	$template->assign_vars(array(
		'S_THUMBNAIL_SIZE'			=> $album_config['thumbnail_size'] + 20 + (($album_config['thumbnail_info_line']) ? 16 : 0),
		'S_COL_WIDTH'			=> (100/$album_config['cols_per_page']) . '%',
		'S_COLS'				=> $columns,
		'S_RANDOM'				=> $random,
		'S_RECENT'				=> $recent,
	));
}

?>