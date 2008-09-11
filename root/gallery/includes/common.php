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
$gallery_root_path = GALLERY_ROOT_PATH;
include("{$phpbb_root_path}{$gallery_root_path}includes/constants.$phpEx");

//
// Get Album Config
//
$sql = 'SELECT *
	FROM ' . GALLERY_CONFIG_TABLE;
@$result = $db->sql_query($sql);
while ($row = $db->sql_fetchrow($result))
{
	$album_config_name = $row['config_name'];
	$album_config_value = $row['config_value'];
	$album_config[$album_config_name] = $album_config_value;
}
$db->sql_freeresult($result);

$user->add_lang('mods/info_acp_gallery');
// Disable gallery if the install/ directory is still present
if (file_exists($phpbb_root_path . 'install'))
{
	trigger_error('REMOVE_GALLERY_INSTALL');
}

$template->assign_vars(array(
	'S_GALLERY_HIGHSLIDE_JS' => file_exists($phpbb_root_path . 'styles/' . $user->theme['template_path'] . '/theme/highslide/highslide-full.js'),
));
include("{$phpbb_root_path}{$gallery_root_path}includes/functions.$phpEx");

//dont display on recent image feature
$recent_image_addon = isset($recent_image_addon) ? true : false;
if (!$recent_image_addon)
{
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $user->lang['GALLERY'],
		'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx"),
	));
	$template->assign_vars(array(
		'S_IN_GALLERY' => true,
	));
}

?>