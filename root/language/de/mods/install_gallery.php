<?php
/**
*
* install_gallery [Deutsch]
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
	'CAT_CONVERT'				=> 'phpBB2 konvertieren',
	'CAT_CONVERT_TS'			=> 'TS Gallery konvertieren',

	'CHECK_TABLES'				=> 'Tabellen überprüfung',
	'CHECK_TABLES_EXPLAIN'		=> 'Die folgenden Tabellen müssten existieren, damit sie auch übernommen werden können.',

	'CONVERT_SMARTOR_INTRO'			=> 'Konverter vom „Album-MOD“ von smartor zur „phpBB Gallery“',
	'CONVERT_SMARTOR_INTRO_BODY'	=> 'Mit diesem Konverter, kannst du deine Alben, Bilder, Bewertungen und Kommentare aus dem <a href="http://www.phpbb.com/community/viewtopic.php?f=16&t=74772">Album-MOD</a> von Smartor (getestet mit v2.0.56) und <a href="http://www.phpbbhacks.com/download/5028">Full Album Pack</a> (getestet mit v1.4.1) auslesen und dann in die phpBB Gallery einfügen lassen.<br /><br /><strong>Achtung:</strong> Die <strong>Berechtigungen</strong> werden dabei <strong>nicht übernommen</strong>.',
	'CONVERT_TS_INTRO'				=> 'Konverter von der „TS Gallery“ zur „phpBB Gallery“',
	'CONVERT_TS_INTRO_BODY'			=> 'Mit diesem Konverter, kannst du deine Alben, Bilder, Bewertungen und Kommentare aus der <a href="http://www.phpbb.com/community/viewtopic.php?f=70&t=610509">TS Gallery</a> (getestet mit v0.2.1) auslesen und dann in die phpBB Gallery einfügen lassen.<br /><br /><strong>Achtung:</strong> Die <strong>Berechtigungen</strong> werden dabei <strong>nicht übernommen</strong>.',
	'CONVERT_COMPLETE_EXPLAIN'		=> 'Du hast nun deine Gallery erfolgreich auf die phpBB Gallery v%s konvertiert.<br />Bitte prüfe, ob alle Einträge richtig übernommen wurden, bevor du dein Board durch Löschen des „install“-Verzeichnisses freigibst.<br /><br /><strong>Bitte beachte, dass die Berechtigungen nicht übernohmen wurden. Du musst diese also neu vergeben.</strong><br /><br />Es wird außerdem empfohlen die Datenbank von Einträgen zu befreien, bei denen die Bilder nicht mehr existieren. Dies kann im Administrations-Bereich unter "MODs > phpBB Galerie > Galerie reinigen" getan werden.',

	'CONVERTED_ALBUMS'			=> 'Die Alben wurden erfolgreich kopiert.',
	'CONVERTED_COMMENTS'		=> 'Die Kommentare wurden erfolgreich kopiert.',
	'CONVERTED_IMAGES'			=> 'Die Bilder wurden erfolgreich kopiert.',
	'CONVERTED_PERSONALS'		=> 'Die persönlichen Alben wurden erfolgreich erstellt.',
	'CONVERTED_RATES'			=> 'Die Bewertungen wurden erfolgreich kopiert.',
	'CONVERTED_RESYNC_ALBUMS'	=> 'Die Alben-Statistiken wurden erfolgreich resyncronisiert.',
	'CONVERTED_RESYNC_COMMENTS'	=> 'Die Kommentare wurden erfolgreich resyncronisiert.',
	'CONVERTED_RESYNC_COUNTS'	=> 'Die Zähler-Statistiken wurden erfolgreich resyncronisiert.',
	'CONVERTED_RESYNC_RATES'	=> 'Die Bewertungen wurden erfolgreich resyncronisiert.',

	'FILES_EXISTS'					=> 'Datei existiert noch',
	'FILES_REQUIRED_EXPLAIN'		=> '<strong>Voraussetzung</strong> - die phpBB Gallery muss auf diverse Verzeichnisse zugreifen oder diese beschreiben können, um reibungslos zu funktionieren. Wenn „Nicht beschreibbar“ angezeigt wird, musst du die Befugnisse für die Datei oder das Verzeichnis so ändern, dass phpBB darauf schreiben kann.',
	'FILES_DELETE_OUTDATED'			=> 'Veraltete Dateien löschen',
	'FILES_DELETE_OUTDATED_EXPLAIN'	=> 'Wenn du die Dateien löscht, werden sie entgülig gelöscht und können nicht wiederhergestellt werden!<br /><br />Hinweis:<br />Wenn du weitere Styles und Sprachpakete installiert hast, musst du die Dateien dort von Hand löschen.',
	'FILES_OUTDATED'				=> 'Veraltete Dateien',
	'FILES_OUTDATED_EXPLAIN'		=> '<strong>Veraltete Dateien</strong> - bitte entferne die folgenden Dateien um mögliche Sicherheitslücken zu entfernen.',

	'INSTALL_CONGRATS_EXPLAIN'	=> '<p>Du hast die phpBB Gallery v%s nun erfolgreich installiert.<br/><br/><strong>Bitte lösche oder verschiebe nun das Installations-Verzeichnis „install“ oder nenne es nun um, bevor du dein Board benutzt. Solange dieses Verzeichnis existiert, ist nur der Administrations-Bereich zugänglich.</strong></p>',
	'INSTALL_INTRO_BODY'		=> 'Dieser Assistent ermöglicht dir die Installation der phpBB Gallery in deinem phpBB-Board.',

	'GOTO_GALLERY'				=> 'Gehe zur phpBB Gallery',

	'MISSING_CONSTANTS'			=> 'Bevor du das Installations-Skript aufrufen kannst, musst du deine geänderten Dateien hochladen, insbesondere die includes/constants.php.',
	'MODULES_CREATE_PARENT'		=> 'Übergeordnetes Standard-Modul erstellen',
	'MODULES_PARENT_SELECT'		=> 'Übergeordnetes Modul auswählen',
	'MODULES_SELECT_4ACP'		=> 'Übergeordnetes Modul für den "Administrations-Bereich"',
	'MODULES_SELECT_4LOG'		=> 'Übergeordnetes Modul für das "Gallery-Protokoll"',
	'MODULES_SELECT_4MCP'		=> 'Übergeordnetes Modul für den "Moderations-Bereich"',
	'MODULES_SELECT_4UCP'		=> 'Übergeordnetes Modul für den "Persönlichen Bereich"',
	'MODULES_SELECT_NONE'		=> 'kein übergeordnetes Modul',

	'REQ_GD_LIBRARY'			=> 'GD Library ist installiert',
	'REQUIREMENTS_EXPLAIN'		=> 'Bevor die Installation fortgesetzt werden kann, wird phpBB einige Tests zu deiner Server-Konfiguration und deinen Dateien durchführen, um sicherzustellen, dass du die phpBB Gallery installieren und benutzen kannst. Bitte lies die Ergebnisse aufmerksam durch und fahre nicht weiter fort, bevor alle erforderlichen Tests bestanden sind.',

	'STAGE_ADVANCED_EXPLAIN'		=> 'Bitte wähle die übergeordneten Module für die Module der phpBB Gallery aus. Im Normalfall solltest du diese Einstellungen nicht verändern.',
	'STAGE_COPY_TABLE'				=> 'Datenbank-Tabellen kopieren',
	'STAGE_COPY_TABLE_EXPLAIN'		=> 'Die Datenbank-Tabellen mit den Alben und Benutzer-Informationen der TS Gallery haben die gleichen Namen wie die der phpBB Gallery. Es wurde daher eine Kopie angelegt, um die Daten trotzdem importieren zu können.',
	'STAGE_CREATE_TABLE_EXPLAIN'	=> 'Die von der phpBB Gallery genutzten Datenbank-Tabellen wurden nun erstellt und mit einigen Ausgangswerten gefüllt. Geh weiter zum nächsten Schritt, um die Installation der phpBB Gallery abzuschließen.',
	'SUPPORT_BODY'					=> 'Für die aktuelle, stabile Version der "phpBB Gallery" wird kostenloser Support gewährt. Dies umfasst:</p><ul><li>Installation</li><li>Konfiguration</li><li>Technische Fragen</li><li>Probleme durch eventuelle Fehler in der Software</li><li>Aktualisierung von Release Candidates (RC) oder stabilen Versionen zur aktuellen stabilen Version</li><li>Konvertierungen von smartor\'s Album MOD für phpBB 2.0.x zur phpBB Gallery für phpBB3</li><li>Konvertierungen von der TS Gallery zur phpBB Gallery</li></ul><p>Die Verwendung der Beta-Versionen wird nur beschränkt empfohlen. Sollte ein neues Update erscheinen, wird empfohlen das Update schnell durchzuführen.</p><p>Support gibt es in folgenden Foren:</p><ul><li><a href="http://www.flying-bits.org/">flying-bits.org - Homepage des MOD-Autor\'s nickvergessen</a></li><li><a href="http://www.phpbb.de/">phpbb.de</a></li><li><a href="http://www.phpbb.com/">phpbb.com</a></li></ul><p>',

	'TABLE_ALBUM'				=> 'Tabelle mit den Bildern',
	'TABLE_ALBUM_CAT'			=> 'Tabelle mit den Alben',
	'TABLE_ALBUM_COMMENT'		=> 'Tabelle mit den Kommentaren',
	'TABLE_ALBUM_CONFIG'		=> 'Tabelle mit den Konfigurationswerten',
	'TABLE_ALBUM_RATE'			=> 'Tabelle mit den Bewertungen',
	'TABLE_EXISTS'				=> 'vorhanden',
	'TABLE_MISSING'				=> 'fehlt',
	'TABLE_PREFIX_EXPLAIN'		=> 'Präfix der phpBB2-Installation',

	'UPDATE_INSTALLATION_EXPLAIN'	=> 'Mit dieser Option kannst du deine phpBB Gallery-Version auf den neuesten Stand bringen.',

	'VERSION_NOT_SUPPORTED'		=> 'Leider konnte das Update-Schema für Versionen < 0.2.0 nicht übernommen werden.',
));

?>