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

class phpbb_gallery_config
{
	/**
	* Prefix which is prepend to the configs before they are stored in the config table.
	*/
	static protected $prefix = 'phpbb_gallery_';

	static protected $config = false;

	public static function get($key)
	{
		if (self::$config === false)
		{
			self::load();
		}

		return self::$config[$key];
	}

	public static function get_array()
	{
		if (self::$config === false)
		{
			self::load();
		}

		return self::$config;
	}

	public static function get_default()
	{
		return self::$default_config;
	}

	public static function set($config_name, $config_value)
	{
		self::$config[$config_name] = settype($config_value, gettype(self::$default_config[$config_name]));
		set_config(self::$prefix . $config_name, $config_value, self::is_dynamic($config_name));
	}

	public static function inc($config_name, $increment)
	{
		if (gettype(self::$default_config[$config_name]) != 'int')
		{
			return false;
		}

		set_config_count(self::$prefix . $config_name, (int) $increment, self::is_dynamic($config_name));
		self::$config[$config_name] += (int) $increment;
		return true;
	}

	public static function dec($config_name, $decrement)
	{
		if (gettype(self::$default_config[$config_name]) != 'int')
		{
			return false;
		}

		set_config_count(self::$prefix . $config_name, 0 - (int) $increment, self::is_dynamic($config_name));
		self::$config[$config_name] -= (int) $decrement;
		return true;
	}

	public static function is_dynamic($config_name)
	{
		if (isset(self::$is_dynamic[$config_name]))
		{
			return true;
		}
		return false;
	}

	public static function load($load_default = false)
	{
		global $config;

		foreach ($config as $config_name => $config_value)
		{
			// Load all config values of the gallery
			if (strpos($config_name, self::$prefix) === 0)
			{
				$config_name = substr($config_name, strlen(self::$prefix));
				self::$config[$config_name] = settype($config_value, gettype(self::$default_config[$config_name]));
			}
		}

		if ($load_default)
		{
			// Should we load the default-config?
			self::$config = self::$config + self::$default_config;
		}
	}

	protected static $is_dynamic = array(
		'mvc_time',
		'mvc_version',

		'num_comments',
		'num_images',
		'num_pegas',
	);

	protected static $default_config = array(
		'album_columns'		=> 3,
		'album_display'		=> 254,
		'album_images'		=> 2500,
		'album_rows'		=> 4,
		'allow_comments'	=> true,
		'allow_gif'			=> true,
		'allow_hotlinking'	=> true,
		'allow_jpg'			=> true,
		'allow_png'			=> true,
		'allow_rates'		=> true,
		'allow_resize'		=> true,
		'allow_rotate'		=> true,

		'captcha_comment'	=> true,
		'captcha_upload'	=> true,
		'comment_length'	=> 2000,
		'contests_ended'	=> 0,

		'default_sort_dir'	=> 'd',
		'default_sort_key'	=> 't',
		'description_length'=> 2000,
		'disp_birthdays'			=> false,
		'disp_exifdata'				=> true,
		'disp_image_url'			=> true,
		'disp_login'				=> true,
		'disp_nextprev_thumbnail'	=> false,
		'disp_statistic'			=> true,
		'disp_total_images'			=> true,
		'disp_whoisonline'			=> true,

		'gdlib_version'		=> 2,

		'hotlinking_domains'	=> 'flying-bits.org',

		'jpg_quality'			=> 100,

		'link_thumbnail'		=> 'image_page',
		'link_imagepage'		=> 'image',
		'link_image_name'		=> 'image_page',
		'link_image_icon'		=> 'image_page',

		'max_filesize'			=> 512000,
		'max_height'			=> 1024,
		'max_rating'			=> 10,
		'max_width'				=> 1280,
		'medium_cache'			=> true,
		'medium_height'			=> 600,
		'medium_width'			=> 800,
		'mini_thumbnail_disp'	=> true,
		'mini_thumbnail_size'	=> 70,
		'mvc_time'				=> 0,
		'mvc_version'			=> '',

		'newest_pega_user_id'	=> 0,
		'newest_pega_username'	=> '',
		'newest_pega_user_colour'	=> '',
		'newest_pega_album_id'	=> 0,
		'num_comments'			=> 0,
		'num_images'			=> 0,
		'num_pegas'				=> 0,
		'num_uploads'			=> 10,

		'pegas_index_album'		=> false,
		'pegas_per_page'		=> 15,
		'profile_user_images'	=> true,
		'profile_pega'			=> true,

		'rrc_gindex_columns'	=> 4,
		'rrc_gindex_comments'	=> false,
		'rrc_gindex_contests'	=> 1,
		'rrc_gindex_crows'		=> 5,
		'rrc_gindex_display'	=> 173,
		'rrc_gindex_mode'		=> 7,
		'rrc_gindex_pegas'		=> true,
		'rrc_gindex_rows'		=> 1,
		'rrc_profile_columns'	=> 4,
		'rrc_profile_display'	=> 141,
		'rrc_profile_mode'		=> 3,
		'rrc_profile_pegas'		=> true,
		'rrc_profile_rows'		=> 1,

		'search_display'		=> 45,
		'shortnames'			=> 25,

		'thumbnail_cache'		=> true,
		'thumbnail_height'		=> 125,
		'thumbnail_infoline'	=> false,
		'thumbnail_quality'		=> 50,
		'thumbnail_width'		=> 125,

		'version'				=> '',
		'viewtopic_icon'		=> true,
		'viewtopic_images'		=> true,
		'viewtopic_link'		=> false,

		'watermark_enabled'		=> true,
		'watermark_height'		=> 50,
		'watermark_position'	=> 20,
		'watermark_source'		=> 'gallery/images/watermark.png',
		'watermark_width'		=> 200,
	);
}
