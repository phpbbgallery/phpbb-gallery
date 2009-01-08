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

$start				= request_var('start', 0);
$sort_key			= request_var('sk', 'image_time');
$sort_dir			= (request_var('sd', 'DESC') == 'DESC') ? 'DESC' : 'ASC';
$images_per_page	= $config['topics_per_page'];
$count_images		= $album_data['album_images_real'];

if (!in_array($sort_key, $sort_by_sql))
{
	$sort_key = 'image_time';
}

$m_status = ' AND image_status = 1';
if (gallery_acl_check('m_status', $album_id))
{
	$m_status = '';
}

$sql = 'SELECT i.*, r.report_status, r.report_id
	FROM ' . GALLERY_IMAGES_TABLE . " i
	LEFT JOIN " . GALLERY_REPORTS_TABLE . " r
		ON r.report_image_id = i.image_id
	WHERE image_album_id = $album_id
		$m_status
	ORDER BY i.$sort_key $sort_dir";
$result = $db->sql_query_limit($sql, $images_per_page, $start);
while( $row = $db->sql_fetchrow($result) )
{
	$template->assign_block_vars('image_row', array(
		'THUMBNAIL'			=> generate_image_link('fake_thumbnail', $album_config['link_thumbnail'], $row['image_id'], $row['image_name'], $album_id),
		'UPLOADER'			=> get_username_string('full', $row['image_user_id'], $row['image_username'], $row['image_user_colour']),
		'IMAGE_TIME'		=> $user->format_date($row['image_time']),
		'IMAGE_NAME'		=> $row['image_name'],
		'COMMENTS'			=> $row['image_comments'],
		'RATING'			=> ($row['image_rate_avg'] / 100),
		'STATUS'			=> $user->lang['QUEUE_STATUS_' . $row['image_status']],
		'IMAGE_ID'			=> $row['image_id'],
		'S_REPORTED'		=> (isset($row['report_status']) && $row['report_status'] == 1) ? true : false,
		'S_UNAPPROVED'		=> ($row['image_status'] == 0) ? true : false,
		'U_IMAGE'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx" , "album_id=$album_id&amp;image_id=" . $row['image_id']),
		'U_IMAGE_PAGE'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx" , "album_id=$album_id&amp;image_id=" . $row['image_id']),
		'U_REPORT'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx" , "mode=report_details&amp;album_id=$album_id&amp;option_id=" . $row['report_id']),
		'U_QUEUE'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx" , "mode=queue_details&amp;album_id=$album_id&amp;option_id=" . $row['image_id']),
	));
}
$template->assign_vars(array(
	'S_SORT_DESC'			=> ($sort_dir == 'DESC') ? true : false,
	'S_SORT_KEY'			=> $sort_key,

	'TITLE'					=> $user->lang['IMAGES'],
	'DESCRIPTION'			=> '',//$desc_string,
	'NO_IMAGES_NOTE'		=> $user->lang['NO_IMAGES'],
	'PAGINATION'			=> generate_pagination(append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=$mode&amp;album_id=$album_id&amp;sd=$sort_dir&amp;sk=$sort_key"), $count_images, $images_per_page, $start),
	'PAGE_NUMBER'			=> on_page($count_images, $images_per_page, $start),
	'TOTAL_IMAGES'			=> ($count_images == 1) ? $user->lang['VIEW_ALBUM_IMAGE'] : sprintf($user->lang['VIEW_ALBUM_IMAGES'], $count_images),

	'S_COMMENTS'			=> $album_config['comment'],
	'S_RATINGS'				=> $album_config['rate'],
	'S_STATUS'				=> true,
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