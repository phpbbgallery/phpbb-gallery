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
	global $db, $phpEx, $user, $phpbb_root_path, $album_config, $config, $template;

	$user->add_lang('mods/gallery');
	$recent_image_addon = true;
	$gallery_root_path = GALLERY_ROOT_PATH;
	include_once("{$phpbb_root_path}{$gallery_root_path}includes/common.$phpEx");
	include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

	$allowed_cat = $mod_cat = '';
	$sql = 'SELECT *
		FROM ' . GALLERY_ALBUMS_TABLE . '
		ORDER BY left_id ASC';
	$result = $db->sql_query($sql);

	while( $row = $db->sql_fetchrow($result) )
	{
		$album_user_access = (!$row['album_user_id']) ? album_user_access($row['album_id'], $row, 1, 0, 0, 0, 0, 0) : personal_album_access($row['album_user_id']);
		if ($album_user_access['view'] == 1)
		{
			$allowed_cat .= ($allowed_cat == '') ? $row['album_id'] : ', ' . $row['album_id'];
		}
		if ($album_user_access['moderator'] == 1)
		{
			$mod_cat .= ($mod_cat == '') ? $row['album_id'] : ', ' . $row['album_id'];
		}
	}
	$personal_gallery_access = personal_gallery_access(1,1);
	if ($personal_gallery_access['view'])
	{
		$allowed_cat .= ($allowed_cat == '') ? PERSONAL_GALLERY : ', ' . PERSONAL_GALLERY;
	}
	//album_moderator_groups
	$limit_sql = $rows * $columns;

	if ($allowed_cat <> '')
	{
		$limit_sql = $rows * $columns;

		if ($display['ratings'])
		{
			$rate_sql1 = ', AVG(r.rate_point) AS rating';
			$rate_sql2 = '			LEFT JOIN ' . GALLERY_RATES_TABLE . ' AS r
				ON i.image_id = r.rate_image_id';
		}
		else
		{
			$rate_sql1 = '';
			$rate_sql2 = '';
		}
		if ($display['comments'])
		{
			$comment_sql1 = ', COUNT(DISTINCT c.comment_id) AS comment';
			$comment_sql2 = '			LEFT JOIN ' . GALLERY_COMMENTS_TABLE . ' AS c
				ON i.image_id = c.comment_image_id';
		}
		else
		{
			$comment_sql1 = '';
			$comment_sql2 = '';
		}

		$sql = "SELECT i.* $rate_sql1 $comment_sql1
			FROM " . GALLERY_IMAGES_TABLE . " AS i
			$rate_sql2
			$comment_sql2
			WHERE (i.image_album_id IN ($allowed_cat) 
					OR i.image_album_id = " . PERSONAL_GALLERY . ")
				AND i.image_approval = 1
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
					'U_IMAGE'		=> ($album_config['fullpic_popup']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']) : append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
					'THUMBNAIL'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}thumbnail.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
					'DESC'			=> $message_parser->message,
				));

				if ($display['ratings'] && !$picrow[$j]['rating'])
				{
					$picrow[$j]['rating'] = $user->lang['NOT_RATED'];
				}
				else if ($display['ratings'])
				{
					$picrow[$j]['rating'] = round($picrow[$j]['rating'], 2);
				}

				$template->assign_block_vars('picrow.pic_detail', array(
					'TITLE'		=> ($display['name']) ? ($picrow[$j]['image_name']) : '',
					'POSTER'	=> ($display['poster']) ? (get_username_string('full', $picrow[$j]['image_user_id'], (($picrow[$j]['image_user_id'] <> ANONYMOUS) ? $picrow[$j]['image_username'] : $user->lang['GUEST']), $picrow[$j]['image_user_colour'])) : '',
					'TIME'		=> ($display['time']) ? ($user->format_date($picrow[$j]['image_time'])) : '',
					'VIEWS'		=> ($display['views']) ? $picrow[$j]['image_view_count'] : '',
					'RATINGS'	=> ($display['ratings']) ? (($album_config['rate'] == 1) ? $picrow[$j]['rating'] : 0) : '',
					'L_COMMENT'	=> ($display['comments']) ? (($picrow[$j]['comment'] == 1) ? $user->lang['COMMENT'] : $user->lang['COMMENTS']) : '',
					'COMMENTS'	=> ($display['comments']) ? (($album_config['comment'] == 1) ? $picrow[$j]['comment'] : 0) : '',
				));
			}
		}
	}
	else
	{
		$template->assign_block_vars('no_pics', array());
	}

	$template->assign_vars(array(
		'S_COLS'				=> $columns,
	));
}

?>