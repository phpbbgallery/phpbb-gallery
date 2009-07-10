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

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Available functions
*
* load_gallery_config()
* set_gallery_config()
* get_album_info()
* get_image_info()
* check_album_user()
* gallery_albumbox()
* update_album_info()
* handle_image_counter()
* get_album_branch()
* generate_image_link()
* gallery_markread()
*
*/

$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . $gallery_root_path . 'includes/functions_phpbb.' . $phpEx);

$sql = 'SELECT *
	FROM ' . GALLERY_USERS_TABLE . '
	WHERE user_id = ' . (int) $user->data['user_id'];
$result = $db->sql_query($sql);
$user->gallery = $db->sql_fetchrow($result);
$db->sql_freeresult($result);
if ($db->sql_affectedrows())
{
	$user->gallery['exists'] = true;
}

/**
* Loading the gallery_config
*/
function load_gallery_config()
{
	global $db;

	// When addons are installed, before the install script is run, this would through an error.
	$db->sql_return_on_error(true);
	$sql = 'SELECT *
		FROM ' . GALLERY_CONFIG_TABLE;
	$result = $db->sql_query($sql);
	$db->sql_return_on_error(false);

	if ($result === true)
	{
		$gallery_config['loaded'] = true;
	}

	while ($row = $db->sql_fetchrow($result))
	{
		$gallery_config[$row['config_name']] = $row['config_value'];
	}
	$db->sql_freeresult($result);

	return $gallery_config;
}

/**
* Set config value. Creates missing album_config entry.
*
* borrowed from phpBB3
* @author: phpBB Group
* @function: set_config
*/
function set_gallery_config($config_name, $config_value, $is_dynamic = false)
{
	global $db, $gallery_config /*, $cache*/;

	$sql = 'UPDATE ' . GALLERY_CONFIG_TABLE . "
		SET config_value = '" . $db->sql_escape($config_value) . "'
		WHERE config_name = '" . $db->sql_escape($config_name) . "'";
	$db->sql_query($sql);

	if (!$db->sql_affectedrows() && !isset($gallery_config[$config_name]))
	{
		$sql = 'INSERT INTO ' . GALLERY_CONFIG_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'config_name'	=> $config_name,
			'config_value'	=> $config_value,
			/*'is_dynamic'	=> ($is_dynamic) ? 1 : 0,*/));
		$db->sql_query($sql);
	}

	$gallery_config[$config_name] = $config_value;

	/*if (!$is_dynamic)
	{
		$cache->destroy('config');
	}*/
}

/**
* Set dynamic config value with arithmetic operation.
*
* borrowed from phpBB3
* @author: phpBB Group
* @function: set_config_count
*/
function set_gallery_config_count($config_name, $increment, $is_dynamic = false)
{
	global $db /*, $cache*/;

	switch ($db->sql_layer)
	{
		case 'firebird':
			$sql_update = 'CAST(CAST(config_value as integer) + ' . (int) $increment . ' as CHAR)';
		break;

		case 'postgres':
			$sql_update = 'int4(config_value) + ' . (int) $increment;
		break;

		// MySQL, SQlite, mssql, mssql_odbc, oracle
		default:
			$sql_update = 'config_value + ' . (int) $increment;
		break;
	}

	$db->sql_query('UPDATE ' . GALLERY_CONFIG_TABLE . ' SET config_value = ' . $sql_update . " WHERE config_name = '" . $db->sql_escape($config_name) . "'");

	/*if (!$is_dynamic)
	{
		$cache->destroy('config');
	}*/
}

/**
* Get album information
*/
function get_album_info($album_id)
{
	global $db, $user;

	$sql_array = array(
		'SELECT'		=> 'a.*, c.*, w.watch_id',
		'FROM'			=> array(GALLERY_ALBUMS_TABLE => 'a'),

		'LEFT_JOIN'		=> array(
			array(
				'FROM'		=> array(GALLERY_WATCH_TABLE => 'w'),
				'ON'		=> 'a.album_id = w.album_id AND w.user_id = ' . $user->data['user_id'],
			),
			array(
				'FROM'		=> array(GALLERY_CONTESTS_TABLE => 'c'),
				'ON'		=> 'a.album_id = c.contest_album_id',
			),
		),

		'WHERE'			=> 'a.album_id = ' . (int) $album_id,
	);
	$sql = $db->sql_build_query('SELECT', $sql_array);

	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		global $gallery_root_path, $phpbb_root_path, $phpEx;

		meta_refresh(3, append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx"));
		trigger_error('ALBUM_NOT_EXIST');
	}

	if (!isset($row['contest_id']))
	{
		$row['contest_id'] = 0;
		$row['contest_rates_start'] = 0;
		$row['contest_end'] = 0;
		$row['contest_marked'] = 0;
		$row['contest_first'] = 0;
		$row['contest_second'] = 0;
		$row['contest_third'] = 0;
	}

	return $row;
}

/**
* Get image information
*/
function get_image_info($image_id)
{
	global $db, $user, $gallery_root_path, $phpbb_root_path, $phpEx;

	$sql_array = array(
		'SELECT'		=> '*',
		'FROM'			=> array(GALLERY_IMAGES_TABLE => 'i'),

		'LEFT_JOIN'		=> array(
			array(
				'FROM'		=> array(GALLERY_WATCH_TABLE => 'w'),
				'ON'		=> 'i.image_id = w.image_id AND w.user_id = ' . $user->data['user_id'],
			),
			array(
				'FROM'		=> array(GALLERY_FAVORITES_TABLE => 'f'),
				'ON'		=> 'i.image_id = f.image_id AND f.user_id = ' . $user->data['user_id'],
			),
		),

		'WHERE'			=> 'i.image_id = ' . (int) $image_id,
	);
	$sql = $db->sql_build_query('SELECT', $sql_array);

	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		meta_refresh(3, append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx"));
		trigger_error('IMAGE_NOT_EXIST');
	}

	return $row;
}

/**
* Check whether the album_user is the user who wants to do something
*/
function check_album_user($album_id)
{
	global $user, $db;

	if (!$user->gallery['personal_album_id'])
	{
		trigger_error('NEED_INITIALISE');
	}

	$sql = 'SELECT album_id
		FROM ' . GALLERY_ALBUMS_TABLE . '
		WHERE album_id = ' . (int) $album_id . '
			AND album_user_id = ' . $user->data['user_id'];
	$result = $db->sql_query($sql);

	if (!$row = $db->sql_fetchrow($result))
	{
		trigger_error('NO_ALBUM_STEALING');
	}
}

/**
* Generate gallery-albumbox
* @param	bool				$ignore_personals		list personal albums
* @param	string				$select_name			request_var() for the select-box
* @param	int					$select_id				selected album
* @param	string				$requested_permission	Exp: for moving a image you need i_upload permissions or a_moderate
* @param	(string || array)	$ignore_id				disabled albums, Exp: on moving: the album where the image is now
* @param	int					$album_user_id			for the select-boxes of the ucp so you only can attach to your own albums
* @param	int					$requested_album_type	only albums of the album_type are allowed
*
* @return	string				$gallery_albumbox		if ($select_name) {full select-box} else {list with options}
*
* comparable to make_forum_select (includes/functions_admin.php)
*/
function gallery_albumbox($ignore_personals, $select_name, $select_id = false, $requested_permission = false, $ignore_id = false, $album_user_id = NON_PERSONAL_ALBUMS, $requested_album_type = -1)
{
	global $db, $user, $cache, $album_access_array;

	// Instead of the query we use the cache
	$album_data = $cache->obtain_album_list();

	$right = $last_a_u_id = 0;
	$access_own = $access_personal = $requested_own = $requested_personal = false;
	$c_access_own = $c_access_personal = false;
	$padding_store = array('0' => '');
	$padding = $album_list = '';
	$check_album_type = ($requested_album_type >= 0) ? true : false;

	// Sometimes it could happen that albums will be displayed here not be displayed within the index page
	// This is the result of albums not displayed at index and a parent of a album with no permissions.
	// If this happens, the padding could be "broken", see includes/functions_admin.php > make_forum_select

	foreach ($album_data as $row)
	{
		$list = false;
		if ($row['album_user_id'] != $last_a_u_id)
		{
			if (!$last_a_u_id && gallery_acl_check('a_list', PERSONAL_GALLERY_PERMISSIONS) && !$ignore_personals)
			{
				$album_list .= '<option disabled="disabled" class="disabled-option">' . $user->lang['PERSONAL_ALBUMS'] . '</option>';
			}
			$padding = '';
			$padding_store[$row['parent_id']] = '';
		}
		if ($row['left_id'] < $right)
		{
			$padding .= '&nbsp; &nbsp;';
			$padding_store[$row['parent_id']] = $padding;
		}
		else if ($row['left_id'] > $right + 1)
		{
			$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : '';
		}

		$right = $row['right_id'];
		$last_a_u_id = $row['album_user_id'];
		$disabled = false;

		if (
		// Is in the ignore_id
		((is_array($ignore_id) && in_array($row['album_id'], $ignore_id)) || $row['album_id'] == $ignore_id)
		||
		// Need upload permissions (for moving)
		(($requested_permission == 'm_move') && (($row['album_type'] == ALBUM_CAT) || (!gallery_acl_check('i_upload', $row['album_id'], $row['album_user_id']) && !gallery_acl_check('m_move', $row['album_id'], $row['album_user_id']))))
		||
		// album_type does not fit
		($check_album_type && ($row['album_type'] != $requested_album_type))
		)
		{
			$disabled = true;
		}

		if (($select_id == SETTING_PERMISSIONS) && !$row['album_user_id'])
		{
			$list = true;
		}
		else if (!$row['album_user_id'])
		{
			if (gallery_acl_check('a_list', $row['album_id'], $row['album_user_id']) || defined('IN_ADMIN'))
			{
				$list = true;
			}
		}
		else if (!$ignore_personals)
		{
			if ($row['album_user_id'] == $user->data['user_id'])
			{
				if (!$c_access_own)
				{
					$c_access_own = true;
					$access_own = gallery_acl_check('a_list', OWN_GALLERY_PERMISSIONS);
					if ($requested_permission)
					{
						$requested_own = !gallery_acl_check($requested_permission, OWN_GALLERY_PERMISSIONS);
					}
					else
					{
						$requested_own = false; // We need the negated version of true here
					}
				}
				$list = (!$list) ? $access_own : $list;
				$disabled = (!$disabled) ? $requested_own : $disabled;
			}
			else if ($row['album_user_id'])
			{
				if (!$c_access_personal)
				{
					$c_access_personal = true;
					$access_personal = gallery_acl_check('a_list', PERSONAL_GALLERY_PERMISSIONS);
					if ($requested_permission)
					{
						$requested_personal = !gallery_acl_check($requested_permission, PERSONAL_GALLERY_PERMISSIONS);
					}
					else
					{
						$requested_personal = false; // We need the negated version of true here
					}
				}
				$list = (!$list) ? $access_personal : $list;
				$disabled = (!$disabled) ? $requested_personal : $disabled;
			}
		}
		if (($album_user_id > NON_PERSONAL_ALBUMS) && ($album_user_id != $row['album_user_id']))
		{
			$list = false;
		}
		else if (($album_user_id > NON_PERSONAL_ALBUMS) && ($row['parent_id'] == 0))
		{
			$disabled = true;
		}

		if ($list)
		{
			$selected = (is_array($select_id)) ? ((in_array($row['album_id'], $select_id)) ? ' selected="selected"' : '') : (($row['album_id'] == $select_id) ? ' selected="selected"' : '');
			$album_list .= '<option value="' . $row['album_id'] . '"' . (($disabled) ? ' disabled="disabled" class="disabled-option"' : $selected) . '>' . $padding . $row['album_name'] . ' (ID: ' . $row['album_id'] . ')</option>';
		}
	}
	unset($padding_store);

	if ($select_name)
	{
		$gallery_albumbox = "<select name='$select_name'>";
		$gallery_albumbox .= $album_list;
		$gallery_albumbox .= '</select>';
	}
	else
	{
		$gallery_albumbox = $album_list;
	}

	return $gallery_albumbox;
}

/**
* Update album information
* Resets the following columns with the correct value:
* - album_images, _real
* - album_last_image_id, _time, _name
* - album_last_username, _user_colour, _user_id
*/
function update_album_info($album_id)
{
	global $db;

	$images_real = $images = $album_user_id = 0;

	// Get the album_user_id, so we can keep the user_colour
	$sql = 'SELECT album_user_id
		FROM ' . GALLERY_ALBUMS_TABLE . '
		WHERE album_id = ' . (int) $album_id;
	$result = $db->sql_query($sql);
	$album_user_id = $db->sql_fetchfield('album_user_id');
	$db->sql_freeresult($result);

	// Number of not unapproved images
	$sql = 'SELECT COUNT(image_id) images
		FROM ' . GALLERY_IMAGES_TABLE . '
		WHERE image_status <> ' . IMAGE_UNAPPROVED . '
			AND image_album_id = ' . (int) $album_id;
	$result = $db->sql_query($sql);
	$images = $db->sql_fetchfield('images');
	$db->sql_freeresult($result);

	// Number of total images
	$sql = 'SELECT COUNT(image_id) images_real
		FROM ' . GALLERY_IMAGES_TABLE . '
		WHERE image_album_id = ' . (int) $album_id;
	$result = $db->sql_query($sql);
	$images_real = $db->sql_fetchfield('images_real');
	$db->sql_freeresult($result);

	// Data of the last not unapproved image
	$sql = 'SELECT image_id, image_time, image_name, image_username, image_user_colour, image_user_id
		FROM ' . GALLERY_IMAGES_TABLE . '
		WHERE image_status <> ' . IMAGE_UNAPPROVED . ' AND
			image_album_id = ' . (int) $album_id . '
		ORDER BY image_time DESC';
	$result = $db->sql_query($sql);
	if ($row = $db->sql_fetchrow($result))
	{
		$sql_ary = array(
			'album_images_real'			=> $images_real,
			'album_images'				=> $images,
			'album_last_image_id'		=> $row['image_id'],
			'album_last_image_time'		=> $row['image_time'],
			'album_last_image_name'		=> $row['image_name'],
			'album_last_username'		=> $row['image_username'],
			'album_last_user_colour'	=> $row['image_user_colour'],
			'album_last_user_id'		=> $row['image_user_id'],
		);
	}
	else
	{
		// No approved image, so we clear the columns
		$sql_ary = array(
			'album_images_real'			=> $images_real,
			'album_images'				=> $images,
			'album_last_image_id'		=> 0,
			'album_last_image_time'		=> 0,
			'album_last_image_name'		=> '',
			'album_last_username'		=> '',
			'album_last_user_colour'	=> '',
			'album_last_user_id'		=> 0,
		);
		if ($album_user_id)
		{
			unset($sql_ary['album_last_user_colour']);
		}
	}
	$db->sql_freeresult($result);

	$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
		WHERE ' . $db->sql_in_set('album_id', $album_id);
	$db->sql_query($sql);

	return $row;
}

/**
* Handle user- & total image_counter
*
* @param	array	$image_id_ary	array with the image_ids which changed their status
* @param	bool	$add			are we adding or removing the images
* @param	bool	$readd			is it possible that there are images which aren't really changed
*/
function handle_image_counter($image_id_ary, $add, $readd = false)
{
	global $config, $gallery_config, $db;

	$num_images = $num_comments = 0;
	$sql = 'SELECT SUM(image_comments) comments
		FROM ' . GALLERY_IMAGES_TABLE . '
		WHERE image_status ' . (($readd) ? '=' : '<>') . ' ' . IMAGE_UNAPPROVED . '
			AND ' . $db->sql_in_set('image_id', $image_id_ary) . '
		GROUP BY image_user_id';
	$result = $db->sql_query($sql);
	$num_comments = $db->sql_fetchfield('comments');
	$db->sql_freeresult($result);

	$sql = 'SELECT COUNT(image_id) images, image_user_id
		FROM ' . GALLERY_IMAGES_TABLE . '
		WHERE image_status ' . (($readd) ? '=' : '<>') . ' ' . IMAGE_UNAPPROVED . '
			AND ' . $db->sql_in_set('image_id', $image_id_ary) . '
		GROUP BY image_user_id';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$sql_ary = array(
			'user_id'				=> $row['image_user_id'],
			'user_images'			=> $row['images'],
		);
		$num_images = $num_images + $row['images'];
		$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
			SET user_images = user_images ' . (($add) ? '+ ' : '- ') . $row['images'] . '
			WHERE ' . $db->sql_in_set('user_id', $row['image_user_id']);
		$db->sql_query($sql);
		if ($db->sql_affectedrows() != 1)
		{
			$sql = 'INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}
	}
	$db->sql_freeresult($result);

	// Since phpBB 3.0.5 this is the better solution
	// If the function does not exist, we load it from gallery/includes/functions_phpbb.php
	set_config_count('num_images', (($add) ? $num_images : 0 - $num_images), true);
	set_gallery_config_count('num_comments', (($add) ? $num_comments : 0 - $num_comments), true);
}

/**
* Get album branch
*
* borrowed from phpBB3
* @author: phpBB Group
* @function: get_forum_branch
*/
function get_album_branch($branch_user_id, $album_id, $type = 'all', $order = 'descending', $include_album = true)
{
	global $db;

	switch ($type)
	{
		case 'parents':
			$condition = 'a1.left_id BETWEEN a2.left_id AND a2.right_id';
		break;

		case 'children':
			$condition = 'a2.left_id BETWEEN a1.left_id AND a1.right_id';
		break;

		default:
			$condition = 'a2.left_id BETWEEN a1.left_id AND a1.right_id OR a1.left_id BETWEEN a2.left_id AND a2.right_id';
		break;
	}

	$rows = array();

	$sql = 'SELECT a2.*
		FROM ' . GALLERY_ALBUMS_TABLE . ' a1
		LEFT JOIN ' . GALLERY_ALBUMS_TABLE . " a2 ON ($condition) AND a2.album_user_id = $branch_user_id
		WHERE a1.album_id = $album_id
			AND a1.album_user_id = $branch_user_id
		ORDER BY a2.left_id " . (($order == 'descending') ? 'ASC' : 'DESC');
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (!$include_album && $row['album_id'] == $album_id)
		{
			continue;
		}

		$rows[] = $row;
	}
	$db->sql_freeresult($result);

	return $rows;
}

/**
* Generate link to image
*
* @param	string	$content	what's in the link: image_name, thumbnail, fake_thumbnail, medium or lastimage_icon
* @param	string	$mode		where does the link leed to: highslide, lytebox, lytebox_slide_show, image_page, image, none
* @param	int		$image_id
* @param	string	$image_name
* @param	int		$album_id
* @param	bool	$is_gif		we need to know whether we display a gif, so we can use a better medium-image
* @param	bool	$count		shall the image-link be counted as view? (Set to false from image_page.php to deny double increment)
*/
function generate_image_link($content, $mode, $image_id, $image_name, $album_id, $is_gif = false, $count = true)
{
	global $phpbb_root_path, $phpEx, $user, $gallery_root_path, $gallery_config;

	if (!$gallery_config)
	{
		$gallery_config = load_gallery_config();
	}

	$image_page_url = append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id");
	$image_url = append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", "album_id=$album_id&amp;image_id=$image_id" . ((!$count) ? '&amp;view=no_count' : ''));
	$thumb_url = append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", "mode=thumbnail&amp;album_id=$album_id&amp;image_id=$image_id");
	$medium_url = append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", "mode=medium&amp;album_id=$album_id&amp;image_id=$image_id");
	switch ($content)
	{
		case 'image_name':
			$shorten_image_name = (utf8_strlen(htmlspecialchars_decode($image_name)) > $gallery_config['shorted_imagenames'] + 3) ? (utf8_substr(htmlspecialchars_decode($image_name), 0, $gallery_config['shorted_imagenames']) . '...') : ($image_name);
			$content = '<span style="font-weight: bold;">' . $shorten_image_name . '</span>';
		break;
		case 'image_name_unbold':
			$shorten_image_name = (utf8_strlen(htmlspecialchars_decode($image_name)) > $gallery_config['shorted_imagenames'] + 3) ? (utf8_substr(htmlspecialchars_decode($image_name), 0, $gallery_config['shorted_imagenames']) . '...') : ($image_name);
			$content = $shorten_image_name;
		break;
		case 'thumbnail':
			$content = '<img src="{U_THUMBNAIL}" alt="{IMAGE_NAME}" title="{IMAGE_NAME}" />';
			$content = str_replace(array('{U_THUMBNAIL}', '{IMAGE_NAME}'), array($thumb_url, $image_name), $content);
		break;
		case 'fake_thumbnail':
			$content = '<img src="{U_THUMBNAIL}" alt="{IMAGE_NAME}" title="{IMAGE_NAME}" style="max-width: {FAKE_THUMB_SIZE}px; max-height: {FAKE_THUMB_SIZE}px;" />';
			$content = str_replace(array('{U_THUMBNAIL}', '{IMAGE_NAME}', '{FAKE_THUMB_SIZE}'), array($thumb_url, $image_name, $gallery_config['fake_thumb_size']), $content);
		break;
		case 'medium':
			$content = '<img src="{U_MEDIUM}" alt="{IMAGE_NAME}" title="{IMAGE_NAME}" />';
			$content = str_replace(array('{U_MEDIUM}', '{IMAGE_NAME}'), array($medium_url, $image_name), $content);
			//cheat for animated/transparent gifs
			if ($is_gif)
			{
				$content = '<img src="{U_MEDIUM}" alt="{IMAGE_NAME}" title="{IMAGE_NAME}" style="max-width: {MEDIUM_WIDTH_SIZE}px; max-height: {MEDIUM_HEIGHT_SIZE}px;" />';
				$content = str_replace(array('{U_MEDIUM}', '{IMAGE_NAME}', '{MEDIUM_HEIGHT_SIZE}', '{MEDIUM_WIDTH_SIZE}'), array($image_url, $image_name, $gallery_config['preview_rsz_height'], $gallery_config['preview_rsz_width']), $content);
			}
		break;
		case 'lastimage_icon':
			$content = $user->img('icon_topic_latest', 'VIEW_LATEST_IMAGE');
		break;
	}
	switch ($mode)
	{
		case 'image_page':
			$url = $image_page_url;
			$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}">{CONTENT}</a>';
		break;
		case 'image_page_next':
			$url = $image_page_url;
			$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}" class="right-box right">{CONTENT}</a>';
		break;
		case 'image_page_prev':
			$url = $image_page_url;
			$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}" class="left-box left">{CONTENT}</a>';
		break;
		case 'image':
			$url = $image_url;
			$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}">{CONTENT}</a>';
		break;
		case 'none':
			$url = $image_page_url;
			$tpl = '{CONTENT}';
		break;
		default:
			$url = $image_url;
			$tpl = generate_image_link_plugins($mode);
		break;
	}

	return str_replace(array('{IMAGE_URL}', '{IMAGE_NAME}', '{CONTENT}'), array($url, $image_name, $content), $tpl);
}

/**
* Marks a album as read
*
* borrowed from phpBB3
* @author: phpBB Group
* @function: markread
*/
function gallery_markread($mode, $album_id = false)
{
	global $db, $user, $config;

	// Sorry, no guest support!
	if ($user->data['user_id'] == ANONYMOUS)
	{
		return;
	}

	if ($mode == 'all')
	{
		if ($forum_id === false || !sizeof($forum_id))
		{
			// Mark all albums read (index page)
			$sql = 'DELETE FROM ' . GALLERY_ATRACK_TABLE . '
				WHERE user_id = ' . $user->data['user_id'];
			$db->sql_query($sql);
			$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
				SET user_lastmark = ' . time() . '
				WHERE user_id = ' . $user->data['user_id'];
			$db->sql_query($sql);
			if ($db->sql_affectedrows() <= 0)
			{
				$sql_ary = array(
					'user_lastmark'		=> time(),
					'user_id'			=> $user->data['user_id'],
				);
				$sql = 'INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
				$db->sql_query($sql);
			}
		}

		return;
	}
	else if ($mode == 'albums')
	{
		// Mark album read
		if (!is_array($album_id))
		{
			$album_id = array($album_id);
		}

		$sql = 'SELECT album_id
			FROM ' . GALLERY_ATRACK_TABLE . "
			WHERE user_id = {$user->data['user_id']}
				AND " . $db->sql_in_set('album_id', $album_id);
		$result = $db->sql_query($sql);

		$sql_update = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$sql_update[] = $row['album_id'];
		}
		$db->sql_freeresult($result);

		if (sizeof($sql_update))
		{
			$sql = 'UPDATE ' . GALLERY_ATRACK_TABLE . '
				SET mark_time = ' . time() . "
				WHERE user_id = {$user->data['user_id']}
					AND " . $db->sql_in_set('album_id', $sql_update);
			$db->sql_query($sql);
		}

		if ($sql_insert = array_diff($album_id, $sql_update))
		{
			$sql_ary = array();
			foreach ($sql_insert as $a_id)
			{
				$sql_ary[] = array(
					'user_id'	=> (int) $user->data['user_id'],
					'album_id'	=> (int) $a_id,
					'mark_time'	=> time()
				);
			}

			$db->sql_multi_insert(GALLERY_ATRACK_TABLE, $sql_ary);
		}

		return;
	}
	else if ($mode == 'album')
	{
		if ($album_id === false)
		{
			return;
		}

		$sql = 'UPDATE ' . GALLERY_ATRACK_TABLE . '
			SET mark_time = ' . time() . "
			WHERE user_id = {$user->data['user_id']}
				AND album_id = $album_id";
		$db->sql_query($sql);

		if (!$db->sql_affectedrows())
		{
			$db->sql_return_on_error(true);

			$sql_ary = array(
				'user_id'		=> (int) $user->data['user_id'],
				'album_id'		=> (int) $album_id,
				'mark_time'		=> time(),
			);

			$db->sql_query('INSERT INTO ' . GALLERY_ATRACK_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

			$db->sql_return_on_error(false);
		}

		return;
	}
}

?>