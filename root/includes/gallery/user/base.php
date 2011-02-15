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

class phpbb_gallery_user_base
{
	/**
	* Name of the database table the values are in.
	*/
	static public function sql_table()
	{
		return GALLERY_USERS_TABLE;
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
}
