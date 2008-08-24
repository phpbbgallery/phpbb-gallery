<?php

/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}
if ($mode == 'queue_details')
{
	$sql = 'SELECT *
		FROM ' . GALLERY_IMAGES_TABLE . "
		WHERE image_id = $option_id
		LIMIT 1";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$template->assign_vars(array(
		'IMAGE_STATUS'		=> $row['image_status'],
		'STATUS'			=> $user->lang['QUEUE_STATUS_' . $row['image_status']],
		'REPORT_ID'			=> $row['image_id'],
	));
}
if ($mode == 'report_details')
{
	$sql = 'SELECT r.*, u.username reporter_name, u.user_colour reporter_colour, i.*
		FROM ' . GALLERY_REPORTS_TABLE . " r
		LEFT JOIN " . USERS_TABLE . " u
			ON r.reporter_id = u.user_id
		LEFT JOIN " . GALLERY_IMAGES_TABLE . " i
			ON r.report_image_id = i.image_id
		WHERE r.report_id = $option_id
		LIMIT 1";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$template->assign_vars(array(
		'REPORTER'			=> get_username_string('full', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
		'REPORT_TIME'		=> $user->format_date($row['report_time']),
		'REPORT_ID'			=> $row['report_id'],
		'REPORT_NOTE'		=> $row['report_note'],
		'REPORT_STATUS'		=> ($row['report_status'] == 1) ? true : false,
		'STATUS'			=> $user->lang['REPORT_STATUS_' . $row['report_status']] . ' ' . $user->lang['QUEUE_STATUS_' . $row['image_status']],
	));
}

$template->assign_vars(array(
	'IMAGE_NAME'		=> $row['image_name'],
	'IMAGE_DESC'		=> generate_text_for_display($row['image_desc'], $row['image_desc_uid'], $row['image_desc_bitfield'], 7),
	'UPLOADER'			=> get_username_string('full', $row['image_user_id'], $row['image_username'], $row['image_user_colour']),
	'IMAGE_TIME'		=> $user->format_date($row['image_time']),
	'U_IMAGE'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx" , 'album_id=' . $album_id . '&amp;image_id=' . $row['image_id']),
	'U_EDIT_IMAGE'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx" , 'album_id=' . $album_id . '&amp;image_id=' . $row['image_id'] . '&amp;mode=image&amp;submode=edit'),
	'U_DELETE_IMAGE'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx" , 'album_id=' . $album_id . '&amp;image_id=' . $row['image_id'] . '&amp;mode=image&amp;submode=delete'),
	'IMAGE_RSZ_WIDTH'	=> $album_config['preview_rsz_width'],
	'IMAGE_RSZ_HEIGHT'	=> $album_config['preview_rsz_height'],
	'S_MCP_ACTION'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx" , "mode=" . (($mode == 'report_details') ? 'report_open' : 'queue_unapproved') . "&amp;album_id=$album_id"),
));

?>