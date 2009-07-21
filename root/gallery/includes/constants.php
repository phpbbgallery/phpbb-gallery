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

// Album/Image states
define('ALBUM_CAT', 0);
define('ALBUM_UPLOAD', 1);
define('ALBUM_CONTEST', 2);

define('IMAGE_UNAPPROVED', 0);
define('IMAGE_APPROVED', 1);
define('IMAGE_LOCKED', 2);

define('IMAGE_NO_CONTEST', 0);
define('IMAGE_CONTEST', 1);

define('REPORT_UNREPORT', 0);
define('REPORT_OPEN', 1);
define('REPORT_LOCKED', 2);

// GD library
define('GDLIB1', 1);
define('GDLIB2', 2);

// Exif-data
define('EXIF_UNAVAILABLE', 0);
define('EXIF_AVAILABLE', 1);
define('EXIF_UNKNOWN', 2);
define('EXIF_DBSAVED', 3);
define('EXIFTIME_OFFSET', 0); // Use this constant, to change the exif-timestamp. Offset in seconds

// Permissions
define('SETTING_PERMISSIONS', -39839);
define('NON_PERSONAL_PERMISSIONS', 0);
define('OWN_GALLERY_PERMISSIONS', -2);
define('PERSONAL_GALLERY_PERMISSIONS', -3);

// ACL - slightly different
define('GALLERY_ACL_NEVER', 2);
define('GALLERY_ACL_YES', 1);
define('GALLERY_ACL_NO', 0);

// Display-options for RRC-Feature
define('RRC_DISPLAY_NONE', 0);
define('RRC_DISPLAY_ALBUMNAME', 1);
define('RRC_DISPLAY_COMMENTS', 2);
define('RRC_DISPLAY_IMAGENAME', 4);
define('RRC_DISPLAY_IMAGETIME', 8);
define('RRC_DISPLAY_IMAGEVIEWS', 16);
define('RRC_DISPLAY_USERNAME', 32);
define('RRC_DISPLAY_RATINGS', 64);

// Mode-options for RRC-Feature
define('RRC_MODE_NONE', 0);
define('RRC_MODE_RECENT', 1);
define('RRC_MODE_RANDOM', 2);
define('RRC_MODE_COMMENT', 4);

// Watermark positions
define('WATERMARK_TOP', 1);
define('WATERMARK_MIDDLE', 2);
define('WATERMARK_BOTTOM', 4);
define('WATERMARK_LEFT', 8);
define('WATERMARK_CENTER', 16);
define('WATERMARK_RIGHT', 32);

// Additional constants
define('CONTEST_IMAGES', 3);
define('MODULE_DEFAULT_ACP', 31);
define('MODULE_DEFAULT_LOG', 25);
define('MODULE_DEFAULT_UCP', 0);
define('NON_PERSONAL_ALBUMS', 0);
define('SEARCH_PAGES_NUMBER', 10);
define('THUMBNAIL_INFO_HEIGHT', 16);

// Additional tables

// Image directories
define('GALLERY_IMAGE_PATH', GALLERY_ROOT_PATH . 'images/');
define('GALLERY_UPLOAD_PATH', GALLERY_IMAGE_PATH . 'upload/');
define('GALLERY_CACHE_PATH', GALLERY_IMAGE_PATH . 'cache/');
define('GALLERY_MEDIUM_PATH', GALLERY_IMAGE_PATH . 'medium/');
define('GALLERY_IMPORT_PATH', GALLERY_IMAGE_PATH . 'import/');

// Are they used?
define('G_ALBUM_CAT', 0);
define('G_ALBUM_UPLOAD', 1);

/**
* Constants, defined in other files:
*/

/**
* gallery/includes/hookup_gallery.php
define('GALLERY_ADD_CASH', 10);
define('GALLERY_DELETE_CASH', 10);
define('GALLERY_VIEW_CASH', 1);
*/

?>