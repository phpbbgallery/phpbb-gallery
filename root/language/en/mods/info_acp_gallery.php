<?php

/**
*
* @package phpBB3 - gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/
if (!defined('IN_PHPBB')) 
{ 
	exit; 
} 

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'GALLERY'									=> 'Gallery',
	'PERSONAL_ALBUM'							=> 'Personal album',
	'GALLERY_EXPLAIN'							=> 'Picture Gallery',
	'PHPBB_GALLERY'								=> 'phpBB Gallery',
	'ACP_GALLERY_OVERVIEW'						=> 'Overview',
	'ACP_GALLERY_MANAGE_ALBUMS'					=> 'Manage Albums',
	'ACP_GALLERY_CONFIGURE_GALLERY'				=> 'Configure Gallery',
	'ACP_GALLERY_ALBUM_PERMISSIONS'				=> 'Album Permissions',
	'ACP_IMPORT_ALBUMS'							=> 'Import Images',
	'IMG_BUTTON_UPLOAD_IMAGE'					=> 'Upload image',
	'REMOVE_GALLERY_INSTALL'					=> 'Please remove the install_gallery/ folder in order to be save.',
));

$lang = array_merge($lang, array(
	'ACP_GALLERY_CLEANUP'						=> 'Cleanup gallery',

	'TOTAL_IMAGES_OTHER'						=> 'Total images <strong>%d</strong>',
	'TOTAL_IMAGES_ZERO'							=> 'Total images <strong>0</strong>',
));

?>