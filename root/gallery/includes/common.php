<?php

/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
}

// Is board disabled and user not an admin or moderator?
if ($config['board_disable'] && !defined('IN_LOGIN') && !$auth->acl_gets('a_', 'm_') && !$auth->acl_getf_global('m_'))
{
	header('HTTP/1.1 503 Service Unavailable');

	$message = (!empty($config['board_disable_msg'])) ? $config['board_disable_msg'] : 'BOARD_DISABLE';
	trigger_error($message);
}
$gallery_root_path = GALLERY_ROOT_PATH;
include("{$phpbb_root_path}{$gallery_root_path}includes/constants.$phpEx");

//
// Get Album Config
//
$sql = 'SELECT *
	FROM ' . GALLERY_CONFIG_TABLE;
$result = $db->sql_query($sql);

while( $row = $db->sql_fetchrow($result) )
{
	$album_config_name = $row['config_name'];
	$album_config_value = $row['config_value'];
	$album_config[$album_config_name] = $album_config_value;
}

//
// Set ALBUM Version
//
$template->assign_vars(array(
	'ALBUM_VERSION' => '2' . $album_config['album_version'],
));
$user->add_lang('mods/info_acp_gallery');
include("{$phpbb_root_path}{$gallery_root_path}includes/functions.$phpEx");

//dont display on recent image feature
$recent_image_addon = isset($recent_image_addon) ? true : false;
if (!$recent_image_addon)
{
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $user->lang['GALLERY'],
		'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx"),
	));
}

?>