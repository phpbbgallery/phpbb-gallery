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

if(!$pic_id)
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
	FROM ' . GALLERY_IMAGES_TABLE . '
	WHERE pic_id = ' . $pic_id . '
	LIMIT 1';
$result = $db->sql_query($sql);

$thispic = $db->sql_fetchrow($result);

$album_id = $thispic['pic_cat_id'];
$user_id = $thispic['pic_user_id'];

$pic_filename = $thispic['pic_filename'];
$pic_thumbnail = $thispic['pic_thumbnail'];

if (empty($thispic))
{
	trigger_error($user->lang['IMAGE_NOT_EXIST'], E_USER_WARNING);
}


// ------------------------------------
// Get the current Category Info
// ------------------------------------

if ($album_id <> PERSONAL_GALLERY)
{
	$sql = 'SELECT *
		FROM ' . GALLERY_ALBUMS_TABLE . '
		WHERE album_id = ' . $album_id . '
		LIMIT 1';
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

$album_user_access = album_user_access($album_id, $thiscat, 0, 0, 0, 0, 0, 1);// DELETE

if (!$album_user_access['delete'])
{
	if ($user->data['is_bot'])
	{
		redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
	}
	if (!$user->data['is_registered'])
	{
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


$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> $user->lang['GALLERY'],
	'U_VIEW_FORUM'	=> append_sid("{$album_root_path}index.$phpEx"),
));

if ($album_id == PERSONAL_GALLERY)
{
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $user->lang['PERSONAL_ALBUMS'],
		'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album_personal_index.$phpEx"),
	));

	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> sprintf($user->lang['PERSONAL_ALBUM_OF_USER'], $thispic['pic_username']),
		'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album_personal.$phpEx", 'user_id=' . $user_id),
	));
}
else
{
	generate_album_nav($thiscat);
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
		if ($album_id)
		{
			redirect(append_sid("album.$phpEx?id=$album_id")); 
		} 
		else
		{
			redirect(append_sid("album_personal.$phpEx?user_id=$user_id")); 
		}
		exit;
	}
	
	$template->assign_vars(array(
		'MESSAGE_TITLE'		=> $user->lang['CONFIRM'],
		'MESSAGE_TEXT'		=> $user->lang['ALBUM_DELETE_CONFIRM'],
		'S_CONFIRM_ACTION'	=> append_sid("image_delete.$phpEx?id=$pic_id"),
	));

	// Output page
	$page_title = $user->lang['GALLERY'];
	page_header($page_title);
	$template->set_filenames(array(
		'body' => 'confirm_body.html',
	));
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
	$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . '
		WHERE comment_image_id = ' . $pic_id;
	$result = $db->sql_query($sql);


	// --------------------------------
	// Delete all ratings
	// --------------------------------
	$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . '
		WHERE rate_image_id = ' . $pic_id;
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
	$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . '
		WHERE pic_id = ' . $pic_id;
	$result = $db->sql_query($sql);


	// --------------------------------
	// Complete... now send a message to user
	// --------------------------------
	$message = $user->lang['IMAGES_DELETED_SUCCESSFULLY'];

	if ($album_id <> PERSONAL_GALLERY)
	{
		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid("album.$phpEx?id=$album_id") . '">',
		));
		$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("album.$phpEx?id=$album_id") . "\">", "</a>");
	}
	else
	{
		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid("album_personal.$phpEx?user_id=$user_id") . '">',
		));
		$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_PERSONAL_ALBUM'], "<a href=\"" . append_sid("album_personal.$phpEx?user_id=$user_id") . "\">", "</a>");
	}

	$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");
	trigger_error($message, E_USER_WARNING);
}
?>