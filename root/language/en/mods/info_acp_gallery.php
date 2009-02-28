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
	'ACP_GALLERY_ALBUM_PERMISSIONS'		=> 'Album Permissions',
	'ACP_GALLERY_CLEANUP'				=> 'Cleanup gallery',
	'ACP_GALLERY_CONFIGURE_GALLERY'		=> 'Configure Gallery',
	'ACP_GALLERY_LOGS'					=> 'Gallery log',
	'ACP_GALLERY_LOGS_EXPLAIN'			=> 'This lists all moderator actions of the gallery, like approving, disapproving, locking, unlocking, closing reports and deleting images.',
	'ACP_GALLERY_MANAGE_ALBUMS'			=> 'Manage albums',
	'ACP_GALLERY_OVERVIEW'				=> 'Overview',
	'ACP_IMPORT_ALBUMS'					=> 'Import Images',

	'GALLERY'							=> 'Gallery',
	'GALLERY_EXPLAIN'					=> 'Image Gallery',

	'IMG_BUTTON_UPLOAD_IMAGE'			=> 'Upload image',

	'LOG_CLEAR_GALLEY'					=> 'Cleared Gallery log',
	'LOG_GALLERY_APPROVED'				=> '<strong>Approved image</strong><br />» %s',
	'LOG_GALLERY_COMMENT_DELETED'		=> '<strong>Deleted comment</strong><br />» %s',
	'LOG_GALLERY_COMMENT_EDITED'		=> '<strong>Edited comment</strong><br />» %s',
	'LOG_GALLERY_DELETED'				=> '<strong>Deleted image</strong><br />» %s',
	'LOG_GALLERY_EDITED'				=> '<strong>Edited image</strong><br />» %s',
	'LOG_GALLERY_LOCKED'				=> '<strong>Locked image</strong><br />» %s',
	'LOG_GALLERY_MOVED'					=> '<strong>Moved image</strong><br />» from %1$s to %2$s',
	'LOG_GALLERY_REPORT_CLOSED'			=> '<strong>Closed report</strong><br />» %s',
	'LOG_GALLERY_REPORT_DELETED'		=> '<strong>Deleted report</strong><br />» %s',
	'LOG_GALLERY_REPORT_OPENED'			=> '<strong>Reopened report</strong><br />» %s',
	'LOG_GALLERY_UNAPPROVED'			=> '<strong>Unapproved image</strong><br />» %s',
	'LOGVIEW_VIEWALBUM'					=> 'View album',
	'LOGVIEW_VIEWIMAGE'					=> 'View image',

	'PERSONAL_ALBUM'					=> 'Personal album',
	'PHPBB_GALLERY'						=> 'phpBB Gallery',

	'TOTAL_IMAGES_OTHER'				=> 'Total images <strong>%d</strong>',
	'TOTAL_IMAGES_ZERO'					=> 'Total images <strong>0</strong>',
));

?>