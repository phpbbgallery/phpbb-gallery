<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

define('G_ALBUM_CAT', 0);
define('G_ALBUM_UPLOAD', 1);

define('SETTING_PERMISSIONS', -39839);
define('OWN_GALLERY_PERMISSIONS', -2);
define('PERSONAL_GALLERY_PERMISSIONS', -3);

define('GALLERY_IMAGE_PATH', GALLERY_ROOT_PATH . 'images/');
define('GALLERY_UPLOAD_PATH', GALLERY_IMAGE_PATH . 'upload/');
define('GALLERY_CACHE_PATH', GALLERY_IMAGE_PATH . 'cache/');
define('GALLERY_MEDIUM_PATH', GALLERY_IMAGE_PATH . 'medium/');
define('GALLERY_IMPORT_PATH', GALLERY_IMAGE_PATH . 'import/');

?>