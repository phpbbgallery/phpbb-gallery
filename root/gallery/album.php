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
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');


//
// Get general album information
//
include($phpbb_root_path . 'gallery/includes/common.'.$phpEx);


// ------------------------------------
// Check the request
// ------------------------------------
if(!$album_id = request_var('album_id', request_var('id', 0)))
{
	trigger_error($user->lang['NO_ALBUM_SPECIFIED'], E_USER_WARNING);
}

if ($album_id == PERSONAL_GALLERY)
{
	redirect(append_sid($phpbb_root_path . "gallery/album_personal.$phpEx"));
}
$moderators_list = '';
$total_pics = 0;
$auth_data = $album_data = $catrows = array();
/**
* Get this cat info
*/
$album_data = get_album_info($album_id);
$album_user_access = album_user_access($album_data['album_id'], $album_data, 1, 0, 0, 0, 0, 0);
if ($album_user_access['view'] == 1)
{
	$auth_data = album_user_access($album_id, $row, 1, 1, 1, 1, 1, 1);
	$total_pics = $album_data['count'];
}
/**
* Build Auth List
*/
$auth_key = array_keys($auth_data);
$auth_list = '';
for ($i = 0; $i < (count($auth_data) - 1); $i++)// ignore MODERATOR in this loop
{// we should skip a loop if RATE and COMMENT is disabled
	if((($album_config['rate'] == 0) && ($auth_key[$i] == 'rate')) || (($album_config['comment'] == 0) && ($auth_key[$i] == 'comment')))
	{
		continue;
	}
	$auth_list .= ($auth_data[$auth_key[$i]] == 1) ? $user->lang['ALBUM_'. strtoupper($auth_key[$i]) .'_CAN'] : $user->lang['ALBUM_'. strtoupper($auth_key[$i]) .'_CANNOT'];
	$auth_list .= '<br />';
}
/**
* send cheaters home
*/
if(!$album_user_access['view'])
{
	if ($user->data['is_bot'])
	{
		redirect(append_sid("{$phpbb_root_path}gallery/index.$phpEx"));
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
if (empty($album_data))
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}

/**
* Build Album-Index
*/
include($phpbb_root_path . 'gallery/includes/functions_display.' . $phpEx);
if ($album_id <> 0)
{
	generate_album_nav($album_data);
}
if ($album_data['album_type'] == 2)
{/* we just do this, when we have images */
	if (($user->data['user_type'] == USER_FOUNDER) || ($auth_data['moderator'] == 1))
	{
		$template->assign_vars(array(
			'U_MCP'	=> append_sid($phpbb_root_path . "gallery/mcp.$phpEx?album_id=$album_id"),
		));
	}
	$grouprows = array();
	$moderators_list = '';
	if ($album_data['album_moderator_groups'] <> '')
	{// Get the namelist of moderator usergroups
		$sql = 'SELECT group_id, group_name, group_type
				FROM ' . GROUPS_TABLE . '
				WHERE group_type <> ' . GROUP_HIDDEN . '
					AND group_id IN (' . $album_data['album_moderator_groups'] . ')
				ORDER BY group_name ASC';
		$result = $db->sql_query($sql);

		while( $row = $db->sql_fetchrow($result) )
		{
			$grouprows[] = $row;
		}

		if (count($grouprows) > 0)
		{
			for ($j = 0; $j < count($grouprows); $j++)
			{
				$group_name = ($grouprows[$j]['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $grouprows[$j]['group_name']] : $grouprows[$j]['group_name'];
				$group_link = '<a href="'. append_sid("{$phpbb_root_path}memberlist.$phpEx?mode=group&g=" . $grouprows[$j]['group_id']) . '">' . $group_name . '</a>';
				$moderators_list .= ($moderators_list == '') ? $group_link : ', ' . $group_link;
			}
		}
	}
	if (empty($moderators_list))
	{
		$moderators_list = $user->lang['NONE'];
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
		$pic_approval_sql = ' AND p.image_approval = 1';
		if (($album_data['album_approval'] <> ALBUM_USER) && (($user->data['user_type'] == USER_FOUNDER) || (($auth_data['moderator'] == 1) && ($album_data['album_approval'] == ALBUM_MOD))))
		{
				$pic_approval_sql = '';
		}

		$sql = 'SELECT p.*, u.user_id, u.username, u.user_colour, r.rate_image_id, AVG(r.rate_point) AS rating, COUNT(DISTINCT c.comment_id) AS comments, MAX(c.comment_id) as new_comment
			FROM ' . GALLERY_IMAGES_TABLE . ' AS p
				LEFT JOIN ' . USERS_TABLE . ' AS u
					ON p.image_user_id = u.user_id
				LEFT JOIN ' . GALLERY_RATES_TABLE . ' AS r
					ON p.image_id = r.rate_image_id
				LEFT JOIN ' . GALLERY_COMMENTS_TABLE . ' AS c
					ON p.image_id = c.comment_image_id
				WHERE p.image_album_id = ' . $album_id . $pic_approval_sql . ' 
				GROUP BY p.image_id
				ORDER BY ' . $sort_method . ' ' . $sort_order . ' 
				LIMIT ' . $limit_sql;
		$result = $db->sql_query($sql);

		$picrow = array();

		while( $row = $db->sql_fetchrow($result) )
		{
			$picrow[] = $row;
		}
		for ($i = 0 ; $i < count($picrow); $i++ )
		{
			if ($picrow[$i]['image_approval'] == 0 ) $tot_unapproved++ ;
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

				if(!$picrow[$j]['rating'])
				{
					$picrow[$j]['rating'] = $user->lang['NOT_RATED'];
				}
				else
				{
					$picrow[$j]['rating'] = round($picrow[$j]['rating'], 2);
				}

				$approval_link = false;
				if ($album_data['album_approval'] <> ALBUM_USER)
				{
					if (($user->data['user_type'] == USER_FOUNDER) || (($auth_data['moderator'] == 1) && ($album_data['album_approval'] == ALBUM_MOD)))
					{
						$approval_mode = ($picrow[$j]['image_approval'] == 0) ? 'approval' : 'unapproval';
						$approval_link = '<a href="'. append_sid($phpbb_root_path . "gallery/mcp.$phpEx?mode=$approval_mode&amp;image_id=" . $picrow[$j]['image_id']) . '">';
						$approval_link .= ($picrow[$j]['image_approval'] == 0) ? '<b>' . $user->lang['APPROVE'] . '</b>' : $user->lang['UNAPPROVE'];
						$approval_link .= '</a>';
					}
				}

				$message_parser				= new parse_message();
				$message_parser->message	= $picrow[$j]['image_desc'];
				$message_parser->decode_message($picrow[$j]['image_desc_uid']);
				$template->assign_block_vars('picrow.piccol', array(
					'U_PIC'			=> ($album_config['fullpic_popup']) ? append_sid($phpbb_root_path . "gallery/image.$phpEx?pic_id=" . $picrow[$j]['image_id']) : append_sid($phpbb_root_path . "gallery/image_page.$phpEx?image_id=" . $picrow[$j]['image_id']),
					'THUMBNAIL'		=> append_sid("thumbnail.$phpEx?pic_id=" . $picrow[$j]['image_id']),
					'DESC'			=> $message_parser->message,
					'APPROVAL'		=> $approval_link,
				));

				$template->assign_block_vars('picrow.pic_detail', array(
					'TITLE'		=> $picrow[$j]['image_name'],
					'POSTER'	=> get_username_string('full', $picrow[$j]['user_id'], ($picrow[$j]['user_id'] <> ANONYMOUS) ? $picrow[$j]['username'] : $user->lang['GUEST'], $picrow[$j]['user_colour']),
					'TIME'		=> $user->format_date($picrow[$j]['image_time']),
					'VIEW'		=> $picrow[$j]['image_view_count'],
					'RATING'	=> ($album_config['rate'] == 1) ? ( '<a href="' . append_sid($phpbb_root_path . "gallery/image_page.$phpEx?image_id=" . $picrow[$j]['image_id']) . '#rating">' . $user->lang['RATING'] . '</a>: ' . $picrow[$j]['rating'] . '<br />') : '',
					'COMMENTS'	=> ($album_config['comment'] == 1) ? ( '<a href="' . append_sid($phpbb_root_path . "gallery/image_page.$phpEx?image_id=" . $picrow[$j]['image_id']) . '#comments">' . $user->lang['COMMENTS'] . '</a>: ' . $picrow[$j]['comments'] . '<br />') : '',
					'EDIT'		=> ( ( $auth_data['edit'] && ($picrow[$j]['image_user_id'] == $user->data['user_id']) ) || ($auth_data['moderator'] && ($album_data['album_edit_level'] <> ALBUM_ADMIN) ) || ($user->data['user_type'] == USER_FOUNDER) ) ? '<a href="' . append_sid($phpbb_root_path . "gallery/edit.$phpEx?pic_id=" . $picrow[$j]['image_id']) . '">' . $user->lang['EDIT_IMAGE'] . '</a>' : '',
					'DELETE'	=> ( ( $auth_data['delete'] && ($picrow[$j]['image_user_id'] == $user->data['user_id']) ) || ($auth_data['moderator'] && ($album_data['album_delete_level'] <> ALBUM_ADMIN) ) || ($user->data['user_type'] == USER_FOUNDER) ) ? '<a href="' . append_sid($phpbb_root_path . "gallery/image_delete.$phpEx?id=" . $picrow[$j]['image_id']) . '">' . $user->lang['DELETE_IMAGE'] . '</a>' : '',
					'MOVE'		=> ($auth_data['moderator']) ? '<a href="' . append_sid($phpbb_root_path . "gallery/mcp.$phpEx?mode=move&amp;image_id=" . $picrow[$j]['image_id']) . '">' . $user->lang['MOVE'] . '</a>' : '',
					'LOCK'		=> ($auth_data['moderator']) ? '<a href="' . append_sid($phpbb_root_path . "gallery/mcp.$phpEx?mode=" . (($picrow[$j]['image_lock'] == 0) ? 'lock' : 'unlock') . "&amp;image_id=" . $picrow[$j]['image_id']) . '">' . (($picrow[$j]['image_lock'] == 0) ? $user->lang['LOCK'] : $user->lang['UNLOCK']) . '</a>' : '',
					'IP'		=> ($user->data['user_type'] == USER_FOUNDER) ? $user->lang['IP'] . ': <a href="http://www.nic.com/cgi-bin/whois.cgi?query=' . $picrow[$j]['image_user_ip'] . '" target="_blank">' . $picrow[$j]['image_user_ip'] . '</a><br />' : ''
				));
			}
		}

		$template->assign_vars(array(
			'PAGINATION'	=> generate_pagination(append_sid($phpbb_root_path . "gallery/album.$phpEx?id=$album_id&amp;sort_method=$sort_method&amp;sort_order=$sort_order"), $total_pics, $pics_per_page, $start),
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
		$sort_rating_option = '<option value="rating" ';
		$sort_rating_option .= ($sort_method == 'rating') ? 'selected="selected"' : '';
		$sort_rating_option .= '>' . $user->lang['RATING'] . '</option>';
	}
	if ($album_config['comment'] == 1)
	{
		$sort_comments_option = '<option value="comments" ';
		$sort_comments_option .= ($sort_method == 'comments') ? 'selected="selected"' : '';
		$sort_comments_option .= '>' . $user->lang['COMMENTS'] . '</option>';

		$sort_new_comment_option = '<option value="new_comment" ';
		$sort_new_comment_option .= ($sort_method == 'new_comment') ? 'selected="selected"' : '';
		$sort_new_comment_option .= '>' . $user->lang['NEW_COMMENT'] . '</option>';
	}
}
else
{
	$tot_unapproved = 0;
	$sort_method = $album_config['sort_method'];
	$sort_order = $album_config['sort_order'];
	$sort_rating_option = '';
	$sort_comments_option = '';
	$sort_new_comment_option = '';
}
/**
* Build Jumpbox
*/
$album_jumpbox  = '<form name="jumpbox" action="' . append_sid($phpbb_root_path . "gallery/album.$phpEx") . '" method="get"><fieldset class="jumpbox">';
$album_jumpbox .= '<label>' . $user->lang['JUMP_TO'] . ':</label><select name="id" onChange="forms[\'jumpbox\'].submit()">';
$album_jumpbox .= make_album_jumpbox($album_id);
$album_jumpbox .= '</select>';
$album_jumpbox .= '&nbsp;<input type="submit" class="button2" value="' . $user->lang['GO'] . '" />';
$album_jumpbox .= '<input type="hidden" name="sid" value="' . $user->data['session_id'] . '" />';
$album_jumpbox .= '</fieldset></form>';

$template->assign_vars(array(
	'S_MODE'					=> $album_data['album_type'],
	'U_VIEW_CAT' 				=> append_sid($phpbb_root_path . "gallery/album.$phpEx?id=$album_id"),
	'CAT_TITLE' 				=> $album_data['album_name'],
	'MODERATORS' 				=> $moderators_list,
	'U_UPLOAD_PIC' 				=> append_sid($phpbb_root_path . "gallery/upload.$phpEx?album_id=$album_id"),
	'WAITING' 					=> ($tot_unapproved == 0) ? '' : $tot_unapproved . $user->lang['WAITING_FOR_APPROVAL'],

	'S_COLS' 					=> $album_config['cols_per_page'],
	'S_COL_WIDTH' 				=> (100/$album_config['cols_per_page']) . '%',
	'ALBUM_JUMPBOX' 			=> $album_jumpbox,
	'S_ALBUM_ACTION' 			=> append_sid($phpbb_root_path . "gallery/album.$phpEx?id=$album_id"),
	'TARGET_BLANK' 				=> ($album_config['fullpic_popup']) ? 'target="_blank"' : '',

	'SORT_TIME' 				=> ($sort_method == 'image_time') ? 'selected="selected"' : '',
	'SORT_PIC_TITLE' 			=> ($sort_method == 'image_name') ? 'selected="selected"' : '',
	'SORT_USERNAME' 			=> ($sort_method == 'username') ? 'selected="selected"' : '',
	'SORT_VIEW' 				=> ($sort_method == 'image_view_count') ? 'selected="selected"' : '',

	'SORT_RATING_OPTION' 		=> $sort_rating_option,
	'SORT_COMMENTS_OPTION' 		=> $sort_comments_option,
	'SORT_NEW_COMMENT_OPTION' 	=> $sort_new_comment_option,
	'SORT_ASC' 					=> ($sort_order == 'ASC') ? 'selected="selected"' : '',
	'SORT_DESC' 				=> ($sort_order == 'DESC') ? 'selected="selected"' : '',
	'S_AUTH_LIST' 				=> $auth_list,

	'U_RETURN_LINK' 			=> append_sid($phpbb_root_path . "gallery/index.$phpEx"),
	'S_RETURN_LINK' 			=> $user->lang['GALLERY'],
));

// Output page
$page_title = $user->lang['VIEW_ALBUM'] . ' &bull; ' . $album_data['album_name'];

page_header($page_title);

$template->set_filenames(array(
	'body' => 'gallery_album_body.html')
);

page_footer();
?>