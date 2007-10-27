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

if (!$pic_id = request_var('pic_id', 0))
{
	die($user->lang['NO_IMAGE_SPECIFIED']);
}


// ------------------------------------
// Get this pic info
// ------------------------------------

$sql = "SELECT *
		FROM ". ALBUM_TABLE ."
		WHERE pic_id = '$pic_id'";
$result = $db->sql_query($sql);

$thispic = $db->sql_fetchrow($result);

$cat_id = $thispic['pic_cat_id'];
$user_id = $thispic['pic_user_id'];

$pic_filetype = substr($thispic['pic_filename'], strlen($thispic['pic_filename']) - 4, 4);
$pic_filename = $thispic['pic_filename'];
$pic_thumbnail = $thispic['pic_thumbnail'];

if( empty($thispic) or !file_exists(ALBUM_UPLOAD_PATH . $pic_filename) )
{
	die($user->lang['IMAGE_NOT_EXIST']);
}


// ------------------------------------
// Get the current Category Info
// ------------------------------------

if ($cat_id != PERSONAL_GALLERY)
{
	$sql = "SELECT *
			FROM ". ALBUM_CAT_TABLE ."
			WHERE cat_id = '$cat_id'";
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

if ($user->data['user_type'] != USER_FOUNDER)
{
	if (($thiscat['cat_approval'] == ADMIN) || (($thiscat['cat_approval'] == MOD) && !$album_user_access['moderator']))
	{
		if ($thispic['pic_approval'] != 1)
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

	if ($album_config['hotlink_allowed'] != '')
	{
		$good_referers = explode(',', $album_config['hotlink_allowed']);
	}

	$good_referers[] = $board_config['server_name'] . $board_config['script_path'];

	$errored = TRUE;

	for ($i = 0; $i < count($good_referers); $i++)
	{
		$good_referers[$i] = trim($good_referers[$i]);

		if ((strstr($check_referer, $good_referers[$i])) && ($good_referers[$i] != ''))
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
// Send Thumbnail to browser
// ------------------------------------

if (($pic_filetype != '.jpg') && ($pic_filetype != '.png') && ($pic_filetype != '.gif'))
{
	// --------------------------------
	// GD does not support GIF so we must SEND a premade No-thumbnail pic then EXIT
	// --------------------------------

	header('Content-type: image/jpeg');
	readfile($images['no_thumbnail']);
	exit;
}
else
{
	// --------------------------------
	// Check thumbnail cache. If cache is available we will SEND & EXIT
	// --------------------------------

	if (($album_config['thumbnail_cache'] == 1) && ($pic_thumbnail != '') && file_exists(ALBUM_CACHE_PATH . $pic_thumbnail))
	{
		switch ($pic_filetype)
		{
		  case '.gif':
			case '.jpg':
				header('Content-type: image/jpeg');
				break;
			case '.png':
				header('Content-type: image/png');
				break;
		}

		readfile(ALBUM_CACHE_PATH . $pic_thumbnail);
		exit;
	}


	// --------------------------------
	// Hmm, cache is empty. Try to re-generate!
	// --------------------------------

	$pic_size = @getimagesize(ALBUM_UPLOAD_PATH . $pic_filename);
	$pic_width = $pic_size[0];
	$pic_height = $pic_size[1];

	$gd_errored = FALSE;
	switch ($pic_filetype)
	{
	 case '.gif':
      $read_function = 'imagecreatefromgif';
      $pic_filetype = '.jpg';
   break;
		case '.jpg':
			$read_function = 'imagecreatefromjpeg';
			break;
		case '.png':
			$read_function = 'imagecreatefrompng';
			break;
	}

	$src = @$read_function(ALBUM_UPLOAD_PATH  . $pic_filename);

	if (!$src)
	{
		$gd_errored = TRUE;
		$pic_thumbnail = '';
	}
	else if( ($pic_width > $album_config['thumbnail_size']) or ($pic_height > $album_config['thumbnail_size']) )
	{
		// ----------------------------
		// Resize it
		// ----------------------------

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

		$thumbnail = ($album_config['gd_version'] == 1) ? @imagecreate($thumbnail_width, $thumbnail_height) : @imagecreatetruecolor($thumbnail_width, $thumbnail_height);

		$resize_function = ($album_config['gd_version'] == 1) ? 'imagecopyresized' : 'imagecopyresampled';

		@$resize_function($thumbnail, $src, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $pic_width, $pic_height);
	}
	else
	{
		$thumbnail = $src;
	}

	if (!$gd_errored)
	{
		if ($album_config['thumbnail_cache'] == 1)
		{
			// ------------------------
			// Re-generate successfully. Write it to disk!
			// ------------------------

			$pic_thumbnail = $pic_filename;

			switch ($pic_filetype)
			{
				case '.jpg':
					@imagejpeg($thumbnail, ALBUM_CACHE_PATH . $pic_thumbnail, $album_config['thumbnail_quality']);
					break;
				case '.png':
					@imagepng($thumbnail, ALBUM_CACHE_PATH . $pic_thumbnail);
					break;
			}

			@chmod(ALBUM_CACHE_PATH . $pic_thumbnail, 0777);
		}


		// ----------------------------
		// After write to disk, donot forget to send to browser also
		// ----------------------------

		switch ($pic_filetype)
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
		// ----------------------------
		// It seems you have not GD installed :(
		// ----------------------------

		header('Content-type: image/jpeg');
		readfile('images/nothumbnail.jpg');
		exit;
	}
}


// +------------------------------------------------------+
// |  Powered by Photo Album 2.x.x (c) 2002-2003 Smartor  |
// +------------------------------------------------------+

?>
