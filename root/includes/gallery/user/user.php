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

class phpbb_gallery_user extends phpbb_gallery_user_base
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
			FROM ' . $this->sql_table() . '
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
	public function increase_data($data)
	{
		$this->force_load();

		$suc = false;
		if ($this->entry_exists)
		{
			$suc = $this->update_values($data);
		}

		if (($suc === false) || !$this->entry_exists)
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
			'user_last_update'	=> time(),
		));
		unset($sql_ary['user_id']);

		$sql = 'UPDATE ' . $this->sql_table() . '
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
	private function update_values($data)
	{
		$set_keys = array();
		foreach ($this->validate_data($data, true) as $key => $value)
		{
			$set_keys[] = $key . ' = ' . $key . (($value > 0) ? (' + ' . $value) : (' - ' . abs($value)));
			$this->data[$key] += $value;
		}

		$this->data['user_last_update'] = time();
		$sql = 'UPDATE ' . $this->sql_table() . '
			SET ' . ((!empty($set_keys)) ? implode(', ', $set_keys) . ', ' : '') . '
				user_last_update = ' . time() . '
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
			'user_id'			=> $this->id,
			'user_last_update'	=> time(),
		));

		$this->db->sql_return_on_error(true);

		$sql = 'INSERT INTO ' . $this->sql_table() . '
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
		$sql = 'DELETE FROM ' . $this->sql_table() . '
			WHERE user_id = ' . $this->id;
		$result = $this->db->sql_query($sql);
	}
}
