<?php

/**
*
* @package phpBB3 - phpBB Gallery database updater
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
include($phpbb_root_path . 'includes/acp/acp_bbcodes.' . $phpEx);
include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();
$user->add_lang('mods/gallery_install');
$user->add_lang('mods/info_acp_gallery');

$new_mod_version = '0.3.2';
$page_title = 'phpBB Gallery v' . $new_mod_version;
$log_name = 'Modification "phpBB Gallery"' . ((request_var('update', 0) > 0) ? '-Update' : '') . ' v' . $new_mod_version;

$mode = request_var('mode', '', true);
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
	else if ($update)
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
$delete = request_var('delete', 0);
$install = request_var('install', 0);
$update = request_var('update', 0);
$version = request_var('v', '0', true);
$convert = request_var('convert', 0);
$convert_prefix = request_var('convert_prefix', '', true);

switch ($mode)
{
	case 'install':
		$installed = false;
		if ($install == 1)
		{
			gallery_create_table_slap_db_tools('phpbb_gallery_albums', true);
			gallery_create_table_slap_db_tools('phpbb_gallery_comments', true);
			gallery_create_table_slap_db_tools('phpbb_gallery_config', true);
			gallery_create_table_slap_db_tools('phpbb_gallery_images', true);
			gallery_create_table_slap_db_tools('phpbb_gallery_rates', true);
			gallery_create_table_slap_db_tools('phpbb_gallery_roles', true);
			gallery_create_table_slap_db_tools('phpbb_gallery_permissions', true);

			//fill the GALLERY_CONFIG_TABLE with some values
			gallery_config_value('max_pics', '1024');
			gallery_config_value('user_pics_limit', '50');
			gallery_config_value('mod_pics_limit', '250');
			gallery_config_value('max_file_size', '128000');
			gallery_config_value('max_width', '800');
			gallery_config_value('max_height', '600');
			gallery_config_value('rows_per_page', '3');
			gallery_config_value('cols_per_page', '4');
			gallery_config_value('fullpic_popup', '0');
			gallery_config_value('thumbnail_quality', '50');
			gallery_config_value('thumbnail_size', '125');
			gallery_config_value('thumbnail_cache', '1');
			gallery_config_value('sort_method', 'image_time');
			gallery_config_value('sort_order', 'DESC');
			gallery_config_value('jpg_allowed', '1');
			gallery_config_value('png_allowed', '1');
			gallery_config_value('gif_allowed', '0');
			gallery_config_value('desc_length', '512');
			gallery_config_value('hotlink_prevent', '0');
			gallery_config_value('hotlink_allowed', 'flying-bits.org');
			gallery_config_value('personal_gallery', '0');
			gallery_config_value('personal_gallery_private', '0');
			gallery_config_value('personal_gallery_limit', '10');
			gallery_config_value('personal_gallery_view', '1');
			gallery_config_value('rate', '1');
			gallery_config_value('rate_scale', '10');
			gallery_config_value('comment', '1');
			gallery_config_value('gd_version', '2');
			gallery_config_value('album_version', '0.2.4');
			gallery_config_value('watermark_images', 1);
			gallery_config_value('watermark_source', 'gallery/mark.png');
			gallery_config_value('preview_rsz_height', 600);
			gallery_config_value('preview_rsz_width', 800);
			gallery_config_value('upload_images', 10);
			gallery_config_value('thumbnail_info_line', 1);
			gallery_config_value('fake_thumb_size', 141);
			gallery_config_value('disp_fake_thumb', 1);
			$album_config = load_album_config();

			// create the modules
			$acp_gallery = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => 31,	'module_class' => 'acp',	'module_langname'=> 'PHPBB_GALLERY',	'module_mode' => '',	'module_auth' => '');
			add_module($acp_gallery);
			$sql = 'SELECT module_id
				FROM ' . MODULES_TABLE . "
				WHERE module_langname = 'PHPBB_GALLERY'
				LIMIT 1";
			$result = $db->sql_query($sql);
			while( $row = $db->sql_fetchrow($result) )
			{
				$acp_gallery['module_id'] = $row['module_id'];
			}
			$db->sql_freeresult($result);

			$acp_gallery_overview = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_OVERVIEW',	'module_mode' => 'overview',	'module_auth' => '');
			add_module($acp_gallery_overview);
			$acp_gallery_manage_albums = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_MANAGE_ALBUMS',	'module_mode' => 'manage_albums',	'module_auth' => '');
			add_module($acp_gallery_manage_albums);
			$acp_gallery_manage_cache = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname' => 'ACP_GALLERY_MANAGE_CACHE',	'module_mode' => 'manage_cache',	'module_auth' => '');
			add_module($acp_gallery_manage_cache);
			$acp_configure_gallery = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_CONFIGURE_GALLERY',	'module_mode' => 'configure_gallery',	'module_auth' => '');
			add_module($acp_configure_gallery);
			$album_permissions = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_ALBUM_PERMISSIONS',	'module_mode' => 'album_permissions',	'module_auth' => '');
			add_module($album_permissions);
			//REMOVE WITH 0.3.3//$album_personal_permissions = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_ALBUM_PERSONAL_PERMISSIONS',	'module_mode' => 'album_personal_permissions',	'module_auth' => '');
			//REMOVE WITH 0.3.3//add_module($album_personal_permissions);
			$import_images = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname'=> 'ACP_IMPORT_ALBUMS',	'module_mode' => 'import_images',	'module_auth' => '');
			add_module($import_images);
			$ucp_gallery = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => 163,	'module_class' => 'ucp',	'module_langname' => 'UCP_GALLERY_PERSONAL_ALBUMS',	'module_mode' => 'manage_albums',	'module_auth' => '');
			add_module($ucp_gallery);

			$gd_check = function_exists('gd_info') ? gd_info() : array();
			$gd_success = isset($gd_check['GD Version']);
			if (!$gd_success && ($album_config['gd_version'] > 0))
			{
				$sql = 'UPDATE ' . GALLERY_CONFIG_TABLE . "SET config_value = 0 WHERE config_name = 'gd_version'";
				$result = $db->sql_query($sql);
				$album_config['gd_version'] = 0;
			}

			add_bbcode('album');
			gallery_column(USERS_TABLE, 'album_id', array('UINT', 0));
			//REMOVE WITH 0.3.3//gallery_column(GROUPS_TABLE, 'allow_personal_albums', array('UINT', 1));
			//REMOVE WITH 0.3.3//gallery_column(GROUPS_TABLE, 'view_personal_albums', array('UINT', 1));
			//REMOVE WITH 0.3.3//gallery_column(GROUPS_TABLE, 'personal_subalbums', array('UINT', 10));
			gallery_column(SESSIONS_TABLE, 'session_album_id', array('UINT', 0));
			gallery_config_value('album_version', $new_mod_version, true);

			// clear cache and log what we did
			$cache->purge();
			add_log('admin', 'LOG_INSTALL_INSTALLED', $log_name);
			add_log('admin', 'LOG_PURGE_CACHE');
			$installed = true;
		}
	break;
	case 'update':
		$updated = false;
		if ($update == 1)
		{
			switch ($version)
			{
				case '0.1.2':
					gallery_column(GALLERY_IMAGES_TABLE, 'pic_desc_bbcode_bitfield', array('VCHAR:255', ''));
					gallery_column(GALLERY_IMAGES_TABLE, 'pic_desc_bbcode_uid', array('VCHAR:8', ''));
					gallery_column(GALLERY_ALBUMS_TABLE, 'cat_desc_bbcode_bitfield', array('VCHAR:255', ''));
					gallery_column(GALLERY_ALBUMS_TABLE, 'cat_desc_bbcode_uid', array('VCHAR:8', ''));
					gallery_column(GALLERY_COMMENTS_TABLE, 'comment_text_bbcode_bitfield', array('VCHAR:255', ''));
					gallery_column(GALLERY_COMMENTS_TABLE, 'comment_text_bbcode_uid', array('VCHAR:8', ''));
				//no break;

				case '0.1.3':
					gallery_create_table_slap_db_tools('phpbb_gallery_albums', true);
					gallery_create_table_slap_db_tools('phpbb_gallery_comments', true);
					gallery_create_table_slap_db_tools('phpbb_gallery_config', true);
					gallery_create_table_slap_db_tools('phpbb_gallery_images', true);
					gallery_create_table_slap_db_tools('phpbb_gallery_rates', true);

					// first lets make the albums...
					$left_id = 1;
					$sql = 'SELECT *
						FROM ' . ALBUM_CAT_TABLE . '
						ORDER BY cat_order';
					$result = $db->sql_query($sql);
					while( $row = $db->sql_fetchrow($result) )
					{
						$album_data = array(
							'album_id'						=> $row['cat_id'],
							'album_name'					=> $row['cat_title'],
							'parent_id'						=> 0,
							'left_id'						=> $left_id,
							'right_id'						=> $left_id + 1,
							'album_parents'					=> '',
							'album_type'					=> 2,
							'album_desc'					=> $row['cat_desc'],
							'album_desc_uid'				=> $row['cat_desc_bbcode_uid'],
							'album_desc_bitfield'			=> $row['cat_desc_bbcode_bitfield'],
							'album_desc_options'			=> 7,
							'album_view_level'				=> $row['cat_view_level'],
							'album_upload_level'			=> $row['cat_upload_level'],
							'album_rate_level'				=> $row['cat_rate_level'],
							'album_comment_level'			=> $row['cat_comment_level'],
							'album_edit_level'				=> $row['cat_edit_level'],
							'album_delete_level'			=> $row['cat_delete_level'],
							'album_view_groups'				=> $row['cat_view_groups'],
							'album_upload_groups'			=> $row['cat_upload_groups'],
							'album_rate_groups'				=> $row['cat_rate_groups'],
							'album_comment_groups'			=> $row['cat_comment_groups'],
							'album_edit_groups'				=> $row['cat_edit_groups'],
							'album_delete_groups'			=> $row['cat_delete_groups'],
							'album_moderator_groups'		=> $row['cat_moderator_groups'],
							'album_approval'				=> $row['cat_approval'],
						);
						generate_text_for_storage($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options'], true, true, true);
						$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
						$left_id = $left_id + 2;
					}
					$db->sql_freeresult($result);

					// second the rates...
					$sql = 'SELECT *
						FROM ' . ALBUM_RATE_TABLE . '
						ORDER BY rate_pic_id';
					$result = $db->sql_query($sql);
					while( $row = $db->sql_fetchrow($result) )
					{
						$rate_data = array(
							'rate_image_id'					=> $row['rate_pic_id'],
							'rate_user_id'					=> $row['rate_user_id'],
							'rate_user_ip'					=> $row['rate_user_ip'],
							'rate_point'					=> $row['rate_point'],
						);
						$db->sql_query('INSERT INTO ' . GALLERY_RATES_TABLE . ' ' . $db->sql_build_array('INSERT', $rate_data));
					}
					$db->sql_freeresult($result);

					// third the comments...
					$sql = 'SELECT *
						FROM ' . ALBUM_COMMENT_TABLE . '
						ORDER BY comment_id';
					$result = $db->sql_query($sql);
					while( $row = $db->sql_fetchrow($result) )
					{
						$comment_data = array(
							'comment_id'			=> $row['comment_id'],
							'comment_image_id'		=> $row['comment_pic_id'],
							'comment_user_id'		=> $row['comment_user_id'],
							'comment_username'		=> $row['comment_username'],
							'comment_user_ip'		=> $row['comment_user_ip'],
							'comment_time'			=> $row['comment_time'],
							'comment'				=> $row['comment_text'],
							'comment_uid'			=> $row['comment_text_bbcode_uid'],
							'comment_bitfield'		=> $row['comment_text_bbcode_bitfield'],
							'comment_options'		=> 7,
							'comment_edit_time'		=> (isset($row['comment_edit_time']) ? $row['comment_edit_time'] : 0),
							'comment_edit_count'	=> (isset($row['comment_edit_count']) ? $row['comment_edit_count'] : 0),
							'comment_edit_user_id'	=> (isset($row['comment_edit_user_id']) ? $row['comment_edit_user_id'] : 0),
						);
						generate_text_for_storage($comment_data['comment'], $comment_data['comment_uid'], $comment_data['comment_bitfield'], $comment_data['comment_options'], true, true, true);
						unset($comment_data['comment_options']);
						$db->sql_query('INSERT INTO ' . GALLERY_COMMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $comment_data));
					}
					$db->sql_freeresult($result);

					// fourth the configs...
					// because there might be some problems, we create them new...
					$sql_query = file_get_contents('schemas/_schema_data.sql');
					switch ($db->sql_layer)
					{
						case 'mssql':
						case 'mssql_odbc':
							$sql_query = preg_replace('#\# MSSQL IDENTITY (phpbb_[a-z_]+) (ON|OFF) \##s', 'SET IDENTITY_INSERT \1 \2;', $sql_query);
						break;
						case 'postgres':
							$sql_query = preg_replace('#\# POSTGRES (BEGIN|COMMIT) \##s', '\1; ', $sql_query);
						break;
					}
					$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);
					$sql_query = preg_replace('/\n{2,}/', "\n", preg_replace('/^#.*$/m', "\n", $sql_query));
					$sql_query = split_sql_file($sql_query, ';');
					foreach ($sql_query as $sql)
					{
						if (!$db->sql_query($sql))
						{
							$error = $db->sql_error();
							$this->p_master->db_error($error['message'], $sql, __LINE__, __FILE__);
						}
					}
					unset($sql_query);

					// last and least the images...
					$sql = 'SELECT *
						FROM ' . ALBUM_TABLE . '
						ORDER BY pic_id';
					$result = $db->sql_query($sql);
					while( $row = $db->sql_fetchrow($result) )
					{
						$image_data = array(
							'image_id'				=> $row['pic_id'],
							'image_filename'		=> $row['pic_filename'],
							'image_thumbnail'		=> $row['pic_thumbnail'],
							'image_name'			=> $row['pic_title'],
							'image_desc'			=> $row['pic_desc'],
							'image_desc_uid'		=> $row['pic_desc_bbcode_uid'],
							'image_desc_bitfield'	=> $row['pic_desc_bbcode_bitfield'],
							'image_desc_options'	=> 7,
							'image_user_id'			=> $row['pic_user_id'],
							'image_username'		=> $row['pic_username'],
							'image_user_ip'			=> $row['pic_user_ip'],
							'image_time'			=> $row['pic_time'],
							'image_album_id'		=> $row['pic_cat_id'],
							'image_view_count'		=> $row['pic_view_count'],
							'image_lock'			=> $row['pic_lock'],
							'image_approval'		=> $row['pic_approval'],
						);
						generate_text_for_storage($image_data['image_desc'], $image_data['image_desc_uid'], $image_data['image_desc_bitfield'], $image_data['image_desc_options'], true, true, true);
						unset($image_data['image_desc_options']);
						$db->sql_query('INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $image_data));
					}
					$db->sql_freeresult($result);

					$sql = 'SELECT module_id FROM ' . MODULES_TABLE . " WHERE module_langname = 'PHPBB_GALLERY' LIMIT 1";
					$result = $db->sql_query($sql);
					$acp_gallery = $db->sql_fetchrow($result);
					$album_personal_permissions = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_ALBUM_PERSONAL_PERMISSIONS',	'module_mode' => 'album_personal_permissions',	'module_auth' => '');
					add_module($album_personal_permissions);
				//no break;

				case '0.2.0':
				//no break;

				case '0.2.1':
				//no break;

				case '0.2.2':

					//add new config-values
					add_bbcode('album');

					//create some new columns

					// create the modules
					$sql = 'SELECT module_id FROM ' . MODULES_TABLE . " WHERE module_langname = 'PHPBB_GALLERY' LIMIT 1";
					$result = $db->sql_query($sql);
					$acp_gallery = $db->sql_fetchrow($result);
					$import_images = array('module_basename'	=> 'gallery',	'module_enabled'	=> 1,	'module_display'	=> 1,	'parent_id'			=> $acp_gallery['module_id'],	'module_class'		=> 'acp',	'module_langname'	=> 'ACP_IMPORT_ALBUMS',	'module_mode'		=> 'import_images',	'module_auth'		=> '');
					add_module($import_images);
				//no break;

				case '0.2.3':
					//add new config-values
					add_bbcode('album');
					gallery_config_value('preview_rsz_height', 600);
					gallery_config_value('preview_rsz_width', 800);
					gallery_config_value('upload_images', 10);
					gallery_config_value('thumbnail_info_line', 1);

					//create some new columns
					$phpbb_db_tools = new phpbb_db_tools($db);
					$phpbb_db_tools->sql_column_change(GALLERY_IMAGES_TABLE, 'image_username', array('VCHAR:255', ''));

					gallery_column(GALLERY_IMAGES_TABLE, 'image_user_colour', array('VCHAR:6', ''));
					gallery_column(GALLERY_ALBUMS_TABLE, 'album_user_id', array('UINT', 0));
					gallery_column(USERS_TABLE, 'album_id', array('UINT', 0));
					//REMOVE WITH 0.3.3//gallery_column(GROUPS_TABLE, 'allow_personal_albums', array('UINT', 1));
					//REMOVE WITH 0.3.3//gallery_column(GROUPS_TABLE, 'view_personal_albums', array('UINT', 1));
					//REMOVE WITH 0.3.3//gallery_column(GROUPS_TABLE, 'personal_subalbums', array('UINT', 10));

					//update the new columns image_username and image_user_colour
					$sql = 'SELECT i.image_user_id, i.image_id, u.username, u.user_colour
						FROM ' . GALLERY_IMAGES_TABLE . ' AS i
						LEFT JOIN ' . USERS_TABLE . " AS u
							ON i.image_user_id = u.user_id
						ORDER BY i.image_id DESC";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$image_id = $row['image_id'];

						if ($row['image_user_id'] == 1 || empty($row['username']))
						{
							continue;
						}

						$sql_ary = array(
							'image_username'      => $row['username'],
							'image_user_colour'      => $row['user_colour'],
						);

						$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE ' . $db->sql_in_set('image_id', $image_id);
						$db->sql_query($sql);
					}
					$db->sql_freeresult($result);

					//now create the new personal albums
					$sql = 'SELECT i.image_id, i.image_username, image_user_id
						FROM ' . GALLERY_IMAGES_TABLE . " AS i
						WHERE image_album_id = 0
						GROUP BY i.image_user_id DESC";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$album_data = array(
							'album_name'					=> $row['image_username'],
							'parent_id'						=> 0,
							//left_id and right_id are created some lines later
							'album_desc_options'			=> 7,
							'album_desc'					=> '',
							'album_parents'					=> '',
							'album_type'					=> 2,
							'album_user_id'					=> $row['image_user_id'],
						);
						$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));

						$sql2 = 'SELECT album_id FROM ' . GALLERY_ALBUMS_TABLE . ' WHERE parent_id = 0 AND album_user_id = ' . $row['image_user_id'] . ' LIMIT 1';
						$result2 = $db->sql_query($sql2);
						$row2 = $db->sql_fetchrow($result2);

						$sql3 = 'UPDATE ' . USERS_TABLE . ' 
								SET album_id = ' . (int) $row2['album_id'] . '
								WHERE user_id  = ' . (int) $row['image_user_id'];
						$db->sql_query($sql3);

						$sql3 = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
								SET image_album_id = ' . (int) $row2['album_id'] . '
								WHERE image_album_id = 0
									AND image_user_id  = ' . (int) $row['image_user_id'];
						$db->sql_query($sql3);
					}
					$db->sql_freeresult($result);

					$ucp_gallery = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => 163,	'module_class' => 'ucp',	'module_langname' => 'UCP_GALLERY_PERSONAL_ALBUMS',	'module_mode' => 'manage_albums',	'module_auth' => '');
					add_module($ucp_gallery);
				//no break;

				case '0.3.0':
				//no break;

				case '0.3.1':
					//we didn't add this to all updates, so we just do it again. the function saves to be double
					gallery_column(SESSIONS_TABLE, 'session_album_id', array('UINT', 0));

					gallery_create_table_slap_db_tools('phpbb_gallery_roles', true);
					gallery_create_table_slap_db_tools('phpbb_gallery_permissions', true);

					$album_config = load_album_config();

					//convert the permissions of personal albums to the new system
					//maybe we already got some permissions
					$roles_ary = array();
					$sql = 'SELECT *
						FROM ' . GALLERY_PERM_ROLES_TABLE;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$roles_ary[$row['i_view'] . '_' . $row['i_upload'] . '_' . $row['i_edit'] . '_' . $row['i_delete'] . '_' . $row['i_rate'] . '_' . $row['i_approve'] . '_' . $row['i_lock'] . '_' . $row['i_report'] . '_' . $row['i_count'] . '_' . $row['c_post'] . '_' . $row['c_edit'] . '_' . $row['c_delete'] . '_' . $row['a_moderate'] . '_' . $row['album_count']] = $row['role_id'];
					}
					$db->sql_freeresult($result);

					//personal gallery permissions
					gallery_create_table_slap_db_tools('phpbb_gallery_roles', true);
					gallery_create_table_slap_db_tools('phpbb_gallery_permissions', true);
					$sql = 'SELECT group_id, personal_subalbums, allow_personal_albums, view_personal_albums
						FROM ' . GROUPS_TABLE;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$sql_ary = array();
						$sql_ary = array(
							'i_view' => $row['view_personal_albums'],
							'i_upload' => $row['allow_personal_albums'],
							'i_edit' => $row['allow_personal_albums'],
							'i_delete' => $row['allow_personal_albums'],
							'i_rate' => $album_config['rate'],
							'i_approve' => 0,
							'i_lock' => 0,
							'i_report' => 1,
							'i_count' => $album_config['user_pics_limit'],
							'c_post' => $album_config['comment'],
							'c_edit' => $album_config['comment'],
							'c_delete' => $album_config['comment'],
							'a_moderate' => 1,
							'album_count' => $row['personal_subalbums'],
						);
						$sum_string = implode('_', $sql_ary);
						if (!isset($roles_ary[$sum_string]))
						{
								$db->sql_query('INSERT INTO ' . GALLERY_PERM_ROLES_TABLE . $db->sql_build_array('INSERT', $sql_ary));
								$data['role_id'] = $db->sql_nextid();
								$roles_ary[$sum_string] = $data['role_id'];
						}
						$sql_ary = array();
						$sql_ary = array(
							'perm_role_id'			=> $roles_ary[$sum_string],
							'perm_album_id'			=> 0,
							'perm_user_id'			=> 0,
							'perm_group_id'			=> $row['group_id'],
							'perm_system'			=> 2,
						);
						$db->sql_query('INSERT INTO ' . GALLERY_PERMISSIONS_TABLE . $db->sql_build_array('INSERT', $sql_ary));
						
						$sql_ary = array();
						$sql_ary = array(
							'i_view' => $row['view_personal_albums'],
							'i_upload' => 0,
							'i_edit' => 0,
							'i_delete' => 0,
							'i_rate' => $album_config['rate'],
							'i_approve' => 0,
							'i_lock' => 0,
							'i_report' => 1,
							'i_count' => $album_config['user_pics_limit'],
							'c_post' => $album_config['comment'],
							'c_edit' => $album_config['comment'],
							'c_delete' => $album_config['comment'],
							'a_moderate' => 0,
							'album_count' => $row['personal_subalbums'],
						);
						$sum_string = implode('_', $sql_ary);
						if (!isset($roles_ary[$sum_string]))
						{
								$db->sql_query('INSERT INTO ' . GALLERY_PERM_ROLES_TABLE . $db->sql_build_array('INSERT', $sql_ary));
								$data['role_id'] = $db->sql_nextid();
								$roles_ary[$sum_string] = $data['role_id'];
						}
						$sql_ary = array(
							'perm_role_id'			=> $roles_ary[$sum_string],
							'perm_album_id'			=> 0,
							'perm_user_id'			=> 0,
							'perm_group_id'			=> $row['group_id'],
							'perm_system'			=> 3,
						);
						$db->sql_query('INSERT INTO ' . GALLERY_PERMISSIONS_TABLE . $db->sql_build_array('INSERT', $sql_ary));
					}
					$db->sql_freeresult($result);

					//deactivate this useless module
					deactivate_module('ACP_GALLERY_ALBUM_PERSONAL_PERMISSIONS');
					gallery_config_value('fake_thumb_size', 141);
					gallery_config_value('disp_fake_thumb', 1);

				case '0.3.2':
					//and drop the old column
					delete_gallery_column(GROUPS_TABLE, 'personal_subalbums');
					delete_gallery_column(GROUPS_TABLE, 'allow_personal_albums');
					delete_gallery_column(GROUPS_TABLE, 'view_personal_albums');
				//no break;


				case 'svn':


				break;
			}


			// clear cache and log what we did
			$gd_check = function_exists('gd_info') ? gd_info() : array();;
			$gd_success = isset($gd_check['GD Version']);
			if (!$gd_success && ($album_config['gd_version'] > 0))
			{
				gallery_config_value('gd_version', 0, true);
				$album_config['gd_version'] = 0;
			}
			gallery_config_value('album_version', $new_mod_version, true);
			$cache->purge();
			add_log('admin', 'LOG_INSTALL_INSTALLED', $log_name);
			add_log('admin', 'LOG_PURGE_CACHE');
			$updated = true;
		}
	break;
	case 'convert':
		$converted = false;
		if ($convert == 1)
		{
			function decode_ip($int_ip)
			{
				$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
				return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
			}
			gallery_create_table_slap_db_tools('phpbb_gallery_albums', true);
			gallery_create_table_slap_db_tools('phpbb_gallery_comments', true);
			gallery_create_table_slap_db_tools('phpbb_gallery_config', true);
			gallery_create_table_slap_db_tools('phpbb_gallery_images', true);
			gallery_create_table_slap_db_tools('phpbb_gallery_rates', true);
			gallery_create_table_slap_db_tools('phpbb_gallery_roles', true);
			gallery_create_table_slap_db_tools('phpbb_gallery_permissions', true);

			// first lets make the albums...
			$personal_album = array();
			$left_id = 1;
			$sql = 'SELECT *
				FROM ' . $convert_prefix . 'album_cat
				ORDER BY cat_order';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				//we have to do this for the add on with the categories in the personal albums
				$row['cat_user_id'] = (isset($row['cat_user_id']) ? $row['cat_user_id'] : 0);
				if ($row['cat_user_id'] != 0)
				{
					$personal_album[] = $row['cat_id'];
				}
				else
				{
					$row['album_desc_uid'] = $row['album_desc_options'] = $row['album_desc_bitfield'] = '';
					$row['album_desc'] = $row['cat_desc'];
					$album_desc_data = generate_text_for_edit($row['album_desc'], $row['album_desc_uid'], $row['album_desc_options']);
					$album_data = array(
						'album_id'						=> $row['cat_id'],
						'album_name'					=> $row['cat_title'],
						'parent_id'						=> 0,
						'left_id'						=> $left_id,
						'right_id'						=> $left_id + 1,
						'album_parents'					=> '',
						'album_type'					=> 2,
						'album_desc'					=> $album_desc_data['text'],
						'album_desc_uid'				=> '',
						'album_desc_bitfield'			=> '',
						'album_desc_options'			=> 7,
						'album_view_level'				=> (($row['cat_view_level'] < 0 ) ? 1 : $row['cat_view_level']),
						'album_upload_level'			=> (($row['cat_upload_level'] < 0 ) ? 1 : $row['cat_upload_level']),
						'album_rate_level'				=> (($row['cat_rate_level'] < 0 ) ? 1 : $row['cat_rate_level']),
						'album_comment_level'			=> (($row['cat_comment_level'] < 0 ) ? 1 : $row['cat_comment_level']),
						'album_edit_level'				=> (($row['cat_edit_level'] < 0 ) ? 1 : $row['cat_edit_level']),
						'album_delete_level'			=> (($row['cat_delete_level'] < 0 ) ? 1 : $row['cat_delete_level']),
						'album_view_groups'				=> (isset($row['cat_view_groups']) ? $row['cat_view_groups'] : 0),
						'album_upload_groups'			=> (isset($row['cat_upload_groups']) ? $row['cat_upload_groups'] : 0),
						'album_rate_groups'				=> (isset($row['cat_rate_groups']) ? $row['cat_rate_groups'] : 0),
						'album_comment_groups'			=> (isset($row['cat_comment_groups']) ? $row['cat_comment_groups'] : 0),
						'album_edit_groups'				=> (isset($row['cat_edit_groups']) ? $row['cat_edit_groups'] : 0),
						'album_delete_groups'			=> (isset($row['cat_delete_groups']) ? $row['cat_delete_groups'] : 0),
						'album_moderator_groups'		=> (isset($row['cat_moderator_groups']) ? $row['cat_moderator_groups'] : 0),
						'album_approval'				=> $row['cat_approval'],
					);
					generate_text_for_storage($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options'], true, true, true);
					$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
					$left_id = $left_id + 2;
				}
			}
			$db->sql_freeresult($result);

			// second the rates...
			$sql = 'SELECT *
				FROM ' . $convert_prefix . 'album_rate
				ORDER BY rate_pic_id';
			$result = $db->sql_query($sql);
			while( $row = $db->sql_fetchrow($result) )
			{
				$rate_data = array(
					'rate_image_id'					=> $row['rate_pic_id'],
					'rate_user_id'					=> ($row['rate_user_id'] < 0) ? 1 : $row['rate_user_id'],
					'rate_user_ip'					=> decode_ip($row['rate_user_ip']),
					'rate_point'					=> $row['rate_point'],
				);
				$db->sql_query('INSERT INTO ' . GALLERY_RATES_TABLE . ' ' . $db->sql_build_array('INSERT', $rate_data));
			}
			$db->sql_freeresult($result);

			// third the comments...
			$sql = 'SELECT *
				FROM ' . $convert_prefix . 'album_comment
				ORDER BY comment_id';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$row['comment_uid'] = $row['comment_options'] = $row['comment_bitfield'] = '';
				$row['comment'] = $row['comment_text'];
				$comment_text_data = generate_text_for_edit($row['comment'], $row['comment_uid'], $row['comment_options']);
				$comment_data = array(
					'comment_id'			=> $row['comment_id'],
					'comment_image_id'		=> $row['comment_pic_id'],
					'comment_user_id'		=> ($row['comment_user_id'] < 0) ? 1 : $row['comment_user_id'],
					'comment_username'		=> $row['comment_username'],
					'comment_user_ip'		=> decode_ip($row['comment_user_ip']),
					'comment_time'			=> $row['comment_time'],
					'comment'				=> $comment_text_data['text'],
					'comment_uid'			=> '',
					'comment_bitfield'		=> '',
					'comment_options'		=> 7,
					'comment_edit_time'		=> (isset($row['comment_edit_time']) ? $row['comment_edit_time'] : 0),
					'comment_edit_count'	=> (isset($row['comment_edit_count']) ? $row['comment_edit_count'] : 0),
					'comment_edit_user_id'	=> (isset($row['comment_edit_user_id']) ? ($row['comment_edit_user_id'] < 0) ? 1 : $row['comment_edit_user_id'] : 0),
				);
				generate_text_for_storage($comment_data['comment'], $comment_data['comment_uid'], $comment_data['comment_bitfield'], $comment_data['comment_options'], 1, 1, 1);
				unset($comment_data['comment_options']);
				$db->sql_query('INSERT INTO ' . GALLERY_COMMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $comment_data));
			}
			$db->sql_freeresult($result);

			// fourth the configs...
			// because there might be some problems, we create them new...
			gallery_config_value('max_pics', '1024');
			gallery_config_value('user_pics_limit', '50');
			gallery_config_value('mod_pics_limit', '250');
			gallery_config_value('max_file_size', '128000');
			gallery_config_value('max_width', '800');
			gallery_config_value('max_height', '600');
			gallery_config_value('rows_per_page', '3');
			gallery_config_value('cols_per_page', '4');
			gallery_config_value('fullpic_popup', '0');
			gallery_config_value('thumbnail_quality', '50');
			gallery_config_value('thumbnail_size', '125');
			gallery_config_value('thumbnail_cache', '1');
			gallery_config_value('sort_method', 'image_time');
			gallery_config_value('sort_order', 'DESC');
			gallery_config_value('jpg_allowed', '1');
			gallery_config_value('png_allowed', '1');
			gallery_config_value('gif_allowed', '0');
			gallery_config_value('desc_length', '512');
			gallery_config_value('hotlink_prevent', '0');
			gallery_config_value('hotlink_allowed', 'flying-bits.org');
			gallery_config_value('personal_gallery', '0');
			gallery_config_value('personal_gallery_private', '0');
			gallery_config_value('personal_gallery_limit', '10');
			gallery_config_value('personal_gallery_view', '1');
			gallery_config_value('rate', '1');
			gallery_config_value('rate_scale', '10');
			gallery_config_value('comment', '1');
			gallery_config_value('gd_version', '2');
			gallery_config_value('album_version', '0.2.4');
			gallery_config_value('watermark_images', 1);
			gallery_config_value('watermark_source', 'gallery/mark.png');
			gallery_config_value('preview_rsz_height', 600);
			gallery_config_value('preview_rsz_width', 800);
			gallery_config_value('upload_images', 10);
			gallery_config_value('thumbnail_info_line', 1);
			gallery_config_value('fake_thumb_size', 141);
			gallery_config_value('disp_fake_thumb', 1);

			// last and least the images...
			$sql = 'SELECT i.*, u.user_colour, u.username
				FROM ' . $convert_prefix . 'album AS i
				LEFT JOIN ' . USERS_TABLE . ' AS u
					ON i.pic_user_id = u.user_id
				ORDER BY i.pic_id';
			$result = $db->sql_query($sql);
			while( $row = $db->sql_fetchrow($result) )
			{
				$row['image_desc_uid'] = $row['image_desc_options'] = $row['image_desc_bitfield'] = '';
				$row['image_desc'] = $row['pic_desc'];
				$image_desc_data = generate_text_for_edit($row['image_desc'], $row['image_desc_uid'], $row['image_desc_options']);
				$image_data = array(
					'image_id'				=> $row['pic_id'],
					'image_filename'		=> $row['pic_filename'],
					'image_thumbnail'		=> $row['pic_thumbnail'],
					'image_name'			=> $row['pic_title'],
					'image_desc'			=> $image_desc_data['text'],
					'image_desc_uid'		=> '',
					'image_desc_bitfield'	=> '',
					'image_desc_options'	=> 7,
					'image_user_id'			=> ($row['pic_user_id'] < 0) ? 1 : $row['pic_user_id'],
					'image_username'		=> (isset($row['username'])) ? $row['username'] : $row['pic_username'],
					'image_user_colour'		=> (isset($row['user_colour'])) ? $row['user_colour'] : '',
					'image_user_ip'			=> decode_ip($row['pic_user_ip']),
					'image_time'			=> $row['pic_time'],
					'image_album_id'		=> (in_array($row['pic_cat_id'], $personal_album) ? 0 : $row['pic_cat_id']),
					'image_view_count'		=> $row['pic_view_count'],
					'image_lock'			=> $row['pic_lock'],
					'image_approval'		=> $row['pic_approval'],
				);
				generate_text_for_storage($image_data['image_desc'], $image_data['image_desc_uid'], $image_data['image_desc_bitfield'], $image_data['image_desc_options'], true, true, true);
				unset($image_data['image_desc_options']);
				$db->sql_query('INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $image_data));
			}
			$db->sql_freeresult($result);

			gallery_column(USERS_TABLE, 'album_id', array('UINT', 0));
			//REMOVE WITH 0.3.3//gallery_column(GROUPS_TABLE, 'allow_personal_albums', array('UINT', 1));
			//REMOVE WITH 0.3.3//gallery_column(GROUPS_TABLE, 'view_personal_albums', array('UINT', 1));
			//REMOVE WITH 0.3.3//gallery_column(GROUPS_TABLE, 'personal_subalbums', array('UINT', 10));
			gallery_column(SESSIONS_TABLE, 'session_album_id', array('UINT', 0));

			//now create the new personal albums
			$sql = 'SELECT i.image_id, i.image_username, image_user_id
				FROM ' . GALLERY_IMAGES_TABLE . " AS i
				WHERE image_album_id = 0
				GROUP BY i.image_user_id DESC";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$album_data = array(
					'album_name'					=> $row['image_username'],
					'parent_id'						=> 0,
					//left_id and right_id are created some lines later
					'album_desc_options'			=> 7,
					'album_desc'					=> '',
					'album_parents'					=> '',
					'album_type'					=> 2,
					'album_user_id'					=> ($row['image_user_id'] < 0) ? 1 : $row['image_user_id'],
				);
				$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));

				$sql2 = 'SELECT album_id FROM ' . GALLERY_ALBUMS_TABLE . ' WHERE parent_id = 0 AND album_user_id = ' . $row['image_user_id'] . ' LIMIT 1';
				$result2 = $db->sql_query($sql2);
				$row2 = $db->sql_fetchrow($result2);

				$sql3 = 'UPDATE ' . USERS_TABLE . ' 
						SET album_id = ' . (int) $row2['album_id'] . '
						WHERE user_id  = ' . (int) $row['image_user_id'];
				$db->sql_query($sql3);

				$sql3 = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
						SET image_album_id = ' . (int) $row2['album_id'] . '
						WHERE image_album_id = 0
							AND image_user_id  = ' . (int) $row['image_user_id'];
				$db->sql_query($sql3);
			}
			$db->sql_freeresult($result);

			// create the modules
			$acp_gallery = array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => 31,	'module_class' => 'acp',	'module_langname'=> 'PHPBB_GALLERY',	'module_mode' => '',	'module_auth' => '');
			add_module($acp_gallery);
			$sql = 'SELECT module_id
				FROM ' . MODULES_TABLE . "
				WHERE module_langname = 'PHPBB_GALLERY'
				LIMIT 1";
			$result = $db->sql_query($sql);
			while( $row = $db->sql_fetchrow($result) )
			{
				$acp_gallery['module_id'] = $row['module_id'];
			}
			$db->sql_freeresult($result);
			$acp_gallery_overview = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_OVERVIEW',	'module_mode' => 'overview',	'module_auth' => '');
			add_module($acp_gallery_overview);
			$acp_gallery_manage_albums = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_MANAGE_ALBUMS',	'module_mode' => 'manage_albums',	'module_auth' => '');
			add_module($acp_gallery_manage_albums);
			$acp_gallery_manage_cache = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname' => 'ACP_GALLERY_MANAGE_CACHE',	'module_mode' => 'manage_cache',	'module_auth' => '');
			add_module($acp_gallery_manage_cache);
			$acp_configure_gallery = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_CONFIGURE_GALLERY',	'module_mode' => 'configure_gallery',	'module_auth' => '');
			add_module($acp_configure_gallery);
			$album_permissions = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_ALBUM_PERMISSIONS',	'module_mode' => 'album_permissions',	'module_auth' => '');
			add_module($album_permissions);
			//REMOVE WITH 0.3.3//$album_personal_permissions = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname'=> 'ACP_GALLERY_ALBUM_PERSONAL_PERMISSIONS',	'module_mode' => 'album_personal_permissions',	'module_auth' => '');
			//REMOVE WITH 0.3.3//add_module($album_personal_permissions);
			$import_images = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $acp_gallery['module_id'],	'module_class' => 'acp',	'module_langname'=> 'ACP_IMPORT_ALBUMS',	'module_mode' => 'import_images',	'module_auth' => '');
			add_module($import_images);
			$ucp_gallery = array('module_basename' => 'gallery',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => 163,	'module_class' => 'ucp',	'module_langname' => 'UCP_GALLERY_PERSONAL_ALBUMS',	'module_mode' => 'manage_albums',	'module_auth' => '');
			add_module($ucp_gallery);

			$gd_check = function_exists('gd_info') ? gd_info() : array();
			$gd_success = isset($gd_check['GD Version']);
			if (!$gd_success && ($album_config['gd_version'] > 0))
			{
				$sql = 'UPDATE ' . GALLERY_CONFIG_TABLE . "SET config_value = 0 WHERE config_name = 'gd_version'";
				$result = $db->sql_query($sql);
				$album_config['gd_version'] = 0;
			}

			add_bbcode('album');
			gallery_config_value('album_version', $new_mod_version, true);

			// clear cache and log what we did
			$cache->purge();
			add_log('admin', 'LOG_INSTALL_INSTALLED', $log_name);
			add_log('admin', 'LOG_PURGE_CACHE');
			$converted = true;
		}
	break;
	case 'delete':
		$deleted = false;
		if ($delete == 1)
		{
			drop_dbs();
			$bbcode_link1 = '<a href="' . $config['server_protocol'] . $config['server_name'] . $config['script_path'] . '/gallery/image_page.php?image_id=${1}"><img src="' . $config['server_protocol'] . $config['server_name'] . $config['script_path'] . '/gallery/thumbnail.php?image_id=${1}" /></a>';
			$bbcode_link2 = '<a href="gallery/image_page.php?id=${1}"><img src="gallery/thumbnail.php?pic_id=${1}" /></a>';
			$sql = 'DELETE FROM ' . BBCODES_TABLE . " WHERE second_pass_replace = '$bbcode_link1' or second_pass_replace = '$bbcode_link2';";
			$db->sql_query($sql);
			$acp_gallery_module = '';
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'gallery'
					AND module_class = 'acp'";
			$result = $db->sql_query($sql);
			while( $row = $db->sql_fetchrow($result) )
			{
				$acp_gallery_module .= (($acp_gallery_module) ? ', ' : '') . $row['parent_id'];
			}
			$sql = 'DELETE FROM ' . MODULES_TABLE . " WHERE module_basename = 'gallery' or module_id IN ($acp_gallery_module);";
			$db->sql_query($sql);

			delete_gallery_column(USERS_TABLE, 'album_id');
			delete_gallery_column(GROUPS_TABLE, 'allow_personal_albums');
			delete_gallery_column(GROUPS_TABLE, 'view_personal_albums');
			delete_gallery_column(GROUPS_TABLE, 'personal_subalbums');
			delete_gallery_column(SESSIONS_TABLE, 'session_album_id');
			$cache->purge();
			$deleted = true;
		}
	break;
	default:
		//we had a little cheater
	break;
}

include($phpbb_root_path . 'install_gallery/layout.'.$phpEx);
?>