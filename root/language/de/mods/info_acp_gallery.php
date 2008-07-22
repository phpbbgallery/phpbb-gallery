<?php

/**
*
* @package phpBB3 - gallery
* @version $Id: info_acp_gallery.php 337 2008-03-01 19:33:57Z stoffel04 $
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
	'GALLERY'									=> 'Galerie',
	'PERSONAL_ALBUM'							=> 'Persönliches Album',
	'GALLERY_EXPLAIN'							=> 'Bilder Galerie',
	'PHPBB_GALLERY'								=> 'phpBB Galerie',
	'ACP_GALLERY_OVERVIEW'						=> 'Übersicht',
	'ACP_GALLERY_MANAGE_ALBUMS'					=> 'Verwalte Alben',
	'ACP_GALLERY_MANAGE_CACHE'					=> 'Den Cache verwalten',
	'ACP_GALLERY_CONFIGURE_GALLERY'				=> 'Galerie konfigurieren',
	'ACP_GALLERY_ALBUM_PERMISSIONS'				=> 'Album Berechtigungen',
	'ACP_GALLERY_ALBUM_PERSONAL_PERMISSIONS'	=> 'Persönliche Alben Berechtigungen',
	'ACP_IMPORT_ALBUMS'							=> 'Bilder importieren',
	'IMG_BUTTON_UPLOAD_IMAGE'   				=> 'Bild hochladen',
	'REMOVE_GALLERY_INSTALL'					=> 'Bitte entferne zur Sicherheit den Ordner install_gallery/.',
	'NEW_PERMISSIONS'							=> 'Neues Berechtigungs System',
));

$lang = array_merge($lang, array(
	'TOTAL_IMAGES_OTHER'						=> 'Bilder insgesamt: <strong>%d</strong>',
	'TOTAL_IMAGES_ZERO'							=> 'Bilder insgesamt: <strong>0</strong>',
));

?>