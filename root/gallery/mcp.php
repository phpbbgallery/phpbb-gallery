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
$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');


//
// Get general album information
//
include($phpbb_root_path . $gallery_root_path . 'includes/common.'.$phpEx);

// ------------------------------------
// set $mode (select action)
// ------------------------------------
$mode = request_var('mode', '');
$target = request_var('target', 0);

if (isset($_POST['mode']))
{
	// Oh data from Mod CP
	if (isset($_POST['move']))
	{
		$mode = 'move';
	}
	else if (isset($_POST['lock']))
	{
		$mode = 'lock';
	}
	else if (isset($_POST['unlock']))
	{
		$mode = 'unlock';
	}
	else if (isset($_POST['delete']))
	{
		$mode = 'delete';
	}
	else if (isset($_POST['approval']))
	{
		$mode = 'approval';
	}
	else if (isset($_POST['unapproval']))
	{
		$mode = 'unapproval';
	}
	else
	{
		$mode = '';
	}
}
else if (isset($_GET['mode']))
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
// Get the $pic_id from GET method then query out the category
// If $pic_id not found we will assign it to FALSE
// We will check $pic_id[] in POST method later (in $mode carry out)
// ------------------------------------

$pic_id = request_var('image_id', 0);
$album_id = request_var('album_id', 0);

if($pic_id)
{
	$sql = 'SELECT *
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_id = ' . $pic_id;
	$result = $db->sql_query($sql);

	$thispic = $db->sql_fetchrow($result);

	if (empty($thispic))
	{
		trigger_error($user->lang['IMAGE_NOT_EXIST'], E_USER_WARNING);
	}

	$album_id = $thispic['image_album_id'];
	$user_id = $thispic['image_user_id'];
}


// ------------------------------------
// Get the cat info
// ------------------------------------
$sql = 'SELECT *
	FROM ' . GALLERY_ALBUMS_TABLE . '
	WHERE album_id = ' . $album_id;
$result = $db->sql_query($sql);
$thiscat = $db->sql_fetchrow($result);

if (empty($thiscat))
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}

$auth_data = (!$thiscat['album_user_id']) ? album_user_access($album_id, $thiscat, 0, 0, 0, 0, 0, 0) : personal_album_access($thiscat['album_user_id']); // MODERATOR only
//
// END category info
//

// ------------------------------------
// Salting the form...yumyum ...
// ------------------------------------
add_form_key('mcp');


// ------------------------------------
// Check the permissions
// ------------------------------------
if (!$auth_data['moderator'])
{
	if ($user->data['is_bot'])
	{
		redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
	}
	if (!$user->data['is_registered'])
	{
		login_box("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?album_id=$album_id", $user->lang['LOGIN_INFO']);
	}
	else
	{
		trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
	}
}


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
	$sort_method = request_var('sort_method', 'image_time');
	$sort_order = request_var('sort_order', 'DESC');

	// Count Pics
	$sql = 'SELECT COUNT(image_id) AS count
		FROM ' . GALLERY_IMAGES_TABLE . '
		WHERE image_album_id = ' . $album_id;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

	$total_pics = $row['count'];

	$pics_per_page = ($album_config['rows_per_page'] * $album_config['cols_per_page']);

	// get information from DB
	if ($total_pics > 0)
	{
		$limit_sql = (!$start) ? $pics_per_page : $start . ', ' . $pics_per_page;

		$pic_approval_sql = '';
		if (($user->data['user_type'] <> USER_FOUNDER) && ($thiscat['album_approval'] == ALBUM_ADMIN))
		{
			// because he went through my Permission Checking above so he must be at least a Moderator
			$pic_approval_sql = ' AND p.image_approval = 1';
		}

		$sql = 'SELECT i.*, r.rate_image_id, AVG(r.rate_point) AS rating, COUNT(c.comment_id) AS comments, MAX(c.comment_id) AS new_comment
			FROM ' . GALLERY_IMAGES_TABLE . ' AS i
			LEFT JOIN ' . GALLERY_RATES_TABLE . ' AS r
				ON i.image_id = r.rate_image_id
			LEFT JOIN ' . GALLERY_COMMENTS_TABLE . ' AS c
				ON i.image_id = c.comment_image_id
			WHERE i.image_album_id = ' . $album_id . ' ' . $pic_approval_sql . '
			GROUP BY i.image_id
			ORDER BY ' . $sort_method . ' ' . $sort_order . '
			LIMIT ' . $limit_sql;
		$result = $db->sql_query($sql);

		$picrow = array();

		while( $row = $db->sql_fetchrow($result) )
		{
			$picrow[] = $row;
		}

		for ($i = 0; $i <count($picrow); $i++)
		{
			$template->assign_block_vars('picrow', array(
				'IMAGE_ID'		=> $picrow[$i]['image_id'],
				'U_IMAGE_THUMB'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}thumbnail.$phpEx?album_id=" . $picrow[$i]['image_album_id'] . "&amp;image_id=". $picrow[$i]['image_id']),
				'U_IMAGE'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx?album_id=" . $picrow[$i]['image_album_id'] . "&amp;image_id=". $picrow[$i]['image_id']),
				'IMAGE_NAME'	=> $picrow[$i]['image_name'],
				'POSTER'		=> get_username_string('full', $picrow[$i]['image_user_id'], ($picrow[$i]['image_user_id'] <> ANONYMOUS) ? $picrow[$i]['image_username'] : $user->lang['GUEST'], $picrow[$i]['image_user_colour']),
				'TIME'			=> $user->format_date($picrow[$i]['image_time']),
				'RATING'		=> ($picrow[$i]['rating'] == 0) ? $user->lang['NOT_RATED'] : round($picrow[$i]['rating'], 2),
				'COMMENTS'		=> $picrow[$i]['comments'],
				'LOCK'			=> ($picrow[$i]['image_lock'] == 0) ? '' : $user->lang['LOCKED'],
				'APPROVAL'		=> ($picrow[$i]['image_approval'] == 0) ? $user->lang['NOT_APPROVED'] : $user->lang['APPROVED'],
			));
		}

		$template->assign_vars(array(
			'PAGINATION' 	=> generate_pagination(append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?album_id=$album_id&amp;sort_method=$sort_method&amp;sort_order=$sort_order"), $total_pics, $pics_per_page, $start),
			'PAGE_NUMBER' 	=> sprintf($user->lang['PAGE_OF'], ( floor( $start / $pics_per_page ) + 1 ), ceil( $total_pics / $pics_per_page )),
			'THUMB_WIDTH'	=> $album_config['thumbnail_size'] + 5,
		));
	}
	else
	{
		// No Pics
		$template->assign_block_vars('no_pics', array());
	}

	$sort_rating_option = sort_new_comment_option = $sort_comments_option = '';
	
	if ($album_config['rate'])
	{
		$sort_rating_option  = '<option value="rating" ';
		$sort_rating_option .= ($sort_method == 'rating') ? 'selected="selected"' : '';
		$sort_rating_option .= '>' . $user->lang['RATING'] . '</option>';
	}

	if ($album_config['comment'])
	{
		$sort_comments_option  = '<option value="comments" ';
		$sort_comments_option .= ($sort_method == 'comments') ? 'selected="selected"' : '';
		$sort_comments_option .= '>' . $user->lang['COMMENTS'] . '</option>';
		$sort_new_comment_option  = '<option value="new_comment" ';
		$sort_new_comment_option .= ($sort_method == 'new_comment') ? 'selected="selected"' : '';
		$sort_new_comment_option .= '>' . $user->lang['NEW_COMMENT'] . '</option>';
	}

	$template->assign_vars(array(
		'U_VIEW_CAT' 			=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?album_id=$album_id"),
		'CAT_TITLE' 			=> $thiscat['album_name'],
		'S_ALBUM_ACTION' 		=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?album_id=$album_id"),
		'DELETE_BUTTON' 		=> ($auth_data['delete']) ? '<input type="submit" class="liteoption" name="delete" value="' . $user->lang['DELETE'] . '" />' : '',
		'APPROVAL_BUTTON' 		=> (($user->data['user_type'] <> USER_FOUNDER) && ($thiscat['album_approval'] == ALBUM_ADMIN)) ? '' : '<input type="submit" class="liteoption" name="approval" value="' . $user->lang['APPROVE'] . '" />',
		'UNAPPROVAL_BUTTON' 	=> (($user->data['user_type'] <> USER_FOUNDER) && ($thiscat['album_approval'] == ALBUM_ADMIN)) ? '' : '<input type="submit" class="liteoption" name="unapproval" value="' . $user->lang['UNAPPROVE'] . '" />',

		'SORT_TIME' 			=> ($sort_method == 'image_time') ? 'selected="selected"' : '',
		'SORT_IMAGE_NAME' 		=> ($sort_method == 'image_name') ? 'selected="selected"' : '',
		'SORT_USERNAME' 		=> ($sort_method == 'image_username') ? 'selected="selected"' : '',
		'SORT_VIEW' 			=> ($sort_method == 'image_view_count') ? 'selected="selected"' : '',

		'SORT_RATING_OPTION' 		=> $sort_rating_option,
		'SORT_COMMENTS_OPTION' 		=> $sort_comments_option,
		'SORT_NEW_COMMENT_OPTION' 	=> $sort_new_comment_option,

		'SORT_ASC' 				=> ($sort_order == 'ASC') ? 'selected="selected"' : '',
		'SORT_DESC' 			=> ($sort_order == 'DESC') ? 'selected="selected"' : '',
	));

	generate_album_nav($thiscat);

	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $user->lang['MODCP'],
		'U_VIEW_FORUM'	=> append_sid("{$album_root_path}mcp.$phpEx", 'album_id=' . $thiscat['album_id']),
	));

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

		$target = request_var('target', 0);
		if(!$target)
		{
			// if "target" has not been set, we will open the category select form
			//
			// we must check POST method now
			$pic_id_array = array();
			if ($pic_id <> FALSE) // from GET
			{
				$pic_id_array[] = $pic_id;
			}
			else
			{
				// Check $pic_id[] on POST Method now
				if (isset($_POST['image_id']))
				{
					$pic_id_array = $_POST['image_id'];
					if (!is_array($pic_id_array))
					{
						trigger_error($user->lang['INVALID_REQUEST'], E_USER_WARNING);
					}
				}
				else
				{
					trigger_error($user->lang['NO_IMAGE_SPECIFIED'], E_USER_WARNING);
				}
			}

			// We must send out the $pic_id_array to store data between page changing
			for ($i = 0; $i < count($pic_id_array); $i++)
			{
				$template->assign_block_vars('pic_id_array', array(
					'VALUE' => $pic_id_array[$i],
				));
			}

			//
			// Create categories select
			//
			$sql = 'SELECT *
				FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE album_id <> ' . $album_id . '
				ORDER BY left_id ASC';
			$result = $db->sql_query($sql);
			$catrows = array();

			while( $row = $db->sql_fetchrow($result) )
			{
				$album_user_access = album_user_access($row['album_id'], $row, 0, 1, 0, 0, 0, 0);
				if ($album_user_access['upload'])
				{
					$catrows[] = $row;
				}
			}

			if (count($catrows) == 0)
			{
				trigger_error($user->lang['NO_MOVE_LEFT'], E_USER_WARNING);
			}

			// write categories out
			$category_select = '<select name="target">';

			$category_select .= make_move_jumpbox($album_id);

			$category_select .= '</select>';
			// end write

			$template->assign_vars(array(
				'S_ALBUM_ACTION'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?mode=move&amp;album_id=$album_id"),
				'S_ALBUM_SELECT'		=> $category_select,
			));

			generate_album_nav($thiscat);

			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $user->lang['MODCP'],
				'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'album_id=' . $thiscat['album_id']),
			));

			// Output page
			$page_title = $user->lang['GALLERY'];
			page_header($page_title);
			$template->set_filenames(array(
				'body' => 'gallery_move_body.html',
			));
			page_footer();
		}
		else
		{
			generate_album_nav($thiscat);

			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $user->lang['MODCP'],
				'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'album_id=' . $thiscat['album_id']),
			));

			// Check the salt... yumyum
			if (!check_form_key('mcp'))
			{
				trigger_error('FORM_INVALID');
			}
			// Do the MOVE action
			//
			// Now we only get $pic_id[] via POST (after the select target screen)
			if (isset($_POST['image_id']))
			{
				$pic_id = $_POST['image_id'];
				if( is_array($pic_id) )
				{
					$pic_id_sql = implode(',', $pic_id);
				}
				else
				{
					trigger_error($user->lang['INVALID_REQUEST'], E_USER_WARNING);
				}
			}
			else
			{
				trigger_error($user->lang['NO_IMAGE_SPECIFIED'], E_USER_WARNING);
			}

			// well, we got the array of pic_id but we must do a check to make sure all these
			// pics are in this category (prevent some naughty moderators to access un-authorised pics)
			$sql = 'SELECT image_id
				FROM ' . GALLERY_IMAGES_TABLE . '
				WHERE image_id IN (' . $pic_id_sql . ') 
					AND image_album_id <> ' . $album_id;
			$result = $db->sql_query($sql);

			if( $db->sql_affectedrows($result) > 0 )
			{
				trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
			}

			// Update the DB
			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
				SET image_album_id = ' . intval($target) . '
				WHERE image_id IN (' . $pic_id_sql . ')';
			$result = $db->sql_query($sql);
			$message = $user->lang['IMAGES_MOVED_SUCCESSFULLY'] .'<br /><br />'
				. sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx?album_id=$album_id") . "\">", "</a>")
				. '<br />' . sprintf($user->lang['CLICK_RETURN_ALBUM_TARGET'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx?album_id=$target") . "\">", "</a>")
				. '<br />' . sprintf($user->lang['CLICK_RETURN_MODCP'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?album_id=$album_id") . "\">", "</a>")
				. '<br />' . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx") . "\">", "</a>");
			trigger_error($message);
		}
	}
	else if ($mode == 'lock')
	{
		//-----------------------------
		// LOCK
		//-----------------------------

		generate_album_nav($thiscat);

		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['MODCP'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'album_id=' . $thiscat['album_id']),
		));

		// we must check POST method now
		if ($pic_id <> FALSE) // from GET
		{
			$pic_id_sql = $pic_id;
		}
		else
		{
			// Check $pic_id[] on POST Method now
			if (isset($_POST['image_id']))
			{
				$pic_id = $_POST['image_id'];
				if( is_array($pic_id) )
				{
					$pic_id_sql = implode(',', $pic_id);
				}
				else
				{
					trigger_error($user->lang['INVALID_REQUEST'], E_USER_WARNING);
				}
			}
			else
			{
				trigger_error($user->lang['NO_IMAGE_SPECIFIED'], E_USER_WARNING);
			}
		}

		// well, we got the array of image_id but we must do a check to make sure all these
		// pics are in this category (prevent some naughty moderators to access un-authorised pics)
		$sql = 'SELECT image_id
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_id IN (' . $pic_id_sql . ') 
				AND image_album_id <> ' . $album_id;
		$result = $db->sql_query($sql);
		if ($db->sql_affectedrows($result) > 0)
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}

		// update the DB
		$sql = 'UPDATE '. GALLERY_IMAGES_TABLE . '
			SET image_lock = 1
			WHERE image_id IN (' . $pic_id_sql . ')';
		$result = $db->sql_query($sql);

		$message = $user->lang['IMAGES_LOCKED_SUCCESSFULLY'] .'<br /><br />';
		$message .= sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx?album_id=$album_id") . "\">", "</a>") .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_MODCP'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?album_id=$album_id") . "\">", "</a>") . "<br /><br />";

		$message .= '<br /><br />' . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx") . "\">", "</a>");
		trigger_error($message, E_USER_WARNING);
	}
	else if ($mode == 'unlock')
	{
		//-----------------------------
		// UNLOCK
		//-----------------------------

		generate_album_nav($thiscat);

		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['MODCP'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'album_id=' . $thiscat['album_id']),
		));

		// we must check POST method now
		if ($pic_id <> FALSE) // from GET
		{
			$pic_id_sql = $pic_id;
		}
		else
		{
			// Check $pic_id[] on POST Method now
			if( isset($_POST['image_id']) )
			{
				$pic_id = $_POST['image_id'];
				if( is_array($pic_id) )
				{
					$pic_id_sql = implode(',', $pic_id);
				}
				else
				{
					trigger_error($user->lang['INVALID_REQUEST'], E_USER_WARNING);
				}
			}
			else
			{
				trigger_error($user->lang['NO_IMAGE_SPECIFIED'], E_USER_WARNING);
			}
		}

		// well, we got the array of image_id but we must do a check to make sure all these
		// pics are in this category (prevent some naughty moderators to access un-authorised pics)
		$sql = 'SELECT image_id
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_id IN (' . $pic_id_sql . ') 
				AND image_album_id <> ' . $album_id;
		$result = $db->sql_query($sql);
		if( $db->sql_affectedrows($result) > 0 )
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}
		
		// update the DB
		$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
			SET image_lock = 0
			WHERE image_id IN (' . $pic_id_sql . ')';
		$result = $db->sql_query($sql);

		$message = $user->lang['IMAGES_UNLOCKED_SUCCESSFULLY'] . '<br /><br />';

		$message .= sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx?id=$album_id") . "\">", "</a>") .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_MODCP'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?album_id=$album_id") . "\">", "</a>") . "<br /><br />";

		$message .= '<br /><br />' . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx") . "\">", "</a>");
		trigger_error($message, E_USER_WARNING);
	}
	else if ($mode == 'approval')
	{
		//-----------------------------
		// APPROVAL
		//-----------------------------

		generate_album_nav($thiscat);

		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['MODCP'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'album_id=' . $thiscat['album_id']),
		));

		// we must check POST method now
		if ($pic_id <> FALSE) // from GET
		{
			$pic_id_sql = $pic_id;
		}
		else
		{
			// Check $pic_id[] on POST Method now
			if( isset($_POST['image_id']) )
			{
				$pic_id = $_POST['image_id'];
				if( is_array($pic_id) )
				{
					$pic_id_sql = implode(',', $pic_id);
				}
				else
				{
					trigger_error($user->lang['INVALID_REQUEST'], E_USER_WARNING);
				}
			}
			else
			{
				trigger_error($user->lang['NO_IMAGE_SPECIFIED'], E_USER_WARNING);
			}
		}

		// well, we got the array of pic_id but we must do a check to make sure all these
		// pics are in this category (prevent some naughty moderators to access un-authorised pics)
		$sql = 'SELECT image_id
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_id IN (' . $pic_id_sql . ') 
				AND image_album_id <> ' . $album_id;
		$result = $db->sql_query($sql);
		if( $db->sql_affectedrows($result) > 0 )
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}

		// update the DB
		$sql = 'UPDATE '. GALLERY_IMAGES_TABLE . '
			SET image_approval = 1
			WHERE image_id IN (' . $pic_id_sql . ')';
		$result = $db->sql_query($sql);

		$message = $user->lang['IMAGES_APPROVED_SUCCESSFULLY'] .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx?album_id=$album_id") . "\">", "</a>") .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_MODCP'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?album_id=$album_id") . "\">", "</a>") . "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx") . "\">", "</a>");
		trigger_error($message, E_USER_WARNING);
	}
	else if ($mode == 'unapproval')
	{
		//-----------------------------
		// UNAPPROVAL
		//-----------------------------

		generate_album_nav($thiscat);

		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['MODCP'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'album_id=' . $thiscat['album_id']),
		));

		// we must check POST method now
		if ($pic_id <> FALSE) // from GET
		{
			$pic_id_sql = $pic_id;
		}
		else
		{
			// Check $pic_id[] on POST Method now
			if( isset($_POST['image_id']) )
			{
				$pic_id = $_POST['image_id'];
				if( is_array($pic_id) )
				{
					$pic_id_sql = implode(',', $pic_id);
				}
				else
				{
					trigger_error($user->lang['INVALID_REQUEST'], E_USER_WARNING);
				}
			}
			else
			{
				trigger_error($user->lang['NO_IMAGE_SPECIFIED'], E_USER_WARNING);
			}
		}

		// well, we got the array of pic_id but we must do a check to make sure all these
		// pics are in this category (prevent some naughty moderators to access un-authorised pics)
		$sql = 'SELECT image_id
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_id IN (' . $pic_id_sql . ') 
				AND image_album_id <> ' . $album_id;
		$result = $db->sql_query($sql);

		if ($db->sql_affectedrows($result) > 0)
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}

		// update the DB
		$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
			SET image_approval = 0
			WHERE image_id IN (' . $pic_id_sql . ')';
		$result = $db->sql_query($sql);

		$message = $user->lang['IMAGES_UNAPPROVED_SUCCESSFULLY'] .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx?album_id=$album_id") . "\">", "</a>") .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_MODCP'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?album_id=$album_id") . "\">", "</a>") . "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx") . "\">", "</a>");
		trigger_error($message, E_USER_WARNING);
	}
	else if ($mode == 'delete')
	{
		//-----------------------------
		// DELETE
		//-----------------------------

		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['GALLERY'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx"),
		));

		generate_album_nav($thiscat);

		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['MODCP'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'album_id=' . $thiscat['album_id']),
		));


		if (!$auth_data['delete'])
		{
			trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
		}

		if (!isset($_POST['confirm']))
		{
			// we must check POST method now
			$pic_id_array = array();
			if ($pic_id <> FALSE) // from GET
			{
				$pic_id_array[] = $pic_id;
			}
			else
			{
				// Check $pic_id[] on POST Method now
				if (isset($_POST['image_id']))
				{
					$pic_id_array = $_POST['image_id'];
					if( !is_array($pic_id_array) )
					{
						trigger_error($user->lang['INVALID_REQUEST'], E_USER_WARNING);
					}
				}
				else
				{
					trigger_error($user->lang['NO_IMAGE_SPECIFIED'], E_USER_WARNING);
				}
			}
			
			if (isset($_POST['cancel']))
			{
				$redirect = "{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?album_id=$album_id";
				redirect(append_sid($redirect, true));
			}

			// We must send out the $pic_id_array to store data between page changing
			$hidden_field = '';
			for ($i = 0; $i < count($pic_id_array); $i++)
			{
				$hidden_field .= '<input name="image_id[]" type="hidden" value="'. $pic_id_array[$i] .'" />' . "\n";
			}

			$template->assign_vars(array(
				'MESSAGE_TITLE' 	=> $user->lang['CONFIRM'],
				'MESSAGE_TEXT' 		=> $user->lang['ALBUM_DELETE_CONFIRM'],
				'S_HIDDEN_FIELDS' 	=> $hidden_field,
				'S_CONFIRM_ACTION' 	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?mode=delete&amp;album_id=$album_id"),
			));

			// Output page
			$page_title = $user->lang['GALLERY'];

			page_header($page_title);

			$template->set_filenames(array(
				'body' => 'confirm_body.html',
			));
			page_footer();
		}
		else
		{
			// Do the delete here...
			if (isset($_POST['image_id']))
			{
				$pic_id = $_POST['image_id'];
				if( is_array($pic_id) )
				{
					$pic_id_sql = implode(',', $pic_id);
				}
				else
				{
					trigger_error($user->lang['INVALID_REQUEST'], E_USER_WARNING);
				}
			}
			else
			{
				trigger_error($user->lang['NO_IMAGE_SPECIFIED'], E_USER_WARNING);
			}

			// well, we got the array of pic_id but we must do a check to make sure all these
			// pics are in this category (prevent some naughty moderators to access un-authorised pics)
			$sql = 'SELECT image_id
				FROM ' . GALLERY_IMAGES_TABLE . '
				WHERE image_id IN (' . $pic_id_sql . ') 
					AND image_album_id <> ' . $album_id;
			$result = $db->sql_query($sql);
			if ($db->sql_affectedrows($result) > 0)
			{
				trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
			}

			// Delete all comments
			$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . '
				WHERE comment_image_id IN (' . $pic_id_sql . ')';
			$result = $db->sql_query($sql);

			// Delete all ratings
			$sql = 'DELETE FROM ' .GALLERY_RATES_TABLE . '
				WHERE rate_image_id IN (' . $pic_id_sql . ')';
			$result = $db->sql_query($sql);

			// Delete Physical Files
			// first we need filenames
			$sql = 'SELECT image_filename, image_thumbnail
				FROM ' . GALLERY_IMAGES_TABLE . '
				WHERE image_id IN (' . $pic_id_sql . ')';
			$result = $db->sql_query($sql);

			$filerow = array();
			while( $row = $db->sql_fetchrow($result) )
			{
				$filerow[] = $row;
			}
			for ($i = 0; $i < count($filerow); $i++)
			{
				if( ($filerow[$i]['image_thumbnail'] <> '') && (@file_exists(ALBUM_CACHE_PATH . $filerow[$i]['image_thumbnail'])) )
				{
					@unlink(ALBUM_CACHE_PATH . $filerow[$i]['image_thumbnail']);
				}
				@unlink(ALBUM_UPLOAD_PATH . $filerow[$i]['image_filename']);
			}

			// Delete DB entry
			$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE image_id IN (' . $pic_id_sql . ')';
			$result = $db->sql_query($sql);

			$message = $user->lang['IMAGES_DELETED_SUCCESSFULLY'] .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx?album_id=$album_id") . "\">", "</a>") .'<br /><br />'. sprintf($user->lang['CLICK_RETURN_MODCP'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx?album_id=$album_id") . "\">", "</a>") . "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx") . "\">", "</a>");

			trigger_error($message, E_USER_WARNING);
		}
	}
	else
	{
		generate_album_nav($thiscat);

		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['MODCP'],
			'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'album_id=' . $thiscat['album_id']),
		));

		trigger_error('Invalid_mode', E_USER_WARNING);
	}
}
?>