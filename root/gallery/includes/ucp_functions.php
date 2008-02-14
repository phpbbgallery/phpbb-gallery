<?php
/**
*
* @package phpBB3
* @version $Id: acp_functions.php 286 2008-02-06 19:55:38Z stoffel04 $
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}

function personal_album_select($user_id, $select_id = 0)
{
	global $db, $user, $auth;

	// This query is identical to the jumpbox one
	$sql = 'SELECT album_id, album_name, parent_id, left_id, right_id, album_type
		FROM ' . GALLERY_ALBUMS_TABLE . "
		WHERE album_user_id = $user_id
			AND parent_id != 0
		ORDER BY left_id ASC";
	$result = $db->sql_query($sql, 600);

	$right = 0;
	$padding_store = array('0' => '');
	$padding = $forum_list = '';

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

		$selected = (is_array($select_id)) ? ((in_array($row['album_id'], $select_id)) ? ' selected="selected"' : '') : (($row['album_id'] == $select_id) ? ' selected="selected"' : '');
		$forum_list .= '<option value="' . $row['album_id'] . '"' .$selected . '>' . $padding . $row['album_name'] . '</option>';
	}
	$db->sql_freeresult($result);
	unset($padding_store);

	return $forum_list;
}
/**
* Check album hacking
*/
function album_hacking($album_id)
{
	global $user, $db;

	if (!$user->data['album_id'])
	{
		trigger_error('NEED_INITIALISE');
	}

	$sql = 'SELECT album_id
		FROM ' . GALLERY_ALBUMS_TABLE . '
		WHERE album_id = ' . $album_id . '
			AND album_user_id = ' . $user->data['user_id'];
	$result = $db->sql_query($sql);

	if (!$row = $db->sql_fetchrow($result))
	{
		trigger_error('NO_ALBUM_STEALING');
	}
}
/**
* Get forum branch
*/
function get_album_branch($album_id, $type = 'all', $order = 'descending', $include_album = true)
{
	global $db, $user;

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
		LEFT JOIN ' . GALLERY_ALBUMS_TABLE . " a2 ON ($condition) AND a2.album_user_id = {$user->data['user_id']}
		WHERE a1.album_id = $album_id
			AND a1.album_user_id = {$user->data['user_id']}
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