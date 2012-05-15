<?php
/**
*
* @package Gallery - Feed Extension [Deutsch]
* @copyright (c) 2012 nickvergessen - http://www.flying-bits.org/
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

/**
* Language for Exif data
*/
$lang = array_merge($lang, array(
	'ALBUM_FEED'					=> 'Bilder aus diesem Album im Feed anzeigen',

	'FEED_ENABLED'					=> 'Feeds für Alben einschalten',
	'FEED_ENABLED_PEGAS'			=> 'Feeds für persönliche Alben einschalten',
	'FEED_LIMIT'					=> 'Anzahl der angezeigten Elemente',
	'FEED_SETTINGS'					=> 'Feed-Einstellungen',
));
