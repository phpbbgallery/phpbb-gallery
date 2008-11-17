<?php

/**
*
* @package phpBB3 - gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/
if (!defined('IN_PHPBB'))
{
	exit;
}
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACP_CREATE_ALBUM_TITLE'			=> 'Create new album',
	'ACP_CREATE_ALBUM_EXPLAIN'			=> 'Create and configure a new album.',
	'ACP_EDIT_ALBUM_TITLE'				=> 'Edit album',
	'ACP_EDIT_ALBUM_EXPLAIN'			=> 'Edit an existing album.',
	'ACP_GALLERY_OVERVIEW'				=> 'phpBB Gallery',
	'ACP_GALLERY_OVERVIEW_EXPLAIN'		=> 'Here are some statistics about your gallery.',
	'ACP_MANAGE_ALBUMS'				=> 'phpBB Gallery Album administration',
	'ACP_MANAGE_ALBUMS_EXPLAIN'			=> 'Here you can manage your Albums (former categories).',

	'ALBUM_AUTH_EXPLAIN'				=> 'Here you can choose which usergroup(s) can moderate albums or just have private access',
	'ALBUM_AUTH_SUCCESSFULLY'			=> 'Authorization Settings have been updated successfully',
	'ALBUM_AUTH_TITLE'				=> 'Album Permissions',
	'ALBUM_CHANGED_ORDER'				=> 'Album order has been changed successfully',
	'ALBUM_DELETED'					=> 'This album has been deleted successfully',
	'ALBUM_DESC'					=> 'Album Description',
	'ALBUM_PERMISSIONS'				=> 'Album Permissions',
	'ALBUM_PERSONAL_GALLERY_EXPLAIN'		=> 'On this page, you can choose which usergroups have right to create and view personal galleries. These settings only affect when you set "PRIVATE" for "Allowed to create personal gallery for users" or "Who can view personal galleries" in Album Configuration screen',
	'ALBUM_PERSONAL_GALLERY_TITLE'		=> 'Personal Gallery',
	'ALBUM_PERSONAL_SUCCESSFULLY'			=> 'The setting has been updated successfully',
	'ALBUM_TITLE'					=> 'Album Title',
	'ALBUM_UPDATED'					=> 'Album has been updated successfully',

	'CAN_COMMENT'					=> 'Can comment',
	'CAN_DELETE'					=> 'Can delete',
	'CAN_EDIT'						=> 'Can edit',
	'CAN_RATE'						=> 'Can rate',
	'CAN_UPLOAD'					=> 'Can upload',
	'CAN_VIEW'						=> 'Can view',
	'CAN_CREATE'					=> 'Can create',

	'CLEAR_CACHE'					=> 'Clear Cache',
	'CLICK_RETURN_ALBUM_AUTH'			=> 'Click %shere%s to return to the Album Permissions',
	'CLICK_RETURN_ALBUM_PERSONAL'			=> 'Click %shere%s to return to the Personal Gallery Settings',
	'CLICK_RETURN_GALLERY_ALBUM'			=> 'Click %shere%s to return to the Album Manager',
	'CLICK_RETURN_GALLERY_CONFIG'			=> 'Click %shere%s to return to the Gallery Configuration',
	'COLS_PER_PAGE'					=> 'Number of columns on thumbnail page',
	'COMMENT'						=> 'Comment',
	'COMMENT_LEVEL'					=> 'Comment Level',
	'COMMENT_SYSTEM'					=> 'Enable comment system',
	'CREATE_ALBUM'					=> 'Create new album',

	'DEFAULT_SORT_METHOD'				=> 'Default Sort Method',
	'DEFAULT_SORT_ORDER'				=> 'Default Sort Order',
	'DELETE_ALBUM'					=> 'Delete Album',
	'DELETE_ALBUM_EXPLAIN'				=> 'The form below will allow you to delete a album and decide where you want to put the images it contained',
	'DELETE_ALL_IMAGES'				=> 'Delete all images',
	'DELETE_LEVEL'					=> 'Delete Level',
	
	'EDIT_ALBUM'					=> 'Edit Album',
	'EDIT_LEVEL'					=> 'Edit Level',
	'EXTRA_SETTINGS'					=> 'Extra Settings',
	
	'FULL_IMAGE_POPUP'				=> 'View full image as a popup',
	
	'GALLERY_ALBUMS_TITLE'				=> 'Gallery Albums Control',
	'GALLERY_CATEGORIES_EXPLAIN'			=> 'On this screen you can manage your albums: create, alter, delete, sort, etc.',
	'GALLERY_CLEAR_CACHE_CONFIRM'			=> 'If you use the Thumbnail Cache feature you must clear your thumbnail cache after changing your thumbnail settings in "Gallery configuration" to make them regenerated.',
	'GALLERY_CONFIG'					=> 'Gallery Configuration',
	'GALLERY_CONFIG_EXPLAIN'			=> 'You can change the general settings of phpBB Gallery here',
	'GALLERY_CONFIG_UPDATED'			=> 'Gallery Configuration has been updated successfully',
	
	'GALLERY_ALL'					=> 'All',
	'GALLERY_REG'					=> 'Registered',
	'GALLERY_PRIVATE'					=> 'Private',
	'GALLERY_MOD'					=> 'Moderator',
	'GALLERY_ADMIN'					=> 'Administrator',
	
	'GD_VERSION'					=> 'Optimize for GD version',
	
	'HOTLINK_ALLOWED'					=> 'Allowed domains for hotlink (separated by a comma)',
	'HOTLINK_PREVENT'					=> 'Hotlink Prevention',
	
	'IMAGE_APPROVAL'					=> 'Image Approval',
	'IMAGE_SETTINGS'					=> 'Image Settings',
	'IMAGE_DESC_MAX_LENGTH'				=> 'Image Description/Comment Max Length (bytes)',
	'INFO_LINE'						=> 'Display file-size on thumbnail',
	'IS_MODERATOR'					=> 'Is Moderator',
	
	'LOOK_UP_ALBUM'					=> 'Look up Album',
	
	'MANUAL_THUMBNAIL'				=> 'Manual thumbnail',
	'MAX_IMAGES_PER_ALBUM'			=> 'Maximum number of images for each Album (-1 = unlimited)',
	'MODERATOR_IMAGES_LIMIT'			=> 'Images limit per Album for each moderator (-1 = unlimited)',
	'MOVE_CONTENTS'					=> 'Move all images',
	'MOVE_DELETE'					=> 'Move and delete',
	'MOVE_AND_DELETE'					=> 'Move and delete',
	
	'NEW_ALBUM_CREATED'				=> 'New album has been created successfully',
	
	'PERSONAL_GALLERIES'				=> 'Personal Galleries',
	'PERSONAL_GALLERY'				=> 'Allowed to create personal gallery for users',
	'PERSONAL_GALLERY_LIMIT'			=> 'Images limit for each personal gallery (-1 = unlimited)',
	'PERSONAL_GALLERY_VIEW'				=> 'Who can view personal galleries',
	
	'RATE'						=> 'Rate',
	'RATE_LEVEL'					=> 'Rate Level',
	'RATE_SCALE'					=> 'Rating Scale',
	'RATE_SYSTEM'					=> 'Enable rating system',
	'ROWS_PER_PAGE'					=> 'Number of rows on thumbnail page',
	'RSZ_HEIGHT'					=> 'Maximum-height on viewing image',
	'RSZ_WIDTH'						=> 'Maximum-width on viewing image',
	
	'SELECT_A_ALBUM'					=> 'Select a album',
	
	'THUMBNAIL_CACHE'					=> 'Thumbnail cache',
	'THUMBNAIL_CACHE_CLEARED_SUCCESSFULLY'	=> '<br />Your thumbnail cache has been cleared successfully<br />&nbsp;',
	'THUMBNAIL_QUALITY'				=> 'Thumbnail quality (1-100)',
	'THUMBNAIL_SETTINGS'				=> 'Thumbnail Settings',
	
	'UPLOAD'						=> 'Upload',
	'UPLOAD_LEVEL'					=> 'Upload Level',
	'USER_IMAGES_LIMIT'				=> 'Images limit per Album for each user (-1 = unlimited)',
	
	'VIEW_LEVEL'					=> 'View Level',
	
	'WATERMARK_IMAGES'				=> 'Watermark images',
	'WATERMARK_SOURCE'		 		=> 'Watermark source file (relative to your phpbb root)',

//new one's
	'ALBUM_TYPE'					=> 'Album Type',
	'ALBUM_CATEGORY'				=> 'Category',
	'ALBUM_NO_CATEGORY'				=> 'Album',
	'ALBUM_PARENT'					=> 'Parent Album',
	'NO_PARENT_ALBUM'				=> '&raquo; No Parent Album',
	'ALBUM_NAME'					=> 'Album Name',
	'ALBUM_SETTINGS'				=> 'Album Settings',
	'ALBUM_DELETE'					=> 'Delete %s',
	'DELETE_SUBS'					=> 'Delete subalbums',
	'DELETE_IMAGES'					=> 'Delete Images',
	'HANDLE_SUBS'					=> 'What to do with the subalbums',
	'HANDLE_IMAGES'					=> 'What to do with the images',
	'DELETE_ALBUM_SUBS'				=> 'Please remove the subalbums first',
	'NO_SUBALBUMS'					=> 'No Albums attached',
	'GALLERY_INDEX'					=> 'Gallery-Index',

//new one's
	'ACP_IMPORT_ALBUMS'					=> 'Import Images',
	'ACP_IMPORT_ALBUMS_EXPLAIN'			=> 'Here you can bulk import images from the file system. Before importing images, please be sure to resize them by hand.',
	'IMPORT_MISSING_DIR'			=> 'Please provide the directory where the images reside.',
	'IMPORT_MISSING_ALBUM'			=> 'Please select an album to import the images into.',
	'NO_DESC'						=> 'no description',
	'IMPORT_DEBUG_MES'				=> '%1$s images imported. There are still %2$s images remaining.',
	'IMPORT_FINISHED'				=> 'All %1$s images successful imported.',
	'IMPORT_ALBUM'				=> 'Album to import images to:',
	'UPLOAD_IMAGES'				=> 'Upload Multiple Images',
));

$lang = array_merge($lang, array(
	'ACP_GALLERY_CLEANUP_EXPLAIN'	=> 'Here you can delete some remains.',
	'ADD_PERMISSIONS'				=> 'Add Permissions',
	'ALBUM_ID'						=> 'Album-ID',
	'ALBUM_IMAGE'					=> 'Album image',

	'CACHE_DIR_SIZE'				=> 'cache/-directory size',
	'CHANGE_AUTHOR'					=> 'Change author to guest',
	'CHECK'							=> 'Check',
	'CHECK_AUTHOR_EXPLAIN'			=> 'No images without valid author found.',
	'CHECK_COMMENT_EXPLAIN'			=> 'No comments without valid author found.',
	'CHECK_ENTRY_EXPLAIN'			=> 'You have to run the check, to search for files without database-entry.',
	'CHECK_PERSONALS_EXPLAIN'		=> 'No personal albums without valid owner found.',
	'CHECK_SOURCE_EXPLAIN'			=> 'No entry found. You should run the check, to be sure.',
	'CLEAN_CHANGED'					=> 'Author changed to "Guest".',
	'CLEAN_GALLERY'					=> 'Clean gallery',
	'CLEAN_GALLERY_ABORT'			=> 'Cleanup abort!',
	'CLEAN_AUTHORS_DONE'			=> 'Images without valid author deleted.',
	'CLEAN_COMMENTS_DONE'			=> 'Comments without valid author deleted.',
	'CLEAN_ENTRIES_DONE'			=> 'Files without database-entry deleted.',
	'CLEAN_PERSONALS_DONE'			=> 'Personal albums without valid owner deleted.',
	'CLEAN_SOURCES_DONE'			=> 'Images without file deleted.',
	'COMMENT_ID'					=> 'Comment-ID',
	'CONFIRM_CLEAN'					=> 'This step can not be undone!',
	'CONFIRM_CLEAN_AUTHORS'			=> 'Delete images without valid author?',
	'CONFIRM_CLEAN_COMMENTS'		=> 'Delete comments without valid author?',
	'CONFIRM_CLEAN_ENTRIES'			=> 'Delete files without database-entry?',
	'CONFIRM_CLEAN_PERSONALS'		=> 'Delete personal albums without valid owner?',
	'CONFIRM_CLEAN_SOURCES'			=> 'Delete images without file?',
	'COPY_PERMISSIONS'				=> 'Copy Permissions from',

	'DELETE_PERMISSIONS'			=> 'Delete permissions',
	'DISP_EXIF_DATA'				=> 'Display Exif-data',
	'DISP_FAKE_THUMB'				=> 'View thumbnail in albumlist',
	'DISP_PERSONAL_ALBUM_PROFIL'	=> 'Show link to personal album in user-profile',
	'DISP_TOTAL_IMAGES'				=> 'Show "Total images" on index.' . $phpEx,
	'DISP_USER_IMAGES_PROFIL'		=> 'Show statistic with uploaded images in user-profile',
	'DONT_COPY_PERMISSIONS'			=> 'don\'t copy permissions',

	'FAKE_THUMB_SIZE'				=> 'Thumbnail-size',
	'FAKE_THUMB_SIZE_EXP'			=> 'If you want to resize them to the full size, remember 16 pixels for the black-info-line',

	'GALLERY_STATS'					=> 'Gallery statistics',
	'GALLERY_VERSION'				=> 'Gallery version',
	'GUPLOAD_DIR_SIZE'				=> 'upload/-directory size',

	'IMAGE_ID'						=> 'Image-ID',
	'IMAGES_PER_DAY'				=> 'Images per day',
	'IMPORT_DIR_EMPTY'				=> 'The folder %simport/ is empty. You need to upload the images, before you can import them.',
	'IMPORT_SELECT'					=> 'Choose the images which you want to import. Successful uploaded images are deleted. All other images are still available.',
	'IMPORT_USER'					=> 'Uploaded by',
	'IMPORT_USER_EXP'				=> 'You can add the images to another user here.',

	'MANAGE_CRASHED_IMAGES'			=> 'Manage crashed images',
	'MANAGE_CRASHED_ENTRIES'		=> 'Manage crashed entries',
	'MANAGE_PERSONALS'				=> 'Manage personal albums',
	'MISSING_AUTHOR'				=> 'Images without valid author',
	'MISSING_AUTHOR_C'				=> 'Comments without valid author',
	'MISSING_ENTRY'					=> 'Files without database-entry',
	'MISSING_OWNER'					=> 'Personal albums without valid owner',
	'MISSING_OWNER_EXP'				=> 'Subalbums, images and comments get deleted aswell.',
	'MISSING_SOURCE'				=> 'Images without files',
	'MISSING_ALBUM_NAME'			=> 'You need to enter a name for the album',

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
	'PERMISSION_M'					=> 'Moderation',
	'PERMISSION_MISC'				=> 'Misc', //Miscellaneous
	'PERMISSION_M_COMMENTS'			=> 'Can moderate comments',
	'PERMISSION_M_DELETE'			=> 'Can delete images',
	'PERMISSION_M_EDIT'				=> 'Can edit images',
	'PERMISSION_M_MOVE'				=> 'Can move images',
	'PERMISSION_M_REPORT'			=> 'Can manage reports',
	'PERMISSION_M_STATUS'			=> 'Can approve and lock images',

	'PERMISSION_EMPTY'				=> 'You didn\'t set all permissions.',
	'PERMISSIONS_STORED'			=> 'Permissions were stored successful.',

	'REMOVE_IMAGES_FOR_CAT'			=> 'You need to remove the images of the album, before you can switch the album-type to category.',
	'RESYNC_IMAGECOUNTS'			=> 'Resynchronise image counts',
	'RESYNC_IMAGECOUNTS_EXPLAIN'	=> 'Only existing images will be taken into consideration.',
	'RESYNC_IMAGECOUNTS_CONFIRM'	=> 'Are you sure you wish to resynchronise image counts?',
	'RESYNC_LAST_IMAGES'			=> 'Refresh "Last image"',
	'RESYNC_PERSONALS'				=> 'Resynchronise personal album id\'s',
	'RESYNC_PERSONALS_CONFIRM'		=> 'Are you sure you wish to resynchronise image counts?',

	'SELECT_ALBUMS'					=> 'Select albums',
	'SELECTED_ALBUMS'				=> 'Selected albums',
	'SELECT_GROUPS'					=> 'Select groups',
	'SELECTED_GROUPS'				=> 'Selected groups',
	'SELECT_PERMISSIONS'			=> 'Select permissions',
	'SELECTED_PERMISSIONS'			=> 'Selected permissions',
	'SET_PERMISSIONS'				=> '<br />Set <a href="%s">permissions</a> now.',
	'SORRY_NO_STATISTIC'			=> 'Sorry, this statistic-value is not yet available.',

	'THIS_WILL_BE_REPORTED'			=> 'Known Bug, sorry guys!',

	'WATERMARK'						=> 'Watermark',
	'WATERMARK_EXP'					=> 'To avoid small images from being covered by the watermark, you may enter a minimum-width/height here.',
	'WATERMARK_OPTIONS'				=> 'Watermark options',
	'WATERMARK_WIDTH'				=> 'Minimum-width for watermark',
	'WATERMARK_HEIGHT'				=> 'Minimum-height for watermark',
));

// Added for 0.4.0-RC3
$lang = array_merge($lang, array(
	'PERMISSION_NO_GROUP'			=> 'You didn\'t select a group to set the permissions.',
	'PURGED_CACHE'					=> 'Purged the cache',

	'RESYNCED_IMAGECOUNTS'			=> 'Resynchronised image counts',
	'RESYNCED_LAST_IMAGES'			=> 'Refreshed "Last image"',
	'RESYNCED_PERSONALS'			=> 'Resynchronised personal album id\'s',

	'SHORTED_IMAGENAMES'			=> 'Shorten Imagenames',
	'SHORTED_IMAGENAMES_EXP'		=> 'If the name of an image is to long and doesn\'t include spaces, the layout maybe destroyed.',
));

// Added for 0.4.0
$lang = array_merge($lang, array(
	'UC_IMAGE_NAME'					=> 'Imagename',
	'UC_IMAGE_ICON'					=> 'Lastimage icon',
	'UC_LINK_CONFIG'				=> 'Link configuration',
	'UC_LINK_HIGHSLIDE'				=> 'Open Highslide-Feature',
	'UC_LINK_IMAGE'					=> 'Open Image',
	'UC_LINK_IMAGE_PAGE'			=> 'Open Image-page (with comments and rates)',
	'UC_LINK_LYTEBOX'				=> 'Open Lytebox-Feature',
	'UC_LINK_NONE'					=> 'No Link',
	'UC_THUMBNAIL'					=> 'Thumbnail',
));

?>