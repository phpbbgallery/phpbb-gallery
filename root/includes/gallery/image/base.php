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

class phpbb_gallery_image_base
{
	/**
	* Only visible for moderators.
	*/
	const STATUS_UNAPPROVED	= 0;

	/**
	* Visible for everyone with the i_view-permissions
	*/
	const STATUS_APPROVED	= 1;

	/**
	* Visible for everyone with the i_view-permissions, but only moderators can comment.
	*/
	const STATUS_LOCKED		= 2;

	/**
	* Constants regarding the image contest relation
	*/
	const NO_CONTEST = 0;

	/**
	* The image is element of an open contest. Only moderators can see the user_name of the user.
	*/
	const IN_CONTEST = 1;

	/**
	* Delete an image completly.
	*
	* @param	array		$images		Array with the image_id(s)
	* @param	array		$filenames	Array with filenames for the image_ids. If a filename is missing it's queried from the database.
	*									Format: $image_id => $filename
	*/
	static public function delete_images($images, $filenames = array())
	{
		//@todo: phpbb_gallery_rating_base::delete_images($images);
		//@todo: phpbb_gallery_comment_base::delete_images($images);
		//@todo: phpbb_gallery_report_base::delete_images($images);
		//@todo: phpbb_gallery_favorite::delete_images($images);
		//@todo: phpbb_gallery_watch::delete_images($images);

		// Delete the files from the disc...
		$need_filenames = array();
		foreach ($images as $image)
		{
			if (!isset($filenames[$image]))
			{
				$need_filenames[] = $image;
			}
		}
		$filenames = array_merge($filenames, self::get_filenames($need_filenames));
		phpbb_gallery_image_file::delete($filenames);

		$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE ' . $db->sql_in_set('image_id', $images);
		$db->sql_query($sql);

		return true;
	}

	/**
	* Get the real filenames, so we can load/delete/edit the image-file.
	*
	* @param	mixed		$images		Array or integer with the image_id(s)
	* @return	array		Format: $image_id => $filename
	*/
	static public function get_filenames($images)
	{
		if (empty($images))
		{
			return array();
		}

		global $db;

		$filenames = array();
		$sql = 'SELECT image_id, image_filename
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE ' . $db->sql_in_set('image_id', $images);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$filenames[(int) $row['image_id']] = $row['image_filename'];
		}
		$db->sql_freeresult($result);

		return $filenames;
	}
}
