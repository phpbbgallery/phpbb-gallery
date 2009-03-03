<?php
/**
*
* gallery [English]
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
	'ALBUM'							=> 'Album',
	'ALBUM_IS_CATEGORY'				=> 'The album you cheated to, is a category-album.<br />You can\'t upload to categories.',
	'ALBUM_LOCKED'					=> 'Locked',
	'ALBUM_NAME'					=> 'Album name',
	'ALBUM_NOT_EXIST'				=> 'This album does not exist',
	'ALBUM_PERMISSIONS'				=> 'Album Permissions',
	'ALBUM_REACHED_QUOTA'			=> 'This album has reached the quota of images. You cannot upload any more. Please contact the administrator for more information.',
	'ALBUM_UPLOAD_NEED_APPROVAL'	=> 'Your image has been uploaded successfully.<br /><br />But the feature Image Approval has been enabled so your image must be approved by a administrator or a moderator before posting',
	'ALBUM_UPLOAD_SUCCESSFUL'		=> 'Your image has been uploaded successfully',
	'ALBUMS_MARKED'					=> 'All albums have been marked read.',
	'ALL_IMAGES'					=> 'All image',
	'ALLOWED_FILETYPES'				=> 'Allowed filetypes',
	'APPROVE'						=> 'Approve',
	'APPROVE_IMAGE'					=> 'Approve image',

	//@todo
	'ALBUM_COMMENT_CAN'			=> 'You <strong>can</strong> post comments to images in this album',
	'ALBUM_COMMENT_CANNOT'		=> 'You <strong>cannot</strong> post comments to images in this album',
	'ALBUM_DELETE_CAN'			=> 'You <strong>can</strong> delete your images in this album',
	'ALBUM_DELETE_CANNOT'		=> 'You <strong>cannot</strong> delete your images in this album',
	'ALBUM_EDIT_CAN'			=> 'You <strong>can</strong> edit your images in this album',
	'ALBUM_EDIT_CANNOT'			=> 'You <strong>cannot</strong> edit your images in this album',
	'ALBUM_RATE_CAN'			=> 'You <strong>can</strong> rate images in this album',
	'ALBUM_RATE_CANNOT'			=> 'You <strong>cannot</strong> rate images in this album',
	'ALBUM_UPLOAD_CAN'			=> 'You <strong>can</strong> upload new images in this album',
	'ALBUM_UPLOAD_CANNOT'		=> 'You <strong>cannot</strong> upload new images in this album',
	'ALBUM_VIEW_CAN'			=> 'You <strong>can</strong> view images in this album',
	'ALBUM_VIEW_CANNOT'			=> 'You <strong>cannot</strong> view images in this album',

	'BAD_UPLOAD_FILE_SIZE'			=> 'Your uploaded file is too large',
	'BROWSING_ALBUM'				=> 'Users browsing this album: %1$s',
	'BROWSING_ALBUM_GUEST'			=> 'Users browsing this album: %1$s and %2$d guest',
	'BROWSING_ALBUM_GUESTS'			=> 'Users browsing this album: %1$s and %2$d guests',

	'CHANGE_IMAGE_STATUS'			=> 'Change image-status',
	'CLICK_RETURN_ALBUM'			=> 'Click %shere%s to return to the album',
	'CLICK_RETURN_IMAGE'			=> 'Click %shere%s to return to the image',
	'COMMENT'						=> 'Comment',
	'COMMENT_IMAGE'					=> 'Posting a comment on an image in album %s',
	'COMMENT_LENGTH'				=> 'Enter your comment here, it may contain no more than <strong>%d</strong> characters.',
	'COMMENT_ON'					=> 'Comment on',
	'COMMENT_STORED'				=> 'Your comment has been saved successfully.',
	'COMMENT_TOO_LONG'				=> 'Your comment is too long.',
	'COMMENTS'						=> 'Comments',
	'CONTEST_COMMENTS_STARTS'		=> 'Comments on images in this contest are allowed from %s on.',
	'CONTEST_RATING_STARTED'		=> 'Rating for this contest started on %s.',
	'CONTEST_RATING_STARTS'			=> 'Rating for this contest starts on %s.',
	'CONTEST_RATING_ENDED'			=> 'Rating for this contest ended on %s.',
	'CONTEST_RESULT'				=> 'Contest',
	'CONTEST_RESULT_1'				=> 'Winner',
	'CONTEST_RESULT_2'				=> 'Second',
	'CONTEST_RESULT_3'				=> 'Third',
	'CONTEST_ENDED'					=> 'This contest ended on %s.',
	'CONTEST_ENDS'					=> 'This contest ends on %s.',
	'CONTEST_USERNAME'				=> '<strong>Contest</strong>',
	'CONTEST_USERNAME_LONG'			=> '<strong>Contest</strong> » The username is hidden, till the end of the contest on %s.',

	'DELETE_COMMENT'				=> 'Delete comment',
	'DELETE_COMMENT2'				=> 'Delete comment?',
	'DELETE_COMMENT2_CONFIRM'		=> 'Are you sure you want to delete the comment?',
	'DELETE_IMAGE'					=> 'Delete',
	'DELETE_IMAGE2'					=> 'Delete image?',
	'DELETE_IMAGE2_CONFIRM'			=> 'Are you sure you want to delete the image?',
	'DELETED_COMMENT'				=> 'Comment deleted',
	'DELETED_COMMENT_NOT'			=> 'Comment not deleted',
	'DELETED_IMAGE'					=> 'Image deleted',
	'DELETED_IMAGE_NOT'				=> 'Image not deleted',
	'DESC_TOO_LONG'					=> 'Your description is too long',
	'DESCRIPTION_LENGTH'			=> 'Enter your descriptions here, it may contain no more than <strong>%d</strong> characters.',
	'DETAILS'						=> 'Details',
	'DONT_RATE_IMAGE'				=> 'Don\'t rate image',

	'EDIT_COMMENT'					=> 'Edit comment',
	'EDIT_IMAGE'					=> 'Edit',
	'EDITED_TIME_TOTAL'				=> 'Last edited by %s on %s; edited %d time in total',
	'EDITED_TIMES_TOTAL'			=> 'Last edited by %s on %s; edited %d times in total',

	'FAVORITE_IMAGE'				=> 'Add to favorites',
	'FAVORITED_IMAGE'				=> 'The image was added to your favorites.',
	'FILE'							=> 'File',
	'FILETYPES_GIF'					=> 'gif',
	'FILETYPES_JPG'					=> 'jpg',
	'FILETYPES_PNG'					=> 'png',

	'IMAGE'								=> 'Image',
	'IMAGE_#'							=> '1 image',
	'IMAGE_ALREADY_REPORTED'			=> 'The image was already reported.',
	'IMAGE_BBCODE'						=> 'Image BBCode',
	'IMAGE_DAY'							=> '%.2f images per day',
	'IMAGE_DESC'						=> 'Image Description',
	'IMAGE_LOCKED'						=> 'Sorry, this image is locked. You cannot post comments for this image anymore.',
	'IMAGE_NAME'						=> 'Imagename',
	'IMAGE_NOT_EXIST'					=> 'This image does not exist.',
	'IMAGE_PCT'							=> '%.2f%% of all images',
	'IMAGE_STATUS'						=> 'Status',
	'IMAGE_URL'							=> 'Image-URL',
	'IMAGES'							=> 'Images',
	'IMAGES_#'							=> '%s images',
	'IMAGES_REPORTED_SUCCESSFULLY'		=> 'The image was successful reported',
	'IMAGES_UPDATED_SUCCESSFULLY'		=> 'Your image information has been updated successfully',
	'INVALID_USERNAME'					=> 'Your Username is invalid',

	'LAST_COMMENT'					=> 'Last Comment',
	'LAST_IMAGE'					=> 'Last Image',
	'LOGIN_EXPLAIN_UPLOAD'			=> 'You must registered and logged in to upload images into this gallery.',
	'LOOP_EXP'						=> 'If you upload more than one file, you may include <span style="font-weight: bold;">{NUM}</span> into the imagename and imagedescription.<br />
										It counts through the images, starting on the value you entered. Example: "Image {NUM}" addes up to "Image 1", "Image 2", etc.',

	'MARK_ALBUMS_READ'				=> 'Mark albums read',
	'MAX_FILE_SIZE'					=> 'Maximum file size (bytes)',
	'MAX_HEIGHT'					=> 'Maximum image height (pixels)',
	'MAX_WIDTH'						=> 'Maximum image width (pixels)',
	'MISSING_COMMENT'				=> 'No Message entered',
	'MISSING_MODE'					=> 'No mode selected',
	'MISSING_REPORT_REASON'			=> 'You need to mention a reason, to report an image.',
	'MISSING_SUBMODE'				=> 'No submode selected',
	'MISSING_USERNAME'				=> 'No Username entered',
	'MOVE_TO_ALBUM'					=> 'Move to album',
	'MOVE_TO_PERSONAL'				=> 'Move to personal album',
	'MOVE_TO_PERSONAL_MOD'			=> 'When you set this option to "yes", the image is moved into the personal album of the user. If the user does not have one yet, it is created automatically.',
	'MOVE_TO_PERSONAL_EXPLAIN'		=> 'When you set this option to "yes", the image is moved into your personal album. If you do not have one yet, it is created automatically.',

	'NEW_COMMENT'					=> 'New Comment',
	'NEW_IMAGES'					=> 'New images',
	'NO_ALBUMS'						=> 'There are no albums in this gallery.',
	'NO_COMMENTS'					=> 'No comments yet',
	'NO_IMAGES'						=> 'No images',
	'NO_IMAGES_FOUND'				=> 'No images found.',
	'NO_NEW_IMAGES'					=> 'No new images',
	'NO_IMAGES_LONG'				=> 'There are no images in this album.',
	'NOT_ALLOWED_FILE_TYPE'			=> 'This file type is not allowed',
	'NOT_RATED'						=> 'not rated',

	'ORDER'							=> 'Order',
	'ORIG_FILENAME'					=> 'Take filename as imagename (the insert-field has no function)',
	'OUT_OF_RANGE_VALUE'			=> 'Value is out of range',

	'PERSONAL_ALBUMS'				=> 'Personal albums',
	'POST_COMMENT'					=> 'Post a comment',
	'POST_COMMENT_RATE_IMAGE'		=> 'Post a comment and rate the image',
	'POSTER'						=> 'Poster',

	'RANDOM_IMAGES'					=> 'Random images',
	'RATE_IMAGE'					=> 'Rate the image',
	'RATE_STRING'					=> '%1$s (%2$s Rate)', // 1.Rating-average 2.number of rates
	'RATES_COUNT'					=> 'Rates',
	'RATES_STRING'					=> '%1$s (%2$s Rates)',
	'RATING'						=> 'Rating',
	'RATING_SUCCESSFUL'				=> 'The image has been rated successfully.',
	'READ_REPORT'					=> 'Read report message',
	'RECENT_COMMENTS'				=> 'Recent comments',
	'RECENT_IMAGES'					=> 'Recent Images',
	'REPORT_IMAGE'					=> 'Report image',
	'RETURN_ALBUM'					=> '%sReturn to the album last visited%s',

	'SEARCH_ALBUMS'					=> 'Search in albums',
	'SEARCH_ALBUMS_EXPLAIN'			=> 'Select the album or albums you wish to search in. Subalbums are searched automatically if you do not disable “search subalbums“ below.',
	'SEARCH_COMMENTS'				=> 'Comments only',
	'SEARCH_CONTEST'				=> 'Contest winners',
	'SEARCH_IMAGE_COMMENTS'			=> 'Imagenames, descriptions and comments',
	'SEARCH_IMAGE_VALUES'			=> 'Imagenames and descriptions only',
	'SEARCH_IMAGENAME'				=> 'Imagenames only',
	'SEARCH_RANDOM'					=> 'Random images',
	'SEARCH_RECENT'					=> 'Recent images',
	'SEARCH_RECENT_COMMENTS'		=> 'Recent comments',
	'SEARCH_SUBALBUMS'				=> 'Search subalbums',
	'SEARCH_TOPRATED'				=> 'Top rated images',
	'SEARCH_USER_IMAGES'			=> 'Search user’s images',
	'SEARCH_USER_IMAGES_OF'			=> 'Images of %s',
	'SHOW_PERSONAL_ALBUM_OF'		=> 'Show personal album of %s',
	'SLIDE_SHOW'					=> 'Slideshow',
	'SLIDE_SHOW_HIGHSLIDE'			=> 'To start the slideshow, click on one of the image-names and than click on the "play"-icon:',
	'SLIDE_SHOW_START'				=> 'To start the slideshow, click on one of the image-names:',
	'SORT_ASCENDING'				=> 'Ascending',
	'SORT_DESCENDING'				=> 'Descending',
	'STATUS'						=> 'Status',
	'SUBALBUMS'						=> 'Subalbums',
	'SUBALBUM'						=> 'Subalbum',

	'THUMBNAIL_SIZE'				=> 'Thumbnail size (pixels)',
	'TOTAL_IMAGES'					=> 'Total images',

	'UNFAVORITE_IMAGE'				=> 'remove from favorites',
	'UNFAVORITED_IMAGE'				=> 'The image was removed from your favorites.',
	'UNFAVORITED_IMAGES'			=> 'The images were removed from your favorites.',
	'UNLOCK_IMAGE'					=> 'Unlock image',
	'UNWATCH_ALBUM'					=> 'Unsubscribe album',
	'UNWATCH_IMAGE'					=> 'Unsubscribe image',
	'UNWATCHED_ALBUM'				=> 'You are no longer informed about new images in this album.',
	'UNWATCHED_ALBUMS'				=> 'You are no longer informed about new images in these albums.',
	'UNWATCHED_IMAGE'				=> 'You are no longer informed about new comments on this image.',
	'UNWATCHED_IMAGES'				=> 'You are no longer informed about new comments on these images.',
	'UPLOAD_IMAGE'					=> 'Upload Image',
	'UPLOAD_IMAGE_SIZE_TOO_BIG'		=> 'Your image dimension size is too large',
	'UPLOAD_NO_FILE'				=> 'You must enter your path and filename',//@todo
	'UPLOADED_BY_USER'				=> 'Uploaded by',
	'UPLOADED_ON_DATE'				=> 'Uploaded',
	'USER_NEARLY_REACHED_QUOTA'		=> 'You are not allowed to upload more than %s images, but you already uploaded %s images. So there are only %s filelines displayed.',
	'USER_REACHED_QUOTA'			=> 'You are not allowed to upload more than %s images.<br /><br />Please contact the administrator for more information.',
	'USERS_PERSONAL_ALBUMS'			=> 'Users Personal Albums',

	'VIEW_ALBUM'					=> 'View album',
	'VIEW_ALBUM_IMAGE'				=> '1 image',
	'VIEW_ALBUM_IMAGES'				=> '%s images',
	'VIEW_IMAGE'					=> 'View image',
	'VIEW_LATEST_IMAGE'				=> 'View the latest image',
	'VIEW_SEARCH_RECENT'			=> 'View recent images',
	'VIEW_SEARCH_RANDOM'			=> 'View random images',
	'VIEW_SEARCH_COMMENTED'			=> 'View recent comments',
	'VIEW_SEARCH_CONTESTS'			=> 'View contest winners',
	'VIEW_SEARCH_TOPRATED'			=> 'View top rated images',
	'VIEW_SEARCH_SELF'				=> 'View your images',
	'VIEWING_ALBUM'					=> 'Viewing album %s',
	'VIEWING_IMAGE'					=> 'Viewing image in album %s',
	'VIEWS'							=> 'Views',

	'WATCH_ALBUM'					=> 'Subscribe album',
	'WATCH_IMAGE'					=> 'Subscribe image',
	'WATCHING_ALBUM'				=> 'You are now informed about new images in this album.',
	'WATCHING_IMAGE'				=> 'You are now informed about new comments on this image.',

	'YOUR_COMMENT'					=> 'Your comment',
	'YOUR_PERSONAL_ALBUM'			=> 'Your Personal Album',
	'YOUR_RATING'					=> 'Your rating',
));

?>