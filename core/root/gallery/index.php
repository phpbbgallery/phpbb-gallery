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

$phpbb_ext_gallery = new phpbb_ext_gallery_core($auth, $cache, $config, $db, $template, $user, $phpEx, $phpbb_root_path);
$phpbb_ext_gallery->setup();
$phpbb_ext_gallery->url->_include('functions_display', 'phpbb');

/**
* Display albums
*/
$mode = request_var('mode', 'index');
phpbb_ext_gallery_core_album_display::display_albums((($mode == 'personal') ? 'personal' : 0), $config['load_moderators']);

if ($mode == 'personal')
{
/*
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $user->lang['PERSONAL_ALBUMS'],
		'U_VIEW_FORUM'	=> $phpbb_ext_gallery->url->append_sid('index', 'mode=personal'))
	);

	$subscribe_pegas = addhandler addressof andalget_data('subscribe_pegas', false);

	$watch_mode = (!$subscribe_pegas) ? 'watch' : 'unwatch';
	$token = request_var('hash', '');
	$watch_pegas = request_var('pegas', '');
	if ((($watch_pegas == 'watch') || ($watch_pegas == 'unwatch')) && check_link_hash($token, "{$watch_pegas}_pegas"))
	{
		$backlink = $phpbb_ext_gallery->url->append_sid('index', "mode=personal");

		if ($watch_pegas == 'watch')
		{
			$phpbb_ext_gallery->user->update_data(array('subscribe_pegas' => true));
			$message = $user->lang['WATCHING_PEGAS'] . '<br />';
		}
		if ($watch_pegas == 'unwatch')
		{
			$phpbb_ext_gallery->user->update_data(array('subscribe_pegas' => false));
			$message = $user->lang['UNWATCHED_PEGAS'] . '<br />';
		}

		$message .= '<br />' . sprintf($user->lang['CLICK_RETURN_INDEX'], '<a href="' . $backlink . '">', '</a>');

		meta_refresh(3, $backlink);
		trigger_error($message);
	}

	$template->assign_vars(array(
		'S_PERSONAL_GALLERY'	=> true,

		'L_WATCH_TOPIC'				=> ($subscribe_pegas) ? $user->lang['UNWATCH_PEGAS'] : $user->lang['WATCH_PEGAS'],
		'U_WATCH_TOPIC'				=> ($user->data['user_id'] != ANONYMOUS) ? $phpbb_ext_gallery->url->append_sid('index', "mode=personal&amp;pegas={$watch_mode}&amp;hash=" . generate_link_hash("{$watch_mode}_pegas")) : '',
		'S_WATCHING_TOPIC'			=> ($subscribe_pegas) ? true : false,
	));
*/
}
/**
* Add a personal albums category to the album listing if the user has permission to view personal albums
*/
else if ($phpbb_ext_gallery->config->get('pegas_index_album') && $phpbb_ext_gallery->auth->acl_check('a_list', phpbb_ext_gallery_core_auth::PERSONAL_ALBUM))
{
	$images = $images_real = $last_image = 0;
	$last_image = $lastimage_image_id = $lastimage_user_id = $lastimage_album_id = 0;
	$lastimage_time = $lastimage_name = $lastimage_username = $lastimage_user_colour = $last_image_page_url = $last_thumb_url = '';

	$sql = 'SELECT *
		FROM ' . GALLERY_ALBUMS_TABLE . '
		WHERE album_user_id <> ' . phpbb_ext_gallery_core_album::PUBLIC_ALBUM;
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$images += $row['album_images'];
		$images_real += $row['album_images_real'];
		if ($last_image < $row['album_last_image_id'])
		{
			$last_image = $row['album_last_image_id'];
			$lastimage_name = $row['album_last_image_name'];
			$lastimage_time = $user->format_date($row['album_last_image_time']);
			$lastimage_image_id = $row['album_last_image_id'];
			$lastimage_user_id = $row['album_last_user_id'];
			$lastimage_username = $row['album_last_username'];
			$lastimage_user_colour = $row['album_last_user_colour'];
			$last_image_page_url = $phpbb_ext_gallery->url->append_sid('image_page', 'album_id=' . $row['album_id'] . '&amp;image_id=' . $row['album_last_image_id']);
			$last_thumb_url = $phpbb_ext_gallery->url->append_sid('thumbnail', 'album_id=' . $row['album_id'] . '&amp;image_id=' . $row['album_last_image_id']);
			$lastimage_album_id = $row['album_id'];
		}
	}
	$db->sql_freeresult($result);

	$template->assign_block_vars('albumrow', array(
		'S_IS_CAT'				=> true,
		'S_NO_CAT'				=> false,
		'S_LIST_SUBALBUMS'		=> true,
		'S_SUBALBUMS'			=> true,
		'U_VIEWALBUM'			=> $phpbb_ext_gallery->url->append_sid('index', 'mode=personal'),
		'ALBUM_NAME'			=> $user->lang['USERS_PERSONAL_ALBUMS'],
	));
	$template->assign_block_vars('albumrow', array(
		'S_IS_CAT'				=> false,
		'S_NO_CAT'				=> false,
		'S_LIST_SUBALBUMS'		=> true,
		'S_SUBALBUMS'			=> true,
		'U_VIEWALBUM'			=> $phpbb_ext_gallery->url->append_sid('index', 'mode=personal'),
		'ALBUM_NAME'			=> $user->lang['USERS_PERSONAL_ALBUMS'],

		'ALBUM_IMG_STYLE'		=> 'forum_read_subforum',
		'ALBUM_FOLDER_IMG'		=> $user->img('forum_read_subforum', 'N/A'),
		'SUBALBUMS'				=> (($phpbb_ext_gallery->auth->acl_check('i_upload', phpbb_ext_gallery_core_auth::OWN_ALBUM) || $phpbb_ext_gallery->user->get_data('personal_album_id')) ? '<a href="' . (($phpbb_ext_gallery->user->get_data('personal_album_id')) ? $phpbb_ext_gallery->url->append_sid('album', 'album_id=' . $phpbb_ext_gallery->user->get_data('personal_album_id')) : $phpbb_ext_gallery->url->append_sid('phpbb', 'ucp', 'i=gallery&amp;mode=manage_albums')) . '">' . $user->data['username'] . '</a>' : ''),
		'ALBUM_DESC'			=> '',
		'L_MODERATORS'			=> '',
		'L_SUBALBUM_STR'		=> ($phpbb_ext_gallery->auth->acl_check('i_upload', phpbb_ext_gallery_core_auth::OWN_ALBUM) || $phpbb_ext_gallery->user->get_data('personal_album_id')) ? $user->lang['YOUR_PERSONAL_ALBUM'] . ': ' : '',
		'MODERATORS'			=> '',
		'IMAGES'				=> $images,
		'UNAPPROVED_IMAGES'		=> ($phpbb_ext_gallery->auth->acl_check('m_status', phpbb_ext_gallery_core_auth::PERSONAL_ALBUM)) ? $images_real - $images : '',
		'LAST_IMAGE_TIME'		=> $lastimage_time,
		'LAST_USER_FULL'		=> get_username_string('full', $lastimage_user_id, $lastimage_username, $lastimage_user_colour),
		'UC_FAKE_THUMBNAIL'		=> '',//@todo: ($phpbb_ext_gallery->config->get('mini_thumbnail_disp')) ? phpbb_gallery_image::generate_link('fake_thumbnail', $phpbb_ext_gallery->config->get('link_thumbnail'), $lastimage_image_id, $lastimage_name, $lastimage_album_id) : '',
		'UC_IMAGE_NAME'			=> '',//@todo: phpbb_gallery_image::generate_link('image_name', $phpbb_ext_gallery->config->get('link_image_name'), $lastimage_image_id, $lastimage_name, $lastimage_album_id),
		'UC_LASTIMAGE_ICON'		=> '',//@todo: phpbb_gallery_image::generate_link('lastimage_icon', $phpbb_ext_gallery->config->get('link_image_icon'), $lastimage_image_id, $lastimage_name, $lastimage_album_id),
	));

	// Assign subforums loop for style authors
	$template->assign_block_vars('albumrow.subalbum', array(
		'U_SUBALBUM'	=> (($phpbb_ext_gallery->auth->acl_check('i_upload', phpbb_ext_gallery_core_auth::OWN_ALBUM)) ? ($phpbb_ext_gallery->user->get_data('personal_album_id')) ? $phpbb_ext_gallery->url->append_sid('album', 'album_id=' . $phpbb_ext_gallery->user->get_data('personal_album_id')) : $phpbb_ext_gallery->url->append_sid('phpbb', 'ucp', 'i=gallery&amp;mode=manage_albums') : ''),
		'SUBALBUM_NAME'	=> $user->lang['YOUR_PERSONAL_ALBUM'],
	));
}

$first_char = request_var('first_char', '');
$s_char_options = '<option value=""' . ((!$first_char) ? ' selected="selected"' : '') . '>' . $user->lang['ALL'] . '</option>';
// Loop the ASCII: a-z
for ($i = 97; $i < 123; $i++)
{
	$s_char_options .= '<option value="' . chr($i) . '"' . (($first_char == chr($i)) ? ' selected="selected"' : '') . '>' . chr($i - 32) . '</option>';
}
$s_char_options .= '<option value="other"' . (($first_char == 'other') ? ' selected="selected"' : '') . '>#</option>';

// Output page
$template->assign_vars(array(
	'TOTAL_IMAGES'		=> ($phpbb_ext_gallery->config->get('disp_statistic')) ? $user->lang('TOTAL_IMAGES_SPRINTF', $phpbb_ext_gallery->config->get('num_images')) : '',
	'TOTAL_COMMENTS'	=> ($phpbb_ext_gallery->config->get('allow_comments')) ? $user->lang('TOTAL_COMMENTS_SPRINTF', $phpbb_ext_gallery->config->get('num_comments')) : '',
	'TOTAL_PGALLERIES'	=> ($phpbb_ext_gallery->auth->acl_check('a_list', phpbb_ext_gallery_core_auth::PERSONAL_ALBUM)) ? $user->lang('TOTAL_PEGAS_SPRINTF', $phpbb_ext_gallery->config->get('num_pegas')) : '',
	'NEWEST_PGALLERIES'	=> ($phpbb_ext_gallery->config->get('num_pegas')) ? sprintf($user->lang['NEWEST_PGALLERY'], get_username_string('full', $phpbb_ext_gallery->config->get('newest_pega_user_id'), $phpbb_ext_gallery->config->get('newest_pega_username'), $phpbb_ext_gallery->config->get('newest_pega_user_colour'), '', $phpbb_ext_gallery->url->append_sid('album', 'album_id=' . $phpbb_ext_gallery->config->get('newest_pega_album_id')))) : '',

	'S_DISP_LOGIN'			=> $phpbb_ext_gallery->config->get('disp_login'),
	'S_DISP_WHOISONLINE'	=> $phpbb_ext_gallery->config->get('disp_whoisonline'),

	'S_LOGIN_ACTION'			=> $phpbb_ext_gallery->url->append_sid('phpbb', 'ucp', 'mode=login&amp;redirect=' . urlencode($phpbb_ext_gallery->url->path('relative') . "index.$phpEx" . (($mode == 'personal') ? '?mode=personal' : ''))),

	'U_YOUR_PERSONAL_GALLERY'		=> ($phpbb_ext_gallery->auth->acl_check('i_upload', phpbb_ext_gallery_core_auth::OWN_ALBUM)) ? ($phpbb_ext_gallery->user->get_data('personal_album_id')) ? $phpbb_ext_gallery->url->append_sid('album', 'album_id=' . $phpbb_ext_gallery->user->get_data('personal_album_id')) : $phpbb_ext_gallery->url->append_sid('phpbb', 'ucp', 'i=gallery&amp;mode=manage_albums') : '',
	'U_USERS_PERSONAL_GALLERIES'	=> ($phpbb_ext_gallery->auth->acl_check('a_list', phpbb_ext_gallery_core_auth::PERSONAL_ALBUM)) ? $phpbb_ext_gallery->url->append_sid('index', 'mode=personal') : '',
	'S_USERS_PERSONAL_GALLERIES'	=> (!$phpbb_ext_gallery->config->get('pegas_index_album') && $phpbb_ext_gallery->auth->acl_check('a_list', phpbb_ext_gallery_core_auth::PERSONAL_ALBUM)) ? true : false,
	'S_CHAR_OPTIONS'				=> $s_char_options,

	'U_MCP'							=> ($phpbb_ext_gallery->auth->acl_check_global('m_')) ? $phpbb_ext_gallery->url->append_sid('mcp', 'mode=overview') : '',
	'U_MARK_ALBUMS'					=> ($user->data['is_registered']) ? $phpbb_ext_gallery->url->append_sid('index', 'hash=' . generate_link_hash('global') . '&amp;mark=albums') : '',

	'U_G_SEARCH_COMMENTED'			=> ($phpbb_ext_gallery->config->get('allow_comments')) ? $phpbb_ext_gallery->url->append_sid('search', 'search_id=commented') : '',
	'U_G_SEARCH_CONTESTS'			=> ($phpbb_ext_gallery->config->get('allow_rates') && $phpbb_ext_gallery->config->get('contests_ended')) ? $phpbb_ext_gallery->url->append_sid('search', 'search_id=contests') : '',
	'U_G_SEARCH_RANDOM'				=> $phpbb_ext_gallery->url->append_sid('search', 'search_id=random'),
	'U_G_SEARCH_RECENT'				=> $phpbb_ext_gallery->url->append_sid('search', 'search_id=recent'),
	'U_G_SEARCH_SELF'				=> $phpbb_ext_gallery->url->append_sid('search', 'search_id=egosearch'),
	'U_G_SEARCH_TOPRATED'			=> ($phpbb_ext_gallery->config->get('allow_rates')) ? $phpbb_ext_gallery->url->append_sid('search', 'search_id=toprated') : '',
));

page_header($user->lang['GALLERY'] . (($mode == 'personal') ? ' - ' . $user->lang['PERSONAL_ALBUMS'] : ''));

$template->set_filenames(array(
	'body' => 'gallery/index_body.html')
);

page_footer();
