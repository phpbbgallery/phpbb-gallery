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

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');
/**
* Get the current Category Info
*/
$album_id = request_var('album_id', 0);
if ($album_id <> PERSONAL_GALLERY)
{
	$album_data = get_album_info($album_id);
	if ($album_data['album_type'] != 2)
	{//Go Home Cheaters
		trigger_error($user->lang['ALBUM_IS_CATEGORY'], E_USER_WARNING);
	}
}
else
{
	$album_data = init_personal_gallery_cat($user->data['user_id']);
}
if (empty($album_data))
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}
/**
* Check the permissions
*/
$album_user_access = album_user_access($album_id, $album_data, 0, 1, 0, 0, 0, 0);// UPLOAD
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
		trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
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
			trigger_error($user->lang['ALBUM_REACHED_QUOTA'], E_USER_WARNING);
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
if(!isset($_POST['pic_title']))
{
	$template->assign_vars(array(
		'U_VIEW_CAT' 				=> ($album_id != PERSONAL_GALLERY) ? append_sid("album.$phpEx?id=$album_id") : append_sid("album_personal.$phpEx"),
		'CAT_TITLE' 				=> $album_data['album_name'],
		'S_PIC_DESC_MAX_LENGTH' 	=> $album_config['desc_length'],
		'S_MAX_FILESIZE' 			=> $album_config['max_file_size'],
		'S_MAX_WIDTH' 				=> $album_config['max_width'],
		'S_MAX_HEIGHT' 				=> $album_config['max_height'],

		'S_JPG' 					=> ($album_config['jpg_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],
		'S_PNG' 					=> ($album_config['png_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],
		'S_GIF' 					=> ($album_config['gif_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],
		'S_THUMBNAIL_SIZE' 			=> $album_config['thumbnail_size'],

		'S_ALBUM_ACTION' 			=> append_sid("upload.$phpEx?album_id=$album_id"),
	));

	if ($album_config['gd_version'] == 0)
	{
		$template->assign_block_vars('switch_manual_thumbnail', array());
	}

	if ($album_id == PERSONAL_GALLERY)
	{
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['PERSONAL_ALBUMS'],
			'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album_personal_index.$phpEx"),
		));

		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> sprintf($user->lang['PERSONAL_ALBUM_OF_USER'], $user->data['username']),
			'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album_personal.$phpEx", 'user_id=' . $user->data['user_id']),
		));
	}
	else
	{
		generate_album_nav($album_data);
	}

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
	$filetype 	= $_FILES['pic_file']['type'];
	$filesize 	= $_FILES['pic_file']['size'];
	$filetmp 	= $_FILES['pic_file']['tmp_name'];
	if ($album_config['gd_version'] == 0)
	{
		$thumbtype 	= $_FILES['pic_thumbnail']['type'];
		$thumbsize 	= $_FILES['pic_thumbnail']['size'];
		$thumbtmp 	= $_FILES['pic_thumbnail']['tmp_name'];
	}
	if ((!$filesize) || ($filesize > $album_config['max_file_size']))
	{
		trigger_error($user->lang['BAD_UPLOAD_FILE_SIZE'], E_USER_WARNING);
	}
	if ($album_config['gd_version'] == 0)
	{
		if (!$thumbsize || ($thumbsize > $album_config['max_file_size']))
		{
			trigger_error($user->lang['BAD_UPLOAD_FILE_SIZE'], E_USER_WARNING);
		}
	}
	switch ($filetype)
	{
		case 'image/jpeg':
		case 'image/jpg':
		case 'image/pjpeg':
			if (!$album_config['jpg_allowed'])
			{
				trigger_error($user->lang['NOT_ALLOWED_FILE_TYPE'], E_USER_WARNING);
			}
			$pic_filetype = '.jpg';
		break;

		case 'image/png':
		case 'image/x-png':
			if (!$album_config['png_allowed'])
			{
				trigger_error($user->lang['NOT_ALLOWED_FILE_TYPE'], E_USER_WARNING);
			}
			$pic_filetype = '.png';
		break;

		case 'image/gif':
			if (!$album_config['gif_allowed'])
			{
				trigger_error($user->lang['NOT_ALLOWED_FILE_TYPE'], E_USER_WARNING);
			}
			$pic_filetype = '.gif';
		break;
		
		default:
			trigger_error($user->lang['NOT_ALLOWED_FILE_TYPE'], E_USER_WARNING);
	}
	if ($album_config['gd_version'] == 0)
	{
		if ($filetype <> $thumbtype)
		{
			trigger_error($user->lang['FILETYPE_AND_THUMBTYPE_DO_NOT_MATCH'], E_USER_WARNING);
		}
	}

	/**
	* Check posted info
	*/
	$pic_title		= request_var('pic_title', '', true);
	$pic_desc		= utf8_substr(request_var('pic_desc', '', true), 0, $album_config['desc_length']);
	$pic_username	= request_var('pic_username', '');
	$pic_time		= time();
	$pic_user_ip	= $user->ip;
	$pic_approval	= (!$album_data['album_approval']) ? 1 : 0;

	if(empty($pic_title))
	{
		trigger_error($user->lang['MISSING_IMAGE_TITLE'], E_USER_WARNING);
	}
	if (!isset($_FILES['pic_file']))
	{
		trigger_error('Bad Upload', E_USER_WARNING);
	}
	if (!$user->data['is_registered'])
	{//Check username for guest posting
		if ($pic_username <> '')
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			$result = validate_username($pic_username);
			if ($result['error'])
			{
				trigger_error($result['error_msg'], E_USER_WARNING);
			}
		}
	}

	/**
	* Generate filename and upload
	*/
	srand((double)microtime()*1000000);// for older than version 4.2.0 of PHP
	do
	{
		$pic_filename = md5(uniqid(rand())) . $pic_filetype;
	}
	while( file_exists(ALBUM_UPLOAD_PATH . $pic_filename) );
	if ($album_config['gd_version'] == 0)
	{
		$pic_thumbnail = $pic_filename;
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
	$move_file($filetmp, ALBUM_UPLOAD_PATH . $pic_filename);
	@chmod(ALBUM_UPLOAD_PATH . $pic_filename, 0777);
	if (!$album_config['gd_version'])
	{
		$move_file($thumbtmp, ALBUM_CACHE_PATH . $pic_thumbnail);
		@chmod(ALBUM_CACHE_PATH . $pic_thumbnail, 0777);
	}


	/**
	* Well, it's an image. Check its image size
	*/
	$pic_size = getimagesize(ALBUM_UPLOAD_PATH . $pic_filename);
	$pic_width = $pic_size[0];
	$pic_height = $pic_size[1];

	if (($pic_width > $album_config['max_width']) || ($pic_height > $album_config['max_height']))
	{
		@unlink(ALBUM_UPLOAD_PATH . $pic_filename);
		if (!$album_config['gd_version'])
		{
			@unlink(ALBUM_CACHE_PATH . $pic_thumbnail);
		}
		trigger_error($user->lang['UPLOAD_IMAGE_SIZE_TOO_BIG'], E_USER_WARNING);
	}

	if (!$album_config['gd_version'])
	{
		$thumb_size = getimagesize(ALBUM_CACHE_PATH . $pic_thumbnail);
		$thumb_width = $thumb_size[0];
		$thumb_height = $thumb_size[1];
		if (($thumb_width > $album_config['thumbnail_size']) || ($thumb_height > $album_config['thumbnail_size']))
		{
			@unlink(ALBUM_UPLOAD_PATH . $pic_filename);
			@unlink(ALBUM_CACHE_PATH . $pic_thumbnail);
			trigger_error($user->lang['UPLOAD_THUMBNAIL_SIZE_TOO_BIG'], E_USER_WARNING);
		}
	}

	/**
	* This image is okay, we can cache its thumbnail now
	*/
	if (($album_config['thumbnail_cache']) && ($album_config['gd_version'] > 0)) 
	{
		$gd_errored = FALSE; 
		switch ($pic_filetype) 
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

		$src = @$read_function(ALBUM_UPLOAD_PATH  . $pic_filename);
		if (!$src)
		{
			$gd_errored = TRUE;
			$pic_thumbnail = '';
		}
		else if (($pic_width > $album_config['thumbnail_size']) || ($pic_height > $album_config['thumbnail_size']))
		{
			// Resize it
			if ($pic_width > $pic_height)
			{
				$thumbnail_width 	= $album_config['thumbnail_size'];
				$thumbnail_height 	= $album_config['thumbnail_size'] * ($pic_height/$pic_width);
			}
			else
			{
				$thumbnail_height 	= $album_config['thumbnail_size'];
				$thumbnail_width 	= $album_config['thumbnail_size'] * ($pic_width/$pic_height);
			}

			// Create thumbnail + 16 Pixel extra for imagesize text 
			$thumbnail = ($album_config['gd_version'] == 1) ? @imagecreate($thumbnail_width, $thumbnail_height + 16) : @imagecreatetruecolor($thumbnail_width, $thumbnail_height + 16); 
			$resize_function = ($album_config['gd_version'] == 1) ? 'imagecopyresized' : 'imagecopyresampled';
			@$resize_function($thumbnail, $src, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $pic_width, $pic_height);

			// Create image details credits to Dr.Death
			$dimension_font = 1;
			$dimension_filesize = filesize(ALBUM_UPLOAD_PATH . $pic_filename);
			$dimension_string = $pic_width . "x" . $pic_height . "(" . intval($dimension_filesize/1024) . "KB)";
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
			$pic_thumbnail = $pic_filename;
			// Write to disk
			switch ($pic_filetype)
			{
				case '.jpg':
					@imagejpeg($thumbnail, ALBUM_CACHE_PATH . $pic_thumbnail, $album_config['thumbnail_quality']);
				break;

				case '.png':
					@imagepng($thumbnail, ALBUM_CACHE_PATH . $pic_thumbnail);
				break;

				case '.gif':
					@imagegif($thumbnail, ALBUM_CACHE_PATH . $pic_thumbnail);
				break;
			}
			@chmod(ALBUM_CACHE_PATH . $pic_thumbnail, 0777);
		}
	}
	else if ($album_config['gd_version'] > 0)
	{
		$pic_thumbnail = '';
	}

	/**
	* Insert into DB
	*/
	include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
	$message_parser 			= new parse_message();
	$message_parser->message 	= utf8_normalize_nfc($pic_desc);
	if($message_parser->message)
	{
		$message_parser->parse(true, true, true, true, false, true, true, true);
	}

	$sql_ary = array(
		'image_filename' 		=> $pic_filename,
		'image_thumbnail'		=> $pic_thumbnail,
		'image_name'			=> $pic_title,
		'image_desc'			=> $message_parser->message,
		'image_desc_uid'		=> $message_parser->bbcode_uid,
		'image_desc_bitfield'	=> $message_parser->bbcode_bitfield,
		'image_user_id'			=> $user->data['user_id'],
		'image_user_colour'		=> $user->data['user_colour'],
		'image_username'		=> ($pic_username) ? $pic_username : $user->data['username'],
		'image_user_ip'			=> $pic_user_ip,
		'image_time'			=> $pic_time,
		'image_album_id'		=> $album_id,
		'image_approval'		=> $pic_approval,
	);

	$db->sql_query('INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));


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
?>