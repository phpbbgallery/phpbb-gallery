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



// ------------------------------------
// Check the request
// ------------------------------------

if (!$pic_id = request_var('id', 0))
{
	trigger_error($user->lang['NO_IMAGE_SPECIFIED'], E_USER_WARNING);
}


// ------------------------------------
// PREVIOUS & NEXT
// ------------------------------------

if( isset($_GET['mode']) )
{
   if( ($_GET['mode'] == 'next') || ($_GET['mode'] == 'previous') )
   {
      $sql = "SELECT pic_id, pic_cat_id, pic_user_id
            FROM ". ALBUM_TABLE ."
            WHERE pic_id = $pic_id";

      $result = $db->sql_query($sql);

      $row = $db->sql_fetchrow($result);

      if( empty($row) )
      {
         trigger_error($user->lang['IMAGE_NOT_EXIST'], E_USER_WARNING);
      }

      $sql = "SELECT new.pic_id, new.pic_time
            FROM ". ALBUM_TABLE ." AS new, ". ALBUM_TABLE ." AS cur
            WHERE cur.pic_id = $pic_id
               AND new.pic_id <> cur.pic_id
               AND new.pic_cat_id = cur.pic_cat_id";

      $sql .= ($_GET['mode'] == 'next') ? " AND new.pic_time >= cur.pic_time" : " AND new.pic_time <= cur.pic_time";

      $sql .= ($row['pic_cat_id'] == PERSONAL_GALLERY) ? " AND new.pic_user_id = cur.pic_user_id" : "";

      $sql .= ($_GET['mode'] == 'next') ? " ORDER BY pic_time ASC LIMIT 1" : " ORDER BY pic_time DESC LIMIT 1";

      $result = $db->sql_query($sql);

      $row = $db->sql_fetchrow($result);

      if( empty($row) )
      {
         trigger_error($user->lang['IMAGE_NOT_EXIST'], E_USER_WARNING);
      }

      $pic_id = $row['pic_id']; // NEW pic_id
   }
} 


// ------------------------------------
// Get this pic info
// ------------------------------------

$sql = "SELECT p.*, u.user_id, u.username, r.rate_pic_id, AVG(r.rate_point) AS rating, COUNT(DISTINCT c.comment_id) AS comments
		FROM ". ALBUM_TABLE ." AS p
			LEFT JOIN ". USERS_TABLE ." AS u ON p.pic_user_id = u.user_id
			LEFT JOIN ". ALBUM_RATE_TABLE ." AS r ON p.pic_id = r.rate_pic_id
			LEFT JOIN ". ALBUM_COMMENT_TABLE ." AS c ON p.pic_id = c.comment_pic_id
		WHERE pic_id = '$pic_id'
		GROUP BY p.pic_id";
$result = $db->sql_query($sql);

$thispic = $db->sql_fetchrow($result);

$cat_id = $thispic['pic_cat_id'];
$user_id = $thispic['pic_user_id'];

if (empty($thispic) || !file_exists(ALBUM_UPLOAD_PATH . $thispic['pic_filename']))
{
	trigger_error($user->lang['IMAGE_NOT_EXIST'], E_USER_WARNING);
}

// ------------------------------------
// Get the current Category Info
// ------------------------------------

if ($cat_id != PERSONAL_GALLERY)
{
	$sql = "SELECT *
			FROM ". ALBUM_CAT_TABLE ."
			WHERE cat_id = '$cat_id'";
	$result = $db->sql_query($sql);

	$thiscat = $db->sql_fetchrow($result);
}
else
{
	$thiscat = init_personal_gallery_cat($user_id);
}

if (empty($thiscat))
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}


// ------------------------------------
// Check the permissions
// ------------------------------------

$album_user_access = album_user_access($cat_id, $thiscat, 1, 0, 1, 1, 1, 1); // VIEW

if ($album_user_access['view'] == 0)
{
	if (!$user->data['is_registered'] || $user->data['is_bot'])
	{
		login_box("gallery/image_page.$phpEx?id=$pic_id");
	}
	else
	{
		trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
	}
}

$already_rated = false;
if( $album_config['rate'] != 0 && $user->data['is_registered'] )
{
	$sql = "SELECT *
			FROM " . ALBUM_RATE_TABLE . "
			WHERE rate_pic_id = '$pic_id'
				AND rate_user_id = '". $user->data['user_id'] ."'
			LIMIT 1";

	$result = $db->sql_query($sql);

	if ($db->sql_affectedrows($result) > 0)
	{
		$already_rated = true;
	}
}

// ------------------------------------
// Check Pic Approval
// ------------------------------------

if ($user->data['user_type'] != USER_FOUNDER)
{
	if (($thiscat['cat_approval'] == ADMIN) || (($thiscat['cat_approval'] == MOD) || !$album_user_access['moderator']))
	{
		if ($thispic['pic_approval'] != 1)
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}
	}
}

// ------------------------------------
// Posting Comments & Rating
// ------------------------------------

if (isset($_POST['comment']) || isset($_POST['rate']))
{
	include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
	if (isset($_POST['comment']))
	{
		if ($album_config['comment'] == 0 || $album_user_access['comment'] == 0)
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}
		
		$comment_text = substr(request_var('comment', '', true), 0, $album_config['desc_length']);
	
		$comment_username = (!$user->data['is_registered']) ? substr(request_var('comment_username', '', true), 0, 32) : str_replace("'", "''", htmlspecialchars(trim($user->data['username'])));
	
		if( empty($comment_text) )
		{
			trigger_error($user->lang['COMMENT_NO_TEXT'], E_USER_WARNING);
		}
	
	
		// --------------------------------
		// Check Pic Locked
		// --------------------------------
	
		if (($thispic['pic_lock'] == 1) && (!$auth_data['moderator']))
		{
			trigger_error($user->lang['IMAGE_LOCKED'], E_USER_WARNING);
		}
	
	
		// --------------------------------
		// Check username for guest posting
		// --------------------------------
	
		if (!$user->data['is_registered'])
		{
			if ($comment_username != '')
			{
				$result = validate_username($comment_username);
				if ( $result['error'] )
				{
					trigger_error($result['error_msg'], E_USER_WARNING);
				}
			}
		}
	
	
		// --------------------------------
		// Prepare variables
		// --------------------------------
	
		$comment_time = time();
		$comment_user_id = $user->data['user_id'];
		$comment_user_ip = $user->data['user_ip'];
	
	
		// --------------------------------
		// Get $comment_id
		// --------------------------------
		$sql = "SELECT MAX(comment_id) AS max
				FROM ". ALBUM_COMMENT_TABLE;
	
		$result = $db->sql_query($sql);
	
		$row = $db->sql_fetchrow($result);
	
		$comment_id = $row['max'] + 1;
	
	
		// --------------------------------
		// Insert into DB
		// --------------------------------
	
		$sql = "INSERT INTO ". ALBUM_COMMENT_TABLE ." (comment_id, comment_pic_id, comment_user_id, comment_username, comment_user_ip, comment_time, comment_text)
				VALUES ('" . $db->sql_escape($comment_id) . "', '" . $db->sql_escape($pic_id) . "', '" . $db->sql_escape($comment_user_id) . "', '" . $db->sql_escape($comment_username) . "', '" . $db->sql_escape($comment_user_ip) . "', '" . $db->sql_escape($comment_time) . "', '" . $db->sql_escape($comment_text) . "')";
		$result = $db->sql_query($sql);
	
	
		// --------------------------------
		// Complete... now send a message to user
		// --------------------------------
	
		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid("image_page.$phpEx?id=$pic_id&comment_set=1") . '#comments">')
		);
	
		$message = $user->lang['COMMENT_STORED'] . "<br /><br />" . sprintf($user->lang['CLICK_VIEW_COMMENT'], "<a href=\"" . append_sid("image_page.$phpEx?id=$pic_id&stored=1") . "#$comment_id\">", "</a>") . "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("album.$phpEx") . "\">", "</a>");
	
		trigger_error($message, E_USER_WARNING);
		
	}
	
	if (isset($_POST['rate']))
	{
		if ($album_config['rate'] == 0 || $album_user_access['rate'] == 0)
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
			trigger_error('Bad submitted value', E_USER_WARNING);
		}
	
		$rate_user_id = $user->data['user_id'];
		$rate_user_ip = $user->data['user_ip'];
		
		// --------------------------------
		// Insert into the DB
		// --------------------------------
	
		$sql = "INSERT INTO " . ALBUM_RATE_TABLE . " (rate_pic_id, rate_user_id, rate_user_ip, rate_point)
				VALUES ('$pic_id', '$rate_user_id', '$rate_user_ip', '$rate_point')";
	
		$result = $db->sql_query($sql);
	
	
		// --------------------------------
		// Complete... now send a message to user
		// --------------------------------
	
		$message = $user->lang['RATING_SUCCESSFUL'];
	
		if ($cat_id != PERSONAL_GALLERY)
		{
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid("image_page.$phpEx?id=$pic_id&rate_set=1#rating") . '">')
			);
	
			$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("album.$phpEx?id=$cat_id") . "\">", "</a>");
		}
		else
		{
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid("album_personal.$phpEx?user_id=$user_id") . '">')
			);
	
			$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_PERSONAL_ALBUM'], "<a href=\"" . append_sid("album_personal.$phpEx?user_id=$user_id") . "\">", "</a>");
		}
	
		$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");
	
		trigger_error($message, E_USER_WARNING);
	}
}

// Next
$sql = "SELECT new.pic_id, new.pic_time
      FROM ". ALBUM_TABLE ." AS new, ". ALBUM_TABLE ." AS cur
      WHERE cur.pic_id = $pic_id
         AND new.pic_id <> cur.pic_id
         AND new.pic_cat_id = cur.pic_cat_id
         AND new.pic_time >= cur.pic_time";

$sql .= ($thispic['pic_cat_id'] == PERSONAL_GALLERY) ? " AND new.pic_user_id = cur.pic_user_id" : "";
$sql .= " ORDER BY pic_time ASC LIMIT 1";

$result = $db->sql_query($sql);

$row = $db->sql_fetchrow($result);

if( empty($row) )
{
   $u_next = "";
   $l_next = "";
}
else
{
   $new_pic_id = $row['pic_id'];
   $u_next = append_sid("image_page.$phpEx?id=$new_pic_id");
   $l_next = $user->lang['NEXT'] . "&nbsp;&raquo;";
}

// Prev
$sql = "SELECT new.pic_id, new.pic_time
      FROM ". ALBUM_TABLE ." AS new, ". ALBUM_TABLE ." AS cur
      WHERE cur.pic_id = $pic_id
         AND new.pic_id <> cur.pic_id
         AND new.pic_cat_id = cur.pic_cat_id
         AND new.pic_time <= cur.pic_time";

$sql .= ($thispic['pic_cat_id'] == PERSONAL_GALLERY) ? " AND new.pic_user_id = cur.pic_user_id" : "";
$sql .= " ORDER BY pic_time DESC LIMIT 1";

$result = $db->sql_query($sql);

$row = $db->sql_fetchrow($result);

if( empty($row) )
{
   $u_prev = "";
   $l_prev = "";
}
else
{
   $new_pic_id = $row['pic_id'];
   $u_prev = append_sid("image_page.$phpEx?id=$new_pic_id");
   $l_prev = "&laquo;&nbsp;" . $user->lang['PREVIOUS'];
}
// end 

/*
+----------------------------------------------------------
| Main work here...
+----------------------------------------------------------
*/

if (($thispic['pic_user_id'] == ALBUM_GUEST) || ($thispic['username'] == ''))
{
	$poster = ($thispic['pic_username'] == '') ? $user->lang['GUEST'] : $thispic['pic_username'];
}
else
{
	$poster = '<a href="'. append_sid("{$phpbb_root_path}memberlist.$phpEx?mode=viewprofile&amp;u=" . $thispic['user_id']) . '">' . $thispic['username'] . '</a>';
}


$template->assign_vars(array(
	'CAT_TITLE' => $thiscat['cat_title'],
	'U_VIEW_CAT' => ($cat_id != PERSONAL_GALLERY) ? append_sid("album.$phpEx?id=$cat_id") : append_sid("album_personal.$phpEx?user_id=$user_id"),

	'U_PIC' => append_sid("image.$phpEx?pic_id=$pic_id"),

	'PIC_TITLE' => $thispic['pic_title'],
	'PIC_DESC' => nl2br($thispic['pic_desc']),

	'POSTER' => $poster,

	'PIC_TIME' => $user->format_date($thispic['pic_time']),

	'PIC_VIEW' => $thispic['pic_view_count'],

	'U_NEXT' => $u_next,
	'U_PREVIOUS' => $u_prev,

	'L_NEXT' => $l_next,
	'L_PREVIOUS' => $l_prev,
	
	'L_DETAILS' => $user->lang['DETAILS'],
	'L_PIC_TITLE' => $user->lang['IMAGE_TITLE'],
	'L_PIC_DESC' => $user->lang['IMAGE_DESC'],
	'L_POSTER' => $user->lang['POSTER'],
	'L_POSTED' => $user->lang['POSTED'],
	'L_VIEW' => $user->lang['VIEWS'],
	
	'S_ALBUM_ACTION' => append_sid("image_page.$phpEx?id=$pic_id"))
);

if ($album_config['rate'])
{
	$template->assign_vars(array(
		'L_RATING' => $user->lang['RATING'],
		'PIC_RATING' => ($thispic['rating'] != 0) ? round($thispic['rating'], 2) : $user->lang['NOT_RATED']
		)
	);
	
	if ($thiscat['cat_rate_level'] < 1 || $album_user_access['rate'])
	{
		$template->assign_vars(array(
				'L_YOUR_RATING' => $user->lang['YOUR_RATING'],
			)
		);
		$ratebox = false;
		if ($user->data['user_id'] == ANONYMOUS || $user->data['is_bot'])
		{
			if ($thiscat['cat_rate_level'] == 0)
			{
				$ratebox = '<a href="' . append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login') . '">' . $user->lang['LOGIN_TO_RATE'] . '</a>';
			}
		}
		if (!$ratebox)
		{
			if (!$already_rated)
			{
				$ratebox = '<form name="rateform" action="' . append_sid("image_page.$phpEx?id=$pic_id") . '" method="post" onsubmit="return checkRateForm();"><select name="rate">';
				for ($i = 0; $i < $album_config['rate_scale']; $i++)
				{
					$rate_point = $i + 1;
					$ratebox .= '<option value="' . $rate_point . '">' . $rate_point . '</option>';
				}
				$ratebox .= '</select> &nbsp; &nbsp; <input type="submit" name="submit" value="' . $user->lang['SUBMIT'] . '" class="button1" /></form>';
			}
			else
			{
				$ratebox = $user->lang['ALREADY_RATED'];
			}
		}
		$template->assign_vars(array(
				'S_RATEBOX' => $ratebox
			)
		);
	}
}

if ($album_config['comment'])
{
	$template->assign_vars(array(
		'L_COMMENTS' => $user->lang['COMMENTS'],
		'PIC_COMMENTS' => $thispic['comments']
		)
	);
	//'PIC_COMMENTS' => $thispic['comments']
	
	if ($thiscat['cat_comment_level'] < 1 || $album_user_access['comment'])
	{
		$template->assign_vars(array(
				'L_POST_COMMENT' => $user->lang['POST_COMMENT'],
				'L_YOUR_COMMENT' => $user->lang['YOUR_COMMENT']
			)
		);
		$commentbox = false;
		if ($user->data['user_id'] == ANONYMOUS || $user->data['is_bot'])
		{
			if ($thiscat['cat_comment_level'] == 0)
			{
				$commentbox = '<a href="' . append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login') . '">' . $user->lang['LOGIN_TO_COMMENT'] . '</a>';
			}
			else
			{
				$template->assign_vars(array(
						'S_CAN_COMMENT' => true
					)
				);
			}
		}
		if (!$commentbox)
		{
			$commentbox = '';
			$commentbox .= '<textarea name="comment" class="inputbox" cols="60" rows="4" size="60"></textarea><br /><br /><input type="submit" name="submit" value="' . $user->lang['SUBMIT'] . '" class="button1" />';
		}
		$template->assign_vars(array(
				'S_COMMENTBOX' => $commentbox,
				'L_ASC' => $user->lang['SORT_ASCENDING'],
				'L_DESC' => $user->lang['SORT_DESCENDING'],
				'L_COMMENT_NO_TEXT' => $user->lang['COMMENT_NO_TEXT'],
				'L_COMMENT_TOO_LONG' => $user->lang['COMMENT_TOO_LONG'],
				'S_MAX_LENGTH' => $album_config['desc_length']
			)
		);
	}
	
	$total_comments = $thispic['comments'];
	$comments_per_page = 10;
	
	$start = request_var('start', 0);
	
	$sort_order = request_var('sort_order', 'ASC');
	
	if ($total_comments > 0)
	{
		$limit_sql = ($start == 0) ? $comments_per_page : $start .','. $comments_per_page;

		$sql = "SELECT c.*, u.user_id, u.username
				FROM ". ALBUM_COMMENT_TABLE ." AS c
					LEFT JOIN ". USERS_TABLE ." AS u ON c.comment_user_id = u.user_id
				WHERE c.comment_pic_id = '$pic_id'
				ORDER BY c.comment_id $sort_order
				LIMIT $limit_sql";

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
				$poster = ($commentrow[$i]['comment_username'] == '') ? $user->lang['GUEST'] : $commentrow[$i]['comment_username'];
			}
			else
			{
				$poster = '<a href="'. append_sid("{$phpbb_root_path}memberlist.$phpEx?mode=viewprofile&amp;u=" . $commentrow[$i]['user_id']) . '" class="username-coloured">' . $commentrow[$i]['username'] . '</a>';
			}

			if ($commentrow[$i]['comment_edit_count'] > 0)
			{
				$sql = "SELECT c.comment_id, c.comment_edit_user_id, u.user_id, u.username
						FROM ". ALBUM_COMMENT_TABLE ." AS c
							LEFT JOIN ". USERS_TABLE ." AS u ON c.comment_edit_user_id = u.user_id
						WHERE c.comment_id = '".$commentrow[$i]['comment_id']."'
						LIMIT 1";

				$result = $db->sql_query($sql);

				$lastedit_row = $db->sql_fetchrow($result);

				$edit_info = ($commentrow[$i]['comment_edit_count'] == 1) ? $user->lang['EDITED_TIME_TOTAL'] : $user->lang['EDITED_TIMES_TOTAL'];

				$edit_info = '<br /><br />&raquo;&nbsp;'. sprintf($edit_info, $lastedit_row['username'], $user->format_date($commentrow[$i]['comment_edit_time']), $commentrow[$i]['comment_edit_count']) .'<br />';
			}
			else
			{
				$edit_info = '';
			}
			//$commentrow[$i]['comment_text'] = smilies_pass($commentrow[$i]['comment_text']);
			
			if ($even == 0)
			{
				$row_style = 'bg2';
				$even++;
			}
			else
			{
				$row_style = 'bg1';
				$even = 0;
			}
				
			$template->assign_block_vars('commentrow', array(
				'ID' => $commentrow[$i]['comment_id'],
				'POSTER' => $poster,
				'TIME' => $user->format_date($commentrow[$i]['comment_time']),
				'IP' => ($user->data['user_type'] == USER_FOUNDER) ? '-----------------------------------<br />' . $user->lang['IP'] . ': <a href="http://www.nic.com/cgi-bin/whois.cgi?query=' . $commentrow[$i]['comment_user_ip'] . '" target="_blank">' . $commentrow[$i]['comment_user_ip'] .'</a><br />' : '',
				
				'S_ROW_STYLE' => $row_style,

				'TEXT' => nl2br($commentrow[$i]['comment_text']),
				'EDIT_INFO' => $edit_info,

				'EDIT' => '',//missing feature ( ( $auth_data['edit'] && ($commentrow[$i]['comment_user_id'] == $user->data['user_id']) ) || ($auth_data['moderator'] && ($thiscat['cat_edit_level'] != ALBUM_ADMIN) ) || ($user->data['user_type'] == USER_FOUNDER) ) ? '<a href="'. append_sid("edit.$phpEx?comment_id=". $commentrow[$i]['comment_id']) .'">'. $user->lang['EDIT_IMAGE'] .'</a>' : '',

				'DELETE' => '',//missing feature ( ( $auth_data['delete'] && ($commentrow[$i]['comment_user_id'] == $user->data['user_id']) ) || ($auth_data['moderator'] && ($thiscat['cat_delete_level'] != ALBUM_ADMIN) ) || ($user->data['user_type'] == USER_FOUNDER) ) ? '<a href="'. append_sid("edit.$phpEx?comment_id=". $commentrow[$i]['comment_id']) .'">'. $user->lang['DELETE_IMAGE'] .'</a>' : ''
				)
			);
		}

		$template->assign_vars(array(
			'PAGINATION' => generate_pagination(append_sid("image_page.$phpEx?id=$pic_id&amp;sort_order=$sort_order"), $total_comments, $comments_per_page, $start),
			'PAGE_NUMBER' => sprintf($user->lang['PAGE_OF'], ( floor( $start / $comments_per_page ) + 1 ), ceil( $total_comments / $comments_per_page ))
			)
		);
	}
	else
	{
		$template->assign_vars(array(
			'L_NO_COMMENTS' => $user->lang['NO_COMMENTS'])
		);
	}
}

// Build the navigation
$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'   => $user->lang['GALLERY'],
	'U_VIEW_FORUM'   => append_sid("{$album_root_path}index.$phpEx"),
		));

if ($cat_id != PERSONAL_GALLERY)
{
   $template->assign_block_vars('navlinks', array(
		'FORUM_NAME'   => $thiscat['cat_title'],
		'U_VIEW_FORUM'   => append_sid("{$album_root_path}album.$phpEx", 'id=' . $thiscat['cat_id']),
		));
}
else
{
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'   => $user->lang['PERSONAL_ALBUMS'],
		'U_VIEW_FORUM'   => append_sid("{$album_root_path}album_personal_index.$phpEx"),
		));

	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'   => sprintf($user->lang['PERSONAL_ALBUM_OF_USER'], $poster),
		'U_VIEW_FORUM'   => append_sid("{$album_root_path}album_personal.$phpEx", 'user_id=' . $user_id),
		));
}

// Output page
$page_title = $user->lang['VIEW_IMAGE'];
// . ' - ' . $thiscat['cat_title']; ### add image title later

page_header($page_title);

$template->set_filenames(array(
	'body' => 'gallery_page_body.html')
);

page_footer();

?>