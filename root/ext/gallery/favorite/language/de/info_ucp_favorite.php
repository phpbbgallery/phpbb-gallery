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
	'UCP_GALLERY_FAVORITES'				=> 'Lieblingsbilder verwalten',
));
