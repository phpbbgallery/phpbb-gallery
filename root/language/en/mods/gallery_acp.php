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
	'ACP_GALLERY_CLEANUP_EXPLAIN'	=> 'Here you can delete some remains.',
	'ACP_GALLERY_OVERVIEW'			=> 'phpBB Gallery',
	'ACP_GALLERY_OVERVIEW_EXPLAIN'	=> 'Here are some statistics about your gallery.',
	'ACP_IMPORT_ALBUMS'				=> 'Import Images',
	'ACP_IMPORT_ALBUMS_EXPLAIN'		=> 'Here you can bulk import images from the file system. Before importing images, please be sure to resize them by hand.',


	'ADD_ALBUM_ON_TOP'				=> 'Add album at the top',
	'ADD_PERMISSIONS'				=> 'Add Permissions',
	'ALBUM_ADMIN'					=> 'Album administration',
	'ALBUM_ADMIN_EXPLAIN'			=> 'In phpBB Gallery there are no categories, everything is album based. Each album can have an unlimited number of subalbums and you can determine whether each may be posted to or not (i.e. whether it acts like an old category). Here you can add, edit, delete, lock, unlock individual albums as well as set certain additional controls. If your images have got out of sync you can also resynchronise a album. <strong>You need to copy or set appropriate permissions for newly created albums to have them displayed.</strong>',
	'ALBUM_AUTH_TITLE'				=> 'Album Permissions',
	'ALBUM_CREATED'					=> 'Album created successfully.',
	'ALBUM_DELETE'					=> 'Delete album',
	'ALBUM_DELETE_EXPLAIN'			=> 'The form below will allow you to delete a album and decide where you want to put the images it contained',
	'ALBUM_DELETED'					=> 'This album has been deleted successfully.',
	'ALBUM_DESC'					=> 'Description',
	'ALBUM_DESC_EXPLAIN'			=> 'Any HTML markup entered here will be displayed as is.',
	'ALBUM_DESC_TOO_LONG'			=> 'The album description is too long, it must be less than 4000 characters.',
	'ALBUM_EDIT_EXPLAIN'			=> 'The form below will allow you to customise this album. Please note that moderation control are set via album permissions for each ' . /*user or */ 'usergroup.',
	'ALBUM_ID'						=> 'Album-ID',
	'ALBUM_IMAGE'					=> 'Album image',
	'ALBUM_IMAGE_EXPLAIN'			=> 'Location, relative to the phpBB root directory, of an additional image to associate with this album.',
	'ALBUM_NAME_EMPTY'				=> 'You must enter a name for this album.',
	'ALBUM_NO_TYPE_CHANGE_TO_CONTEST'	=> 'A Non-Contest-Album can not be turned into a Contest-Albums.',
	'ALBUM_PARENT'					=> 'Parent album',
	'ALBUM_PASSWORD'				=> 'Album password',
	'ALBUM_PASSWORD_EXPLAIN'		=> 'Defines a password for this album, use the permission system in preference.',
	'ALBUM_PASSWORD_CONFIRM'		=> 'Confirm album password',
	'ALBUM_PASSWORD_CONFIRM_EXPLAIN'	=> 'Only needs to be set if a album password is entered.',
	'ALBUM_RESYNCED'				=> 'Album “%s” successfully resynced',
	'ALBUM_SETTINGS'				=> 'Album settings',
	'ALBUM_STATUS'					=> 'Album status',
	'ALBUM_TYPE'					=> 'Album type',
	'ALBUM_TYPE_CAT'				=> 'Category',
	'ALBUM_TYPE_CONTEST'			=> 'Contest',
	'ALBUM_TYPE_UPLOAD'				=> 'Album',
	'ALBUM_UPDATED'					=> 'Album has been updated successfully.',
	'ALBUM_WITH_CONTEST_NO_TYPE_CHANGE'	=> 'Contest-Albums can not be turned into a Non-Contest-Album.',
	'ALBUMS'						=> 'Albums',

	'CACHE_DIR_SIZE'				=> 'cache/-directory size',
	'CHANGE_AUTHOR'					=> 'Change author to guest',
	'CHECK'							=> 'Check',
	'CHECK_AUTHOR_EXPLAIN'			=> 'No images without valid author found.',
	'CHECK_COMMENT_EXPLAIN'			=> 'No comments without valid author found.',
	'CHECK_ENTRY_EXPLAIN'			=> 'You have to run the check, to search for files without database-entry.',
	'CHECK_PERSONALS_EXPLAIN'		=> 'No personal albums without valid owner found.',
	'CHECK_PERSONALS_BAD_EXPLAIN'	=> 'No personal albums found.',
	'CHECK_SOURCE_EXPLAIN'			=> 'No entry found. You should run the check, to be sure.',
	'CLEAN_AUTHORS_DONE'			=> 'Images without valid author deleted.',
	'CLEAN_CHANGED'					=> 'Author changed to "Guest".',
	'CLEAN_COMMENTS_DONE'			=> 'Comments without valid author deleted.',
	'CLEAN_ENTRIES_DONE'			=> 'Files without database-entry deleted.',
	'CLEAN_GALLERY'					=> 'Clean gallery',
	'CLEAN_GALLERY_ABORT'			=> 'Cleanup abort!',
	'CLEAN_PERSONALS_DONE'			=> 'Personal albums without valid owner deleted.',
	'CLEAN_PERSONALS_BAD_DONE'		=> 'Personal albums from selected users deleted.',
	'CLEAN_SOURCES_DONE'			=> 'Images without file deleted.',
	'COLS_PER_PAGE'					=> 'Number of columns on thumbnail page',
	'COMMENT'						=> 'Comment',
	'COMMENT_ID'					=> 'Comment-ID',
	'COMMENT_SYSTEM'				=> 'Enable comment system',
	'CONFIRM_CLEAN'					=> 'This step can not be undone!',
	'CONFIRM_CLEAN_AUTHORS'			=> 'Delete images without valid author?',
	'CONFIRM_CLEAN_COMMENTS'		=> 'Delete comments without valid author?',
	'CONFIRM_CLEAN_ENTRIES'			=> 'Delete files without database-entry?',
	'CONFIRM_CLEAN_PERSONALS'		=> 'Delete personal albums without valid owner?<br /><strong>» %s</strong>',
	'CONFIRM_CLEAN_PERSONALS_BAD'	=> 'Delete personal albums from selected users?<br /><strong>» %s</strong>',
	'CONFIRM_CLEAN_SOURCES'			=> 'Delete images without file?',
	'CONTEST_DATE_EXPLAIN'			=> 'Please enter date in YYYY-MM-DD HH:MM format.',
	'CONTEST_END'					=> 'Contest end',
	'CONTEST_END_BEFORE_RATING'		=> 'The contest-end must not be before the contest-rating-start.',
	'CONTEST_END_BEFORE_START'		=> 'The contest-end must not be before the contest-start.',
	'CONTEST_END_EXPLAIN'			=> 'After the end of the contest, users can no longer rate images.',
	'CONTEST_END_INVALID'			=> 'Invalid contest-end (%s). Please enter date in YYYY-MM-DD HH:MM format.',
	'CONTEST_RATING'				=> 'Rating start',
	'CONTEST_RATING_BEFORE_START'	=> 'The contest-rating-start must not be before the contest-start.',
	'CONTEST_RATING_EXPLAIN'		=> 'After the "Rating start", users can no longer upload images.',
	'CONTEST_RATING_INVALID'		=> 'Invalid contest-rating-start (%s). Please enter date in YYYY-MM-DD HH:MM format.',
	'CONTEST_SETTINGS'				=> 'Contest settings',
	'CONTEST_START'					=> 'Contest start',
	'CONTEST_START_EXPLAIN'			=> 'At the start of the contest, users are allowed to upload images.',
	'CONTEST_START_INVALID'			=> 'Invalid contest-start (%s). Please enter date in YYYY-MM-DD HH:MM format.',
	'COPY_PERMISSIONS'				=> 'Copy Permissions from',
	'COPY_PERMISSIONS_ADD_EXPLAIN'	=> 'If you select to copy permissions, the album will have the same permissions as the one you select here. If no album is selected you need to set the permissions afterwards.',
	'COPY_PERMISSIONS_EDIT_EXPLAIN'	=> 'If you select to copy permissions, the album will have the same permissions as the one you select here. This will overwrite any permissions you have previously set for this album with the permissions of the album you select here. If no album is selected the current permissions will be kept.',
	'CREATE_ALBUM'					=> 'Create new album',

	'DECIDE_MOVE_DELETE_CONTENT'	=> 'Delete content or move to album',
	'DECIDE_MOVE_DELETE_SUBALBUMS'	=> 'Delete subalbums or move to album',
	'DEFAULT_SORT_METHOD'			=> 'Default Sort Method',
	'DEFAULT_SORT_ORDER'			=> 'Default Sort Order',
	'DELETE_ALBUM_SUBS'				=> 'Please remove the subalbums first',
	'DELETE_ALL_IMAGES'				=> 'Delete images',
	'DELETE_IMAGES'					=> 'Delete images',
	'DELETE_PERMISSIONS'			=> 'Delete permissions',
	'DELETE_SUBALBUMS'				=> 'Delete subalbums and their images',
	'DISP_BIRTHDAYS'				=> 'Show birthdays',
	'DISP_EXIF_DATA'				=> 'Display Exif-data',
	'DISP_FAKE_THUMB'				=> 'View thumbnail in albumlist',
	'DISP_LOGIN'					=> 'Show login-field',
	'DISP_LOGIN_EXP'				=> 'Guest only',
	'DISP_PERSONAL_ALBUM_PROFILE'	=> 'Show link to personal album in user-profile',
	'DISP_STATISTIC'				=> 'Show gallery-statistic',
	'DISP_TOTAL_IMAGES'				=> 'Show "Total images" on index.' . $phpEx,
	'DISP_USER_IMAGES_PROFILE'		=> 'Show statistic with uploaded images in user-profile',
	'DISP_VIEWTOPIC_ICON'			=> 'Show button to personal album on viewtopic.' . $phpEx,
	'DISP_VIEWTOPIC_IMAGES'			=> 'Show image-counter on viewtopic.' . $phpEx,
	'DISP_VIEWTOPIC_LINK'			=> 'Link image-counter to user´s images',
	'DISP_WHOISONLINE'				=> 'Show Who-Is-Online',
	'DISPLAY_IN_RRC'				=> 'Display images of this album in "Recent-Random"-images',
	'DONT_COPY_PERMISSIONS'			=> 'Do not copy permissions',

	'EDIT_ALBUM'					=> 'Edit album',

	'FAKE_THUMB_SIZE'				=> 'Thumbnail-size',
	'FAKE_THUMB_SIZE_EXP'			=> 'If you want to resize them to the full size, remember 16 pixels for the black-info-line',

	'GALLERY_ALBUMS_TITLE'			=> 'Gallery Albums Control',
	'GALLERY_CONFIG'				=> 'Gallery Configuration',
	'GALLERY_CONFIG_EXPLAIN'		=> 'You can change the general settings of phpBB Gallery here.',
	'GALLERY_CONFIG_UPDATED'		=> 'Gallery Configuration has been updated successfully.',
	'GALLERY_INDEX'					=> 'Gallery-Index',
	'GALLERY_PURGE_CACHE_EXPLAIN'	=> 'If you use the Thumbnail Cache feature you must clear your thumbnail cache after changing your thumbnail settings in "Gallery configuration" to make them regenerated.',
	'GALLERY_STATS'					=> 'Gallery statistics',
	'GALLERY_VERSION'				=> 'Gallery version',
	'GD_VERSION'					=> 'Optimize for GD version',
	'GENERAL_ALBUM_SETTINGS'		=> 'General album settings',
	'GIF_ALLOWED'					=> 'Allowed to upload GIF files',
	'GUPLOAD_DIR_SIZE'				=> 'upload/-directory size',

	'HACKING_ATTEMPT'				=> 'Hacking attempt!',
	'HANDLE_IMAGES'					=> 'What to do with the images',
	'HANDLE_SUBS'					=> 'What to do with the subalbums',
	'HOTLINK_ALLOWED'				=> 'Allowed domains for hotlink',
	'HOTLINK_ALLOWED_EXP'			=> 'The domains must be seperated by comma only (no space). Exp: "flying-bits.org,phpbb.com"',
	'HOTLINK_PREVENT'				=> 'Hotlink Prevention',

	'IMAGE_DESC_MAX_LENGTH'			=> 'Image Description/Comment Max Length (bytes)',
	'IMAGE_ID'						=> 'Image-ID',
	'IMAGE_SETTINGS'				=> 'Image settings',
	'IMAGES_PER_DAY'				=> 'Images per day',
	'IMPORT_ALBUM'					=> 'Album to import images to:',
	'IMPORT_DEBUG_MES'				=> '%1$s images imported. There are still %2$s images remaining.',
	'IMPORT_DIR_EMPTY'				=> 'The folder %s is empty. You need to upload the images, before you can import them.',
	'IMPORT_FINISHED'				=> 'All %1$s images successful imported.',
	'IMPORT_MISSING_ALBUM'			=> 'Please select an album to import the images into.',
	'IMPORT_SELECT'					=> 'Choose the images which you want to import. Successful uploaded images are deleted. All other images are still available.',
	'IMPORT_SCHEMA_CREATED'			=> 'The import-schema was successfully created, please wait while the images get imported.',
	'IMPORT_USER'					=> 'Uploaded by',
	'IMPORT_USER_EXP'				=> 'You can add the images to another user here.',
	'INDEX_SETTINGS'				=> 'Settings for gallery/index.' . $phpEx,
	'INFO_LINE'						=> 'Display file-size on thumbnail',
	'INHERIT_PERMISSIONS_ALBUM'		=> 'Inherit permissions of an other album',
	'INHERIT_PERMISSIONS_VICTIM'	=> 'Inherit permissions of an other setting',

	'JPG_ALLOWED'					=> 'Allowed to upload JPG files',
	'JPG_QUALITY'					=> 'JPG-Qualtity',
	'JPG_QUALITY_EXP'				=> 'When rotating or resizing image, the filesize might get bigger than it was before the action. With this option you can reduce the quality, to save disk space.',

	'LIST_INDEX'					=> 'List subalbum in parent-album’s legend',
	'LIST_INDEX_EXPLAIN'			=> 'Displays this album on the index and elsewhere as a link within the legend of its parent-album if the parent-album’s “List subalbums in legend” option is enabled.',
	'LIST_SUBALBUMS'				=> 'List subalbums in legend',
	'LIST_SUBALBUMS_EXPLAIN'		=> 'Displays this album’s subalbums on the index and elsewhere as a link within the legend if their “List subalbum in parent-album’s legend” option is enabled.',
	'LOCKED'						=> 'Locked',

	'MANAGE_CRASHED_ENTRIES'		=> 'Manage crashed entries',
	'MANAGE_CRASHED_IMAGES'			=> 'Manage crashed images',
	'MANAGE_PERSONALS'				=> 'Manage personal albums',
	'MAX_IMAGES_PER_ALBUM'			=> 'Maximum number of images for each album',
	'MAX_IMAGES_PER_ALBUM_EXP'		=> 'Unlimited is -1',
	'MEDIUM_CACHE'					=> 'Cache resized images for image-page',
	'MEDIUM_DIR_SIZE'				=> 'medium/-directory size',
	'MISSING_ALBUM_NAME'			=> 'You need to enter a name for the album.',
	'MISSING_AUTHOR'				=> 'Images without valid author',
	'MISSING_AUTHOR_C'				=> 'Comments without valid author',
	'MISSING_ENTRY'					=> 'Files without database-entry',
	'MISSING_IMPORT_SCHEMA'			=> 'The specified import-schema (%s) could not be found.',
	'MISSING_OWNER'					=> 'Personal albums without valid owner',
	'MISSING_OWNER_EXP'				=> 'Subalbums, images and comments get deleted aswell.',
	'MISSING_SOURCE'				=> 'Images without files',
	'MOVE_IMAGES_TO'				=> 'Move images to',
	'MOVE_SUBALBUMS_TO'				=> 'Move subalbums to',

	'NEW_ALBUM_CREATED'				=> 'New album has been created successfully',
	'NO_ALBUM_SELECTED'				=> 'You need to select atleast one album.',
	'NO_DESTINATION_ALBUM'			=> 'You have not specified a album to move content to.',
	'NO_FILE_SELECTED'				=> 'You need to select atleast one file.',
	'NO_PERMISSIONS_SELECTED'		=> 'You need to select atleast one album or a special permission-system.',
	'NO_VICTIM_SELECTED'			=> 'You need to select atleast one user or group.',
	'NO_INHERIT'					=> 'Do not copy permissions',
	'NO_PARENT_ALBUM'				=> 'No parent',
	'NO_SUBALBUMS'					=> 'No Albums attached',
	'NUMBER_ALBUMS'					=> 'Number of albums',
	'NUMBER_IMAGES'					=> 'Number of images',
	'NUMBER_PERSONALS'				=> 'Number of personal albums',

	'OWN_PERSONAL_ALBUMS'			=> 'Own personal albums',

	'PERMISSION'					=> 'Permission',
	'PERMISSION_NEVER'				=> 'Never',
	'PERMISSION_NO'					=> 'No',
	'PERMISSION_SETTING'			=> 'Setting',
	'PERMISSION_YES'				=> 'Yes',

	'PERMISSION_A_LIST'				=> 'Can see album',
	'PERMISSION_ALBUM_COUNT'		=> 'Number of possible personal subalbums',
	'PERMISSION_ALBUM_UNLIMITED'	=> 'Unlimited number of personal subalbums',
	'PERMISSION_C'					=> 'Comments',
	'PERMISSION_C_DELETE'			=> 'Can delete own comments',
	'PERMISSION_C_EDIT'				=> 'Can edit own comments',
	'PERMISSION_C_POST'				=> 'Can comment on image',
	'PERMISSION_C_READ'				=> 'Can read comments',
	'PERMISSION_I'					=> 'Images',
	'PERMISSION_I_APPROVE'			=> 'Can upload without approval',
	'PERMISSION_I_COUNT'			=> 'Number of uploadable images',
	'PERMISSION_I_DELETE'			=> 'Can delete own images',
	'PERMISSION_I_EDIT'				=> 'Can edit own images',
	'PERMISSION_I_LOCK'				=> 'Can lock images',
	'PERMISSION_I_RATE'				=> 'Can rate images',
	'PERMISSION_I_RATE_EXPLAIN'		=> 'Guests and the image-uploader can <samp>NEVER</samp> rate images.',
	'PERMISSION_I_REPORT'			=> 'Can report images',
	'PERMISSION_I_UNLIMITED'		=> 'Can upload unlimited images',
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
	'PERMISSIONS'					=> 'Permissions',
	'PERMISSIONS_EXPLAIN'			=> 'Here you can alter which users and groups can access which albums.',
	'PERMISSIONS_STORED'			=> 'Permissions were stored successful.',
	'PERSONAL_ALBUM_INDEX'			=> 'View personal albums as album on the index',
	'PERSONAL_ALBUM_INDEX_EXP'		=> 'If choosen "No", there will be the link, right beneath.',
	'PGALLERIES_PER_PAGE'			=> 'Number of personal galleries per page',
	'PHPBB_INTEGRATION'				=> 'phpBB integration',
	'PNG_ALLOWED'					=> 'Allowed to upload PNG files',
	'PURGED_CACHE'					=> 'Purged the cache',

	'RATE_SCALE'					=> 'Rating Scale',
	'RATE_SYSTEM'					=> 'Enable rating system',
	'REDIRECT_ACL'					=> 'Now you are able to %sset permissions%s for this album.',
	'REMOVE_IMAGES_FOR_CAT'			=> 'You need to remove the images of the album, before you can switch the album-type to category.',
	'RESET_RATING'					=> 'Reset rating for album',
	'RESET_RATING_COMPLETED'		=> 'Rates successful deleted.',
	'RESET_RATING_CONFIRM'			=> 'Do you really want to delete all ratings on the images of the album "%s"?',
	'RESET_RATING_EXPLAIN'			=> 'Deletes all ratings on images in the specific album. Enter album-id in the field on the right side.',
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
	'ROTATE_IMAGES'					=> 'Allow to rotate images',
	'ROWS_PER_PAGE'					=> 'Number of rows on thumbnail page',

	'RRC_DISPLAY_ALBUMNAME'			=> 'Album name',
	'RRC_DISPLAY_COMMENTS'			=> 'Comments',
	'RRC_DISPLAY_IMAGENAME'			=> 'Image name',
	'RRC_DISPLAY_IMAGETIME'			=> 'Image time',
	'RRC_DISPLAY_IMAGEVIEWS'		=> 'Image views',
	'RRC_DISPLAY_IP'				=> 'User ip',
	'RRC_DISPLAY_NONE'				=> 'None',
	'RRC_DISPLAY_OPTIONS'			=> 'Which values should be displayed underneath the thumbnails',
	'RRC_DISPLAY_USERNAME'			=> 'Username',
	'RRC_DISPLAY_RATINGS'			=> 'Ratings',
	'RRC_GINDEX'					=> 'Recent- & Random-Images & Comment - Feature',
	'RRC_GINDEX_COLUMNS'			=> 'Columns',
	'RRC_GINDEX_COMMENTS'			=> 'Collapse comments',
	'RRC_GINDEX_CONTESTS'			=> 'Number of contests',
	'RRC_GINDEX_CROWS'				=> 'Number of comments',
	'RRC_GINDEX_MODE'				=> 'Mode',
	'RRC_GINDEX_MODE_EXP'			=> '"Random images" may take some time to load, on large databases!',
	'RRC_GINDEX_PGALLERIES'			=> 'View images of personal albums',
	'RRC_GINDEX_ROWS'				=> 'Rows',
	'RRC_MODE_COMMENTS'				=> 'Comments',
	'RRC_MODE_NONE'					=> 'None',
	'RRC_MODE_RANDOM'				=> 'Random images',
	'RRC_MODE_RECENT'				=> 'Recent images',
	'RRC_PROFILE_COLUMNS'			=> 'Columns',
	'RRC_PROFILE_MODE'				=> 'Mode of "Recent- & Random-Images"-Feature in the profile',
	'RRC_PROFILE_MODE_EXP'			=> '"Random images" may take some time to load, on large databases!',
	'RRC_PROFILE_ROWS'				=> 'Rows',

	'RSZ_HEIGHT'					=> 'Maximum-height on viewing image',
	'RSZ_WIDTH'						=> 'Maximum-width on viewing image',

	'SELECT_ALBUM'					=> 'Select album',
	'SELECT_GROUPS'					=> 'Select groups',
	'SELECT_PERM_SYS'				=> 'Select permission-system',
	'SELECT_PERMISSIONS'			=> 'Select permissions',
	'SELECTED_ALBUMS'				=> 'Selected albums',
	'SELECTED_GROUPS'				=> 'Selected groups',
	'SELECTED_PERM_SYS'				=> 'Selected permission-system',
	'SET_PERMISSIONS'				=> '<br />Set <a href="%s">permissions</a> now.',
	'SHORTED_IMAGENAMES'			=> 'Shorten Imagenames',
	'SHORTED_IMAGENAMES_EXP'		=> 'If the name of an image is to long and doesn\'t include spaces, the layout maybe destroyed.',
	'SORRY_NO_STATISTIC'			=> 'Sorry, this statistic-value is not yet available.',
	'SYNC_IN_PROGRESS'				=> 'Synchronizing album',
	'SYNC_IN_PROGRESS_EXPLAIN'		=> 'Currently resyncing image range %1$d/%2$d.',

	'THUMBNAIL_CACHE'				=> 'Thumbnail cache',
	'THUMBNAIL_QUALITY'				=> 'Thumbnail quality (1-100)',
	'THUMBNAIL_SETTINGS'			=> 'Thumbnail Settings',

	'UC_IMAGE_NAME'					=> 'Imagename',
	'UC_IMAGE_ICON'					=> 'Lastimage icon',
	'UC_IMAGEPAGE'					=> 'Image on Image-page (with comments and rates)',
	'UC_LINK_CONFIG'				=> 'Link configuration',
	'UC_LINK_HIGHSLIDE'				=> 'Open Highslide-Plugin',
	'UC_LINK_IMAGE'					=> 'Open Image',
	'UC_LINK_IMAGE_PAGE'			=> 'Open Image-page (with comments and rates)',
	'UC_LINK_LYTEBOX'				=> 'Open Lytebox-Plugin',
	'UC_LINK_NONE'					=> 'No Link',
	'UC_LINK_SHADOWBOX'				=> 'Open Shadowbox-Plugin',
	'UC_THUMBNAIL'					=> 'Thumbnail',
	'UC_THUMBNAIL_EXP'				=> 'Also used for the BBCode.',
	'UNLOCKED'						=> 'Unlocked',
	'UPDATE_BBCODE'					=> 'Update BBCode',
	'UPLOAD_IMAGES'					=> 'Upload Multiple Images',

	'VIEW_IMAGE_URL'				=> 'View Image-URL on imagepage',

	'WATERMARK'						=> 'Watermark',
	'WATERMARK_HEIGHT'				=> 'Minimum-height for watermark',
	'WATERMARK_HEIGHT_EXP'			=> 'To avoid small images from being covered by the watermark, you may enter a minimum height of the image here. If the image is smaller, the watermark will not be viewed.',
	'WATERMARK_IMAGES'				=> 'Watermark images',
	'WATERMARK_OPTIONS'				=> 'Watermark options',
	'WATERMARK_POSITION'			=> 'Watermark position',
	'WATERMARK_POSITION_BOTTOM'		=> 'bottom',
	'WATERMARK_POSITION_CENTER'		=> 'center',
	'WATERMARK_POSITION_LEFT'		=> 'left',
	'WATERMARK_POSITION_MIDDLE'		=> 'middle',
	'WATERMARK_POSITION_RIGHT'		=> 'right',
	'WATERMARK_POSITION_TOP'		=> 'top',
	'WATERMARK_SOURCE'		 		=> 'Watermark source file (relative to your phpbb root)',
	'WATERMARK_WIDTH'				=> 'Minimum-width for watermark',
	'WATERMARK_WIDTH_EXP'			=> 'To avoid small images from being covered by the watermark, you may enter a minimum width of the image here. If the image is smaller, the watermark will not be viewed.',
));

/**
* A copy of Handyman` s MOD version check, to view it on the gallery overview
*/
$lang = array_merge($lang, array(
	'ANNOUNCEMENT_TOPIC'	=> 'Release Announcement',
	'CURRENT_VERSION'		=> 'Current Version',
	'DOWNLOAD_LATEST'		=> 'Download Latest Version',
	'LATEST_VERSION'		=> 'Latest Version',
	'NO_INFO'					=> 'Version server could not be contacted',
	'NOT_UP_TO_DATE'			=> '%s is not up to date',
	'RELEASE_ANNOUNCEMENT'	=> 'Annoucement Topic',
	'UP_TO_DATE'			=> '%s is up to date',
	'VERSION_CHECK'			=> 'MOD Version Check',
));

?>