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
function add_module($array)
{
	global $user;
	$modules = new acp_modules();
	$failed = $modules->update_module_data($array, true);
	if ($failed == 'PARENT_NO_EXIST')
	{
		$user->add_lang('mods/info_acp_gallery');
		trigger_error(sprintf($user->lang['MISSING_PARENT_MODULE'], $array['parent_id'], $user->lang[$array['module_langname']]));
	}
}
function deactivate_module($module_langname)
{
	global $db;

	$sql = 'UPDATE ' . MODULES_TABLE . " SET module_enabled = 0 WHERE module_langname = '$module_langname';";
	$db->sql_query($sql);
}
function gallery_column($table, $column, $values)
{
	global $db;

	$phpbb_db_tools = new phpbb_db_tools($db);
	if (!$phpbb_db_tools->sql_column_exists($table, $column))
	{
		$phpbb_db_tools->sql_column_add($table, $column, $values);
	}
}
function delete_gallery_column($table, $column)
{
	global $db;

	$phpbb_db_tools = new phpbb_db_tools($db);
	if ($phpbb_db_tools->sql_column_exists($table, $column))
	{
		$phpbb_db_tools->sql_column_remove($table, $column);
	}
}
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
	else
	{
		$sql_ary = array(
			'config_name'				=> $column,
			'config_value'				=> $value,
		);
		$db->sql_query('UPDATE ' . GALLERY_CONFIG_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . " WHERE config_name = '$column'");
	}
}
function add_bbcode($album_bbcode)
{
	global $db, $config;

	$sql = 'SELECT * FROM ' . BBCODES_TABLE . " WHERE bbcode_tag = '$album_bbcode'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		$sql_ary = array(
			'bbcode_tag'				=> $album_bbcode,
			'bbcode_match'				=> '[' . $album_bbcode . ']{NUMBER}[/' . $album_bbcode . ']',
			'bbcode_tpl'				=> '<a href="' . generate_board_url() . GALLERY_ROOT_PATH . '/image_page.php?image_id={NUMBER}"><img src="' . generate_board_url() . GALLERY_ROOT_PATH . '/thumbnail.php?image_id={NUMBER}" alt="image_id: {NUMBER}" /></a>',
			'display_on_posting'		=> true,
			'bbcode_helpline'			=> '',
			'first_pass_match'			=> '!\[' . $album_bbcode . '\]([0-9]+)\[/' . $album_bbcode . '\]!i',
			'first_pass_replace'		=> '[' . $album_bbcode . ':$uid]${1}[/' . $album_bbcode . ':$uid]',
			'second_pass_match'			=> '!\[' . $album_bbcode . ':$uid\]([0-9]+)\[/' . $album_bbcode . ':$uid\]!s',
			'second_pass_replace'		=> '<a href="' . generate_board_url() . GALLERY_ROOT_PATH . '/image_page.php?image_id=${1}"><img src="' . generate_board_url() . GALLERY_ROOT_PATH . '/thumbnail.php?image_id=${1}" alt="image_id: ${1}" /></a>',
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
			'bbcode_tpl'				=> '<a href="' . generate_board_url() . GALLERY_ROOT_PATH . '/image_page.php?image_id={NUMBER}"><img src="' . generate_board_url() . GALLERY_ROOT_PATH . '/thumbnail.php?image_id={NUMBER}" alt="image_id: {NUMBER}" /></a>',
			'second_pass_replace'		=> '<a href="' . generate_board_url() . GALLERY_ROOT_PATH . '/image_page.php?image_id=${1}"><img src="' . generate_board_url() . GALLERY_ROOT_PATH . '/thumbnail.php?image_id=${1}" alt="image_id: ${1}" /></a>',
		);
		$db->sql_query('UPDATE ' . BBCODES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE bbcode_id = ' . (int) $row['bbcode_id']);
	}
}
function gallery_create_table_slap_db_tools($table, $drop = true)
{
	global $db, $table_prefix, $db_schema, $delimiter;

	$table_name = substr($table . '#', 6, -1);

	if ($drop)
	{
		//Drop if existing
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

function change_column($table, $column_name, $column_data)
{
	global $db;

	$phpbb_db_tools = new phpbb_db_tools($db);
	if ($phpbb_db_tools->sql_column_exists($table, $column_name))
	{
		$phpbb_db_tools->sql_column_change($table, $column_name, $column_data);
	}
}

?>