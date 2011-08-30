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
if (!defined('IN_INSTALL'))
{
	exit;
}

$gallery_root_path = phpbb_gallery_url::path('relative');

function get_gallery_version()
{
	global $db;

	$sql = 'SELECT config_value
		FROM ' . CONFIG_TABLE . "
		WHERE config_name = 'phpbb_gallery_version'";
	$result = $db->sql_query($sql);
	$config_data = $db->sql_fetchfield('config_value');
	$db->sql_freeresult($result);

	if ($config_data)
	{
		return $config_data;
	}

	$db->sql_return_on_error(true);

	$sql = 'SELECT config_value
		FROM ' . GALLERY_CONFIG_TABLE . "
		WHERE config_name = 'phpbb_gallery_version'";
	$result = $db->sql_query($sql);
	$config_data = $db->sql_fetchfield('config_value');
	$db->sql_freeresult($result);

	if ($config_data)
	{
		$db->sql_return_on_error(false);
		return $config_data;
	}

	return '0.0.0';
}

/*
* Creates a dropdown box with all modules to choose a parent-module for a new module to avoid "PARENT_NO_EXIST"
* Note: you will loose all data of this column!
* @param	string	$module_class	'acp' or 'mcp' or 'ucp'
* @param	int		$default_id		the "standard" id of the module: enter 0 if not available, Exp: 31
* @param	string	$default_langname	language-less name Exp for 31 (.MODs): ACP_CAT_DOT_MODS
*/
function module_select($module_class, $default_id, $default_langname)
{
	global $db, $user;

	$module_options = '<option value="0">' . $user->lang['MODULES_SELECT_NONE'] . '</option>';
	$found_selected = false;

	$sql = 'SELECT module_id, module_langname, module_class
		FROM ' . MODULES_TABLE . "
		WHERE module_class = '$module_class'";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$selected = '';
		if (($row['module_id'] == $default_id) || ($row['module_langname'] == $default_langname))
		{
			$selected = ' selected="selected"';
			$found_selected = true;
		}
		$module_options .= '<option value="' . $row['module_id'] . '"' . $selected .'>' . ((isset($user->lang[$row['module_langname']])) ? $user->lang[$row['module_langname']] : $row['module_langname']) . '</option>';
	}
	if (!$found_selected && $default_id)
	{
		$module_options = '<option value="-1">' . $user->lang['MODULES_CREATE_PARENT'] . '</option>' . $module_options;
	}

	return $module_options;
}

/*
* Create a back-link
*	Note: just like phpbb3's adm_back_link
* @param	string	$u_action	back-link-url
*/
function adm_back_link($u_action)
{
	global $user;
	return '<br /><br /><a href="' . $u_action . '">&laquo; ' . $user->lang['BACK_TO_PREV'] . '</a>';
}

/*
* Advanced: Add BBCode
* @param	string	$album_bbcode	"[$album_bbcode]"
*/
function add_bbcode($album_bbcode)
{
	global $cache, $config, $db, $phpbb_root_path, $phpEx;

	if (!class_exists('acp_bbcodes'))
	{
		include($phpbb_root_path . 'includes/acp/acp_bbcodes.' . $phpEx);
	}
	$acp_bbcodes = new acp_bbcodes();
	$gallery_url = phpbb_gallery_url::path('full');

	$bbcode_match = '[' . $album_bbcode . ']{NUMBER}[/' . $album_bbcode . ']';
	$bbcode_tpl = '<a href="' . $gallery_url . 'image.php?image_id={NUMBER}"><img src="' . $gallery_url . 'image.php?mode=thumbnail&amp;image_id={NUMBER}" alt="{NUMBER}" /></a>';

	$sql_ary = $acp_bbcodes->build_regexp($bbcode_match, $bbcode_tpl);
	$sql_ary = array_merge($sql_ary, array(
		'bbcode_match'			=> $bbcode_match,
		'bbcode_tpl'			=> $bbcode_tpl,
		'display_on_posting'	=> true,
		'bbcode_helpline'		=> 'GALLERY_HELPLINE_ALBUM',
	));

	$sql = 'UPDATE ' . BBCODES_TABLE . '
		SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
		WHERE bbcode_tag = '" . $db->sql_escape($sql_ary['bbcode_tag']) . "'";
	$db->sql_query($sql);

	if ($db->sql_affectedrows() <= 1)
	{
		$sql = 'SELECT bbcode_id
			FROM ' . BBCODES_TABLE . "
			WHERE bbcode_tag = '" . $db->sql_escape($sql_ary['bbcode_tag']) . "'";
		$result = $db->sql_query($sql);
		$bbcode_id = (int) $db->sql_fetchfield('bbcode_id');
		$db->sql_freeresult($result);

		if (!$bbcode_id)
		{
			$sql = 'SELECT bbcode_id
				FROM ' . BBCODES_TABLE . "
				ORDER BY bbcode_id DESC";
			$result = $db->sql_query_limit($sql, 1);
			$max_bbcode_id = (int) $db->sql_fetchfield('bbcode_id') + 1;
			$db->sql_freeresult($result);

			if ($max_bbcode_id <= NUM_CORE_BBCODES)
			{
				$max_bbcode_id = NUM_CORE_BBCODES + 1;
			}

			// The table does NOT have autoincrement because of the core-bbcodes, so we need to add it here.
			$sql_ary['bbcode_id'] = $max_bbcode_id;
			$sql = 'INSERT INTO ' . BBCODES_TABLE . '
				' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}
	}

	$cache->destroy('sql', BBCODES_TABLE);
}

/**
* Recalculate Binary Tree
*
* borrowed from phpBB3
* @author: phpBB Group
* @function: recalc_btree
* @fixed with recalc_btree_adv.diff from http://www.phpbb.com/bugs/phpbb3/41555
*/
function recalc_btree($sql_id, $sql_table, $where_options = array())
{
	global $db;

	if (!$sql_id || !$sql_table)
	{
		return;
	}

	$sql_where = '';
	if ($where_options)
	{
		$options = array();
		foreach ($where_options as $option)
		{
			$options[] = "{$option['fieldname']} = '" . $db->sql_escape($option['fieldvalue']) . "'";
		}
		$sql_where = 'WHERE ' . implode(' AND ', $options);
	}

	$sql = "SELECT $sql_id, parent_id, left_id, right_id
		FROM $sql_table
		$sql_where
		ORDER BY left_id ASC, parent_id ASC, $sql_id ASC";
	$f_result = $db->sql_query($sql);

	while ($item_data = $db->sql_fetchrow($f_result))
	{
		if ($item_data['parent_id'])
		{
			$sql = "SELECT left_id, right_id
				FROM $sql_table
				$sql_where " . (($sql_where) ? 'AND' : 'WHERE') . "
					$sql_id = {$item_data['parent_id']}";
			$result = $db->sql_query($sql);

			if (!$row = $db->sql_fetchrow($result))
			{
				$sql = "UPDATE $sql_table
					SET parent_id = 0
					$sql_where " . (($sql_where) ? 'AND' : 'WHERE') . "
						$sql_id = " . $item_data[$sql_id];
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);

			$sql = "UPDATE $sql_table
				SET left_id = left_id + 2, right_id = right_id + 2
				$sql_where " . (($sql_where) ? 'AND' : 'WHERE') . "
					left_id > {$row['right_id']}";
			$db->sql_query($sql);

			$sql = "UPDATE $sql_table
				SET right_id = right_id + 2
				$sql_where " . (($sql_where) ? 'AND' : 'WHERE') . "
					{$row['left_id']} BETWEEN left_id AND right_id";
			$db->sql_query($sql);

			$item_data['left_id'] = $row['right_id'];
			$item_data['right_id'] = $row['right_id'] + 1;
		}
		else
		{
			$sql = "SELECT MAX(right_id) AS right_id
				FROM $sql_table
				$sql_where";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$item_data['left_id'] = $row['right_id'] + 1;
			$item_data['right_id'] = $row['right_id'] + 2;
		}

		$sql = "UPDATE $sql_table
			SET left_id = {$item_data['left_id']}, right_id = {$item_data['right_id']}
			$sql_where " . (($sql_where) ? 'AND' : 'WHERE') . "
				$sql_id = " . $item_data[$sql_id];
		$db->sql_query($sql);
	}
	$db->sql_freeresult($f_result);

	// Reset to minimum possible left and right id
	$sql = "SELECT MIN(left_id) min_left_id, MIN(right_id) min_right_id
		FROM $sql_table
		$sql_where";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$substract = (int) (min($row['min_left_id'], $row['min_right_id']) - 1);

	if ($substract > 0)
	{
		$sql = "UPDATE $sql_table
			SET left_id = left_id - $substract, right_id = right_id - $substract
			$sql_where";
		$db->sql_query($sql);
	}
}

function remove_duplicated_rates()
{
	global $db;

	$sql = 'SELECT *, COUNT(*) AS num_extries
		FROM ' . GALLERY_RATES_TABLE . '
		GROUP BY rate_image_id , rate_user_id
		WHERE num_extries > 1';
}

function config_mapping()
{
	return array(
		'gallery_total_images'		=> 'disp_total_images',
		'gallery_viewtopic_icon'	=> 'viewtopic_icon',
		'gallery_viewtopic_images'	=> 'viewtopic_images',
		'gallery_viewtopic_link'	=> 'viewtopic_link',

		'num_comments'				=> 'num_comments',
		'num_images'				=> 'num_images',
		'personal_counter'			=> 'num_pegas',

		'watermark_images'			=> 'watermark_enabled',
		'watermark_source'			=> 'watermark_source',
		'watermark_height'			=> 'watermark_height',
		'watermark_width'			=> 'watermark_width',
		'watermark_position'		=> 'watermark_position',

		'jpg_allowed'				=> 'allow_jpg',
		'png_allowed'				=> 'allow_png',
		'gif_allowed'				=> 'allow_gif',
		'jpg_quality'				=> 'jpg_quality',

		'allow_comments'			=> 'allow_comments',
		'allow_rates'				=> 'allow_rates',
		'allow_resize_images'		=> 'allow_resize',
		'allow_rotate_images'		=> 'allow_rotate',

		'captcha_comment'			=> 'captcha_comment',
		'captcha_upload'			=> 'captcha_upload',

		'version_check_version'		=> 'mvc_version',
		'version_check_time'		=> 'mvc_time',

		'link_thumbnail'			=> 'link_thumbnail',
		'link_image_name'			=> 'link_image_name',
		'link_image_icon'			=> 'link_image_icon',

		'disp_fake_thumb'			=> 'mini_thumbnail_disp',
		'fake_thumb_size'			=> 'mini_thumbnail_size',
		'contests_ended'			=> 'contests_ended',

		'rrc_gindex_mode'			=> 'rrc_gindex_mode',
		'rrc_gindex_rows'			=> 'rrc_gindex_rows',
		'rrc_gindex_columns'		=> 'rrc_gindex_columns',
		'rrc_gindex_comments'		=> 'rrc_gindex_comments',
		'rrc_gindex_crows'			=> 'rrc_gindex_crows',
		'rrc_gindex_contests'		=> 'rrc_gindex_contests',
		'rrc_gindex_display'		=> 'rrc_gindex_display',
		'rrc_gindex_pgalleries'		=> 'rrc_gindex_pegas',

		'disp_whoisonline'			=> 'disp_whoisonline',
		'disp_birthdays'			=> 'disp_birthdays',
		'disp_statistic'			=> 'disp_statistic',
		'disp_login'				=> 'disp_login',

		'personal_album_index'		=> 'pegas_index_album',
		'pgalleries_per_page'		=> 'pegas_per_page',
		'sort_method'				=> 'default_sort_key',
		'sort_order'				=> 'default_sort_dir',
		'shorted_imagenames'		=> 'shortnames',

		'thumbnail_info_line'	=> 'thumbnail_infoline',
		'thumbnail_quality'		=> 'thumbnail_quality',
		'thumbnail_cache'		=> 'thumbnail_cache',

		'hotlink_prevent'		=> 'allow_hotlinking',
		'hotlink_allowed'		=> 'hotlinking_domains',
		'gd_version'			=> 'gdlib_version',

		'max_file_size'			=> 'max_filesize',
		'max_width'				=> 'max_width',
		'max_height'			=> 'max_height',
		'medium_cache'			=> 'medium_cache',
		'preview_rsz_height'	=> 'medium_height',
		'preview_rsz_width'		=> 'medium_width',

		'rows_per_page'			=> 'album_rows',
		'cols_per_page'			=> 'album_columns',
		'album_display'			=> 'album_display',
		'view_image_url'		=> 'disp_image_url',
		'exif_data'				=> 'disp_exifdata',
		'rate_scale'			=> 'max_rating',
		'comment_length'		=> 'comment_length',
		'search_display'		=> 'search_display',
		'link_imagepage'		=> 'link_imagepage',

		'rrc_profile_mode'		=> 'rrc_profile_mode',
		'rrc_profile_columns'	=> 'rrc_profile_columns',
		'rrc_profile_rows'		=> 'rrc_profile_rows',
		'rrc_profile_display'	=> 'rrc_profile_display',
		'rrc_profile_pgalleries'=> 'rrc_profile_pegas',
		'user_images_profile'		=> 'profile_user_images',
		'personal_album_profile'	=> 'profile_pega',
		'newest_pgallery_user_id'		=> 'newest_pega_user_id',
		'newest_pgallery_username'		=> 'newest_pega_username',
		'newest_pgallery_user_colour'	=> 'newest_pega_user_colour',
		'newest_pgallery_album_id'		=> 'newest_pega_album_id',

		'images_per_album'		=> 'album_images',
		'upload_images'			=> 'num_uploads',
		'description_length'	=> 'description_length',
	);
}

?>