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

if (!defined('IN_PHPBB'))
{
	exit;
}

if ($mode == 'queue_details')
{
	$sql = 'SELECT *
		FROM ' . GALLERY_IMAGES_TABLE . "
		WHERE image_id = $option_id";
	$result = $db->sql_query_limit($sql, 1);
	$row = $db->sql_fetchrow($result);
	$template->assign_vars(array(
		'IMAGE_STATUS'		=> $row['image_status'],
		'STATUS'			=> $user->lang['QUEUE_STATUS_' . $row['image_status']],
		'REPORT_ID'			=> $row['image_id'],
	));
}
if ($mode == 'report_details')
{
	$m_status = ' AND i.image_status = 1';
	if (gallery_acl_check('m_status', $album_id))
	{
		$m_status = '';
	}
	$sql = 'SELECT r.*, u.username reporter_name, u.user_colour reporter_colour, i.*
		FROM ' . GALLERY_REPORTS_TABLE . " r
		LEFT JOIN " . USERS_TABLE . " u
			ON r.reporter_id = u.user_id
		LEFT JOIN " . GALLERY_IMAGES_TABLE . " i
			ON r.report_image_id = i.image_id
		WHERE r.report_id = $option_id
			$m_status";
	$result = $db->sql_query_limit($sql, 1);
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
	'UC_IMAGE'			=> generate_image_link('medium', $album_config['link_thumbnail'], $row['image_id'], $row['image_name'], $album_id),
	'U_EDIT_IMAGE'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx" , 'album_id=' . $album_id . '&amp;image_id=' . $row['image_id'] . '&amp;mode=image&amp;submode=edit'),
	'U_DELETE_IMAGE'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx" , 'album_id=' . $album_id . '&amp;image_id=' . $row['image_id'] . '&amp;mode=image&amp;submode=delete'),
	'S_MCP_ACTION'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx" , "mode=" . (($mode == 'report_details') ? 'report_open' : 'queue_unapproved') . "&amp;album_id=$album_id"),
));

?>