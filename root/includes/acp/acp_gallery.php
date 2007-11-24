<?php

/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class acp_gallery
{
	var $u_action;
	function main($id, $mode)
	{
		global $user, $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'gallery/includes/acp_functions.' . $phpEx);

		$user->add_lang('mods/gallery_acp');
		$user->add_lang('mods/gallery');

		// Set up the page
		$this->tpl_name 	= 'acp_gallery';
		// Salting the form...yumyum ...
		add_form_key('acp_gallery');

		switch ($mode)
		{
			case 'manage_albums':
				$action = request_var('action', '');
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
			
			case 'configure_gallery':
				$title = 'GALLERY_CONFIG';
				$this->page_title = $user->lang[$title];

				$this->configure_gallery();
			break;
			
			case 'manage_cache':
				$title = 'CLEAR_CACHE';
				$this->page_title = $user->lang[$title];
				$this->tpl_name = 'confirm_body';

				$this->manage_cache();
			break;
			
			case 'album_permissions':
				$title = 'ALBUM_AUTH_TITLE';
				$this->page_title = $user->lang[$title];

				$this->album_permissions();
			break;
			
			case 'album_personal_permissions':
				$title = 'ALBUM_PERSONAL_GALLERY_TITLE';
				$this->page_title = $user->lang[$title];

				$this->album_personal_permissions();
			break;
			
			default:
				$title = 'ACP_GALLERY_OVERVIEW';
				$this->page_title = $user->lang[$title];

				$this->overview();
			break;
		}
	}

	function overview()
	{
		global $template, $user;
		$template->assign_vars(array(
			'S_GALLERY_OVERVIEW'			=> true,
			'ACP_GALLERY_TITLE'				=> $user->lang['ACP_GALLERY_OVERVIEW'],
			'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ACP_GALLERY_OVERVIEW_EXPLAIN'],
		));
	}

	function configure_gallery()
	{
		global $db, $template, $user, $cache;

		$sql = 'SELECT * FROM ' . GALLERY_CONFIG_TABLE;
		$result = $db->sql_query($sql);

		while( $row = $db->sql_fetchrow($result) )
		{
			$config_name = $row['config_name'];
			$config_value = $row['config_value'];
			$default_config[$config_name] = isset($_POST['submit']) ? str_replace("'", "\'", $config_value) : $config_value;
			$new[$config_name] = request_var($config_name, $default_config[$config_name]);

			if( isset($_POST['submit']) )
			{
				// Is it salty ?
				if (!check_form_key('acp_gallery'))
				{
					trigger_error('FORM_INVALID');
				}

				$sql_ary = array(
					'config_value'		=> $new[$config_name],
				);
				$sql = 'UPDATE ' . GALLERY_CONFIG_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
					WHERE config_name = '$config_name'" ;
				$db->sql_query($sql);
			}
		}

		if (isset($_POST['submit']))
		{
			$cache->destroy('sql', GALLERY_CONFIG_TABLE);
			trigger_error($user->lang['GALLERY_CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'S_CONFIGURE_GALLERY'				=> true,
			'S_ALBUM_CONFIG_ACTION' 			=> $this->u_action,

			'ACP_GALLERY_TITLE'					=> $user->lang['GALLERY_CONFIG'],
			'ACP_GALLERY_TITLE_EXPLAIN'			=> $user->lang['GALLERY_CONFIG_EXPLAIN'],

			'MAX_PICS' 							=> $new['max_pics'],
			'MAX_FILE_SIZE' 					=> $new['max_file_size'],
			'MAX_WIDTH' 						=> $new['max_width'],
			'MAX_HEIGHT' 						=> $new['max_height'],
			'ROWS_PER_PAGE' 					=> $new['rows_per_page'],
			'COLS_PER_PAGE' 					=> $new['cols_per_page'],
			'WATERMARK_SOURCE' 					=> $new['watermark_source'],
			'THUMBNAIL_QUALITY' 				=> $new['thumbnail_quality'],
			'THUMBNAIL_SIZE' 					=> $new['thumbnail_size'],
			'PERSONAL_GALLERY_LIMIT' 			=> $new['personal_gallery_limit'],

			'USER_PICS_LIMIT' 					=> $new['user_pics_limit'],
			'MOD_PICS_LIMIT' 					=> $new['mod_pics_limit'],

			'THUMBNAIL_CACHE_ENABLED' 			=> ($new['thumbnail_cache'] == 1) ? 'checked="checked"' : '',
			'THUMBNAIL_CACHE_DISABLED' 			=> ($new['thumbnail_cache'] == 0) ? 'checked="checked"' : '',

			'JPG_ENABLED' 						=> ($new['jpg_allowed'] == 1) ? 'checked="checked"' : '',
			'JPG_DISABLED' 						=> ($new['jpg_allowed'] == 0) ? 'checked="checked"' : '',
			'PNG_ENABLED' 						=> ($new['png_allowed'] == 1) ? 'checked="checked"' : '',
			'PNG_DISABLED' 						=> ($new['png_allowed'] == 0) ? 'checked="checked"' : '',
			'GIF_ENABLED' 						=> ($new['gif_allowed'] == 1) ? 'checked="checked"' : '',
			'GIF_DISABLED' 						=> ($new['gif_allowed'] == 0) ? 'checked="checked"' : '',

			'PIC_DESC_MAX_LENGTH' 				=> $new['desc_length'],

			'WATERMARK_ENABLED' 				=> ($new['watermark_images'] == 1) ? 'checked="checked"' : '',
			'WATERMARK_DISABLED' 				=> ($new['watermark_images'] == 0) ? 'checked="checked"' : '',

			'HOTLINK_PREVENT_ENABLED' 			=> ($new['hotlink_prevent'] == 1) ? 'checked="checked"' : '',
			'HOTLINK_PREVENT_DISABLED' 			=> ($new['hotlink_prevent'] == 0) ? 'checked="checked"' : '',
			'HOTLINK_ALLOWED' 					=> $new['hotlink_allowed'],

			'PERSONAL_GALLERY_USER' 			=> ($new['personal_gallery'] == ALBUM_USER) ? 'checked="checked"' : '',
			'PERSONAL_GALLERY_PRIVATE' 			=> ($new['personal_gallery'] == ALBUM_PRIVATE) ? 'checked="checked"' : '',
			'PERSONAL_GALLERY_ADMIN' 			=> ($new['personal_gallery'] == ALBUM_ADMIN) ? 'checked="checked"' : '',
			'PERSONAL_GALLERY_VIEW_ALL' 		=> ($new['personal_gallery_view'] == ALBUM_GUEST) ? 'checked="checked"' : '',
			'PERSONAL_GALLERY_VIEW_REG' 		=> ($new['personal_gallery_view'] == ALBUM_USER) ? 'checked="checked"' : '',
			'PERSONAL_GALLERY_VIEW_PRIVATE' 	=> ($new['personal_gallery_view'] == ALBUM_PRIVATE) ? 'checked="checked"' : '',

			'RATE_ENABLED' 						=> ($new['rate'] == 1) ? 'checked="checked"' : '',
			'RATE_DISABLED' 					=> ($new['rate'] == 0) ? 'checked="checked"' : '',
			'RATE_SCALE' 						=> $new['rate_scale'],

			'COMMENT_ENABLED' 					=> ($new['comment'] == 1) ? 'checked="checked"' : '',
			'COMMENT_DISABLED' 					=> ($new['comment'] == 0) ? 'checked="checked"' : '',

			'NO_GD' 							=> ($new['gd_version'] == 0) ? 'checked="checked"' : '',
			'GD_V1' 							=> ($new['gd_version'] == 1) ? 'checked="checked"' : '',
			'GD_V2' 							=> ($new['gd_version'] == 2) ? 'checked="checked"' : '',

			'SORT_TIME' 						=> ($new['sort_method'] == 'pic_time') ? 'selected="selected"' : '',
			'SORT_PIC_TITLE' 					=> ($new['sort_method'] == 'pic_title') ? 'selected="selected"' : '',
			'SORT_USERNAME' 					=> ($new['sort_method'] == 'pic_user_id') ? 'selected="selected"' : '',
			'SORT_VIEW' 						=> ($new['sort_method'] == 'pic_view_count') ? 'selected="selected"' : '',
			'SORT_RATING' 						=> ($new['sort_method'] == 'rating') ? 'selected="selected"' : '',
			'SORT_COMMENTS' 					=> ($new['sort_method'] == 'comments') ? 'selected="selected"' : '',
			'SORT_NEW_COMMENT' 					=> ($new['sort_method'] == 'new_comment') ? 'selected="selected"' : '',
			'SORT_ASC' 							=> ($new['sort_order'] == 'ASC') ? 'selected="selected"' : '',
			'SORT_DESC' 						=> ($new['sort_order'] == 'DESC') ? 'selected="selected"' : '',

			'FULLPIC_POPUP_ENABLED' 			=> ($new['fullpic_popup'] == 1) ? 'checked="checked"' : '',
			'FULLPIC_POPUP_DISABLED' 			=> ($new['fullpic_popup'] == 0) ? 'checked="checked"' : '',

			'S_GUEST' 							=> ALBUM_GUEST,
			'S_USER' 							=> ALBUM_USER,
			'S_PRIVATE' 						=> ALBUM_PRIVATE,
			'S_MOD' 							=> ALBUM_MOD,
			'S_ADMIN' 							=> ALBUM_ADMIN,
		));
	}

	function album_permissions()
	{/*album*/
		global $db, $template, $user;

		if( !isset($_POST['submit']) )
		{
			// Build the category selector
			$album_list = make_album_select();

			$template->assign_vars(array(
				'S_ALBUM_PERMISSIONS_SELECT_ALBUM'	=> true,
				'S_ALBUM_ACTION' 					=> $this->u_action,

				'ACP_GALLERY_TITLE'				=> $user->lang['ALBUM_AUTH_TITLE'],
				'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ALBUM_AUTH_EXPLAIN'],
				'ALBUM_LIST'					=> $album_list,
			));
		}
		else
		{
			if(!isset($_GET['album_id']))
			{
				$album_id = request_var('album_id', 0);

				$template->assign_vars(array(
					'S_ALBUM_PERMISSIONS_SELECT_GROUPS'	=> true,
					'S_ALBUM_ACTION' 					=> $this->u_action . "&amp;album_id=$album_id",

					'ACP_GALLERY_TITLE'				=> $user->lang['ALBUM_AUTH_TITLE'],
					'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ALBUM_AUTH_EXPLAIN'],
					));

				// Get the list of phpBB usergroups
				$sql = 'SELECT group_id, group_name, group_type
						FROM ' . GROUPS_TABLE . '
						ORDER BY group_name ASC';
				$result = $db->sql_query($sql);

				while( $row = $db->sql_fetchrow($result) )
				{
					$groupdata[] = $row;
				}

				// Get info of this cat
				$sql = 'SELECT *
						FROM ' . GALLERY_ALBUMS_TABLE . "
						WHERE album_id = '$album_id'";
				$result = $db->sql_query($sql);

				$thiscat = $db->sql_fetchrow($result);

				$view_groups 		= @explode(',', $thiscat['album_view_groups']);
				$upload_groups 		= @explode(',', $thiscat['album_upload_groups']);
				$rate_groups 		= @explode(',', $thiscat['album_rate_groups']);
				$comment_groups 	= @explode(',', $thiscat['album_comment_groups']);
				$edit_groups 		= @explode(',', $thiscat['album_edit_groups']);
				$delete_groups 		= @explode(',', $thiscat['album_delete_groups']);

				$moderator_groups 	= @explode(',', $thiscat['album_moderator_groups']);

				for ($i = 0; $i < count($groupdata); $i++)
				{
					$template->assign_block_vars('grouprow', array(
						'GROUP_ID' 			=> $groupdata[$i]['group_id'],
						'GROUP_NAME' 		=> ($groupdata[$i]['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $groupdata[$i]['group_name']] : $groupdata[$i]['group_name'],

						'VIEW_CHECKED' 		=> (in_array($groupdata[$i]['group_id'], $view_groups)) ? 'checked="checked"' : '',
						'UPLOAD_CHECKED' 	=> (in_array($groupdata[$i]['group_id'], $upload_groups)) ? 'checked="checked"' : '',
						'RATE_CHECKED' 		=> (in_array($groupdata[$i]['group_id'], $rate_groups)) ? 'checked="checked"' : '',
						'COMMENT_CHECKED' 	=> (in_array($groupdata[$i]['group_id'], $comment_groups)) ? 'checked="checked"' : '',
						'EDIT_CHECKED' 		=> (in_array($groupdata[$i]['group_id'], $edit_groups)) ? 'checked="checked"' : '',
						'DELETE_CHECKED' 	=> (in_array($groupdata[$i]['group_id'], $delete_groups)) ? 'checked="checked"' : '',
						'MODERATOR_CHECKED' => (in_array($groupdata[$i]['group_id'], $moderator_groups)) ? 'checked="checked"' : '',
					));
				}
			}
			else
			{
				// Is it salty ?
				if (!check_form_key('acp_gallery'))
				{
					trigger_error('FORM_INVALID');
				}

				$album_id 		= request_var('album_id', 0);

				$view_groups 		= @implode(',', $_POST['view']);
				$upload_groups 		= @implode(',', $_POST['upload']);
				$rate_groups 		= @implode(',', $_POST['rate']);
				$comment_groups 	= @implode(',', $_POST['comment']);
				$edit_groups 		= @implode(',', $_POST['edit']);
				$delete_groups 		= @implode(',', $_POST['delete']);

				$moderator_groups 	= @implode(',', $_POST['moderator']);

				$sql_ary = array(
					'album_view_groups'		=> $view_groups,
					'album_upload_groups'		=> $upload_groups,
					'album_rate_groups'		=> $rate_groups,
					'album_comment_groups'	=> $comment_groups,
					'album_edit_groups'		=> $edit_groups,
					'album_delete_groups'		=> $delete_groups,
					'album_moderator_groups'	=> $moderator_groups,
					);

				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' 
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE album_id = ' . (int) $album_id;
				$db->sql_query($sql);

				trigger_error($user->lang['ALBUM_AUTH_SUCCESSFULLY'] . adm_back_link($this->u_action));
			}
		}

	}
	
	function album_personal_permissions()
	{/*album*/
		global $db, $template, $user;

		if( !isset($_POST['submit']) )
		{
			// Get the list of phpBB usergroups
			$sql = 'SELECT group_id, group_name, group_type
					FROM ' . GROUPS_TABLE . '
					ORDER BY group_name ASC';
			$result = $db->sql_query($sql);

			while( $row = $db->sql_fetchrow($result) )
			{
				$groupdata[] = $row;
			}

			// Get the current album settings for non created personal galleries
			$sql = 'SELECT *
					FROM ' . GALLERY_CONFIG_TABLE . "
					WHERE config_name = 'personal_gallery_private'";
			$result = $db->sql_query($sql);

			$row = $db->sql_fetchrow($result);

			$private_groups = explode(',', $row['config_value']);

			for($i = 0; $i < count($groupdata); $i++)
			{
				$template->assign_block_vars('creation_grouprow', array(
					'GROUP_ID' 			=> $groupdata[$i]['group_id'],
					'GROUP_NAME' 		=> ($groupdata[$i]['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $groupdata[$i]['group_name']] : $groupdata[$i]['group_name'],
					'PRIVATE_CHECKED' 	=> (in_array($groupdata[$i]['group_id'], $private_groups)) ? 'checked="checked"' : ''
				));
			}

			$template->assign_vars(array(
				'S_PERSONAL_ALBUM_PERMISSIONS_SELECT_GROUPS'	=> true,
				'S_ALBUM_ACTION' 								=> $this->u_action,

				'ACP_GALLERY_TITLE'							=> $user->lang['ALBUM_PERSONAL_GALLERY_TITLE'],
				'ACP_GALLERY_TITLE_EXPLAIN'					=> $user->lang['ALBUM_PERSONAL_GALLERY_EXPLAIN'],
			));
		}
		else
		{
			// Is it salty ?
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			$create_groups 		= @implode(',', $_POST['create']);

			$sql_ary = array(
				'config_value'		=> $create_groups,
			);

			$sql = 'UPDATE ' . GALLERY_CONFIG_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE config_name = 'personal_gallery_private'";
			$db->sql_query($sql);

			trigger_error($user->lang['ALBUM_AUTH_SUCCESSFULLY'] . adm_back_link($this->u_action));
		}
	}
	
	function manage_albums()
	{/*album*/
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;
		$catrow = array();
		$template->assign_vars(array(
			'S_MANAGE_ALBUMS'				=> true,
			'S_ALBUM_ACTION'				=> $this->u_action . '&amp;action=create',

			'ACP_GALLERY_TITLE'			=> $user->lang['ACP_MANAGE_ALBUMS'],
			'ACP_GALLERY_TITLE_EXPLAIN'	=> $user->lang['ACP_MANAGE_ALBUMS_EXPLAIN'],
		));
		$parent_id = request_var('parent_id', 0);
		if (!$parent_id)
		{
			$navigation = $user->lang['GALLERY_INDEX'];
		}
		else
		{
			$navigation = '<a href="' . $this->u_action . '">' . $user->lang['GALLERY_INDEX'] . '</a>';

			$albums_nav = get_album_branch($parent_id, 'parents', 'descending');
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
			ORDER BY left_id ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$album[] = $row;
		}

		for( $i = 0; $i < count($album); $i++ )
		{
			$folder_image = ($album[$i]['left_id'] + 1 != $album[$i]['right_id']) ? '<img src="images/icon_subfolder.gif" alt="' . $user->lang['SUBFORUM'] . '" />' : '<img src="images/icon_folder.gif" alt="' . $user->lang['FOLDER'] . '" />';
			$template->assign_block_vars('catrow', array(
				'FOLDER_IMAGE'			=> $folder_image,
				'U_ALBUM'				=> $this->u_action . '&amp;parent_id=' . $album[$i]['album_id'],
				'ALBUM_NAME'			=> $album[$i]['album_name'],
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
	{/*album*/
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;
		include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		$submit = (isset($_POST['submit'])) ? true : false;
		if(!$submit)
		{
			$parents_list = make_album_select(0, false, false, false, false);
			$template->assign_vars(array(
				'S_CREATE_ALBUM'				=> true,
				'ACP_GALLERY_TITLE'				=> $user->lang['GALLERY_ALBUMS_TITLE'],
				'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ACP_CREATE_ALBUM_EXPLAIN'],
				'S_PARENT_OPTIONS'				=> $parents_list,
				'S_ALBUM_ACTION'				=> $this->u_action . '&amp;action=create',
				'S_DESC_BBCODE_CHECKED'		=> true,
				'S_DESC_SMILIES_CHECKED'	=> true,
				'S_DESC_URLS_CHECKED'		=> true,
				'VIEW_LEVEL'				=> permission_drop_down_box('album_view_level', 1),
				'UPLOAD_LEVEL'				=> permission_drop_down_box('album_upload_level', 0),
				'RATE_LEVEL'				=> permission_drop_down_box('album_rate_level', 0),
				'COMMENT_LEVEL'				=> permission_drop_down_box('album_comment_level', 0),
				'EDIT_LEVEL'				=> permission_drop_down_box('album_edit_level', 0),
				'DELETE_LEVEL'				=> permission_drop_down_box('album_delete_level', 0),
				'IMAGE_APPROVAL'			=> permission_drop_down_box('album_approval', 0),
				));
		}
		else
		{// Is it salty ?
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			$album_data = array();
			$album_data = array(
				'album_name'					=> request_var('album_name', '', true),
				'parent_id'						=> request_var('parent_id', 0),
				//left_id and right_id are created some lines later
				'album_parents'					=> '',
				'album_type'					=> request_var('album_type', 0),
				'album_desc_options'			=> 7,
				'album_desc'					=> utf8_normalize_nfc(request_var('album_desc', '', true)),
				'album_view_level'				=> request_var('album_view_level', 0),
				'album_upload_level'			=> request_var('album_upload_level', 0),
				'album_rate_level'				=> request_var('album_rate_level', 0),
				'album_comment_level'			=> request_var('album_comment_level', 0),
				'album_edit_level'				=> request_var('album_edit_level', 0),
				'album_delete_level'			=> request_var('album_delete_level', 0),
				'album_approval'				=> request_var('album_approval', 0),
			);
			generate_text_for_storage($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options'], request_var('desc_parse_bbcode', false), request_var('desc_parse_urls', false), request_var('desc_parse_smilies', false));

			//the following is copied from the forum management. thx to the developers
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
					WHERE left_id > ' . $row['right_id'];
				$db->sql_query($sql);

				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
					SET right_id = right_id + 2
					WHERE ' . $row['left_id'] . ' BETWEEN left_id AND right_id';
				$db->sql_query($sql);

				$album_data['left_id'] = $row['right_id'];
				$album_data['right_id'] = $row['right_id'] + 1;
			}
			else
			{
				$sql = 'SELECT MAX(right_id) AS right_id
					FROM ' . GALLERY_ALBUMS_TABLE;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$album_data['left_id'] = $row['right_id'] + 1;
				$album_data['right_id'] = $row['right_id'] + 2;
			}
			$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);

			trigger_error($user->lang['NEW_ALBUM_CREATED'] . adm_back_link($this->u_action));
		}
	}

	function edit_album()
	{/*album*/
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;
		include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

		if (!$album_id = request_var('album_id', 0))
		{
			trigger_error('No Album ID', E_USER_WARNING);
		}

		$submit = (isset($_POST['submit'])) ? true : false;
		if(!$submit)
		{
			$sql = 'SELECT *
				FROM ' . GALLERY_ALBUMS_TABLE . "
				WHERE album_id = '$album_id'";
			$result = $db->sql_query($sql);
			if ($db->sql_affectedrows($result) == 0)
			{
				trigger_error('The requested album does not exist', E_USER_WARNING);
			}
			$album_data = $db->sql_fetchrow($result);
			$album_desc_data = generate_text_for_edit($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_options']);
			$parents_list = make_album_select($album_data['parent_id'], $album_id);

			$template->assign_vars(array(
				'S_EDIT_ALBUM'				=> true,
				'ACP_GALLERY_TITLE'			=> $user->lang['GALLERY_ALBUMS_TITLE'],
				'ACP_GALLERY_TITLE_EXPLAIN'	=> $user->lang['ACP_EDIT_ALBUM_EXPLAIN'],

				'S_ALBUM_ACTION' 			=> $this->u_action . '&amp;action=edit&amp;album_id=' . $album_id,
				'S_PARENT_OPTIONS'			=> $parents_list,

				'ALBUM_NAME' 				=> $album_data['album_name'],
				'ALBUM_DESC'				=> $album_desc_data['text'],
				'S_DESC_BBCODE_CHECKED'		=> ($album_desc_data['allow_bbcode']) ? true : false,
				'S_DESC_SMILIES_CHECKED'	=> ($album_desc_data['allow_smilies']) ? true : false,
				'S_DESC_URLS_CHECKED'		=> ($album_desc_data['allow_urls']) ? true : false,

				'VIEW_LEVEL'				=> permission_drop_down_box('album_view_level', $album_data['album_view_level']),
				'UPLOAD_LEVEL'				=> permission_drop_down_box('album_upload_level', $album_data['album_upload_level']),
				'RATE_LEVEL'				=> permission_drop_down_box('album_rate_level', $album_data['album_rate_level']),
				'COMMENT_LEVEL'				=> permission_drop_down_box('album_comment_level', $album_data['album_comment_level']),
				'EDIT_LEVEL'				=> permission_drop_down_box('album_edit_level', $album_data['album_edit_level']),
				'DELETE_LEVEL'				=> permission_drop_down_box('album_delete_level', $album_data['album_delete_level']),
				'IMAGE_APPROVAL'			=> permission_drop_down_box('album_approval', $album_data['album_approval']),

				'S_MODE' 				=> 'edit',
			));
		}
		else
		{// Is it salty ?
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			$album_data = array();
			$album_data = array(
				'album_name'					=> request_var('album_name', '', true),
				'parent_id'						=> request_var('parent_id', 0),
				//left_id and right_id are created some lines later
				'album_parents'					=> '',
				'album_type'					=> request_var('album_type', 0),
				'album_desc_options'			=> 7,
				'album_desc'					=> utf8_normalize_nfc(request_var('album_desc', '', true)),
				'album_view_level'				=> request_var('album_view_level', 0),
				'album_upload_level'			=> request_var('album_upload_level', 0),
				'album_rate_level'				=> request_var('album_rate_level', 0),
				'album_comment_level'			=> request_var('album_comment_level', 0),
				'album_edit_level'				=> request_var('album_edit_level', 0),
				'album_delete_level'			=> request_var('album_delete_level', 0),
				'album_approval'				=> request_var('album_approval', 0),
			);
			generate_text_for_storage($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options'], request_var('desc_parse_bbcode', false), request_var('desc_parse_urls', false), request_var('desc_parse_smilies', false));
			$row = get_album_info($album_id);
			if ($row['parent_id'] != $album_data['parent_id'])
			{//if the parent is different, we'll have to watch out because the left_id and right_id have changed
				//how many do we have to move and how far
				$moving_ids = ($row['right_id'] - $row['left_id']) + 1;
				$sql = 'SELECT MAX(right_id) AS right_id
					FROM ' . GALLERY_ALBUMS_TABLE;
				$result = $db->sql_query($sql);
				$highest = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				$moving_distance = ($highest['right_id'] - $row['left_id']) + 1;
				$stop_updating = $moving_distance + $row['left_id'];

				//echo '$moving_distance ' . $moving_distance . '; $moving_ids ' . $moving_ids;

				//update the moving album... move it to the end
				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
					SET right_id = right_id + ' . $moving_distance . ',
						left_id = left_id + ' . $moving_distance . '
					WHERE left_id >= ' . $row['left_id'] . '
						AND right_id <= ' . $row['right_id'];
				$db->sql_query($sql);
				$new['left_id'] = $row['left_id'] + $moving_distance;
				$new['right_id'] = $row['right_id'] + $moving_distance;

				//close the gap, we got
				if ($album_data['parent_id'] == 0)
				{//we move to root
					//left_id
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET left_id = left_id - ' . $moving_ids . '
						WHERE left_id >= ' . $row['left_id'];
					$db->sql_query($sql);
					//right_id
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET right_id = right_id - ' . $moving_ids . '
						WHERE right_id >= ' . $row['left_id'];
					$db->sql_query($sql);
				}
				else
				{
					//close the gap
					//left_id
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET left_id = left_id - ' . $moving_ids . '
						WHERE left_id >= ' . $row['left_id'] . '
							AND right_id <= ' . $stop_updating;
					$db->sql_query($sql);
					//right_id
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET right_id = right_id - ' . $moving_ids . '
						WHERE right_id >= ' . $row['left_id'] . '
							AND right_id <= ' . $stop_updating;
					$db->sql_query($sql);

					//create new gap
					//need parent_information
					$parent = get_album_info($album_data['parent_id']);
					//left_id
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET left_id = left_id + ' . $moving_ids . '
						WHERE left_id >= ' . $parent['right_id'] . '
							AND right_id <= ' . $stop_updating;
					$db->sql_query($sql);
					//right_id
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET right_id = right_id + ' . $moving_ids . '
						WHERE right_id >= ' . $parent['right_id'] . '
							AND right_id <= ' . $stop_updating;
					$db->sql_query($sql);

					//close the gap again
					//new parent right_id!!!
					$parent['right_id'] = $parent['right_id'] + $moving_ids;
					$move_back = ($new['right_id'] - $parent['right_id']) + 1;
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET left_id = left_id - ' . $move_back . ',
							right_id = right_id - ' . $move_back . '
						WHERE left_id >= ' . $stop_updating;
					$db->sql_query($sql);
				}
			}
			if ($row['album_name'] != $album_data['album_name'])
			{
				// the forum name has changed, clear the parents list of all forums (for safety)
				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
					SET album_parents = ''";
				$db->sql_query($sql);
			}
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' 
					SET ' . $db->sql_build_array('UPDATE', $album_data) . '
					WHERE album_id  = ' . (int) $album_id;
			$db->sql_query($sql);
			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);

			trigger_error($user->lang['ALBUM_UPDATED'] . adm_back_link($this->u_action));
		}
	}

	function delete_album()
	{/*album*/
		global $db, $template, $user, $cache;

		if (!$album_id = request_var('album_id', 0))
		{
			trigger_error('No Album ID', E_USER_WARNING);
		}
		else
		{
			$sql = 'SELECT *
				FROM ' . GALLERY_ALBUMS_TABLE . "
				WHERE album_id = '$album_id'";
			$result = $db->sql_query($sql);
			if ($db->sql_affectedrows($result) == 0)
			{
				trigger_error('The requested album does not exist', E_USER_WARNING);
			}
		}

		$submit = (isset($_POST['submit'])) ? true : false;
		if(!$submit)
		{/*album*/
			$sql = 'SELECT *
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE album_id = ' . $album_id;
			$result = $db->sql_query($sql);

			$album_found = false;
			while($row = $db->sql_fetchrow($result))
			{
				if($row['album_id'] == $album_id)
				{
					$thisalbum = $row;
					$album_found = true;
				}
				else
				{
					$albumrow[] = $row;
				}
			}

			if(!$album_found)
			{
				trigger_error('The requested album does not exist', E_USER_WARNING);
			}

			$template->assign_vars(array(
				'S_DELETE_ALBUM'		=> true,
				'ACP_GALLERY_TITLE'			=> $user->lang['DELETE_ALBUM'],
				'ACP_GALLERY_TITLE_EXPLAIN'	=> $user->lang['DELETE_ALBUM_EXPLAIN'],

				'S_ALBUM_ACTION'		=>  $this->u_action . '&amp;action=delete&amp;album_id=' . $album_id,
				'ALBUM_DELETE'			=> sprintf($user->lang['ALBUM_DELETE'], $thisalbum['album_name']),
				'ALBUM_TYPE'			=> $thisalbum['album_type'],
				'S_PARENT_OPTIONS'		=> make_album_select($thisalbum['parent_id'], $album_id),
				'ALBUM_NAME'			=> $thisalbum['album_name'],
				'ALBUM_DESC'			=> generate_text_for_display($thisalbum['album_desc'], $thisalbum['album_desc_uid'], $thisalbum['album_desc_bitfield'], $thisalbum['album_desc_options']),
				'S_MOVE_ALBUM_OPTIONS'	=> make_album_select(false, $album_id),
				'S_MOVE_IMAGE_OPTIONS'	=> make_album_select(false, $album_id, true),
			));
		}
		else
		{// Is it salty ?//$album_id
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			$album = get_album_info($album_id);
			if (($album['album_type'] == 1) && (($album['right_id'] - $album['left_id']) > 2))
			{//handle subs if there
				$handle_subs = request_var('handle_subs', 0);
				//we have to learn how to delete or move the subs
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
			{//handle images if there
				$handle_images = request_var('handle_images', -1);
				if ($handle_images < 0)
				{
					$sql = 'SELECT pic_id, pic_filename, pic_thumbnail, pic_cat_id
							FROM ' . GALLERY_IMAGES_TABLE . "
							WHERE pic_cat_id = '$album_id'";
					$result = $db->sql_query($sql);
					
					$picrow = array();
					while ($row = $db ->sql_fetchrow($result))
					{
						$picrow[] = $row;
						$pic_id_row[] = $row['pic_id'];
					}
					if(count($picrow) > 0)
					{
						// Delete all physical pic & cached thumbnail files
						for ($i = 0; $i < count($picrow); $i++)
						{
							@unlink('../' . ALBUM_CACHE_PATH . $picrow[$i]['pic_thumbnail']);
							@unlink('../' . ALBUM_UPLOAD_PATH . $picrow[$i]['pic_filename']);
						}

						$pic_id_sql = '(' . implode(',', $pic_id_row) . ')';
						// Delete all related ratings
						$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . '
							WHERE rate_pic_id IN ' . $pic_id_sql;
						$result = $db->sql_query($sql);
						// Delete all related comments
						$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . '
							WHERE comment_pic_id IN ' . $pic_id_sql;
						$result = $db->sql_query($sql);
						// Delete pic entries in db
						$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . "
							WHERE pic_cat_id = '$album_id'";
						$result = $db->sql_query($sql);
					}
				}
				else
				{
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET pic_cat_id = ' . $handle_images . '
						WHERE pic_cat_id = ' . $album_id;
					$db->sql_query($sql);
				}
			}
			//reorder the other albums
			//left_id
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
				SET left_id = left_id - 2
				WHERE left_id > ' . $album['left_id'];
			$db->sql_query($sql);
			//right_id
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
				SET right_id = right_id - 2
				WHERE right_id > ' . $album['left_id'];
			$db->sql_query($sql);
			$sql = 'DELETE FROM ' . GALLERY_ALBUMS_TABLE . "
				WHERE album_id = '$album_id'";
			$result = $db->sql_query($sql);
			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
			trigger_error($user->lang['ALBUM_DELETED'] . adm_back_link($this->u_action));
		}
	}

	function move_album()
	{/*album*/
		global $db, $user, $cache;

		if (!$album_id = request_var('album_id', 0))
		{
			trigger_error('No Album ID', E_USER_WARNING);
		}
		else
		{
			$sql = 'SELECT *
				FROM ' . GALLERY_ALBUMS_TABLE . "
				WHERE album_id = '$album_id'";
			$result = $db->sql_query($sql);
			if ($db->sql_affectedrows($result) == 0)
			{
				trigger_error('The requested album does not exist', E_USER_WARNING);
			}
		}
		$move = request_var('move', '', true);
		$moving = get_album_info($album_id);

		$sql = 'SELECT album_id, left_id, right_id
			FROM ' . GALLERY_ALBUMS_TABLE . "
			WHERE parent_id = {$moving['parent_id']}
				AND " . (($move == 'move_up') ? "right_id < {$moving['right_id']} ORDER BY right_id DESC" : "left_id > {$moving['left_id']} ORDER BY left_id ASC");
		$result = $db->sql_query_limit($sql, 1);

		$target = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$target = $row;
		}
		$db->sql_freeresult($result);

		if (!sizeof($target))
		{
			// The forum is already on top or bottom
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
				AND right_id BETWEEN {$left_id} AND {$right_id}";
		$db->sql_query($sql);
		$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
		trigger_error($user->lang['ALBUM_CHANGED_ORDER'] . adm_back_link($this->u_action));
	}
	
	function manage_cache()
	{
		global $db, $template, $user;
		if( !isset($_POST['confirm']) )
		{
			$template->assign_vars(array(
				'MESSAGE_TITLE' 		=> $user->lang['CLEAR_CACHE'],
				'MESSAGE_TEXT' 			=> $user->lang['GALLERY_CLEAR_CACHE_CONFIRM'],
				'S_CONFIRM_ACTION' 		=> $this->u_action,
				));
		}
		else
		{
			$cache_dir = @opendir('../' . ALBUM_DIR_NAME . ALBUM_CACHE_PATH);

			while( $cache_file = @readdir($cache_dir) )
			{
				if( preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $cache_file) )
				{
					@unlink('../' . ALBUM_DIR_NAME . ALBUM_CACHE_PATH . $cache_file);
				}
			}

			@closedir($cache_dir);
			trigger_error($user->lang['THUMBNAIL_CACHE_CLEARED_SUCCESSFULLY'] . adm_back_link($this->u_action));
		}
	}

}

?>