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

function integrate_memberlist_viewprofile (&$member)
{
	global $config, $db, $template, $user;
	global $gallery_root_path, $phpbb_root_path, $phpEx;
	$user->add_lang('mods/gallery');

	if (!function_exists('load_gallery_config'))
	{
		$recent_image_addon = true;
		include($phpbb_root_path . $gallery_root_path . 'includes/common.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/permissions.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/functions_recent.' . $phpEx);
	}

	$user_id = $member['user_id'];
	$memberdays = max(1, round((time() - $member['user_regdate']) / 86400));

	$member_gallery = array('user_images' => 0, 'personal_album_id' => 0);
	$sql = 'SELECT user_images, personal_album_id
		FROM ' . GALLERY_USERS_TABLE . '
		WHERE user_id = ' . $user_id;
	$result = $db->sql_query_limit($sql, 1);
	$member_gallery = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	$member = array_merge($member, $member_gallery);

	$images_per_day = $member['user_images'] / $memberdays;
	$percentage_images = ($config['num_images']) ? min(100, ($member['user_images'] / $config['num_images']) * 100) : 0;

	$ints = array(
		'rows'		=> $gallery_config['rrc_profile_rows'],
		'columns'	=> $gallery_config['rrc_profile_columns'],
		'comments'	=> 0,
		'contests'	=> 0,
	);
	$display = array(
		'name'		=> true,
		'poster'	=> true,
		'time'		=> true,
		'views'		=> true,
		'ratings'	=> true,
		'comments'	=> true,
		'album'		=> true,
	);
	if ($gallery_config['rrc_profile_mode'] != '!all')
	{
		recent_gallery_images($ints, $display, $gallery_config['rrc_profile_mode'], false, $user_id);
	}

	$template->assign_vars(array(
		'TOTAL_IMAGES'		=> $gallery_config['user_images_profile'],
		'IMAGES'			=> $member['user_images'],
		'IMAGES_DAY'		=> sprintf($user->lang['IMAGE_DAY'], $images_per_day),
		'IMAGES_PCT'		=> sprintf($user->lang['IMAGE_PCT'], $percentage_images),

		'SHOW_PERSONAL_ALBUM_OF'	=> sprintf($user->lang['SHOW_PERSONAL_ALBUM_OF'], $member['username']),
		'U_GALLERY'			=> ($member['personal_album_id'] && $gallery_config['personal_album_profile']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $member['personal_album_id']) : '',
		'U_SEARCH_GALLERY'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", 'user_id=' . $user_id),
	));
}

function integrate_viewonline ($on_page, $album_id, $session_page)
{
	global $album_data, $config, $cache, $db, $template, $user;
	global $gallery_root_path, $phpbb_root_path, $phpEx;
	global $location, $location_url;

	// Initial load of some needed stuff, like permissions, album data, ...
	if (!function_exists('load_gallery_config'))
	{
		$recent_image_addon = true;
		include($phpbb_root_path . $gallery_root_path . 'includes/common.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/permissions.' . $phpEx);
	}
	if (!isset($album_data))
	{
		$user->add_lang('mods/gallery');
		$album_data = $cache->obtain_album_list();
	}

	// Handle user location
	$location = $user->lang['GALLERY'];
	$location_url = append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx");

	if ($album_id && gallery_acl_check('i_view', $album_id))
	{
		switch ($on_page[1])
		{
			case $gallery_root_path . 'album':
				$location = sprintf($user->lang['VIEWING_ALBUM'], $album_data[$album_id]['album_name']);
				$location_url = append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $album_id);
			break;

			case $gallery_root_path . 'image_page':
			case $gallery_root_path . 'image':
				$location = sprintf($user->lang['VIEWING_IMAGE'], $album_data[$album_id]['album_name']);
				$location_url = append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $album_id);
			break;

			case $gallery_root_path . 'posting':
				preg_match('#mode=([a-z]+)#', $row['session_page'], $on_page);
				$on_page = (sizeof($on_page)) ? $on_page[1] : '';

				switch ($on_page)
				{
					case 'comment':
						$location = sprintf($user->lang['COMMENT_IMAGE'], $album_data[$album_id]['album_name']);
					break;

					default:
						$location = sprintf($user->lang['VIEWING_ALBUM'], $album_data[$album_id]['album_name']);
					break;
				}
				$location_url = append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $album_id);
			break;
		}
	}
	else
	{
		preg_match('#mode=([a-z]+)#', $session_page, $on_page);
		echo $on_page[1];
		echo gallery_acl_check('i_view', PERSONAL_GALLERY_PERMISSIONS); 
		$on_page = (sizeof($on_page)) ? $on_page[1] : '';
		if (($on_page == 'personal') && (gallery_acl_check('i_view', PERSONAL_GALLERY_PERMISSIONS)))
		{
			$location = $user->lang['PERSONAL_ALBUMS'];
			$location_url = append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx", 'mode=personal');
		}
	}
}

?>