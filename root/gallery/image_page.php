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
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include('common.' . $phpEx);
include($phpbb_root_path . 'common.' . $phpEx);

phpbb_gallery::setup(array('mods/gallery', 'mods/exif_data'));
phpbb_gallery_url::_include('functions_display', 'phpbb');

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

/**
* Check the request and get image_data
*/
$image_id = request_var('image_id', 0);
$image_data = phpbb_gallery_image::get_info($image_id);

$album_id = $image_data['image_album_id'];
$album_data = phpbb_gallery_album::get_info($album_id);

$user_id = $image_data['image_user_id'];

if (!file_exists(phpbb_gallery_url::path('upload') . $image_data['image_filename']))
{
	$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
		SET image_filemissing = 1
		WHERE image_id = ' . $image_id;
	$db->sql_query($sql);
}

/**
* Check the permissions and approval
*/
if (!phpbb_gallery::$auth->acl_check('i_view', $album_id, $album_data['album_user_id']))
{
	if (!$user->data['is_registered'])
	{
		login_box(phpbb_gallery_url::append_sid('relative', 'image_page', "album_id=$album_id&amp;image_id=$image_id"), $user->lang['LOGIN_INFO']);
	}
	else
	{
		trigger_error('NOT_AUTHORISED');
	}
}
if (!phpbb_gallery::$auth->acl_check('m_status', $album_id, $album_data['album_user_id']) && ($image_data['image_status'] == phpbb_gallery_image::STATUS_UNAPPROVED))
{
	trigger_error('NOT_AUTHORISED');
}

// Build the navigation
phpbb_gallery_album::generate_nav($album_data);
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

$image_approval_sql = ' AND image_status <> ' . phpbb_gallery_image::STATUS_UNAPPROVED;
if (phpbb_gallery::$auth->acl_check('m_status', $album_id, $album_data['album_user_id']))
{
	$image_approval_sql = '';
}

//$sort_days	= request_var('st', 0);
$sort_key	= request_var('sk', ($album_data['album_sort_key']) ? $album_data['album_sort_key'] : phpbb_gallery_config::get('default_sort_key'));
$sort_dir	= request_var('sd', ($album_data['album_sort_dir']) ? $album_data['album_sort_dir'] : phpbb_gallery_config::get('default_sort_dir'));

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

$s_allowed_delete = $s_allowed_edit = $s_allowed_status = false;
if ((phpbb_gallery::$auth->acl_check('m_', $album_id, $album_data['album_user_id']) || ($image_data['image_user_id'] == $user->data['user_id'])) && ($user->data['user_id'] != ANONYMOUS))
{
	$s_user_allowed = (($image_data['image_user_id'] == $user->data['user_id']) && ($album_data['album_status'] != phpbb_gallery_album::STATUS_LOCKED));

	$s_allowed_delete = ((phpbb_gallery::$auth->acl_check('i_delete', $album_id, $album_data['album_user_id']) && $s_user_allowed) || phpbb_gallery::$auth->acl_check('m_delete', $album_id, $album_data['album_user_id']));
	$s_allowed_edit = ((phpbb_gallery::$auth->acl_check('i_edit', $album_id, $album_data['album_user_id']) && $s_user_allowed) || phpbb_gallery::$auth->acl_check('m_edit', $album_id, $album_data['album_user_id']));
	$s_quick_mod = ($s_allowed_delete || $s_allowed_edit || phpbb_gallery::$auth->acl_check('m_status', $album_id, $album_data['album_user_id']) || phpbb_gallery::$auth->acl_check('m_move', $album_id, $album_data['album_user_id']));

	$user->add_lang('mods/gallery_mcp');
	$template->assign_vars(array(
		'S_MOD_ACTION'		=> phpbb_gallery_url::append_sid('mcp', "album_id=$album_id&amp;image_id=$image_id&amp;quickmod=1" /*&amp;redirect=" . urlencode(str_replace('&amp;', '&', $viewtopic_url))*/, true, $user->session_id),
		'S_QUICK_MOD'		=> $s_quick_mod,
		'S_QM_MOVE'			=> phpbb_gallery::$auth->acl_check('m_move', $album_id, $album_data['album_user_id']),
		'S_QM_EDIT'			=> $s_allowed_edit,
		'S_QM_DELETE'		=> $s_allowed_delete,
		'S_QM_REPORT'		=> phpbb_gallery::$auth->acl_check('m_report', $album_id, $album_data['album_user_id']),
		'S_QM_STATUS'		=> phpbb_gallery::$auth->acl_check('m_status', $album_id, $album_data['album_user_id']),

		'S_IMAGE_REPORTED'		=> $image_data['image_reported'],
		'U_IMAGE_REPORTED'		=> ($image_data['image_reported']) ? phpbb_gallery_url::append_sid('mcp', "mode=report_details&amp;album_id=$album_id&amp;option_id=" . $image_data['image_reported']) : '',
		'S_STATUS_APPROVED'		=> ($image_data['image_status'] == phpbb_gallery_image::STATUS_APPROVED),
		'S_STATUS_UNAPPROVED'	=> ($image_data['image_status'] == phpbb_gallery_image::STATUS_UNAPPROVED),
		'S_STATUS_LOCKED'		=> ($image_data['image_status'] == phpbb_gallery_image::STATUS_LOCKED),
	));
}

$image_desc = '';
if (phpbb_gallery::$auth->acl_check('m_status', $album_id, $album_data['album_user_id']) || ($image_data['image_contest'] != phpbb_gallery_image::IN_CONTEST))
{
	$image_desc = generate_text_for_display($image_data['image_desc'], $image_data['image_desc_uid'], $image_data['image_desc_bitfield'], 7);
}
elseif ($image_data['image_desc'])
{
	$image_desc = sprintf($user->lang['CONTEST_IMAGE_DESC'], $user->format_date(($album_data['contest_start'] + $album_data['contest_end']), false, true));
}


$template->assign_vars(array(
	'U_VIEW_ALBUM'		=> phpbb_gallery_url::append_sid("album.$phpEx", "album_id=$album_id"),

	'UC_PREVIOUS_IMAGE'	=> (!empty($previous_data) && phpbb_gallery_config::get('disp_nextprev_thumbnail')) ? generate_image_link('thumbnail', 'image_page', $previous_data['image_id'], $previous_data['image_name'], $album_id) : '',
	'UC_PREVIOUS'		=> (!empty($previous_data)) ? phpbb_gallery_image::generate_link('image_name_unbold', 'image_page_prev', $previous_data['image_id'], $previous_data['image_name'], $album_id) : '',
	'UC_IMAGE'			=> phpbb_gallery_image::generate_link('medium', phpbb_gallery_config::get('link_imagepage'), $image_id, $image_data['image_name'], $album_id, ((substr($image_data['image_filename'], 0 -3) == 'gif') ? true : false), false, '', $next_data['image_id']),
	'UC_NEXT_IMAGE'		=> (!empty($next_data) && phpbb_gallery_config::get('disp_nextprev_thumbnail')) ? generate_image_link('thumbnail', 'image_page', $next_data['image_id'], $next_data['image_name'], $album_id) : '',
	'UC_NEXT'			=> (!empty($next_data)) ? phpbb_gallery_image::generate_link('image_name_unbold', 'image_page_next', $next_data['image_id'], $next_data['image_name'], $album_id) : '',

	'EDIT_IMG'			=> $user->img('icon_post_edit', 'EDIT_IMAGE'),
	'DELETE_IMG'		=> $user->img('icon_post_delete', 'DELETE_IMAGE'),
	'REPORT_IMG'		=> $user->img('icon_post_report', 'REPORT_IMAGE'),
	'STATUS_IMG'		=> $user->img('icon_post_info', 'STATUS_IMAGE'),
	'U_DELETE'			=> ($s_allowed_delete) ? phpbb_gallery_url::append_sid('posting', "mode=image&amp;submode=delete&amp;album_id=$album_id&amp;image_id=$image_id") : '',
	'U_EDIT'			=> ($s_allowed_edit) ? phpbb_gallery_url::append_sid('posting', "mode=image&amp;submode=edit&amp;album_id=$album_id&amp;image_id=$image_id") : '',
	'U_REPORT'			=> (phpbb_gallery::$auth->acl_check('i_report', $album_id, $album_data['album_user_id']) && ($image_data['image_user_id'] != $user->data['user_id'])) ? phpbb_gallery_url::append_sid('posting', "mode=image&amp;submode=report&amp;album_id=$album_id&amp;image_id=$image_id") : '',
	'U_STATUS'			=> ($s_allowed_status) ? phpbb_gallery_url::append_sid('mcp', "mode=queue_details&amp;album_id=$album_id&amp;option_id=$image_id") : '',

	'CONTEST_RANK'		=> ($image_data['image_contest_rank']) ? $user->lang['CONTEST_RESULT_' . $image_data['image_contest_rank']] : '',
	'IMAGE_NAME'		=> $image_data['image_name'],
	'IMAGE_DESC'		=> $image_desc,
	'IMAGE_BBCODE'		=> '[album]' . $image_id . '[/album]',
	'IMAGE_IMGURL_BBCODE'	=> (phpbb_gallery_config::get('disp_image_url')) ? '[url=' . phpbb_gallery_url::path('full') . "image.$phpEx?album_id=$album_id&amp;image_id=$image_id" . '][img]' . generate_board_url(false) . '/' . phpbb_gallery_url::path('relative') . "image.$phpEx?album_id=$album_id&amp;image_id=$image_id&amp;mode=thumbnail" . '[/img][/url]' : '',
	'IMAGE_URL'			=> (phpbb_gallery_config::get('disp_image_url')) ? phpbb_gallery_url::path('full') . "image.$phpEx?album_id=$album_id&amp;image_id=$image_id" : '',
	'IMAGE_TIME'		=> $user->format_date($image_data['image_time']),
	'IMAGE_VIEW'		=> $image_data['image_view_count'],
	'POSTER_IP'			=> ($auth->acl_get('a_')) ? $image_data['image_user_ip'] : '',
	'U_POSTER_WHOIS'	=> ($auth->acl_get('a_')) ? phpbb_gallery_url::append_sid('mcp', 'mode=whois&amp;ip=' . $image_data['image_user_ip']) : '',

	'L_BOOKMARK_TOPIC'	=> ($image_data['favorite_id']) ? $user->lang['UNFAVORITE_IMAGE'] : $user->lang['FAVORITE_IMAGE'],
	'U_BOOKMARK_TOPIC'	=> ($user->data['user_id'] != ANONYMOUS) ? phpbb_gallery_url::append_sid('posting', "mode=image&amp;submode=" . (($image_data['favorite_id']) ?  'un' : '') . "favorite&amp;album_id=$album_id&amp;image_id=$image_id") : '',
	'L_WATCH_TOPIC'		=> ($image_data['watch_id']) ? $user->lang['UNWATCH_IMAGE'] : $user->lang['WATCH_IMAGE'],
	'U_WATCH_TOPIC'		=> ($user->data['user_id'] != ANONYMOUS) ? phpbb_gallery_url::append_sid('posting', "mode=image&amp;submode=" . (($image_data['watch_id']) ?  'un' : '') . "watch&amp;album_id=$album_id&amp;image_id=$image_id") : '',
	'S_WATCHING_TOPIC'	=> ($image_data['watch_id']) ? true : false,
	'S_ALBUM_ACTION'	=> phpbb_gallery_url::append_sid('image_page', "album_id=$album_id&amp;image_id=$image_id"),

	'U_RETURN_LINK'		=> phpbb_gallery_url::append_sid('album', "album_id=$album_id"),
	'S_RETURN_LINK'		=> $album_data['album_name'],
	'S_JUMPBOX_ACTION'	=> phpbb_gallery_url::append_sid('album'),
	'ALBUM_JUMPBOX'		=> phpbb_gallery_album::get_albumbox(false, '', $album_id),
));

/**
* Exif-Data
*/
if (phpbb_gallery_config::get('disp_exifdata') && ($image_data['image_has_exif'] != phpbb_gallery_exif::UNAVAILABLE) && (substr($image_data['image_filename'], -4) == '.jpg') && function_exists('exif_read_data') && (phpbb_gallery::$auth->acl_check('m_status', $album_id, $album_data['album_user_id']) || ($image_data['image_contest'] != phpbb_gallery_image::IN_CONTEST)))
{
	$exif = new phpbb_gallery_exif(phpbb_gallery_url::path('upload') . $image_data['image_filename'], $image_id);
	$exif->interpret($image_data['image_has_exif'], $image_data['image_exif_data']);

	if (!empty($exif->data["EXIF"]))
	{
		$exif->send_to_template(phpbb_gallery::$user->get_data('user_viewexif'));
	}
	unset($exif);
}

/**
* Rating
*/
if (phpbb_gallery_config::get('allow_rates'))
{
	$rating = new phpbb_gallery_image_rating($image_id, $image_data, $album_data);

	$user_rating = $rating->get_user_rating($user->data['user_id']);

	// Check: User didn't rate yet, has permissions, it's not the users own image and the user is logged in
	if (!$user_rating && $rating->is_allowed())
	{
		$rating->display_box();
	}
	$template->assign_vars(array(
		'IMAGE_RATING'			=> $rating->get_image_rating($user_rating),
		'S_ALLOWED_TO_RATE'		=> (!$user_rating && $rating->is_allowed()),
		'S_VIEW_RATE'			=> (phpbb_gallery::$auth->acl_check('i_rate', $album_id, $album_data['album_user_id'])) ? true : false,
		'S_COMMENT_ACTION'		=> phpbb_gallery_url::append_sid('posting', "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=rate"),
	));
	unset($rating);
}

/**
* Posting comment
*/
$comments_disabled = (!phpbb_gallery_config::get('allow_comments') || (phpbb_gallery_config::get('comment_user_control') && !$image_data['image_allow_comments']));
if (!$comments_disabled && phpbb_gallery::$auth->acl_check('c_post', $album_id, $album_data['album_user_id']) && ($album_data['album_status'] != ITEM_LOCKED) && (($image_data['image_status'] != phpbb_gallery_image::STATUS_LOCKED) || phpbb_gallery::$auth->acl_check('m_status', $album_id, $album_data['album_user_id'])))
{
	$user->add_lang('posting');
	phpbb_gallery_url::_include('functions_posting', 'phpbb');

	$bbcode_status	= ($config['allow_bbcode']) ? true : false;
	$smilies_status	= ($config['allow_smilies']) ? true : false;
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

		'BBCODE_STATUS'			=> ($bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . phpbb_gallery_url::append_sid('phpbb', 'faq', 'mode=bbcode') . '">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . phpbb_gallery_url::append_sid('phpbb', 'faq', 'mode=bbcode') . '">', '</a>'),
		'IMG_STATUS'			=> ($img_status) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
		'FLASH_STATUS'			=> ($flash_status) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
		'SMILIES_STATUS'		=> ($smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
		'URL_STATUS'			=> ($bbcode_status && $url_status) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],
		'S_SIGNATURE_CHECKED'	=> ($user->optionget('attachsig')) ? ' checked="checked"' : '',

		'S_BBCODE_ALLOWED'		=> $bbcode_status,
		'S_SMILIES_ALLOWED'		=> $smilies_status,
		'S_LINKS_ALLOWED'		=> $url_status,
		'S_BBCODE_IMG'			=> $img_status,
		'S_BBCODE_URL'			=> $url_status,
		'S_BBCODE_FLASH'		=> $flash_status,
		'S_BBCODE_QUOTE'		=> $quote_status,
		'L_COMMENT_LENGTH'		=> sprintf($user->lang['COMMENT_LENGTH'], phpbb_gallery_config::get('comment_length')),
	));

	if (phpbb_gallery_misc::display_captcha('comment'))
	{
		// Get the captcha instance
		phpbb_gallery_url::_include('captcha/captcha_factory', 'phpbb');
		$captcha =& phpbb_captcha_factory::get_instance($config['captcha_plugin']);
		$captcha->init(CONFIRM_POST);

		$template->assign_vars(array(
			'S_CONFIRM_CODE'		=> true,
			'CAPTCHA_TEMPLATE'		=> $captcha->get_template(),
		));
	}

	// Different link, when we rate and dont comment
	if (!$s_hide_comment_input)
	{
		$template->assign_var('S_COMMENT_ACTION', phpbb_gallery_url::append_sid('posting', "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=add"));
	}
}
elseif (phpbb_gallery_config::get('comment_user_control') && !$image_data['image_allow_comments'])
{
	$template->assign_var('S_COMMENTS_DISABLED', true);
}

/**
* Listing comment
*/
if ((phpbb_gallery_config::get('allow_comments') && phpbb_gallery::$auth->acl_check('c_read', $album_id, $album_data['album_user_id'])) && (time() > ($album_data['contest_start'] + $album_data['contest_end'])))
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
		if (!class_exists('bbcode'))
		{
			phpbb_gallery_url::_include('bbcode', 'phpbb');
		}
		$bbcode = new bbcode();

		$comments = $users = $user_cache = array();
		$users[] = $image_data['image_user_id'];
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

		$users = array_unique($users);
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
			phpbb_gallery_user::add_user_to_cache($user_cache, $row);
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
				'U_COMMENT'		=> phpbb_gallery_url::append_sid('image_page', "album_id=$album_id&amp;image_id=$image_id&amp;start=$start&amp;sort_order=$sort_order") . '#comment_' . $row['comment_id'],
				'COMMENT_ID'	=> $row['comment_id'],
				'TIME'			=> $user->format_date($row['comment_time']),
				'TEXT'			=> generate_text_for_display($row['comment'], $row['comment_uid'], $row['comment_bitfield'], 7),
				'EDIT_INFO'		=> $edit_info,
				'U_DELETE'		=> (phpbb_gallery::$auth->acl_check('m_comments', $album_id, $album_data['album_user_id']) || (phpbb_gallery::$auth->acl_check('c_delete', $album_id, $album_data['album_user_id']) && ($row['comment_user_id'] == $user->data['user_id']) && $user->data['is_registered'])) ? phpbb_gallery_url::append_sid('posting', "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=delete&amp;comment_id=" . $row['comment_id']) : '',
				'U_QUOTE'		=> (phpbb_gallery::$auth->acl_check('c_post', $album_id, $album_data['album_user_id'])) ? phpbb_gallery_url::append_sid('posting', "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=add&amp;comment_id=" . $row['comment_id']) : '',
				'U_EDIT'		=> (phpbb_gallery::$auth->acl_check('m_comments', $album_id, $album_data['album_user_id']) || (phpbb_gallery::$auth->acl_check('c_edit', $album_id, $album_data['album_user_id']) && ($row['comment_user_id'] == $user->data['user_id']) && $user->data['is_registered'])) ? phpbb_gallery_url::append_sid('posting', "album_id=$album_id&amp;image_id=$image_id&amp;mode=comment&amp;submode=edit&amp;comment_id=" . $row['comment_id']) : '',
				'U_INFO'		=> ($auth->acl_get('a_')) ? phpbb_gallery_url::append_sid('mcp', 'mode=whois&amp;ip=' . $row['comment_user_ip']) : '',

				'POST_AUTHOR_FULL'		=> get_username_string('full', $user_id, $row['comment_username'], $user_cache[$user_id]['user_colour']),
				'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $user_id, $row['comment_username'], $user_cache[$user_id]['user_colour']),
				'POST_AUTHOR'			=> get_username_string('username', $user_id, $row['comment_username'], $user_cache[$user_id]['user_colour']),
				'U_POST_AUTHOR'			=> get_username_string('profile', $user_id, $row['comment_username'], $user_cache[$user_id]['user_colour']),

				'SIGNATURE'			=> ($row['comment_signature']) ? $user_cache[$user_id]['sig'] : '',
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
				'U_PM'			=> ($user_id != ANONYMOUS && $config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($user_cache[$user_id]['allow_pm'] || $auth->acl_gets('a_', 'm_'))) ? phpbb_gallery_url::append_sid('phpbb', 'ucp', 'i=pm&amp;mode=compose&amp;u=' . $user_id) : '',
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
			'QUOTE_IMG'			=> $user->img('icon_post_quote', 'QUOTE_COMMENT'),
			'INFO_IMG'			=> $user->img('icon_post_info', 'IP'),
			'MINI_POST_IMG'		=> $user->img('icon_post_target_unread', 'COMMENT'),
			'PAGE_NUMBER'		=> sprintf($user->lang['PAGE_OF'], (floor($start / $config['posts_per_page']) + 1), ceil($image_data['image_comments'] / $config['posts_per_page'])),
			'PAGINATION'		=> generate_pagination(phpbb_gallery_url::append_sid('image_page', "album_id=$album_id&amp;image_id=$image_id&amp;sort_order=$sort_order"), $image_data['image_comments'], $config['posts_per_page'], $start),
		));
	}
}

// Get the data of the image-uploader, if we don't have it from the comments anyway.
if (!isset($user_cache[$image_data['image_user_id']]))
{
	$sql = $db->sql_build_query('SELECT', array(
		'SELECT'	=> 'u.*, gu.personal_album_id, gu.user_images',
		'FROM'		=> array(USERS_TABLE => 'u'),

		'LEFT_JOIN'	=> array(
			array(
				'FROM'	=> array(GALLERY_USERS_TABLE => 'gu'),
				'ON'	=> 'gu.user_id = u.user_id'
			),
		),

		'WHERE'		=> 'u.user_id = ' . $image_data['image_user_id'],
	));
	$result = $db->sql_query($sql);

	$user_cache = array();
	while ($row = $db->sql_fetchrow($result))
	{
		phpbb_gallery_user::add_user_to_cache($user_cache, $row);
	}
	$db->sql_freeresult($result);
}

if (phpbb_gallery::$auth->acl_check('m_status', $album_id, $album_data['album_user_id']) || ($image_data['image_contest'] != phpbb_gallery_image::IN_CONTEST))
{
	$user_cache[$user_id]['username'] = ($image_data['image_username']) ? $image_data['image_username'] : $user->lang['GUEST'];
	$template->assign_vars(array(
		'POSTER_FULL'		=> get_username_string('full', $user_id, $user_cache[$user_id]['username'], $user_cache[$user_id]['user_colour']),
		'POSTER_COLOUR'		=> get_username_string('colour', $user_id, $user_cache[$user_id]['username'], $user_cache[$user_id]['user_colour']),
		'POSTER_USERNAME'	=> get_username_string('username', $user_id, $user_cache[$user_id]['username'], $user_cache[$user_id]['user_colour']),
		'U_POSTER'			=> get_username_string('profile', $user_id, $user_cache[$user_id]['username'], $user_cache[$user_id]['user_colour']),

		'POSTER_SIGNATURE'		=> $user_cache[$user_id]['sig'],
		'POSTER_RANK_TITLE'		=> $user_cache[$user_id]['rank_title'],
		'POSTER_RANK_IMG'		=> $user_cache[$user_id]['rank_image'],
		'POSTER_RANK_IMG_SRC'	=> $user_cache[$user_id]['rank_image_src'],
		'POSTER_JOINED'		=> $user_cache[$user_id]['joined'],
		'POSTER_POSTS'		=> $user_cache[$user_id]['posts'],
		'POSTER_FROM'		=> $user_cache[$user_id]['from'],
		'POSTER_AVATAR'		=> $user_cache[$user_id]['avatar'],
		'POSTER_WARNINGS'	=> $user_cache[$user_id]['warnings'],
		'POSTER_AGE'		=> $user_cache[$user_id]['age'],

		'POSTER_ICQ_STATUS_IMG'		=> $user_cache[$user_id]['icq_status_img'],
		'POSTER_ONLINE_IMG'			=> ($user_id == ANONYMOUS || !$config['load_onlinetrack']) ? '' : (($user_cache[$user_id]['online']) ? $user->img('icon_user_online', 'ONLINE') : $user->img('icon_user_offline', 'OFFLINE')),
		'S_POSTER_ONLINE'			=> ($user_id == ANONYMOUS || !$config['load_onlinetrack']) ? false : (($user_cache[$user_id]['online']) ? true : false),

		'U_POSTER_PROFILE'		=> $user_cache[$user_id]['profile'],
		'U_POSTER_SEARCH'		=> $user_cache[$user_id]['search'],
		'U_POSTER_PM'			=> ($user_id != ANONYMOUS && $config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($user_cache[$user_id]['allow_pm'] || $auth->acl_gets('a_', 'm_'))) ? phpbb_gallery_url::append_sid('phpbb', 'ucp', 'i=pm&amp;mode=compose&amp;u=' . $user_id) : '',
		'U_POSTER_EMAIL'		=> $user_cache[$user_id]['email'],
		'U_POSTER_WWW'			=> $user_cache[$user_id]['www'],
		'U_POSTER_ICQ'			=> $user_cache[$user_id]['icq'],
		'U_POSTER_AIM'			=> $user_cache[$user_id]['aim'],
		'U_POSTER_MSN'			=> $user_cache[$user_id]['msn'],
		'U_POSTER_YIM'			=> $user_cache[$user_id]['yim'],
		'U_POSTER_JABBER'		=> $user_cache[$user_id]['jabber'],

		'U_POSTER_GALLERY'			=> $user_cache[$user_id]['gallery_album'],
		'POSTER_GALLERY_IMAGES'		=> $user_cache[$user_id]['gallery_images'],
		'U_POSTER_GALLERY_SEARCH'	=> $user_cache[$user_id]['gallery_search'],
	));
}
else
{
	$template->assign_vars(array(
		'POSTER_FULL'	=> sprintf($user->lang['CONTEST_USERNAME_LONG'], $user->format_date(($album_data['contest_start'] + $album_data['contest_end']), false, true)),
	));
}

$template->assign_vars(array(
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
));

page_header($user->lang['VIEW_IMAGE'] . ' - ' . $image_data['image_name'], false);

$template->set_filenames(array(
	'body' => 'gallery/viewimage_body.html')
);

page_footer();

?>