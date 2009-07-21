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

// Edit your amounts here.
define('GALLERY_ADD_CASH', 10);
define('GALLERY_DELETE_CASH', 10);
define('GALLERY_VIEW_CASH', 1);

/**
* Hookup image counter
*
* This function is called, after an image was/multiple images were uploaded/deleted.
* You can add your code here, to get/substruct cash on Cash-MODs or what ever.
*
* @param int $user_id		ID of the user, who ownes the images
* @param int $num_images	Number of images which are handled. (positive on add, negative on delete)
*/
function gallery_hookup_image_counter($user_id, $num_images)
{
	global $config, $db, $user, $phpbb_root_path, $phpEx;

	/**
	* Example code:
	* <code>
	if (defined('IN_ULTIMATE_POINTS') && $config['points_enable'])
	{
		if (!function_exists('add_points') || !function_exists('substract_points'))
		{
			includes($phpbb_root_path . 'includes/points/functions_points.' . $phpEx);
		}
		if ($num_images > 0)
		{
			// Add cash for uploading
			add_points($user_id, $num_images * GALLERY_ADD_CASH);
		}
		else
		{
			// Substract cash for deleting
			substract_points($user_id, $num_images * GALLERY_DELETE_CASH);
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
function gallery_hookup_image_view($user_id)
{
	global $config, $db, $user, $phpbb_root_path, $phpEx;

	/**
	* Example code:
	* <code>
	if (defined('IN_ULTIMATE_POINTS') && $config['points_enable'])
	{
		if (!function_exists('substract_points'))
		{
			includes($phpbb_root_path . 'includes/points/functions_points.' . $phpEx);
		}
		substract_points($user_id, GALLERY_VIEW_CASH);
		// If the user has negative cash now (would be needed on return from the cash-mods function, you can deny to view the image at all,
		// be removing the // in the next lines:
		// if (get_points($user_id) < 0)
		// {
		// 	// readd the cash to users-cash
		// 	add_points($user_id, GALLERY_VIEW_CASH);
		// 	return false;
		// }
	}
	* </code>
	*/

	return true;
}

?>