<?php

/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$album_root_path = $phpbb_root_path . 'gallery/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($album_root_path . 'includes/common.'.$phpEx);
include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');
/**
* Get the current Category Info
*/
$album_id = request_var('album_id', 0);
$album_data = get_album_info($album_id);
if ($album_data['album_type'] != 2)
{//Go Home Cheaters
	trigger_error('ALBUM_IS_CATEGORY');
}
if (empty($album_data))
{
	trigger_error('ALBUM_NOT_EXIST');
}
/**
* Check the permissions
*/
if (!$album_data['album_user_id'])
{
	$album_user_access = album_user_access($album_id, $album_data, 0, 1, 0, 0, 0, 0);// UPLOAD
}
else
{
	$album_user_access['upload'] = ($album_data['album_user_id'] == $user->data['user_id']) ? true : false;
	$album_user_access['moderator'] = false;
}
if (!$album_user_access['upload'])
{
	if ($user->data['is_bot'])
	{
		redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
	}
	if (!$user->data['is_registered'])
	{
		login_box("gallery/upload.$phpEx?album_id=$album_id", $user->lang['LOGIN_EXPLAIN_UPLOAD']);
	}
	else
	{
		trigger_error('NOT_AUTHORISED');
	}
}

/**
* Upload Quota Check
*/
if ($album_id <> PERSONAL_GALLERY)
{
	/**
	* Check Album Configuration Quota
	*/
	if ($album_config['max_pics'] >= 0)
	{//do we have enough images in this album?
		if ($album_data['count'] >= $album_config['max_pics'])
		{
			trigger_error('ALBUM_REACHED_QUOTA');
		}
	}
	/**
	* Check User Limit
	*/
	$check_user_limit = false;
	if (($user->data['user_type'] <> USER_FOUNDER) && $user->data['is_registered'] && !$user->data['is_bot'])
	{
		if (($album_user_access['moderator']) && ($album_config['mod_pics_limit'] >= 0))
		{
			$check_user_limit = 'mod_pics_limit';
		}
		else if ($album_config['user_pics_limit'] >= 0)
		{
			$check_user_limit = 'user_pics_limit';
		}
	}
	if ($check_user_limit)
	{
		$sql = 'SELECT COUNT(image_id) AS count
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_user_id = ' . $user->data['user_id'] . '
				AND image_album_id = ' . $album_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$own_pics = $row['count'];
		if ($own_pics >= $album_config[$check_user_limit])
		{
			trigger_error($user->lang['USER_REACHED_QUOTA'], E_USER_WARNING);
		}
	}
}
else
{
	if (($album_data['count'] >= $album_config['personal_gallery_limit']) && ($album_config['personal_gallery_limit'] >= 0))
	{
		trigger_error($user->lang['ALBUM_REACHED_QUOTA'], E_USER_WARNING);
	}
}

// ------------------------------------
// Salting the form...yumyum ...
// ------------------------------------
add_form_key('upload');


/**
* Main work here...
*/
$submit = (isset($_POST['submit'])) ? true : false;
if(!$submit)
{
	$template->assign_vars(array(
		'U_VIEW_CAT'				=> ($album_id != PERSONAL_GALLERY) ? append_sid("album.$phpEx?id=$album_id") : append_sid("album_personal.$phpEx"),
		'CAT_TITLE'					=> $album_data['album_name'],
		'S_PIC_DESC_MAX_LENGTH'		=> $album_config['desc_length'],
		'S_MAX_FILESIZE'			=> $album_config['max_file_size'],
		'S_MAX_WIDTH'				=> $album_config['max_width'],
		'S_MAX_HEIGHT'				=> $album_config['max_height'],

		'S_JPG'					=> ($album_config['jpg_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],
		'S_PNG'					=> ($album_config['png_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],
		'S_GIF'					=> ($album_config['gif_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],
		'S_THUMBNAIL_SIZE'		=> $album_config['thumbnail_size'],
		'S_THUMBNAIL'			=> ($album_config['gd_version']) ? true : false,
		'S_MULTI_IMAGES'		=> ($album_config['upload_images'] > 1) ? true : false,

		'S_ALBUM_ACTION' 			=> append_sid("upload.$phpEx?album_id=$album_id"),
	));

	$count = 0;
	while($count < $album_config['upload_images'])
	{
		$template->assign_block_vars('upload_image', array());
		$count++;
	}

	if ($album_config['gd_version'] == 0)
	{
		$template->assign_block_vars('switch_manual_thumbnail', array());
	}
	generate_album_nav($album_data);

	// Output page
	$page_title = $user->lang['UPLOAD_IMAGE'];
	page_header($page_title);
	$template->set_filenames(array(
		'body' => 'gallery_upload_body.html',
	));
	page_footer();

}
else
{
	// Check the salt... yumyum
	if (!check_form_key('upload'))
	{
		trigger_error('FORM_INVALID');
	}

	/**
	* Get File Upload Info
	*/
	$loop = request_var('image_num', 0);
	$loop = ($loop != 0) ? $loop - 1 : $loop;
	foreach ($_FILES['image']['type'] as $i => $type)
	{
		$image_data = array();

		$image_data['image_type']	= $_FILES['image']['type'][$i];
		$image_data['image_size']	= $_FILES['image']['size'][$i];
		$image_data['image_tmp']	= $_FILES['image']['tmp_name'][$i];
		if ($image_data['image_size'])
		{
			$loop = $loop + 1;
			if ($album_config['gd_version'] == 0)
			{
				$image_data['thumbnail_type']	= $_FILES['thumbnail']['type'][$i];
				$image_data['thumbnail_size']	= $_FILES['thumbnail']['size'][$i];
				$image_data['thumbnail_tmp']	= $_FILES['thumbnail']['tmp_name'][$i];
			}
			if (
				((!$image_data['image_size']) || ($image_data['image_size'] > $album_config['max_file_size']))
				||
				(($album_config['gd_version'] == 0) && (!$image_data['thumbnail_size'] || ($image_data['thumbnail_size'] > $album_config['max_file_size'])))
			)
			{
				trigger_error('BAD_UPLOAD_FILE_SIZE');
			}
			switch ($image_data['image_type'])
			{
				case 'image/jpeg':
				case 'image/jpg':
				case 'image/pjpeg':
					if (!$album_config['jpg_allowed']) {
						trigger_error('NOT_ALLOWED_FILE_TYPE');
					}
					$image_data['image_type2'] = '.jpg';
				break;
				case 'image/png':
				case 'image/x-png':
					if (!$album_config['png_allowed']) {
						trigger_error('NOT_ALLOWED_FILE_TYPE');
					}
					$image_data['image_type2'] = '.png';
				break;
				case 'image/gif':
					if (!$album_config['gif_allowed']) {
						trigger_error('NOT_ALLOWED_FILE_TYPE');
					}
					$image_data['image_type2'] = '.gif';
				break;
				default:
					trigger_error('NOT_ALLOWED_FILE_TYPE');
				break;
			}
			if ($album_config['gd_version'] == 0)
			{
				if ($image_data['image_type'] <> $image_data['thumbnail_type'])
				{
					trigger_error('FILETYPE_AND_THUMBTYPE_DO_NOT_MATCH');
				}
			}
			$image_data_2 = array(
				'filename'			=> '',
				'image_album_id'	=> $album_data['album_id'],
				'image_album_name'	=> $album_data['album_name'],
				'image_approval'	=> (!$album_data['album_approval']) ? 1 : 0,
				'image_desc'		=> str_replace('{NUM}', $loop, request_var('image_desc', '', true)),
				'image_name'		=> str_replace('{NUM}', $loop, request_var('image_name', '', true)),
				'image_time'		=> time() + $loop,
				'thumbnail'			=> '',
				'username'			=> request_var('username', $user->data['username']),
			);
			$image_data = array_merge($image_data, $image_data_2);

			if(!$image_data['image_name'])
			{
				trigger_error('MISSING_IMAGE_TITLE');
			}
			if (!$user->data['is_registered'] && $image_data['username'])
			{
				include_once("{$phpbb_root_path}includes/functions_user.$phpEx");
				$result = validate_username($image_data['username']);
				if ($result['error'])
				{
					trigger_error($result['error_msg']);
				}
			}

			/**
			* Generate filename and upload
			*/
			srand((double)microtime()*1000000);// for older than version 4.2.0 of PHP
			do
			{
				$image_data['filename'] = md5(uniqid(rand())) . $image_data['image_type2'];
			}
			while(file_exists(GALLERY_UPLOAD_PATH . $image_data['filename']));
			if ($album_config['gd_version'] == 0)
			{
				$image_data['thumbnail'] = $image_data['filename'];
			}

			$ini_val = ( @phpversion() >= '4.0.0' ) ? 'ini_get' : 'get_cfg_var';
			if (@$ini_val('open_basedir') <> '')
			{
				if (@phpversion() < '4.0.3')
				{
					trigger_error('open_basedir is set and your PHP version does not allow move_uploaded_file<br /><br />Please contact your server admin', E_USER_WARNING);
				}
				$move_file = 'move_uploaded_file';
			}
			else
			{
				$move_file = 'copy';
			}
			$move_file($image_data['image_tmp'], $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
			@chmod($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename'], 0777);
			if ($album_config['gd_version'] == 0)
			{
				$move_file($image_data['thumbnail_tmp'], $phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail']);
				@chmod($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail'], 0777);
			}

			$image_size = getimagesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
			$image_data['width'] = $image_size[0];
			$image_data['height'] = $image_size[1];
			if (($image_data['width'] > $album_config['max_width']) || ($image_data['height'] > $album_config['max_height']))
			{
				@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
				if ($album_config['gd_version'] == 0)
				{
					@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail']);
				}
				trigger_error('UPLOAD_IMAGE_SIZE_TOO_BIG');
			}

			if ($album_config['gd_version'] == 0)
			{
				$thumbnail_size = getimagesize($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail']);
				$image_data['thumbnail_width'] = $thumbnail_size[0];
				$image_data['thumbnail_height'] = $thumbnail_size[1];
				if (($image_data['thumbnail_width'] > $album_config['thumbnail_size']) || ($image_data['thumbnail_height'] > $album_config['thumbnail_size']))
				{
					@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
					@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail']);
					trigger_error('UPLOAD_THUMBNAIL_SIZE_TOO_BIG');
				}
			}

			/**
			* This image is okay, we can cache its thumbnail now
			*/
			if (($album_config['thumbnail_cache']) && ($album_config['gd_version'] > 0)) 
			{
				$gd_errored = FALSE; 
				switch ($image_data['image_type2']) 
				{
					case '.jpg': 
						$read_function = 'imagecreatefromjpeg'; 
					break; 

					case '.png': 
						$read_function = 'imagecreatefrompng'; 
					break; 

					case '.gif': 
						$read_function = 'imagecreatefromgif'; 
					break;
				}

				$src = $read_function($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
				if (!$src)
				{
					$gd_errored = TRUE;
					$image_data['thumbnail'] = '';
				}
				else if (($image_data['width'] > $album_config['thumbnail_size']) || ($image_data['height'] > $album_config['thumbnail_size']))
				{
					// Resize it
					if ($image_data['width'] > $image_data['height'])
					{
						$thumbnail_width	= $album_config['thumbnail_size'];
						$thumbnail_height	= $album_config['thumbnail_size'] * ($image_data['height'] / $image_data['width']);
					}
					else
					{
						$thumbnail_height	= $album_config['thumbnail_size'];
						$thumbnail_width	= $album_config['thumbnail_size'] * ($image_data['width'] / $image_data['height']);
					}

					// Create thumbnail + 16 Pixel extra for imagesize text 
					$thumbnail = ($album_config['gd_version'] == 1) ? @imagecreate($thumbnail_width, $thumbnail_height + 16) : @imagecreatetruecolor($thumbnail_width, $thumbnail_height + 16); 
					$resize_function = ($album_config['gd_version'] == 1) ? 'imagecopyresized' : 'imagecopyresampled';
					@$resize_function($thumbnail, $src, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $image_data['width'], $image_data['height']);

					// Create image details credits to Dr.Death
					$dimension_font = 1;
					$dimension_filesize = filesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
					$dimension_string = $image_data['width'] . "x" . $image_data['height'] . "(" . intval($dimension_filesize/1024) . "KB)";
					$dimension_colour = ImageColorAllocate($thumbnail,255,255,255);
					$dimension_height = imagefontheight($dimension_font);
					$dimension_width = imagefontwidth($dimension_font) * strlen($dimension_string);
					$dimension_x = ($thumbnail_width - $dimension_width) / 2;
					$dimension_y = $thumbnail_height + ((16 - $dimension_height) / 2);
					imagestring($thumbnail, 1, $dimension_x, $dimension_y, $dimension_string, $dimension_colour);

				}
				else
				{
					$thumbnail = $src;
				}
				if (!$gd_errored)
				{
					$image_data['thumbnail'] = $image_data['filename'];
					// Write to disk
					switch ($image_data['image_type2'])
					{
						case '.jpg':
							@imagejpeg($thumbnail, $phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail'], $album_config['thumbnail_quality']);
						break;

						case '.png':
							@imagepng($thumbnail, $phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail']);
						break;

						case '.gif':
							@imagegif($thumbnail, $phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail']);
						break;
					}
					@chmod($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail'], 0777);
				}
			}
			else if ($album_config['gd_version'] > 0)
			{
				$image_data['thumbnail'] = '';
			}

			upload_image($image_data);
		}
	}
	/**
	* Complete... now send a message to user
	*/

	if (!$album_data['album_approval'])
	{
		$message = $user->lang['ALBUM_UPLOAD_SUCCESSFUL'];
	}
	else
	{
		$message = $user->lang['ALBUM_UPLOAD_NEED_APPROVAL'];
	}

	if ($album_id <> PERSONAL_GALLERY)
	{
		if (!$album_data['album_approval'])
		{
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid($phpbb_root_path . "gallery/album.$phpEx?id=$album_id") . '">',
			));
		}
		$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid($phpbb_root_path . "gallery/album.$phpEx?id=$album_id") . "\">", "</a>");
	}
	else
	{
		if (!$album_data['album_approval'])
		{
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid($phpbb_root_path . "gallery/album_personal.$phpEx") . '">')
			);
		}
		$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_PERSONAL_ALBUM'], "<a href=\"" . append_sid($phpbb_root_path . "gallery/album_personal.$phpEx") . "\">", "</a>");
	}


	$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid($phpbb_root_path . "gallery/index.$phpEx") . "\">", "</a>");
	trigger_error($message, E_USER_WARNING);
}
function upload_image(&$image_data)
{
	global $user, $db;

	$sql_ary = array(
		'image_filename' 		=> $image_data['filename'],
		'image_thumbnail'		=> $image_data['thumbnail'],
		'image_name'			=> $image_data['image_name'],
		'image_user_id'			=> $user->data['user_id'],
		'image_user_colour'		=> $user->data['user_colour'],
		'image_username'		=> $image_data['username'],
		'image_user_ip'			=> $user->ip,
		'image_time'			=> $image_data['image_time'],
		'image_album_id'		=> $image_data['image_album_id'],
		'image_approval'		=> $image_data['image_approval'],
	);

	$message_parser				= new parse_message();
	$message_parser->message	= utf8_normalize_nfc($image_data['image_desc']);
	if($message_parser->message)
	{
		$message_parser->parse(true, true, true, true, false, true, true, true);
		$sql_ary['image_desc']			= $message_parser->message;
		$sql_ary['image_desc_uid']		= $message_parser->bbcode_uid;
		$sql_ary['image_desc_bitfield']	= $message_parser->bbcode_bitfield;
	}
	else
	{
		$sql_ary['image_desc']			= '';
		$sql_ary['image_desc_uid']		= '';
		$sql_ary['image_desc_bitfield']	= '';
	}

	$db->sql_query('INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
}
?>