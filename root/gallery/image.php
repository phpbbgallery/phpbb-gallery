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

if ( $pic_id == 0 )
{
	die($user->lang['NO_IMAGE_SPECIFIED']);
}


// ------------------------------------
// Get this pic info
// ------------------------------------

$sql = 'SELECT *
		FROM ' . ALBUM_TABLE . '
		WHERE pic_id = ' . $pic_id;
$result = $db->sql_query($sql);

$thispic = $db->sql_fetchrow($result);

$cat_id = $thispic['pic_cat_id'];
$user_id = $thispic['pic_user_id'];

$pic_filetype = substr($thispic['pic_filename'], strlen($thispic['pic_filename']) - 4, 4);
$pic_filename = $thispic['pic_filename'];
$pic_thumbnail = $thispic['pic_thumbnail'];

if (empty($thispic) || !file_exists(ALBUM_UPLOAD_PATH . $pic_filename) )
{
	die($user->lang['IMAGE_NOT_EXIST']);
}


// ------------------------------------
// Get the current Category Info
// ------------------------------------

if ($cat_id <> PERSONAL_GALLERY)
{
	$sql = 'SELECT *
			FROM ' . ALBUM_CAT_TABLE . '
			WHERE cat_id = ' . $cat_id;
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

$album_user_access = album_user_access($cat_id, $thiscat, 1, 0, 0, 0, 0, 0); // VIEW
if ($album_user_access['view'] == 0)
{
	die($user->lang['NOT_AUTHORISED']);
}


// ------------------------------------
// Check Pic Approval
// ------------------------------------

if ($user->data['user_type'] <> USER_FOUNDER)
{
	if (($thiscat['cat_approval'] == ADMIN) || (($thiscat['cat_approval'] == MOD) && !$album_user_access['moderator']))
	{
		if ($thispic['pic_approval'] <> 1)
		{
			die($user->lang['NOT_AUTHORISED']);
		}
	}
}


// ------------------------------------
// Check hotlink
// ------------------------------------

if (($album_config['hotlink_prevent'] == 1) && (isset($HTTP_SERVER_VARS['HTTP_REFERER'])))
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

		if( (strstr($check_referer, $good_referers[$i])) && ($good_referers[$i] <> '') )
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

$sql = 'UPDATE ' . ALBUM_TABLE . '
		SET pic_view_count = pic_view_count + 1
		WHERE pic_id = ' . $pic_id;
$result = $db->sql_query($sql);


// ------------------------------------
// Okay, now we can send image to the browser
// ------------------------------------
$watermark_ok = false;

if ($album_config['watermark_images'] == 1)
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

	if ( $nm )
	{
		$sx = imagesx($nm);
		$sy = imagesy($nm);

		switch ( $pic_filetype )
		{
			case '.png':
				$im = imagecreatefrompng(ALBUM_UPLOAD_PATH  . $thispic['pic_filename']);
			break;
			
			case '.gif':
				$im = imagecreatefromgif(ALBUM_UPLOAD_PATH  . $thispic['pic_filename']);
			break;
			
			case '.jpg':
			case 'jpeg':
				$im = imagecreatefromjpeg(ALBUM_UPLOAD_PATH  . $thispic['pic_filename']);
			break;
			
			default:
				$im = false;
		}

		if ( $im )
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
	switch ( $pic_filetype )
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
	switch ( $pic_filetype )
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
	}

	readfile(ALBUM_UPLOAD_PATH  . $thispic['pic_filename']);
}
exit;


// +------------------------------------------------------+
// |  Powered by Photo Album 2.x.x (c) 2002-2003 Smartor  |
// +------------------------------------------------------+

?>