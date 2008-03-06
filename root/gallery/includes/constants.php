<?php

/**
*
* @package phpBB3
* @version $Id: constants.php 288 2008-02-14 16:29:33Z nickvergessen $
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
}

define('PAGE_ALBUM', -19);// for Session Handling

define('PERSONAL_GALLERY', 0);// image_album_id <- do NOT change this value


// User Levels for Album system <- do NOT change these values
define('ALBUM_ANONYMOUS', 1);
define('ALBUM_GUEST', 1);

define('ALBUM_USER', 0);
define('ALBUM_ADMIN', 4);
define('ALBUM_MOD', 2);
define('ALBUM_PRIVATE', 3);

define('ADMIN', 1);
define('MOD', 2);


// Path (trailing slash required)
define('ALBUM_UPLOAD_PATH', 'upload/');
define('ALBUM_CACHE_PATH', 'upload/cache/');
define('ALBUM_DIR_NAME', 'gallery/');

define('GALLERY_UPLOAD_PATH', GALLERY_ROOT_PATH . 'upload/');
define('GALLERY_CACHE_PATH', GALLERY_UPLOAD_PATH . 'cache/');

?>