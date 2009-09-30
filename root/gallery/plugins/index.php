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
* Latest version tested: 4.1.7
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

/**
* Shadowbox.js
*
* Latest version tested: 3.0b
* Download: http://www.shadowbox-js.com/download.html
* License:  Shadowbox.js License version 1.0
*           http://shadowbox-js.com/LICENSE
*/
if (file_exists($phpbb_root_path . $gallery_root_path . 'plugins/shadowbox/shadowbox.js'))
{
	$gallery_plugins['plugins'][] = 'shadowbox';
	$gallery_plugins['slideshow'] = true;
	$template->assign_var('S_GP_SHADOWBOX', $phpbb_root_path . $gallery_root_path . 'plugins/shadowbox/');
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
		if (in_array('shadowbox', $gallery_plugins['plugins']))
		{
			$sort_order_options .= '<option' . (($value == 'shadowbox') ? ' selected="selected"' : '') . " value='shadowbox'>" . $user->lang['UC_LINK_SHADOWBOX'] . '</option>';
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
			case 'lytebox_slideshow':
				$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}" rel="lyteshow[album]" class="image-resize">{CONTENT}</a>';
			break;
			case 'shadowbox':
				$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}" rel="shadowbox[flying-bits]">{CONTENT}</a>';
			break;
			case 'shadowbox_slideshow':
				$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}" rel="shadowbox[flying-bits];options={slideshowDelay: 3}">{CONTENT}</a>';
			break;
			default:
				// Plugin not found, return blank image
				$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}">{CONTENT}</a>';
		}

		return $tpl;
	}
}

if (!function_exists('slideshow_plugins'))
{
	function slideshow_plugins($query_result)
	{
		global $db, $gallery_plugins, $user;

		$images = array();

		if (in_array('highslide', $gallery_plugins['plugins']))
		{
			$trigger_message = $user->lang['SLIDE_SHOW_HIGHSLIDE'];
			while ($row = $db->sql_fetchrow($query_result))
			{
				$images[] = generate_image_link('image_name', 'highslide', $row['image_id'], $row['image_name'], $row['image_album_id']);
			}
		}
		elseif (in_array('shadowbox', $gallery_plugins['plugins']))
		{
			$trigger_message = $user->lang['SLIDE_SHOW_SHADOWBOX'];
			while ($row = $db->sql_fetchrow($query_result))
			{
				$images[] = generate_image_link('image_name', 'shadowbox_slideshow', $row['image_id'], $row['image_name'], $row['image_album_id']);
			}
		}
		elseif (in_array('lytebox', $gallery_plugins['plugins']))
		{
			$trigger_message = $user->lang['SLIDE_SHOW_LYTEBOX'];
			while ($row = $db->sql_fetchrow($query_result))
			{
				$images[] = generate_image_link('image_name', 'lytebox_slideshow', $row['image_id'], $row['image_name'], $row['image_album_id']);
			}
		}
		else
		{
			trigger_error('MISSING_SLIDESHOW_PLUGIN');
		}

		return $trigger_message . '<br /><br />' . implode(', ', $images);
	}
}

define('S_GALLERY_PLUGINS', true);

?>