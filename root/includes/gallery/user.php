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

class phpbb_gallery_user
{
	/**
	* phpBB-user_id
	*/
	public $id = 0;

	/**
	* phpBB database object
	*/
	private $db = null;

	/**
	* Do we have an entry for the user in the table?
	*/
	public $entry_exists = null;

	/**
	* Users data in the table
	*/
	private $data = array();

	/**
	* Constructor
	*
	* @param	int		$user_id
	* @param	bool	$load		Shall we automatically load the users data from the database?
	*/
	public function __construct($db, $user_id, $load = true)
	{
		$this->db			= $db;
		$this->id			= (int) $user_id;
		if ($load)
		{
			$this->load_data();
		}
	}

	/**
	* Load the users data from the database and cast it...
	*/
	public function load_data()
	{
		$sql = 'SELECT *
			FROM ' . GALLERY_USERS_TABLE . '
			WHERE user_id = ' . $this->id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->entry_exists	= false;
		if ($row !== false)
		{
			$this->data			= $this->validate_data($row);
			$this->entry_exists	= true;
		}
	}

	/**
	* Some functions need the data to be loaded or at least checked.
	* So here we loaded if it is not laoded yet and we need it ;)
	*/
	public function force_load()
	{
		if (is_null($this->entry_exists))
		{
			$this->load_data();
		}
	}

	/**
	* Get user-setting, if the user does not have his own settings we fall back to default.
	*
	* @param	string	$key	Column name from the users-table
	* @return	mixed			Returns the value of the column, it it does not exist it returns false.
	*/
	public function get_data($key)
	{
		if (isset($this->data[$key]))
		{
			return $this->data[$key];
		}
		elseif (isset(self::$default_values[$key]))
		{
			return self::$default_values[$key];
		}

		return false;
	}

	/**
	* Updates/Inserts the data, depending on whether the user already exists or not.
	*	Example: 'SET key = x'
	*/
	public function update_data($data)
	{
		$this->force_load();

		$suc = false;
		if ($this->entry_exists)
		{
			$suc = $this->update($data);
		}

		if (($suc === false) || !$this->entry_exists)
		{
			$suc = $this->insert($data);
		}

		return $suc;
	}

	/**
	* Increase/Inserts the data, depending on whether the user already exists or not.
	*	Example: 'SET key = key + x'
	*/
	public function update_images($num)
	{
		$suc = false;
		if ($this->entry_exists || is_null($this->entry_exists))
		{
			$suc = $this->update_image_count($num);
			if ($suc === false)
			{
				$suc = $this->update(array('user_images' => max(0, $num)));
			}
		}

		if ($suc === false)
		{
			$suc = $this->insert(array('user_images' => max(0, $num)));
		}

		return $suc;
	}

	/**
	* Updates the users table with the new data.
	*
	* @param	array	$data	Array of data we want to add/update.
	* @return	bool			Returns true if the columns were updated successfully
	*/
	private function update($data)
	{
		$sql_ary = array_merge($this->validate_data($data), array(
			'user_last_update'	=> time(),
		));
		unset($sql_ary['user_id']);

		$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE user_id = ' . $this->id;
		$this->db->sql_query($sql);

		$this->data = array_merge($this->data, $sql_ary);

		return ($this->db->sql_affectedrows() == 1) ? true : false;
	}

	/**
	* Updates the users table by increasing the values.
	*
	* @param	array	$data	Array of data we want to increment
	* @return	mixed			Returns true if the columns were updated successfully, else false
	*/
	private function update_image_count($num)
	{
		$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
			SET user_images = user_images ' . (($num > 0) ? (' + ' . $num) : (' - ' . abs($num))) . ',
				user_last_update = ' . time() . '
			WHERE ' . (($num < 0) ? ' user_images > ' . abs($num) . ' AND ' : '') . '
				user_id = ' . $this->id;
		$this->db->sql_query($sql);

		if ($this->db->sql_affectedrows() == 1)
		{
			if (!empty($this->data))
			{
				$this->data['user_last_update'] = time();
				$this->data['user_images'] += $num;
			}
			return true;
		}
		return false;
	}

	/**
	* Updates the users table with the new data.
	*
	* @param	array	$data	Array of data we want to insert
	* @return	bool			Returns true if the data was inserted successfully
	*/
	private function insert($data)
	{
		$sql_ary = array_merge(self::$default_values, $this->validate_data($data), array(
			'user_id'			=> $this->id,
			'user_last_update'	=> time(),
		));

		$this->db->sql_return_on_error(true);

		$sql = 'INSERT INTO ' . GALLERY_USERS_TABLE . '
			' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);
		$error = $this->db->sql_error_triggered;

		$this->db->sql_return_on_error(false);

		$this->data = $sql_ary;
		$this->entry_exists = true;

		return ($error) ? false : true;
	}

	/**
	* Delete the user from the table.
	*/
	public function delete()
	{
		$sql = 'DELETE FROM ' . GALLERY_USERS_TABLE . '
			WHERE user_id = ' . $this->id;
		$result = $this->db->sql_query($sql);
	}

	/**
	* Delete the user from the table.
	*
	* @param	mixed	$user_ids	Can either be an array of IDs, one ID or the string 'all' to delete all users.
	*/
	static public function delete_users($user_ids)
	{
		global $db;

		$sql_where = self::sql_build_where($user_ids);

		$sql = 'DELETE FROM ' . GALLERY_USERS_TABLE . '
			' . $sql_where;
		$result = $db->sql_query($sql);
	}

	/**
	* Updates the users table with new data.
	*
	* @param	mixed	$user_ids	Can either be an array of IDs, one ID or the string 'all' to update all users.
	* @param	array	$data		Array of data we want to add/update.
	* @return	bool				Returns true if the columns were updated successfully
	*/
	static public function update_users($user_ids, $data)
	{
		global $db;

		$sql_ary = array_merge(self::validate_data($data), array(
			'user_last_update'	=> time(),
		));
		unset($sql_ary['user_id']);

		$sql_where = self::sql_build_where($user_ids);

		$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
			' . $sql_where;
		$db->sql_query($sql);

		return ($db->sql_affectedrows() != 0) ? true : false;
	}

	/**
	* Builds a valid WHERE-sql-statement, with casted integers, or empty to allow handling all users.
	*
	* @param	mixed	$user_ids	Can either be an array of IDs, one ID or the string 'all' to update all users.
	* @return	string				The WHERE statement with "WHERE " if needed.
	*/
	static public function sql_build_where($user_ids)
	{
		global $db;

		$sql_where = '';
		if (is_array($user_ids) && !empty($user_ids))
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

		return $sql_where;
	}

	/**
	* Validate user data.
	*
	* @param	array	$data	Array of data we need to validate
	* @return	array			Array with all allowed keys and their casted and selected values
	*/
	static public function validate_data($data, $inc = false)
	{
		$validated_data = array();
		foreach ($data as $name => $value)
		{
			switch ($name)
			{
				case 'user_id':
				case 'user_images':
				case 'personal_album_id':
				case 'user_lastmark':
				case 'user_last_update':
					if ($inc && ($name == 'user_images'))
					{
						// While incrementing, the iamges might be lower than 0.
						$validated_data[$name] = (int) $value;
					}
					else
					{
						$validated_data[$name] = max(0, (int) $value);
					}
				break;

				case 'user_viewexif':
				case 'watch_own':
				case 'watch_favo':
				case 'watch_com':
					$validated_data[$name] = (bool) $value;
				break;

				case 'user_permissions':
					$validated_data[$name] = $value;
				break;
			}
		}
		return $validated_data;
	}

	/**
	* Default values for new users.
	*/
	static protected $default_values = array(
		'user_images'		=> 0,
		'personal_album_id'	=> 0,
		'user_lastmark'		=> 0,
		'user_last_update'	=> 0,

		'user_permissions'	=> '',

		// Shall the EXIF data be viewed or collapsed by default?
		'user_viewexif'		=> true,
		// Shall the user be subscribed to his own images?
		'watch_own'			=> true,
		// Shall the user be subscribed if he adds the images to his favorites?
		'watch_favo'		=> false,
		// Shall the user be subscribed if he comments on an images?
		'watch_com'			=> false,
	);

	/**
	*
	*/
	static public function add_user_to_cache(&$user_cache, $row)
	{
		global $auth, $config, $user;

		$user_id = $row['user_id'];

		if ($user_id == ANONYMOUS)
		{
			$user_cache[$user_id] = array(
				'joined'		=> '',
				'posts'			=> '',
				'from'			=> '',

				'sig'					=> '',
				'sig_bbcode_uid'		=> '',
				'sig_bbcode_bitfield'	=> '',

				'online'			=> false,
				'avatar'			=> ($user->optionget('viewavatars')) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']) : '',
				'rank_title'		=> '',
				'rank_image'		=> '',
				'rank_image_src'	=> '',
				'sig'				=> '',
				'profile'			=> '',
				'pm'				=> '',
				'email'				=> '',
				'www'				=> '',
				'icq_status_img'	=> '',
				'icq'				=> '',
				'aim'				=> '',
				'msn'				=> '',
				'yim'				=> '',
				'jabber'			=> '',
				'search'			=> '',
				'age'				=> '',

				'gallery_album'		=> '',
				'gallery_images'	=> '',
				'gallery_search'	=> '',


				'username'			=> $row['username'],
				'user_colour'		=> $row['user_colour'],

				'warnings'			=> 0,
				'allow_pm'			=> 0,
			);

			get_user_rank($row['user_rank'], false, $user_cache[$user_id]['rank_title'], $user_cache[$user_id]['rank_image'], $user_cache[$user_id]['rank_image_src']);
		}
		else
		{
			$user_sig = '';
			if ($row['user_sig'] && $config['allow_sig'] && $user->optionget('viewsigs'))
			{
				$user_sig = $row['user_sig'];
			}

			$id_cache[] = $user_id;

			$user_cache[$user_id] = array(
				'joined'		=> $user->format_date($row['user_regdate']),
				'posts'			=> $row['user_posts'],
				'warnings'		=> (isset($row['user_warnings'])) ? $row['user_warnings'] : 0,
				'from'			=> (!empty($row['user_from'])) ? $row['user_from'] : '',

				'sig'					=> $user_sig,
				'sig_bbcode_uid'		=> (!empty($row['user_sig_bbcode_uid'])) ? $row['user_sig_bbcode_uid'] : '',
				'sig_bbcode_bitfield'	=> (!empty($row['user_sig_bbcode_bitfield'])) ? $row['user_sig_bbcode_bitfield'] : '',

				'viewonline'	=> $row['user_allow_viewonline'],
				'allow_pm'		=> $row['user_allow_pm'],

				'avatar'		=> ($user->optionget('viewavatars')) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']) : '',
				'age'			=> '',

				'rank_title'		=> '',
				'rank_image'		=> '',
				'rank_image_src'	=> '',

				'user_id'			=> $row['user_id'],
				'username'			=> $row['username'],
				'user_colour'		=> $row['user_colour'],

				'online'		=> false,
				'profile'		=> phpbb_gallery_url::append_sid('phpbb', 'memberlist', "mode=viewprofile&amp;u=$user_id"),
				'www'			=> $row['user_website'],
				'aim'			=> ($row['user_aim'] && $auth->acl_get('u_sendim')) ? phpbb_gallery_url::append_sid('phpbb', 'memberlist', "mode=contact&amp;action=aim&amp;u=$user_id") : '',
				'msn'			=> ($row['user_msnm'] && $auth->acl_get('u_sendim')) ? phpbb_gallery_url::append_sid('phpbb', 'memberlist', "mode=contact&amp;action=msnm&amp;u=$user_id") : '',
				'yim'			=> ($row['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . urlencode($row['user_yim']) . '&amp;.src=pg' : '',
				'jabber'		=> ($row['user_jabber'] && $auth->acl_get('u_sendim')) ? phpbb_gallery_url::append_sid('phpbb', 'memberlist', "mode=contact&amp;action=jabber&amp;u=$user_id") : '',
				'search'		=> ($auth->acl_get('u_search')) ? phpbb_gallery_url::append_sid('phpbb', 'search', "author_id=$user_id&amp;sr=posts") : '',

				'gallery_album'		=> ($row['personal_album_id'] && phpbb_gallery_config::get('viewtopic_icon')) ? phpbb_gallery_url::append_sid('album', "album_id=" . $row['personal_album_id']) : '',
				'gallery_images'	=> (phpbb_gallery_config::get('viewtopic_images')) ? $row['user_images'] : 0,
				'gallery_search'	=> (phpbb_gallery_config::get('viewtopic_images') && phpbb_gallery_config::get('viewtopic_link') && $row['user_images']) ? phpbb_gallery_url::append_sid('search', "user_id=$user_id") : '',
			);

			get_user_rank($row['user_rank'], $row['user_posts'], $user_cache[$user_id]['rank_title'], $user_cache[$user_id]['rank_image'], $user_cache[$user_id]['rank_image_src']);

			if (!empty($row['user_allow_viewemail']) || $auth->acl_get('a_email'))
			{
				$user_cache[$user_id]['email'] = ($config['board_email_form'] && $config['email_enable']) ? phpbb_gallery_url::append_sid('phpbb', 'memberlist', "mode=email&amp;u=$user_id") : (($config['board_hide_emails'] && !$auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email']);
			}
			else
			{
				$user_cache[$user_id]['email'] = '';
			}

			if (!empty($row['user_icq']))
			{
				$user_cache[$user_id]['icq'] = 'http://www.icq.com/people/webmsg.php?to=' . $row['user_icq'];
				$user_cache[$user_id]['icq_status_img'] = '<img src="http://web.icq.com/whitepages/online?icq=' . $row['user_icq'] . '&amp;img=5" width="18" height="18" alt="" />';
			}
			else
			{
				$user_cache[$user_id]['icq_status_img'] = '';
				$user_cache[$user_id]['icq'] = '';
			}

			if ($config['allow_birthdays'] && !empty($row['user_birthday']))
			{
				list($bday_day, $bday_month, $bday_year) = array_map('intval', explode('-', $row['user_birthday']));

				if ($bday_year)
				{
					$now = getdate(time() + $user->timezone + $user->dst - date('Z'));

					$diff = $now['mon'] - $bday_month;
					if ($diff == 0)
					{
						$diff = ($now['mday'] - $bday_day < 0) ? 1 : 0;
					}
					else
					{
						$diff = ($diff < 0) ? 1 : 0;
					}

					$user_cache[$user_id]['age'] = (int) ($now['year'] - $bday_year - $diff);
				}
			}
		}
	}
}
