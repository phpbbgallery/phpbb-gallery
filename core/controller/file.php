<?php

/**
*
* @package phpBB Gallery Core
* @copyright (c) 2014 nickvergessen
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbgallery\core\controller;

class file
{
	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\db\driver\driver */
	protected $db;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbbgallery\core\auth\auth */
	protected $auth;

	/* @var string */
	protected $path_source;

	/* @var string */
	protected $path_watermark;

	/* @var string */
	protected $table_albums;

	/* @var string */
	protected $table_images;

	/**
	* Constructor
	*
	* @param \phpbb\config\config		$config		Config object
	* @param \phpbb\db\driver\driver	$db			Database object
	* @param \phpbb\user				$user		User object
	* @param \phpbbgallery\core\album\display	$display	Albums display object
	* @param \phpbbgallery\core\auth\auth	$gallery_auth	Gallery auth object
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver $db, \phpbb\user $user, \phpbbgallery\core\auth\auth $gallery_auth, $source_path, $watermark_file, $albums_table, $images_table)
	{
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->auth = $gallery_auth;
		$this->path_source = $source_path;
		$this->path_watermark = $watermark_file;
		$this->table_albums = $albums_table;
		$this->table_images = $images_table;
	}

	/**
	* Index Controller
	*	Route: gallery/image/{image_id}/file
	*
	* @param	int		$image_id
	* @return Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function base($image_id)
	{
		$sql = 'SELECT *
			FROM ' . $this->table_images . ' i
			LEFT JOIN ' . $this->table_albums . ' a
				ON (i.image_album_id = a.album_id)
			WHERE i.image_id = ' . (int) $image_id;
		$result = $this->db->sql_query($sql);
		$image_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$image_data || !$image_data['album_id'])
		{
			// Image or album does not exist
			trigger_error('INVALID_IMAGE');
		}

		$image_error = false;
		$possible_watermark = true;
		$image_filetype = utf8_substr($image_data['image_filename'], strlen($image_data['image_filename']) - 4, 4);
		if (!file_exists($this->path_source . $image_data['image_filename']))
		{
			$sql = 'UPDATE ' . $this->table_images . '
				SET image_filemissing = 1
				WHERE image_id = ' . $image_id;
			$this->db->sql_query($sql);
			//trigger_error('IMAGE_NOT_EXIST');
			$image_error = 'image_not_exist.jpg';
		}

		// Check permissions
		if (($image_data['image_user_id'] != $this->user->data['user_id']) && ($image_data['image_status'] == \phpbbgallery\core\image\image::STATUS_ORPHAN))
		{
			//trigger_error('NOT_AUTHORISED');
			$image_error = 'not_authorised.jpg';
		}

		if ((!$this->auth->acl_check('i_view', $image_data['album_id'], $image_data['album_user_id'])) || (!$this->auth->acl_check('m_status', $image_data['album_id'], $image_data['album_user_id']) && ($image_data['image_status'] == \phpbbgallery\core\image\image::STATUS_UNAPPROVED)))
		{
			//trigger_error('NOT_AUTHORISED');
			$image_error = 'not_authorised.jpg';
		}

		$image_source = $this->path_source  . $image_data['image_filename'];
		// There was a reason to not display the image, so we send an error-image
		if ($image_error)
		{
			$image_data['image_filename'] = $this->user->data['user_lang'] . '_' . $image_error;
			if (!file_exists($this->path_source . $image_data['image_filename']))
			{
				$image_data['image_filename'] = $image_error;
			}
			$image_source = $this->path_source . $image_data['image_filename'];
			$possible_watermark = false;
		}

		$image_tools = new \phpbbgallery\core\file\file(2);
		$image_tools->set_image_options($this->config['phpbb_gallery_max_filesize'], $this->config['phpbb_gallery_max_height'], $this->config['phpbb_gallery_max_width']);
		$image_tools->set_image_data($image_source, $image_data['image_name']);
		if ($image_error || !$this->user->data['is_registered'])
		{
			$image_tools->disable_browser_cache();
		}
//		$image_tools->set_last_modified($phpbb_ext_gallery->user->get_data('user_permissions_changed'));
//		$image_tools->set_last_modified($phpbb_ext_gallery->config->get('watermark_changed'));

		// Watermark
		if (false && $this->config['phpbb_gallery_watermark_enabled'] && $image_data['album_watermark'] && !$this->auth->acl_check('i_watermark', $image_data['album_id'], $image_data['album_user_id']) && $possible_watermark)
		{
			$filesize_var = '';
			$image_tools->set_last_modified(@filemtime($this->path_watermark));
			$image_tools->watermark_image($this->path_watermark, $this->config['phpbb_gallery_watermark_position'], $this->config['phpbb_gallery_watermark_height'], $this->config['phpbb_gallery_watermark_width']);
		}

		$image_tools->send_image_to_browser();
	}
}

//switch ($mode)
//{
//	case 'medium':
//		$filesize_var = 'filesize_medium';
//		$image_source_path = $phpbb_ext_gallery->url->path('medium');
//		$possible_watermark = true;
//		break;
//	case 'thumbnail':
//		$filesize_var = 'filesize_cache';
//		$image_source_path = $phpbb_ext_gallery->url->path('thumbnail');
//		$possible_watermark = false;
//		break;
//	default:
//		$filesize_var = 'filesize_upload';
//		/*if (!class_exists('phpbb_gallery_hookup'))
//		{
//			$phpbb_ext_gallery->url->_include_core('hookup');
//		}
//		if (!phpbb_gallery_hookup::view_image($user->data['user_id']))
//		{
//			// Cash-MOD HookUp failed and denies to view the image
//			//trigger_error('NOT_AUTHORISED');
//			$image_error = 'not_authorised.jpg';
//		}*/
//
//		$image_source_path = $phpbb_ext_gallery->url->path('upload');
//		$possible_watermark = true;
//
//		// Increase the view count only for full images, if not already counted
//		$view = request_var('view', '');
//		if (!$user->data['is_bot'] && !$image_error && ($view != 'no_count'))
//		{
//			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
//				SET image_view_count = image_view_count + 1
//				WHERE image_id = ' . $image_id;
//			$db->sql_query($sql);
//		}
//		break;
//}
//
//// Generate the sourcefile, if it's missing
//if (($mode == 'medium') || ($mode == 'thumbnail'))
//{
//	$filesize_var = '';
//	if ($mode == 'thumbnail')
//	{
//		$resize_width = $phpbb_ext_gallery->config->get('thumbnail_width');
//		$resize_height = $phpbb_ext_gallery->config->get('thumbnail_height');
//	}
//	else
//	{
//		$resize_width = $phpbb_ext_gallery->config->get('medium_width');
//		$resize_height = $phpbb_ext_gallery->config->get('medium_height');
//	}
//
//	if (!file_exists($image_source))
//	{
//		$image_tools->set_image_data($phpbb_ext_gallery->url->path('upload') . $image_data['image_filename']);
//		$image_tools->read_image(true);
//
//		$image_size['file'] = $image_tools->image_size['file'];
//		$image_size['width'] = $image_tools->image_size['width'];
//		$image_size['height'] = $image_tools->image_size['height'];
//
//		$image_tools->set_image_data($image_source);
//
//		if (($image_size['width'] > $resize_width) || ($image_size['height'] > $resize_height))
//		{
//			$put_details = ($phpbb_ext_gallery->config->get('thumbnail_infoline') && ($mode == 'thumbnail')) ? true : false;
//			$image_tools->create_thumbnail($resize_width, $resize_height, $put_details, phpbb_ext_gallery_core_file::THUMBNAIL_INFO_HEIGHT, $image_size);
//		}
//
//		if ($phpbb_ext_gallery->config->get($mode . '_cache'))
//		{
//			$image_tools->write_image($image_source, (($mode == 'thumbnail') ? $phpbb_ext_gallery->config->get('thumbnail_quality') : $phpbb_ext_gallery->config->get('jpg_quality')), false);
//
//			if ($mode == 'thumbnail')
//			{
//				$image_data['filesize_cache'] = @filesize($image_source);
//				$sql_ary = array('filesize_cache' => $image_data['filesize_cache']);
//			}
//			else
//			{
//				$image_data['filesize_medium'] = @filesize($image_source);
//				$sql_ary = array('filesize_medium' => $image_data['filesize_medium']);
//			}
//			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
//				WHERE ' . $db->sql_in_set('image_id', $image_id);
//			$db->sql_query($sql);
//		}
//	}
//}
