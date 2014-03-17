<?php

/**
* Check the request
*/
$user_id	= request_var('user_id', 0);
$album_id	= request_var('album_id', 0);
$start		= request_var('start', 0);
$mode		= request_var('mode', '');
$album_data	= phpbb_ext_gallery_core_album::get_info($album_id);
$sort_days	= request_var('st', 0);
$sort_key	= request_var('sk', ($album_data['album_sort_key']) ? $album_data['album_sort_key'] : $phpbb_ext_gallery->config->get('default_sort_key'));
$sort_dir	= request_var('sd', ($album_data['album_sort_dir']) ? $album_data['album_sort_dir'] : $phpbb_ext_gallery->config->get('default_sort_dir'));

// Set some variables to their defaults
$allowed_create = false;
$image_counter = 0;
$l_moderator = $moderators_list = $s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
$grouprows = $album_moderators = array();
$images_per_page = $phpbb_ext_gallery->config->get('album_rows') * $phpbb_ext_gallery->config->get('album_columns');

/**
* We have album_type so that there may be images ...
*/
if ($album_data['album_type'] != phpbb_ext_gallery_core_album::TYPE_CAT)
{
	/**
	* Build the sort options
	*/
	$limit_days = array(0 => $user->lang['ALL_IMAGES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
	$sort_by_text = array('t' => $user->lang['TIME'], 'n' => $user->lang['IMAGE_NAME'], 'vc' => $user->lang['GALLERY_VIEWS']);
	$sort_by_sql = array('t' => 'image_time', 'n' => 'image_name_clean', 'vc' => 'image_view_count');

	// Do not sort images after upload-username on running contests, and of course ratings aswell!
	if ($album_data['contest_marked'] != phpbb_ext_gallery_core_image::IN_CONTEST)
	{
		$sort_by_text['u'] = $user->lang['SORT_USERNAME'];
		$sort_by_sql['u'] = 'image_username_clean';
		
		if ($phpbb_ext_gallery->config->get('allow_rates'))
		{
			$sort_by_text['ra'] = $user->lang['RATING'];
			$sort_by_sql['ra'] = 'image_rate_points';//(phpbb_gallery_contest::$mode == phpbb_gallery_contest::MODE_SUM) ? 'image_rate_points' : 'image_rate_avg';
			$sort_by_text['r'] = $user->lang['RATES_COUNT'];
			$sort_by_sql['r'] = 'image_rates';
		}
	}
	if ($phpbb_ext_gallery->config->get('allow_comments'))
	{
		$sort_by_text['c'] = $user->lang['COMMENTS'];
		$sort_by_sql['c'] = 'image_comments';
		$sort_by_text['lc'] = $user->lang['NEW_COMMENT'];
		$sort_by_sql['lc'] = 'image_last_comment';
	}
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
	$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

	if ($album_data['album_images_real'] > 0)
	{
		$image_status_check = ' AND image_status <> ' . phpbb_ext_gallery_core_image::STATUS_UNAPPROVED;
		$image_counter = $album_data['album_images'];
		if ($phpbb_ext_gallery->auth->acl_check('m_status', $album_id, $album_data['album_user_id']))
		{
			$image_status_check = '';
			$image_counter = $album_data['album_images_real'];
		}

		if (in_array($sort_key, array('r', 'ra')))
		{
			$sql_help_sort = ', image_id ' . (($sort_dir == 'd') ? 'ASC' : 'DESC');
		}
		else
		{
			$sql_help_sort = ', image_id ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');
		}

		$images = array();
		$sql = 'SELECT *
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_album_id = ' . (int) $album_id . "
				$image_status_check
				AND image_status <> " . phpbb_ext_gallery_core_image::STATUS_ORPHAN . "
			ORDER BY $sql_sort_order" . $sql_help_sort;
		$result = $db->sql_query_limit($sql, $images_per_page, $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$images[] = $row;
		}
		$db->sql_freeresult($result);

		$init_block = true;

		for ($i = 0, $end = count($images); $i < $end; $i += $phpbb_ext_gallery->config->get('album_columns'))
		{
			if ($init_block)
			{
				$template->assign_block_vars('imageblock', array(
					//'U_BLOCK'		=> $phpbb_ext_gallery->url->append_sid('album', 'album_id=' . $album_data['album_id']),
					'BLOCK_NAME'	=> $album_data['album_name'],
					'S_COL_WIDTH'	=> (100 / $phpbb_ext_gallery->config->get('album_columns')) . '%',
					'S_COLS'		=> $phpbb_ext_gallery->config->get('album_columns'),
				));
				$init_block = false;
			}

			$template->assign_block_vars('imageblock.imagerow', array());

			for ($j = $i, $end_columns = ($i + $phpbb_ext_gallery->config->get('album_columns')); $j < $end_columns; $j++)
			{
				if ($j >= $end)
				{
					$template->assign_block_vars('imageblock.imagerow.no_image', array());
					continue;
				}

				// Assign the image to the template-block
				$images[$j]['album_name'] = $album_data['album_name'];
				phpbb_ext_gallery_core_image::assign_block('imageblock.imagerow.image', $images[$j], $album_data['album_status'], $phpbb_ext_gallery->config->get('album_display'), $album_data['album_user_id']);
			}
		}
	}
}
// End of "We have album_type so that there may be images ..."

// Page is ready loaded, mark album as "read"
phpbb_ext_gallery_core_misc::markread('album', $album_id);

$watch_mode = ($album_data['watch_id']) ?  'unwatch' : 'watch';

phpbb_generate_template_pagination($template, $phpbb_ext_gallery->url->append_sid('album', "album_id=$album_id&amp;sk=$sort_key&amp;sd=$sort_dir&amp;st=$sort_days"), 'pagination', 'start', $image_counter, $images_per_page, $start);

$template->assign_vars(array(
	'TOTAL_IMAGES'				=> $user->lang('VIEW_ALBUM_IMAGES', $image_counter),
	'PAGE_NUMBER'				=> phpbb_on_page($template, $user, $phpbb_ext_gallery->url->append_sid('album', "album_id=$album_id&amp;sk=$sort_key&amp;sd=$sort_dir&amp;st=$sort_days"), $image_counter, $images_per_page, $start),
	'S_MODE'					=> $album_data['album_type'],

	'S_DISPLAY_SEARCHBOX'		=> ($auth->acl_get('u_search') && $config['load_search']) ? true : false,
	'S_SEARCHBOX_ACTION'		=> $phpbb_ext_gallery->url->append_sid('search', 'aid[]=' . $album_id),
	'S_ENABLE_FEEDS_ALBUM'		=> $album_data['album_feed'] && ($phpbb_ext_gallery->config->get('feed_enable_pegas') || !$album_data['album_user_id']),

	'S_THUMBNAIL_SIZE'			=> $phpbb_ext_gallery->config->get('thumbnail_height') + 20 + (($phpbb_ext_gallery->config->get('thumbnail_infoline')) ? phpbb_gallery_constants::THUMBNAIL_INFO_HEIGHT : 0),
	'S_JUMPBOX_ACTION'			=> $phpbb_ext_gallery->url->append_sid('album'),
	'S_ALBUM_ACTION'			=> $phpbb_ext_gallery->url->append_sid('album', "album_id=$album_id"),

	'S_SELECT_SORT_DIR'			=> $s_sort_dir,
	'S_SELECT_SORT_KEY'			=> $s_sort_key,

	'ALBUM_JUMPBOX'				=> phpbb_ext_gallery_core_album::get_albumbox(false, '', $album_id),

	'L_WATCH_TOPIC'				=> ($album_data['watch_id']) ? $user->lang['UNWATCH_ALBUM'] : $user->lang['WATCH_ALBUM'],
	'U_WATCH_TOPIC'				=> (($album_data['album_type'] != phpbb_ext_gallery_core_album::TYPE_CAT) && ($user->data['user_id'] != ANONYMOUS)) ? $phpbb_ext_gallery->url->append_sid('album', "mode=" . $watch_mode . "&amp;album_id=$album_id&amp;hash=" . generate_link_hash("{$watch_mode}_$album_id")) : '',
	'S_WATCHING_TOPIC'			=> ($album_data['watch_id']) ? true : false,
));
