<?php
/**
*
* gallery_acp [English]
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_CREATE_ALBUM_EXPLAIN'		=> 'Create and configure a new album.',
	'ACP_EDIT_ALBUM_TITLE'			=> 'Edit album',
	'ACP_EDIT_ALBUM_EXPLAIN'		=> 'Edit an existing album.',
	'ACP_GALLERY_CLEANUP_EXPLAIN'	=> 'Here you can delete some remains.',
	'ACP_GALLERY_OVERVIEW'			=> 'phpBB Gallery',
	'ACP_GALLERY_OVERVIEW_EXPLAIN'	=> 'Here are some statistics about your gallery.',
	'ACP_IMPORT_ALBUMS'				=> 'Import Images',
	'ACP_IMPORT_ALBUMS_EXPLAIN'		=> 'Here you can bulk import images from the file system. Before importing images, please be sure to resize them by hand.',
	'ACP_MANAGE_ALBUMS'				=> 'phpBB Gallery Album administration',
	'ACP_MANAGE_ALBUMS_EXPLAIN'		=> 'Here you can manage your Albums (former categories).',

	'ADD_PERMISSIONS'				=> 'Add Permissions',
	'ALBUM_AUTH_TITLE'				=> 'Album Permissions',
	'ALBUM_CATEGORY'				=> 'Category',
	'ALBUM_DELETE'					=> 'Delete %s',
	'ALBUM_DELETED'					=> 'This album has been deleted successfully.',
	'ALBUM_DESC'					=> 'Album Description',
	'ALBUM_ID'						=> 'Album-ID',
	'ALBUM_IMAGE'					=> 'Album image',
	'ALBUM_NAME'					=> 'Album Name',
	'ALBUM_NO_CATEGORY'				=> 'Album',
	'ALBUM_PARENT'					=> 'Parent Album',
	'ALBUM_SETTINGS'				=> 'Album Settings',
	'ALBUM_TYPE'					=> 'Album Type',
	'ALBUM_UPDATED'					=> 'Album has been updated successfully.',

	'CACHE_DIR_SIZE'				=> 'cache/-directory size',
	'CHANGE_AUTHOR'					=> 'Change author to guest',
	'CHECK'							=> 'Check',
	'CHECK_AUTHOR_EXPLAIN'			=> 'No images without valid author found.',
	'CHECK_COMMENT_EXPLAIN'			=> 'No comments without valid author found.',
	'CHECK_ENTRY_EXPLAIN'			=> 'You have to run the check, to search for files without database-entry.',
	'CHECK_PERSONALS_EXPLAIN'		=> 'No personal albums without valid owner found.',
	'CHECK_SOURCE_EXPLAIN'			=> 'No entry found. You should run the check, to be sure.',
	'CLEAN_AUTHORS_DONE'			=> 'Images without valid author deleted.',
	'CLEAN_CHANGED'					=> 'Author changed to "Guest".',
	'CLEAN_COMMENTS_DONE'			=> 'Comments without valid author deleted.',
	'CLEAN_ENTRIES_DONE'			=> 'Files without database-entry deleted.',
	'CLEAN_GALLERY'					=> 'Clean gallery',
	'CLEAN_GALLERY_ABORT'			=> 'Cleanup abort!',
	'CLEAN_PERSONALS_DONE'			=> 'Personal albums without valid owner deleted.',
	'CLEAN_SOURCES_DONE'			=> 'Images without file deleted.',
	'COLS_PER_PAGE'					=> 'Number of columns on thumbnail page',
	'COMMENT'						=> 'Comment',
	'COMMENT_ID'					=> 'Comment-ID',
	'COMMENT_SYSTEM'				=> 'Enable comment system',
	'CONFIRM_CLEAN'					=> 'This step can not be undone!',
	'CONFIRM_CLEAN_AUTHORS'			=> 'Delete images without valid author?',
	'CONFIRM_CLEAN_COMMENTS'		=> 'Delete comments without valid author?',
	'CONFIRM_CLEAN_ENTRIES'			=> 'Delete files without database-entry?',
	'CONFIRM_CLEAN_PERSONALS'		=> 'Delete personal albums without valid owner?',
	'CONFIRM_CLEAN_SOURCES'			=> 'Delete images without file?',
	'COPY_PERMISSIONS'				=> 'Copy Permissions from',
	'CREATE_ALBUM'					=> 'Create new album',

	'DEFAULT_SORT_METHOD'			=> 'Default Sort Method',
	'DEFAULT_SORT_ORDER'			=> 'Default Sort Order',
	'DELETE_ALBUM'					=> 'Delete Album',
	'DELETE_ALBUM_EXPLAIN'			=> 'The form below will allow you to delete a album and decide where you want to put the images it contained',
	'DELETE_ALBUM_SUBS'				=> 'Please remove the subalbums first',//@todo
	'DELETE_IMAGES'					=> 'Delete Images',
	'DELETE_PERMISSIONS'			=> 'Delete permissions',
	'DELETE_SUBS'					=> 'Delete subalbums',
	'DISP_EXIF_DATA'				=> 'Display Exif-data',
	'DISP_FAKE_THUMB'				=> 'View thumbnail in albumlist',
	'DISP_PERSONAL_ALBUM_PROFIL'	=> 'Show link to personal album in user-profile',
	'DISP_TOTAL_IMAGES'				=> 'Show "Total images" on index.' . $phpEx,
	'DISP_USER_IMAGES_PROFIL'		=> 'Show statistic with uploaded images in user-profile',
	'DONT_COPY_PERMISSIONS'			=> 'don\'t copy permissions',

	'EDIT_ALBUM'					=> 'Edit Album',

	'FAKE_THUMB_SIZE'				=> 'Thumbnail-size',
	'FAKE_THUMB_SIZE_EXP'			=> 'If you want to resize them to the full size, remember 16 pixels for the black-info-line',

	'GALLERY_ALBUMS_TITLE'			=> 'Gallery Albums Control',
	'GALLERY_CLEAR_CACHE_CONFIRM'	=> 'If you use the Thumbnail Cache feature you must clear your thumbnail cache after changing your thumbnail settings in "Gallery configuration" to make them regenerated.',//@todo
	'GALLERY_CONFIG'				=> 'Gallery Configuration',
	'GALLERY_CONFIG_EXPLAIN'		=> 'You can change the general settings of phpBB Gallery here.',
	'GALLERY_CONFIG_UPDATED'		=> 'Gallery Configuration has been updated successfully.',
	'GALLERY_INDEX'					=> 'Gallery-Index',
	'GALLERY_STATS'					=> 'Gallery statistics',
	'GALLERY_VERSION'				=> 'Gallery version',
	'GD_VERSION'					=> 'Optimize for GD version',
	'GUPLOAD_DIR_SIZE'				=> 'upload/-directory size',

	'HANDLE_IMAGES'					=> 'What to do with the images',
	'HANDLE_SUBS'					=> 'What to do with the subalbums',
	'HOTLINK_ALLOWED'				=> 'Allowed domains for hotlink (separated by a comma)',
	'HOTLINK_PREVENT'				=> 'Hotlink Prevention',

	'IMAGE_DESC_MAX_LENGTH'			=> 'Image Description/Comment Max Length (bytes)',
	'IMAGE_ID'						=> 'Image-ID',
	'IMAGES_PER_DAY'				=> 'Images per day',
	'IMPORT_ALBUM'					=> 'Album to import images to:',
	'IMPORT_DEBUG_MES'				=> '%1$s images imported. There are still %2$s images remaining.',
	'IMPORT_DIR_EMPTY'				=> 'The folder %s is empty. You need to upload the images, before you can import them.',
	'IMPORT_FINISHED'				=> 'All %1$s images successful imported.',
	'IMPORT_MISSING_ALBUM'			=> 'Please select an album to import the images into.',
	'IMPORT_SELECT'					=> 'Choose the images which you want to import. Successful uploaded images are deleted. All other images are still available.',
	'IMPORT_USER'					=> 'Uploaded by',
	'IMPORT_USER_EXP'				=> 'You can add the images to another user here.',
	'INFO_LINE'						=> 'Display file-size on thumbnail',

	'MANAGE_CRASHED_ENTRIES'		=> 'Manage crashed entries',
	'MANAGE_CRASHED_IMAGES'			=> 'Manage crashed images',
	'MANAGE_PERSONALS'				=> 'Manage personal albums',
	'MAX_IMAGES_PER_ALBUM'			=> 'Maximum number of images for each Album (-1 = unlimited)',
	'MEDIUM_CACHE'					=> 'Cache resized images for image-page',
	'MEDIUM_DIR_SIZE'				=> 'medium/-directory size',
	'MISSING_ALBUM_NAME'			=> 'You need to enter a name for the album.',
	'MISSING_AUTHOR'				=> 'Images without valid author',
	'MISSING_AUTHOR_C'				=> 'Comments without valid author',
	'MISSING_ENTRY'					=> 'Files without database-entry',
	'MISSING_OWNER'					=> 'Personal albums without valid owner',
	'MISSING_OWNER_EXP'				=> 'Subalbums, images and comments get deleted aswell.',
	'MISSING_SOURCE'				=> 'Images without files',

	'NEW_ALBUM_CREATED'				=> 'New album has been created successfully',
	'NO_PARENT_ALBUM'				=> '&raquo; No Parent Album',
	'NO_SUBALBUMS'					=> 'No Albums attached',
	'NUMBER_ALBUMS'					=> 'Number of albums',
	'NUMBER_IMAGES'					=> 'Number of images',
	'NUMBER_PERSONALS'				=> 'Number of personal albums',

	'OWN_PERSONAL_ALBUMS'			=> 'Own personal albums',

	'PERMISSION'					=> 'Permission',
	'PERMISSION_NEVER'				=> 'Never',
	'PERMISSION_NO'					=> 'No',
	'PERMISSION_YES'				=> 'Yes',

	'PERMISSION_A_LIST'				=> 'Can see album',
	'PERMISSION_ALBUM_COUNT'		=> 'Number of possible personal subalbums',
	'PERMISSION_C'					=> 'Comments',
	'PERMISSION_C_DELETE'			=> 'Can delete comments',
	'PERMISSION_C_EDIT'				=> 'Can edit comments',
	'PERMISSION_C_POST'				=> 'Can comment on image',
	'PERMISSION_C_READ'				=> 'Can read comments',
	'PERMISSION_I'					=> 'Images',
	'PERMISSION_I_APPROVE'			=> 'Can upload without approval',
	'PERMISSION_I_COUNT'			=> 'Number of uploadable images',
	'PERMISSION_I_DELETE'			=> 'Can delete images',
	'PERMISSION_I_EDIT'				=> 'Can edit images',
	'PERMISSION_I_LOCK'				=> 'Can lock images',
	'PERMISSION_I_RATE'				=> 'Can rate images',
	'PERMISSION_I_REPORT'			=> 'Can report images',
	'PERMISSION_I_UPLOAD'			=> 'Can upload images',
	'PERMISSION_I_VIEW'				=> 'Can view images',
	'PERMISSION_I_WATERMARK'		=> 'Can view images without watermark',
	'PERMISSION_M'					=> 'Moderation',
	'PERMISSION_MISC'				=> 'Misc', //Miscellaneous
	'PERMISSION_M_COMMENTS'			=> 'Can moderate comments',
	'PERMISSION_M_DELETE'			=> 'Can delete images',
	'PERMISSION_M_EDIT'				=> 'Can edit images',
	'PERMISSION_M_MOVE'				=> 'Can move images',
	'PERMISSION_M_REPORT'			=> 'Can manage reports',
	'PERMISSION_M_STATUS'			=> 'Can approve and lock images',

	'PERMISSION_EMPTY'				=> 'You didn\'t set all permissions.',
	'PERMISSION_NO_GROUP'			=> 'You didn\'t select a group to set the permissions.',
	'PERMISSIONS_STORED'			=> 'Permissions were stored successful.',
	'PERSONAL_ALBUM_INDEX'			=> 'View personal albums as album on the index',
	'PERSONAL_ALBUM_INDEX_EXP'		=> 'If choosen "No", there will be the link, right beneath.',
	'PURGED_CACHE'					=> 'Purged the cache',

	'RATE_SCALE'					=> 'Rating Scale',
	'RATE_SYSTEM'					=> 'Enable rating system',
	'REMOVE_IMAGES_FOR_CAT'			=> 'You need to remove the images of the album, before you can switch the album-type to category.',
	'RESIZE_IMAGES'					=> 'Resize bigger images',
	'RESYNC_IMAGECOUNTS'			=> 'Resynchronise image counts',
	'RESYNC_IMAGECOUNTS_CONFIRM'	=> 'Are you sure you wish to resynchronise image counts?',
	'RESYNC_IMAGECOUNTS_EXPLAIN'	=> 'Only existing images will be taken into consideration.',
	'RESYNC_LAST_IMAGES'			=> 'Refresh "Last image"',
	'RESYNC_PERSONALS'				=> 'Resynchronise personal album id\'s',
	'RESYNC_PERSONALS_CONFIRM'		=> 'Are you sure you wish to resynchronise image counts?',
	'RESYNCED_IMAGECOUNTS'			=> 'Resynchronised image counts',
	'RESYNCED_LAST_IMAGES'			=> 'Refreshed "Last image"',
	'RESYNCED_PERSONALS'			=> 'Resynchronised personal album id\'s',
	'ROWS_PER_PAGE'					=> 'Number of rows on thumbnail page',
	'RSZ_HEIGHT'					=> 'Maximum-height on viewing image',
	'RSZ_WIDTH'						=> 'Maximum-width on viewing image',

	'SELECT_ALBUMS'					=> 'Select albums',
	'SELECT_GROUPS'					=> 'Select groups',
	'SELECT_PERMISSIONS'			=> 'Select permissions',
	'SELECTED_ALBUMS'				=> 'Selected albums',
	'SELECTED_GROUPS'				=> 'Selected groups',
	'SET_PERMISSIONS'				=> '<br />Set <a href="%s">permissions</a> now.',
	'SHORTED_IMAGENAMES'			=> 'Shorten Imagenames',
	'SHORTED_IMAGENAMES_EXP'		=> 'If the name of an image is to long and doesn\'t include spaces, the layout maybe destroyed.',
	'SORRY_NO_STATISTIC'			=> 'Sorry, this statistic-value is not yet available.',

	'THIS_WILL_BE_REPORTED'			=> 'Known Bug, sorry guys!',
	'THUMBNAIL_CACHE'				=> 'Thumbnail cache',
	'THUMBNAIL_QUALITY'				=> 'Thumbnail quality (1-100)',
	'THUMBNAIL_SETTINGS'			=> 'Thumbnail Settings',

	'UC_IMAGE_NAME'					=> 'Imagename',
	'UC_IMAGE_ICON'					=> 'Lastimage icon',
	'UC_IMAGEPAGE'					=> 'Image on Image-page (with comments and rates)',
	'UC_LINK_CONFIG'				=> 'Link configuration',
	'UC_LINK_HIGHSLIDE'				=> 'Open Highslide-Feature',
	'UC_LINK_IMAGE'					=> 'Open Image',
	'UC_LINK_IMAGE_PAGE'			=> 'Open Image-page (with comments and rates)',
	'UC_LINK_LYTEBOX'				=> 'Open Lytebox-Feature',
	'UC_LINK_NONE'					=> 'No Link',
	'UC_THUMBNAIL'					=> 'Thumbnail',
	'UPLOAD_IMAGES'					=> 'Upload Multiple Images',

	'VIEW_IMAGE_URL'				=> 'View Image-URL on imagepage',

	'WATERMARK'						=> 'Watermark',
	'WATERMARK_EXP'					=> 'To avoid small images from being covered by the watermark, you may enter a minimum-width/height here.',
	'WATERMARK_HEIGHT'				=> 'Minimum-height for watermark',
	'WATERMARK_IMAGES'				=> 'Watermark images',
	'WATERMARK_OPTIONS'				=> 'Watermark options',
	'WATERMARK_SOURCE'		 		=> 'Watermark source file (relative to your phpbb root)',
	'WATERMARK_WIDTH'				=> 'Minimum-width for watermark',
));

?>