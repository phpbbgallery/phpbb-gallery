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
* Latest version tested: 4.1.5
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

if (!function_exists('uc_select_plugins'))
{
	function uc_select_plugins($value, $key)
	{
		global $gallery_plugins, $user;

		$sort_order_options = '';
		if (in_array('highslide', $gallery_plugins['plugins']))
		{
			$sort_order_options .= '<option' . (($value == 'highslide') ? ' selected="selected"' : '') . " value='highslide'>" . $user->lang['UC_LINK_HIGHSLIDE'] . '</option>';
		}
		if (in_array('lytebox', $gallery_plugins['plugins']))
		{
			$sort_order_options .= '<option' . (($value == 'lytebox') ? ' selected="selected"' : '') . " value='lytebox'>" . $user->lang['UC_LINK_LYTEBOX'] . '</option>';
		}

		return $sort_order_options;
	}
}

if (!function_exists('generate_image_link_plugins'))
{
	function generate_image_link_plugins($mode)
	{
		global $gallery_plugins, $user;

		$tpl = '';
		switch ($mode)
		{
			case 'highslide':
				$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}" class="highslide" onclick="return hs.expand(this)">{CONTENT}</a>';
			break;
			case 'lytebox':
				// LPI is a little credit to Dr.Death =)
				$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}" rel="lytebox[LPI]" class="image-resize">{CONTENT}</a>';
			break;
			case 'lytebox_slide_show':
				$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}" rel="lyteshow[album]" class="image-resize">{CONTENT}</a>';
			break;
			default:
				// Plugin not found, return blank image
				$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}">{CONTENT}</a>';
		}

		return $tpl;
	}
}

define('S_GALLERY_PLUGINS', true);

?>