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
	'PAGE_TITLE'				=> 'phpBB Gallery v%s',
	'SPECIAL_ROOT_PATH'			=> 'phpBB Gallery',//Description next to the Forum-Index link
));

$lang = array_merge($lang, array(
	'INTRO_WELCOME_NOTE'		=> 'Willkommen bei phpBB Gallery!<br /><br />Bitte wähle aus, was du tun möchtest.',
));

$lang = array_merge($lang, array(
	'INSTALL_WELCOME_NOTE'		=> 'Wenn du den MOD installierst, werden möglicherweise vorhandene Datenbanktabellen mit gleichem Namen gelöscht.',
));

$lang = array_merge($lang, array(
	'CONVERT_SMARTOR'			=> 'Konvertiere von Smartor´s Album MOD (auch mit Full Album Pack)',
	'CONVERT_SUCCESSFUL_ADD'	=> 'Kopiere nun die Bilder aus den Verzeichnissen album/upload und album/upload/cache aus der phpBB2-Installation in die der phpBB3-Installation.',
));
/*
* End of Force!
* The rest is gallery specific, but an Example for the STEP_LOG-Message!
*/

$lang = array_merge($lang, array(
	'STEPS_ADD_BBCODE'			=> 'BBCode hinzufügen',
	'STEPS_ADD_CONFIGS'			=> 'Konfigurationswerte hinzufügen',
	'STEPS_ADD_PERSONALS'		=> 'Persönliche Alben hinzufügen',
	'STEPS_COPY_ALBUMS'			=> 'Alben kopieren',
	'STEPS_COPY_COMMENTS'		=> 'Kommentare kopieren',
	'STEPS_COPY_IMAGES'			=> 'Bilder kopieren',
	'STEPS_COPY_RATES'			=> 'Bewertungen kopieren',
	'STEPS_CREATE_EXAMPLES'		=> 'Beispielalbum und -bild hinzufügen',
	'STEPS_DBSCHEMA'			=> 'Datenbank-Tabellen erstellen und Datenbank-Felder hinzufügen',
	'STEPS_IMPORT_ALBUMS'		=> 'Alben importieren',
	'STEPS_IMPORT_COMMENTS'		=> 'Kommentare importieren',
	'STEPS_IMPORT_IMAGES'		=> 'Bilder importieren',
	'STEPS_IMPORT_RATES'		=> 'Bewertungen importieren',
	'STEPS_UPDATE_IMAGES'		=> 'Bilderdaten aktualisieren',
	'STEPS_UPDATE_COMMENTS'		=> 'Kommentardaten aktualisieren',
	'STEPS_MODULES'				=> 'Module erstellen',
	'STEPS_REMOVE_COLUMNS'		=> 'Datenbank-Felder löschen',
	'STEPS_REMOVE_CONFIGS'		=> 'Konfigurationswerte löschen',
	'STEPS_RESYN_ALBUMS'		=> 'Alben-Statistik resyncronisieren',
	'STEPS_RESYN_COUNTERS'		=> 'Zähler-Statistik resyncronisieren',
	'STEPS_RESYN_MODULES'		=> 'Module resyncronisieren',
));

$lang = array_merge($lang, array(
	'EXAMPLE_ALBUM1'					=> 'Deine erste Kategorie',
	'EXAMPLE_ALBUM2'					=> 'Dein erstes Album',
	'EXAMPLE_ALBUM2_DESC'				=> 'Beschreibung deines ersten Albums.',
	'EXAMPLE_DESC'						=> 'Danke dass du phpBB Gallery v%s aka. &quot;DB-Bird&quot; installiert hast.<br />'
											. 'Das ist nur ein Beispiel-Bild. Du kannst es löschen, wenn du möchtest.',
	'EXAMPLE_DESC_UID'					=> '3ogfgm0h',
));

?>