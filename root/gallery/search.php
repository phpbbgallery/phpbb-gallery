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
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');

// Get general album information
$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . $gallery_root_path . 'includes/common.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/permissions.' . $phpEx);
$album_access_array = get_album_access_array();


/**
* Check the request
*/
$user_id = request_var('user_id', 0);
$start = request_var('start', 0);
$username = '';
$images_per_page = $album_config['rows_per_page'] * $album_config['cols_per_page'];
$tot_unapproved = $image_counter = 0;

/**
* Build the sort options
*/

$sort_days	= request_var('st', 0);
$sort_key	= request_var('sk', $album_config['sort_method']);
$sort_dir	= request_var('sd', $album_config['sort_order']);
$limit_days = array(0 => $user->lang['ALL_IMAGES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);

$sort_by_text = array('t' => $user->lang['TIME'], 'n' => $user->lang['IMAGE_NAME'], 'u' => $user->lang['SORT_USERNAME'], 'vc' => $user->lang['VIEWS']);
$sort_by_sql = array('t' => 'image_time', 'n' => 'image_name', 'u' => 'image_username', 'vc' => 'image_view_count');

if ($album_config['rate'] == 1)
{
	$sort_by_text['ra'] = $user->lang['RATING'];
	$sort_by_sql['ra'] = 'image_rate_avg';
	$sort_by_text['r'] = $user->lang['RATES_COUNT'];
	$sort_by_sql['r'] = 'image_rates';
}
if ($album_config['comment'] == 1)
{
	$sort_by_text['c'] = $user->lang['COMMENTS'];
	$sort_by_sql['c'] = 'image_comments';
	$sort_by_text['lc'] = $user->lang['NEW_COMMENT'];
	$sort_by_sql['lc'] = 'image_last_comment';
}
$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

$view_string = gallery_acl_album_ids('i_view', 'string');
$view_string = ($view_string) ? 'image_album_id IN (' . $view_string . ') AND image_status = 1' : 'image_album_id = 0';
$moderativ_string = gallery_acl_album_ids('m_status', 'string');
$moderativ_string = ($moderativ_string) ? (($view_string) ? ' OR ' : '') . 'image_album_id IN (' . $moderativ_string . ')' : '';

$sql = 'SELECT *
	FROM ' . GALLERY_IMAGES_TABLE . '
	WHERE image_user_id = ' . $user_id . "
		AND ($view_string $moderativ_string)";
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$image_counter++;
	$username = $row['image_username'];
}
$db->sql_freeresult($result);

$sql = 'SELECT i.*, a.album_name
	FROM ' . GALLERY_IMAGES_TABLE . ' i
	LEFT JOIN ' . GALLERY_ALBUMS_TABLE . ' a
		ON a.album_id = i.image_album_id
	WHERE image_user_id = ' . $user_id . "
		AND ($view_string $moderativ_string)
	ORDER BY $sql_sort_order";
$result = $db->sql_query_limit($sql, $images_per_page, $start);

		$images = array();

while ($row = $db->sql_fetchrow($result))
{
	$images[] = $row;
}
$db->sql_freeresult($result);

for ($i = 0; $i < count($images); $i += $album_config['cols_per_page'])
{
	$template->assign_block_vars('image_row', array());

	for ($j = $i; $j < ($i + $album_config['cols_per_page']); $j++)
	{
		if ($j >= count($images))
		{
			$template->assign_block_vars('image_row.no_image', array());
			continue;
		}
		$album_id = $images[$j]['image_album_id'];

		if (!$images[$j]['image_rates'])
		{
			$images[$j]['rating'] = $user->lang['NOT_RATED'];
		}
		else
		{
			$images[$j]['rating'] = sprintf((($images[$j]['image_rates'] == 1) ? $user->lang['RATE_STRING'] : $user->lang['RATES_STRING']), $images[$j]['image_rate_avg'] / 100, $images[$j]['image_rates']);
		}

		$perm_user_id = ($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from'];
		$allow_edit = ((gallery_acl_check('i_edit', $album_id) && ($images[$j]['image_user_id'] == $perm_user_id)) || gallery_acl_check('m_edit', $album_id)) ? true : false;
		$allow_delete = ((gallery_acl_check('i_delete', $album_id) && ($images[$j]['image_user_id'] == $perm_user_id)) || gallery_acl_check('m_delete', $album_id)) ? true : false;

		$template->assign_block_vars('image_row.image', array(
			'IMAGE_ID'		=> $images[$j]['image_id'],
			'UC_IMAGE_NAME'	=> generate_image_link('image_name', $album_config['link_image_name'], $images[$j]['image_id'], $images[$j]['image_name'], $images[$j]['image_album_id']),
			'UC_THUMBNAIL'	=> generate_image_link('thumbnail', $album_config['link_thumbnail'], $images[$j]['image_id'], $images[$j]['image_name'], $images[$j]['image_album_id']),
			'U_ALBUM'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $images[$j]['image_album_id']),
			'S_UNAPPROVED'	=> (gallery_acl_check('m_status', $album_id) && (!$images[$j]['image_status'])) ? true : false,
			'S_LOCKED'		=> (gallery_acl_check('m_status', $album_id) && ($images[$j]['image_status'] == 2)) ? true : false,
			'S_REPORTED'	=> (gallery_acl_check('m_report', $album_id) && $images[$j]['image_reported']) ? true : false,

			'ALBUM_NAME'	=> ((utf8_strlen(htmlspecialchars_decode($images[$j]['album_name'])) > $album_config['shorted_imagenames'] + 3 ) ? htmlspecialchars(utf8_substr(htmlspecialchars_decode($images[$j]['album_name']), 0, $album_config['shorted_imagenames']) . '...') : ($images[$j]['album_name'])),
			'POSTER'		=> get_username_string('full', $images[$j]['image_user_id'], ($images[$j]['image_user_id'] <> ANONYMOUS) ? $images[$j]['image_username'] : $user->lang['GUEST'], $images[$j]['image_user_colour']),
			'TIME'			=> $user->format_date($images[$j]['image_time']),
			'VIEW'			=> $images[$j]['image_view_count'],

			'S_RATINGS'		=> (($album_config['rate'] == 1) && gallery_acl_check('i_rate', $album_id)) ? $images[$j]['rating'] : '',
			'U_RATINGS'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $images[$j]['image_album_id'] . "&amp;image_id=" . $images[$j]['image_id']) . '#rating',
			'L_COMMENTS'	=> ($images[$j]['image_comments'] == 1) ? $user->lang['COMMENT'] : $user->lang['COMMENTS'],
			'S_COMMENTS'	=> (($album_config['comment'] == 1) && gallery_acl_check('c_read', $album_id)) ? (($images[$j]['image_comments']) ? $images[$j]['image_comments'] : $user->lang['NO_COMMENTS']) : '',
			'U_COMMENTS'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $images[$j]['image_album_id'] . "&amp;image_id=" . $images[$j]['image_id']) . '#comments',

			'S_IP'		=> ($auth->acl_get('a_')) ? $images[$j]['image_user_ip'] : '',
			'U_WHOIS'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'mode=whois&amp;ip=' . $images[$j]['image_user_ip']),
			'U_REPORT'	=> (gallery_acl_check('m_report', $album_id) && $images[$j]['image_reported']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=report_details&amp;album_id=$album_id&amp;option_id=" . $images[$j]['image_reported']) : '',
			'U_STATUS'	=> (gallery_acl_check('m_status', $album_id) && ($images[$j]['image_status'] || ($user->data['user_id'] <> $images[$j]['image_user_id']))) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=queue_details&amp;album_id=$album_id&amp;option_id=" . $images[$j]['image_id']) : '',
			'L_STATUS'	=> (!$images[$j]['image_status']) ? $user->lang['APPROVE_IMAGE'] : (($images[$j]['image_status'] == 1) ? $user->lang['CHANGE_IMAGE_STATUS'] : $user->lang['UNLOCK_IMAGE']),
			'U_MOVE'	=> (gallery_acl_check('m_move', $album_id)) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "action=images_move&amp;album_id=$album_id&amp;image_id=" . $images[$j]['image_id'] . "&amp;redirect=redirect") : '',
			'U_EDIT'	=> $allow_edit ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=edit&amp;album_id=$album_id&amp;image_id=" . $images[$j]['image_id']) : '',
			'U_DELETE'	=> $allow_delete ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=delete&amp;album_id=$album_id&amp;image_id=" . $images[$j]['image_id']) : '',
		));
	}
}

$template->assign_vars(array(
	'S_THUMBNAIL_SIZE'			=> $album_config['thumbnail_size'] + 20 + (($album_config['thumbnail_info_line']) ? 16 : 0),
	'S_COLS'					=> $album_config['cols_per_page'],
	'S_COL_WIDTH'				=> (100/$album_config['cols_per_page']) . '%',
	'S_SEARCH_ACTION'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", "user_id=$user_id"),
	'SEARCH_NAME'				=> sprintf($user->lang['SEARCH_USER_IMAGES_OF'], $username),

	'S_SELECT_SORT_DIR'			=> $s_sort_dir,
	'S_SELECT_SORT_KEY'			=> $s_sort_key,

	'PAGINATION'				=> generate_pagination(append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", "user_id=$user_id&amp;sk=$sort_key&amp;sd=$sort_dir&amp;st=$sort_days"), $image_counter, $images_per_page, $start),
	'TOTAL_IMAGES'				=> ($image_counter == 1) ? $user->lang['IMAGE_#'] : sprintf($user->lang['IMAGES_#'], $image_counter),
	'PAGE_NUMBER'				=> on_page($image_counter, $images_per_page, $start),
));

page_header($user->lang['GALLERY'] . ' &bull; ' . $user->lang['SEARCH']);

$template->set_filenames(array(
	'body' => 'gallery_search_result_body.html')
);

page_footer();

?>