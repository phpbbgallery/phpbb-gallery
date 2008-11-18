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
include_once("{$phpbb_root_path}{$gallery_root_path}includes/common.$phpEx");
include_once("{$phpbb_root_path}{$gallery_root_path}includes/permissions.$phpEx");
$album_access_array = get_album_access_array();


/**
* Build Album-Index
*/
$mode = request_var('mode', 'index', true);
include($phpbb_root_path . $gallery_root_path . 'includes/functions_display.' . $phpEx);
display_albums(($mode == 'personal') ? 'personal' : 0);
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
else if ($album_config['personal_album_index'])
{
/**
* add a personal albums category to the album listing if the user has permission to view personal albums
*/
if (gallery_acl_check('a_list', PERSONAL_GALLERY_PERMISSIONS))
{
	$personal_albums = array();
	$images = $images_real = $last_image = 0;
	$sql = 'SELECT *
		FROM ' . GALLERY_ALBUMS_TABLE . '
		WHERE album_user_id <> 0';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$personal_albums[] = $row['album_id'];
		$images = $row['album_images'];
		$images_real = $row['album_images_real'];
		if ($last_image < $row['album_last_image_id'])
		{
			$last_image = $row['album_last_image_id'];
			$lastimage_name = $row['album_last_image_name'];
			$lastimage_time = $user->format_date($row['album_last_image_time']);
			$lastimage_image_id = $row['album_last_image_id'];
			$lastimage_user_id = $row['album_last_user_id'];
			$lastimage_username = $row['album_last_username'];
			$lastimage_user_colour = $row['album_last_user_colour'];
			$last_image_page_url = append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $row['album_id'] . '&amp;image_id=' . $row['album_last_image_id']);
			$last_thumb_url = append_sid("{$phpbb_root_path}{$gallery_root_path}thumbnail.$phpEx", 'album_id=' . $row['album_id'] . '&amp;image_id=' . $row['album_last_image_id']);
			$lastimage_album_id = $row['album_id'];
		}
	}
	$db->sql_freeresult($result);

	$template->assign_block_vars('albumrow', array(
		'S_IS_CAT'				=> false,
		'S_NO_CAT'				=> true,
		'S_LIST_SUBALBUMS'		=> true,
		'S_SUBALBUMS'			=> true,
		'U_VIEWALBUM' 			=> append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx", 'mode=personal'),
		'ALBUM_NAME' 			=> $user->lang['USERS_PERSONAL_ALBUMS'],
		'ALBUM_FOLDER_IMG_SRC'	=> $user->img('forum_read_subforum', 'no', false, '', 'src'),
		'SUBALBUMS'				=> ((gallery_acl_check('i_upload', OWN_GALLERY_PERMISSIONS)) ? '<a href="' . (($user->gallery['personal_album_id'] > 0) ? append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $user->gallery['personal_album_id']) : append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=gallery&amp;mode=manage_albums')) . '">' . $user->data['username'] . '</a>' : ''),
		'ALBUM_DESC'			=> '',
		'L_MODERATORS'			=> '',
		'L_SUBALBUM_STR'		=> (gallery_acl_check('i_upload', OWN_GALLERY_PERMISSIONS)) ? $user->lang['YOUR_PERSONAL_ALBUM'] . ': ' : '',
		'MODERATORS'			=> '',
		'IMAGES'				=> $images,
		'UNAPPROVED_IMAGES'		=> $images_real,
		'LAST_IMAGE_TIME'		=> $lastimage_time,
		'LAST_USER_FULL'		=> get_username_string('full', $lastimage_user_id, $lastimage_username, $lastimage_user_colour),
		'UC_FAKE_THUMBNAIL'		=> ($album_config['disp_fake_thumb']) ? generate_image_link('fake_thumbnail', $album_config['link_thumbnail'], $lastimage_image_id, $lastimage_name, $lastimage_album_id) : '',
		'UC_IMAGE_NAME'			=> generate_image_link('image_name', $album_config['link_image_name'], $lastimage_image_id, $lastimage_name, $lastimage_album_id),
		'UC_LASTIMAGE_ICON'		=> generate_image_link('lastimage_icon', $album_config['link_image_icon'], $lastimage_image_id, $lastimage_name, $lastimage_album_id),
	));

	// Assign subforums loop for style authors
	$template->assign_block_vars('albumrow.subalbum', array(
		'U_SUBALBUM'	=> ((gallery_acl_check('i_upload', OWN_GALLERY_PERMISSIONS)) ? ($user->gallery['personal_album_id'] > 0) ? append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $user->gallery['personal_album_id']) : append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=gallery&amp;mode=manage_albums') : ''),
		'SUBALBUM_NAME'	=> $user->lang['YOUR_PERSONAL_ALBUM'],
	));
}
/**
* add categories and albums to the album listing
*/
}




/**
* Recent Public Pics
*/
include($phpbb_root_path . $gallery_root_path . 'includes/functions_recent.' . $phpEx);
$display = array(
	'name'		=> true,
	'poster'	=> true,
	'time'		=> true,
	'views'		=> true,
	'ratings'	=> true,
	'comments'	=> true,
	'album'		=> true,
);
/**
* rows		numeric default 1,
* columns	numeric default 4,
* display	array,
* modes		string(recent|random|both),
*/
recent_gallery_images(1, 4, $display, 'both');

/**
* Start output the page
*/

$template->assign_vars(array(
	'U_YOUR_PERSONAL_GALLERY' 		=> (!$album_config['personal_album_index'] && gallery_acl_check('i_upload', OWN_GALLERY_PERMISSIONS)) ? ($user->gallery['personal_album_id'] > 0) ? append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $user->gallery['personal_album_id']) : append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=gallery&amp;mode=manage_albums') : '',
	'U_USERS_PERSONAL_GALLERIES' 	=> (!$album_config['personal_album_index'] &&gallery_acl_check('a_list', PERSONAL_GALLERY_PERMISSIONS)) ? append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx", 'mode=personal') : '',

	'S_LOGIN_ACTION'				=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login&amp;redirect=' . urlencode("{$gallery_root_path}index.$phpEx" . (($mode == 'personal') ? '?mode=personal' : ''))),
	'S_COLS' 						=> $album_config['cols_per_page'],
	'S_COL_WIDTH' 					=> (100/$album_config['cols_per_page']) . '%',
));

// Output page
$page_title = $user->lang['GALLERY'];

page_header($page_title);

$template->set_filenames(array(
	'body' => 'gallery_index_body.html')
);

page_footer();
?>