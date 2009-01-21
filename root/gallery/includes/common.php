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
include($phpbb_root_path . $gallery_root_path . 'includes/constants.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/functions.' . $phpEx);
$gallery_config = load_gallery_config();

$template->assign_vars(array(
	'U_GALLERY_SEARCH'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx"),
	'U_G_SEARCH_COMMENTED'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", 'search_id=commented'),
	'U_G_SEARCH_RANDOM'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", 'search_id=random'),
	'U_G_SEARCH_RECENT'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", 'search_id=recent'),
	'U_G_SEARCH_SELF'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", 'search_id=egosearch'),
	'U_G_SEARCH_TOPRATED'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}search.$phpEx", 'search_id=toprated'),
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