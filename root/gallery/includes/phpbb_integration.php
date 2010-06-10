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
	global $auth, $db, $template, $user;
	$user->add_lang('mods/gallery');

	if (!class_exists('phpbb_gallery'))
	{
		global $phpbb_root_path, $phpEx;
		include('core.' . $phpEx);
		phpbb_gallery::init('no_setup', $phpbb_root_path);
	}
	phpbb_gallery::_include('functions_recent');

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
	$percentage_images = (phpbb_gallery_config::get('num_images')) ? min(100, ($member['user_images'] / phpbb_gallery_config::get('num_images')) * 100) : 0;

	$ints = array(
		'rows'		=> phpbb_gallery_config::get('rrc_profile_rows'),
		'columns'	=> phpbb_gallery_config::get('rrc_profile_columns'),
		'comments'	=> 0,
		'contests'	=> 0,
	);
	if (phpbb_gallery_config::get('rrc_profile_mode'))
	{
		recent_gallery_images($ints, phpbb_gallery_config::get('rrc_profile_display'), phpbb_gallery_config::get('rrc_profile_mode'), false, phpbb_gallery_config::get('rrc_profile_pegas'), 'user', $user_id);
	}

	$template->assign_vars(array(
		'TOTAL_IMAGES'		=> phpbb_gallery_config::get('profile_user_images'),
		'IMAGES'			=> $member['user_images'],
		'IMAGES_DAY'		=> sprintf($user->lang['IMAGE_DAY'], $images_per_day),
		'IMAGES_PCT'		=> sprintf($user->lang['IMAGE_PCT'], $percentage_images),

		'SHOW_PERSONAL_ALBUM_OF'	=> sprintf($user->lang['SHOW_PERSONAL_ALBUM_OF'], $member['username']),
		'U_GALLERY'			=> ($member['personal_album_id'] && phpbb_gallery_config::get('profile_pega')) ? phpbb_gallery::append_sid('album', 'album_id=' . $member['personal_album_id']) : '',
		'U_SEARCH_GALLERY'	=> phpbb_gallery::append_sid('search', 'user_id=' . $user_id),
	));
}

function integrate_viewonline($on_page, $album_id, $session_page)
{
	// Some of the globals may not be used here, but in the included files
	global $auth, $album_data, $cache, $db, $template, $user;
	global $location, $location_url;

	// Initial load of some needed stuff, like permissions, album data, ...
	if (!class_exists('phpbb_gallery'))
	{
		global $phpbb_root_path, $phpEx;
		include('core.' . $phpEx);
		phpbb_gallery::init('no_setup', $phpbb_root_path);
	}
	if (!isset($album_data))
	{
		$user->add_lang(array('mods/info_acp_gallery', 'mods/gallery'));
		$album_data = $cache->obtain_album_list();
	}

	// Handle user location
	$location = $user->lang['GALLERY'];
	$location_url = phpbb_gallery::append_sid('index');

	if ($album_id && phpbb_gallery::$auth->acl_check('i_view', $album_id))
	{
		switch ($on_page[1])
		{
			case phpbb_gallery::path('relative') . 'album':
				$location = sprintf($user->lang['VIEWING_ALBUM'], $album_data[$album_id]['album_name']);
				$location_url = phpbb_gallery::append_sid('album', 'album_id=' . $album_id);
			break;

			case phpbb_gallery::path('relative') . 'image_page':
			case phpbb_gallery::path('relative') . 'image':
				$location = sprintf($user->lang['VIEWING_IMAGE'], $album_data[$album_id]['album_name']);
				$location_url = phpbb_gallery::append_sid('album', 'album_id=' . $album_id);
			break;

			case phpbb_gallery::path('relative') . 'posting':
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
				$location_url = phpbb_gallery::append_sid('album', 'album_id=' . $album_id);
			break;
		}
	}
	else
	{
		preg_match('#mode=([a-z]+)#', $session_page, $on_page);
		$on_page = (sizeof($on_page)) ? $on_page[1] : '';
		if (($on_page == 'personal') && (phpbb_gallery::$auth->acl_check('i_view', PERSONAL_GALLERY_PERMISSIONS)))
		{
			$location = $user->lang['PERSONAL_ALBUMS'];
			$location_url = phpbb_gallery::append_sid('index', 'mode=personal');
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
	global $db, $cache;

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

		if (!class_exists('phpbb_gallery'))
		{
			global $phpbb_root_path, $phpEx;
			include('core.' . $phpEx);
			//phpbb_gallery::init('no_setup', $phpbb_root_path);
		}

		if (in_array(phpbb_gallery_config::get('newest_pega_user_id'), $user_id_ary))
		{
			phpbb_gallery_config::set('newest_pega_user_colour', $sql_ary['user_colour']);
		}
	}
}

?>