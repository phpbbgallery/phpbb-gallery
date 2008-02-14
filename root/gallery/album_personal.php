<?php

//useless file, just kept for information

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
$user_id = request_var('user_id', $user->data['user_id']);

// ------------------------------------
// Check $user_id
// ------------------------------------

if ($user->data['is_bot'])
{
	redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
}


// ------------------------------------
// Get the username of this gallery's owner
// ------------------------------------

$sql = 'SELECT username, user_colour, user_id
		FROM ' . USERS_TABLE . '
		WHERE user_id = ' . $user_id . '
		LIMIT 1';
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
$username = $row['username'];
$user_colour = $row['user_colour'];
$user_id = $row['user_id'];

if( empty($username) )
{
	trigger_error('NO_USER', E_USER_WARNING);
}


// ------------------------------------
// Check Permissions
// ------------------------------------
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
// END check permissions
//


// ------------------------------------
// Check own gallery
// ------------------------------------

if ($user_id == $user->data['user_id'])
{
	if (!$personal_gallery_access['upload'])
	{
		trigger_error($user->lang['NOT_ALLOWED_TO_CREATE_PERSONAL_ALBUM'], E_USER_WARNING);
	}
}

//
// End check own gallery
//


// ------------------------------------
// Build the thumbnail page
// ------------------------------------

$start = request_var('start', 0);
$sort_method = request_var('sort_method', $album_config['sort_method']);
$sort_order = request_var('sort_order', $album_config['sort_order']);
$pics_per_page = $album_config['rows_per_page'] * $album_config['cols_per_page'];


// ------------------------------------
// Count Pics
// ------------------------------------

$sql = 'SELECT COUNT(image_id) AS count
		FROM ' . GALLERY_IMAGES_TABLE . '
		WHERE image_album_id = ' . PERSONAL_GALLERY . '
			AND image_user_id = ' . $user_id . '
		LIMIT 1';
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
$total_pics = $row['count'];


// ------------------------------------
// Build up
// ------------------------------------

if ($total_pics > 0)
{
	$limit_sql = ($start == 0) ? $pics_per_page : $start .','. $pics_per_page;

	$sql = 'SELECT i.*, r.rate_image_id, AVG(r.rate_point) AS rating, COUNT(DISTINCT c.comment_id) AS comments, MAX(c.comment_id) as new_comment
		FROM ' . GALLERY_IMAGES_TABLE . ' AS i
		LEFT JOIN ' . GALLERY_RATES_TABLE . ' AS r
			ON i.image_id = r.rate_image_id
		LEFT JOIN ' . GALLERY_COMMENTS_TABLE . ' AS c
			ON i.image_id = c.comment_image_id
		WHERE i.image_album_id = ' . PERSONAL_GALLERY . '
			AND i.image_user_id = ' . $user_id . '
		GROUP BY i.image_id
		ORDER BY ' . $sort_method . ' ' . $sort_order . ' 
		LIMIT ' . $limit_sql;
	$result = $db->sql_query($sql);
	$picrow = array();
	while( $row = $db->sql_fetchrow($result) )
	{
		$picrow[] = $row;
	}


	// --------------------------------
	// Thumbnails table
	// --------------------------------

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
			$message_parser				= new parse_message();
			$message_parser->message	= $picrow[$j]['image_desc'];
			$message_parser->decode_message($picrow[$j]['image_desc_uid']);

			$template->assign_block_vars('picrow.piccol', array(
				'U_PIC'			=> ($album_config['fullpic_popup']) ? append_sid("image.$phpEx?pic_id=" . $picrow[$j]['image_id']) : append_sid("image_page.$phpEx?image_id=" . $picrow[$j]['image_id']),
				'THUMBNAIL'		=> append_sid("thumbnail.$phpEx?pic_id=" . $picrow[$j]['image_id']),
				'DESC'			=> $message_parser->message,
				)
			);

			$template->assign_block_vars('picrow.pic_detail', array(
				'TITLE'		=> $picrow[$j]['image_name'],
				'TIME'		=> $user->format_date($picrow[$j]['image_time']),
				'VIEW'		=> $picrow[$j]['image_view_count'],
				'RATING'	=> ($album_config['rate'] == 1) ? ( '<a href="' . append_sid("image_page.$phpEx?image_id=" . $picrow[$j]['image_id']) . '#rating">' . $user->lang['RATING'] . '</a>: ' . $picrow[$j]['rating'] . '<br />') : '',
				'COMMENTS'	=> ($album_config['comment'] == 1) ? ( '<a href="' . append_sid("image_page.$phpEx?image_id=" . $picrow[$j]['image_id']) . '#comments">' . $user->lang['COMMENTS'] . '</a>: ' . $picrow[$j]['comments'] . '<br />') : '',
				'EDIT'		=> ( ($user->data['user_type'] == USER_FOUNDER) || ($user->data['user_id'] == $picrow[$j]['image_user_id']) ) ? '<a href="' . append_sid("edit.$phpEx?pic_id=" . $picrow[$j]['image_id']) . '">' . $user->lang['EDIT_IMAGE'] . '</a>' : '',
				'DELETE'	=> ( ($user->data['user_type'] == USER_FOUNDER) || ($user->data['user_id'] == $picrow[$j]['image_user_id']) ) ? '<a href="' . append_sid("image_delete.$phpEx?id=" . $picrow[$j]['image_id']) . '">' . $user->lang['DELETE_IMAGE'] . '</a>' : '',
				'LOCK'		=> ($user->data['user_type'] == USER_FOUNDER) ? '<a href="' . append_sid("mcp.$phpEx?mode=" . (($picrow[$j]['image_lock'] == 0) ? 'lock' : 'unlock') . "&amp;image_id=" . $picrow[$j]['image_id']) . '">'. (($picrow[$j]['image_lock'] == 0) ? $user->lang['LOCK'] : $user->lang['UNLOCK']) . '</a>' : '',
				'IP'		=> ($user->data['user_type'] == USER_FOUNDER) ? $user->lang['IP'] . ': <a href="http://www.nic.com/cgi-bin/whois.cgi?query=' . $picrow[$j]['image_user_ip'] . '">' . $picrow[$j]['image_user_ip'] . '</a><br />' : '',
				)
			);
		}
	}


	// --------------------------------
	// Pagination
	// --------------------------------

	$template->assign_vars(array(
		'PAGINATION'	=> generate_pagination(append_sid("album_personal.$phpEx?user_id=$user_id&amp;sort_method=$sort_method&amp;sort_order=$sort_order"), $total_pics, $pics_per_page, $start),
		'PAGE_NUMBER'	=> sprintf($user->lang['PAGE_OF'], ( floor( $start / $pics_per_page ) + 1 ), ceil( $total_pics / $pics_per_page )),
		)
	);
}
else
{
	$template->assign_block_vars('no_pics', array());
}


/*
+----------------------------------------------------------
| Main page...
+----------------------------------------------------------
*/

// ------------------------------------
// additional sorting options
// ------------------------------------

$sort_rating_option = '';
$sort_comments_option = '';
if( $album_config['rate'] == 1 )
{
	$sort_rating_option  = '<option value="rating" ';
	$sort_rating_option .= ($sort_method == 'rating') ? 'selected="selected"' : '';
	$sort_rating_option .= '>' . $user->lang['RATING'] . '</option>';
}
if( $album_config['comment'] == 1 )
{
	$sort_comments_option  = '<option value="comments" ';
	$sort_comments_option .= ($sort_method == 'comments') ? 'selected="selected"' : '';
	$sort_comments_option .= '>' . $user->lang['COMMENTS'] . '</option>';

	$sort_new_comment_option  = '<option value="new_comment" ';
	$sort_new_comment_option .= ($sort_method == 'new_comment') ? 'selected="selected"' : '';
	$sort_new_comment_option .= '>' . $user->lang['NEW_COMMENT'] . '</option>';
}

if( $user_id == $user->data['user_id'] )
{
	$template->assign_vars(array('S_YOUR_PERSONAL_GALLERY' => true));
}

$template->assign_vars(array(
	'U_UPLOAD_PIC'					=> append_sid("upload.$phpEx?album_id=" . PERSONAL_GALLERY),
	'PERSONAL_ALBUM_NOT_CREATED'	=> sprintf($user->lang['PERSONAL_ALBUM_NOT_CREATED'], get_username_string('full', $user_id, $username, $user_colour)),
	'TARGET_BLANK'					=> ($album_config['fullpic_popup']) ? 'target="_blank"' : '',

	'S_COLS'						=> $album_config['cols_per_page'],
	'S_COL_WIDTH'					=> (100/$album_config['cols_per_page']) . '%',
	'U_PERSONAL_GALLERY'			=> append_sid("album_personal.$phpEx?user_id=$user_id"),
	'PERSONAL_GALLERY_OF_USER'		=> sprintf($user->lang['PERSONAL_ALBUM_OF_USER'], $username),

	'SORT_TIME'						=> ($sort_method == 'image_time') ? 'selected="selected"' : '',
	'SORT_PIC_TITLE'				=> ($sort_method == 'image_name') ? 'selected="selected"' : '',
	'SORT_VIEW'						=> ($sort_method == 'image_view_count') ? 'selected="selected"' : '',

	'SORT_RATING_OPTION'			=> $sort_rating_option,
	'SORT_COMMENTS_OPTION'			=> $sort_comments_option,
	'SORT_NEW_COMMENT_OPTION'		=> $sort_new_comment_option,

	'SORT_ASC'						=> ($sort_order == 'ASC') ? 'selected="selected"' : '',
	'SORT_DESC'						=> ($sort_order == 'DESC') ? 'selected="selected"' : '',
));

/*
+----------------------------------------------------------
| Start output the page
+----------------------------------------------------------
*/

$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> $user->lang['PERSONAL_ALBUMS'],
	'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album_personal_index.$phpEx"),
));

$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'	=> sprintf($user->lang['PERSONAL_ALBUM_OF_USER'], $username),
	'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album_personal.$phpEx", 'user_id=' . $user_id),
));

$page_title = $user->lang['GALLERY'];

page_header($page_title);

$template->set_filenames(array(
	'body' => 'gallery_personal_body.html')
);

page_footer();

?>