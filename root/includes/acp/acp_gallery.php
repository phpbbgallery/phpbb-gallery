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
		global $gallery_config, $db, $template, $user;
		global $gallery_root_path, $phpbb_root_path, $phpEx;
		$gallery_root_path = GALLERY_ROOT_PATH;

		include($phpbb_root_path . $gallery_root_path . 'includes/constants.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/functions.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/permissions.' . $phpEx);
		$gallery_config = load_gallery_config();
		$album_access_array = get_album_access_array();

		$user->add_lang('mods/gallery_acp');
		$user->add_lang('mods/gallery');
		$this->tpl_name = 'gallery_main';
		add_form_key('acp_gallery');

		switch ($mode)
		{
			case 'overview':
				$title = 'ACP_GALLERY_OVERVIEW';
				$this->page_title = $user->lang[$title];

				$this->overview();
			break;

			case 'manage_albums':
				$action = request_var('action', '');
				$this->tpl_name = 'gallery_albums';
				switch ($action)
				{
					case 'create':
						$title = 'GALLERY_ALBUMS_TITLE';
						$this->page_title = $user->lang[$title];

						$this->create_album();
					break;

					case 'edit':
						$title = 'ACP_EDIT_ALBUM_TITLE';
						$this->page_title = $user->lang[$title];

						$this->edit_album();
					break;

					case 'delete':
						$title = 'DELETE_ALBUM';
						$this->page_title = $user->lang[$title];

						$this->delete_album();
					break;

					case 'move':
						$this->move_album();
					break;

					default:
						$title = 'ACP_GALLERY_MANAGE_ALBUMS';
						$this->page_title = $user->lang[$title];

						$this->manage_albums();
					break;
				}
			break;

			case 'album_permissions':
				$title = 'ALBUM_AUTH_TITLE';
				$this->tpl_name = 'gallery_permissions';
				$this->page_title = $user->lang[$title];

				$this->permissions();
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
				case 'purge_cache':
					$confirm = true;
					$confirm_lang = 'GALLERY_CLEAR_CACHE_CONFIRM';
				break;
			}

			if ($confirm)
			{
				confirm_box(false, $user->lang[$confirm_lang], build_hidden_fields(array(
					'i'			=> $id,
					'mode'		=> $mode,
					'action'	=> $action,
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
						WHERE image_status = 1
						GROUP BY image_user_id';
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$total_images += $row['num_images'];
						$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
							SET user_images = ' . $row['num_images'] . '
							WHERE user_id = ' . $row['user_id'];
						$db->sql_query($sql);

						if ($db->sql_affectedrows() != 1)
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

					while ($row = $db->sql_fetchrow($result))
					{
						$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
							SET personal_album_id = ' . $row['album_id'] . '
							WHERE user_id = ' . $row['album_user_id'];
						$db->sql_query($sql);

						if ($db->sql_affectedrows() != 1)
						{
							$sql_ary = array(
								'user_id'				=> $row['album_user_id'],
								'personal_album_id'		=> $row['album_id'],
							);
							$sql = 'INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
							$db->sql_query($sql);
						}
					}
					$db->sql_freeresult($result);

					trigger_error($user->lang['RESYNCED_PERSONALS'] . adm_back_link($this->u_action));
				break;

				case 'stats':
					if (!$auth->acl_get('a_board'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					// Hopefully this won't take to long!
					$sql = 'SELECT image_id, image_filename, image_thumbnail
						FROM ' . GALLERY_IMAGES_TABLE;
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
		$num_albums = $db->sql_fetchfield('num_albums');
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

	function manage_albums()
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$parent_id = request_var('parent_id', 0);
		$template->assign_vars(array(
			'S_MANAGE_ALBUMS'			=> true,
			'S_ALBUM_ACTION'			=> $this->u_action . '&amp;action=create&amp;album_id=' . $parent_id,

			'ACP_GALLERY_TITLE'			=> $user->lang['ACP_MANAGE_ALBUMS'],
			'ACP_GALLERY_TITLE_EXPLAIN'	=> $user->lang['ACP_MANAGE_ALBUMS_EXPLAIN'],
		));

		if (!$parent_id)
		{
			$navigation = $user->lang['GALLERY_INDEX'];
		}
		else
		{
			$navigation = '<a href="' . $this->u_action . '">' . $user->lang['GALLERY_INDEX'] . '</a>';

			$albums_nav = get_album_branch(0, $parent_id, 'parents', 'descending');
			foreach ($albums_nav as $row)
			{
				if ($row['album_id'] == $parent_id)
				{
					$navigation .= ' -&gt; ' . $row['album_name'];
				}
				else
				{
					$navigation .= ' -&gt; <a href="' . $this->u_action . '&amp;parent_id=' . $row['album_id'] . '">' . $row['album_name'] . '</a>';
				}
			}
		}

		$album = array();
		$sql = 'SELECT *
			FROM ' . GALLERY_ALBUMS_TABLE . '
			WHERE parent_id = ' . $parent_id . '
				AND album_user_id = 0
			ORDER BY left_id ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$album[] = $row;
		}
		$db->sql_freeresult($result);

		for ($i = 0; $i < count($album); $i++)
		{
			$folder_image = ($album[$i]['left_id'] + 1 != $album[$i]['right_id']) ? '<img src="images/icon_subfolder.gif" alt="' . $user->lang['SUBALBUMS'] . '" />' : '<img src="images/icon_folder.gif" alt="' . $user->lang['ALBUM'] . '" />';
			$template->assign_block_vars('album_row', array(
				'FOLDER_IMAGE'			=> $folder_image,
				'U_ALBUM'				=> $this->u_action . '&amp;parent_id=' . $album[$i]['album_id'],
				'ALBUM_NAME'			=> $album[$i]['album_name'],
				'ALBUM_IMAGE'			=> $album[$i]['album_image'],
				'ALBUM_DESCRIPTION'		=> generate_text_for_display($album[$i]['album_desc'], $album[$i]['album_desc_uid'], $album[$i]['album_desc_bitfield'], $album[$i]['album_desc_options']),
				'U_MOVE_UP'				=> $this->u_action . '&amp;action=move&amp;move=move_up&amp;album_id=' . $album[$i]['album_id'],
				'U_MOVE_DOWN'			=> $this->u_action . '&amp;action=move&amp;move=move_down&amp;album_id=' . $album[$i]['album_id'],
				'U_EDIT'				=> $this->u_action . '&amp;action=edit&amp;album_id=' . $album[$i]['album_id'],
				'U_DELETE'				=> $this->u_action . '&amp;action=delete&amp;album_id=' . $album[$i]['album_id'],
			));
		}
		$template->assign_vars(array(
			'NAVIGATION'		=> $navigation,
			'S_ALBUM'			=> $parent_id,
			'U_EDIT'			=> $this->u_action . '&amp;action=edit&amp;album_id=' . $parent_id,
			'U_DELETE'			=> $this->u_action . '&amp;action=delete&amp;album_id=' . $parent_id,
		));
	}

	function create_album()
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

		$submit = (isset($_POST['submit'])) ? true : false;
		if (!$submit)
		{
			$template->assign_vars(array(
				'S_CREATE_ALBUM'				=> true,
				'ACP_GALLERY_TITLE'				=> $user->lang['GALLERY_ALBUMS_TITLE'],
				'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ACP_CREATE_ALBUM_EXPLAIN'],
				'S_PARENT_OPTIONS'				=> gallery_albumbox(true, '', request_var('album_id', 0)),
				'S_COPY_OPTIONS'				=> gallery_albumbox(true, '', request_var('album_id', 0)),
				'S_ALBUM_ACTION'				=> $this->u_action . '&amp;action=create',
				'S_DESC_BBCODE_CHECKED'		=> true,
				'S_DESC_SMILIES_CHECKED'	=> true,
				'S_DESC_URLS_CHECKED'		=> true,
				'ALBUM_TYPE'				=> 1,
				'ALBUM_IMAGE'				=> '',
				));
		}
		else
		{
			// Is it salty ?
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}

			$album_data = array(
				'album_name'					=> request_var('album_name', '', true),
				'parent_id'						=> request_var('parent_id', 0),
				//left_id and right_id are created some lines later
				'album_parents'					=> '',
				'album_type'					=> request_var('album_type', 0),
				'album_desc_options'			=> 7,
				'album_desc'					=> utf8_normalize_nfc(request_var('album_desc', '', true)),
				'album_user_id'					=> 0,
				'album_last_username'			=> '',
				'album_image'					=> request_var('album_image', ''),
			);
			generate_text_for_storage($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options'], request_var('desc_parse_bbcode', false), request_var('desc_parse_urls', false), request_var('desc_parse_smilies', false));

			if ($album_data['album_name'] == '')
			{
				trigger_error($user->lang['MISSING_ALBUM_NAME'] . adm_back_link(append_sid("{$phpbb_admin_path}index.$phpEx", 'i=gallery&amp;mode=manage_albums&action=create')));
			}

			/**
			* borrowed from phpBB3
			* @author: phpBB Group
			* @location: acp_forums->manage_forums
			*/
			if ($album_data['parent_id'])
			{
				$sql = 'SELECT left_id, right_id, album_type
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE album_id = ' . $album_data['parent_id'];
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['PARENT_NOT_EXIST'] . adm_back_link($this->u_action . '&amp;' . $this->parent_id), E_USER_WARNING);
				}

				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
					SET left_id = left_id + 2, right_id = right_id + 2
					WHERE left_id > ' . $row['right_id'] . '
						AND album_user_id = ' . $album_data['album_user_id'];
				$db->sql_query($sql);

				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
					SET right_id = right_id + 2
					WHERE ' . $row['left_id'] . ' BETWEEN left_id AND right_id
						AND album_user_id = ' . $album_data['album_user_id'];
				$db->sql_query($sql);

				$album_data['left_id'] = $row['right_id'];
				$album_data['right_id'] = $row['right_id'] + 1;
			}
			else
			{
				$sql = 'SELECT MAX(right_id) right_id
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE album_user_id = ' . $album_data['album_user_id'];
				$result = $db->sql_query($sql);
				$right_id = $db->sql_fetchfield('right_id');
				$db->sql_freeresult($result);

				$album_data['left_id'] = $right_id + 1;
				$album_data['right_id'] = $right_id + 2;
			}
			$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
			$album_data['album_id'] = $db->sql_nextid();
			$album_id = $album_data['album_id'];

			$copy_permissions = request_var('copy_permissions', 0);
			if ($copy_permissions <> 0)
			{
				// Delete the old permissions and than copy the new one's
				$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . '
					WHERE perm_album_id = ' . $album_id;
				$db->sql_query($sql);

				$sql = 'SELECT *
					FROM ' . GALLERY_PERMISSIONS_TABLE . '
					WHERE perm_album_id = ' . $copy_permissions;
				$result = $db->sql_query($sql);
				while($row = $db->sql_fetchrow($result))
				{
					$perm_data = array(
						'perm_role_id'			=> $row['perm_role_id'],
						'perm_album_id'			=> $album_id,
						'perm_user_id'			=> $row['perm_user_id'],
						'perm_group_id'			=> $row['perm_group_id'],
						'perm_system'			=> $row['perm_system'],
					);
					$db->sql_query('INSERT INTO ' . GALLERY_PERMISSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $perm_data));
				}
				$db->sql_freeresult($result);

				$sql = 'SELECT *
					FROM ' . GALLERY_MODSCACHE_TABLE . '
					WHERE album_id = ' . $copy_permissions;
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$sql_ary = array(
						'album_id'			=> $album_id,
						'user_id'			=> $row['user_id'],
						'username '			=> $row['username'],
						'group_id'			=> $row['group_id'],
						'group_name'		=> $row['group_name'],
						'display_on_index'	=> $row['display_on_index'],
					);
					$db->sql_query('INSERT INTO ' . GALLERY_MODSCACHE_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
				}
				$db->sql_freeresult($result);
			}
			$cache->destroy('sql', GALLERY_MODSCACHE_TABLE);
			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
			$cache->destroy('_albums');

			trigger_error($user->lang['NEW_ALBUM_CREATED'] . sprintf($user->lang['SET_PERMISSIONS'], 
				append_sid("{$phpbb_admin_path}index.$phpEx", 
				array('i' => 'gallery', 'mode' => 'album_permissions', 'step' => 1, 'album_id' => $album_id, 'uncheck' => 'true')
				)) . adm_back_link($this->u_action));
		}
	}

	function edit_album()
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

		$album_id = request_var('album_id', 0);

		$submit = (isset($_POST['submit'])) ? true : false;
		if(!$submit)
		{
			$album_data = get_album_info($album_id);
			$album_desc_data = generate_text_for_edit($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_options']);

			$template->assign_vars(array(
				'S_EDIT_ALBUM'				=> true,
				'ACP_GALLERY_TITLE'			=> $user->lang['GALLERY_ALBUMS_TITLE'],
				'ACP_GALLERY_TITLE_EXPLAIN'	=> $user->lang['ACP_EDIT_ALBUM_EXPLAIN'],

				'S_ALBUM_ACTION' 			=> $this->u_action . '&amp;action=edit&amp;album_id=' . $album_id,
				'S_PARENT_OPTIONS'			=> gallery_albumbox(true, '', $album_data['parent_id'], '', $album_id),
				'S_COPY_OPTIONS'			=> gallery_albumbox(true, '', 0, '', $album_id),

				'ALBUM_NAME' 				=> $album_data['album_name'],
				'ALBUM_DESC'				=> $album_desc_data['text'],
				'ALBUM_TYPE'				=> $album_data['album_type'],
				'ALBUM_IMAGE'				=> $album_data['album_image'],
				'S_DESC_BBCODE_CHECKED'		=> ($album_desc_data['allow_bbcode']) ? true : false,
				'S_DESC_SMILIES_CHECKED'	=> ($album_desc_data['allow_smilies']) ? true : false,
				'S_DESC_URLS_CHECKED'		=> ($album_desc_data['allow_urls']) ? true : false,
				'S_MODE' 				=> 'edit',
			));
		}
		else
		{
			// Is it salty ?
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			$album_data = array(
				'album_name'					=> request_var('album_name', '', true),
				'parent_id'						=> request_var('parent_id', 0),
				//left_id and right_id are created some lines later
				'album_parents'					=> '',
				'album_type'					=> request_var('album_type', 0),
				'album_desc_options'			=> 7,
				'album_desc'					=> utf8_normalize_nfc(request_var('album_desc', '', true)),
				'album_image'					=> request_var('album_image', ''),
			);

			generate_text_for_storage($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options'], request_var('desc_parse_bbcode', false), request_var('desc_parse_urls', false), request_var('desc_parse_smilies', false));
			$row = get_album_info($album_id);
			if (($row['album_images_real'] > 0) && !$album_data['album_type'])
			{
				trigger_error($user->lang['REMOVE_IMAGES_FOR_CAT'] . adm_back_link(append_sid("{$phpbb_admin_path}index.$phpEx", 'i=gallery&amp;mode=manage_albums&amp;action=edit&amp;album_id=' . $album_id)));
			}
			if ($album_data['album_name'] == '')
			{
				trigger_error($user->lang['MISSING_ALBUM_NAME'] . adm_back_link(append_sid("{$phpbb_admin_path}index.$phpEx", 'i=gallery&amp;mode=manage_albums&amp;action=edit&amp;album_id=' . $album_id)));
			}

			// If the parent is different, the left_id and right_id have changed.
			if ($row['parent_id'] != $album_data['parent_id'])
			{
				// How many do we have to move and how far.
				$moving_ids = ($row['right_id'] - $row['left_id']) + 1;
				$sql = 'SELECT MAX(right_id) AS right_id
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE album_user_id = ' . $row['album_user_id'];
				$result = $db->sql_query($sql);
				$moving_distance = ($db->sql_fetchfield('right_id') - $row['left_id']) + 1;
				$db->sql_freeresult($result);
				$stop_updating = $moving_distance + $row['left_id'];

				// Update the moving albums... move them to the end.
				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
					SET right_id = right_id + ' . $moving_distance . ',
						left_id = left_id + ' . $moving_distance . '
					WHERE album_user_id = ' . $row['album_user_id'] . ' AND
						left_id >= ' . $row['left_id'] . '
						AND right_id <= ' . $row['right_id'];
				$db->sql_query($sql);

				$new['left_id'] = $row['left_id'] + $moving_distance;
				$new['right_id'] = $row['right_id'] + $moving_distance;

				// Close the gap, we produced through moving.
				if ($album_data['parent_id'] == 0)
				{
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET left_id = left_id - ' . $moving_ids . '
						WHERE album_user_id = ' . $row['album_user_id'] . '
							AND left_id >= ' . $row['left_id'];
					$db->sql_query($sql);

					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET right_id = right_id - ' . $moving_ids . '
						WHERE album_user_id = ' . $row['album_user_id'] . '
							AND right_id >= ' . $row['left_id'];
					$db->sql_query($sql);
				}
				else
				{
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET left_id = left_id - ' . $moving_ids . '
						WHERE album_user_id = ' . $row['album_user_id'] . '
							AND left_id >= ' . $row['left_id'] . '
							AND right_id <= ' . $stop_updating;
					$db->sql_query($sql);

					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET right_id = right_id - ' . $moving_ids . '
						WHERE album_user_id = ' . $row['album_user_id'] . '
							AND right_id >= ' . $row['left_id'] . '
							AND right_id <= ' . $stop_updating;
					$db->sql_query($sql);

					// Create new gap, therefore we need parent_information.
					$parent = get_album_info($album_data['parent_id']);

					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET left_id = left_id + ' . $moving_ids . '
						WHERE album_user_id = ' . $row['album_user_id'] . '
							AND left_id >= ' . $parent['right_id'] . '
							AND right_id <= ' . $stop_updating;
					$db->sql_query($sql);

					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET right_id = right_id + ' . $moving_ids . '
						WHERE album_user_id = ' . $row['album_user_id'] . '
							AND right_id >= ' . $parent['right_id'] . '
							AND right_id <= ' . $stop_updating;
					$db->sql_query($sql);

					// Move the albums to the suggested gap.
					$parent['right_id'] = $parent['right_id'] + $moving_ids;
					$move_back = ($new['right_id'] - $parent['right_id']) + 1;
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET left_id = left_id - ' . $move_back . ',
							right_id = right_id - ' . $move_back . '
						WHERE album_user_id = ' . $row['album_user_id'] . '
							AND left_id >= ' . $stop_updating;
					$db->sql_query($sql);
				}
			}

			// The album name has changed, clear the parents list of all albums.
			if ($row['album_name'] != $album_data['album_name'])
			{
				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
					SET album_parents = ''";
				$db->sql_query($sql);
			}

			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' 
					SET ' . $db->sql_build_array('UPDATE', $album_data) . '
					WHERE album_id  = ' . (int) $album_id;
			$db->sql_query($sql);
			$copy_permissions = request_var('copy_permissions', 0);
			if ($copy_permissions <> 0)
			{
				// Delete the old permissions and than copy the new one's
				$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . '
					WHERE perm_album_id = ' . $album_id;
				$result = $db->sql_query($sql);

				$sql = 'SELECT *
					FROM ' . GALLERY_PERMISSIONS_TABLE . '
					WHERE perm_album_id = ' . $copy_permissions;
				$result = $db->sql_query($sql);
				while($row = $db->sql_fetchrow($result))
				{
					$perm_data = array(
						'perm_role_id'					=> $row['perm_role_id'],
						'perm_album_id'					=> $album_id,
						'perm_user_id'					=> $row['perm_user_id'],
						'perm_group_id'					=> $row['perm_group_id'],
						'perm_system'					=> $row['perm_system'],
					);
					$db->sql_query('INSERT INTO ' . GALLERY_PERMISSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $perm_data));
				}
				$db->sql_freeresult($result);

				$sql = 'DELETE FROM ' . GALLERY_MODSCACHE_TABLE . '
					WHERE album_id = ' . $album_id;
				$db->sql_query($sql);

				$sql = 'SELECT * FROM ' . GALLERY_MODSCACHE_TABLE . '
					WHERE album_id = ' . $copy_permissions;
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$sql_ary = array(
						'album_id'			=> $album_id,
						'user_id'			=> $row['user_id'],
						'username '			=> $row['username'],
						'group_id'			=> $row['group_id'],
						'group_name'		=> $row['group_name'],
						'display_on_index'	=> $row['display_on_index'],
					);
					$db->sql_query('INSERT INTO ' . GALLERY_MODSCACHE_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
				}
				$db->sql_freeresult($result);
			}
			$cache->destroy('sql', GALLERY_MODSCACHE_TABLE);
			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
			$cache->destroy('_albums');

			trigger_error($user->lang['ALBUM_UPDATED'] . sprintf($user->lang['SET_PERMISSIONS'], 
				append_sid("{$phpbb_admin_path}index.$phpEx", 
				array('i' => 'gallery', 'mode' => 'album_permissions', 'step' => 1, 'album_id' => $album_id, 'uncheck' => 'true')
				)) . adm_back_link($this->u_action));
		}
	}

	function delete_album()
	{
		global $db, $template, $user, $cache;

		$album_id = request_var('album_id', 0);
		$album_data = get_album_info($album_id);

		$submit = (isset($_POST['submit'])) ? true : false;
		if (!$submit)
		{
			$template->assign_vars(array(
				'S_DELETE_ALBUM'		=> true,
				'ACP_GALLERY_TITLE'			=> $user->lang['DELETE_ALBUM'],
				'ACP_GALLERY_TITLE_EXPLAIN'	=> $user->lang['DELETE_ALBUM_EXPLAIN'],

				'S_ALBUM_ACTION'		=>  $this->u_action . '&amp;action=delete&amp;album_id=' . $album_id,
				'ALBUM_DELETE'			=> sprintf($user->lang['ALBUM_DELETE'], $album_data['album_name']),
				'ALBUM_TYPE'			=> $album_data['album_type'],
				'S_PARENT_OPTIONS'		=> gallery_albumbox(false, '', $album_data['parent_id'], 'i_upload', $album_id),
				'ALBUM_NAME'			=> $album_data['album_name'],
				'ALBUM_DESC'			=> generate_text_for_display($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options']),
				'S_MOVE_ALBUM_OPTIONS'	=> gallery_albumbox(false, '', false, 'i_view', $album_id),
				'S_MOVE_IMAGE_OPTIONS'	=> gallery_albumbox(false, '', false, 'i_upload', $album_id),
			));
		}
		else
		{
			// Is it salty ?
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			$album = $album_data;
			if (($album['album_type'] == 1) && (($album['right_id'] - $album['left_id']) > 2))
			{
				$handle_subs = request_var('handle_subs', 0);
				// We have to learn how to delete or move the subs
				if ((($album['right_id'] - $album['left_id']) > 2) && ($handle_subs >= 0))
				{
					trigger_error($user->lang['DELETE_ALBUM_SUBS'] . adm_back_link($this->u_action));
				}
				else
				{
					trigger_error($user->lang['DELETE_ALBUM_SUBS'] . adm_back_link($this->u_action));
				}
			}
			else if ($album['album_type'] == 2)
			{
				// Delete images
				$handle_images = request_var('handle_images', -1);
				if ($handle_images < 0)
				{
					$sql = 'SELECT image_id, image_filename, image_thumbnail, image_album_id
							FROM ' . GALLERY_IMAGES_TABLE . '
							WHERE image_album_id = ' . $album_id;
					$result = $db->sql_query($sql);

					$images = array();
					$deleted_images = '';
					while ($row = $db->sql_fetchrow($result))
					{
						$images[] = $row;
						$deleted_images .= (($deleted_images) ? ', ' : '') . $row['image_id'];
					}
					$db->sql_freeresult($result);

					if (count($images) > 0)
					{
						// Delete the files themselves.
						for ($i = 0; $i < count($images); $i++)
						{
							@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $images[$i]['image_thumbnail']);
							@unlink($phpbb_root_path . GALLERY_MEDIUM_PATH . $images[$i]['image_filename']);
							@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $images[$i]['image_filename']);
						}

						$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . "
							WHERE comment_image_id IN ($deleted_images)";
						$result = $db->sql_query($sql);
						$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . "
							WHERE rate_image_id IN ($deleted_images)";
						$result = $db->sql_query($sql);
						$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . "
							WHERE image_id IN ($deleted_images)";
						$result = $db->sql_query($sql);
						$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . "
							WHERE image_album_id = $album_id";
						$result = $db->sql_query($sql);
						$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . "
							WHERE image_id IN ($deleted_images)";
						$result = $db->sql_query($sql);
					}
				}
				else
				{
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET image_album_id = ' . $handle_images . '
						WHERE image_album_id = ' . $album_id;
					$db->sql_query($sql);
				}
			}

			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
				SET left_id = left_id - 2
				WHERE album_user_id = {$album['album_user_id']}
					AND left_id > " . $album['left_id'];
			$db->sql_query($sql);

			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
				SET right_id = right_id - 2
				WHERE album_user_id = {$album['album_user_id']}
					AND right_id > " . $album['left_id'];
			$db->sql_query($sql);

			$sql = 'DELETE FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE album_id = ' . $album_id;
			$result = $db->sql_query($sql);

			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
			$cache->destroy('sql', GALLERY_COMMENTS_TABLE);
			$cache->destroy('sql', GALLERY_FAVORITES_TABLE);
			$cache->destroy('sql', GALLERY_IMAGES_TABLE);
			$cache->destroy('sql', GALLERY_RATES_TABLE);
			$cache->destroy('sql', GALLERY_WATCH_TABLE);
			$cache->destroy('_albums');
			trigger_error($user->lang['ALBUM_DELETED'] . adm_back_link($this->u_action));
		}
	}

	function move_album()
	{
		global $db, $user, $cache;

		$album_id = request_var('album_id', 0);
		$move = request_var('move', '');
		$moving = get_album_info($album_id);

		$sql = 'SELECT album_id, left_id, right_id
			FROM ' . GALLERY_ALBUMS_TABLE . "
			WHERE parent_id = {$moving['parent_id']}
				AND album_user_id = {$moving['album_user_id']}
				AND " . (($move == 'move_up') ? "right_id < {$moving['right_id']} ORDER BY right_id DESC" : "left_id > {$moving['left_id']} ORDER BY left_id ASC");
		$result = $db->sql_query_limit($sql, 1);
		$target = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!sizeof($target))
		{
			// The album is already on top or bottom
			return false;
		}

		if ($move == 'move_up')
		{
			$left_id = $target['left_id'];
			$right_id = $moving['right_id'];

			$diff_up = $moving['left_id'] - $target['left_id'];
			$diff_down = $moving['right_id'] + 1 - $moving['left_id'];

			$move_up_left = $moving['left_id'];
			$move_up_right = $moving['right_id'];
		}
		else
		{
			$left_id = $moving['left_id'];
			$right_id = $target['right_id'];

			$diff_up = $moving['right_id'] + 1 - $moving['left_id'];
			$diff_down = $target['right_id'] - $moving['right_id'];

			$move_up_left = $moving['right_id'] + 1;
			$move_up_right = $target['right_id'];
		}

		// Now do the dirty job
		$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
			SET left_id = left_id + CASE
				WHEN left_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			right_id = right_id + CASE
				WHEN right_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			album_parents = ''
			WHERE
				left_id BETWEEN {$left_id} AND {$right_id}
				AND right_id BETWEEN {$left_id} AND {$right_id}
				AND album_user_id = {$moving['album_user_id']}";
		$db->sql_query($sql);
		$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
		$cache->destroy('_albums');
		redirect($this->u_action);
	}

	function permissions()
	{
		global $db, $template, $user, $cache;

		$submit = (isset($_POST['submit'])) ? true : false;
		$delete = (isset($_POST['delete'])) ? true : false;
		$album_ary = request_var('album_ids', array(''));
		$album_list = implode(', ', $album_ary);
		$group_ary = request_var('group_ids', array(''));
		$group_list = implode(', ', $group_ary);
		$step = request_var('step', 0);
		$perm_system = request_var('perm_system', 0);
		if ($perm_system > 1)
		{
			$album_ary = array();
		}
		if ($delete)
		{
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}

			// User dropped the permissions
			$drop_perm_ary = request_var('drop_perm', array(''));
			$drop_perm_string = implode(', ', $drop_perm_ary);
			if ($drop_perm_string && $album_list)
			{
				$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . '
					WHERE ' . $db->sql_in_set('perm_group_id', $drop_perm_ary) . '
						AND ' . $db->sql_in_set('perm_album_id', $album_ary) . '
						AND perm_system = ' . $perm_system;
				$db->sql_query($sql);
			}
			else if ($drop_perm_string)
			{
				$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . '
					WHERE ' . $db->sql_in_set('perm_group_id', $drop_perm_ary) . '
						AND perm_system = ' . $perm_system;
				$db->sql_query($sql);
			}
			$step = 1;
		}

		$album_name_ary = array();
		// Build the array with some kind of order.
		$permissions = $permission_parts['misc'] = $permission_parts['m'] = $permission_parts['c'] = $permission_parts['i'] = array();
		if ($perm_system != 2)
		{
			$permission_parts['i'] = array_merge($permission_parts['i'], array('i_view'));
		}
		$permission_parts['i'] = array_merge($permission_parts['i'], array('i_watermark', 'i_upload'));
		if ($perm_system != 3)
		{
			$permission_parts['i'] = array_merge($permission_parts['i'], array('i_approve'));
		}
		$permission_parts['i'] = array_merge($permission_parts['i'], array('i_edit', 'i_delete', 'i_report', 'i_rate'));
		$permission_parts['c'] = array_merge($permission_parts['c'], array('c_read', 'c_post', 'c_edit', 'c_delete'));
		$permission_parts['m'] = array_merge($permission_parts['m'], array('m_comments', 'm_delete', 'm_edit', 'm_move', 'm_report', 'm_status'));
		$permission_parts['misc'] = array_merge($permission_parts['misc'], array('a_list'));
		if ($perm_system != 3)
		{
			$permission_parts['misc'] = array_merge($permission_parts['misc'], array('i_count'));
		}
		if ($perm_system == 2)
		{
			$permission_parts['misc'] = array_merge($permission_parts['misc'], array('album_count'));
		}
		$permissions = array_merge($permissions, $permission_parts['i'], $permission_parts['c'], $permission_parts['m'], $permission_parts['misc']);

		$albums = $cache->obtain_album_list();

		if ($step == 0)
		{
			$template->assign_var('ALBUM_LIST', gallery_albumbox(true, '', SETTING_PERMISSIONS));
			$step = 1;
		}
		else if ($step == 1)
		{
			if (request_var('uncheck', '') == '')
			{
				if (!check_form_key('acp_gallery'))
				{
					trigger_error('FORM_INVALID');
				}
			}
			else
			{
				$album_ary = array(request_var('album_id', 0));
			}
			if ($perm_system == 0)
			{
				foreach ($albums as $album)
				{
					if (in_array($album['album_id'], $album_ary))
					{
						$template->assign_block_vars('albumrow', array(
							'ALBUM_ID'				=> $album['album_id'],
							'ALBUM_NAME'			=> $album['album_name'],
						));
					}
				}
			}

			$sql = 'SELECT group_id, group_type, group_name, group_colour
				FROM ' . GROUPS_TABLE;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$row['group_name'] = ($row['group_type'] == 3) ? $user->lang['G_' . $row['group_name']] : $row['group_name'];
				$template->assign_block_vars('grouprow', array(
					'GROUP_ID'				=> $row['group_id'],
					'GROUP_NAME'			=> $row['group_name'],
				));
				$group[$row['group_id']]['group_name'] = $row['group_name'];
				$group[$row['group_id']]['group_colour'] = $row['group_colour'];
			}
			$db->sql_freeresult($result);

			if (!isset($album_ary[1]))
			{
				$where = '';
				if ($perm_system == 0)
				{
					if (!isset($album_ary[0]))
					{
						trigger_error('NO_ALBUM_SELECTED', E_USER_WARNING);
					}
					$where = 'perm_album_id = ' . $album_ary[0];
				}
				else
				{
					$where = 'perm_system = ' . $perm_system;
				}
				$sql2 = 'SELECT * FROM ' . GALLERY_PERMISSIONS_TABLE . "
					WHERE $where
						AND perm_group_id <> 0";
				$result2 = $db->sql_query($sql2);
				while ($row = $db->sql_fetchrow($result2))
				{
					$template->assign_block_vars('perm_grouprow', array(
						'GROUP_ID'				=> $row['perm_group_id'],
						'GROUP_COLOUR'			=> $group[$row['perm_group_id']]['group_colour'],
						'GROUP_NAME'			=> $group[$row['perm_group_id']]['group_name'],
					));
				}
				$db->sql_freeresult($result2);
			}
			$step = 2;
		}
		else if ($step == 2)
		{
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			//Album names
			foreach ($albums as $album)
			{
				if (in_array($album['album_id'], $album_ary))
				{
					$template->assign_block_vars('albumrow', array(
						'ALBUM_ID'				=> $album['album_id'],
						'ALBUM_NAME'			=> $album['album_name'],
					));
				}
			}
			//Group names
			if (!$group_list)
			{
				trigger_error($user->lang['PERMISSION_NO_GROUP'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
			$sql = 'SELECT group_id, group_type, group_name, group_colour
				FROM ' . GROUPS_TABLE . "
				WHERE group_id IN ($group_list)";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$row['group_name'] = ($row['group_type'] == 3) ? $user->lang['G_' . $row['group_name']] : $row['group_name'];
				$template->assign_block_vars('grouprow', array(
					'GROUP_ID'				=> $row['group_id'],
					'GROUP_NAME'			=> $row['group_name'],
					'GROUP_COLOUR'			=> $row['group_colour'],
				));
			}
			$db->sql_freeresult($result);
			if ((!isset($album_ary[1])) && (!isset($group_ary[1])))
			{
				$where = '';
				if ($perm_system == 0)
				{
					$where = 'p.perm_album_id = ' . $album_ary[0];
				}
				else
				{
					$where = 'p.perm_system = ' . $perm_system;
				}
				$sql = 'SELECT pr.*
					FROM ' . GALLERY_PERMISSIONS_TABLE . ' p
					LEFT JOIN ' .  GALLERY_ROLES_TABLE .  " pr
						ON p.perm_role_id = pr.role_id
					WHERE p.perm_group_id = {$group_ary[0]}
						AND $where";
				$result = $db->sql_query($sql);
				$perm_ary = $db->sql_fetchrow($result, 1);
				$db->sql_freeresult($result);
			}

			//Permissions
			foreach ($permission_parts as $perm_groupname => $permission)
			{
				$template->assign_block_vars('perm_group', array(
					'PERMISSION_GROUP'			=> $user->lang['PERMISSION_' . strtoupper($perm_groupname)],
					'PERM_GROUP_ID'				=> $perm_groupname,
				));
				$string = implode(', ', $permission);
				foreach ($permission_parts[$perm_groupname] as $permission)
				{
					#echo $permission;
					$template->assign_block_vars('perm_group.permission', array(
						'PERMISSION'			=> $user->lang['PERMISSION_' . strtoupper($permission)],
						'S_FIELD_NAME'			=> $permission,
						'S_NO'					=> ((isset($perm_ary[$permission]) && ($perm_ary[$permission] == 0)) ? true : false),
						'S_YES'					=> ((isset($perm_ary[$permission]) && ($perm_ary[$permission] == 1)) ? true : false),
						'S_NEVER'				=> ((isset($perm_ary[$permission]) && ($perm_ary[$permission] == 2)) ? true : false),
						'S_VALUE'				=> ((isset($perm_ary[$permission])) ? $perm_ary[$permission] : 0),
						'S_COUNT_FIELD'			=> (substr($permission, -6, 6) == '_count') ? true : false,
					));
				}
			}
			$step = 3;
		}
		else if ($step == 3)
		{
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			$set_moderator = false;
			foreach ($permissions as $permission)
			{
				// Hacked for deny empty submit
				$submitted_valued = request_var($permission, 0);
				if (substr($permission, -6, 6) == '_count')
				{
					$submitted_valued = $submitted_valued + 1;
				}
				else if ($submitted_valued == 0)
				{
					trigger_error('PERMISSION_EMPTY', E_USER_WARNING);
				}
				$sql_ary[$permission] = $submitted_valued - 1;
				if ((substr($permission, 0, 2) == 'm_') && ($sql_ary[$permission] == 1))
				{
					$set_moderator = true;
				}
			}
			// Need to set a defaults here: view your own personal albums
			if ($perm_system == 2)
			{
				$sql_ary['i_view'] = 1;
			}

			$db->sql_query('INSERT INTO ' . GALLERY_ROLES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
			$insert_role = $db->sql_nextid();
			if ($album_ary != array())
			{
				foreach ($album_ary as $album)
				{
					foreach ($group_ary as $group)
					{
						$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . " WHERE perm_album_id = $album AND perm_group_id = $group AND perm_system = $perm_system";
						$db->sql_query($sql);
						$sql_ary = array(
							'perm_role_id'			=> $insert_role,
							'perm_album_id'			=> $album,
							'perm_user_id'			=> 0,
							'perm_group_id'			=> $group,
							'perm_system'			=> $perm_system,
						);
						$db->sql_query('INSERT INTO ' . GALLERY_PERMISSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
						$sql = 'DELETE FROM ' . GALLERY_MODSCACHE_TABLE . " WHERE album_id = $album AND group_id = $group";
						$db->sql_query($sql);
						if ($set_moderator)
						{
							$sql = 'SELECT group_name FROM ' . GROUPS_TABLE . '
								WHERE ' . $db->sql_in_set('group_id', $group);
							$result = $db->sql_query($sql);
							while ($row = $db->sql_fetchrow($result))
							{
								$group_name = $row['group_name'];
							}
							$db->sql_freeresult($result);
							$sql_ary = array(
								'album_id'			=> $album,
								'group_id'			=> $group,
								'group_name'		=> $group_name,
							);
							$db->sql_query('INSERT INTO ' . GALLERY_MODSCACHE_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
						}
					}
				}
			}
			else
			{
				foreach ($group_ary as $group)
				{
					$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . " WHERE perm_group_id = $group AND perm_system = $perm_system";
					$db->sql_query($sql);
					$sql_ary = array(
						'perm_role_id'			=> $insert_role,
						'perm_album_id'			=> 0,
						'perm_user_id'			=> 0,
						'perm_group_id'			=> $group,
						'perm_system'			=> $perm_system,
					);
					$db->sql_query('INSERT INTO ' . GALLERY_PERMISSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
				}
			}
			$cache->destroy('sql', GALLERY_MODSCACHE_TABLE);
			trigger_error('PERMISSIONS_STORED');
		}


		if ($perm_system)
		{
			$hidden_fields = build_hidden_fields(array(
				'album_ids'			=> $album_ary,
				'group_ids'			=> $group_ary,
				'step'				=> $step,
				'perm_system'		=> $perm_system,
			));
		}
		else
		{
			$hidden_fields = build_hidden_fields(array(
				'album_ids'			=> $album_ary,
				'group_ids'			=> $group_ary,
				'step'				=> $step,
			));
		}

		$template->assign_vars(array(
			'S_HIDDEN_FIELDS'		=> $hidden_fields,
			'ALBUMS'				=> implode(', ', $album_name_ary),
			'GROUPS'				=> implode(', ', $group_ary),
			'STEP'					=> $step,
			'PERM_SYSTEM'			=> $perm_system,
			'S_ALBUM_ACTION' 		=> $this->u_action,
		));
	}

	function import()
	{
		global $gallery_config, $config, $db, $template, $user;
		global $gallery_root_path, $phpbb_root_path, $phpEx;

		$images = request_var('images', array(''), true);
		$images_string = request_var('images_string', '', true);
		$images = ($images_string) ? explode('&quot;', utf8_decode($images_string)) : $images;
		$submit = (isset($_POST['submit'])) ? true : ((empty($images)) ? false : true);

		$directory = $phpbb_root_path . GALLERY_IMPORT_PATH;

		if (!$submit)
		{
			$sql = 'SELECT username, user_id
				FROM ' . USERS_TABLE . "
				ORDER BY user_id ASC";
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

			$handle = opendir($directory);

			while ($file = readdir($handle))
			{
				if (!is_dir($directory . "$file") && (
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
				'S_SELECT_IMPORT' 				=> gallery_albumbox(true, 'album_id', 0, ''),
			));
		}
		else
		{
			/**
			* Commented to allow the loop
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			*/

			$done_images_string = request_var('done_images_string', '', true);
			$done_images = explode('&quot;', utf8_decode($done_images_string));
			$album_id = request_var('album_id', 0);
			if(!$album_id)
			{
				trigger_error('IMPORT_MISSING_ALBUM');
			}
			$user_id = request_var('user_id', 0);

			$sql = 'SELECT username, user_colour
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $user_id;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$username = $row['username'];
				$user_colour = $row['user_colour'];
			}
			$db->sql_freeresult($result);

			$results = array();
			$images_per_loop = 0;
			// This time we do:
			foreach ($images as $image)
			{
				if (($images_per_loop < 10) && !in_array($image, $done_images))
				{
					$results[] = $image;
					$images_per_loop++;
				}
			}

			$image_count = count($results);
			$counter = request_var('counter', 0);

			foreach ($results as $image)
			{
				$image_path = $directory . utf8_decode($image);

				$filetype = getimagesize($image_path);
				$image_width = $filetype[0];
				$image_height = $filetype[1];

				switch ($filetype['mime'])
				{
					case 'image/jpeg':
					case 'image/jpg':
					case 'image/pjpeg':
						$image_filetype = '.jpg';
						break;

					case 'image/png':
					case 'image/x-png':
						$image_filetype = '.png';
						break;

					case 'image/gif':
						$image_filetype = '.gif';
						break;

					default:
						break;
				}
				$image_filename = md5(unique_id()) . $image_filetype;

				copy($image_path, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);
				@chmod($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename, 0777);

				// The source image is imported, so we delete it.
				@unlink($image_path);

				$no_time = time();
				$time = request_var('time', 0);
				$time = ($time) ? $time : $no_time;

				$sql_ary = array(
					'image_filename' 		=> $image_filename,
					'image_thumbnail'		=> '',
					'image_desc'			=> '',
					'image_desc_uid'		=> '',
					'image_desc_bitfield'	=> '',
					'image_user_id'			=> $user_id,
					'image_username'		=> $username,
					'image_user_colour'		=> $user_colour,
					'image_user_ip'			=> $user->ip,
					'image_time'			=> $time + $counter,
					'image_album_id'		=> $album_id,
					'image_status'			=> 1,
				);
				$sql_ary['image_name'] = (request_var('filename', '') == 'filename') ? str_replace("_", " ", utf8_substr($image, 0, -4)) : str_replace('{NUM}', $counter + 1, request_var('image_name', '', true));
				if ($sql_ary['image_name'] == '')
				{
					$sql_ary['image_name'] = str_replace("_", " ", utf8_substr($image, 0, -4));
				}

				$db->sql_query('INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
				$counter++;
				$done_images[] = $image;
				$done_images_string .= (($done_images_string) ? '%22' : '') . urlencode($image);
			}
			$left = count($images) - count($done_images);

			$sql = 'UPDATE ' . GALLERY_USERS_TABLE . "
				SET user_images = user_images + $counter
				WHERE user_id = " . $user_id;
			$db->sql_query($sql);
			if ($db->sql_affectedrows() != 1)
			{
				$sql_ary = array(
					'user_id'				=> $user_id,
					'user_images'			=> $counter,
				);
				$sql = 'INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
				$db->sql_query($sql);
			}
			set_config('num_images', $config['num_images'] + $counter, true);
			update_album_info($album_id);

			$images_string			= urlencode(implode('"', $images));
			$done_images_string		= substr(urlencode(implode('"', $done_images)), 3, strlen($done_images_string));
			$images_to_do = str_replace('%22' . $done_images_string, "", '%22' . $images_string);
			if ('%22' . $images_string != $images_to_do)
			{
				$images_to_do = str_replace($done_images_string, "", $images_string);
				$images_to_do = substr($images_to_do, 3, strlen($images_to_do));
			}
			if ($images_to_do)
			{
				$imagename = request_var('image_name', '');
				$filename = request_var('filename', '');
				$forward_url = $this->u_action . "&amp;album_id=$album_id&amp;time=$time&amp;counter=$counter&amp;user_id=$user_id" . (($filename) ? '&amp;filename=' . request_var('filename', '') : '') . (($imagename && !$filename) ? '&amp;image_name=' . request_var('image_name', '') : '') . "&amp;images_string=$images_to_do";
				meta_refresh(1, $forward_url);
				trigger_error(sprintf($user->lang['IMPORT_DEBUG_MES'], $counter, $left + 1));
				
			}
			else
			{
				trigger_error(sprintf($user->lang['IMPORT_FINISHED'], $counter) . adm_back_link($this->u_action));
			}
		}
	}


	function cleanup()
	{
		global $db, $template, $user, $cache, $auth, $phpbb_root_path;

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
				$sql = 'SELECT album_id
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE ' . $db->sql_in_set('album_user_id', $delete_albums);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$deleted_albums[] = $row['album_id'];
				}
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
					$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $deleted_images);
					$db->sql_query($sql);
				}
				$sql = 'DELETE FROM ' . GALLERY_ALBUMS_TABLE . ' WHERE ' . $db->sql_in_set('album_id', $deleted_albums);
				$db->sql_query($sql);
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
				if ($missing_personals)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN_PERSONALS'] . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($personals_bad)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN_PERSONALS_BAD'] . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
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

}

?>