<?php
/** 
*
* viewforum [* German language v 0.1.2 - by Cerkes - http://Tuerkei-Digital.de]
*
* @package language
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

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
	'PHPBB_GALLERY'						=> 'phpBB Gallery',
	'ACP_GALLERY_MANAGE_CACHE'			=> 'Den Cache verwalten',
	'ACP_GALLERY_CONFIGURE_GALLERY'		=> 'Galerie konfigurieren',
	'ACP_GALLERY_ALBUM_PERMISSIONS'		=> 'Album Berechtigungen',
	'ACP_GALLERY_ALBUM_PERSONAL_PERMISSIONS'	=> 'Persönliche Alben Berechtigungen',
	'ACP_GALLERY_OVERVIEW'				=> 'Übersicht',
	'ACP_GALLERY_MANAGE_ALBUMS'			=> 'Verwalte Alben',
	
	'MANAGE_ALBUMS'						=> 'Verwalte Alben',
	'GALLERY_OVERVIEW'					=> 'Galerie Übersicht',
	'GALLERY_MANAGE_CACHE'				=> 'Den Cache verwalten',
	'GALLERY_CONFIGURE_GALLERY'			=> 'Galerie konfigurieren',
	'ALBUM_PERMISSIONS'					=> 'Album Berechtigungen',
));

?>