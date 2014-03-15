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

class phpbb_gallery_plugins
{
	static public $plugins		= array();
	static public $slideshow	= false;

	static public function init($path)
	{
		global $template;

		/**
		* Highslide JS
		*
		* Latest version tested: 4.1.9
		* Download: http://highslide.com/download.php
		* License:  Creative Commons Attribution-NonCommercial 2.5 License
		*           http://creativecommons.org/licenses/by-nc/2.5/
		*/
		if (file_exists($path . 'plugins/highslide/highslide-full.js'))
		{
			self::$plugins[] = 'highslide';
			self::$slideshow = true;
			$template->assign_var('S_GP_HIGHSLIDE', $path . 'plugins/highslide/');
		}

		/**
		* Lytebox
		*
		* Latest version tested: 3.22
		* Download: http://www.dolem.com/lytebox/
		* License:  Creative Commons Attribution 3.0 License
		*           http://creativecommons.org/licenses/by/3.0/
		*/
		if (file_exists($path . 'plugins/lytebox/lytebox.js'))
		{
			self::$plugins[] = 'lytebox';
			self::$slideshow = true;
			$template->assign_var('S_GP_LYTEBOX', $path . 'plugins/lytebox/');
		}

		/**
		* Shadowbox.js
		*
		* Latest version tested: 3.0b
		* Download: http://www.shadowbox-js.com/download.html
		* License:  Shadowbox.js License version 1.0
		*           http://shadowbox-js.com/LICENSE
		*/
		if (file_exists($path . 'plugins/shadowbox/shadowbox.js'))
		{
			self::$plugins[] = 'shadowbox';
			self::$slideshow = true;
			$template->assign_var('S_GP_SHADOWBOX', $path . 'plugins/shadowbox/');
		}
	}

	static public function uc_select($value, $key)
	{
		global $user;

		$sort_order_options = '';
		$sort_order_options .= '<option' . (($value == 'newtab') ? ' selected="selected"' : '') . " value='newtab'>" . $user->lang['UC_LINK_NEWTAB'] . '</option>';
		if (in_array('highslide', self::$plugins))
		{
			$sort_order_options .= '<option' . (($value == 'highslide') ? ' selected="selected"' : '') . " value='highslide'>" . $user->lang['UC_LINK_HIGHSLIDE'] . '</option>';
		}
		if (in_array('lytebox', self::$plugins))
		{
			$sort_order_options .= '<option' . (($value == 'lytebox') ? ' selected="selected"' : '') . " value='lytebox'>" . $user->lang['UC_LINK_LYTEBOX'] . '</option>';
		}
		if (in_array('shadowbox', self::$plugins))
		{
			$sort_order_options .= '<option' . (($value == 'shadowbox') ? ' selected="selected"' : '') . " value='shadowbox'>" . $user->lang['UC_LINK_SHADOWBOX'] . '</option>';
		}

		return $sort_order_options;
	}

	static public function generate_image_link($mode)
	{
		global $user;

		if ($mode == 'plugin' && !empty(self::$plugins[0]))
		{
			$mode = self::$plugins[0];
		}

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
			case 'newtab':
				$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}" onclick="window.open(this.href); return false;">{CONTENT}</a>';
			break;
			default:
				// Plugin not found, return blank image
				$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}">{CONTENT}</a>';
		}

		return $tpl;
	}

	static public function slideshow($query_result)
	{
		global $db, $user;

		$images = array();

		if (in_array('highslide', self::$plugins))
		{
			$trigger_message = $user->lang['SLIDE_SHOW_HIGHSLIDE'];
			while ($row = $db->sql_fetchrow($query_result))
			{
				$images[] = phpbb_gallery_image::generate_link('image_name', 'highslide', $row['image_id'], $row['image_name'], $row['image_album_id']);
			}
		}
		elseif (in_array('shadowbox', self::$plugins))
		{
			$trigger_message = $user->lang['SLIDE_SHOW_SHADOWBOX'];
			while ($row = $db->sql_fetchrow($query_result))
			{
				$images[] = phpbb_gallery_image::generate_link('image_name', 'shadowbox_slideshow', $row['image_id'], $row['image_name'], $row['image_album_id']);
			}
		}
		elseif (in_array('lytebox', self::$plugins))
		{
			$trigger_message = $user->lang['SLIDE_SHOW_LYTEBOX'];
			while ($row = $db->sql_fetchrow($query_result))
			{
				$images[] = phpbb_gallery_image::generate_link('image_name', 'lytebox_slideshow', $row['image_id'], $row['image_name'], $row['image_album_id']);
			}
		}
		else
		{
			trigger_error('MISSING_SLIDESHOW_PLUGIN');
		}

		return $trigger_message . '<br /><br />' . implode(', ', $images);
	}
}
