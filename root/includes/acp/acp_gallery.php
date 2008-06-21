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
		include($phpbb_root_path . GALLERY_ROOT_PATH . 'includes/constants.' . $phpEx);
		include($phpbb_root_path . GALLERY_ROOT_PATH . 'includes/acp_functions.' . $phpEx);

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

				$this->permissions();
			break;
			
			case 'album_personal_permissions':
				$title = 'ALBUM_PERSONAL_GALLERY_TITLE';
				$this->page_title = $user->lang[$title];

				$this->album_personal_permissions();
			break;

			case 'import_images':
				$title = 'ACP_IMPORT_ALBUMS';
				$this->page_title = $user->lang[$title];

				$this->import();
			break;

			case 'new_permissions':
				$title = 'NEW_PERMISSIONS';
				$this->page_title = $user->lang[$title];

				$this->permissions();
			break;

			default:
				$title = 'ACP_GALLERY_OVERVIEW';
				$this->page_title = $user->lang[$title];

				$this->overview();
			break;
		}
	}

	function import()
	{
		global $db, $template, $user, $phpbb_root_path;

		$submit = (isset($_POST['submit'])) ? true : false;
		if(!$submit)
		{
			$template->assign_vars(array(
				'S_IMPORT_IMAGES'				=> true,
				'ACP_GALLERY_TITLE'				=> $user->lang['ACP_IMPORT_ALBUMS'],
				'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ACP_IMPORT_ALBUMS_EXPLAIN'],
				'S_ALBUM_IMPORT_ACTION'			=> $this->u_action,
				'S_SELECT_IMPORT' 			=> make_album_select(0, false, false, false, false),
			));
		}
		else
		{// Is it salty ?
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}

			$sql = 'SELECT *
				FROM ' . GALLERY_CONFIG_TABLE;
			$result = $db->sql_query($sql);
			while( $row = $db->sql_fetchrow($result) )
			{
				$album_config_name = $row['config_name'];
				$album_config_value = $row['config_value'];
				$album_config[$album_config_name] = $album_config_value;
			}

			// There was no directory specified
			if(!$directory = request_var('img_dir', ''))
			{
				trigger_error($user->lang['IMPORT_MISSING_DIR'], E_USER_WARNING);
			}
			// There was no album selected
			if(!$album_id = request_var('target', 0))
			{
				trigger_error($user->lang['IMPORT_MISSING_ALBUM'], E_USER_WARNING);
			}

			$img_per_cycle = request_var('img_per_cycle', 15);

			// Take a look at the directory supplied
			$results = array();
			$handle = opendir($directory);

			while ($file = readdir($handle))
			{
				if (!is_dir("$directory/$file") && $file != '.' && $file != '..' && $file != 'Thumbs.db')
				{
					$results[] = $file;
				}
			}
			closedir($handle);

			// Do the work now
			$image_user_id 	= $user->data['user_id'];
			$image_user_ip 	= $user->ip;
			$image_username	= $user->data['username'];

			$image_count = count($results);
			$counter = 0;
			
			foreach ($results as $image)
			{
				if($counter >= $img_per_cycle)
				{
					break;
				}
				$image_path = $directory . '/' . $image;
				//$imp_debug .= $image_path . '<br />-  ';

				// Determine the file type
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

				// Prep the image to be moved to the store

				// Generate filename
				srand((double)microtime()*1000000);// for older than version 4.2.0 of PHP
				$image_filename = md5(uniqid(rand())) . $image_filetype;
				$image_time 		= time();


				$ini_val = ( @phpversion() >= '4.0.0' ) ? 'ini_get' : 'get_cfg_var';

				if (@$ini_val('open_basedir') <> '')
				{
					if (@phpversion() < '4.0.3')
					{
						trigger_error('open_basedir is set and your PHP version does not allow move_uploaded_file<br /><br />Please contact your server admin', E_USER_WARNING);
					}
					$move_file = 'move_uploaded_file';
				}
				else
				{
					$move_file = 'copy';
				}
				
				$move_file($image_path, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);
				@chmod($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename, 0777);

				if (!$album_config['gd_version'])
				{
					$move_file($thumbtmp, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_thumbnail);
					@chmod(ALBUM_CACHE_PATH . $image_thumbnail, 0777);
				}


				if (($album_config['thumbnail_cache']) && ($album_config['gd_version'] > 0))
				{
					$gd_errored = FALSE;
					switch ($image_filetype)
					{
						case '.jpg':
							$read_function = 'imagecreatefromjpeg';
							break;

						case '.png':
							$read_function = 'imagecreatefrompng';
							break;

						case '.gif':
							$read_function = 'imagecreatefromgif';
							break;
					}
					//cheat the server for uploading bigger files
					#no cheating: ini_set('memory_limit', '128M');
					$src = $read_function($phpbb_root_path . GALLERY_UPLOAD_PATH  . $image_filename);

					if (!$src)
					{
						$gd_errored = TRUE;
						$image_thumbnail = '';
					}
					else if (($image_width > $album_config['thumbnail_size']) || ($image_height > $album_config['thumbnail_size']))
					{
						// Resize it
						if ($image_width > $image_height)
						{
							$thumbnail_width 	= $album_config['thumbnail_size'];
							$thumbnail_height 	= $album_config['thumbnail_size'] * ($image_height/$image_width);
						}
						else
						{
							$thumbnail_height 	= $album_config['thumbnail_size'];
							$thumbnail_width 	= $album_config['thumbnail_size'] * ($image_width/$image_height);
						}

						if ($album_config['thumbnail_info_line'])
						{// Create image details credits to Dr.Death
							$thumbnail = ($album_config['gd_version'] == 1) ? @imagecreate($thumbnail_width, $thumbnail_height + 16) : @imagecreatetruecolor($thumbnail_width, $thumbnail_height + 16); 
						}
						else
						{
							$thumbnail = ($album_config['gd_version'] == 1) ? @imagecreate($thumbnail_width, $thumbnail_height) : @imagecreatetruecolor($thumbnail_width, $thumbnail_height);
						}
						$resize_function = ($album_config['gd_version'] == 1) ? 'imagecopyresized' : 'imagecopyresampled';
						@$resize_function($thumbnail, $src, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $image_width, $image_height);

						if ($album_config['thumbnail_info_line'])
						{// Create image details credits to Dr.Death
							$dimension_font = 1;
							$dimension_filesize = filesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);
							$dimension_string = $image_width . "x" . $image_height . "(" . intval($dimension_filesize/1024) . "KiB)";
							$dimension_colour = ImageColorAllocate($thumbnail,255,255,255);
							$dimension_height = imagefontheight($dimension_font);
							$dimension_width = imagefontwidth($dimension_font) * strlen($dimension_string);
							$dimension_x = ($thumbnail_width - $dimension_width) / 2;
							$dimension_y = $thumbnail_height + ((16 - $dimension_height) / 2);
							imagestring($thumbnail, 1, $dimension_x, $dimension_y, $dimension_string, $dimension_colour);
						}
					}
					else
					{
						$thumbnail = $src;
					}

					if (!$gd_errored)
					{
						$image_thumbnail = $image_filename;

						// Write to disk
						switch ($image_filetype)
						{
							case '.jpg':
								@imagejpeg($thumbnail, $phpbb_root_path . GALLERY_CACHE_PATH . $image_thumbnail, $album_config['thumbnail_quality']);
							break;

							case '.png':
								@imagepng($thumbnail, $phpbb_root_path . GALLERY_CACHE_PATH . $image_thumbnail);
							break;

							case '.gif':
								@imagegif($thumbnail, $phpbb_root_path . GALLERY_CACHE_PATH . $image_thumbnail);
							break;
						}
						@chmod(ALBUM_CACHE_PATH . $image_thumbnail, 0777);
					} // End IF $gd_errored
				} // End Thumbnail Cache
				else if ($album_config['gd_version'] > 0)
				{
					$image_thumbnail = '';
				}

				// The source image is imported and thumbnailed, delete it
				#@unlink($image_path);

				$sql_ary = array(
					'image_filename' 		=> $image_filename,
					'image_thumbnail'		=> $image_thumbnail,
					'image_name'			=> $image,
					'image_desc'			=> $user->lang['NO_DESC'],
					'image_desc_uid'		=> '',
					'image_desc_bitfield'	=> '',
					'image_user_id'			=> $image_user_id,
					'image_username'		=> $image_username,
					'image_user_ip'			=> $image_user_ip,
					'image_time'			=> $image_time,
					'image_album_id'		=> $album_id,
					'image_approval'		=> 1,
				);

				$db->sql_query('INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
				$counter++;
			}
			$left = $image_count - $counter;
			$template->assign_vars(array(
				'ACP_GALLERY_TITLE'				=> $user->lang['IMPORT_DEBUG'],
				'ACP_GALLERY_TITLE_EXPLAIN'		=> sprintf($user->lang['IMPORT_DEBUG_MES'], $counter, $left),
			));
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

			'MAX_IMAGES' 						=> $new['max_pics'],
			'UPLOAD_IMAGES' 					=> $new['upload_images'],
			'MAX_FILE_SIZE' 					=> $new['max_file_size'],
			'MAX_WIDTH' 						=> $new['max_width'],
			'MAX_HEIGHT' 						=> $new['max_height'],
			'RSZ_WIDTH' 						=> $new['preview_rsz_width'],
			'RSZ_HEIGHT' 						=> $new['preview_rsz_height'],
			'ROWS_PER_PAGE' 					=> $new['rows_per_page'],
			'COLS_PER_PAGE' 					=> $new['cols_per_page'],
			'WATERMARK_SOURCE' 					=> $new['watermark_source'],
			'THUMBNAIL_QUALITY' 				=> $new['thumbnail_quality'],
			'THUMBNAIL_SIZE' 					=> $new['thumbnail_size'],
			'PERSONAL_GALLERY_LIMIT' 			=> $new['personal_gallery_limit'],

			'FAKE_THUMB_SIZE' 					=> $new['fake_thumb_size'],
			'DISP_FAKE_THUMB' 					=> $new['disp_fake_thumb'],

			'THUMBNAIL_CACHE_ENABLED'			=> ($new['thumbnail_cache'] == 1) ? 'checked="checked"' : '',
			'THUMBNAIL_CACHE_DISABLED'			=> ($new['thumbnail_cache'] == 0) ? 'checked="checked"' : '',

			'INFO_LINE_ENABLED'					=> ($new['thumbnail_info_line'] == 1) ? 'checked="checked"' : '',
			'INFO_LINE_DISABLED'				=> ($new['thumbnail_info_line'] == 0) ? 'checked="checked"' : '',

			'JPG_ENABLED' 						=> ($new['jpg_allowed'] == 1) ? 'checked="checked"' : '',
			'JPG_DISABLED' 						=> ($new['jpg_allowed'] == 0) ? 'checked="checked"' : '',
			'PNG_ENABLED' 						=> ($new['png_allowed'] == 1) ? 'checked="checked"' : '',
			'PNG_DISABLED' 						=> ($new['png_allowed'] == 0) ? 'checked="checked"' : '',
			'GIF_ENABLED' 						=> ($new['gif_allowed'] == 1) ? 'checked="checked"' : '',
			'GIF_DISABLED' 						=> ($new['gif_allowed'] == 0) ? 'checked="checked"' : '',

			'IMAGE_DESC_MAX_LENGTH' 				=> $new['desc_length'],

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

			'SORT_TIME' 						=> ($new['sort_method'] == 'image_time') ? 'selected="selected"' : '',
			'SORT_IMAGE_TITLE' 					=> ($new['sort_method'] == 'image_name') ? 'selected="selected"' : '',
			'SORT_USERNAME' 					=> ($new['sort_method'] == 'username') ? 'selected="selected"' : '',
			'SORT_VIEW' 						=> ($new['sort_method'] == 'image_view_count') ? 'selected="selected"' : '',
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
	{
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
					'album_view_groups'			=> (isset($view_groups)) ? $view_groups : 0,
					'album_upload_groups'		=> (isset($upload_groups)) ? $upload_groups : 0,
					'album_rate_groups'			=> (isset($rate_groups)) ? $rate_groups : 0,
					'album_comment_groups'		=> (isset($comment_groups)) ? $comment_groups : 0,
					'album_edit_groups'			=> (isset($edit_groups)) ? $edit_groups : 0,
					'album_delete_groups'		=> (isset($delete_groups)) ? $delete_groups : 0,
					'album_moderator_groups'	=> (isset($moderator_groups)) ? $moderator_groups : 0,
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
	{
		global $db, $template, $user;

		if( !isset($_POST['submit']) )
		{
			// Get the list of phpBB usergroups
			$sql = 'SELECT group_id, group_name, group_type, allow_personal_albums, view_personal_albums, personal_subalbums
					FROM ' . GROUPS_TABLE . '
					ORDER BY group_name ASC';
			$result = $db->sql_query($sql);
			while ($groupdata = $db->sql_fetchrow($result))
			{
				$template->assign_block_vars('creation_grouprow', array(
					'GROUP_ID'		=> $groupdata['group_id'],
					'GROUP_NAME'	=> ($groupdata['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $groupdata['group_name']] : $groupdata['group_name'],
					'ALLOWED'		=> $groupdata['allow_personal_albums'],
					'VIEW'			=> $groupdata['view_personal_albums'],
					'SUBALBUMS'		=> $groupdata['personal_subalbums'],
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
			$allow = request_var('allow', array(0));
			$view = request_var('view', array(0));
			$subalbums = request_var('subalbums', array(0));
			$group_id = request_var('group_id', array(0));

			for($i = 0; $i < count($group_id); $i++)
			{
				$sql_ary = array(
					'allow_personal_albums'		=> $allow[$i],
					'view_personal_albums'		=> $view[$i],
					'personal_subalbums'		=> $subalbums[$i],
				);
				$sql = 'UPDATE ' . GROUPS_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
					WHERE group_id = '{$group_id[$i]}'";
				$db->sql_query($sql);
			}

			trigger_error($user->lang['ALBUM_AUTH_SUCCESSFULLY'] . adm_back_link($this->u_action));
		}
	}
	
	function manage_albums()
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;

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
				AND album_user_id = 0
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
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;
		include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		$submit = (isset($_POST['submit'])) ? true : false;
		if(!$submit)
		{
			$parents_list = make_album_select(0, false, false, false, false);
			$copy_list = make_album_select(0);
			$template->assign_vars(array(
				'S_CREATE_ALBUM'				=> true,
				'ACP_GALLERY_TITLE'				=> $user->lang['GALLERY_ALBUMS_TITLE'],
				'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ACP_CREATE_ALBUM_EXPLAIN'],
				'S_PARENT_OPTIONS'				=> $parents_list,
				'S_COPY_OPTIONS'			=> $copy_list,
				'S_ALBUM_ACTION'				=> $this->u_action . '&amp;action=create',
				'S_DESC_BBCODE_CHECKED'		=> true,
				'S_DESC_SMILIES_CHECKED'	=> true,
				'S_DESC_URLS_CHECKED'		=> true,
				'ALBUM_TYPE'				=> 2,
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
				'album_user_id'					=> 0,
				'album_view_level'				=> request_var('album_view_level', 0),
				'album_upload_level'			=> request_var('album_upload_level', 0),
				'album_rate_level'				=> request_var('album_rate_level', 0),
				'album_comment_level'			=> request_var('album_comment_level', 0),
				'album_edit_level'				=> request_var('album_edit_level', 0),
				'album_delete_level'			=> request_var('album_delete_level', 0),
				'album_approval'				=> request_var('album_approval', 0),
				'album_last_username'			=> '',
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
				$sql = 'SELECT MAX(right_id) AS right_id
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE album_user_id = ' . $album_data['album_user_id'];
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$album_data['left_id'] = $row['right_id'] + 1;
				$album_data['right_id'] = $row['right_id'] + 2;
			}
			$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
			$album_data['album_id'] = $db->sql_nextid();
			$album_id = $album_data['album_id'];
			$copy_permissions = request_var('copy_permissions', 0);
			if ($copy_permissions <> 0)
			{
				//delete the old permissions and thatn copy the new one's
				$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . "
					WHERE perm_album_id = $album_id";
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
			}
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
			$copy_list = make_album_select(0, $album_id);

			$template->assign_vars(array(
				'S_EDIT_ALBUM'				=> true,
				'ACP_GALLERY_TITLE'			=> $user->lang['GALLERY_ALBUMS_TITLE'],
				'ACP_GALLERY_TITLE_EXPLAIN'	=> $user->lang['ACP_EDIT_ALBUM_EXPLAIN'],

				'S_ALBUM_ACTION' 			=> $this->u_action . '&amp;action=edit&amp;album_id=' . $album_id,
				'S_PARENT_OPTIONS'			=> $parents_list,
				'S_COPY_OPTIONS'			=> $copy_list,

				'ALBUM_NAME' 				=> $album_data['album_name'],
				'ALBUM_DESC'				=> $album_desc_data['text'],
				'ALBUM_TYPE'				=> $album_data['album_type'],
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
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE album_user_id = ' . $row['album_user_id'];
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
					WHERE album_user_id = ' . $row['album_user_id'] . ' AND
						left_id >= ' . $row['left_id'] . '
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
						WHERE album_user_id = ' . $row['album_user_id'] . ' AND
							left_id >= ' . $row['left_id'];
					$db->sql_query($sql);
					//right_id
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET right_id = right_id - ' . $moving_ids . '
						WHERE album_user_id = ' . $row['album_user_id'] . ' AND
							right_id >= ' . $row['left_id'];
					$db->sql_query($sql);
				}
				else
				{
					//close the gap
					//left_id
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET left_id = left_id - ' . $moving_ids . '
						WHERE album_user_id = ' . $row['album_user_id'] . ' AND
							left_id >= ' . $row['left_id'] . '
							AND right_id <= ' . $stop_updating;
					$db->sql_query($sql);
					//right_id
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET right_id = right_id - ' . $moving_ids . '
						WHERE album_user_id = ' . $row['album_user_id'] . ' AND
							right_id >= ' . $row['left_id'] . '
							AND right_id <= ' . $stop_updating;
					$db->sql_query($sql);

					//create new gap
					//need parent_information
					$parent = get_album_info($album_data['parent_id']);
					//left_id
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET left_id = left_id + ' . $moving_ids . '
						WHERE album_user_id = ' . $row['album_user_id'] . ' AND
							left_id >= ' . $parent['right_id'] . '
							AND right_id <= ' . $stop_updating;
					$db->sql_query($sql);
					//right_id
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET right_id = right_id + ' . $moving_ids . '
						WHERE album_user_id = ' . $row['album_user_id'] . ' AND
							right_id >= ' . $parent['right_id'] . '
							AND right_id <= ' . $stop_updating;
					$db->sql_query($sql);

					//close the gap again
					//new parent right_id!!!
					$parent['right_id'] = $parent['right_id'] + $moving_ids;
					$move_back = ($new['right_id'] - $parent['right_id']) + 1;
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
						SET left_id = left_id - ' . $move_back . ',
							right_id = right_id - ' . $move_back . '
						WHERE album_user_id = ' . $row['album_user_id'] . ' AND
							left_id >= ' . $stop_updating;
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
			$copy_permissions = request_var('copy_permissions', 0);
			if ($copy_permissions <> 0)
			{
				//delete the old permissions and thatn copy the new one's
				$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . "
					WHERE perm_album_id = $album_id";
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
			}
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
		{
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
			$db->sql_freeresult($result);

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
					$sql = 'SELECT image_id, image_filename, image_thumbnail, image_album_id
							FROM ' . GALLERY_IMAGES_TABLE . "
							WHERE image_album_id = '$album_id'";
					$result = $db->sql_query($sql);
					
					$picrow = array();
					while ($row = $db ->sql_fetchrow($result))
					{
						$picrow[] = $row;
						$pic_id_row[] = $row['image_id'];
					}
					if(count($picrow) > 0)
					{
						// Delete all physical pic & cached thumbnail files
						for ($i = 0; $i < count($picrow); $i++)
						{
							@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $picrow[$i]['image_thumbnail']);
							@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $picrow[$i]['image_filename']);
						}

						$pic_id_sql = '(' . implode(',', $pic_id_row) . ')';
						// Delete all related ratings
						$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . '
							WHERE rate_image_id IN ' . $pic_id_sql;
						$result = $db->sql_query($sql);
						// Delete all related comments
						$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . '
							WHERE comment_image_id IN ' . $pic_id_sql;
						$result = $db->sql_query($sql);
						// Delete pic entries in db
						$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . "
							WHERE image_album_id = '$album_id'";
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
			//reorder the other albums
			//left_id
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
				SET left_id = left_id - 2
				WHERE album_user_id = {$album['album_user_id']} AND
				left_id > " . $album['left_id'];
			$db->sql_query($sql);
			//right_id
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
				SET right_id = right_id - 2
				WHERE album_user_id = {$album['album_user_id']} AND
				right_id > " . $album['left_id'];
			$db->sql_query($sql);
			$sql = 'DELETE FROM ' . GALLERY_ALBUMS_TABLE . "
				WHERE album_id = '$album_id'";
			$result = $db->sql_query($sql);
			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
			$cache->destroy('_albums');
			trigger_error($user->lang['ALBUM_DELETED'] . adm_back_link($this->u_action));
		}
	}

	function move_album()
	{
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
				AND album_user_id = {$moving['album_user_id']}
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
				AND right_id BETWEEN {$left_id} AND {$right_id}
				AND album_user_id = {$moving['album_user_id']}";
		$db->sql_query($sql);
		$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
		$cache->destroy('_albums');
		trigger_error($user->lang['ALBUM_CHANGED_ORDER'] . adm_back_link($this->u_action));
	}

	function manage_cache()
	{
		global $db, $template, $user, $phpbb_root_path, $cache;
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
			$cache_dir = @opendir($phpbb_root_path . GALLERY_CACHE_PATH);

			while( $cache_file = @readdir($cache_dir) )
			{
				if( preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $cache_file) )
				{
					@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $cache_file);
				}
			}

			@closedir($cache_dir);
			$cache->destroy('_albums');
			trigger_error($user->lang['THUMBNAIL_CACHE_CLEARED_SUCCESSFULLY'] . adm_back_link($this->u_action));
		}
	}

	function permissions()
	{
		global $db, $template, $user, $cache;

		$sql = 'SELECT *
			FROM ' . GALLERY_CONFIG_TABLE;
		$result = $db->sql_query($sql);
		while( $row = $db->sql_fetchrow($result) )
		{
			$album_config_name = $row['config_name'];
			$album_config_value = $row['config_value'];
			$album_config[$album_config_name] = $album_config_value;
		}

		$this->tpl_name = 'acp_gallery_permissions';

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
			//you wished to drop the permissions
			$drop_perm_ary = request_var('drop_perm', array(''));
			$drop_perm_string = implode(', ', $drop_perm_ary);
			if ($drop_perm_string && $album_list)
			{
				$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . "
					WHERE " . $db->sql_in_set('perm_group_id', $drop_perm_ary) . "
						AND " . $db->sql_in_set('perm_album_id', $album_ary) . "
						AND perm_system = $perm_system";
				$db->sql_query($sql);
			}
			else if ($drop_perm_string)
			{
				$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . "
					WHERE " . $db->sql_in_set('perm_group_id', $drop_perm_ary) . "
						AND perm_system = $perm_system";
				$db->sql_query($sql);
			}
			$step = 1;
		}

		$album_name_ary = array();
		//build the array with some kind of order.
		$permissions = array();
		if ($perm_system != 2)
		{
			$permissions = array_merge($permissions, array('i_view'));
		}
		if ($perm_system != 3)
		{
			$permissions = array_merge($permissions, array('i_upload', 'i_approve'));
		}
		$permissions = array_merge($permissions, array('i_edit', 'i_delete', 'i_lock', 'i_report'));
		//im not sure, whether whe should add this everytime, so you can make the rights without the users having them already
		//if ($album_config['rate'])
		//{
			$permissions = array_merge($permissions, array('i_rate'));
		//}
		//if ($album_config['comment'])
		//{
			$permissions = array_merge($permissions, array('c_post', 'c_edit', 'c_delete'));
		//}
		$permissions = array_merge($permissions, array('a_moderate'));
		if ($perm_system != 3)
		{
			$permissions = array_merge($permissions, array('i_count'));
		}
		if ($perm_system == 2)
		{
			$permissions = array_merge($permissions, array('album_count'));
		}

		$albums = $cache->obtain_album_list();

		if ($step == 0)
		{
			foreach ($albums as $album)
			{
				if ($album['album_user_id'] == 0)
				{
					$template->assign_block_vars('albumrow', array(
						'ALBUM_ID'				=> $album['album_id'],
						'ALBUM_NAME'			=> $album['album_name'],
					));
				}
			}
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

			$sql = 'SELECT group_id, group_type, group_name, group_colour FROM ' . GROUPS_TABLE;
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
						trigger_error('THIS_WILL_BE_REPORTED', E_USER_WARNING);
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
			//ALbum names
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
			$sql = 'SELECT group_id, group_type, group_name, group_colour FROM ' . GROUPS_TABLE . '
				WHERE ' . $db->sql_in_set('group_id', $group_list);
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
				$sql = "SELECT pr.*
					FROM " . GALLERY_PERMISSIONS_TABLE . " as p
					LEFT JOIN " .  GALLERY_PERM_ROLES_TABLE .  " as pr
						ON p.perm_role_id = pr.role_id
					WHERE p.perm_group_id = {$group_ary[0]}
						AND $where";
				$result = $db->sql_query($sql);
				$perm_ary = $db->sql_fetchrow($result, 1);
				$db->sql_freeresult($result);
			}

			//Permissions
			foreach ($permissions as $permission)
			{
				$template->assign_block_vars('permission', array(
					'PERMISSION'			=> $user->lang['PERMISSION_' . strtoupper($permission)],
					'S_FIELD_NAME'			=> $permission,
					'S_NO'					=> ((isset($perm_ary[$permission]) && ($perm_ary[$permission] == 0)) ? true : false),
					'S_YES'					=> ((isset($perm_ary[$permission]) && ($perm_ary[$permission] == 1)) ? true : false),
					'S_NEVER'				=> ((isset($perm_ary[$permission]) && ($perm_ary[$permission] == 2)) ? true : false),
					'S_VALUE'				=> ((isset($perm_ary[$permission])) ? $perm_ary[$permission] : 0),
					'S_COUNT_FIELD'			=> (substr($permission, -6, 6) == '_count') ? true : false,
				));
			}
			$step = 3;
		}
		else if ($step == 3)
		{
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			foreach ($permissions as $permission)
			{
				$submitted_valued = request_var($permission, 0);//hacked for deny empty submit
				if (substr($permission, -6, 6) == '_count')
				{
					$submitted_valued = $submitted_valued + 1;
				}
				else if ($submitted_valued == 0)
				{
					trigger_error('PERMISSION_EMPTY', E_USER_WARNING);
				}
				$sql_ary[$permission] = $submitted_valued - 1;
			}
			//need to set some defaults here
			if ($perm_system == 2)
			{//view your own personal albums
				$sql_ary['i_view'] = 1;
			}
			$set_moderator = false;
			if ($sql_ary['a_moderate'] == 1)
			{
				$set_moderator = true;
			}

			$db->sql_query('INSERT INTO ' . GALLERY_PERM_ROLES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
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

}

?>