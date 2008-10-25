<?php

/**
*
* @package phpBB3 - gallery
* @version $Id: gallery_acp.php 347 2008-03-03 17:39:58Z nickvergessen $
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
	'ACP_CREATE_ALBUM_TITLE'			=> 'Neues Album erstellen',
	'ACP_CREATE_ALBUM_EXPLAIN'			=> 'Neues Album erstellen und konfigurieren.',
	'ACP_EDIT_ALBUM_TITLE'				=> 'Album bearbeiten',
	'ACP_EDIT_ALBUM_EXPLAIN'			=> 'Ein vorhandenes Album bearbeiten.',
	'ACP_GALLERY_OVERVIEW'				=> 'phpBB Galerie',
	'ACP_GALLERY_OVERVIEW_EXPLAIN'		=> 'Hier findest du ein paar Statistiken zu deiner Galerie.',
	'ACP_MANAGE_ALBUMS'					=> 'phpBB Galerie Album administration',
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
	'CAN_CREATE'						=> 'Kann erstellen',

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
	'GALLERY_CLEAR_CACHE_CONFIRM'		=> 'Wenn du den Cache Feature für das Vorschaubild benutzt, musst du nach einer Änderungen an den Einstellungen in "Gallery konfigurieren" den Cache deiner Vorschaubilder leeren, um sie neu generieren zu lassen.',
	'GALLERY_CONFIG'					=> 'Galerie Konfiguration',
	'GALLERY_CONFIG_EXPLAIN'			=> 'Hier kannst du die Allgemeinen Einstellungen von phpBB Galerie durchführen.',
	'GALLERY_CONFIG_UPDATED'			=> 'Galerie Konfiguration wurde erfolgreich aktualisiert',

	'GALLERY_ALL'						=> 'Alle',
	'GALLERY_REG'						=> 'Registrierte',
	'GALLERY_PRIVATE'					=> 'Privat',
	'GALLERY_MOD'						=> 'Moderator',
	'GALLERY_ADMIN'						=> 'Administrator',

	'GD_VERSION'						=> 'GD Version optimieren',

	'HOTLINK_ALLOWED'					=> 'Erlaubt Domains für Hotlink (getrennt durch ein Komma)',
	'HOTLINK_PREVENT'					=> 'Hotlink Prävention',

	'IMAGE_APPROVAL'					=> 'Genehmigung der Bilder',
	'IMAGE_SETTINGS'					=> 'Bilder Einstellungen',
	'IMAGE_DESC_MAX_LENGTH'				=> 'Bild Beschreibung / Kommentar Max Länge (Bytes)',
	'INFO_LINE'							=> 'Dateigröße auf dem Thumbnail anzeigen',
	'IS_MODERATOR'						=> 'Ist Moderator',

	'LOOK_UP_ALBUM'						=> 'Album wählen',

	'MANUAL_THUMBNAIL'					=> 'Manuelles Vorschaubild',
	'MAX_IMAGES_PER_ALBUM'				=> 'Die maximale Anzahl der Bilder für jedes Album (-1 = unbegrenzt)',
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
	'RSZ_HEIGHT'						=> 'Maximuale Höhe beim anzeigen eines Bildes',
	'RSZ_WIDTH'							=> 'Maximuale Breite beim anzeigen eines Bildes',

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

//new one's
	'ALBUM_TYPE'					=> 'Album Type',
	'ALBUM_CATEGORY'				=> 'Kategorie',
	'ALBUM_NO_CATEGORY'				=> 'Album',
	'ALBUM_PARENT'					=> 'übergeordnetes Album',
	'NO_PARENT_ALBUM'				=> '&raquo; kein übergeordnetes Album',
	'ALBUM_NAME'					=> 'Album Name',
	'ALBUM_SETTINGS'				=> 'Album Einstellungen',
	'ALBUM_DELETE'					=> '%s löschen',
	'DELETE_SUBS'					=> 'Angehängte Alben löschen',
	'DELETE_IMAGES'					=> 'Bilder löschen',
	'HANDLE_SUBS'					=> 'Was soll mit angehängten Alben passieren',
	'HANDLE_IMAGES'					=> 'Was soll mit Bildern passieren',
	'DELETE_ALBUM_SUBS'				=> 'Bitte erst die angehängten Alben löschen',
	'NO_SUBALBUMS'					=> 'Keine Alben angehängt',
	'GALLERY_INDEX'					=> 'Galerie-Index',

//new one's
	'ACP_IMPORT_ALBUMS'					=> 'Neue Bilder importieren',
	'ACP_IMPORT_ALBUMS_EXPLAIN'			=> 'Hier kannst Du die Anzahl von Bilder eingeben, die importiert werden sollen. Bevor Du die Bilder importierst, ändere die Größe von Hand mit einer Bildbearbeitungssoftware.',
	'IMPORT_MISSING_DIR'			=> 'Gebe bitte das Verzeichnis an, wo sich Deine Bilder befinden.',
	'IMPORT_MISSING_ALBUM'			=> 'Wähle bitte ein Album aus, in das die Bilder importiert werden sollen.',
	'NO_DESC'						=> 'Keine Beschreibung',
	'IMPORT_DEBUG_MES'				=> '%1$s Bilder importiert. Es sind noch %2$s Bilder zu importieren.',
	'IMPORT_FINISHED'				=> 'Alle %1$s Bilder erfolgreich importiert.',
	'IMPORT_ALBUM'				=> 'Zielalbum:',
	'UPLOAD_IMAGES'				=> 'Mehrere Bilder auf einmal hochladen',
));

$lang = array_merge($lang, array(
	'ACP_GALLERY_CLEANUP_EXPLAIN'	=> 'Hier kannst du Überreste aus der Galerie entfernen.',
	'ADD_PERMISSIONS'				=> 'Berechtigungen hinzufügen',
	'ALBUM_ID'						=> 'Album-ID',
	'ALBUM_IMAGE'					=> 'Albumbild',

	'CACHE_DIR_SIZE'				=> 'Größe des cache/-Ordners',
	'CHANGE_AUTHOR'					=> 'Autor in Gast ändern',
	'CHECK'							=> 'Überprüfen',
	'CHECK_AUTHOR_EXPLAIN'			=> 'Keine Bilder ohne gültigen Autor gefunden.',
	'CHECK_COMMENT_EXPLAIN'			=> 'Keine Kommentare ohne gültigen Autor gefunden.',
	'CHECK_ENTRY_EXPLAIN'			=> 'Du musst die Überprüfung einmal starten, um nach Dateien ohne Datenbank-Eintrag zu suchen.',
	'CHECK_PERSONALS_EXPLAIN'		=> 'Keine persönlichen Alben ohne Besitzer gefunden.',
	'CHECK_SOURCE_EXPLAIN'			=> 'Es wurde kein Eintrag gefunden. Du solltest aber die Überprüfung einmal starten, um sicher zu gehen.',
	'CLEAN_CHANGED'					=> 'Autor in "Gast" geändert.',
	'CLEAN_GALLERY'					=> 'Galerie reinigen',
	'CLEAN_GALLERY_ABORT'			=> 'Reinigung abgebrochen!',
	'CLEAN_AUTHORS_DONE'			=> 'Bilder ohne Autor gelöscht.',
	'CLEAN_COMMENTS_DONE'			=> 'Kommentare ohne Autor gelöscht.',
	'CLEAN_ENTRIES_DONE'			=> 'Dateien ohne Datenbank-Einträge gelöscht.',
	'CLEAN_PERSONALS_DONE'			=> 'Persönliche Alben ohne Besitzer gelöscht.',
	'CLEAN_SOURCES_DONE'			=> 'Datenbank-Einträge ohne Dateien gelöscht.',
	'COMMENT_ID'					=> 'Kommentar-ID',
	'CONFIRM_CLEAN'					=> 'Dieser Vorgang kann nicht Rückgängig gemacht werden!',
	'CONFIRM_CLEAN_AUTHORS'			=> 'Bilder ohne Autor löschen?',
	'CONFIRM_CLEAN_COMMENTS'		=> 'Kommentare ohne Autor löschen?',
	'CONFIRM_CLEAN_ENTRIES'			=> 'Dateien ohne Datenbank-Einträge löschen?',
	'CONFIRM_CLEAN_PERSONALS'		=> 'Persönliche Alben ohne Besitzer löschen?',
	'CONFIRM_CLEAN_SOURCES'			=> 'Datenbank-Einträge ohne Dateien löschen?',
	'COPY_PERMISSIONS'				=> 'Kopiere Berechtigungen von',

	'DELETE_PERMISSIONS'			=> 'Berechtigungen löschen',
	'DISP_EXIF_DATA'				=> 'Exif-Daten anzeigen',
	'DISP_FAKE_THUMB'				=> 'Thumbnail in der Album-Liste anzeigen',
	'DISP_PERSONAL_ALBUM_PROFIL'	=> 'Link zum persönlichen Album im Profil anzeigen',
	'DISP_TOTAL_IMAGES'				=> '"Bilder insgesamt" auf der index.' . $phpEx . ' anzeigen.',
	'DISP_USER_IMAGES_PROFIL'		=> 'Statistik über hochgeladene Bilder im Profil anzeigen',
	'DONT_COPY_PERMISSIONS'			=> 'Berechtigungen nicht kopieren',

	'FAKE_THUMB_SIZE'				=> 'Thumbnailgröße',
	'FAKE_THUMB_SIZE_EXP'			=> 'Wenn du die volle Größe wählst, denke an die 16 Pixel für die schwarze Info-Zeile',

	'GALLERY_STATS'					=> 'Galerie Statistik',
	'GALLERY_VERSION'				=> 'Version der phpBB Gallery',//ja mit ll und y, da es sich um den MOD-Namen handelt
	'GUPLOAD_DIR_SIZE'				=> 'Größe des upload/-Ordners',

	'IMAGE_ID'						=> 'Bild-ID',
	'IMAGES_PER_DAY'				=> 'Bilder pro Tag',
	'IMPORT_DIR_EMPTY'				=> 'Das Verzeichnis %simport/ ist leer. Du musst die Bilder erst hochladen, bevor du sie importieren kannst.',
	'IMPORT_SELECT'					=> 'Wähle die Bilder aus, die importiert werden sollen. Bilder die erfolgreich importiert wurden, werden aus der Auswahl gelöscht. Die anderen Bilder stehen dir danach noch zur Verfügung.',
	'IMPORT_USER'					=> 'Hochgeladen durch',
	'IMPORT_USER_EXP'				=> 'Du kannst die Bilder auch einem anderem Mitglied zuordnen lassen.',

	'MANAGE_CRASHED_IMAGES'			=> 'Defekte Bilder verwalten',
	'MANAGE_CRASHED_ENTRIES'		=> 'Defekte Einträge verwalten',
	'MANAGE_PERSONALS'				=> 'Persönliche Alben verwalten',
	'MISSING_AUTHOR'				=> 'Bilder ohne gültigen Autor',
	'MISSING_AUTHOR_C'				=> 'Kommentare ohne gültigen Autor',
	'MISSING_ENTRY'					=> 'Dateien ohne Datenbank-Eintrag',
	'MISSING_OWNER'					=> 'Persönliche Alben ohne Besitzer',
	'MISSING_OWNER_EXP'				=> 'Beim Löschen werden alle Subalben, Bilder und Kommentare mit gelöscht.',
	'MISSING_SOURCE'				=> 'Datenbank-Einträge ohne Datei',
	'MISSING_ALBUM_NAME'			=> 'Du musst einen Namen für das Album eintragen.',

	'NUMBER_ALBUMS'					=> 'Anzahl von Alben',
	'NUMBER_IMAGES'					=> 'Anzahl von Bilder',
	'NUMBER_PERSONALS'				=> 'Anzahl von Persönlichen Alben',

	'OWN_PERSONAL_ALBUMS'			=> 'Eigene persönliche Alben',

	'PERMISSION'					=> 'Berechtigung',
	'PERMISSION_NEVER'				=> 'Nie',
	'PERMISSION_NO'					=> 'Nein',
	'PERMISSION_YES'				=> 'Ja',

	'PERMISSION_A_MODERATE'			=> 'Kann Album moderieren',
	'PERMISSION_ALBUM_COUNT'		=> 'Anzahl der möglichen persönlichen Subalben',
	'PERMISSION_C_DELETE'			=> 'Kann Kommentare löschen',
	'PERMISSION_C_EDIT'				=> 'Kann Kommentare editieren',
	'PERMISSION_C_POST'				=> 'Kann Bilder kommentieren',
	'PERMISSION_I_APPROVE'			=> 'Kann Bilder ohne Freigabe erstellen',
	'PERMISSION_I_COUNT'			=> 'Anzahl der hochladbaren Bilder',
	'PERMISSION_I_DELETE'			=> 'Kann Bilder löschen',
	'PERMISSION_I_EDIT'				=> 'Kann Bilder editieren',
	'PERMISSION_I_LOCK'				=> 'Kann Bilder sperren',
	'PERMISSION_I_RATE'				=> 'Kann Bilder bewerten',
	'PERMISSION_I_REPORT'			=> 'Kann Bilder melden',
	'PERMISSION_I_UPLOAD'			=> 'Kann Bilder hochladen',
	'PERMISSION_I_VIEW'				=> 'Kann Bilder sehen',

	'PERMISSION_EMPTY'				=> 'Du hast nicht alle Berechtigungen gesetzt.',
	'PERMISSIONS_STORED'			=> 'Berechtigungen erfolgreich gespeichert.',

	'REMOVE_IMAGES_FOR_CAT'			=> 'Du musst erst die Bilder aus dem Album entfernen, bevor du das Album zu einer Kategorie machen kannst.',
	'RESYNC_IMAGECOUNTS'			=> 'Anzahl der hochgeladenen Bilder resynchronisieren',
	'RESYNC_IMAGECOUNTS_EXPLAIN'	=> 'Es werden nur Bilder mitgezählt, die noch existieren.',
	'RESYNC_IMAGECOUNTS_CONFIRM'	=> 'Bist du sicher, dass du die Anzahl der hochgeladenen Bilder resynchronisieren willst?',
	'RESYNC_LAST_IMAGES'			=> '"Letztes Bild" neu ermitteln',
	'RESYNC_PERSONALS'				=> 'Persönliche Alben resynchronisieren',
	'RESYNC_PERSONALS_CONFIRM'		=> 'Bist du sicher, dass du die Persönliche Alben resynchronisieren willst?',

	'SELECT_ALBUMS'					=> 'Wähle Alben',
	'SELECTED_ALBUMS'				=> 'Ausgewählte Alben',
	'SELECT_GROUPS'					=> 'Wähle Gruppen',
	'SELECTED_GROUPS'				=> 'Ausgewählte Gruppen',
	'SELECT_PERMISSIONS'			=> 'Wähle Berechtigungen',
	'SELECTED_PERMISSIONS'			=> 'Ausgewählte Berechtigunen',
	'SET_PERMISSIONS'				=> '<br /><a href="%s">Berechtigungen</a> jetzt vergeben.',
	'SORRY_NO_STATISTIC'			=> 'Entschuldigung, aber dieser Statistik-Wert ist noch nicht verfügbar.',

	'THIS_WILL_BE_REPORTED'			=> 'Bekannter Fehler, sorry guys!',

	'WATERMARK'						=> 'Wasserzeichen',
	'WATERMARK_EXP'					=> 'Um zu verhindern, dass kleine Bilder vollkommen vom Wasserzeichen verdeckt werden, kannst du hier eine Mindestgröße angeben.',
	'WATERMARK_OPTIONS'				=> 'Wasserzeichen-Einstellungen',
	'WATERMARK_WIDTH'				=> 'Mindestbreite',
	'WATERMARK_HEIGHT'				=> 'Mindesthöhe',
));

// Added for 0.4.0-RC3
$lang = array_merge($lang, array(
	'PERMISSION_NO_GROUP'			=> 'Du hast keine Gruppe ausgewählt, für die du Berechtigungen vergeben möchtest.',
	'PURGED_CACHE'					=> 'Cache geleert',

	'RESYNCED_IMAGECOUNTS'			=> 'Anzahl der hochgeladenen Bilder resynchronisiert',
	'RESYNCED_LAST_IMAGES'			=> '"Letztes Bild" neu ermittelt',
	'RESYNCED_PERSONALS'			=> 'Persönliche Alben resynchronisiert',

	'SHORTED_IMAGENAMES'			=> 'Bildernamen kürzen',
	'SHORTED_IMAGENAMES_EXP'		=> 'Sollte der Name eines Bildes zu lange sein und kein Leerzeichen enthalten, kann es zu Problemen im Layout führen.',
));

?>