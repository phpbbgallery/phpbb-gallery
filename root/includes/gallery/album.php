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

class phpbb_gallery_album
{
	const PUBLIC_ALBUM		= 0;

	const TYPE_CAT			= 0;
	const TYPE_UPLOAD		= 1;
	const TYPE_CONTEST		= 2;

	const STATUS_OPEN		= 0;
	const STATUS_LOCKED		= 1;

	/**
	* Get album information
	*/
	static public function get_info($album_id, $extended_info = true)
	{
		global $db, $user;

		$sql_array = array(
			'SELECT'		=> 'a.*',
			'FROM'			=> array(GALLERY_ALBUMS_TABLE => 'a'),

			'WHERE'			=> 'a.album_id = ' . (int) $album_id,
		);

		if ($extended_info)
		{
			$sql_array['SELECT'] .= ', c.*, w.watch_id';
			$sql_array['LEFT_JOIN'] = array(
				array(
					'FROM'		=> array(GALLERY_WATCH_TABLE => 'w'),
					'ON'		=> 'a.album_id = w.album_id AND w.user_id = ' . (int) $user->data['user_id'],
				),
				array(
					'FROM'		=> array(GALLERY_CONTESTS_TABLE => 'c'),
					'ON'		=> 'a.album_id = c.contest_album_id',
				),
			);
		}
		$sql = $db->sql_build_query('SELECT', $sql_array);

		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			meta_refresh(3, phpbb_gallery_url::append_sid('index'));
			trigger_error('ALBUM_NOT_EXIST');
		}

		if ($extended_info  && !isset($row['contest_id']))
		{
			$row['contest_id'] = 0;
			$row['contest_rates_start'] = 0;
			$row['contest_end'] = 0;
			$row['contest_marked'] = 0;
			$row['contest_first'] = 0;
			$row['contest_second'] = 0;
			$row['contest_third'] = 0;
		}

		return $row;
	}

	/**
	* Check whether the album_user is the user who wants to do something
	*/
	static public function check_user($album_id, $user_id = false)
	{
		if ($user_id === false)
		{
			global $user;
			$user_id = (int) $user->data['user_id'];
		}
		else
		{
			$user_id = (int) $user_id;
		}

		global $db;

		$sql = 'SELECT album_id
			FROM ' . GALLERY_ALBUMS_TABLE . '
			WHERE album_id = ' . (int) $album_id . '
				AND album_user_id = ' . $user_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row === false)
		{
			// return false;
			trigger_error('NO_ALBUM_STEALING');
		}

		return true;
	}

	/**
	* Generate gallery-albumbox
	* @param	bool				$ignore_personals		list personal albums
	* @param	string				$select_name			request_var() for the select-box
	* @param	int					$select_id				selected album
	* @param	string				$requested_permission	Exp: for moving a image you need i_upload permissions or a_moderate
	* @param	(string || array)	$ignore_id				disabled albums, Exp: on moving: the album where the image is now
	* @param	int					$album_user_id			for the select-boxes of the ucp so you only can attach to your own albums
	* @param	int					$requested_album_type	only albums of the album_type are allowed
	*
	* @return	string				$gallery_albumbox		if ($select_name) {full select-box} else {list with options}
	*
	* comparable to make_forum_select (includes/functions_admin.php)
	*/
	function get_albumbox($ignore_personals, $select_name, $select_id = false, $requested_permission = false, $ignore_id = false, $album_user_id = self::PUBLIC_ALBUM, $requested_album_type = -1)
	{
		global $db, $user, $cache;

		// Instead of the query we use the cache
		$album_data = $cache->obtain_album_list();

		$right = $last_a_u_id = 0;
		$access_own = $access_personal = $requested_own = $requested_personal = false;
		$c_access_own = $c_access_personal = false;
		$padding_store = array('0' => '');
		$padding = $album_list = '';
		$check_album_type = ($requested_album_type >= 0) ? true : false;

		// Sometimes it could happen that albums will be displayed here not be displayed within the index page
		// This is the result of albums not displayed at index and a parent of a album with no permissions.
		// If this happens, the padding could be "broken", see includes/functions_admin.php > make_forum_select

		foreach ($album_data as $row)
		{
			$list = false;
			if ($row['album_user_id'] != $last_a_u_id)
			{
				if (!$last_a_u_id && phpbb_gallery::$auth->acl_check('a_list', phpbb_gallery_auth::PERSONAL_ALBUM) && !$ignore_personals)
				{
					$album_list .= '<option disabled="disabled" class="disabled-option">' . $user->lang['PERSONAL_ALBUMS'] . '</option>';
				}
				$padding = '';
				$padding_store[$row['parent_id']] = '';
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
			$last_a_u_id = $row['album_user_id'];
			$disabled = false;

			if (
			// Is in the ignore_id
			((is_array($ignore_id) && in_array($row['album_id'], $ignore_id)) || $row['album_id'] == $ignore_id)
			||
			// Need upload permissions (for moving)
			(($requested_permission == 'm_move') && (($row['album_type'] == self::TYPE_CAT) || (!phpbb_gallery::$auth->acl_check('i_upload', $row['album_id'], $row['album_user_id']) && !phpbb_gallery::$auth->acl_check('m_move', $row['album_id'], $row['album_user_id']))))
			||
			// album_type does not fit
			($check_album_type && ($row['album_type'] != $requested_album_type))
			)
			{
				$disabled = true;
			}

			if (($select_id == phpbb_gallery_auth::SETTING_PERMISSIONS) && !$row['album_user_id'])
			{
				$list = true;
			}
			else if (!$row['album_user_id'])
			{
				if (phpbb_gallery::$auth->acl_check('a_list', $row['album_id'], $row['album_user_id']) || defined('IN_ADMIN'))
				{
					$list = true;
				}
			}
			else if (!$ignore_personals)
			{
				if ($row['album_user_id'] == $user->data['user_id'])
				{
					if (!$c_access_own)
					{
						$c_access_own = true;
						$access_own = phpbb_gallery::$auth->acl_check('a_list', phpbb_gallery_auth::OWN_ALBUM);
						if ($requested_permission)
						{
							$requested_own = !phpbb_gallery::$auth->acl_check($requested_permission, phpbb_gallery_auth::OWN_ALBUM);
						}
						else
						{
							$requested_own = false; // We need the negated version of true here
						}
					}
					$list = (!$list) ? $access_own : $list;
					$disabled = (!$disabled) ? $requested_own : $disabled;
				}
				else if ($row['album_user_id'])
				{
					if (!$c_access_personal)
					{
						$c_access_personal = true;
						$access_personal = phpbb_gallery::$auth->acl_check('a_list', phpbb_gallery_auth::PERSONAL_ALBUM);
						if ($requested_permission)
						{
							$requested_personal = !phpbb_gallery::$auth->acl_check($requested_permission, phpbb_gallery_auth::PERSONAL_ALBUM);
						}
						else
						{
							$requested_personal = false; // We need the negated version of true here
						}
					}
					$list = (!$list) ? $access_personal : $list;
					$disabled = (!$disabled) ? $requested_personal : $disabled;
				}
			}
			if (($album_user_id != self::PUBLIC_ALBUM) && ($album_user_id != $row['album_user_id']))
			{
				$list = false;
			}
			else if (($album_user_id != self::PUBLIC_ALBUM) && ($row['parent_id'] == 0))
			{
				$disabled = true;
			}

			if ($list)
			{
				$selected = (is_array($select_id)) ? ((in_array($row['album_id'], $select_id)) ? ' selected="selected"' : '') : (($row['album_id'] == $select_id) ? ' selected="selected"' : '');
				$album_list .= '<option value="' . $row['album_id'] . '"' . (($disabled) ? ' disabled="disabled" class="disabled-option"' : $selected) . '>' . $padding . $row['album_name'] . ' (ID: ' . $row['album_id'] . ')</option>';
			}
		}
		unset($padding_store);

		if ($select_name)
		{
			$gallery_albumbox = "<select name='$select_name' id='$select_name'>";
			$gallery_albumbox .= $album_list;
			$gallery_albumbox .= '</select>';
		}
		else
		{
			$gallery_albumbox = $album_list;
		}

		return $gallery_albumbox;
	}

	/**
	* Update album information
	* Resets the following columns with the correct value:
	* - album_images, _real
	* - album_last_image_id, _time, _name
	* - album_last_username, _user_colour, _user_id
	*/
	static public function update_info($album_id)
	{
		global $db;

		$images_real = $images = $album_user_id = 0;

		// Get the album_user_id, so we can keep the user_colour
		$sql = 'SELECT album_user_id
			FROM ' . GALLERY_ALBUMS_TABLE . '
			WHERE album_id = ' . (int) $album_id;
		$result = $db->sql_query($sql);
		$album_user_id = $db->sql_fetchfield('album_user_id');
		$db->sql_freeresult($result);

		// Number of not unapproved images
		$sql = 'SELECT COUNT(image_id) images
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_status <> ' . phpbb_gallery_image::STATUS_UNAPPROVED . '
				AND image_status <> ' . phpbb_gallery_image::STATUS_ORPHAN . '
				AND image_album_id = ' . (int) $album_id;
		$result = $db->sql_query($sql);
		$images = $db->sql_fetchfield('images');
		$db->sql_freeresult($result);

		// Number of total images
		$sql = 'SELECT COUNT(image_id) images_real
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_status <> ' . phpbb_gallery_image::STATUS_ORPHAN . '
				AND image_album_id = ' . (int) $album_id;
		$result = $db->sql_query($sql);
		$images_real = $db->sql_fetchfield('images_real');
		$db->sql_freeresult($result);

		// Data of the last not unapproved image
		$sql = 'SELECT image_id, image_time, image_name, image_username, image_user_colour, image_user_id
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_status <> ' . phpbb_gallery_image::STATUS_UNAPPROVED . '
				AND image_status <> ' . phpbb_gallery_image::STATUS_ORPHAN . '
				AND image_album_id = ' . (int) $album_id . '
			ORDER BY image_time DESC';
		$result = $db->sql_query($sql);
		if ($row = $db->sql_fetchrow($result))
		{
			$sql_ary = array(
				'album_images_real'			=> $images_real,
				'album_images'				=> $images,
				'album_last_image_id'		=> $row['image_id'],
				'album_last_image_time'		=> $row['image_time'],
				'album_last_image_name'		=> $row['image_name'],
				'album_last_username'		=> $row['image_username'],
				'album_last_user_colour'	=> $row['image_user_colour'],
				'album_last_user_id'		=> $row['image_user_id'],
			);
		}
		else
		{
			// No approved image, so we clear the columns
			$sql_ary = array(
				'album_images_real'			=> $images_real,
				'album_images'				=> $images,
				'album_last_image_id'		=> 0,
				'album_last_image_time'		=> 0,
				'album_last_image_name'		=> '',
				'album_last_username'		=> '',
				'album_last_user_colour'	=> '',
				'album_last_user_id'		=> 0,
			);
			if ($album_user_id)
			{
				unset($sql_ary['album_last_user_colour']);
			}
		}
		$db->sql_freeresult($result);

		$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE ' . $db->sql_in_set('album_id', $album_id);
		$db->sql_query($sql);

		return $row;
	}

	/**
	* Get album branch
	*
	* borrowed from phpBB3
	* @author: phpBB Group
	* @function: get_forum_branch
	*/
	static public function get_branch($branch_user_id, $album_id, $type = 'all', $order = 'descending', $include_album = true)
	{
		global $db;

		switch ($type)
		{
			case 'parents':
				$condition = 'a1.left_id BETWEEN a2.left_id AND a2.right_id';
			break;

			case 'children':
				$condition = 'a2.left_id BETWEEN a1.left_id AND a1.right_id';
			break;

			default:
				$condition = 'a2.left_id BETWEEN a1.left_id AND a1.right_id OR a1.left_id BETWEEN a2.left_id AND a2.right_id';
			break;
		}

		$rows = array();

		$sql = 'SELECT a2.*
			FROM ' . GALLERY_ALBUMS_TABLE . ' a1
			LEFT JOIN ' . GALLERY_ALBUMS_TABLE . " a2 ON ($condition) AND a2.album_user_id = $branch_user_id
			WHERE a1.album_id = $album_id
				AND a1.album_user_id = $branch_user_id
			ORDER BY a2.left_id " . (($order == 'descending') ? 'ASC' : 'DESC');
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if (!$include_album && $row['album_id'] == $album_id)
			{
				continue;
			}

			$rows[] = $row;
		}
		$db->sql_freeresult($result);

		return $rows;
	}

	/**
	* Create album navigation links for given album, create parent
	* list if currently null, assign basic album info to template
	*
	* borrowed from phpBB3
	* @author: phpBB Group
	* @function: generate_forum_nav
	*/
	static public function generate_nav(&$album_data)
	{
		global $db, $user, $template;

		// Get album parents
		$album_parents = self::get_parents($album_data);

		// Display username for personal albums
		if ($album_data['album_user_id'] > self::PUBLIC_ALBUM)
		{
			$sql = 'SELECT user_id, username, user_colour
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $album_data['album_user_id'];
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$template->assign_block_vars('navlinks', array(
					'FORUM_NAME'	=> $user->lang['PERSONAL_ALBUMS'],
					'U_VIEW_FORUM'	=> phpbb_gallery_url::append_sid('index', 'mode=personal'),
				));
			}
			$db->sql_freeresult($result);
		}

		// Build navigation links
		if (!empty($album_parents))
		{
			foreach ($album_parents as $parent_album_id => $parent_data)
			{
				list($parent_name, $parent_type) = array_values($parent_data);

				$template->assign_block_vars('navlinks', array(
					'FORUM_NAME'	=> $parent_name,
					'FORUM_ID'		=> $parent_album_id,
					'U_VIEW_FORUM'	=> phpbb_gallery_url::append_sid('album', 'album_id=' . $parent_album_id),
				));
			}
		}

		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $album_data['album_name'],
			'FORUM_ID'		=> $album_data['album_id'],
			'U_VIEW_FORUM'	=> phpbb_gallery_url::append_sid('album', 'album_id=' . $album_data['album_id']),
		));

		$template->assign_vars(array(
			'ALBUM_ID' 		=> $album_data['album_id'],
			'ALBUM_NAME'	=> $album_data['album_name'],
			'ALBUM_DESC'	=> generate_text_for_display($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options']),
			'ALBUM_CONTEST_START'	=> ($album_data['contest_id']) ? sprintf($user->lang['CONTEST_START' . ((($album_data['contest_start']) < time())? 'ED' : 'S')], $user->format_date(($album_data['contest_start']), false, true)) : '',
			'ALBUM_CONTEST_RATING'	=> ($album_data['contest_id']) ? sprintf($user->lang['CONTEST_RATING_START' . ((($album_data['contest_start'] + $album_data['contest_rating']) < time())? 'ED' : 'S')], $user->format_date(($album_data['contest_start'] + $album_data['contest_rating']), false, true)) : '',
			'ALBUM_CONTEST_END'		=> ($album_data['contest_id']) ? sprintf($user->lang['CONTEST_END' . ((($album_data['contest_start'] + $album_data['contest_end']) < time())? 'ED' : 'S')], $user->format_date(($album_data['contest_start'] + $album_data['contest_end']), false, true)) : '',
			'U_VIEW_ALBUM'	=> phpbb_gallery_url::append_sid('album', 'album_id=' . $album_data['album_id']),
		));

		return;
	}

	/**
	* Returns album parents as an array. Get them from album_data if available, or update the database otherwise
	*
	* borrowed from phpBB3
	* @author: phpBB Group
	* @function: get_forum_parents
	*/
	static public function get_parents(&$album_data)
	{
		global $db;

		$album_parents = array();
		if ($album_data['parent_id'] > 0)
		{
			if ($album_data['album_parents'] == '')
			{
				$sql = 'SELECT album_id, album_name, album_type
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE left_id < ' . $album_data['left_id'] . '
						AND right_id > ' . $album_data['right_id'] . '
						AND album_user_id = ' . $album_data['album_user_id'] . '
					ORDER BY left_id ASC';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$album_parents[$row['album_id']] = array($row['album_name'], (int) $row['album_type']);
				}
				$db->sql_freeresult($result);

				$album_data['album_parents'] = serialize($album_parents);

				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
					SET album_parents = '" . $db->sql_escape($album_data['album_parents']) . "'
					WHERE parent_id = " . $album_data['parent_id'];
				$db->sql_query($sql);
			}
			else
			{
				$album_parents = unserialize($album_data['album_parents']);
			}
		}

		return $album_parents;
	}

	/**
	* Obtain list of moderators of each album
	*
	* borrowed from phpBB3
	* @author: phpBB Group
	* @function: get_forum_moderators
	*/
	static public function get_moderators(&$album_moderators, $album_id = false)
	{
		global $auth, $db, $template, $user;

		$album_id_ary = array();

		if ($album_id !== false)
		{
			if (!is_array($album_id))
			{
				$album_id = array($album_id);
			}

			// Exchange key/value pair to be able to faster check for the album id existence
			$album_id_ary = array_flip($album_id);
		}

		$sql_array = array(
			'SELECT'	=> 'm.*, u.user_colour, g.group_colour, g.group_type',
			'FROM'		=> array(GALLERY_MODSCACHE_TABLE => 'm'),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'm.user_id = u.user_id',
				),
				array(
					'FROM'	=> array(GROUPS_TABLE => 'g'),
					'ON'	=> 'm.group_id = g.group_id',
				),
			),

			'WHERE'		=> 'm.display_on_index = 1',
			'ORDER_BY'	=> 'm.group_id ASC, m.user_id ASC',
		);

		// We query every album here because for caching we should not have any parameter.
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql, 3600);

		while ($row = $db->sql_fetchrow($result))
		{
			$a_id = (int) $row['album_id'];

			if (!isset($album_id_ary[$a_id]))
			{
				continue;
			}

			if (!empty($row['user_id']))
			{
				$album_moderators[$a_id][] = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
			}
			else
			{
				$group_name = (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']);

				if ($user->data['user_id'] != ANONYMOUS && !$auth->acl_get('u_viewprofile'))
				{
					$album_moderators[$a_id][] = '<span' . (($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . ';"' : '') . '>' . $group_name . '</span>';
				}
				else
				{
					$album_moderators[$a_id][] = '<a' . (($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . ';"' : '') . ' href="' . phpbb_gallery_url::append_sid('phpbb', 'memberlist', 'mode=group&amp;g=' . $row['group_id']) . '">' . $group_name . '</a>';
				}
			}
		}
		$db->sql_freeresult($result);

		return;
	}

	/**
	* Display albums
	*
	* borrowed from phpBB3
	* @author: phpBB Group
	* @function: display_forums
	*/
	static public function display_albums($root_data = '', $display_moderators = true, $return_moderators = false)
	{
		global $auth, $db, $template, $user;

		$album_rows = $subalbums = $album_ids = $album_ids_moderator = $album_moderators = $active_album_ary = array();
		$parent_id = $visible_albums = 0;
		$sql_from = '';
		$mode = request_var('mode', '');

		// Mark albums read?
		$mark_read = request_var('mark', '');

		if ($mark_read == 'all')
		{
			$mark_read = '';
		}

		if (!$root_data)
		{
			if ($mark_read == 'albums')
			{
				$mark_read = 'all';
			}
			$root_data = array('album_id' => self::PUBLIC_ALBUM);
			$sql_where = 'a.album_user_id = ' . self::PUBLIC_ALBUM;
		}
		else if ($root_data == 'personal')
		{
			if ($mark_read == 'albums')
			{
				$mark_read = 'all';
			}
			$root_data = array('album_id' => 0);//@todo: I think this is incorrect!?
			$sql_where = 'a.album_user_id > ' . self::PUBLIC_ALBUM;
			$num_pegas = phpbb_gallery_config::get('num_pegas');
			$first_char = request_var('first_char', '');
			if ($first_char == 'other')
			{
				// Loop the ASCII: a-z
				for ($i = 97; $i < 123; $i++)
				{
					$sql_where .= ' AND u.username_clean NOT ' . $db->sql_like_expression(chr($i) . $db->any_char);
				}
			}
			else if ($first_char)
			{
				$sql_where .= ' AND u.username_clean ' . $db->sql_like_expression(substr($first_char, 0, 1) . $db->any_char);
			}

			if ($first_char)
			{
				// We do not view all personal albums, so we need to recount, for the pagination.
				$sql_array = array(
					'SELECT'		=> 'count(a.album_id) as pgalleries',
					'FROM'			=> array(GALLERY_ALBUMS_TABLE => 'a'),

					'LEFT_JOIN'		=> array(
						array(
							'FROM'		=> array(USERS_TABLE => 'u'),
							'ON'		=> 'u.user_id = a.album_user_id',
						),
					),

					'WHERE'			=> 'a.parent_id = 0 AND ' . $sql_where,
				);
				$sql = $db->sql_build_query('SELECT', $sql_array);
				$result = $db->sql_query($sql);
				$num_pegas = $db->sql_fetchfield('pgalleries');
				$db->sql_freeresult($result);
			}

			$mode_personal = true;
			$start = request_var('start', 0);
			$limit = phpbb_gallery_config::get('pegas_per_page');
			$template->assign_vars(array(
				'PAGINATION'				=> generate_pagination(phpbb_gallery_url::append_sid('index', 'mode=' . $mode . (($first_char) ? '&amp;first_char=' . $first_char : '')), $num_pegas, $limit, $start),
				'TOTAL_PGALLERIES_SHORT'	=> sprintf($user->lang['TOTAL_PGALLERIES_SHORT'], $num_pegas),
				'PAGE_NUMBER'				=> on_page($num_pegas, $limit, $start),
			));
		}
		else
		{
			$sql_where = 'a.left_id > ' . $root_data['left_id'] . ' AND a.left_id < ' . $root_data['right_id'] . ' AND a.album_user_id = ' . $root_data['album_user_id'];
		}

		$sql_array = array(
			'SELECT'	=> 'a.*, at.mark_time',
			'FROM'		=> array(GALLERY_ALBUMS_TABLE => 'a'),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(GALLERY_ATRACK_TABLE => 'at'),
					'ON'	=> 'at.user_id = ' . $user->data['user_id'] . ' AND a.album_id = at.album_id'
				)
			),

			'ORDER_BY'	=> 'a.album_user_id, a.left_id',
		);

		if (isset($mode_personal))
		{
			$sql_array['LEFT_JOIN'][] = array(
				'FROM'	=> array(USERS_TABLE => 'u'),
				'ON'	=> 'u.user_id = a.album_user_id'
			);
			$sql_array['ORDER_BY'] = 'u.username_clean, a.left_id';
		}
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(GALLERY_CONTESTS_TABLE => 'c'),
			'ON'	=> 'c.contest_album_id = a.album_id'
		);
		$sql_array['SELECT'] = $sql_array['SELECT'] . ', c.contest_marked';


		$sql = $db->sql_build_query('SELECT', array(
			'SELECT'	=> $sql_array['SELECT'],
			'FROM'		=> $sql_array['FROM'],
			'LEFT_JOIN'	=> $sql_array['LEFT_JOIN'],
			'WHERE'		=> $sql_where,
			'ORDER_BY'	=> $sql_array['ORDER_BY'],
		));

		$result = $db->sql_query($sql);

		$album_tracking_info = array();
		$branch_root_id = $root_data['album_id'];
		while ($row = $db->sql_fetchrow($result))
		{
			$album_id = $row['album_id'];

			// Mark albums read?
			if ($mark_read == 'albums' || $mark_read == 'all')
			{
				if (phpbb_gallery::$auth->acl_check('a_list', $album_id, $row['album_user_id']))
				{
					$album_ids[] = $album_id;
					continue;
				}
			}

			// Category with no members
			if (!$row['album_type'] && ($row['left_id'] + 1 == $row['right_id']))
			{
				continue;
			}

			// Skip branch
			if (isset($right_id))
			{
				if ($row['left_id'] < $right_id)
				{
					continue;
				}
				unset($right_id);
			}

			if (!phpbb_gallery::$auth->acl_check('a_list', $album_id, $row['album_user_id']))
			{
				// if the user does not have permissions to list this album, skip everything until next branch
				$right_id = $row['right_id'];
				continue;
			}

			$album_tracking_info[$album_id] = (!empty($row['mark_time'])) ? $row['mark_time'] : phpbb_gallery::$user->get_data('user_lastmark');

			$row['album_images'] = $row['album_images'];
			$row['album_images_real'] = $row['album_images_real'];

			if ($row['parent_id'] == $root_data['album_id'] || $row['parent_id'] == $branch_root_id)
			{
				if ($row['album_type'])
				{
					$album_ids_moderator[] = (int) $album_id;
				}

				// Direct child of current branch
				$parent_id = $album_id;
				$album_rows[$album_id] = $row;

				if (!$row['album_type'] && $row['parent_id'] == $root_data['album_id'])
				{
					$branch_root_id = $album_id;
				}
				$album_rows[$parent_id]['album_id_last_image'] = $row['album_id'];
				$album_rows[$parent_id]['album_type_last_image'] = $row['album_type'];
				$album_rows[$parent_id]['album_contest_marked'] = $row['contest_marked'];
				$album_rows[$parent_id]['orig_album_last_image_time'] = $row['album_last_image_time'];
			}
			else if ($row['album_type'])
			{
				$subalbums[$parent_id][$album_id]['display'] = ($row['display_on_index']) ? true : false;
				$subalbums[$parent_id][$album_id]['name'] = $row['album_name'];
				$subalbums[$parent_id][$album_id]['orig_album_last_image_time'] = $row['album_last_image_time'];
				$subalbums[$parent_id][$album_id]['children'] = array();

				if (isset($subalbums[$parent_id][$row['parent_id']]) && !$row['display_on_index'])
				{
					$subalbums[$parent_id][$row['parent_id']]['children'][] = $album_id;
				}

				$album_rows[$parent_id]['album_images'] += $row['album_images'];
				$album_rows[$parent_id]['album_images_real'] += $row['album_images_real'];

				if ($row['album_last_image_time'] > $album_rows[$parent_id]['album_last_image_time'])
				{
					$album_rows[$parent_id]['album_last_image_id'] = $row['album_last_image_id'];
					$album_rows[$parent_id]['album_last_image_name'] = $row['album_last_image_name'];
					$album_rows[$parent_id]['album_last_image_time'] = $row['album_last_image_time'];
					$album_rows[$parent_id]['album_last_user_id'] = $row['album_last_user_id'];
					$album_rows[$parent_id]['album_last_username'] = $row['album_last_username'];
					$album_rows[$parent_id]['album_last_user_colour'] = $row['album_last_user_colour'];
					$album_rows[$parent_id]['album_type_last_image'] = $row['album_type'];
					$album_rows[$parent_id]['album_contest_marked'] = $row['contest_marked'];
					$album_rows[$parent_id]['album_id_last_image'] = $album_id;
				}
			}
		}
		$db->sql_freeresult($result);

		// Handle marking albums
		if ($mark_read == 'albums' || $mark_read == 'all')
		{
			$redirect = build_url('mark', 'hash');
			$token = request_var('hash', '');
			if (check_link_hash($token, 'global'))
			{
				if ($mark_read == 'all')
				{
					phpbb_gallery_misc::markread('all');
					$message = sprintf($user->lang['RETURN_INDEX'], '<a href="' . $redirect . '">', '</a>');
				}
				else
				{
					phpbb_gallery_misc::markread('albums', $album_ids);
					$message = sprintf($user->lang['RETURN_ALBUM'], '<a href="' . $redirect . '">', '</a>');
				}
				meta_refresh(3, $redirect);
				trigger_error($user->lang['ALBUMS_MARKED'] . '<br /><br />' . $message);
			}
			else
			{
				$message = sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>');
				meta_refresh(3, $redirect);
				trigger_error($message);
			}
		}


		// Grab moderators ... if necessary
		if ($display_moderators)
		{
			if ($return_moderators)
			{
				$album_ids_moderator[] = $root_data['album_id'];
			}
			self::get_moderators($album_moderators, $album_ids_moderator);
		}

		// Used to tell whatever we have to create a dummy category or not.
		$last_catless = true;
		foreach ($album_rows as $row)
		{
			// Empty category
			if (($row['parent_id'] == $root_data['album_id']) && ($row['album_type'] == self::TYPE_CAT))
			{
				$template->assign_block_vars('albumrow', array(
					'S_IS_CAT'				=> true,
					'ALBUM_ID'				=> $row['album_id'],
					'ALBUM_NAME'			=> $row['album_name'],
					'ALBUM_DESC'			=> generate_text_for_display($row['album_desc'], $row['album_desc_uid'], $row['album_desc_bitfield'], $row['album_desc_options']),
					'ALBUM_FOLDER_IMG'		=> '',
					'ALBUM_FOLDER_IMG_SRC'	=> '',
					'ALBUM_IMAGE'			=> ($row['album_image']) ? phpbb_gallery_url::path('phpbb') . $row['album_image'] : '',
					'U_VIEWALBUM'			=> phpbb_gallery_url::append_sid('album', 'album_id=' . $row['album_id']))
				);

				continue;
			}

			$visible_albums++;
			if (($mode == 'personal') && (($visible_albums <= $start) || ($visible_albums > ($start + $limit))))
			{
				continue;
			}

			$album_id = $row['album_id'];
			$album_unread = (isset($album_tracking_info[$album_id]) && ($row['orig_album_last_image_time'] > $album_tracking_info[$album_id]) && ($user->data['user_id'] != ANONYMOUS)) ? true : false;

			$folder_image = $folder_alt = $l_subalbums = '';
			$subalbums_list = array();

			// Generate list of subalbums if we need to
			if (isset($subalbums[$album_id]))
			{
				foreach ($subalbums[$album_id] as $subalbum_id => $subalbum_row)
				{
					$subalbum_unread = (isset($album_tracking_info[$subalbum_id]) && $subalbum_row['orig_album_last_image_time'] > $album_tracking_info[$subalbum_id] && ($user->data['user_id'] != ANONYMOUS)) ? true : false;

					if (!$subalbum_unread && !empty($subalbum_row['children']) && ($user->data['user_id'] != ANONYMOUS))
					{
						foreach ($subalbum_row['children'] as $child_id)
						{
							if (isset($album_tracking_info[$child_id]) && $subalbums[$album_id][$child_id]['orig_album_last_image_time'] > $album_tracking_info[$child_id])
							{
								// Once we found an unread child album, we can drop out of this loop
								$subalbum_unread = true;
								break;
							}
						}
					}

					if ($subalbum_row['display'] && $subalbum_row['name'])
					{
						$subalbums_list[] = array(
							'link'		=> phpbb_gallery_url::append_sid('album', 'album_id=' . $subalbum_id),
							'name'		=> $subalbum_row['name'],
							'unread'	=> $subalbum_unread,
						);
					}
					else
					{
						unset($subalbums[$album_id][$subalbum_id]);
					}

					if ($subalbum_unread)
					{
						$album_unread = true;
					}
				}

				$l_subalbums = (sizeof($subalbums[$album_id]) == 1) ? $user->lang['SUBALBUM'] . ': ' : $user->lang['SUBALBUMS'] . ': ';
				$folder_image = ($album_unread) ? 'forum_unread_subforum' : 'forum_read_subforum';
			}
			else
			{
				$folder_alt = ($album_unread) ? 'NEW_IMAGES' : 'NO_NEW_IMAGES';
				$folder_image = ($album_unread) ? 'forum_unread' : 'forum_read';
			}
			if ($row['album_status'] == self::STATUS_LOCKED)
			{
				$folder_image = ($album_unread) ? 'forum_unread_locked' : 'forum_read_locked';
				$folder_alt = 'ALBUM_LOCKED';
			}

			// Create last post link information, if appropriate
			if ($row['album_last_image_id'])
			{
				$lastimage_name = $row['album_last_image_name'];
				$lastimage_time = $user->format_date($row['album_last_image_time']);
				$lastimage_image_id = $row['album_last_image_id'];
				$lastimage_album_id = $row['album_id_last_image'];
				$lastimage_album_type = $row['album_type_last_image'];
				$lastimage_contest_marked = $row['album_contest_marked'];
				$lastimage_uc_fake_thumbnail = phpbb_gallery_image::generate_link('fake_thumbnail', phpbb_gallery_config::get('link_thumbnail'), $lastimage_image_id, $lastimage_name, $lastimage_album_id);
				$lastimage_uc_thumbnail = phpbb_gallery_image::generate_link('thumbnail', phpbb_gallery_config::get('link_thumbnail'), $lastimage_image_id, $lastimage_name, $lastimage_album_id);
				$lastimage_uc_name = phpbb_gallery_image::generate_link('image_name', phpbb_gallery_config::get('link_image_name'), $lastimage_image_id, $lastimage_name, $lastimage_album_id);
				$lastimage_uc_icon = phpbb_gallery_image::generate_link('lastimage_icon', phpbb_gallery_config::get('link_image_icon'), $lastimage_image_id, $lastimage_name, $lastimage_album_id);
			}
			else
			{
				$lastimage_time = $lastimage_image_id = $lastimage_album_id = $lastimage_album_type = 0;
				$lastimage_name = $lastimage_uc_fake_thumbnail = $lastimage_uc_thumbnail = $lastimage_uc_name = $lastimage_uc_icon = '';
			}

			// Output moderator listing ... if applicable
			$l_moderator = $moderators_list = '';
			if ($display_moderators && !empty($album_moderators[$album_id]))
			{
				$l_moderator = (sizeof($album_moderators[$album_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
				$moderators_list = implode(', ', $album_moderators[$album_id]);
			}

			$s_subalbums_list = array();
			foreach ($subalbums_list as $subalbum)
			{
				$s_subalbums_list[] = '<a href="' . $subalbum['link'] . '" class="subforum ' . (($subalbum['unread']) ? 'unread' : 'read') . '" title="' . (($subalbum['unread']) ? $user->lang['NEW_IMAGES'] : $user->lang['NO_NEW_IMAGES']) . '">' . $subalbum['name'] . '</a>';
			}
			$s_subalbums_list = (string) implode(', ', $s_subalbums_list);
			$catless = ($row['parent_id'] == $root_data['album_id']) ? true : false;

			$template->assign_block_vars('albumrow', array(
				'S_IS_CAT'			=> false,
				'S_NO_CAT'			=> $catless && !$last_catless,
				'S_LOCKED_ALBUM'	=> ($row['album_status'] == self::STATUS_LOCKED) ? true : false,
				'S_LIST_SUBALBUMS'	=> ($row['display_subalbum_list']) ? true : false,
				'S_SUBALBUMS'		=> (sizeof($subalbums_list)) ? true : false,

				'ALBUM_ID'				=> $row['album_id'],
				'ALBUM_NAME'			=> $row['album_name'],
				'ALBUM_DESC'			=> generate_text_for_display($row['album_desc'], $row['album_desc_uid'], $row['album_desc_bitfield'], $row['album_desc_options']),
				'IMAGES'				=> $row['album_images'],
				'UNAPPROVED_IMAGES'		=> (phpbb_gallery::$auth->acl_check('m_status', $album_id, $row['album_user_id'])) ? ($row['album_images_real'] - $row['album_images']) : 0,
				'ALBUM_FOLDER_IMG'		=> $user->img($folder_image, $folder_alt),
				'ALBUM_FOLDER_IMG_SRC'	=> $user->img($folder_image, $folder_alt, false, '', 'src'),
				'ALBUM_FOLDER_IMG_ALT'	=> isset($user->lang[$folder_alt]) ? $user->lang[$folder_alt] : '',
				'ALBUM_IMAGE'			=> ($row['album_image']) ? phpbb_gallery_url::path('phpbb') . $row['album_image'] : '',
				'LAST_IMAGE_TIME'		=> $lastimage_time,
				'LAST_USER_FULL'		=> (($lastimage_album_type == self::TYPE_CONTEST) && ($lastimage_contest_marked && !phpbb_gallery::$auth->acl_check('m_status', $album_id, $row['album_user_id']))) ? $user->lang['CONTEST_USERNAME'] : get_username_string('full', $row['album_last_user_id'], $row['album_last_username'], $row['album_last_user_colour']),
				'UC_THUMBNAIL'			=> (phpbb_gallery_config::get('mini_thumbnail_disp')) ? $lastimage_uc_thumbnail : '',
				'UC_FAKE_THUMBNAIL'		=> (phpbb_gallery_config::get('mini_thumbnail_disp')) ? $lastimage_uc_fake_thumbnail : '',
				'UC_IMAGE_NAME'			=> $lastimage_uc_name,
				'UC_LASTIMAGE_ICON'		=> $lastimage_uc_icon,
				'ALBUM_COLOUR'			=> get_username_string('colour', $row['album_last_user_id'], $row['album_last_username'], $row['album_last_user_colour']),
				'MODERATORS'			=> $moderators_list,
				'SUBALBUMS'				=> $s_subalbums_list,

				'L_SUBALBUM_STR'		=> $l_subalbums,
				'L_ALBUM_FOLDER_ALT'	=> $folder_alt,
				'L_MODERATOR_STR'		=> $l_moderator,

				'U_VIEWALBUM'			=> phpbb_gallery_url::append_sid('album', 'album_id=' . $row['album_id']),
			));

			// Assign subforums loop for style authors
			foreach ($subalbums_list as $subalbum)
			{
				$template->assign_block_vars('albumrow.subalbum', array(
					'U_SUBALBUM'	=> $subalbum['link'],
					'SUBALBUM_NAME'	=> $subalbum['name'],
					'S_UNREAD'		=> $subalbum['unread'],
				));
			}

			$last_catless = $catless;
		}

		$template->assign_vars(array(
			'U_MARK_ALBUMS'		=> ($user->data['is_registered']) ? phpbb_gallery_url::append_sid('album', 'hash=' . generate_link_hash('global') . '&amp;album_id=' . $root_data['album_id'] . '&amp;mark=albums') : '',
			'S_HAS_SUBALBUM'	=> ($visible_albums) ? true : false,
			'L_SUBFORUM'		=> ($visible_albums == 1) ? $user->lang['SUBALBUM'] : $user->lang['SUBALBUMS'],
			'LAST_POST_IMG'		=> $user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
			'FAKE_THUMB_SIZE'	=> phpbb_gallery_config::get('mini_thumbnail_size'),
		));

		if ($return_moderators)
		{
			return array($active_album_ary, $album_moderators);
		}

		return array($active_album_ary, array());
	}

	/**
	* Generate personal album for user, when moving image into it
	*/
	public static function generate_personal_album($album_name, $user_id, $user_colour, $gallery_user)
	{
		global $cache, $db;

		$album_data = array(
			'album_name'					=> $album_name,
			'parent_id'						=> 0,
			//left_id and right_id default by db
			'album_desc_options'			=> 7,
			'album_desc'					=> '',
			'album_parents'					=> '',
			'album_type'					=> self::TYPE_UPLOAD,
			'album_status'					=> self::STATUS_OPEN,
			'album_user_id'					=> $user_id,
			'album_last_username'			=> '',
			'album_last_user_colour'		=> $user_colour,
		);
		$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
		$personal_album_id = $db->sql_nextid();

		$gallery_user->update_data(array(
				'personal_album_id'	=> $personal_album_id,
		));

		phpbb_gallery_config::inc('num_pegas', 1);

		// Update the config for the statistic on the index
		phpbb_gallery_config::set('newest_pega_user_id', $user_id);
		phpbb_gallery_config::set('newest_pega_username', $album_name);
		phpbb_gallery_config::set('newest_pega_user_colour', $user_colour);
		phpbb_gallery_config::set('newest_pega_album_id', $personal_album_id);

		$cache->destroy('_albums');
		$cache->destroy('sql', GALLERY_ALBUMS_TABLE);

		return $personal_album_id;
	}
}
