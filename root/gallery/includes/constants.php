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


// Table names
define('GALLERY_ALBUMS_TABLE', $table_prefix . 'gallery_albums');
define('GALLERY_COMMENTS_TABLE', $table_prefix . 'gallery_comments');
define('GALLERY_CONFIG_TABLE', $table_prefix . 'gallery_config');
define('GALLERY_IMAGES_TABLE', $table_prefix . 'gallery_images');
define('GALLERY_RATES_TABLE', $table_prefix . 'gallery_rates');

//we still need them for the updater
define('ALBUM_TABLE', $table_prefix.'album');
define('ALBUM_CAT_TABLE', $table_prefix.'album_cat');
define('ALBUM_CONFIG_TABLE', $table_prefix.'album_config');
define('ALBUM_COMMENT_TABLE', $table_prefix.'album_comment');
define('ALBUM_RATE_TABLE', $table_prefix.'album_rate');

?>