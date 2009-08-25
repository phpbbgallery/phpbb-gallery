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

$user->add_lang('mods/info_acp_gallery');
$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . $gallery_root_path . 'plugins/index.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/constants.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/functions.' . $phpEx);
$gallery_config = load_gallery_config();

if ($gallery_config['version_check_time'] + 86400 < time())
{
	// Scare the user of outdated versions
	if (!function_exists('mod_version_check'))
	{
		$phpbb_admin_path = $phpbb_root_path . 'adm/';
		include($phpbb_root_path . $gallery_root_path . 'includes/functions_version_check.' . $phpEx);
	}
	set_gallery_config('version_check_time', time());
	set_gallery_config('version_check_version', mod_version_check(true));
}

if (!isset($auth))
{
	// Quite hackish, sometimes from memberlist.php this is the case.
	global $auth;
}

if ($auth->acl_get('a_') && version_compare($gallery_config['phpbb_gallery_version'], $gallery_config['version_check_version'], '<'))
{
	$user->add_lang('mods/gallery_acp');
	$template->assign_vars(array(
		'GALLERY_VERSION_CHECK'			=> sprintf($user->lang['NOT_UP_TO_DATE'], $user->lang['GALLERY']),
	));
}

$template->assign_vars(array(
	'U_GALLERY_SEARCH'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx"),
	'U_G_SEARCH_COMMENTED'			=> ($gallery_config['allow_comments']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", 'search_id=commented') : '',
	'U_G_SEARCH_CONTESTS'			=> ($gallery_config['allow_rates'] && $gallery_config['contests_ended']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", 'search_id=contests') : '',
	'U_G_SEARCH_RANDOM'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", 'search_id=random'),
	'U_G_SEARCH_RECENT'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", 'search_id=recent'),
	'U_G_SEARCH_SELF'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", 'search_id=egosearch'),
	'U_G_SEARCH_TOPRATED'			=> ($gallery_config['allow_rates']) ? append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", 'search_id=toprated') : '',

	'GALLERY_TRANSLATION_INFO'		=> (!empty($user->lang['GALLERY_TRANSLATION_INFO'])) ? $user->lang['GALLERY_TRANSLATION_INFO'] : '',
));

// Do not display Gallery-NavLink / activate Gallery-Tab on "recent-random-images"
$recent_image_addon = isset($recent_image_addon);
if (!$recent_image_addon)
{
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $user->lang['GALLERY'],
		'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx"),
	));
	$template->assign_var('S_IN_GALLERY', true);
}

?>