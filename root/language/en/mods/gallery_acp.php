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
	'ACP_GALLERY_OVERVIEW'				=> 'phpBB Gallery Overview',
	'ACP_GALLERY_OVERVIEW_EXPLAIN'		=> 'Gallery Admin Overview. In the next Version there will be some statistics etc. here.',
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
	'GALLERY_CLEAR_CACHE_CONFIRM'			=> 'If you use the Thumbnail Cache feature you must clear your thumbnail cache after changing your thumbnail settings in Album Configuration to make them re-generated.<br /><br /> Do you want to clear them now?',
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
	'MAX_IMAGES'					=> 'Maximum number of images for each Album (-1 = unlimited)',
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
	'IMPORT_DEBUG'					=> 'Debug Import Status',
	'IMPORT_DEBUG_MES'				=> '%1$s images imported. There are still %2$s images remaining. Repeat the process.',
	'IMPORT_DIR'				=> 'Full path to your images:',
	'IMPORT_DIR_EXP'			=> 'Windows: C:/www/mysite/phpBB3/gallery/import<br />Linux: /home/user/www/phpBB3/gallery/import',
	'IMPORT_DIR_DEL'			=> 'Please note, that the images are <span style="color: red;">deleted</span> form the root.<br />So you better copy them to an other folder.',
	'IMPORT_ALBUM'				=> 'Album to import images to:',
	'IMPORT_CIRCLE'				=> 'Images to import per cycle:',
	'UPLOAD_IMAGES'				=> 'Upload Multiple Images',

	'VIEW_PERSONALS'			=> 'Allow to view personal Albums',
	'CREATE_PERSONALS'			=> 'Allow to create personal Albums',
	'ALLOWED_SUBS'				=> 'Number of Subalbums (attached to the users one)',
));

$lang = array_merge($lang, array(
	'DELETE_PERMISSIONS'			=> 'Delete permissions',
	'DISP_FAKE_THUMB'				=> 'View thumbnail in albumlist',

	'FAKE_THUMB_SIZE'				=> 'Thumbnail-size',
	'FAKE_THUMB_SIZE_EXP'			=> 'If you want to resize them to the full size, remember 16 pixels for the black-info-line',

	'OWN_PERSONAL_ALBUMS'			=> 'Own personal albums',

	'PERMISSION'					=> 'Permission',
	'PERMISSION_NEVER'				=> 'Never',
	'PERMISSION_NO'					=> 'No',
	'PERMISSION_YES'				=> 'Yes',

	'PERMISSION_A_MODERATE'			=> 'Can moderate album',
	'PERMISSION_ALBUM_COUNT'		=> 'Number of personal subalbums',
	'PERMISSION_C_DELETE'			=> 'Can delete comment',
	'PERMISSION_C_EDIT'				=> 'Can edit comment',
	'PERMISSION_C_POST'				=> 'Can comment on image',
	'PERMISSION_I_APPROVE'			=> 'Can avoid image-approval',
	'PERMISSION_I_COUNT'			=> 'Number of uploaded images',
	'PERMISSION_I_DELETE'			=> 'Can delete images',
	'PERMISSION_I_EDIT'				=> 'Can edit images',
	'PERMISSION_I_LOCK'				=> 'Can lock images',
	'PERMISSION_I_RATE'				=> 'Can rate images',
	'PERMISSION_I_REPORT'			=> 'Can report images',
	'PERMISSION_I_UPLOAD'			=> 'Can upload images',
	'PERMISSION_I_VIEW'				=> 'Can view images',

	'PERMISSION_EMPTY'				=> 'You didn\'t set all permissions.',
	'PERMISSIONS_STORED'			=> 'Permissions were stored successful.',

	'SELECT_ALBUMS'					=> 'Select albums',
	'SELECTED_ALBUMS'				=> 'Selected albums',
	'SELECT_GROUPS'					=> 'Select groups',
	'SELECTED_GROUPS'				=> 'Selected groups',
	'SELECT_PERMISSIONS'			=> 'Select permissions',
	'SELECTED_PERMISSIONS'			=> 'Selected permissions',
	'SET_PERMISSIONS'				=> '<br />Set <a href="%s">permissions</a> now.',

	'THIS_WILL_BE_REPORTED'			=> 'Known Bug, sorry guys!',
));

?>