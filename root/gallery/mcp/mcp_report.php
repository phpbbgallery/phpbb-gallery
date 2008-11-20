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

$start				= request_var('start', 0);
$sort_key			= request_var('sk', 'image_time');
$sort_dir			= (request_var('sd', 'DESC') == 'DESC') ? 'DESC' : 'ASC';
$images_per_page	= $config['topics_per_page'];
$count_images		= 0;

if (!in_array($sort_key, $sort_by_sql))
{
	$sort_key = 'image_time';
}

$m_status = ' AND i.image_status = 1';
if (gallery_acl_check('m_status', $album_id))
{
	$m_status = '';
}

if ($mode == 'report_open')
{
	$report_status = 1;
}
else
{
	$report_status = 2;
}
$sql = 'SELECT r.*, i.*
	FROM ' . GALLERY_REPORTS_TABLE . " r
	LEFT JOIN " . GALLERY_IMAGES_TABLE . " i
		ON r.report_image_id = i.image_id
	WHERE r.report_album_id = $album_id
		AND r.report_status = $report_status
		$m_status";
$result = $db->sql_query($sql);
while( $row = $db->sql_fetchrow($result) )
{
	$count_images++;
}
$db->sql_freeresult($result);
$sql = 'SELECT r.*, u.username reporter_name, u.user_colour reporter_colour, m.username mod_username, m.user_colour mod_user_colour, i.*
	FROM ' . GALLERY_REPORTS_TABLE . " r
	LEFT JOIN " . USERS_TABLE . " u
		ON r.reporter_id = u.user_id
	LEFT JOIN " . USERS_TABLE . " m
		ON r.report_manager = m.user_id
	LEFT JOIN " . GALLERY_IMAGES_TABLE . " i
		ON r.report_image_id = i.image_id
	WHERE r.report_album_id = $album_id
		AND r.report_status = $report_status
		$m_status
	ORDER BY $sort_key $sort_dir";
$result = $db->sql_query_limit($sql, $images_per_page, $start);
while( $row = $db->sql_fetchrow($result) )
{
	$template->assign_block_vars('image_row', array(
		'THUMBNAIL'			=> generate_image_link('fake_thumbnail', $album_config['link_thumbnail'], $row['image_id'], $row['image_name'], $album_id),
		'REPORTER'			=> get_username_string('full', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
		'UPLOADER'			=> get_username_string('full', $row['image_user_id'], $row['image_username'], $row['image_user_colour']),
		'REPORT_ID'			=> $row['report_id'],
		'REPORT_MOD'		=> ($row['report_manager']) ? get_username_string('full', $row['report_manager'], $row['mod_username'], $row['mod_user_colour']) : '',
		'REPORT_TIME'		=> $user->format_date($row['report_time']),
		'IMAGE_TIME'		=> $user->format_date($row['image_time']),
		'IMAGE_NAME'		=> $row['image_name'],
		'U_IMAGE'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx" , "album_id=$album_id&amp;image_id=" . $row['image_id']),
		'U_IMAGE_PAGE'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx" , 'mode=report_details&amp;album_id=' . $album_id . '&amp;option_id=' . $row['report_id']),
	));
}
$db->sql_freeresult($result);
if ($report_status == 2)
{
	$desc_string = $user->lang['WAITING_REPORTED_DONE'];
}
else
{
	switch ($count_images)
	{
		case 0:
			$desc_string = $user->lang['WAITING_REPORTED_NONE'];
		break;
		case 1:
			$desc_string = sprintf($user->lang['WAITING_REPORTED_IMAGE'], $count_images);
		break;
		default:
			$desc_string = sprintf($user->lang['WAITING_REPORTED_IMAGES'], $count_images);
		break;
	}
}


$template->assign_vars(array(
	'S_SORT_DESC'			=> ($sort_dir == 'DESC') ? true : false,
	'S_SORT_KEY'			=> $sort_key,

	'TITLE'					=> $user->lang['REPORTED_IMAGES'],
	'DESCRIPTION'			=> $desc_string,
	'PAGINATION'			=> generate_pagination(append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=$mode&amp;album_id=$album_id&amp;sd=$sort_dir&amp;sk=$sort_key"), $count_images, $images_per_page, $start),
	'PAGE_NUMBER'			=> on_page($count_images, $images_per_page, $start),
	'TOTAL_IMAGES'			=> ($count_images == 1) ? $user->lang['VIEW_ALBUM_IMAGE'] : sprintf($user->lang['VIEW_ALBUM_IMAGES'], $count_images),

	'S_REPORT_LIST'			=> true,
	'S_REPORTER'			=> true,
	'S_MARK'				=> true,
));

$template->assign_vars(array(
	'REPORTED_IMG'				=> $user->img('icon_topic_reported', 'IMAGE_REPORTED'),
	'UNAPPROVED_IMG'			=> $user->img('icon_topic_unapproved', 'IMAGE_UNAPPROVED'),
	'S_MCP_ACTION'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx" , "mode=$mode&amp;album_id=$album_id"),
	'DISP_FAKE_THUMB'			=> (empty($album_config['disp_fake_thumb'])) ? 0 : $album_config['disp_fake_thumb'],
	'FAKE_THUMB_SIZE'			=> (empty($album_config['fake_thumb_size'])) ? 50 : $album_config['fake_thumb_size'],
));

?>