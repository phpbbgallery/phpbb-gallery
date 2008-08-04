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

// Get general album information
include_once("{$phpbb_root_path}{$gallery_root_path}includes/common.$phpEx");
include_once("{$phpbb_root_path}{$gallery_root_path}includes/permissions.$phpEx");
$album_access_array = get_album_access_array();


// ------------------------------------
// Check the request
// ------------------------------------
$image_id = request_var('image_id', request_var('pic_id', 0));
if (!$image_id)
{
	die($user->lang['NO_IMAGE_SPECIFIED']);
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

$image_filetype = utf8_substr($image_data['image_filename'], strlen($image_data['image_filename']) - 4, 4);
if (empty($image_data) || !file_exists($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['image_filename']) )
{
	$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
		SET image_filemissing = 1
		WHERE image_id = ' . $image_id;
	$db->sql_query($sql);
	trigger_error('IMAGE_NOT_EXIST');
}
$sql = 'SELECT *
	FROM ' . GALLERY_ALBUMS_TABLE . '
	WHERE album_id = ' . $album_id . '
	LIMIT 1';
$result = $db->sql_query($sql);
$album_data = $db->sql_fetchrow($result);
if (empty($album_data))
{
	trigger_error('ALBUM_NOT_EXIST');
}
if (!gallery_acl_check('i_view', $album_id))
{
	trigger_error('NOT_AUTHORISED');
}


// ------------------------------------
// Check Pic Approval
// ------------------------------------
if (!gallery_acl_check('a_moderate', $album_id) && ($image_data['image_status'] != 1))
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
		if((strstr($check_referer, $good_referers[$i])) && ($good_referers[$i] <> ''))
		{
			$errored = FALSE;
		}
	}

	if ($errored)
	{
		trigger_error('NOT_AUTHORISED');
	}
}


/*
+----------------------------------------------------------
| Main work here...
+----------------------------------------------------------
*/


// ------------------------------------
// Increase view counter
// ------------------------------------

$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
	SET image_view_count = image_view_count + 1
	WHERE image_id = ' . $image_id . '
	LIMIT 1';
$result = $db->sql_query($sql);


// ------------------------------------
// Okay, now we can send image to the browser
// ------------------------------------
$watermark_ok = false;
$file_size = getimagesize($phpbb_root_path . GALLERY_UPLOAD_PATH  . $image_data['image_filename']);
if ($album_config['watermark_images'] && ($album_config['watermark_height'] < $file_size[0]) && ($album_config['watermark_width'] < $file_size[1]) && $gd_success)
{
	$marktype = substr($album_config['watermark_source'], strlen($album_config['watermark_source']) - 4, 4);
	switch ( $marktype )
	{
		case '.png':
			$nm = imagecreatefrompng($phpbb_root_path . $album_config['watermark_source']);
		break;

		case '.gif':
			$nm = imagecreatefromgif($phpbb_root_path . $album_config['watermark_source']);
		break;

		case '.jpg':
		case 'jpeg':
			$nm = imagecreatefromjpeg($phpbb_root_path .$album_config['watermark_source']);
		break;

		default:
			$nm = false;
	}

	if ($nm)
	{
		$sx = imagesx($nm);
		$sy = imagesy($nm);

		switch ($image_filetype)
		{
			case '.png':
				$im = imagecreatefrompng($phpbb_root_path . GALLERY_UPLOAD_PATH  . $image_data['image_filename']);
			break;

			case '.gif':
				$im = imagecreatefromgif($phpbb_root_path . GALLERY_UPLOAD_PATH  . $image_data['image_filename']);
			break;

			case '.jpg':
			case 'jpeg':
				$im = imagecreatefromjpeg($phpbb_root_path . GALLERY_UPLOAD_PATH  . $image_data['image_filename']);
			break;

			default:
				$im = false;
		}

		if ($im)
		{
			$sx2 = imagesx($im);
			$sy2 = imagesy($im);
			imagecopy($im,$nm,(($sx2 * 0.5) - ($sx * 0.5)), ($sy2 - $sy - 5), 0,0,$sx,$sy);

			$watermark_ok = true;
		}
	}
}

if ($watermark_ok)
{
	switch ($image_filetype)
	{
		case '.png':
		case '.gif':
			header('Content-type: image/png');
			imagepng($im);
		break;

		default:
			header('Content-type: image/jpeg');
			imagejpeg($im);
		break;
	}
}
else
{
	switch ($image_filetype)
	{
		case '.png':
			header('Content-type: image/png');
		break;

		case '.gif':
			header('Content-type: image/gif');
		break;

		case '.jpg':
			header('Content-type: image/jpeg');
		break;

		default:
			die('The filename data in the DB was corrupted');
		break;
	}

	readfile($phpbb_root_path . GALLERY_UPLOAD_PATH  . $image_data['image_filename']);
}
exit;
?>