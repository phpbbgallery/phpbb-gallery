<?php

/**
*
* @package phpBB3
* @version $Id: album.php 541 2008-06-28 08:48:56Z nickvergessen $
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');

// Get general album information
include_once("{$phpbb_root_path}{$gallery_root_path}includes/common.$phpEx");
include_once("{$phpbb_root_path}{$gallery_root_path}includes/permissions.$phpEx");
$album_access_array = get_album_access_array();


// ------------------------------------
// Check the request
// ------------------------------------
$user_id = request_var('user_id', 0);

$moderators_list = '';
$total_pics = 0;

	/**
	* Build the thumbnail page
	*/
	$start = request_var('start', 0);
	$sort_method = request_var('sort_method', $album_config['sort_method']);
	$sort_order = request_var('sort_order', $album_config['sort_order']);
	$pics_per_page = $album_config['rows_per_page'] * $album_config['cols_per_page'];
	$tot_unapproved = $image_counter = 0;

		$limit_sql = ($start == 0) ? $pics_per_page : $start .','. $pics_per_page;
		$view_string = gallery_acl_album_ids('i_view', 'string');
		$view_string = ($view_string) ? 'image_album_id IN (' . $view_string . ') AND image_status = 1' : 'image_album_id = 0';
		$moderativ_string = gallery_acl_album_ids('a_moderate', 'string');
		$moderativ_string = ($moderativ_string) ? (($view_string) ? ' OR ' : '') . 'image_album_id IN (' . $moderativ_string . ')' : '';

		$sql = 'SELECT *
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_user_id = ' . $user_id . "
				AND ($view_string $moderativ_string)";
		$result = $db->sql_query($sql);

		$picrow = array();

		while ($row = $db->sql_fetchrow($result))
		{
			$image_counter++;
		}
		$db->sql_freeresult($result);
		$sql = 'SELECT i.*, a.album_name
			FROM ' . GALLERY_IMAGES_TABLE . ' i
			LEFT JOIN ' . GALLERY_ALBUMS_TABLE . ' a
				ON a.album_id = i.image_album_id
			WHERE image_user_id = ' . $user_id . "
				AND ($view_string $moderativ_string)
			ORDER BY $sort_method $sort_order
			LIMIT $limit_sql";
		$result = $db->sql_query($sql);

		$picrow = array();

		while ($row = $db->sql_fetchrow($result))
		{
			$picrow[] = $row;
		}
		$db->sql_freeresult($result);
		for ($i = 0; $i < count($picrow); $i += $album_config['cols_per_page'])
		{
			$template->assign_block_vars('picrow', array());

			for ($j = $i; $j < ($i + $album_config['cols_per_page']); $j++)
			{
				if( $j >= count($picrow) )
				{
					$template->assign_block_vars('picrow.nopiccol', array()); 
					$template->assign_block_vars('picrow.picnodetail', array()); 
					continue;
				}
				$album_id = $picrow[$j]['image_album_id'];

				if(!$picrow[$j]['image_rates'])
				{
					$picrow[$j]['rating'] = $user->lang['NOT_RATED'];
				}
				else
				{
					$picrow[$j]['rating'] = $picrow[$j]['image_rate_avg'] / 100;
				}

				$approval_link = (gallery_acl_check('a_moderate', $album_id) && ($picrow[$j]['image_status'] == 0)) ? '<a href="'. append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=queue_details&amp;album_id=$album_id&amp;option_id=" . $picrow[$j]['image_id']) . '">' . $user->lang['APPROVE'] . '</a>' : '';


				$message_parser				= new parse_message();
				$message_parser->message	= $picrow[$j]['image_desc'];
				$message_parser->decode_message($picrow[$j]['image_desc_uid']);
				$template->assign_block_vars('picrow.piccol', array(
					'U_IMAGE'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
					'THUMBNAIL'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}thumbnail.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
					'DESC'			=> $message_parser->message,
					'APPROVAL'		=> $approval_link,
				));

				$allow_edit = ((gallery_acl_check('i_edit', $album_id) && ($picrow[$j]['image_user_id'] == $user_id)) || gallery_acl_check('a_moderate', $album_id)) ? true : false;
				$allow_delete = ((gallery_acl_check('i_delete', $album_id) && ($picrow[$j]['image_user_id'] == $user_id)) || gallery_acl_check('a_moderate', $album_id)) ? true : false;

				$template->assign_block_vars('picrow.pic_detail', array(
					'U_IMAGE_PAGE'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
					'U_ALBUM'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $picrow[$j]['image_album_id']),
					'IMAGE_NAME'	=> $picrow[$j]['image_name'],
					'ALBUM_NAME'	=> $picrow[$j]['album_name'],
					'POSTER'	=> get_username_string('full', $picrow[$j]['image_user_id'], ($picrow[$j]['image_user_id'] <> ANONYMOUS) ? $picrow[$j]['image_username'] : $user->lang['GUEST'], $picrow[$j]['image_user_colour']),
					'TIME'		=> $user->format_date($picrow[$j]['image_time']),
					'VIEW'		=> $picrow[$j]['image_view_count'],
					'RATING'	=> (($album_config['rate'] == 1) && gallery_acl_check('i_rate', $album_id)) ? ( '<a href="' . append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . "&amp;image_id=" . $picrow[$j]['image_id']) . '#rating">' . $user->lang['RATING'] . '</a>: ' . $picrow[$j]['rating'] . '<br />') : '',
					'COMMENTS'	=> (($album_config['comment'] == 1) && gallery_acl_check('c_post', $album_id)) ? ( '<a href="' . append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . "&amp;image_id=" . $picrow[$j]['image_id']) . '#comments">' . $user->lang['COMMENTS'] . '</a>: ' . $picrow[$j]['image_comments'] . '<br />') : '',

					'EDIT'		=> $allow_edit ? '<a href="' . append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=edit&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id']) . '">' . $user->lang['EDIT_IMAGE'] . '</a>' : '',
					'DELETE'	=> $allow_delete ? '<a href="' . append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=delete&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id']) . '">' . $user->lang['DELETE_IMAGE'] . '</a>' : '',
					'MOVE'		=> (gallery_acl_check('a_moderate', $album_id)) ? '<a href="' . append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "action=images_move&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id']) . '&amp;redirect=redirect">' . $user->lang['MOVE'] . '</a>' : '',
					'STATUS'	=> (gallery_acl_check('a_moderate', $album_id)) ? '<a href="'. append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=queue_details&amp;album_id=$album_id&amp;option_id=" . $picrow[$j]['image_id']) . '">' . $user->lang['IMAGE_STATUS'] . '</a>' : '',
					'IP'		=> ($user->data['user_type'] == USER_FOUNDER) ? $user->lang['IP'] . ': <a href="http://www.nic.com/cgi-bin/whois.cgi?query=' . $picrow[$j]['image_user_ip'] . '">' . $picrow[$j]['image_user_ip'] . '</a><br />' : ''
				));
			}
		}

	/**
	* additional sorting options
	*/
	$sort_rating_option = $sort_new_comment_option = $sort_comments_option = '';
	if ($album_config['rate'] == 1)
	{
		$sort_rating_option = '<option value="image_rate_avg" ';
		$sort_rating_option .= ($sort_method == 'image_rate_avg') ? 'selected="selected"' : '';
		$sort_rating_option .= '>' . $user->lang['RATING'] . '</option>';
	}
	if ($album_config['comment'] == 1)
	{
		$sort_comments_option = '<option value="image_comments" ';
		$sort_comments_option .= ($sort_method == 'image_comments') ? 'selected="selected"' : '';
		$sort_comments_option .= '>' . $user->lang['COMMENTS'] . '</option>';

		$sort_new_comment_option = '<option value="image_last_comment" ';
		$sort_new_comment_option .= ($sort_method == 'image_last_comment') ? 'selected="selected"' : '';
		$sort_new_comment_option .= '>' . $user->lang['NEW_COMMENT'] . '</option>';
	}

$template->assign_vars(array(
	'S_COLS'					=> $album_config['cols_per_page'],
	'S_COL_WIDTH'				=> (100/$album_config['cols_per_page']) . '%',
	'S_SEARCH_ACTION'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", "user_id=$user_id"),

	'SORT_TIME'					=> ($sort_method == 'image_time') ? 'selected="selected"' : '',
	'SORT_IMAGE_TITLE'			=> ($sort_method == 'image_name') ? 'selected="selected"' : '',
	'SORT_USERNAME' 			=> ($sort_method == 'image_username') ? 'selected="selected"' : '',
	'SORT_VIEW'					=> ($sort_method == 'image_view_count') ? 'selected="selected"' : '',

	'SORT_RATING_OPTION'		=> $sort_rating_option,
	'SORT_COMMENTS_OPTION'		=> $sort_comments_option,
	'SORT_NEW_COMMENT_OPTION'	=> $sort_new_comment_option,
	'SORT_ASC'					=> ($sort_order == 'ASC') ? 'selected="selected"' : '',
	'SORT_DESC'					=> ($sort_order == 'DESC') ? 'selected="selected"' : '',

	'PAGINATION'				=> generate_pagination(append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", "user_id=$user_id&amp;sort_method=$sort_method&amp;sort_order=$sort_order"), $image_counter, $pics_per_page, $start),
	'TOTAL_IMAGES'				=> ($image_counter == 1) ? $user->lang['IMAGE_#'] : sprintf($user->lang['IMAGES_#'], $image_counter),
	'PAGE_NUMBER'				=> on_page($image_counter, $pics_per_page, $start),
));

// Output page
$page_title = $user->lang['SEARCH'];

page_header($page_title);

$template->set_filenames(array(
	'body' => 'gallery_search_result_body.html')
);

page_footer();
?>