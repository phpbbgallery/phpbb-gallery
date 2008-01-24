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

$config['topics_per_page'] = 15;
$start		= request_var('start', 0);
$mode		= request_var('mode', 'joined');
$sort_order = request_var('order', 'ASC');

$personal_gallery_access = personal_gallery_access(1,1);

if (!$personal_gallery_access['view'])
{
	if(!$user->data['is_registered'])
	{
		login_box("gallery/album_personal.$phpEx", $user->lang['LOGIN_EXPLAIN_PERSONAL_GALLERY']);
	}
	trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
}
//
// Memberlist sorting
//
$mode_types_text 	= array($user->lang['SORT_JOINED'], $user->lang['SORT_USERNAME'], $user->lang['IMAGES'], $user->lang['LAST_IMAGE']);
$mode_types 		= array('joindate', 'username', 'images', 'last_image');

$select_sort_mode = '<select name="mode">';
for($i = 0; $i < count($mode_types_text); $i++)
{
	$selected = ( $mode == $mode_types[$i] ) ? ' selected="selected"' : '';
	$select_sort_mode .= '<option value="' . $mode_types[$i] . '"' . $selected . '>' . $mode_types_text[$i] . '</option>';
}
$select_sort_mode .= '</select>';

$select_sort_order = '<select name="order">';
if($sort_order == 'ASC')
{
	$select_sort_order .= '<option value="ASC" selected="selected">' . $user->lang['SORT_ASCENDING'] . '</option><option value="DESC">' . $user->lang['SORT_DESCENDING'] . '</option>';
}
else
{
	$select_sort_order .= '<option value="ASC">' . $user->lang['SORT_ASCENDING'] . '</option><option value="DESC" selected="selected">' . $user->lang['SORT_DESCENDING'] . '</option>';
}
$select_sort_order .= '</select>';

$template->assign_vars(array(
	'S_MODE_SELECT'					=> $select_sort_mode,
	'S_ORDER_SELECT'				=> $select_sort_order,
	'S_MODE_ACTION'					=> append_sid("album_personal_index.$phpEx")
));


switch( $mode )
{
	case 'joined':
		$order_by = "user_regdate ASC LIMIT $start, " . $config['topics_per_page'];
	break;

	case 'username':
		$order_by = "username $sort_order LIMIT $start, " . $config['topics_per_page'];
	break;

	case 'images':
		$order_by = "images $sort_order LIMIT $start, " . $config['topics_per_page'];
	break;

	case 'last_image':
		$order_by = "image_time $sort_order LIMIT $start, " . $config['topics_per_page'];
	break;

	default:
		$order_by = "user_regdate $sort_order LIMIT $start, " . $config['topics_per_page'];
}

$sql = 'SELECT u.username, u.user_id, u.user_regdate, MAX(i.image_id) as image_id, i.image_name, i.image_user_id, COUNT(i.image_id) AS images, MAX(i.image_time) as image_time
	FROM ' . USERS_TABLE . ' AS u, ' . GALLERY_IMAGES_TABLE . ' as i
	WHERE u.user_id <> ' . ANONYMOUS . '
		AND u.user_id = i.image_user_id
		AND i.image_album_id = ' . PERSONAL_GALLERY . '
	GROUP BY user_id
	ORDER BY ' . $order_by;

$result = $db->sql_query($sql);

$memberrow = array(); 

while( $row = $db->sql_fetchrow($result) ) 
{
	$memberrow[] = $row; 
}


for ($i = 0; $i < count($memberrow); $i++) 
{ 
	$pic_number = $memberrow[$i]['images'];
	$pic_id = $memberrow[$i]['image_id'];
	$sql = 'SELECT *
		FROM ' . GALLERY_IMAGES_TABLE . '
		WHERE image_id = ' . $pic_id;
	$result = $db->sql_query($sql);
	$thispic = $db->sql_fetchrow($result); 

	$template->assign_block_vars('memberrow', array(
		'ROW_CLASS'			=> ( !($i % 2) ) ? 'bg1' : 'bg2',
		'USERNAME'			=> $memberrow[$i]['username'],
		'U_VIEWGALLERY'		=> append_sid("album_personal.$phpEx?user_id=" . $memberrow[$i]['user_id']),
		'JOINED'			=> $user->format_date($memberrow[$i]['user_regdate']),
		'U_LAST_IMAGE'			=> append_sid("{$phpbb_root_path}gallery/image_page.$phpEx" , 'image_id=' . $thispic['image_id']),
		'LAST_IMAGE_NAME'		=> $thispic['image_name'],
		'LAST_IMAGE_TIME'		=> $user->format_date($thispic['image_time']),
		'IMAGES'				=> $pic_number,
	));
}

$sql = 'SELECT COUNT(DISTINCT u.user_id) AS total
	FROM ' . USERS_TABLE . ' AS u, '. GALLERY_IMAGES_TABLE . ' AS p
	WHERE u.user_id <> ' . ANONYMOUS . '
		AND u.user_id = p.image_user_id
		AND p.image_album_id = ' . PERSONAL_GALLERY;

$result = $db->sql_query($sql);

if ($total = $db->sql_fetchrow($result))
{
	$total_galleries = $total['total'];

	$pagination = generate_pagination("album_personal_index.$phpEx?mode=$mode&amp;order=$sort_order", $total_galleries, $config['topics_per_page'], $start);
}

$template->assign_vars(array(
	'LAST_POST_IMG'			=> $user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
	'PAGINATION'			=> $pagination,
	'PAGE_NUMBER'			=> sprintf($user->lang['PAGE_OF'], ( floor( $start / $config['topics_per_page'] ) + 1 ), ceil( $total_galleries / $config['topics_per_page'] )),
));

$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> $user->lang['PERSONAL_ALBUMS'],
	'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album_personal_index.$phpEx"),
));

// Output page
$page_title = $user->lang['GALLERY'];

page_header($page_title);

$template->set_filenames(array(
	'body' => 'gallery_personal_index_body.html')
);

page_footer();

?>