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
	'ALBUM'								=> 'Album',
	'ALBUM_DELETE_CONFIRM'				=> 'Are you sure you want to delete these images?',
	'ALBUM_NOT_EXIST'					=> 'This album does not exist',
	'ALBUM_PERMISSIONS'					=> 'Album Permissions',
	'ALBUM_REACHED_QUOTA'				=> 'This album has reached the quota of images. You cannot upload any more. Please contact the administrator for more information.',
	'ALBUM_UPLOAD_NEED_APPROVAL'		=> 'Your image has been uploaded successfully.<br /><br />But the feature Image Approval has been enabled so your image must be approved by a administrator or a moderator before posting',
	'ALBUM_UPLOAD_SUCCESSFUL'			=> 'Your image has been uploaded successfully',

	'ALBUM_COMMENT_CAN'					=> 'You <strong>can</strong> post comments to images in this album',
	'ALBUM_COMMENT_CANNOT'				=> 'You <strong>cannot</strong> post comments to images in this album',
	'ALBUM_DELETE_CAN'					=> 'You <strong>can</strong> delete your images and comments in this album',
	'ALBUM_DELETE_CANNOT'				=> 'You <strong>cannot</strong> delete your images and comments in this album',
	'ALBUM_EDIT_CAN'					=> 'You <strong>can</strong> edit your images and comments in this album',
	'ALBUM_EDIT_CANNOT'					=> 'You <strong>cannot</strong> edit your images and comments in this album',
	'ALBUM_RATE_CAN'					=> 'You <strong>can</strong> rate images in this album',
	'ALBUM_RATE_CANNOT'					=> 'You <strong>cannot</strong> rate images in this album',
	'ALBUM_UPLOAD_CAN'					=> 'You <strong>can</strong> upload new images in this album',
	'ALBUM_UPLOAD_CANNOT'				=> 'You <strong>cannot</strong> upload new images in this album',
	'ALBUM_VIEW_CAN'					=> 'You <strong>can</strong> view images in this album',
	'ALBUM_VIEW_CANNOT'					=> 'You <strong>cannot</strong> view images in this album',

	'ALREADY_RATED'					=> 'You have already rated this image',
	'APPROVAL'						=> 'Approval',
	'APPROVE'						=> 'Approve',
	'APPROVED'						=> 'Approved',

	'JPG_ALLOWED'					=> 'Allowed to upload JPG files',
	'PNG_ALLOWED'					=> 'Allowed to upload PNG files',
	'GIF_ALLOWED'					=> 'Allowed to upload GIF files',

	'BAD_UPLOAD_FILE_SIZE'				=> 'Your uploaded file is too large or corrupted',

	'CLICK_RETURN_ALBUM'				=> 'Click %shere%s to return to the album',
	'CLICK_RETURN_GALLERY_INDEX'		=> 'Click %shere%s to return to the gallery index',
	'CLICK_RETURN_MODCP'				=> 'Click %shere%s to return to the moderator control panel',
	'CLICK_RETURN_PERSONAL_ALBUM'		=> 'Click %shere%s to return to the personal album',
	'CLICK_VIEW_COMMENT'				=> 'Click %shere%s to view your comment',
	'COMMENT'							=> 'Commment',
	'COMMENT_DELETE_CONFIRM'			=> 'Are you sure to delete this comment?',
	'COMMENT_NO_TEXT'					=> 'Please enter your comment',
	'COMMENT_STORED'					=> 'Your comment has been entered successfully.',
	'COMMENT_TOO_LONG'					=> 'Your comment is too long',
	'COMMENTS'							=> 'Comments',
	'CURRENT_RATING'					=> 'Current Rating',

	'DELETE_IMAGE'					=> 'Delete',
	'DESC_TOO_LONG'					=> 'Your description is too long',
	'DETAILS'						=> 'Details',

	'EDIT_IMAGE'					=> 'Edit',
	'EDIT_IMAGE_INFO'				=> 'Edit image information',
	'EDITED_TIME_TOTAL'				=> 'Last edited by %s on %s; edited %d time in total',
	'EDITED_TIMES_TOTAL'			=> 'Last edited by %s on %s; edited %d times in total',

	'FILE'									=> 'File',
	'FILETYPE_AND_THUMBNAIL_DO_NOT_MATCH'	=> 'Your image and your thumbnail must be the same type',

	'IMAGE_DESC'					=> 'Image Description',
	'IMAGE_LOCKED'					=> 'Sorry, this image is locked. You cannot post comments for this image anymore.',
	'IMAGE_NOT_EXIST'				=> 'This image does not exist',
	'IMAGE_TITLE'					=> 'Image Title',
	'IMAGES'						=> 'Images',
	'IMAGES_APPROVED_SUCCESSFULLY'		=> 'Your image(s) have been approved successfully',
	'IMAGES_DELETED_SUCCESSFULLY'		=> 'These image(s) have been deleted successfully',
	'IMAGES_LOCKED_SUCCESSFULLY'		=> 'Your image(s) have been locked successfully',
	'IMAGES_MOVED_SUCCESSFULLY'			=> 'Your image(s) have been moved successfully',
	'IMAGES_UNAPPROVED_SUCCESSFULLY'	=> 'Your image(s) have been unapproved successfully',
	'IMAGES_UNLOCKED_SUCCESSFULLY'		=> 'Your image(s) have been unlocked successfully',
	'IMAGES_UPDATED_SUCCESSFULLY'		=> 'Your image information has been updated successfully',

	'LAST_IMAGE'					=> 'Last Image',
	'LOCK'							=> 'Lock',
	'LOCKED'						=> 'Locked',
	'LOGIN_EXPLAIN_PERSONAL_GALLERY'		=> 'You must registered and logged in to view the personal gallery.',
	'LOGIN_TO_COMMENT'				=> 'Login to post a comment',
	'LOGIN_TO_RATE'					=> 'Login to rate this image',

	'MAX_FILE_SIZE'					=> 'Maximum file size (bytes)',
	'MAX_HEIGHT'					=> 'Maximum image height (pixels)',
	'MAX_LENGTH'					=> 'Max length (bytes)',
	'MAX_WIDTH'						=> 'Maximum image width (pixels)',
	'MISSING_IMAGE_TITLE'			=> 'You must enter a title for your image',
	'MODCP'							=> 'Moderator Control Panel',
	'MOVE_TO_ALBUM'					=> 'Move to album',

	'NEW_COMMENT'					=> 'New Comment',
	'NO_COMMENTS'					=> 'No comments yet',
	'NO_IMAGE_SPECIFIED'			=> 'No image specified',
	'NO_IMAGES'						=> 'No images',
	'NONE'							=> 'None',
	'NOT_ALLOWED_FILE_TYPE'					=> 'Thias file type is not allowed',
	'NOT_ALLOWED_TO_CREATE_PERSONAL_ALBUM'	=> 'Sorry, the administrators of this board do not allow you to create a personal album.',
	'NOT_APPROVED'					=> 'not approved',
	'NOT_RATED'						=> 'not rated',

	'ORDER'							=> 'Order',

	'PERSONAL_ALBUM_EXPLAIN'			=> 'You can view the personal albums of other users by clicking on the link in their profiles.',
	'PERSONAL_ALBUM_NOT_CREATED'		=> 'The personal gallery of %s is empty or has not been created.',
	'PERSONAL_ALBUM_OF_USER'			=> 'Personal album of %s',
	'PERSONAL_ALBUMS'					=> 'Personal albums',
	'PLAIN_TEXT_ONLY'					=> 'Plain text only',
	'POST_COMMENT'						=> 'Post a comment',
	'POSTER'							=> 'Poster',

	'RATING'						=> 'Rating',
	'RECENT_PUBLIC_IMAGES'			=> 'Recent Public Images',
	'RATING_SUCCESSFUL'				=> 'Your image has been rated successfully.',

	'SELECT_SORT_METHOD'		=> 'Select sort method',
	'SORT'						=> 'Sort',
	'SORT_ASCENDING'			=> 'Ascending',
	'SORT_DESCENDING'			=> 'Descending',
	'STATUS'					=> 'Status',

	'THUMBNAIL_SIZE'			=> 'Thumbnail size (pixels)',

	'UNAPPROVE'							=> 'Unapprove',
	'UNLOCK'							=> 'Unlock',
	'UPLOAD_IMAGE'						=> 'Upload Image',
	'UPLOAD_IMAGE_SIZE_TOO_BIG'			=> 'Your image dimension size is too large',
	'UPLOAD_NO_FILE'					=> 'You must enter your path and filename',
	'UPLOAD_NO_TITLE'					=> 'You must enter a title for your image',
	'UPLOAD_THUMBNAIL'					=> 'Upload a thumbnail image',
	'UPLOAD_THUMBNAIL_EXPLAIN'			=> 'It must be of the same filetype as your image',
	'UPLOAD_THUMBNAIL_FROM_MACHINE'		=> 'Upload its thumbnail from your machine (must be the same filetype as your image)',
	'UPLOAD_THUMBNAIL_SIZE_TOO_BIG'		=> 'Your thumbnail dimension size is too large',
	'UPLOAD_TO_ALBUM'					=> 'Upload to album',
	'USER_REACHED_QUOTA'				=> 'You have reached your quota of imagess. You cannot upload any more. Please contact the administrator for more information.',
	'USERS_PERSONAL_ALBUMS'				=> 'Users Personal Albums',

	'VIEW_ALBUM'					=> 'View album',
	'VIEW_IMAGE'					=> 'View image',
	'VIEW_THE_LATEST_IMAGE'			=> 'View the latest image',
	'VIEWS'							=> 'Views',

	'WAITING_FOR_APPROVAL'			=> 'image(s) waiting for approval',

	'YOUR_COMMENT'					=> 'Your comment',
	'YOUR_PERSONAL_ALBUM'			=> 'Your Personal Album',
	'YOUR_RATING'					=> 'Your rating'
));

?>