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

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');
$user->setup('mods/gallery_ucp');

// Get general album information
include($phpbb_root_path . $gallery_root_path . 'includes/common.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/permissions.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/functions_display.' . $phpEx);

/**
* Check the request
*/
$user_id	= request_var('user_id', 0);
$album_id	= request_var('album_id', 0);
$start		= request_var('start', 0);
$mode		= request_var('mode', '');
$sort_days	= request_var('st', 0);
$sort_key	= request_var('sk', $gallery_config['sort_method']);
$sort_dir	= request_var('sd', $gallery_config['sort_order']);
$album_data	= get_album_info($album_id);

// End contest
if ($album_data['contest_id'] && $album_data['contest_marked'] && (($album_data['contest_start'] + $album_data['contest_end']) < time()))
{
	$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
		SET image_contest = ' . IMAGE_NO_CONTEST . '
		WHERE image_album_id = ' . (int) $album_id;
	$db->sql_query($sql);

	$sql = 'SELECT image_id
		FROM ' . GALLERY_IMAGES_TABLE . '
		WHERE image_album_id = ' . $album_id . '
		ORDER BY image_rate_avg DESC';
	$result = $db->sql_query_limit($sql, 3);
	$first = (int) $db->sql_fetchfield('image_id');
	$second = (int) $db->sql_fetchfield('image_id');
	$third = (int) $db->sql_fetchfield('image_id');
	$db->sql_freeresult($result);

	$sql = 'UPDATE ' . GALLERY_CONTESTS_TABLE . '
		SET contest_marked = ' . IMAGE_NO_CONTEST . ",
			contest_first = $first,
			contest_second = $second,
			contest_third = $third
		WHERE contest_id = " . (int) $album_data['contest_id'];
	$db->sql_query($sql);
	$contest_end_time = $album_data['contest_start'] + $album_data['contest_end'];
	$sql_update = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
		SET image_contest_end = ' . $contest_end_time . ',
			image_contest_rank = 1
		WHERE image_id = ' . $first;
	$db->sql_query($sql_update);
	$sql_update = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
		SET image_contest_end = ' . $contest_end_time . ',
			image_contest_rank = 2
		WHERE image_id = ' . $second;
	$db->sql_query($sql_update);
	$sql_update = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
		SET image_contest_end = ' . $contest_end_time . ',
			image_contest_rank = 3
		WHERE image_id = ' . $third;
	$db->sql_query($sql_update);

	set_gallery_config('contests_ended', $gallery_config['contests_ended'] + 1);

	$album_data['contest_marked'] = IMAGE_NO_CONTEST;
}

/**
* Build auth-list
*/
gen_album_auth_level('album', $album_id, $album_data['album_status']);
if (!gallery_acl_check('i_view', $album_id))
{
	if ($user->data['is_bot'])
	{
		redirect(append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx"));
	}
	if (!$user->data['is_registered'])
	{
		login_box();
	}
	else
	{
		trigger_error('NOT_AUTHORISED');
	}
}

// Build the navigation & display subalbums
generate_album_nav($album_data);
display_albums($album_data);

// Set some variables to their defaults
$allowed_create = false;
$image_counter = 0;
$l_moderator = $moderators_list = $s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
$grouprows = $album_moderators = array();
$images_per_page = $gallery_config['rows_per_page'] * $gallery_config['cols_per_page'];

/**
* We have album_type so that there may be images ...
*/
if ($album_data['album_type'] != ALBUM_CAT)
{
	if (gallery_acl_check('m_', $album_id))
	{
		$template->assign_var('U_MCP', append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "album_id=$album_id"));
	}

	// When we do the slideshow, we don't need the moderators
	if ($mode != 'slide_show')
	{
		get_album_moderators($album_moderators, $album_id);
		if (!empty($album_moderators[$album_id]))
		{
			$l_moderator = (sizeof($album_moderators[$album_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
			$moderators_list = implode(', ', $album_moderators[$album_id]);
		}
	}

	/**
	* Build the sort options
	*/
	$limit_days = array(0 => $user->lang['ALL_IMAGES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
	$sort_by_text = array('t' => $user->lang['TIME'], 'n' => $user->lang['IMAGE_NAME'], 'vc' => $user->lang['VIEWS']);
	$sort_by_sql = array('t' => 'image_time', 'n' => 'image_name', 'vc' => 'image_view_count');

	// Do not sort images after upload-username on running contests
	if ($album_data['contest_marked'] != IMAGE_CONTEST)
	{
		$sort_by_text['u'] = $user->lang['SORT_USERNAME'];
		$sort_by_sql['u'] = 'image_username';
	}
	if ($gallery_config['allow_rates'])
	{
		$sort_by_text['ra'] = $user->lang['RATING'];
		$sort_by_sql['ra'] = 'image_rate_avg';
		$sort_by_text['r'] = $user->lang['RATES_COUNT'];
		$sort_by_sql['r'] = 'image_rates';
	}
	if ($gallery_config['allow_comments'])
	{
		$sort_by_text['c'] = $user->lang['COMMENTS'];
		$sort_by_sql['c'] = 'image_comments';
		$sort_by_text['lc'] = $user->lang['NEW_COMMENT'];
		$sort_by_sql['lc'] = 'image_last_comment';
	}
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
	$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

	if ($album_data['album_images_real'] > 0)
	{
		$image_status_check = ' AND image_status = ' . IMAGE_APPROVED;
		$image_counter = $album_data['album_images'];
		if (gallery_acl_check('m_status', $album_id))
		{
			$image_status_check = '';
			$image_counter = $album_data['album_images_real'];
		}

		$images = array();
		$sql = 'SELECT *
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_album_id = ' . (int) $album_id . "
				$image_status_check
			ORDER BY $sql_sort_order";

		if ($mode == 'slide_show')
		{
			/**
			* Slideshow - Using message_body.html
			*/
			$result = $db->sql_query($sql);

			if (file_exists($phpbb_root_path . 'styles/' . $user->theme['template_path'] . '/theme/highslide/highslide-full.js'))
			{
				$trigger_message = $user->lang['SLIDE_SHOW_HIGHSLIDE'];
				while ($row = $db->sql_fetchrow($result))
				{
					$images[] = generate_image_link('image_name', 'highslide', $row['image_id'], $row['image_name'], $row['image_album_id']);
				}
			}
			else
			{
				$trigger_message = $user->lang['SLIDE_SHOW_START'];
				while ($row = $db->sql_fetchrow($result))
				{
					$images[] = generate_image_link('image_name', 'lytebox_slide_show', $row['image_id'], $row['image_name'], $row['image_album_id']);
				}
			}
			$db->sql_freeresult($result);

			$template->assign_vars(array(
				'MESSAGE_TITLE'		=> $user->lang['SLIDE_SHOW'],
				'MESSAGE_TEXT'		=> $trigger_message . '<br /><br />' . implode(', ', $images),
			));

			page_header($user->lang['SLIDE_SHOW']);
			$template->set_filenames(array(
				'body' => 'message_body.html')
			);
			page_footer();
		}
		else
		{
			$result = $db->sql_query_limit($sql, $images_per_page, $start);
		}

		while ($row = $db->sql_fetchrow($result))
		{
			$images[] = $row;
		}
		$db->sql_freeresult($result);

		for ($i = 0; $i < count($images); $i += $gallery_config['cols_per_page'])
		{
			$template->assign_block_vars('image_row', array());

			for ($j = $i; $j < ($i + $gallery_config['cols_per_page']); $j++)
			{
				if ($j >= count($images))
				{
					$template->assign_block_vars('image_row.no_image', array());
					continue;
				}

				// Assign the image to the template-block
				assign_image_block('image_row.image', $images[$j], $album_data['album_status']);
			}
		}
	}

	// Is it a personal album, and does the user have permissions to create more?
	if ($album_data['album_user_id'] == $user->data['user_id'])
	{
		if (gallery_acl_check('i_upload', OWN_GALLERY_PERMISSIONS))
		{
			$sql = 'SELECT COUNT(album_id) albums
				FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE album_user_id = ' . $user->data['user_id'];
			$result = $db->sql_query($sql);
			$albums = (int) $db->sql_fetchfield('albums');
			$db->sql_freeresult($result);

			if ($albums < gallery_acl_check('album_count', OWN_GALLERY_PERMISSIONS))
			{
				$allowed_create = true;
			}
		}
	}
}// End of "We have album_type so that there may be images ..."

$template->assign_vars(array(
	'S_IS_POSTABLE'				=> ($album_data['album_type'] != ALBUM_CAT) ? true : false,
	'S_IS_LOCKED'				=> ($album_data['album_status'] == ITEM_LOCKED) ? true : false,
	'UPLOAD_IMG'				=> ($album_data['album_status'] == ITEM_LOCKED) ? $user->img('button_topic_locked', 'ALBUM_LOCKED') : $user->img('button_upload_image', 'UPLOAD_IMAGE'),
	'S_MODE'					=> $album_data['album_type'],
	'L_MODERATORS'				=> $l_moderator,
	'MODERATORS'				=> $moderators_list,

	'U_UPLOAD_IMAGE'			=> (!$album_data['album_user_id'] || ($album_data['album_user_id'] == $user->data['user_id'])) ?
										append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=upload&amp;album_id=$album_id") : '',
	'U_CREATE_ALBUM'			=> (($album_data['album_user_id'] == $user->data['user_id']) && $allowed_create) ?
										append_sid("{$phpbb_root_path}ucp.$phpEx", "i=gallery&amp;mode=manage_albums&amp;action=create&amp;parent_id=$album_id&amp;redirect=album") : '',
	'U_EDIT_ALBUM'				=> ($album_data['album_user_id'] == $user->data['user_id']) ?
										append_sid("{$phpbb_root_path}ucp.$phpEx", "i=gallery&amp;mode=manage_albums&amp;action=edit&amp;album_id=$album_id&amp;redirect=album") : '',
	'U_SLIDE_SHOW'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id&amp;mode=slide_show"),

	'S_THUMBNAIL_SIZE'			=> $gallery_config['thumbnail_size'] + 20 + (($gallery_config['thumbnail_info_line']) ? THUMBNAIL_INFO_HEIGHT : 0),
	'S_COLS'					=> $gallery_config['cols_per_page'],
	'S_COL_WIDTH'				=> (100 / $gallery_config['cols_per_page']) . '%',
	'S_JUMPBOX_ACTION'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx"),
	'S_ALBUM_ACTION'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id"),

	'S_SELECT_SORT_DIR'			=> $s_sort_dir,
	'S_SELECT_SORT_KEY'			=> $s_sort_key,

	'ALBUM_JUMPBOX'				=> gallery_albumbox(false, '', $album_id),
	'U_RETURN_LINK'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx"),
	'S_RETURN_LINK'				=> $user->lang['GALLERY'],

	'PAGINATION'				=> generate_pagination(append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id&amp;sk=$sort_key&amp;sd=$sort_dir&amp;st=$sort_days"), $image_counter, $images_per_page, $start),
	'TOTAL_IMAGES'				=> ($image_counter == 1) ? $user->lang['IMAGE_#'] : sprintf($user->lang['IMAGES_#'], $image_counter),
	'PAGE_NUMBER'				=> on_page($image_counter, $images_per_page, $start),

	'L_WATCH_TOPIC'				=> ($album_data['watch_id']) ? $user->lang['UNWATCH_ALBUM'] : $user->lang['WATCH_ALBUM'],
	'U_WATCH_TOPIC'				=> (($album_data['album_type'] != ALBUM_CAT) && ($user->data['user_id'] != ANONYMOUS)) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=album&amp;submode=" . (($album_data['watch_id']) ?  'unwatch' : 'watch') . "&amp;album_id=$album_id") : '',
	'S_WATCHING_TOPIC'			=> ($album_data['watch_id']) ? true : false,
));

/**
* Cheat on phpBB #31975
* Once we will get the normal function pumped up for the external use.
*
* borrowed from phpBB3
* @author: phpBB Group
* @functions: obtain_guest_count, obtain_users_online and obtain_users_online_string
*/
function cheat_obtain_guest_count($id = 0, $mode = 'forum')
{
	global $db, $config;

	if ($id)
	{
		$reading_sql = ' AND s.session_'. $mode. '_id = ' . (int) $id;
	}
	else
	{
		$reading_sql = '';
	}
	$time = (time() - (intval($config['load_online_time']) * 60));

	// Get number of online guests

	if ($db->sql_layer === 'sqlite')
	{
		$sql = 'SELECT COUNT(session_ip) as num_guests
			FROM (
				SELECT DISTINCT s.session_ip
				FROM ' . SESSIONS_TABLE . ' s
				WHERE s.session_user_id = ' . ANONYMOUS . '
					AND s.session_time >= ' . ($time - ((int) ($time % 60))) .
				$reading_sql .
			')';
	}
	else
	{
		$sql = 'SELECT COUNT(DISTINCT s.session_ip) as num_guests
			FROM ' . SESSIONS_TABLE . ' s
			WHERE s.session_user_id = ' . ANONYMOUS . '
				AND s.session_time >= ' . ($time - ((int) ($time % 60))) .
			$reading_sql;
	}
	$result = $db->sql_query($sql, 60);
	$guests_online = (int) $db->sql_fetchfield('num_guests');
	$db->sql_freeresult($result);

	return $guests_online;
}

function cheat_obtain_users_online($id = 0, $mode = 'forum')
{
	global $db, $config, $user;

	$reading_sql = '';
	if ($id !== 0)
	{
		$reading_sql = ' AND s.session_'. $mode. '_id = ' . (int) $id;
	}

	$online_users = array(
		'online_users'			=> array(),
		'hidden_users'			=> array(),
		'total_online'			=> 0,
		'visible_online'		=> 0,
		'hidden_online'			=> 0,
		'guests_online'			=> 0,
	);

	if ($config['load_online_guests'])
	{
		$online_users['guests_online'] = cheat_obtain_guest_count($id, $mode);
	}

	// a little discrete magic to cache this for 30 seconds
	$time = (time() - (intval($config['load_online_time']) * 60));

	$sql = 'SELECT s.session_user_id, s.session_ip, s.session_viewonline
		FROM ' . SESSIONS_TABLE . ' s
		WHERE s.session_time >= ' . ($time - ((int) ($time % 30))) .
			$reading_sql .
		' AND s.session_user_id <> ' . ANONYMOUS;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		// Skip multiple sessions for one user
		if (!isset($online_users['online_users'][$row['session_user_id']]))
		{
			$online_users['online_users'][$row['session_user_id']] = (int) $row['session_user_id'];
			if ($row['session_viewonline'])
			{
				$online_users['visible_online']++;
			}
			else
			{
				$online_users['hidden_users'][$row['session_user_id']] = (int) $row['session_user_id'];
				$online_users['hidden_online']++;
			}
		}
	}
	$online_users['total_online'] = $online_users['guests_online'] + $online_users['visible_online'] + $online_users['hidden_online'];
	$db->sql_freeresult($result);

	return $online_users;
}

function cheat_obtain_users_online_string($online_users, $id = 0, $mode = 'forum')
{
	global $config, $db, $user, $auth;

	$user_online_link = $online_userlist = '';
	// for the language-string
	$caps_mode = strtoupper($mode);

	if (sizeof($online_users['online_users']))
	{
		$sql = 'SELECT username, username_clean, user_id, user_type, user_allow_viewonline, user_colour
				FROM ' . USERS_TABLE . '
				WHERE ' . $db->sql_in_set('user_id', $online_users['online_users']) . '
				ORDER BY username_clean ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// User is logged in and therefore not a guest
			if ($row['user_id'] != ANONYMOUS)
			{
				if (isset($online_users['hidden_users'][$row['user_id']]))
				{
					$row['username'] = '<em>' . $row['username'] . '</em>';
				}

				if (!isset($online_users['hidden_users'][$row['user_id']]) || $auth->acl_get('u_viewonline'))
				{
					$user_online_link = get_username_string(($row['user_type'] <> USER_IGNORE) ? 'full' : 'no_profile', $row['user_id'], $row['username'], $row['user_colour']);
					$online_userlist .= ($online_userlist != '') ? ', ' . $user_online_link : $user_online_link;
				}
			}
		}
		$db->sql_freeresult($result);
	}

	if (!$online_userlist)
	{
		$online_userlist = $user->lang['NO_ONLINE_USERS'];
	}

	if ($id === 0)
	{
		$online_userlist = $user->lang['REGISTERED_USERS'] . ' ' . $online_userlist;
	}
	else if ($config['load_online_guests'])
	{
		$l_online = ($online_users['guests_online'] === 1) ? $user->lang['BROWSING_' . $caps_mode . '_GUEST'] : $user->lang['BROWSING_' . $caps_mode . '_GUESTS'];
		$online_userlist = sprintf($l_online, $online_userlist, $online_users['guests_online']);
	}
	else
	{
		$online_userlist = sprintf($user->lang['BROWSING_' . $caps_mode], $online_userlist);
	}
	// Build online listing
	$vars_online = array(
		'ONLINE'	=> array('total_online', 'l_t_user_s', 0),
		'REG'		=> array('visible_online', 'l_r_user_s', !$config['load_online_guests']),
		'HIDDEN'	=> array('hidden_online', 'l_h_user_s', $config['load_online_guests']),
		'GUEST'		=> array('guests_online', 'l_g_user_s', 0)
	);

	foreach ($vars_online as $l_prefix => $var_ary)
	{
		if ($var_ary[2])
		{
			$l_suffix = '_AND';
		}
		else
		{
			$l_suffix = '';
		}
		switch ($online_users[$var_ary[0]])
		{
			case 0:
				${$var_ary[1]} = $user->lang[$l_prefix . '_USERS_ZERO_TOTAL' . $l_suffix];
			break;

			case 1:
				${$var_ary[1]} = $user->lang[$l_prefix . '_USER_TOTAL' . $l_suffix];
			break;

			default:
				${$var_ary[1]} = $user->lang[$l_prefix . '_USERS_TOTAL' . $l_suffix];
			break;
		}
	}
	unset($vars_online);

	$l_online_users = sprintf($l_t_user_s, $online_users['total_online']);
	$l_online_users .= sprintf($l_r_user_s, $online_users['visible_online']);
	$l_online_users .= sprintf($l_h_user_s, $online_users['hidden_online']);

	if ($config['load_online_guests'])
	{
		$l_online_users .= sprintf($l_g_user_s, $online_users['guests_online']);
	}



	return array(
		'online_userlist'	=> $online_userlist,
		'l_online_users'	=> $l_online_users,
	);
}
if ($config['load_online'] && $config['load_online_time'])
{
	$who_is_online_mode = 'forum';
	$f = request_var('f', 0);
	$album_id = request_var('album_id', 0);
	if ($album_id > 0)
	{
		$who_is_online_mode = 'album';
		$f = $album_id;
	}
	$f = max($f, 0);
	$online_users = cheat_obtain_users_online($f, $who_is_online_mode);
	$user_online_strings = cheat_obtain_users_online_string($online_users, $f, $who_is_online_mode);

	$l_online_users = $user_online_strings['l_online_users'];
	$online_userlist = $user_online_strings['online_userlist'];
	$total_online_users = $online_users['total_online'];

	$l_online_time = ($config['load_online_time'] == 1) ? 'VIEW_ONLINE_TIME' : 'VIEW_ONLINE_TIMES';
	$l_online_time = sprintf($user->lang[$l_online_time], $config['load_online_time']);
	$template->assign_vars(array(
		'CHEAT_LOGGED_IN_USER_LIST'			=> $online_userlist,
	));
}

/**
* END of Cheating
*/

page_header($user->lang['VIEW_ALBUM'] . ' - ' . $album_data['album_name']);

$template->set_filenames(array(
	'body' => 'gallery/album_body.html')
);

page_footer();

?>