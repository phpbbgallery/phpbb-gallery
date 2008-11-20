<?php

/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
}

// only need for converter
define('ALBUM_UPLOAD_PATH', 'upload/');
define('ALBUM_CACHE_PATH', 'upload/cache/');
define('ALBUM_DIR_NAME', 'gallery/');

define('G_ALBUM_CAT', 0);
define('G_ALBUM_UPLOAD', 1);

define('SETTING_PERMISSIONS', -39839);
define('OWN_GALLERY_PERMISSIONS', -2);
define('PERSONAL_GALLERY_PERMISSIONS', -3);

define('GALLERY_IMAGE_PATH', GALLERY_ROOT_PATH . 'images/');
define('GALLERY_UPLOAD_PATH', GALLERY_ROOT_PATH . 'upload/');
define('GALLERY_CACHE_PATH', GALLERY_UPLOAD_PATH . 'cache/');
define('GALLERY_MEDIUM_PATH', GALLERY_IMAGE_PATH . 'medium/');

?>