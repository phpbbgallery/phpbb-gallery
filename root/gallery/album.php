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

phpbb_gallery::setup(array('mods/gallery_ucp', 'mods/gallery'));
phpbb_gallery_url::_include('functions_display', 'phpbb');

/**
* Check the request
*/
$user_id	= request_var('user_id', 0);
$album_id	= request_var('album_id', 0);
$start		= request_var('start', 0);
$mode		= request_var('mode', '');
$album_data	= phpbb_gallery_album::get_info($album_id);
$sort_days	= request_var('st', 0);
$sort_key	= request_var('sk', ($album_data['album_sort_key']) ? $album_data['album_sort_key'] : phpbb_gallery_config::get('default_sort_key'));
$sort_dir	= request_var('sd', ($album_data['album_sort_dir']) ? $album_data['album_sort_dir'] : phpbb_gallery_config::get('default_sort_dir'));

/**
* Did the contest end?
*/
if ($album_data['contest_id'] && $album_data['contest_marked'] && (($album_data['contest_start'] + $album_data['contest_end']) < time()))
{
	$contest_end_time = $album_data['contest_start'] + $album_data['contest_end'];
	phpbb_gallery_contest::end($album_id, $album_data['contest_id'], $contest_end_time);

	$album_data['contest_marked'] = phpbb_gallery_image::NO_CONTEST;
}

/**
* Build auth-list
*/
phpbb_gallery::$auth->gen_auth_level('album', $album_id, $album_data['album_status'], $album_data['album_user_id']);

if (!phpbb_gallery::$auth->acl_check('i_view', $album_id, $album_data['album_user_id']))
{
	if ($user->data['is_bot'])
	{
		phpbb_gallery_url::redirect('index');
	}
	if (!$user->data['is_registered'])
	{
		login_box();
	}
	else
	{
		trigger_error('NOT_AUTHORISED');
	}
}

/**
* Are we (un)watching the album?
*/
$token = request_var('hash', '');
if ((($mode == 'watch') || ($mode == 'unwatch')) && check_link_hash($token, "{$mode}_$album_id"))
{
	$backlink = phpbb_gallery_url::append_sid('album', "album_id=$album_id");

	if ($mode == 'watch')
	{
		phpbb_gallery_notification::add_albums($album_id);
		$message = $user->lang['WATCHING_ALBUM'] . '<br />';
	}
	if ($mode == 'unwatch')
	{
		phpbb_gallery_notification::remove_albums($album_id);
		$message = $user->lang['UNWATCHED_ALBUM'] . '<br />';
	}

	$message .= '<br />' . sprintf($user->lang['CLICK_RETURN_ALBUM'], '<a href="' . $backlink . '">', '</a>');

	meta_refresh(3, $backlink);
	trigger_error($message);
}

// Build the navigation & display subalbums
phpbb_gallery_album::generate_nav($album_data);
phpbb_gallery_album::display_albums($album_data, $config['load_moderators']);

// Set some variables to their defaults
$allowed_create = false;
$image_counter = 0;
$l_moderator = $moderators_list = $s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
$grouprows = $album_moderators = array();
$images_per_page = phpbb_gallery_config::get('album_rows') * phpbb_gallery_config::get('album_columns');

/**
* We have album_type so that there may be images ...
*/
if ($album_data['album_type'] != phpbb_gallery_album::TYPE_CAT)
{
	if (phpbb_gallery::$auth->acl_check('m_', $album_id, $album_data['album_user_id']))
	{
		$template->assign_var('U_MCP', phpbb_gallery_url::append_sid('mcp', "album_id=$album_id"));
	}

	// When we do the slideshow, we don't need the moderators
	if ($mode != 'slide_show')
	{
		if ($config['load_moderators'])
		{
			phpbb_gallery_album::get_moderators($album_moderators, $album_id);
		}
		if (!empty($album_moderators[$album_id]))
		{
			$l_moderator = (sizeof($album_moderators[$album_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
			$moderators_list = implode(', ', $album_moderators[$album_id]);
		}
	}

	/**
	* Build the sort options
	*/
	$limit_days = array(0 => $user->lang['ALL_IMAGES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
	$sort_by_text = array('t' => $user->lang['TIME'], 'n' => $user->lang['IMAGE_NAME'], 'vc' => $user->lang['VIEWS']);
	$sort_by_sql = array('t' => 'image_time', 'n' => 'image_name_clean', 'vc' => 'image_view_count');

	// Do not sort images after upload-username on running contests, and of course ratings aswell!
	if ($album_data['contest_marked'] != phpbb_gallery_image::IN_CONTEST)
	{
		$sort_by_text['u'] = $user->lang['SORT_USERNAME'];
		$sort_by_sql['u'] = 'image_username_clean';
		
		if (phpbb_gallery_config::get('allow_rates'))
		{
			$sort_by_text['ra'] = $user->lang['RATING'];
			$sort_by_sql['ra'] = (phpbb_gallery_contest::$mode == phpbb_gallery_contest::MODE_SUM) ? 'image_rate_points' : 'image_rate_avg';
			$sort_by_text['r'] = $user->lang['RATES_COUNT'];
			$sort_by_sql['r'] = 'image_rates';
		}
	}
	if (phpbb_gallery_config::get('allow_comments'))
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
		$image_status_check = ' AND image_status <> ' . phpbb_gallery_image::STATUS_UNAPPROVED;
		$image_counter = $album_data['album_images'];
		if (phpbb_gallery::$auth->acl_check('m_status', $album_id, $album_data['album_user_id']))
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
				AND image_status <> " . phpbb_gallery_image::STATUS_ORPHAN . "
			ORDER BY $sql_sort_order" . $sql_help_sort;

		if ($mode == 'slide_show')
		{
			/**
			* Slideshow - Using message_body.html
			*/
			// No plugins means, no javascript to do a slideshow
			if (!phpbb_gallery_plugins::$slideshow)
			{
				trigger_error('MISSING_SLIDESHOW_PLUGIN');
			}

			$result = $db->sql_query($sql);

			$trigger_message = phpbb_gallery_plugins::slideshow($result);
			$db->sql_freeresult($result);

			$template->assign_vars(array(
				'MESSAGE_TITLE'		=> $user->lang['SLIDE_SHOW'],
				'MESSAGE_TEXT'		=> $trigger_message,
			));

			page_header($user->lang['SLIDE_SHOW']);
			$template->set_filenames(array(
				'body' => 'message_body.html')
			);
			page_footer();
		}
		else
		{
			$result = $db->sql_query_limit($sql, $images_per_page, $start);
		}

		while ($row = $db->sql_fetchrow($result))
		{
			$images[] = $row;
		}
		$db->sql_freeresult($result);

		$init_block = true;

		for ($i = 0, $end = count($images); $i < $end; $i += phpbb_gallery_config::get('album_columns'))
		{
			if ($init_block)
			{
				$template->assign_block_vars('imageblock', array(
					//'U_BLOCK'		=> phpbb_gallery_url::append_sid('album', 'album_id=' . $album_data['album_id']),
					'BLOCK_NAME'	=> $album_data['album_name'],
					'S_COL_WIDTH'	=> (100 / phpbb_gallery_config::get('album_columns')) . '%',
					'S_COLS'		=> phpbb_gallery_config::get('album_columns'),
				));
				$init_block = false;
			}

			$template->assign_block_vars('imageblock.imagerow', array());

			for ($j = $i, $end_columns = ($i + phpbb_gallery_config::get('album_columns')); $j < $end_columns; $j++)
			{
				if ($j >= $end)
				{
					$template->assign_block_vars('imageblock.imagerow.no_image', array());
					continue;
				}

				// Assign the image to the template-block
				$images[$j]['album_name'] = $album_data['album_name'];
				phpbb_gallery_image::assign_block('imageblock.imagerow.image', $images[$j], $album_data['album_status'], phpbb_gallery_config::get('album_display'), $album_data['album_user_id']);
			}
		}
	}
	// Is it a personal album, and does the user have permissions to create more?
	if ($album_data['album_user_id'] == $user->data['user_id'])
	{
		if (phpbb_gallery::$auth->acl_check('i_upload', phpbb_gallery_auth::OWN_ALBUM) && !phpbb_gallery::$auth->acl_check('a_unlimited', phpbb_gallery_auth::OWN_ALBUM))
		{
			$sql = 'SELECT COUNT(album_id) albums
				FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE album_user_id = ' . $user->data['user_id'];
			$result = $db->sql_query($sql);
			$albums = (int) $db->sql_fetchfield('albums');
			$db->sql_freeresult($result);

			if ($albums < phpbb_gallery::$auth->acl_check('a_count', phpbb_gallery_auth::OWN_ALBUM))
			{
				$allowed_create = true;
			}
		}
		elseif (phpbb_gallery::$auth->acl_check('a_unlimited', phpbb_gallery_auth::OWN_ALBUM))
		{
			$allowed_create = true;
		}
	}
}
// End of "We have album_type so that there may be images ..."

// Page is ready loaded, mark album as "read"
phpbb_gallery_misc::markread('album', $album_id);

$watch_mode = ($album_data['watch_id']) ?  'unwatch' : 'watch';

$template->assign_vars(array(
	'S_IN_ALBUM'				=> true, // used for some templating in subsilver2
	'S_IS_POSTABLE'				=> ($album_data['album_type'] != phpbb_gallery_album::TYPE_CAT) ? true : false,
	'S_IS_LOCKED'				=> ($album_data['album_status'] == phpbb_gallery_album::STATUS_LOCKED) ? true : false,
	'UPLOAD_IMG'				=> ($album_data['album_status'] == phpbb_gallery_album::STATUS_LOCKED) ? $user->img('button_topic_locked', 'ALBUM_LOCKED') : $user->img('button_upload_image', 'UPLOAD_IMAGE'),
	'S_MODE'					=> $album_data['album_type'],
	'L_MODERATORS'				=> $l_moderator,
	'MODERATORS'				=> $moderators_list,

	'U_UPLOAD_IMAGE'			=> ((!$album_data['album_user_id'] || ($album_data['album_user_id'] == $user->data['user_id'])) && (($user->data['user_id'] == ANONYMOUS) || phpbb_gallery::$auth->acl_check('i_upload', $album_id, $album_data['album_user_id']))) ?
										phpbb_gallery_url::append_sid('posting', "mode=upload&amp;album_id=$album_id") : '',
	'U_CREATE_ALBUM'			=> (($album_data['album_user_id'] == $user->data['user_id']) && $allowed_create) ?
										phpbb_gallery_url::append_sid('phpbb', 'ucp', "i=gallery&amp;mode=manage_albums&amp;action=create&amp;parent_id=$album_id&amp;redirect=album") : '',
	'U_EDIT_ALBUM'				=> ($album_data['album_user_id'] == $user->data['user_id']) ?
										phpbb_gallery_url::append_sid('phpbb', 'ucp', "i=gallery&amp;mode=manage_albums&amp;action=edit&amp;album_id=$album_id&amp;redirect=album") : '',
	'U_SLIDE_SHOW'				=> (sizeof(phpbb_gallery_plugins::$plugins) && phpbb_gallery_plugins::$slideshow) ? phpbb_gallery_url::append_sid('album', "album_id=$album_id&amp;mode=slide_show" . (($sort_key != phpbb_gallery_config::get('default_sort_key')) ? "&amp;sk=$sort_key" : '') . (($sort_dir != phpbb_gallery_config::get('default_sort_dir')) ? "&amp;sd=$sort_dir" : '')) : '',
	'S_DISPLAY_SEARCHBOX'		=> ($auth->acl_get('u_search') && $config['load_search']) ? true : false,
	'S_SEARCHBOX_ACTION'		=> phpbb_gallery_url::append_sid('search', 'aid[]=' . $album_id),
	'S_ENABLE_FEEDS_ALBUM'		=> $album_data['album_feed'] && (phpbb_gallery_config::get('feed_enable_pegas') || !$album_data['album_user_id']),

	'S_THUMBNAIL_SIZE'			=> phpbb_gallery_config::get('thumbnail_height') + 20 + ((phpbb_gallery_config::get('thumbnail_infoline')) ? phpbb_gallery_constants::THUMBNAIL_INFO_HEIGHT : 0),
	'S_JUMPBOX_ACTION'			=> phpbb_gallery_url::append_sid('album'),
	'S_ALBUM_ACTION'			=> phpbb_gallery_url::append_sid('album', "album_id=$album_id"),

	'S_SELECT_SORT_DIR'			=> $s_sort_dir,
	'S_SELECT_SORT_KEY'			=> $s_sort_key,

	'ALBUM_JUMPBOX'				=> phpbb_gallery_album::get_albumbox(false, '', $album_id),
	'U_RETURN_LINK'				=> phpbb_gallery_url::append_sid('index'),
	'S_RETURN_LINK'				=> $user->lang['GALLERY'],

	'PAGINATION'				=> generate_pagination(phpbb_gallery_url::append_sid('album', "album_id=$album_id&amp;sk=$sort_key&amp;sd=$sort_dir&amp;st=$sort_days"), $image_counter, $images_per_page, $start),
	'TOTAL_IMAGES'				=> ($image_counter == 1) ? $user->lang['IMAGE_#'] : sprintf($user->lang['IMAGES_#'], $image_counter),
	'PAGE_NUMBER'				=> on_page($image_counter, $images_per_page, $start),

	'L_WATCH_TOPIC'				=> ($album_data['watch_id']) ? $user->lang['UNWATCH_ALBUM'] : $user->lang['WATCH_ALBUM'],
	'U_WATCH_TOPIC'				=> (($album_data['album_type'] != phpbb_gallery_album::TYPE_CAT) && ($user->data['user_id'] != ANONYMOUS)) ? phpbb_gallery_url::append_sid('album', "mode=" . $watch_mode . "&amp;album_id=$album_id&amp;hash=" . generate_link_hash("{$watch_mode}_$album_id")) : '',
	'S_WATCHING_TOPIC'			=> ($album_data['watch_id']) ? true : false,
));


page_header($user->lang['VIEW_ALBUM'] . ' - ' . $album_data['album_name'], true, $album_id, 'album');

$template->set_filenames(array(
	'body' => 'gallery/album_body.html')
);

page_footer();
