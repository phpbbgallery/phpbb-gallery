<?php
/**
*
* @package Gallery - Favorite Extension [Deutsch]
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
* Language for Favorites
*/
$lang = array_merge($lang, array(
	'FAVORITE_IMAGE'				=> 'zu Lieblingsbildern hinzufügen',
	'FAVORITED_IMAGE'				=> 'Das Bild wurde zu deinen Lieblingsbildern hinzugefügt.',

	'NO_FAVORITES'					=> 'Du hast keine Lieblingsbilder.',

	'REMOVE_FROM_FAVORITES'			=> 'Aus den Lieblingsbildern entfernen',

	'UNFAVORITE_IMAGE'				=> 'aus Lieblingsbildern entfernen',
	'UNFAVORITED_IMAGE'				=> 'Das Bild wurde aus deinen Lieblingsbildern entfernt.',
	'UNFAVORITED_IMAGES'			=> 'Die Bilder wurde aus deinen Lieblingsbildern entfernt.',

	'YOUR_FAVORITE_IMAGES'			=> 'Hier siehst du deine Lieblingsbilder. Du kannst sie auch wieder entfernen, wenn sie dir nicht gefallen.',

	'WATCH_FAVO'					=> 'Lieblingsbilder standardmässig beobachten',
));
