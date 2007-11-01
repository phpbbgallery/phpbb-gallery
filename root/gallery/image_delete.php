<?php

/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$album_root_path = $phpbb_root_path . 'gallery/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');


//
// Get general album information
//
include($album_root_path . 'includes/common.'.$phpEx);


// ------------------------------------
// Check the request
// ------------------------------------

$pic_id = request_var('id', 0);

if( $pic_id == 0)
{
	trigger_error($user->lang['NO_IMAGE_SPECIFIED'], E_USER_WARNING);
}

// ------------------------------------
// Salting the form...yumyum ...
// ------------------------------------
add_form_key('image_delete');

// ------------------------------------
// Get this pic info
// ------------------------------------

$sql = 'SELECT *
		FROM ' . ALBUM_TABLE . '
		WHERE pic_id = ' . $pic_id;
$result = $db->sql_query($sql);

$thispic = $db->sql_fetchrow($result);

$cat_id = $thispic['pic_cat_id'];
$user_id = $thispic['pic_user_id'];

$pic_filename = $thispic['pic_filename'];
$pic_thumbnail = $thispic['pic_thumbnail'];

if(empty($thispic))
{
	trigger_error($user->lang['IMAGE_NOT_EXIST'], E_USER_WARNING);
}


// ------------------------------------
// Get the current Category Info
// ------------------------------------

if ($cat_id <> PERSONAL_GALLERY)
{
	$sql = 'SELECT *
			FROM ' . ALBUM_CAT_TABLE . '
			WHERE cat_id = ' . $cat_id;
	$result = $db->sql_query($sql);

	$thiscat = $db->sql_fetchrow($result);
}
else
{
	$thiscat = init_personal_gallery_cat($user_id);
}

if (empty($thiscat))
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}


// ------------------------------------
// Check the permissions
// ------------------------------------

$album_user_access = album_user_access($cat_id, $thiscat, 0, 0, 0, 0, 0, 1); // DELETE

if ($album_user_access['delete'] == 0)
{
	if (!$user->data['is_registered'])
	{
		if ($user->data['is_bot'])
		{
			redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
		}
		login_box("gallery/image_delete.$phpEx?id=$pic_id", $user->lang['LOGIN_INFO']);
	}
	else
	{
		trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
	}
}
else
{
	if ((!$album_user_access['moderator']) && ($user->data['user_type'] <> USER_FOUNDER))
	{
		if ($thispic['pic_user_id'] <> $user->data['user_id'])
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}
	}
}



/*
+----------------------------------------------------------
| Main work here...
+----------------------------------------------------------
*/

if(!isset($_POST['confirm']))
{
	// --------------------------------
	// If user give up deleting...
	// --------------------------------
	if(isset($_POST['cancel']))
	{
		if ($cat_id)
		{ 
        		redirect(append_sid("album.$phpEx?id=$cat_id")); 
		} 
		else
		{ 
			redirect(append_sid("album_personal.$phpEx?user_id=$user_id")); 
		} 
		exit;
	}
	
	$template->assign_vars(array(
		'MESSAGE_TITLE' 	=> $user->lang['CONFIRM'],

		'MESSAGE_TEXT' 		=> $user->lang['ALBUM_DELETE_CONFIRM'],

		'L_NO' 				=> $user->lang['NO'],
		'L_YES' 			=> $user->lang['YES'],

		'S_CONFIRM_ACTION' 	=> append_sid("image_delete.$phpEx?id=$pic_id"),
		)
	);
	
	// Output page
	$page_title = $user->lang['GALLERY'];
	
	page_header($page_title);
	
	$template->set_filenames(array(
		'body' => 'confirm_body.html')
	);
	
	page_footer();
	
}
else
{
	// Check the salt... yumyum
	if (!check_form_key('image_delete'))
	{
		trigger_error('FORM_INVALID');
	}
	
	// --------------------------------
	// It's confirmed. First delete all comments
	// --------------------------------
	$sql = 'DELETE FROM ' . ALBUM_COMMENT_TABLE . '
			WHERE comment_pic_id = ' . $pic_id;
	$result = $db->sql_query($sql);


	// --------------------------------
	// Delete all ratings
	// --------------------------------
	$sql = 'DELETE FROM ' . ALBUM_RATE_TABLE . '
			WHERE rate_pic_id = ' . $pic_id;
	$result = $db->sql_query($sql);


	// --------------------------------
	// Delete cached thumbnail
	// --------------------------------
	if (($thispic['pic_thumbnail'] <> '') && @file_exists(ALBUM_CACHE_PATH . $thispic['pic_thumbnail']))
	{
		@unlink(ALBUM_CACHE_PATH . $thispic['pic_thumbnail']);
	}


	// --------------------------------
	// Delete File
	// --------------------------------
	@unlink(ALBUM_UPLOAD_PATH . $thispic['pic_filename']);


	// --------------------------------
	// Delete DB entry
	// --------------------------------
	$sql = 'DELETE FROM ' . ALBUM_TABLE . '
			WHERE pic_id = ' . $pic_id;
	$result = $db->sql_query($sql);


	// --------------------------------
	// Complete... now send a message to user
	// --------------------------------
	$message = $user->lang['IMAGES_DELETED_SUCCESSFULLY'];

	if ($cat_id <> PERSONAL_GALLERY)
	{
		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid("album.$phpEx?id=$cat_id") . '">')
		);

		$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("album.$phpEx?id=$cat_id") . "\">", "</a>");
	}
	else
	{
		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid("album_personal.$phpEx?user_id=$user_id") . '">')
		);

		$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_PERSONAL_ALBUM'], "<a href=\"" . append_sid("album_personal.$phpEx?user_id=$user_id") . "\">", "</a>");
	}
	
	$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");
	
	trigger_error($message, E_USER_WARNING);
}


// +------------------------------------------------------+
// |  Powered by Photo Album 2.x.x (c) 2002-2003 Smartor  |
// +------------------------------------------------------+

?>