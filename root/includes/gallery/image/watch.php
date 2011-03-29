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

class phpbb_gallery_image_watch
{
	/**
	* Add images to watch-list
	*
	* @param	mixed	$image_ids		Array or integer with image_id where we delete from the watch-list.
	* @param	int		$user_id		If not set, it uses the currents user_id
	*/
	static public function add($image_ids, $user_id = false)
	{
		global $db;

		$image_ids = self::cast_mixed_int2array($image_ids);
		$user_id = (int) (($user_id) ? $user_id : $user->data['user_id']);

		foreach ($image_ids as $image_id)
		{
			$sql_ary = array(
				'image_id'		=> $image_id,
				'user_id'		=> $user_id,
			);
			$sql = 'INSERT INTO ' . GALLERY_WATCH_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}
	}
	/**
	* Add albums to watch-list
	*
	* @param	mixed	$album_ids		Array or integer with album_id where we delete from the watch-list.
	* @param	int		$user_id		If not set, it uses the currents user_id
	*/
	static public function add_albums($album_ids, $user_id = false)
	{
		global $db;

		$album_ids = self::cast_mixed_int2array($album_ids);
		$user_id = (int) (($user_id) ? $user_id : $user->data['user_id']);

		foreach ($album_ids as $album_id)
		{
			$sql_ary = array(
				'album_id'		=> $album_id,
				'user_id'		=> $user_id,
			);
			$sql = 'INSERT INTO ' . GALLERY_WATCH_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}
	}

	/**
	* Remove images from watch-list
	*
	* @param	mixed	$image_ids		Array or integer with image_id where we delete from the watch-list.
	* @param	mixed	$user_ids		If not set, it uses the currents user_id
	*/
	static public function remove($image_ids, $user_ids = false)
	{
		global $db;

		$image_ids = self::cast_mixed_int2array($image_ids);
		$user_ids = self::cast_mixed_int2array((($user_ids) ? $user_ids : $user->data['user_id']));

		$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . '
			WHERE ' . $db->sql_in_set('user_id', $user_ids) . '
				AND ' . $db->sql_in_set('image_id', $image_ids);
		$db->sql_query($sql);
	}

	/**
	* Remove albums from watch-list
	*
	* @param	mixed	$album_ids		Array or integer with album_id where we delete from the watch-list.
	* @param	mixed	$user_ids		If not set, it uses the currents user_id
	*/
	static public function remove_albums($album_ids, $user_ids = false)
	{
		global $db;

		$album_ids = self::cast_mixed_int2array($album_ids);
		$user_ids = self::cast_mixed_int2array((($user_ids) ? $user_ids : $user->data['user_id']));

		$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . '
			WHERE ' . $db->sql_in_set('user_id', $user_ids) . '
				AND ' . $db->sql_in_set('album_id', $album_ids);
		$db->sql_query($sql);
	}

	/**
	* Delete given image_ids from watch-list
	*
	* @param	mixed	$image_ids		Array or integer with image_id where we delete from watch-list.
	*/
	static public function delete_images($image_ids)
	{
		global $db;

		$image_ids = self::cast_mixed_int2array($image_ids);

		$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . '
			WHERE ' . $db->sql_in_set('image_id', $image_ids);
		$result = $db->sql_query($sql);
	}


	/**
	* Delete given album_ids from watch-list
	*
	* @param	mixed	$album_ids		Array or integer with album_id where we delete from watch-list.
	*/
	static public function delete_albums($album_ids)
	{
		global $db;

		$album_ids = self::cast_mixed_int2array($album_ids);

		$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . '
			WHERE ' . $db->sql_in_set('album_id', $album_ids);
		$result = $db->sql_query($sql);
	}

	static public function cast_mixed_int2array($ids)
	{
		if (is_array($ids))
		{
			return array_map('intval', $ids);
		}
		else
		{
			return array((int) $ids);
		}
	}
}
