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
// Get the $pic_id from GET method then query out the category
// If $pic_id not found we will assign it to FALSE
// We will check $pic_id[] in POST method later (in $mode carry out)
// ------------------------------------

$pic_id = request_var('pic_id', 0);
$cat_id = request_var('cat_id', 0);

if($pic_id <> 0)
{
	$sql = 'SELECT *
			FROM ' . ALBUM_TABLE . '
			WHERE pic_id = ' . $pic_id;
	$result = $db->sql_query($sql);

	$thispic = $db->sql_fetchrow($result);

	if( empty($thispic) )
	{
		trigger_error($user->lang['IMAGE_NOT_EXIST'], E_USER_WARNING);
	}

	$cat_id = $thispic['pic_cat_id'];
	$user_id = $thispic['pic_user_id'];
}


// ------------------------------------
// Get the cat info
// ------------------------------------
if( ($cat_id == PERSONAL_GALLERY) && (($_GET['mode'] == 'lock') || ($_GET['mode'] == 'unlock')) )
{
	$thiscat = init_personal_gallery_cat($user_id);
}
else
{
	$sql = "SELECT *
			FROM ". ALBUM_CAT_TABLE ."
			WHERE cat_id = '$cat_id'";
	$result = $db->sql_query($sql);

	$thiscat = $db->sql_fetchrow($result);
}

if (empty($thiscat))
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}

$auth_data = album_user_access($cat_id, $thiscat, 0, 0, 0, 0, 0, 0); // MODERATOR only
//
// END category info
//


// ------------------------------------
// set $mode (select action)
// ------------------------------------
if( isset($_POST['mode']) )
{
	// Oh data from Mod CP
	if( isset($_POST['move']) )
	{
		$mode = 'move';
	}
	else if( isset($_POST['lock']) )
	{
		$mode = 'lock';
	}
	else if( isset($_POST['unlock']) )
	{
		$mode = 'unlock';
	}
	else if( isset($_POST['delete']) )
	{
		$mode = 'delete';
	}
	else if( isset($_POST['approval']) )
	{
		$mode = 'approval';
	}
	else if( isset($_POST['unapproval']) )
	{
		$mode = 'unapproval';
	}
	else
	{
		$mode = '';
	}
}
else if( isset($_GET['mode']) )
{
	$mode = trim($_GET['mode']);
}
else
{
	$mode = '';
}
//
// END $mode (select action)
//


// ------------------------------------
// Check the permissions
// ------------------------------------
if ($auth_data['moderator'] == 0)
{
	if (!$user->data['is_registered'] || $user->data['is_bot'])
	{
		login_box("gallery/mcp.$phpEx?cat_id=$cat_id");
	}
	else
	{
		trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
	}
}
//
// END permissions
//



/*
+----------------------------------------------------------
| Main work here...
+----------------------------------------------------------
*/

if ($mode == '')
{
	// --------------------------------
	// Moderator Control Panel
	// --------------------------------

	// Set Variables
	$start = request_var('start', 0);
	
	$sort_method = request_var('sort_method', 'pic_time');

	$sort_order = request_var('sort_order', 'DESC');

	// Count Pics
	$sql = "SELECT COUNT(pic_id) AS count
			FROM ". ALBUM_TABLE ."
			WHERE pic_cat_id = '$cat_id'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

	$total_pics = $row['count'];

	$pics_per_page = 15; // Text list only ######### CHANGE LATER ################

	// get information from DB
	if ($total_pics > 0)
	{
		$limit_sql = ($start == 0) ? $pics_per_page : $start . ', ' . $pics_per_page;

		$pic_approval_sql = '';
		if (($user->data['user_type'] != USER_FOUNDER) && ($thiscat['cat_approval'] == ALBUM_ADMIN))
		{
			// because he went through my Permission Checking above so he must be at least a Moderator
			$pic_approval_sql = 'AND p.pic_approval = 1';
		}

		$sql = "SELECT p.pic_id, p.pic_title, p.pic_user_id, p.pic_user_ip, p.pic_username, p.pic_time, p.pic_cat_id, p.pic_view_count, p.pic_lock, p.pic_approval, u.user_id, u.username, r.rate_pic_id, AVG(r.rate_point) AS rating, COUNT(c.comment_id) AS comments, MAX(c.comment_id) AS new_comment
				FROM ". ALBUM_TABLE ." AS p
					LEFT JOIN ". USERS_TABLE ." AS u ON p.pic_user_id = u.user_id
					LEFT JOIN ". ALBUM_RATE_TABLE ." AS r ON p.pic_id = r.rate_pic_id
					LEFT JOIN ". ALBUM_COMMENT_TABLE ." AS c ON p.pic_id = c.comment_pic_id
				WHERE p.pic_cat_id = '$cat_id' $pic_approval_sql
				GROUP BY p.pic_id
				ORDER BY $sort_method $sort_order
				LIMIT $limit_sql";
		$result = $db->sql_query($sql);

		$picrow = array();

		while( $row = $db->sql_fetchrow($result) )
		{
			$picrow[] = $row;
		}

		for ($i = 0; $i <count($picrow); $i++)
		{
			if (($picrow[$i]['user_id'] == ALBUM_GUEST) || ($picrow[$i]['username'] == ''))
			{
				$pic_poster = ($picrow[$i]['pic_username'] == '') ? $user->lang['GUEST'] : $picrow[$i]['pic_username'];
			}
			else
			{
				$pic_poster = '<a href="'. append_sid("{$phpbb_root_path}memberlist.$phpEx?mode=viewprofile&u=" . $picrow[$i]['user_id']) .'">'. $picrow[$i]['username'] .'</a>';
			}

			$template->assign_block_vars('picrow', array(
				'PIC_ID' => $picrow[$i]['pic_id'],
				'PIC_TITLE' => '<a href="'. append_sid("image.$phpEx?pic_id=". $picrow[$i]['pic_id']) .'" target="_blank">'. $picrow[$i]['pic_title'] .'</a>',
				'POSTER' => $pic_poster,
				'TIME' => $user->format_date($picrow[$i]['pic_time']),
				'RATING' => ($picrow[$i]['rating'] == 0) ? $user->lang['NOT_RATED'] : round($picrow[$i]['rating'], 2),
				'COMMENTS' => $picrow[$i]['comments'],
				'LOCK' => ($picrow[$i]['pic_lock'] == 0) ? '' : $user->lang['LOCKED'],
				'APPROVAL' => ($picrow[$i]['pic_approval'] == 0) ? $user->lang['NOT_APPROVED'] : $user->lang['APPROVED']
				)
			);
		}

		$template->assign_vars(array(
			'PAGINATION' => generate_pagination(append_sid("mcp.$phpEx?cat_id=$cat_id&amp;sort_method=$sort_method&amp;sort_order=$sort_order"), $total_pics, $pics_per_page, $start),
			'PAGE_NUMBER' => sprintf($user->lang['PAGE_OF'], ( floor( $start / $pics_per_page ) + 1 ), ceil( $total_pics / $pics_per_page ))
			)
		);
	}
	else
	{
		// No Pics
		$template->assign_block_vars('no_pics', array());
	}

	$sort_rating_option = '';
	$sort_comments_option = '';
	if( $album_config['rate'] == 1 )
	{
		$sort_rating_option = '<option value="rating" ';
		$sort_rating_option .= ($sort_method == 'rating') ? 'selected="selected"' : '';
		$sort_rating_option .= '>' . $user->lang['RATING'] .'</option>';
	}
	if( $album_config['comment'] == 1 )
	{
		$sort_comments_option = '<option value="comments" ';
		$sort_comments_option .= ($sort_method == 'comments') ? 'selected="selected"' : '';
		$sort_comments_option .= '>' . $user->lang['COMMENTS'] .'</option>';
		$sort_new_comment_option = '<option value="new_comment" ';
		$sort_new_comment_option .= ($sort_method == 'new_comment') ? 'selected="selected"' : '';
		$sort_new_comment_option .= '>' . $user->lang['NEW_COMMENT'] .'</option>';
	}

	$template->assign_vars(array(
		'U_VIEW_CAT' => append_sid("mcp.$phpEx?cat_id=$cat_id"),
		'CAT_TITLE' => $thiscat['cat_title'],

		'L_CATEGORY' => $user->lang['ALBUM'],
		'L_MODCP' => $user->lang['MODCP'],

		'L_NO_PICS' => $user->lang['NO_IMAGES'],

		'L_VIEW' => $user->lang['VIEWS'],
		'L_POSTER' => $user->lang['POSTER'],
		'L_POSTED' => $user->lang['POSTED'],

		'S_ALBUM_ACTION' => append_sid("mcp.$phpEx?cat_id=$cat_id"),

		'L_SELECT_SORT_METHOD' => $user->lang['SELECT_SORT_METHOD'],
		'L_ORDER' => $user->lang['ORDER'],
		'L_SORT' => $user->lang['SORT'],

		'L_TIME' => $user->lang['TIME'],
		'L_PIC_TITLE' => $user->lang['IMAGE_TITLE'],
		'L_POSTER' => $user->lang['POSTER'],
		'L_RATING' => $user->lang['RATING'],
		'L_COMMENTS' => $user->lang['COMMENTS'],
		'L_STATUS' => $user->lang['STATUS'],
		'L_APPROVAL' => $user->lang['APPROVAL'],
		'L_SELECT' => $user->lang['SELECT'],
		'L_DELETE' => $user->lang['DELETE'],
		'L_MOVE' => $user->lang['MOVE'],
		'L_LOCK' => $user->lang['LOCK'],
		'L_UNLOCK' => $user->lang['UNLOCK'],

		'DELETE_BUTTON' => ($auth_data['delete'] == 1) ? '<input type="submit" class="liteoption" name="delete" value="'. $user->lang['DELETE'] .'" />' : '',

		'APPROVAL_BUTTON' => ( ($user->data['user_type'] != USER_FOUNDER) && ($thiscat['cat_approval'] == ALBUM_ADMIN) ) ? '' : '<input type="submit" class="liteoption" name="approval" value="'. $user->lang['APPROVE'] .'" />',

		'UNAPPROVAL_BUTTON' => ( ($user->data['user_type'] != USER_FOUNDER) && ($thiscat['cat_approval'] == ALBUM_ADMIN) ) ? '' : '<input type="submit" class="liteoption" name="unapproval" value="'. $user->lang['UNAPPROVE'] .'" />',

		'L_USERNAME' => $user->lang['SORT_USERNAME'],

		'SORT_TIME' => ($sort_method == 'pic_time') ? 'selected="selected"' : '',
		'SORT_PIC_TITLE' => ($sort_method == 'pic_title') ? 'selected="selected"' : '',
		'SORT_USERNAME' => ($sort_method == 'pic_user_id') ? 'selected="selected"' : '',
		'SORT_VIEW' => ($sort_method == 'pic_view_count') ? 'selected="selected"' : '',

		'SORT_RATING_OPTION' => $sort_rating_option,
		'SORT_COMMENTS_OPTION' => $sort_comments_option,
		'SORT_NEW_COMMENT_OPTION' => $sort_new_comment_option,

		'L_ASC' => $user->lang['SORT_ASCENDING'],
		'L_DESC' => $user->lang['SORT_DESCENDING'],

		'SORT_ASC' => ($sort_order == 'ASC') ? 'selected="selected"' : '',
		'SORT_DESC' => ($sort_order == 'DESC') ? 'selected="selected"' : ''
		)
	);

	// Output page
	$page_title = $user->lang['GALLERY'];
	
	page_header($page_title);
	
	$template->set_filenames(array(
		'body' => 'gallery_modcp_body.html')
	);
	
	page_footer();

}
else
{
	//
	// Switch with $mode
	//
	if ($mode == 'move')
	{
		//-----------------------------
		// MOVE
		//-----------------------------

		if( !isset($_POST['target']) )
		{
			// if "target" has not been set, we will open the category select form
			//
			// we must check POST method now
			$pic_id_array = array();
			if ($pic_id != FALSE) // from GET
			{
				$pic_id_array[] = $pic_id;
			}
			else
			{
				// Check $pic_id[] on POST Method now
				if( isset($_POST['pic_id']) )
				{
					$pic_id_array = $_POST['pic_id'];
					if( !is_array($pic_id_array) )
					{
						trigger_error('Invalid request', E_USER_WARNING);
					}
				}
				else
				{
					trigger_error('No pics specified', E_USER_WARNING);
				}
			}

			// We must send out the $pic_id_array to store data between page changing
			for ($i = 0; $i < count($pic_id_array); $i++)
			{
				$template->assign_block_vars('pic_id_array', array(
					'VALUE' => $pic_id_array[$i])
				);
			}

			//
			// Create categories select
			//
			$sql = "SELECT *
					FROM ". ALBUM_CAT_TABLE ."
					WHERE cat_id <> '$cat_id'
					ORDER BY cat_order ASC";
			$result = $db->sql_query($sql);

			$catrows = array();

			while( $row = $db->sql_fetchrow($result) )
			{
				$album_user_access = album_user_access($row['cat_id'], $row, 0, 1, 0, 0, 0, 0);

				if ($album_user_access['upload'] == 1)
				{
					$catrows[] = $row;
				}
			}

			if( count($catrows) == 0 )
			{
				trigger_error('There is no more categories which you have permisson to move images to', E_USER_WARNING);
			}

			// write categories out
			$category_select = '<select name="target">';

			for ($i = 0; $i < count($catrows); $i++)
			{
				$category_select .= '<option value="'. $catrows[$i]['cat_id'] .'">'. $catrows[$i]['cat_title'] .'</option>';
			}

			$category_select .= '</select>';
			// end write

			$template->assign_vars(array(
				'S_ALBUM_ACTION' => append_sid("mcp.$phpEx?mode=move&amp;cat_id=$cat_id"),
				'L_MOVE' => $user->lang['MOVE'],
				'L_MOVE_TO_CATEGORY' => $user->lang['MOVE_TO_ALBUM'],
				'S_CATEGORY_SELECT' => $category_select)
			);

			// Output page
			$page_title = $user->lang['GALLERY'];
			
			page_header($page_title);
			
			$template->set_filenames(array(
				'body' => 'gallery_move_body.html')
			);
			
			page_footer();
		}
		else
		{
			// Do the MOVE action
			//
			// Now we only get $pic_id[] via POST (after the select target screen)
			if( isset($_POST['pic_id']) )
			{
				$pic_id = $_POST['pic_id'];
				if( is_array($pic_id) )
				{
					$pic_id_sql = implode(',', $pic_id);
				}
				else
				{
					trigger_error('Invalid request', E_USER_WARNING);
				}
			}
			else
			{
				trigger_error('No pics specified', E_USER_WARNING);
			}

			// well, we got the array of pic_id but we must do a check to make sure all these
			// pics are in this category (prevent some naughty moderators to access un-authorised pics)
			$sql = "SELECT pic_id
					FROM ". ALBUM_TABLE ."
					WHERE pic_id IN ($pic_id_sql) AND pic_cat_id <> $cat_id";
			$result = $db->sql_query($sql);
			if( $db->sql_affectedrows($result) > 0 )
			{
				trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
			}

			// Update the DB
			$sql = "UPDATE ". ALBUM_TABLE ."
					SET pic_cat_id = ". intval($_POST['target']) ."
					WHERE pic_id IN ($pic_id_sql)";
			$result = $db->sql_query($sql);

			$message = $user->lang['IMAGES_MOVED_SUCCESSFULLY'] .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("album.$phpEx?id=$cat_id") . "\">", "</a>") .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_MODCP'], "<a href=\"" . append_sid("mcp.$phpEx?cat_id=$cat_id") . "\">", "</a>") . "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

			trigger_error($message, E_USER_WARNING);
		}
	}
	else if ($mode == 'lock')
	{
		//-----------------------------
		// LOCK
		//-----------------------------

		// we must check POST method now
		if ($pic_id != FALSE) // from GET
		{
			$pic_id_sql = $pic_id;
		}
		else
		{
			// Check $pic_id[] on POST Method now
			if( isset($_POST['pic_id']) )
			{
				$pic_id = $_POST['pic_id'];
				if( is_array($pic_id) )
				{
					$pic_id_sql = implode(',', $pic_id);
				}
				else
				{
					trigger_error('Invalid request', E_USER_WARNING);
				}
			}
			else
			{
				trigger_error('No pics specified', E_USER_WARNING);
			}
		}

		// well, we got the array of pic_id but we must do a check to make sure all these
		// pics are in this category (prevent some naughty moderators to access un-authorised pics)
		$sql = "SELECT pic_id
				FROM ". ALBUM_TABLE ."
				WHERE pic_id IN ($pic_id_sql) AND pic_cat_id <> $cat_id";
		$result = $db->sql_query($sql);
		if( $db->sql_affectedrows($result) > 0 )
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}

		// update the DB
		$sql = "UPDATE ". ALBUM_TABLE ."
				SET pic_lock = 1
				WHERE pic_id IN ($pic_id_sql)";
		$result = $db->sql_query($sql);

		$message = $user->lang['IMAGES_LOCKED_SUCCESSFULLY'] .'<br /><br />';

		if ($cat_id != PERSONAL_GALLERY)
		{
			$message .= sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("album.$phpEx?id=$cat_id") . "\">", "</a>") .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_MODCP'], "<a href=\"" . append_sid("mcp.$phpEx?cat_id=$cat_id") . "\">", "</a>") . "<br /><br />";
		}
		else
		{
			$message .= sprintf($user->lang['CLICK_RETURN_PERSONAL_ALBUM'], "<a href=\"" . append_sid("album_personal.$phpEx?user_id=$user_id") . "\">", "</a>");
		}

		$message .= '<br /><br />' . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

		trigger_error($message, E_USER_WARNING);
	}
	else if ($mode == 'unlock')
	{
		//-----------------------------
		// UNLOCK
		//-----------------------------

		// we must check POST method now
		if ($pic_id != FALSE) // from GET
		{
			$pic_id_sql = $pic_id;
		}
		else
		{
			// Check $pic_id[] on POST Method now
			if( isset($_POST['pic_id']) )
			{
				$pic_id = $_POST['pic_id'];
				if( is_array($pic_id) )
				{
					$pic_id_sql = implode(',', $pic_id);
				}
				else
				{
					trigger_error('Invalid request', E_USER_WARNING);
				}
			}
			else
			{
				trigger_error('No pics specified', E_USER_WARNING);
			}
		}

		// well, we got the array of pic_id but we must do a check to make sure all these
		// pics are in this category (prevent some naughty moderators to access un-authorised pics)
		$sql = "SELECT pic_id
				FROM ". ALBUM_TABLE ."
				WHERE pic_id IN ($pic_id_sql) AND pic_cat_id <> $cat_id";
		$result = $db->sql_query($sql);
		if( $db->sql_affectedrows($result) > 0 )
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}

		// update the DB
		$sql = "UPDATE ". ALBUM_TABLE ."
				SET pic_lock = 0
				WHERE pic_id IN ($pic_id_sql)";
		$result = $db->sql_query($sql);

		$message = $user->lang['IMAGES_UNLOCKED_SUCCESSFULLY'] .'<br /><br />';

		if ($cat_id != PERSONAL_GALLERY)
		{
			$message .= sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("album.$phpEx?id=$cat_id") . "\">", "</a>") .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_MODCP'], "<a href=\"" . append_sid("mcp.$phpEx?cat_id=$cat_id") . "\">", "</a>") . "<br /><br />";
		}
		else
		{
			$message .= sprintf($user->lang['CLICK_RETURN_PERSONAL_ALBUM'], "<a href=\"" . append_sid("album_personal.$phpEx?user_id=$user_id") . "\">", "</a>");
		}

		$message .= '<br /><br />' . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

		trigger_error($message, E_USER_WARNING);
	}
	else if ($mode == 'approval')
	{
		//-----------------------------
		// APPROVAL
		//-----------------------------

		// we must check POST method now
		if ($pic_id != FALSE) // from GET
		{
			$pic_id_sql = $pic_id;
		}
		else
		{
			// Check $pic_id[] on POST Method now
			if( isset($_POST['pic_id']) )
			{
				$pic_id = $_POST['pic_id'];
				if( is_array($pic_id) )
				{
					$pic_id_sql = implode(',', $pic_id);
				}
				else
				{
					trigger_error('Invalid request', E_USER_WARNING);
				}
			}
			else
			{
				trigger_error('No pics specified', E_USER_WARNING);
			}
		}

		// well, we got the array of pic_id but we must do a check to make sure all these
		// pics are in this category (prevent some naughty moderators to access un-authorised pics)
		$sql = "SELECT pic_id
				FROM ". ALBUM_TABLE ."
				WHERE pic_id IN ($pic_id_sql) AND pic_cat_id <> $cat_id";
		$result = $db->sql_query($sql);
		if( $db->sql_affectedrows($result) > 0 )
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}

		// update the DB
		$sql = "UPDATE ". ALBUM_TABLE ."
				SET pic_approval = 1
				WHERE pic_id IN ($pic_id_sql)";
		$result = $db->sql_query($sql);

		$message = $user->lang['IMAGES_APPROVED_SUCCESSFULLY'] .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("album.$phpEx?id=$cat_id") . "\">", "</a>") .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_MODCP'], "<a href=\"" . append_sid("mcp.$phpEx?cat_id=$cat_id") . "\">", "</a>") . "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

		trigger_error($message, E_USER_WARNING);
	}
	else if ($mode == 'unapproval')
	{
		//-----------------------------
		// UNAPPROVAL
		//-----------------------------

		// we must check POST method now
		if ($pic_id != FALSE) // from GET
		{
			$pic_id_sql = $pic_id;
		}
		else
		{
			// Check $pic_id[] on POST Method now
			if( isset($_POST['pic_id']) )
			{
				$pic_id = $_POST['pic_id'];
				if( is_array($pic_id) )
				{
					$pic_id_sql = implode(',', $pic_id);
				}
				else
				{
					trigger_error('Invalid request', E_USER_WARNING);
				}
			}
			else
			{
				trigger_error('No pics specified', E_USER_WARNING);
			}
		}

		// well, we got the array of pic_id but we must do a check to make sure all these
		// pics are in this category (prevent some naughty moderators to access un-authorised pics)
		$sql = "SELECT pic_id
				FROM ". ALBUM_TABLE ."
				WHERE pic_id IN ($pic_id_sql) AND pic_cat_id <> $cat_id";
		$result = $db->sql_query($sql);
		if( $db->sql_affectedrows($result) > 0 )
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}

		// update the DB
		$sql = "UPDATE ". ALBUM_TABLE ."
				SET pic_approval = 0
				WHERE pic_id IN ($pic_id_sql)";
		$result = $db->sql_query($sql);

		$message = $user->lang['IMAGES_UNAPPROVED_SUCCESSFULLY'] .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("album.$phpEx?id=$cat_id") . "\">", "</a>") .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_MODCP'], "<a href=\"" . append_sid("mcp.$phpEx?cat_id=$cat_id") . "\">", "</a>") . "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

		trigger_error($message, E_USER_WARNING);
	}
	else if ($mode == 'delete')
	{
		//-----------------------------
		// DELETE
		//-----------------------------

		if ($auth_data['delete'] == 0)
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}

		if( !isset($_POST['confirm']) )
		{
			// we must check POST method now
			$pic_id_array = array();
			if ($pic_id != FALSE) // from GET
			{
				$pic_id_array[] = $pic_id;
			}
			else
			{
				// Check $pic_id[] on POST Method now
				if( isset($_POST['pic_id']) )
				{
					$pic_id_array = $_POST['pic_id'];
					if( !is_array($pic_id_array) )
					{
						trigger_error('Invalid request', E_USER_WARNING);
					}
				}
				else
				{
					trigger_error('No pics specified', E_USER_WARNING);
				}
			}
      if ( isset($_POST['cancel']) )
         {
            $redirect = "mcp.$phpEx?cat_id=$cat_id";
            redirect(append_sid($redirect, true));
         }			

			// We must send out the $pic_id_array to store data between page changing
			$hidden_field = '';
			for ($i = 0; $i < count($pic_id_array); $i++)
			{
				$hidden_field .= '<input name="pic_id[]" type="hidden" value="'. $pic_id_array[$i] .'" />' . "\n";
			}

			$template->assign_vars(array(
				'MESSAGE_TITLE' => $user->lang['CONFIRM'],
				'MESSAGE_TEXT' => $user->lang['ALBUM_DELETE_CONFIRM'],
				'S_HIDDEN_FIELDS' => $hidden_field,
				'L_NO' => $user->lang['NO'],
				'L_YES' => $user->lang['YES'],
				'S_CONFIRM_ACTION' => append_sid("mcp.$phpEx?mode=delete&amp;cat_id=$cat_id"),
				)
			);

			// Output page
			$page_title = $user->lang['GALLERY'];
			
			page_header($page_title);
			
			$template->set_filenames(array(
				'body' => 'confirm_body.html')
			);
			
			page_footer();
		}
		else
		{
			//
			// Do the delete here...
			//
			if( isset($_POST['pic_id']) )
			{
				$pic_id = $_POST['pic_id'];
				if( is_array($pic_id) )
				{
					$pic_id_sql = implode(',', $pic_id);
				}
				else
				{
					trigger_error('Invalid request', E_USER_WARNING);
				}
			}
			else
			{
				trigger_error('No pics specified', E_USER_WARNING);
			}

			// well, we got the array of pic_id but we must do a check to make sure all these
			// pics are in this category (prevent some naughty moderators to access un-authorised pics)
			$sql = "SELECT pic_id
					FROM ". ALBUM_TABLE ."
					WHERE pic_id IN ($pic_id_sql) AND pic_cat_id <> $cat_id";
			$result = $db->sql_query($sql);
			if( $db->sql_affectedrows($result) > 0 )
			{
				trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
			}

			// Delete all comments
			$sql = "DELETE FROM ". ALBUM_COMMENT_TABLE ."
					WHERE comment_pic_id IN ($pic_id_sql)";
			$result = $db->sql_query($sql);

			// Delete all ratings
			$sql = "DELETE FROM ". ALBUM_RATE_TABLE ."
					WHERE rate_pic_id IN ($pic_id_sql)";
			$result = $db->sql_query($sql);

			// Delete Physical Files
			// first we need filenames
			$sql = "SELECT pic_filename, pic_thumbnail
					FROM ". ALBUM_TABLE ."
					WHERE pic_id IN ($pic_id_sql)";
			$result = $db->sql_query($sql);
			$filerow = array();
			while( $row = $db->sql_fetchrow($result) )
			{
				$filerow[] = $row;
			}
			for ($i = 0; $i < count($filerow); $i++)
			{
				if( ($filerow[$i]['pic_thumbnail'] != '') && (@file_exists(ALBUM_CACHE_PATH . $filerow[$i]['pic_thumbnail'])) )
				{
					@unlink(ALBUM_CACHE_PATH . $filerow[$i]['pic_thumbnail']);
				}
				@unlink(ALBUM_UPLOAD_PATH . $filerow[$i]['pic_filename']);
			}

			// Delete DB entry
			$sql = "DELETE FROM ". ALBUM_TABLE ."
					WHERE pic_id IN ($pic_id_sql)";
			$result = $db->sql_query($sql);

			$message = $user->lang['IMAGES_DELETED_SUCCESSFULLY'] .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("album.$phpEx?id=$cat_id") . "\">", "</a>") .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_MODCP'], "<a href=\"" . append_sid("mcp.$phpEx?cat_id=$cat_id") . "\">", "</a>") . "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

			trigger_error($message, E_USER_WARNING);
		}
	}
	else
	{
		trigger_error('Invalid_mode', E_USER_WARNING);
	}
}


// +------------------------------------------------------+
// |  Powered by Photo Album 2.x.x (c) 2002-2003 Smartor  |
// +------------------------------------------------------+


?>