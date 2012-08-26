<?php
/**
*
* @package Gallery - ACP CleanUp Extension [Deutsch]
* @copyright (c) 2012 nickvergessen - http://www.flying-bits.org/
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

$lang = array_merge($lang, array(
	'ACP_GALLERY_CLEANUP_EXPLAIN'	=> 'Hier kannst du Überreste aus der Galerie entfernen.',

	'CLEAN_AUTHORS_DONE'			=> 'Bilder ohne Autor gelöscht.',
	'CLEAN_CHANGED'					=> 'Autor in „Gast“ geändert.',
	'CLEAN_COMMENTS_DONE'			=> 'Kommentare ohne Autor gelöscht.',
	'CLEAN_ENTRIES_DONE'			=> 'Dateien ohne Datenbank-Einträge gelöscht.',
	'CLEAN_GALLERY'					=> 'Galerie reinigen',
	'CLEAN_GALLERY_ABORT'			=> 'Reinigung abgebrochen!',
	'CLEAN_NO_ACTION'				=> 'Keine Aktion ausgeführt. Irgendwas ist schief gelaufen!',
	'CLEAN_PERSONALS_DONE'			=> 'Persönliche Alben ohne Besitzer gelöscht.',
	'CLEAN_PERSONALS_BAD_DONE'		=> 'Persönliche Alben der gewählten Benutzer gelöscht.',
	'CLEAN_PRUNE_DONE'				=> 'Bilder erfolgreich automatisch gelöscht.',
	'CLEAN_PRUNE_NO_PATTERN'		=> 'Kein Suchmuster angegeben.',
	'CLEAN_SOURCES_DONE'			=> 'Datenbank-Einträge ohne Dateien gelöscht.',

	'CONFIRM_CLEAN'					=> 'Dieser Vorgang kann nicht rückgängig gemacht werden!',
	'CONFIRM_CLEAN_AUTHORS'			=> 'Bilder ohne Autor löschen?',
	'CONFIRM_CLEAN_COMMENTS'		=> 'Kommentare ohne Autor löschen?',
	'CONFIRM_CLEAN_ENTRIES'			=> 'Dateien ohne Datenbank-Einträge löschen?',
	'CONFIRM_CLEAN_PERSONALS'		=> 'Persönliche Alben ohne Besitzer löschen?<br /><strong>» %s</strong>',
	'CONFIRM_CLEAN_PERSONALS_BAD'	=> 'Persönliche Alben der gewählten Benutzer löschen?<br /><strong>» %s</strong>',
	'CONFIRM_CLEAN_SOURCES'			=> 'Datenbank-Einträge ohne Dateien löschen?',
	'CONFIRM_PRUNE'					=> 'Alle Bilder löschen, die folgende Bedingungen erfüllen:<br /><br />%s<br />',

	'PRUNE'							=> 'Bilder löschen',
	'PRUNE_ALBUMS'					=> 'Bilder aus Alben löschen',
	'PRUNE_CHECK_OPTION'			=> 'Diese Option beim Löschen berücksichtigen.',
	'PRUNE_COMMENTS'				=> 'Weniger als x Kommentare',
	'PRUNE_PATTERN_ALBUM_ID'		=> 'Das Bild ist in einem der folgenden Alben:<br />&raquo; <strong>%s</strong>',
	'PRUNE_PATTERN_COMMENTS'		=> 'Das Bild hat weniger als <strong>%d</strong> Kommentare.',
	'PRUNE_PATTERN_RATES'			=> 'Das Bild hat weniger als <strong>%d</strong> Bewertungen.',
	'PRUNE_PATTERN_RATE_AVG'		=> 'Das Bild hat eine schlechtere Durchschnitts-Bewertung als <strong>%s</strong>.',
	'PRUNE_PATTERN_TIME'			=> 'Das Bild wurde vor „<strong>%s</strong>“ hochgeladen.',
	'PRUNE_PATTERN_USER_ID'			=> 'Das Bild wurde von einem der folgenden Benutzer hochgeladen:<br />&raquo; <strong>%s</strong>',
	'PRUNE_RATINGS'					=> 'Weniger als x Bewertungen',
	'PRUNE_RATING_AVG'				=> 'Durchschnitts-Bewertung schlechter als',
	'PRUNE_RATING_AVG_EXP'			=> 'Lösche nur Bilder, deren Durchschnitts-Bewertung schlechter als „<samp>x.yz</samp>“ ist.',
	'PRUNE_TIME'					=> 'Hochgeladen vor',
	'PRUNE_TIME_EXP'				=> 'Lösche nur Bilder, die vor „<samp>YYYY-MM-DD</samp>“ hochgeladen wurden.',
	'PRUNE_USERNAME'				=> 'Hochgeladen von',
	'PRUNE_USERNAME_EXP'			=> 'Lösche nur Bilder von bestimmten Benutzern. Um Bilder von „Gästen“ zu löschen, wähle bitte die Check-Box unter der Namens-box aus.',
));
