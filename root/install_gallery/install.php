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
$new_mod_version = '0.2.4';
$page_title = 'phpBB Gallery v' . $new_mod_version;

$mode = request_var('mode', '', true);
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
switch ($mode)
{
	case 'install':
		$install = request_var('install', 0);
		$installed = false;
		if ($install == 1)
		{
			// Drop thes tables if existing
			if ($db->sql_layer != 'mssql')
			{
				$sql = 'DROP TABLE IF EXISTS ' . $table_prefix . 'gallery_albums';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'DROP TABLE IF EXISTS ' . $table_prefix . 'gallery_comments';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'DROP TABLE IF EXISTS ' . $table_prefix . 'gallery_config';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'DROP TABLE IF EXISTS ' . $table_prefix . 'gallery_images';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'DROP TABLE IF EXISTS ' . $table_prefix . 'gallery_rates';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
			}
			else
			{
				$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'gallery_albums)
				drop table ' . $table_prefix . 'gallery_albums';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'gallery_comments)
				drop table ' . $table_prefix . 'gallery_comments';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'gallery_config)
				drop table ' . $table_prefix . 'gallery_config';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'gallery_images)
				drop table ' . $table_prefix . 'gallery_images';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'gallery_rates)
				drop table ' . $table_prefix . 'gallery_rates';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
			}
			// locate the schema files
			$dbms_schema = 'schemas/_' . $db_schema . '_schema.sql';
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

			//fill the GALLERY_CONFIG_TABLE with some values
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

			// create the acp modules
			$modules = new acp_modules();
			$acp_gallery = array(
				'module_basename'	=> '',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> 31,
				'module_class'		=> 'acp',
				'module_langname'	=> 'PHPBB_GALLERY',
				'module_mode'		=> '',
				'module_auth'		=> ''
			);
			$modules->update_module_data($acp_gallery);
			$acp_gallery_overview = array(
				'module_basename'	=> 'gallery',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $acp_gallery['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'ACP_GALLERY_OVERVIEW',
				'module_mode'		=> 'overview',
				'module_auth'		=> ''
			);
			$modules->update_module_data($acp_gallery_overview);
			$acp_gallery_manage_albums = array(
				'module_basename'	=> 'gallery',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $acp_gallery['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'ACP_GALLERY_MANAGE_ALBUMS',
				'module_mode'		=> 'manage_albums',
				'module_auth'		=> ''
			);
			$modules->update_module_data($acp_gallery_manage_albums);
			$acp_gallery_manage_cache = array(
				'module_basename'	=> 'gallery',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $acp_gallery['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'ACP_GALLERY_MANAGE_CACHE',
				'module_mode'		=> 'manage_cache',
				'module_auth'		=> ''
			);
			$modules->update_module_data($acp_gallery_manage_cache);
			$acp_configure_gallery = array(
				'module_basename'	=> 'gallery',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $acp_gallery['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'ACP_GALLERY_CONFIGURE_GALLERY',
				'module_mode'		=> 'configure_gallery',
				'module_auth'		=> ''
			);
			$modules->update_module_data($acp_configure_gallery);
			$album_permissions = array(
				'module_basename'	=> 'gallery',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $acp_gallery['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'ACP_GALLERY_ALBUM_PERMISSIONS',
				'module_mode'		=> 'album_permissions',
				'module_auth'		=> ''
			);
			$modules->update_module_data($album_permissions);
			$album_personal_permissions = array(
				'module_basename'	=> 'gallery',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $acp_gallery['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'ACP_GALLERY_ALBUM_PERSONAL_PERMISSIONS',
				'module_mode'		=> 'album_personal_permissions',
				'module_auth'		=> ''
			);
			$modules->update_module_data($album_personal_permissions);
			$import_images = array(
				'module_basename'	=> 'gallery',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $acp_gallery['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'ACP_IMPORT_ALBUMS',
				'module_mode'		=> 'import_images',
				'module_auth'		=> ''
			);
			$modules->update_module_data($import_images);
			$gd_check = function_exists('gd_info') ? gd_info() : array();
			$gd_success = isset($gd_check['GD Version']);
			if (!$gd_success && ($album_config['gd_version'] > 0))
			{
				$sql = 'UPDATE ' . GALLERY_CONFIG_TABLE . "SET config_value = 0 WHERE config_name = 'gd_version'";
				$result = $db->sql_query($sql);
				$album_config['gd_version'] = 0;
			}
			//add the bbcode
			$sql = 'SELECT * FROM ' . BBCODES_TABLE . " WHERE bbcode_tag = 'album'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				$album_bbcode = 'album';
				$sql_ary = array(
					'bbcode_tag'				=> $album_bbcode,
					'bbcode_match'				=> '[' . $album_bbcode . ']{NUMBER}[/' . $album_bbcode . ']',
					'bbcode_tpl'				=> '<a href="gallery/image_page.php?id={NUMBER}"><img src="gallery/thumbnail.php?pic_id={NUMBER}" /></a>',
					'display_on_posting'		=> true,
					'bbcode_helpline'			=> '',
					'first_pass_match'			=> '!\[' . $album_bbcode . '\]([0-9]+)\[/' . $album_bbcode . '\]!i',
					'first_pass_replace'		=> '[' . $album_bbcode . ':$uid]${1}[/' . $album_bbcode . ':$uid]',
					'second_pass_match'			=> '!\[' . $album_bbcode . ':$uid\]([0-9]+)\[/' . $album_bbcode . ':$uid\]!s',
					'second_pass_replace'		=> '<a href="gallery/image_page.php?id=${1}"><img src="gallery/thumbnail.php?pic_id=${1}" /></a>',
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
			// clear cache and log what we did
			$cache->purge();
			add_log('admin', 'phpBB Gallery v' . $new_mod_version . ' installed');
			add_log('admin', 'LOG_PURGE_CACHE');
			$installed = true;
		}
	break;
	case 'update023':
		$update = request_var('update', 0);
		$version = request_var('v', '0', true);
		$updated = false;
		if ($update == 1)
		{
			$sql = 'SELECT * FROM ' . GALLERY_CONFIG_TABLE . " WHERE config_name = 'preview_rsz_width'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			if (!$row)
			{
				$sql_ary = array(
					'config_name'				=> 'preview_rsz_width',
					'config_value'				=> 800,
				);
				$db->sql_query('INSERT INTO ' . GALLERY_CONFIG_TABLE . $db->sql_build_array('INSERT', $sql_ary));
			}

			$sql = 'SELECT * FROM ' . GALLERY_CONFIG_TABLE . " WHERE config_name = 'preview_rsz_height'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			if (!$row)
			{
				$sql_ary = array(
					'config_name'				=> 'preview_rsz_height',
					'config_value'				=> 600,
				);
				$db->sql_query('INSERT INTO ' . GALLERY_CONFIG_TABLE . $db->sql_build_array('INSERT', $sql_ary));
			}

			$column_add = new phpbb_db_tools($db);
			$column_add->sql_column_add(GALLERY_IMAGES_TABLE, 'image_user_colour', array('VCHAR:6', ''));
			$column_change = new phpbb_db_tools($db);
			$column_change->sql_column_change(GALLERY_IMAGES_TABLE, 'image_username', array('VCHAR:255', ''));

			$sql = 'SELECT i.image_user_id, i.image_id, u.username, u.user_colour
				FROM ' . GALLERY_IMAGES_TABLE . ' AS i
				LEFT JOIN ' . USERS_TABLE . " AS u
					ON i.image_user_id = u.user_id
				ORDER BY i.image_id DESC";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$image_id = $row['image_id'];
				if ($row['image_user_id'] != 1)
				{
					$sql_ary = array(
						'image_username'		=> $row['username'],
						'image_user_colour'		=> $row['user_colour'],
					);
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE ' . $db->sql_in_set('image_id', $image_id);
					$db->sql_query($sql);
				}
			}
			$db->sql_freeresult($result);

			// clear cache and log what we did
			$cache->purge();
			add_log('admin', 'phpBB Gallery updated to v' . $new_mod_version);
			add_log('admin', 'LOG_PURGE_CACHE');
			$updated = true;
		}
	break;
	case 'convert':
		$convert = request_var('convert', 0);
		$convert_prefix = request_var('convert_prefix', '', true);
		$converted = false;
		if ($convert == 1)
		{
			function decode_ip($int_ip)
			{
				$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
				return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
			}
			// Drop thes tables if existing
			if ($db->sql_layer != 'mssql')
			{
				$sql = 'DROP TABLE IF EXISTS ' . $table_prefix . 'gallery_albums';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'DROP TABLE IF EXISTS ' . $table_prefix . 'gallery_comments';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'DROP TABLE IF EXISTS ' . $table_prefix . 'gallery_config';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'DROP TABLE IF EXISTS ' . $table_prefix . 'gallery_images';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'DROP TABLE IF EXISTS ' . $table_prefix . 'gallery_rates';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
			}
			else
			{
				$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'gallery_albums)
				drop table ' . $table_prefix . 'gallery_albums';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'gallery_comments)
				drop table ' . $table_prefix . 'gallery_comments';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'gallery_config)
				drop table ' . $table_prefix . 'gallery_config';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'gallery_images)
				drop table ' . $table_prefix . 'gallery_images';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'gallery_rates)
				drop table ' . $table_prefix . 'gallery_rates';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
			}

			// locate the schema files
			$dbms_schema = 'schemas/_' . $db_schema . '_schema.sql';
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
					'rate_user_id'					=> $row['rate_user_id'],
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
					'comment_user_id'		=> $row['comment_user_id'],
					'comment_username'		=> $row['comment_username'],
					'comment_user_ip'		=> decode_ip($row['comment_user_ip']),
					'comment_time'			=> $row['comment_time'],
					'comment'				=> $comment_text_data['text'],
					'comment_uid'			=> '',
					'comment_bitfield'		=> '',
					'comment_options'		=> 7,
					'comment_edit_time'		=> (isset($row['comment_edit_time']) ? $row['comment_edit_time'] : 0),
					'comment_edit_count'	=> (isset($row['comment_edit_count']) ? $row['comment_edit_count'] : 0),
					'comment_edit_user_id'	=> (isset($row['comment_edit_user_id']) ? $row['comment_edit_user_id'] : 0),
				);
				generate_text_for_storage($comment_data['comment'], $comment_data['comment_uid'], $comment_data['comment_bitfield'], $comment_data['comment_options'], 1, 1, 1);
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
			$sql = 'SELECT i.*, u.user_colour
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
					'image_user_id'			=> $row['pic_user_id'],
					'image_username'		=> $row['pic_username'],
					'image_user_colour'		=> $row['user_colour'],
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

			// Drop the old tables
			if ($db->sql_layer != 'mssql')
			{
				$sql = 'DROP TABLE IF EXISTS ' . $convert_prefix . 'album';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'DROP TABLE IF EXISTS ' . $convert_prefix . 'album_rate';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'DROP TABLE IF EXISTS ' . $convert_prefix . 'album_comment';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'DROP TABLE IF EXISTS ' . $convert_prefix . 'album_cat';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'DROP TABLE IF EXISTS ' . $convert_prefix . 'album_config';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
			}
			else
			{
				$sql = 'if exists (select * from sysobjects where name = ' . $convert_prefix . 'album)
				drop table ' . $convert_prefix . 'album';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'if exists (select * from sysobjects where name = ' . $convert_prefix . 'album_rate)
				drop table ' . $convert_prefix . 'album_rate';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'if exists (select * from sysobjects where name = ' . $convert_prefix . 'album_comment)
				drop table ' . $convert_prefix . 'album_comment';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'if exists (select * from sysobjects where name = ' . $convert_prefix . 'album_cat)
				drop table ' . $convert_prefix . 'album_cat';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				$sql = 'if exists (select * from sysobjects where name = ' . $convert_prefix . 'album_config)
				drop table ' . $convert_prefix . 'album_config';
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
			}

			// create the acp modules
			$modules = new acp_modules();
			$acp_gallery = array(
				'module_basename'	=> '',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> 31,
				'module_class'		=> 'acp',
				'module_langname'	=> 'PHPBB_GALLERY',
				'module_mode'		=> '',
				'module_auth'		=> ''
			);
			$modules->update_module_data($acp_gallery);
			$acp_gallery_overview = array(
				'module_basename'	=> 'gallery',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $acp_gallery['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'ACP_GALLERY_OVERVIEW',
				'module_mode'		=> 'overview',
				'module_auth'		=> ''
			);
			$modules->update_module_data($acp_gallery_overview);
			$acp_gallery_manage_albums = array(
				'module_basename'	=> 'gallery',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $acp_gallery['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'ACP_GALLERY_MANAGE_ALBUMS',
				'module_mode'		=> 'manage_albums',
				'module_auth'		=> ''
			);
			$modules->update_module_data($acp_gallery_manage_albums);
			$acp_gallery_manage_cache = array(
				'module_basename'	=> 'gallery',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $acp_gallery['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'ACP_GALLERY_MANAGE_CACHE',
				'module_mode'		=> 'manage_cache',
				'module_auth'		=> ''
			);
			$modules->update_module_data($acp_gallery_manage_cache);
			$acp_configure_gallery = array(
				'module_basename'	=> 'gallery',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $acp_gallery['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'ACP_GALLERY_CONFIGURE_GALLERY',
				'module_mode'		=> 'configure_gallery',
				'module_auth'		=> ''
			);
			$modules->update_module_data($acp_configure_gallery);
			$album_permissions = array(
				'module_basename'	=> 'gallery',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $acp_gallery['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'ACP_GALLERY_ALBUM_PERMISSIONS',
				'module_mode'		=> 'album_permissions',
				'module_auth'		=> ''
			);
			$modules->update_module_data($album_permissions);
			$album_personal_permissions = array(
				'module_basename'	=> 'gallery',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $acp_gallery['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'ACP_GALLERY_ALBUM_PERSONAL_PERMISSIONS',
				'module_mode'		=> 'album_personal_permissions',
				'module_auth'		=> ''
			);
			$modules->update_module_data($album_personal_permissions);
			$import_images = array(
				'module_basename'	=> 'gallery',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $acp_gallery['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'ACP_IMPORT_ALBUMS',
				'module_mode'		=> 'import_images',
				'module_auth'		=> ''
			);
			$modules->update_module_data($import_images);

			$gd_check = function_exists('gd_info') ? gd_info() : array();
			$gd_success = isset($gd_check['GD Version']);
			if (!$gd_success && ($album_config['gd_version'] > 0))
			{
				$sql = 'UPDATE ' . GALLERY_CONFIG_TABLE . "SET config_value = 0 WHERE config_name = 'gd_version'";
				$result = $db->sql_query($sql);
				$album_config['gd_version'] = 0;
			}
			//add the bbcode
			$sql = 'SELECT * FROM ' . BBCODES_TABLE . " WHERE bbcode_tag = 'album'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				$album_bbcode = 'album';
				$sql_ary = array(
					'bbcode_tag'				=> $album_bbcode,
					'bbcode_match'				=> '[' . $album_bbcode . ']{NUMBER}[/' . $album_bbcode . ']',
					'bbcode_tpl'				=> '<a href="gallery/image_page.php?id={NUMBER}"><img src="gallery/thumbnail.php?pic_id={NUMBER}" /></a>',
					'display_on_posting'		=> true,
					'bbcode_helpline'			=> '',
					'first_pass_match'			=> '!\[' . $album_bbcode . '\]([0-9]+)\[/' . $album_bbcode . '\]!i',
					'first_pass_replace'		=> '[' . $album_bbcode . ':$uid]${1}[/' . $album_bbcode . ':$uid]',
					'second_pass_match'			=> '!\[' . $album_bbcode . ':$uid\]([0-9]+)\[/' . $album_bbcode . ':$uid\]!s',
					'second_pass_replace'		=> '<a href="gallery/image_page.php?id=${1}"><img src="gallery/thumbnail.php?pic_id=${1}" /></a>',
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

			// clear cache and log what we did
			$cache->purge();
			add_log('admin', 'phpBB Gallery converted to v' . $new_mod_version);
			add_log('admin', 'LOG_PURGE_CACHE');
			$converted = true;
		}
	break;
	default:
		//we had a little cheater
	break;
}

include($phpbb_root_path . 'install_gallery/layout.'.$phpEx);
?>