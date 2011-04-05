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

class phpbb_gallery_cleanup
{
	/**
	* Delete source files without a database entry.
	*
	* @param	array	$filenames		An array of filenames
	* @return	string	Language key for the success message.
	*/
	static public function delete_files($filenames)
	{
		foreach ($filenames as $file)
		{
			phpbb_gallery_image_file::delete(utf8_decode($file));
		}

		return 'CLEAN_ENTRIES_DONE';
	}

	/**
	* Delete images, where the source file is missing.
	*
	* @param	mixed	$image_ids		Either an array of integers or an integer.
	* @return	string	Language key for the success message.
	*/
	static public function delete_images($image_ids)
	{
		phpbb_gallery_image::delete_images($image_ids, false, true);

		return 'CLEAN_SOURCES_DONE';
	}

	/**
	* Delete images, where the author is missing.
	*
	* @param	mixed	$image_ids		Either an array of integers or an integer.
	* @return	string	Language key for the success message.
	*/
	static public function delete_author_images($image_ids)
	{
		phpbb_gallery_image::delete_images($image_ids);

		return 'CLEAN_AUTHORS_DONE';
	}

	/**
	* Delete comments, where the author is missing.
	*
	* @param	mixed	$comment_ids	Either an array of integers or an integer.
	* @return	string	Language key for the success message.
	*/
	static public function delete_author_comments($comment_ids)
	{
		phpbb_gallery_comment::delete_comments($comment_ids);

		return 'CLEAN_COMMENTS_DONE';
	}

	/**
	* Delete unwanted and obsolent personal galleries.
	*
	* @param	array	$unwanted_pegas		User IDs we want to delete the pegas.
	* @param	array	$obsolent_pegas		User IDs we want to delete the pegas.
	* @return	array	Language keys for the success messages.
	*/
	static public function delete_pegas($unwanted_pegas, $obsolent_pegas)
	{
		$delete_pegas = array_merge($unwanted_pegas, $obsolent_pegas);

		$delete_images = $delete_albums = $user_image_count = array();
		$num_pegas = 0;

		$sql = 'SELECT album_id
			FROM ' . GALLERY_ALBUMS_TABLE . '
			WHERE ' . $db->sql_in_set('album_user_id', $delete_pegas);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$delete_albums[] = (int) $row['album_id'];
			if ($row['parent_id'] == 0)
			{
				$num_pegas++;
			}
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT image_id, image_filename, image_status, image_user_id
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE ' . $db->sql_in_set('image_album_id', $delete_albums, false, true);
		$result = $db->sql_query($sql);

		$filenames = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$delete_images[] = (int) $row['image_id'];
			$filenames[(int) $row['image_id']] = $row['image_filename'];

			if ($row['image_status'] == phpbb_gallery_image::STATUS_UNAPPROVED)
			{
				continue;
			}

			if (isset($user_image_count[(int) $row['image_user_id']]))
			{
				$user_image_count[(int) $row['image_user_id']]++;
			}
			else
			{
				$user_image_count[(int) $row['image_user_id']] = 1;
			}
		}
		$db->sql_freeresult($result);

		if (!empty($delete_images))
		{
			phpbb_gallery_image::delete_images($delete_images, $filenames);
		}

		$sql = 'DELETE FROM ' . GALLERY_ALBUMS_TABLE . '
			WHERE ' . $db->sql_in_set('album_id', $delete_albums);
		$db->sql_query($sql);
		phpbb_gallery_config::dec('num_pegas', $num_pegas);

		if (in_array(phpbb_gallery_config::get('newest_pega_album_id'), $delete_albums))
		{
			// Update the config for the statistic on the index
			if (phpbb_gallery_config::get('num_pegas') > 0)
			{
				$sql_array = array(
					'SELECT'		=> 'a.album_id, u.user_id, u.username, u.user_colour',
					'FROM'			=> array(GALLERY_ALBUMS_TABLE => 'a'),

					'LEFT_JOIN'		=> array(
						array(
							'FROM'		=> array(USERS_TABLE => 'u'),
							'ON'		=> 'u.user_id = a.album_user_id',
						),
					),

					'WHERE'			=> 'a.album_user_id <> ' . phpbb_gallery_album::PUBLIC_ALBUM . ' AND a.parent_id = 0',
					'ORDER_BY'		=> 'a.album_id DESC',
				);
				$sql = $db->sql_build_query('SELECT', $sql_array);

				$result = $db->sql_query_limit($sql, 1);
				$newest_pega = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
			}

			if ((phpbb_gallery_config::get('num_pegas') > 0) && isset($newest_pega))
			{
				phpbb_gallery_config::set('newest_pega_user_id', $newest_pega['user_id']);
				phpbb_gallery_config::set('newest_pega_username', $newest_pega['username']);
				phpbb_gallery_config::set('newest_pega_user_colour', $newest_pega['user_colour']);
				phpbb_gallery_config::set('newest_pega_album_id', $newest_pega['album_id']);
			}
			else
			{
				phpbb_gallery_config::set('newest_pega_user_id', 0);
				phpbb_gallery_config::set('newest_pega_username', '');
				phpbb_gallery_config::set('newest_pega_user_colour', '');
				phpbb_gallery_config::set('newest_pega_album_id', 0);

				if (isset($newest_pega == false))
				{
					phpbb_gallery_config::set('num_pegas', 0);
				}
			}
		}

		foreach ($user_image_count as $user_id => $images)
		{
			phpbb_gallery_hookup::add_image($user_id, (0 - $images));

			$uploader = new phpbb_gallery_user($db, $user_id, false);
			$uploader->update_images((0 - $images));
		}
		phpbb_gallery_user::update_users($delete_pegas, array('personal_album_id' => 0));

		$return = array();
		if ($obsolent_pegas)
		{
			$return[] = 'CLEAN_PERSONALS_DONE';
		}
		if ($unwanted_pegas)
		{
			$return[] = 'CLEAN_PERSONALS_BAD_DONE';
		}

		return $return;
	}
}






















