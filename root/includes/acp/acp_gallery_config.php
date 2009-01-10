<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* borrowed from phpBB3
* @author: phpBB Group
* @file: acp_boards
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_gallery_config
{
	var $u_action;
	var $new_config = array();

	function main($id, $mode)
	{
		global $db, $user, $auth, $cache, $template, $gallery_config;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
		$gallery_root_path = GALLERY_ROOT_PATH;

		include($phpbb_root_path . $gallery_root_path . 'includes/constants.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/functions.' . $phpEx);
		$gallery_config = load_gallery_config();

		$user->add_lang('mods/gallery_acp');
		$user->add_lang('mods/gallery');

		$submit = (isset($_POST['submit'])) ? true : false;

		$form_key = 'acp_time';
		add_form_key($form_key);

		switch ($mode)
		{
			case 'main':
				$display_vars = array(
					'title'	=> 'GALLERY_CONFIG',
					'vars'	=> array(
						'legend1'				=> 'GALLERY_CONFIG',
						'allow_comments'		=> array('lang' => 'COMMENT_SYSTEM',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),
						'allow_rates'			=> array('lang' => 'RATE_SYSTEM',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),
						'rate_scale'			=> array('lang' => 'RATE_SCALE',			'validate' => 'int',	'type' => 'text:7:2',		'gallery' => true,	'explain' => false),
						'hotlink_prevent'		=> array('lang' => 'HOTLINK_PREVENT',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),
						'hotlink_allowed'		=> array('lang' => 'HOTLINK_ALLOWED',		'validate' => 'string',	'type' => 'text:40:255',	'gallery' => true,	'explain' => true),
						'gallery_total_images'			=> array('lang' => 'DISP_TOTAL_IMAGES',				'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => false,	'explain' => false),
						'gallery_user_images_profil'	=> array('lang' => 'DISP_USER_IMAGES_PROFIL',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => false,	'explain' => false),
						'gallery_personal_album_profil'	=> array('lang' => 'DISP_PERSONAL_ALBUM_PROFIL',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => false,	'explain' => false),
						'personal_album_index'	=> array('lang' => 'PERSONAL_ALBUM_INDEX',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => true),
						'shorted_imagenames'	=> array('lang' => 'SHORTED_IMAGENAMES',	'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => true),

						'legend2'				=> 'ALBUM_SETTINGS',
						'rows_per_page'			=> array('lang' => 'ROWS_PER_PAGE',			'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false),
						'cols_per_page'			=> array('lang' => 'COLS_PER_PAGE',			'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false),
						'sort_method'			=> array('lang' => 'DEFAULT_SORT_METHOD',	'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'sort_method_select'),
						'sort_order'			=> array('lang' => 'DEFAULT_SORT_ORDER',	'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'sort_order_select'),
						'max_pics'				=> array('lang' => 'MAX_IMAGES_PER_ALBUM',	'validate' => 'int',	'type' => 'text:7:7',		'gallery' => true,	'explain' => false),
						'disp_fake_thumb'		=> array('lang' => 'DISP_FAKE_THUMB',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),
						'fake_thumb_size'		=> array('lang' => 'FAKE_THUMB_SIZE',		'validate' => 'int',	'type' => 'text:7:4',		'gallery' => true,	'explain' => true),

						'legend3'				=> 'IMAGE_SETTINGS',
						'upload_images'			=> array('lang' => 'UPLOAD_IMAGES',			'validate' => 'int',	'type' => 'text:7:2',		'gallery' => true,	'explain' => false),
						'max_file_size'			=> array('lang' => 'MAX_FILE_SIZE',			'validate' => 'int',	'type' => 'text:12:9',		'gallery' => true,	'explain' => false),
						'max_width'				=> array('lang' => 'MAX_WIDTH',				'validate' => 'int',	'type' => 'text:7:5',		'gallery' => true,	'explain' => false),
						'max_height'			=> array('lang' => 'MAX_HEIGHT',			'validate' => 'int',	'type' => 'text:7:5',		'gallery' => true,	'explain' => false),
						'resize_images'			=> array('lang' => 'RESIZE_IMAGES',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),
						'medium_cache'			=> array('lang' => 'MEDIUM_CACHE',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),
						'preview_rsz_width'		=> array('lang' => 'RSZ_WIDTH',				'validate' => 'int',	'type' => 'text:7:4',		'gallery' => true,	'explain' => false),
						'preview_rsz_height'	=> array('lang' => 'RSZ_HEIGHT',			'validate' => 'int',	'type' => 'text:7:4',		'gallery' => true,	'explain' => false),
						'gif_allowed'			=> array('lang' => 'GIF_ALLOWED',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),
						'jpg_allowed'			=> array('lang' => 'JPG_ALLOWED',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),
						'png_allowed'			=> array('lang' => 'PNG_ALLOWED',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),
						'desc_length'			=> array('lang' => 'IMAGE_DESC_MAX_LENGTH',	'validate' => 'int',	'type' => 'text:7:5',		'gallery' => true,	'explain' => false),
						'exif_data'				=> array('lang' => 'DISP_EXIF_DATA',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),
						'view_image_url'		=> array('lang' => 'VIEW_IMAGE_URL',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),

						'legend4'				=> 'THUMBNAIL_SETTINGS',
						'thumbnail_cache'		=> array('lang' => 'THUMBNAIL_CACHE',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),
						'gd_version'			=> array('lang' => 'GD_VERSION',			'validate' => 'bool',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'gd_radio'),
						'thumbnail_size'		=> array('lang' => 'THUMBNAIL_SIZE',		'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false),
						'thumbnail_quality'		=> array('lang' => 'THUMBNAIL_QUALITY',		'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false),
						'thumbnail_info_line'	=> array('lang' => 'INFO_LINE',				'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),

						'legend5'				=> 'WATERMARK_OPTIONS',
						'watermark_images'		=> array('lang' => 'WATERMARK_IMAGES',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),
						'watermark_source'		=> array('lang' => 'WATERMARK_SOURCE',		'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'watermark_source'),
						'watermark_height'		=> array('lang' => 'WATERMARK_HEIGHT',		'validate' => 'int',	'type' => 'text:7:4',		'gallery' => true,	'explain' => true),
						'watermark_width'		=> array('lang' => 'WATERMARK_WIDTH',		'validate' => 'int',	'type' => 'text:7:4',		'gallery' => true,	'explain' => true),

						'legend6'				=> 'UC_LINK_CONFIG',
						'link_thumbnail'		=> array('lang' => 'UC_THUMBNAIL',			'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'uc_select'),
						'link_imagepage'		=> array('lang' => 'UC_IMAGEPAGE',			'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'uc_select'),
						'link_image_name'		=> array('lang' => 'UC_IMAGE_NAME',			'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'uc_select'),
						'link_image_icon'		=> array('lang' => 'UC_IMAGE_ICON',			'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'uc_select'),

						'legend7'				=> 'RRC_GINDEX',
						'rrc_gindex_mode'		=> array('lang' => 'RRC_GINDEX_MODE',		'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'rrc_gindex'),
						'rrc_gindex_rows'		=> array('lang' => 'RRC_GINDEX_ROWS',		'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false),
						'rrc_gindex_columns'	=> array('lang' => 'RRC_GINDEX_COLUMNS',	'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false),
						'rrc_gindex_comments'	=> array('lang' => 'RRC_GINDEX_COMMENTS',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false),

						'legend8'				=> '',
					)
				);
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

		$this->new_config = $config;
		$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
		$error = array();

		// We validate the complete config if whished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);
		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}

		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}

		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

			if ($submit)
			{
				if ($null['gallery'])
				{
					set_gallery_config($config_name, $config_value);
				}
				else
				{
					set_config($config_name, $config_value);
				}
			}
		}

		if ($submit)
		{
			$cache->destroy('sql', CONFIG_TABLE);
			$cache->destroy('sql', GALLERY_CONFIG_TABLE);
			trigger_error($user->lang['GALLERY_CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$this->tpl_name = 'acp_board';
		$this->page_title = $display_vars['title'];

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action)
		);

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
				);

				continue;
			}

			if ($vars['gallery'])
			{
				$this->new_config[$config_key] = $gallery_config[$config_key];
			}
			else
			{
				$this->new_config[$config_key] = $config[$config_key];
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'])
			{
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXP'])) ? $user->lang[$vars['lang'] . '_EXP'] : '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
			));

			unset($display_vars['vars'][$config_key]);
		}
	}

	/**
	* Select sort method
	*/
	function sort_method_select($value, $key)
	{
		global $user;

		$sort_method_options = '<option' . (($value == 't') ? ' selected="selected"' : '') . " value='t'>" . $user->lang['TIME'] . '</option>';
		$sort_method_options .= '<option' . (($value == 'n') ? ' selected="selected"' : '') . " value='n'>" . $user->lang['IMAGE_NAME'] . '</option>';
		$sort_method_options .= '<option' . (($value == 'u') ? ' selected="selected"' : '') . " value='u'>" . $user->lang['USERNAME'] . '</option>';
		$sort_method_options .= '<option' . (($value == 'vc') ? ' selected="selected"' : '') . " value='vc'>" . $user->lang['VIEWS'] . '</option>';
		$sort_method_options .= '<option' . (($value == 'r') ? ' selected="selected"' : '') . " value='r'>" . $user->lang['RATING'] . '</option>';
		$sort_method_options .= '<option' . (($value == 'c') ? ' selected="selected"' : '') . " value='c'>" . $user->lang['COMMENTS'] . '</option>';
		$sort_method_options .= '<option' . (($value == 'lc') ? ' selected="selected"' : '') . " value='lc'>" . $user->lang['NEW_COMMENT'] . '</option>';

		return "<select name=\"config[sort_method]\" id=\"sort_method\">$sort_method_options</select>";
	}

	/**
	* Select sort order
	*/
	function sort_order_select($value, $key)
	{
		global $user;

		$sort_order_options = '<option' . (($value == 'd') ? ' selected="selected"' : '') . " value='d'>" . $user->lang['SORT_DESCENDING'] . '</option>';
		$sort_order_options .= '<option' . (($value == 'a') ? ' selected="selected"' : '') . " value='a'>" . $user->lang['SORT_ASCENDING'] . '</option>';

		return "<select name=\"config[sort_order]\" id=\"sort_order\">$sort_order_options</select>";
	}

	/**
	* Radio Buttons for GD library
	*/
	function gd_radio($value, $key)
	{
		$key_gd1	= ($value == 1) ? ' checked="checked"' : '';
		$key_gd2	= ($value == 2) ? ' checked="checked"' : '';

		$tpl = '<label><input type="radio" name="config[' . $key . ']" value="1"' . $key_gd1 . ' class="radio" /> GD1</label>';
		$tpl .= '<label><input type="radio" id="' . $key . '" name="config[' . $key . ']" value="2"' . $key_gd2 . ' class="radio" /> GD2</label>';

		return $tpl;
	}

	/**
	* Display watermark
	*/
	function watermark_source($value, $key)
	{
		global $user;

		return generate_board_url() . "<br /><input type=\"text\" name=\"config[$key]\" id=\"$key\" value=\"$value\" size =\"40\" maxlength=\"125\" /><br /><img src=\"" . generate_board_url() . "/$value\" alt=\"" . $user->lang['WATERMARK'] . "\" />";
	}

	/**
	* Select the link destination
	*/
	function uc_select($value, $key)
	{
		global $phpbb_root_path, $user;

		$sort_order_options = '<option' . (($value == 'lytebox') ? ' selected="selected"' : '') . " value='lytebox'>" . $user->lang['UC_LINK_LYTEBOX'] . '</option>';
		if (file_exists($phpbb_root_path . 'styles/' . $user->theme['template_path'] . '/theme/highslide/highslide-full.js'))
		{
			$sort_order_options .= '<option' . (($value == 'highslide') ? ' selected="selected"' : '') . " value='highslide'>" . $user->lang['UC_LINK_HIGHSLIDE'] . '</option>';
		}
		if ($key != 'link_imagepage')
		{
			$sort_order_options .= '<option' . (($value == 'image_page') ? ' selected="selected"' : '') . " value='image_page'>" . $user->lang['UC_LINK_IMAGE_PAGE'] . '</option>';
		}
		$sort_order_options .= '<option' . (($value == 'image') ? ' selected="selected"' : '') . " value='image'>" . $user->lang['UC_LINK_IMAGE'] . '</option>';
		$sort_order_options .= '<option' . (($value == 'none') ? ' selected="selected"' : '') . " value='none'>" . $user->lang['UC_LINK_NONE'] . '</option>';

		return "<select name=\"config[$key]\" id=\"$key\">$sort_order_options</select>";
	}

	/**
	* Select RRC-Config on gallery/index.php
	*/
	function rrc_gindex($value, $key)
	{
		global $user;

		$rrc_gindex_options = '<option' . (($value == 'recent') ? ' selected="selected"' : '') . " value='recent'>" . $user->lang['RRC_MODE_RECENT'] . '</option>';
		$rrc_gindex_options .= '<option' . (($value == 'random') ? ' selected="selected"' : '') . " value='random'>" . $user->lang['RRC_MODE_RANDOM'] . '</option>';
		$rrc_gindex_options .= '<option' . (($value == 'comment') ? ' selected="selected"' : '') . " value='comment'>" . $user->lang['RRC_MODE_COMMENTS'] . '</option>';
		$rrc_gindex_options .= '<option' . (($value == '!recent') ? ' selected="selected"' : '') . " value='!recent'>" . $user->lang['RRC_MODE_ARECENT'] . '</option>';
		$rrc_gindex_options .= '<option' . (($value == '!random') ? ' selected="selected"' : '') . " value='!random'>" . $user->lang['RRC_MODE_ARANDOM'] . '</option>';
		$rrc_gindex_options .= '<option' . (($value == '!comment') ? ' selected="selected"' : '') . " value='!comment'>" . $user->lang['RRC_MODE_ACOMMENTS'] . '</option>';
		$rrc_gindex_options .= '<option' . (($value == 'all') ? ' selected="selected"' : '') . " value='all'>" . $user->lang['RRC_MODE_ALL'] . '</option>';
		$rrc_gindex_options .= '<option' . (($value == '!all') ? ' selected="selected"' : '') . " value='!all'>" . $user->lang['RRC_MODE_AALL'] . '</option>';

		return "<select name=\"config[$key]\" id=\"$key\">$rrc_gindex_options</select>";
	}
}

?>