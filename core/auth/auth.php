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

class phpbb_ext_gallery_core_auth
{
	const SETTING_PERMISSIONS	= -39839;
	const PERSONAL_ALBUM		= -3;
	const OWN_ALBUM				= -2;
	const PUBLIC_ALBUM			= 0;

	const ACCESS_ALL			= 0;
	const ACCESS_REGISTERED	= 1;
	const ACCESS_NOT_FOES		= 2;
	const ACCESS_FRIENDS		= 3;

	// ACL - slightly different
	const ACL_NO		= 0;
	const ACL_YES		= 1;
	const ACL_NEVER		= 2;

	static private $_permission_i = array('i_view', 'i_watermark', 'i_upload', 'i_approve', 'i_edit', 'i_delete', 'i_report', 'i_rate');
	static private $_permission_c = array('c_read', 'c_post', 'c_edit', 'c_delete');
	static private $_permission_m = array('m_comments', 'm_delete', 'm_edit', 'm_move', 'm_report', 'm_status');
	static private $_permission_misc = array('a_list', 'i_count', 'i_unlimited', 'a_count', 'a_unlimited', 'a_restrict');
	static private $_permissions = array();
	static private $_permissions_flipped = array();

	private $_auth_data = array();
	private $_auth_data_never = array();

	private $acl_cache = array();

	private $cache;
	private $user;
	private $phpbb_db;
	private $phpbb_template;
	private $phpbb_user;

	/**
	* Create a auth-object for a given user
	*
	* @param	int		$user_id	User you want the permissions from.
	* @param	int		$album_id	Only get the permissions for a given album_id. Should save some memory. // Not yet implemented.
	*/
	public function __construct(phpbb_ext_gallery_core_cache $cache, phpbb_ext_gallery_core_user $user, dbal $db, phpbb_template $template, phpbb_user $phpbb_user, $user_id, $album_id = false)
	{
		self::$_permissions = array_merge(self::$_permission_i, self::$_permission_c, self::$_permission_m, self::$_permission_misc);
		self::$_permissions_flipped = array_flip(array_merge(self::$_permissions, array('m_')));
		self::$_permissions_flipped['i_count'] = 'i_count';
		self::$_permissions_flipped['a_count'] = 'a_count';

		$this->cache = $cache;
		$this->user = $user;
		$this->phpbb_db = $db;
		$this->phpbb_template = $template;
		$this->phpbb_user = $phpbb_user;

		$cached_permissions = $this->user->get_data('user_permissions');
		if (($user_id == $this->phpbb_user->data['user_id']) && !empty($cached_permissions))
		{
			$this->unserialize_auth_data($cached_permissions);
			return;
		}
		else if ($user_id != $this->phpbb_user->data['user_id'])
		{
			$permissions_user = new phpbb_ext_gallery_core_user($this->phpbb_db, $user_id);
			$cached_permissions = $permissions_user->get_data('user_permissions');
			if (!empty($cached_permissions))
			{
				$this->unserialize_auth_data($cached_permissions);
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
		$albums = $this->cache->get('albums');
		$user_groups_ary = $this->get_usergroups($user_id);

		$sql_select = '';
		foreach (self::$_permissions as $permission)
		{
			$sql_select .= " MAX($permission) as $permission,";
		}

		$this->_auth_data[self::OWN_ALBUM]				= new phpbb_ext_gallery_core_auth_set();
		$this->_auth_data_never[self::OWN_ALBUM]		= new phpbb_ext_gallery_core_auth_set();
		$this->_auth_data[self::PERSONAL_ALBUM]			= new phpbb_ext_gallery_core_auth_set();
		$this->_auth_data_never[self::PERSONAL_ALBUM]	= new phpbb_ext_gallery_core_auth_set();

		foreach ($albums as $album)
		{
			if ($album['album_user_id'] == self::PUBLIC_ALBUM)
			{
				$this->_auth_data[$album['album_id']]		= new phpbb_ext_gallery_core_auth_set();
				$this->_auth_data_never[$album['album_id']]	= new phpbb_ext_gallery_core_auth_set();
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

			'WHERE'			=> 'p.perm_user_id = ' . $user_id . ' OR ' . $this->phpbb_db->sql_in_set('p.perm_group_id', $user_groups_ary, false, true),
			'GROUP_BY'		=> 'p.perm_system, p.perm_album_id',
			'ORDER_BY'		=> 'p.perm_system DESC, p.perm_album_id ASC',
		);
		$sql = $this->phpbb_db->sql_build_query('SELECT', $sql_array);

		$this->phpbb_db->sql_return_on_error(true);
		$result = $this->phpbb_db->sql_query($sql);
		if ($this->phpbb_db->sql_error_triggered)
		{
			trigger_error('DATABASE_NOT_UPTODATE');
		}
		$this->phpbb_db->sql_return_on_error(false);

		while ($row = $this->phpbb_db->sql_fetchrow($result))
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
		$this->phpbb_db->sql_freeresult($result);

		$this->merge_acl_row();

		$this->restrict_pegas($user_id);

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
				$this->_auth_data[$a_id] = new phpbb_ext_gallery_core_auth_set($bits, $i_count, $a_count);
			}
		}
	}

	/**
	* Stores an acl-row into the _auth_data-array.
	*/
	private function store_acl_row($album_id, $data)
	{
		if (!isset($this->_auth_data[$album_id]))
		{
			// The album we have permissions for does not exist any more, so do nothing.
			return;
		}

		foreach (self::$_permissions as $permission)
		{
			if (strpos($permission, '_count') === false)
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
	* Restrict the access to personal galleries, if the user is not a moderator.
	*/
	private function restrict_pegas($user_id)
	{
		if (($user_id != ANONYMOUS) && $this->_auth_data[self::PERSONAL_ALBUM]->get_bit(self::$_permissions_flipped['m_']))
		{
			// No restrictions for moderators.
			return;
		}

		$zebra = null;

		$albums = $this->cache->get('albums');

		foreach ($albums as $album)
		{
			if (!$album['album_auth_access'] || ($album['album_user_id'] == self::PUBLIC_ALBUM))# || ($album['album_user_id'] == $user_id))
			{
				continue;
			}
			else if ($user_id == ANONYMOUS)
			{
				// Level 1: No guests
				$this->_auth_data[$album['album_id']] = new phpbb_gallery_auth_set();
				continue;
			}
			else if ($album['album_auth_access'] == self::ACCESS_NOT_FOES)
			{
				if ($zebra == null)
				{
					$zebra = self::get_user_zebra($user_id);
				}
				if (in_array($album['album_user_id'], $zebra['foe']))
				{
					// Level 2: No foes allowed
					$this->_auth_data[$album['album_id']] = new phpbb_gallery_auth_set();
					continue;
				}
			}
			else if ($album['album_auth_access'] == self::ACCESS_FRIENDS)
			{
				if ($zebra == null)
				{
					$zebra = self::get_user_zebra($user_id);
				}
				if (!in_array($album['album_user_id'], $zebra['friend']))
				{
					// Level 3: Only friends allowed
					$this->_auth_data[$album['album_id']] = new phpbb_gallery_auth_set();
					continue;
				}
			}
		}
	}

	/**
	* Get the users, which added our user as friend and/or foe
	*/
	static public function get_user_zebra($user_id)
	{
		$zebra = array('foe' => array(), 'friend' => array());
		$sql = 'SELECT *
			FROM ' . ZEBRA_TABLE . '
			WHERE zebra_id = ' . (int) $user_id;
		$result = $this->phpbb_db->sql_query($sql);
		while ($row = $this->phpbb_db->sql_fetchrow($result))
		{
			if ($row['foe'])
			{
				$zebra['foe'][] = (int) $row['user_id'];
			}
			else
			{
				$zebra['friend'][] = (int) $row['user_id'];
			}
		}
		$this->phpbb_db->sql_freeresult($result);
		return $zebra;
	}

	/**
	* Get groups a user is member from.
	*/
	public function get_usergroups($user_id)
	{
		$groups_ary = array();

		$sql = 'SELECT ug.group_id
			FROM ' . USER_GROUP_TABLE . ' ug
			LEFT JOIN ' . GROUPS_TABLE . ' g
				ON (ug.group_id = g.group_id)
			WHERE ug.user_id = ' . (int) $user_id . '
				AND ug.user_pending = 0
				AND g.group_skip_auth = 0';
		$result = $this->phpbb_db->sql_query($sql);

		while ($row = $this->phpbb_db->sql_fetchrow($result))
		{
			$groups_ary[] = $row['group_id'];
		}
		$this->phpbb_db->sql_freeresult($result);

		return $groups_ary;
	}

	/**
	* Sets the permissions-cache in users-table to given array.
	*/
	public function set_user_permissions($user_ids, $permissions = false)
	{
		$sql_set = (is_array($permissions)) ? $this->phpbb_db->sql_escape(self::serialize_auth_data($permissions)) : '';
		$sql_where = '';
		if (is_array($user_ids))
		{
			$sql_where = 'WHERE ' . $this->phpbb_db->sql_in_set('user_id', array_map('intval', $user_ids));
		}
		elseif ($user_ids == 'all')
		{
			$sql_where = '';
		}
		else
		{
			$sql_where = 'WHERE user_id = ' . (int) $user_ids;
		}

		if (isset($this->phpbb_user) && isset($this->user))
		{
			if ($user_ids == $this->phpbb_user->data['user_id'])
			{
				$this->user->set_permissions_changed(time());
			}
		}

		$sql = 'UPDATE ' . GALLERY_USERS_TABLE . "
			SET user_permissions = '" . $sql_set . "',
				user_permissions_changed = " . time() . '
			' . $sql_where;
		$this->phpbb_db->sql_query($sql);
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
				$_album_list = $this->cache->get('albums');
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
			if ($u_id == $this->phpbb_user->data['user_id'])
			{
				$p_id = self::OWN_ALBUM;
			}
			else
			{
				if (!isset($this->_auth_data[$a_id]))
				{
					$p_id = self::PERSONAL_ALBUM;
				}
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
	* Does the user have the permission for any album?
	*
	* @param	string	$acl			One of the permissions, Exp: i_view; *_count permissions are not allowed!
	*
	* @return	bool			Is the user allowed to do the $acl?
	*/
	public function acl_check_global($acl)
	{
		$bit = self::$_permissions_flipped[$acl];
		if (!is_int($bit))
		{
			// No support for *_count permissions.
			return false;
		}

		if ($this->_auth_data[self::OWN_ALBUM]->get_bit($bit))
		{
			return true;
		}
		if ($this->_auth_data[self::PERSONAL_ALBUM]->get_bit($bit))
		{
			return true;
		}

		$albums = $this->cache->get('albums');
		foreach ($albums as $album)
		{
			if (!$album['album_user_id'] && $this->_auth_data[$album['album_id']]->get_bit($bit))
			{
				return true;
			}
		}

		return false;
	}

	/**
	* Get albums by permission
	*
	* @param	string	$acl			One of the permissions, Exp: i_view; *_count permissions are not allowed!
	* @param	string	$return			Type of the return value. array returns an array, else it's a string.
	*									bool means it only checks whether the user has the permission anywhere.
	* @param	bool	$display_in_rrc	Only return albums, that have the display_in_rrc-flag set.
	* @param	bool	$display_pegas	Include personal galleries in the list.
	*
	* @return	mixed					$album_ids, either as list or array.
	*/
	public function acl_album_ids($acl, $return = 'array', $display_in_rrc = false, $display_pegas = true)
	{
		$bit = self::$_permissions_flipped[$acl];
		if (!is_int($bit))
		{
			// No support for *_count permissions.
			return ($mode == 'array') ? array() : '';
		}

		$album_list = '';
		$album_array = array();
		$albums = $this->cache->get('albums');
		foreach ($albums as $album)
		{
			if ($album['album_user_id'] == $this->phpbb_user->data['user_id'])
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
				if ($return == 'bool')
				{
					return true;
				}
				$album_list .= (($album_list) ? ', ' : '') . $album['album_id'];
				$album_array[] = (int) $album['album_id'];
			}
		}

		if ($return == 'bool')
		{
			return false;
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
		global $phpbb_ext_gallery;
		$locked = ($album_status == ITEM_LOCKED && !$this->acl_check('m_', $album_id, $album_user_id)) ? true : false;

		$rules = array(
			($this->acl_check('i_view', $album_id, $album_user_id) && !$locked) ? $this->phpbb_user->lang['ALBUM_VIEW_CAN'] : $this->phpbb_user->lang['ALBUM_VIEW_CANNOT'],
			($this->acl_check('i_upload', $album_id, $album_user_id) && !$locked) ? $this->phpbb_user->lang['ALBUM_UPLOAD_CAN'] : $this->phpbb_user->lang['ALBUM_UPLOAD_CANNOT'],
			($this->acl_check('i_edit', $album_id, $album_user_id) && !$locked) ? $this->phpbb_user->lang['ALBUM_EDIT_CAN'] : $this->phpbb_user->lang['ALBUM_EDIT_CANNOT'],
			($this->acl_check('i_delete', $album_id, $album_user_id) && !$locked) ? $this->phpbb_user->lang['ALBUM_DELETE_CAN'] : $this->phpbb_user->lang['ALBUM_DELETE_CANNOT'],
		);
		if ($phpbb_ext_gallery->config->get('allow_comments') && $this->acl_check('c_read', $album_id, $album_user_id))
		{
			$rules[] = ($this->acl_check('c_post', $album_id, $album_user_id) && !$locked) ? $this->phpbb_user->lang['ALBUM_COMMENT_CAN'] : $this->phpbb_user->lang['ALBUM_COMMENT_CANNOT'];
		}
		if ($phpbb_ext_gallery->config->get('allow_rates'))
		{
			$rules[] = ($this->acl_check('i_rate', $album_id, $album_user_id) && !$locked) ? $this->phpbb_user->lang['ALBUM_RATE_CAN'] : $this->phpbb_user->lang['ALBUM_RATE_CANNOT'];
		}

		foreach ($rules as $rule)
		{
			$this->phpbb_template->assign_block_vars('rules', array('RULE' => $rule));
		}

		return;
	}
}
