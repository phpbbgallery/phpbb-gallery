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

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin(false);
$auth->acl($user->data);
$user->setup('mods/gallery');

// Get general album information
include($phpbb_root_path . $gallery_root_path . 'includes/common.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/permissions.' . $phpEx);

/**
* Check whether the requested image & album exit.
*/
$image_id = request_var('image_id', 0);
$image_data = get_image_info($image_id);

$album_id = $image_data['image_album_id'];
$album_data = get_album_info($album_id);

$user_id = $image_data['image_user_id'];

$image_filetype = utf8_substr($image_data['image_filename'], strlen($image_data['image_filename']) - 4, 4);
if (!file_exists($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['image_filename']))
{
	$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
		SET image_filemissing = 1
		WHERE image_id = ' . $image_id;
	$db->sql_query($sql);
	trigger_error('IMAGE_NOT_EXIST');
}

/**
* Check permissions and hotlinking
*/
if ((!gallery_acl_check('i_view', $album_id)) || (!gallery_acl_check('m_status', $album_id) && ($image_data['image_status'] != 1)))
{
	trigger_error('NOT_AUTHORISED');
}

// Hotlink prevention
if ($gallery_config['hotlink_prevent'] && isset($HTTP_SERVER_VARS['HTTP_REFERER']))
{
	$check_referer = trim($HTTP_SERVER_VARS['HTTP_REFERER']);
	if (substr($check_referer, 0, 7) == 'http://')
	{
		$check_referer = substr($check_referer, 7);
	}
	else if (substr($check_referer, 0, 8) == 'https://')
	{
		$check_referer = substr($check_referer, 8);
	}
	if (strpos($check_referer, '/'))
	{
		$check_referer = substr($check_referer, 0, strpos($check_referer, '/'));
	}
	if (substr_count($check_referer, '.') == 2)
	{
		$check_referer = substr($check_referer, (strpos($check_referer, '.') + 1));
	}

	$good_referers = array($config['server_name']);
	if ($gallery_config['hotlink_allowed'] != '')
	{
		$good_referers = array_merge($good_referers, explode(',', $gallery_config['hotlink_allowed']));
	}

	if (!in_array($check_referer, $good_referers))
	{
		trigger_error('NOT_AUTHORISED');
	}
}


/**
* Main work here...
*/
$mode = request_var('mode', '');
switch ($mode)
{
	case 'medium':
		$image_source = $phpbb_root_path . GALLERY_MEDIUM_PATH  . $image_data['image_filename'];
		$possible_watermark = true;
	break;
	case 'thumbnail':
		$image_source = $phpbb_root_path . GALLERY_CACHE_PATH  . $image_data['image_filename'];
		$possible_watermark = false;
	break;
	default:
		$image_source = $phpbb_root_path . GALLERY_UPLOAD_PATH  . $image_data['image_filename'];
		$possible_watermark = true;
	break;
}

/**
* Generate the sourcefile, if it's missing
*/
if (($mode == 'medium') || ($mode == 'thumbnail'))
{
	if ($mode == 'thumbnail')
	{
		$resize_width = $gallery_config['thumbnail_size'];
		$resize_height = $gallery_config['thumbnail_size'];
		$thumbnail = true;
	}
	else
	{
		$resize_width = $gallery_config['preview_rsz_width'];
		$resize_height = $gallery_config['preview_rsz_height'];
		$thumbnail = false;
	}
	if (!file_exists($image_source))
	{
		// Regenerate and write to images/$mode/
		switch (utf8_substr($image_source, utf8_strlen($image_source) - 4, 4))
		{
			case '.png':
				$create_function = 'imagecreatefrompng';
			break;
			case '.gif':
				$create_function = 'imagecreatefromgif';
			break;
			default:
				$create_function = 'imagecreatefromjpeg';
			break;
		}
		$image_file = $create_function($phpbb_root_path . GALLERY_UPLOAD_PATH  . $image_data['image_filename']);

		// Make them transparent again
		imagealphablending($image_file, true);
		imagesavealpha($image_file, true);

		$image_size = getimagesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['image_filename']);
		$image_width = $image_size[0];
		$image_height = $image_size[1];
		$thumb_file = $image_file;

		if (($image_width > $resize_width) || ($image_height > $resize_height))
		{
			// Resize it
			if (($image_width / $resize_width) > ($image_height / $resize_height))
			{
				$thumbnail_height =$resize_height * (($image_height / $resize_height) / ($image_width / $resize_width));
				$thumbnail_width = $resize_width;
			}
			else
			{
				$thumbnail_height = $resize_height;
				$thumbnail_width = $resize_width * (($image_width / $resize_width) / ($image_height / $resize_height));
			}

			// Create thumbnail + constant (THUMBNAIL_INFO_HEIGHT) Pixel extra for imagesize text 
			// Create image details credits to Dr.Death
			if ($gallery_config['thumbnail_info_line'] && $thumbnail)
			{
				$thumb_file = ($gallery_config['gd_version'] == GDLIB1) ? @imagecreate($thumbnail_width, $thumbnail_height + THUMBNAIL_INFO_HEIGHT) : @imagecreatetruecolor($thumbnail_width, $thumbnail_height + THUMBNAIL_INFO_HEIGHT); 
			}
			else
			{
				$thumb_file = ($gallery_config['gd_version'] == GDLIB1) ? @imagecreate($thumbnail_width, $thumbnail_height) : @imagecreatetruecolor($thumbnail_width, $thumbnail_height);
			}
			$resize_function = ($gallery_config['gd_version'] == GDLIB1) ? 'imagecopyresized' : 'imagecopyresampled';
			@$resize_function($thumb_file, $image_file, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $image_width, $image_height);

			if ($gallery_config['thumbnail_info_line'] && $thumbnail)
			{
				$dimension_font = 1;
				$dimension_filesize = filesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['image_filename']);
				$dimension_string = $image_width . "x" . $image_height . "(" . intval($dimension_filesize / 1024) . "KiB)";
				$dimension_colour = ImageColorAllocate($thumb_file, 255, 255, 255);
				$dimension_height = imagefontheight($dimension_font);
				$dimension_width = imagefontwidth($dimension_font) * strlen($dimension_string);
				$dimension_x = ($thumbnail_width - $dimension_width) / 2;
				$dimension_y = $thumbnail_height + ((THUMBNAIL_INFO_HEIGHT - $dimension_height) / 2);
				imagestring($thumb_file, 1, $dimension_x, $dimension_y, $dimension_string, $dimension_colour);
			}
		}

		$save_file = (($mode == 'thumbnail') && $gallery_config['thumbnail_cache']) ? true : (($mode == 'medium') && $gallery_config['medium_cache']) ? true : false;
		$wirte_source = '';
		if ($save_file)
		{
			$wirte_source = $phpbb_root_path . (($mode == 'thumbnail') ? GALLERY_CACHE_PATH : GALLERY_MEDIUM_PATH) . $image_data['image_filename'];
		}
		switch ($image_filetype)
		{
			case '.jpg':
				if (!$save_file)
				{
					header('Content-type: image/jpeg');
				}
				@imagejpeg($thumb_file, $wirte_source, (($thumbnail) ? $gallery_config['thumbnail_quality'] : 100));
			break;
			case '.gif':
				if (!$save_file)
				{
					header('Content-type: image/gif');
				}
				@imagegif($thumb_file, $wirte_source);
			break;
			case '.png':
				if (!$save_file)
				{
					header('Content-type: image/png');
				}
				@imagepng($thumb_file, $wirte_source);
			break;
		}
		@chmod($image_source, 0777);
		if (!$save_file)
		{
			exit;
		}
		else
		{
			if ($mode == 'thumbnail')
			{
				$sql_ary = array('filesize_cache' => @filesize($wirte_source));
			}
			else
			{
				$sql_ary = array('filesize_medium' => @filesize($wirte_source));
			}
			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE ' . $db->sql_in_set('image_id', $image_id);
			$db->sql_query($sql);
		}
	}
}

/**
* Watermark
*/
$file_size = getimagesize($image_source);
if (!gallery_acl_check('i_watermark', $album_id) && $possible_watermark && $gallery_config['watermark_images'] &&
	file_exists($phpbb_root_path . $gallery_config['watermark_source']) &&
	($gallery_config['watermark_height'] < $file_size[0]) && ($gallery_config['watermark_width'] < $file_size[1]))
{
	$watermark_source = $phpbb_root_path . $gallery_config['watermark_source'];
	switch (substr($watermark_source, (strlen($watermark_source) - 4), 4))
	{
		case '.png':
			$watermark_file = imagecreatefrompng($watermark_source);
		break;
		case '.gif':
			$watermark_file = imagecreatefromgif($watermark_source);
		break;
		default:
			$watermark_file = imagecreatefromjpeg($watermark_source);
		break;
	}

	$watermark_x = imagesx($watermark_file);
	$watermark_y = imagesy($watermark_file);

	switch (utf8_substr($image_source, utf8_strlen($image_source) - 4, 4))
	{
		case '.png':
			$image_file = imagecreatefrompng($image_source);
		break;
		case '.gif':
			$image_file = imagecreatefromgif($image_source);
		break;
		default:
			$image_file = imagecreatefromjpeg($image_source);
		break;
	}

	$image_x = imagesx($image_file);
	$image_y = imagesy($image_file);
	imagecopy($image_file, $watermark_file, (($image_x * 0.5) - ($watermark_x * 0.5)), ($image_y - $watermark_y - 5), 0, 0, $watermark_x, $watermark_y);

	switch ($image_filetype)
	{
		case '.png':
		case '.gif':
			header('Content-type: image/png');
			imagepng($image_file);
		break;

		default:
			header('Content-type: image/jpeg');
			imagejpeg($image_file);
		break;
	}
}
else
{
	/**
	* Get a browser friendly UTF-8 encoded filename
	*
	* borrowed from phpBB3
	* @author: phpBB Group
	* @function: header_filename
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
	header('Content-Disposition: inline; ' . header_filename(htmlspecialchars_decode($image_data['image_name'])));

	switch ($image_filetype)
	{
		case '.png':
			header('Content-type: image/png');
		break;

		case '.gif':
			header('Content-type: image/gif');
		break;

		case '.jpg':
		case '.JPG':
			case 'jpeg':
			header('Content-type: image/jpeg');
		break;
	}

	if (@readfile($image_source) == false)
	{
		$fp = @fopen($image_source, 'rb');
			if ($fp !== false)
			{
				while (!feof($fp))
				{
					echo fread($fp, 8192);
				}
				fclose($fp);
			}
	}
	flush();
}

?>