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
function recent_gallery_images($rows, $columns, &$display)
{
	global $db, $phpEx, $user, $cache;
	global $phpbb_root_path, $album_config, $config, $template;

	include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

	$user->add_lang('mods/gallery');
	$recent_image_addon = true;
	$gallery_root_path = GALLERY_ROOT_PATH;
	include_once("{$phpbb_root_path}{$gallery_root_path}includes/common.$phpEx");
	include_once("{$phpbb_root_path}{$gallery_root_path}includes/permissions.$phpEx");
	$album_access_array = get_album_access_array();

	$albums = $cache->obtain_album_list();
	$allowed_albums = gallery_acl_album_ids('i_view', 'string');
	$limit_sql = $rows * $columns;

	if ($allowed_albums != '')
	{
		$limit_sql = $rows * $columns;

		if ($display['album'])
		{
			$album_sql1 = ', a.album_name, a.album_id';
			$album_sql2 = '			LEFT JOIN ' . GALLERY_ALBUMS_TABLE . ' AS a
				ON i.image_album_id = a.album_id';
		}
		else
		{
			$album_sql1 = '';
			$album_sql2 = '';
		}

		$sql = "SELECT i.* $album_sql1
			FROM " . GALLERY_IMAGES_TABLE . " AS i
			$album_sql2
			WHERE i.image_album_id IN ($allowed_albums)
				AND i.image_status = 1
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
			$template->assign_block_vars('picrow', array());

			for ($j = $i; $j < ($i + $columns); $j++)
			{
				if( $j >= count($picrow) )
				{
					$template->assign_block_vars('picrow.nopiccol', array()); 
					$template->assign_block_vars('picrow.picnodetail', array()); 
					continue;
				}

				$message_parser				= new parse_message();
				$message_parser->message	= $picrow[$j]['image_desc'];
				$message_parser->decode_message($picrow[$j]['image_desc_uid']);
				$template->assign_block_vars('picrow.piccol', array(
					'U_IMAGE'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
					'THUMBNAIL'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}thumbnail.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
					'DESC'			=> $message_parser->message,
				));

				if ($display['ratings'] && !$picrow[$j]['image_rates'])
				{
					$picrow[$j]['rating'] = $user->lang['NOT_RATED'];
				}
				else if ($display['ratings'])
				{
					$picrow[$j]['rating'] = $picrow[$j]['image_rate_avg'] / 100;
				}

				$template->assign_block_vars('picrow.pic_detail', array(
					'U_IMAGE'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
					'U_IMAGE_PAGE'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
					'IMAGE_NAME'	=> ($display['name']) ? ($picrow[$j]['image_name']) : '',
					'POSTER'		=> ($display['poster']) ? (get_username_string('full', $picrow[$j]['image_user_id'], (($picrow[$j]['image_user_id'] <> ANONYMOUS) ? $picrow[$j]['image_username'] : $user->lang['GUEST']), $picrow[$j]['image_user_colour'])) : '',
					'TIME'			=> ($display['time']) ? ($user->format_date($picrow[$j]['image_time'])) : '',
					'VIEWS'			=> ($display['views']) ? $picrow[$j]['image_view_count'] : '',
					'RATINGS'		=> ($display['ratings']) ? (($album_config['rate'] == 1) ? $picrow[$j]['rating'] : 0) : '',
					'L_COMMENT'		=> ($display['comments']) ? (($picrow[$j]['image_comments'] == 1) ? $user->lang['COMMENT'] : $user->lang['COMMENTS']) : '',
					'COMMENTS'		=> ($display['comments']) ? (($album_config['comment'] == 1) ? $picrow[$j]['image_comments'] : 0) : '',
					'ALBUM'			=> ($display['album']) ? $picrow[$j]['album_name'] : '',
					'U_ALBUM'		=> ($display['album']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $picrow[$j]['album_id']) : '',
				));
			}
		}
	}
	else
	{
		$template->assign_block_vars('no_pics', array());
	}

	$template->assign_vars(array(
		'S_COL_WIDTH'			=> (100/$album_config['cols_per_page']) . '%',
		'S_COLS'				=> $columns,
	));
}

?>