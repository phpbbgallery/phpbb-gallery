<?php

/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');


//
// Get general album information
//
include($phpbb_root_path . 'gallery/includes/common.'.$phpEx);


/**
* Build Album-Index
*/
$album_id = 0;
include($phpbb_root_path . 'gallery/includes/functions_display.' . $phpEx);


/**
* Recent Public Pics
*/
$allowed_cat = '';
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
}
if ($allowed_cat <> '')
{
	$sql = 'SELECT p.*, u.user_id, u.username, u.user_colour, r.rate_image_id, AVG(r.rate_point) AS rating, COUNT(DISTINCT c.comment_id) AS comments
		FROM ' . GALLERY_IMAGES_TABLE . ' AS p
		LEFT JOIN ' . USERS_TABLE . ' AS u
			ON p.image_user_id = u.user_id
		LEFT JOIN ' . GALLERY_ALBUMS_TABLE . ' AS ct
			ON p.image_album_id = ct.album_id
		LEFT JOIN ' . GALLERY_RATES_TABLE . ' AS r
			ON p.image_id = r.rate_image_id
		LEFT JOIN ' . GALLERY_COMMENTS_TABLE . ' AS c
			ON p.image_id = c.comment_image_id
		WHERE p.image_album_id IN (' . $allowed_cat . ') 
			AND ( p.image_approval = 1 OR ct.album_approval = 0 )
		GROUP BY p.image_id
		ORDER BY p.image_time DESC
		LIMIT ' . $album_config['cols_per_page'];
	$result = $db->sql_query($sql);

	$recentrow = array();

	while( $row = $db->sql_fetchrow($result) )
	{
		$recentrow[] = $row;
	}


	if (count($recentrow) > 0)
	{
		for ($i = 0; $i < count($recentrow); $i += $album_config['cols_per_page'])
		{
			$template->assign_block_vars('recent_pics', array());

			for ($j = $i; $j < ($i + $album_config['cols_per_page']); $j++)
			{
				if( $j >= count($recentrow) )
				{
					break;
				}

				if(!$recentrow[$j]['rating'])
				{
					$recentrow[$j]['rating'] = $user->lang['NOT_RATED'];
				}
				else
				{
					$recentrow[$j]['rating'] = round($recentrow[$j]['rating'], 2);
				}
				$message_parser				= new parse_message();
				$message_parser->message	= $recentrow[$j]['image_desc'];
				$message_parser->decode_message($recentrow[$j]['image_desc_uid']);
				$template->assign_block_vars('recent_pics.recent_col', array(
					'U_PIC' 		=> ($album_config['fullpic_popup']) ? append_sid("{$phpbb_root_path}gallery/image.$phpEx?pic_id=". $recentrow[$j]['image_id']) : append_sid("{$phpbb_root_path}gallery/image_page.$phpEx?image_id=". $recentrow[$j]['image_id']),
					'THUMBNAIL' 	=> append_sid("{$phpbb_root_path}gallery/thumbnail.$phpEx?pic_id=". $recentrow[$j]['image_id']),
					'DESC' 			=> $message_parser->message,
					)
				);

				if ($recentrow[$j]['user_id'] == ALBUM_GUEST)
				{
					$recent_poster = ($recentrow[$j]['image_username'] == '') ? $user->lang['GUEST'] : $recentrow[$j]['image_username'];
				}

				$template->assign_block_vars('recent_pics.recent_detail', array(
					'TITLE'			=> $recentrow[$j]['image_name'],
					'POSTER_FULL'	=> get_username_string('full', $recentrow[$j]['user_id'], ($recentrow[$j]['user_id'] <> ANONYMOUS) ? $recentrow[$j]['username'] : $user->lang['GUEST'], $recentrow[$j]['user_colour']),
					'TIME'			=> $user->format_date($recentrow[$j]['image_time']),
					'VIEW'			=> $recentrow[$j]['image_view_count'],
					'RATING'		=> ($album_config['rate'] == 1) ? ( '<a href="' . append_sid("{$phpbb_root_path}gallery/image_page.$phpEx?image_id=" . $recentrow[$j]['image_id']) . '#rating">' . $user->lang['RATING'] . '</a>: ' . $recentrow[$j]['rating'] . '<br />') : '',
					'COMMENTS'		=> ($album_config['comment'] == 1) ? ( '<a href="' . append_sid("{$phpbb_root_path}gallery/image_page.$phpEx?image_id=" . $recentrow[$j]['image_id']) . '#comments">' . $user->lang['COMMENTS'] . '</a>: ' . $recentrow[$j]['comments'] . '<br />') : '',
					'IP'			=> ($user->data['user_type'] == USER_FOUNDER) ? $user->lang['IP'] . ': <a href="http://www.nic.com/cgi-bin/whois.cgi?query=' . $recentrow[$j]['image_user_ip'] . '" target="_blank">' . $recentrow[$j]['image_user_ip'] . '</a><br />' : ''
				));
			}
		}
	}
	else
	{
		$template->assign_block_vars('no_pics', array());
	}
}
else
{
	$template->assign_block_vars('no_pics', array());
}


/**
* Start output the page
*/

$template->assign_vars(array(
	'U_YOUR_PERSONAL_GALLERY' 		=> append_sid("{$phpbb_root_path}gallery/album_personal.$phpEx?user_id=" . $user->data['user_id']),
	'U_USERS_PERSONAL_GALLERIES' 	=> append_sid("{$phpbb_root_path}gallery/album_personal_index.$phpEx"),

	'S_LOGIN_ACTION'				=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login'),
	'S_COLS' 						=> $album_config['cols_per_page'],
	'S_COL_WIDTH' 					=> (100/$album_config['cols_per_page']) . '%',
	'TARGET_BLANK' 					=> ($album_config['fullpic_popup']) ? 'target="_blank"' : '',
));

// Output page
$page_title = $user->lang['GALLERY'];

page_header($page_title);

$template->set_filenames(array(
	'body' => 'gallery_index_body.html')
);

page_footer();
?>