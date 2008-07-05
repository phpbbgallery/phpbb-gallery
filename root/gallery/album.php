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
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');
$user->setup('mods/info_ucp_gallery');

// Get general album information
include_once("{$phpbb_root_path}{$gallery_root_path}includes/common.$phpEx");
include_once("{$phpbb_root_path}{$gallery_root_path}includes/permissions.$phpEx");
$album_access_array = get_album_access_array();


// ------------------------------------
// Check the request
// ------------------------------------
$user_id = request_var('user_id', 0);
$album_id = request_var('album_id', request_var('id', 0));
if ($user_id)
{
	$sql = 'SELECT album_id FROM ' . GALLERY_ALBUMS_TABLE . ' WHERE album_user_id = ' . $user_id . ' AND parent_id = 0';
	$result = $db->sql_fetchrow($db->sql_query($sql));
	$album_id = $result['album_id'];
}
if(!$album_id)
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}
$moderators_list = '';
$total_pics = 0;
$album_data = $catrows = array();
$album_data = get_album_info($album_id);
if (empty($album_data))
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}

if ($album_data['album_user_id'] > 0)
{
	$album_access_array[$album_id] = $album_access_array[-3];
}

$total_pics = $album_data['count'];
/**
* Build Auth List
*/
$auth_key = array_keys($album_access_array[$album_id]);
$auth_list = '';
/*Needs to be fixed
for ($i = 0; $i < (count($album_user_access) - 1); $i++)// ignore MODERATOR in this loop
{// we should skip a loop if RATE and COMMENT is disabled
	if((($album_config['rate'] == 0) && ($auth_key[$i] == 'rate')) || (($album_config['comment'] == 0) && ($auth_key[$i] == 'comment')))
	{
		continue;
	}
	$auth_list .= ($album_user_access[$auth_key[$i]] == 1) ? $user->lang['ALBUM_'. strtoupper($auth_key[$i]) .'_CAN'] : $user->lang['ALBUM_'. strtoupper($auth_key[$i]) .'_CANNOT'];
	$auth_list .= '<br />';
}*/
/**
* send cheaters home
*/
if(!$album_access_array[$album_id]['i_view'])
{
	if ($user->data['is_bot'])
	{
		redirect(append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx"));
	}
	if (!$user->data['is_registered'])
	{
		login_box();
	}
	else
	{
		trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
	}
}

/**
* Build Album-Index
*/
include("{$phpbb_root_path}{$gallery_root_path}includes/functions_display.$phpEx");
display_albums($album_data);
if ($album_id <> 0)
{
	generate_album_nav($album_data);
}
/*if ($album_data['album_type'] == 2)
{ we just do this, when we have images */
	if (($user->data['user_type'] == USER_FOUNDER) || ($album_access_array[$album_id]['a_moderate'] == 1))
	{
		$template->assign_vars(array(
			'U_MCP'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?album_id=$album_id"),
		));
	}
	$grouprows = array();
	$album_moderators = array();
	get_album_moderators($album_moderators, $album_id);
	$l_moderator = $moderators_list = '';
	if (!empty($album_moderators[$album_id]))
	{
		$l_moderator = (sizeof($album_moderators[$album_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
		$moderators_list = implode(', ', $album_moderators[$album_id]);
	}

	/**
	* Build the thumbnail page
	*/
	$start = request_var('start', 0);
	$sort_method = request_var('sort_method', $album_config['sort_method']);
	$sort_order = request_var('sort_order', $album_config['sort_order']);
	$pics_per_page = $album_config['rows_per_page'] * $album_config['cols_per_page'];
	$tot_unapproved = 0;

	if ($total_pics > 0)
	{
		$limit_sql = ($start == 0) ? $pics_per_page : $start .','. $pics_per_page;
		$pic_approval_sql = ' AND image_status = 1';
		if ($album_access_array[$album_id]['a_moderate'] == 1)
		{
			$pic_approval_sql = '';
		}

		$sql = 'SELECT *
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_album_id = ' . $album_id . $pic_approval_sql . ' 
			ORDER BY ' . $sort_method . ' ' . $sort_order . ' 
			LIMIT ' . $limit_sql;
		$result = $db->sql_query($sql);

		$picrow = array();

		while( $row = $db->sql_fetchrow($result) )
		{
			$picrow[] = $row;
		}
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

				if(!$picrow[$j]['image_rates'])
				{
					$picrow[$j]['rating'] = $user->lang['NOT_RATED'];
				}
				else
				{
					$picrow[$j]['rating'] = $picrow[$j]['image_rate_avg'] / 100;
				}

				$approval_link = (($album_access_array[$album_id]['a_moderate'] == 1) && ($picrow[$j]['image_status'] == 0)) ? '<a href="'. append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=queue_details&amp;album_id=$album_id&amp;option_id=" . $picrow[$j]['image_id']) . '">' . $user->lang['APPROVE'] . '</a>' : '';


				$message_parser				= new parse_message();
				$message_parser->message	= $picrow[$j]['image_desc'];
				$message_parser->decode_message($picrow[$j]['image_desc_uid']);
				$template->assign_block_vars('picrow.piccol', array(
					'U_IMAGE'		=> ($album_config['fullpic_popup']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']) : append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
					'THUMBNAIL'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}thumbnail.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . '&amp;image_id=' . $picrow[$j]['image_id']),
					'DESC'			=> $message_parser->message,
					'APPROVAL'		=> $approval_link,
				));

				$allow_edit = ((($album_access_array[$album_id]['i_edit'] == 1) && ($picrow[$j]['image_user_id'] == $user->data['user_id'])) || ($album_access_array[$album_id]['a_moderate'] == 1)) ? true : false;
				$allow_delete = ((($album_access_array[$album_id]['i_delete'] == 1) && ($picrow[$j]['image_user_id'] == $user->data['user_id'])) || ($album_access_array[$album_id]['a_moderate'] == 1)) ? true : false;

				$template->assign_block_vars('picrow.pic_detail', array(
					'TITLE'		=> $picrow[$j]['image_name'],
					'POSTER'	=> get_username_string('full', $picrow[$j]['image_user_id'], ($picrow[$j]['image_user_id'] <> ANONYMOUS) ? $picrow[$j]['image_username'] : $user->lang['GUEST'], $picrow[$j]['image_user_colour']),
					'TIME'		=> $user->format_date($picrow[$j]['image_time']),
					'VIEW'		=> $picrow[$j]['image_view_count'],
					'RATING'	=> (($album_config['rate'] == 1) && ($album_access_array[$album_id]['i_rate'] == 1)) ? ( '<a href="' . append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . "&amp;image_id=" . $picrow[$j]['image_id']) . '#rating">' . $user->lang['RATING'] . '</a>: ' . $picrow[$j]['rating'] . '<br />') : '',
					'COMMENTS'	=> (($album_config['comment'] == 1) && ($album_access_array[$album_id]['c_post'] == 1)) ? ( '<a href="' . append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", 'album_id=' . $picrow[$j]['image_album_id'] . "&amp;image_id=" . $picrow[$j]['image_id']) . '#comments">' . $user->lang['COMMENTS'] . '</a>: ' . $picrow[$j]['image_comments'] . '<br />') : '',

					'EDIT'		=> $allow_edit ? '<a href="' . append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=edit&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id']) . '">' . $user->lang['EDIT_IMAGE'] . '</a>' : '',
					'DELETE'	=> $allow_delete ? '<a href="' . append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=delete&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id']) . '">' . $user->lang['DELETE_IMAGE'] . '</a>' : '',
					'MOVE'		=> ($album_access_array[$album_id]['a_moderate'] == 1) ? '<a href="' . append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "action=images_move&amp;album_id=$album_id&amp;image_id=" . $picrow[$j]['image_id']) . '&amp;redirect=redirect">' . $user->lang['MOVE'] . '</a>' : '',
					'STATUS'	=> ($album_access_array[$album_id]['a_moderate'] == 1) ? '<a href="'. append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=queue_details&amp;album_id=$album_id&amp;option_id=" . $picrow[$j]['image_id']) . '">' . $user->lang['IMAGE_STATUS'] . '</a>' : '',
					'IP'		=> ($user->data['user_type'] == USER_FOUNDER) ? $user->lang['IP'] . ': <a href="http://www.nic.com/cgi-bin/whois.cgi?query=' . $picrow[$j]['image_user_ip'] . '">' . $picrow[$j]['image_user_ip'] . '</a><br />' : ''
				));
			}
		}

		$template->assign_vars(array(
			'PAGINATION'	=> generate_pagination(append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "id=$album_id&amp;sort_method=$sort_method&amp;sort_order=$sort_order"), $total_pics, $pics_per_page, $start),
			'PAGE_NUMBER'	=> on_page($total_pics, $pics_per_page, $start),
		));
	}
	else
	{
		$template->assign_block_vars('no_pics', array());
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
/*}*/
/**
* Build Jumpbox
*/
$album_jumpbox = $user->lang['JUMP_TO'] . ': ';
$album_jumpbox .= '<form id="jumpbox" action="' . append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx") . '" method="get">';
$album_jumpbox .= '<p><select name="album_id" onchange="forms[\'jumpbox\'].submit()">';
if (!$album_data['album_user_id'])
{
	$album_jumpbox .= make_album_jumpbox($album_id);
}
else
{
	$album_jumpbox .= make_personal_jumpbox($album_data['album_user_id'], $album_id);
}
$album_jumpbox .= '</select>';
$album_jumpbox .= '<input type="hidden" name="sid" value="' . $user->data['session_id'] . '" /></p>';
$album_jumpbox .= '</form>';

$allowed_create = false;
if ($album_data['album_user_id'] == $user->data['user_id'])
{
	$allowed_create = $album_access_array[-2]['i_upload'];
	if ($allowed_create)
	{
		$sql = 'SELECT COUNT(album_id) as albums
			FROM ' . GALLERY_ALBUMS_TABLE . "
			WHERE album_user_id = {$user->data['user_id']}";
		$result = $db->sql_query($sql);
		$albums = $db->sql_fetchrow($result);
		if (($albums['albums'] - 1) >= $album_access_array[-2]['album_count'])
		{
			$allowed_create = false;
		}
	}
}
$template->assign_vars(array(
	'S_MODE'					=> $album_data['album_type'],
	'L_MODERATORS'				=> $l_moderator,
	'MODERATORS'				=> $moderators_list,
	'U_UPLOAD_IMAGE'			=> (!$album_data['album_user_id'] || ($album_data['album_user_id'] == $user->data['user_id'])) ?
										append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=upload&amp;album_id=$album_id") : '',
	'U_CREATE_ALBUM'			=> (($album_data['album_user_id'] == $user->data['user_id']) && $allowed_create) ?
										append_sid("{$phpbb_root_path}ucp.$phpEx", "i=gallery&amp;mode=manage_albums&amp;action=create&amp;parent_id=$album_id&amp;redirect=album") : '',
	'U_EDIT_ALBUM'				=> ($album_data['album_user_id'] == $user->data['user_id']) ?
										append_sid("{$phpbb_root_path}ucp.$phpEx", "i=gallery&amp;mode=manage_albums&amp;action=edit&amp;album_id=$album_id&amp;redirect=album") : '',

	'S_COLS'					=> $album_config['cols_per_page'],
	'S_COL_WIDTH'				=> (100/$album_config['cols_per_page']) . '%',
	'ALBUM_JUMPBOX'				=> $album_jumpbox,
	'S_ALBUM_ACTION'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id"),
	'TARGET_BLANK' 				=> ($album_config['fullpic_popup']) ? 'target="_blank"' : '',

	'SORT_TIME'					=> ($sort_method == 'image_time') ? 'selected="selected"' : '',
	'SORT_IMAGE_TITLE'			=> ($sort_method == 'image_name') ? 'selected="selected"' : '',
	'SORT_USERNAME' 			=> ($sort_method == 'image_username') ? 'selected="selected"' : '',
	'SORT_VIEW'					=> ($sort_method == 'image_view_count') ? 'selected="selected"' : '',

	'SORT_RATING_OPTION'		=> $sort_rating_option,
	'SORT_COMMENTS_OPTION'		=> $sort_comments_option,
	'SORT_NEW_COMMENT_OPTION'	=> $sort_new_comment_option,
	'SORT_ASC'					=> ($sort_order == 'ASC') ? 'selected="selected"' : '',
	'SORT_DESC'					=> ($sort_order == 'DESC') ? 'selected="selected"' : '',
	'S_AUTH_LIST'				=> $auth_list,

	'U_RETURN_LINK'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx"),
	'S_RETURN_LINK'				=> $user->lang['GALLERY'],
));

// Output page
$page_title = $user->lang['VIEW_ALBUM'] . ' &bull; ' . $album_data['album_name'];

page_header($page_title);

$template->set_filenames(array(
	'body' => 'gallery_album_body.html')
);

page_footer();
?>