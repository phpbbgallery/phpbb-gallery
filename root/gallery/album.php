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
$user->setup('mods/gallery_ucp');

// Get general album information
include_once("{$phpbb_root_path}{$gallery_root_path}includes/common.$phpEx");
include_once("{$phpbb_root_path}{$gallery_root_path}includes/permissions.$phpEx");
$album_access_array = get_album_access_array();

// ------------------------------------
// Check the request
// ------------------------------------
$user_id = request_var('user_id', 0);
$album_id = request_var('album_id', request_var('id', 0));

if(!$album_id)
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}
$moderators_list = '';
$total_pics = 0;
$album_data = $catrows = array();
$album_data = get_album_info($album_id);
if (empty($album_data))
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}

/**
* Build Auth List
*/
gen_album_auth_level('album', $album_id, 0 /*replace with $album_data['album_status'] later*/);

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
		trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
	}
}

/**
* Build Album-Index
*/
include("{$phpbb_root_path}{$gallery_root_path}includes/functions_display.$phpEx");
display_albums($album_data);
if ($album_id <> 0)
{
	generate_album_nav($album_data);
}
/*if ($album_data['album_type'])
{ we just do this, when we have images */
	if (gallery_acl_check('m_', $album_id))
	{
		$template->assign_vars(array(
			'U_MCP'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?album_id=$album_id"),
		));
	}
	$grouprows = array();
	$album_moderators = array();
	get_album_moderators($album_moderators, $album_id);
	$l_moderator = $moderators_list = '';
	if (!empty($album_moderators[$album_id]))
	{
		$l_moderator = (sizeof($album_moderators[$album_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
		$moderators_list = implode(', ', $album_moderators[$album_id]);
	}

	/**
	* Build the thumbnail page
	*/
	$start = request_var('start', 0);
	$pics_per_page = $album_config['rows_per_page'] * $album_config['cols_per_page'];
	$tot_unapproved = $image_counter = 0;

	$sort_days	= request_var('st', 0);
	$sort_key	= request_var('sk', $album_config['sort_method']);
	$sort_dir	= request_var('sd', $album_config['sort_order']);
	$limit_days = array(0 => $user->lang['ALL_IMAGES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);

	$sort_by_text = array('t' => $user->lang['TIME'], 'n' => $user->lang['IMAGE_NAME'], 'u' => $user->lang['SORT_USERNAME'], 'vc' => $user->lang['VIEWS']);
	$sort_by_sql = array('t' => 'image_time', 'n' => 'image_name', 'u' => 'image_username', 'vc' => 'image_view_count');

	if ($album_config['rate'] == 1)
	{
		$sort_by_text['ra'] = $user->lang['RATING'];
		$sort_by_sql['ra'] = 'image_rate_avg';
		$sort_by_text['r'] = $user->lang['RATES_COUNT'];
		$sort_by_sql['r'] = 'image_rates';
	}
	if ($album_config['comment'] == 1)
	{
		$sort_by_text['c'] = $user->lang['COMMENTS'];
		$sort_by_sql['c'] = 'image_comments';
		$sort_by_text['lc'] = $user->lang['NEW_COMMENT'];
		$sort_by_sql['lc'] = 'image_last_comment';
	}
	$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
	$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

	if ($album_data['album_images_real'] > 0)
	{
		$image_status_check = ' AND image_status = 1';
		$image_counter = $album_data['album_images'];
		if (gallery_acl_check('m_status', $album_id))
		{
			$image_status_check = '';
			$image_counter = $album_data['album_images_real'];
		}

		$sql = 'SELECT *
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_album_id = ' . (int) $album_id . "
				$image_status_check
			ORDER BY $sql_sort_order";
		if (request_var('mode', '') == 'slide_show')
		{
			$result = $db->sql_query($sql);
			if (file_exists($phpbb_root_path . 'styles/' . $user->theme['template_path'] . '/theme/highslide/highslide-full.js'))
			{
				$trigger_message = $user->lang['SLIDE_SHOW_HIGHSLIDE'];
				while ($row = $db->sql_fetchrow($result))
				{
					$picrow[] = generate_image_link('image_name', 'highslide', $row['image_id'], $row['image_name'], $row['image_album_id']);
				}
			}
			else
			{
				$trigger_message = $user->lang['SLIDE_SHOW_START'];
				while ($row = $db->sql_fetchrow($result))
				{
					$picrow[] = generate_image_link('image_name', 'lytebox_slide_show', $row['image_id'], $row['image_name'], $row['image_album_id']);
				}
			}
			$db->sql_freeresult($result);

			$template->assign_vars(array(
				'MESSAGE_TITLE'		=> $user->lang['SLIDE_SHOW'],
				'MESSAGE_TEXT'		=> $trigger_message . '<br /><br />' . implode(', ', $picrow),
			));

			page_header($user->lang['SLIDE_SHOW']);
			$template->set_filenames(array(
				'body' => 'message_body.html')
			);
			page_footer();
		}
		else
		{
			$result = $db->sql_query_limit($sql, $pics_per_page, $start);
		}

		$picrow = array();

		while ($row = $db->sql_fetchrow($result))
		{
			$picrow[] = $row;
		}
		$db->sql_freeresult($result);
		for ($i = 0; $i < count($picrow); $i += $album_config['cols_per_page'])
		{
			$template->assign_block_vars('image_row', array());

			for ($j = $i; $j < ($i + $album_config['cols_per_page']); $j++)
			{
				if( $j >= count($picrow) )
				{
					$template->assign_block_vars('image_row.no_image', array());
					continue;
				}

				if(!$picrow[$j]['image_rates'])
				{
					$picrow[$j]['rating'] = $user->lang['NOT_RATED'];
				}
				else
				{
					$picrow[$j]['rating'] = sprintf((($picrow[$j]['image_rates'] == 1) ? $user->lang['RATE_STRING'] : $user->lang['RATES_STRING']), $picrow[$j]['image_rate_avg'] / 100, $picrow[$j]['image_rates']);
				}

				$approval_link = (gallery_acl_check('m_status', $album_id) && ($picrow[$j]['image_status'] == 0)) ? '<a href="'. append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=queue_details&amp;album_id=$album_id&amp;option_id=" . $picrow[$j]['image_id']) . '">' . $user->lang['APPROVE'] . '</a>' : '';


				$perm_user_id = ($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from'];
				$allow_edit = ((gallery_acl_check('i_edit', $album_id) && ($picrow[$j]['image_user_id'] == $perm_user_id)) || gallery_acl_check('m_edit', $album_id)) ? true : false;
				$allow_delete = ((gallery_acl_check('i_delete', $album_id) && ($picrow[$j]['image_user_id'] == $perm_user_id)) || gallery_acl_check('m_delete', $album_id)) ? true : false;

				$template->assign_block_vars('image_row.image', array(
					'IMAGE_ID'		=> $picrow[$j]['image_id'],
					'UC_IMAGE_NAME'	=> generate_image_link('image_name', $album_config['link_image_name'], $picrow[$j]['image_id'], $picrow[$j]['image_name'], $picrow[$j]['image_album_id']),
					'UC_THUMBNAIL'	=> generate_image_link('thumbnail', $album_config['link_thumbnail'], $picrow[$j]['image_id'], $picrow[$j]['image_name'], $picrow[$j]['image_album_id']),
					'S_UNAPPROVED'	=> (gallery_acl_check('m_status', $album_id) && (!$picrow[$j]['image_status'])) ? true : false,
					'S_LOCKED'		=> (gallery_acl_check('m_status', $album_id) && ($picrow[$j]['image_status'] == 2)) ? true : false,
					'S_REPORTED'	=> (gallery_acl_check('m_report', $album_id) && $picrow[$j]['image_reported']) ? true : false,

					'POSTER'		=> get_username_string('full', $picrow[$j]['image_user_id'], ($picrow[$j]['image_user_id'] <> ANONYMOUS) ? $picrow[$j]['image_username'] : $user->lang['GUEST'], $picrow[$j]['image_user_colour']),
					'TIME'			=> $user->format_date($picrow[$j]['image_time']),
					'VIEW'			=> $picrow[$j]['image_view_count'],

					'S_RATINGS'		=> (($album_config['allow_rates'] == 1) && gallery_acl_check('i_rate', $album_id)) ? $picrow[$j]['rating'] : '',
					'U_RATINGS'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . "&amp;image_id=" . $picrow[$j]['image_id']) . '#rating',
					'L_COMMENTS'	=> ($picrow[$j]['image_comments'] == 1) ? $user->lang['COMMENT'] : $user->lang['COMMENTS'],
					'S_COMMENTS'	=> (($album_config['allow_comments'] == 1) && gallery_acl_check('c_read', $album_id)) ? (($picrow[$j]['image_comments']) ? $picrow[$j]['image_comments'] : $user->lang['NO_COMMENTS']) : '',
					'U_COMMENTS'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . "&amp;image_id=" . $picrow[$j]['image_id']) . '#comments',

					'S_IP'		=> ($auth->acl_get('a_')) ? $picrow[$j]['image_user_ip'] : '',
					'U_WHOIS'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'mode=whois&amp;ip=' . $picrow[$j]['image_user_ip']),
					'U_REPORT'	=> (gallery_acl_check('m_report', $album_id) && $picrow[$j]['image_reported']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=report_details&amp;album_id=$album_id&amp;option_id=" . $picrow[$j]['image_reported']) : '',
					'U_STATUS'	=> (gallery_acl_check('m_status', $album_id)) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=queue_details&amp;album_id=$album_id&amp;option_id=" . $picrow[$j]['image_id']) : '',
					'L_STATUS'	=> (!$picrow[$j]['image_status']) ? $user->lang['APPROVE_IMAGE'] : (($picrow[$j]['image_status'] == 1) ? $user->lang['CHANGE_IMAGE_STATUS'] : $user->lang['UNLOCK_IMAGE']),
					'U_MOVE'	=> (gallery_acl_check('m_move', $album_id)) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "action=images_move&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id'] . "&amp;redirect=redirect") : '',
					'U_EDIT'	=> $allow_edit ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=edit&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id']) : '',
					'U_DELETE'	=> $allow_delete ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=delete&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id']) : '',
				));
			}
		}
	}
/*}*/

$allowed_create = false;
if ($album_data['album_user_id'] == $user->data['user_id'])
{
	if (gallery_acl_check('i_upload', OWN_GALLERY_PERMISSIONS))
	{
		$allowed_create = true;
		$sql = 'SELECT COUNT(album_id) as albums
			FROM ' . GALLERY_ALBUMS_TABLE . "
			WHERE album_user_id = {$user->data['user_id']}";
		$result = $db->sql_query($sql);
		$albums = $db->sql_fetchrow($result);
		if (($albums['albums'] - 1) >= gallery_acl_check('album_count', OWN_GALLERY_PERMISSIONS))
		{
			$allowed_create = false;
		}
		$db->sql_freeresult($result);
	}
}
$template->assign_vars(array(
	'S_IS_POSTABLE'				=> ($album_data['album_type'] == FORUM_POST) ? true : false,
	'UPLOAD_IMG'				=> /*($album_data['album_status'] == ITEM_LOCKED) ? $user->img('button_topic_locked', $post_alt) : */$user->img('button_upload_image', 'UPLOAD_IMAGE'),
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

	'S_THUMBNAIL_SIZE'			=> $album_config['thumbnail_size'] + 20 + (($album_config['thumbnail_info_line']) ? 16 : 0),
	'S_COLS'					=> $album_config['cols_per_page'],
	'S_COL_WIDTH'				=> (100/$album_config['cols_per_page']) . '%',
	'S_JUMPBOX_ACTION'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx"),
	'S_ALBUM_ACTION'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id"),

	'S_SELECT_SORT_DIR'		=> $s_sort_dir,
	'S_SELECT_SORT_KEY'		=> $s_sort_key,

	'ALBUM_JUMPBOX'				=> gallery_albumbox(false, '', $album_id),
	'U_RETURN_LINK'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx"),
	'S_RETURN_LINK'				=> $user->lang['GALLERY'],

	'PAGINATION'				=> generate_pagination(append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id&amp;sk=$sort_key&amp;sd=$sort_dir&amp;st=$sort_days"), $image_counter, $pics_per_page, $start),
	'TOTAL_IMAGES'				=> ($image_counter == 1) ? $user->lang['IMAGE_#'] : sprintf($user->lang['IMAGES_#'], $image_counter),
	'PAGE_NUMBER'				=> on_page($image_counter, $pics_per_page, $start),

	'L_WATCH_TOPIC'		=> ($album_data['watch_id']) ? $user->lang['UNWATCH_ALBUM'] : $user->lang['WATCH_ALBUM'],
	'U_WATCH_TOPIC'		=> ($user->data['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=album&amp;submode=" . (($album_data['watch_id']) ?  'un' : '') . "watch&amp;album_id=$album_id") : '',
	'S_WATCHING_TOPIC'	=> ($album_data['watch_id']) ? true : false,
));

/*
* cheat on phpbb till 3.0.3
* we will get the normal function pumped up for the external use =)
* i think you didn't recognize this, but we didn't display the users, browsing in this album, but in any album
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

/* END of Cheating
*/

// Output page
$page_title = $user->lang['VIEW_ALBUM'] . ' &bull; ' . $album_data['album_name'];

page_header($page_title);

$template->set_filenames(array(
	'body' => 'gallery_album_body.html')
);

page_footer();
?>