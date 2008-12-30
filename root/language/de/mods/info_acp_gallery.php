<?php
/**
*
* info_acp_gallery [Deutsch]
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
	'ACP_GALLERY_ALBUM_PERMISSIONS'		=> 'Album Berechtigungen',
	'ACP_GALLERY_CLEANUP'				=> 'Galerie reinigen',
	'ACP_GALLERY_CONFIGURE_GALLERY'		=> 'Galerie konfigurieren',
	'ACP_GALLERY_MANAGE_ALBUMS'			=> 'Verwalte Alben',
	'ACP_GALLERY_OVERVIEW'				=> 'Übersicht',
	'ACP_IMPORT_ALBUMS'					=> 'Bilder importieren',

	'GALLERY'							=> 'Galerie',
	'GALLERY_EXPLAIN'					=> 'Bilder Galerie',

	'IMG_BUTTON_UPLOAD_IMAGE'			=> 'Bild hochladen',

	'PERSONAL_ALBUM'					=> 'Persönliches Album',
	'PHPBB_GALLERY'						=> 'phpBB Galerie',

	'REMOVE_GALLERY_INSTALL'			=> 'Bitte entferne zur Sicherheit den Ordner install_gallery/.',

	'TOTAL_IMAGES_OTHER'				=> 'Bilder insgesamt: <strong>%d</strong>',
	'TOTAL_IMAGES_ZERO'					=> 'Bilder insgesamt: <strong>0</strong>',
));

?>