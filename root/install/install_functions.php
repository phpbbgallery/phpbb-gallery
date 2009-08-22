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

$gallery_root_path = GALLERY_ROOT_PATH;

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
			trigger_error('Sorry, unsupportet Databases found.');
		break;
	}

	return $return;
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

	if ($db->sql_layer != 'mssql')
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
* Advanced: Add/update a gallery-config value
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
* Advanced: Load gallery-config values
*/
function load_gallery_config()
{
	global $db;

	$gallery_config = array();

	$sql = 'SELECT * FROM ' . GALLERY_CONFIG_TABLE;
	$result = $db->sql_query($sql);
	while( $row = $db->sql_fetchrow($result) )
	{
		$gallery_config[$row['config_name']] = $row['config_value'];
	}
	$db->sql_freeresult($result);

	return $gallery_config;
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

/**
* Set the default config
*/
function set_default_config()
{
	global $config, $gallery_config;

	// Previous configs
	set_config('num_images', 0, true);
	set_config('gallery_total_images', 1);

	set_gallery_config('max_file_size', 512000);
	set_gallery_config('max_width', 800);
	set_gallery_config('max_height', 600);
	set_gallery_config('rows_per_page', 3);
	set_gallery_config('cols_per_page', 4);
	set_gallery_config('thumbnail_quality', 50);
	set_gallery_config('thumbnail_size', 125);
	set_gallery_config('thumbnail_cache', 1);
	set_gallery_config('sort_method', 't');
	set_gallery_config('sort_order', 'd');
	set_gallery_config('jpg_allowed', 1);
	set_gallery_config('png_allowed', 1);
	set_gallery_config('gif_allowed', 0);
	set_gallery_config('desc_length', 512);
	set_gallery_config('hotlink_prevent', 0);
	set_gallery_config('hotlink_allowed', 'flying-bits.org');
	set_gallery_config('rate', 1);
	set_gallery_config('rate_scale', 10);
	set_gallery_config('comment', 1);
	set_gallery_config('gd_version', 2);
	set_gallery_config('watermark_images', 1);
	set_gallery_config('watermark_source', GALLERY_IMAGE_PATH . 'watermark.png');
	set_gallery_config('preview_rsz_height', 768);
	set_gallery_config('preview_rsz_width', 1024);
	set_gallery_config('upload_images', 10);
	set_gallery_config('thumbnail_info_line', 1);

	// Added 0.3.2-RC1
	set_gallery_config('fake_thumb_size', 70);
	set_gallery_config('disp_fake_thumb', 1);
	set_gallery_config('personal_counter', 0);
	set_gallery_config('exif_data', 1);
	set_gallery_config('watermark_height', 50);
	set_gallery_config('watermark_width', 200);

	// Added 0.4.0-RC3
	set_gallery_config('shorted_imagenames', 25);

	// Added 0.4.0
	set_gallery_config('comment_length', 1024);
	set_gallery_config('description_length', 1024);
	set_gallery_config('allow_rates', 1);
	set_gallery_config('allow_comments', 1);
	set_gallery_config('link_thumbnail', 'image_page');
	set_gallery_config('link_image_name', 'image_page');
	set_gallery_config('link_image_icon', 'image_page');
	set_gallery_config('personal_album_index', 0);
	set_gallery_config('view_image_url', 1);
	set_gallery_config('medium_cache', 1);

	// Added 0.4.1
	set_gallery_config('link_imagepage', 'image_page');

	// Added 0.5.0
	set_gallery_config('rrc_gindex_mode', 7);
	set_gallery_config('rrc_gindex_rows', 1);
	set_gallery_config('rrc_gindex_columns', 4);
	set_gallery_config('rrc_gindex_comments', 0);

	// Added 0.5.1:
	set_gallery_config('user_images_profile', 1);
	set_gallery_config('personal_album_profile', 1);
	set_gallery_config('rrc_profile_mode', 3);
	set_gallery_config('rrc_profile_columns', 4);
	set_gallery_config('rrc_profile_rows', 1);
	set_gallery_config('rrc_gindex_crows', 5);
	set_gallery_config('contests_ended', 0);
	set_gallery_config('rrc_gindex_contests', 1);

	// Added 0.5.2:
	set_gallery_config('rrc_gindex_display', 173);
	set_gallery_config('rrc_profile_display', 141);
	set_gallery_config('album_display', 254);

	// Added 0.5.3:
	set_config('gallery_viewtopic_icon', 1);
	set_config('gallery_viewtopic_images', 1);
	set_config('gallery_viewtopic_link', 0);

	// Added 0.5.4:
	set_gallery_config('num_comments', 0);
	set_gallery_config('disp_login', 1);
	set_gallery_config('disp_whoisonline', 1);
	set_gallery_config('disp_birthdays', 0);
	set_gallery_config('disp_statistic', 1);
	set_gallery_config('rrc_gindex_pgalleries', 1);
	set_gallery_config('newest_pgallery_user_id', 0);
	set_gallery_config('newest_pgallery_username', '');
	set_gallery_config('newest_pgallery_user_colour', '');
	set_gallery_config('newest_pgallery_album_id', 0);

	// Added 1.0.0-dev:
	set_gallery_config('pgalleries_per_page', 10);
	set_gallery_config('images_per_album', 1024);
	set_gallery_config('watermark_position', 20);

	// Added 1.0.0-RC1:
	set_gallery_config('rrc_profile_pgalleries', 1);

	// Added 1.0.2:
	set_gallery_config('allow_resize_images', 1);
	set_gallery_config('allow_rotate_images', 1);

	// Added 1.0.3:
	set_gallery_config('jpg_quality', 100);
	set_gallery_config('search_display', 45);
	set_gallery_config('version_check_version', '0.0.0');
	set_gallery_config('version_check_time', 0);
}

?>