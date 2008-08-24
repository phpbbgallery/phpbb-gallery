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
$user->add_lang('mods/exif_data');

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
$album_data = get_album_info($album_id);
if (empty($album_data))
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}

/**
* Check the permissions
*/
//echo gallery_acl_check('i_view', $album_id);
//echo gallery_acl_album_ids('i_view', 'array');
if (!gallery_acl_check('i_view', $album_id))
{
	if (!$user->data['is_registered'])
	{
		login_box("{$gallery_root_path}image_page.$phpEx?album_id=$album_id&amp;image_id=$image_id", $user->lang['LOGIN_INFO']);
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
if (!gallery_acl_check('a_moderate', $album_id) && ($image_data['image_status'] != 1))
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
		if (!$album_config['rate'] || !gallery_acl_check('i_rate', $album_id))
		{
			trigger_error($user->lang['NOT_AUTHORISED']);
		}
		else if ($already_rated)
		{
			trigger_error($user->lang['ALREADY_RATED']);
		}
		$rate_point = request_var('rate', 0);
		if( ($rate_point <= 0) || ($rate_point > $album_config['rate_scale']) )
		{
			trigger_error($user->lang['OUT_OF_RANGE_VALUE']);
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
if (gallery_acl_check('a_moderate', $album_id))
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
	if ($row['image_id'] == $image_id)
	{
		$previous_id = $last_id;
		$do_next = true;
	}
	$last_id = $row['image_id'];
}
$db->sql_freeresult($result);

//Get Watch- and Favorite-mode
$is_watching = $image_data['watch_id'];


$template->assign_vars(array(
	'U_VIEW_ALBUM'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx?album_id=$album_id"),

	'U_IMAGE'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx?album_id=$album_id&amp;image_id=$image_id"),
	'U_PREVIOUS'		=> ($previous_id) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx?album_id=$album_id&amp;image_id=$previous_id") : '',
	'U_NEXT'			=> ($next_id && ($next_id != $previous_id)) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx?album_id=$album_id&amp;image_id=$next_id") : '',
	'IMAGE_RSZ_WIDTH'	=> $album_config['preview_rsz_width'],
	'IMAGE_RSZ_HEIGHT'	=> $album_config['preview_rsz_height'],

	'EDIT_IMG'			=> $user->img('icon_post_edit', 'EDIT_IMAGE'),
	'DELETE_IMG'		=> $user->img('icon_post_delete', 'DELETE_IMAGE'),
	'REPORT_IMG'		=> $user->img('icon_post_report', 'REPORT_IMAGE'),
	'U_EDIT'			=> ((gallery_acl_check('i_edit', $album_id) && ($image_data['image_user_id'] == $user->data['user_id'])) || gallery_acl_check('a_moderate', $album_id)) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=edit&amp;album_id=$album_id&amp;image_id=$image_id") : '',
	'U_DELETE'			=> ((gallery_acl_check('i_delete', $album_id) && ($image_data['image_user_id'] == $user->data['user_id'])) || gallery_acl_check('a_moderate', $album_id)) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=delete&amp;album_id=$album_id&amp;image_id=$image_id") : '',
	'U_REPORT'			=> (gallery_acl_check('i_report', $album_id) && ($image_data['image_user_id'] != $user->data['user_id'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=report&amp;album_id=$album_id&amp;image_id=$image_id") : '',

	'IMAGE_NAME'		=> $image_data['image_name'],
	'IMAGE_DESC'		=> generate_text_for_display($image_data['image_desc'], $image_data['image_desc_uid'], $image_data['image_desc_bitfield'], 7),
	'IMAGE_BBCODE'		=> '[album]' . $image_id . '[/album]',
	'POSTER'			=> get_username_string('full', $image_data['image_user_id'], ($image_data['image_username']) ? $image_data['image_username'] : $user->lang['GUEST'], $image_data['image_user_colour']),
	'IMAGE_TIME'		=> $user->format_date($image_data['image_time']),
	'IMAGE_VIEW'		=> $image_data['image_view_count'],

	'L_BOOKMARK_TOPIC'	=> ($image_data['favorite_id']) ? $user->lang['UNFAVORITE_IMAGE'] : $user->lang['FAVORITE_IMAGE'],
	'U_BOOKMARK_TOPIC'	=> ($user->data['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=" . (($image_data['favorite_id']) ?  'un' : '') . "favorite&amp;album_id=$album_id&amp;image_id=$image_id") : '',
	'L_WATCH_TOPIC'		=> ($image_data['watch_id']) ? $user->lang['UNWATCH_IMAGE'] : $user->lang['WATCH_IMAGE'],
	'U_WATCH_TOPIC'		=> ($user->data['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=" . (($image_data['watch_id']) ?  'un' : '') . "watch&amp;album_id=$album_id&amp;image_id=$image_id") : '',
	'S_WATCHING_TOPIC'	=> ($image_data['watch_id']) ? true : false,
	'S_ALBUM_ACTION'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id"),

	'U_RETURN_LINK'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id"),
	'S_RETURN_LINK'		=> $album_data['album_name'],
	'S_JUMPBOX_ACTION'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx"),
	'ALBUM_JUMPBOX'		=> gallery_albumbox(false, '', $album_id),
));

if ($album_config['exif_data'] && ($image_data['image_has_exif'] > 0) && (substr($image_data['image_filename'], -3, 3) == 'jpg') && function_exists('exif_read_data'))
{
	$exif = @exif_read_data($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['image_filename'], 0, true);
	if (!empty($exif["EXIF"]))
	{
		$exif_date = $exif_focal =  $exif_aperture = $exif_exposure = $exif_iso = 
		$exif_exposureprogram = $exif_exposure_bias = $exif_metering_mode = 
		$exif_whitebalance = $exif_flash = $exif_make = $exif_model = $user->lang['EXIF_NOT_AVAILABLE'];

		if(isset($exif["EXIF"]["DateTimeOriginal"]))
		{
			$timestamp_year = substr($exif["EXIF"]["DateTimeOriginal"], 0, 4);
			$timestamp_month = substr($exif["EXIF"]["DateTimeOriginal"], 5, 2);
			$timestamp_day = substr($exif["EXIF"]["DateTimeOriginal"], 8, 2);
			$timestamp_hour = substr($exif["EXIF"]["DateTimeOriginal"], 11, 2);
			$timestamp_minute = substr($exif["EXIF"]["DateTimeOriginal"], 14, 2);
			$timestamp_second = substr($exif["EXIF"]["DateTimeOriginal"], 17, 2);
			$timestamp = mktime($timestamp_hour, $timestamp_minute, $timestamp_second, $timestamp_month, $timestamp_day, $timestamp_year);
			$exif_date = $user->format_date($timestamp);
		}
		if(isset($exif["EXIF"]["FocalLength"]))
		{
			list($num, $den) = explode("/", $exif["EXIF"]["FocalLength"]);
			$exif_focal  = ($num/$den);
		}
		if(isset($exif["EXIF"]["ExposureTime"]))
		{
			list($num, $den) = explode("/", $exif["EXIF"]["ExposureTime"]);
			$exif_exposure = '1/' . $den/$num;
		}
		if(isset($exif["EXIF"]["FNumber"]))
		{
			list($num,$den) = explode("/",$exif["EXIF"]["FNumber"]);
			$exif_aperture  = "F/" . ($num/$den);
		}
		if(isset($exif["EXIF"]["ISOSpeedRatings"]))
		{
			$exif_iso = $exif["EXIF"]["ISOSpeedRatings"];
		}
		if (isset($exif["EXIF"]["WhiteBalance"]))
		{
			$exif_whitebalance = $user->lang['EXIF_WHITEB_' . (($exif["EXIF"]["WhiteBalance"]) ? 'MANU' : 'AUTO')];
		}
		if(isset($exif["EXIF"]["Flash"]))
		{
			$exif_flash = $user->lang['EXIF_FLASH_CASE_' . $exif["EXIF"]["Flash"]];
		}
		if (isset($exif["IFD0"]["Model"]))
		{
			$exif_model = ucwords($exif["IFD0"]["Model"]);
		}
		if (isset($exif["EXIF"]["ExposureProgram"]))
		{
			$exif_exposureprogram = $user->lang['EXIF_EXPOSURE_PROG_' . $exif["EXIF"]["ExposureProgram"]];
		}
		if (isset($exif["EXIF"]["ExposureBiasValue"]))
		{
			$exif_exposure_bias = sprintf($user->lang['EXIF_EXPOSURE_BIAS_EXP'], $exif["EXIF"]["ExposureBiasValue"]);
		}
		if (isset($exif["EXIF"]["MeteringMode"]))
		{
			$exif_metering_mode = $user->lang['EXIF_METERING_MODE_' . $exif["EXIF"]["MeteringMode"]];
		}

		$template->assign_vars(array(
			'EXIF_DATE'			=> htmlspecialchars($exif_date),
			'EXIF_FOCAL'		=> htmlspecialchars($exif_focal),
			'EXIF_EXPOSURE'		=> htmlspecialchars($exif_exposure),
			'EXIF_APERTURE'		=> htmlspecialchars($exif_aperture),
			'EXIF_ISO'			=> htmlspecialchars($exif_iso),
			'EXIF_FLASH'		=> htmlspecialchars($exif_flash),
			'EXIF_EXPOSURE_PROG'	=> htmlspecialchars($exif_exposureprogram),
			'EXIF_EXPOSURE_BIAS'	=> htmlspecialchars($exif_exposure_bias),
			'EXIF_METERING_MODE'	=> htmlspecialchars($exif_metering_mode),

			'WHITEB'		=> htmlspecialchars($exif_whitebalance),
			'CAM_MODEL'		=> htmlspecialchars($exif_model),
			'S_EXIF_DATA'	=> true,
		));

		if ($image_data['image_has_exif'] == 2)
		{
			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
				SET image_has_exif = 1
				WHERE image_id = ' . $image_id;
			$db->sql_query($sql);
		}
	}
	else
	{
		$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
			SET image_has_exif = 0
			WHERE image_id = ' . $image_id;
		$db->sql_query($sql);
	}
}

if ($album_config['rate'])
{
	$template->assign_vars(array(
		'RATING'		=> $user->lang['RATING'],
		'IMAGE_RATING'	=> ($image_data['image_rates'] <> 0) ? $image_data['image_rate_avg'] / 100 : $user->lang['NOT_RATED'],
	));
	
	if (gallery_acl_check('i_rate', $album_id))
	{
		$ratebox = false;
		if ($user->data['user_id'] == ANONYMOUS)
		{
				$ratebox = '<a href="' . append_sid("{$phpbb_root_path}ucp.$phpEx", "mode=login&amp;redirect=" . urlencode("{$gallery_root_path}image_page.$phpEx?album_id=$album_id&image_id=$image_id")) . '">' . $user->lang['LOGIN_TO_RATE'] . '</a>';
		}
		else if ($user->data['user_id'] == $image_data['image_user_id'])
		{
			$ratebox = $user->lang['NO_RATE_ON_OWN_IMAGES'];
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
	
	if (gallery_acl_check('c_post', $album_id))
	{
		$template->assign_vars(array(
			'POST_COMMENT'			=> true,
			'YOUR_COMMENT'			=> true,
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
				'EDIT'			=> (gallery_acl_check('a_moderate', $album_id) || (gallery_acl_check('c_edit', $album_id) && ($commentrow[$i]['comment_user_id'] == $user->data['user_id']) && $user->data['is_registered'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=edit&amp;comment_id=" . $commentrow[$i]['comment_id']) : '',
				'DELETE'		=> (gallery_acl_check('a_moderate', $album_id) || (gallery_acl_check('c_delete', $album_id) && ($commentrow[$i]['comment_user_id'] == $user->data['user_id']) && $user->data['is_registered'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=delete&amp;comment_id=" . $commentrow[$i]['comment_id']) : '',
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
page_header($user->lang['VIEW_IMAGE'] . ' - ' . $image_data['image_name']);

$template->set_filenames(array(
	'body' => 'gallery_page_body.html')
);

page_footer();

?>