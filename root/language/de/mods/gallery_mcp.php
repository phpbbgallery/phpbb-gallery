<?php

/**
*
* @package phpBB3 - gallery
* @version $Id$
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
	'CHOOSE_ACTION'					=> 'gewünschte Aktion auswählen',

	'GALLERY_MCP_MAIN'				=> 'Hauptbereich',
	'GALLERY_MCP_QUEUE'				=> 'Warteschlange',
	'GALLERY_MCP_QUEUE_DETAIL'		=> 'Details des Bildes',
	'GALLERY_MCP_REPORTED'			=> 'Gemeldete Bilder',
	'GALLERY_MCP_REPO_DONE'			=> 'Geschlossene Meldungen',
	'GALLERY_MCP_REPO_OPEN'			=> 'Offene Meldungen',
	'GALLERY_MCP_REPO_DETAIL'		=> 'Details der Meldung',
	'GALLERY_MCP_UNAPPROVED'		=> 'Auf Freigabe wartende Bilder',
	'GALLERY_MCP_APPROVED'			=> 'freigegebene Bilder',
	'GALLERY_MCP_LOCKED'			=> 'gesperrte Bilder',
	'GALLERY_MCP_VIEWALBUM'			=> 'Album anzeigen',

	'IMAGE_REPORTED'				=> 'Das Bild wurde gemeldet.',
	'IMAGE_UNAPPROVED'				=> 'Das Bild wartet auf freigabe.',

	'LAST_QUEUE_IMAGES'				=> 'Die letzten 5 Bilder, die auf Freigabe warten',
	'LAST_REPORTED_IMAGES'			=> 'Die letzten 5 gemeldeten Bilder',

	'MODERATE_ALBUM'				=> 'Album moderieren',

	'QUEUE_A_APPROVE'				=> 'Bild freischalten',
	'QUEUE_A_APPROVE2'				=> 'Bild freischalten?',
	'QUEUE_A_APPROVE2_CONFIRM'		=> 'Bist du dir sicher, dass du das Bild freischalten möchtest?',
	'QUEUE_A_DELETE'				=> 'Bild löschen',
	'QUEUE_A_DELETE2'				=> 'Bild löschen?',
	'QUEUE_A_DELETE2_CONFIRM'		=> 'Bist du dir sicher, dass du das Bild löschen möchtest?',
	'QUEUE_A_MOVE'					=> 'Bild verschieben',
	'QUEUE_A_UNAPPROVE'				=> 'erneute Freischaltung erzwingen',
	'QUEUE_A_UNAPPROVE2'			=> 'erneute Freischaltung erzwingen?',
	'QUEUE_A_UNAPPROVE2_CONFIRM'	=> 'Bist du dir sicher, dass eine erneute Freischaltung erzwungen werden soll?',
	'QUEUE_A_LOCK'					=> 'Bild sperren',
	'QUEUE_A_LOCK2'					=> 'Bild sperren?',
	'QUEUE_A_LOCK2_CONFIRM'			=> 'Bist du dir sicher, dass du das Bild sperren möchtest?',

	'QUEUE_STATUS_0'				=> 'Das Bild wartet auf Freigabe.',
	'QUEUE_STATUS_1'				=> 'Das Bild ist freigeschalten.',
	'QUEUE_STATUS_2'				=> 'Das Bild ist gesperrt.',

	'QUEUES_A_APPROVE'				=> 'Bilder freischalten',
	'QUEUES_A_APPROVE2'				=> 'Bilder freischalten?',
	'QUEUES_A_APPROVE2_CONFIRM'		=> 'Bist du dir sicher, dass du die Bilder freischalten möchtest?',
	'QUEUES_A_DELETE'				=> 'Bilder löschen',
	'QUEUES_A_DELETE2'				=> 'Bilder löschen?',
	'QUEUES_A_DELETE2_CONFIRM'		=> 'Bist du dir sicher, dass du die Bilder löschen möchtest?',
	'QUEUES_A_MOVE'					=> 'Bilder verschieben',
	'QUEUES_A_UNAPPROVE'			=> 'erneute Freischaltung erzwingen',
	'QUEUES_A_UNAPPROVE2'			=> 'erneute Freischaltung erzwingen?',
	'QUEUES_A_UNAPPROVE2_CONFIRM'	=> 'Bist du dir sicher, dass eine erneute Freischaltung erzwungen werden soll?',
	'QUEUES_A_LOCK'					=> 'Bilder sperren',
	'QUEUES_A_LOCK2'				=> 'Bilder sperren?',
	'QUEUES_A_LOCK2_CONFIRM'		=> 'Bist du dir sicher, dass du die Bilder sperren möchtest?',

	'REPORT_A_CLOSE'				=> 'Meldung schliessen',
	'REPORT_A_CLOSE2'				=> 'Meldung schliessen?',
	'REPORT_A_CLOSE2_CONFIRM'		=> 'Bist du dir sicher, dass du die Meldung schliessen möchtest?',
	'REPORT_A_DELETE'				=> 'Meldung löschen',
	'REPORT_A_DELETE2'				=> 'Meldung löschen?',
	'REPORT_A_DELETE2_CONFIRM'		=> 'Bist du dir sicher, dass du die Meldung löschen möchtest?',
	'REPORT_A_OPEN'					=> 'Meldung öffnen',
	'REPORT_A_OPEN2'				=> 'Meldung öffnen?',
	'REPORT_A_OPEN2_CONFIRM'		=> 'Bist du dir sicher, dass du die Meldung öffnen möchtest?',

	'REPORT_STATUS_1'				=> 'Die Meldung wartet auf Überprüfung.',
	'REPORT_STATUS_2'				=> 'Die Meldung ist geschlossen.',

	'REPORTS_A_CLOSE'				=> 'Meldungen schliessen',
	'REPORTS_A_CLOSE2'				=> 'Meldungen schliessen?',
	'REPORTS_A_CLOSE2_CONFIRM'		=> 'Bist du dir sicher, dass du die Meldungen schliessen möchtest?',
	'REPORTS_A_DELETE'				=> 'Meldungen löschen',
	'REPORTS_A_DELETE2'				=> 'Meldungen löschen?',
	'REPORTS_A_DELETE2_CONFIRM'		=> 'Bist du dir sicher, dass du die Meldungen löschen möchtest?',
	'REPORTS_A_OPEN'				=> 'Meldungen öffnen',
	'REPORTS_A_OPEN2'				=> 'Meldungen öffnen?',
	'REPORTS_A_OPEN2_CONFIRM'		=> 'Bist du dir sicher, dass du die Meldungen öffnen möchtest?',

	'REPORTER'						=> 'Meldender Benutzer',
	'REPORT_MOD'					=> 'Bearbeitet von',
	'REPORTER_AND_ALBUM'			=> 'Meldender Benutzer & Album',
	'REPORTED_IMAGES'				=> 'Gemeldete Bilder',

	'UPLOADED_BY'					=> 'Hochgeladen von',

	'VIEW_ALBUM_IMAGES'				=> '%s Bilder',
	'VIEW_ALBUM_IMAGE'				=> '1 Bild',

	'WAITING_APPROVED_IMAGE'		=> 'Insgesamt ist <span style="font-weight: bold;">%s</span> Bild freigeschalten.',
	'WAITING_APPROVED_IMAGES'		=> 'Insgesamt sind <span style="font-weight: bold;">%s</span> Bilder freigeschalten.',
	'WAITING_APPROVED_NONE'			=> 'Es sind keine Bilder freigeschalten.',
	'WAITING_APPROVED_IMAGE'		=> 'Insgesamt ist <span style="font-weight: bold;">%s</span> Bild freigeschalten.',
	'WAITING_APPROVED_IMAGES'		=> 'Insgesamt sind <span style="font-weight: bold;">%s</span> Bilder freigeschalten.',
	'WAITING_APPROVED_NONE'			=> 'Es sind keine Bilder freigeschalten.',
	'WAITING_LOCKED_IMAGE'			=> 'Insgesamt ist <span style="font-weight: bold;">%s</span> Bild gesperrt.',
	'WAITING_LOCKED_IMAGES'			=> 'Insgesamt sind <span style="font-weight: bold;">%s</span> Bilder gesperrt.',
	'WAITING_LOCKED_NONE'			=> 'Es sind keine Bilder gesperrt.',
	'WAITING_REPORTED_DONE'			=> 'Es sind keine Meldungen erledigt.',
	'WAITING_REPORTED_IMAGE'		=> 'Insgesamt wartet <span style="font-weight: bold;">%s</span> Meldung auf Überprüfung.',
	'WAITING_REPORTED_IMAGES'		=> 'Insgesamt warten <span style="font-weight: bold;">%s</span> Meldungen auf Überprüfung.',
	'WAITING_REPORTED_NONE'			=> 'Es wurden keine Bilder gemeldet.',
	'WAITING_UNAPPROVED_IMAGE'		=> 'Insgesamt wartet <span style="font-weight: bold;">%s</span> Bild auf Freischaltung.',
	'WAITING_UNAPPROVED_IMAGES'		=> 'Insgesamt warten <span style="font-weight: bold;">%s</span> Bilder auf Freischaltung.',
	'WAITING_UNAPPROVED_NONE'		=> 'Es sind keine Bilder in der Warteschlange.',
));

?>