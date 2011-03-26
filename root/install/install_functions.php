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

function get_dbms_infos()
{
	global $db;

	switch ($db->sql_layer)
	{
		case 'mysql':
			$return['db_schema'] = 'mysql_40';
			$return['delimiter'] = ';';
		break;

		case 'mysql4':
			if (version_compare($db->sql_server_info(true), '4.1.3', '>='))
			{
				$return['db_schema'] = 'mysql_41';
			}
			else
			{
				$return['db_schema'] = 'mysql_40';
			}
			$return['delimiter'] = ';';
		break;

		case 'mysqli':
			$return['db_schema'] = 'mysql_41';
			$return['delimiter'] = ';';
		break;

		case 'mssql':
		case 'mssql_odbc':
			$return['db_schema'] = 'mssql';
			$return['delimiter'] = 'GO';
		break;

		case 'postgres':
			$return['db_schema'] = 'postgres';
			$return['delimiter'] = ';';
		break;

		case 'sqlite':
			$return['db_schema'] = 'sqlite';
			$return['delimiter'] = ';';
		break;

		case 'firebird':
			$return['db_schema'] = 'firebird';
			$return['delimiter'] = ';;';
		break;

		case 'oracle':
			$return['db_schema'] = 'oracle';
			$return['delimiter'] = '/';
		break;

		default:
			trigger_error('Sorry, unsupported Databases found.');
		break;
	}

	return $return;
}

function get_gallery_version()
{
	global $db;
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

	$sql = 'SELECT config_value
		FROM ' . GALLERY_CONFIG_TABLE . "
		WHERE config_name = 'album_version'";
	$result = $db->sql_query($sql);
	$config_data = $db->sql_fetchfield('config_value');
	$db->sql_freeresult($result);

	$config_data = (isset($config_data)) ? $config_data : '0.0.0';

	if (in_array($config_data, array('0.1.2', '0.1.3', '0.2.0', '0.2.1', '0.2.2', '0.2.3', '0.3.0', '0.3.1')))
	{
		$sql = 'SELECT *
			FROM ' . GALLERY_ALBUMS_TABLE;
		$result = $db->sql_query_limit($sql, 1);
		$test = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($test === false)
		{
			// DB-Table missing
			$config_data = '0.1.2';
		}
		else
		{
			// No Schema Changes between 0.1.3 and 0.2.2
			$config_data = '0.1.3';
			if (defined('GALLERY_ALBUMS_TABLE'))
			{
				$config_data = '0.2.1';

				global $phpbb_root_path, $phpEx;

				$gallery_folder_name = (defined('ALBUM_DIR_NAME')) ? ALBUM_DIR_NAME : ((defined('GALLERY_ROOT_PATH')) ? GALLERY_ROOT_PATH : 'gallery/');
				include($phpbb_root_path . $gallery_folder_name . 'includes/functions.' . $phpEx);

				if (function_exists('make_album_jumpbox'))
				{
					$config_data = '0.2.2';
				}
				if (isset($test['album_user_id']))
				{
					$config_data = '0.2.3';
					if (function_exists('personal_album_access'))
					{
						$config_data = '0.3.0';
					}
					if (nv_check_column(SESSIONS_TABLE, 'session_album_id'))
					{
						$config_data = '0.3.1';
					}
				}
			}
		}
	}

	$db->sql_return_on_error(false);

	return $config_data;
}

/*
* Creates a new db-table
*	Note: we don't check for it on anyother way, so it might return a SQL-Error,
*	if you create the same table twice without this!
* @param	string	$table	table-name
* @param	bool	$drop	drops the table if it exist.
*/
function nv_create_table($table, $dbms_data, $drop = true)
{
	global $db, $table_prefix, $db_schema, $delimiter;

	$table_name = substr($table . '#', 6, -1);
	$db_schema = $dbms_data['db_schema'];
	$delimiter = $dbms_data['delimiter'];

	if ($drop)
	{
		nv_drop_table($table);
	}

	// locate the schema files
	$dbms_schema = 'schemas/' . $table . '/_' . $db_schema . '_schema.sql';
	$sql_query = @file_get_contents($dbms_schema);
	$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);
	$sql_query = preg_replace('/\n{2,}/', "\n", preg_replace('/^#.*$/m', "\n", $sql_query));
	$sql_query = split_sql_file($sql_query, $delimiter);
	// make the new one's
	foreach ($sql_query as $sql)
	{
		if (!$db->sql_query($sql))
		{
			$error = $db->sql_error();
			$this->p_master->db_error($error['message'], $sql, __LINE__, __FILE__);
		}
	}
	unset($sql_query);
}

/*
* Drops a db-table
* Note: you will loose all data!
* @param	string	$table	table-name
*/
function nv_drop_table($table)
{
	global $db, $table_prefix, $db_schema;

	$table_name = substr($table . '#', 6, -1);

	if (($db->sql_layer != 'mssql') && ($db->sql_layer != 'mssql_odbc'))
	{
		$sql = 'DROP TABLE IF EXISTS ' . $table_prefix . $table_name;
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
	}
	else
	{
		$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . $table_name . ')
			drop table ' . $table_prefix . $table_name;
		$sql = "IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '{$table_prefix}{$table_name}')
			DROP TABLE {$table_prefix}{$table_name}";
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
	}
}

/*
* Checks whether a column exists within a table
* @param	string	$table	table-name
* @param	string	$column	column-name
*
* @returns	bool	true || false
*/
function nv_check_column($table, $column)
{
	global $db;

	$phpbb_db_tools = new phpbb_db_tools($db);
	if ($phpbb_db_tools->sql_column_exists($table, $column))
	{
		return true;
	}

	return false;
}

/*
* Adds a column to a table
* @param	string	$table	table-name
* @param	string	$column	column-name
* @param	array	$values		column-type
*							array({column_type}, {default}, {auto_increment})
*							for explanation see: create_schema_files.php "*	Column Types:"
*/
function nv_add_column($table, $column, $values)
{
	global $db;

	$phpbb_db_tools = new phpbb_db_tools($db);
	if (!$phpbb_db_tools->sql_column_exists($table, $column))
	{
		$phpbb_db_tools->sql_column_add($table, $column, $values);
	}
}

/*
* Changes a column to a table
*	Note: it's not allowed to change the name of the column!
* @param	string	$table	table-name
* @param	string	$column	column-name
* @param	array	$values	column-type
*							array({column_type}, {default}, {auto_increment})
*							for explanation see: create_schema_files.php "*	Column Types:"
*/
function nv_change_column($table, $column_name, $column_data)
{
	global $db;

	$phpbb_db_tools = new phpbb_db_tools($db);
	if ($phpbb_db_tools->sql_column_exists($table, $column_name))
	{
		$phpbb_db_tools->sql_column_change($table, $column_name, $column_data);
	}
}

/*
* Remove a column from a table
* Note: you will loose all data of this column!
* @param	string	$table	table-name
* @param	string	$column	column-name
*/
function nv_remove_column($table, $column)
{
	global $db;

	$phpbb_db_tools = new phpbb_db_tools($db);
	if ($phpbb_db_tools->sql_column_exists($table, $column))
	{
		$phpbb_db_tools->sql_column_remove($table, $column);
	}
}

/*
* Adds a index to a table
* @param	string	$table	table-name
* @param	string	$index_name	index-name
* @param	array	$column	column-name
*/
function nv_add_index($table, $index_name, $column)
{
	global $db;

	$phpbb_db_tools = new phpbb_db_tools($db);
	if ($phpbb_db_tools->sql_column_exists($table, $column))
	{
		$phpbb_db_tools->sql_create_index($table_name, $index_name, $column);
	}
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
* Adds a module to the phpbb_modules-table
* @param	array	$array	Exp:	array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $choosen_acp_module,	'module_class' => 'acp',	'module_langname'=> 'PHPBB_GALLERY',	'module_mode' => '',	'module_auth' => '')
*/
function add_module($array)
{
	global $user;
	$modules = new acp_modules();
	$failed = $modules->update_module_data($array, true);
}

/*
* Removes a module of the phpbb_modules-table
*	Note: Be sure that the module exists, otherwise it may give an error message
*/
function remove_module($module_id, $module_class)
{
	global $user;
	$modules = new acp_modules();
	$modules->module_class = $module_class;
	$failed = $modules->delete_module($module_id);
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
	$gallery_url = generate_board_url() . '/' . GALLERY_ROOT_PATH;

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