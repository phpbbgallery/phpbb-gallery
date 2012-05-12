<?php
/**
*
* @package Gallery - Config Core
* @copyright (c) 2012 nickvergessen - http://www.flying-bits.org/
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_ext_gallery_core_config_sets_core implements phpbb_ext_nickvergessen_toolio_config_sets_interface
{
	/**
	* Returns the prefix that should be used for the set.
	* All config names will be prefixed with this prefix:
	* Example:	prefix:			sampleprefix_
	*			config_name:	myconfig
	*			stored in db:	sampleprefix_myconfig
	* Please remember the length limit of 255 characters for config names
	*
	* @return	string		The set's prefix
	*/
	static public function get_prefix()
	{
		return 'phpbb_gallery_';
	}

	/**
	* Returns the array with all configs and their default values.
	* NOTE:	The values on set() and get() will be casted to the same type as the default value.
	*		The functions inc() and dec() are only available for type integer.
	*
	* @return	array		The array of the configs
	*/
	static public function get_configs()
	{
		return array(
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
			'allow_zip'			=> false,

			'captcha_comment'		=> true,
			'captcha_upload'		=> true,
			'comment_length'		=> 2000,
			'comment_user_control'	=> true,
			'contests_ended'		=> 0,
			'current_upload_dir_size'	=> 0,
			'current_upload_dir'	=> 0,

			'default_sort_dir'	=> 'd',
			'default_sort_key'	=> 't',
			'description_length'=> 2000,
			'disp_birthdays'			=> false,
			'disp_image_url'			=> true,
			'disp_login'				=> true,
			'disp_nextprev_thumbnail'	=> false,
			'disp_statistic'			=> true,
			'disp_total_images'			=> true,
			'disp_whoisonline'			=> true,

			'feed_enable'			=> true,
			'feed_enable_pegas'		=> true,
			'feed_limit'			=> 10,

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
			'mvc_ignore'			=> 0,
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
			'prune_orphan_time'		=> 0,

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
			'thumbnail_height'		=> 160,
			'thumbnail_infoline'	=> false,
			'thumbnail_quality'		=> 50,
			'thumbnail_width'		=> 240,

			'version'				=> '',
			'viewtopic_icon'		=> true,
			'viewtopic_images'		=> true,
			'viewtopic_link'		=> false,

			'watermark_changed'		=> 0,
			'watermark_enabled'		=> true,
			'watermark_height'		=> 50,
			'watermark_position'	=> 20,
			'watermark_source'		=> 'gallery/images/watermark.png',
			'watermark_width'		=> 200,
		);
	}

	/**
	* Returns an array of all config names, that are dynamic.
	* Dynamic values are not cached, but always pulled from the database.
	*
	* @return	array		The array of dynamic configs
	*/
	static public function get_dynamics()
	{
		return array(
			'mvc_time',
			'mvc_version',

			'num_comments',
			'num_images',
			'num_pegas',

			'current_upload_dir_size',
		);
	}
}
