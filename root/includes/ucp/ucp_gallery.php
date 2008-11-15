<?php

/**
*
* @package phpBB3
* @version $Id: acp_gallery.php 256 2008-01-25 18:52:19Z nickvergessen $
* @copyright (c) 2007 phpBB Gallery
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

class ucp_gallery
{
	var $u_action;
	function main($id, $mode)
	{
		global $user, $phpbb_root_path, $phpEx, $db, $template;
		$gallery_root_path = GALLERY_ROOT_PATH;
		include($phpbb_root_path . $gallery_root_path . 'includes/functions.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/constants.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/ucp_functions.' . $phpEx);

		$user->add_lang('mods/gallery');
		$user->add_lang('mods/gallery_acp');
		$user->add_lang('mods/gallery_mcp');
		$user->add_lang('mods/gallery_ucp');
		$this->tpl_name = 'ucp_gallery';
		add_form_key('ucp_gallery');

		$mode = request_var('mode', '');
		$action = request_var('action', '');
		$cancel = (isset($_POST['cancel'])) ? true : false;
		if ($cancel)
		{
			$action = '';
		}
		switch ($mode)
		{
			case 'manage_albums':
				switch ($action)
				{
					case 'manage':
						$title = 'MANAGE_SUBALBUMS';
						$this->page_title = $user->lang[$title];
						$this->manage_albums();
					break;

					case 'create':
						$title = 'CREATE_SUBALBUM';
						$this->page_title = $user->lang[$title];
						$this->create_album();
					break;

					case 'edit':
						$title = 'EDIT_SUBALBUM';
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

					case 'initialise':
						$this->initialise_album();
					break;

					default:
						$title = 'UCP_GALLERY_PERSONAL_ALBUMS';
						$this->page_title = $user->lang[$title];
						if (!$user->gallery['personal_album_id'])
						{
							$this->info();
						}
						else
						{
							$this->manage_albums();
						}
					break;
				}
			break;

			case 'manage_favorites':
				$title = 'UCP_GALLERY_FAVORITES';
				$this->page_title = $user->lang[$title];
				$this->manage_favorites();
			break;

			case 'manage_subscriptions':
				$title = 'UCP_GALLERY_WATCH';
				$this->page_title = $user->lang[$title];
				$this->manage_subscriptions();
			break;

			case 'manage_settings':
			default:
				$title = 'UCP_GALLERY_SETTINGS';
				$this->page_title = $user->lang[$title];
				$this->set_personal_settings();
			break;
		}
	}

	function set_personal_settings()
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;
		$gallery_root_path = GALLERY_ROOT_PATH;

		$submit = (isset($_POST['submit'])) ? true : false;

		if($submit)
		{
			$sql = 'SELECT user_id
				FROM ' . GALLERY_USERS_TABLE . "
				WHERE user_id = {$user->data['user_id']}";
			$result = $db->sql_query($sql);
			$check = $db->sql_fetchrow($result);
			$gallery_settings = array(
				'user_id'		=> $user->data['user_id'],
				'watch_own'		=> request_var('watch_own', 0),
				'watch_com'		=> request_var('watch_com', 0),
				'watch_favo'	=> request_var('watch_favo', 0),
			);
			if ($check['user_id'] == $user->data['user_id'])
			{
				$sql = 'UPDATE ' . GALLERY_USERS_TABLE . ' 
						SET ' . $db->sql_build_array('UPDATE', $gallery_settings) . '
						WHERE user_id  = ' . (int) $user->data['user_id'];
				$db->sql_query($sql);
			}
			else
			{
				$db->sql_query('INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $gallery_settings));
			}
			meta_refresh(3, $this->u_action);
			$message = $user->lang['WATCH_CHANGED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
			trigger_error($message);
		}


		$template->assign_vars(array(
			'S_PERSONAL_SETTINGS'	=> true,
			'S_UCP_ACTION'			=> $this->u_action,

			'L_TITLE'			=> $user->lang['UCP_GALLERY_SETTINGS'],
			'L_TITLE_EXPLAIN'	=> $user->lang['WATCH_NOTE'],

			'S_WATCH_OWN'		=> $user->gallery['watch_own'],
			'S_WATCH_COM'		=> $user->gallery['watch_com'],
			'S_WATCH_FAVO'		=> $user->gallery['watch_favo'],
		));
	}

	function manage_favorites()
	{
		global $db, $user, $auth, $template, $cache, $album_config;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;
		$gallery_root_path = GALLERY_ROOT_PATH;
		include_once($phpbb_root_path . $gallery_root_path . 'includes/functions.' . $phpEx);

		$action = request_var('action', '');
		$image_id_ary = request_var('image_id_ary', array(0));
		if ($image_id_ary && ($action == 'remove_favorite'))
		{
			$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . ' WHERE user_id = ' . $user->data['user_id'] . ' AND ' . $db->sql_in_set('image_id', $image_id_ary);
			$db->sql_query($sql);
			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET image_favorited = image_favorited - 1 WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
			$db->sql_query($sql);

			meta_refresh(3, $this->u_action);
			$message = $user->lang['UNFAVORITED_IMAGES'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
			trigger_error($message);
		}

		$start				= request_var('start', 0);
		$images_per_page	= $config['topics_per_page'];
		$total_images		= 0;
		$sql = 'SELECT count(image_id) as images
			FROM ' . GALLERY_FAVORITES_TABLE . "
			WHERE user_id = {$user->data['user_id']}";
		$result = $db->sql_query($sql);
		while( $row = $db->sql_fetchrow($result) )
		{
			$total_images = $row['images'];
		}
		$db->sql_freeresult($result);
		$sql = 'SELECT i.image_time, i.image_name, i.image_id, i.image_user_id, i.image_username, i.image_user_colour, i.image_album_id, a.album_name
			FROM ' . GALLERY_FAVORITES_TABLE . " f
			LEFT JOIN " . GALLERY_IMAGES_TABLE . " i
				ON f.image_id = i.image_id
			LEFT JOIN " . GALLERY_ALBUMS_TABLE . " a
				ON a.album_id = i.image_album_id
			WHERE f.user_id = {$user->data['user_id']}
			LIMIT $start, $images_per_page";
		$result = $db->sql_query($sql);
		while( $row = $db->sql_fetchrow($result) )
		{
			$template->assign_block_vars('image_row', array(
				'THUMBNAIL'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}thumbnail.$phpEx" , 'album_id=' . $row['image_album_id'] .  '&amp;image_id=' . $row['image_id']),
				'UPLOADER'			=> get_username_string('full', $row['image_user_id'], $row['image_username'], $row['image_user_colour']),
				'IMAGE_TIME'		=> $user->format_date($row['image_time']),
				'IMAGE_NAME'		=> $row['image_name'],
				'ALBUM_NAME'		=> $row['album_name'],
				'IMAGE_ID'			=> $row['image_id'],
				'U_VIEW_ALBUM'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx" , "album_id=" . $row['image_album_id']),
				'U_IMAGE'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx" , "album_id=" . $row['image_album_id'] . "&amp;image_id=" . $row['image_id']),
			));
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'S_MANAGE_FAVORITES'	=> true,
			'S_UCP_ACTION'			=> $this->u_action,

			'L_TITLE'				=> $user->lang['UCP_GALLERY_FAVORITES'],
			'L_TITLE_EXPLAIN'		=> $user->lang['YOUR_FAVORITE_IMAGES'],

			'PAGINATION'				=> generate_pagination(append_sid("{$phpbb_root_path}ucp.$phpEx", "i=gallery&amp;mode=manage_favorites"), $total_images, $images_per_page, $start),
			'PAGE_NUMBER'				=> on_page($total_images, $images_per_page, $start),
			'TOTAL_IMAGES'				=> ($total_images == 1) ? $user->lang['VIEW_ALBUM_IMAGE'] : sprintf($user->lang['VIEW_ALBUM_IMAGES'], $total_images),

			'DISP_FAKE_THUMB'			=> true,
			'FAKE_THUMB_SIZE'			=> (empty($album_config['fake_thumb_size'])) ? 50 : $album_config['fake_thumb_size'],
		));
	}

	function manage_subscriptions()
	{
		global $db, $user, $auth, $template, $cache, $album_config;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;
		$gallery_root_path = GALLERY_ROOT_PATH;
		include_once($phpbb_root_path . $gallery_root_path . 'includes/functions.' . $phpEx);

		$action = request_var('action', '');
		$image_id_ary = request_var('image_id_ary', array(0));
		$album_id_ary = request_var('album_id_ary', array(0));
		if (($image_id_ary || $album_id_ary) && ($action == 'unsubscribe'))
		{
			if ($album_id_ary)
			{
				$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . ' WHERE user_id = ' . $user->data['user_id'] . ' AND ' . $db->sql_in_set('album_id', $album_id_ary);
				$db->sql_query($sql);
			}
			if ($image_id_ary)
			{
				$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . ' WHERE user_id = ' . $user->data['user_id'] . ' AND ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);
			}

			meta_refresh(3, $this->u_action);
			$message = '';
			if ($album_id_ary)
			{
				$message .= $user->lang['UNWATCHED_ALBUMS'] . '<br />';
			}
			if ($image_id_ary)
			{
				$message .= $user->lang['UNWATCHED_IMAGES'] . '<br />';
			}
			$message .= '<br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
			trigger_error($message);
		}

		//subscribed albums
		$sql = 'SELECT *
			FROM ' . GALLERY_WATCH_TABLE . " w
			LEFT JOIN " . GALLERY_ALBUMS_TABLE . " a
				ON w.album_id = a.album_id
			WHERE w.user_id = {$user->data['user_id']}
				AND w.album_id <> 0";
		$result = $db->sql_query($sql);
		while( $row = $db->sql_fetchrow($result) )
		{
			$template->assign_block_vars('album_row', array(
				'ALBUM_ID'			=> $row['album_id'],
				'ALBUM_NAME'		=> $row['album_name'],
				'U_VIEW_ALBUM'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx" , "album_id=" . $row['album_id']),
				'ALBUM_DESC'		=> generate_text_for_display($row['album_desc'], $row['album_desc_uid'], $row['album_desc_bitfield'], $row['album_desc_options']),

				'THUMBNAIL'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}thumbnail.$phpEx" , 'album_id=' . $row['album_id'] .  '&amp;image_id=' . $row['album_last_image_id']),
				'UPLOADER'			=> get_username_string('full', $row['album_last_user_id'], $row['album_last_username'], $row['album_last_user_colour']),
				'IMAGE_TIME'		=> $user->format_date($row['album_last_image_time']),
				'IMAGE_NAME'		=> $row['album_last_image_name'],
				'LAST_IMAGE'		=> $row['album_last_image_id'],
				'U_IMAGE'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx" , "album_id=" . $row['album_id'] . "&amp;image_id=" . $row['album_last_image_id']),
			));
		}
		$db->sql_freeresult($result);

		//subscribed images
		$start				= request_var('start', 0);
		$images_per_page	= $config['topics_per_page'];
		$total_images		= 0;
		$sql = 'SELECT count(image_id) as images
			FROM ' . GALLERY_WATCH_TABLE . "
			WHERE user_id = {$user->data['user_id']}
				AND image_id <> 0";
		$result = $db->sql_query($sql);
		while( $row = $db->sql_fetchrow($result) )
		{
			$total_images = $row['images'];
		}
		$db->sql_freeresult($result);
		$sql = 'SELECT *
			FROM ' . GALLERY_WATCH_TABLE . " w
			LEFT JOIN " . GALLERY_IMAGES_TABLE . " i
				ON w.image_id = i.image_id
			LEFT JOIN " . GALLERY_ALBUMS_TABLE . " a
				ON a.album_id = i.image_album_id
			LEFT JOIN " . GALLERY_COMMENTS_TABLE . " c
				ON i.image_last_comment = c.comment_id
			WHERE w.user_id = {$user->data['user_id']}
				AND w.image_id <> 0
			LIMIT $start, $images_per_page";
		$result = $db->sql_query($sql);
		while( $row = $db->sql_fetchrow($result) )
		{
			$template->assign_block_vars('image_row', array(
				'THUMBNAIL'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}thumbnail.$phpEx" , 'album_id=' . $row['image_album_id'] .  '&amp;image_id=' . $row['image_id']),
				'UPLOADER'			=> get_username_string('full', $row['image_user_id'], $row['image_username'], $row['image_user_colour']),
				'LAST_COMMENT_BY'	=> get_username_string('full', $row['comment_user_id'], $row['comment_username'], $row['comment_user_colour']),
				'COMMENT'			=> $row['image_comments'],
				'LAST_COMMENT_TIME'	=> $user->format_date($row['comment_time']),
				'IMAGE_TIME'		=> $user->format_date($row['image_time']),
				'IMAGE_NAME'		=> $row['image_name'],
				'ALBUM_NAME'		=> $row['album_name'],
				'IMAGE_ID'			=> $row['image_id'],
				'U_VIEW_ALBUM'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx" , "album_id=" . $row['image_album_id']),
				'U_IMAGE'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx" , "album_id=" . $row['image_album_id'] . "&amp;image_id=" . $row['image_id']),
			));
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'S_MANAGE_SUBSCRIPTIONS'	=> true,
			'S_UCP_ACTION'				=> $this->u_action,

			'L_TITLE'					=> $user->lang['UCP_GALLERY_FAVORITES'],
			'L_TITLE_EXPLAIN'			=> $user->lang['YOUR_SUBSCRIPTIONS'],

			'PAGINATION'				=> generate_pagination(append_sid("{$phpbb_root_path}ucp.$phpEx", "i=gallery&amp;mode=manage_subscriptions"), $total_images, $images_per_page, $start),
			'PAGE_NUMBER'				=> on_page($total_images, $images_per_page, $start),
			'TOTAL_IMAGES'				=> ($total_images == 1) ? $user->lang['VIEW_ALBUM_IMAGE'] : sprintf($user->lang['VIEW_ALBUM_IMAGES'], $total_images),

			'DISP_FAKE_THUMB'			=> true,
			'FAKE_THUMB_SIZE'			=> (empty($album_config['fake_thumb_size'])) ? 50 : $album_config['fake_thumb_size'],
		));
	}

	function info()
	{
		global $user, $template, $phpbb_root_path, $gallery_root_path, $phpEx;

		if (!$user->gallery['personal_album_id'])
		{
			//user will probally go to initialise_album()
			$template->assign_vars(array(
				'S_INFO_CREATE'				=> true,
				'S_UCP_ACTION'		=> $this->u_action . '&amp;action=initialise',

				'L_TITLE'			=> $user->lang['UCP_GALLERY_PERSONAL_ALBUMS'],
				'L_TITLE_EXPLAIN'	=> $user->lang['NO_PERSONAL_ALBUM'],
			));
		}
		else
		{
			redirect("{$phpbb_root_path}ucp.$phpEx", 'i=gallery&amp;mode=manage_albums');
		}
	}

	function initialise_album()
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$gallery_root_path = GALLERY_ROOT_PATH;

		if (!$user->gallery['personal_album_id'])
		{
			//check if the user has already reached his limit
			include_once("{$phpbb_root_path}{$gallery_root_path}includes/permissions.$phpEx");
			$album_access_array = get_album_access_array();
			if (!gallery_acl_check('i_upload', OWN_GALLERY_PERMISSIONS))
			{
				trigger_error('NO_PERSALBUM_ALLOWED');
			}
			$album_data = array(
				'album_name'					=> $user->data['username'],
				'parent_id'						=> request_var('parent_id', 0),
				//left_id and right_id are created some lines later
				'album_desc_options'			=> 7,
				'album_desc'					=> utf8_normalize_nfc(request_var('album_desc', '', true)),
				'album_parents'					=> '',
				'album_type'					=> 1,
				'album_user_id'					=> $user->data['user_id'],
				'album_last_username'			=> '',
				'album_last_user_colour'		=> $user->data['user_colour'],
			);
			$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));

			$sql = 'SELECT album_id FROM ' . GALLERY_ALBUMS_TABLE . ' WHERE parent_id = 0 AND album_user_id = ' . $user->data['user_id'] . ' LIMIT 1';
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);

			if (!empty($user->gallery))
			{
				$sql = 'UPDATE ' . GALLERY_USERS_TABLE . ' 
					SET personal_album_id = ' . (int) $row['album_id'] . '
					WHERE user_id  = ' . (int) $user->data['user_id'];
				$db->sql_query($sql);
			}
			else
			{
				$gallery_settings = array(
					'user_id'			=> $user->data['user_id'],
					'personal_album_id'	=> $row['album_id'],
				);
				$db->sql_query('INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $gallery_settings));
			}

			$sql = 'UPDATE ' . GALLERY_CONFIG_TABLE . " 
					SET config_value = config_value + 1
					WHERE config_name  = 'personal_counter'";
			$db->sql_query($sql);
			$cache->destroy('_albums');
			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
		}
		redirect($this->u_action);
	}

	function manage_albums()
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;
		$gallery_root_path = GALLERY_ROOT_PATH;

		$parent_id = request_var('parent_id', $user->gallery['personal_album_id']);
		album_hacking($parent_id);

		$template->assign_vars(array(
			'S_MANAGE_SUBALBUMS'			=> true,
			'U_CREATE_SUBALBUM'				=> $this->u_action . '&amp;action=create' . (($parent_id) ? '&amp;parent_id=' . $parent_id : ''),

			'L_TITLE'			=> $user->lang['MANAGE_SUBALBUMS'],
			#'ACP_GALLERY_TITLE_EXPLAIN'	=> $user->lang['ALBUM'],
		));

		if (!$parent_id)
		{
			$navigation = $user->lang['PERSONAL_ALBUM'];
		}
		else
		{
			$navigation = $user->lang['PERSONAL_ALBUM'];

			$albums_nav = get_album_branch($user->data['user_id'], $parent_id, 'parents', 'descending');
			foreach ($albums_nav as $row)
			{
				if ($row['album_id'] == $parent_id)
				{
					$navigation .= ' -&gt; ' . $row['album_name'] . '</a>';
				}
				else
				{
					$navigation .= ' -&gt; <a href="' . $this->u_action . '&amp;action=manage&amp;parent_id=' . $row['album_id'] . '">' . $row['album_name'] . '</a>';
				}
			}
		}
		$album = array();
		$sql = 'SELECT *
			FROM ' . GALLERY_ALBUMS_TABLE . '
			WHERE parent_id = ' . $parent_id . '
				AND album_user_id = ' . $user->data['user_id'] . '
			ORDER BY left_id ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$album[] = $row;
		}

		for( $i = 0; $i < count($album); $i++ )
		{
			$folder_img = ($album[$i]['left_id'] + 1 != $album[$i]['right_id']) ? 'forum_read_subforum' : 'forum_read';
			$template->assign_block_vars('catrow', array(
				'FOLDER_IMAGE'			=> $user->img($folder_img, $album[$i]['album_name'], false, '', 'src'),
				'U_ALBUM'				=> $this->u_action . '&amp;action=manage&amp;parent_id=' . $album[$i]['album_id'],
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
			'U_GOTO'			=> append_sid($phpbb_root_path . $gallery_root_path . "album.$phpEx", 'album_id=' . $parent_id),
			'U_EDIT'			=> $this->u_action . '&amp;action=edit&amp;album_id=' . $parent_id,
			'U_DELETE'			=> $this->u_action . '&amp;action=delete&amp;album_id=' . $parent_id,
			'U_UPLOAD'			=> append_sid($phpbb_root_path . $gallery_root_path . "posting.$phpEx", 'mode=image&amp;submode=upload&amp;album_id=' . $parent_id),
			'ICON_MOVE_DOWN'			=> '<img src="' . $phpbb_root_path . '/adm/images/icon_down.gif" alt="" />',
			'ICON_MOVE_DOWN_DISABLED'	=> '<img src="' . $phpbb_root_path . '/adm/images/icon_down_disabled.gif" alt="" />',
			'ICON_MOVE_UP'				=> '<img src="' . $phpbb_root_path . '/adm/images/icon_up.gif" alt="" />',
			'ICON_MOVE_UP_DISABLED'		=> '<img src="' . $phpbb_root_path . '/adm/images/icon_up_disabled.gif" alt="" />',
			'ICON_EDIT'					=> '<img src="' . $phpbb_root_path . '/adm/images/icon_edit.gif" alt="" />',
			'ICON_DELETE'				=> '<img src="' . $phpbb_root_path . '/adm/images/icon_delete.gif" alt="" />',
		));
	}//function

	function create_album()
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$gallery_root_path = GALLERY_ROOT_PATH;

		include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		include_once("{$phpbb_root_path}{$gallery_root_path}includes/permissions.$phpEx");
		$album_access_array = get_album_access_array();

		//check if the user has already reached his limit
		if (!gallery_acl_check('i_upload', OWN_GALLERY_PERMISSIONS))
		{
			trigger_error('NO_PERSALBUM_ALLOWED');
		}
		$sql = 'SELECT COUNT(album_id) as albums
			FROM ' . GALLERY_ALBUMS_TABLE . "
			WHERE album_user_id = {$user->data['user_id']}";
		$result = $db->sql_query($sql);
		$albums = $db->sql_fetchrow($result);
		if (($albums['albums'] - 1) >= gallery_acl_check('album_count', OWN_GALLERY_PERMISSIONS))
		{
			trigger_error('NO_MORE_SUBALBUMS_ALLOWED');
		}

		$submit = (isset($_POST['submit'])) ? true : false;
		$redirect = request_var('redirect', '');

		if(!$submit)
		{
			$parent_id = request_var('parent_id', 0);
			album_hacking($parent_id);
			$parents_list = personal_album_select($user->data['user_id'], $parent_id);
			$template->assign_vars(array(
				'S_CREATE_SUBALBUM'		=> true,
				'S_UCP_ACTION'			=> $this->u_action . '&amp;action=create' . (($redirect != '') ? '&amp;redirect=album' : ''),
				'L_TITLE'				=> $user->lang['CREATE_SUBALBUM'],
				'L_TITLE_EXPLAIN'		=> $user->lang['CREATE_SUBALBUM_EXP'],

				'S_DESC_BBCODE_CHECKED'		=> true,
				'S_DESC_SMILIES_CHECKED'	=> true,
				'S_DESC_URLS_CHECKED'		=> true,
				'S_PARENT_OPTIONS'			=> '<option value="' . $user->gallery['personal_album_id'] . '">' . $user->lang['NO_PARENT_ALBUM'] . '</option>' . $parents_list,
			));
		}
		else
		{
			if (!check_form_key('ucp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			//create the subalbum
			$album_data = array(
				'album_name'					=> request_var('album_name', '', true),
				'parent_id'						=> request_var('parent_id', 0),
				'album_parents'					=> '',
				'album_type'					=> 1,
				'album_desc_options'			=> 7,
				'album_desc'					=> utf8_normalize_nfc(request_var('album_desc', '', true)),
				'album_user_id'					=> $user->data['user_id'],
				'album_last_username'			=> '',
			);
			if (!$album_data['album_name'])
			{
				trigger_error('MISSING_NAME');
			}
			$album_data['parent_id'] = ($album_data['parent_id']) ? $album_data['parent_id'] : $user->gallery['personal_album_id'];
			generate_text_for_storage($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options'], request_var('desc_parse_bbcode', false), request_var('desc_parse_urls', false), request_var('desc_parse_smilies', false));

			//the following is copied from the forum management. thx to the developers
			if ($album_data['parent_id'])//should be always, but we keep it for better overview
			{
				$sql = 'SELECT left_id, right_id, album_type
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE album_id = ' . $album_data['parent_id'];
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['PARENT_NOT_EXIST'], E_USER_WARNING);
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
			$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
			$redirect_album_id = $db->sql_nextid();
			$cache->destroy('_albums');
			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);

			trigger_error($user->lang['CREATED_SUBALBUM'] . '<br /><br /><a href="' . (($redirect) ? append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$redirect_album_id") : $this->u_action) . '">' . $user->lang['BACK_TO_PREV'] . '</a>');
		}
	}

	function edit_album()
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$gallery_root_path = GALLERY_ROOT_PATH;
		
		include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		include_once($phpbb_root_path . $gallery_root_path . 'includes/functions.' . $phpEx);

		$album_id = request_var('album_id', 0);
		album_hacking($album_id);

		$submit = (isset($_POST['submit'])) ? true : false;
		$redirect = request_var('redirect', '');
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
			$parents_list = personal_album_select($user->data['user_id'], $album_data['parent_id'], $album_id);

			$template->assign_vars(array(
				'S_EDIT_SUBALBUM'				=> true,
				'S_PERSONAL_ALBUM'				=> ($album_id == $user->gallery['personal_album_id']) ? true : false,

				'L_TITLE'			=> $user->lang['EDIT_SUBALBUM'],
				'L_TITLE_EXPLAIN'	=> $user->lang['EDIT_SUBALBUM_EXP'],

				'S_ALBUM_ACTION' 			=> $this->u_action . '&amp;action=edit&amp;album_id=' . $album_id . (($redirect != '') ? '&amp;redirect=album' : ''),
				'S_PARENT_OPTIONS'			=> '<option value="' . $user->gallery['personal_album_id'] . '">' . $user->lang['NO_PARENT_ALBUM'] . '</option>' . $parents_list,

				'ALBUM_NAME' 				=> $album_data['album_name'],
				'ALBUM_DESC'				=> $album_desc_data['text'],
				'ALBUM_TYPE'				=> $album_data['album_type'],
				'S_DESC_BBCODE_CHECKED'		=> ($album_desc_data['allow_bbcode']) ? true : false,
				'S_DESC_SMILIES_CHECKED'	=> ($album_desc_data['allow_smilies']) ? true : false,
				'S_DESC_URLS_CHECKED'		=> ($album_desc_data['allow_urls']) ? true : false,

				'S_MODE' 				=> 'edit',
			));
		}
		else
		{// Is it salty ?
			if (!check_form_key('ucp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}
			$album_data = array(
				'album_name'					=> ($album_id == $user->gallery['personal_album_id']) ? $user->data['username'] : request_var('album_name', '', true),
				'parent_id'						=> request_var('parent_id', (($album_id == $user->gallery['personal_album_id']) ? 0 : $user->gallery['personal_album_id'])),
				//left_id and right_id are created some lines later
				'album_parents'					=> '',
				'album_type'					=> 1,
				'album_desc_options'			=> 7,
				'album_desc'					=> utf8_normalize_nfc(request_var('album_desc', '', true)),
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
			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
			$cache->destroy('_albums');

			trigger_error($user->lang['EDITED_SUBALBUM'] . '<br /><br />
				<a href="' . (($redirect) ? append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id") : append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=gallery&amp;mode=manage_albums&amp;action=manage&amp;parent_id=' . (($album_data['parent_id']) ? $album_data['parent_id'] : $user->gallery['personal_album_id']))) . '">' . $user->lang['BACK_TO_PREV'] . '</a>');
		}
	}

	function delete_album()
	{
		global $db, $template, $user, $cache, $phpbb_root_path, $phpEx;

		$s_hidden_fields = build_hidden_fields(array(
			'album_id'		=> request_var('album_id', 0),
		));

		if (confirm_box(true))
		{
			$album_id = request_var('album_id', 0);
			$left_id = $right_id = 0;
			$deleted_images_na = '';
			$deleted_albums = $deleted_images = '';
			$deleted_albums_a = $deleted_images_a = array();

			//check for owner
			$sql = 'SELECT album_id, left_id, right_id, parent_id
				FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE album_user_id = ' . $user->data['user_id'] . '
				ORDER BY left_id ASC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$album[] = $row;
				if ($row['album_id'] == $album_id)
				{
					$left_id = $row['left_id'];
					$right_id = $row['right_id'];
					$parent_id = $row['parent_id'];
				}
			}
			for( $i = 0; $i < count($album); $i++ )
			{
				if (($left_id <= $album[$i]['left_id']) && ($album[$i]['left_id'] <= $right_id))
				{
					$deleted_albums .= (($deleted_albums) ? ', ' : '') . $album[$i]['album_id'];
					$deleted_albums_a[] = $album[$i]['album_id'];
				}
			}
			// $deleted_albums is the array of albums we are going to delete.
			// now get the images in $deleted_images
			$sql = 'SELECT image_id, image_thumbnail, image_filename
				FROM ' . GALLERY_IMAGES_TABLE . '
				WHERE image_album_id IN (' . $deleted_albums . ')
				ORDER BY image_id ASC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				//delete the files themselves
				@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $row['image_thumbnail']);
				@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $row['image_filename']);

				$deleted_images .= (($deleted_images) ? ', ' : '') . $row['image_id'];
				$deleted_images_a[] = $row['image_id'];
			}
			// we have all image_ids in $deleted_images which are deleted
			// aswell as the album_ids in $deleted_albums
			// so now drop the comments, ratings, images and albums
			if ($deleted_images)
			{
				$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . " WHERE comment_image_id IN ($deleted_images)";
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . " WHERE image_id IN ($deleted_images)";
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . " WHERE image_id IN ($deleted_images)";
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . " WHERE rate_image_id IN ($deleted_images)";
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . " WHERE image_id IN ($deleted_images)";
				$db->sql_query($sql);
			}
			$sql = 'DELETE FROM ' . GALLERY_ALBUMS_TABLE . " WHERE album_id IN ($deleted_albums)";
			$db->sql_query($sql);

			//Maybe we deleted all, so we have to empty $user->gallery['personal_album_id']
			if (in_array($user->gallery['personal_album_id'], $deleted_albums_a))
			{
				$sql = 'UPDATE ' . GALLERY_USERS_TABLE . " SET personal_album_id = 0 WHERE personal_album_id IN ($deleted_albums)";
				$db->sql_query($sql);

			$sql = 'UPDATE ' . GALLERY_CONFIG_TABLE . " 
					SET config_value = config_value - 1
					WHERE config_name  = 'personal_counter'";
			$db->sql_query($sql);
			}

			//solve the left_id right_id problem
			$delete_id = $right_id - ($left_id - 1);
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
				SET left_id = left_id - $delete_id
				WHERE left_id > $left_id
					AND album_user_id = {$user->data['user_id']}";
			$db->sql_query($sql);
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
				SET right_id = right_id - $delete_id
				WHERE right_id > $right_id
					AND album_user_id = {$user->data['user_id']}";
			$db->sql_query($sql);

			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
			$cache->destroy('sql', GALLERY_IMAGES_TABLE);
			$cache->destroy('sql', GALLERY_RATES_TABLE);
			$cache->destroy('sql', GALLERY_COMMENTS_TABLE);
			$cache->destroy('_albums');

			trigger_error($user->lang['DELETED_ALBUMS'] . (($parent_id) ? '<br /><br />
				<a href="' . append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=gallery&amp;mode=manage_albums&amp;action=manage&amp;parent_id=' . $parent_id) . '">' . $user->lang['BACK_TO_PREV'] . '</a>' : ''));
		}
		else
		{
			$album_id = request_var('album_id', 0);
			album_hacking($album_id);
			confirm_box(false, 'DELETE_ALBUM', $s_hidden_fields);
		}
	}

	function move_album()
	{
		global $db, $user, $cache, $phpEx, $phpbb_root_path;

		$album_id = request_var('album_id', 0);
		album_hacking($album_id);

		$move = request_var('move', '', true);
		$sql = 'SELECT *
			FROM ' . GALLERY_ALBUMS_TABLE . "
			WHERE album_id = $album_id";
		$result = $db->sql_query($sql);
		$moving = $db->sql_fetchrow($result);

		$sql = 'SELECT album_id, left_id, right_id
			FROM ' . GALLERY_ALBUMS_TABLE . "
			WHERE parent_id = {$moving['parent_id']}
				AND album_user_id = {$user->data['user_id']}
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
				AND album_user_id = {$user->data['user_id']}";
		$db->sql_query($sql);

		$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
		$cache->destroy('_albums');
		redirect(append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=gallery&amp;mode=manage_albums&amp;action=manage&amp;parent_id=' . $moving['parent_id']));
	}

}

?>