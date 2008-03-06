<?php

/**
*
* @package phpBB3
* @version $Id: index.php 371 2008-03-04 14:44:15Z nickvergessen $
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');


//
// Get general album information
//
include($phpbb_root_path . $gallery_root_path . 'includes/common.'.$phpEx);


/**
* Build Album-Index
*/
$mode = request_var('mode', 'index', true);
include($phpbb_root_path . $gallery_root_path . 'includes/functions_display.' . $phpEx);
if ($mode == 'personal')
{
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $user->lang['PERSONAL_ALBUMS'],
		'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx", 'mode=personal'))
	);

	$template->assign_vars(array(
		'S_PERSONAL_GALLERY' 		=> true,
	));
}
display_albums(0, $mode);



/**
* Recent Public Pics
*/
include($phpbb_root_path . $gallery_root_path . 'includes/functions_recent.' . $phpEx);
//(rows, columns)
$display = array(
	'name'		=> true,
	'poster'	=> true,
	'time'		=> true,
	'views'		=> true,
	'ratings'	=> false,
	'comments'	=> false,
	'album'		=> true,
);
recent_gallery_images(1, 4, $display);
/*

				$template->assign_block_vars('recent_pics.recent_detail', array(
					'TITLE'			=> $recentrow[$j]['image_name'],
					'POSTER_FULL'	=> get_username_string('full', $recentrow[$j]['image_user_id'], ($recentrow[$j]['image_user_id'] <> ANONYMOUS) ? $recentrow[$j]['image_username'] : $user->lang['GUEST'], $recentrow[$j]['image_user_colour']),
					'TIME'			=> $user->format_date($recentrow[$j]['image_time']),
					'VIEW'			=> $recentrow[$j]['image_view_count'],
					'RATING'		=> ($album_config['rate'] == 1) ? ( '<a href="' . append_sid("{$phpbb_root_path}gallery/image_page.$phpEx?image_id=" . $recentrow[$j]['image_id']) . '#rating">' . $user->lang['RATING'] . '</a>: ' . $recentrow[$j]['rating'] . '<br />') : '',
					'COMMENTS'		=> ($album_config['comment'] == 1) ? ( '<a href="' . append_sid("{$phpbb_root_path}gallery/image_page.$phpEx?image_id=" . $recentrow[$j]['image_id']) . '#comments">' . $user->lang['COMMENTS'] . '</a>: ' . $recentrow[$j]['comments'] . '<br />') : '',
					'IP'			=> ($user->data['user_type'] == USER_FOUNDER) ? $user->lang['IP'] . ': <a href="http://www.nic.com/cgi-bin/whois.cgi?query=' . $recentrow[$j]['image_user_ip'] . '">' . $recentrow[$j]['image_user_ip'] . '</a><br />' : ''
				));
*/


/**
* Start output the page
*/

$template->assign_vars(array(
	'U_YOUR_PERSONAL_GALLERY' 		=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'user_id=' . $user->data['user_id']),
	'U_USERS_PERSONAL_GALLERIES' 	=> append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx", 'mode=personal'),

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