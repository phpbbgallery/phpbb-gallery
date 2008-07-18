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


//see GALLERY_ROOT_PATH/permissions_overview.php for a short description
function get_album_access_array ()
{
	global $cache, $db, $user;
	global $album_access_array, $album_config;

	if ($album_config == array())
	{
		// Get Album Config
		$sql = 'SELECT *
			FROM ' . GALLERY_CONFIG_TABLE;
		$result = $db->sql_query($sql);

		while( $row = $db->sql_fetchrow($result) )
		{
			$album_config_name = $row['config_name'];
			$album_config_value = $row['config_value'];
			$album_config[$album_config_name] = $album_config_value;
		}
	}
	$albums = $cache->obtain_album_list();
	$permissions = array('i_view', 'i_upload', 'i_edit', 'i_delete', 'i_approve', 'i_lock', 'i_report', 'i_count', 'a_moderate', 'album_count');
	//if ($album_config['rate'])
	//{
			$permissions = array_merge($permissions, array('i_rate'));
	//}
	//if ($album_config['comment'])
	//{
			$permissions = array_merge($permissions, array('c_post', 'c_edit', 'c_delete'));
	//}

	if (!$album_access_array)
	{
		$user_groups_ary = $pull_data = '';

		//set all parts of the permissions to 0 / "no"
		foreach ($permissions as $permission)
		{
			$album_access_array[-1][$permission] = 0;
			$album_access_array[-2][$permission] = 0;
			$album_access_array[-3][$permission] = 0;
			//generate for the sql
			$pull_data .= " MAX($permission) as $permission,";
		}
		foreach ($albums as $album)
		{
			foreach ($permissions as $permission)
			{
				$album_access_array[$album['album_id']][$permission] = 0;
			}
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
			$user_groups_ary .= (($user_groups_ary) ? ', ' : '') . $row['group_id'];
		}
		$db->sql_freeresult($result);

		$sql = "SELECT p.perm_album_id, $pull_data p.perm_system
			FROM " . GALLERY_PERMISSIONS_TABLE . " as p
			LEFT JOIN " .  GALLERY_ROLES_TABLE .  " as pr
				ON p.perm_role_id = pr.role_id
			WHERE ( p.perm_user_id = $user_id
				OR p.perm_group_id IN ($user_groups_ary))
			GROUP BY p.perm_system DESC, p.perm_album_id ASC";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			switch ($row['perm_system'])
			{
				case 3:
					foreach ($permissions as $permission)
					{
						$album_access_array[-3][$permission] = $row[$permission];
					}
				break;

				case 2:
					foreach ($permissions as $permission)
					{
						$album_access_array[-2][$permission] = $row[$permission];
					}
				break;

				case 1:
					foreach ($permissions as $permission)
					{
						// if the permission is true ($row[$permission] == 1) and global_permission is never ($album_access_array[-3][$permission] == 2) we set it to "never"
						$album_access_array[$row['perm_album_id']][$permission] = (($row[$permission]) ? (($row[$permission] == 1 && ($album_access_array[-3][$permission] == 2)) ? $album_access_array[-3][$permission] : $row[$permission]) : 0);
					}
				break;

				case 0:
					foreach ($permissions as $permission)
					{
						$album_access_array[$row['perm_album_id']][$permission] = $row[$permission];
					}
				break;

				default:
					trigger_error('PERMISSION_SYSTEM_FAILURE_01');
				break;
			}
		}
		$db->sql_freeresult($result);
	}

	return $album_access_array;
}

?>