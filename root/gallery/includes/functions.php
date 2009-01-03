<?php

/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}
$gallery_root_path = GALLERY_ROOT_PATH;

$gd_check = function_exists('gd_info') ? gd_info() : array();;
$gd_success = isset($gd_check['GD Version']);
if (!$gd_success)
{
	if (!isset($album_config['gd_version']))
	{
		$sql = 'SELECT * FROM ' . GALLERY_CONFIG_TABLE . " WHERE config_name = 'gd_version'";
		$result = $db->sql_query($sql);
		while( $row = $db->sql_fetchrow($result) )
		{
			$album_config_name = $row['config_name'];
			$album_config_value = $row['config_value'];
			$album_config[$album_config_name] = $album_config_value;
		}
	}
	if ($album_config['gd_version'] > 0)
	{
		$sql = 'UPDATE ' . GALLERY_CONFIG_TABLE . " SET config_value = 0 WHERE config_name = 'gd_version'";
		$result = $db->sql_query($sql);
		$album_config['gd_version'] = 0;
	}
}

$sql = 'SELECT *
	FROM ' . GALLERY_USERS_TABLE . '
	WHERE user_id = ' . (int) $user->data['user_id'];
$result = $db->sql_query($sql);
$user->gallery = $db->sql_fetchrow($result);

function load_gallery_config($gallery_config = false)
{
	global $db;

	$sql = 'SELECT * FROM ' . GALLERY_CONFIG_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$gallery_config[$row['config_name']] = $row['config_value'];
	}
	$db->sql_freeresult($result);

	return $gallery_config;
}
/**
* Get album children (for displaying the subalbums
*/
function get_album_children($album_id)
{
	global $db, $phpEx, $phpbb_root_path, $gallery_root_path;

	$rows = array();

	$sql = 'SELECT *
		FROM ' . GALLERY_ALBUMS_TABLE . "
		WHERE parent_id = $album_id
		ORDER BY left_id ASC";
	$result = $db->sql_query($sql);
	$navigation = '';

	while ($row = $db->sql_fetchrow($result))
	{
		$rows[] = $row;
		$navigation .= (($navigation) ? ', ' : '') . '<a href="' . append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $row['album_id']) . '">' . $row['album_name'] . '</a>';
	}
	$db->sql_freeresult($result);

	return $navigation;
}
/**
* Get album details
*/
function get_album_info($album_id)
{
	global $db, $user, $gallery_root_path, $phpbb_root_path, $phpEx;

	$sql = 'SELECT a.*, w.watch_id
		FROM ' . GALLERY_ALBUMS_TABLE . ' AS a
		LEFT JOIN ' . GALLERY_WATCH_TABLE . ' AS w
			ON a.album_id = w.album_id
				AND w.user_id = ' . $user->data['user_id']  . "
		WHERE a.album_id = $album_id";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		meta_refresh(3, append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx"));
		trigger_error(sprintf($user->lang['ALBUM_ID_NOT_EXIST'], $album_id));
	}

	return $row;
}

function generate_album_nav(&$album_data)
{
	global $db, $user, $template, $auth;
	global $phpEx, $phpbb_root_path, $gallery_root_path;

	// Get album parents
	$album_parents = get_album_parents($album_data);
	if ($album_data['album_user_id'] > 0 )
	{
		$sql = 'SELECT user_id, username, user_colour
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $album_data['album_user_id'];
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $user->lang['PERSONAL_ALBUMS'],
				'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx", 'mode=personal'))
			);
		}
	}

	// Build navigation links
	if (!empty($album_parents))
	{
		foreach ($album_parents as $parent_album_id => $parent_data)
		{
			list($parent_name, $parent_type) = array_values($parent_data);

			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $parent_name,
				'FORUM_ID'		=> $parent_album_id,
				'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $parent_album_id))
			);
		}
	}

	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $album_data['album_name'],
		'FORUM_ID'		=> $album_data['album_id'],
		'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", 'album_id=' . $album_data['album_id']))
	);
	$template->assign_vars(array(
		'ALBUM_ID' 		=> $album_data['album_id'],
		'ALBUM_NAME'	=> $album_data['album_name'],
		'ALBUM_DESC'	=> generate_text_for_display($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options']))
	);
	return;
}

/**
* Returns album parents as an array. Get them from album_data if available, or update the database otherwise
*/
function get_album_parents(&$album_data)
{
	global $db;

	$album_parents = array();
	if ($album_data['parent_id'] > 0)
	{
		if ($album_data['album_parents'] == '')
		{
			$sql = 'SELECT album_id, album_name, album_type
				FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE left_id < ' . $album_data['left_id'] . '
					AND right_id > ' . $album_data['right_id'] . '
					AND album_user_id = ' . $album_data['album_user_id'] . '
				ORDER BY left_id ASC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$album_parents[$row['album_id']] = array($row['album_name'], (int) $row['album_type']);
			}
			$db->sql_freeresult($result);

			$album_data['album_parents'] = serialize($album_parents);

			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
				SET album_parents = '" . $db->sql_escape($album_data['album_parents']) . "'
				WHERE parent_id = " . $album_data['parent_id'];
			$db->sql_query($sql);
		}
		else
		{
			$album_parents = unserialize($album_data['album_parents']);
		}
	}

	return $album_parents;
}

/**
* Generate gallery-albumbox
* @param	bool				$ignore_personals		list personal albums
* @param	string				$select_name			request_var() for the select-box
* @param	int					$select_id				selected album
* @param	string				$requested_permission	Exp: for moving a image you need i_upload permissions or a_moderate
* @param	(string || array)	$ignore_id				disabled albums, Exp: on moving: the album where the image is now
*
* @return	string				$gallery_albumbox		if ($select_name) {full select-box} else {list with options}
*/
function gallery_albumbox($ignore_personals, $select_name, $select_id = false, $requested_permission = false, $ignore_id = false)
{
	global $db, $user, $auth, $cache, $album_access_array;

	// Instead of the query we use the cache
	$album_data = $cache->obtain_album_list();

	$right = $last_a_u_id = 0;
	$access_own = $access_personal = false;
	$c_access_own = $c_access_personal = false;
	$padding_store = array('0' => '');
	$padding = $album_list = '';

	// Sometimes it could happen that albums will be displayed here not be displayed within the index page
	// This is the result of albums not displayed at index and a parent of a album with no permissions.
	// If this happens, the padding could be "broken"

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
		//is in the ignore_id
		((is_array($ignore_id) && in_array($row['album_id'], $ignore_id)) || $row['album_id'] == $ignore_id)
		||
		//need upload permissions (for moving)
		(($requested_permission == 'm_move') && (($row['album_type'] == G_ALBUM_CAT) || (!gallery_acl_check('i_upload', $row['album_id'], $row['album_user_id']) && !gallery_acl_check('m_move', $row['album_id'], $row['album_user_id'])))))
		{
			$disabled = true;
		}

		if (($select_id == SETTING_PERMISSIONS) && !$row['album_user_id'])
		{
			$list = true;
		}
		else if (!$row['album_user_id'])
		{
			if (gallery_acl_check('a_list', $row['album_id'], $row['album_user_id']))
			{
				$list = true;
			}
			#echo $row['album_user_id'] . $row['album_id'] . '<br />';
		}
		else if (!$ignore_personals)
		{
			if ($row['album_user_id'] == $user->data['user_id'])
			{
				if (!$c_access_own)
				{
					$c_access_own = true;
					$access_own = gallery_acl_check('a_list', OWN_GALLERY_PERMISSIONS);
				}
				$list = $access_own;
			}
			else if ($row['album_user_id'])
			{
				if (!$c_access_personal)
				{
					$c_access_personal = true;
					$access_personal = gallery_acl_check('a_list', PERSONAL_GALLERY_PERMISSIONS);
				}
				$list = $access_personal;
			}
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
* get image info
*/
function get_image_info($image_id)
{
	global $db, $user;

	$sql = 'SELECT *
		FROM ' . GALLERY_IMAGES_TABLE . ' i
		LEFT JOIN ' . GALLERY_WATCH_TABLE . ' w
			ON w.image_id = i.image_id
				AND w.user_id = ' . $user->data['user_id']  . '
		LEFT JOIN ' . GALLERY_FAVORITES_TABLE . ' f
			ON f.image_id = i.image_id
				AND f.user_id = ' . $user->data['user_id']  . '
		WHERE i.image_id = ' . $image_id;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		trigger_error('IMAGE_NOT_EXIST');
	}

	return $row;
}

/**
* Update Album-Information
*/
function update_lastimage_info($album_id)
{
	global $db, $user;

	//update album-information
	$images_real = $images = $album_user_id = 0;
	$sql = 'SELECT album_user_id
		FROM ' . GALLERY_ALBUMS_TABLE . "
		WHERE album_id = $album_id";
	$result = $db->sql_query($sql);
	if ($row = $db->sql_fetchrow($result))
	{
		$album_user_id = $row['album_user_id'];
	}
	$db->sql_freeresult($result);
	$sql = 'SELECT COUNT(image_id) images
		FROM ' . GALLERY_IMAGES_TABLE . "
		WHERE image_album_id = $album_id
			AND image_status = 1";
	$result = $db->sql_query($sql);
	if ($row = $db->sql_fetchrow($result))
	{
		$images = $row['images'];
	}
	$db->sql_freeresult($result);
	$sql = 'SELECT COUNT(image_id) images_real
		FROM ' . GALLERY_IMAGES_TABLE . "
		WHERE image_album_id = $album_id";
	$result = $db->sql_query($sql);
	if ($row = $db->sql_fetchrow($result))
	{
		$images_real = $row['images_real'];
	}
	$db->sql_freeresult($result);
	$sql = 'SELECT *
		FROM ' . GALLERY_IMAGES_TABLE . "
		WHERE image_album_id = $album_id
			AND image_status = 1
		ORDER BY image_time DESC";
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
* Obtain list of moderators of each album
*/
function get_album_moderators(&$album_moderators, $album_id = false)
{
	global $config, $template, $db, $phpbb_root_path, $phpEx, $user;

	// Have we disabled the display of moderators? If so, then return
	// from whence we came ...
	if (!$config['load_moderators'])
	{
		return;
	}

	$album_sql = '';

	if ($album_id !== false)
	{
		if (!is_array($album_id))
		{
			$album_id = array($album_id);
		}

		// If we don't have a forum then we can't have a moderator
		if (!sizeof($album_id))
		{
			return;
		}

		$album_sql = 'AND m.' . $db->sql_in_set('album_id', $album_id);
	}

	$sql_array = array(
		'SELECT'	=> 'm.*, u.user_colour, g.group_colour, g.group_type',

		'FROM'		=> array(
			GALLERY_MODSCACHE_TABLE	=> 'm',
		),

		'LEFT_JOIN'	=> array(
			array(
				'FROM'	=> array(USERS_TABLE => 'u'),
				'ON'	=> 'm.user_id = u.user_id',
			),
			array(
				'FROM'	=> array(GROUPS_TABLE => 'g'),
				'ON'	=> 'm.group_id = g.group_id',
			),
		),

		'WHERE'		=> "m.display_on_index = 1 $album_sql",
	);

	$sql = $db->sql_build_query('SELECT', $sql_array);
	$result = $db->sql_query($sql, 3600);

	while ($row = $db->sql_fetchrow($result))
	{
		if (!empty($row['user_id']))
		{
			$album_moderators[$row['album_id']][] = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
		}
		else
		{
			$album_moderators[$row['album_id']][] = '<a' . (($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . ';"' : '') . ' href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']) . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</a>';
		}
	}
	$db->sql_freeresult($result);

	return;
}

function handle_image_counter($image_id_ary, $add, $readd = false)
{
	global $config, $db;

	$num_images = 0;
	$sql = 'SELECT count(image_id) AS images, image_user_id
		FROM ' . GALLERY_IMAGES_TABLE . '
		WHERE image_status ' . (($readd) ? '<>' : '=') . ' 1
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
		$sql = 'UPDATE ' . GALLERY_USERS_TABLE . ' SET user_images = user_images ' . (($add) ? '+ ' : '- ') . $row['images'] . '
			WHERE ' . $db->sql_in_set('user_id', $row['image_user_id']);
		$db->sql_query($sql);
		if ($db->sql_affectedrows() != 1)
		{
			$sql = 'INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}
	}
	$db->sql_freeresult($result);

	set_config('num_images', (($add) ? $config['num_images'] + $num_images : $config['num_images'] - $num_images), true);
}

/**
* Get forum branch
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
*/
function generate_image_link($content, $mode, $image_id, $image_name, $album_id, $is_gif = false)
{
	global $phpbb_root_path, $phpEx, $user, $gallery_root_path, $album_config;

	if (!$album_config)
	{
		$album_config = load_gallery_config();
	}

	$image_page_url = append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id");
	$image_url = append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", "album_id=$album_id&amp;image_id=$image_id");
	$thumb_url = append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", "mode=thumbnail&amp;album_id=$album_id&amp;image_id=$image_id");
	$medium_url = append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", "mode=medium&amp;album_id=$album_id&amp;image_id=$image_id");
	switch ($content)
	{
		case 'image_name':
			$shorten_image_name = (utf8_strlen(htmlspecialchars_decode($image_name)) > $album_config['shorted_imagenames'] + 3 ) ? (utf8_substr(htmlspecialchars_decode($image_name), 0, $album_config['shorted_imagenames']) . '...') : ($image_name);
			$content = '<span style="font-weight: bold;">' . $shorten_image_name . '</span>';
		break;
		case 'thumbnail':
			$content = '<img src="{U_THUMBNAIL}" alt="{IMAGE_NAME}" title="{IMAGE_NAME}" />';
			$content = str_replace(array('{U_THUMBNAIL}', '{IMAGE_NAME}'), array($thumb_url, $image_name), $content);
		break;
		case 'fake_thumbnail':
			$content = '<img src="{U_THUMBNAIL}" alt="{IMAGE_NAME}" title="{IMAGE_NAME}" style="max-width: {FAKE_THUMB_SIZE}px; max-height: {FAKE_THUMB_SIZE}px;" />';
			$content = str_replace(array('{U_THUMBNAIL}', '{IMAGE_NAME}', '{FAKE_THUMB_SIZE}'), array($thumb_url, $image_name, $album_config['fake_thumb_size']), $content);
		break;
		case 'medium':
			$content = '<img src="{U_MEDIUM}" alt="{IMAGE_NAME}" title="{IMAGE_NAME}" />';
			$content = str_replace(array('{U_MEDIUM}', '{IMAGE_NAME}'), array($medium_url, $image_name), $content);
			//cheat for animated/transparent gifs
			if ($is_gif)
			{
				$content = '<img src="{U_MEDIUM}" alt="{IMAGE_NAME}" title="{IMAGE_NAME}" style="max-width: {MEDIUM_WIDTH_SIZE}px; max-height: {MEDIUM_HEIGHT_SIZE}px;" />';
				$content = str_replace(array('{U_MEDIUM}', '{IMAGE_NAME}', '{MEDIUM_HEIGHT_SIZE}', '{MEDIUM_WIDTH_SIZE}'), array($image_url, $image_name, $album_config['preview_rsz_height'], $album_config['preview_rsz_width']), $content);
			}
		break;
		case 'lastimage_icon':
			$content = $user->img('icon_topic_latest', 'VIEW_LATEST_IMAGE');
		break;
	}
	switch ($mode)
	{
		case 'highslide':
			$url = $image_url;
			$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}" class="highslide" onclick="return hs.expand(this)">{CONTENT}</a>';
		break;
		case 'lytebox':
			$url = $image_url;
			// LPI is a little credit to Dr.Death =)
			$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}" rel="lytebox[LPI]" class="image-resize">{CONTENT}</a>';
		break;
		case 'lytebox_slide_show':
			$url = $image_url;
			$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}" rel="lyteshow[album]" class="image-resize">{CONTENT}</a>';
		break;
		case 'image_page':
			$url = $image_page_url;
			$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}">{CONTENT}</a>';
		break;
		case 'image':
			$url = $image_url;
			$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}">{CONTENT}</a>';
		break;
		case 'none':
			$url = $image_page_url;
			$tpl = '{CONTENT}';
		break;
	}
	return str_replace(array('{IMAGE_URL}', '{IMAGE_NAME}', '{CONTENT}'), array($url, $image_name, $content), $tpl);
}

?>