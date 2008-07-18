<?php

/**
*
* @package phpBB3 - gallery
* @version $Id: gallery_acp.php 256 2008-01-25 18:52:19Z nickvergessen $
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
	'PERSONAL_ALBUM'					=> 'personal album',
	'NO_PERSONAL_ALBUM'					=> 'You don\'t have a personal album yet. Here you can create your personal album, with some subalbums.<br />In personal albums only the owner can upload pictures.',
	'CREATE_PERSONAL_ALBUM'				=> 'create personal album',
	'MANAGE_PERSONAL_ALBUM'				=> 'Here you can manage your personal album. You may add subalbums, add and edit descriptions, change the order of display, etc.',
	'MANAGE_SUBALBUMS'					=> 'manage your subalbums',
	'CREATE_SUBALBUM'					=> 'create a subalbum',
	'CREATE_SUBALBUM_EXP'				=> 'You may attach a new subalbum to your personal gallery.',

	'ALBUM_NAME'					=> 'Name',
	'ALBUM_DESC'					=> 'Description',
	'ALBUM_PARENT'					=> 'Parentalbum',
	'NO_PARENT_ALBUM'				=> '&laquo;-- no parent',
	'PARSE_BBCODE'					=> 'parse bbcodes',
	'PARSE_SMILIES'					=> 'parse smiles',
	'PARSE_URLS'					=> 'parse urls',

	'EDIT'							=> 'Edit',
	'EDIT_SUBALBUM'					=> 'Edit Subalbum',
	'EDIT_SUBALBUM_EXP'				=> 'You can edit you albums here.',
	'EDITED_SUBALBUM'				=> 'Album successful edited',
	'CREATED_SUBALBUM'				=> 'Subalbum successful created',
	'MISSING_NAME'					=> 'Please insert a name for the album',
	'ATTACHED_SUBALBUMS'			=> 'attached subalbums',
	'NO_SUBALBUMS'					=> 'no subalbums',
	'NEED_INITIALISE'				=> 'You don\'t have a personal album yet.',
	'DELETE_ALBUM'					=> 'Delete Album',
	'DELETE_ALBUM_CONFIRM'			=> 'Delete Album, with all attached subalbums and images?',
	'DELETED_ALBUMS'				=> 'Albums successful deleted',
	'MOVED_ALBUMS'					=> 'Albums successful moved',
	'NO_ALBUM_STEALING'				=> 'You are not allowed to manage the Album of other users.',
	'NO_SUBALBUMS_ALLOWED'			=> 'You don\'t have the permissions to add subalbums to your personal album',
	'NO_MORE_SUBALBUMS_ALLOWED'		=> 'You added your maximum of subalbums to your personal album',
	'NO_PERSALBUM_ALLOWED'			=> 'You don\'t have the permissions create your personal album',
	'GOTO'							=> 'Go To',

	'EDIT_ALBUM'					=> 'edit this album',
));

$lang = array_merge($lang, array(
	'NO_FAVORITES'					=> 'You don\'t have any favorites.',
	'NO_SUBSCRIPTIONS'				=> 'You didn\'t subscribe to any image.',
	'NO_SUBSCRIPTIONS_ALBUM'		=> 'You didn\'t subscribe to any album.',

	'REMOVE_FROM_FAVORITES'			=> 'Remove from favorites',

	'YOUR_FAVORITE_IMAGES'			=> 'Here you can see your favorite-images. You may remove them, if you don\'t like them anymore.',

	'WATCH_CHANGED'					=> 'Settings stored',
	'WATCH_COM'						=> 'subscribe commented images by default',
	'WATCH_FAVO'					=> 'subscribe favorite images by default',
	'WATCH_NOTE'					=> 'This option only affects on new images. All other images need to be added by the "subscribe image" option.',
	'WATCH_OWN'						=> 'subscribe own images by default',
));

?>