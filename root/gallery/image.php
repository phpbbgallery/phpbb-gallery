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


// ------------------------------------
// Check the request
// ------------------------------------
$pic_id = request_var('pic_id', 0);
if (!$pic_id)
{
	die($user->lang['NO_IMAGE_SPECIFIED']);
}


// ------------------------------------
// Get this pic info
// ------------------------------------

$sql = 'SELECT *
	FROM ' . GALLERY_IMAGES_TABLE . '
	WHERE image_id = ' . $pic_id . '
	LIMIT 1';
$result = $db->sql_query($sql);

$thispic = $db->sql_fetchrow($result);

$album_id = $thispic['image_album_id'];
$user_id = $thispic['image_user_id'];

$pic_filetype = utf8_substr($thispic['image_filename'], strlen($thispic['image_filename']) - 4, 4);
$pic_filename = $thispic['image_filename'];
$pic_thumbnail = $thispic['image_thumbnail'];

if (empty($thispic) || !file_exists(ALBUM_UPLOAD_PATH . $pic_filename) )
{
	die($user->lang['IMAGE_NOT_EXIST']);
}


// ------------------------------------
// Get the current Category Info
// ------------------------------------

if ($album_id <> PERSONAL_GALLERY)
{
	$sql = 'SELECT *
		FROM ' . GALLERY_ALBUMS_TABLE . '
		WHERE album_id = ' . $album_id . '
		LIMIT 1';
	$result = $db->sql_query($sql);
	$thiscat = $db->sql_fetchrow($result);
}
else
{
	$thiscat = init_personal_gallery_cat($user_id);
}

if (empty($thiscat))
{
	die($user->lang['ALBUM_NOT_EXIST']);
}


// ------------------------------------
// Check the permissions
// ------------------------------------

$album_user_access = album_user_access($album_id, $thiscat, 1, 0, 0, 0, 0, 0); // VIEW
if (!$album_user_access['view'])
{
	die($user->lang['NOT_AUTHORISED']);
}


// ------------------------------------
// Check Pic Approval
// ------------------------------------

if ($user->data['user_type'] <> USER_FOUNDER)
{
	if (($thiscat['album_approval'] == ADMIN) || (($thiscat['album_approval'] == MOD) && !$album_user_access['moderator']))
	{
		if (!$thispic['image_approval'])
		{
			die($user->lang['NOT_AUTHORISED']);
		}
	}
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
		die($user->lang['NOT_AUTHORISED']);
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
	WHERE image_id = ' . $pic_id . '
	LIMIT 1';
$result = $db->sql_query($sql);


// ------------------------------------
// Okay, now we can send image to the browser
// ------------------------------------
$watermark_ok = false;

if ($album_config['watermark_images'] && $gd_success)
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

		switch ($pic_filetype)
		{
			case '.png':
				$im = imagecreatefrompng(ALBUM_UPLOAD_PATH  . $thispic['image_filename']);
			break;

			case '.gif':
				$im = imagecreatefromgif(ALBUM_UPLOAD_PATH  . $thispic['image_filename']);
			break;

			case '.jpg':
			case 'jpeg':
				$im = imagecreatefromjpeg(ALBUM_UPLOAD_PATH  . $thispic['image_filename']);
			break;

			default:
				$im = false;
		}

		if ($im)
		{
			$sx2 = imagesx($im);
			$sy2 = imagesy($im);
			imagecopymerge($im,$nm,(($sx2 * 0.5) - ($sx * 0.5)), ($sy2 - $sy - 5), 0,0,$sx,$sy,85);

			$watermark_ok = true;
		}
	}
}

if ($watermark_ok)
{
	switch ($pic_filetype)
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
	switch ($pic_filetype)
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

	readfile(ALBUM_UPLOAD_PATH  . $thispic['image_filename']);
}
exit;
?>