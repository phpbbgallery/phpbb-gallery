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

class phpbb_gallery_auth
{
	const SETTING_PERMISSIONS	= -39839;
	const PERSONAL_ALBUM		= -3;
	const OWN_ALBUM				= -2;
	const PUBLIC_ALBUM			= 0;

	// ACL - slightly different
	const ACL_NO		= 0;
	const ACL_YES		= 1;
	const ACL_NEVER		= 2;

	static private $_permission_i = array('i_view', 'i_watermark', 'i_upload', 'i_approve', 'i_edit', 'i_delete', 'i_report', 'i_rate');
	static private $_permission_c = array('c_read', 'c_post', 'c_edit', 'c_delete');
	static private $_permission_m = array('m_comments', 'm_delete', 'm_edit', 'm_move', 'm_report', 'm_status');
	static private $_permission_misc = array('a_list', 'i_count', 'i_unlimited', 'album_count', 'album_unlimited');
	static private $_permissions = array();
	static private $_permissions_flipped = array();

	private $_auth_data = array();
	private $_auth_data_never = array();

	private $acl_cache = array();

	/**
	* Create a auth-object for a given user
	*
	* @param	int		$user_id	User you want the permissions from.
	* @param	int		$album_id	Only get the permissions for a given album_id. Should save some memory. // Not yet implemented.
	*/
	public function phpbb_gallery_auth($user_id, $album_id = false)
	{
		self::$_permissions = array_merge(self::$_permission_i, self::$_permission_c, self::$_permission_m, self::$_permission_misc);
		self::$_permissions_flipped = array_flip(array_merge(self::$_permissions, array('m_')));
		self::$_permissions_flipped['i_count'] = 'i_count';
		self::$_permissions_flipped['a_count'] = 'a_count';

		global $user;

		$cached_permissions = phpbb_gallery::$user->get_data('user_permissions');
		if (($user_id == $user->data['user_id']) && !empty($cached_permissions))
		{
			$this->unserialize_auth_data($cached_permissions);
			return;
		}
		else if ($user_id != $user->data['user_id'])
		{
			$permissions_user = phpbb_gallery_user::get_settings($user_id);
			if (!empty($permissions_user['user_permissions']))
			{
				$this->unserialize_auth_data($permissions_user['user_permissions']);
				return;
			}
		}
		$this->query_auth_data($user_id);
	}

	/**
	* Query the permissions for a given user and store them in the database.
	*/
	private function query_auth_data($user_id)
	{
		global $cache, $config, $db, $user;

		$albums = $cache->obtain_album_list();
		$user_groups_ary = self::get_usergroups($user_id);

		$sql_select = '';
		foreach (self::$_permissions as $permission)
		{
			$sql_select .= " MAX($permission) as $permission,";
		}

		$this->_auth_data[self::OWN_ALBUM]				= new phpbb_gallery_auth_set();
		$this->_auth_data_never[self::OWN_ALBUM]		= new phpbb_gallery_auth_set();
		$this->_auth_data[self::PERSONAL_ALBUM]			= new phpbb_gallery_auth_set();
		$this->_auth_data_never[self::PERSONAL_ALBUM]	= new phpbb_gallery_auth_set();

		foreach ($albums as $album)
		{
			if ($album['album_user_id'] == self::PUBLIC_ALBUM)
			{
				$this->_auth_data[$album['album_id']]		= new phpbb_gallery_auth_set();
				$this->_auth_data_never[$album['album_id']]	= new phpbb_gallery_auth_set();
			}
		}

		$sql_array = array(
			'SELECT'		=> "p.perm_album_id, $sql_select p.perm_system",
			'FROM'			=> array(GALLERY_PERMISSIONS_TABLE => 'p'),

			'LEFT_JOIN'		=> array(
				array(
					'FROM'		=> array(GALLERY_ROLES_TABLE => 'pr'),
					'ON'		=> 'p.perm_role_id = pr.role_id',
				),
			),

			'WHERE'			=> 'p.perm_user_id = ' . $user_id . ' OR ' . $db->sql_in_set('p.perm_group_id', $user_groups_ary, false, true),
			'GROUP_BY'		=> 'p.perm_system, p.perm_album_id',
			'ORDER_BY'		=> 'p.perm_system DESC, p.perm_album_id ASC',
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			switch ($row['perm_system'])
			{
				case self::PERSONAL_ALBUM:
					$this->store_acl_row(self::PERSONAL_ALBUM, $row);
				break;

				case self::OWN_ALBUM:
					$this->store_acl_row(self::OWN_ALBUM, $row);
				break;

				case self::PUBLIC_ALBUM:
					$this->store_acl_row(((int) $row['perm_album_id']), $row);
				break;
			}
		}
		$db->sql_freeresult($result);

		$this->merge_acl_row();

		$this->set_user_permissions($user_id, $this->_auth_data);
	}

	/**
	* Serialize the auth-data sop we can store it.
	*
	* Line-Format:	bitfields:i_count:a_count::album_id(s)
	* Samples:		8912837:0:10::-3
	*				9961469:20:0::1:23:42
	*/
	private function serialize_auth_data($auth_data)
	{
		$acl_array = array();

		foreach ($auth_data as $a_id => $obj)
		{
			$key = $obj->get_bits() . ':' . $obj->get_count('i_count') . ':' . $obj->get_count('a_count');
			if (!isset($acl_array[$key]))
			{
				$acl_array[$key] = $key . '::' . $a_id;
			}
			else
			{
				$acl_array[$key] .= ':' . $a_id;
			}
		}

		return implode("\n", $acl_array);
	}

	/**
	* Unserialize the stored auth-data
	*/
	private function unserialize_auth_data($serialized_data)
	{
		$acl_array = explode("\n", $serialized_data);

		foreach ($acl_array as $acl_row)
		{
			list ($acls, $a_ids) = explode('::', $acl_row);
			list ($bits, $i_count, $a_count) = explode(':', $acls);

			foreach (explode(':', $a_ids) as $a_id)
			{
				$this->_auth_data[$a_id] = new phpbb_gallery_auth_set($bits, $i_count, $a_count);
			}
		}
	}

	/**
	* Stores an acl-row into the _auth_data-array.
	*/
	private function store_acl_row($album_id, $data)
	{
		foreach (self::$_permissions as $permission)
		{
			if (strpos('_count', $permission) === false)
			{
				if ($data[$permission] == self::ACL_NEVER)
				{
					$this->_auth_data_never[$album_id]->set_bit(self::$_permissions_flipped[$permission], true);
				}
				else if ($data[$permission] == self::ACL_YES)
				{
					$this->_auth_data[$album_id]->set_bit(self::$_permissions_flipped[$permission], true);
					if (substr($permission, 0, 2) == 'm_')
					{
						$this->_auth_data[$album_id]->set_bit(self::$_permissions_flipped['m_'], true);
					}
				}
			}
			else
			{
				$this->_auth_data[$album_id]->set_count($permission, $data[$permission]);
			}
		}
	}

	/**
	* Merge the NEVER-options into the YES-options by removing the YES, if it is set.
	*/
	private function merge_acl_row()
	{
		foreach ($this->_auth_data as $album_id => $obj)
		{
			foreach (self::$_permissions as $acl)
			{
				if (strpos('_count', $acl) === false)
				{
					$bit = self::$_permissions_flipped[$acl];
					// If the yes and the never bit are set, we overwrite the yes with a false.
					if ($obj->get_bit($bit) && $this->_auth_data_never[$album_id]->get_bit($bit))
					{
						$obj->set_bit($bit, false);
					}
				}
			}
		}
	}

	/**
	* Get groups a user is member from.
	*/
	static public function get_usergroups($user_id)
	{
		global $config, $db;

		$groups_ary = array();
		// Only available in >= 3.0.6
		if (version_compare($config['version'], '3.0.5', '>'))
		{
			$sql = 'SELECT ug.group_id
				FROM ' . USER_GROUP_TABLE . ' ug
				LEFT JOIN ' . GROUPS_TABLE . ' g
					ON (ug.group_id = g.group_id)
				WHERE ug.user_id = ' . (int) $user_id . '
					AND ug.user_pending = 0
					AND g.group_skip_auth = 0';
		}
		else
		{
			$sql = 'SELECT group_id
				FROM ' . USER_GROUP_TABLE . '
				WHERE user_id = ' . (int) $user_id . '
					AND user_pending = 0';
		}
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$groups_ary[] = $row['group_id'];
		}
		$db->sql_freeresult($result);

		return $groups_ary;
	}

	/**
	* Sets the permissions-cache in users-table to given array.
	*/
	static public function set_user_permissions($user_ids, $permissions = false)
	{
		global $db;

		$sql_set = (is_array($permissions)) ? $db->sql_escape(self::serialize_auth_data($permissions)) : '';
		$sql_where = '';
		if (is_array($user_ids))
		{
			$sql_where = 'WHERE ' . $db->sql_in_set('user_id', array_map('intval', $user_ids));
		}
		elseif ($user_ids == 'all')
		{
			$sql_where = '';
		}
		else
		{
			$sql_where = 'WHERE user_id = ' . (int) $user_ids;
		}

		$sql = 'UPDATE ' . GALLERY_USERS_TABLE . "
			SET user_permissions = '" . $sql_set . "'
			" . $sql_where;
		$db->sql_query($sql);
	}

	/**
	* Get permission
	*
	* @param	string	$acl	One of the permissions, Exp: i_view
	* @param	int		$a_id	The album_id, from which we want to have the permissions
	* @param	int		$u_id	The user_id from the album-owner. If not specified we need to get it from the cache.
	*
	* @return	bool			Is the user allowed to do the $acl?
	*/
	public function acl_check($acl, $a_id, $u_id = -1)
	{
		$bit = self::$_permissions_flipped[$acl];
		if ($bit < 0)
		{
			$bit = $acl;
		}

		if (isset($this->acl_cache[$a_id][$bit]))
		{
			return $this->acl_cache[$a_id][$bit];
		}

		// Do we have a function call without $album_user_id ?
		if (($u_id < self::PUBLIC_ALBUM) && ($a_id > 0))
		{
			static $_album_list;
			// Yes, from viewonline.php
			if (!$_album_list)
			{
				global $cache;
				$_album_list = $cache->obtain_album_list();
			}
			if (!isset($_album_list[$a_id]))
			{
				// Do not give permissions, if the album does not exist.
				return false;
			}
			$u_id = $_album_list[$a_id]['album_user_id'];
		}

		$get_acl = 'get_bit';
		if (!is_int($bit))
		{
			$get_acl = 'get_count';
		}

		$p_id = $a_id;
		if ($u_id)
		{
			global $user;

			if ($u_id == $user->data['user_id'])
			{
				$p_id = self::OWN_ALBUM;
			}
			else
			{
				$p_id = self::PERSONAL_ALBUM;
			}
		}

		if (isset($this->_auth_data[$p_id]))
		{
			$this->acl_cache[$a_id][$bit] = $this->_auth_data[$p_id]->$get_acl($bit);
			return $this->acl_cache[$a_id][$bit];
		}
		return false;
	}

	/**
	* Get albums by permission
	*
	* @param	string	$acl			One of the permissions, Exp: i_view; *_count permissions are not allowed!
	* @param	string	$return			Type of the return value. array returns an array, else it's a string.
	* @param	bool	$display_in_rrc	Only return albums, that have the display_in_rrc-flag set.
	* @param	bool	$display_pegas	Include personal galleries in the list.
	*
	* @return	mixed					$album_ids, either as list or array.
	*/
	public function acl_album_ids($acl, $return = 'array', $display_in_rrc = false, $display_pegas = true)
	{
		global $user, $cache;

		$bit = self::$_permissions_flipped[$acl];
		if (!is_int($bit))
		{
			// No support for *_count permissions.
			return ($mode == 'array') ? array() : '';
		}

		$album_list = '';
		$album_array = array();
		$albums = $cache->obtain_album_list();
		foreach ($albums as $album)
		{
			if ($album['album_user_id'] == $user->data['user_id'])
			{
				$a_id = self::OWN_ALBUM;
			}
			else if ($album['album_user_id'] > self::PUBLIC_ALBUM)
			{
				$a_id = self::PERSONAL_ALBUM;
			}
			else
			{
				$a_id = $album['album_id'];
			}
			if ($this->_auth_data[$a_id]->get_bit($bit) && (!$display_in_rrc || ($display_in_rrc && $album['display_in_rrc'])) && ($display_pegas || ($album['album_user_id'] == self::PUBLIC_ALBUM)))
			{
				$album_list .= (($album_list) ? ', ' : '') . $album['album_id'];
				$album_array[] = (int) $album['album_id'];
			}
		}

		return ($return == 'array') ? $album_array : $album_list;
	}

	/**
	* User authorisation levels output
	*
	* @param	string	$mode			Can only be 'album' so far.
	* @param	int		$album_id		The current album the user is in.
	* @param	int		$album_status	The albums status bit.
	* @param	int		$album_user_id	The user-id of the album owner. Saves us a call to the cache if it is set.
	*
	* borrowed from phpBB3
	* @author: phpBB Group
	* @function: gen_forum_auth_level
	*/
	public function gen_auth_level($mode, $album_id, $album_status, $album_user_id = -1)
	{
		global $template, $user;

		$locked = ($album_status == ITEM_LOCKED && !gallery_acl_check('m_', $album_id, $album_user_id)) ? true : false;

		$rules = array(
			($this->acl_check('i_view', $album_id, $album_user_id) && !$locked) ? $user->lang['ALBUM_VIEW_CAN'] : $user->lang['ALBUM_VIEW_CANNOT'],
			($this->acl_check('i_upload', $album_id, $album_user_id) && !$locked) ? $user->lang['ALBUM_UPLOAD_CAN'] : $user->lang['ALBUM_UPLOAD_CANNOT'],
			($this->acl_check('i_edit', $album_id, $album_user_id) && !$locked) ? $user->lang['ALBUM_EDIT_CAN'] : $user->lang['ALBUM_EDIT_CANNOT'],
			($this->acl_check('i_delete', $album_id, $album_user_id) && !$locked) ? $user->lang['ALBUM_DELETE_CAN'] : $user->lang['ALBUM_DELETE_CANNOT'],
		);
		if (phpbb_gallery_config::get('allow_comments') && $this->acl_check('c_read', $album_id, $album_user_id))
		{
			$rules[] = ($this->acl_check('c_post', $album_id, $album_user_id) && !$locked) ? $user->lang['ALBUM_COMMENT_CAN'] : $user->lang['ALBUM_COMMENT_CANNOT'];
		}
		if (phpbb_gallery_config::get('allow_rates'))
		{
			$rules[] = ($this->acl_check('i_rate', $album_id, $album_user_id) && !$locked) ? $user->lang['ALBUM_RATE_CAN'] : $user->lang['ALBUM_RATE_CANNOT'];
		}

		foreach ($rules as $rule)
		{
			$template->assign_block_vars('rules', array('RULE' => $rule));
		}

		return;
	}
}
