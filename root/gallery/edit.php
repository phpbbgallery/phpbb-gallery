<?php

//ignored file

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
include($album_root_path . 'includes/common.'.$phpEx);
include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');

// ------------------------------------
// Check the request
// ------------------------------------

$pic_id = request_var('pic_id', 0);

if(!$pic_id)
{
	trigger_error($user->lang['NO_IMAGE_SPECIFIED'], E_USER_WARNING);
}

$sql = 'SELECT *
	FROM ' . GALLERY_IMAGES_TABLE . '
	WHERE image_id = ' . $pic_id . '
	LIMIT 1';
$result = $db->sql_query($sql);

$thispic = $db->sql_fetchrow($result);

$album_id = $thispic['image_album_id'];
$user_id = $thispic['image_user_id'];

$pic_filename = $thispic['image_filename'];
$pic_thumbnail = $thispic['image_thumbnail'];

if( empty($thispic) )
{
	trigger_error($user->lang['IMAGE_NOT_EXIST'], E_USER_WARNING);
}


// ------------------------------------
// Get the current Category Info
// ------------------------------------
$sql = 'SELECT *
	FROM ' . GALLERY_ALBUMS_TABLE . '
	WHERE album_id = ' . $album_id . '
	LIMIT 1';
$result = $db->sql_query($sql);

$album_data = $db->sql_fetchrow($result);

if (empty($album_data))
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}


// ------------------------------------
// Check the permissions
// ------------------------------------

$album_user_access = (!$album_data['album_user_id']) ? album_user_access($album_id, $album_data, 0, 0, 0, 0, 1, 0) : personal_album_access($album_data['album_user_id']);// EDIT

if (!$album_user_access['edit'])
{
	// Only registered users can go beyond this point
	if ($user->data['is_bot'])
	{
		redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
	}
	if (!$user->data['is_registered'])
	{
		login_box("gallery/edit.$phpEx?pic_id=$pic_id", $user->lang['LOGIN_INFO']);
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
		if ($thispic['image_user_id'] <> $user->data['user_id'])
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}
	}
}

if ($album_id == PERSONAL_GALLERY)
{
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $user->lang['PERSONAL_ALBUMS'],
		'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album_personal_index.$phpEx"),
	));

	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $album_data['album_name'],
		'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album_personal.$phpEx", 'user_id=' . $user_id),
	));
}
else
{
	generate_album_nav($album_data);
}
/**
* Main work here...
*/

// Salting the form...yumyum ...
add_form_key('edit_gallery');


if(!isset($_POST['pic_title']))
{

	$message_parser				= new parse_message();
	$message_parser->message	= $thispic['image_desc'];
	$message_parser->decode_message($thispic['image_desc_uid']);

	$template->assign_vars(array(
		'U_IMAGE'			=> append_sid("image.$phpEx?pic_id=$pic_id"),
		'IMAGE_NAME'				=> $thispic['image_name'],
		'IMAGE_DESC'				=> $message_parser->message,
		'S_IMAGE_DESC_MAX_LENGTH'	=> $album_config['desc_length'],

		'S_ALBUM_ACTION'		=> append_sid("edit.$phpEx?pic_id=$pic_id"),
	));

	// Output page
	$page_title = $user->lang['GALLERY'];
	page_header($page_title);

	$template->set_filenames(array(
		'body' => 'gallery_edit_body.html',
	));

	page_footer();
}
else
{
	// Check the salt... yumyum
	if (!check_form_key('edit_gallery'))
	{
		trigger_error('FORM_INVALID');
	}

	// --------------------------------
	// Check posted info
	// --------------------------------

	$pic_title = request_var('pic_title', '', true);
	$pic_desc = utf8_substr(request_var('pic_desc', '', true), 0, $album_config['desc_length']);

	if(empty($pic_title))
	{
		trigger_error($user->lang['MISSING_IMAGE_TITLE'], E_USER_WARNING);
	}
	$message_parser 			= new parse_message();
	$message_parser->message 	= utf8_normalize_nfc($pic_desc);
	if($message_parser->message)
	{
		$message_parser->parse(true, true, true, true, false, true, true, true);
	}


	// --------------------------------
	// Update the DB
	// --------------------------------
	$sql_ary = array(
		'image_name'		=> $pic_title,
		'image_desc'						=> $message_parser->message,
		'image_desc_uid'			=> $message_parser->bbcode_uid,
		'image_desc_bitfield'		=> $message_parser->bbcode_bitfield,
	);

	$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
		SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
		WHERE image_id = ' . (int) $pic_id;
	$db->sql_query($sql);

	// --------------------------------
	// Complete... now send a message to user
	// --------------------------------

	$message = $user->lang['IMAGES_UPDATED_SUCCESSFULLY'];

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
	$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("album.$phpEx") . "\">", "</a>");
	trigger_error($message, E_USER_WARNING);
}
?>