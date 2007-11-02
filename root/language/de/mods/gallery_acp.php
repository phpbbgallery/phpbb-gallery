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
	'ACP_CREATE_ALBUM_TITLE'			=> 'Neues Album erstellen',
	'ACP_CREATE_ALBUM_EXPLAIN'			=> 'Neues Album erstellen und konfigurieren.',
	'ACP_EDIT_ALBUM_TITLE'				=> 'Album bearbeiten',
	'ACP_EDIT_ALBUM_EXPLAIN'			=> 'Ein vorhandenes Album bearbeiten.',
	'ACP_GALLERY_OVERVIEW'				=> 'phpBB Gallery Übersicht',
	'ACP_GALLERY_OVERVIEW_EXPLAIN'		=> 'Gallery Admin Übersicht. Hier werden sich in der nächsten Version einige Statistiken etc. befinden.',
	'ACP_MANAGE_ALBUMS'					=> 'phpBB Gallery Album administration',
	'ACP_MANAGE_ALBUMS_EXPLAIN'			=> 'Hier kannst du Deine Alben verwalten (Ehemalige Kategorien).',
	
	'ALBUM_AUTH_EXPLAIN'				=> 'Hier kannst Du auswählen, welche Benutzergruppen Alben moderieren dürfen oder nur privaten Zugang erhalten',
	'ALBUM_AUTH_SUCCESSFULLY'			=> 'Einstellungen für die Berechtigungen wurden erfolgreich aktualisiert',
	'ALBUM_AUTH_TITLE'					=> 'Album Berechtigungen',
	'ALBUM_CHANGED_ORDER'				=> 'Album Reihenfolge wurde erfolgreich geändert',
	'ALBUM_DELETED'						=> 'Dieses Album wurde erfolgreich gelöscht',
	'ALBUM_DESC'						=> 'Album Beschreibung',
	'ALBUM_PERMISSIONS'					=> 'Album Berechtigungen',
	'ALBUM_PERSONAL_GALLERY_EXPLAIN'	=> 'Auf dieser Seite kannst Du wählen, welche Benutzergruppen berechtigt sind Alben zu erstellen und persönliche Galerien anzusehen. Diese Einstellungen sind nur relevant, wenn du die Option "PRIVAT" bei "der Benutzer darf persönliche Galerien erstellen" oder "Wer kann persönlichen Galerien erstellen" in der Konfiguration des Albums ausgewählt hast.',
	'ALBUM_PERSONAL_GALLERY_TITLE'		=> 'Persönliche Galerie',
	'ALBUM_PERSONAL_SUCCESSFULLY'		=> 'Die Einstellungen wurden erfolgreich aktuallisiert',
	'ALBUM_TITLE'						=> 'Album Titel',
	'ALBUM_UPDATED'						=> 'Das Album wurde erfolgreich aktuallisiert',
	
	'CAN_COMMENT'						=> 'Kann kommentieren',
	'CAN_DELETE'						=> 'Kann löschen',
	'CAN_EDIT'							=> 'Kann bearbeiten',
	'CAN_RATE'							=> 'Kann Bewerten',
	'CAN_UPLOAD'						=> 'Kann hochladen',
	'CAN_VIEW'							=> 'Kann ansehen',

	'CLEAR_CACHE'						=> 'Den Cache leeren',
	'CLICK_RETURN_ALBUM_AUTH'			=> 'Klicke %shier%s um zu den Album Berechtigungen zurückzukehren',
	'CLICK_RETURN_ALBUM_PERSONAL'		=> 'Klicke %shier%s um zu den Einstellungen der persönlichen Alben zurückzukehren',
	'CLICK_RETURN_GALLERY_ALBUM'		=> 'Klicke %shier%s um zum Album Manager zurückzukehren',
	'CLICK_RETURN_GALLERY_CONFIG'		=> 'Klicke %shier%s um zur Galerie Konfiguration zurückzukehren',
	'COLS_PER_PAGE'						=> 'Anzahl der Spalten auf Seite Vorschau-Seite',
	'COMMENT'							=> 'Kommentar',
	'COMMENT_LEVEL'						=> 'Kommentar Level',
	'COMMENT_SYSTEM'					=> 'Kommentar System aktivieren',
	'CREATE_ALBUM'						=> 'Neues Album erstellen',
	
	'DEFAULT_SORT_METHOD'				=> 'Voreingestellte Sortiermethode',
	'DEFAULT_SORT_ORDER'				=> 'Voreingestellte Sortierreihenfolge',
	'DELETE_ALBUM'						=> 'Album löschen',
	'DELETE_ALBUM_EXPLAIN'				=> 'Das untere Formular erlaubt Dir ein Album zu löschen und zu entscheiden ob du die Bilder löschen oder verschieben möchtest.',
	'DELETE_ALL_IMAGES'					=> 'Lösche alle Bilder',
	'DELETE_LEVEL'						=> 'Löschen Level',
	
	'EDIT_ALBUM'						=> 'Album bearbeiten',
	'EDIT_LEVEL'						=> 'Bearbeiten Level',
	'EXTRA_SETTINGS'					=> 'Extra Einstellungen',
	
	'FULL_IMAGE_POPUP'					=> 'Das Bild in voller Größe im Pop Up-Fenster ansehen',
	
	'GALLERY_ALBUMS_TITLE'				=> 'Galerie Alben Kontrolle',
	'GALLERY_CATEGORIES_EXPLAIN'		=> 'Hier kannst du deine Alben verwalten: erstellen, ändern, löschen, sortieren, usw.',
	'GALLERY_CLEAR_CACHE_CONFIRM'		=> 'Wenn du den Cache Feature für das Vorschaubild benutzt, musst du nach einer Änderungen in der Album Konfiguratioin den Cache Deiner Vorschaubild-Seite leeren um sie neu generieren zu lassen. <br /> <br /> Willst du ihn jetzt leeren?',
	'GALLERY_CONFIG'					=> 'Galerie Konfiguration',
	'GALLERY_CONFIG_EXPLAIN'			=> 'Hier kannst du die Allgemeinen Einstellungen von phpBB Gallery durchführen.',
	'GALLERY_CONFIG_UPDATED'			=> 'Gallery Konfiguration wurde erfolgreich aktualisiert',
	
	'GALLERY_ALL'						=> 'Alle',
	'GALLERY_REG'						=> 'Registrierte',
	'GALLERY_PRIVATE'					=> 'Private',
	'GALLERY_MOD'						=> 'Moderator',
	'GALLERY_ADMIN'						=> 'Administrator',
	
	'GD_VERSION'						=> 'GD Version optimieren',
	
	'HOTLINK_ALLOWED'					=> 'Erlaubt Domains für Hot-Link (getrennt durch ein Komma)',
	'HOTLINK_PREVENT'					=> 'Hotlink Prävention',
	
	'IMAGE_APPROVAL'					=> 'Genehmigung der Bilder',
	'IMAGE_DESC_MAX_LENGTH'				=> 'Bild Beschreibung / Kommentar Max Länge (Bytes)',
	'IS_MODERATOR'						=> 'Ist Moderator',
	
	'LOOK_UP_ALBUM'						=> 'Album wählen',
	
	'MANUAL_THUMBNAIL'					=> 'Manuelles Vorschaubild',
	'MAX_IMAGES'						=> 'Die maximale Anzahl der Bilder für jedes Album (-1 = unbegrenzt)',
	'MODERATOR_IMAGES_LIMIT'			=> 'Bilder pro Album für jeden Moderator (-1 = unbegrenzt)',
	'MOVE_CONTENTS'						=> 'Alle Bilder verschieben',
	'MOVE_DELETE'						=> 'Verschieben und löschen',
	'MOVE_AND_DELETE'					=> 'Verschieben und löschen',
	
	'NEW_ALBUM_CREATED'					=> 'Neues Album wurde erfolgreich erstellt',
	
	'PERSONAL_GALLERIES'				=> 'Persönliche Alben',
	'PERSONAL_GALLERY'					=> 'Erlaubt den Benutzern persönliche Alben anzulegen',
	'PERSONAL_GALLERY_LIMIT'			=> 'Bilder für jedes persönliche Album (-1 = unbegrenzt)',
	'PERSONAL_GALLERY_VIEW'				=> 'Wer darf persönliche Alben ansehen',
	
	'RATE'								=> 'Bewertung',
	'RATE_LEVEL'						=> 'Bewertungs Level',
	'RATE_SCALE'						=> 'Bewertungsskala',
	'RATE_SYSTEM'						=> 'Bewertungssystem aktivieren',
	'ROWS_PER_PAGE'						=> 'Anzahl der Zeilen auf Bildvorschau-Seite',
	
	'SELECT_A_ALBUM'					=> 'Wähle ein Album',
	
	'THUMBNAIL_CACHE'					=> 'Bildvorschau Cache',
	'THUMBNAIL_CACHE_CLEARED_SUCCESSFULLY'	=> '<br />Dein Bildvorschau Cache wurde erfolgreich geleert<br />&nbsp;',
	'THUMBNAIL_QUALITY'					=> 'Bildvorschau Qualität (1-100)',
	'THUMBNAIL_SETTINGS'				=> 'Bildvorschau Einstellungen',
	
	'UPLOAD'							=> 'Hochladen',
	'UPLOAD_LEVEL'						=> 'Hochladen Level',
	'USER_IMAGES_LIMIT'					=> 'Bilder pro Album für jeden Benutzer (-1 = unbegrenzt)',
	
	'VIEW_LEVEL'						=> 'Betrachten Level',
	
	'WATERMARK_IMAGES'					=> 'Wasserzeichen aktivieren',
	'WATERMARK_SOURCE'		 			=> 'Wasserzeichen Bild (Releativer Pfad zum Forums Root)',
	)
);

?>