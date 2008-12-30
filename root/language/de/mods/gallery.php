<?php
/**
*
* gallery [Deutsch]
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
	'ALBUM'							=> 'Album',
	'ALBUM_DELETE_CONFIRM'			=> 'Bist du sicher das du dieses Bild löschen möchtest?',
	'ALBUM_ID_NOT_EXIST'			=> 'Album ID %s existiert nicht',
	'ALBUM_IS_CATEGORY'				=> 'Das Album, in welches du dich gemogelt hast, ist eine Kategorie.<br />In Kategorien können keine Bilder hochgeladen werden.',
	'ALBUM_NAME'					=> 'Albumname',
	'ALBUM_NOT_EXIST'				=> 'Dieses Album existiert nicht',
	'ALBUM_PERMISSIONS'				=> 'Berechtigungen in diesem Album',
	'ALBUM_REACHED_QUOTA'			=> 'Dieses Album hat das Kontingent zum hochladen von Bildern erreicht. Es können keine weiteren Bilder mehr hochgeladen werden. Für weitere Information wende dich bitte an einen Administrator',
	'ALBUM_UPLOAD_NEED_APPROVAL'	=> 'Dein Bild wurde erfolgreich hochgeladen.<br /><br />Aber die Funktion der Genehmigung von Bildern vor der Veröffentlichung ist aktiv. Somit muss dein Bild vor der Veröffentlichung von einem Administrator oder einem Moderator genehmigt werden.',
	'ALBUM_UPLOAD_SUCCESSFUL'		=> 'Dein Bild wurde erfolgreich hochgeladen.',
	'ALBUMS'						=> 'Alben',
	'ALL_IMAGES'					=> 'Alle Bilder',
	'ALREADY_RATED'					=> 'Du hast dieses Bild bereits bewertet',
	'APPROVAL'						=> 'Genehmigung',
	'APPROVE'						=> 'Freigeben',
	'APPROVE_IMAGE'					=> 'Bild freischalten',
	'APPROVED'						=> 'Freigegeben',

	//@todo
	'ALBUM_COMMENT_CAN'			=> 'Du <strong>darfst</strong> Bilder in diesem Album kommentieren',
	'ALBUM_COMMENT_CANNOT'		=> 'Du <strong>darfst keine</strong> Bilder in diesem Album kommentieren',
	'ALBUM_DELETE_CAN'			=> 'Du <strong>darfst</strong> deine Bilder in diesem Album löschen',
	'ALBUM_DELETE_CANNOT'		=> 'Du <strong>darfst</strong> deine Bilder in diesem Album <strong>nicht</strong> löschen',
	'ALBUM_EDIT_CAN'			=> 'Du <strong>darfst</strong> deine Bilder in diesem Album bearbeiten',
	'ALBUM_EDIT_CANNOT'			=> 'Du <strong>darfst</strong> deine Bilder in diesem Album <strong>nicht</strong> bearbeiten',
	'ALBUM_RATE_CAN'			=> 'Du <strong>darfst</strong> Bilder in diesem Album bewerten',
	'ALBUM_RATE_CANNOT'			=> 'Du <strong>darfst keine</strong> Bilder in diesem Album bewerten',
	'ALBUM_UPLOAD_CAN'			=> 'Du <strong>darfst</strong> neue Bilder in diesem Album hochladen',
	'ALBUM_UPLOAD_CANNOT'		=> 'Du <strong>darfst keine</strong> neuen Bilder in diesem Album hochladen',
	'ALBUM_VIEW_CAN'			=> 'Du <strong>darfst</strong> Bilder in diesem Album ansehen',
	'ALBUM_VIEW_CANNOT'			=> 'Du <strong>darfst keine</strong> Bilder in diesem Album ansehen',


	//@todo
	'GIF_ALLOWED'					=> 'Es ist erlaubt GIF- Dateien hochzuladen',
	'JPG_ALLOWED'					=> 'Es ist erlaubt JPG- Dateien hochzuladen',
	'PNG_ALLOWED'					=> 'Es ist erlaubt PNG- Dateien hochzuladen',

	'BAD_UPLOAD_FILE_SIZE'			=> 'Deine hochgeladene Datei ist entweder zu gross oder defekt',
	'BROWSING_ALBUM'				=> 'Mitglieder in diesem Album: %1$s',
	'BROWSING_ALBUM_GUEST'			=> 'Mitglieder in diesem Album: %1$s und %2$d Gast',
	'BROWSING_ALBUM_GUESTS'			=> 'Mitglieder in diesem Album: %1$s und %2$d Gäste',

	'CHANGE_IMAGE_STATUS'			=> 'Bildstatus ändern',
	'CLICK_RETURN_ALBUM'			=> 'Klicke %shier%s um zum Album zurückzukehren',
	'CLICK_RETURN_ALBUM_TARGET'		=> 'Klicke %shier%s um zum neuen Album zurückzukehren',
	'CLICK_RETURN_GALLERY_INDEX'	=> 'Klicke %shier%s um zur Startseite der Galerie zu gelangen',
	'CLICK_RETURN_IMAGE'			=> 'Klicke %shier%s um zum Bild zurückzukehren',
	'CLICK_RETURN_MODCP'			=> 'Klicke %shier%s um zum Moderatons-Bereich zurückzukehren',
	'CLICK_RETURN_PERSONAL_ALBUM'	=> 'Klicke %shier%s um zu den persönlichen Alben zurückzukehren',
	'CLICK_VIEW_COMMENT'			=> 'Klicke %shier%s um Deine Kommentare anzusehen',
	'COMMENT'						=> 'Kommentar',
	'COMMENT_DELETE_CONFIRM'		=> 'Bist du sicher, das du diesen Kommentar löschen möchtest?',
	'COMMENT_IMAGE'					=> 'Schreibt einen Kommentar über ein Bild im Album %s',
	'COMMENT_LENGTH'				=> 'Gib deinen Kommentar hier ein. Er darf nicht mehr als <strong>%d</strong> Zeichen enthalten.',
	'COMMENT_NO_TEXT'				=> 'Bitte gebe deinen Kommentar ein',
	'COMMENT_ON'					=> 'Kommentar zu',
	'COMMENT_STORED'				=> 'Dein Kommentar wurde erfolgreich hinzugefügt.',
	'COMMENT_TOO_LONG'				=> 'Dein Kommentar ist zu lang',
	'COMMENTS'						=> 'Kommentare',
	'CURRENT_RATING'				=> 'Aktuelle Bewertung',

	'DELETE_COMMENT'				=> 'Kommentar löschen?',
	'DELETE_COMMENT_CONFIRM'		=> 'Bist Du Dir sicher das Du den Kommentar löschen möchtest?',
	'DELETE_IMAGE'					=> 'Löschen',
	'DELETE_IMAGE2'					=> 'Das Bild löschen?',
	'DELETE_IMAGE2_CONFIRM'			=> 'Bist Du Dir sicher das Du das Bild löschen möchtest?',
	'DELETED_COMMENT'				=> 'Kommentar gelöscht',
	'DELETED_COMMENT_NOT'			=> 'Kommentar wurde nicht gelöscht',
	'DELETED_IMAGE'					=> 'Bild wurde gelöscht',
	'DELETED_IMAGE_NOT'				=> 'Bild wurde nicht gelöscht',
	'DESC_TOO_LONG'					=> 'Deine Beschreibung ist zu lang',
	'DESCRIPTION_LENGTH'			=> 'Gib deine Beschreibung hier ein. Sie darf nicht mehr als <strong>%d</strong> Zeichen enthalten.',
	'DETAILS'						=> 'Details',
	'DONT_RATE_IMAGE'				=> 'Bild nicht bewerten',

	'EDIT_COMMENT'					=> 'Kommentar ändern',
	'EDIT_IMAGE'					=> 'Bearbeiten',
	'EDIT_IMAGE_INFO'				=> 'Bearbeite Bild-Information',
	'EDITED_TIME_TOTAL'				=> 'Zuletzt bearbeitet von %s am %s; insgesamt %d mal bearbeitet',
	'EDITED_TIMES_TOTAL'			=> 'Zuletzt bearbeitet von %s am %s; insgesamt %d mal bearbeitet',

	'FAVORITE_IMAGE'						=> 'zu Lieblingsbildern hinzufügen',
	'FAVORITED_IMAGE'						=> 'Das Bild wurde zu deinen Lieblingsbildern hinzugefügt.',
	'FILE'									=> 'Datei',
	'FILETYPE_AND_THUMBNAIL_DO_NOT_MATCH'	=> 'Das Bild und Vorschaubild müssen vom gleichen Typ sein',

	//@todo
	'GALLERY_INSTALLATION'			=> 'v%s installieren',
	'GALLERY_UPDATE'				=> 'v%s zu v%s aktualisieren',
	'GALLERY_UPDATE_SMARTOR'		=> 'Smartor-Album zu v%s aktualisieren',
	'GALLERY_UPDATE_SMARTOR2'		=> 'Öffne bitte die Datei install_gallery/install.php<br />Suche diese Zeile: $smartor_prefix = \'\';//ENTER YOUR PREFIX HERE example $smartor_prefix = \'phpbb2_\';<br />und passe sie auf Deinen Präfix an.<br /><br />Danach rufe diese datei erneut auf.',
	'GALLERY_INSTALL_NOTE1'			=> 'Script für die automatische Galerie-Datenbank-Erstellung.<br /><br /><span style="color:red; font-weight: bold;">Diese Script wird alle Einstellungen, Kategorien, Bilder und Kommentare von vorherigen Installationen löschen!</span><br /><span style="color:green; font-weight: bold;">Ein Update löscht diese Daten nicht.</span><br />Bist Du Dir absolut sicher?!',
	'GALLERY_INSTALL_NOTE2'			=> '<span style="color:green; font-weight: bold; font-size: 1.5em;">Galerie-Datenbank erfolgreich erstellt.</span>',
	'GALLERY_INSTALL_NOTE3'			=> 'Du musst Gründer Rechte besitzen um dieses Script ausführen zu können.',
	'GALLERY_INSTALL_NOTE4'			=> '<span style="color:green; font-weight: bold; font-size: 1.5em;">Galerie-Datenbank erfolgreich aktualisiert.</span>',

	'IMAGE_#'							=> '1 Bild',
	'IMAGE_ALREADY_REPORTED'			=> 'Das Bild wurde bereits gemeldet.',
	'IMAGE_BBCODE'						=> 'BB-Code',
	'IMAGE_DAY'							=> '%.2f Bilder pro Tag',
	'IMAGE_DESC'						=> 'Bildbeschreibung',
	'IMAGE_LOCKED'						=> 'Entschuldigung, aber dieses Bild wurde gesperrt. Du kannst für dieses Bild keine Kommentare mehr abgeben.',
	'IMAGE_NAME'						=> 'Bildname',
	'IMAGE_NOT_EXIST'					=> 'Dieses Bild existiert nicht',
	'IMAGE_PCT'							=> '%.2f%% aller Bilder',
	'IMAGE_STATUS'						=> 'Status',
	'IMAGE_TITLE'						=> 'Bild Titel',
	'IMAGE_URL'							=> 'Bildlink',
	'IMAGES'							=> 'Bilder',
	'IMAGES_#'							=> '%s Bilder',
	'IMAGES_APPROVED_SUCCESSFULLY'		=> 'Dein(e) Bild(er) wurde(n) freigegeben',
	'IMAGES_DELETED_SUCCESSFULLY'		=> 'Diese(s) Bild(er) wurde(n) erfolgreich gelöscht',
	'IMAGES_LOCKED_SUCCESSFULLY'		=> 'Dein(e) Bild(er) wurde(n) erfolgreich gesperrt',
	'IMAGES_MOVED_SUCCESSFULLY'			=> 'Dein(e) Bild(er) wurde(n) erfolgreich verschoben',
	'IMAGES_REPORTED_SUCCESSFULLY'		=> 'Das Bild wurde erfolgreich gemeldet',
	'IMAGES_UNAPPROVED_SUCCESSFULLY'	=> 'Dein(e) Bild(er) wurde(n) erfolgreich auf ungeprüft gesetzt',
	'IMAGES_UNLOCKED_SUCCESSFULLY'		=> 'Dein(e) Bild(er) wurde(n) erfolgreich entsperrt',
	'IMAGES_UPDATED_SUCCESSFULLY'		=> 'Deine Bilderinformationen wurden erfolgreich aktualisiert',
	'INVALID_REQUEST'					=> 'Ungültiger Script Aufruf.',
	'INVALID_USERNAME'					=> 'Der Benutzername ist ungültig',

	'LAST_COMMENT'						=> 'Letzter Kommentar',
	'LAST_IMAGE'						=> 'Letztes Bild',
	'LOCK'								=> 'Sperren',
	'LOCKED'							=> 'Gesperrt',
	'LOGIN_EXPLAIN_PERSONAL_GALLERY'	=> 'Du musst registriert und angemeldet sein, um Persönliche Alben anzuschauen.',
	'LOGIN_EXPLAIN_UPLOAD'				=> 'Du musst registriert und angemeldet sein, um Bilder hochladen zu können.',
	'LOGIN_TO_COMMENT'					=> 'Melde Dich an, um ein Kommentar abzugeben',
	'LOGIN_TO_RATE'						=> 'Melde Dich an, um dieses Bild zu bewerten',
	'LOOP_EXP'							=> 'Wenn du mehrere Bilder auf einmal hochlädst, kannst du sie mit <span style="font-weight: bold;">{NUM}</span> in der Bildbeschreibung und im Bild-Titel durchnummerieren.<br />
											Der Zähler beginnt mit der Zahl, die du hier eingibst. Beispiel: "Bild {NUM}" ergibt: "Bild 1", "Bild 2", usw.',

	'MAX_FILE_SIZE'					=> 'Maximale Dateigröße (bytes)',
	'MAX_HEIGHT'					=> 'Maximale Bildhöhe (pixels)',
	'MAX_LENGTH'					=> 'Max Länge (bytes)',
	'MAX_WIDTH'						=> 'Maximale Bildbreite (pixels)',
	'MISSING_COMMENT'				=> 'Keinen Text eingegeben',
	'MISSING_IMAGE_TITLE'			=> 'Du musst einen Titel für Dein Bild angeben',
	'MISSING_MODE'					=> 'Kein Modus ausgewählt',
	'MISSING_REPORT_REASON'			=> 'Du musst einen Grund angeben um das Bild zu melden.',
	'MISSING_SUBMODE'				=> 'Kein Sub-Modus ausgewählt',
	'MISSING_USERNAME'				=> 'Kein Benutzernamen angegeben',
	'MODCP'							=> 'Moderations-Bereich',
	'MOVE_TO_ALBUM'					=> 'Ins Album verschieben',

	'NEW_COMMENT'							=> 'Neuer Kommentar',
	'NO_ALBUMS'								=> 'In dieser Galerie gibt es keine Alben',
	'NO_COMMENTS'							=> 'Noch keine Kommentare',
	'NO_IMAGE_SPECIFIED'					=> 'Kein Bild angegeben',
	'NO_IMAGES'								=> 'keine Bilder',
	'NO_IMAGES_FOUND'						=> 'Es wurden keine Bilder gefunden.',
	'NO_IMAGES_LONG'						=> 'In diesem Album gibt es keine Bilder.',
	'NO_MOVE_LEFT'							=> 'Es gibt keine weiteren von Dir berechtigten Alben mehr in die Du die Bilder verschieben darfst.',
	'NO_RATE_ON_OWN_IMAGES'					=> 'Du darfst deine eigenen Bilder nicht bewerten.',
	'NONE'									=> 'Keiner',
	'NOT_ALLOWED_FILE_TYPE'					=> 'Dieser Datei Typ ist nicht erlaubt',
	'NOT_ALLOWED_TO_CREATE_PERSONAL_ALBUM'	=> 'Es tut uns leid, die Administratoren von diesem Board erlauben Dir nicht, ein persönliches Album anzulegen.',
	'NOT_APPROVED'							=> 'Ungeprüft',
	'NOT_RATED'								=> 'Nicht bewertet',

	'ORDER'							=> 'Reihenfolge',
	'ORIG_FILENAME'					=> 'Dateinamen als Bild-Titel verwenden (das Eingabefeld ist ohne Funktion)',
	'OUT_OF_RANGE_VALUE'			=> 'Wert ist ausserhalb des Bereichs',

	'PERSONAL_ALBUM_EXPLAIN'		=> 'Du kannst die persönlichen Alben anderer Mitglieder mit einem Klick auf den Link in ihrem Profil ansehen.',
	'PERSONAL_ALBUM_NOT_CREATED'	=> 'Die persönliche Galerie von %s ist leer oder wurde noch nicht erstellt.',
	'PERSONAL_ALBUM_OF_USER'		=> 'Persönliches Album von %s',
	'PERSONAL_ALBUMS'				=> 'Persönliche Alben',
	'PLAIN_TEXT_ONLY'				=> 'Nur Textformat',
	'POST_COMMENT'					=> 'Kommentar schreiben',
	'POST_COMMENT_RATE_IMAGE'		=> 'Kommentar schreiben und Bild bewerten',
	'POSTER'						=> 'Autor',

	'RANDOM_IMAGES'					=> 'Zufällige Bilder',
	'RATE_IMAGE'					=> 'Bild bewerten',
	'RATE_STRING'					=> '%1$s (%2$s Bewertung)', // 1.Rating-average 2.number of rates
	'RATES_COUNT'					=> 'Bewertungen',
	'RATES_STRING'					=> '%1$s (%2$s Bewertungen)',
	'RATING'						=> 'Bewertung',
	'RATING_AVG'					=> 'Bewertungsdurchschnitt',
	'RATING_SUCCESSFUL'				=> 'Das Bild wurde erfolgreich bewertet.',
	'READ_REPORT'					=> 'Meldung ansehen',
	'RECENT_COMMENTS'				=> 'Neuesten Kommentare',
	'RECENT_IMAGES'					=> 'Neuesten Bilder',
	'RECENT_PUBLIC_IMAGES'			=> 'Neuesten öffentliche Bilder',
	'REPORT_IMAGE'					=> 'Bild melden',

	'SEARCH_USER_IMAGES'			=> 'Bilder des Mitglieds anzeigen',
	'SEARCH_USER_IMAGES_OF'			=> 'Bilder von %s',
	'SHOW_PERSONAL_ALBUM_OF'		=> 'Persönliches Album von %s anzeigen',
	'SLIDE_SHOW'					=> 'Diashow',
	'SLIDE_SHOW_HIGHSLIDE'			=> 'Um die Diashow zu starten, klicke auf einen der Bildnamen und dann auf das "play"-Icon:',
	'SLIDE_SHOW_START'				=> 'Um die Diashow zu starten, klicke auf einen der Bildnamen:',
	'SORT_ASCENDING'				=> 'Aufsteigend',
	'SORT_DESCENDING'				=> 'Absteigend',
	'STATUS'						=> 'Status',
	'SUBALBUMS'						=> 'Subalben',
	'SUBALBUM'						=> 'Subalbum',

	'THUMBNAIL_SIZE'				=> 'Vorschaubildgrösse (Pixel)',
	'TOTAL_IMAGES'					=> 'Bilder insgesamt',

	'UNAPPROVE'							=> 'Genehmigung entziehen',
	'UNFAVORITE_IMAGE'					=> 'aus Lieblingsbildern entfernen',
	'UNFAVORITED_IMAGE'					=> 'Das Bild wurde aus deinen Lieblingsbildern entfernt.',
	'UNFAVORITED_IMAGES'				=> 'Die Bilder wurde aus deinen Lieblingsbildern entfernt.',
	'UNLOCK'							=> 'Entsperren',
	'UNLOCK_IMAGE'						=> 'Bild entsperren',
	'UNWATCH_ALBUM'						=> 'Album nicht mehr beobachten',
	'UNWATCH_IMAGE'						=> 'Bild nicht mehr beobachten',
	'UNWATCHED_ALBUM'					=> 'Du wirst nicht mehr über neue Bilder in diesem Album benachrichtigt.',
	'UNWATCHED_ALBUMS'					=> 'Du wirst nicht mehr über neue Bilder in diesen Alben benachrichtigt.',
	'UNWATCHED_IMAGE'					=> 'Du wirst nicht mehr über Kommentare zu diesem Bild benachrichtigt.',
	'UNWATCHED_IMAGES'					=> 'Du wirst nicht mehr über Kommentare zu diesen Bildern benachrichtigt.',
	'UPLOAD_IMAGE'						=> 'Bild hochladen',
	'UPLOAD_IMAGE_SIZE_TOO_BIG'			=> 'Die Dimension Deines Bildes ist zu groß',
	'UPLOAD_NO_FILE'					=> 'Du musst deinen Pfad und Dateinamen eingeben',
	'UPLOAD_NO_TITLE'					=> 'Du musst einen Titel für Dein Bild angeben',
	'UPLOAD_THUMBNAIL'					=> 'Lade ein Vorschaubild hoch',
	'UPLOAD_THUMBNAIL_EXPLAIN'			=> 'Es muss der gleiche Dateityp sein wie Dein Bild',
	'UPLOAD_THUMBNAIL_FROM_MACHINE'		=> 'Lade Vorschaubild von Deinem Rechner hoch (es muss der gleiche Dateityp sein wie Dein Bild)',
	'UPLOAD_THUMBNAIL_SIZE_TOO_BIG'		=> 'Die Dimension Deines Vorschaubildes ist zu groß',
	'UPLOAD_TO_ALBUM'					=> 'In Album hochladen',
	'USER_NEARLY_REACHED_QUOTA'			=> 'Du darfst nur %s Bilder hochladen, hast aber schon %s Bilder hochgeladen. Deswegen werden nun nur noch %s Dateifelder angezeigt.',
	'USER_REACHED_QUOTA'				=> 'Du darfst nur %s Bilder hochladen.<br /><br />Für weitere Information wende dich bitte an einen Administrator.',
	'USERS_PERSONAL_ALBUMS'				=> 'Persönliche Alben der Mitglieder',

	'VIEW_ALBUM'					=> 'Album ansehen',
	'VIEW_IMAGE'					=> 'Bild ansehen',
	'VIEW_LATEST_IMAGE'				=> 'Das neueste Bild ansehen',
	'VIEWING_ALBUM'					=> 'Betrachtet Album %s',
	'VIEWING_IMAGE'					=> 'Betrachtet ein Bild im Album %s',
	'VIEWS'							=> 'Betrachtet',

	'WAITING_FOR_APPROVAL'			=> 'Bild(er), die auf Genehmigung warten',
	'WATCH_ALBUM'					=> 'Album beobachten',
	'WATCH_IMAGE'					=> 'Bild beobachten',
	'WATCHING_ALBUM'				=> 'Du wirst über neue Bilder in diesem Album benachrichtigt.',
	'WATCHING_IMAGE'				=> 'Du wirst über Kommentare zu diesem Bild benachrichtigt.',

	'YOUR_COMMENT'					=> 'Dein Kommentar',
	'YOUR_PERSONAL_ALBUM'			=> 'Dein persönliches Album',
	'YOUR_RATING'					=> 'Deine Bewertung',
));

?>