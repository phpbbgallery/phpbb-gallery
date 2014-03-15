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

class phpbb_ext_gallery_core_cache
{
	private $phpbb_cache;
	private $phpbb_db;

	public function __construct(phpbb_cache_service $cache, dbal $db)
	{
		$this->phpbb_cache = $cache;
		$this->phpbb_db = $db;
	}

	public function get($data = 'albums')
	{
		switch ($data)
		{
			case 'albums':
				return $this->get_albums();
			default:
				return false;
		}
	}

	private function get_albums()
	{
		static $albums;

		if (isset($albums))
		{
			return $albums;
		}

		if (($albums = $this->phpbb_cache->get('_albums')) === false)
		{
			$sql = 'SELECT a.album_id, a.parent_id, a.album_name, a.album_type, a.left_id, a.right_id, a.album_user_id, a.display_in_rrc, a.album_auth_access
				FROM ' . GALLERY_ALBUMS_TABLE . ' a
				LEFT JOIN ' . USERS_TABLE . ' u
					ON (u.user_id = a.album_user_id)
				ORDER BY u.username_clean, a.album_user_id, a.left_id ASC';
			$result = $this->phpbb_db->sql_query($sql);

			$albums = array();
			while ($row = $this->phpbb_db->sql_fetchrow($result))
			{
				$albums[(int) $row['album_id']] = array(
					'album_id'			=> (int) $row['album_id'],
					'parent_id'			=> (int) $row['parent_id'],
					'album_name'		=> $row['album_name'],
					'album_type'		=> (int) $row['album_type'],
					'left_id'			=> (int) $row['left_id'],
					'right_id'			=> (int) $row['right_id'],
					'album_user_id'		=> (int) $row['album_user_id'],
					'display_in_rrc'	=> (bool) $row['display_in_rrc'],
					'album_auth_access'	=> (int) $row['album_auth_access'],
				);
			}
			$this->phpbb_db->sql_freeresult($result);
			$this->phpbb_cache->put('_albums', $albums);
		}

		return $albums;
	}
}
