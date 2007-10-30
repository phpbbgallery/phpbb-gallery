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

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');


//
// Get general album information
//
include($album_root_path . 'includes/common.'.$phpEx);



$cat_id = request_var('album_id', 0);


// ------------------------------------
// Get the current Category Info
// ------------------------------------

if ($cat_id != PERSONAL_GALLERY)
{
	$sql = "SELECT c.*, COUNT(p.pic_id) AS count
			FROM ". ALBUM_CAT_TABLE ." AS c
				LEFT JOIN ". ALBUM_TABLE ." AS p ON c.cat_id = p.pic_cat_id
			WHERE c.cat_id = '$cat_id'
			GROUP BY c.cat_id
			LIMIT 1";
	$result = $db->sql_query($sql);

	$thiscat = $db->sql_fetchrow($result);
}
else
{
	$thiscat = init_personal_gallery_cat($user->data['user_id']);
}

$current_pics = $thiscat['count'];

if (empty($thiscat))
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
}


// ------------------------------------
// Check the permissions
// ------------------------------------

$album_user_access = album_user_access($cat_id, $thiscat, 0, 1, 0, 0, 0, 0); // UPLOAD

if ($album_user_access['upload'] == 0)
{
	if (!$user->data['is_registered'] || $user->data['is_bot'])
	{
		login_box("gallery/upload.$phpEx?album_id=$cat_id", $user->lang['LOGIN_EXPLAIN_UPLOAD']);
	}
	else
	{
		trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
	}
}


/*
+----------------------------------------------------------
| Upload Quota Check
+----------------------------------------------------------
*/

if ($cat_id != PERSONAL_GALLERY)
{
	// ------------------------------------
	// Check Album Configuration Quota
	// ------------------------------------

	if ($album_config['max_pics'] >= 0)
	{
		//
		// $current_pics was set at "Get the current Category Info"
		//
		if( $current_pics >= $album_config['max_pics'] )
		{
			trigger_error($user->lang['ALBUM_REACHED_QUOTA'], E_USER_WARNING);
		}
	}


	// ------------------------------------
	// Check User Limit
	// ------------------------------------

	$check_user_limit = FALSE;

	if (($user->data['user_type'] != USER_FOUNDER) && $user->data['is_registered'] && !$user->data['is_bot'])
	{
		if ($album_user_access['moderator'])
		{
			if ($album_config['mod_pics_limit'] >= 0)
			{
				$check_user_limit = 'mod_pics_limit';
			}
		}
		else
		{
			if ($album_config['user_pics_limit'] >= 0)
			{
				$check_user_limit = 'user_pics_limit';
			}
		}
	}

	// Do the check here
	if ($check_user_limit != FALSE)
	{
		$sql = "SELECT COUNT(pic_id) AS count
				FROM ". ALBUM_TABLE ."
				WHERE pic_user_id = '". $user->data['user_id'] ."'
					AND pic_cat_id = '$cat_id'";
		$result = $db->sql_query($sql);
		
		$row = $db->sql_fetchrow($result);
		$own_pics = $row['count'];

		if( $own_pics >= $album_config[$check_user_limit] )
		{
			trigger_error($user->lang['USER_REACHED_QUOTA'], E_USER_WARNING);
		}
	}
}
else
{
	if (($current_pics >= $album_config['personal_gallery_limit']) && ($album_config['personal_gallery_limit'] >= 0))
	{
		trigger_error($user->lang['ALBUM_REACHED_QUOTA'], E_USER_WARNING);
	}
}

/*
+----------------------------------------------------------
| Main work here...
+----------------------------------------------------------
*/

if(!isset($_POST['pic_title']))
{
	// --------------------------------
	// Build categories select
	// --------------------------------
	$sql = "SELECT *
			FROM " . ALBUM_CAT_TABLE ."
			ORDER BY cat_order ASC";
	$result = $db->sql_query($sql);

	$catrows = array();

	while( $row = $db->sql_fetchrow($result) )
	{
		$thiscat_access = album_user_access($row['cat_id'], $row, 0, 1, 0, 0, 0, 0); // UPLOAD

		if ($thiscat_access['upload'] == 1)
		{
			$catrows[] = $row;
		}
	}

	$select_cat = '<select name="album_id">';

	if ($cat_id == PERSONAL_GALLERY)
	{
		$select_cat .= '<option value="$cat_id" selected="selected">';
		$select_cat .= sprintf($user->lang['PERSONAL_ALBUM_OF_USER'], $user->data['username']);
		$select_cat .= '</option>';
	}

	for ($i = 0; $i < count($catrows); $i++)
	{
		$select_cat .= '<option value="'. $catrows[$i]['cat_id'] .'" ';
		$select_cat .= ($cat_id == $catrows[$i]['cat_id']) ? 'selected="selected"' : '';
		$select_cat .= '>'. $catrows[$i]['cat_title'] .'</option>';
	}

	$select_cat .= '</select>';

	$template->assign_vars(array(
		'U_VIEW_CAT' => ($cat_id != PERSONAL_GALLERY) ? append_sid("album.$phpEx?id=$cat_id") : append_sid("album_personal.$phpEx"),
		'CAT_TITLE' => $thiscat['cat_title'],

		'L_UPLOAD_PIC' => $user->lang['UPLOAD_IMAGE'],

		'L_USERNAME' => $user->lang['USERNAME'],
		'L_PIC_TITLE' => $user->lang['IMAGE_TITLE'],

		'L_PIC_DESC' => $user->lang['IMAGE_DESC'],
		'L_PLAIN_TEXT_ONLY' => $user->lang['PLAIN_TEXT_ONLY'],
		'L_MAX_LENGTH' => $user->lang['MAX_LENGTH'],
		'S_PIC_DESC_MAX_LENGTH' => $album_config['desc_length'],

		'L_UPLOAD_PIC_FROM_MACHINE' => $user->lang['FILE'],
		'L_UPLOAD_TO_CATEGORY' => $user->lang['UPLOAD_TO_ALBUM'],

		'SELECT_CAT' => $select_cat,

		'L_MAX_FILESIZE' => $user->lang['MAX_FILE_SIZE'],
		'S_MAX_FILESIZE' => $album_config['max_file_size'],

		'L_MAX_WIDTH' => $user->lang['MAX_WIDTH'],
		'L_MAX_HEIGHT' => $user->lang['MAX_HEIGHT'],

		'S_MAX_WIDTH' => $album_config['max_width'],
		'S_MAX_HEIGHT' => $album_config['max_height'],

		'L_ALLOWED_JPG' => $user->lang['JPG_ALLOWED'],
		'L_ALLOWED_PNG' => $user->lang['PNG_ALLOWED'],
		'L_ALLOWED_GIF' => $user->lang['GIF_ALLOWED'],

		'S_JPG' => ($album_config['jpg_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],
		'S_PNG' => ($album_config['png_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],
		'S_GIF' => ($album_config['gif_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],

		'L_UPLOAD_NO_TITLE' => $user->lang['UPLOAD_NO_TITLE'],
		'L_UPLOAD_NO_FILE' => $user->lang['UPLOAD_NO_FILE'],
		'L_DESC_TOO_LONG' => $user->lang['DESC_TOO_LONG'],

		// Manual Thumbnail
		'L_UPLOAD_THUMBNAIL' => $user->lang['UPLOAD_THUMBNAIL'],
		'L_UPLOAD_THUMBNAIL_EXPLAIN' => $user->lang['UPLOAD_THUMBNAIL_EXPLAIN'],
		'L_THUMBNAIL_SIZE' => $user->lang['THUMBNAIL_SIZE'],
		'S_THUMBNAIL_SIZE' => $album_config['thumbnail_size'],

		'L_RESET' => $user->lang['RESET'],
		'L_SUBMIT' => $user->lang['SUBMIT'],

		'S_ALBUM_ACTION' => append_sid("upload.$phpEx?album_id=$cat_id"),
		)
	);

	if ($album_config['gd_version'] == 0)
	{
		$template->assign_block_vars('switch_manual_thumbnail', array());
	}
	
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $user->lang['GALLERY'],
		'U_VIEW_FORUM'	=> append_sid("{$album_root_path}index.$phpEx"))
	);
	
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $thiscat['cat_title'],
		'U_VIEW_FORUM'	=> append_sid("{$album_root_path}album.$phpEx", 'id=' . $thiscat['cat_id']))
	);

	// Output page
	$page_title = $user->lang['UPLOAD_IMAGE'];
	
	page_header($page_title);
	
	$template->set_filenames(array(
		'body' => 'gallery_upload_body.html')
	);
	
	page_footer();

}
else
{
	// --------------------------------
	// Check posted info
	// --------------------------------

	$pic_title = request_var('pic_title', '', true);

	$pic_desc = substr(request_var('pic_desc', '', true), 0, $album_config['desc_length']);

	$pic_username = (!$user->data['is_registered']) ? substr(request_var('pic_username', ''), 0, 32) : str_replace("'", "''", $user->data['username']);

	if(empty($pic_title))
	{
		trigger_error($user->lang['MISSING_IMAGE_TITLE'], E_USER_WARNING);
	}

	if( !isset($_FILES['pic_file']) )
	{
		trigger_error('Bad Upload', E_USER_WARNING);
	}


	// --------------------------------
	// Check username for guest posting
	// --------------------------------

	if (!$user->data['is_registered'])
	{
		if ($pic_username != '')
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			$result = validate_username($pic_username);
			if ( $result['error'] )
			{
				trigger_error($result['error_msg'], E_USER_WARNING);
			}
		}
	}	


	// --------------------------------
	// Get File Upload Info
	// --------------------------------

	$filetype = $_FILES['pic_file']['type'];
	$filesize = $_FILES['pic_file']['size'];
	$filetmp = $_FILES['pic_file']['tmp_name'];

	if ($album_config['gd_version'] == 0)
	{
		$thumbtype = $_FILES['pic_thumbnail']['type'];
		$thumbsize = $_FILES['pic_thumbnail']['size'];
		$thumbtmp = $_FILES['pic_thumbnail']['tmp_name'];
	}


	// --------------------------------
	// Prepare variables
	// --------------------------------

	$pic_time = time();
	$pic_user_id = $user->data['user_id'];
	$pic_user_ip = $user->ip;


	// --------------------------------
	// Check file size
	// --------------------------------

	if (($filesize == 0) || ($filesize > $album_config['max_file_size']))
	{
		trigger_error($user->lang['BAD_UPLOAD_FILE_SIZE'], E_USER_WARNING);
	}

	if ($album_config['gd_version'] == 0)
	{
		if (($thumbsize == 0) || ($thumbsize > $album_config['max_file_size']))
		{
			trigger_error($user->lang['BAD_UPLOAD_FILE_SIZE'], E_USER_WARNING);
		}
	}


	// --------------------------------
	// Check file type
	// --------------------------------

	switch ($filetype)
	{
		case 'image/jpeg':
		case 'image/jpg':
		case 'image/pjpeg':
			if ($album_config['jpg_allowed'] == 0)
			{
				trigger_error($user->lang['NOT_ALLOWED_FILE_TYPE'], E_USER_WARNING);
			}
			$pic_filetype = '.jpg';
			break;

		case 'image/png':
		case 'image/x-png':
			if ($album_config['png_allowed'] == 0)
			{
				trigger_error($user->lang['NOT_ALLOWED_FILE_TYPE'], E_USER_WARNING);
			}
			$pic_filetype = '.png';
			break;

		case 'image/gif':
			if ($album_config['gif_allowed'] == 0)
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
		if ($filetype != $thumbtype)
		{
			trigger_error($user->lang['FILETYPE_AND_THUMBTYPE_DO_NOT_MATCH'], E_USER_WARNING);
		}
	}


	// --------------------------------
	// Generate filename
	// --------------------------------

	srand((double)microtime()*1000000);	// for older than version 4.2.0 of PHP

	do
	{
		$pic_filename = md5(uniqid(rand())) . $pic_filetype;
	}
	while( file_exists(ALBUM_UPLOAD_PATH . $pic_filename) );

	if ($album_config['gd_version'] == 0)
	{
		$pic_thumbnail = $pic_filename;
	}


	// --------------------------------
	// Move this file to upload directory
	// --------------------------------

	$ini_val = ( @phpversion() >= '4.0.0' ) ? 'ini_get' : 'get_cfg_var';

	if ( @$ini_val('open_basedir') != '' )
	{
		if ( @phpversion() < '4.0.3' )
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

	if ($album_config['gd_version'] == 0)
	{
		$move_file($thumbtmp, ALBUM_CACHE_PATH . $pic_thumbnail);

		@chmod(ALBUM_CACHE_PATH . $pic_thumbnail, 0777);
	}


	// --------------------------------
	// Well, it's an image. Check its image size
	// --------------------------------

	$pic_size = getimagesize(ALBUM_UPLOAD_PATH . $pic_filename);

	$pic_width = $pic_size[0];
	$pic_height = $pic_size[1];

	if (($pic_width > $album_config['max_width']) || ($pic_height > $album_config['max_height']))
	{
		@unlink(ALBUM_UPLOAD_PATH . $pic_filename);

		if ($album_config['gd_version'] == 0)
		{
			@unlink(ALBUM_CACHE_PATH . $pic_thumbnail);
		}
		trigger_error($user->lang['UPLOAD_IMAGE_SIZE_TOO_BIG'], E_USER_WARNING);
	}

	if ($album_config['gd_version'] == 0)
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


	// --------------------------------
	// This image is okay, we can cache its thumbnail now
	// --------------------------------

	if (($album_config['thumbnail_cache'] == 1) && ($album_config['gd_version'] > 0)) 
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
				$thumbnail_width = $album_config['thumbnail_size'];
				$thumbnail_height = $album_config['thumbnail_size'] * ($pic_height/$pic_width);
			}
			else
			{
				$thumbnail_height = $album_config['thumbnail_size'];
				$thumbnail_width = $album_config['thumbnail_size'] * ($pic_width/$pic_height);
			}

			// Create thumbnail + 16 Pixel extra for imagesize text 
			$thumbnail = ($album_config['gd_version'] == 1) ? @imagecreate($thumbnail_width, $thumbnail_height + 16) : @imagecreatetruecolor($thumbnail_width, $thumbnail_height + 16); 

			$resize_function = ($album_config['gd_version'] == 1) ? 'imagecopyresized' : 'imagecopyresampled';

			@$resize_function($thumbnail, $src, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $pic_width, $pic_height);
			
			// Create image details
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

		} // End IF $gd_errored

	} // End Thumbnail Cache
	else if ($album_config['gd_version'] > 0)
	{
		$pic_thumbnail = '';
	}

	// --------------------------------
	// Check Pic Approval
	// --------------------------------

	$pic_approval = ($thiscat['cat_approval'] == 0) ? 1 : 0;


	// --------------------------------
	// Insert into DB
	// --------------------------------

	$sql = "INSERT INTO ". ALBUM_TABLE ." (pic_filename, pic_thumbnail, pic_title, pic_desc, pic_user_id, pic_user_ip, pic_username, pic_time, pic_cat_id, pic_approval)
			VALUES ('" . $db->sql_escape($pic_filename) . "', '" . $db->sql_escape($pic_thumbnail) . "', '" . $db->sql_escape($pic_title) . "', '" . $db->sql_escape($pic_desc) . "', '" . $db->sql_escape($pic_user_id) . "', '" . $db->sql_escape($pic_user_ip) . "', '" . $db->sql_escape($pic_username) . "', '" . $db->sql_escape($pic_time) . "', '" . $db->sql_escape($cat_id) . "', '" . $db->sql_escape($pic_approval) . "')";
	$result = $db->sql_query($sql);


	// --------------------------------
	// Complete... now send a message to user
	// --------------------------------

	if ($thiscat['cat_approval'] == 0)
	{
		$message = $user->lang['ALBUM_UPLOAD_SUCCESSFUL'];
	}
	else
	{
		$message = $user->lang['ALBUM_UPLOAD_NEED_APPROVAL'];
	}

	if ($cat_id != PERSONAL_GALLERY)
	{
		if ($thiscat['cat_approval'] == 0)
		{
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid("album.$phpEx?id=$cat_id") . '">')
			);
		}

		$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_ALBUM'], "<a href=\"" . append_sid("album.$phpEx?id=$cat_id") . "\">", "</a>");
	}
	else
	{
		if ($thiscat['cat_approval'] == 0)
		{
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid("album_personal.$phpEx") . '">')
			);
		}

		$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_PERSONAL_ALBUM'], "<a href=\"" . append_sid("album_personal.$phpEx") . "\">", "</a>");
	}


	$message .= "<br /><br />" . sprintf($user->lang['CLICK_RETURN_GALLERY_INDEX'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

	trigger_error($message, E_USER_WARNING);
}


// +------------------------------------------------------+
// |  Powered by Photo Album 2.x.x (c) 2002-2003 Smartor  |
// +------------------------------------------------------+

?>