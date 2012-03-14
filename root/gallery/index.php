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

phpbb_gallery::setup(array('mods/gallery'));
phpbb_gallery_url::_include('functions_display', 'phpbb');

/**
* Display albums
*/
$mode = request_var('mode', 'index');
phpbb_gallery_album::display_albums((($mode == 'personal') ? 'personal' : 0), $config['load_moderators']);
if ($mode == 'personal')
{
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $user->lang['PERSONAL_ALBUMS'],
		'U_VIEW_FORUM'	=> phpbb_gallery_url::append_sid('index', 'mode=personal'))
	);

	$subscribe_pegas = phpbb_gallery::$user->get_data('subscribe_pegas', false);

	$watch_mode = (!$subscribe_pegas) ? 'watch' : 'unwatch';
	$token = request_var('hash', '');
	$watch_pegas = request_var('pegas', '');
	if ((($watch_pegas == 'watch') || ($watch_pegas == 'unwatch')) && check_link_hash($token, "{$watch_pegas}_pegas"))
	{
		$backlink = phpbb_gallery_url::append_sid('index', "mode=personal");

		if ($watch_pegas == 'watch')
		{
			phpbb_gallery::$user->update_data(array('subscribe_pegas' => true));
			$message = $user->lang['WATCHING_PEGAS'] . '<br />';
		}
		if ($watch_pegas == 'unwatch')
		{
			phpbb_gallery::$user->update_data(array('subscribe_pegas' => false));
			$message = $user->lang['UNWATCHED_PEGAS'] . '<br />';
		}

		$message .= '<br />' . sprintf($user->lang['CLICK_RETURN_INDEX'], '<a href="' . $backlink . '">', '</a>');

		meta_refresh(3, $backlink);
		trigger_error($message);
	}

	$template->assign_vars(array(
		'S_PERSONAL_GALLERY'	=> true,

		'L_WATCH_TOPIC'				=> ($subscribe_pegas) ? $user->lang['UNWATCH_PEGAS'] : $user->lang['WATCH_PEGAS'],
		'U_WATCH_TOPIC'				=> ($user->data['user_id'] != ANONYMOUS) ? phpbb_gallery_url::append_sid('index', "mode=personal&amp;pegas={$watch_mode}&amp;hash=" . generate_link_hash("{$watch_mode}_pegas")) : '',
		'S_WATCHING_TOPIC'			=> ($subscribe_pegas) ? true : false,
	));
}
/**
* Add a personal albums category to the album listing if the user has permission to view personal albums
*/
else if (phpbb_gallery_config::get('pegas_index_album') && phpbb_gallery::$auth->acl_check('a_list', phpbb_gallery_auth::PERSONAL_ALBUM))
{
	$images = $images_real = $last_image = 0;
	$last_image = $lastimage_image_id = $lastimage_user_id = $lastimage_album_id = 0;
	$lastimage_time = $lastimage_name = $lastimage_username = $lastimage_user_colour = $last_image_page_url = $last_thumb_url = '';

	$sql = 'SELECT *
		FROM ' . GALLERY_ALBUMS_TABLE . '
		WHERE album_user_id <> ' . phpbb_gallery_album::PUBLIC_ALBUM;
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
			$last_image_page_url = phpbb_gallery_url::append_sid('image_page', 'album_id=' . $row['album_id'] . '&amp;image_id=' . $row['album_last_image_id']);
			$last_thumb_url = phpbb_gallery_url::append_sid('thumbnail', 'album_id=' . $row['album_id'] . '&amp;image_id=' . $row['album_last_image_id']);
			$lastimage_album_id = $row['album_id'];
		}
	}
	$db->sql_freeresult($result);

	$template->assign_block_vars('albumrow', array(
		'S_IS_CAT'				=> true,
		'S_NO_CAT'				=> false,
		'S_LIST_SUBALBUMS'		=> true,
		'S_SUBALBUMS'			=> true,
		'U_VIEWALBUM'			=> phpbb_gallery_url::append_sid('index', 'mode=personal'),
		'ALBUM_NAME'			=> $user->lang['USERS_PERSONAL_ALBUMS'],
	));
	$template->assign_block_vars('albumrow', array(
		'S_IS_CAT'				=> false,
		'S_NO_CAT'				=> false,
		'S_LIST_SUBALBUMS'		=> true,
		'S_SUBALBUMS'			=> true,
		'U_VIEWALBUM'			=> phpbb_gallery_url::append_sid('index', 'mode=personal'),
		'ALBUM_NAME'			=> $user->lang['USERS_PERSONAL_ALBUMS'],
		'ALBUM_FOLDER_IMG'		=> $user->img('forum_read_subforum', 'no'),
		'ALBUM_FOLDER_IMG_SRC'	=> $user->img('forum_read_subforum', 'no', false, '', 'src'),
		'SUBALBUMS'				=> ((phpbb_gallery::$auth->acl_check('i_upload', phpbb_gallery_auth::OWN_ALBUM) || phpbb_gallery::$user->get_data('personal_album_id')) ? '<a href="' . ((phpbb_gallery::$user->get_data('personal_album_id')) ? phpbb_gallery_url::append_sid('album', 'album_id=' . phpbb_gallery::$user->get_data('personal_album_id')) : phpbb_gallery_url::append_sid('phpbb', 'ucp', 'i=gallery&amp;mode=manage_albums')) . '">' . $user->data['username'] . '</a>' : ''),
		'ALBUM_DESC'			=> '',
		'L_MODERATORS'			=> '',
		'L_SUBALBUM_STR'		=> (phpbb_gallery::$auth->acl_check('i_upload', phpbb_gallery_auth::OWN_ALBUM) || phpbb_gallery::$user->get_data('personal_album_id')) ? $user->lang['YOUR_PERSONAL_ALBUM'] . ': ' : '',
		'MODERATORS'			=> '',
		'IMAGES'				=> $images,
		'UNAPPROVED_IMAGES'		=> (phpbb_gallery::$auth->acl_check('m_status', phpbb_gallery_auth::PERSONAL_ALBUM)) ? $images_real - $images : '',
		'LAST_IMAGE_TIME'		=> $lastimage_time,
		'LAST_USER_FULL'		=> get_username_string('full', $lastimage_user_id, $lastimage_username, $lastimage_user_colour),
		'UC_FAKE_THUMBNAIL'		=> (phpbb_gallery_config::get('mini_thumbnail_disp')) ? phpbb_gallery_image::generate_link('fake_thumbnail', phpbb_gallery_config::get('link_thumbnail'), $lastimage_image_id, $lastimage_name, $lastimage_album_id) : '',
		'UC_IMAGE_NAME'			=> phpbb_gallery_image::generate_link('image_name', phpbb_gallery_config::get('link_image_name'), $lastimage_image_id, $lastimage_name, $lastimage_album_id),
		'UC_LASTIMAGE_ICON'		=> phpbb_gallery_image::generate_link('lastimage_icon', phpbb_gallery_config::get('link_image_icon'), $lastimage_image_id, $lastimage_name, $lastimage_album_id),
	));

	// Assign subforums loop for style authors
	$template->assign_block_vars('albumrow.subalbum', array(
		'U_SUBALBUM'	=> ((phpbb_gallery::$auth->acl_check('i_upload', phpbb_gallery_auth::OWN_ALBUM)) ? (phpbb_gallery::$user->get_data('personal_album_id')) ? phpbb_gallery_url::append_sid('album', 'album_id=' . phpbb_gallery::$user->get_data('personal_album_id')) : phpbb_gallery_url::append_sid('phpbb', 'ucp', 'i=gallery&amp;mode=manage_albums') : ''),
		'SUBALBUM_NAME'	=> $user->lang['YOUR_PERSONAL_ALBUM'],
	));
}

/**
* Recent images & comments and random images
*/
/**
* int		array	including all relevent numbers for rows, columns and stuff like that,
* display	int		sum of the options which should be displayed, see gallery/includes/constants.php "// Display-options for RRC-Feature" for values
* modes		int		sum of the modes which should be displayed, see gallery/includes/constants.php "// Mode-options for RRC-Feature" for values
* collapse	bool	collapse comments
* include_pgalleries	bool	include personal albums
* mode_id	string	'user' or 'album' to only display images of a certain user or album
* id		int		user_id for user profile or album_id for view of recent and random images
*/
if (phpbb_gallery_config::get('rrc_gindex_mode'))
{
	$ints = array(
		phpbb_gallery_config::get('rrc_gindex_rows'),
		phpbb_gallery_config::get('rrc_gindex_columns'),
		phpbb_gallery_config::get('rrc_gindex_crows'),
		phpbb_gallery_config::get('rrc_gindex_contests'),
	);
	$gallery_block = new phpbb_gallery_block(phpbb_gallery_config::get('rrc_gindex_mode'), phpbb_gallery_config::get('rrc_gindex_display'), $ints, phpbb_gallery_config::get('rrc_gindex_comments'), phpbb_gallery_config::get('rrc_gindex_pegas'));
	$gallery_block->display();
}

// Grab group details for legend display
$legend = '';
if (phpbb_gallery_config::get('disp_whoisonline'))
{
	// Copied from phpbb::index.php
	if ($auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel'))
	{
		$sql = 'SELECT group_id, group_name, group_colour, group_type
			FROM ' . GROUPS_TABLE . '
			WHERE group_legend = 1
			ORDER BY group_name ASC';
	}
	else
	{
		$sql = 'SELECT g.group_id, g.group_name, g.group_colour, g.group_type
			FROM ' . GROUPS_TABLE . ' g
			LEFT JOIN ' . USER_GROUP_TABLE . ' ug
				ON (
					g.group_id = ug.group_id
					AND ug.user_id = ' . $user->data['user_id'] . '
					AND ug.user_pending = 0
				)
			WHERE g.group_legend = 1
				AND (g.group_type <> ' . GROUP_HIDDEN . ' OR ug.user_id = ' . $user->data['user_id'] . ')
			ORDER BY g.group_name ASC';
	}
	$result = $db->sql_query($sql);

	$legend = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$colour_text = ($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . '"' : '';
		$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name'];

		if ($row['group_name'] == 'BOTS' || ($user->data['user_id'] != ANONYMOUS && !$auth->acl_get('u_viewprofile')))
		{
			$legend[] = '<span' . $colour_text . '>' . $group_name . '</span>';
		}
		else
		{
			$legend[] = '<a' . $colour_text . ' href="' . phpbb_gallery_url::append_sid('phpbb', 'memberlist', 'mode=group&amp;g=' . $row['group_id']) . '">' . $group_name . '</a>';
		}
	}
	$db->sql_freeresult($result);

	$legend = implode(', ', $legend);
}

// Generate birthday list if required ...
$birthday_list = '';
if ($config['allow_birthdays'] && phpbb_gallery_config::get('disp_birthdays'))
{
	// Copied from phpbb::index.php
	$now = getdate(time() + $user->timezone + $user->dst - date('Z'));
	$sql = 'SELECT u.user_id, u.username, u.user_colour, u.user_birthday
		FROM ' . USERS_TABLE . ' u
		LEFT JOIN ' . BANLIST_TABLE . " b ON (u.user_id = b.ban_userid)
		WHERE (b.ban_id IS NULL
			OR b.ban_exclude = 1)
			AND u.user_birthday LIKE '" . $db->sql_escape(sprintf('%2d-%2d-', $now['mday'], $now['mon'])) . "%'
			AND u.user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$birthday_list .= (($birthday_list != '') ? ', ' : '') . get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

		if ($age = (int) substr($row['user_birthday'], -4))
		{
			$birthday_list .= ' (' . ($now['year'] - $age) . ')';
		}
	}
	$db->sql_freeresult($result);
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
	'TOTAL_IMAGES'		=> (phpbb_gallery_config::get('disp_statistic')) ? $user->lang('TOTAL_IMAGES_SPRINTF', phpbb_gallery_config::get('num_images')) : '',
	'TOTAL_COMMENTS'	=> (phpbb_gallery_config::get('allow_comments')) ? $user->lang('TOTAL_COMMENTS_SPRINTF', phpbb_gallery_config::get('num_comments')) : '',
	'TOTAL_PGALLERIES'	=> (phpbb_gallery::$auth->acl_check('a_list', phpbb_gallery_auth::PERSONAL_ALBUM)) ? $user->lang('TOTAL_PEGAS_SPRINTF', phpbb_gallery_config::get('num_pegas')) : '',
	'NEWEST_PGALLERIES'	=> (phpbb_gallery_config::get('num_pegas')) ? sprintf($user->lang['NEWEST_PGALLERY'], get_username_string('full', phpbb_gallery_config::get('newest_pega_user_id'), phpbb_gallery_config::get('newest_pega_username'), phpbb_gallery_config::get('newest_pega_user_colour'), '', phpbb_gallery_url::append_sid('album', 'album_id=' . phpbb_gallery_config::get('newest_pega_album_id')))) : '',

	'S_DISP_LOGIN'			=> phpbb_gallery_config::get('disp_login'),
	'S_DISP_WHOISONLINE'	=> phpbb_gallery_config::get('disp_whoisonline'),
	'LEGEND'				=> $legend,
	'BIRTHDAY_LIST'			=> $birthday_list,

	'S_LOGIN_ACTION'			=> phpbb_gallery_url::append_sid('phpbb', 'ucp', 'mode=login&amp;redirect=' . urlencode(phpbb_gallery_url::path('relative') . "index.$phpEx" . (($mode == 'personal') ? '?mode=personal' : ''))),
	'S_DISPLAY_BIRTHDAY_LIST'	=> (phpbb_gallery_config::get('disp_birthdays')) ? true : false,

	'U_YOUR_PERSONAL_GALLERY'		=> (phpbb_gallery::$auth->acl_check('i_upload', phpbb_gallery_auth::OWN_ALBUM)) ? (phpbb_gallery::$user->get_data('personal_album_id')) ? phpbb_gallery_url::append_sid('album', 'album_id=' . phpbb_gallery::$user->get_data('personal_album_id')) : phpbb_gallery_url::append_sid('phpbb', 'ucp', 'i=gallery&amp;mode=manage_albums') : '',
	'U_USERS_PERSONAL_GALLERIES'	=> (phpbb_gallery::$auth->acl_check('a_list', phpbb_gallery_auth::PERSONAL_ALBUM)) ? phpbb_gallery_url::append_sid('index', 'mode=personal') : '',
	'S_USERS_PERSONAL_GALLERIES'	=> (!phpbb_gallery_config::get('pegas_index_album') && phpbb_gallery::$auth->acl_check('a_list', phpbb_gallery_auth::PERSONAL_ALBUM)) ? true : false,
	'S_CHAR_OPTIONS'				=> $s_char_options,

	'U_MCP'							=> (phpbb_gallery::$auth->acl_check_global('m_')) ? phpbb_gallery_url::append_sid('mcp', 'mode=overview') : '',
	'U_MARK_ALBUMS'					=> ($user->data['is_registered']) ? phpbb_gallery_url::append_sid('index', 'hash=' . generate_link_hash('global') . '&amp;mark=albums') : '',

	'U_G_SEARCH_COMMENTED'			=> (phpbb_gallery_config::get('allow_comments')) ? phpbb_gallery_url::append_sid('search', 'search_id=commented') : '',
	'U_G_SEARCH_CONTESTS'			=> (phpbb_gallery_config::get('allow_rates') && phpbb_gallery_config::get('contests_ended')) ? phpbb_gallery_url::append_sid('search', 'search_id=contests') : '',
	'U_G_SEARCH_RANDOM'				=> phpbb_gallery_url::append_sid('search', 'search_id=random'),
	'U_G_SEARCH_RECENT'				=> phpbb_gallery_url::append_sid('search', 'search_id=recent'),
	'U_G_SEARCH_SELF'				=> phpbb_gallery_url::append_sid('search', 'search_id=egosearch'),
	'U_G_SEARCH_TOPRATED'			=> (phpbb_gallery_config::get('allow_rates')) ? phpbb_gallery_url::append_sid('search', 'search_id=toprated') : '',
));

page_header($user->lang['GALLERY'] . (($mode == 'personal') ? ' - ' . $user->lang['PERSONAL_ALBUMS'] : ''));

$template->set_filenames(array(
	'body' => 'gallery/index_body.html')
);

page_footer();

?>