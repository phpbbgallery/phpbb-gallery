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
if (defined('S_GALLERY_PLUGINS') || isset($gallery_plugins))
{
	// Plugins are already loaded, or shall not be loaded.
	return;
}

$gallery_plugins = array(
	'plugins'	=> array(),
	'slideshow'	=> false,
);

// We just set some vars the real load is in the template-file and on usage

/**
* Highslide JS
*
* Latest version tested: 4.1.3
* Download: http://highslide.com/download.php
* License:  Creative Commons Attribution-NonCommercial 2.5 License
*           http://creativecommons.org/licenses/by-nc/2.5/
*/
if (file_exists($phpbb_root_path . $gallery_root_path . 'plugins/highslide/highslide-full.js'))
{
	$gallery_plugins['plugins'][] = 'highslide';
	$gallery_plugins['slideshow'] = true;
	$template->assign_var('S_GP_HIGHSLIDE', $phpbb_root_path . $gallery_root_path . 'plugins/highslide/');
}

/**
* Lytebox
*
* Latest version tested: 3.22
* Download: http://www.dolem.com/lytebox/
* License:  Creative Commons Attribution 3.0 License
*           http://creativecommons.org/licenses/by/3.0/
*/
if (file_exists($phpbb_root_path . $gallery_root_path . 'plugins/lytebox/lytebox.js'))
{
	$gallery_plugins['plugins'][] = 'lytebox';
	$gallery_plugins['slideshow'] = true;
	$template->assign_var('S_GP_LYTEBOX', $phpbb_root_path . $gallery_root_path . 'plugins/lytebox/');
}

define('S_GALLERY_PLUGINS', true);

?>