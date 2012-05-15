<?php
/**
*
* @package Gallery - Feed Extension [English]
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
	'ALBUM_FEED'					=> 'Display images of this album in feeds',

	'FEED_ENABLED'					=> 'Enable album feeds',
	'FEED_ENABLED_PEGAS'			=> 'Enable feeds for personal galleries',
	'FEED_LIMIT'					=> 'Number of elements displayed',
	'FEED_SETTINGS'					=> 'Feed settings',
));
