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
$album_config = load_gallery_config();

// Don't display Gallery-NavLink / activate Gallery-Tab on "recent-random-images"
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