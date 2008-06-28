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

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');

include_once("{$phpbb_root_path}{$gallery_root_path}includes/common.$phpEx");
include_once("{$phpbb_root_path}{$gallery_root_path}includes/permissions.$phpEx");
$album_access_array = get_album_access_array();
/**
* Check the request
*/
$image_id = request_var('image_id', request_var('id', 0));
if (!$image_id)
{
	trigger_error($user->lang['NO_IMAGE_SPECIFIED'], E_USER_WARNING);
}
// ------------------------------------
// Salting the form...yumyum ...
// ------------------------------------
add_form_key('gallery');

/**
* Get the image info
*/
$image_data = get_image_info($image_id);
$album_id = $image_data['image_album_id'];
$user_id = $image_data['image_user_id'];
if (empty($image_data) || !file_exists($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['image_filename']))
{
	trigger_error($user->lang['IMAGE_NOT_EXIST'], E_USER_WARNING);
}
/**
* Get the album info of the images album
*/
$album_data = get_album_info($album_id);
if (empty($album_data))
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}
if ($album_data['album_user_id'] > 0)
{
	$album_access_array[$album_id] = $album_access_array[(($album_data['album_user_id'] == $user->data['user_id']) ? -2 : -3)];
}

/**
* Check the permissions
*/
if ($album_access_array[$album_id]['i_view'] != 1)
{
	if (!$user->data['is_registered'])
	{
		login_box("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx?album_id=$album_id&amp;image_id=$image_id", $user->lang['LOGIN_INFO']);
	}
	else
	{
		trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
	}
}

// ------------------------------------
// Check Pic Rating
// ------------------------------------

$already_rated = false;

if (($album_config['rate'] <> 0) && $user->data['is_registered'])
{
	$sql = 'SELECT *
		FROM ' . GALLERY_RATES_TABLE . '
		WHERE rate_image_id = ' . $image_id . '
			AND rate_user_id = ' . $user->data['user_id'] . '
		LIMIT 1';

	$result = $db->sql_query($sql);

	if ($db->sql_affectedrows($result) > 0)
	{
		$already_rated = true;
	}
}

// ------------------------------------
// Check Pic Approval
// ------------------------------------
if (($album_access_array[$album_id]['a_moderate'] != 1) && (!$image_data['image_status'] != 1))
{
	trigger_error($user->lang['NOT_AUTHORISED']);
}

// ------------------------------------
// Posting Comments & Rating
// ------------------------------------

if (isset($_POST['rate']))
{
	// Check the salt... yumyum
	if (!check_form_key('gallery'))
	{
		trigger_error('FORM_INVALID');
	}

	include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

	if (isset($_POST['rate']))
	{
		if (!$album_config['rate'] || $album_access_array[$album_id]['i_rate'] != 1)
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}
		else if ($already_rated)
		{
			trigger_error($user->lang['ALREADY_RATED'], E_USER_WARNING);
		}
		$rate_point = request_var('rate', 0);
		if( ($rate_point <= 0) || ($rate_point > $album_config['rate_scale']) )
		{
			trigger_error($user->lang['OUT_OF_RANGE_VALUE'], E_USER_WARNING);
		}
		$rate_user_id = $user->data['user_id'];
		$rate_user_ip = $user->ip;
		// --------------------------------
		// Insert into the DB
		// --------------------------------
		$sql_ary = array(
			'rate_image_id'	=> $image_id,
			'rate_user_id'	=> $rate_user_id,
			'rate_user_ip'	=> $rate_user_ip,
			'rate_point'	=> $rate_point,
		);
		$db->sql_query('INSERT INTO ' . GALLERY_RATES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
		$sql = 'SELECT rate_image_id, COUNT(rate_user_ip) image_rates, AVG(rate_point) image_rate_avg, SUM(rate_point) image_rate_points
			FROM ' . GALLERY_RATES_TABLE . "
			WHERE rate_image_id = $image_id
			GROUP BY rate_image_id";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
				SET image_rates = ' . $row['image_rates'] . ',
					image_rate_points = ' . $row['image_rate_points'] . ',
					image_rate_avg = ' . round($row['image_rate_avg'], 2) * 100 . '
				WHERE image_id = ' . $row['rate_image_id'];
			$db->sql_query($sql);
		}
		$db->sql_freeresult($result);

		// --------------------------------
		// Complete... now send a message to user
		// --------------------------------
		$message = $user->lang['RATING_SUCCESSFUL'];

		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx?album_id=$album_id&amp;image_id=$image_id&rate_set=1#rating") . '">',
		));
		$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx?album_id=$album_id") . "\">", "</a>");

		$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx") . "\">", "</a>");
		trigger_error($message, E_USER_WARNING);
	}
}

/*
+----------------------------------------------------------
| Main work here...
+----------------------------------------------------------
*/
$previous_id = $next_id = $last_id = 0;
$do_next = false;
$sort_method = request_var('sort_method', $album_config['sort_method']);
$sort_order = request_var('sort_order', $album_config['sort_order']);
$image_approval_sql = ' AND image_status = 1';
if ($album_access_array[$album_id]['a_moderate'] == 1)
{
	$image_approval_sql = '';
}
$sql = 'SELECT *
	FROM ' . GALLERY_IMAGES_TABLE . '
	WHERE image_album_id = ' . $album_id . $image_approval_sql . '
	ORDER BY ' . $sort_method . ' ' . $sort_order;
$result = $db->sql_query($sql);
//there should also be a way to go with a limit here, but we'll see

while ($row = $db->sql_fetchrow($result))
{
	if ($do_next)
	{
		$next_id = $row['image_id'];
	}
	$do_next = false;
	if ($row['image_id'] == $image_data['image_id'])
	{
		$previous_id = $last_id;
		$do_next = true;
	}
	$last_id = $row['image_id'];
}
$template->assign_vars(array(
	'U_VIEW_ALBUM'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx?album_id=$album_id"),

	'U_IMAGE'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx?album_id=$album_id&amp;image_id=$image_id"),
	'U_PREVIOUS'		=> ($previous_id) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx?album_id=$album_id&amp;image_id=$previous_id") : '',
	'U_NEXT'			=> ($next_id && ($next_id != $previous_id)) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx?album_id=$album_id&amp;image_id=$next_id") : '',
	'IMAGE_RSZ_WIDTH'	=> $album_config['preview_rsz_width'],
	'IMAGE_RSZ_HEIGHT'	=> $album_config['preview_rsz_height'],

	'IMAGE_NAME'		=> $image_data['image_name'],
	'IMAGE_DESC'		=> generate_text_for_display($image_data['image_desc'], $image_data['image_desc_uid'], $image_data['image_desc_bitfield'], 7),
	'IMAGE_BBCODE'		=> '[album]' . $image_data['image_id'] . '[/album]',
	'POSTER'			=> get_username_string('full', $image_data['image_user_id'], ($image_data['image_user_id'] <> ANONYMOUS) ? $image_data['image_username'] : $user->lang['GUEST'], $image_data['image_user_colour']),
	'IMAGE_TIME'		=> $user->format_date($image_data['image_time']),
	'IMAGE_VIEW'		=> $image_data['image_view_count'],

	'S_ALBUM_ACTION'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx?album_id=$album_id&amp;image_id=$image_id"))
);

if ($album_config['rate'])
{
	$template->assign_vars(array(
		'RATING'		=> $user->lang['RATING'],
		'IMAGE_RATING'	=> ($image_data['image_rates'] <> 0) ? $image_data['image_rate_avg'] / 100 : $user->lang['NOT_RATED'],
	));
	
	if ($album_data['album_rate_level'] < 1 || $album_access_array[$album_id]['i_rate'] == 1)
	{
		$ratebox = false;
		if ($user->data['user_id'] == ANONYMOUS || $user->data['is_bot'])
		{
			if ($album_data['album_rate_level'] == 0)
			{
				$ratebox = '<a href="' . append_sid("{$phpbb_root_path}ucp.$phpEx", "mode=login&amp;redirect=" . urlencode("{$gallery_root_path}image_page.$phpEx?album_id=$album_id&image_id=$image_id")) . '">' . $user->lang['LOGIN_TO_RATE'] . '</a>';
			}
		}
		else if ($user->data['user_id'] == $image_data['image_user_id'])
		{
			if ($album_data['album_rate_level'] == 0)
			{
				$ratebox = $user->lang['NO_RATE_ON_OWN_IMAGES'];
			}
		}
		if (!$ratebox)
		{
			if (!$already_rated)
			{
				$ratebox = '<select name="rate">';
				for ($i = 0; $i < $album_config['rate_scale']; $i++)
				{
					$rate_point = $i + 1;
					$ratebox .= '<option value="' . $rate_point . '">' . $rate_point . '</option>';
				}
				$ratebox .= '</select> &nbsp; &nbsp; <input type="submit" name="submit" value="' . $user->lang['SUBMIT'] . '" class="button1" />';
			}
			else
			{
				$ratebox = $user->lang['ALREADY_RATED'];
			}
		}
		$template->assign_vars(array(
			'YOUR_RATING'	=> true,
			'S_RATEBOX'	=> $ratebox,
		));
	}
}

if ($album_config['comment'])
{
	$user->add_lang('posting');
	$template->assign_vars(array(
		'COMMENTS'		=> true,
		'IMAGE_COMMENTS'	=> $image_data['image_comments'],
	));
	
	if ($album_data['album_comment_level'] < 1 || $album_access_array[$album_id]['c_post'])
	{
		$template->assign_vars(array(
			'POST_COMMENT'	=> true,
			'YOUR_COMMENT'	=> true,
		));
		$commentbox = false;
		if ($user->data['user_id'] == ANONYMOUS || $user->data['is_bot'])
		{
			if ($album_data['album_comment_level'] == 0)
			{
				$commentbox = '<a href="' . append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login') . '">' . $user->lang['LOGIN_TO_COMMENT'] . '</a>';
			}
			else
			{
				$template->assign_vars(array(
						'S_CAN_COMMENT' => true
				));
			}
		}
		if (!$commentbox)
		{
			$commentbox  = '';
			$commentbox .= '<textarea name="message" class="inputbox" cols="60" rows="4"></textarea><br /><br /><input type="submit" name="submit" value="' . $user->lang['SUBMIT'] . '" class="button1" />';
		}
		$template->assign_vars(array(
			'S_COMMENTBOX' 			=> true,
			'S_BBCODE_ALLOWED' 		=> true,
			'S_MAX_LENGTH' 			=> $album_config['desc_length'],
			'S_COMMENT_ACTION' 		=> append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=add"),
		));
	}
	
	$total_comments = $image_data['image_comments'];
	$comments_per_page = 10;
	
	$start = request_var('start', 0);
	
	$sort_order = request_var('sort_order', 'ASC');
	
	if ($total_comments > 0)
	{
		$limit_sql = ($start == 0) ? $comments_per_page : $start .','. $comments_per_page;

		$sql = 'SELECT c.*, u.user_id, u.username, u.user_colour
			FROM ' . GALLERY_COMMENTS_TABLE . ' AS c
			LEFT JOIN ' . USERS_TABLE . ' AS u
				ON c.comment_user_id = u.user_id
			WHERE c.comment_image_id = ' . $image_id . '
			ORDER BY c.comment_id ' . $sort_order . '
			LIMIT ' . $limit_sql;

		$result = $db->sql_query($sql);

		$commentrow = array();

		while( $row = $db->sql_fetchrow($result) )
		{
			$commentrow[] = $row;
		}
		
		$even = 0;
		
		for ($i = 0; $i < count($commentrow); $i++)
		{
			if (($commentrow[$i]['user_id'] == ALBUM_GUEST) || ($commentrow[$i]['username'] == ''))
			{
				$poster = ($commentrow[$i]['comment_username'] != '') ? $user->lang['GUEST'] : $commentrow[$i]['comment_username'];
			}
			else
			{
				$poster = '<a href="'. append_sid("{$phpbb_root_path}memberlist.$phpEx?mode=viewprofile&amp;u=" . $commentrow[$i]['user_id']) . '" class="username-coloured">' . $commentrow[$i]['username'] . '</a>';
			}

			if ($commentrow[$i]['comment_edit_count'] > 0)
			{
				$sql = 'SELECT c.comment_id, c.comment_edit_user_id, u.user_id, u.username, u.user_colour
					FROM ' . GALLERY_COMMENTS_TABLE . ' AS c
					LEFT JOIN ' . USERS_TABLE . ' AS u
						ON c.comment_edit_user_id = u.user_id
					WHERE c.comment_id = ' . $commentrow[$i]['comment_id']. '
					LIMIT 1';

				$result = $db->sql_query($sql);

				$lastedit_row = $db->sql_fetchrow($result);

				$edit_info = ($commentrow[$i]['comment_edit_count'] == 1) ? $user->lang['EDITED_TIME_TOTAL'] : $user->lang['EDITED_TIMES_TOTAL'];

				$edit_info = '<br /><br />&raquo;&nbsp;'. sprintf($edit_info, get_username_string('full', $lastedit_row['user_id'], $lastedit_row['username'], $lastedit_row['user_colour']), $user->format_date($commentrow[$i]['comment_edit_time']), $commentrow[$i]['comment_edit_count']) .'<br />';
			}
			else
			{
				$edit_info = '';
			}

			$template->assign_block_vars('commentrow', array(
				'ID'			=> $commentrow[$i]['comment_id'],
				'POSTER'		=> get_username_string('full', $commentrow[$i]['user_id'], ($commentrow[$i]['user_id'] <> ANONYMOUS) ? $commentrow[$i]['username'] : ($user->lang['GUEST'] . ': ' . $commentrow[$i]['comment_username']), $commentrow[$i]['user_colour']),
				'TIME'			=> $user->format_date($commentrow[$i]['comment_time']),
				'IP'			=> ($user->data['user_type'] == USER_FOUNDER) ? '<br />' . $user->lang['IP'] . ': <a href="http://www.nic.com/cgi-bin/whois.cgi?query=' . $commentrow[$i]['comment_user_ip'] . '">' . $commentrow[$i]['comment_user_ip'] .'</a><br />' : '',
				'TEXT'			=> generate_text_for_display($commentrow[$i]['comment'], $commentrow[$i]['comment_uid'], $commentrow[$i]['comment_bitfield'], 7),
				'EDIT_INFO'		=> $edit_info,
				'EDIT'			=> (($album_access_array[$album_id]['c_edit'] != 1) || (($commentrow[$i]['comment_user_id'] != $user->data['user_id']) && ($user->data['user_type'] != USER_FOUNDER)) || !$user->data['is_registered']) ? '' : append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=edit&amp;comment_id=" . $commentrow[$i]['comment_id']),
				'DELETE'		=> (($album_access_array[$album_id]['c_delete'] != 1) || (($commentrow[$i]['comment_user_id'] != $user->data['user_id']) && ($user->data['user_type'] != USER_FOUNDER)) || !$user->data['is_registered']) ? '' : append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=delete&amp;comment_id=" . $commentrow[$i]['comment_id']),
			));
		}

		$template->assign_vars(array(
			'EDIT_IMG'			=> $user->img('icon_post_edit', 'EDIT_POST'),
			'DELETE_IMG'		=> $user->img('icon_post_delete', 'DELETE_POST'),
			'PAGINATION'	=> generate_pagination(append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx?album_id=$album_id&amp;image_id=$image_id&amp;sort_order=$sort_order"), $total_comments, $comments_per_page, $start),
			'PAGE_NUMBER'	=> sprintf($user->lang['PAGE_OF'], ( floor( $start / $comments_per_page ) + 1 ), ceil( $total_comments / $comments_per_page ))
		));
	}
	else
	{
		$template->assign_vars(array(
			'NO_COMMENTS' => true)
		);
	}
}

// Build the navigation
generate_album_nav($album_data);

// Output page
$page_title = $user->lang['VIEW_IMAGE'];// . ' &bull; ' . $album_data['album_name']; ### add image title later

page_header($page_title);

$template->set_filenames(array(
	'body' => 'gallery_page_body.html')
);

page_footer();

?>