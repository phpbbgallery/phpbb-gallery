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
	FROM ' . ALBUM_TABLE . '
	WHERE pic_id = ' . $pic_id . '
	LIMIT 1';
$result = $db->sql_query($sql);

$thispic = $db->sql_fetchrow($result);

$cat_id = $thispic['pic_cat_id'];
$user_id = $thispic['pic_user_id'];

$pic_filename = $thispic['pic_filename'];
$pic_thumbnail = $thispic['pic_thumbnail'];

if( empty($thispic) )
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
		WHERE cat_id = ' . $cat_id . '
		LIMIT 1';
	$result = $db->sql_query($sql);

	$thiscat = $db->sql_fetchrow($result);
}
else
{
	$thiscat = init_personal_gallery_cat($user_id);
}

if ( empty($thiscat) )
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}


// ------------------------------------
// Check the permissions
// ------------------------------------

$album_user_access = album_user_access($cat_id, $thiscat, 0, 0, 0, 0, 1, 0);// EDIT

if (!$album_user_access['edit'])
{
	// Only registered users can go beyond this point
	if (!$user->data['is_registered'])
	{
		login_box("gallery/edit.$phpEx?pic_id=$pic_id", $user->lang['LOGIN_INFO']);
	}
	else
	{
		if ($user->data['is_bot'])
		{
			redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
		}
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

if ($cat_id == PERSONAL_GALLERY)
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
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $thiscat['cat_title'],
		'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album.$phpEx", 'id=' . $thiscat['cat_id']),
	));

	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $user->lang['MODCP'],
		'U_VIEW_FORUM'	=> append_sid("{$album_root_path}mcp.$phpEx", 'cat_id=' . $thiscat['cat_id']),
	));
}
/*
+----------------------------------------------------------
| Main work here...
+----------------------------------------------------------
*/

// Salting the form...yumyum ...
add_form_key('edit_gallery');


if(!isset($_POST['pic_title']))
{

	$message_parser				= new parse_message();
	$message_parser->message	= $thispic['pic_desc'];
	$message_parser->decode_message($thispic['pic_desc_bbcode_uid']);

	$template->assign_vars(array(
		'CAT_TITLE'				=> $thiscat['cat_title'],
		'U_VIEW_CAT'			=> ($cat_id <> PERSONAL_GALLERY) ? append_sid("album.$phpEx?id=$cat_id") : append_sid("album_personal.$phpEx?user_id=$user_id"),

		'PIC_TITLE'				=> $thispic['pic_title'],
		'PIC_DESC'				=> $message_parser->message,
		'S_PIC_DESC_MAX_LENGTH'	=> $album_config['desc_length'],

		'S_ALBUM_ACTION'		=> append_sid("edit.$phpEx?pic_id=$pic_id"),
	));

/*
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'			=> $user->lang['GALLERY'],
		'U_VIEW_FORUM'			=> append_sid("{$album_root_path}index.$phpEx"),
	));

	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'			=> $thiscat['cat_title'],
		'U_VIEW_FORUM'			=> append_sid("{$album_root_path}album.$phpEx", 'id=' . $thiscat['cat_id']),
	));
*/

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
		'pic_title'		=> $pic_title,
		'pic_desc'						=> $message_parser->message,
		'pic_desc_bbcode_uid'			=> $message_parser->bbcode_uid,
		'pic_desc_bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
	);

	$sql = 'UPDATE ' . ALBUM_TABLE . ' 
		SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
		WHERE pic_id = ' . (int) $pic_id;
	$db->sql_query($sql);

	// --------------------------------
	// Complete... now send a message to user
	// --------------------------------

	$message = $user->lang['IMAGES_UPDATED_SUCCESSFULLY'];

	if ($cat_id <> PERSONAL_GALLERY)
	{
		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid("album.$phpEx?id=$cat_id") . '">',
		));
		$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("album.$phpEx?id=$cat_id") . "\">", "</a>");
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