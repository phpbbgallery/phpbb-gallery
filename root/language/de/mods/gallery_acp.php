<?php
/**
*
* gallery_acp [Deutsch]
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
	'ACP_CREATE_ALBUM_EXPLAIN'		=> 'Neues Album erstellen und konfigurieren.',
	'ACP_EDIT_ALBUM_TITLE'			=> 'Album bearbeiten',
	'ACP_EDIT_ALBUM_EXPLAIN'		=> 'Ein vorhandenes Album bearbeiten.',
	'ACP_GALLERY_CLEANUP_EXPLAIN'	=> 'Hier kannst du Überreste aus der Galerie entfernen.',
	'ACP_GALLERY_OVERVIEW'			=> 'phpBB Galerie',
	'ACP_GALLERY_OVERVIEW_EXPLAIN'	=> 'Hier findest du ein paar Statistiken zu deiner Galerie.',
	'ACP_IMPORT_ALBUMS'				=> 'Neue Bilder importieren',
	'ACP_IMPORT_ALBUMS_EXPLAIN'		=> 'Hier kannst Du die Anzahl von Bilder eingeben, die importiert werden sollen. Bevor Du die Bilder importierst, ändere die Größe von Hand mit einer Bildbearbeitungssoftware.',
	'ACP_MANAGE_ALBUMS'				=> 'phpBB Galerie Album administration',
	'ACP_MANAGE_ALBUMS_EXPLAIN'		=> 'Hier kannst du Deine Alben verwalten (Ehemalige Kategorien).',

	'ADD_PERMISSIONS'				=> 'Berechtigungen hinzufügen',
	'ALBUM_AUTH_TITLE'				=> 'Album Berechtigungen',
	'ALBUM_CATEGORY'				=> 'Kategorie',
	'ALBUM_DELETE'					=> '%s löschen',
	'ALBUM_DELETED'					=> 'Dieses Album wurde erfolgreich gelöscht',
	'ALBUM_DESC'					=> 'Album Beschreibung',
	'ALBUM_ID'						=> 'Album-ID',
	'ALBUM_IMAGE'					=> 'Albumbild',
	'ALBUM_NAME'					=> 'Album Name',
	'ALBUM_NO_CATEGORY'				=> 'Album',
	'ALBUM_PARENT'					=> 'übergeordnetes Album',
	'ALBUM_SETTINGS'				=> 'Album Einstellungen',
	'ALBUM_TYPE'					=> 'Album Type',
	'ALBUM_UPDATED'					=> 'Das Album wurde erfolgreich aktuallisiert',

	'CACHE_DIR_SIZE'				=> 'Größe des cache/-Ordners',
	'CHANGE_AUTHOR'					=> 'Autor in Gast ändern',
	'CHECK'							=> 'Überprüfen',
	'CHECK_AUTHOR_EXPLAIN'			=> 'Keine Bilder ohne gültigen Autor gefunden.',
	'CHECK_COMMENT_EXPLAIN'			=> 'Keine Kommentare ohne gültigen Autor gefunden.',
	'CHECK_ENTRY_EXPLAIN'			=> 'Du musst die Überprüfung einmal starten, um nach Dateien ohne Datenbank-Eintrag zu suchen.',
	'CHECK_PERSONALS_EXPLAIN'		=> 'Keine persönlichen Alben ohne Besitzer gefunden.',
	'CHECK_PERSONALS_BAD_EXPLAIN'	=> 'Keine persönlichen Alben gefunden.',
	'CHECK_SOURCE_EXPLAIN'			=> 'Es wurde kein Eintrag gefunden. Du solltest aber die Überprüfung einmal starten, um sicher zu gehen.',
	'CLEAN_AUTHORS_DONE'			=> 'Bilder ohne Autor gelöscht.',
	'CLEAN_CHANGED'					=> 'Autor in "Gast" geändert.',
	'CLEAN_COMMENTS_DONE'			=> 'Kommentare ohne Autor gelöscht.',
	'CLEAN_ENTRIES_DONE'			=> 'Dateien ohne Datenbank-Einträge gelöscht.',
	'CLEAN_GALLERY'					=> 'Galerie reinigen',
	'CLEAN_GALLERY_ABORT'			=> 'Reinigung abgebrochen!',
	'CLEAN_PERSONALS_DONE'			=> 'Persönliche Alben ohne Besitzer gelöscht.',
	'CLEAN_PERSONALS_BAD_DONE'		=> 'Persönliche Alben der gewählten Benutzer gelöscht.',
	'CLEAN_SOURCES_DONE'			=> 'Datenbank-Einträge ohne Dateien gelöscht.',
	'COLS_PER_PAGE'					=> 'Anzahl der Spalten auf Seite Vorschau-Seite',
	'COMMENT'						=> 'Kommentar',
	'COMMENT_ID'					=> 'Kommentar-ID',
	'COMMENT_SYSTEM'				=> 'Kommentar System aktivieren',
	'CONFIRM_CLEAN'					=> 'Dieser Vorgang kann nicht Rückgängig gemacht werden!',
	'CONFIRM_CLEAN_AUTHORS'			=> 'Bilder ohne Autor löschen?',
	'CONFIRM_CLEAN_COMMENTS'		=> 'Kommentare ohne Autor löschen?',
	'CONFIRM_CLEAN_ENTRIES'			=> 'Dateien ohne Datenbank-Einträge löschen?',
	'CONFIRM_CLEAN_PERSONALS'		=> 'Persönliche Alben ohne Besitzer löschen?',
	'CONFIRM_CLEAN_PERSONALS_BAD'	=> 'Persönliche Alben der gewählten Benutzer löschen?',
	'CONFIRM_CLEAN_SOURCES'			=> 'Datenbank-Einträge ohne Dateien löschen?',
	'COPY_PERMISSIONS'				=> 'Kopiere Berechtigungen von',
	'CREATE_ALBUM'					=> 'Neues Album erstellen',

	'DEFAULT_SORT_METHOD'			=> 'Voreingestellte Sortiermethode',
	'DEFAULT_SORT_ORDER'			=> 'Voreingestellte Sortierreihenfolge',
	'DELETE_ALBUM'					=> 'Album löschen',
	'DELETE_ALBUM_EXPLAIN'			=> 'Das untere Formular erlaubt Dir ein Album zu löschen und zu entscheiden ob du die Bilder löschen oder verschieben möchtest.',
	'DELETE_ALBUM_SUBS'				=> 'Bitte erst die angehängten Alben löschen',
	'DELETE_IMAGES'					=> 'Bilder löschen',
	'DELETE_PERMISSIONS'			=> 'Berechtigungen löschen',
	'DELETE_SUBS'					=> 'Angehängte Alben löschen',
	'DISP_EXIF_DATA'				=> 'Exif-Daten anzeigen',
	'DISP_FAKE_THUMB'				=> 'Thumbnail in der Album-Liste anzeigen',
	'DISP_PERSONAL_ALBUM_PROFIL'	=> 'Link zum persönlichen Album im Profil anzeigen',
	'DISP_TOTAL_IMAGES'				=> '"Bilder insgesamt" auf der index.' . $phpEx . ' anzeigen.',
	'DISP_USER_IMAGES_PROFIL'		=> 'Statistik über hochgeladene Bilder im Profil anzeigen',
	'DONT_COPY_PERMISSIONS'			=> 'Berechtigungen nicht kopieren',

	'EDIT_ALBUM'					=> 'Album bearbeiten',

	'FAKE_THUMB_SIZE'				=> 'Thumbnailgröße',
	'FAKE_THUMB_SIZE_EXP'			=> 'Wenn du die volle Größe wählst, denke an die 16 Pixel für die schwarze Info-Zeile',

	'GALLERY_ALBUMS_TITLE'			=> 'Galerie Alben Kontrolle',
	'GALLERY_CLEAR_CACHE_CONFIRM'	=> 'Wenn du den Cache Feature für das Vorschaubild benutzt, musst du nach einer Änderungen an den Einstellungen in "Gallery konfigurieren" den Cache deiner Vorschaubilder leeren, um sie neu generieren zu lassen.',
	'GALLERY_CONFIG'				=> 'Galerie Konfiguration',
	'GALLERY_CONFIG_EXPLAIN'		=> 'Hier kannst du die Allgemeinen Einstellungen von phpBB Galerie durchführen.',
	'GALLERY_CONFIG_UPDATED'		=> 'Galerie Konfiguration wurde erfolgreich aktualisiert',
	'GALLERY_INDEX'					=> 'Galerie-Index',
	'GALLERY_STATS'					=> 'Galerie Statistik',
	'GALLERY_VERSION'				=> 'Version der phpBB Gallery',//ja mit ll und y, da es sich um den MOD-Namen handelt
	'GD_VERSION'					=> 'GD Version optimieren',
	'GUPLOAD_DIR_SIZE'				=> 'Größe des upload/-Ordners',

	'HANDLE_IMAGES'					=> 'Was soll mit Bildern passieren',
	'HANDLE_SUBS'					=> 'Was soll mit angehängten Alben passieren',
	'HOTLINK_ALLOWED'				=> 'Erlaubt Domains für Hotlink',
	'HOTLINK_ALLOWED_EXP'			=> 'getrennt durch ein Komma',
	'HOTLINK_PREVENT'				=> 'Hotlink Prävention',

	'IMAGE_DESC_MAX_LENGTH'			=> 'Bild Beschreibung / Kommentar Max Länge (Bytes)',
	'IMAGE_ID'						=> 'Bild-ID',
	'IMAGE_SETTINGS'				=> 'Einstellungen zu den Bildern',
	'IMAGES_PER_DAY'				=> 'Bilder pro Tag',
	'IMPORT_ALBUM'					=> 'Zielalbum:',
	'IMPORT_DEBUG_MES'				=> '%1$s Bilder importiert. Es sind noch %2$s Bilder zu importieren.',
	'IMPORT_DIR_EMPTY'				=> 'Das Verzeichnis %s ist leer. Du musst die Bilder erst hochladen, bevor du sie importieren kannst.',
	'IMPORT_FINISHED'				=> 'Alle %1$s Bilder erfolgreich importiert.',
	'IMPORT_MISSING_ALBUM'			=> 'Wähle bitte ein Album aus, in das die Bilder importiert werden sollen.',
	'IMPORT_SELECT'					=> 'Wähle die Bilder aus, die importiert werden sollen. Bilder die erfolgreich importiert wurden, werden aus der Auswahl gelöscht. Die anderen Bilder stehen dir danach noch zur Verfügung.',
	'IMPORT_USER'					=> 'Hochgeladen durch',
	'IMPORT_USER_EXP'				=> 'Du kannst die Bilder auch einem anderem Mitglied zuordnen lassen.',
	'INFO_LINE'						=> 'Dateigröße auf dem Thumbnail anzeigen',

	'MANAGE_CRASHED_ENTRIES'		=> 'Defekte Einträge verwalten',
	'MANAGE_CRASHED_IMAGES'			=> 'Defekte Bilder verwalten',
	'MANAGE_PERSONALS'				=> 'Persönliche Alben verwalten',
	'MAX_IMAGES_PER_ALBUM'			=> 'Die maximale Anzahl der Bilder für jedes Album (-1 = unbegrenzt)',
	'MEDIUM_CACHE'					=> 'Verkleinerte Bilder für die Image-page cachen',
	'MEDIUM_DIR_SIZE'				=> 'Größe des medium/-Ordners',
	'MISSING_ALBUM_NAME'			=> 'Du musst einen Namen für das Album eintragen.',
	'MISSING_AUTHOR'				=> 'Bilder ohne gültigen Autor',
	'MISSING_AUTHOR_C'				=> 'Kommentare ohne gültigen Autor',
	'MISSING_ENTRY'					=> 'Dateien ohne Datenbank-Eintrag',
	'MISSING_OWNER'					=> 'Persönliche Alben ohne Besitzer',
	'MISSING_OWNER_EXP'				=> 'Beim Löschen werden alle Subalben, Bilder und Kommentare mit gelöscht.',
	'MISSING_SOURCE'				=> 'Datenbank-Einträge ohne Datei',

	'NEW_ALBUM_CREATED'				=> 'Neues Album wurde erfolgreich erstellt',
	'NO_ALBUM_SELECTED'				=> 'Du musst mindestens ein Album auswählen.',
	'NO_PARENT_ALBUM'				=> '&raquo; kein übergeordnetes Album',
	'NO_SUBALBUMS'					=> 'Keine Alben angehängt',
	'NUMBER_ALBUMS'					=> 'Anzahl von Alben',
	'NUMBER_IMAGES'					=> 'Anzahl von Bilder',
	'NUMBER_PERSONALS'				=> 'Anzahl von Persönlichen Alben',

	'OWN_PERSONAL_ALBUMS'			=> 'Eigene persönliche Alben',

	'PERMISSION'					=> 'Berechtigung',
	'PERMISSION_NEVER'				=> 'Nie',
	'PERMISSION_NO'					=> 'Nein',
	'PERMISSION_YES'				=> 'Ja',

	'PERMISSION_A_LIST'				=> 'Kann das Album sehen',
	'PERMISSION_ALBUM_COUNT'		=> 'Anzahl der möglichen persönlichen Subalben',
	'PERMISSION_C'					=> 'Kommentare',
	'PERMISSION_C_DELETE'			=> 'Kann Kommentare löschen',
	'PERMISSION_C_EDIT'				=> 'Kann Kommentare editieren',
	'PERMISSION_C_POST'				=> 'Kann Bilder kommentieren',
	'PERMISSION_C_READ'				=> 'Kann Kommentare lesen',
	'PERMISSION_I'					=> 'Bilder',
	'PERMISSION_I_APPROVE'			=> 'Kann Bilder ohne Freigabe erstellen',
	'PERMISSION_I_COUNT'			=> 'Anzahl der hochladbaren Bilder',
	'PERMISSION_I_DELETE'			=> 'Kann Bilder löschen',
	'PERMISSION_I_EDIT'				=> 'Kann Bilder bearbeiten',
	'PERMISSION_I_LOCK'				=> 'Kann Bilder sperren',
	'PERMISSION_I_RATE'				=> 'Kann Bilder bewerten',
	'PERMISSION_I_REPORT'			=> 'Kann Bilder melden',
	'PERMISSION_I_UPLOAD'			=> 'Kann Bilder hochladen',
	'PERMISSION_I_VIEW'				=> 'Kann Bilder sehen',
	'PERMISSION_I_WATERMARK'		=> 'Kann Bilder ohne Wasserzeichen sehen',
	'PERMISSION_M'					=> 'Moderation',
	'PERMISSION_MISC'				=> 'Sonstiges', //Miscellaneous
	'PERMISSION_M_COMMENTS'			=> 'Kann Kommentare moderieren',
	'PERMISSION_M_DELETE'			=> 'Kann Bilder löschen',
	'PERMISSION_M_EDIT'				=> 'Kann Bilder bearbeiten',
	'PERMISSION_M_MOVE'				=> 'Kann Bilder verschieben',
	'PERMISSION_M_REPORT'			=> 'Kann Meldungen bearbeiten',
	'PERMISSION_M_STATUS'			=> 'Kann Bilder freischalten und sperren',

	'PERMISSION_EMPTY'				=> 'Du hast nicht alle Berechtigungen gesetzt.',
	'PERMISSION_NO_GROUP'			=> 'Du hast keine Gruppe ausgewählt, für die du Berechtigungen vergeben möchtest.',
	'PERMISSIONS_STORED'			=> 'Berechtigungen erfolgreich gespeichert.',
	'PERSONAL_ALBUM_INDEX'			=> 'Persönliche Alben in der Galerie-Übersicht als Album anzeigen',
	'PERSONAL_ALBUM_INDEX_EXP'		=> 'Wenn "Nein" ausgewählt ist, wird der Link unterhalb der Alben angezeigt.',
	'PURGED_CACHE'					=> 'Cache geleert',

	'RATE_SCALE'					=> 'Bewertungsskala',
	'RATE_SYSTEM'					=> 'Bewertungssystem aktivieren',
	'REMOVE_IMAGES_FOR_CAT'			=> 'Du musst erst die Bilder aus dem Album entfernen, bevor du das Album zu einer Kategorie machen kannst.',
	'RESIZE_IMAGES'					=> 'Größere Bilder verkleinern',
	'RESYNC_IMAGECOUNTS'			=> 'Anzahl der hochgeladenen Bilder resynchronisieren',
	'RESYNC_IMAGECOUNTS_CONFIRM'	=> 'Bist du sicher, dass du die Anzahl der hochgeladenen Bilder resynchronisieren willst?',
	'RESYNC_IMAGECOUNTS_EXPLAIN'	=> 'Es werden nur Bilder mitgezählt, die noch existieren.',
	'RESYNC_LAST_IMAGES'			=> '"Letztes Bild" neu ermitteln',
	'RESYNC_PERSONALS'				=> 'Persönliche Alben resynchronisieren',
	'RESYNC_PERSONALS_CONFIRM'		=> 'Bist du sicher, dass du die Persönliche Alben resynchronisieren willst?',
	'RESYNCED_IMAGECOUNTS'			=> 'Anzahl der hochgeladenen Bilder resynchronisiert',
	'RESYNCED_LAST_IMAGES'			=> '"Letztes Bild" neu ermittelt',
	'RESYNCED_PERSONALS'			=> 'Persönliche Alben resynchronisiert',
	'ROWS_PER_PAGE'					=> 'Anzahl der Zeilen auf Bildvorschau-Seite',

	'RRC_GINDEX'					=> 'Neueste & zufällige Bilder & Kommentare - Feature',
	'RRC_GINDEX_COLUMNS'			=> 'Spalten',
	'RRC_GINDEX_COMMENTS'			=> 'Kommentare einklappen',
	'RRC_GINDEX_MODE'				=> 'Modus',
	'RRC_GINDEX_ROWS'				=> 'Zeilen',
	'RRC_MODE_AALL'					=> 'Nichts',
	'RRC_MODE_ACOMMENTS'			=> 'Ohne Kommentare',
	'RRC_MODE_ALL'					=> 'Kommentare, Neueste & zufällige Bilder',
	'RRC_MODE_ARANDOM'				=> 'Ohne zufällige Bilder',
	'RRC_MODE_ARECENT'				=> 'Ohne neueste Bilder',
	'RRC_MODE_COMMENTS'				=> 'Kommentare',
	'RRC_MODE_RANDOM'				=> 'Zufällige Bilder',
	'RRC_MODE_RECENT'				=> 'Neueste Bilder',

	'RSZ_HEIGHT'					=> 'Maximale Höhe beim anzeigen eines Bildes',
	'RSZ_WIDTH'						=> 'Maximale Breite beim anzeigen eines Bildes',

	'SELECT_ALBUMS'					=> 'Wähle Alben',
	'SELECT_GROUPS'					=> 'Wähle Gruppen',
	'SELECT_PERMISSIONS'			=> 'Wähle Berechtigungen',
	'SELECTED_ALBUMS'				=> 'Ausgewählte Alben',
	'SELECTED_GROUPS'				=> 'Ausgewählte Gruppen',
	'SET_PERMISSIONS'				=> '<br /><a href="%s">Berechtigungen</a> jetzt vergeben.',
	'SHORTED_IMAGENAMES'			=> 'Bildernamen kürzen',
	'SHORTED_IMAGENAMES_EXP'		=> 'Sollte der Name eines Bildes zu lange sein und kein Leerzeichen enthalten, kann es zu Problemen im Layout führen.',
	'SORRY_NO_STATISTIC'			=> 'Entschuldigung, aber dieser Statistik-Wert ist noch nicht verfügbar.',

	'THUMBNAIL_CACHE'				=> 'Bildvorschau Cache',
	'THUMBNAIL_QUALITY'				=> 'Bildvorschau Qualität (1-100)',
	'THUMBNAIL_SETTINGS'			=> 'Bildvorschau Einstellungen',

	'UC_IMAGE_NAME'					=> 'Bildname',
	'UC_IMAGE_ICON'					=> '"Letzes Bild"-Icon',
	'UC_IMAGEPAGE'					=> 'Bild auf der Image-page (mit Kommentaren und Bewertungen)',
	'UC_LINK_CONFIG'				=> 'Link Konfiguration',
	'UC_LINK_HIGHSLIDE'				=> 'Highslide-Feature öffnen',
	'UC_LINK_IMAGE'					=> 'Bild öffnen',
	'UC_LINK_IMAGE_PAGE'			=> 'Image-page (mit Kommentaren und Bewertungen) öffnen',
	'UC_LINK_LYTEBOX'				=> 'Lytebox-Feature öffnen',
	'UC_LINK_NONE'					=> 'Kein Link',
	'UC_THUMBNAIL'					=> 'Thumbnail',
	'UPLOAD_IMAGES'					=> 'Mehrere Bilder auf einmal hochladen',

	'VIEW_IMAGE_URL'				=> 'Link zum Bild auf der Imagepage anzeigen',

	'WATERMARK'						=> 'Wasserzeichen',
	'WATERMARK_HEIGHT'				=> 'Mindesthöhe',
	'WATERMARK_HEIGHT_EXP'			=> 'Um zu verhindern, dass kleine Bilder vollkommen vom Wasserzeichen verdeckt werden, kannst du hier eine Mindestgröße angeben.',
	'WATERMARK_IMAGES'				=> 'Wasserzeichen aktivieren',
	'WATERMARK_OPTIONS'				=> 'Wasserzeichen-Einstellungen',
	'WATERMARK_SOURCE'		 		=> 'Wasserzeichen Bild (Releativer Pfad zum Forums Root)',
	'WATERMARK_WIDTH'				=> 'Mindestbreite',
	'WATERMARK_WIDTH_EXP'			=> 'Um zu verhindern, dass kleine Bilder vollkommen vom Wasserzeichen verdeckt werden, kannst du hier eine Mindestgröße angeben.',
));

?>