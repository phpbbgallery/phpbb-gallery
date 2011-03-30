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

class phpbb_gallery_image_favorite
{
	/**
	* Add to favorites
	*
	* @param	mixed	$image_ids		Array or integer with image_id where we delete from the favorites.
	* @param	int		$user_id		If not set, it uses the currents user_id
	*/
	static public function add($image_ids, $user_id = false)
	{
		global $db, $user;

		$image_ids = self::cast_mixed_int2array($image_ids);
		$user_id = (int) (($user_id) ? $user_id : $user->data['user_id']);

		foreach ($image_ids as $image_id)
		{
			$sql_ary = array(
				'image_id'		=> $image_id,
				'user_id'		=> $user_id,
			);
			$sql = 'INSERT INTO ' . GALLERY_FAVORITES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}

		$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
			SET image_favorited = image_favorited + 1
			WHERE ' . $db->sql_in_set('image_id', $image_ids);
		$db->sql_query($sql);
	}

	/**
	* Remove from favorites
	*
	* @param	mixed	$image_ids		Array or integer with image_id where we delete from the favorites.
	* @param	int		$user_id		If not set, it uses the currents user_id
	*/
	static public function remove($image_ids, $user_id = false)
	{
		global $db, $user;

		$image_ids = self::cast_mixed_int2array($image_ids);
		$user_id = (int) (($user_id) ? $user_id : $user->data['user_id']);

		$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . '
			WHERE user_id = ' . $user_id . '
				AND ' . $db->sql_in_set('image_id', $image_ids);
		$db->sql_query($sql);
		$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
			SET image_favorited = image_favorited - 1
			WHERE ' . $db->sql_in_set('image_id', $image_ids);
		$db->sql_query($sql);
	}

	/**
	* Delete given image_ids from the favorites
	*
	* @param	mixed	$image_ids		Array or integer with image_id where we delete from the favorites.
	* @param	bool	$reset_votes	Shall we also reset the average? We can save that query, when the images are deleted anyway.
	*/
	static public function delete_favorites($image_ids, $reset_votes = false)
	{
		global $db;

		$image_ids = self::cast_mixed_int2array($image_ids);

		$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . '
			WHERE ' . $db->sql_in_set('image_id', $image_ids);
		$result = $db->sql_query($sql);

		if ($reset_votes)
		{
			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
				SET image_favorited = 0
				WHERE ' . $db->sql_in_set('image_id', $image_ids);
			$db->sql_query($sql);
		}
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
