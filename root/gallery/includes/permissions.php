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

// Load the permissions
$album_access_array = get_album_access_array();

function get_album_access_array()
{
	global $cache, $db, $user;
	global $album_access_array, $gallery_config;

	if ($gallery_config == array())
	{
		// If we don't have the config, we don't have the function to call it aswell?
		$sql = 'SELECT *
			FROM ' . GALLERY_CONFIG_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$gallery_config[$row['config_name']] = $row['config_value'];
		}
		$db->sql_freeresult($result);
	}
	$albums = $cache->obtain_album_list();

	$permissions = $permission_parts['misc'] = $permission_parts['m'] = $permission_parts['c'] = $permission_parts['i'] = array();
	$permission_parts['i'] = array('i_view', 'i_watermark', 'i_upload', 'i_approve', 'i_edit', 'i_delete', 'i_report', 'i_rate');
	$permission_parts['c'] = array('c_read', 'c_post', 'c_edit', 'c_delete');
	$permission_parts['m'] = array('m_comments', 'm_delete', 'm_edit', 'm_move', 'm_report', 'm_status');
	$permission_parts['misc'] = array('a_list', 'i_count', 'album_count', 'a_moderate');
	$permissions = array_merge($permissions, $permission_parts['i'], $permission_parts['c'], $permission_parts['m'], $permission_parts['misc']);

	if (!$album_access_array)
	{
		$pull_data = '';
		$user_groups_ary = array();

		//set all parts of the permissions to 0 / "no"
		foreach ($permissions as $permission)
		{
			$album_access_array[-1][$permission] = 0;
			$album_access_array[OWN_GALLERY_PERMISSIONS][$permission] = 0;
			$album_access_array[PERSONAL_GALLERY_PERMISSIONS][$permission] = 0;
			//generate for the sql
			$pull_data .= " MAX($permission) as $permission,";
		}
		$album_access_array[-1]['m_'] = 0;
		$album_access_array[OWN_GALLERY_PERMISSIONS]['m_'] = 0;
		$album_access_array[PERSONAL_GALLERY_PERMISSIONS]['m_'] = 0;
		foreach ($albums as $album)
		{
			foreach ($permissions as $permission)
			{
				$album_access_array[$album['album_id']][$permission] = 0;
			}
			$album_access_array[$album['album_id']]['m_'] = 0;
		}
		//testing user permissions?
		$user_id = ($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from'];

		$sql = 'SELECT g.group_id
			FROM ' . GROUPS_TABLE . ' as g
			LEFT JOIN ' . USER_GROUP_TABLE . " as ug
				ON ug.group_id = g.group_id
			WHERE ug.user_id = $user_id
				AND ug.user_pending = 0";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$user_groups_ary[] = $row['group_id'];
		}
		$db->sql_freeresult($result);

		$sql = "SELECT p.perm_album_id, $pull_data p.perm_system
			FROM " . GALLERY_PERMISSIONS_TABLE . " as p
			LEFT JOIN " . GALLERY_ROLES_TABLE .  " as pr
				ON p.perm_role_id = pr.role_id
			WHERE ( p.perm_user_id = $user_id
				OR " . $db->sql_in_set('p.perm_group_id', $user_groups_ary) . ')
			GROUP BY p.perm_system DESC, p.perm_album_id ASC';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			switch ($row['perm_system'])
			{
				case PERSONAL_GALLERY_PERMISSIONS:
					foreach ($permissions as $permission)
					{
						$album_access_array[PERSONAL_GALLERY_PERMISSIONS][$permission] = $row[$permission];
						if ((substr($permission, 0, 2) == 'm_') && ($row[$permission] == 1))
						{
							$album_access_array[PERSONAL_GALLERY_PERMISSIONS]['m_'] = $row[$permission];
						}
					}
				break;

				case OWN_GALLERY_PERMISSIONS:
					foreach ($permissions as $permission)
					{
						$album_access_array[OWN_GALLERY_PERMISSIONS][$permission] = $row[$permission];
						if ((substr($permission, 0, 2) == 'm_') && ($row[$permission] == 1))
						{
							$album_access_array[OWN_GALLERY_PERMISSIONS]['m_'] = $row[$permission];
						}
					}
				break;

				case 1:
					foreach ($permissions as $permission)
					{
						// if the permission is true ($row[$permission] == 1) and global_permission is never ($album_access_array[PERSONAL_GALLERY_PERMISSIONS][$permission] == 2) we set it to "never"
						$album_access_array[$row['perm_album_id']][$permission] = (($row[$permission]) ? (($row[$permission] == 1 && ($album_access_array[PERSONAL_GALLERY_PERMISSIONS][$permission] == 2)) ? $album_access_array[PERSONAL_GALLERY_PERMISSIONS][$permission] : $row[$permission]) : 0);
						if ((substr($permission, 0, 2) == 'm_') && ($row[$permission] == 1))
						{
							$album_access_array[$row['perm_album_id']]['m_'] = $row[$permission];
						}
					}
				break;

				case 0:
					foreach ($permissions as $permission)
					{
						$album_access_array[$row['perm_album_id']][$permission] = $row[$permission];
						if ((substr($permission, 0, 2) == 'm_') && ($row[$permission] == 1))
						{
							$album_access_array[$row['perm_album_id']]['m_'] = $row[$permission];
						}
					}
				break;
			}
		}
		$db->sql_freeresult($result);
	}

	return $album_access_array;
}

/**
* An other call for the permissions ...
*/
function gallery_acl_check($mode, $album_id, $album_user_id = -1)
{
	static $_gallery_acl_cache;

	// Do we have a function call without $album_user_id ?
	if (($album_user_id < 0) && ($album_id > 0))
	{
		global $cache;

		$albums = $cache->obtain_album_list();
		$album_user_id = $albums[$album_id]['album_user_id'];
	}

	if (isset($_gallery_acl_cache[$album_id][$mode]))
	{
		return $_gallery_acl_cache[$album_id][$mode];
	}

	global $user, $album_access_array;

	if ($album_id == OWN_GALLERY_PERMISSIONS)
	{
		if ($mode == 'album_count')
		{
			$_gallery_acl_cache[$album_id][$mode] = $album_access_array[OWN_GALLERY_PERMISSIONS][$mode];
		}
		else
		{
			$_gallery_acl_cache[$album_id][$mode] = ($album_access_array[OWN_GALLERY_PERMISSIONS][$mode] == 1) ? true : false;
		}
		return $_gallery_acl_cache[$album_id][$mode];
	}
	if ($album_id == PERSONAL_GALLERY_PERMISSIONS)
	{
		if ($mode == 'album_count')
		{
			$_gallery_acl_cache[$album_id][$mode] = $album_access_array[PERSONAL_GALLERY_PERMISSIONS][$mode];
		}
		else
		{
			$_gallery_acl_cache[$album_id][$mode] = ($album_access_array[PERSONAL_GALLERY_PERMISSIONS][$mode] == 1) ? true : false;
		}
		return $_gallery_acl_cache[$album_id][$mode];
	}

	if ($mode == 'i_count')
	{
		if ($album_user_id == $user->data['user_id'])
		{
			$_gallery_acl_cache[$album_id][$mode] = $album_access_array[OWN_GALLERY_PERMISSIONS][$mode];
		}
		else if ($album_user_id > 0)
		{
			$_gallery_acl_cache[$album_id][$mode] = $album_access_array[PERSONAL_GALLERY_PERMISSIONS][$mode];
		}
		else
		{
			$_gallery_acl_cache[$album_id][$mode] = $album_access_array[$album_id][$mode];
		}
	}
	else
	{
		if ($album_user_id == $user->data['user_id'])
		{
			$_gallery_acl_cache[$album_id][$mode] = (isset($album_access_array[OWN_GALLERY_PERMISSIONS][$mode]) && $album_access_array[OWN_GALLERY_PERMISSIONS][$mode] == 1) ? true : false;
		}
		else if ($album_user_id > 0)
		{
			$_gallery_acl_cache[$album_id][$mode] = (isset($album_access_array[PERSONAL_GALLERY_PERMISSIONS][$mode]) && $album_access_array[PERSONAL_GALLERY_PERMISSIONS][$mode] == 1) ? true : false;
		}
		else
		{
			$_gallery_acl_cache[$album_id][$mode] = (isset($album_access_array[$album_id][$mode]) && $album_access_array[$album_id][$mode] == 1) ? true : false;
		}
	}

	return $_gallery_acl_cache[$album_id][$mode];
}

/**
* Get album lists by permissions
*
* @param	string	$permission		One of the permissions, Exp: i_view
* @param	string	$mode			'array' || 'string'
*/
function gallery_acl_album_ids($permission, $mode = 'array', $display_in_rrc = false)
{
	global $user, $album_access_array, $cache;

	$album_list = '';
	$album_array = array();
	$albums = $cache->obtain_album_list();
	foreach ($albums as $album)
	{
		if ($album['album_user_id'] == $user->data['user_id'])
		{
			$acl_case = OWN_GALLERY_PERMISSIONS;
		}
		else if ($album['album_user_id'] > 0)
		{
			$acl_case = PERSONAL_GALLERY_PERMISSIONS;
		}
		else
		{
			$acl_case = $album['album_id'];
		}
		if (($album_access_array[$acl_case][$permission] == 1) && (!$display_in_rrc || ($display_in_rrc && $album['display_in_rrc'])))
		{
			$album_list .= (($album_list) ? ', ' : '') . $album['album_id'];
			$album_array[] = $album['album_id'];
		}
	}

	return ($mode == 'array') ? $album_array : $album_list;
}

/**
* User authorisation levels output
*
* @param	string	$mode			Can only be 'album' so far.
* @param	int		$album_id		The current album the user is in.
* @param	int		$album_status	The albums status bit.
*
* borrowed from phpBB3
* @author: phpBB Group
* @function: gen_forum_auth_level
*/
function gen_album_auth_level($mode, $album_id, $album_status)
{
	global $template, $user, $gallery_config, $album_access_array;

	$locked = ($album_status == ITEM_LOCKED && !gallery_acl_check('m_', $album_id)) ? true : false;

	$rules = array(
		(gallery_acl_check('i_view', $album_id) && !$locked) ? $user->lang['ALBUM_VIEW_CAN'] : $user->lang['ALBUM_VIEW_CANNOT'],
		(gallery_acl_check('i_upload', $album_id) && !$locked) ? $user->lang['ALBUM_UPLOAD_CAN'] : $user->lang['ALBUM_UPLOAD_CANNOT'],
		(gallery_acl_check('i_edit', $album_id) && !$locked) ? $user->lang['ALBUM_EDIT_CAN'] : $user->lang['ALBUM_EDIT_CANNOT'],
		(gallery_acl_check('i_delete', $album_id) && !$locked) ? $user->lang['ALBUM_DELETE_CAN'] : $user->lang['ALBUM_DELETE_CANNOT'],
	);
	if ($gallery_config['allow_comments'] && gallery_acl_check('c_read', $album_id))
	{
		$rules[] = (gallery_acl_check('c_post', $album_id) && !$locked) ? $user->lang['ALBUM_COMMENT_CAN'] : $user->lang['ALBUM_COMMENT_CANNOT'];
	}
	if ($gallery_config['allow_rates'])
	{
		$rules[] = (gallery_acl_check('i_rate', $album_id) && !$locked) ? $user->lang['ALBUM_RATE_CAN'] : $user->lang['ALBUM_RATE_CANNOT'];
	}

	foreach ($rules as $rule)
	{
		$template->assign_block_vars('rules', array('RULE' => $rule));
	}

	return;
}
?>