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
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin(false);
$auth->acl($user->data);
$user->setup('mods/gallery');

include_once("{$phpbb_root_path}{$gallery_root_path}includes/common.$phpEx");
include_once("{$phpbb_root_path}{$gallery_root_path}includes/permissions.$phpEx");
$album_access_array = get_album_access_array();

/**
* Check the request
*/
$image_id = request_var('image_id', request_var('pic_id', 0));
if (!$image_id)
{
	trigger_error('NO_IMAGE_SPECIFIED');
}


// ------------------------------------
// Get this pic info
// ------------------------------------

$sql = 'SELECT *
	FROM ' . GALLERY_IMAGES_TABLE . '
	WHERE image_id = ' . $image_id . '
	LIMIT 1';
$result = $db->sql_query($sql);

$image_data = $db->sql_fetchrow($result);

$album_id = $image_data['image_album_id'];
$user_id = $image_data['image_user_id'];
$image_filetype = substr($image_data['image_filename'], strlen($image_data['image_filename']) - 4, 4);

if (empty($image_data) or !file_exists($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['image_filename']))
{
	trigger_error('IMAGE_NOT_EXIST');
}


// ------------------------------------
// Get the current Category Info
// ------------------------------------
$sql = 'SELECT *
	FROM ' . GALLERY_ALBUMS_TABLE . '
	WHERE album_id = ' . $album_id;
$result = $db->sql_query($sql);
$album_data = $db->sql_fetchrow($result);
if (empty($album_data))
{
	trigger_error('ALBUM_NOT_EXIST');
}
if ($album_data['album_user_id'] > 0)
{
	$album_access_array[$album_id] = $album_access_array[(($album_data['album_user_id'] == $user->data['user_id']) ? -2 : -3)];
}
if ($album_access_array[$album_id]['i_view'] != 1)
{
	trigger_error('NOT_AUTHORISED');
}


// ------------------------------------
// Check Pic Approval
// ------------------------------------
if (($album_access_array[$album_id]['a_moderate'] != 1) && (!$image_data['image_status'] != 1))
{
	trigger_error($user->lang['NOT_AUTHORISED']);
}

// ------------------------------------
// Check hotlink
// ------------------------------------

if ($album_config['hotlink_prevent'] && isset($HTTP_SERVER_VARS['HTTP_REFERER']))
{
	$check_referer = explode('?', $HTTP_SERVER_VARS['HTTP_REFERER']);
	$check_referer = trim($check_referer[0]);
	$good_referers = array();

	if ($album_config['hotlink_allowed'] <> '')
	{
		$good_referers = explode(',', $album_config['hotlink_allowed']);
	}

	$good_referers[] = $config['server_name'] . $config['script_path'];
	$errored = TRUE;

	for ($i = 0; $i < count($good_referers); $i++)
	{
		$good_referers[$i] = trim($good_referers[$i]);
		if ((strstr($check_referer, $good_referers[$i])) && ($good_referers[$i] <> ''))
		{
			$errored = FALSE;
		}
	}

	if ($errored)
	{
		trigger_error('NOT_AUTHORISED');
	}
}


/**
* Main work here...
*/


// ------------------------------------
// Send Thumbnail to browser
// ------------------------------------

	// --------------------------------
	// Check thumbnail cache. If cache is available we will SEND & EXIT
	// --------------------------------

	if (($album_config['thumbnail_cache']) && ($image_data['image_thumbnail'] <> '') && file_exists($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['image_thumbnail']))
	{
		switch ($image_filetype)
		{
			case '.gif':
			case '.jpg':
				header('Content-type: image/jpeg');
			break;
			
			case '.png':
				header('Content-type: image/png');
			break;
		}

		readfile($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['image_thumbnail']);
		exit;
	}

	// --------------------------------
	// Hmm, cache is empty. Try to re-generate!
	// --------------------------------

	$image_size = getimagesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['image_filename']);
	$image_width = $image_size[0];
	$image_height = $image_size[1];
	$gd_errored = FALSE;
	switch ($image_filetype)
	{
		case '.gif':
			$read_function = 'imagecreatefromgif';
			$image_filetype = '.jpg';
		break;

		case '.jpg':
			$read_function = 'imagecreatefromjpeg';
		break;

		case '.png':
			$read_function = 'imagecreatefrompng';
		break;
	}
	$src = @$read_function($phpbb_root_path . GALLERY_UPLOAD_PATH  . $image_data['image_filename']);

if (!$src)
{
	$gd_errored = TRUE;
	$image_data['image_thumbnail'] = '';
}
else if (($image_width > $album_config['thumbnail_size']) or ($image_height > $album_config['thumbnail_size']))
{
	// ----------------------------
	// Resize it
	// ----------------------------

	if ($image_width > $image_height)
	{
		$thumbnail_width = $album_config['thumbnail_size'];
		$thumbnail_height = $album_config['thumbnail_size'] * ($image_height/$image_width);
	}
	else
	{
		$thumbnail_height = $album_config['thumbnail_size'];
		$thumbnail_width = $album_config['thumbnail_size'] * ($image_width/$image_height);
	}

	// Create thumbnail + 16 Pixel extra for imagesize text 
	if ($album_config['thumbnail_info_line'])
	{// Create image details credits to Dr.Death
		$thumbnail = ($album_config['gd_version'] == 1) ? @imagecreate($thumbnail_width, $thumbnail_height + 16) : @imagecreatetruecolor($thumbnail_width, $thumbnail_height + 16); 
	}
	else
	{
		$thumbnail = ($album_config['gd_version'] == 1) ? @imagecreate($thumbnail_width, $thumbnail_height) : @imagecreatetruecolor($thumbnail_width, $thumbnail_height);
	}
	$resize_function = ($album_config['gd_version'] == 1) ? 'imagecopyresized' : 'imagecopyresampled';
	@$resize_function($thumbnail, $src, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $image_width, $image_height);

	if ($album_config['thumbnail_info_line'])
	{// Create image details credits to Dr.Death
		$dimension_font = 1;
		$dimension_filesize = filesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['image_filename']);
		$dimension_string = $image_width . "x" . $image_height . "(" . intval($dimension_filesize/1024) . "KB)";
		$dimension_colour = ImageColorAllocate($thumbnail,255,255,255);
		$dimension_height = imagefontheight($dimension_font);
		$dimension_width = imagefontwidth($dimension_font) * strlen($dimension_string);
		$dimension_x = ($thumbnail_width - $dimension_width) / 2;
		$dimension_y = $thumbnail_height + ((16 - $dimension_height) / 2);
		imagestring($thumbnail, 1, $dimension_x, $dimension_y, $dimension_string, $dimension_colour);
	}
}
else
{
	$thumbnail = $src;
}

if (!$gd_errored)
{
	if ($album_config['thumbnail_cache'])
	{// Re-generate successfully. Write it to disk!

		$image_data['image_thumbnail'] = $image_data['image_filename'];

		switch ($image_filetype)
		{
			case '.jpg':
				@imagejpeg($thumbnail, $phpbb_root_path . GALLERY_CACHE_PATH . $image_data['image_thumbnail'], $album_config['thumbnail_quality']);
				break;
			case '.png':
				@imagepng($thumbnail, $phpbb_root_path . GALLERY_CACHE_PATH . $image_data['image_thumbnail']);
			break;
		}

		@chmod($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['image_thumbnail'], 0777);
	}


	/*
	* After write to disk, don't forget to send to browser also
	*/

	switch ($image_filetype)
	{
		case '.jpg':
			@imagejpeg($thumbnail, '', $album_config['thumbnail_quality']);
		break;
		case '.png':
			@imagepng($thumbnail);
		break;
	}
	exit;
}
else
{
	/*
	* It seems you have not GD installed :(
	*/
	header('Content-type: image/jpeg');
	readfile('images/nothumbnail.jpg');
	exit;
}
?>