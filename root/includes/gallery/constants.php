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

class phpbb_gallery_constants
{
	const REPORT_UNREPORT = 0;
	const REPORT_OPEN = 1;
	const REPORT_LOCKED = 2;

	// GD library
	const GDLIB1 = 1;
	const GDLIB2 = 2;

	// Exif-data
	const EXIF_UNAVAILABLE = 0;
	const EXIF_AVAILABLE = 1;
	const EXIF_UNKNOWN = 2;
	const EXIF_DBSAVED = 3;
	const EXIFTIME_OFFSET = 0; // Use this constant, to change the exif-timestamp. Offset in seconds

	// Watermark positions
	const WATERMARK_TOP = 1;
	const WATERMARK_MIDDLE = 2;
	const WATERMARK_BOTTOM = 4;
	const WATERMARK_LEFT = 8;
	const WATERMARK_CENTER = 16;
	const WATERMARK_RIGHT = 32;

	// Additional constants
	const CONTEST_IMAGES = 3;
	const MODULE_DEFAULT_ACP = 31;
	const MODULE_DEFAULT_LOG = 25;
	const MODULE_DEFAULT_UCP = 0;
	const SEARCH_PAGES_NUMBER = 10;
	const THUMBNAIL_INFO_HEIGHT = 16;
}
