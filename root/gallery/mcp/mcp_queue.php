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
$count_images		= 0;

if (!in_array($sort_key, $sort_by_sql))
{
	$sort_key = 'image_time';
}

$where_case = '';
if ($mode == 'queue_unapproved')
{
	$where_case = 'AND image_status = 0';
}
else if ($mode == 'queue_approved')
{
	$where_case = 'AND image_status = 1';
}
else if ($mode == 'queue_locked')
{
	$where_case = 'AND image_status = 2';
}
$sql = 'SELECT COUNT(image_id) images
	FROM ' . GALLERY_IMAGES_TABLE . "
	WHERE image_album_id = $album_id
	$where_case";
$result = $db->sql_query($sql);
$count_images = $db->sql_fetchfield('images');
$db->sql_freeresult($result);

$sql = 'SELECT image_time, image_name, image_id, image_user_id, image_username, image_user_colour
	FROM ' . GALLERY_IMAGES_TABLE . "
	WHERE image_album_id = $album_id
	$where_case
	ORDER BY $sort_key $sort_dir";
$result = $db->sql_query_limit($sql, $images_per_page, $start);
while ($row = $db->sql_fetchrow($result))
{
	$template->assign_block_vars('image_row', array(
		'THUMBNAIL'			=> generate_image_link('fake_thumbnail', $gallery_config['link_thumbnail'], $row['image_id'], $row['image_name'], $album_id),
		'UPLOADER'			=> get_username_string('full', $row['image_user_id'], $row['image_username'], $row['image_user_colour']),
		'IMAGE_TIME'		=> $user->format_date($row['image_time']),
		'IMAGE_NAME'		=> $row['image_name'],
		'IMAGE_ID'			=> $row['image_id'],
		'U_IMAGE'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx" , "album_id=$album_id&amp;image_id=" . $row['image_id']),
		'U_IMAGE_PAGE'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx" , "mode=queue_details&amp;album_id=$album_id&amp;option_id=" . $row['image_id']),
	));
}
$db->sql_freeresult($result);

if ($mode == 'queue_unapproved')
{
	$case = 'UNAPPROVED';
}
else if ($mode == 'queue_approved')
{
	$case = 'APPROVED';
}
else if ($mode == 'queue_locked')
{
	$case = 'LOCKED';
}
switch ($count_images)
{
	case 0:
		$desc_string = $user->lang['WAITING_' . $case . '_NONE'];
	break;
	case 1:
		$desc_string = sprintf($user->lang['WAITING_' . $case . '_IMAGE'], $count_images);
	break;
	default:
		$desc_string = sprintf($user->lang['WAITING_' . $case . '_IMAGES'], $count_images);
	break;
}

$template->assign_vars(array(
	'S_SORT_DESC'			=> ($sort_dir == 'DESC') ? true : false,
	'S_SORT_KEY'			=> $sort_key,

	'TITLE'					=> $user->lang['IMAGES'],
	'DESCRIPTION'			=> $desc_string,
	'PAGINATION'			=> generate_pagination(append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=$mode&amp;album_id=$album_id&amp;sd=$sort_dir&amp;sk=$sort_key"), $count_images, $images_per_page, $start),
	'PAGE_NUMBER'			=> on_page($count_images, $images_per_page, $start),
	'TOTAL_IMAGES'			=> ($count_images == 1) ? $user->lang['VIEW_ALBUM_IMAGE'] : sprintf($user->lang['VIEW_ALBUM_IMAGES'], $count_images),

	'S_QUEUE_LIST'			=> true,
	'S_MARK'				=> true,
));

$template->assign_vars(array(
	'REPORTED_IMG'				=> $user->img('icon_topic_reported', 'IMAGE_REPORTED'),
	'UNAPPROVED_IMG'			=> $user->img('icon_topic_unapproved', 'IMAGE_UNAPPROVED'),
	'S_MCP_ACTION'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx" , "mode=$mode&amp;album_id=$album_id"),
	'DISP_FAKE_THUMB'			=> (empty($gallery_config['disp_fake_thumb'])) ? 0 : $gallery_config['disp_fake_thumb'],
	'FAKE_THUMB_SIZE'			=> (empty($gallery_config['fake_thumb_size'])) ? 50 : $gallery_config['fake_thumb_size'],
));

?>