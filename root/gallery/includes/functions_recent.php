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
function recent_gallery_images($rows, $columns)
{
	global $db, $phpEx, $user, $phpbb_root_path, $config, $template;
	
	$user->add_lang('mods/gallery');
	include_once($phpbb_root_path . 'gallery/includes/common.'.$phpEx);
	include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
$allowed_cat = $mod_cat = '';
$sql = 'SELECT *
	FROM ' . GALLERY_ALBUMS_TABLE . '
	ORDER BY left_id ASC';
$result = $db->sql_query($sql);

while( $row = $db->sql_fetchrow($result) )
{
	$album_user_access = album_user_access($row['album_id'], $row, 1, 0, 0, 0, 0, 0);
	if ($album_user_access['view'] == 1)
	{
		$allowed_cat .= ($allowed_cat == '') ? $row['album_id'] : ', ' . $row['album_id'];
	}
	if ($album_user_access['moderator'] == 1)
	{
		$mod_cat .= ($mod_cat == '') ? $row['album_id'] : ', ' . $row['album_id'];
	}
}
//album_moderator_groups
		$limit_sql = $rows * $columns;
if ($allowed_cat <> '')
{
		$limit_sql = $rows * $columns;
		$sql = 'SELECT i.*, u.user_id, u.username, u.user_colour, r.rate_image_id, AVG(r.rate_point) AS rating, COUNT(DISTINCT c.comment_id) AS comments, MAX(c.comment_id) as new_comment
			FROM ' . GALLERY_IMAGES_TABLE . ' AS i
			LEFT JOIN ' . USERS_TABLE . ' AS u
				ON i.image_user_id = u.user_id
			LEFT JOIN ' . GALLERY_RATES_TABLE . ' AS r
				ON i.image_id = r.rate_image_id
			LEFT JOIN ' . GALLERY_COMMENTS_TABLE . ' AS c
				ON i.image_id = c.comment_image_id
			WHERE (i.image_album_id IN (' . $allowed_cat . ') 
					OR i.image_album_id = ' . PERSONAL_GALLERY . ')
				AND i.image_approval = 1
			GROUP BY i.image_id
			ORDER BY i.image_time DESC
			LIMIT ' . $limit_sql;
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

				if(!$picrow[$j]['rating'])
				{
					$picrow[$j]['rating'] = $user->lang['NOT_RATED'];
				}
				else
				{
					$picrow[$j]['rating'] = round($picrow[$j]['rating'], 2);
				}

				$message_parser				= new parse_message();
				$message_parser->message	= $picrow[$j]['image_desc'];
				$message_parser->decode_message($picrow[$j]['image_desc_uid']);
				$template->assign_block_vars('picrow.piccol', array(
					'U_IMAGE'		=> append_sid("{$phpbb_root_path}gallery/image_page.$phpEx?image_id=" . $picrow[$j]['image_id']),
					'THUMBNAIL'		=> append_sid("{$phpbb_root_path}gallery/thumbnail.$phpEx?image_id=" . $picrow[$j]['image_id']),
					'DESC'			=> $message_parser->message,
				));

				$template->assign_block_vars('picrow.pic_detail', array(
					'TITLE'		=> $picrow[$j]['image_name'],
					'POSTER'	=> get_username_string('full', $picrow[$j]['user_id'], ($picrow[$j]['user_id'] <> ANONYMOUS) ? $picrow[$j]['username'] : $user->lang['GUEST'], $picrow[$j]['user_colour']),
					'TIME'		=> $user->format_date($picrow[$j]['image_time']),
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