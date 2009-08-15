<?php
/**
*
* @package NV Image Tools
* @version $Id$
* @copyright (c) 2009 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org/
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

/**
* A little class for all the actions that the gallery does on images.
*
* resize, rotate, watermark, read exif, create thumbnail, write to hdd, send to browser
*/
class nv_image_tools
{
	var $chmod = 0777;

	var $errors = array();

	var $exif_data = array();
	var $exif_data_exist = 2;
	var $exif_data_serialized = '';
	var $exif_data_force_db = false;

	var $gd_version = 0;

	var $image;
	var $image_content_type;
	var $image_name = '';
	var $image_quality = 100;
	var $image_size = array();
	var $image_source = '';
	var $image_type;

	var $max_file_size = 0;
	var $max_height = 0;
	var $max_width = 0;

	var $resized = false;
	var $rotated = false;

	var $thumb_height = 0;
	var $thumb_width = 0;

	var $watermark;
	var $watermark_size = array();
	var $watermark_source = '';
	var $watermarked = false;

	/**
	* Constructor - init some basic stuff
	* PHP5: function __constructor()
	*/
	function nv_image_tools($gd_version = 0)
	{
		if (!defined('EXIF_UNAVAILABLE'))
		{
			define('EXIF_UNAVAILABLE', 0);
			define('EXIF_AVAILABLE', 1);
			define('EXIF_UNKNOWN', 2);
			define('EXIF_DBSAVED', 3);
			define('EXIFTIME_OFFSET', 0); // Use this constant, to change the exif-timestamp. Offset in seconds
		}
		if (!defined('WATERMARK_TOP'))
		{
			define('WATERMARK_TOP', 1);
			define('WATERMARK_MIDDLE', 2);
			define('WATERMARK_BOTTOM', 4);
			define('WATERMARK_LEFT', 8);
			define('WATERMARK_CENTER', 16);
			define('WATERMARK_RIGHT', 32);
		}
		if (!defined('GDLIB1'))
		{
			define('GDLIB1', 1);
			define('GDLIB2', 2);
		}

		if ($gd_version)
		{
			$this->gd_version = $gd_version;
		}
	}

	function set_image_options($max_file_size, $max_height, $max_width)
	{
		$this->max_file_size = $max_file_size;
		$this->max_height = $max_height;
		$this->max_width = $max_width;
	}

	function set_image_data($source = '', $name = '', $size = 0)
	{
		if ($source)
		{
			$this->image_source = $source;
		}
		if ($name)
		{
			$this->image_name = $name;
		}
		if ($size)
		{
			$this->image_size['file'] = $size;
		}
	}

	/**
	* Read image
	*/
	function read_image($force_filesize = false)
	{
		if (!file_exists($this->image_source))
		{
			return false;
		}

		switch (utf8_substr(strtolower($this->image_source), -4))
		{
			case '.png':
				$this->image = imagecreatefrompng($this->image_source);
				$this->image_type = 'png';
			break;
			case '.gif':
				$this->image = imagecreatefromgif($this->image_source);
				$this->image_type = 'gif';
			break;
			default:
				$this->image = imagecreatefromjpeg($this->image_source);
				$this->image_type = 'jpeg';
			break;
		}

		$file_size = 0;
		if (isset($this->image_size['file']))
		{
			$file_size = $this->image_size['file'];
		}
		else if ($force_filesize)
		{
			$file_size = @filesize($this->image_source);
		}

		$image_size = getimagesize($this->image_source);

		$this->image_size['file'] = $file_size;
		$this->image_size['width'] = $image_size[0];
		$this->image_size['height'] = $image_size[1];

		$this->image_content_type = $image_size['mime'];
	}

	/**
	* Write image to disk
	*/
	function write_image($destination, $quality = 100, $destroy_image = false)
	{
		switch ($this->image_type)
		{
			case 'jpeg':
				@imagejpeg($this->image, $destination, $quality);
			break;
			case 'png':
				@imagepng($this->image, $destination);
			break;
			case 'gif':
				@imagegif($this->image, $destination);
			break;
		}
		@chmod($destination, $this->chmod);

		if ($destroy_image)
		{
			imagedestroy($this->image);
		}
	}

	/**
	* Sending the image to the browser.
	* Mostly copied from phpBB::download/file.php
	*/
	function send_image_to_browser($content_length = 0)
	{
		global $db, $user;

		header('Pragma: public');
		$is_ie8 = (strpos(strtolower($user->browser), 'msie 8.0') !== false);
		header('Content-Type: ' . $this->image_content_type);

		if ($is_ie8)
		{
			header('X-Content-Type-Options: nosniff');
		}

		/**
		* Get a browser friendly UTF-8 encoded filename
		*/
		function header_filename($file)
		{
			$user_agent = (!empty($_SERVER['HTTP_USER_AGENT'])) ? htmlspecialchars((string) $_SERVER['HTTP_USER_AGENT']) : '';

			// There be dragons here.
			// Not many follows the RFC...
			if (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Safari') !== false || strpos($user_agent, 'Konqueror') !== false)
			{
				return "filename=" . rawurlencode($file);
			}

			// follow the RFC for extended filename for the rest
			return "filename*=UTF-8''" . rawurlencode($file);
		}

		if (empty($user->browser) || (!$is_ie8 && (strpos(strtolower($user->browser), 'msie') !== false)))
		{
			header('Content-Disposition: attachment; ' . header_filename(htmlspecialchars_decode($this->image_name)));
			if (empty($user->browser) || (strpos(strtolower($user->browser), 'msie 6.0') !== false))
			{
				header('expires: -1');
			}
		}
		else
		{
			header('Content-Disposition: inline; ' . header_filename(htmlspecialchars_decode($this->image_name)));
		}

		if ($content_length)
		{
			header('Content-Length: ' . $content_length);
		}

		$db->sql_close();

		if ($this->image)
		{
			$image_function = 'image' . $this->image_type;
			$image_function($this->image);
		}
		else
		{
			// Try to deliver in chunks
			@set_time_limit(0);

			$fp = @fopen($this->image_source, 'rb');

			if ($fp !== false)
			{
				while (!feof($fp))
				{
					echo fread($fp, 8192);
				}
				fclose($fp);
			}
			else
			{
				@readfile($this->image_source);
			}

			flush();
		}
	}

	function create_thumbnail($max_width, $max_height, $print_details = false, $additional_height = 0, $image_size = array())
	{
		$this->resize_image($max_width, $max_height, (($print_details) ? $additional_height : 0));

		// Create image details credits to Dr.Death
		if ($print_details && sizeof($image_size))
		{
			$dimension_font = 1;
			$dimension_string = $image_size['width'] . "x" . $image_size['height'] . "(" . intval($image_size['file'] / 1024) . "KiB)";
			$dimension_colour = imagecolorallocate($this->image, 255, 255, 255);
			$dimension_height = imagefontheight($dimension_font);
			$dimension_width = imagefontwidth($dimension_font) * strlen($dimension_string);
			$dimension_x = ($this->image_size['width'] - $dimension_width) / 2;
			$dimension_y = $this->image_size['height'] + (($additional_height - $dimension_height) / 2);
			imagestring($this->image, 1, $dimension_x, $dimension_y, $dimension_string, $dimension_colour);
		}
	}

	function resize_image($max_width, $max_height, $additional_height = 0)
	{
		if (!$this->image)
		{
			$this->read_image();
		}

		if (($this->image_size['height'] <= $max_height) && ($this->image_size['width'] <= $max_width))
		{
			// image is small enough, nothing to do here.
			return;
		}

		if (($this->image_size['height'] / $max_height) > ($this->image_size['width'] / $max_width))
		{
			$this->thumb_height	= $max_height;
			$this->thumb_width	= round($max_width * (($this->image_size['width'] / $max_width) / ($this->image_size['height'] / $max_height)));
		}
		else
		{
			$this->thumb_height	= round($max_height * (($this->image_size['height'] / $max_height) / ($this->image_size['width'] / $max_width)));
			$this->thumb_width	= $max_width;
		}

		$image_copy = (($this->gd_version == GDLIB1) ? @imagecreate($this->thumb_width, $this->thumb_height + $additional_height) : @imagecreatetruecolor($this->thumb_width, $this->thumb_height + $additional_height));
		$resize_function = ($this->gd_version == GDLIB1) ? 'imagecopyresized' : 'imagecopyresampled';
		$resize_function($image_copy, $this->image, 0, 0, 0, 0, $this->thumb_width, $this->thumb_height, $this->image_size['width'], $this->image_size['height']);

		imagealphablending($image_copy, true);
		imagesavealpha($image_copy, true);
		$this->image = $image_copy;

		$this->image_size['height'] = $this->thumb_height;
		$this->image_size['width'] = $this->thumb_width;

		$this->resized = true;
		// We loose the exif data, so force to store them in the database
		$this->exif_data_force_db = true;
	}

	/**
	* Rotate the image
	* Usage optimized for 0°, 90°, 180° and 270° because of the height and width
	*/
	function rotate_image($angle, $ignore_dimensions)
	{
		if (($angle <= 0) || (($angle % 90) != 0))
		{
			$this->errors[] = array('ROTATE_IMAGE_ANGLE', $angle);
			return;
		}

		if (!$this->image)
		{
			$this->read_image();
		}

		if ((($angle / 90) % 2) == 1)
		{
			// Left or Right, we need to switch the height and width
			if (!$ignore_dimensions && (($this->image_size['height'] > $this->max_width) || ($this->image_size['width'] > $this->max_height)))
			{
				// image would be to wide/high
				if ($this->image_size['height'] > $this->max_width)
				{
					$this->errors[] = array('ROTATE_IMAGE_WIDTH');
				}
				if ($this->image_size['width'] > $this->max_height)
				{
					$this->errors[] = array('ROTATE_IMAGE_HEIGHT');
				}
				return;
			}
			$new_width = $this->image_size['height'];
			$this->image_size['height'] = $this->image_size['width'];
			$this->image_size['width'] = $new_width;
		}

		$this->image = imagerotate($this->image, $angle, 0);

		$this->rotated = true;
		// We loose the exif data, so force to store them in the database
		$this->exif_data_force_db = true;
	}

	/**
	* Watermark the image:
	*
	* @param int $watermark_position summary of the parameters for vertical and horizontal adjustment
	*/
	function watermark_image($watermark_source, $watermark_position = 20, $min_height = 0, $min_width = 0)
	{
		$this->watermark_source = $watermark_source;
		if (!$this->watermark_source)
		{
			$this->errors[] = array('WATERMARK_IMAGE_SOURCE');
			return;
		}

		if (!$this->image)
		{
			$this->read_image();
		}

		if (($min_height && ($this->image_size['height'] < $min_height)) || ($min_width && ($this->image_size['width'] < $min_width)))
		{
			return;
			//$this->errors[] = array('WATERMARK_IMAGE_DIMENSION');
		}

		$this->watermark_size = getimagesize($this->watermark_source);
		switch ($this->watermark_size['mime'])
		{
			case 'image/png':
				$imagecreate = 'imagecreatefrompng';
			break;
			case 'image/gif':
				$imagecreate = 'imagecreatefromgif';
			break;
			default:
				$imagecreate = 'imagecreatefromjpeg';
			break;
		}

		// Get the watermark as resource.
		if (($this->watermark = $imagecreate($this->watermark_source)) === false)
		{
			$this->errors[] = array('WATERMARK_IMAGE_IMAGECREATE');
		}

		// Where do we display the watermark? up-left, down-right, ...?
		$dst_x = (($this->image_size['width'] * 0.5) - ($this->watermark_size[0] * 0.5));
		$dst_y = ($this->image_size['height'] - $this->watermark_size[1] - 5);
		if ($watermark_position & WATERMARK_LEFT)
		{
			$dst_x = 5;
		}
		elseif ($watermark_position & WATERMARK_RIGHT)
		{
			$dst_x = ($this->image_size['height'] - $this->watermark_size[1] - 5);
		}
		if ($watermark_position & WATERMARK_TOP)
		{
			$dst_y = 5;
		}
		elseif ($watermark_position & WATERMARK_MIDDLE)
		{
			$dst_y = (($this->image_size['width'] * 0.5) - ($this->watermark_size[0] * 0.5));
		}
		imagecopy($this->image, $this->watermark, $dst_x, $dst_y, 0, 0, $this->watermark_size[0], $this->watermark_size[1]);
		imagedestroy($this->watermark);

		$this->watermarked = true;
	}

	/**
	* Read exif data from the image
	*/
	function read_exif_data()
	{
		if (!function_exists('exif_read_data') || !$this->image_source)
		{
			return;
		}

		$this->exif_data = @exif_read_data($this->image_source, 0, true);

		if (!empty($this->exif_data["EXIF"]))
		{
			// Unset invalid exifs
			foreach ($this->exif_data as $key => $array)
			{
				if (!in_array($key, array('EXIF', 'IFD0')))
				{
					unset($this->exif_data[$key]);
				}
				else
				{
					foreach ($this->exif_data[$key] as $subkey => $array)
					{
						if (!in_array($subkey, array('DateTimeOriginal', 'FocalLength', 'ExposureTime', 'FNumber', 'ISOSpeedRatings', 'WhiteBalance', 'Flash', 'Model', 'ExposureProgram', 'ExposureBiasValue', 'MeteringMode')))
						{
							unset($this->exif_data[$key][$subkey]);
						}
					}
				}
			}

			$this->exif_data_serialized = serialize($this->exif_data);
			$this->exif_data_exist = EXIF_DBSAVED;
		}
		else
		{
			$this->exif_data_exist = EXIF_UNAVAILABLE;
		}
	}
}

?>