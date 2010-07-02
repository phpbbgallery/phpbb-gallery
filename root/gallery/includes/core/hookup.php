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

class phpbb_gallery_hookup
{
	// Edit your amounts here.
	const GALLERY_ADD_CASH = 10;
	const GALLERY_DELETE_CASH = 10;
	const GALLERY_VIEW_CASH = 1;

	/**
	* Hookup image counter
	*
	* This function is called, after an image was/multiple images were uploaded/deleted.
	* You can add your code here, to get/substruct cash on Cash-MODs or what ever.
	*
	* @param int $user_id		ID of the user, who ownes the images
	* @param int $num_images	Number of images which are handled. (positive on add, negative on delete)
	*/
	public static function add_image($user_id, $num_images)
	{
		global $config, $db, $user;

		/**
		* Example code:
		* <code>
		if (defined('IN_ULTIMATE_POINTS') && $config['points_enable'])
		{
			if (!function_exists('add_points') || !function_exists('substract_points'))
			{
				// If your file is in $phpbb_root_path/includes/points/functions_points.php use:
				phpbb_gallery_url::_include('functions_points', 'phpbb', 'includes/points/');
			}
			if ($num_images > 0)
			{
				// Add cash for uploading
				add_points($user_id, $num_images * self::GALLERY_ADD_CASH);
			}
			else
			{
				// Substract cash for deleting
				substract_points($user_id, abs($num_images) * self::GALLERY_DELETE_CASH);
			}
		}
		* </code>
		*/
	}

	/**
	* Hookup image view
	*
	* This function is called, when an image was viewed in fullsize.
	* You can add your code here, to substruct cash on Cash-MODs or what ever.
	*
	* @param int $user_id		ID of the user, who viewed the images
	*/
	public static function view_image($user_id)
	{
		global $config, $db, $user;

		/**
		* Example code:
		* <code>
		if (defined('IN_ULTIMATE_POINTS') && $config['points_enable'])
		{
			if (!function_exists('substract_points'))
			{
				phpbb_gallery_url::_include('functions_points', 'phpbb', 'includes/points/');
			}
			substract_points($user_id, self::GALLERY_VIEW_CASH);
			// If the user has negative cash now (would be needed on return from the cash-mods function, you can deny to view the image at all,
			// be removing the // in the next lines:
			// if (get_points($user_id) < 0)
			// {
			// 	// readd the cash to users-cash
			// 	add_points($user_id, self::GALLERY_VIEW_CASH);
			// 	return false;
			// }
		}
		* </code>
		*/

		return true;
	}
}