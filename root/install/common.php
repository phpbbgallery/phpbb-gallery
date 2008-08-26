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
include($phpbb_root_path . 'install/install_functions.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$user->add_lang('mods/gallery_install');
$user->add_lang('mods/info_acp_gallery');

$new_mod_version = '0.4.0-RC3';
$last_mod_version = '0.4.0-RC2';
$page_title = 'phpBB Gallery v' . $new_mod_version;
$log_name = 'Modification "phpBB Gallery" v' . $new_mod_version;

$module_names = array('ACP_GALLERY_MANAGE_USER', 'ACP_GALLERY_MANAGE_RESTS', 'ACP_GALLERY_CLEANUP', 'ACP_IMPORT_ALBUMS', 'ACP_GALLERY_ALBUM_PERSONAL_PERMISSIONS', 'ACP_GALLERY_ALBUM_PERMISSIONS', 'ACP_GALLERY_CONFIGURE_GALLERY', 'ACP_GALLERY_MANAGE_CACHE', 'ACP_GALLERY_MANAGE_ALBUMS', 'ACP_GALLERY_OVERVIEW', 'PHPBB_GALLERY');
$module_names = array_merge($module_names, array('UCP_GALLERY_PERSONAL_ALBUMS', 'UCP_GALLERY_FAVORITES', 'UCP_GALLERY_WATCH', 'UCP_GALLERY_SETTINGS', 'UCP_GALLERY'));

$old_configs = array('user_pics_limit', 'mod_pics_limit', 'fullpic_popup', 'personal_gallery', 'personal_gallery_private', 'personal_gallery_limit', 'personal_gallery_view');

$template = new template();
$template->set_custom_template('../install/style', 'nv_install');
$template->assign_var('T_TEMPLATE_PATH', '../install/style');

$template->assign_vars(array(
	'GALLERY_ROOT_PATH'			=> $phpbb_root_path . $gallery_root_path,
	'INSTALL_VERSION'			=> sprintf($user->lang['INSTALLER_INSTALL_VERSION'], $new_mod_version),
	'PAGE_TITLE'				=> 'phpBB Gallery v' . $new_mod_version,

	'U_INTRO'				=> append_sid("{$phpbb_root_path}install/index.php"),
	'U_INSTALL'				=> append_sid("{$phpbb_root_path}install/install.php"),
	'U_UPDATE'				=> append_sid("{$phpbb_root_path}install/update.php"),
	'U_CONVERT'				=> append_sid("{$phpbb_root_path}install/convert.php"),
	'U_DELETE'				=> append_sid("{$phpbb_root_path}install/delete.php"),
));

function check_chmods()
{
	global $template, $phpbb_root_path, $gallery_root_path;

	//Check some Dirs for the right CHMODs
	$chmod_dirs = array(
		array('name' => $gallery_root_path . 'import/', 'chmod' => is_writable($phpbb_root_path . $gallery_root_path . 'import/')),
		array('name' => $gallery_root_path . 'upload/', 'chmod' => is_writable($phpbb_root_path . $gallery_root_path . 'upload/')),
		array('name' => $gallery_root_path . 'upload/cache/', 'chmod' => is_writable($phpbb_root_path . $gallery_root_path . 'upload/cache/')),
	);
	foreach ($chmod_dirs as $dir)
	{
		$template->assign_block_vars('chmods', array(
			'DIR'			=> $dir['name'],
			'S_WRITABLE'	=> $dir['chmod'],
		));
	}
}

?>