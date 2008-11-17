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
$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');
$user->add_lang('mods/gallery_mcp');

include_once("{$phpbb_root_path}{$gallery_root_path}includes/common.$phpEx");
include_once("{$phpbb_root_path}{$gallery_root_path}includes/permissions.$phpEx");
$album_access_array = get_album_access_array();

$image_id = request_var('image_id', 0);
$album_id = request_var('album_id', 0);
include_once("{$phpbb_root_path}{$gallery_root_path}mcp/mcp_functions.$phpEx");
$mode = request_var('mode', 'album');
$action = request_var('action', '');

if ($mode == 'whois' && $auth->acl_get('a_') && request_var('ip', ''))
{
	include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

	$template->assign_var('WHOIS', user_ipwhois(request_var('ip', '')));

	// Output the page
	page_header($user->lang['WHO_IS_ONLINE']);

	$template->set_filenames(array(
		'body' => 'viewonline_whois.html')
	);

	page_footer();
}

//Basic-Information && Permissions
if($image_id)
{
	$sql = 'SELECT *
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_id = ' . $image_id;
	$result = $db->sql_query($sql);
	$image_data = $db->sql_fetchrow($result);
	if (empty($image_data))
	{
		trigger_error('IMAGE_NOT_EXIST');
	}
	$user_id = $image_data['image_user_id'];
	//deny-cheating
	$album_id = $image_data['image_album_id'];
}

/**
* Check for all the requested permissions
*/
if (!gallery_acl_check('m_', $album_id))
{
	meta_refresh(5, append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id"));
	trigger_error('NOT_AUTHORISED');
}
if ((substr($mode, 0, 7) == 'report_') && !gallery_acl_check('m_report', $album_id))
{
	meta_refresh(5, append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id"));
	trigger_error('NOT_AUTHORISED');
}
if ((substr($mode, 0, 6) == 'queue_') && !gallery_acl_check('m_status', $album_id))
{
	meta_refresh(5, append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id"));
	trigger_error('NOT_AUTHORISED');
}
if (($action == 'images_move') && !gallery_acl_check('m_move', $album_id))
{
	meta_refresh(5, append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id"));
	trigger_error('NOT_AUTHORISED');
}

$sql = 'SELECT *
	FROM ' . GALLERY_ALBUMS_TABLE . '
	WHERE album_id = ' . $album_id;
$result = $db->sql_query($sql);
$album_data = $db->sql_fetchrow($result);
if (empty($album_data))
{
	trigger_error('ALBUM_NOT_EXIST');
}

generate_album_nav($album_data);
$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> $user->lang['MCP'],
	'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'album_id=' . $album_data['album_id']),
));

$template->assign_vars(array(
	'EDIT_IMG'		=> $user->img('icon_post_edit', 'EDIT_IMAGE'),
	'DELETE_IMG'	=> $user->img('icon_post_delete', 'DELETE_IMAGE'),
	'ALBUM_NAME'	=> $album_data['album_name'],
	'ALBUM_IMAGES'	=> $album_data['album_images'] . ' ' . (($album_data['album_images'] == 1) ? $user->lang['IMAGE'] : $user->lang['IMAGES']),
	'U_VIEW_ALBUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $album_id),
	'U_MOD_ALBUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'mode=album&amp;album_id=' . $album_id),
));

//some other basic-variables
$option_id = request_var('option_id', 0);
$submit = (isset($_POST['submit'])) ? true : false;
$action = request_var('action', '');
$redirect = request_var('redirect', $mode);
$moving_target = request_var('moving_target', 0);
$image_id = request_var('image_id', request_var('option_id', 0));
$image_id_ary = ($image_id) ? array($image_id) : request_var('image_id_ary', array(0));

//build navigation
build_gallery_mcp_navigation($album_id, $mode, $option_id);

if ($action && $image_id_ary)
{
	//add images to the counters
	//sub images from the counters

	$s_hidden_fields = build_hidden_fields(array(
		'mode'				=> $mode,
		'album_id'			=> $album_id,
		'image_id_ary'		=> $image_id_ary,
		'action'			=> $action,
		'redirect'			=> $redirect,
	));
	$multiple = '';
	if (isset($image_id_ary[1]))
	{
		$multiple = 'S';
	}
	// group by user-id and reduce on later call
	switch ($action)
	{
		case 'images_move':
			if ($moving_target)
			{
				$sql_ary = array('image_album_id' => $moving_target,);
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);
				$sql_ary = array('report_album_id' => $moving_target,);
				$sql = 'UPDATE ' . GALLERY_REPORTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $db->sql_in_set('report_image_id', $image_id_ary);
				$db->sql_query($sql);
				$success = true;
			}
			else
			{
				$category_select = gallery_albumbox(false, 'moving_target', $album_id, 'i_upload', $album_id);
				$template->assign_vars(array(
					'S_MOVING_IMAGES'	=> true,
					'S_ALBUM_SELECT'	=> $category_select,
					'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
				));
			}

		break;
		case 'images_unapprove':
			if (confirm_box(true))
			{
				handle_image_counter($image_id_ary, false);
				$sql_ary = array(
					'image_status'			=> 0,
				);
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);
				$success = true;
			}
			else
			{
				confirm_box(false, 'QUEUE' . $multiple . '_A_UNAPPROVE2', $s_hidden_fields);
			}
		break;
		case 'images_approve':
			if (confirm_box(true))
			{
				handle_image_counter($image_id_ary, true, true);
				$sql_ary = array(
					'image_status'			=> 1,
				);
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);
				$success = true;
			}
			else
			{
				confirm_box(false, 'QUEUE' . $multiple . '_A_APPROVE2', $s_hidden_fields);
			}
		break;
		case 'images_lock':
			if (confirm_box(true))
			{
				handle_image_counter($image_id_ary, false);
				$sql_ary = array(
					'image_status'			=> 2,
				);
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);
				$success = true;
			}
			else
			{
				confirm_box(false, 'QUEUE' . $multiple . '_A_LOCK2', $s_hidden_fields);
			}
		break;
		case 'images_delete':
			if (confirm_box(true))
			{
				handle_image_counter($image_id_ary, false);
				$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . ' WHERE ' . $db->sql_in_set('comment_image_id', $image_id_ary);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . ' WHERE ' . $db->sql_in_set('rate_image_id', $image_id_ary);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_REPORTS_TABLE . ' WHERE ' . $db->sql_in_set('report_image_id', $image_id_ary);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);
				$success = true;
			}
			else
			{
				confirm_box(false, 'QUEUE' . $multiple . '_A_DELETE2', $s_hidden_fields);
			}
		break;
		case 'reports_close':
			if (confirm_box(true))
			{
				$sql_ary = array(
					'report_manager'		=> $user->data['user_id'],
					'report_status'			=> 2,
				);
				$sql = 'UPDATE ' . GALLERY_REPORTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $db->sql_in_set('report_id', $image_id_ary);
				$db->sql_query($sql);
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET image_reported = 0
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);
				$success = true;
			}
			else
			{
				confirm_box(false, 'REPORT' . $multiple . '_A_CLOSE2', $s_hidden_fields);
			}
		break;
		case 'reports_open':
			if (confirm_box(true))
			{
				$sql_ary = array(
					'report_manager'		=> $user->data['user_id'],
					'report_status'			=> 1,
				);
				$sql = 'UPDATE ' . GALLERY_REPORTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $db->sql_in_set('report_id', $image_id_ary);
				$db->sql_query($sql);
				$sql = 'SELECT report_image_id, report_id
					FROM ' . GALLERY_REPORTS_TABLE . "
					WHERE report_status = 1
						AND " . $db->sql_in_set('report_id', $image_id_ary);;
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET image_reported = ' . $row['report_id'] . '
						WHERE ' . $db->sql_in_set('image_id', $row['report_image_id']);
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);
				$success = true;
			}
			else
			{
				confirm_box(false, 'REPORT' . $multiple . '_A_OPEN2', $s_hidden_fields);
			}
		break;
		case 'reports_delete':
			if (confirm_box(true))
			{
				$sql = 'DELETE FROM ' . GALLERY_REPORTS_TABLE . ' WHERE ' . $db->sql_in_set('report_id', $image_id_ary);
				$db->sql_query($sql);
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET image_reported = 0
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);
				$success = true;
			}
			else
			{
				confirm_box(false, 'REPORT' . $multiple . '_A_DELETE2', $s_hidden_fields);
			}
		break;
	}
	if (isset($success))
	{
		update_lastimage_info($album_id);
		if ($moving_target)
		{
			update_lastimage_info($moving_target);
		}
		redirect(($redirect == 'redirect') ? append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx" , "album_id=$album_id") : append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx" , "mode=$mode&amp;album_id=$album_id"));
	}

// <- if ($action && $image_id_ary)
}

$sort_by_sql = array('image_time', 'image_name', 'image_username', 'image_view_count', 'image_rate_avg', 'image_comments', 'image_last_comment');
switch ($mode)
{
	case 'album':
		include("{$phpbb_root_path}{$gallery_root_path}mcp/mcp_album.$phpEx");
	break;

	case 'report_open':
	case 'report_closed':
		include("{$phpbb_root_path}{$gallery_root_path}mcp/mcp_report.$phpEx");
	break;

	case 'queue_unapproved':
	case 'queue_approved':
	case 'queue_locked':
		include("{$phpbb_root_path}{$gallery_root_path}mcp/mcp_queue.$phpEx");
	break;

	break;

	case 'report_details':
	case 'queue_details':
		include("{$phpbb_root_path}{$gallery_root_path}mcp/mcp_details.$phpEx");
	break;
}


// Output page
$page_title = $user->lang['MCP'] . ' &bull; ' .  $user->lang['GALLERY'];

page_header($page_title);

$template->set_filenames(array(
	'body' => 'gallery_mcp_body.html')
);

page_footer();

?>