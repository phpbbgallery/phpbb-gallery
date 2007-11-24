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
include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');


//
// Get general album information
//
include($album_root_path . 'includes/common.'.$phpEx);


// ------------------------------------
// Check the request
// ------------------------------------
if(!$album_id = request_var('id', 0))
{
	trigger_error($user->lang['NO_ALBUM_SPECIFIED'], E_USER_WARNING);
}

if ($album_id == PERSONAL_GALLERY)
{
	redirect(append_sid("album_personal.$phpEx"));
}
$album_data = get_album_info($album_id);

if ($album_data['album_type'] == 1)
{
	//subalbums
	$mode = 'albums';
}
else if ($album_data['album_type'] == 2)
{
	//images
	$mode = 'images';
}
if ($mode == 'albums')
{//we have this twice, we could build a function to keep the bytes down (see gallery/index.php)
	$sql = 'SELECT ga.*, COUNT(p.pic_id) AS count
			FROM ' . GALLERY_ALBUMS_TABLE . ' AS ga
				LEFT JOIN ' . GALLERY_IMAGES_TABLE . ' AS p ON ga.album_id = p.pic_cat_id
			WHERE ga.album_id <> 0
				AND ga.parent_id = ' . $album_data['album_id'] . '
			GROUP BY ga.album_id
			ORDER BY ga.left_id ASC';
	$result = $db->sql_query($sql);

	$album = array();

	while( $row = $db->sql_fetchrow($result) )
	{
		$album_user_access = album_user_access($row['album_id'], $row, 1, 0, 0, 0, 0, 0); // VIEW
		if ($album_user_access['view'] == 1)
		{
			$album[] = $row;
		}
	}

	$allowed_cat = '';

	//
	// $albums now stores all categories which this user can view. Dump them out!
	//
	for ($i = 0; $i < count($album); $i++)
	{
		// --------------------------------
		// Build allowed category-list (for recent pics after here)
		// --------------------------------

		$allowed_cat .= ($allowed_cat == '') ? $album[$i]['album_id'] : ',' . $album[$i]['album_id'];


		// --------------------------------
		// Build moderators list
		// --------------------------------

		$l_moderators = '';
		$moderators_list = '';

		$grouprows= array();

		if( $album[$i]['album_moderator_groups'] <> '')
		{
			// We have usergroup_ID, now we need usergroup name
			$sql = 'SELECT group_id, group_name, group_type
					FROM ' . GROUPS_TABLE . '
					WHERE group_type <> ' . GROUP_HIDDEN . '
						AND group_id IN (' . $album[$i]['album_moderator_groups'] . ')
					ORDER BY group_name ASC';
			$result = $db->sql_query($sql);

			while( $row = $db->sql_fetchrow($result) )
			{
				$grouprows[] = $row;
			}
		}

		if( count($grouprows) > 0 )
		{
			$l_moderators = $user->lang['MODERATORS'];

			for ($j = 0; $j < count($grouprows); $j++)
			{
				$group_name = ($grouprows[$j]['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $grouprows[$j]['group_name']] : $grouprows[$j]['group_name'];
				$group_link = '<a href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx?mode=group&g=" . $grouprows[$j]['group_id']) . '">' . $group_name . '</a>';

				$moderators_list .= ($moderators_list == '') ? $group_link : ', ' . $group_link;
			}
		}


		// ------------------------------------------
		// Get Last Pic of this Category
		// ------------------------------------------

		if ($album[$i]['count'] == 0)
		{
			//
			// Oh, this category is empty
			//
			$last_pic_info = $user->lang['NO_IMAGES'];
			$u_last_pic = '';
			$last_pic_title = '';
		}
		else
		{
			// ----------------------------
			// Check Pic Approval
			// ----------------------------

			if (($album[$i]['album_approval'] == ALBUM_ADMIN) || ($album[$i]['album_approval'] == ALBUM_MOD))
			{
				$pic_approval_sql = 'AND p.pic_approval = 1';
			}
			else
			{
				$pic_approval_sql = '';
			}


			// ----------------------------
			// OK, we may do a query now...
			// ----------------------------

			$sql = 'SELECT p.pic_id, p.pic_title, p.pic_user_id, p.pic_username, p.pic_time, p.pic_cat_id, u.user_id, u.username, u.user_colour
					FROM ' . GALLERY_IMAGES_TABLE . ' AS p
						LEFT JOIN ' . USERS_TABLE . ' AS u ON p.pic_user_id = u.user_id
					WHERE p.pic_cat_id = ' . $album[$i]['album_id'] . ' ' . $pic_approval_sql . ' 
						ORDER BY p.pic_time DESC
						LIMIT 1';
			$result = $db->sql_query($sql);
			$lastrow = $db->sql_fetchrow($result);

			$last_pic_info = '';
			$last_pic_info .= '<dfn>' . $user->lang['LAST_IMAGE'] . '</dfn> ';
			if( !isset($album_config['last_pic_title_length']) )
			{
				$album_config['last_pic_title_length'] = 25;
			}
			if (strlen($lastrow['pic_title']) > $album_config['last_pic_title_length'])
			{
				$lastrow['pic_title'] = substr($lastrow['pic_title'], 0, $album_config['last_pic_title_length']) . '...';
			}
			$last_pic_info .= '<a href="' . append_sid("{$album_root_path}image_page.$phpEx?id=" . $lastrow['pic_id']) . '" style="font-weight: bold;">';
			$last_pic_info .= $lastrow['pic_title'] . '</a><br />' . $user->lang['POST_BY_AUTHOR'] . ' ';
			$last_pic_info .= get_username_string('full', $lastrow['user_id'], ($lastrow['user_id'] <> ANONYMOUS) ? $lastrow['username'] : $user->lang['GUEST'], $lastrow['user_colour']);
	//		$last_pic_info .= '<a href="' . append_sid("../memberlist.$phpEx?mode=viewprofile&amp;u=". $lastrow['user_id']) .'" style="color: #' . $user->data['user_colour'] . ';" class="username-coloured">'. $lastrow['username'] .'</a> ';
			$last_pic_info .= '<a href="' . append_sid("{$album_root_path}image_page.$phpEx?id=" . $lastrow['pic_id']) . '"><img src="' . $phpbb_root_path . 'styles/prosilver/imageset/icon_topic_latest.gif" width="11" height="9" alt="' . $user->lang['VIEW_THE_LATEST_IMAGE'] . '" title="' . $user->lang['VIEW_THE_LATEST_IMAGE'] . '" /></a><br />';
			$last_pic_info .= $user->lang['POSTED_ON_DATE'] . ' ' . $user->format_date($lastrow['pic_time']);
		}
		if ($album[$i]['left_id'] + 1 != $album[$i]['right_id'])
		{
			$folder_image = 'forum_read_subforum';
			$folder_alt = 'no';
			$l_subalbums = $user->lang['SUBALBUM'];
			if ($album[$i]['left_id'] + 3 != $album[$i]['right_id'])
			{
				$l_subalbums = $user->lang['SUBALBUMS'];
			}
		}
		else
		{
			$folder_image = 'forum_read';
			$l_subalbums = '';
			$folder_alt = 'no';
		}
		//$folder_image = ($album[$i]['left_id'] + 1 != $album[$i]['right_id']) ? '<img src="images/icon_subfolder.gif" alt="' . $user->lang['SUBFORUM'] . '" />' : '<img src="images/icon_folder.gif" alt="' . $user->lang['FOLDER'] . '" />';
		// END of Last Pic

		// ------------------------------------------
		// Parse to template the info of the current Category
		// ------------------------------------------

		$template->assign_block_vars('albumrow', array(
			'U_VIEW_CAT' 		=> append_sid("album.$phpEx?id=" . $album[$i]['album_id']),
			'CAT_TITLE' 		=> $album[$i]['album_name'],
			'ALBUM_FOLDER_IMG_SRC'		=> $user->img($folder_image, $folder_alt, false, '', 'src'),
			'SUBALBUMS'			=> get_album_children($album[$i]['album_id']),
			'CAT_DESC' 			=> generate_text_for_display($album[$i]['album_desc'], $album[$i]['album_desc_uid'], $album[$i]['album_desc_bitfield'], $album[$i]['album_desc_options']),
			'L_MODERATORS' 		=> $l_moderators,
			'L_SUBALBUMS' 		=> $l_subalbums,
			'MODERATORS' 		=> $moderators_list,
			'PICS' 				=> $album[$i]['count'],
			'LAST_PIC_INFO' 	=> $last_pic_info)
		);
	}
	$template->assign_vars(array(
		'S_MODE'					=> $album_data['album_type'],
	));
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $user->lang['GALLERY'],
		'U_VIEW_FORUM'	=> append_sid("{$album_root_path}index.$phpEx"),
	));

	if ($album_id == PERSONAL_GALLERY)
	{
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['PERSONAL_ALBUMS'],
			'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album_personal_index.$phpEx"),
		));

		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> sprintf($user->lang['PERSONAL_ALBUM_OF_USER'], $user->data['username']),
			'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album_personal.$phpEx", 'user_id=' . $user->data['user_id']),
		));
	}
	else
	{
		generate_album_nav($album_data);
	}
}
/**
* the old part of the file
*/
else if ($mode == 'images')
{
	// ------------------------------------
	// Get this cat info
	// ------------------------------------
	$sql = 'SELECT ga.*, COUNT(p.pic_id) AS count
			FROM ' . GALLERY_ALBUMS_TABLE . ' AS ga LEFT JOIN ' . GALLERY_IMAGES_TABLE . ' AS p ON ga.album_id = p.pic_cat_id
			WHERE ga.album_id <> 0
				GROUP BY ga.album_id
				ORDER BY ga.left_id';
	$result = $db->sql_query($sql);

	$thiscat = $catrows = array();

	while( $row = $db->sql_fetchrow($result) )
	{
		$album_user_access = album_user_access($row['album_id'], $row, 1, 0, 0, 0, 0, 0); // VIEW
		if ($album_user_access['view'] == 1)
		{
			$catrows[] = $row;

			if( $row['album_id'] == $album_id )
			{
				$thiscat = $row;
				$auth_data = album_user_access($album_id, $row, 1, 1, 1, 1, 1, 1); // ALL
				$total_pics = $thiscat['count'];
			}
		}
	}

	//
	// END cat info
	//


	// ------------------------------------
	// Check permissions
	// ------------------------------------
	if(!$auth_data['view'])
	{
		if (!$user->data['is_registered'])
		{
			if ($user->data['is_bot'])
			{
				redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
			}
			login_box();
		}
		else
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}
	}
	//
	// END check permissions
	//


	if (empty($thiscat))
	{
		trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
	}


	// ------------------------------------
	// Build Auth List
	// ------------------------------------
	$auth_key = array_keys($auth_data);

	$auth_list = '';
	for ($i = 0; $i < (count($auth_data) - 1); $i++) // ignore MODERATOR in this loop
	{
		//
		// we should skip a loop if RATE and COMMENT is disabled
		//
		if( ( ($album_config['rate'] == 0) && ($auth_key[$i] == 'rate') ) || ( ($album_config['comment'] == 0) && ($auth_key[$i] == 'comment') ) )
		{
			continue;
		}

		$auth_list .= ($auth_data[$auth_key[$i]] == 1) ? $user->lang['ALBUM_'. strtoupper($auth_key[$i]) .'_CAN'] : $user->lang['ALBUM_'. strtoupper($auth_key[$i]) .'_CANNOT'];
		$auth_list .= '<br />';
	}

	// add Moderator Control Panel here
	if (($user->data['user_type'] == USER_FOUNDER) || ($auth_data['moderator'] == 1))
	{
		$template->assign_vars(array(
			'U_MCP'	=> append_sid("mcp.$phpEx?album_id=$album_id"))
		);
	}

	//
	// END Auth List
	//


	// ------------------------------------
	// Build Moderators List
	// ------------------------------------

	$grouprows = array();
	$moderators_list = '';

	if ($thiscat['album_moderator_groups'] <> '')
	{
		// Get the namelist of moderator usergroups
		$sql = 'SELECT group_id, group_name, group_type
				FROM ' . GROUPS_TABLE . '
				WHERE group_type <> ' . GROUP_HIDDEN . '
					AND group_id IN (' . $thiscat['album_moderator_groups'] . ')
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
	//
	// END Moderator List
	//


	// ------------------------------------
	// Build the thumbnail page
	// ------------------------------------

	$start = request_var('start', 0);
	$sort_method = request_var('sort_method', $album_config['sort_method']);
	$sort_order = request_var('sort_order', $album_config['sort_order']);
	$pics_per_page = $album_config['rows_per_page'] * $album_config['cols_per_page'];
	$tot_unapproved = 0;

	if ($total_pics > 0)
	{
		$limit_sql = ($start == 0) ? $pics_per_page : $start .','. $pics_per_page;
		$pic_approval_sql = 'AND p.pic_approval = 1';
		if ($thiscat['album_approval'] <> ALBUM_USER)
		{
			if (($user->data['user_type'] == USER_FOUNDER) || (($auth_data['moderator'] == 1) && ($thiscat['album_approval'] == ALBUM_MOD)))
			{
				$pic_approval_sql = '';
			}
		}

		$sql = 'SELECT p.*, u.user_id , u.username, u.user_colour, r.rate_pic_id, AVG(r.rate_point) AS rating, COUNT(DISTINCT c.comment_id) AS comments, MAX(c.comment_id) as new_comment
			FROM ' . GALLERY_IMAGES_TABLE . ' AS p
				LEFT JOIN ' . USERS_TABLE . ' AS u ON p.pic_user_id = u.user_id
				LEFT JOIN ' . ALBUM_RATE_TABLE . ' AS r ON p.pic_id = r.rate_pic_id
				LEFT JOIN ' . GALLERY_COMMENTS_TABLE . ' AS c ON p.pic_id = c.comment_pic_id 
			WHERE p.pic_cat_id = ' . $album_id . ' 
			GROUP BY p.pic_id
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
			if ($picrow[$i]['pic_approval'] == 0 ) $tot_unapproved++ ;
		}

		$sql = 'SELECT p.*, u.user_id, u.username, u.user_colour, r.rate_pic_id, AVG(r.rate_point) AS rating, COUNT(DISTINCT c.comment_id) AS comments, MAX(c.comment_id) as new_comment
			FROM ' . GALLERY_IMAGES_TABLE . ' AS p
				LEFT JOIN ' . USERS_TABLE . ' AS u ON p.pic_user_id = u.user_id
				LEFT JOIN ' . ALBUM_RATE_TABLE . ' AS r ON p.pic_id = r.rate_pic_id
				LEFT JOIN ' . GALLERY_COMMENTS_TABLE . ' AS c ON p.pic_id = c.comment_pic_id
				WHERE p.pic_cat_id = ' . $album_id . ' ' . $pic_approval_sql . ' 
				GROUP BY p.pic_id
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

				if(!$picrow[$j]['rating'])
				{
					$picrow[$j]['rating'] = $user->lang['NOT_RATED'];
				}
				else
				{
					$picrow[$j]['rating'] = round($picrow[$j]['rating'], 2);
				}

				$approval_link = false;
				if ($thiscat['album_approval'] <> ALBUM_USER)
				{
					if (($user->data['user_type'] == USER_FOUNDER) || (($auth_data['moderator'] == 1) && ($thiscat['album_approval'] == ALBUM_MOD)))
					{
						$approval_mode = ($picrow[$j]['pic_approval'] == 0) ? 'approval' : 'unapproval';
						$approval_link = '<a href="'. append_sid("mcp.$phpEx?mode=$approval_mode&amp;pic_id=" . $picrow[$j]['pic_id']) . '">';
						$approval_link .= ($picrow[$j]['pic_approval'] == 0) ? '<b>' . $user->lang['APPROVE'] . '</b>' : $user->lang['UNAPPROVE'];
						$approval_link .= '</a>';
					}
				}

				$message_parser				= new parse_message();
				$message_parser->message	= $picrow[$j]['pic_desc'];
				$message_parser->decode_message($picrow[$j]['pic_desc_bbcode_uid']);
				$template->assign_block_vars('picrow.piccol', array(
					'U_PIC'			=> ($album_config['fullpic_popup']) ? append_sid("image.$phpEx?pic_id=" . $picrow[$j]['pic_id']) : append_sid("image_page.$phpEx?id=" . $picrow[$j]['pic_id']),
					'THUMBNAIL'		=> append_sid("thumbnail.$phpEx?pic_id=" . $picrow[$j]['pic_id']),
					'DESC'			=> $message_parser->message,
					'APPROVAL'		=> $approval_link,
				));

				$template->assign_block_vars('picrow.pic_detail', array(
					'TITLE'		=> $picrow[$j]['pic_title'],
					'POSTER'	=> get_username_string('full', $picrow[$j]['user_id'], ($picrow[$j]['user_id'] <> ANONYMOUS) ? $picrow[$j]['username'] : $user->lang['GUEST'], $picrow[$j]['user_colour']),
					'TIME'		=> $user->format_date($picrow[$j]['pic_time']),
					'VIEW'		=> $picrow[$j]['pic_view_count'],
					'RATING'	=> ($album_config['rate'] == 1) ? ( '<a href="' . append_sid("image_page.$phpEx?id=" . $picrow[$j]['pic_id']) . '#rating">' . $user->lang['RATING'] . '</a>: ' . $picrow[$j]['rating'] . '<br />') : '',
					'COMMENTS'	=> ($album_config['comment'] == 1) ? ( '<a href="' . append_sid("image_page.$phpEx?id=" . $picrow[$j]['pic_id']) . '#comments">' . $user->lang['COMMENTS'] . '</a>: ' . $picrow[$j]['comments'] . '<br />') : '',
					'EDIT'		=> ( ( $auth_data['edit'] && ($picrow[$j]['pic_user_id'] == $user->data['user_id']) ) || ($auth_data['moderator'] && ($thiscat['album_edit_level'] <> ALBUM_ADMIN) ) || ($user->data['user_type'] == USER_FOUNDER) ) ? '<a href="' . append_sid("edit.$phpEx?pic_id=" . $picrow[$j]['pic_id']) . '">' . $user->lang['EDIT_IMAGE'] . '</a>' : '',
					'DELETE'	=> ( ( $auth_data['delete'] && ($picrow[$j]['pic_user_id'] == $user->data['user_id']) ) || ($auth_data['moderator'] && ($thiscat['album_delete_level'] <> ALBUM_ADMIN) ) || ($user->data['user_type'] == USER_FOUNDER) ) ? '<a href="' . append_sid("image_delete.$phpEx?id=" . $picrow[$j]['pic_id']) . '">' . $user->lang['DELETE_IMAGE'] . '</a>' : '',
					'MOVE'		=> ($auth_data['moderator']) ? '<a href="' . append_sid("mcp.$phpEx?mode=move&amp;pic_id=" . $picrow[$j]['pic_id']) . '">' . $user->lang['MOVE'] . '</a>' : '',
					'LOCK'		=> ($auth_data['moderator']) ? '<a href="' . append_sid("mcp.$phpEx?mode=" . (($picrow[$j]['pic_lock'] == 0) ? 'lock' : 'unlock') . "&amp;pic_id=" . $picrow[$j]['pic_id']) . '">' . (($picrow[$j]['pic_lock'] == 0) ? $user->lang['LOCK'] : $user->lang['UNLOCK']) . '</a>' : '',
					'IP'		=> ($user->data['user_type'] == USER_FOUNDER) ? $user->lang['IP'] . ': <a href="http://www.nic.com/cgi-bin/whois.cgi?query=' . $picrow[$j]['pic_user_ip'] . '" target="_blank">' . $picrow[$j]['pic_user_ip'] . '</a><br />' : ''
					)
				);
			}
		}

		$template->assign_vars(array(
			'PAGINATION'	=> generate_pagination(append_sid("album.$phpEx?id=$album_id&amp;sort_method=$sort_method&amp;sort_order=$sort_order"), $total_pics, $pics_per_page, $start),
			'PAGE_NUMBER'	=> on_page($total_pics, $pics_per_page, $start),
			)
		);
	}
	else
	{
		$template->assign_block_vars('no_pics', array());
	}
	//
	// END thumbnails table
	//


	// ------------------------------------
	// Build Jumpbox - based on $catrows which was created at the top of this file
	// ------------------------------------
	$album_jumpbox  = '<form name="jumpbox" action="' . append_sid("album.$phpEx") . '" method="get"><fieldset class="jumpbox">';
	$album_jumpbox .= '<label>' . $user->lang['JUMP_TO'] . ':</label><select name="id" onChange="forms[\'jumpbox\'].submit()">';
	for ($i = 0; $i < count($catrows); $i++)
	{
		$album_jumpbox .= '<option value="'. $catrows[$i]['album_id'] .'"';
		$album_jumpbox .= ($catrows[$i]['album_id'] == $album_id) ? 'selected="selected"' : '';
		$album_jumpbox .= '>' . $catrows[$i]['album_name'] .'</option>';
	}
	$album_jumpbox .= '</select>';
	$album_jumpbox .= '&nbsp;<input type="submit" class="button2" value="' . $user->lang['GO'] . '" />';
	$album_jumpbox .= '<input type="hidden" name="sid" value="' . $user->data['session_id'] . '" />';
	$album_jumpbox .= '</fieldset></form>';
	//
	// END build jumpbox
	//


	// ------------------------------------
	// additional sorting options
	// ------------------------------------

	$sort_rating_option = '';
	$sort_comments_option = '';
	if( $album_config['rate'] == 1 )
	{
		$sort_rating_option = '<option value="rating" ';
		$sort_rating_option .= ($sort_method == 'rating') ? 'selected="selected"' : '';
		$sort_rating_option .= '>' . $user->lang['RATING'] . '</option>';
	}
	if( $album_config['comment'] == 1 )
	{
		$sort_comments_option = '<option value="comments" ';
		$sort_comments_option .= ($sort_method == 'comments') ? 'selected="selected"' : '';
		$sort_comments_option .= '>' . $user->lang['COMMENTS'] . '</option>';

		$sort_new_comment_option = '<option value="new_comment" ';
		$sort_new_comment_option .= ($sort_method == 'new_comment') ? 'selected="selected"' : '';
		$sort_new_comment_option .= '>' . $user->lang['NEW_COMMENT'] . '</option>';
	}

	$template->assign_vars(array(
		'S_MODE'					=> $album_data['album_type'],
		'U_VIEW_CAT' 				=> append_sid("album.$phpEx?id=$album_id"),
		'CAT_TITLE' 				=> $thiscat['album_name'],
		'MODERATORS' 				=> $moderators_list,
		'U_UPLOAD_PIC' 				=> append_sid("upload.$phpEx?album_id=$album_id"),
		'WAITING' 					=> ($tot_unapproved == 0) ? '' : $tot_unapproved . $user->lang['WAITING_FOR_APPROVAL'],

		'S_COLS' 					=> $album_config['cols_per_page'],
		'S_COL_WIDTH' 				=> (100/$album_config['cols_per_page']) . '%',
		'ALBUM_JUMPBOX' 			=> $album_jumpbox,
		'S_ALBUM_ACTION' 			=> append_sid("album.$phpEx?id=$album_id"),
		'TARGET_BLANK' 				=> ($album_config['fullpic_popup']) ? 'target="_blank"' : '',

		'SORT_TIME' 				=> ($sort_method == 'pic_time') ? 'selected="selected"' : '',
		'SORT_PIC_TITLE' 			=> ($sort_method == 'pic_title') ? 'selected="selected"' : '',
		'SORT_USERNAME' 			=> ($sort_method == 'username') ? 'selected="selected"' : '',
		'SORT_VIEW' 				=> ($sort_method == 'pic_view_count') ? 'selected="selected"' : '',

		'SORT_RATING_OPTION' 		=> $sort_rating_option,
		'SORT_COMMENTS_OPTION' 		=> $sort_comments_option,
		'SORT_NEW_COMMENT_OPTION' 	=> $sort_new_comment_option,
		'SORT_ASC' 					=> ($sort_order == 'ASC') ? 'selected="selected"' : '',
		'SORT_DESC' 				=> ($sort_order == 'DESC') ? 'selected="selected"' : '',
		'S_AUTH_LIST' 				=> $auth_list,

		'U_RETURN_LINK' 			=> append_sid("./index.$phpEx"),
		'S_RETURN_LINK' 			=> $user->lang['ALBUM'])
	);

	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $user->lang['GALLERY'],
		'U_VIEW_FORUM'	=> append_sid("{$album_root_path}index.$phpEx"),
	));

	if ($album_id == PERSONAL_GALLERY)
	{
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['PERSONAL_ALBUMS'],
			'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album_personal_index.$phpEx"),
		));

		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> sprintf($user->lang['PERSONAL_ALBUM_OF_USER'], $user->data['username']),
			'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album_personal.$phpEx", 'user_id=' . $user->data['user_id']),
		));
	}
	else
	{
		generate_album_nav($album_data);
	}
}

// Output page
$page_title = $user->lang['VIEW_ALBUM'] . ' - ' . $album_data['album_name'];

page_header($page_title);

$template->set_filenames(array(
	'body' => 'gallery_album_body.html')
);

page_footer();
?>