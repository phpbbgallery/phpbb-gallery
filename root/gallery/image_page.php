<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/

define('IN_PHPBB', true);
$phpbb_root_path = $gallery_root_path = '';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . $gallery_root_path . 'includes/root_path.' . $phpEx);
include($phpbb_root_path . 'common.' . $phpEx);
$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('mods/gallery', 'mods/exif_data'));

/**
* Filestructure:
*
* - Check the request and get image_data
* - Check the permissions and approval
* - Main work here...
* - Exif-Data
* - Rating
* - Posting comment
* - Listing comment
*
*/

include($phpbb_root_path . $gallery_root_path . 'includes/common.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/permissions.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/functions_users.' . $phpEx);

/**
* Check the request and get image_data
*/
$image_id = request_var('image_id', 0);
$image_data = get_image_info($image_id);

$album_id = $image_data['image_album_id'];
$album_data = get_album_info($album_id);

$user_id = $image_data['image_user_id'];

if (!file_exists($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['image_filename']))
{
	$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
		SET image_filemissing = 1
		WHERE image_id = ' . $image_id;
	$db->sql_query($sql);
	// Since we display error-images, we still view this page!
	//trigger_error('IMAGE_NOT_EXIST');
}

/**
* Check the permissions and approval
*/
if (!gallery_acl_check('i_view', $album_id, $album_data['album_user_id']))
{
	if (!$user->data['is_registered'])
	{
		login_box("{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id", $user->lang['LOGIN_INFO']);
	}
	else
	{
		trigger_error('NOT_AUTHORISED');
	}
}
if (!gallery_acl_check('m_status', $album_id, $album_data['album_user_id']) && ($image_data['image_status'] == IMAGE_UNAPPROVED))
{
	trigger_error('NOT_AUTHORISED');
}

// Build the navigation
generate_album_nav($album_data);
// Salting the form...yumyum ...
add_form_key('gallery');

/**
* Main work here...
*/
// Increase the counter, as we load the image with increment-blocker from this site it's no problem.
// We also copy some parts from topic_views here
if (isset($user->data['session_page']) && !$user->data['is_bot'] && (strpos($user->data['session_page'], '&image_id=' . $image_id) === false || isset($user->data['session_created'])))
{
	$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
		SET image_view_count = image_view_count + 1
		WHERE image_id = ' . $image_id;
	$db->sql_query($sql);
}

$image_approval_sql = ' AND image_status <> ' . IMAGE_UNAPPROVED;
if (gallery_acl_check('m_status', $album_id, $album_data['album_user_id']))
{
	$image_approval_sql = '';
}

//$sort_days	= request_var('st', 0);
$sort_key	= request_var('sk', $gallery_config['sort_method']);
$sort_dir	= request_var('sd', $gallery_config['sort_order']);

$sort_by_sql = array('t' => 'image_time', 'n' => 'image_name_clean', 'u' => 'image_username_clean', 'vc' => 'image_view_count', 'ra' => 'image_rate_avg', 'r' => 'image_rates', 'c' => 'image_comments', 'lc' => 'image_last_comment');
$sql_sort_by = (isset($sort_by_sql[$sort_key])) ? $sort_by_sql[$sort_key] : $sort_by_sql['t'];
if ($sort_dir == 'd')
{
	$sql_next_condition = '<';
	$sql_next_ordering = 'DESC';
	$sql_previous_condition = '>';
	$sql_previous_ordering = 'ASC';
}
else
{
	$sql_next_condition = '>';
	$sql_next_ordering = 'ASC';
	$sql_previous_condition = '<';
	$sql_previous_ordering = 'DESC';
}
// Two sqls now, but much better performance!
// As we do not allow to duplicate images, we can relay on the id as second sort parameter
$sql = 'SELECT image_id, image_name
	FROM ' . GALLERY_IMAGES_TABLE . '
	WHERE image_album_id = ' . (int) $album_id . $image_approval_sql . "
		AND (($sql_sort_by = '" . $db->sql_escape($image_data[$sql_sort_by]) . "' AND image_id $sql_next_condition {$image_id})
		OR $sql_sort_by $sql_next_condition '" . $db->sql_escape($image_data[$sql_sort_by]) . "')
	ORDER BY $sql_sort_by $sql_next_ordering";
$result = $db->sql_query_limit($sql, 1);
$next_data = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

$sql = 'SELECT image_id, image_name
	FROM ' . GALLERY_IMAGES_TABLE . '
	WHERE image_album_id = ' . (int) $album_id . $image_approval_sql . "
		AND (($sql_sort_by = '" . $db->sql_escape($image_data[$sql_sort_by]) . "' AND image_id $sql_previous_condition {$image_id})
		OR $sql_sort_by $sql_previous_condition '" . $db->sql_escape($image_data[$sql_sort_by]) . "')
	ORDER BY $sql_sort_by $sql_previous_ordering";
$result = $db->sql_query_limit($sql, 1);
$previous_data = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

$template->assign_vars(array(
	'U_VIEW_ALBUM'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id"),

	'UC_PREVIOUS_IMAGE'	=> generate_image_link('thumbnail', 'image_page', $previous_data['image_id'], $previous_data['image_name'], $album_id),
	'UC_PREVIOUS'		=> (!empty($previous_data)) ? generate_image_link('image_name_unbold', 'image_page_prev', $previous_data['image_id'], $previous_data['image_name'], $album_id) : '',
	'UC_IMAGE'			=> generate_image_link('medium', $gallery_config['link_imagepage'], $image_id, $image_data['image_name'], $album_id, ((substr($image_data['image_filename'], 0 -3) == 'gif') ? true : false), false),
	'UC_NEXT_IMAGE'		=> generate_image_link('thumbnail', 'image_page', $next_data['image_id'], $next_data['image_name'], $album_id),
	'UC_NEXT'			=> (!empty($next_data)) ? generate_image_link('image_name_unbold', 'image_page_next', $next_data['image_id'], $next_data['image_name'], $album_id) : '',

	'EDIT_IMG'			=> $user->img('icon_post_edit', 'EDIT_IMAGE'),
	'DELETE_IMG'		=> $user->img('icon_post_delete', 'DELETE_IMAGE'),
	'REPORT_IMG'		=> $user->img('icon_post_report', 'REPORT_IMAGE'),
	'STATUS_IMG'		=> $user->img('icon_post_info', 'STATUS_IMAGE'),
	'U_DELETE'			=> ((gallery_acl_check('i_delete', $album_id, $album_data['album_user_id']) && ($image_data['image_user_id'] == $user->data['user_id']) && ($album_data['album_status'] != ITEM_LOCKED)) || gallery_acl_check('m_delete', $album_id, $album_data['album_user_id'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=delete&amp;album_id=$album_id&amp;image_id=$image_id") : '',
	'U_EDIT'			=> ((gallery_acl_check('i_edit', $album_id, $album_data['album_user_id']) && ($image_data['image_user_id'] == $user->data['user_id']) && ($album_data['album_status'] != ITEM_LOCKED)) || gallery_acl_check('m_edit', $album_id, $album_data['album_user_id'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=edit&amp;album_id=$album_id&amp;image_id=$image_id") : '',
	'U_REPORT'			=> (gallery_acl_check('i_report', $album_id, $album_data['album_user_id']) && ($image_data['image_user_id'] != $user->data['user_id'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=report&amp;album_id=$album_id&amp;image_id=$image_id") : '',
	'U_STATUS'			=> (gallery_acl_check('m_status', $album_id, $album_data['album_user_id'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", "mode=queue_details&amp;album_id=$album_id&amp;option_id=$image_id") : '',

	'CONTEST_RANK'		=> ($image_data['image_contest_rank']) ? $user->lang['CONTEST_RESULT_' . $image_data['image_contest_rank']] : '',
	'IMAGE_NAME'		=> $image_data['image_name'],
	'IMAGE_DESC'		=> generate_text_for_display($image_data['image_desc'], $image_data['image_desc_uid'], $image_data['image_desc_bitfield'], 7),
	'IMAGE_BBCODE'		=> '[album]' . $image_id . '[/album]',
	'IMAGE_IMGURL_BBCODE'	=> ($gallery_config['view_image_url']) ? '[url=' . generate_board_url(false) . '/' . $gallery_root_path . "image.$phpEx?album_id=$album_id&amp;image_id=$image_id" . '][img]' . generate_board_url(false) . '/' . $gallery_root_path . "image.$phpEx?album_id=$album_id&amp;image_id=$image_id&amp;mode=thumbnail" . '[/img][/url]' : '',
	'IMAGE_URL'			=> ($gallery_config['view_image_url']) ? generate_board_url(false) . '/' . $gallery_root_path . "image.$phpEx?album_id=$album_id&amp;image_id=$image_id" : '',
	'POSTER'			=> (gallery_acl_check('m_status', $album_id, $album_data['album_user_id']) || ($image_data['image_contest'] != IMAGE_CONTEST)) ? get_username_string('full', $image_data['image_user_id'], ($image_data['image_username']) ? $image_data['image_username'] : $user->lang['GUEST'], $image_data['image_user_colour']) : sprintf($user->lang['CONTEST_USERNAME_LONG'], $user->format_date(($album_data['contest_start'] + $album_data['contest_end']), false, true)),
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

/**
* Exif-Data
*/
if ($gallery_config['exif_data'] && ($image_data['image_has_exif'] != EXIF_UNAVAILABLE) && (substr($image_data['image_filename'], -4) == '.jpg') && function_exists('exif_read_data') && (gallery_acl_check('m_status', $album_id, $album_data['album_user_id']) || ($image_data['image_contest'] != IMAGE_CONTEST)))
{
	if ($image_data['image_has_exif'] == EXIF_DBSAVED)
	{
		$exif = unserialize($image_data['image_exif_data']);
	}
	else
	{
		if (!class_exists('nv_image_tools'))
		{
			include($phpbb_root_path . $gallery_root_path . 'includes/functions_image.' . $phpEx);
		}
		$image_tools = new nv_image_tools();
		$image_tools->set_image_data($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['image_filename']);
		$image_tools->read_exif_data();
		$exif = $image_tools->exif_data;
	}
	if (!empty($exif["EXIF"]))
	{
		$exif_data = array();

		if (isset($exif["EXIF"]["DateTimeOriginal"]))
		{
			$timestamp_year = substr($exif["EXIF"]["DateTimeOriginal"], 0, 4);
			$timestamp_month = substr($exif["EXIF"]["DateTimeOriginal"], 5, 2);
			$timestamp_day = substr($exif["EXIF"]["DateTimeOriginal"], 8, 2);
			$timestamp_hour = substr($exif["EXIF"]["DateTimeOriginal"], 11, 2);
			$timestamp_minute = substr($exif["EXIF"]["DateTimeOriginal"], 14, 2);
			$timestamp_second = substr($exif["EXIF"]["DateTimeOriginal"], 17, 2);
			$timestamp = (int) @mktime($timestamp_hour, $timestamp_minute, $timestamp_second, $timestamp_month, $timestamp_day, $timestamp_year);
			if ($timestamp)
			{
				$exif_data['exif_date'] = $user->format_date($timestamp + EXIFTIME_OFFSET);
			}
		}
		if (isset($exif["EXIF"]["FocalLength"]))
		{
			list($num, $den) = explode("/", $exif["EXIF"]["FocalLength"]);
			if ($den)
			{
				$exif_data['exif_focal'] = sprintf($user->lang['EXIF_FOCAL_EXP'], ($num / $den));
			}
		}
		if (isset($exif["EXIF"]["ExposureTime"]))
		{
			list($num, $den) = explode("/", $exif["EXIF"]["ExposureTime"]);
			$exif_exposure = '';
			if (($num > $den) && $den)
			{
				$exif_exposure = $num / $den;
			}
			else if ($num)
			{
				$exif_exposure = ' 1/' . $den / $num ;
			}
			if ($exif_exposure)
			{
				$exif_data['exif_exposure'] = sprintf($user->lang['EXIF_EXPOSURE_EXP'], $exif_exposure);
			}
		}
		if (isset($exif["EXIF"]["FNumber"]))
		{
			list($num,$den) = explode("/",$exif["EXIF"]["FNumber"]);
			if ($den)
			{
				$exif_data['exif_aperture'] = "F/" . ($num / $den);
			}
		}
		if (isset($exif["EXIF"]["ISOSpeedRatings"]) && !is_array($exif["EXIF"]["ISOSpeedRatings"]))
		{
			$exif_data['exif_iso'] = $exif["EXIF"]["ISOSpeedRatings"];
		}
		if (isset($exif["EXIF"]["WhiteBalance"]))
		{
			$exif_data['exif_whiteb'] = $user->lang['EXIF_WHITEB_' . (($exif["EXIF"]["WhiteBalance"]) ? 'MANU' : 'AUTO')];
		}
		if (isset($exif["EXIF"]["Flash"]))
		{
			if (isset($user->lang['EXIF_FLASH_CASE_' . $exif["EXIF"]["Flash"]]))
			{
				$exif_data['exif_flash'] = $user->lang['EXIF_FLASH_CASE_' . $exif["EXIF"]["Flash"]];
			}
		}
		if (isset($exif["IFD0"]["Model"]))
		{
			$exif_data['exif_cam_model'] = ucwords($exif["IFD0"]["Model"]);
		}
		if (isset($exif["EXIF"]["ExposureProgram"]))
		{
			if (isset($user->lang['EXIF_EXPOSURE_PROG_' . $exif["EXIF"]["ExposureProgram"]]))
			{
				$exif_data['exif_exposure_prog'] = $user->lang['EXIF_EXPOSURE_PROG_' . $exif["EXIF"]["ExposureProgram"]];
			}
		}
		if (isset($exif["EXIF"]["ExposureBiasValue"]))
		{
			list($num,$den) = explode("/", $exif["EXIF"]["ExposureBiasValue"]);
			if ($den)
			{
				if (($num / $den) == 0)
				{
					$exif_exposure_bias = 0;
				}
				else
				{
					$exif_exposure_bias = $exif["EXIF"]["ExposureBiasValue"];
				}
				$exif_data['exif_exposure_bias'] = sprintf($user->lang['EXIF_EXPOSURE_BIAS_EXP'], $exif_exposure_bias);
			}
		}
		if (isset($exif["EXIF"]["MeteringMode"]))
		{
			if (isset($user->lang['EXIF_METERING_MODE_' . $exif["EXIF"]["MeteringMode"]]))
			{
				$exif_data['exif_metering_mode'] = $user->lang['EXIF_METERING_MODE_' . $exif["EXIF"]["MeteringMode"]];
			}
		}

		if (sizeof($exif_data))
		{
			foreach ($exif_data as $exif => $value)
			{
				$template->assign_block_vars('exif_value', array(
					'EXIF_NAME'			=> $user->lang[strtoupper($exif)],
					'EXIF_VALUE'		=> htmlspecialchars($value),
				));
			}
			$template->assign_vars(array(
				'S_EXIF_DATA'	=> true,
			));

			if ($image_data['image_has_exif'] == EXIF_UNKNOWN)
			{
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
					SET image_has_exif = ' . EXIF_AVAILABLE . '
					WHERE image_id = ' . $image_id;
				$db->sql_query($sql);
			}
		}
	}
	else
	{
		$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
			SET image_has_exif = ' . EXIF_UNAVAILABLE . '
			WHERE image_id = ' . $image_id;
		$db->sql_query($sql);
	}
}

/**
* Rating
*/
if ($gallery_config['allow_rates'])
{
	$allowed_to_rate = $your_rating = $contest_rating_msg = $contest_result_hidden = false;

	if ($user->data['is_registered'])
	{
		$sql = 'SELECT *
			FROM ' . GALLERY_RATES_TABLE . '
			WHERE rate_image_id = ' . $image_id . '
				AND rate_user_id = ' . (int) $user->data['user_id'];
		$result = $db->sql_query($sql);

		if ($db->sql_affectedrows($result) > 0)
		{
			$rated = $db->sql_fetchrow($result);
			$your_rating = $rated['rate_point'];
		}
		$db->sql_freeresult($result);
	}
	// Hide the result, while still rating on contests
	if ($image_data['image_contest'])
	{
		$contest_result_hidden = sprintf($user->lang['CONTEST_RESULT_HIDDEN'], $user->format_date(($album_data['contest_start'] + $album_data['contest_end']), false, true));
	}

	// Check: User didn't rate yet, has permissions, it's not the users own image and the user is logged in
	if (!$your_rating && gallery_acl_check('i_rate', $album_id, $album_data['album_user_id']) && ($user->data['user_id'] != $image_data['image_user_id']) && ($user->data['user_id'] != ANONYMOUS) && ($album_data['album_status'] != ITEM_LOCKED) && ($image_data['image_status'] != IMAGE_LOCKED))
	{
		$hide_rate = false;
		if ($album_data['contest_id'])
		{
			if (time() < ($album_data['contest_start'] + $album_data['contest_rating']))
			{
				$hide_rate = true;
				$contest_rating_msg = sprintf($user->lang['CONTEST_RATING_STARTS'], $user->format_date(($album_data['contest_start'] + $album_data['contest_rating']), false, true));
			}
			if (($album_data['contest_start'] + $album_data['contest_end']) < time())
			{
				$hide_rate = true;
				$contest_rating_msg = sprintf($user->lang['CONTEST_RATING_ENDED'], $user->format_date(($album_data['contest_start'] + $album_data['contest_end']), false, true));
			}
		}
		if (!$hide_rate)
		{
			for ($rate_scale = 1; $rate_scale <= $gallery_config['rate_scale']; $rate_scale++)
			{
				$template->assign_block_vars('rate_scale', array(
					'RATE_POINT'	=> $rate_scale,
				));
			}
		}
		$allowed_to_rate = true;
	}
	$template->assign_vars(array(
		'IMAGE_RATING'			=> ($image_data['image_rates'] != 0) ? sprintf((($image_data['image_rates'] == 1) ? $user->lang['RATE_STRING'] : $user->lang['RATES_STRING']), $image_data['image_rate_avg'] / 100, $image_data['image_rates']) : $user->lang['NOT_RATED'],
		'S_YOUR_RATING'			=> $your_rating,
		'S_ALLOWED_TO_RATE'		=> $allowed_to_rate,
		'CONTEST_RATING'		=> $contest_rating_msg,
		'CONTEST_RESULT_HIDDEN'	=> $contest_result_hidden,
		'S_VIEW_RATE'			=> (gallery_acl_check('i_rate', $album_id, $album_data['album_user_id'])) ? true : false,
		'S_COMMENT_ACTION'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=rate"),
	));
}

/**
* Posting comment
*/
if ($gallery_config['allow_comments'] && gallery_acl_check('c_post', $album_id, $album_data['album_user_id']) && ($album_data['album_status'] != ITEM_LOCKED) && (($image_data['image_status'] != IMAGE_LOCKED) || gallery_acl_check('m_status', $album_id, $album_data['album_user_id'])))
{
	$user->add_lang('posting');
	include("{$phpbb_root_path}includes/functions_posting.$phpEx");

	$bbcode_status	= ($config['allow_bbcode']) ? true : false;
	$smilies_status	= ($bbcode_status && $config['allow_smilies']) ? true : false;
	$img_status		= ($bbcode_status) ? true : false;
	$url_status		= ($config['allow_post_links']) ? true : false;
	$flash_status	= false;
	$quote_status	= true;

	// Build custom bbcodes array
	display_custom_bbcodes();

	// Build smilies array
	generate_smilies('inline', 0);

	$s_hide_comment_input = (time() < ($album_data['contest_start'] + $album_data['contest_end'])) ? true : false;

	$template->assign_vars(array(
		'S_ALLOWED_TO_COMMENT'	=> true,
		'S_HIDE_COMMENT_INPUT'	=> $s_hide_comment_input,
		'CONTEST_COMMENTS'		=> sprintf($user->lang['CONTEST_COMMENTS_STARTS'], $user->format_date(($album_data['contest_start'] + $album_data['contest_end']), false, true)),

		'BBCODE_STATUS'			=> ($bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
		'IMG_STATUS'			=> ($img_status) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
		'FLASH_STATUS'			=> ($flash_status) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
		'SMILIES_STATUS'		=> ($smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
		'URL_STATUS'			=> ($bbcode_status && $url_status) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],

		'S_BBCODE_ALLOWED'		=> $bbcode_status,
		'S_SMILIES_ALLOWED'		=> $smilies_status,
		'S_LINKS_ALLOWED'		=> $url_status,
		'S_BBCODE_IMG'			=> $img_status,
		'S_BBCODE_URL'			=> $url_status,
		'S_BBCODE_FLASH'		=> $flash_status,
		'S_BBCODE_QUOTE'		=> $quote_status,
		'L_COMMENT_LENGTH'		=> sprintf($user->lang['COMMENT_LENGTH'], $gallery_config['comment_length']),
	));

	// Different link, when we rate and dont comment
	if (!$s_hide_comment_input)
	{
		$template->assign_var('S_COMMENT_ACTION', append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=add"));
	}
}


/**
* Listing comment
*/
if (($gallery_config['allow_comments'] && gallery_acl_check('c_read', $album_id, $album_data['album_user_id'])) && (time() > ($album_data['contest_start'] + $album_data['contest_end'])))
{
	$user->add_lang('viewtopic');
	$start = request_var('start', 0);
	$sort_order = (request_var('sort_order', 'ASC') == 'ASC') ? 'ASC' : 'DESC';
	$template->assign_vars(array(
		'S_ALLOWED_READ_COMMENTS'	=> true,
		'IMAGE_COMMENTS'			=> $image_data['image_comments'],
		'SORT_ASC'					=> ($sort_order == 'ASC') ? true : false,
	));

	if ($image_data['image_comments'] > 0)
	{
		$bbcode = new bbcode();

		$comments = $users = $user_cache = array();
		$sql = 'SELECT *
			FROM ' . GALLERY_COMMENTS_TABLE . '
			WHERE comment_image_id = ' . $image_id . '
			ORDER BY comment_id ' . $sort_order;
		$result = $db->sql_query_limit($sql, $config['posts_per_page'], $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$comments[] = $row;
			$users[] = $row['comment_user_id'];
			if ($row['comment_edit_count'] > 0)
			{
				$users[] = $row['comment_edit_user_id'];
			}
		}
		$db->sql_freeresult($result);

		$sql = $db->sql_build_query('SELECT', array(
			'SELECT'	=> 'u.*, gu.personal_album_id, gu.user_images',
			'FROM'		=> array(USERS_TABLE => 'u'),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(GALLERY_USERS_TABLE => 'gu'),
					'ON'	=> 'gu.user_id = u.user_id'
				),
			),

			'WHERE'		=> $db->sql_in_set('u.user_id', $users),
		));
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			add_user_to_user_cache($user_cache, $row);
		}
		$db->sql_freeresult($result);

		if ($config['load_onlinetrack'] && sizeof($users))
		{
			// Load online-information
			$sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
				FROM ' . SESSIONS_TABLE . '
				WHERE ' . $db->sql_in_set('session_user_id', $users) . '
				GROUP BY session_user_id';
			$result = $db->sql_query($sql);

			$update_time = $config['load_online_time'] * 60;
			while ($row = $db->sql_fetchrow($result))
			{
				$user_cache[$row['session_user_id']]['online'] = (time() - $update_time < $row['online_time'] && (($row['viewonline']) || $auth->acl_get('u_viewonline'))) ? true : false;
			}
			$db->sql_freeresult($result);
		}

		foreach ($comments as $row)
		{
			$edit_info = '';
			if ($row['comment_edit_count'] > 0)
			{
				$edit_info = ($row['comment_edit_count'] == 1) ? $user->lang['EDITED_TIME_TOTAL'] : $user->lang['EDITED_TIMES_TOTAL'];
				$edit_info = sprintf($edit_info, get_username_string('full', $user_cache[$row['comment_edit_user_id']]['user_id'], $user_cache[$row['comment_edit_user_id']]['username'], $user_cache[$row['comment_edit_user_id']]['user_colour']), $user->format_date($row['comment_edit_time'], false, true), $row['comment_edit_count']);
			}

			$user_id = $row['comment_user_id'];
			if ($user_cache[$user_id]['sig'] && empty($user_cache[$user_id]['sig_parsed']))
			{
				$user_cache[$user_id]['sig'] = censor_text($user_cache[$user_id]['sig']);

				if ($user_cache[$user_id]['sig_bbcode_bitfield'])
				{
					$bbcode->bbcode_second_pass($user_cache[$user_id]['sig'], $user_cache[$user_id]['sig_bbcode_uid'], $user_cache[$user_id]['sig_bbcode_bitfield']);
				}

				$user_cache[$user_id]['sig'] = bbcode_nl2br($user_cache[$user_id]['sig']);
				$user_cache[$user_id]['sig'] = smiley_text($user_cache[$user_id]['sig']);
				$user_cache[$user_id]['sig_parsed'] = true;
			}

			$template->assign_block_vars('commentrow', array(
				'U_COMMENT'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id&amp;start=$start&amp;sort_order=$sort_order") . '#' . $row['comment_id'],
				'COMMENT_ID'	=> $row['comment_id'],
				'TIME'			=> $user->format_date($row['comment_time']),
				'TEXT'			=> generate_text_for_display($row['comment'], $row['comment_uid'], $row['comment_bitfield'], 7),
				'EDIT_INFO'		=> $edit_info,
				'U_DELETE'		=> (gallery_acl_check('m_comments', $album_id, $album_data['album_user_id']) || (gallery_acl_check('c_delete', $album_id, $album_data['album_user_id']) && ($row['comment_user_id'] == $user->data['user_id']) && $user->data['is_registered'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=delete&amp;comment_id=" . $row['comment_id']) : '',
				'U_EDIT'		=> (gallery_acl_check('m_comments', $album_id, $album_data['album_user_id']) || (gallery_acl_check('c_edit', $album_id, $album_data['album_user_id']) && ($row['comment_user_id'] == $user->data['user_id']) && $user->data['is_registered'])) ? append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=edit&amp;comment_id=" . $row['comment_id']) : '',
				'U_INFO'		=> ($auth->acl_get('a_')) ? append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx", 'mode=whois&amp;ip=' . $row['comment_user_ip']) : '',

				'POST_AUTHOR_FULL'		=> get_username_string('full', $user_id, $row['comment_username'], $user_cache[$user_id]['user_colour']),
				'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $user_id, $row['comment_username'], $user_cache[$user_id]['user_colour']),
				'POST_AUTHOR'			=> get_username_string('username', $user_id, $row['comment_username'], $user_cache[$user_id]['user_colour']),
				'U_POST_AUTHOR'			=> get_username_string('profile', $user_id, $row['comment_username'], $user_cache[$user_id]['user_colour']),

				'SIGNATURE'			=> $user_cache[$user_id]['sig'],
				'RANK_TITLE'		=> $user_cache[$user_id]['rank_title'],
				'RANK_IMG'			=> $user_cache[$user_id]['rank_image'],
				'RANK_IMG_SRC'		=> $user_cache[$user_id]['rank_image_src'],
				'POSTER_JOINED'		=> $user_cache[$user_id]['joined'],
				'POSTER_POSTS'		=> $user_cache[$user_id]['posts'],
				'POSTER_FROM'		=> $user_cache[$user_id]['from'],
				'POSTER_AVATAR'		=> $user_cache[$user_id]['avatar'],
				'POSTER_WARNINGS'	=> $user_cache[$user_id]['warnings'],
				'POSTER_AGE'		=> $user_cache[$user_id]['age'],

				'ICQ_STATUS_IMG'	=> $user_cache[$user_id]['icq_status_img'],
				'ONLINE_IMG'		=> ($user_id == ANONYMOUS || !$config['load_onlinetrack']) ? '' : (($user_cache[$user_id]['online']) ? $user->img('icon_user_online', 'ONLINE') : $user->img('icon_user_offline', 'OFFLINE')),
				'S_ONLINE'			=> ($user_id == ANONYMOUS || !$config['load_onlinetrack']) ? false : (($user_cache[$user_id]['online']) ? true : false),

				'U_PROFILE'		=> $user_cache[$user_id]['profile'],
				'U_SEARCH'		=> $user_cache[$user_id]['search'],
				'U_PM'			=> ($user_id != ANONYMOUS && $config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($user_cache[$user_id]['allow_pm'] || $auth->acl_gets('a_', 'm_'))) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;mode=compose&amp;u=' . $user_id) : '',
				'U_EMAIL'		=> $user_cache[$user_id]['email'],
				'U_WWW'			=> $user_cache[$user_id]['www'],
				'U_ICQ'			=> $user_cache[$user_id]['icq'],
				'U_AIM'			=> $user_cache[$user_id]['aim'],
				'U_MSN'			=> $user_cache[$user_id]['msn'],
				'U_YIM'			=> $user_cache[$user_id]['yim'],
				'U_JABBER'		=> $user_cache[$user_id]['jabber'],

				'U_GALLERY'			=> $user_cache[$user_id]['gallery_album'],
				'GALLERY_IMAGES'	=> $user_cache[$user_id]['gallery_images'],
				'U_GALLERY_SEARCH'	=> $user_cache[$user_id]['gallery_search'],
			));
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'DELETE_IMG'		=> $user->img('icon_post_delete', 'DELETE_COMMENT'),
			'EDIT_IMG'			=> $user->img('icon_post_edit', 'EDIT_COMMENT'),
			'INFO_IMG'			=> $user->img('icon_post_info', 'IP'),
			'MINI_POST_IMG'		=> $user->img('icon_post_target_unread', 'COMMENT'),
			'PROFILE_IMG'		=> $user->img('icon_user_profile', 'READ_PROFILE'),
			'SEARCH_IMG' 		=> $user->img('icon_user_search', 'SEARCH_USER_POSTS'),
			'PM_IMG' 			=> $user->img('icon_contact_pm', 'SEND_PRIVATE_MESSAGE'),
			'EMAIL_IMG' 		=> $user->img('icon_contact_email', 'SEND_EMAIL'),
			'WWW_IMG' 			=> $user->img('icon_contact_www', 'VISIT_WEBSITE'),
			'ICQ_IMG' 			=> $user->img('icon_contact_icq', 'ICQ'),
			'AIM_IMG' 			=> $user->img('icon_contact_aim', 'AIM'),
			'MSN_IMG' 			=> $user->img('icon_contact_msnm', 'MSNM'),
			'YIM_IMG' 			=> $user->img('icon_contact_yahoo', 'YIM'),
			'JABBER_IMG'		=> $user->img('icon_contact_jabber', 'JABBER') ,
			'GALLERY_IMG'		=> $user->img('icon_contact_gallery', 'PERSONAL_ALBUM'),
			'PAGE_NUMBER'		=> sprintf($user->lang['PAGE_OF'], (floor($start / $config['posts_per_page']) + 1), ceil($image_data['image_comments'] / $config['posts_per_page'])),
			'PAGINATION'		=> generate_pagination(append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id&amp;sort_order=$sort_order"), $image_data['image_comments'], $config['posts_per_page'], $start),
		));
	}
}

page_header($user->lang['VIEW_IMAGE'] . ' - ' . $image_data['image_name'], false);

$template->set_filenames(array(
	'body' => 'gallery/viewimage_body.html')
);

page_footer();

?>