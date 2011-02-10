<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/

define('IN_PHPBB', true);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include('common.' . $phpEx);
include($phpbb_root_path . 'common.' . $phpEx);

phpbb_gallery::setup(array('mods/gallery', 'mods/gallery_mcp'));
phpbb_gallery_url::_include(array('functions_display'), 'phpbb');

$mode = request_var('mode', 'album');
$action = request_var('action', '');
$option_id = request_var('option_id', 0);
$image_id = request_var('image_id', 0);
$album_id = request_var('album_id', 0);

if ((request_var('quickmod', 0) == 1) && ($action == 'report_details'))
{
	$mode = 'report_details';
	$option_id = (int) $image_data['image_reported'];
}
else if ((request_var('quickmod', 0) == 1) && ($action == 'image_edit'))
{
	phpbb_gallery_url::redirect('posting', "mode=image&amp;submode=edit&amp;album_id=$album_id&amp;image_id=$image_id");
}

if ($mode == 'whois' && $auth->acl_get('a_') && request_var('ip', ''))
{
	phpbb_gallery_url::_include(array('functions_user'), 'phpbb');

	$template->assign_var('WHOIS', user_ipwhois(request_var('ip', '')));

	page_header($user->lang['WHO_IS_ONLINE']);

	$template->set_filenames(array(
		'body' => 'viewonline_whois.html')
	);

	page_footer();
}

//Basic-Information && Permissions
if ($image_id)
{
	$image_data = phpbb_gallery_image::get_info($image_id);
	$album_id = $image_data['image_album_id'];
	$user_id = $image_data['image_user_id'];
}
if ($album_id)
{
	$album_data = phpbb_gallery_album::get_info($album_id);
}

// Some other variables
$submit = (isset($_POST['submit'])) ? true : false;
$redirect = request_var('redirect', $mode);
$moving_target = request_var('moving_target', 0);
$image_id = ($image_id && !$option_id) ? $image_id : $option_id;
$image_id_ary = ($image_id) ? array($image_id) : request_var('image_id_ary', array(0));

/**
* Check for all the requested permissions
*/
$access_denied = false;
switch ($mode)
{
	case 'report_open':
	case 'report_closed':
	case 'report_details':
		if ($album_id)
		{
			$access_denied = (!phpbb_gallery::$auth->acl_check('m_report', $album_id, $album_data['album_user_id'])) ? true : false;
		}
		else
		{
			$access_denied = (!sizeof(phpbb_gallery::$auth->acl_album_ids('m_report'))) ? true : false;
		}
	break;
	case 'queue_unapproved':
	case 'queue_approved':
	case 'queue_locked':
	case 'queue_details':
		if ($album_id)
		{
			$access_denied = (!phpbb_gallery::$auth->acl_check('m_status', $album_id, $album_data['album_user_id'])) ? true : false;
		}
		else
		{
			$access_denied = (!sizeof(phpbb_gallery::$auth->acl_album_ids('m_status'))) ? true : false;
		}
	break;
}
switch ($action)
{
	case 'images_move':
		$access_denied = (!phpbb_gallery::$auth->acl_check('m_move', $album_id, $album_data['album_user_id']) || ($moving_target && !phpbb_gallery::$auth->acl_check('i_upload', $moving_target))) ? true : false;
	break;
	case 'images_unapprove':
	case 'images_approve':
	case 'images_lock':
		$access_denied = (!phpbb_gallery::$auth->acl_check('m_status', $album_id, $album_data['album_user_id'])) ? true : false;
	break;
	case 'images_delete':
		$access_denied = (!phpbb_gallery::$auth->acl_check('m_delete', $album_id, $album_data['album_user_id'])) ? true : false;
	break;
	case 'reports_close':
	case 'reports_open':
	case 'reports_delete':
		$access_denied = (!phpbb_gallery::$auth->acl_check('m_report', $album_id, $album_data['album_user_id'])) ? true : false;
	break;
}

if ($access_denied || (($album_id && !phpbb_gallery::$auth->acl_check('m_', $album_id, $album_data['album_user_id'])) || (!$album_id && !sizeof(phpbb_gallery::$auth->acl_album_ids('m_')))))
{
	if (!$album_id)
	{
		meta_refresh(5, phpbb_gallery_url::append_sid('index'));
	}
	else
	{
		meta_refresh(5, phpbb_gallery_url::append_sid('album', "album_id=$album_id"));
	}
	trigger_error('NOT_AUTHORISED');
}

// Build Navigation
$page_title = phpbb_gallery_mcp::build_navigation($album_id, $mode, $option_id);

if (!$album_id)
{
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $user->lang['MCP'],
		'U_VIEW_FORUM'	=> phpbb_gallery_url::append_sid('mcp'),
	));

	page_header($user->lang['GALLERY'] . ' &bull; ' . $user->lang['MCP'] . ' &bull; ' . $page_title, false);

	$template->set_filenames(array(
		'body' => 'gallery/mcp_body.html')
	);

	page_footer();
}

phpbb_gallery_album::generate_nav($album_data);
$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> $user->lang['MCP'],
	'U_VIEW_FORUM'	=> phpbb_gallery_url::append_sid('mcp', 'album_id=' . $album_data['album_id']),
));

$template->assign_vars(array(
	'S_ALLOWED_MOVE'	=> (phpbb_gallery::$auth->acl_check('m_move', $album_id, $album_data['album_user_id'])) ? true : false,
	'S_ALLOWED_STATUS'	=> (phpbb_gallery::$auth->acl_check('m_status', $album_id, $album_data['album_user_id'])) ? true : false,
	'S_ALLOWED_DELETE'	=> (phpbb_gallery::$auth->acl_check('m_delete', $album_id, $album_data['album_user_id'])) ? true : false,
	'S_ALLOWED_REPORT'	=> (phpbb_gallery::$auth->acl_check('m_report', $album_id, $album_data['album_user_id'])) ? true : false,
	'EDIT_IMG'		=> $user->img('icon_post_edit', 'EDIT_IMAGE'),
	'DELETE_IMG'	=> $user->img('icon_post_delete', 'DELETE_IMAGE'),
	'ALBUM_NAME'	=> $album_data['album_name'],
	'ALBUM_IMAGES'	=> $album_data['album_images'] . ' ' . (($album_data['album_images'] == 1) ? $user->lang['IMAGE'] : $user->lang['IMAGES']),
	'U_VIEW_ALBUM'	=> phpbb_gallery_url::append_sid('album', 'album_id=' . $album_id),
	'U_MOD_ALBUM'	=> phpbb_gallery_url::append_sid('mcp', 'mode=album&amp;album_id=' . $album_id),
));

if ($action && $image_id_ary)
{
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
		// We add an S to the lang string (IMAGE), when we have more than one image, so we get IMAGES
		$multiple = 'S';
	}
	switch ($action)
	{
		case 'images_move':
			if ($moving_target)
			{
				$target_data = get_album_info($moving_target);

				if ($target_data['contest_id'] && (time() < ($target_data['contest_start'] + $target_data['contest_end'])))
				{
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET image_album_id = ' . $moving_target . ',
							image_contest = ' . phpbb_gallery_image::IN_CONTEST . '
						WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
					$db->sql_query($sql);
				}
				else
				{
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET image_album_id = ' . $moving_target . ',
							image_contest = ' . phpbb_gallery_image::NO_CONTEST . '
						WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
					$db->sql_query($sql);
				}

				$sql = 'UPDATE ' . GALLERY_REPORTS_TABLE . '
					SET report_album_id = ' . $moving_target . '
					WHERE ' . $db->sql_in_set('report_image_id', $image_id_ary);
				$db->sql_query($sql);

				foreach ($image_id_ary as $image)
				{
					add_log('gallery', $moving_target, $image, 'LOG_GALLERY_MOVED', $album_data['album_name'], $target_data['album_name']);
				}

				$success = true;
			}
			else
			{
				$category_select = phpbb_gallery_album::get_albumbox(false, 'moving_target', $album_id, 'i_upload', $album_id);
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
				phpbb_gallery_image::handle_counter($image_id_ary, false);

				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
					SET image_status = ' . phpbb_gallery_image::STATUS_UNAPPROVED . '
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);

				$sql = 'SELECT image_id, image_name
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					add_log('gallery', $album_id, $row['image_id'], 'LOG_GALLERY_UNAPPROVED', $row['image_name']);
				}
				$db->sql_freeresult($result);

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
				phpbb_gallery_image::handle_counter($image_id_ary, true, true);

				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
					SET image_status = ' . phpbb_gallery_image::STATUS_APPROVED . '
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);

				$image_names = array();
				$sql = 'SELECT image_id, image_name
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					add_log('gallery', $album_id, $row['image_id'], 'LOG_GALLERY_APPROVED', $row['image_name']);
				}
				$db->sql_freeresult($result);

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
				phpbb_gallery_image::handle_counter($image_id_ary, false);

				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
					SET image_status = ' . phpbb_gallery_image::STATUS_LOCKED . '
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);

				$sql = 'SELECT image_id, image_name
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					add_log('gallery', $album_id, $row['image_id'], 'LOG_GALLERY_LOCKED', $row['image_name']);
				}
				$db->sql_freeresult($result);

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
				phpbb_gallery_image::handle_counter($image_id_ary, false);

				// Delete the files
				$sql = 'SELECT image_id, image_name, image_filename
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$result = $db->sql_query($sql);

				$filenames = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$filenames[(int) $row['image_id']] = $image_data['image_filename'];
					add_log('gallery', $album_id, $row['image_id'], 'LOG_GALLERY_DELETED', $row['image_name']);
				}
				$db->sql_freeresult($result);

				$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . '
					WHERE ' . $db->sql_in_set('comment_image_id', $image_id_ary);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_REPORTS_TABLE . '
					WHERE ' . $db->sql_in_set('report_image_id', $image_id_ary);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . '
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . '
					WHERE ' . $db->sql_in_set('image_id', $image_id_ary);
				$db->sql_query($sql);

				phpbb_gallery_image_base::delete_images($image_id_ary, $filenames);

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
					'report_status'			=> phpbb_gallery_constants::REPORT_LOCKED,
				);
				$sql = 'UPDATE ' . GALLERY_REPORTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $db->sql_in_set('report_id', $image_id_ary);
				$db->sql_query($sql);

				$sql = 'SELECT image_id, image_name
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE ' . $db->sql_in_set('image_reported', $image_id_ary);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					add_log('gallery', $album_id, $row['image_id'], 'LOG_GALLERY_REPORT_CLOSED', $row['image_name']);
				}
				$db->sql_freeresult($result);

				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
					SET image_reported = ' . phpbb_gallery_constants::REPORT_UNREPORT . '
					WHERE ' . $db->sql_in_set('image_reported', $image_id_ary);
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
					'report_status'			=> phpbb_gallery_constants::REPORT_OPEN,
				);
				$sql = 'UPDATE ' . GALLERY_REPORTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $db->sql_in_set('report_id', $image_id_ary);
				$db->sql_query($sql);

				$sql = 'SELECT report_image_id, report_id
					FROM ' . GALLERY_REPORTS_TABLE . '
					WHERE report_status = ' . phpbb_gallery_constants::REPORT_OPEN . '
						AND ' . $db->sql_in_set('report_id', $image_id_ary);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET image_reported = ' . $row['report_id'] . '
						WHERE ' . $db->sql_in_set('image_id', $row['report_image_id']);
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);

				$sql = 'SELECT image_id, image_name
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE ' . $db->sql_in_set('image_reported', $image_id_ary);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					add_log('gallery', $album_id, $row['image_id'], 'LOG_GALLERY_REPORT_OPENED', $row['image_name']);
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
				$sql = 'DELETE FROM ' . GALLERY_REPORTS_TABLE . '
					WHERE ' . $db->sql_in_set('report_id', $image_id_ary);
				$db->sql_query($sql);

				$sql = 'SELECT image_id, image_name
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE ' . $db->sql_in_set('image_reported', $image_id_ary);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					add_log('gallery', $album_id, $row['image_id'], 'LOG_GALLERY_REPORT_DELETED', $row['image_name']);
				}
				$db->sql_freeresult($result);

				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
					SET image_reported = ' . phpbb_gallery_constants::REPORT_UNREPORT . '
					WHERE ' . $db->sql_in_set('image_reported', $image_id_ary);
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
		phpbb_gallery_album::update_info($album_id);
		if ($moving_target)
		{
			phpbb_gallery_album::update_info($moving_target);
		}
		redirect(($redirect == 'redirect') ? phpbb_gallery_url::append_sid('album', "album_id=$album_id") : phpbb_gallery_url::append_sid('mcp', "mode=$mode&amp;album_id=$album_id"));
	}
}// end if ($action && $image_id_ary)

switch ($mode)
{
	case 'album':
		phpbb_gallery_mcp::album($mode, $album_id, $album_data);
	break;

	case 'report_open':
	case 'report_closed':
		phpbb_gallery_mcp::report($mode, $album_id, $album_data);
	break;

	case 'queue_unapproved':
	case 'queue_approved':
	case 'queue_locked':
		phpbb_gallery_mcp::queue($mode, $album_id, $album_data);
	break;

	break;

	case 'report_details':
	case 'queue_details':
		phpbb_gallery_mcp::details($mode, $option_id, $album_id, $album_data);
	break;
}

page_header($user->lang['GALLERY'] . ' &bull; ' . $user->lang['MCP'] . ' &bull; ' . $page_title, false);

$template->set_filenames(array(
	'body' => 'gallery/mcp_body.html')
);

page_footer();
