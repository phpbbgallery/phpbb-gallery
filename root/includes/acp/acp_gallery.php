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
* @package acp
*/
class acp_gallery
{
	var $u_action;

	function main($id, $mode)
	{
		global $gallery_config, $db, $template, $user, $permissions;
		global $gallery_root_path, $phpbb_root_path, $phpEx;
		$gallery_root_path = GALLERY_ROOT_PATH;

		include($phpbb_root_path . $gallery_root_path . 'includes/constants.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/functions.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/permissions.' . $phpEx);
		$gallery_config = load_gallery_config();

		$user->add_lang('mods/gallery_acp');
		$user->add_lang('mods/gallery');
		$this->tpl_name = 'gallery_main';
		add_form_key('acp_gallery');
		$submode = request_var('submode', '');

		/**
		* All our beautiful permissions
		*/
		$permissions->cats['full'] = array(
			'i'		=> array('i_view', 'i_watermark', 'i_upload', 'i_approve', 'i_edit', 'i_delete', 'i_report', 'i_rate'),
			'c'		=> array('c_read', 'c_post', 'c_edit', 'c_delete'),
			'm'		=> array('m_comments', 'm_delete', 'm_edit', 'm_move', 'm_report', 'm_status'),
			'misc'	=> array('a_list', 'i_count', 'album_count'),
		);
		$permissions->p_masks['full'] = array_merge($permissions->cats['full']['i'], $permissions->cats['full']['c'], $permissions->cats['full']['m'], $permissions->cats['full']['misc']);

		// Permissions for the normal albums
		$permissions->cats[0] = array(
			'i'		=> array('i_view', 'i_watermark', 'i_upload', 'i_approve', 'i_edit', 'i_delete', 'i_report', 'i_rate'),
			'c'		=> array('c_read', 'c_post', 'c_edit', 'c_delete'),
			'm'		=> array('m_comments', 'm_delete', 'm_edit', 'm_move', 'm_report', 'm_status'),
			'misc'	=> array('a_list', 'i_count'/*, 'album_count'*/),
		);
		$permissions->p_masks[0] = array_merge($permissions->cats[0]['i'], $permissions->cats[0]['c'], $permissions->cats[0]['m'], $permissions->cats[0]['misc']);
		$permissions->p_masks_anti[0] = array('album_count');

		// Permissions for own personal albums
		// Note: we set i_view to 1 as default on storing the permissions
		$permissions->cats[OWN_GALLERY_PERMISSIONS] = array(
			'i'		=> array(/*'i_view', */'i_watermark', 'i_upload', 'i_approve', 'i_edit', 'i_delete', 'i_report', 'i_rate'),
			'c'		=> array('c_read', 'c_post', 'c_edit', 'c_delete'),
			'm'		=> array('m_comments', 'm_delete', 'm_edit', 'm_move', 'm_report', 'm_status'),
			'misc'	=> array('a_list', 'i_count', 'album_count'),
		);
		$permissions->p_masks[OWN_GALLERY_PERMISSIONS] = array_merge($permissions->cats[OWN_GALLERY_PERMISSIONS]['i'], $permissions->cats[OWN_GALLERY_PERMISSIONS]['c'], $permissions->cats[OWN_GALLERY_PERMISSIONS]['m'], $permissions->cats[OWN_GALLERY_PERMISSIONS]['misc']);
		$permissions->p_masks_anti[OWN_GALLERY_PERMISSIONS] = array();// Note: we set i_view to 1 as default, so it's not needed on anti array('i_view');

		// Permissions for personal albums of other users
		// Note: Do !NOT! hide the i_upload. It's used for the moving-permissions
		$permissions->cats[PERSONAL_GALLERY_PERMISSIONS] = array(
			'i'		=> array('i_view', 'i_watermark', 'i_upload', /*'i_approve', 'i_edit', 'i_delete', */'i_report', 'i_rate'),
			'c'		=> array('c_read', 'c_post', 'c_edit', 'c_delete'),
			'm'		=> array('m_comments', 'm_delete', 'm_edit', 'm_move', 'm_report', 'm_status'),
			'misc'	=> array('a_list'/*, 'i_count', 'album_count'*/),
		);
		$permissions->p_masks[PERSONAL_GALLERY_PERMISSIONS] = array_merge($permissions->cats[PERSONAL_GALLERY_PERMISSIONS]['i'], $permissions->cats[PERSONAL_GALLERY_PERMISSIONS]['c'], $permissions->cats[PERSONAL_GALLERY_PERMISSIONS]['m'], $permissions->cats[PERSONAL_GALLERY_PERMISSIONS]['misc']);
		$permissions->p_masks_anti[PERSONAL_GALLERY_PERMISSIONS] = array('i_approve', 'i_edit', 'i_delete', 'i_count', 'album_count');

		switch ($mode)
		{
			case 'overview':
				$title = 'ACP_GALLERY_OVERVIEW';
				$this->page_title = $user->lang[$title];

				$this->overview();
			break;

			case 'album_permissions':
				$title = 'ALBUM_AUTH_TITLE';
				$this->tpl_name = 'gallery_permissions';
				$this->page_title = $user->lang[$title];
				$submit = (isset($_POST['submit_edit_options'])) ? true : ((isset($_POST['submit_add_options'])) ? true : false);

				switch ($submode)
				{
					case 'set':
						$this->permissions_set();
					break;
					case 'v_mask':
						if (!$submit)
						{
							$this->permissions_v_mask();
						}
						else
						{
							$this->permissions_p_mask();
						}
					break;
					default:
						$this->permissions_c_mask();
					break;
				}
			break;

			case 'import_images':
				$title = 'ACP_IMPORT_ALBUMS';
				$this->page_title = $user->lang[$title];

				$this->import();
			break;

			case 'cleanup':
				$title = 'ACP_GALLERY_CLEANUP';
				$this->page_title = $user->lang[$title];

				$this->cleanup();
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
	}

	function overview()
	{
		global $gallery_config, $template, $user, $db, $phpbb_root_path, $config, $auth;

		$action = request_var('action', '');
		$id = request_var('i', '');
		$mode = 'overview';

		if (!confirm_box(true))
		{
			$confirm = false;
			$album_id = 0;
			switch ($action)
			{
				case 'images':
					$confirm = true;
					$confirm_lang = 'RESYNC_IMAGECOUNTS_CONFIRM';
				break;
				case 'personals':
					$confirm = true;
					$confirm_lang = 'CONFIRM_OPERATION';
				break;
				case 'stats':
					$confirm = true;
					$confirm_lang = 'CONFIRM_OPERATION';
				break;
				case 'last_images':
					$confirm = true;
					$confirm_lang = 'CONFIRM_OPERATION';
				break;
				case 'reset_rating':
					$album_id = request_var('reset_album_id', 0);
					$album_data = get_album_info($album_id);
					$confirm = true;
					$confirm_lang = sprintf($user->lang['RESET_RATING_CONFIRM'], $album_data['album_name']);
				break;
				case 'purge_cache':
					$confirm = true;
					$confirm_lang = 'GALLERY_PURGE_CACHE_EXPLAIN';
				break;
			}

			if ($confirm)
			{
				confirm_box(false, (($album_id) ? $confirm_lang : $user->lang[$confirm_lang]), build_hidden_fields(array(
					'i'			=> $id,
					'mode'		=> $mode,
					'action'	=> $action,
					'reset_album_id'	=> $album_id,
				)));
			}
		}
		else
		{
			switch ($action)
			{
				case 'images':
					if (!$auth->acl_get('a_board'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$total_images = 0;
					$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
						SET user_images = 0';
					$db->sql_query($sql);

					$sql = 'SELECT COUNT(image_id) num_images, image_user_id user_id
						FROM ' . GALLERY_IMAGES_TABLE . '
						WHERE image_status = ' . IMAGE_APPROVED . '
						GROUP BY image_user_id';
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$total_images += $row['num_images'];
						$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
							SET user_images = ' . $row['num_images'] . '
							WHERE user_id = ' . $row['user_id'];
						$db->sql_query($sql);

						if ($db->sql_affectedrows() <= 0)
						{
							$sql_ary = array(
								'user_id'				=> $row['user_id'],
								'user_images'			=> $row['num_images'],
							);
							$sql = 'INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
							$db->sql_query($sql);
						}
					}
					$db->sql_freeresult($result);

					set_config('num_images', $total_images, true);
					trigger_error($user->lang['RESYNCED_IMAGECOUNTS'] . adm_back_link($this->u_action));
				break;

				case 'personals':
					if (!$auth->acl_get('a_board'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$sql = 'UPDATE ' . GALLERY_USERS_TABLE . "
						SET personal_album_id = 0";
					$db->sql_query($sql);

					$sql = 'SELECT album_id, album_user_id
						FROM ' . GALLERY_ALBUMS_TABLE . '
						WHERE album_user_id <> 0
							AND parent_id = 0
						GROUP BY album_user_id';
					$result = $db->sql_query($sql);

					$number_of_personals = 0;
					while ($row = $db->sql_fetchrow($result))
					{
						$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
							SET personal_album_id = ' . $row['album_id'] . '
							WHERE user_id = ' . $row['album_user_id'];
						$db->sql_query($sql);

						if ($db->sql_affectedrows() <= 0)
						{
							$sql_ary = array(
								'user_id'				=> $row['album_user_id'],
								'personal_album_id'		=> $row['album_id'],
							);
							$sql = 'INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
							$db->sql_query($sql);
						}
						$number_of_personals++;
					}
					$db->sql_freeresult($result);
					set_gallery_config('personal_counter', $number_of_personals);

					trigger_error($user->lang['RESYNCED_PERSONALS'] . adm_back_link($this->u_action));
				break;

				case 'stats':
					if (!$auth->acl_get('a_board'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					// Hopefully this won't take to long! >> I think we must make it bunchwise
					$sql = 'SELECT image_id, image_filename, image_thumbnail
						FROM ' . GALLERY_IMAGES_TABLE . '
						WHERE filesize_upload = 0';
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$sql_ary = array(
							'filesize_upload'		=> @filesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $row['image_filename']),
							'filesize_medium'		=> @filesize($phpbb_root_path . GALLERY_MEDIUM_PATH . $row['image_thumbnail']),
							'filesize_cache'		=> @filesize($phpbb_root_path . GALLERY_CACHE_PATH . $row['image_thumbnail']),
						);
						$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE ' . $db->sql_in_set('image_id', $row['image_id']);
						$db->sql_query($sql);
					}
					$db->sql_freeresult($result);

					redirect($this->u_action);
				break;

				case 'last_images':
					$sql = 'SELECT album_id
						FROM ' . GALLERY_ALBUMS_TABLE;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						// 5 sql's per album, but you don't run this daily ;)
						update_album_info($row['album_id']);
					}
					$db->sql_freeresult($result);
					trigger_error($user->lang['RESYNCED_LAST_IMAGES'] . adm_back_link($this->u_action));
				break;

				case 'reset_rating':
					$album_id = request_var('reset_album_id', 0);
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET image_rates = 0,
							image_rate_points = 0,
							image_rate_avg = 0
						WHERE image_album_id = ' . $album_id;
					$db->sql_query($sql);

					$image_ids = array();
					$sql = 'SELECT image_id
						FROM ' . GALLERY_IMAGES_TABLE . '
						WHERE image_album_id = ' . $album_id;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$image_ids[] = $row['image_id'];
					}
					$db->sql_freeresult($result);

					$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . '
						WHERE ' . $db->sql_in_set('rate_image_id', $image_ids);
					$db->sql_query($sql);

					trigger_error($user->lang['RESET_RATING_COMPLETED'] . adm_back_link($this->u_action));
				break;

				case 'purge_cache':
					if ($user->data['user_type'] != USER_FOUNDER)
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$cache_dir = @opendir($phpbb_root_path . GALLERY_CACHE_PATH);
					while ($cache_file = @readdir($cache_dir))
					{
						if (preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $cache_file))
						{
							@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $cache_file);
						}
					}
					@closedir($cache_dir);

					$medium_dir = @opendir($phpbb_root_path . GALLERY_MEDIUM_PATH);
					while ($medium_file = @readdir($medium_dir))
					{
						if (preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $medium_file))
						{
							@unlink($phpbb_root_path . GALLERY_MEDIUM_PATH . $medium_file);
						}
					}
					@closedir($medium_dir);

					$sql_ary = array(
						'filesize_medium'		=> @filesize($phpbb_root_path . GALLERY_MEDIUM_PATH . $row['image_thumbnail']),
						'filesize_cache'		=> @filesize($phpbb_root_path . GALLERY_CACHE_PATH . $row['image_thumbnail']),
					);
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql_ary);
					$db->sql_query($sql);

					trigger_error($user->lang['PURGED_CACHE'] . adm_back_link($this->u_action));
				break;
			}
		}

		$boarddays = (time() - $config['board_startdate']) / 86400;
		$images_per_day = sprintf('%.2f', $config['num_images'] / $boarddays);

		$sql = 'SELECT COUNT(album_user_id) num_albums
			FROM ' . GALLERY_ALBUMS_TABLE . '
			WHERE album_user_id = 0';
		$result = $db->sql_query($sql);
		$num_albums = (int) $db->sql_fetchfield('num_albums');
		$db->sql_freeresult($result);

		$sql = 'SELECT SUM(filesize_upload) as stat, SUM(filesize_medium) as stat_medium, SUM(filesize_cache) as stat_cache
			FROM ' . GALLERY_IMAGES_TABLE;
		$result = $db->sql_query($sql);
		$dir_sizes = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'S_GALLERY_OVERVIEW'			=> true,
			'ACP_GALLERY_TITLE'				=> $user->lang['ACP_GALLERY_OVERVIEW'],
			'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ACP_GALLERY_OVERVIEW_EXPLAIN'],

			'TOTAL_IMAGES'			=> $config['num_images'],
			'IMAGES_PER_DAY'		=> $images_per_day,
			'TOTAL_ALBUMS'			=> $num_albums,
			'TOTAL_PERSONALS'		=> $gallery_config['personal_counter'],
			'GUPLOAD_DIR_SIZE'		=> get_formatted_filesize($dir_sizes['stat']),
			'MEDIUM_DIR_SIZE'		=> get_formatted_filesize($dir_sizes['stat_medium']),
			'CACHE_DIR_SIZE'		=> get_formatted_filesize($dir_sizes['stat_cache']),
			'GALLERY_VERSION'		=> $gallery_config['phpbb_gallery_version'],

			'S_FOUNDER'				=> ($user->data['user_type'] == USER_FOUNDER) ? true : false,
		));
	}

	function permissions_c_mask()
	{
		global $cache, $template;

		// Send contants to the template
		$template->assign_vars(array(
			'C_OWN_PERSONAL_ALBUMS'	=> OWN_GALLERY_PERMISSIONS,
			'C_PERSONAL_ALBUMS'		=> PERSONAL_GALLERY_PERMISSIONS,
		));

		$submit = (isset($_POST['submit'])) ? true : false;
		$albums = $cache->obtain_album_list();

		$template->assign_vars(array(
			'U_ACTION'					=> $this->u_action . '&amp;submode=v_mask',
			'S_PERMISSION_C_MASK'		=> true,
			'ALBUM_LIST'				=> gallery_albumbox(true, '', SETTING_PERMISSIONS),
		));
	}

	function permissions_v_mask()
	{
		global $cache, $db, $template, $user;
		$user->add_lang('acp/permissions');

		$submit = (isset($_POST['submit'])) ? true : false;
		$delete = (isset($_POST['delete'])) ? true : false;
		$album_id = request_var('album_id', array(0));
		$group_id = request_var('group_id', array(0));
		$p_system = request_var('p_system', 0);

		// Delete permissions
		if ($delete)
		{
			// Delete group permissions
			if (!empty($group_id))
			{
				// Get the possible outdated p_masks
				$sql = 'SELECT perm_role_id
					FROM ' . GALLERY_PERMISSIONS_TABLE . '
					WHERE ' . ((!$p_system) ? $db->sql_in_set('perm_album_id', $album_id) : $db->sql_in_set('perm_system', $p_system)) . '
						AND ' . $db->sql_in_set('perm_group_id', $group_id);
				$result = $db->sql_query($sql);

				$outdated_p_masks = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$outdated_p_masks[] = $row['perm_role_id'];
				}
				$db->sql_freeresult($result);

				// Delete the permissions and moderators
				$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . '
					WHERE ' . ((!$p_system) ? $db->sql_in_set('perm_album_id', $album_id) : $db->sql_in_set('perm_system', $p_system)) . '
						AND ' . $db->sql_in_set('perm_group_id', $group_id);
				$db->sql_query($sql);
				if (!$p_system)
				{
					// We do not display the moderators on personals so, just on albums
					$sql = 'DELETE FROM ' . GALLERY_MODSCACHE_TABLE . '
						WHERE ' . $db->sql_in_set('album_id', $album_id) . '
							AND ' . $db->sql_in_set('group_id', $group_id);
					$db->sql_query($sql);
				}

				// Check for further usage
				$sql = 'SELECT perm_role_id
					FROM ' . GALLERY_PERMISSIONS_TABLE . '
					WHERE ' . $db->sql_in_set('perm_role_id', $outdated_p_masks, false, true);
				$result = $db->sql_query($sql);

				$still_used_p_masks = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$still_used_p_masks[] = $row['perm_role_id'];
				}
				$db->sql_freeresult($result);

				// Delete the p_masks, which are no longer used
				$sql = 'DELETE FROM ' . GALLERY_ROLES_TABLE . '
					WHERE ' . $db->sql_in_set('role_id', $outdated_p_masks, false, true) . '
						AND ' . $db->sql_in_set('role_id', $still_used_p_masks, true, true);
				$db->sql_query($sql);
			}

			// Delete user permissions
			if (!empty($user_id))
			{
				// Get the possible outdated p_masks
				$sql = 'SELECT perm_role_id
					FROM ' . GALLERY_PERMISSIONS_TABLE . '
					WHERE ' . ((!$p_system) ? $db->sql_in_set('perm_album_id', $album_id) : $db->sql_in_set('perm_system', $p_system)) . '
						AND ' . $db->sql_in_set('perm_user_id', $user_id);
				$result = $db->sql_query($sql);

				$outdated_p_masks = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$outdated_p_masks[] = $row['perm_role_id'];
				}
				$db->sql_freeresult($result);

				// Delete the permissions and moderators
				$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . '
					WHERE ' . ((!$p_system) ? $db->sql_in_set('perm_album_id', $album_id) : $db->sql_in_set('perm_system', $p_system)) . '
						AND ' . $db->sql_in_set('perm_user_id', $user_id);
				$db->sql_query($sql);
				if (!$p_system)
				{
					// We do not display the moderators on personals so, just on albums
					$sql = 'DELETE FROM ' . GALLERY_MODSCACHE_TABLE . '
						WHERE ' . $db->sql_in_set('album_id', $album_id) . '
							AND ' . $db->sql_in_set('user_id', $user_id);
					$db->sql_query($sql);
				}

				// Check for further usage
				$sql = 'SELECT perm_role_id
					FROM ' . GALLERY_PERMISSIONS_TABLE . '
					WHERE ' . $db->sql_in_set('perm_role_id', $outdated_p_masks, false, true);
				$result = $db->sql_query($sql);

				$still_used_p_masks = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$still_used_p_masks[] = $row['perm_role_id'];
				}
				$db->sql_freeresult($result);

				// Delete the p_masks, which are no longer used
				$sql = 'DELETE FROM ' . GALLERY_ROLES_TABLE . '
					WHERE ' . $db->sql_in_set('role_id', $outdated_p_masks, false, true) . '
						AND ' . $db->sql_in_set('role_id', $still_used_p_masks, true, true);
				$db->sql_query($sql);
			}

			// Only clear if we did something
			if (!empty($group_id) || !empty($user_id))
			{
				$cache->destroy('sql', GALLERY_PERMISSIONS_TABLE);
				$cache->destroy('sql', GALLERY_ROLES_TABLE);
				$cache->destroy('sql', GALLERY_MODSCACHE_TABLE);
			}
		}

		if (!$p_system)
		{
			// Get the album names of the selected albums
			$sql = 'SELECT album_name
				FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE ' . $db->sql_in_set('album_id', $album_id, false, true) . '
				ORDER BY left_id';
			$result = $db->sql_query($sql);

			$a_names = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$a_names[] = $row['album_name'];
			}
			$db->sql_freeresult($result);
		}

		// Get the groups for selected album/p_system
		$sql = 'SELECT g.group_name, g.group_id, g.group_type
			FROM ' . GROUPS_TABLE . ' g
			LEFT JOIN ' . GALLERY_PERMISSIONS_TABLE . ' gp
				ON gp.perm_group_id = g.group_id
			WHERE ' . ((!$p_system) ? $db->sql_in_set('gp.perm_album_id', $album_id, false, true) : $db->sql_in_set('gp.perm_system', $p_system, false, true)) . '
			GROUP BY g.group_id';
		$result = $db->sql_query($sql);

		$set_groups = array();
		$s_defined_group_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$set_groups[] = $row['group_id'];
			$s_defined_group_options .= '<option value="' . $row['group_id'] . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
		}
		$db->sql_freeresult($result);

		// Get the other groups, so that the user can add them
		$sql = 'SELECT group_name, group_id, group_type
			FROM ' . GROUPS_TABLE . '
			WHERE ' . $db->sql_in_set('group_id', $set_groups, true, true) . '
			GROUP BY group_id';
		$result = $db->sql_query($sql);

		$s_add_group_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$s_add_group_options .= '<option value="' . $row['group_id'] . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
		}
		$db->sql_freeresult($result);

		// Setting permissions screen
		$s_hidden_fields = build_hidden_fields(array(
			'album_id'		=> $album_id,
			'p_system'		=> $p_system,
		));

		$template->assign_vars(array(
			'S_HIDDEN_FIELDS'			=> $s_hidden_fields,
			'U_ACTION'					=> $this->u_action . '&amp;submode=v_mask',
			'S_PERMISSION_V_MASK'		=> true,

			'C_MASKS_NAMES'				=> (!$p_system) ? implode(', ', $a_names) : (($p_system == OWN_GALLERY_PERMISSIONS) ? $user->lang['OWN_PERSONAL_ALBUMS'] : $user->lang['PERSONAL_ALBUMS']),
			'L_C_MASKS'					=> $user->lang['ALBUMS'],

			'S_CAN_SELECT_GROUP'		=> true,
			'S_DEFINED_GROUP_OPTIONS'	=> $s_defined_group_options,
			'S_ADD_GROUP_OPTIONS'		=> $s_add_group_options,
		));
	}

	function permissions_p_mask()
	{
		global $cache, $db, $permissions, $template, $user;
		$user->add_lang('acp/permissions');

		if (!check_form_key('acp_gallery'))
		{
			trigger_error('FORM_INVALID');
		}

		$submit = (isset($_POST['submit'])) ? true : false;
		$delete = (isset($_POST['delete'])) ? true : false;
		$album_id = request_var('album_id', array(0));
		$group_id = request_var('group_id', array(0));
		$user_id = request_var('user_id', array(0));
		$p_system = request_var('p_system', 0);

		// Create the loops for the javascript
		for ($i = 0; $i < sizeof($permissions->cats[$p_system]); $i++)
		{
			$template->assign_block_vars('c_rows', array());
		}

		// Get the group information
		$sql = 'SELECT group_name, group_id, group_type, group_colour
			FROM ' . GROUPS_TABLE . '
			WHERE ' . $db->sql_in_set('group_id', $group_id);
		$result = $db->sql_query($sql);

		$victim_list = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$row['group_name'] = (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']);
			$victim_list[$row['group_id']] = $row;
		}
		$db->sql_freeresult($result);

		// Fetch the full-permissions-tree
		$sql = 'SELECT perm_role_id, perm_group_id, perm_album_id
			FROM ' . GALLERY_PERMISSIONS_TABLE . '
			WHERE ' . ((!$p_system) ? $db->sql_in_set('perm_album_id', $album_id) : $db->sql_in_set('perm_system', $p_system)) . '
				AND ' . $db->sql_in_set('perm_group_id', $group_id);
		$result = $db->sql_query($sql);

		$p_masks = $fetch_roles = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$fetch_roles[] = $row['perm_role_id'];
			$p_masks[((!$p_system) ? $row['perm_album_id'] : $p_system)][$row['perm_group_id']] = $row['perm_role_id'];
		}
		$db->sql_freeresult($result);

		// Fetch the roles
		$roles = array();
		if (!empty($fetch_roles))
		{
			$sql = 'SELECT *
				FROM ' . GALLERY_ROLES_TABLE . '
				WHERE ' . $db->sql_in_set('role_id', $fetch_roles);
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$roles[$row['role_id']] = $row;
			}
			$db->sql_freeresult($result);
		}

		// Album permissions
		if (!$p_system)
		{
			$album_list = $cache->obtain_album_list();
			foreach ($album_id as $album)
			{
				$album_row = $album_list[$album];
				$template->assign_block_vars('c_mask', array(
					'C_MASK_ID'				=> $album_row['album_id'],
					'C_MASK_NAME'			=> $album_row['album_name'],
					'INHERIT_C_MASKS'		=> $this->inherit_albums($album_list, $album_id, $album_row['album_id']),
				));
				foreach ($group_id as $group)
				{
					$group_row = $victim_list[$group];
					$template->assign_block_vars('c_mask.v_mask', array(
						'VICTIM_ID'				=> $group_row['group_id'],
						'VICTIM_NAME'			=> '<span' . (($group_row['group_colour']) ? (' style="color: #' . $group_row['group_colour'] . '"') : '') . '>' . $group_row['group_name'] . '</span>',
						'INHERIT_VICTIMS'		=> $this->inherit_victims($album_list, $album_id, $victim_list, $album_row['album_id'], $group_row['group_id']),
					));
					$role_id = (isset($p_masks[$album_row['album_id']][$group_row['group_id']])) ? $p_masks[$album_row['album_id']][$group_row['group_id']] : 0;
					foreach ($permissions->cats[$p_system] as $category => $permission_values)
					{
						$template->assign_block_vars('c_mask.v_mask.category', array(
							'CAT_NAME'				=> $user->lang['PERMISSION_' . strtoupper($category)],
							'PERM_GROUP_ID'			=> $category,
						));
						foreach ($permission_values as $permission)
						{
							$template->assign_block_vars('c_mask.v_mask.category.mask', array(
								'PERMISSION'			=> $user->lang['PERMISSION_' . strtoupper($permission)],
								'S_FIELD_NAME'			=> 'setting[' . $album_row['album_id'] . '][' . $group_row['group_id'] . '][' . $permission . ']',
								'S_NO'					=> ((isset($roles[$role_id][$permission]) && ($roles[$role_id][$permission] == GALLERY_ACL_NO)) ? true : false),
								'S_YES'					=> ((isset($roles[$role_id][$permission]) && ($roles[$role_id][$permission] == GALLERY_ACL_YES)) ? true : false),
								'S_NEVER'				=> ((isset($roles[$role_id][$permission]) && ($roles[$role_id][$permission] == GALLERY_ACL_NEVER)) ? true : false),
								'S_VALUE'				=> ((isset($roles[$role_id][$permission])) ? $roles[$role_id][$permission] : 0),
								'S_COUNT_FIELD'			=> (substr($permission, -6, 6) == '_count') ? true : false,
							));
						}
					}
				}
			}
		}
		else
		{
			$template->assign_block_vars('c_mask', array(
				'C_MASK_ID'				=> $p_system,
				'C_MASK_NAME'			=> (($p_system == OWN_GALLERY_PERMISSIONS) ? $user->lang['OWN_PERSONAL_ALBUMS'] : $user->lang['PERSONAL_ALBUMS']),
			));
			foreach ($group_id as $group)
			{
				$group_row = $victim_list[$group];
				$template->assign_block_vars('c_mask.v_mask', array(
					'VICTIM_ID'				=> $group_row['group_id'],
					'VICTIM_NAME'			=> '<span' . (($group_row['group_colour']) ? (' style="color: #' . $group_row['group_colour'] . '"') : '') . '>' . $group_row['group_name'] . '</span>',
					'INHERIT_VICTIMS'		=> $this->p_system_inherit_victims($p_system, $victim_list, $group_row['group_id']),
				));
				$role_id = (isset($p_masks[$p_system][$group_row['group_id']])) ? $p_masks[$p_system][$group_row['group_id']] : 0;
				foreach ($permissions->cats[$p_system] as $category => $permission_values)
				{
					$template->assign_block_vars('c_mask.v_mask.category', array(
						'CAT_NAME'				=> $user->lang['PERMISSION_' . strtoupper($category)],
						'PERM_GROUP_ID'			=> $category,
					));
					foreach ($permission_values as $permission)
					{
						$template->assign_block_vars('c_mask.v_mask.category.mask', array(
							'PERMISSION'			=> $user->lang['PERMISSION_' . strtoupper($permission)],
							'S_FIELD_NAME'			=> 'setting[' . $p_system . '][' . $group_row['group_id'] . '][' . $permission . ']',
							'S_NO'					=> ((isset($roles[$role_id][$permission]) && ($roles[$role_id][$permission] == GALLERY_ACL_NO)) ? true : false),
							'S_YES'					=> ((isset($roles[$role_id][$permission]) && ($roles[$role_id][$permission] == GALLERY_ACL_YES)) ? true : false),
							'S_NEVER'				=> ((isset($roles[$role_id][$permission]) && ($roles[$role_id][$permission] == GALLERY_ACL_NEVER)) ? true : false),
							'S_VALUE'				=> ((isset($roles[$role_id][$permission])) ? $roles[$role_id][$permission] : 0),
							'S_COUNT_FIELD'			=> (substr($permission, -6, 6) == '_count') ? true : false,
						));
					}
				}
			}
		}

		// Setting permissions screen
		$s_hidden_fields = build_hidden_fields(array(
			'user_id'		=> $user_id,
			'group_id'		=> $group_id,
			'album_id'		=> $album_id,
			'p_system'		=> $p_system,
		));

		$template->assign_vars(array(
			'S_HIDDEN_FIELDS'			=> $s_hidden_fields,
			'U_ACTION'					=> $this->u_action . '&amp;submode=set',
			'S_PERMISSION_P_MASK'		=> true,
		));
	}

	function permissions_set()
	{
		global $cache, $db, $permissions, $template, $user;
		global $phpbb_admin_path, $phpEx;

		// Send contants to the template
		$submit = (isset($_POST['submit'])) ? true : false;
		$album_id = request_var('album_id', array(0));
		$group_id = request_var('group_id', array(0));
		$user_id = request_var('user_id', array(0));
		$p_system = request_var('p_system', 0);

		if ($submit)
		{
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}
			$coal = $cache->obtain_album_list();

			/**
			* Grab the permissions
			*
			* includes/acp/acp_permissions.php says:
			* // We obtain and check $_POST['setting'][$ug_id][$forum_id] directly and not using request_var() because request_var()
			* // currently does not support the amount of dimensions required. ;)
			*/
			//		$auth_settings = request_var('setting', array(0 => array(0 => array('' => 0))));
			$p_mask_count = 0;
			$auth_settings = $p_mask_storage = $c_mask_storage = $v_mask_storage = array();
			foreach ($_POST['setting'] as $c_mask => $v_sets)
			{
				$c_mask = (int) $c_mask;
				$c_mask_storage[] = $c_mask;
				$auth_settings[$c_mask] = array();
				foreach ($v_sets as $v_mask => $p_sets)
				{
					$v_mask = (int) $v_mask;
					$v_mask_storage[] = $v_mask;
					$auth_settings[$c_mask][$v_mask] = array();
					$is_moderator = false;
					foreach ($p_sets as $p_mask => $value)
					{
						if (!in_array($p_mask, $permissions->p_masks[$p_system]))
						{
							// An admin tried to set a non-existing permission. Hacking attempt?!
							trigger_error('HACKING_ATTEMPT', E_USER_WARNING);
						}
						// Casted all values to integer and checked all strings whether they are permissions!
						// Should be fine than for the .com MOD-Team now =)
						$value = (int) $value;
						if (substr($p_mask, -6, 6) == '_count')
						{
							$auth_settings[$c_mask][$v_mask][$p_mask] = $value;
						}
						else
						{
							$auth_settings[$c_mask][$v_mask][$p_mask] = ($value == ACL_YES) ? GALLERY_ACL_YES : (($value == ACL_NEVER) ? GALLERY_ACL_NEVER : GALLERY_ACL_NO);
							// Do we have moderators?
							if ((substr($p_mask, 0, 2) == 'm_') && ($value == ACL_YES))
							{
								$is_moderator = true;
							}
						}
					}
					// Need to set a defaults here: view your own personal album images
					if ($p_system == OWN_GALLERY_PERMISSIONS)
					{
						$auth_settings[$c_mask][$v_mask]['i_view'] = 1;
					}

					$p_mask_storage[$p_mask_count]['p_mask'] = $auth_settings[$c_mask][$v_mask];
					$p_mask_storage[$p_mask_count]['is_moderator'] = $is_moderator;
					$p_mask_storage[$p_mask_count]['usage'][] = array('c_mask' => $c_mask, 'v_mask' => $v_mask);
					$auth_settings[$c_mask][$v_mask] = $p_mask_count;
					$p_mask_count++;
				}
			}
			/**
			* Inherit the permissions
			*/
			foreach ($_POST['inherit'] as $c_mask => $v_sets)
			{
				$c_mask = (int) $c_mask;
				foreach ($v_sets as $v_mask => $i_mask)
				{
					if (($v_mask == 'full') && $i_mask)
					{
						$i_mask = (int) $i_mask;
						// Inherit all permissions of an other c_mask
						if (isset($auth_settings[$i_mask]))
						{
							if ($this->inherit_albums($coal, $c_mask_storage, $c_mask, $i_mask))
							{
								foreach ($auth_settings[$c_mask] as $v_mask => $p_mask)
								{
									// You are not able to inherit a later c_mask, so we can remove the p_mask from the storage,
									// and just use the same p_mask
									unset($p_mask_storage[$auth_settings[$c_mask][$v_mask]]);
									$auth_settings[$c_mask][$v_mask] = $auth_settings[$i_mask][$v_mask];
									$p_mask_storage[$auth_settings[$c_mask][$v_mask]]['usage'][] = array('c_mask' => $c_mask, 'v_mask' => $v_mask);
								}
								// We take all permissions of another c_mask, so:
								break;
							}
							else
							{
								// The choosen option was disabled: Hacking attempt?!
								trigger_error('HACKING_ATTEMPT', E_USER_WARNING);
							}
						}
					}
					elseif ($i_mask)
					{
						// Inherit permissions of one [c_mask][v_mask]
						$v_mask = (int) $v_mask;
						list($ci_mask, $vi_mask) = explode("_", $i_mask);
						$ci_mask = (int) $ci_mask;
						$vi_mask = (int) $vi_mask;
						if (isset($auth_settings[$ci_mask][$vi_mask]))
						{
							$no_hacking_attempt = ((!$p_system) ? $this->inherit_victims($coal, $c_mask_storage, $v_mask_storage, $c_mask, $v_mask, $ci_mask, $vi_mask) : $this->p_system_inherit_victims($p_system, $v_mask_storage, $v_mask, $vi_mask));
							if ($no_hacking_attempt)
							{
								// You are not able to inherit a later c_mask, so we can remove the p_mask from the storage,
								// and just use the same p_mask
								if (isset($auth_settings[$c_mask][$v_mask]))
								{
									// Should exist, but didn't on testing so only do it, when it does exist
									unset($p_mask_storage[$auth_settings[$c_mask][$v_mask]]);
								}
								$auth_settings[$c_mask][$v_mask] = $auth_settings[$ci_mask][$vi_mask];
								$p_mask_storage[$auth_settings[$c_mask][$v_mask]]['usage'][] = array('c_mask' => $c_mask, 'v_mask' => $v_mask);
							}
							else
							{
								// The choosen option was disabled: Hacking attempt?!
								trigger_error('HACKING_ATTEMPT', E_USER_WARNING);
							}
						}
					}
				}
			}
			unset($auth_settings);

			// Get the possible outdated p_masks
			$sql = 'SELECT perm_role_id
				FROM ' . GALLERY_PERMISSIONS_TABLE . '
				WHERE ' . ((!$p_system) ? $db->sql_in_set('perm_album_id', $album_id) : $db->sql_in_set('perm_system', $p_system)) . '
					AND ' . $db->sql_in_set('perm_group_id', $v_mask_storage);
			$result = $db->sql_query($sql);

			$outdated_p_masks = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$outdated_p_masks[] = $row['perm_role_id'];
			}
			$db->sql_freeresult($result);

			// Delete the permissions and moderators
			$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . '
				WHERE ' . ((!$p_system) ? $db->sql_in_set('perm_album_id', $album_id) : $db->sql_in_set('perm_system', $p_system)) . '
					AND ' . $db->sql_in_set('perm_group_id', $v_mask_storage);
			$db->sql_query($sql);
			if (!$p_system)
			{
				$sql = 'DELETE FROM ' . GALLERY_MODSCACHE_TABLE . '
					WHERE ' . $db->sql_in_set('album_id', $c_mask_storage) . '
						AND ' . $db->sql_in_set('group_id', $v_mask_storage);
				$db->sql_query($sql);
			}

			// Check for further usage
			$sql = 'SELECT perm_role_id
				FROM ' . GALLERY_PERMISSIONS_TABLE . '
				WHERE ' . $db->sql_in_set('perm_role_id', $outdated_p_masks, false, true);
			$result = $db->sql_query($sql);

			$still_used_p_masks = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$still_used_p_masks[] = $row['perm_role_id'];
			}
			$db->sql_freeresult($result);

			// Delete the p_masks, which are no longer used
			$sql = 'DELETE FROM ' . GALLERY_ROLES_TABLE . '
				WHERE ' . $db->sql_in_set('role_id', $outdated_p_masks, false, true) . '
					AND ' . $db->sql_in_set('role_id', $still_used_p_masks, true, true);
			$db->sql_query($sql);

			$group_names = array();
			if (!$p_system)
			{
				// Get group_name's for the GALLERY_MODSCACHE_TABLE
				$sql = 'SELECT group_id, group_name
					FROM ' . GROUPS_TABLE . '
					WHERE ' . $db->sql_in_set('group_id', $v_mask_storage);
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$group_names[$row['group_id']] = $row['group_name'];
				}
				$db->sql_freeresult($result);
			}

			$sql_permissions = $sql_moderators = array();
			foreach ($p_mask_storage as $p_set)
			{
				// Check whether the p_mask is already in the DB
				$sql_where = '';
				foreach ($p_set['p_mask'] as $p_mask => $value)
				{
					$sql_where .= (($sql_where) ? ' AND ' : '') . $p_mask . ' = ' . $value;
				}
				// Check back, so we dont give more permissions than the admin wants to
				$check_permissions_to_default = array_diff($permissions->p_masks_anti[$p_system], $p_set['p_mask']);
				foreach ($check_permissions_to_default as $p_mask)
				{
					$sql_where .= (($sql_where) ? ' AND ' : '') . $p_mask . ' = 0';
				}

				$role_id = 0;
				$sql = 'SELECT role_id
					FROM ' . GALLERY_ROLES_TABLE . "
					WHERE $sql_where";
				$result = $db->sql_query_limit($sql, 1);
				$role_id = (int) $db->sql_fetchfield('role_id');
				$db->sql_freeresult($result);

				if (!$role_id)
				{
					// Note: Do not collect the roles to insert, to deny doubles and we need the ID!
					$sql = 'INSERT INTO ' . GALLERY_ROLES_TABLE . ' ' . $db->sql_build_array('INSERT', $p_set['p_mask']);
					$db->sql_query($sql);
					$role_id = $db->sql_nextid();
				}

				foreach ($p_set['usage'] as $usage)
				{
					if (!$p_system)
					{
						$sql_permissions[] = array(
							'perm_role_id'	=> $role_id,
							'perm_album_id'	=> $usage['c_mask'],
							'perm_group_id'	=> $usage['v_mask'],
						);
						if ($p_set['is_moderator'])
						{
							$sql_moderators[] = array(
								'album_id'		=> $usage['c_mask'],
								'group_id'		=> $usage['v_mask'],
								'group_name'	=> $group_names[$usage['v_mask']],
							);
						}
					}
					else
					{
						$sql_permissions[] = array(
							'perm_role_id'	=> $role_id,
							'perm_system'	=> $usage['c_mask'],
							'perm_group_id'	=> $usage['v_mask'],
						);
					}
				}
			}
			$db->sql_multi_insert(GALLERY_PERMISSIONS_TABLE, $sql_permissions);
			$db->sql_multi_insert(GALLERY_MODSCACHE_TABLE, $sql_moderators);

			$cache->destroy('sql', GALLERY_PERMISSIONS_TABLE);
			$cache->destroy('sql', GALLERY_ROLES_TABLE);
			$cache->destroy('sql', GALLERY_MODSCACHE_TABLE);

			trigger_error($user->lang['PERMISSIONS_STORED'] . adm_back_link($this->u_action));
		}
		trigger_error('HACKING_ATTEMPT', E_USER_WARNING);
	}

	/**
	* Create the drop-down-options to inherit the c_masks
	* or check, whether the choosen option is valid
	*/
	function inherit_albums($cache_obtain_album_list, $allowed_albums, $album_id, $check_inherit_album = 0)
	{
		global $user;
		$disabled = false;

		$return = '';
		$return .= '<option value="0" selected="selected">' . $user->lang['NO_INHERIT'] . '</option>';
		foreach ($cache_obtain_album_list as $album)
		{
			if (in_array($album['album_id'], $allowed_albums))
			{
				// We found the requested album: return true!
				if ($check_inherit_album && ($album['album_id'] == $check_inherit_album))
				{
					return true;
				}
				if ($album['album_id'] == $album_id)
				{
					$disabled = true;
					// Could we find the requested album so far? No? Hacking attempt?!
					if ($check_inherit_album)
					{
						return false;
					}
				}
				$return .= '<option value="' . $album['album_id'] . '"';
				if ($disabled)
				{
					$return .= ' disabled="disabled" class="disabled-option"';
				}
				$return .= '>' . $album['album_name'] . '</option>';
			}
		}
		// Could we not find the requested album even here?
		if ($check_inherit_album)
		{
			// Something went really wrong here!
			return false;
		}
		return $return;
	}

	/**
	* Create the drop-down-options to inherit the v_masks
	* or check, whether the choosen option is valid
	*/
	function inherit_victims($cache_obtain_album_list, $allowed_albums, $allowed_groups, $album_id, $group_id, $check_inherit_album = 0, $check_inherit_group = 0)
	{
		global $user;
		$disabled = false;
		// We submit a "wrong" array on the check (to make it more easy) so we convert it here
		if ($check_inherit_album && $check_inherit_group)
		{
			$converted_groups = array();
			foreach ($allowed_groups as $group)
			{
				$converted_groups[] = array(
					'group_id'		=> $group,
					'group_name'	=> '',
				);
			}
			$allowed_groups = $converted_groups;
			unset ($converted_groups);
		}

		$return = '';
		$return .= '<option value="0" selected="selected">' . $user->lang['NO_INHERIT'] . '</option>';
		foreach ($cache_obtain_album_list as $album)
		{
			if (in_array($album['album_id'], $allowed_albums))
			{
				$return .= '<option value="0" disabled="disabled" class="disabled-option">' . $album['album_name'] . '</option>';
				foreach ($allowed_groups as $group)
				{
					// We found the requested album_group: return true!
					if ($check_inherit_album && $check_inherit_group && (($album['album_id'] == $check_inherit_album) && ($group['group_id'] == $check_inherit_group)))
					{
						return true;
					}
					if (($album['album_id'] == $album_id) && ($group['group_id'] == $group_id))
					{
						$disabled = true;
						// Could we find the requested album_group so far? No? Hacking attempt?!
						if ($check_inherit_album && $check_inherit_group)
						{
							return false;
						}
					}
					$return .= '<option value="' . $album['album_id'] . '_' . $group['group_id'] . '"';
					if ($disabled)
					{
						$return .= ' disabled="disabled" class="disabled-option"';
					}
					$return .= '>&nbsp;&nbsp;&nbsp;' . $album['album_name'] . ' >>> ' . $group['group_name'] . '</option>';
				}
			}
		}
		// Could we not find the requested album_group even here?
		if ($check_inherit_album && $check_inherit_group)
		{
			// Something went really wrong here!
			return false;
		}
		return $return;
	}

	/**
	* Create the drop-down-options to inherit the v_masks
	* or check, whether the choosen option is valid
	*/
	function p_system_inherit_victims($p_system, $allowed_groups, $group_id, $check_inherit_group = 0)
	{
		global $user;
		$disabled = false;
		// We submit a "wrong" array on the check (to make it more easy) so we convert it here
		if ($check_inherit_group)
		{
			$converted_groups = array();
			foreach ($allowed_groups as $group)
			{
				$converted_groups[] = array(
					'group_id'		=> $group,
					'group_name'	=> '',
				);
			}
			$allowed_groups = $converted_groups;
			unset ($converted_groups);
		}

		$return = '';
		$return .= '<option value="0" selected="selected">' . $user->lang['NO_INHERIT'] . '</option>';
		foreach ($allowed_groups as $group)
		{
			// We found the requested {$p_system}_group: return true!
			if ($check_inherit_group && ($group['group_id'] == $check_inherit_group))
			{
				return true;
			}
			if ($group['group_id'] == $group_id)
			{
				$disabled = true;
				// Could we find the requested {$p_system}_group so far? No? Hacking attempt?!
				if ($check_inherit_group)
				{
					return false;
				}
			}
			$return .= '<option value="' . $p_system . '_' . $group['group_id'] . '"';
			if ($disabled)
			{
				$return .= ' disabled="disabled" class="disabled-option"';
			}
			$return .= '>&nbsp;&nbsp;&nbsp;' . (($p_system == OWN_GALLERY_PERMISSIONS) ? $user->lang['OWN_PERSONAL_ALBUMS'] : $user->lang['PERSONAL_ALBUMS']) . ' >>> ' . $group['group_name'] . '</option>';
		}
		// Could we not find the requested {$p_system}_group even here?
		if ($check_inherit_group)
		{
			// Something went really wrong here!
			return false;
		}
		return $return;
	}

	function import()
	{
		global $gallery_config, $config, $db, $template, $user;
		global $gallery_root_path, $phpbb_root_path, $phpEx;

		$import_schema = request_var('import_schema', '');
		$images = request_var('images', array(''), true);
		$submit = (isset($_POST['submit'])) ? true : ((empty($images)) ? false : true);

		if ($import_schema)
		{
			if (file_exists($phpbb_root_path . GALLERY_IMPORT_PATH . $import_schema . '.' . $phpEx))
			{
				include($phpbb_root_path . GALLERY_IMPORT_PATH . $import_schema . '.' . $phpEx);
			}
			else
			{
				trigger_error(sprintf($user->lang['MISSING_IMPORT_SCHEMA'], ($import_schema . '.' . $phpEx)), E_USER_WARNING);
			}

			$images_loop = 0;
			foreach ($images as $image_src)
			{
				/**
				* Import the images
				*/
				$image_src_full = $phpbb_root_path . GALLERY_IMPORT_PATH . utf8_decode($image_src);
				if (file_exists($image_src_full))
				{
					$filetype = getimagesize($image_src_full);
					$image_width = $filetype[0];
					$image_height = $filetype[1];

					$filetype_ext = '';
					switch ($filetype['mime'])
					{
						case 'image/jpeg':
						case 'image/jpg':
						case 'image/pjpeg':
							$filetype_ext = '.jpg';
							$read_function = 'imagecreatefromjpeg';
							if ((substr(strtolower($image_src), -4) != '.jpg') && (substr(strtolower($image_src), -5) != '.jpeg'))
							{
								trigger_error(sprintf($user->lang['FILETYPE_MIMETYPE_MISMATCH'], $image_src, $filetype['mime']), E_USER_WARNING);
							}
						break;

						case 'image/png':
						case 'image/x-png':
							$filetype_ext = '.png';
							$read_function = 'imagecreatefrompng';
							if (substr(strtolower($image_src), -4) != '.png')
							{
								trigger_error(sprintf($user->lang['FILETYPE_MIMETYPE_MISMATCH'], $image_src, $filetype['mime']), E_USER_WARNING);
							}
						break;

						case 'image/gif':
						case 'image/giff':
							$filetype_ext = '.gif';
							$read_function = 'imagecreatefromgif';
							if (substr(strtolower($image_src), -4) != '.gif')
							{
								trigger_error(sprintf($user->lang['FILETYPE_MIMETYPE_MISMATCH'], $image_src, $filetype['mime']), E_USER_WARNING);
							}
						break;

						default:
							trigger_error('NOT_ALLOWED_FILE_TYPE');
						break;
					}
					$image_filename = md5(unique_id()) . $filetype_ext;

					$move_file = (@ini_get('open_basedir') <> '') ? 'move_uploaded_file' : 'copy';
					$move_file($image_src_full, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);
					@chmod($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename, 0777);
					// The source image is imported, so we delete it.
					@unlink($image_src_full);

					$sql_ary = array(
						'image_filename' 		=> $image_filename,
						'image_thumbnail'		=> '',
						'image_desc'			=> '',
						'image_desc_uid'		=> '',
						'image_desc_bitfield'	=> '',
						'image_user_id'			=> $user_data['user_id'],
						'image_username'		=> $user_data['username'],
						'image_user_colour'		=> $user_data['user_colour'],
						'image_user_ip'			=> $user->ip,
						'image_time'			=> $start_time + $done_images,
						'image_album_id'		=> $album_id,
						'image_status'			=> IMAGE_APPROVED,
						'image_exif_data'		=> '',
					);
					$exif = @exif_read_data($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename, 0, true);
					if (!empty($exif["EXIF"]))
					{
						// Unset invalid exifs
						foreach ($exif as $key => $array)
						{
							if (!in_array($key, array('EXIF', 'IFD0')))
							{
								unset($exif[$key]);
							}
							else
							{
								foreach ($exif[$key] as $subkey => $array)
								{
									if (!in_array($subkey, array('DateTimeOriginal', 'FocalLength', 'ExposureTime', 'FNumber', 'ISOSpeedRatings', 'WhiteBalance', 'Flash', 'Model', 'ExposureProgram', 'ExposureBiasValue', 'MeteringMode')))
									{
										unset($exif[$key][$subkey]);
									}
								}
							}
						}
						$sql_ary['image_exif_data'] = serialize ($exif);
						$sql_ary['image_has_exif'] = EXIF_DBSAVED;
					}
					else
					{
						$sql_ary['image_exif_data'] = '';
						$sql_ary['image_has_exif'] = EXIF_UNAVAILABLE;
					}

					if (($image_width > $gallery_config['max_width']) || ($image_height > $gallery_config['max_height']))
					{
						/**
						* Resize overside images
						*/
						if ($gallery_config['resize_images'])
						{
							$src = $read_function($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);
							// Resize it
							if (($image_width / $gallery_config['max_width']) > ($image_height / $gallery_config['max_height']))
							{
								$thumbnail_width	= $gallery_config['max_width'];
								$thumbnail_height	= round($gallery_config['max_height'] * (($image_height / $gallery_config['max_height']) / ($image_width / $gallery_config['max_width'])));
							}
							else
							{
								$thumbnail_height	= $gallery_config['max_height'];
								$thumbnail_width	= round($gallery_config['max_width'] * (($image_width / $gallery_config['max_width']) / ($image_height / $gallery_config['max_height'])));
							}
							$thumbnail = ($gallery_config['gd_version'] == GDLIB1) ? @imagecreate($thumbnail_width, $thumbnail_height) : @imagecreatetruecolor($thumbnail_width, $thumbnail_height);
							$resize_function = ($gallery_config['gd_version'] == GDLIB1) ? 'imagecopyresized' : 'imagecopyresampled';
							$resize_function($thumbnail, $src, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $image_width, $image_height);
							imagedestroy($src);
							switch ($filetype_ext)
							{
								case '.jpg':
									@imagejpeg($thumbnail, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename, 100);
								break;

								case '.png':
									@imagepng($thumbnail, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);
								break;

								case '.gif':
									@imagegif($thumbnail, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);
								break;
							}
							imagedestroy($thumbnail);
						}
					}
					else if ($sql_ary['image_has_exif'] == EXIF_DBSAVED)
					{
						// Image was not resized, so we can pull the Exif from the image to save db-memory.
						$sql_ary['image_has_exif'] = EXIF_AVAILABLE;
						$sql_ary['image_exif_data'] = '';
					}
					$sql_ary['filesize_upload'] = filesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);

					if ($filename || ($image_name == ''))
					{
						$sql_ary['image_name'] = str_replace("_", " ", utf8_substr($image_src, 0, -4));
					}
					else
					{
						$sql_ary['image_name'] = str_replace('{NUM}', $num_offset + $done_images, $image_name);
					}

					// Put the images into the database
					$db->sql_query('INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
					$done_images++;
				}

				// Remove the image from the list
				unset($images[$images_loop]);
				$images_loop++;
				if ($images_loop == 10)
				{
					// We made 10 images, so we end for this turn
					break;
				}
			}
			if ($images_loop)
			{
				$sql = 'UPDATE ' . GALLERY_USERS_TABLE . "
					SET user_images = user_images + $images_loop
					WHERE user_id = " . $user_data['user_id'];
				$db->sql_query($sql);
				if ($db->sql_affectedrows() <= 0)
				{
					$sql_ary = array(
						'user_id'				=> $user_data['user_id'],
						'user_images'			=> $images_loop,
					);
					$sql = 'INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
					$db->sql_query($sql);
				}
				set_config('num_images', $config['num_images'] + $images_loop, true);
				$todo_images = $todo_images - $images_loop;
			}
			update_album_info($album_id);

			if (!$todo_images)
			{
				unlink($phpbb_root_path . GALLERY_IMPORT_PATH . $import_schema . '.' . $phpEx);
				trigger_error(sprintf($user->lang['IMPORT_FINISHED'], $done_images) . adm_back_link($this->u_action));
			}
			else
			{
				// Write the new list
				$this->create_import_schema($import_schema, $album_id, $user_data, $start_time, $num_offset, $done_images, $todo_images, $image_name, $filename, &$images);

				// Redirect
				$forward_url = $this->u_action . "&amp;import_schema=$import_schema";
				meta_refresh(1, $forward_url);
				trigger_error(sprintf($user->lang['IMPORT_DEBUG_MES'], $done_images, $todo_images));
			}
		}
		else if ($submit)
		{
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}
			if (!$images)
			{
				trigger_error('NO_FILE_SELECTED', E_USER_WARNING);
			}

			// Who is the uploader?
			$user_id = request_var('user_id', 0);
			$sql = 'SELECT username, user_colour, user_id
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $user_id;
			$result = $db->sql_query($sql);
			$user_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			if (!$user_row)
			{
				trigger_error('HACKING_ATTEMPT', E_USER_WARNING);
			}

			// Where do we put them to?
			$album_id = request_var('album_id', 0);
			$sql = 'SELECT album_id, album_name
				FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE album_id = ' . $album_id;
			$result = $db->sql_query($sql);
			$album_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			if (!$album_row)
			{
				trigger_error('HACKING_ATTEMPT', E_USER_WARNING);
			}

			$start_time = time();
			$import_schema = md5($start_time);
			$filename = (request_var('filename', '') == 'filename') ? true : false;
			$image_name = request_var('image_name', '', true);
			$num_offset = request_var('image_num', 0);

			$this->create_import_schema($import_schema, $album_row['album_id'], $user_row, $start_time, $num_offset, 0, sizeof($images), $image_name, $filename, &$images);

			$forward_url = $this->u_action . "&amp;import_schema=$import_schema";
			meta_refresh(2, $forward_url);
			trigger_error('IMPORT_SCHEMA_CREATED');
		}

		$sql = 'SELECT username, user_id
			FROM ' . USERS_TABLE . '
			ORDER BY user_id ASC';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('userrow', array(
				'USER_ID'				=> $row['user_id'],
				'USERNAME'				=> $row['username'],
				'SELECTED'				=> ($row['user_id'] == $user->data['user_id']) ? true : false,
			));
		}
		$db->sql_freeresult($result);

		$handle = opendir($phpbb_root_path . GALLERY_IMPORT_PATH);
		while ($file = readdir($handle))
		{
			if (!is_dir($phpbb_root_path . GALLERY_IMPORT_PATH . "$file") && (
			((substr(strtolower($file), '-4') == '.png') && $gallery_config['png_allowed']) ||
			((substr(strtolower($file), '-4') == '.gif') && $gallery_config['gif_allowed']) ||
			((substr(strtolower($file), '-4') == '.jpg') && $gallery_config['jpg_allowed'])
			))
			{
				$template->assign_block_vars('imagerow', array(
					'FILE_NAME'				=> utf8_encode($file),
				));
			}
		}
		closedir($handle);

		$template->assign_vars(array(
			'S_IMPORT_IMAGES'				=> true,
			'ACP_GALLERY_TITLE'				=> $user->lang['ACP_IMPORT_ALBUMS'],
			'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ACP_IMPORT_ALBUMS_EXPLAIN'],
			'L_IMPORT_DIR_EMPTY'			=> sprintf($user->lang['IMPORT_DIR_EMPTY'], GALLERY_IMPORT_PATH),
			'S_ALBUM_IMPORT_ACTION'			=> $this->u_action,
			'S_SELECT_IMPORT' 				=> gallery_albumbox(true, 'album_id', false, false, false, 0, ALBUM_UPLOAD),
		));

	}


	function cleanup()
	{
		global $auth, $cache, $db, $gallery_config, $phpbb_root_path, $template, $user;

		$delete = (isset($_POST['delete'])) ? true : false;
		$submit = (isset($_POST['submit'])) ? true : false;

		$missing_sources = request_var('source', array(0));
		$missing_entries = request_var('entry', array(''), true);
		$missing_authors = request_var('author', array(0), true);
		$missing_comments = request_var('comment', array(0), true);
		$missing_personals = request_var('personal', array(0), true);
		$personals_bad = request_var('personal_bad', array(0), true);
		$s_hidden_fields = build_hidden_fields(array(
			'source'		=> $missing_sources,
			'entry'			=> $missing_entries,
			'author'		=> $missing_authors,
			'comment'		=> $missing_comments,
			'personal'		=> $missing_personals,
			'personal_bad'	=> $personals_bad,
		));

		if ($submit)
		{
			if ($missing_authors)
			{
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
					SET image_user_id = ' . ANONYMOUS . ",
						image_user_colour = ''
					WHERE " . $db->sql_in_set('image_id', $missing_authors);
				$db->sql_query($sql);
			}
			if ($missing_comments)
			{
				$sql = 'UPDATE ' . GALLERY_COMMENTS_TABLE . ' 
					SET comment_user_id = ' . ANONYMOUS . ",
						comment_user_colour = ''
					WHERE " . $db->sql_in_set('comment_id', $missing_comments);
				$db->sql_query($sql);
			}
			trigger_error($user->lang['CLEAN_CHANGED'] . adm_back_link($this->u_action));
		}

		if (confirm_box(true))
		{
			$message = '';
			if ($missing_sources)
			{
				$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $missing_sources);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . ' WHERE ' . $db->sql_in_set('comment_image_id', $missing_sources);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . ' WHERE ' . $db->sql_in_set('rate_image_id', $missing_sources);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_REPORTS_TABLE . ' WHERE ' . $db->sql_in_set('report_image_id', $missing_sources);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $missing_sources);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $missing_sources);
				$db->sql_query($sql);
				$message .= $user->lang['CLEAN_SOURCES_DONE'];
			}
			if ($missing_entries)
			{
				foreach ($missing_entries as $missing_image)
				{
					@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . utf8_decode($missing_image));
				}
				$message .= $user->lang['CLEAN_ENTRIES_DONE'];
			}
			if ($missing_authors)
			{
				$deleted_images = array();
				$sql = 'SELECT image_id, image_thumbnail, image_filename
					FROM ' . GALLERY_IMAGES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $missing_authors);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					// Delete the files themselves
					@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $row['image_thumbnail']);
					@unlink($phpbb_root_path . GALLERY_MEDIUM_PATH . $row['image_filename']);
					@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $row['image_filename']);
					$deleted_images[] = $row['image_id'];
				}
				// we have all image_ids in $deleted_images which are deleted
				// aswell as the album_ids in $deleted_albums
				// so now drop the comments, ratings, images and albums
				if ($deleted_images)
				{
					$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . ' WHERE ' . $db->sql_in_set('comment_image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . ' WHERE ' . $db->sql_in_set('rate_image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_REPORTS_TABLE . ' WHERE ' . $db->sql_in_set('report_image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $deleted_images);
					$db->sql_query($sql);
				}
				$message .= $user->lang['CLEAN_AUTHORS_DONE'];
			}
			if ($missing_comments)
			{
				$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . ' WHERE ' . $db->sql_in_set('comment_id', $missing_comments);
				$db->sql_query($sql);
				$message .= $user->lang['CLEAN_COMMENTS_DONE'];
			}
			if ($missing_personals || $personals_bad)
			{
				$delete_albums = array_merge($missing_personals, $personals_bad);

				$deleted_images = $deleted_albums = array(0);
				$sql = 'SELECT COUNT(album_user_id) personal_counter
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE ' . $db->sql_in_set('album_user_id', $delete_albums);
				$result = $db->sql_query($sql);
				$remove_personal_counter = $db->sql_fetchfield('personal_counter');
				$db->sql_freeresult($result);
				$sql = 'SELECT album_id
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE ' . $db->sql_in_set('album_user_id', $delete_albums);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$deleted_albums[] = $row['album_id'];
				}
				$db->sql_freeresult($result);
				$sql = 'SELECT image_id, image_thumbnail, image_filename
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE ' . $db->sql_in_set('image_album_id', $deleted_albums);
				@$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $row['image_thumbnail']);
					@unlink($phpbb_root_path . GALLERY_MEDIUM_PATH . $row['image_filename']);
					@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $row['image_filename']);
					$deleted_images[] = $row['image_id'];
				}
				$db->sql_freeresult($result);
				if ($deleted_images)
				{
					$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . ' WHERE ' . $db->sql_in_set('comment_image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . ' WHERE ' . $db->sql_in_set('rate_image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_REPORTS_TABLE . ' WHERE ' . $db->sql_in_set('report_image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $deleted_images);
					$db->sql_query($sql);
				}
				$sql = 'DELETE FROM ' . GALLERY_ALBUMS_TABLE . ' WHERE ' . $db->sql_in_set('album_id', $deleted_albums);
				$db->sql_query($sql);
				set_gallery_config('personal_counter', ($gallery_config['personal_counter'] - $remove_personal_counter));
				$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
					SET personal_album_id = 0
					WHERE ' . $db->sql_in_set('user_id', $delete_albums);
				$db->sql_query($sql);
				if ($missing_personals)
				{
					$message .= $user->lang['CLEAN_PERSONALS_DONE'];
				}
				if ($personals_bad)
				{
					$message .= $user->lang['CLEAN_PERSONALS_BAD_DONE'];
				}
			}

			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
			$cache->destroy('sql', GALLERY_COMMENTS_TABLE);
			$cache->destroy('sql', GALLERY_FAVORITES_TABLE);
			$cache->destroy('sql', GALLERY_IMAGES_TABLE);
			$cache->destroy('sql', GALLERY_RATES_TABLE);
			$cache->destroy('sql', GALLERY_REPORTS_TABLE);
			$cache->destroy('sql', GALLERY_WATCH_TABLE);
			$cache->destroy('_albums');

			trigger_error($message . adm_back_link($this->u_action));
		}
		else if (($delete) || (isset($_POST['cancel'])))
		{
			if (isset($_POST['cancel']))
			{
				trigger_error($user->lang['CLEAN_GALLERY_ABORT'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
			else
			{
				$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN'];
				if ($missing_sources)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN_SOURCES'] . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($missing_entries)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN_ENTRIES'] . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($missing_authors)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN_AUTHORS'] . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($missing_comments)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN_COMMENTS'] . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($personals_bad || $missing_personals)
				{
					$sql = 'SELECT album_name, album_user_id
						FROM ' . GALLERY_ALBUMS_TABLE . '
						WHERE ' . $db->sql_in_set('album_user_id', array_merge($missing_personals, $personals_bad));
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						if (in_array($row['album_user_id'], $personals_bad))
						{
							$personals_bad_names[] = $row['album_name'];
						}
						else
						{
							$missing_personals_names[] = $row['album_name'];
						}
					}
					$db->sql_freeresult($result);
				}
				if ($missing_personals)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = sprintf($user->lang['CONFIRM_CLEAN_PERSONALS'], implode(', ', $missing_personals_names)) . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($personals_bad)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = sprintf($user->lang['CONFIRM_CLEAN_PERSONALS_BAD'], implode(', ', $personals_bad_names)) . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				confirm_box(false, 'CLEAN_GALLERY', $s_hidden_fields);
			}
		}

		$requested_source = array();
		$sql = 'SELECT gi.image_id, gi.image_name, gi.image_filemissing, gi.image_filename, gi.image_username, u.user_id
			FROM ' . GALLERY_IMAGES_TABLE . ' gi
			LEFT JOIN ' . USERS_TABLE . ' u
				ON u.user_id = gi.image_user_id';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['image_filemissing'])
			{
				$template->assign_block_vars('sourcerow', array(
					'IMAGE_ID'		=> $row['image_id'],
					'IMAGE_NAME'	=> $row['image_name'],
				));
			}
			if (!$row['user_id'])
			{
				$template->assign_block_vars('authorrow', array(
					'IMAGE_ID'		=> $row['image_id'],
					'AUTHOR_NAME'	=> $row['image_username'],
				));
			}
			$requested_source[] = $row['image_filename'];
		}
		$db->sql_freeresult($result);

		$check_mode = request_var('check_mode', '');
		if ($check_mode == 'source')
		{
			$source_missing = array();

			// Reset the status: a image might have been viewed without file but the file is back
			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
				SET image_filemissing = 0';
			$db->sql_query($sql);

			$sql = 'SELECT image_id, image_filename, image_filemissing
				FROM ' . GALLERY_IMAGES_TABLE;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if (!file_exists($phpbb_root_path . GALLERY_UPLOAD_PATH . $row['image_filename']))
				{
					$source_missing[] = $row['image_id'];
				}
			}
			$db->sql_freeresult($result);
			if ($source_missing)
			{
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . "
					SET image_filemissing = 1
					WHERE " . $db->sql_in_set('image_id', $source_missing);
				$db->sql_query($sql);
			}
		}
		if ($check_mode == 'entry')
		{
			$directory = $phpbb_root_path . GALLERY_UPLOAD_PATH;
			$handle = opendir($directory);
			while ($file = readdir($handle))
			{
				if (!is_dir($directory . "$file") &&
				((substr(strtolower($file), '-4') == '.png') || (substr(strtolower($file), '-4') == '.gif') || (substr(strtolower($file), '-4') == '.jpg'))
				&& !in_array($file, $requested_source)
				)
				{
					$template->assign_block_vars('entryrow', array(
						'FILE_NAME'				=> utf8_encode($file),
					));
				}
			}
			closedir($handle);
		}


		$sql = 'SELECT gc.comment_id, gc.comment_image_id, gc.comment_username, u.user_id
			FROM ' . GALLERY_COMMENTS_TABLE . ' gc
			LEFT JOIN ' . USERS_TABLE . ' u
				ON u.user_id = gc.comment_user_id';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if (!$row['user_id'])
			{
				$template->assign_block_vars('commentrow', array(
					'COMMENT_ID'	=> $row['comment_id'],
					'IMAGE_ID'		=> $row['comment_image_id'],
					'AUTHOR_NAME'	=> $row['comment_username'],
				));
			}
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT ga.album_id, ga.album_user_id, ga.album_name, u.user_id, SUM(ga.album_images_real) images
			FROM ' . GALLERY_ALBUMS_TABLE . ' ga
			LEFT JOIN ' . USERS_TABLE . ' u
				ON u.user_id = ga.album_user_id
			WHERE ga.album_user_id <> 0
			GROUP BY ga.album_user_id';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if (!$row['user_id'])
			{
				$template->assign_block_vars('personalrow', array(
					'USER_ID'		=> $row['album_user_id'],
					'ALBUM_ID'		=> $row['album_id'],
					'AUTHOR_NAME'	=> $row['album_name'],
				));
			}
			$template->assign_block_vars('personal_bad_row', array(
				'USER_ID'		=> $row['album_user_id'],
				'ALBUM_ID'		=> $row['album_id'],
				'AUTHOR_NAME'	=> $row['album_name'],
				'IMAGES'		=> $row['images'],
			));
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'S_GALLERY_MANAGE_RESTS'		=> true,
			'ACP_GALLERY_TITLE'				=> $user->lang['ACP_GALLERY_CLEANUP'],
			'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ACP_GALLERY_CLEANUP_EXPLAIN'],
			'CHECK_SOURCE'			=> $this->u_action . '&amp;check_mode=source',
			'CHECK_ENTRY'			=> $this->u_action . '&amp;check_mode=entry',

			'S_FOUNDER'				=> ($user->data['user_type'] == USER_FOUNDER) ? true : false,
		));
	}

	function create_import_schema($import_schema, $album_id, $user_row, $start_time, $num_offset, $done_images, $todo_images, $image_name, $filename, &$images)
	{
		global $phpbb_root_path, $phpEx;

		$import_file = "<?php\n\n";
		$import_file .= "if (!defined('IN_PHPBB'))\n{\n	exit;\n}\n\n";
		$import_file .= "\$album_id = " . $album_id . ";\n";
		$import_file .= "\$start_time = " . $start_time . ";\n";
		$import_file .= "\$num_offset = " . $num_offset . ";\n";
		$import_file .= "\$done_images = " . $done_images . ";\n";
		$import_file .= "\$todo_images = " . $todo_images . ";\n";
		$import_file .= "\$image_name = '" . $image_name . "';\n";
		$import_file .= "\$filename = " . (($filename) ? 'true' : 'false') . ";\n";
		$import_file .= "\$user_data = array(\n";
		$import_file .= "	'user_id'		=> " . $user_row['user_id'] . ",\n";
		$import_file .= "	'username'		=> '" . $user_row['username'] . "',\n";
		$import_file .= "	'user_colour'	=> '" . $user_row['user_colour'] . "',\n";
		$import_file .= ");\n";
		$import_file .= "\$images = array(\n";
		foreach ($images as $image_src)
		{
			$import_file .= "	'" . $image_src . "',\n";
		}
		$import_file .= ");\n";
		$import_file .= "\n";
		$import_file .= '?' . '>';

		// Write to disc
		if ((file_exists($phpbb_root_path . GALLERY_IMPORT_PATH . $import_schema . '.' . $phpEx) && is_writable($phpbb_root_path . GALLERY_IMPORT_PATH . $import_schema . '.' . $phpEx)) || is_writable($phpbb_root_path . GALLERY_IMPORT_PATH))
		{
			$written = true;
			if (!($fp = @fopen($phpbb_root_path . GALLERY_IMPORT_PATH . $import_schema . '.' . $phpEx, 'w')))
			{
				$written = false;
			}
			if (!(@fwrite($fp, $import_file)))
			{
				$written = false;
			}
			@fclose($fp);
		}
	}
}

?>