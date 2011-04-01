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

/**
* This file contains functions, to be backwards and forwards compatible with phpBB-versions
*/

/**
* Queries the session table to get information about online guests
*
* phpbb::Bug #31975
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

/**
* Queries the session table to get information about online users
*/
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

/**
* Uses the result of obtain_users_online to generate a localized, readable representation.
*/
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

function cheat_phpbb_31975()
{
	global $config, $template, $user;

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
}

if (!function_exists('set_config_count'))
{
	/**
	* Set dynamic config value with arithmetic operation.
	*
	* phpbb::rev10614
	*/
	function set_config_count($config_name, $increment, $is_dynamic = false)
	{
		global $db, $cache;

		switch ($db->sql_layer)
		{
			case 'firebird':
			case 'postgres':
				$sql_update = 'CAST(CAST(config_value as DECIMAL(255, 0)) + ' . (int) $increment . ' as VARCHAR(255))';
			break;

			// MySQL, SQlite, mssql, mssql_odbc, oracle
			default:
				$sql_update = 'config_value + ' . (int) $increment;
			break;
		}

		$db->sql_query('UPDATE ' . CONFIG_TABLE . ' SET config_value = ' . $sql_update . " WHERE config_name = '" . $db->sql_escape($config_name) . "'");

		if (!$is_dynamic)
		{
			$cache->destroy('config');
		}
	}
}

if (!function_exists('generate_link_hash'))
{
	/**
	* Add a secret hash   for use in links/GET requests
	* @param string  $link_name The name of the link; has to match the name used in check_link_hash, otherwise no restrictions apply
	* @return string the hash
	*
	* phpbb::rev10172
	*/
	function generate_link_hash($link_name)
	{
		global $user;

		if (!isset($user->data["hash_$link_name"]))
		{
			$user->data["hash_$link_name"] = substr(sha1($user->data['user_form_salt'] . $link_name), 0, 8);
		}

		return $user->data["hash_$link_name"];
	}
}

if (!function_exists('check_link_hash'))
{
	/**
	* checks a link hash - for GET requests
	* @param string $token the submitted token
	* @param string $link_name The name of the link
	* @return boolean true if all is fine
	*
	* phpbb::rev10172
	*/
	function check_link_hash($token, $link_name)
	{
		return $token === generate_link_hash($link_name);
	}
}

if (!function_exists('send_status_line'))
{
	/**
	* Outputs correct status line header.
	*
	* Depending on php sapi one of the two following forms is used:
	*
	* Status: 404 Not Found
	*
	* HTTP/1.x 404 Not Found
	*
	* HTTP version is taken from HTTP_VERSION environment variable,
	* and defaults to 1.0.
	*
	* Sample usage:
	*
	* send_status_line(404, 'Not Found');
	*
	* @param int $code HTTP status code
	* @param string $message Message for the status code
	* @return void
	*/
	function send_status_line($code, $message)
	{
		if (substr(strtolower(@php_sapi_name()), 0, 3) === 'cgi')
		{
			// in theory, we shouldn't need that due to php doing it. Reality offers a differing opinion, though
			header("Status: $code $message", true, $code);
		}
		else
		{
			if (!empty($_SERVER['SERVER_PROTOCOL']))
			{
				$version = $_SERVER['SERVER_PROTOCOL'];
			}
			else if (!empty($_SERVER['HTTP_VERSION']))
			{
				// I cannot remember where I got this from.
				// This code path may never be reachable in reality.
				$version = $_SERVER['HTTP_VERSION'];
			}
			else
			{
				$version = 'HTTP/1.0';
			}
			header("$version $code $message", true, $code);
		}
	}
}

if (!function_exists('phpbb_parse_http_date'))
{
	/**
	* Converts an HTTP 'full date' to UNIX timestamp
	* See:	http://tools.ietf.org/html/rfc2616#section-3.3.1
	*
	* Formats allowed by rfc 2616 are:
	*
	*      Sun, 06 Nov 1994 08:49:37 GMT  ; RFC 822, updated by RFC 1123
	*      Sunday, 06-Nov-94 08:49:37 GMT ; RFC 850, obsoleted by RFC 1036
	*      Sun Nov  6 08:49:37 1994       ; ANSI C's asctime() format
	*
	* The asctime format has no timezone information. At least some systems
	* take timezone as an argument to asctime, but the timezone is lost by
	* the time formatted string is produced. Because it is impossible to know
	* what timezone a time in asctime format is in, we do not support the
	* asctime format and return false if a time in asctime format is passed in.
	*
	* @param string	$date		Parameter array, see $param_defaults array.
	*
	* @return int|bool			False on failure,
	*							GMT Unix timestamp otherwise.
	*/
	function phpbb_parse_http_date($date)
	{
		if (substr($date, -3) == 'GMT')
		{
			return strtotime($date);
		}

		return false;
	}
}

if (!function_exists('phpbb_parse_if_modified_since'))
{
	/**
	* Parses If-Modified-Since HTTP header, returning the UNIX timestamp.
	*
	* The value may be given as $date parameter. If no parameter is given,
	* $_SERVER['HTTP_IF_MODIFIED_SINCE'] will be examined.
	*
	* If a date is supplied via the $date parameter or $_SERVER, and the
	* date is valid, the UNIX timestamp for the date is returned.
	*
	* If there is no date supplied or the date is invalid or does not parse,
	* false is returned.
	*
	* phpbb_parse_http_date is used for date parsing, which does not accept
	* ANSI C asctime-formatted dates.
	*
	* @param string	$date		HTTP 'full date' to parse, or false to use $_SERVER['HTTP_IF_MODIFIED_SINCE'].
	*
	* @return int|bool			False on failure,
	*							GMT Unix timestamp otherwise.
	*/
	function phpbb_parse_if_modified_since($date = false)
	{
		if ($date === false && isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			$date = trim($_SERVER['HTTP_IF_MODIFIED_SINCE']);
		}

		if (empty($date))
		{
			return false;
		}

		$if_modified_time = phpbb_parse_http_date($date);
		return $if_modified_time;
	}
}

?>