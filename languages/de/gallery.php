<?php
/**
*
**************************************************************************
*     German Language v 0.1.2 - by Cerkes - http://Tuerkei-Digital.de
 *                              -------------------
 *     begin                : Sunday, February 02, 2003
 *     copyright            : (C) 2003 Smartor
 *     email                : smartor_xp@hotmail.com
 *
 *     $Id$
 *
 ****************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

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
	'ALBUM'									=> 'Album',
	'ALBUM_DELETE_CONFIRM'					=> 'Bist du sicher das du dieses Bild löschen möchtest?',
	'ALBUM_NOT_EXIST'						=> 'Dieses Album existiert nicht',
	'ALBUM_PERMISSIONS'						=> 'Album Berechtigungen',
	'ALBUM_REACHED_QUOTA'					=> 'Dieses Album hat das Kontingent zum hochladen von Bildern erreicht. Es können keine weiteren Bilder mehr hochgeladen werden. Für weitere Information wende dich bitte an einen Administrator',
	'ALBUM_UPLOAD_NEED_APPROVAL'			=> 'Dein Bild wurde erfolgreich hochgeladen.<br /><br />Aber die Funktion der Genehmigung von Bildern vor der Veröffentlichung ist aktiv. Somit muss dein Bild vor der Veröffentlichung von einem Administrator oder einem Moderator genehmigt werden.',
	'ALBUM_UPLOAD_SUCCESSFUL'				=> 'Dein Bild wurde erfolgreich hochgeladen.',

	'ALBUM_COMMENT_CAN'						=> 'Du <strong>darfst</strong> Bilder in diesem Album kommentieren',
	'ALBUM_COMMENT_CANNOT'					=> 'Du <strong>darfst keine</strong> Bilder in diesem Album kommentieren',
	'ALBUM_DELETE_CAN'						=> 'Du <strong>darfst</strong> Deine Bilder und Kommentare in diesem Album löschen',
	'ALBUM_DELETE_CANNOT'					=> 'Du <strong>darfst nicht</strong> Deine Bilder und Kommentare in diesem Album löschen',
	'ALBUM_EDIT_CAN'						=> 'Du <strong>darfst</strong> Deine Bilder und Kommentare in diesem Album bearbeiten',
	'ALBUM_EDIT_CANNOT'						=> 'Du <strong>darfst nicht</strong> Deine Bilder und Kommentare in diesem Album bearbeiten',
	'ALBUM_RATE_CAN'						=> 'Du <strong>darfst</strong> Bilder in diesem Album bewerten',
	'ALBUM_RATE_CANNOT'						=> 'Du <strong>darfst keine</strong> Bilder in diesem Album bewerten',
	'ALBUM_UPLOAD_CAN'						=> 'Du <strong>darfst</strong> neue Bilder in diesem Album hochladen',
	'ALBUM_UPLOAD_CANNOT'					=> 'Du <strong>darfst keine</strong> neuen Bilder in diesem Album hochladen',
	'ALBUM_VIEW_CAN'						=> 'Du <strong>darfst</strong> Bilder in diesem Album ansehen',
	'ALBUM_VIEW_CANNOT'						=> 'Du <strong>darfst keine</strong> Bilder in diesem Album ansehen',

	'ALREADY_RATED'							=> 'Du hast dieses Bild bereits bewertet',
	'APPROVAL'								=> 'Genehmigung',
	'APPROVE'								=> 'Genehmigen',
	'APPROVED'								=> 'Genehmigt',

	'JPG_ALLOWED'							=> 'Es ist erlaubt JPG- Dateien hochzuladen',
	'PNG_ALLOWED'							=> 'Es ist erlaubt PNG- Dateien hochzuladen',
	'GIF_ALLOWED'							=> 'Es ist erlaubt GIF- Dateien hochzuladen',

	'BAD_UPLOAD_FILE_SIZE'					=> 'Deine hochgeladene Datei ist entweder zu gross oder defekt',

	'CLICK_RETURN_ALBUM'					=> 'Klicke %shier%s um zum Album zurückzukehren',
	'CLICK_RETURN_GALLERY_INDEX'			=> 'Klicke %shier%s um zur Startseite der Galerie zu gelangen',
	'CLICK_RETURN_MODCP'					=> 'Klicke %shier%s um zum Moderatons-Bereich zurückzukehren',
	'CLICK_RETURN_PERSONAL_ALBUM'			=> 'Klicke %shier%s um zu den persönlichen Alben zurückzukehren',
	'CLICK_VIEW_COMMENT'					=> 'Klicke %shier%s um Deine Kommentare anzusehen',
	'COMMENT'								=> 'Kommentar',
	'COMMENT_DELETE_CONFIRM'				=> 'Bist du sicher, das du diesen Kommentar löschen möchtest?',
	'COMMENT_NO_TEXT'						=> 'Bitte gebe deinen Kommentar ein',
	'COMMENT_STORED'						=> 'Dein Kommentar wurde erfolgreich hinzugefügt.',
	'COMMENT_TOO_LONG'						=> 'Dein Kommentar ist zu lang',
	'COMMENTS'								=> 'Kommentare',
	'CURRENT_RATING'						=> 'Aktuelle Bewertung',

	'DELETE_IMAGE'							=> 'Löschen',
	'DESC_TOO_LONG'							=> 'Deine Beschreibung ist zu lang',
	'DETAILS'								=> 'Details',

	'EDIT_IMAGE'							=> 'Bearbeiten',
	'EDIT_IMAGE_INFO'						=> 'Bearbeite Bild-Information',
	'EDITED_TIME_TOTAL'						=> 'Zuletzt bearbeitet von %s am %s; insgesamt %d mal bearbeitet',
	'EDITED_TIMES_TOTAL'					=> 'Zuletzt bearbeitet von %s am %s; insgesamt %d mal bearbeitet',

	'FILE'									=> 'Datei',
	'FILETYPE_AND_THUMBNAIL_DO_NOT_MATCH'	=> 'Das Bild und Vorschaubild müssen vom gleichen Typ sein',

	'GALLERY_INSTALLATION'			=> 'v%s installieren',
	'GALLERY_UPDATE'				=> 'v%s zu v%s aktualisieren',
	'GALLERY_UPDATE_SMARTOR'		=> 'Smartor-Album zu v%s aktualisieren',
	'GALLERY_INSTALL_NOTE1'		=> 'Script für die automatische Gallery-Datenbank-Erstellung.<br /><br /><span style="color:red; font-weight: bold;">Diese Script wird alle Einstellungen, Kategorien, Bilder und Kommentare von vorherigen Installationen löschen!</span><br /><span style="color:green; font-weight: bold;">Ein Update löscht diese Daten nicht.</span><br />Bist Du Dir absolut sicher?!',
	'GALLERY_INSTALL_NOTE2'		=> '<span style="color:green; font-weight: bold; font-size: 1.5em;">Gallery-Datenbank erfolgreich erstellt.</span>',
	'GALLERY_INSTALL_NOTE3'		=> 'Du musst Gründer Rechte besitzen um dieses Script ausführen zu können.',
	'GALLERY_INSTALL_NOTE4'		=> '<span style="color:green; font-weight: bold; font-size: 1.5em;">Gallery-Datenbank erfolgreich aktualisiert.</span>',

	'IMAGE_DESC'							=> 'Bildbeschreibung',
	'IMAGE_LOCKED'							=> 'Entschuldigung, aber dieses Bild wurde gesperrt. Du kannst für dieses Bild keine Kommentare mehr abgeben.',
	'IMAGE_NOT_EXIST'						=> 'Dieses Bild existiert nicht',
	'IMAGE_TITLE'							=> 'Bild Titel',
	'IMAGES'								=> 'Bilder',
	'IMAGES_APPROVED_SUCCESSFULLY'			=> 'Dein(e) Bild(er) wurde(n) freigegeben',
	'IMAGES_DELETED_SUCCESSFULLY'			=> 'Diese(s) Bild(er) wurde(n) erfolgreich gelöscht',
	'IMAGES_LOCKED_SUCCESSFULLY'			=> 'Dein(e) Bild(er) wurde(n) erfolgreich gesperrt',
	'IMAGES_MOVED_SUCCESSFULLY'				=> 'Dein(e) Bild(er) wurde(n) erfolgreich verschoben',
	'IMAGES_UNAPPROVED_SUCCESSFULLY'		=> 'Dein(e) Bild(er) wurde(n) erfolgreich auf ungeprüft gesetzt',
	'IMAGES_UNLOCKED_SUCCESSFULLY'			=> 'Dein(e) Bild(er) wurde(n) erfolgreich entsperrt',
	'IMAGES_UPDATED_SUCCESSFULLY'			=> 'Deine Bilderinformationen wurden erfolgreich aktuallisiert',

	'LAST_IMAGE'							=> 'Letztes Bild',
	'LOCK'									=> 'Sperren',
	'LOCKED'								=> 'Gesperrt',
	'LOGIN_EXPLAIN_PERSONAL_GALLERY'		=> 'Du musst registriert und angemeldet sein, um Persönliche Alben anzuschauen.',
	'LOGIN_TO_COMMENT'						=> 'Melde Dich an, um ein Kommentar abzugeben',
	'LOGIN_TO_RATE'							=> 'Melde Dich an, um dieses Bild zu bewerten',

	'MAX_FILE_SIZE'							=> 'Maximale Dateigröße (bytes)',
	'MAX_HEIGHT'							=> 'Maximale Bildhöhe (pixels)',
	'MAX_LENGTH'							=> 'Max Länge (bytes)',
	'MAX_WIDTH'								=> 'Maximale Bildbreite (pixels)',
	'MISSING_IMAGE_TITLE'					=> 'Du musst einen Titel für Dein Bild angeben',
	'MODCP'									=> 'Moderations-Bereich',
	'MOVE_TO_ALBUM'							=> 'Ins Album verschieben',

	'NEW_COMMENT'							=> 'Neuer Kommentar',
	'NO_COMMENTS'							=> 'Noch keine Kommentare',
	'NO_IMAGE_SPECIFIED'					=> 'Kein Bild angegeben',
	'NO_IMAGES'								=> 'Keine Bilder',
	'NONE'									=> 'Keiner',
	'NOT_ALLOWED_FILE_TYPE'					=> 'Dieser Datei Typ ist nicht erlaubt',
	'NOT_ALLOWED_TO_CREATE_PERSONAL_ALBUM'	=> 'Es tut uns leid, die Administratoren von diesem Board erlauben Dir nicht, ein persönliches Album anzulegen.',
	'NOT_APPROVED'							=> 'Ungeprüft',
	'NOT_RATED'								=> 'Nicht bewertet',

	'ORDER'									=> 'Reihenfolge',

	'PERSONAL_ALBUM_EXPLAIN'				=> 'Du kannst die persönlichen Alben anderer Mitglieder mit einem Klick auf den Link in ihrem Profil ansehen.',
	'PERSONAL_ALBUM_NOT_CREATED'			=> 'Die persönliche Galerie von %s ist leer oder wurde noch nicht erstellt.',
	'PERSONAL_ALBUM_OF_USER'				=> 'Persönliches Album von %s',
	'PERSONAL_ALBUMS'						=> 'Persönliche Alben',
	'PLAIN_TEXT_ONLY'						=> 'Nur Textformat',
	'POST_COMMENT'							=> 'Einen Kommentar schreiben',
	'POSTER'								=> 'Autor',

	'RATING'								=> 'Bewertung',
	'RECENT_PUBLIC_IMAGES'					=> 'Neuesten öffentliche Bilder',
	'RATING_SUCCESSFUL'						=> 'Das Bild wurde erfolgreich bewertet.',

	'SELECT_SORT_METHOD'					=> 'Wähle die Sortiermethode',
	'SORT'									=> 'Sortieren',
	'SORT_ASCENDING'						=> 'Aufsteigend',
	'SORT_DESCENDING'						=> 'Absteigend',
	'STATUS'								=> 'Status',

	'THUMBNAIL_SIZE'						=> 'Vorschaubildgrösse (Pixel)',

	'UNAPPROVE'								=> 'Genehmigung entziehen',
	'UNLOCK'								=> 'Entsperren',
	'UPLOAD_IMAGE'							=> 'Bild hochladen',
	'UPLOAD_IMAGE_SIZE_TOO_BIG'				=> 'Die Dimension Deines Bildes ist zu groß',
	'UPLOAD_NO_FILE'						=> 'Du musst deinen Pfad und Dateinamen eingeben',
	'UPLOAD_NO_TITLE'						=> 'Du musst einen Titel für Dein Bild angeben',
	'UPLOAD_THUMBNAIL'						=> 'Lade ein Vorschaubild hoch',
	'UPLOAD_THUMBNAIL_EXPLAIN'				=> 'Es muss der gleiche Dateityp sein wie Dein Bild',
	'UPLOAD_THUMBNAIL_FROM_MACHINE'			=> 'Lade Vorschaubild von Deinem Rechner hoch (es muss der gleiche Dateityp sein wie Dein Bild)',
	'UPLOAD_THUMBNAIL_SIZE_TOO_BIG'			=> 'Die Dimension Deines Vorschaubildes ist zu groß',
	'UPLOAD_TO_ALBUM'						=> 'In Album hochladen',
	'USER_REACHED_QUOTA'					=> 'Du hast das Kontingent zum Hochladen von Bildern erreicht. Es können keine weiteren Bilder mehr hochgeladen werden. Für weitere Information wende dich bitte an einen Administrator.',
	'USERS_PERSONAL_ALBUMS'					=> 'Persönliche Alben der Mitglieder',

	'VIEW_ALBUM'							=> 'Album ansehen',
	'VIEW_IMAGE'							=> 'Bild ansehen',
	'VIEW_THE_LATEST_IMAGE'					=> 'Das neueste Bild ansehen',
	'VIEWS'									=> 'Betrachtet',

	'WAITING_FOR_APPROVAL'					=> 'Bild(er), die auf Genehmigung warten',

	'YOUR_COMMENT'							=> 'Dein Kommentar',
	'YOUR_PERSONAL_ALBUM'					=> 'Dein persönliches Album',
	'YOUR_RATING'							=> 'Deine Bewertung',
	
//new
	'SUBALBUMS'						=> 'Subalben',
	'SUBALBUM'						=> 'Subalbum',
	'ALBUM_IS_CATEGORY'				=> 'Das Album in welches du dich gemogelt hast, ist eine Kategorie.<br />In Kategorien können keine Bilder hochgeladen werden.',
));

?>