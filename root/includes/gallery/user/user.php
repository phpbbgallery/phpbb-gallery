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
	public $db = null;

	/**
	* Name of the database table the values are in.
	*/
	public $table = '';

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
	public function __construct($db, $table, $user_id, $load = true)
	{
		$this->db		= $db;
		$this->table	= $table;
		$this->id		= (int) $user_id;
		if ($load)
		{
			$this->load();
		}
	}

	/**
	* Load the users data from the database and cast it...
	*/
	public function load()
	{
		$sql = 'SELECT *
			FROM ' . $this->table . '
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
	* Get user-setting, if the user does not have his own settings we fall back to default.
	*
	* @param	string	$key	Column name from the users-table
	* @return	mixed			Returns the value of the column, it it does not exist it returns false.
	*/
	public function data($key)
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
		$suc = false;
		if ($this->entry_exists)
		{
			$suc = $this->update($data);
		}

		if (!$suc || !$this->entry_exists)
		{
			$suc = $this->insert($data);
		}

		return $suc;
	}

	/**
	* Increase/Inserts the data, depending on whether the user already exists or not.
	*	Example: 'SET key = key + x'
	*/
	public function increase_data($data)
	{
		$suc = false;
		if ($this->entry_exists)
		{
			$suc = $this->update_values($data);
		}

		if (!$suc || !$this->entry_exists)
		{
			$suc = $this->insert($data);
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
		$sql_ary = array_merge($this->data, $this->validate_data($data), array(
			'last_update'	=> time(),
		));
		unset($sql_ary['user_id']);

		$sql = 'UPDATE ' . $this->table . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE user_id = ' . $this->id;
		$this->db->sql_query($sql);

		return ($this->db->sql_affectedrows() == 1) ? true : false;
	}

	/**
	* Updates the users table by increasing the values.
	*
	* @param	array	$data	Array of data we want to increment
	* @return	bool			Returns true if the columns were updated successfully
	*/
	private function update_values($data)
	{
		$set_keys = array();
		foreach ($this->validate_data($data) as $key => $value)
		{
			$set_keys[] = $key . ' = ' . $key . (($value > 0) ? (' + ' . $value) : (' - ' . abs($value)));
		}

		if (empty($set_keys))
		{
			return false;
		}

		$sql = 'UPDATE ' . $this->table . '
			SET ' . implode(', ', $set_keys) . ',
				last_update = ' . time() . '
			WHERE user_id = ' . $this->id;
		$this->db->sql_query($sql);

		return ($this->db->sql_affectedrows() == 1) ? true : false;
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
			'user_id'		=> $this->id,
			'last_update'	=> time(),
		));

		$this->db->sql_return_on_error(true);
		$sql = 'INSERT INTO ' . $this->table . '
			' . $this->db->sql_build_array('INSERT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$this->db->sql_return_on_error(false);

		return ($result !== false) ? true : false;
	}

	/**
	* Delete the user from the table.
	*/
	public function delete()
	{
		$sql = 'DELETE FROM ' . $this->table . '
			WHERE user_id = ' . $this->id;
		$result = $this->db->sql_query($sql);
	}

	/**
	* Validate user data.
	*
	* @param	array	$data	Array of data we need to validate
	* @return	array			Array with all allowed keys and their casted and selected values
	*/
	public function validate_data($data)
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
					$validated_data[$name] = max(0, (int) $value);
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
	static public $default_values = array(
		'user_images'		=> 0,
		'personal_album_id'	=> 0,
		'user_lastmark'		=> 0,
		'last_update'		=> 0,

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
}
