<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* @request by a_engles for http://www.sintillate.co.uk/
*/

// Comment this line to use the script!
//die ('Please read the first lines of this script for instructions on how to enable it');

/**
* The script itself must be in develop/
* The images to import, must lay in
* $phpbb_root_path/GALLERY_IMAGE_PATH/massimport/
*                                                city_name/
*                                                          club_name/
*                                                                    date/
* The albums are automatically generated.
* You need one album, where the permissions are copied from.
*/
define('IMAGE_USER_ID', 2);					// User_ID of the Bulking user
define('IMAGE_USERNAME', 'nickvergessen');	// Name of the Bulking user
define('IMAGE_USER_COLOUR', 'AA0000');		// Colour of the Bulking user
define('COPY_PERMISSIONS', 2);				// Album_ID which has the permissions that are going to be copied

/**
* @ignore
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');
$user->add_lang('posting');

$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . $gallery_root_path . 'includes/common.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/permissions.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/functions_display.' . $phpEx);
$album_access_array = get_album_access_array();

$directory = $phpbb_root_path . GALLERY_IMAGE_PATH . 'massimport/';
$images_per_loop = 0;
$results = array();
$time = request_var('time', 0);
if ($time == 0)
{
	$time = time();
}

$albums = $date_ids = array();
$city_ids = $city_ids2 = $city_names = array();
$club_ids = $club_ids2 = $club_names = array();
$sql = 'SELECT album_id, album_name, parent_id
	FROM ' . GALLERY_ALBUMS_TABLE . '
	WHERE album_user_id = 0';
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$albums[] = $row;
}
$db->sql_freeresult($result);

$echo = 'Cities: <br />';
foreach ($albums as $album)
{
	if ($album['parent_id'] == 0)
	{
		$city_ids2[] = $album['album_id'];
		$albums_tree[$album['album_name']] = array();
		$city_names[$album['album_id']] = $album;
		$city_ids[$album['album_name']] = $album;
		$echo .= $album['album_id'] . ',';
	}
}
$echo .= '<br />';

$echo .= 'Clubs: <br />';
foreach ($albums as $album)
{
	if (in_array($album['parent_id'], $city_ids2))
	{
		$club_ids2[] = $album['album_id'];
		$albums_tree[$city_names[$album['parent_id']]['album_name']][$album['album_name']] = array();
		$club_names[$album['album_id']] = $album;
		$club_ids[$city_names[$album['parent_id']]['album_name']][$album['album_name']] = $album;
		$echo .= $album['album_id'] . ',';
	}
}
$echo .= '<br />';

$echo .= 'Dates: <br />';
foreach ($albums as $album)
{
	if (in_array($album['parent_id'], $club_ids2))
	{
		$date_ids[] = $album['album_id'];
		$albums_tree [$city_names[$club_names[$album['parent_id']]['parent_id']]['album_name']] [$club_names[$album['parent_id']]['album_name']] [$album['album_name']] = $album['album_id'];
		$echo .= $album['album_id'] . ',';
	}
}
/*
$echo .= '<br />';
echo $echo;

print_r ($albums_tree);
echo '<br />';
*/

function show_dir($dir, $pos = '', $files = array())
{
	global $gallery_config, $results, $images_per_loop, $albums_tree, $city_ids, $club_ids;

	$handle = @opendir($dir);
	if (is_resource($handle))
	{
		while ((($file = readdir($handle)) !== false) && ($images_per_loop < 10))
		//@todo: while (($file = readdir($handle)) !== false)
		{
			if (preg_match('~^\.{1,2}$~', $file))
			{
				continue;
			}
 
			$album_path = substr($dir . $file, strripos($dir . $file, 'massimport/') + 11);
			$album_path = explode('/', $album_path);
			if (is_dir($dir.$file))
			{
				if (isset($album_path[0]))
				{
					//echo $album_path[0] . '/';
					if (!isset($albums_tree [$album_path[0]]))
					{
						// Create album - City
						$parent_id = 0;
						$album = create_new_album($parent_id, $album_path[0], ALBUM_CAT);
						$albums_tree[$album_path[0]] = array();
						$city_ids[$album_path[0]] = $album;
					}
				}
				if (isset($album_path[1]))
				{
					//echo $album_path[1] . '/';
					if (!isset($albums_tree [$album_path[0]] [$album_path[1]]))
					{
						// Create album - City > Club
						$parent_id = $city_ids [$album_path[0]] ['album_id'];
						$album = create_new_album($parent_id, $album_path[1], ALBUM_CAT);
						$albums_tree [$album_path[0]] [$album_path[1]] = array();
						$club_ids [$album_path[0]] [$album_path[1]] = $album;
					}
				}
				if (isset($album_path[2]))
				{
					//echo $album_path[2] . '/';
					if (!isset($albums_tree [$album_path[0]] [$album_path[1]] [$album_path[2]]))
					{
						// Create album - City > Club > Date
						$parent_id = $club_ids [$album_path[0]] [$album_path[1]] ['album_id'];
						$album = create_new_album($parent_id, $album_path[2], ALBUM_UPLOAD);
						$albums_tree [$album_path[0]] [$album_path[1]] [$album_path[2]] = $album;
					}
					//echo ' ' . $albums_tree[$album_path[0]][$album_path[1]][$album_path[2]];
				}
				//echo '<br />';

				$files = show_dir($dir . $file . '/', $dir . $file, $files);
			}
			else
			{
				//echo substr($pos, strpos($pos, '/') + 1) . '/' . $file . '<br>';
				if (
				((substr(strtolower($file), '-4') == '.png') && $gallery_config['png_allowed']) ||
				((substr(strtolower($file), '-4') == '.gif') && $gallery_config['gif_allowed']) ||
				((substr(strtolower($file), '-4') == '.jpg') && $gallery_config['jpg_allowed'])
				)
				{
					if ($images_per_loop < 10)
					{
						$results[] = utf8_encode($pos . '/' . $file);
						$images_per_loop++;
					}
				}
			}
		}
		closedir($handle);
	}

	return $files;
}
show_dir($directory);
$refresh_albums = array();
foreach ($results as $image)
{
	$image_path = utf8_decode($image);

	$album_path = substr($image_path, strripos($image_path, 'massimport/') + 11);
	$album_path = explode('/', $album_path);

	$filetype = getimagesize($image_path);
	$image_width = $filetype[0];
	$image_height = $filetype[1];

	switch ($filetype['mime'])
	{
		case 'image/jpeg':
		case 'image/jpg':
		case 'image/pjpeg':
			$image_filetype = '.jpg';
		break;

		case 'image/png':
		case 'image/x-png':
			$image_filetype = '.png';
		break;

		case 'image/gif':
			$image_filetype = '.gif';
		break;

		default:
		break;
	}
	$image_filename = md5(unique_id()) . $image_filetype;

	copy($image_path, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);
	@chmod($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename, 0777);

	// The source image is imported, so we delete it.
	@unlink($image_path);
	$time = $time + 1;

	$refresh_albums[] = $albums_tree [$album_path[0]] [$album_path[1]] [$album_path[2]];

	$image_size = getimagesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);
	$image_data['width'] = $image_size[0];
	$image_data['height'] = $image_size[1];

	if (($image_data['width'] > $gallery_config['max_width']) || ($image_data['height'] > $gallery_config['max_height']))
	{
		// Resize overside images
		switch ($image_filetype) 
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

		$src = $read_function($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);
		// Resize it
		if (($image_data['width'] / $gallery_config['max_width']) > ($image_data['height'] / $gallery_config['max_height']))
		{
			$thumbnail_width	= $gallery_config['max_width'];
			$thumbnail_height	= round($gallery_config['max_height'] * (($image_data['height'] / $gallery_config['max_height']) / ($image_data['width'] / $gallery_config['max_width'])));
		}
		else
		{
			$thumbnail_height	= $gallery_config['max_height'];
			$thumbnail_width	= round($gallery_config['max_width'] * (($image_data['width'] / $gallery_config['max_width']) / ($image_data['height'] / $gallery_config['max_height'])));
		}
		$thumbnail = ($gallery_config['gd_version'] == GDLIB1) ? @imagecreate($thumbnail_width, $thumbnail_height) : @imagecreatetruecolor($thumbnail_width, $thumbnail_height);
		$resize_function = ($gallery_config['gd_version'] == GDLIB1) ? 'imagecopyresized' : 'imagecopyresampled';
		$resize_function($thumbnail, $src, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $image_data['width'], $image_data['height']);
		imagedestroy($src);
		switch ($image_filetype)
		{
			case '.jpg':
				@imagejpeg($thumbnail, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename, 100);
			break;

			case '.png':
				@imagepng($thumbnail, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);
			break;

			case '.gif':
				@imagegif($thumbnail, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);
			break;
		}
		imagedestroy($thumbnail);
	}

	$multi_images[] = array(
		'image_name' 			=> str_replace("_", " ", utf8_substr(utf8_substr($image, strripos($image, '/') + 1), 0, -4)),
		'image_filename' 		=> $image_filename,
		'image_thumbnail'		=> '',
		'image_desc'			=> '',
		'image_desc_uid'		=> '',
		'image_desc_bitfield'	=> '',
		'image_user_id'			=> IMAGE_USER_ID,
		'image_username'		=> IMAGE_USERNAME,
		'image_user_colour'		=> IMAGE_USER_COLOUR,
		'image_user_ip'			=> $user->ip,
		'image_time'			=> $time,
		'image_album_id'		=> (int) $albums_tree [$album_path[0]] [$album_path[1]] [$album_path[2]],
		'image_status'			=> IMAGE_APPROVED,
		'filesize_upload'		=> filesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename),
	);

}
$db->sql_multi_insert(GALLERY_IMAGES_TABLE, $multi_images);

$refresh_albums = array_unique($refresh_albums);
foreach ($refresh_albums as $album)
{
	update_album_info($album);
}
if ($results)
{
	meta_refresh(1, append_sid("{$phpbb_root_path}develop/massimport.$phpEx", 'time=' . $time));
	trigger_error('There are still images left to import, please be patient!');
}
else
{
	$sql = 'SELECT COUNT(image_id) images
		FROM ' . GALLERY_IMAGES_TABLE . '
		WHERE image_status = ' . IMAGE_APPROVED;
	$result = $db->sql_query($sql);
	$image_counter = $db->sql_fetchfield('images');
	$db->sql_freeresult($result);
	set_config('num_images', $image_counter, true);

	trigger_error('All images were successful imported, have fun with the new images in the Gallery!');
}


function create_new_album ($parent_id, $album_name, $album_type)
{
	global $user, $db, $cache;

	$album_data = array(
		'album_name'					=> $album_name,
		'parent_id'						=> $parent_id,
		//left_id and right_id are created some lines later
		'album_parents'					=> '',
		'album_type'					=> $album_type,
		'album_desc_options'			=> 7,
		'album_desc'					=> '',
		'album_desc_uid'				=> '',
		'album_desc_bitfield'			=> '',
		'album_user_id'					=> 0,
		'album_last_username'			=> '',
		'album_image'					=> '',
	);
	if ($album_data['parent_id'])
	{
		$sql = 'SELECT left_id, right_id, album_type
			FROM ' . GALLERY_ALBUMS_TABLE . '
			WHERE album_id = ' . $album_data['parent_id'];
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
			SET left_id = left_id + 2, right_id = right_id + 2
			WHERE left_id > ' . $row['right_id'] . '
				AND album_user_id = ' . $album_data['album_user_id'];
		$db->sql_query($sql);

		$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
			SET right_id = right_id + 2
			WHERE ' . $row['left_id'] . ' BETWEEN left_id AND right_id
				AND album_user_id = ' . $album_data['album_user_id'];
		$db->sql_query($sql);

		$album_data['left_id'] = $row['right_id'];
		$album_data['right_id'] = $row['right_id'] + 1;
	}
	else
	{
		$sql = 'SELECT MAX(right_id) right_id
			FROM ' . GALLERY_ALBUMS_TABLE . '
			WHERE album_user_id = ' . $album_data['album_user_id'];
		$result = $db->sql_query($sql);
		$right_id = $db->sql_fetchfield('right_id');
		$db->sql_freeresult($result);

		$album_data['left_id'] = $right_id + 1;
		$album_data['right_id'] = $right_id + 2;
	}
	$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
	$album_data['album_id'] = $db->sql_nextid();
	$album_id = $album_data['album_id'];

	$sql = 'SELECT album_id, album_name, parent_id
		FROM ' . GALLERY_ALBUMS_TABLE . '
		WHERE album_id = ' . $album_data['album_id'];
	$result = $db->sql_query($sql);
	$album = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$copy_permissions = COPY_PERMISSIONS;
	if ($copy_permissions <> 0)
	{
		$sql = 'SELECT *
			FROM ' . GALLERY_PERMISSIONS_TABLE . '
			WHERE perm_album_id = ' . $copy_permissions;
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			$perm_data[] = array(
				'perm_role_id'			=> $row['perm_role_id'],
				'perm_album_id'			=> $album_id,
				'perm_user_id'			=> $row['perm_user_id'],
				'perm_group_id'			=> $row['perm_group_id'],
				'perm_system'			=> $row['perm_system'],
			);
		}
		$db->sql_freeresult($result);
		$db->sql_multi_insert(GALLERY_PERMISSIONS_TABLE, $perm_data);

		$sql_ary = array();
		$sql = 'SELECT *
			FROM ' . GALLERY_MODSCACHE_TABLE . '
			WHERE album_id = ' . $copy_permissions;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$sql_ary[] = array(
				'album_id'			=> $album_id,
				'user_id'			=> $row['user_id'],
				'username '			=> $row['username'],
				'group_id'			=> $row['group_id'],
				'group_name'		=> $row['group_name'],
				'display_on_index'	=> $row['display_on_index'],
			);
		}
		$db->sql_freeresult($result);
		$db->sql_multi_insert(GALLERY_MODSCACHE_TABLE, $sql_ary);
	}

	$cache->destroy('sql', GALLERY_MODSCACHE_TABLE);
	$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
	$cache->destroy('_albums');

	return $album;
}

?>