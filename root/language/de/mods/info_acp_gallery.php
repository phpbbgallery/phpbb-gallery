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
	'ACP_GALLERY_LOGS'					=> 'Gallery-Protokoll',
	'ACP_GALLERY_LOGS_EXPLAIN'			=> 'Diese Liste zeigt alle Vorgänge, die von Moderatoren an Bildern und Kommentaren durchgeführt wurden.',
	'ACP_GALLERY_MANAGE_ALBUMS'			=> 'Verwalte Alben',
	'ACP_GALLERY_OVERVIEW'				=> 'Übersicht',
	'ACP_IMPORT_ALBUMS'					=> 'Bilder importieren',

	'GALLERY'							=> 'Galerie',
	'GALLERY_EXPLAIN'					=> 'Bilder Galerie',

	'IMG_BUTTON_UPLOAD_IMAGE'			=> 'Bild hochladen',

	'LOG_CLEAR_GALLEY'					=> 'Gallery-Protokoll gelöscht',
	'LOG_GALLERY_APPROVED'				=> '<strong>Bild freigeschalten</strong><br />» %s',
	'LOG_GALLERY_COMMENT_DELETED'		=> '<strong>Kommentar gelöscht</strong><br />» %s',
	'LOG_GALLERY_COMMENT_EDITED'		=> '<strong>Kommentar bearbeitet</strong><br />» %s',
	'LOG_GALLERY_DELETED'				=> '<strong>Bild gelöscht</strong><br />» %s',
	'LOG_GALLERY_EDITED'				=> '<strong>Bild bearbeitet</strong><br />» %s',
	'LOG_GALLERY_LOCKED'				=> '<strong>Bild gesperrt</strong><br />» %s',
	'LOG_GALLERY_MOVED'					=> '<strong>Bild verschoben</strong><br />» von %1$s nach %2$s',
	'LOG_GALLERY_REPORT_CLOSED'			=> '<strong>Meldung geschlossen</strong><br />» %s',
	'LOG_GALLERY_REPORT_DELETED'		=> '<strong>Meldung gelöscht</strong><br />» %s',
	'LOG_GALLERY_REPORT_OPENED'			=> '<strong>Meldung wieder geöffnet</strong><br />» %s',
	'LOG_GALLERY_UNAPPROVED'			=> '<strong>Erneute Freischaltung erzwungen</strong><br />» %s',
	'LOGVIEW_VIEWALBUM'					=> 'Album anzeigen',
	'LOGVIEW_VIEWIMAGE'					=> 'Bild anzeigen',

	'PERSONAL_ALBUM'					=> 'Persönliches Album',
	'PHPBB_GALLERY'						=> 'phpBB Galerie',

	'TOTAL_IMAGES_OTHER'				=> 'Bilder insgesamt: <strong>%d</strong>',
	'TOTAL_IMAGES_ZERO'					=> 'Bilder insgesamt: <strong>0</strong>',
));

?>