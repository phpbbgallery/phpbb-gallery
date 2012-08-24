<?php
/**
*
* @package Gallery - Favorite Extension [English]
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
	'FAVORITE_IMAGE'				=> 'Add to favorites',
	'FAVORITED_IMAGE'				=> 'The image was added to your favorites.',

	'NO_FAVORITES'					=> 'You don’t have any favorites.',

	'REMOVE_FROM_FAVORITES'			=> 'Remove from favorites',

	'UNFAVORITE_IMAGE'				=> 'Remove from favorites',
	'UNFAVORITED_IMAGE'				=> 'The image was removed from your favorites.',
	'UNFAVORITED_IMAGES'			=> 'The images were removed from your favorites.',

	'YOUR_FAVORITE_IMAGES'			=> 'Here you can see your favorite-images. You may remove them, if you don’t like them anymore.',

	'WATCH_FAVO'					=> 'Subscribe favorite images by default',
));
