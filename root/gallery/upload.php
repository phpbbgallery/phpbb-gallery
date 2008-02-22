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
$album_data = get_album_info($album_id);
if ($album_data['album_type'] != 2)
{//Go Home Cheaters
	trigger_error($user->lang['ALBUM_IS_CATEGORY'], E_USER_WARNING);
}
if (empty($album_data))
{
	trigger_error($user->lang['ALBUM_NOT_EXIST'], E_USER_WARNING);
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
$submit = (isset($_POST['submit'])) ? true : false;
$loop = request_var('loop', 0);
$error = '';
if ($submit && request_var('pic_title', '', true) == '')
{
	$error .= $user->lang['UPLOAD_NO_TITLE'] . '<br />';
	$submit = false;
}

if ($loop != 0)
{
	$allowed_files = array();
	if ($album_config['jpg_allowed'])
	{
		$allowed_files[] = 'jpg';
		$allowed_files[] = 'JPG';
	}
	if ($album_config['gif_allowed'])
	{
		$allowed_files[] = 'gif';
		$allowed_files[] = 'GIF';
	}
	if ($album_config['png_allowed'])
	{
		$allowed_files[] = 'png';
		$allowed_files[] = 'PNG';
	}
	$empty = $message = $mu_index_root = $import_data = $results = '';
	$check_loop = $import_counter = 0;
	$loop = request_var('loop', 0);

	include_once($phpbb_root_path . GALLERY_ROOT_PATH . 'import/' . $user->data['user_id']. ".$phpEx");
	$mu_index_root_f = $store_root;
	$mu_index = 'multiple_upload.txt';
	$mu_index_root = utf8_substr($mu_index_root_f, 0, -(utf8_strlen($mu_index)));

	if ($loop > $store_counter)
	{
		unlink($phpbb_root_path . GALLERY_ROOT_PATH . 'import/' . $user->data['user_id']. ".$phpEx");
		trigger_error('ALBUM_UPLOAD_SUCCESSFUL');
	}
	if (!is_dir("{$store_images[$loop]}") && in_array(utf8_substr($store_images[$loop], -3), $allowed_files))
	{
		$filetype = getimagesize($store_images[$loop]);
		$image_data = array(
			'image_root'			=> $store_images[$loop],
			'image_type'			=> utf8_substr($store_images[$loop], -4),
			'image_size'			=> filesize($store_images[$loop]),
			'image_height'			=> $filetype[1],
			'image_width'			=> $filetype[0],
			'image_name'			=> str_replace('{NUM}', $loop, $store_name),
			'image_desc'			=> str_replace('{NUM}', $loop, $store_description),
			'image_time'			=> $store_time + $loop,
		);
		upload_images($image_data, $loop);
	}
	$loop = $loop + 1;
	$meta_info = append_sid("{$phpbb_root_path}" . GALLERY_ROOT_PATH . "upload.$phpEx", "album_id=$album_id&amp;loop=$loop");

	meta_refresh(0.1, $meta_info);
	trigger_error(sprintf($user->lang['MU_PENDING'], $loop, $store_counter));
}
elseif ($submit)
{
	if (!check_form_key('upload'))
	{
		trigger_error('FORM_INVALID');
	}
	$allowed_files = array();
	if ($album_config['jpg_allowed'])
	{
		$allowed_files[] = 'jpg';
		$allowed_files[] = 'JPG';
	}
	if ($album_config['gif_allowed'])
	{
		$allowed_files[] = 'gif';
		$allowed_files[] = 'GIF';
	}
	if ($album_config['png_allowed'])
	{
		$allowed_files[] = 'png';
		$allowed_files[] = 'PNG';
	}
	$mu_index_root_f = request_var('multiple_upload', '');
	if ($mu_index_root_f != '')
	{
		$mu_counter = 0;
		$mu_description = request_var('pic_desc', '', true);
		$mu_name = request_var('pic_title', '', true);
		$mu_index = 'multiple_upload.txt';
		$mu_index_root = utf8_substr($mu_index_root_f, 0, -(utf8_strlen($mu_index)));
		$config_data = "<?php\n";
		$config_data .= "\$store_root = '{$mu_index_root_f}';\n";
		$config_data .= "\$store_name = '{$mu_name}';\n";
		$config_data .= "\$store_description = '{$mu_description}';\n";
		$config_data .= "\$store_time = '" . time() . "';\n";
		$config_data .= "\$store_images = array(\n";
		$handle = opendir($mu_index_root);
		while ($file = readdir($handle))
		{
			if (!is_dir("$mu_index_root$file") && ($file != '.') && ($file != '..') && ($file != 'Thumbs.db') && in_array(utf8_substr($file, -3), $allowed_files))
			{
				// how often do we have to run?
				$mu_counter = $mu_counter + 1;
				$config_data .= "	'$mu_counter'	=> '$mu_index_root$file',\n";
			}
		}
		closedir($handle);

		$config_data .= ");\n\n";
		$config_data .= "\$store_counter = '{$mu_counter}';\n\n";
		$config_data .= '?' . '>'; // Done this to prevent highlighting editors getting confused!
		if ((file_exists($phpbb_root_path . GALLERY_ROOT_PATH . 'import/' . $user->data['user_id']. ".$phpEx") && is_writable($phpbb_root_path . GALLERY_ROOT_PATH . 'import/' . $user->data['user_id']. ".$phpEx")) || is_writable($phpbb_root_path . GALLERY_ROOT_PATH . 'import/'))
		{
			$written = true;
			if (!($fp = @fopen($phpbb_root_path . GALLERY_ROOT_PATH . 'import/' . $user->data['user_id']. ".$phpEx", 'w')))
			{
				$written = false;
			}
			if (!(@fwrite($fp, $config_data)))
			{
				$written = false;
			}
			@fclose($fp);
		}
		meta_refresh(3, append_sid($phpbb_root_path . GALLERY_ROOT_PATH . "upload.$phpEx", 'album_id=' . $album_id
			. '&amp;loop=1'
		));
		trigger_error(redirect(append_sid($phpbb_root_path . GALLERY_ROOT_PATH . "upload.$phpEx",
			'album_id=' . $album_id
			. '&amp;loop=1'
		)));
	}
	else
	{
		$filetype = getimagesize($_FILES['pic_file']['tmp_name']);
		switch ($_FILES['pic_file']['type'])
		{
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				if (!$album_config['jpg_allowed'])
				{
					trigger_error($user->lang['NOT_ALLOWED_FILE_TYPE'], E_USER_WARNING);
				}
				$image_type = '.jpg';
			break;

			case 'image/png':
			case 'image/x-png':
				if (!$album_config['png_allowed'])
				{
					trigger_error($user->lang['NOT_ALLOWED_FILE_TYPE'], E_USER_WARNING);
				}
				$image_type = '.png';
			break;

			case 'image/gif':
				if (!$album_config['gif_allowed'])
				{
					trigger_error($user->lang['NOT_ALLOWED_FILE_TYPE'], E_USER_WARNING);
				}
				$image_type = '.gif';
			break;
			
			default:
				trigger_error($user->lang['NOT_ALLOWED_FILE_TYPE'], E_USER_WARNING);
		}
		$image_data = array(
			'image_root'			=> $_FILES['pic_file']['tmp_name'],
			'image_type'			=> $image_type,
			'image_size'			=> $_FILES['pic_file']['size'],
			'image_height'			=> $filetype[1],
			'image_width'			=> $filetype[0],
			'image_name'			=> request_var('pic_title', '', true),
			'image_desc'			=> utf8_substr(request_var('pic_desc', '', true), 0, $album_config['desc_length']),
		);
		upload_images($image_data, $loop);
		trigger_error('ALBUM_UPLOAD_SUCCESSFUL');
	}
}
elseif(!$submit)
{
	$template->assign_vars(array(
		'U_VIEW_CAT'				=> ($album_id != PERSONAL_GALLERY) ? append_sid("album.$phpEx?id=$album_id") : append_sid("album_personal.$phpEx"),
		'ERROR'						=> $error,
		'CAT_TITLE'					=> $album_data['album_name'],
		'S_PIC_DESC_MAX_LENGTH'		=> $album_config['desc_length'],
		'S_MAX_FILESIZE'			=> $album_config['max_file_size'],
		'S_MAX_WIDTH'				=> $album_config['max_width'],
		'S_MAX_HEIGHT'				=> $album_config['max_height'],

		'S_JPG'					=> ($album_config['jpg_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],
		'S_PNG'					=> ($album_config['png_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],
		'S_GIF'					=> ($album_config['gif_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],
		'S_THUMBNAIL_SIZE'		=> $album_config['thumbnail_size'],

		'S_ALBUM_ACTION'		=> append_sid("upload.$phpEx?album_id=$album_id"),
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

//this is were we upload it
function upload_images(&$image_data, $loop = 0)
{
	global $db, $user, $phpbb_root_path, $phpEx, $album_config;

	//check for the acp-values
	$image_error = false;
	$skip = $loop + 1;
	$u_skip = ($loop) ? '<br /><a href="' . append_sid($phpbb_root_path . GALLERY_ROOT_PATH . "upload.$phpEx", 'album_id=' . request_var('album_id', 0) . "&amp;loop=$skip") . '">' . $user->lang['NEXT_STEP'] . '</a>' : '';
	if ($image_data['image_size'] > $album_config['max_file_size'])
	{
		trigger_error($user->lang['BAD_UPLOAD_FILE_SIZE'] . $u_skip);
	}
	if (($image_data['image_width'] > $album_config['max_width']) || ($image_data['image_height'] > $album_config['max_height']))
	{
		trigger_error($user->lang['UPLOAD_IMAGE_SIZE_TOO_BIG'] . $u_skip);
	}

	if (!$image_error)
	{
		include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		$message_parser				= new parse_message();
		$message_parser->message	= $image_data['image_desc'];
		if($message_parser->message)
		{
			$message_parser->parse(true, true, true, true, false, true, true, true);
		}

		srand((double)microtime()*1000000);// for older than version 4.2.0 of PHP
		do
		{
			$image_filename = md5(uniqid(rand())) . strtolower($image_data['image_type']);
		}
		while( file_exists(ALBUM_UPLOAD_PATH . $image_filename) );
		if ($album_config['gd_version'] == 0)
		{
			$pic_thumbnail = $image_filename;
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
		$move_file($image_data['image_root'], $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);
		@chmod($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename, 0777);

		$sql_ary = array(
			'image_filename' 		=> $image_filename,
			'image_thumbnail'		=> $image_filename,
			'image_name'			=> $image_data['image_name'],
			'image_desc'			=> $message_parser->message,
			'image_desc_uid'		=> $message_parser->bbcode_uid,
			'image_desc_bitfield'	=> $message_parser->bbcode_bitfield,
			'image_user_id'			=> $user->data['user_id'],
			'image_user_colour'		=> $user->data['user_colour'],
			'image_username'		=> ($user->data['user_id'] != 1) ? $user->data['username'] : request_var('pic_username', 'Guest'),
			'image_user_ip'			=> $user->ip,
			'image_time'			=> $image_data['image_time'],
			'image_album_id'		=> request_var('album_id', 0),
			'image_approval'		=> 1,
		);

		$db->sql_query('INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
	}

}
?>