<?php

/**
*
* @package phpBB3 - phpBB Gallery database updater
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
include($phpbb_root_path . 'includes/acp/acp_bbcodes.' . $phpEx);
include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

$gallery_root_path = GALLERY_ROOT_PATH;
// What sql_layer should we use?
switch ($db->sql_layer)
{
	case 'mysql':
		$db_schema = 'mysql_40';
		$delimiter = ';';
	break;

	case 'mysql4':
		if (version_compare($db->mysql_version, '4.1.3', '>='))
		{
			$db_schema = 'mysql_41';
		}
		else
		{
			$db_schema = 'mysql_40';
		}
		$delimiter = ';';
	break;

	case 'mysqli':
		$db_schema = 'mysql_41';
		$delimiter = ';';
	break;

	case 'mssql':
	case 'mssql_odbc':
		$db_schema = 'mssql';
		$delimiter = 'GO';
	break;

	case 'postgres':
		$db_schema = 'postgres';
		$delimiter = ';';
	break;

	case 'sqlite':
		$db_schema = 'sqlite';
		$delimiter = ';';
	break;

	case 'firebird':
		$db_schema = 'firebird';
		$delimiter = ';;';
	break;

	case 'oracle':
		$db_schema = 'oracle';
		$delimiter = '/';
	break;

	default:
		trigger_error('Sorry, unsupportet Databases found.');
	break;
}

/*
* Needed to handle the creating of the db-tables out of the schema-files
*/
function split_sql_file($sql, $delimiter)
{
	$sql = str_replace("\r" , '', $sql);
	$data = preg_split('/' . preg_quote($delimiter, '/') . '$/m', $sql);

	$data = array_map('trim', $data);

	// The empty case
	$end_data = end($data);

	if (empty($end_data))
	{
		unset($data[key($data)]);
	}

	return $data;
}

/*
* Create a back-link
*	Note: just like phpbb3's adm_back_link
* @param	string	$u_action	back-link-url
*/
function install_back_link($u_action)
{
	global $user;
	return '<br /><br /><a href="' . $u_action . '">&laquo; ' . $user->lang['BACK_TO_PREV'] . '</a>';
}

/*
* Creates a new db-table
*	Note: we don't check for it on anyother way, so it might return a SQL-Error,
*	if you create the same table twice without this!
* @param	string	$table	table-name
* @param	bool	$drop	drops the table if it exist.
*/
function nv_create_table($table, $drop = true)
{
	global $db, $table_prefix, $db_schema, $delimiter;

	$table_name = substr($table . '#', 6, -1);

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
* Creates a dropdown box with all modules to choose a parent-module for a new module to avoid "PARENT_NO_EXIST"
* Note: you will loose all data of this column!
* @param	string	$module_class	'acp' or 'mcp' or 'ucp'
* @param	int		$default_id		the "standard" id of the module: enter 0 if not available, Exp: 31
* @param	string	$default_langname	language-less name Exp for 31 (.MODs): ACP_CAT_DOT_MODS
*/
function select_parent_module($module_class, $default_id, $default_langname)
{
	global $db, $user;

	$select_module_list = '';
	$found_selected = ($default_id > 0) ? false : true;

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
			$default_note = $user->lang['MODULES_MODULE_ID'] . ': ' . $row['module_id'] . ' - ' . $user->lang['MODULES_MODULE_NAME'] . ': ' . $row['module_langname'];
		}
		if ($select_module_list == '')
		{
			$select_module_list .= '<option value="0">' . $user->lang['MODULES_SELECT_NONE'] . '</option>';
		}
		$select_module_list .= '<option value="' . $row['module_id'] . '"' . $selected . '>' . $user->lang['MODULES_MODULE_ID'] . ': ' . $row['module_id'] . ' - ' . $user->lang['MODULES_MODULE_NAME'] . ': ' . $row['module_langname'] . '</option>';
	}
	$db->sql_freeresult($result);
	if (!$default_id)
	{
		$default_note = $user->lang['MODULES_SELECT_NONE'];
	}

	$select_module = '<select name="select_' . $module_class . '_module">';
	if (!$found_selected)
	{
		$select_module .= '<option value="-1">' . $user->lang['MODULES_CREATE_PARENT'] . '</option>';
		$default_note = $user->lang['MODULES_CREATE_PARENT'];
	}
	$select_module .= $select_module_list;
	$select_module .= '</select>';
	$return = array(
		'default'	=> sprintf($user->lang['MODULES_ADVICE_SELECT'], $default_note),
		'list'		=> $select_module,
	);

	return $return;
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
function load_album_config()
{
	global $db;

	$sql = 'SELECT *
		FROM ' . GALLERY_CONFIG_TABLE;
	$result = $db->sql_query($sql);

	while( $row = $db->sql_fetchrow($result) )
	{
		$album_config_name = $row['config_name'];
		$album_config_value = $row['config_value'];
		$album_config[$album_config_name] = $album_config_value;
	}

	return $album_config;
}

/*
* Advanced: Add/update a gallery-config value
*/
function gallery_config_value($column, $value, $update = false)
{
	global $db;

	$sql = 'SELECT * FROM ' . GALLERY_CONFIG_TABLE . " WHERE config_name = '$column'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	if (!$row)
	{
		$sql_ary = array(
			'config_name'				=> $column,
			'config_value'				=> $value,
		);
		$db->sql_query('INSERT INTO ' . GALLERY_CONFIG_TABLE . $db->sql_build_array('INSERT', $sql_ary));
	}
	else if ($update)
	{
		$sql_ary = array(
			'config_name'				=> $column,
			'config_value'				=> $value,
		);
		$db->sql_query('UPDATE ' . GALLERY_CONFIG_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . " WHERE config_name = '$column'");
	}
}

/*
* Advanced: Add BBCode
* @param	string	$album_bbcode	"[$album_bbcode]"
*/
function add_bbcode($album_bbcode)
{
	global $db, $config, $phpbb_root_path;

	$sql = 'SELECT * FROM ' . BBCODES_TABLE . " WHERE bbcode_tag = '$album_bbcode'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	//which bbcode template:
	if (file_exists($phpbb_root_path . 'highslide/highslide-full.js'))
	{
			$bbcode_tpl = '<a class="highslide" onclick="return hs.expand(this)" href="' . generate_board_url() . '/' . GALLERY_ROOT_PATH . 'image.php?image_id={NUMBER}"><img src="' . generate_board_url() . '/' . GALLERY_ROOT_PATH . 'thumbnail.php?image_id={NUMBER}" alt="{NUMBER}" /></a>'
							. '<br /><a href="' . generate_board_url() . '/' . GALLERY_ROOT_PATH . 'image_page.php?image_id={NUMBER}">{NUMBER}</a>';
			$second_pass_replace = '<a class="highslide" onclick="return hs.expand(this)" href="' . generate_board_url() . '/' . GALLERY_ROOT_PATH . 'image.php?image_id=${1}"><img src="' . generate_board_url() . '/' . GALLERY_ROOT_PATH . 'thumbnail.php?image_id=${1}" alt="${1}" /></a>'
							. '<br /><a href="' . generate_board_url() . '/' . GALLERY_ROOT_PATH . 'image_page.php?image_id=${1}">${1}</a>';
	}
	else
	{
			$bbcode_tpl = '<a rel="lytebox" class="image-resize" href="' . generate_board_url() . '/' . GALLERY_ROOT_PATH . 'image.php?image_id={NUMBER}"><img src="' . generate_board_url() . '/' . GALLERY_ROOT_PATH . 'thumbnail.php?image_id={NUMBER}" alt="{NUMBER}" /></a>'
							. '<br /><a href="' . generate_board_url() . '/' . GALLERY_ROOT_PATH . 'image_page.php?image_id={NUMBER}">{NUMBER}</a>';
			$second_pass_replace = '<a rel="lytebox" class="image-resize" href="' . generate_board_url() . '/' . GALLERY_ROOT_PATH . 'image.php?image_id=${1}"><img src="' . generate_board_url() . '/' . GALLERY_ROOT_PATH . 'thumbnail.php?image_id=${1}" alt="${1}" /></a>'
							. '<br /><a href="' . generate_board_url() . '/' . GALLERY_ROOT_PATH . 'image_page.php?image_id=${1}">${1}</a>';
	}

	if (!$row)
	{
		$sql_ary = array(
			'bbcode_tag'				=> $album_bbcode,
			'bbcode_match'				=> '[' . $album_bbcode . ']{NUMBER}[/' . $album_bbcode . ']',
			'bbcode_tpl'				=> $bbcode_tpl,
			'display_on_posting'		=> true,
			'bbcode_helpline'			=> '',
			'first_pass_match'			=> '!\[' . $album_bbcode . '\]([0-9]+)\[/' . $album_bbcode . '\]!i',
			'first_pass_replace'		=> '[' . $album_bbcode . ':$uid]${1}[/' . $album_bbcode . ':$uid]',
			'second_pass_match'			=> '!\[' . $album_bbcode . ':$uid\]([0-9]+)\[/' . $album_bbcode . ':$uid\]!s',
			'second_pass_replace'		=> $second_pass_replace,
		);

		$sql = 'SELECT MAX(bbcode_id) as max_bbcode_id
			FROM ' . BBCODES_TABLE;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			$bbcode_id = $row['max_bbcode_id'] + 1;

			// Make sure it is greater than the core bbcode ids...
			if ($bbcode_id <= NUM_CORE_BBCODES)
			{
				$bbcode_id = NUM_CORE_BBCODES + 1;
			}
		}
		else
		{
			$bbcode_id = NUM_CORE_BBCODES + 1;
		}
		$sql_ary['bbcode_id'] = (int) $bbcode_id;

		$db->sql_query('INSERT INTO ' . BBCODES_TABLE . $db->sql_build_array('INSERT', $sql_ary));
	}
	else
	{
		$sql_ary = array(
			'bbcode_tpl'				=> $bbcode_tpl,
			'second_pass_replace'		=> $second_pass_replace,
		);
		$db->sql_query('UPDATE ' . BBCODES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE bbcode_id = ' . (int) $row['bbcode_id']);
	}
}

?>