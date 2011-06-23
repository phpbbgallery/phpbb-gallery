<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2011 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
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

class phpbb_gallery_image_upload
{
	/**
	* phpBB Upload-Form-Object, File-Object, ExifData-Object
	*/
	private $form = null;
	private $file = null;
	private $exif = null;
	private $tools = null;

	/**
	*
	*/
	public $errors = array();
	public $uploaded_files = 0;
	private $file_count = 0;
	private $album_id = 0;
	private $exif_status = false;
	private $exif_data = false;

	/**
	*
	*/
	public function __construct($album_id, $num_files = 0)
	{
		if (!class_exists('fileupload'))
		{
			phpbb_gallery_url::_include('functions_upload', 'phpbb');
		}
		$this->form = new fileupload();
		$this->form->fileupload('', self::get_allowed_types(), (4 * phpbb_gallery_config::get('max_filesize')));

		$this->tools = new phpbb_gallery_image_file(phpbb_gallery_config::get('gdlib_version'));

		$this->album_id = (int) $album_id;
		$this->file_limit = (int) $num_files;
	}

	/**
	*
	*/
	public function upload_file($file_count)
	{
		if ($this->file_limit && ($this->uploaded_files >= $this->file_limit))
		{
			return false;
		}
		$this->file_count = (int) $file_count;
		$this->file = $this->form->form_upload('image_file_' . $this->file_count);
		if (!$this->file->uploadname)
		{
			return false;
		}
		$this->exif_status = false;
		$this->exif_data = false;

		$error = $this->prepare_file();

		if (!$error)
		{
			$this->uploaded_files++;
		}
	}

	public function prepare_file()
	{
		// Rename the file, move it to the correct location and set chmod
		$this->file->clean_filename('unique_ext'/*, $user->data['user_id'] . '_'*/);
		$this->file->move_file(substr(phpbb_gallery_url::path('upload_noroot'), 0, -1), false, false, CHMOD_ALL);
		if (!empty($this->file->error))
		{
			global $user;

			$this->file->remove();
			$this->new_error($user->lang('UPLOAD_ERROR', $this->file->uploadname, implode('<br />&raquo; ', $this->file->error)));
			return false;
		}
		@chmod($this->file->destination_file, 0777);

		if (in_array($this->file->extension, array('jpg', 'jpeg')))
		{
			$this->get_exif();
		}

		$this->tools->set_image_options(phpbb_gallery_config::get('max_filesize'), phpbb_gallery_config::get('max_height'), phpbb_gallery_config::get('max_width'));
		$this->tools->set_image_data($this->file->destination_file, '', $this->file->filesize);


		// Rotate the image
		if (phpbb_gallery_config::get('allow_rotate') && $this->get_rotating())
		{
			$this->tools->rotate_image($this->get_rotating(), phpbb_gallery_config::get('allow_resize'));
			if ($this->tools->rotated)
			{
				$this->file->height = $this->tools->image_size['height'];
				$this->file->width = $this->tools->image_size['width'];
			}
		}

		// Resize overside images
		if (($this->file->width > phpbb_gallery_config::get('max_width')) || ($this->file->height > phpbb_gallery_config::get('max_height')))
		{
			if (phpbb_gallery_config::get('allow_resize'))
			{
				$this->tools->resize_image(phpbb_gallery_config::get('max_width'), phpbb_gallery_config::get('max_height'));
				if ($this->tools->resized)
				{
					$this->file->height = $this->tools->image_size['height'];
					$this->file->width = $this->tools->image_size['width'];
				}
			}
			else
			{
				global $user;

				$this->file->remove();
				$this->new_error($user->lang('UPLOAD_ERROR', $this->file->uploadname, $user->lang['UPLOAD_IMAGE_SIZE_TOO_BIG']));
				return false;
			}
		}

		if ($this->file->filesize > (1.2 * phpbb_gallery_config::get('max_filesize')))
		{
			global $user;

			$this->file->remove();
			$this->new_error($user->lang('UPLOAD_ERROR', $image_file->uploadname, $user->lang['BAD_UPLOAD_FILE_SIZE']));
			return false;
		}

		// Everything okay, now add the file to the database and return the image_id
		return $this->file_to_database();
	}

	/**
	* Insert the file into the database
	*/
	public function file_to_database()
	{
		global $user, $db;

		$image_name = str_replace("_", " ", utf8_substr($this->file->uploadname, 0, utf8_strrpos($this->file->uploadname, '.')));

		$sql_ary = array(
			'image_name'			=> $image_name,
			'image_name_clean'		=> utf8_clean_string($image_name),
			'image_filename' 		=> $this->file->realname,
			'filesize_upload'		=> $this->file->filesize,
			'image_time'			=> time() + $this->file_count,
			'image_exif_data'		=> $this->exif_data,
			'image_has_exif'		=> $this->exif_status,

			'image_user_id'			=> $user->data['user_id'],
			'image_user_colour'		=> $user->data['user_colour'],
			'image_username'		=> $user->data['username'],
			'image_username_clean'	=> utf8_clean_string($user->data['username']),
			'image_user_ip'			=> $user->ip,

			'image_album_id'		=> $this->album_id,
			'image_status'			=> phpbb_gallery_image::STATUS_UPLOAD,
			'image_contest'			=> phpbb_gallery_image::NO_CONTEST,
			'image_allow_comments'	=> true,
			'image_desc'			=> '',
			'image_desc_uid'		=> '',
			'image_desc_bitfield'	=> '',
		);

		$sql = 'INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		return (int) $db->sql_nextid();
	}

	/**
	*
	*/
	public function new_error($error_msg)
	{
		$this->errors[] = $error_msg;
	}

	public function set_file_limit($num_files)
	{
		$this->file_limit = (int) $num_files;
	}

	public function set_rotating($data)
	{
		$this->file_rotating = array_map('intval', $data);
	}

	public function get_rotating()
	{
		return $this->file_rotating[$this->file_count];
	}

	public function get_exif()
	{
		// Read exif data from file
		$exif = new phpbb_gallery_exif($this->file->destination_file);
		$exif->read();
		$this->exif_status = $exif->status;
		$this->exif_data = $exif->serialized;
		unset($exif);
	}

	/**
	* Get an array of allowed file types or file extensions
	*/
	static public function get_allowed_types($get_types = false)
	{
		global $user;

		$extensions = $types = array();
		if (phpbb_gallery_config::get('allow_jpg'))
		{
			$types[] = $user->lang['FILETYPES_JPG'];
			$extensions[] = 'jpg';
			$extensions[] = 'jpeg';
		}
		if (phpbb_gallery_config::get('allow_gif'))
		{
			$types[] = $user->lang['FILETYPES_GIF'];
			$extensions[] = 'gif';
		}
		if (phpbb_gallery_config::get('allow_png'))
		{
			$types[] = $user->lang['FILETYPES_PNG'];
			$extensions[] = 'png';
		}

		return ($get_types) ? $types : $extensions;
	}
}
