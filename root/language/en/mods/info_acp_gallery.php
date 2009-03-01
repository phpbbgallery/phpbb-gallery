<?php
/**
*
* info_acp_gallery [English]
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_GALLERY_ALBUM_MANAGEMENT'		=> 'Album management',
	'ACP_GALLERY_ALBUM_PERMISSIONS'		=> 'Permissions',
	'ACP_GALLERY_CLEANUP'				=> 'Cleanup gallery',
	'ACP_GALLERY_CONFIGURE_GALLERY'		=> 'Configure gallery',
	'ACP_GALLERY_LOGS'					=> 'Gallery log',
	'ACP_GALLERY_LOGS_EXPLAIN'			=> 'This lists all moderator actions of the gallery, like approving, disapproving, locking, unlocking, closing reports and deleting images.',
	'ACP_GALLERY_MANAGE_ALBUMS'			=> 'Manage albums',
	'ACP_GALLERY_OVERVIEW'				=> 'Overview',
	'ACP_IMPORT_ALBUMS'					=> 'Import Images',

	'GALLERY'							=> 'Gallery',
	'GALLERY_EXPLAIN'					=> 'Image Gallery',

	'IMG_BUTTON_UPLOAD_IMAGE'			=> 'Upload image',

	'PERSONAL_ALBUM'					=> 'Personal album',
	'PHPBB_GALLERY'						=> 'phpBB Gallery',

	'TOTAL_IMAGES_OTHER'				=> 'Total images <strong>%d</strong>',
	'TOTAL_IMAGES_ZERO'					=> 'Total images <strong>0</strong>',
));

?>