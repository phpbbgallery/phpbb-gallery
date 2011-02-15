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

class phpbb_gallery_misc
{
	/**
	* Insert the image into the database
	*/
	static public function upload_image(&$image_data, $album_id)
	{
		global $user, $db;

		$sql_ary = array(
			'image_filename' 		=> $image_data['filename'],
			'image_name'			=> $image_data['image_name'],
			'image_name_clean'		=> utf8_clean_string($image_data['image_name']),
			'image_user_id'			=> $user->data['user_id'],
			'image_user_colour'		=> $user->data['user_colour'],
			'image_username'		=> $image_data['username'],
			'image_username_clean'	=> utf8_clean_string($image_data['username']),
			'image_user_ip'			=> $user->ip,
			'image_time'			=> $image_data['image_time'],
			'image_album_id'		=> $image_data['image_album_id'],
			'image_status'			=> (phpbb_gallery::$auth->acl_check('i_approve', $album_id)) ? phpbb_gallery_image::STATUS_APPROVED : phpbb_gallery_image::STATUS_UNAPPROVED,
			'filesize_upload'		=> $image_data['image_filesize'],
			'image_contest'			=> $image_data['image_contest'],
			'image_exif_data'		=> $image_data['image_exif_data'],
			'image_has_exif'		=> $image_data['image_has_exif'],
		);

		$message_parser				= new parse_message();
		$message_parser->message	= utf8_normalize_nfc($image_data['image_desc']);
		if($message_parser->message)
		{
			$message_parser->parse(true, true, true, true, false, true, true, true);
			$sql_ary['image_desc']			= $message_parser->message;
			$sql_ary['image_desc_uid']		= $message_parser->bbcode_uid;
			$sql_ary['image_desc_bitfield']	= $message_parser->bbcode_bitfield;
		}
		else
		{
			$sql_ary['image_desc']			= '';
			$sql_ary['image_desc_uid']		= '';
			$sql_ary['image_desc_bitfield']	= '';
		}

		$sql = 'INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);
		$image_id = $db->sql_nextid();

		if (phpbb_gallery::$user->get_data('watch_own'))
		{
			$sql_ary = array(
				'image_id'			=> $image_id,
				'user_id'			=> $user->data['user_id'],
			);
			$sql = 'INSERT INTO ' . GALLERY_WATCH_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}

		return array('image_id' => $image_id, 'image_name' => $image_data['image_name']);
	}

	/**
	* Gallery Notification
	*
	* borrowed from phpBB3
	* @author: phpBB Group
	* @function: user_notification
	*/
	static public function notification($mode, $handle_id, $image_name)
	{
		global $user, $db, $album_id, $image_id, $image_data, $album_data;

		$help_mode = $mode . '_id';
		$mode_id = $$help_mode;
		$mode_notification = ($mode == 'album') ? 'image' : 'comment';

		// Get banned User ID's
		$sql = 'SELECT ban_userid
			FROM ' . BANLIST_TABLE . '
			WHERE ban_userid <> 0
				AND ban_exclude <> 1';
		$result = $db->sql_query($sql);

		$sql_ignore_users = ANONYMOUS . ', ' . $user->data['user_id'];
		while ($row = $db->sql_fetchrow($result))
		{
			$sql_ignore_users .= ', ' . (int) $row['ban_userid'];
		}
		$db->sql_freeresult($result);

		$notify_rows = array();

		// -- get album_userids	|| image_userids
		$sql = 'SELECT u.user_id, u.username, u.user_email, u.user_lang, u.user_notify_type, u.user_jabber
			FROM ' . GALLERY_WATCH_TABLE . ' w, ' . USERS_TABLE . ' u
			WHERE w.' . $help_mode . ' = ' . $handle_id . "
				AND w.user_id NOT IN ($sql_ignore_users)
				AND u.user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')
				AND u.user_id = w.user_id';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$notify_rows[$row['user_id']] = array(
				'user_id'		=> $row['user_id'],
				'username'		=> $row['username'],
				'user_email'	=> $row['user_email'],
				'user_jabber'	=> $row['user_jabber'],
				'user_lang'		=> $row['user_lang'],
				'notify_type'	=> ($mode != 'album') ? 'image' : 'album',
				'template'		=> "new{$mode_notification}_notify",
				'method'		=> $row['user_notify_type'],
				'allowed'		=> false
			);
		}
		$db->sql_freeresult($result);

		if (!sizeof($notify_rows))
		{
			return;
		}

		// Get album_user_id to check for personal albums.
		$sql = 'SELECT album_id, album_user_id
			FROM ' . GALLERY_ALBUMS_TABLE . '
			WHERE album_id = ' . $album_id;
		$result = $db->sql_query($sql);
		$album = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if (empty($album))
		{
			trigger_error('ALBUM_NOT_EXIST');
		}

		// Make sure users are allowed to view the album
		$i_view_ary = $groups_ary = $groups_row = array();
		$sql_array = array(
			'SELECT'		=> 'pr.i_view, p.perm_system, p.perm_group_id, p.perm_user_id',
			'FROM'			=> array(GALLERY_PERMISSIONS_TABLE => 'p'),

			'LEFT_JOIN'		=> array(
				array(
					'FROM'		=> array(GALLERY_ROLES_TABLE => 'pr'),
					'ON'		=> 'p.perm_role_id = pr.role_id',
				),
			),

			'WHERE'			=> (($album['album_user_id'] == phpbb_gallery_album::PUBLIC_ALBUM) ? 'p.perm_album_id = ' . $album_id : 'p.perm_system <> ' . phpbb_gallery_album::PUBLIC_ALBUM),
			'ORDER_BY'		=> 'pr.i_view ASC',
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['perm_group_id'])
			{
				$groups_ary[] = $row['perm_group_id'];
				$groups_row[$row['perm_group_id']] = $row;
			}
			else
			{
				if (!isset($i_view_ary[$row['perm_user_id']]) || (isset($i_view_ary[$row['perm_user_id']]) && ($i_view_ary[$row['perm_user_id']] < $row['i_view'])))
				{
					if (!$row['perm_system'])
					{
						$i_view_ary[$row['perm_user_id']] = $row['i_view'];
					}
					elseif (($row['perm_system'] == phpbb_gallery_auth::OWN_ALBUM) && ($album['album_user_id'] == $row['perm_user_id']))
					{
						$i_view_ary[$row['perm_user_id']] = $row['i_view'];
					}
					elseif (($row['perm_system'] == phpbb_gallery_auth::PERSONAL_ALBUM) && ($album['album_user_id'] != $row['perm_user_id']))
					{
						$i_view_ary[$row['perm_user_id']] = $row['i_view'];
					}
				}
			}
		}
		$db->sql_freeresult($result);

		if (sizeof($groups_ary))
		{
			// Get all users by their group permissions
			$sql = 'SELECT user_id, group_id
				FROM ' . USER_GROUP_TABLE . '
				WHERE ' . $db->sql_in_set('group_id', $groups_ary) . '
					AND user_pending = 0';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if (!isset($i_view_ary[$row['user_id']]) || (isset($i_view_ary[$row['user_id']]) && ($i_view_ary[$row['user_id']] < $groups_row[$row['group_id']]['i_view'])))
				{
					if (!$groups_row[$row['group_id']]['perm_system'])
					{
						$i_view_ary[$row['user_id']] = $groups_row[$row['group_id']]['i_view'];
					}
					else if (($groups_row[$row['group_id']]['perm_system'] == phpbb_gallery_auth::OWN_ALBUM) && ($album['album_user_id'] == $row['user_id']))
					{
						$i_view_ary[$row['user_id']] = $groups_row[$row['group_id']]['i_view'];
					}
					else if (($groups_row[$row['group_id']]['perm_system'] == phpbb_gallery_auth::PERSONAL_ALBUM) && ($album['album_user_id'] != $row['user_id']))
					{
						$i_view_ary[$row['user_id']] = $groups_row[$row['group_id']]['i_view'];
					}
				}
			}
			$db->sql_freeresult($result);
		}

		// Now, we have to do a little step before really sending, we need to distinguish our users a little bit. ;)
		$msg_users = $delete_ids = $update_notification = array();
		foreach ($notify_rows as $user_id => $row)
		{
			if (($i_view_ary[$row['user_id']] != phpbb_gallery_auth::ACL_YES) || !trim($row['user_email']))
			{
				$delete_ids[$row['notify_type']][] = $row['user_id'];
			}
			else
			{
				$msg_users[] = $row;
				$update_notification[$row['notify_type']][] = $row['user_id'];
			}
		}
		unset($notify_rows);

		// Now, we are able to really send out notifications
		if (sizeof($msg_users))
		{
			if (!class_exists('messenger'))
			{
				phpbb_gallery_url::_include('functions_messenger', 'phpbb');
			}
			$messenger = new messenger();

			$msg_list_ary = array();
			foreach ($msg_users as $row)
			{
				$pos = (!isset($msg_list_ary[$row['template']])) ? 0 : sizeof($msg_list_ary[$row['template']]);

				$msg_list_ary[$row['template']][$pos]['method']	= $row['method'];
				$msg_list_ary[$row['template']][$pos]['email']	= $row['user_email'];
				$msg_list_ary[$row['template']][$pos]['jabber']	= $row['user_jabber'];
				$msg_list_ary[$row['template']][$pos]['name']	= $row['username'];
				$msg_list_ary[$row['template']][$pos]['lang']	= $row['user_lang'];
			}
			unset($msg_users);

			foreach ($msg_list_ary as $email_template => $email_list)
			{
				foreach ($email_list as $addr)
				{
					$messenger->template($email_template, $addr['lang']);

					$messenger->to($addr['email'], $addr['name']);
					$messenger->im($addr['jabber'], $addr['name']);

					$messenger->assign_vars(array(
						'USERNAME'		=> htmlspecialchars_decode($addr['name']),
						'IMAGE_NAME'	=> htmlspecialchars_decode($image_name),
						'ALBUM_NAME'	=> htmlspecialchars_decode($album_data['album_name']),

						'U_ALBUM'				=> phpbb_gallery_url::create_link('full', 'album', "album_id=$album_id"),
						'U_IMAGE'				=> phpbb_gallery_url::create_link('full', 'image_page', "album_id=$album_id&amp;image_id=$image_id"),
						'U_NEWEST_POST'			=> phpbb_gallery_url::create_link('full', 'viewtopic', "album_id=$album_id&amp;image_id=$image_id"),
						'U_STOP_WATCHING_IMAGE'	=> phpbb_gallery_url::create_link('full', 'posting', "mode=image&amp;submode=unwatch&amp;album_id=$album_id&amp;image_id=$image_id"),
						'U_STOP_WATCHING_ALBUM'	=> phpbb_gallery_url::create_link('full', 'posting', "mode=album&amp;submode=unwatch&amp;album_id=$album_id"),
					));

					$messenger->send($addr['method']);
				}
			}
			unset($msg_list_ary);

			$messenger->save_queue();
		}

		// Now delete the user_ids not authorised to receive notifications on this image/album
		if (!empty($delete_ids['image']))
		{
			$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . "
				WHERE image_id = $image_id
					AND " . $db->sql_in_set('user_id', $delete_ids['image']);
			$db->sql_query($sql);
		}

		if (!empty($delete_ids['album']))
		{
			$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . "
				WHERE album_id = $album_id
					AND " . $db->sql_in_set('user_id', $delete_ids['album']);
			$db->sql_query($sql);
		}
	}

	static public function display_captcha($mode)
	{
		static $gallery_display_captcha;

		if (isset($gallery_display_captcha[$mode]))
		{
			return $gallery_display_captcha[$mode];
		}

		global $config, $user;

		$gallery_display_captcha[$mode] = ($user->data['user_id'] == ANONYMOUS) && phpbb_gallery_config::get('captcha_' . $mode) && (version_compare($config['version'], '3.0.5', '>'));

		return $gallery_display_captcha[$mode];
	}

	/**
	* Marks a album as read
	*
	* borrowed from phpBB3
	* @author: phpBB Group
	* @function: markread
	*/
	static public function markread($mode, $album_id = false)
	{
		global $db, $user;

		// Sorry, no guest support!
		if ($user->data['user_id'] == ANONYMOUS)
		{
			return;
		}

		if ($mode == 'all')
		{
			if ($album_id === false || !sizeof($album_id))
			{
				// Mark all albums read (index page)
				$sql = 'DELETE FROM ' . GALLERY_ATRACK_TABLE . '
					WHERE user_id = ' . $user->data['user_id'];
				$db->sql_query($sql);

				phpbb_gallery::$user->update_data(array(
						'user_lastmark'		=> time(),
				));
			}

			return;
		}
		else if ($mode == 'albums')
		{
			// Mark album read
			if (!is_array($album_id))
			{
				$album_id = array($album_id);
			}

			$sql = 'SELECT album_id
				FROM ' . GALLERY_ATRACK_TABLE . "
				WHERE user_id = {$user->data['user_id']}
					AND " . $db->sql_in_set('album_id', $album_id);
			$result = $db->sql_query($sql);

			$sql_update = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$sql_update[] = $row['album_id'];
			}
			$db->sql_freeresult($result);

			if (sizeof($sql_update))
			{
				$sql = 'UPDATE ' . GALLERY_ATRACK_TABLE . '
					SET mark_time = ' . time() . "
					WHERE user_id = {$user->data['user_id']}
						AND " . $db->sql_in_set('album_id', $sql_update);
				$db->sql_query($sql);
			}

			if ($sql_insert = array_diff($album_id, $sql_update))
			{
				$sql_ary = array();
				foreach ($sql_insert as $a_id)
				{
					$sql_ary[] = array(
						'user_id'	=> (int) $user->data['user_id'],
						'album_id'	=> (int) $a_id,
						'mark_time'	=> time()
					);
				}

				$db->sql_multi_insert(GALLERY_ATRACK_TABLE, $sql_ary);
			}

			return;
		}
		else if ($mode == 'album')
		{
			if ($album_id === false)
			{
				return;
			}

			$sql = 'UPDATE ' . GALLERY_ATRACK_TABLE . '
				SET mark_time = ' . time() . "
				WHERE user_id = {$user->data['user_id']}
					AND album_id = $album_id";
			$db->sql_query($sql);

			if (!$db->sql_affectedrows())
			{
				$db->sql_return_on_error(true);

				$sql_ary = array(
					'user_id'		=> (int) $user->data['user_id'],
					'album_id'		=> (int) $album_id,
					'mark_time'		=> time(),
				);

				$db->sql_query('INSERT INTO ' . GALLERY_ATRACK_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

				$db->sql_return_on_error(false);
			}

			return;
		}
	}
}
