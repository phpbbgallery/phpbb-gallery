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

function generate_album_nav(&$album_data)
{
	global $db, $user, $template, $auth;
	global $phpEx, $phpbb_root_path;

	// Get album parents
	$album_parents = get_album_parents($album_data);

	// Build navigation links
	/*
	if (!empty($album_parents))
	{
		foreach ($album_parents as $parent_album_id => $parent_data)
		{
			list($parent_name, $parent_type) = array_values($parent_data);

			$template->assign_block_vars('navlinks', array(
				'ALBUM_NAME'	=> $parent_name,
				'ALBUM_ID'		=> $parent_album_id,
				'U_VIEW_ALBUM'	=> append_sid("{$phpbb_root_path}viewalbum.$phpEx", 'f=' . $parent_album_id))
			);
		}
	}

	$template->assign_block_vars('navlinks', array(
		'ALBUM_NAME'	=> $album_data['album_name'],
		'ALBUM_ID'		=> $album_data['album_id'],
		'U_VIEW_album'	=> append_sid("{$phpbb_root_path}viewalbum.$phpEx", 'f=' . $album_data['album_id']))
	);
	$album_data['cat_desc_options'] = 7;
	$template->assign_vars(array(
		'ALBUM_ID' 		=> $album_data['album_id'],
		'ALBUM_NAME'	=> $album_data['album_name'],
		'ALBUM_DESC'	=> generate_text_for_display($album_data['cat_desc'], $album_data['cat_desc_bbcode_uid'], $album_data['cat_desc_bbcode_bitfield'], $album_data['cat_desc_options']))
	);*/
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
			//$db->sql_query($sql);
		}
		else
		{
			$album_parents = unserialize($album_data['album_parents']);
		}
	}

	return $album_parents;
}

function make_album_select($select_id = false, $ignore_id = false, $album = false, $ignore_acl = false, $ignore_nonpost = false, $ignore_emptycat = true, $only_acl_post = false, $return_array = false)
{
	global $db, $user, $auth;

	// no permissions yet
	$acl = ($ignore_acl) ? '' : (($only_acl_post) ? 'f_post' : array('f_list', 'a_forum', 'a_forumadd', 'a_forumdel'));

	// This query is identical to the jumpbox one
	$sql = 'SELECT album_id, album_name, parent_id, left_id, right_id, album_type
		FROM ' . GALLERY_ALBUMS_TABLE . '
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

		if ($acl && !$auth->acl_gets($acl, $row['album_id']))
		{
			// List permission?
			if ($auth->acl_get('f_list', $row['album_id']))
			{
				$disabled = true;
			}
			else
			{
				continue;
			}
		}

		if (((is_array($ignore_id) && in_array($row['album_id'], $ignore_id)) || $row['album_id'] == $ignore_id) || ($album && ($row['album_type'] != 2)))
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
	$db->sql_freeresult($result);
	unset($padding_store);

	return $forum_list;
}
/**
* create permissions drop-down box for creating and editing albums
*/
function permission_drop_down_box($type, $permission)
{
	global $user;

	$permission_drop_down_box = '<select name="$type">';
	if (($type == 'album_view_level') || ($type == 'album_upload_level') || ($type == 'album_rate_level') || ($type == 'album_comment_level'))
	{
		$permission_drop_down_box .= '<option' . (($permission == ALBUM_GUEST) ? ' selected="selected"' : '') . ' value="ALBUM_GUEST">' . $user->lang['GALLERY_ALL'] . '</option>';
	}
	$permission_drop_down_box .= '<option' . (($permission == ALBUM_USER) ? ' selected="selected"' : '') . ' value="ALBUM_USER">' . $user->lang['GALLERY_REG'] . '</option>';
	if ($type != 'album_approval')
	{
		$permission_drop_down_box .= '<option' . (($permission == ALBUM_PRIVATE) ? ' selected="selected"' : '') . ' value="ALBUM_PRIVATE">' . $user->lang['GALLERY_PRIVATE'] . '</option>';
	}
	$permission_drop_down_box .= '<option' . (($permission == ALBUM_MOD) ? ' selected="selected"' : '') . ' value="ALBUM_MOD}">' . $user->lang['GALLERY_MOD'] . '</option>';
	$permission_drop_down_box .= '<option' . (($permission == ALBUM_ADMIN) ? ' selected="selected"' : '') . ' value="ALBUM_ADMIN">' . $user->lang['GALLERY_ADMIN'] . '</option>';
	$permission_drop_down_box .= '</select>';
	return $permission_drop_down_box;
}

/**
* Get album details
*/
function get_album_info($album_id)
{
	global $db;

	$sql = 'SELECT *
		FROM ' . GALLERY_ALBUMS_TABLE . "
		WHERE album_id = $album_id";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		trigger_error("Album #$album_id does not exist", E_USER_ERROR);
	}

	return $row;
}

/**
* Get forum branch
*/
function get_album_branch($album_id, $type = 'all', $order = 'descending', $include_album = true)
{
	global $db;

	switch ($type)
	{
		case 'parents':
			$condition = 'a1.left_id BETWEEN a2.left_id AND a2.right_id';
		break;

		case 'children':
			$condition = 'a2.left_id BETWEEN a1.left_id AND a1.right_id';
		break;

		default:
			$condition = 'a2.left_id BETWEEN a1.left_id AND a1.right_id OR a1.left_id BETWEEN a2.left_id AND a2.right_id';
		break;
	}

	$rows = array();

	$sql = 'SELECT a2.*
		FROM ' . GALLERY_ALBUMS_TABLE . ' a1
		LEFT JOIN ' . GALLERY_ALBUMS_TABLE . " a2 ON ($condition)
		WHERE a1.album_id = $album_id
		ORDER BY a2.left_id " . (($order == 'descending') ? 'ASC' : 'DESC');
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (!$include_album && $row['album_id'] == $album_id)
		{
			continue;
		}

		$rows[] = $row;
	}
	$db->sql_freeresult($result);

	return $rows;
}

?>