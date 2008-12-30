<?php
/**
*
* gallery_ucp [English]
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
	'ALBUM_NAME'					=> 'Name',
	'ALBUM_DESC'					=> 'Description',
	'ALBUM_PARENT'					=> 'Parentalbum',
	'ATTACHED_SUBALBUMS'			=> 'attached subalbums',

	'CREATE_PERSONAL_ALBUM'			=> 'create personal album',
	'CREATE_SUBALBUM'				=> 'create a subalbum',
	'CREATE_SUBALBUM_EXP'			=> 'You may attach a new subalbum to your personal gallery.',
	'CREATED_SUBALBUM'				=> 'Subalbum successful created',

	'DELETE_ALBUM'					=> 'Delete Album',
	'DELETE_ALBUM_CONFIRM'			=> 'Delete Album, with all attached subalbums and images?',
	'DELETED_ALBUMS'				=> 'Albums successful deleted',

	'EDIT'							=> 'Edit',
	'EDIT_ALBUM'					=> 'edit this album',
	'EDIT_SUBALBUM'					=> 'Edit Subalbum',
	'EDIT_SUBALBUM_EXP'				=> 'You can edit your albums here.',
	'EDITED_SUBALBUM'				=> 'Album successful edited',

	'GOTO'							=> 'Go To',

	'MANAGE_PERSONAL_ALBUM'			=> 'Here you can manage your personal album. You may add subalbums, add and edit descriptions, change the order of display, etc.',
	'MANAGE_SUBALBUMS'				=> 'manage your subalbums',
	'MISSING_NAME'					=> 'Please insert a name for the album',
	'MOVED_ALBUMS'					=> 'Albums successful moved',

	'NEED_INITIALISE'				=> 'You don\'t have a personal album yet.',
	'NO_ALBUM_STEALING'				=> 'You are not allowed to manage the Album of other users.',
	'NO_FAVORITES'					=> 'You don\'t have any favorites.',
	'NO_MORE_SUBALBUMS_ALLOWED'		=> 'You added your maximum of subalbums to your personal album',
	'NO_PARENT_ALBUM'				=> '&laquo;-- no parent',
	'NO_PERSALBUM_ALLOWED'			=> 'You don\'t have the permissions create your personal album',
	'NO_PERSONAL_ALBUM'				=> 'You don\'t have a personal album yet. Here you can create your personal album, with some subalbums.<br />In personal albums only the owner can upload pictures.',
	'NO_SUBALBUMS'					=> 'no subalbums',
	'NO_SUBALBUMS_ALLOWED'			=> 'You don\'t have the permissions to add subalbums to your personal album',
	'NO_SUBSCRIPTIONS'				=> 'You didn\'t subscribe to any image.',
	'NO_SUBSCRIPTIONS_ALBUM'		=> 'You didn\'t subscribe to any album.',

	'PARSE_BBCODE'					=> 'parse bbcodes',
	'PARSE_SMILIES'					=> 'parse smiles',
	'PARSE_URLS'					=> 'parse urls',
	'PERSONAL_ALBUM'				=> 'personal album',

	'REMOVE_FROM_FAVORITES'			=> 'Remove from favorites',

	'UNSUBSCRIBE'					=> 'stop watching',

	'YOUR_FAVORITE_IMAGES'			=> 'Here you can see your favorite-images. You may remove them, if you don\'t like them anymore.',
	'YOUR_SUBSCRIPTIONS'			=> 'Here you see albums and images you get notified on.',

	'WATCH_CHANGED'					=> 'Settings stored',
	'WATCH_COM'						=> 'subscribe commented images by default',
	'WATCH_FAVO'					=> 'subscribe favorite images by default',
	'WATCH_NOTE'					=> 'This option only affects on new images. All other images need to be added by the "subscribe image" option.',
	'WATCH_OWN'						=> 'subscribe own images by default',
));

?>