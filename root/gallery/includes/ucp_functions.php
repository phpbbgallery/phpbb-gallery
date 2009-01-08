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

function personal_album_select($user_id, $select_id = 0, $disable_id = 0)
{
	global $db, $user, $auth;

	// This query is identical to the jumpbox one
	$sql = 'SELECT album_id, album_name, parent_id, left_id, right_id, album_type
		FROM ' . GALLERY_ALBUMS_TABLE . "
		WHERE album_user_id = $user_id
			AND parent_id != 0
		ORDER BY left_id ASC";
	$result = $db->sql_query($sql, 600);

	$left_block = $right_block = $right = 0;
	$padding_store = array('0' => '');
	$padding = $forum_list = '';

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['album_id'] == $disable_id)
		{
			$left_block = $row['left_id'];
			$right_block = $row['right_id'];
		}
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
		if ((($left_block <= $row['left_id']) && ($row['right_id'] <= $right_block)))
		{
			$disabled = true;
		}

		$selected = (is_array($select_id)) ? ((in_array($row['album_id'], $select_id)) ? ' selected="selected"' : '') : (($row['album_id'] == $select_id) ? ' selected="selected"' : '');
		$forum_list .= '<option value="' . $row['album_id'] . '"' . (($disabled) ? ' disabled="disabled" class="disabled-option"' : $selected) . '>' . $padding . $row['album_name'] . '</option>';
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

	if (!$user->gallery['personal_album_id'])
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

?>