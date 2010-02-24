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

function integrate_memberlist_viewprofile(&$member)
{
	// Some of the globals may not be used here, but in the included files
	global $auth, $config, $db, $gallery_config, $template, $user;
	global $gallery_root_path, $phpbb_admin_path, $phpbb_root_path, $phpEx;
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

	$sql = 'SELECT user_images, personal_album_id
		FROM ' . GALLERY_USERS_TABLE . '
		WHERE user_id = ' . $user_id;
	$result = $db->sql_query_limit($sql, 1);
	$member_gallery = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	if (!$member_gallery)
	{
		$member_gallery = array('user_images' => 0, 'personal_album_id' => 0);
	}
	$member = array_merge($member, $member_gallery);

	$images_per_day = $member['user_images'] / $memberdays;
	$percentage_images = ($config['num_images']) ? min(100, ($member['user_images'] / $config['num_images']) * 100) : 0;

	$ints = array(
		'rows'		=> $gallery_config['rrc_profile_rows'],
		'columns'	=> $gallery_config['rrc_profile_columns'],
		'comments'	=> 0,
		'contests'	=> 0,
	);
	if ($gallery_config['rrc_profile_mode'])
	{
		recent_gallery_images($ints, $gallery_config['rrc_profile_display'], $gallery_config['rrc_profile_mode'], false, $gallery_config['rrc_profile_pgalleries'], 'user', $user_id);
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

function integrate_viewonline($on_page, $album_id, $session_page)
{
	// Some of the globals may not be used here, but in the included files
	global $auth, $album_data, $config, $cache, $db, $template, $user;
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
				preg_match('#mode=([a-z]+)#', $session_page, $on_page);
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
		$on_page = (sizeof($on_page)) ? $on_page[1] : '';
		if (($on_page == 'personal') && (gallery_acl_check('i_view', PERSONAL_GALLERY_PERMISSIONS)))
		{
			$location = $user->lang['PERSONAL_ALBUMS'];
			$location_url = append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx", 'mode=personal');
		}
	}
}

/**
* Updates a username across all relevant tables/fields
*
* @param string $old_name the old/current username
* @param string $new_name the new username
*
* borrowed from phpBB3
* @author: phpBB Group
* @function: user_update_name
*/
function gallery_integrate_user_update_name($old_name, $new_name)
{
	global $config, $db, $cache;

	$update_ary = array(
		GALLERY_ALBUMS_TABLE	=> array('album_last_username'),
		GALLERY_COMMENTS_TABLE	=> array('comment_username'),
		GALLERY_IMAGES_TABLE	=> array('image_username'),
	);

	foreach ($update_ary as $table => $field_ary)
	{
		foreach ($field_ary as $field)
		{
			$sql = "UPDATE $table
				SET $field = '" . $db->sql_escape($new_name) . "'
				WHERE $field = '" . $db->sql_escape($old_name) . "'";
			$db->sql_query($sql);
		}
	}

	$update_clean_ary = array(
		GALLERY_IMAGES_TABLE	=> array('image_username_clean'),
	);

	foreach ($update_clean_ary as $table => $field_ary)
	{
		foreach ($field_ary as $field)
		{
			$sql = "UPDATE $table
				SET $field = '" . $db->sql_escape(utf8_clean_string($new_name)) . "'
				WHERE $field = '" . $db->sql_escape(utf8_clean_string($old_name)) . "'";
			$db->sql_query($sql);
		}
	}

	$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
		SET album_name = '" . $db->sql_escape($new_name) . "'
		WHERE album_name = '" . $db->sql_escape($old_name) . "'
			AND album_user_id <> 0
			AND parent_id = 0";
	$db->sql_query($sql);

	$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
		SET album_parents = ''";
	$db->sql_query($sql);

	// Because some tables/caches use username-specific data we need to purge this here.
	$cache->destroy('_albums');
	$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
	$cache->destroy('sql', GALLERY_MODSCACHE_TABLE);
}

/**
* Set users default group
*
* @access private
*
* borrowed from phpBB3
* @author: phpBB Group
* @function: group_set_user_default
*/
function gallery_integrate_group_set_user_default($user_id_ary, $sql_ary)
{
	global $db;

	if (empty($user_id_ary))
	{
		return;
	}

	if (isset($sql_ary['user_colour']))
	{
		// Update any cached colour information for these users
		$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . " SET album_last_user_colour = '" . $db->sql_escape($sql_ary['user_colour']) . "'
			WHERE " . $db->sql_in_set('album_last_user_id', $user_id_ary);
		$db->sql_query($sql);

		$sql = 'UPDATE ' . GALLERY_COMMENTS_TABLE . " SET comment_user_colour = '" . $db->sql_escape($sql_ary['user_colour']) . "'
			WHERE " . $db->sql_in_set('comment_user_id', $user_id_ary);
		$db->sql_query($sql);

		$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . " SET image_user_colour = '" . $db->sql_escape($sql_ary['user_colour']) . "'
			WHERE " . $db->sql_in_set('image_user_id', $user_id_ary);
		$db->sql_query($sql);

		global $gallery_config;

		if ($gallery_config === null)
		{
			$db->sql_return_on_error(true);
			$sql = 'SELECT *
				FROM ' . GALLERY_CONFIG_TABLE;
			$result = $db->sql_query($sql);

			$gallery_config = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$gallery_config[$row['config_name']] = $row['config_value'];
			}
			$db->sql_freeresult($result);

			$db->sql_return_on_error(false);
		}

		if (isset($gallery_config['newest_pgallery_user_id']) && in_array($gallery_config['newest_pgallery_user_id'], $user_id_ary))
		{
			if (!function_exists('set_gallery_config'))
			{
				global $phpbb_root_path, $phpEx, $user;
				include($phpbb_root_path . GALLERY_ROOT_PATH . 'includes/functions.' . $phpEx);
			}
			set_gallery_config('newest_pgallery_user_colour', $sql_ary['user_colour'], true);
		}
	}
}

?>