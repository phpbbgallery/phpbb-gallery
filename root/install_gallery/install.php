<?php
/** 
*
* \install_f1\install.php
*
* @package
* @version $Id$
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();
$user->add_lang('mods/gallery');
$old_gallery_version = '0.1.2';
$new_gallery_version = '0.1.3';

if ($user->data['user_type'] != USER_FOUNDER)
{
	$message = $user->lang['GALLERY_INSTALL_NOTE3'];
	trigger_error($message);
}

$submit = request_var('mode', '');

/**
* split_sql_file will split an uploaded sql file into single sql statements.
* Note: expects trim() to have already been run on $sql.
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

if ($submit == 'install') 
{
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
	
	// Drop the bug_status  table if existing
	if ($db->sql_layer != 'mssql')
	{
		$sql = 'DROP TABLE IF EXISTS '.$table_prefix.'album';
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
		$sql = 'DROP TABLE IF EXISTS '.$table_prefix.'album_rate';
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
		$sql = 'DROP TABLE IF EXISTS '.$table_prefix.'album_comment';
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
		$sql = 'DROP TABLE IF EXISTS '.$table_prefix.'album_cat';
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
		$sql = 'DROP TABLE IF EXISTS '.$table_prefix.'album_config';
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
	}
	else
	{
		$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'album
		drop table ' . $table_prefix . 'album';
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
		$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'album_rate
		drop table ' . $table_prefix . 'album_rate';
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
		$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'album_comment
		drop table ' . $table_prefix . 'album_comment';
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
		$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'album_cat
		drop table ' . $table_prefix . 'album_cat';
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
		$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . 'album_config
		drop table ' . $table_prefix . 'album_config';
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
	}

	// locate the schema files
	$dbms_schema = 'install_schemas/_' . $db_schema . '_schema.sql';

	$sql_query = @file_get_contents($dbms_schema);


	$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

	$sql_query = preg_replace('/\n{2,}/', "\n", preg_replace('/^#.*$/m', "\n", $sql_query));

	$sql_query = split_sql_file($sql_query, $delimiter);

	foreach ($sql_query as $sql)
	{
		if (!$db->sql_query($sql))
		{
			$error = $db->sql_error();
			$this->p_master->db_error($error['message'], $sql, __LINE__, __FILE__);
		}
	}
	unset($sql_query);

	$sql_query = file_get_contents('install_schemas/_schema_data.sql');

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

	$cache->purge();
	add_log('admin', 'phpBB Gallery v' . $new_gallery_version . ' installed');
	add_log('admin', 'LOG_PURGE_CACHE');

	$message = $user->lang['GALLERY_INSTALL_NOTE2'];
	trigger_error($message);
} 
else if ($submit == 'update') 
{
/* the mod was just available for mysql, so we don't need this yet.
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

	// locate the schema files
	$dbms_schema = 'update_schemas/_' . $db_schema . '_schema.sql';

	$sql_query = @file_get_contents($dbms_schema);


	$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

	$sql_query = preg_replace('/\n{2,}/', "\n", preg_replace('/^#.*$/m', "\n", $sql_query));

	$sql_query = split_sql_file($sql_query, $delimiter);

	foreach ($sql_query as $sql)
	{
		if (!$db->sql_query($sql))
		{
			$error = $db->sql_error();
			$this->p_master->db_error($error['message'], $sql, __LINE__, __FILE__);
		}
	}
	unset($sql_query);
*/

	$sql_query = file_get_contents('update_schemas/_schema_data.sql');

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
	$sql = 'ALTER TABLE `phpbb_album` ADD `pic_desc_bbcode_bitfield` varchar(255) AFTER pic_desc;';
	$db->sql_query($sql);
	$sql = 'ALTER TABLE `phpbb_album` ADD `pic_desc_bbcode_uid` varchar(8) AFTER pic_desc;';
	$db->sql_query($sql);
	$sql = 'ALTER TABLE `phpbb_album_cat` ADD `cat_desc_bbcode_bitfield` varchar(255) AFTER catc_desc;';
	$db->sql_query($sql);
	$sql = 'ALTER TABLE `phpbb_album_cat` ADD `cat_desc_bbcode_uid` varchar(8) AFTER cat_desc;';
	$db->sql_query($sql);
	$sql = 'ALTER TABLE `phpbb_album_comment` ADD `comment_text_bbcode_bitfield` varchar(255) AFTER comment_text;';
	$db->sql_query($sql);
	$sql = 'ALTER TABLE `phpbb_album_comment` ADD `comment_text_bbcode_uid` varchar(8) AFTER comment_text;';
	$db->sql_query($sql);
	$cache->purge();
	add_log('admin', 'phpBB Gallery v' . $old_gallery_version . ' updated to v' . $new_gallery_version);
	add_log('admin', 'LOG_PURGE_CACHE');

	$message = $user->lang['GALLERY_INSTALL_NOTE2'];
	trigger_error($message);
} 
else 
{
	$message = $user->lang['GALLERY_INSTALL_NOTE1'] . '<br />';
	$message .= '<br />&raquo; <a href="'.append_sid("install.$phpEx?mode=install").'" class="gen">' . sprintf($user->lang['GALLERY_INSTALLATION'], $new_gallery_version) . '</a>';
	$message .= '<br />&raquo; <a href="'.append_sid("install.$phpEx?mode=update").'" class="gen">' . sprintf($user->lang['GALLERY_UPDATE'], $old_gallery_version, $new_gallery_version) . '</a>';
	$message .= '<br />&raquo; <a href="'.append_sid( $phpbb_root_path . "index.$phpEx").'" class="gen">' . $user->lang['CANCEL'] . '</a>';
	trigger_error( $message);
}
?>