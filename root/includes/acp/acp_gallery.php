<?php

/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
//test the change thing
class acp_gallery
{
	var $u_action;
					
	function main($id, $mode)
	{
		global $user;
					
		$user->add_lang('mods/gallery_acp');
		$user->add_lang('mods/gallery');
							
		// Set up the page
		$this->tpl_name 	= 'acp_gallery';
		$this->page_title = 'ACP_GALLERY_' . strtoupper($mode);

		// Salting the form...yumyum ...
		add_form_key('acp_gallery');
		
		switch ($mode)
		{
			case 'manage_albums':
				$action = request_var('action', '');
				switch ($action)
				{
					case 'create':
						$this->create_album();
					break;
					
					case 'edit':
						$this->edit_album();
					break;
					
					case 'delete':
						$this->delete_album();
					break;
					
					case 'move':
						$this->move_album();
					break;
					
					default:
						$this->manage_albums();
					break;
				}
			break;
			
			case 'configure_gallery':
				$this->configure_gallery();
			break;
			
			case 'manage_cache':
				$this->tpl_name 	= 'confirm_body';
				$this->manage_cache();
			break;
			
			case 'album_permissions':
				$this->album_permissions();
			break;
			
			case 'album_personal_permissions':
				$this->album_personal_permissions();
			break;
			
			default:
				$this->overview();
			break;
		}
	}
	
	function overview()
	{
		global $template, $user;
		$template->assign_vars(array(
			'S_GALLERY_OVERVIEW'	=> true,
		));
	}
	
	function configure_gallery()
	{
		global $db, $template, $user;

		$sql = 'SELECT * FROM ' . ALBUM_CONFIG_TABLE;
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
				$sql = 'UPDATE ' . ALBUM_CONFIG_TABLE . ' 
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . " 
					WHERE config_name = '$config_name'" ;
				$db->sql_query($sql);
			}
		}
	
		if (isset($_POST['submit']))
		{
			trigger_error($user->lang['GALLERY_CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}
		
		$template->assign_vars(array(
			'S_CONFIGURE_GALLERY'				=> true,
			'S_ALBUM_CONFIG_ACTION' 			=> $this->u_action,
		
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
			
		//	'L_WATERMARK_IMAGES' 				=> $user->lang['WATERMARK_IMAGES'],
		//	'L_WATERMARK_SOURCE' 				=> $user->lang['WATERMARk_SOURCE'],
		
			'S_GUEST' 							=> ALBUM_GUEST,
			'S_USER' 							=> ALBUM_USER,
			'S_PRIVATE' 						=> ALBUM_PRIVATE,
			'S_MOD' 							=> ALBUM_MOD,
			'S_ADMIN' 							=> ALBUM_ADMIN,
		));
	}

	function album_permissions()
	{
		global $db, $template, $user;
		
		if( !isset($_POST['submit']) )
		{
			// Build the category selector
			$sql = 'SELECT cat_id, cat_title, cat_order
					FROM ' . ALBUM_CAT_TABLE . '
					ORDER BY cat_order ASC';
			$result = $db->sql_query($sql);
		
			while( $row = $db->sql_fetchrow($result) )
			{
				$catrows[] = $row;
			}
		
			for ($i = 0; $i < count($catrows); $i++)
			{
				$template->assign_block_vars('catrow', array(
					'CAT_ID' 	=> $catrows[$i]['cat_id'],
					'CAT_TITLE' => $catrows[$i]['cat_title'],
					));
			}
		
			$template->assign_vars(array(
				'S_ALBUM_PERMISSIONS_SELECT_ALBUM'	=> true,
				'S_ALBUM_ACTION' 		=> $this->u_action,
			));
		}
		else
		{
			if(!isset($_GET['cat_id']))
			{
				$cat_id = request_var('cat_id', 0);
		
				$template->assign_vars(array(/**/
					'S_ALBUM_PERMISSIONS_SELECT_GROUPS'	=> true,
					'L_GROUPS' 			=> $user->lang['USERGROUPS'],
					'L_VIEW' 			=> $user->lang['CAN_VIEW'],
					'L_UPLOAD' 			=> $user->lang['CAN_UPLOAD'],
					'L_RATE' 			=> $user->lang['CAN_RATE'],
					'L_COMMENT' 		=> $user->lang['CAN_COMMENT'],
					'L_EDIT' 			=> $user->lang['CAN_EDIT'],
					'L_DELETE' 			=> $user->lang['CAN_DELETE'],
					'L_IS_MODERATOR' 	=> $user->lang['IS_MODERATOR'],
					'S_ALBUM_ACTION' 	=> $this->u_action . "&amp;cat_id=$cat_id",
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
				$sql = 'SELECT cat_id, cat_title, cat_view_groups, cat_upload_groups, cat_rate_groups, cat_comment_groups, cat_edit_groups, cat_delete_groups, cat_moderator_groups
						FROM ' . ALBUM_CAT_TABLE . "
						WHERE cat_id = '$cat_id'";
				$result = $db->sql_query($sql);
		
				$thiscat = $db->sql_fetchrow($result);
		
				$view_groups 		= @explode(',', $thiscat['cat_view_groups']);
				$upload_groups 		= @explode(',', $thiscat['cat_upload_groups']);
				$rate_groups 		= @explode(',', $thiscat['cat_rate_groups']);
				$comment_groups 	= @explode(',', $thiscat['cat_comment_groups']);
				$edit_groups 		= @explode(',', $thiscat['cat_edit_groups']);
				$delete_groups 		= @explode(',', $thiscat['cat_delete_groups']);
		
				$moderator_groups 	= @explode(',', $thiscat['cat_moderator_groups']);
		
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
				
				$cat_id 		= request_var('cat_id', 0);
		
				$view_groups 		= @implode(',', $_POST['view']);
				$upload_groups 		= @implode(',', $_POST['upload']);
				$rate_groups 		= @implode(',', $_POST['rate']);
				$comment_groups 	= @implode(',', $_POST['comment']);
				$edit_groups 		= @implode(',', $_POST['edit']);
				$delete_groups 		= @implode(',', $_POST['delete']);
		
				$moderator_groups 	= @implode(',', $_POST['moderator']);

				$sql_ary = array(
					'cat_view_groups'		=> $view_groups,
					'cat_upload_groups'		=> $upload_groups,
					'cat_rate_groups'		=> $rate_groups,
					'cat_comment_groups'	=> $comment_groups,
					'cat_edit_groups'		=> $edit_groups,
					'cat_delete_groups'		=> $delete_groups,
					'cat_moderator_groups'	=> $moderator_groups,
					);
				
				$sql = 'UPDATE ' . ALBUM_CAT_TABLE . ' 
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE cat_id = ' . (int) $cat_id;
				$db->sql_query($sql);
		
				trigger_error($user->lang['ALBUM_AUTH_SUCCESSFULLY'] . adm_back_link($this->u_action));
			}
		}

	}
	
	function album_personal_permissions()
	{
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
					FROM ' . ALBUM_CONFIG_TABLE . "
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
					) //end array
				);
				
			}
			
			$template->assign_vars(array(/**/
				'S_PERSONAL_ALBUM_PERMISSIONS_SELECT_GROUPS'	=> true,
				'L_ALBUM_AUTH_TITEL'	=> $user->lang['ALBUM_PERSONAL_GALLERY_TITLE'],
				'L_ALBUM_AUTH_EXPLAIN'	=> $user->lang['ALBUM_PERSONAL_GALLERY_EXPLAIN'],
				'S_ALBUM_ACTION' 		=> $this->u_action,
			));
		}

	}
	
	function manage_albums()
	{
		global $db, $user, $auth, $template, $cache;/**/
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;/**/
		$template->assign_vars(array(
			'S_MANAGE_ALBUMS'		=> true,
			'S_ALBUM_ACTION'		=> $this->u_action . '&amp;action=create',
			'TEST'					=> $this->u_action . '&amp;parent_id='
		));

		$sql = 'SELECT *
				FROM ' . ALBUM_CAT_TABLE . '
				ORDER BY cat_order ASC';
		$result = $db->sql_query($sql);
		
		while ($row = $db->sql_fetchrow($result))
		{
			$catrow[] = $row;
		}

		for( $i = 0; $i < count($catrow); $i++ )
		{
			$template->assign_block_vars('catrow', array(
				'COLOR' 			=> ($i % 2) ? 'row1' : 'row2',
				'TITLE' 			=> $catrow[$i]['cat_title'],
				'DESC' 				=> generate_text_for_display($catrow[$i]['cat_desc'], $catrow[$i]['cat_desc_bbcode_uid'], $catrow[$i]['cat_desc_bbcode_bitfield'], 7),/**/
				'S_MOVE_UP' 		=> $this->u_action . '&amp;action=move&amp;move=-15&amp;cat_id=' . $catrow[$i]['cat_id'],
				'S_MOVE_DOWN' 		=> $this->u_action . '&amp;action=move&amp;move=15&amp;cat_id=' . $catrow[$i]['cat_id'],
				'S_EDIT_ACTION' 	=> $this->u_action . '&amp;action=edit&amp;cat_id=' . $catrow[$i]['cat_id'],
				'S_DELETE_ACTION' 	=> $this->u_action . '&amp;action=delete&amp;cat_id=' . $catrow[$i]['cat_id'],
				));
		}

	}
	
	function create_album()
	{
		global $db, $user, $auth, $template, $cache;/**/
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;/**/
		include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);/**/
		if( !isset($_POST['cat_title']) )
		{
			$template->assign_vars(array(/**/
				'S_CREATE_ALBUM'		=> true,
				'L_ALBUM_CAT_TITLE' 	=> $user->lang['GALLERY_ALBUMS_TITLE'],
				'L_ALBUM_CAT_EXPLAIN' 	=> $user->lang['ACP_CREATE_ALBUM_EXPLAIN'],
				'S_ALBUM_ACTION' 		=> $this->u_action . '&amp;action=create',
				'L_CAT_TITLE' 			=> $user->lang['ALBUM_TITLE'],
				'L_CAT_DESC' 			=> $user->lang['ALBUM_DESC'],
				'L_CAT_PERMISSIONS' 	=> $user->lang['ALBUM_PERMISSIONS'],
				'L_VIEW_LEVEL' 			=> $user->lang['VIEW_LEVEL'],
				'L_UPLOAD_LEVEL' 		=> $user->lang['UPLOAD_LEVEL'],
				'L_RATE_LEVEL' 			=> $user->lang['RATE_LEVEL'],
				'L_COMMENT_LEVEL' 		=> $user->lang['COMMENT_LEVEL'],
				'L_EDIT_LEVEL' 			=> $user->lang['EDIT_LEVEL'],
				'L_DELETE_LEVEL' 		=> $user->lang['DELETE_LEVEL'],
				'L_PICS_APPROVAL' 		=> $user->lang['IMAGE_APPROVAL'],
				'L_GUEST' 				=> $user->lang['GALLERY_ALL'], 
				'L_REG' 				=> $user->lang['GALLERY_REG'], 
				'L_PRIVATE' 			=> $user->lang['GALLERY_PRIVATE'], 
				'L_MOD' 				=> $user->lang['GALLERY_MOD'], 
				'L_ADMIN' 				=> $user->lang['GALLERY_ADMIN'],

				'L_DISABLED' 			=> $user->lang['DISABLED'],

				'VIEW_GUEST' 			=> 'selected="selected"',
				'UPLOAD_REG' 			=> 'selected="selected"',
				'RATE_REG' 				=> 'selected="selected"',
				'COMMENT_REG' 			=> 'selected="selected"',
				'EDIT_REG' 				=> 'selected="selected"',
				'DELETE_MOD' 			=> 'selected="selected"',
				'APPROVAL_DISABLED' 	=> 'selected="selected"',

				'S_MODE' 				=> 'new',

				'S_GUEST' 				=> ALBUM_GUEST,
				'S_USER' 				=> ALBUM_USER,
				'S_PRIVATE' 			=> ALBUM_PRIVATE,
				'S_MOD' 				=> ALBUM_MOD,
				'S_ADMIN' 				=> ALBUM_ADMIN,
				));
		}
		else
		{
			// Is it salty ?
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			
			// Get posting variables
			$cat_title 		= request_var('cat_title', '', true);/**/
			$cat_desc 		= request_var('cat_desc', '', true);/**/
			$view_level 	= request_var('cat_view_level', 1);
			$upload_level 	= request_var('cat_upload_level', 0);
			$rate_level 	= request_var('cat_rate_level', 0);
			$comment_level 	= request_var('cat_comment_level', 0);
			$edit_level 	= request_var('cat_edit_level', 0);
			$delete_level 	= request_var('cat_delete_level', 0);
			$cat_approval 	= request_var('cat_approval', 0);

			// Get the last ordered category
			$sql = 'SELECT cat_order 
					FROM ' . ALBUM_CAT_TABLE . '
					ORDER BY cat_order DESC
					LIMIT 1';
			$result 					= $db->sql_query($sql);
			$row 						= $db->sql_fetchrow($result);
			$last_order 				= $row['cat_order'];
			$cat_order 					= $last_order + 10;
			$message_parser 			= new parse_message();
			$message_parser->message 	= utf8_normalize_nfc(request_var('cat_desc', '', true));
			
			if($message_parser->message)
			{
				$message_parser->parse(true, true, true, true, false, true, true, true);
			}
			// Here we insert a new row into the db
			/**/
			$sql_ary = array(
				'cat_title'					=> $cat_title,
				'cat_desc'					=> $message_parser->message,
				'cat_desc_bbcode_uid'		=> $message_parser->bbcode_uid,
				'cat_desc_bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
				'cat_order'					=> $cat_order,
				'cat_view_level'			=> $view_level,
				'cat_upload_level'			=> $upload_level,
				'cat_rate_level'			=> $rate_level,
				'cat_comment_level'			=> $comment_level,
				'cat_edit_level'			=> $edit_level,
				'cat_delete_level'			=> $delete_level,
				'cat_approval'				=> $cat_approval,
			);
			
			$db->sql_query('INSERT INTO ' . ALBUM_CAT_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

			// Return a message...
			//$message = $user->lang['NEW_CATEGORY_CREATED'] . "<br /><br />" . sprintf($user->lang['Click_return_album_category'], "<a href=\"" . append_sid("admin_album_cat.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($user->lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

			trigger_error($user->lang['NEW_ALBUM_CREATED'] . adm_back_link($this->u_action));
		}
	}
	
	function edit_album()
	{
		global $db, $user, $auth, $template, $cache;/**/
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;/**/
		include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);/**/

		if (!$cat_id = request_var('cat_id', 0))
		{
			trigger_error('No Album ID', E_USER_WARNING);
		}
		
		if(!isset($_POST['cat_title']))
		{
			$sql = 'SELECT *
					FROM ' . ALBUM_CAT_TABLE . "
					WHERE cat_id = '$cat_id'";
			$result = $db->sql_query($sql);
			
			if( $db->sql_affectedrows($result) == 0 )
			{
				trigger_error('The requested album does not exist', E_USER_WARNING);
			}
			
			$catrow = $db->sql_fetchrow($result);
			
			$message_parser 			= new parse_message();
			$message_parser->message 	= $catrow['cat_desc'];
			$message_parser->decode_message($catrow['cat_desc_bbcode_uid']);
			
			$template->assign_vars(array(/**/
				'S_CREATE_ALBUM'		=> true,
				'S_CREATE_ALBUM2'		=> true,

				'L_ALBUM_CAT_TITLE' 	=> $user->lang['GALLERY_ALBUMS_TITLE'],
				'L_ALBUM_CAT_EXPLAIN' 	=> $user->lang['ACP_EDIT_ALBUM_EXPLAIN'],
				'S_ALBUM_ACTION' 		=> $this->u_action . '&amp;action=edit&amp;cat_id=' . $cat_id,
				'L_CAT_TITLE' 			=> $user->lang['ALBUM_TITLE'],
				'L_CAT_DESC' 			=> $user->lang['ALBUM_DESC'],
				'L_CAT_PERMISSIONS' 	=> $user->lang['ALBUM_PERMISSIONS'],
				'L_PICS_APPROVAL' 		=> $user->lang['IMAGE_APPROVAL'],
				'L_GUEST' 				=> $user->lang['GALLERY_ALL'], 
				'L_REG' 				=> $user->lang['GALLERY_REG'], 
				'L_PRIVATE' 			=> $user->lang['GALLERY_PRIVATE'], 
				'L_MOD' 				=> $user->lang['GALLERY_MOD'], 
				'L_ADMIN' 				=> $user->lang['GALLERY_ADMIN'],

				'S_CAT_TITLE' 			=> $catrow['cat_title'],
				'S_CAT_DESC' 			=> $message_parser->message,

				'VIEW_GUEST' 			=> ($catrow['cat_view_level'] == ALBUM_GUEST) ? 'selected="selected"' : '',
				'VIEW_REG' 				=> ($catrow['cat_view_level'] == ALBUM_USER) ? 'selected="selected"' : '',
				'VIEW_PRIVATE' 			=> ($catrow['cat_view_level'] == ALBUM_PRIVATE) ? 'selected="selected"' : '',
				'VIEW_MOD' 				=> ($catrow['cat_view_level'] == ALBUM_MOD) ? 'selected="selected"' : '',
				'VIEW_ADMIN' 			=> ($catrow['cat_view_level'] == ALBUM_ADMIN) ? 'selected="selected"' : '',

				'UPLOAD_GUEST' 			=> ($catrow['cat_upload_level'] == ALBUM_GUEST) ? 'selected="selected"' : '',
				'UPLOAD_REG' 			=> ($catrow['cat_upload_level'] == ALBUM_USER) ? 'selected="selected"' : '',
				'UPLOAD_PRIVATE' 		=> ($catrow['cat_upload_level'] == ALBUM_PRIVATE) ? 'selected="selected"' : '',
				'UPLOAD_MOD' 			=> ($catrow['cat_upload_level'] == ALBUM_MOD) ? 'selected="selected"' : '',
				'UPLOAD_ADMIN' 			=> ($catrow['cat_upload_level'] == ALBUM_ADMIN) ? 'selected="selected"' : '',

				'RATE_GUEST' 			=> ($catrow['cat_rate_level'] == ALBUM_GUEST) ? 'selected="selected"' : '',
				'RATE_REG' 				=> ($catrow['cat_rate_level'] == ALBUM_USER) ? 'selected="selected"' : '',
				'RATE_PRIVATE' 			=> ($catrow['cat_rate_level'] == ALBUM_PRIVATE) ? 'selected="selected"' : '',
				'RATE_MOD' 				=> ($catrow['cat_rate_level'] == ALBUM_MOD) ? 'selected="selected"' : '',
				'RATE_ADMIN' 			=> ($catrow['cat_rate_level'] == ALBUM_ADMIN) ? 'selected="selected"' : '',

				'COMMENT_GUEST' 		=> ($catrow['cat_comment_level'] == ALBUM_GUEST) ? 'selected="selected"' : '',
				'COMMENT_REG' 			=> ($catrow['cat_comment_level'] == ALBUM_USER) ? 'selected="selected"' : '',
				'COMMENT_PRIVATE' 		=> ($catrow['cat_comment_level'] == ALBUM_PRIVATE) ? 'selected="selected"' : '',
				'COMMENT_MOD' 			=> ($catrow['cat_comment_level'] == ALBUM_MOD) ? 'selected="selected"' : '',
				'COMMENT_ADMIN' 		=> ($catrow['cat_comment_level'] == ALBUM_ADMIN) ? 'selected="selected"' : '',

				'EDIT_REG' 				=> ($catrow['cat_edit_level'] == ALBUM_USER) ? 'selected="selected"' : '',
				'EDIT_PRIVATE' 			=> ($catrow['cat_edit_level'] == ALBUM_PRIVATE) ? 'selected="selected"' : '',
				'EDIT_MOD' 				=> ($catrow['cat_edit_level'] == ALBUM_MOD) ? 'selected="selected"' : '',
				'EDIT_ADMIN' 			=> ($catrow['cat_edit_level'] == ALBUM_ADMIN) ? 'selected="selected"' : '',

				'DELETE_REG' 			=> ($catrow['cat_delete_level'] == ALBUM_USER) ? 'selected="selected"' : '',
				'DELETE_PRIVATE' 		=> ($catrow['cat_delete_level'] == ALBUM_PRIVATE) ? 'selected="selected"' : '',
				'DELETE_MOD' 			=> ($catrow['cat_delete_level'] == ALBUM_MOD) ? 'selected="selected"' : '',
				'DELETE_ADMIN' 			=> ($catrow['cat_delete_level'] == ALBUM_ADMIN) ? 'selected="selected"' : '',

				'APPROVAL_DISABLED' 	=> ($catrow['cat_approval'] == ALBUM_USER) ? 'selected="selected"' : '',
				'APPROVAL_MOD' 			=> ($catrow['cat_approval'] == ALBUM_MOD) ? 'selected="selected"' : '',
				'APPROVAL_ADMIN' 		=> ($catrow['cat_approval'] == ALBUM_ADMIN) ? 'selected="selected"' : '',

				'S_MODE' 				=> 'edit',

				'S_GUEST' 				=> ALBUM_GUEST,
				'S_USER' 				=> ALBUM_USER,
				'S_PRIVATE' 			=> ALBUM_PRIVATE,
				'S_MOD' 				=> ALBUM_MOD,
				'S_ADMIN' 				=> ALBUM_ADMIN,

				'L_PANEL_TITLE' 		=> $user->lang['EDIT_ALBUM'],
			));
		}
		else
		{
			// Is it salty ?
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			
			$cat_title 		= request_var('cat_title', '', true);
			$view_level 	= request_var('cat_view_level', 1);
			$upload_level 	= request_var('cat_upload_level', 0);
			$rate_level 	= request_var('cat_rate_level', 0);
			$comment_level 	= request_var('cat_comment_level', 0);
			$edit_level 	= request_var('cat_edit_level', 0);
			$delete_level 	= request_var('cat_delete_level', 0);
			$cat_approval 	= request_var('cat_approval', 0);

			$message_parser 			= new parse_message();
			$message_parser->message 	= utf8_normalize_nfc(request_var('cat_desc', '', true));
			$message_parser->parse(true, true, true, true, false, true, true, true);
			
			// Now we update this row
			/**/
			$sql_ary = array(
				'cat_title'				=> $cat_title,
				'cat_desc'				=> $message_parser->message,
				'cat_desc_bbcode_uid'	=> $message_parser->bbcode_uid,
				'cat_desc_bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
				//'cat_order'			=> $cat_order,
				'cat_view_level'		=> $view_level,
				'cat_upload_level'		=> $upload_level,
				'cat_rate_level'		=> $rate_level,
				'cat_comment_level'		=> $comment_level,
				'cat_edit_level'		=> $edit_level,
				'cat_delete_level'		=> $delete_level,
				'cat_approval'			=> $cat_approval,
			);
			
			$sql = 'UPDATE ' . ALBUM_CAT_TABLE . ' 
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE cat_id  = ' . (int) $cat_id;

			$db->sql_query($sql);

			// Return a message...
			//$message =  . "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_ALBUM'], "<a href=\"" . append_sid("admin_album_cat.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($user->lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");
			
			trigger_error($user->lang['ALBUM_UPDATED'] . adm_back_link($this->u_action));
		}
	}
	
	function delete_album()
	{
		global $db, $template, $user;

		if (!$cat_id = request_var('cat_id', 0))
		{
			trigger_error('No Album ID', E_USER_WARNING);
		}
		
		if( !isset($_POST['submit']) )
		{
			$sql = 'SELECT cat_id, cat_title, cat_order
					FROM ' . ALBUM_CAT_TABLE . '
					ORDER BY cat_order ASC';
			$result = $db->sql_query($sql);

			$cat_found = false;
			while( $row = $db->sql_fetchrow($result) )
			{
				if($row['cat_id'] == $cat_id)
				{
					$thiscat = $row;
					$cat_found = true;
				}
				else
				{
					$catrow[] = $row;
				}
			}
			
			if(!$cat_found)
			{
				trigger_error('The requested album does not exist', E_USER_WARNING);
			}

			$select_to = '<select name="target"><option value="0">'. $user->lang['DELETE_ALL_IMAGES'] .'</option>';
			for ($i = 0; $i < count($catrow); $i++)
			{
				$select_to .= '<option value="'. $catrow[$i]['cat_id'] .'">'. $catrow[$i]['cat_title'] .'</option>';
			}
			$select_to .= '</select>';

			$template->assign_vars(array(
				'S_DELETE_ALBUM'		=> true,
				
				'S_ALBUM_ACTION' 		=>  $this->u_action . '&amp;action=delete&amp;cat_id=' . $cat_id,
				'L_CAT_DELETE' 			=> $user->lang['DELETE_ALBUM'],
				'L_CAT_DELETE_EXPLAIN' 	=> $user->lang['DELETE_ALBUM_EXPLAIN'],
				'L_CAT_TITLE' 			=> $user->lang['ALBUM_TITLE'],
				'S_CAT_TITLE' 			=> $thiscat['cat_title'],
				'L_MOVE_CONTENTS' 		=> $user->lang['MOVE_CONTENTS'],
				'L_MOVE_DELETE' 		=> $user->lang['MOVE_AND_DELETE'],
				'S_SELECT_TO' 			=> $select_to,
				));
		}
		else
		{
			// Is it salty ?
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			
			$target = request_var('target', 0);
			if(!$target) // Delete All
			{
				// Get file information of all pics in this category
				$sql = 'SELECT pic_id, pic_filename, pic_thumbnail, pic_cat_id
						FROM ' . ALBUM_TABLE . "
						WHERE pic_cat_id = '$cat_id'";
				$result = $db->sql_query($sql);
				
				$picrow = array();
				while( $row = $db ->sql_fetchrow($result) )
				{
					$picrow[] = $row;
					$pic_id_row[] = $row['pic_id'];
				}
				if(count($picrow) > 0) // if this category is not empty
				{
					// Delete all physical pic & cached thumbnail files
					for ($i = 0; $i < count($picrow); $i++)
					{
						@unlink('../' . ALBUM_CACHE_PATH . $picrow[$i]['pic_thumbnail']);
						@unlink('../' . ALBUM_UPLOAD_PATH . $picrow[$i]['pic_filename']);
					}
					
					$pic_id_sql = '(' . implode(',', $pic_id_row) . ')';
					
					// Delete all related ratings
					$sql = 'DELETE FROM ' . ALBUM_RATE_TABLE . '
							WHERE rate_pic_id IN ' . $pic_id_sql;
					$result = $db->sql_query($sql);
					
					// Delete all related comments
					$sql = 'DELETE FROM ' . ALBUM_COMMENT_TABLE . '
							WHERE comment_pic_id IN ' . $pic_id_sql;
					$result = $db->sql_query($sql);
					
					// Delete pic entries in db
					$sql = 'DELETE FROM ' . ALBUM_TABLE . "
							WHERE pic_cat_id = '$cat_id'";
					$result = $db->sql_query($sql);
				}
				
				// This category is now emptied, we can remove it!
				$sql = 'DELETE FROM ' . ALBUM_CAT_TABLE . "
						WHERE cat_id = '$cat_id'";
				$result = $db->sql_query($sql);
				
				// Re-order the rest of categories
				$this->reorder_cat();
				
				// Return a message...
				//$message = $user->lang['Category_deleted'] . "<br /><br />" . sprintf($user->lang['Click_return_album_category'], "<a href=\"" . append_sid("admin_album_cat.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($user->lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");
				
				trigger_error($user->lang['ALBUM_DELETED'] . adm_back_link($this->u_action));
			}
			else // Move content...
			{				
				$sql = 'UPDATE ' . ALBUM_TABLE . "
						SET pic_cat_id = '$target'
						WHERE pic_cat_id = '$cat_id'";
				$result = $db->sql_query($sql);
				
				// This category is now emptied, we can remove it!
				$sql = 'DELETE FROM ' . ALBUM_CAT_TABLE . "
						WHERE cat_id = '$cat_id'";
				$result = $db->sql_query($sql);
				
				// Re-order the rest of categories
				$this->reorder_cat();
				
				// Return a message...
				//$message = $user->lang['Category_deleted'] . "<br /><br />" . sprintf($user->lang['Click_return_album_category'], "<a href=\"" . append_sid("admin_album_cat.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($user->lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");
				
				trigger_error($user->lang['ALBUM_DELETED'] . adm_back_link($this->u_action));
			}
		}
	}
	
	function move_album()
	{
		global $db, $user;

		if (!$cat_id = request_var('cat_id', 0))
		{
			trigger_error('No Album ID', E_USER_WARNING);
		}
		
		$move = request_var('move', 0);

		$sql = 'UPDATE ' . ALBUM_CAT_TABLE . "
				SET cat_order = cat_order + $move
				WHERE cat_id = $cat_id";
		$db->sql_query($sql);

		$this->reorder_cat();

		// Return a message...
		//$message = $user->lang['Category_changed_order'] . "<br /><br />" . sprintf($user->lang['Click_return_album_category'], "<a href=\"" . append_sid("admin_album_cat.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($user->lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

		trigger_error($user->lang['ALBUM_CHANGED_ORDER'] . adm_back_link($this->u_action));
	}
	
	function manage_cache()
	{
		global $db, $template, $user;
		if( !isset($_POST['confirm']) )
		{
			$template->assign_vars(array(
				'MESSAGE_TITLE' 	=> $user->lang['CLEAR_CACHE'],
				'MESSAGE_TEXT' 		=> $user->lang['GALLERY_CLEAR_CACHE_CONFIRM'],
				'S_CONFIRM_ACTION' 	=> $this->u_action,
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
	
	function reorder_cat()
	{
		global $db;
	
		$sql = 'SELECT cat_id, cat_order
				FROM ' . ALBUM_CAT_TABLE . '
				WHERE cat_id <> 0
				ORDER BY cat_order ASC';
		$result = $db->sql_query($sql);
	
		$i = 10;
	
		while( $row = $db->sql_fetchrow($result) )
		{
			$sql = 'UPDATE ' . ALBUM_CAT_TABLE . "
					SET cat_order = $i
					WHERE cat_id = ". $row['cat_id'];
			$db->sql_query($sql);
			
			$i += 10;
		}
	}
}

?>