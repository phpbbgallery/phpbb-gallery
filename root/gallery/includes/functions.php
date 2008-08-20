<?php

/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}
$gallery_root_path = GALLERY_ROOT_PATH;

$gd_check = function_exists('gd_info') ? gd_info() : array();;
$gd_success = isset($gd_check['GD Version']);
if (!$gd_success)
{
	if (!isset($album_config['gd_version']))
	{
		$sql = 'SELECT * FROM ' . GALLERY_CONFIG_TABLE . " WHERE config_name = 'gd_version'";
		$result = $db->sql_query($sql);
		while( $row = $db->sql_fetchrow($result) )
		{
			$album_config_name = $row['config_name'];
			$album_config_value = $row['config_value'];
			$album_config[$album_config_name] = $album_config_value;
		}
	}
	if ($album_config['gd_version'] > 0)
	{
		$sql = 'UPDATE ' . GALLERY_CONFIG_TABLE . " SET config_value = 0 WHERE config_name = 'gd_version'";
		$result = $db->sql_query($sql);
		$album_config['gd_version'] = 0;
	}
}

$sql = 'SELECT *
	FROM ' . GALLERY_USERS_TABLE . '
	WHERE user_id = ' . (int) $user->data['user_id'];
$result = $db->sql_query($sql);
$user->gallery = $db->sql_fetchrow($result);

/**
* Get album children (for displaying the subalbums
*/
function get_album_children($album_id)
{
	global $db, $phpEx, $phpbb_root_path, $gallery_root_path;

	$rows = array();

	$sql = 'SELECT *
		FROM ' . GALLERY_ALBUMS_TABLE . "
		WHERE parent_id = $album_id
		ORDER BY left_id ASC";
	$result = $db->sql_query($sql);
	$navigation = '';

	while ($row = $db->sql_fetchrow($result))
	{
		$rows[] = $row;
		$navigation .= (($navigation) ? ', ' : '') . '<a href="' . append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $row['album_id']) . '">' . $row['album_name'] . '</a>';
	}
	$db->sql_freeresult($result);

	return $navigation;
}
/**
* Get album details
*/
function get_album_info($album_id)
{
	global $db, $user, $gallery_root_path, $phpbb_root_path, $phpEx;

	$sql = 'SELECT a.*, w.watch_id
		FROM ' . GALLERY_ALBUMS_TABLE . ' AS a
		LEFT JOIN ' . GALLERY_WATCH_TABLE . ' AS w
			ON a.album_id = w.album_id
				AND w.user_id = ' . $user->data['user_id']  . "
		WHERE a.album_id = $album_id";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		meta_refresh(3, append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx"));
		trigger_error(sprintf($user->lang['ALBUM_ID_NOT_EXIST'], $album_id));
	}

	return $row;
}

function generate_album_nav(&$album_data)
{
	global $db, $user, $template, $auth;
	global $phpEx, $phpbb_root_path, $gallery_root_path;

	// Get album parents
	$album_parents = get_album_parents($album_data);
	if ($album_data['album_user_id'] > 0 )
	{
		$sql = 'SELECT user_id, username, user_colour
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . $album_data['album_user_id'] . '
			LIMIT 1';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $user->lang['PERSONAL_ALBUMS'],
				'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx", 'mode=personal'))
			);
		}
	}

	// Build navigation links
	if (!empty($album_parents))
	{
		foreach ($album_parents as $parent_album_id => $parent_data)
		{
			list($parent_name, $parent_type) = array_values($parent_data);

			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $parent_name,
				'FORUM_ID'		=> $parent_album_id,
				'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $parent_album_id))
			);
		}
	}

	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $album_data['album_name'],
		'FORUM_ID'		=> $album_data['album_id'],
		'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $album_data['album_id']))
	);
	$template->assign_vars(array(
		'ALBUM_ID' 		=> $album_data['album_id'],
		'ALBUM_NAME'	=> $album_data['album_name'],
		'ALBUM_DESC'	=> generate_text_for_display($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options']))
	);
	return;
}

/**
* Returns album parents as an array. Get them from album_data if available, or update the database otherwise
*/
function get_album_parents(&$album_data)
{
	global $db;

	$album_parents = array();
	if ($album_data['parent_id'] > 0)
	{
		if ($album_data['album_parents'] == '')
		{
			$sql = 'SELECT album_id, album_name, album_type
				FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE left_id < ' . $album_data['left_id'] . '
					AND right_id > ' . $album_data['right_id'] . '
					AND album_user_id = ' . $album_data['album_user_id'] . '
				ORDER BY left_id ASC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$album_parents[$row['album_id']] = array($row['album_name'], (int) $row['album_type']);
			}
			$db->sql_freeresult($result);

			$album_data['album_parents'] = serialize($album_parents);

			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
				SET album_parents = '" . $db->sql_escape($album_data['album_parents']) . "'
				WHERE parent_id = " . $album_data['parent_id'];
			$db->sql_query($sql);
		}
		else
		{
			$album_parents = unserialize($album_data['album_parents']);
		}
	}

	return $album_parents;
}

/**
* make the jump box, parent_album box, etc
*/
function make_album_jumpbox($select_id = false, $ignore_id = false, $album = false, $ignore_acl = false, $ignore_nonpost = false, $ignore_emptycat = true, $only_acl_post = false, $return_array = false)
{
	global $db, $user, $auth, $album_access_array;

	// no permissions yet
	$acl = ($ignore_acl) ? '' : (($only_acl_post) ? 'f_post' : array('f_list', 'a_forum', 'a_forumadd', 'a_forumdel'));

	// This query is identical to the jumpbox one
	$sql = 'SELECT *
		FROM ' . GALLERY_ALBUMS_TABLE . '
		WHERE album_user_id = 0
		ORDER BY left_id ASC';
	$result = $db->sql_query($sql, 600);

	$right = 0;
	$padding_store = array('0' => '');
	$padding = '';
	$forum_list = ($return_array) ? array() : '';

	// Sometimes it could happen that forums will be displayed here not be displayed within the index page
	// This is the result of forums not displayed at index, having list permissions and a parent of a forum with no permissions.
	// If this happens, the padding could be "broken"

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['left_id'] < $right)
		{
			$padding .= '&nbsp; &nbsp;';
			$padding_store[$row['parent_id']] = $padding;
		}
		else if ($row['left_id'] > $right + 1)
		{
			$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : '';
		}

		$right = $row['right_id'];
		$disabled = false;

		if (((is_array($ignore_id) && in_array($row['album_id'], $ignore_id)) || $row['album_id'] == $ignore_id) || ($album && !$row['album_type']))
		{
			$disabled = true;
		}

		if (gallery_acl_check('i_view', $row['album_id']))
		{
			if ($return_array)
			{
				// Include some more information...
				$selected = (is_array($select_id)) ? ((in_array($row['album_id'], $select_id)) ? true : false) : (($row['album_id'] == $select_id) ? true : false);
				$forum_list[$row['album_id']] = array_merge(array('padding' => $padding, 'selected' => ($selected && !$disabled), 'disabled' => $disabled), $row);
			}
			else
			{
				$selected = (is_array($select_id)) ? ((in_array($row['album_id'], $select_id)) ? ' selected="selected"' : '') : (($row['album_id'] == $select_id) ? ' selected="selected"' : '');
				$forum_list .= '<option value="' . $row['album_id'] . '"' . (($disabled) ? ' disabled="disabled" class="disabled-option"' : $selected) . '>' . $padding . $row['album_name'] . '</option>';
			}
		}
	}
	$db->sql_freeresult($result);
	unset($padding_store);

	return $forum_list;
}

function make_personal_jumpbox($album_user_id, $select_id = false, $ignore_id = false, $album = false, $ignore_acl = false, $ignore_nonpost = false, $ignore_emptycat = true, $only_acl_post = false, $return_array = false)
{
	global $db, $user, $auth, $album_access_array;

	// This query is identical to the jumpbox one
	$sql = 'SELECT *
		FROM ' . GALLERY_ALBUMS_TABLE . "
		WHERE album_user_id = $album_user_id
		ORDER BY left_id ASC";
	$result = $db->sql_query($sql, 600);

	$right = 0;
	$padding_store = array('0' => '');
	$padding = '';
	$forum_list = ($return_array) ? array() : '';

	// Sometimes it could happen that forums will be displayed here not be displayed within the index page
	// This is the result of forums not displayed at index, having list permissions and a parent of a forum with no permissions.
	// If this happens, the padding could be "broken"

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['left_id'] < $right)
		{
			$padding .= '&nbsp; &nbsp;';
			$padding_store[$row['parent_id']] = $padding;
		}
		else if ($row['left_id'] > $right + 1)
		{
			$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : '';
		}

		$right = $row['right_id'];
		$disabled = false;

		if (((is_array($ignore_id) && in_array($row['album_id'], $ignore_id)) || $row['album_id'] == $ignore_id) || ($album && !$row['album_type']))
		{
			$disabled = true;
		}

		if (gallery_acl_check('i_view', $row['album_id']))
		{
			if ($return_array)
			{
				// Include some more information...
				$selected = (is_array($select_id)) ? ((in_array($row['album_id'], $select_id)) ? true : false) : (($row['album_id'] == $select_id) ? true : false);
				$forum_list[$row['album_id']] = array_merge(array('padding' => $padding, 'selected' => ($selected && !$disabled), 'disabled' => $disabled), $row);
			}
			else
			{
				$selected = (is_array($select_id)) ? ((in_array($row['album_id'], $select_id)) ? ' selected="selected"' : '') : (($row['album_id'] == $select_id) ? ' selected="selected"' : '');
				$forum_list .= '<option value="' . $row['album_id'] . '"' . (($disabled) ? ' disabled="disabled" class="disabled-option"' : $selected) . '>' . $padding . $row['album_name'] . '</option>';
			}
		}
	}
	$db->sql_freeresult($result);
	unset($padding_store);

	return $forum_list;
}

function make_move_jumpbox($select_id = false, $ignore_id = false, $album = false, $ignore_acl = false, $ignore_nonpost = false, $ignore_emptycat = true, $only_acl_post = false, $return_array = false)
{
	global $db, $user, $auth, $album_access_array;

	// This query is identical to the jumpbox one
	$sql = 'SELECT *
		FROM ' . GALLERY_ALBUMS_TABLE . "
		ORDER BY album_user_id, left_id ASC";
	$result = $db->sql_query($sql);

	$album_user_id = $right = 0;
	$padding_store = array('0' => '');
	$padding = '';
	$forum_list = ($return_array) ? array() : '';
	$personal_info = false;

	// Sometimes it could happen that forums will be displayed here not be displayed within the index page
	// This is the result of forums not displayed at index, having list permissions and a parent of a forum with no permissions.
	// If this happens, the padding could be "broken"

	while ($row = $db->sql_fetchrow($result))
	{
		if (($row['album_user_id'] > 0) && !$personal_info)
		{
			$personal_info = true;
			$forum_list .= '<option disabled="disabled" class="disabled-option">' . $user->lang['PERSONAL_ALBUMS'] . '</option>';
		}
		if (gallery_acl_check('i_view', $row['album_id']))
		{
			if ($album_user_id == $row['album_user_id'])
			{
				if ($row['left_id'] < $right)
				{
					$padding .= '&nbsp; &nbsp;';
					$padding_store[$row['parent_id']] = $padding;
				}
				else if ($row['left_id'] > $right + 1)
				{
					$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : '';
				}
			}
			else
			{
				$padding = '';
			}

			$right = $row['right_id'];
			$album_user_id = $row['album_user_id'];
			$disabled = false;

			if (!gallery_acl_check('i_upload', $row['album_id']))
			{
				$disabled = true;
			}

			if ($return_array)
			{
				// Include some more information...
				$selected = (is_array($select_id)) ? ((in_array($row['album_id'], $select_id)) ? true : false) : (($row['album_id'] == $select_id) ? true : false);
				$forum_list[$row['album_id']] = array_merge(array('padding' => $padding, 'selected' => ($selected && !$disabled), 'disabled' => $disabled), $row);
			}
			else
			{
				$selected = (is_array($select_id)) ? ((in_array($row['album_id'], $select_id)) ? ' selected="selected"' : '') : (($row['album_id'] == $select_id) ? ' selected="selected"' : '');
				$forum_list .= '<option value="' . $row['album_id'] . '"' . (($disabled) ? ' disabled="disabled" class="disabled-option"' : $selected) . '>' . $padding . $row['album_name'] . '</option>';
			}
		}
	}
	$db->sql_freeresult($result);
	unset($padding_store);

	return $forum_list;
}
/**
* get image info
*/
function get_image_info($image_id)
{
	global $db, $user;

	$sql = 'SELECT *
		FROM ' . GALLERY_IMAGES_TABLE . ' i
		LEFT JOIN ' . GALLERY_WATCH_TABLE . ' w
			ON w.image_id = i.image_id
				AND w.user_id = ' . $user->data['user_id']  . '
		LEFT JOIN ' . GALLERY_FAVORITES_TABLE . ' f
			ON f.image_id = i.image_id
				AND f.user_id = ' . $user->data['user_id']  . '
		WHERE i.image_id = ' . $image_id;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		trigger_error('IMAGE_NOT_EXIST');
	}

	return $row;
}

/**
* Update Album-Information
*/
function update_lastimage_info($album_id)
{
	global $db, $user;

	//update album-information
	$images_real = $images = $album_user_id = 0;
	$sql = 'SELECT album_user_id
		FROM ' . GALLERY_ALBUMS_TABLE . "
		WHERE album_id = $album_id";
	$result = $db->sql_query($sql);
	if ($row = $db->sql_fetchrow($result))
	{
		$album_user_id = $row['album_user_id'];
	}
	$db->sql_freeresult($result);
	$sql = 'SELECT COUNT(image_id) images
		FROM ' . GALLERY_IMAGES_TABLE . "
		WHERE image_album_id = $album_id
			AND image_status = 1";
	$result = $db->sql_query($sql);
	if ($row = $db->sql_fetchrow($result))
	{
		$images = $row['images'];
	}
	$db->sql_freeresult($result);
	$sql = 'SELECT COUNT(image_id) images_real
		FROM ' . GALLERY_IMAGES_TABLE . "
		WHERE image_album_id = $album_id";
	$result = $db->sql_query($sql);
	if ($row = $db->sql_fetchrow($result))
	{
		$images_real = $row['images_real'];
	}
	$db->sql_freeresult($result);
	$sql = 'SELECT *
		FROM ' . GALLERY_IMAGES_TABLE . "
		WHERE image_album_id = $album_id
			AND image_status = 1
		ORDER BY image_time DESC
		LIMIT 1";
	$result = $db->sql_query($sql);
	if ($row = $db->sql_fetchrow($result))
	{
		$sql_ary = array(
			'album_images_real'			=> $images_real,
			'album_images'				=> $images,
			'album_last_image_id'		=> $row['image_id'],
			'album_last_image_time'		=> $row['image_time'],
			'album_last_image_name'		=> $row['image_name'],
			'album_last_username'		=> $row['image_username'],
			'album_last_user_colour'	=> $row['image_user_colour'],
			'album_last_user_id'		=> $row['image_user_id'],
		);
	}
	else
	{
		$sql_ary = array(
			'album_images_real'			=> $images_real,
			'album_images'				=> $images,
			'album_last_image_id'		=> 0,
			'album_last_image_time'		=> 0,
			'album_last_image_name'		=> '',
			'album_last_username'		=> '',
			'album_last_user_colour'	=> '',
			'album_last_user_id'		=> 0,
		);
		if ($album_user_id)
		{
			unset($sql_ary['album_last_user_colour']);
		}
	}
	$db->sql_freeresult($result);
	$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
		WHERE ' . $db->sql_in_set('album_id', $album_id);
	$db->sql_query($sql);

	return $row;
}

/**
* Obtain list of moderators of each album
*/
function get_album_moderators(&$album_moderators, $album_id = false)
{
	global $config, $template, $db, $phpbb_root_path, $phpEx, $user;

	// Have we disabled the display of moderators? If so, then return
	// from whence we came ...
	if (!$config['load_moderators'])
	{
		return;
	}

	$album_sql = '';

	if ($album_id !== false)
	{
		if (!is_array($album_id))
		{
			$album_id = array($album_id);
		}

		// If we don't have a forum then we can't have a moderator
		if (!sizeof($album_id))
		{
			return;
		}

		$album_sql = 'AND m.' . $db->sql_in_set('album_id', $album_id);
	}

	$sql_array = array(
		'SELECT'	=> 'm.*, u.user_colour, g.group_colour, g.group_type',

		'FROM'		=> array(
			GALLERY_MODSCACHE_TABLE	=> 'm',
		),

		'LEFT_JOIN'	=> array(
			array(
				'FROM'	=> array(USERS_TABLE => 'u'),
				'ON'	=> 'm.user_id = u.user_id',
			),
			array(
				'FROM'	=> array(GROUPS_TABLE => 'g'),
				'ON'	=> 'm.group_id = g.group_id',
			),
		),

		'WHERE'		=> "m.display_on_index = 1 $album_sql",
	);

	$sql = $db->sql_build_query('SELECT', $sql_array);
	$result = $db->sql_query($sql, 3600);

	while ($row = $db->sql_fetchrow($result))
	{
		if (!empty($row['user_id']))
		{
			$album_moderators[$row['album_id']][] = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
		}
		else
		{
			$album_moderators[$row['album_id']][] = '<a' . (($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . ';"' : '') . ' href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']) . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</a>';
		}
	}
	$db->sql_freeresult($result);

	return;
}
function handle_image_counter($image_id_ary, $add, $readd = false)
{
	global $config, $db;

	$num_images = 0;
	$sql = 'SELECT count(image_id) AS images, image_user_id
		FROM ' . GALLERY_IMAGES_TABLE . '
		WHERE image_status ' . (($readd) ? '<>' : '=') . ' 1
			AND ' . $db->sql_in_set('image_id', $image_id_ary) . '
		GROUP BY image_user_id';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$sql_ary = array(
			'user_id'				=> $row['image_user_id'],
			'user_images'			=> $row['images'],
		);
		$num_images = $num_images + $row['images'];
		$sql = 'UPDATE ' . GALLERY_USERS_TABLE . ' SET user_images = user_images ' . (($add) ? '+ ' : '- ') . $row['images'] . '
			WHERE ' . $db->sql_in_set('user_id', $row['image_user_id']);
		$db->sql_query($sql);
		if ($db->sql_affectedrows() != 1)
		{
			$sql = 'INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}
	}
	$db->sql_freeresult($result);

	set_config('num_images', (($add) ? $config['num_images'] + $num_images : $config['num_images'] - $num_images), true);
}

?>