<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_gallery
{
	var $u_action;

	function main($id, $mode)
	{
		global $gallery_config, $db, $template, $user;
		global $gallery_root_path, $phpbb_root_path, $phpEx;
		$gallery_root_path = GALLERY_ROOT_PATH;

		include($phpbb_root_path . $gallery_root_path . 'includes/constants.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/functions.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/permissions.' . $phpEx);
		$gallery_config = load_gallery_config();

		$user->add_lang(array('mods/gallery_acp', 'mods/gallery'));
		$this->tpl_name = 'gallery_main';
		add_form_key('acp_gallery');
		$submode = request_var('submode', '');

		switch ($mode)
		{
			case 'overview':
				$title = 'ACP_GALLERY_OVERVIEW';
				$this->page_title = $user->lang[$title];

				$this->overview();
			break;

			case 'import_images':
				$title = 'ACP_IMPORT_ALBUMS';
				$this->page_title = $user->lang[$title];

				$this->import();
			break;

			case 'cleanup':
				$title = 'ACP_GALLERY_CLEANUP';
				$this->page_title = $user->lang[$title];

				$this->cleanup();
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
	}

	function overview()
	{
		global $auth, $config, $db, $gallery_config, $template, $user;
		global $gallery_root_path, $phpbb_root_path, $phpEx;

		$action = request_var('action', '');
		$id = request_var('i', '');
		$mode = 'overview';

		if (!confirm_box(true))
		{
			$confirm = false;
			$album_id = 0;
			switch ($action)
			{
				case 'images':
					$confirm = true;
					$confirm_lang = 'RESYNC_IMAGECOUNTS_CONFIRM';
				break;
				case 'personals':
					$confirm = true;
					$confirm_lang = 'CONFIRM_OPERATION';
				break;
				case 'stats':
					$confirm = true;
					$confirm_lang = 'CONFIRM_OPERATION';
				break;
				case 'last_images':
					$confirm = true;
					$confirm_lang = 'CONFIRM_OPERATION';
				break;
				case 'reset_rating':
					$album_id = request_var('reset_album_id', 0);
					$album_data = get_album_info($album_id);
					$confirm = true;
					$confirm_lang = sprintf($user->lang['RESET_RATING_CONFIRM'], $album_data['album_name']);
				break;
				case 'purge_cache':
					$confirm = true;
					$confirm_lang = 'GALLERY_PURGE_CACHE_EXPLAIN';
				break;
			}

			if ($confirm)
			{
				confirm_box(false, (($album_id) ? $confirm_lang : $user->lang[$confirm_lang]), build_hidden_fields(array(
					'i'			=> $id,
					'mode'		=> $mode,
					'action'	=> $action,
					'reset_album_id'	=> $album_id,
				)));
			}
		}
		else
		{
			switch ($action)
			{
				case 'images':
					if (!$auth->acl_get('a_board'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$total_images = $total_comments = 0;
					$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
						SET user_images = 0';
					$db->sql_query($sql);

					$sql = 'SELECT COUNT(image_id) num_images, image_user_id user_id, SUM(image_comments) AS num_comments
						FROM ' . GALLERY_IMAGES_TABLE . '
						WHERE image_status <> ' . IMAGE_UNAPPROVED . '
						GROUP BY image_user_id';
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$total_images += $row['num_images'];
						$total_comments += $row['num_comments'];
						$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
							SET user_images = ' . $row['num_images'] . '
							WHERE user_id = ' . $row['user_id'];
						$db->sql_query($sql);

						if ($db->sql_affectedrows() <= 0)
						{
							$sql_ary = array(
								'user_id'				=> $row['user_id'],
								'user_images'			=> $row['num_images'],
							);
							$sql = 'INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
							$db->sql_query($sql);
						}
					}
					$db->sql_freeresult($result);

					set_config('num_images', $total_images, true);
					set_gallery_config('num_comments', $total_comments, true);
					trigger_error($user->lang['RESYNCED_IMAGECOUNTS'] . adm_back_link($this->u_action));
				break;

				case 'personals':
					if (!$auth->acl_get('a_board'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$sql = 'UPDATE ' . GALLERY_USERS_TABLE . "
						SET personal_album_id = 0";
					$db->sql_query($sql);

					$sql = 'SELECT album_id, album_user_id
						FROM ' . GALLERY_ALBUMS_TABLE . '
						WHERE album_user_id <> ' . NON_PERSONAL_ALBUMS . '
							AND parent_id = 0
						GROUP BY album_user_id';
					$result = $db->sql_query($sql);

					$number_of_personals = 0;
					while ($row = $db->sql_fetchrow($result))
					{
						$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
							SET personal_album_id = ' . $row['album_id'] . '
							WHERE user_id = ' . $row['album_user_id'];
						$db->sql_query($sql);

						if ($db->sql_affectedrows() <= 0)
						{
							$sql_ary = array(
								'user_id'				=> $row['album_user_id'],
								'personal_album_id'		=> $row['album_id'],
							);
							$sql = 'INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
							$db->sql_query($sql);
						}
						$number_of_personals++;
					}
					$db->sql_freeresult($result);
					set_gallery_config('personal_counter', $number_of_personals);

					// Update the config for the statistic on the index
					$sql_array = array(
						'SELECT'		=> 'a.album_id, u.user_id, u.username, u.user_colour',
						'FROM'			=> array(GALLERY_ALBUMS_TABLE => 'a'),

						'LEFT_JOIN'		=> array(
							array(
								'FROM'		=> array(USERS_TABLE => 'u'),
								'ON'		=> 'u.user_id = a.album_user_id',
							),
						),

						'WHERE'			=> 'a.album_user_id <> ' . NON_PERSONAL_ALBUMS . ' AND a.parent_id = 0',
						'ORDER_BY'		=> 'a.album_id DESC',
					);
					$sql = $db->sql_build_query('SELECT', $sql_array);

					$result = $db->sql_query_limit($sql, 1);
					$newest_pgallery = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					set_gallery_config('newest_pgallery_user_id', (int) $newest_pgallery['user_id']);
					set_gallery_config('newest_pgallery_username', (string) $newest_pgallery['username']);
					set_gallery_config('newest_pgallery_user_colour', (string) $newest_pgallery['user_colour']);
					set_gallery_config('newest_pgallery_album_id', (int) $newest_pgallery['album_id']);

					trigger_error($user->lang['RESYNCED_PERSONALS'] . adm_back_link($this->u_action));
				break;

				case 'stats':
					if (!$auth->acl_get('a_board'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					// Hopefully this won't take to long! >> I think we must make it batchwise
					$sql = 'SELECT image_id, image_filename, image_thumbnail
						FROM ' . GALLERY_IMAGES_TABLE . '
						WHERE filesize_upload = 0';
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$sql_ary = array(
							'filesize_upload'		=> @filesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $row['image_filename']),
							'filesize_medium'		=> @filesize($phpbb_root_path . GALLERY_MEDIUM_PATH . $row['image_thumbnail']),
							'filesize_cache'		=> @filesize($phpbb_root_path . GALLERY_CACHE_PATH . $row['image_thumbnail']),
						);
						$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE ' . $db->sql_in_set('image_id', $row['image_id']);
						$db->sql_query($sql);
					}
					$db->sql_freeresult($result);

					redirect($this->u_action);
				break;

				case 'last_images':
					$sql = 'SELECT album_id
						FROM ' . GALLERY_ALBUMS_TABLE;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						// 5 sql's per album, but you don't run this daily ;)
						update_album_info($row['album_id']);
					}
					$db->sql_freeresult($result);
					trigger_error($user->lang['RESYNCED_LAST_IMAGES'] . adm_back_link($this->u_action));
				break;

				case 'reset_rating':
					$album_id = request_var('reset_album_id', 0);
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET image_rates = 0,
							image_rate_points = 0,
							image_rate_avg = 0
						WHERE image_album_id = ' . $album_id;
					$db->sql_query($sql);

					$image_ids = array();
					$sql = 'SELECT image_id
						FROM ' . GALLERY_IMAGES_TABLE . '
						WHERE image_album_id = ' . $album_id;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$image_ids[] = $row['image_id'];
					}
					$db->sql_freeresult($result);

					$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . '
						WHERE ' . $db->sql_in_set('rate_image_id', $image_ids);
					$db->sql_query($sql);

					trigger_error($user->lang['RESET_RATING_COMPLETED'] . adm_back_link($this->u_action));
				break;

				case 'purge_cache':
					if ($user->data['user_type'] != USER_FOUNDER)
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$cache_dir = @opendir($phpbb_root_path . GALLERY_CACHE_PATH);
					while ($cache_file = @readdir($cache_dir))
					{
						if (preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $cache_file))
						{
							@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $cache_file);
						}
					}
					@closedir($cache_dir);

					$medium_dir = @opendir($phpbb_root_path . GALLERY_MEDIUM_PATH);
					while ($medium_file = @readdir($medium_dir))
					{
						if (preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $medium_file))
						{
							@unlink($phpbb_root_path . GALLERY_MEDIUM_PATH . $medium_file);
						}
					}
					@closedir($medium_dir);

					$sql_ary = array(
						'filesize_medium'		=> @filesize($phpbb_root_path . GALLERY_MEDIUM_PATH . $row['image_thumbnail']),
						'filesize_cache'		=> @filesize($phpbb_root_path . GALLERY_CACHE_PATH . $row['image_thumbnail']),
					);
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql_ary);
					$db->sql_query($sql);

					trigger_error($user->lang['PURGED_CACHE'] . adm_back_link($this->u_action));
				break;
			}
		}

		if (!function_exists('mod_version_check'))
		{
			include($phpbb_root_path . $gallery_root_path . 'includes/functions_version_check.' . $phpEx);
		}
		mod_version_check();

		$boarddays = (time() - $config['board_startdate']) / 86400;
		$images_per_day = sprintf('%.2f', $config['num_images'] / $boarddays);

		$sql = 'SELECT COUNT(album_user_id) num_albums
			FROM ' . GALLERY_ALBUMS_TABLE . '
			WHERE album_user_id = 0';
		$result = $db->sql_query($sql);
		$num_albums = (int) $db->sql_fetchfield('num_albums');
		$db->sql_freeresult($result);

		$sql = 'SELECT SUM(filesize_upload) as stat, SUM(filesize_medium) as stat_medium, SUM(filesize_cache) as stat_cache
			FROM ' . GALLERY_IMAGES_TABLE;
		$result = $db->sql_query($sql);
		$dir_sizes = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'S_GALLERY_OVERVIEW'			=> true,
			'ACP_GALLERY_TITLE'				=> $user->lang['ACP_GALLERY_OVERVIEW'],
			'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ACP_GALLERY_OVERVIEW_EXPLAIN'],

			'TOTAL_IMAGES'			=> $config['num_images'],
			'IMAGES_PER_DAY'		=> $images_per_day,
			'TOTAL_ALBUMS'			=> $num_albums,
			'TOTAL_PERSONALS'		=> $gallery_config['personal_counter'],
			'GUPLOAD_DIR_SIZE'		=> get_formatted_filesize($dir_sizes['stat']),
			'MEDIUM_DIR_SIZE'		=> get_formatted_filesize($dir_sizes['stat_medium']),
			'CACHE_DIR_SIZE'		=> get_formatted_filesize($dir_sizes['stat_cache']),
			'GALLERY_VERSION'		=> $gallery_config['phpbb_gallery_version'],

			'S_FOUNDER'				=> ($user->data['user_type'] == USER_FOUNDER) ? true : false,
		));
	}

	function import()
	{
		global $gallery_config, $config, $db, $template, $user;
		global $gallery_root_path, $phpbb_root_path, $phpEx;

		$import_schema = request_var('import_schema', '');
		$images = request_var('images', array(''), true);
		$submit = (isset($_POST['submit'])) ? true : ((empty($images)) ? false : true);

		if ($import_schema)
		{
			if (file_exists($phpbb_root_path . GALLERY_IMPORT_PATH . $import_schema . '.' . $phpEx))
			{
				include($phpbb_root_path . GALLERY_IMPORT_PATH . $import_schema . '.' . $phpEx);
				// Replace the md5 with the ' again and remove the space at the end to prevent \' troubles
				$user_data['username'] = utf8_substr(str_replace("{{$import_schema}}", "'", $user_data['username']), 0, -1);
				$image_name = utf8_substr(str_replace("{{$import_schema}}", "'", $image_name), 0, -1);
			}
			else
			{
				trigger_error(sprintf($user->lang['MISSING_IMPORT_SCHEMA'], ($import_schema . '.' . $phpEx)), E_USER_WARNING);
			}

			$images_loop = 0;
			foreach ($images as $image_src)
			{
				/**
				* Import the images
				*/
				$image_src = str_replace("{{$import_schema}}", "'", $image_src);
				$image_src_full = $phpbb_root_path . GALLERY_IMPORT_PATH . utf8_decode($image_src);
				if (file_exists($image_src_full))
				{
					$filetype = getimagesize($image_src_full);
					$image_width = $filetype[0];
					$image_height = $filetype[1];

					$filetype_ext = '';
					switch ($filetype['mime'])
					{
						case 'image/jpeg':
						case 'image/jpg':
						case 'image/pjpeg':
							$filetype_ext = '.jpg';
							$read_function = 'imagecreatefromjpeg';
							if ((substr(strtolower($image_src), -4) != '.jpg') && (substr(strtolower($image_src), -5) != '.jpeg'))
							{
								trigger_error(sprintf($user->lang['FILETYPE_MIMETYPE_MISMATCH'], $image_src, $filetype['mime']), E_USER_WARNING);
							}
						break;

						case 'image/png':
						case 'image/x-png':
							$filetype_ext = '.png';
							$read_function = 'imagecreatefrompng';
							if (substr(strtolower($image_src), -4) != '.png')
							{
								trigger_error(sprintf($user->lang['FILETYPE_MIMETYPE_MISMATCH'], $image_src, $filetype['mime']), E_USER_WARNING);
							}
						break;

						case 'image/gif':
						case 'image/giff':
							$filetype_ext = '.gif';
							$read_function = 'imagecreatefromgif';
							if (substr(strtolower($image_src), -4) != '.gif')
							{
								trigger_error(sprintf($user->lang['FILETYPE_MIMETYPE_MISMATCH'], $image_src, $filetype['mime']), E_USER_WARNING);
							}
						break;

						default:
							trigger_error('NOT_ALLOWED_FILE_TYPE');
						break;
					}
					$image_filename = md5(unique_id()) . $filetype_ext;

					if (!@move_uploaded_file($image_src_full, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename))
					{
						if (!@copy($image_src_full, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename))
						{
							$user->add_lang('posting');
							trigger_error(sprintf($user->lang['GENERAL_UPLOAD_ERROR'], $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename), E_USER_WARNING);
						}
					}
					@chmod($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename, 0777);
					// The source image is imported, so we delete it.
					@unlink($image_src_full);

					$sql_ary = array(
						'image_filename' 		=> $image_filename,
						'image_thumbnail'		=> '',
						'image_desc'			=> '',
						'image_desc_uid'		=> '',
						'image_desc_bitfield'	=> '',
						'image_user_id'			=> $user_data['user_id'],
						'image_username'		=> $user_data['username'],
						'image_username_clean'	=> utf8_clean_string($user_data['username']),
						'image_user_colour'		=> $user_data['user_colour'],
						'image_user_ip'			=> $user->ip,
						'image_time'			=> $start_time + $done_images,
						'image_album_id'		=> $album_id,
						'image_status'			=> IMAGE_APPROVED,
						'image_exif_data'		=> '',
					);

					$exif = array();
					if (function_exists('exif_read_data'))
					{
						$exif = @exif_read_data($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename, 0, true);
					}
					if (!empty($exif["EXIF"]))
					{
						// Unset invalid exifs
						foreach ($exif as $key => $array)
						{
							if (!in_array($key, array('EXIF', 'IFD0')))
							{
								unset($exif[$key]);
							}
							else
							{
								foreach ($exif[$key] as $subkey => $array)
								{
									if (!in_array($subkey, array('DateTimeOriginal', 'FocalLength', 'ExposureTime', 'FNumber', 'ISOSpeedRatings', 'WhiteBalance', 'Flash', 'Model', 'ExposureProgram', 'ExposureBiasValue', 'MeteringMode')))
									{
										unset($exif[$key][$subkey]);
									}
								}
							}
						}
						$sql_ary['image_exif_data'] = serialize ($exif);
						$sql_ary['image_has_exif'] = EXIF_DBSAVED;
					}
					else
					{
						$sql_ary['image_exif_data'] = '';
						$sql_ary['image_has_exif'] = EXIF_UNAVAILABLE;
					}

					if (($image_width > $gallery_config['max_width']) || ($image_height > $gallery_config['max_height']))
					{
						/**
						* Resize overside images
						*/
						if ($gallery_config['resize_images'])
						{
							$src = $read_function($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename);
							// Resize it
							if (($image_width / $gallery_config['max_width']) > ($image_height / $gallery_config['max_height']))
							{
								$thumbnail_width	= $gallery_config['max_width'];
								$thumbnail_height	= round($gallery_config['max_height'] * (($image_height / $gallery_config['max_height']) / ($image_width / $gallery_config['max_width'])));
							}
							else
							{
								$thumbnail_height	= $gallery_config['max_height'];
								$thumbnail_width	= round($gallery_config['max_width'] * (($image_width / $gallery_config['max_width']) / ($image_height / $gallery_config['max_height'])));
							}
							$thumbnail = ($gallery_config['gd_version'] == GDLIB1) ? @imagecreate($thumbnail_width, $thumbnail_height) : @imagecreatetruecolor($thumbnail_width, $thumbnail_height);
							$resize_function = ($gallery_config['gd_version'] == GDLIB1) ? 'imagecopyresized' : 'imagecopyresampled';
							$resize_function($thumbnail, $src, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $image_width, $image_height);
							imagedestroy($src);
							switch ($filetype_ext)
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
					}
					else if ($sql_ary['image_has_exif'] == EXIF_DBSAVED)
					{
						// Image was not resized, so we can pull the Exif from the image to save db-memory.
						$sql_ary['image_has_exif'] = EXIF_AVAILABLE;
						$sql_ary['image_exif_data'] = '';
					}
					// Try to get real filesize from temporary folder (not always working) ;)
					$sql_ary['filesize_upload'] = (@filesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename)) ? @filesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_filename) : 0;

					if ($filename || ($image_name == ''))
					{
						$sql_ary['image_name'] = str_replace("_", " ", utf8_substr($image_src, 0, -4));
					}
					else
					{
						$sql_ary['image_name'] = str_replace('{NUM}', $num_offset + $done_images, $image_name);
					}
					$sql_ary['image_name_clean'] = utf8_clean_string($sql_ary['image_name']);

					// Put the images into the database
					$db->sql_query('INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
					$done_images++;
				}

				// Remove the image from the list
				unset($images[$images_loop]);
				$images_loop++;
				if ($images_loop == 10)
				{
					// We made 10 images, so we end for this turn
					break;
				}
			}
			if ($images_loop)
			{
				$sql = 'UPDATE ' . GALLERY_USERS_TABLE . "
					SET user_images = user_images + $images_loop
					WHERE user_id = " . $user_data['user_id'];
				$db->sql_query($sql);
				if ($db->sql_affectedrows() <= 0)
				{
					$sql_ary = array(
						'user_id'				=> $user_data['user_id'],
						'user_images'			=> $images_loop,
					);
					$sql = 'INSERT INTO ' . GALLERY_USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
					$db->sql_query($sql);
				}
				// Since phpBB 3.0.5 this is the better solution
				// If the function does not exist, we load it from gallery/includes/functions_phpbb.php
				set_config_count('num_images', $images_loop);
				$todo_images = $todo_images - $images_loop;
			}
			update_album_info($album_id);

			if (!$todo_images)
			{
				unlink($phpbb_root_path . GALLERY_IMPORT_PATH . $import_schema . '.' . $phpEx);
				trigger_error(sprintf($user->lang['IMPORT_FINISHED'], $done_images) . adm_back_link($this->u_action));
			}
			else
			{
				// Write the new list
				$this->create_import_schema($import_schema, $album_id, $user_data, $start_time, $num_offset, $done_images, $todo_images, $image_name, $filename, $images);

				// Redirect
				$forward_url = $this->u_action . "&amp;import_schema=$import_schema";
				meta_refresh(1, $forward_url);
				trigger_error(sprintf($user->lang['IMPORT_DEBUG_MES'], $done_images, $todo_images));
			}
		}
		else if ($submit)
		{
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}
			if (!$images)
			{
				trigger_error('NO_FILE_SELECTED', E_USER_WARNING);
			}

			// Who is the uploader?
			$username = request_var('username', '', true);
			$user_id = 0;
			if ($username)
			{
				if (!function_exists('user_get_id_name'))
				{
					include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
				}
				user_get_id_name($user_id, $username);
			}
			if (is_array($user_id))
			{
				$user_id = $user_id[0];
			}
			if (!$user_id)
			{
				$user_id = $user->data['user_id'];
			}

			$sql = 'SELECT username, user_colour, user_id
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $user_id;
			$result = $db->sql_query($sql);
			$user_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			if (!$user_row)
			{
				trigger_error('HACKING_ATTEMPT', E_USER_WARNING);
			}

			// Where do we put them to?
			$album_id = request_var('album_id', 0);
			$sql = 'SELECT album_id, album_name
				FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE album_id = ' . $album_id;
			$result = $db->sql_query($sql);
			$album_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			if (!$album_row)
			{
				trigger_error('HACKING_ATTEMPT', E_USER_WARNING);
			}

			$start_time = time();
			$import_schema = md5($start_time);
			$filename = (request_var('filename', '') == 'filename') ? true : false;
			$image_name = request_var('image_name', '', true);
			$num_offset = request_var('image_num', 0);

			$this->create_import_schema($import_schema, $album_row['album_id'], $user_row, $start_time, $num_offset, 0, sizeof($images), $image_name, $filename, $images);

			$forward_url = $this->u_action . "&amp;import_schema=$import_schema";
			meta_refresh(2, $forward_url);
			trigger_error('IMPORT_SCHEMA_CREATED');
		}

		$handle = opendir($phpbb_root_path . GALLERY_IMPORT_PATH);
		$files = array();
		while ($file = readdir($handle))
		{
			if (!is_dir($phpbb_root_path . GALLERY_IMPORT_PATH . "$file") && (
			((substr(strtolower($file), -4) == '.png') && $gallery_config['png_allowed']) ||
			((substr(strtolower($file), -4) == '.gif') && $gallery_config['gif_allowed']) ||
			((substr(strtolower($file), -4) == '.jpg') && $gallery_config['jpg_allowed']) ||
			((substr(strtolower($file), -5) == '.jpeg') && $gallery_config['jpg_allowed'])
			))
			{
				$files[strtolower($file)] = $file;
			}
		}
		closedir($handle);

		// Sort the files by name again
		ksort($files);
		foreach ($files as $file)
		{
			$template->assign_block_vars('imagerow', array(
				'FILE_NAME'				=> utf8_encode($file),
			));
		}

		$template->assign_vars(array(
			'S_IMPORT_IMAGES'				=> true,
			'ACP_GALLERY_TITLE'				=> $user->lang['ACP_IMPORT_ALBUMS'],
			'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ACP_IMPORT_ALBUMS_EXPLAIN'],
			'L_IMPORT_DIR_EMPTY'			=> sprintf($user->lang['IMPORT_DIR_EMPTY'], GALLERY_IMPORT_PATH),
			'S_ALBUM_IMPORT_ACTION'			=> $this->u_action,
			'S_SELECT_IMPORT' 				=> gallery_albumbox(false, 'album_id', false, false, false, 0, ALBUM_UPLOAD),
			'U_FIND_USERNAME'				=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=acp_gallery&amp;field=username&amp;select_single=true'),
		));
	}

	function create_import_schema($import_schema, $album_id, $user_row, $start_time, $num_offset, $done_images, $todo_images, $image_name, $filename, $images)
	{
		global $phpbb_root_path, $phpEx;

		$import_file = "<?php\n\nif (!defined('IN_PHPBB'))\n{\n	exit;\n}\n\n";
		$import_file .= "\$album_id = " . $album_id . ";\n";
		$import_file .= "\$start_time = " . $start_time . ";\n";
		$import_file .= "\$num_offset = " . $num_offset . ";\n";
		$import_file .= "\$done_images = " . $done_images . ";\n";
		$import_file .= "\$todo_images = " . $todo_images . ";\n";
		// We add a space at the end of the name, to not get troubles with \';
		$import_file .= "\$image_name = '" . str_replace("'", "{{$import_schema}}", $image_name) . " ';\n";
		$import_file .= "\$filename = " . (($filename) ? 'true' : 'false') . ";\n";
		$import_file .= "\$user_data = array(\n";
		$import_file .= "	'user_id'		=> " . $user_row['user_id'] . ",\n";
		// We add a space at the end of the name, to not get troubles with \',
		$import_file .= "	'username'		=> '" . str_replace("'", "{{$import_schema}}", $user_row['username']) . " ',\n";
		$import_file .= "	'user_colour'	=> '" . $user_row['user_colour'] . "',\n";
		$import_file .= ");\n";
		$import_file .= "\$images = array(\n";

		// We need to replace some characters to find the image and not produce syntax errors
		$replace_chars = array("'", "&amp;");
		$replace_with = array("{{$import_schema}}", "&");

		foreach ($images as $image_src)
		{
			$import_file .= "	'" . str_replace($replace_chars, $replace_with, $image_src) . "',\n";
		}
		$import_file .= ");\n\n?" . '>'; // Done this to prevent highlighting editors getting confused!

		// Write to disc
		if ((file_exists($phpbb_root_path . GALLERY_IMPORT_PATH . $import_schema . '.' . $phpEx) && is_writable($phpbb_root_path . GALLERY_IMPORT_PATH . $import_schema . '.' . $phpEx)) || is_writable($phpbb_root_path . GALLERY_IMPORT_PATH))
		{
			$written = true;
			if (!($fp = @fopen($phpbb_root_path . GALLERY_IMPORT_PATH . $import_schema . '.' . $phpEx, 'w')))
			{
				$written = false;
			}
			if (!(@fwrite($fp, $import_file)))
			{
				$written = false;
			}
			@fclose($fp);
		}
	}


	function cleanup()
	{
		global $auth, $cache, $db, $gallery_config, $phpbb_root_path, $template, $user;

		$delete = (isset($_POST['delete'])) ? true : false;
		$submit = (isset($_POST['submit'])) ? true : false;

		$missing_sources = request_var('source', array(0));
		$missing_entries = request_var('entry', array(''), true);
		$missing_authors = request_var('author', array(0), true);
		$missing_comments = request_var('comment', array(0), true);
		$missing_personals = request_var('personal', array(0), true);
		$personals_bad = request_var('personal_bad', array(0), true);
		$s_hidden_fields = build_hidden_fields(array(
			'source'		=> $missing_sources,
			'entry'			=> $missing_entries,
			'author'		=> $missing_authors,
			'comment'		=> $missing_comments,
			'personal'		=> $missing_personals,
			'personal_bad'	=> $personals_bad,
		));

		if ($submit)
		{
			if ($missing_authors)
			{
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
					SET image_user_id = ' . ANONYMOUS . ",
						image_user_colour = ''
					WHERE " . $db->sql_in_set('image_id', $missing_authors);
				$db->sql_query($sql);
			}
			if ($missing_comments)
			{
				$sql = 'UPDATE ' . GALLERY_COMMENTS_TABLE . ' 
					SET comment_user_id = ' . ANONYMOUS . ",
						comment_user_colour = ''
					WHERE " . $db->sql_in_set('comment_id', $missing_comments);
				$db->sql_query($sql);
			}
			trigger_error($user->lang['CLEAN_CHANGED'] . adm_back_link($this->u_action));
		}

		if (confirm_box(true))
		{
			$message = '';
			if ($missing_sources)
			{
				$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $missing_sources);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . ' WHERE ' . $db->sql_in_set('comment_image_id', $missing_sources);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . ' WHERE ' . $db->sql_in_set('rate_image_id', $missing_sources);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_REPORTS_TABLE . ' WHERE ' . $db->sql_in_set('report_image_id', $missing_sources);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $missing_sources);
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $missing_sources);
				$db->sql_query($sql);
				$message .= $user->lang['CLEAN_SOURCES_DONE'];
			}
			if ($missing_entries)
			{
				foreach ($missing_entries as $missing_image)
				{
					@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . utf8_decode($missing_image));
				}
				$message .= $user->lang['CLEAN_ENTRIES_DONE'];
			}
			if ($missing_authors)
			{
				$deleted_images = array();
				$sql = 'SELECT image_id, image_thumbnail, image_filename
					FROM ' . GALLERY_IMAGES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $missing_authors);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					// Delete the files themselves
					@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $row['image_thumbnail']);
					@unlink($phpbb_root_path . GALLERY_MEDIUM_PATH . $row['image_filename']);
					@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $row['image_filename']);
					$deleted_images[] = $row['image_id'];
				}
				// we have all image_ids in $deleted_images which are deleted
				// aswell as the album_ids in $deleted_albums
				// so now drop the comments, ratings, images and albums
				if ($deleted_images)
				{
					$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . ' WHERE ' . $db->sql_in_set('comment_image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . ' WHERE ' . $db->sql_in_set('rate_image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_REPORTS_TABLE . ' WHERE ' . $db->sql_in_set('report_image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $deleted_images);
					$db->sql_query($sql);
				}
				$message .= $user->lang['CLEAN_AUTHORS_DONE'];
			}
			if ($missing_comments)
			{
				$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . ' WHERE ' . $db->sql_in_set('comment_id', $missing_comments);
				$db->sql_query($sql);
				$message .= $user->lang['CLEAN_COMMENTS_DONE'];
			}
			if ($missing_personals || $personals_bad)
			{
				$delete_albums = array_merge($missing_personals, $personals_bad);

				$deleted_images = $deleted_albums = array(0);
				$sql = 'SELECT COUNT(album_user_id) personal_counter
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE parent_id = 0
						AND ' . $db->sql_in_set('album_user_id', $delete_albums);
				$result = $db->sql_query($sql);
				$remove_personal_counter = $db->sql_fetchfield('personal_counter');
				$db->sql_freeresult($result);
				$sql = 'SELECT album_id
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE ' . $db->sql_in_set('album_user_id', $delete_albums);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$deleted_albums[] = $row['album_id'];
				}
				$db->sql_freeresult($result);
				$sql = 'SELECT image_id, image_thumbnail, image_filename
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE ' . $db->sql_in_set('image_album_id', $deleted_albums);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $row['image_thumbnail']);
					@unlink($phpbb_root_path . GALLERY_MEDIUM_PATH . $row['image_filename']);
					@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $row['image_filename']);
					$deleted_images[] = $row['image_id'];
				}
				$db->sql_freeresult($result);
				if ($deleted_images)
				{
					$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . ' WHERE ' . $db->sql_in_set('comment_image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . ' WHERE ' . $db->sql_in_set('rate_image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_REPORTS_TABLE . ' WHERE ' . $db->sql_in_set('report_image_id', $deleted_images);
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . ' WHERE ' . $db->sql_in_set('image_id', $deleted_images);
					$db->sql_query($sql);
				}
				$sql = 'DELETE FROM ' . GALLERY_ALBUMS_TABLE . ' WHERE ' . $db->sql_in_set('album_id', $deleted_albums);
				$db->sql_query($sql);
				set_gallery_config_count('personal_counter', 0 - $remove_personal_counter);

				if (in_array($gallery_config['newest_pgallery_album_id'], $deleted_albums))
				{
					// Update the config for the statistic on the index
					if ($gallery_config['personal_counter'] > 0)
					{
						$sql_array = array(
							'SELECT'		=> 'a.album_id, u.user_id, u.username, u.user_colour',
							'FROM'			=> array(GALLERY_ALBUMS_TABLE => 'a'),

							'LEFT_JOIN'		=> array(
								array(
									'FROM'		=> array(USERS_TABLE => 'u'),
									'ON'		=> 'u.user_id = a.album_user_id',
								),
							),

							'WHERE'			=> 'a.album_user_id <> ' . NON_PERSONAL_ALBUMS . ' AND a.parent_id = 0',
							'ORDER_BY'		=> 'a.album_id DESC',
						);
						$sql = $db->sql_build_query('SELECT', $sql_array);

						$result = $db->sql_query_limit($sql, 1);
						$newest_pgallery = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						set_gallery_config('newest_pgallery_user_id', (int) $newest_pgallery['user_id']);
						set_gallery_config('newest_pgallery_username', (string) $newest_pgallery['username']);
						set_gallery_config('newest_pgallery_user_colour', (string) $newest_pgallery['user_colour']);
						set_gallery_config('newest_pgallery_album_id', (int) $newest_pgallery['album_id']);
					}
					else
					{
						set_gallery_config('newest_pgallery_user_id', 0);
						set_gallery_config('newest_pgallery_username', '');
						set_gallery_config('newest_pgallery_user_colour', '');
						set_gallery_config('newest_pgallery_album_id', 0);
					}
				}
				$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
					SET personal_album_id = 0
					WHERE ' . $db->sql_in_set('user_id', $delete_albums);
				$db->sql_query($sql);
				if ($missing_personals)
				{
					$message .= $user->lang['CLEAN_PERSONALS_DONE'];
				}
				if ($personals_bad)
				{
					$message .= $user->lang['CLEAN_PERSONALS_BAD_DONE'];
				}
			}

			// Make sure the overall image & comment count is correct...
			$sql = 'SELECT COUNT(image_id) AS num_images, SUM(image_comments) AS num_comments
				FROM ' . GALLERY_IMAGES_TABLE . '
				WHERE image_status <> ' . IMAGE_UNAPPROVED;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			set_config('num_images', (int) $row['num_images'], true);
			set_gallery_config('num_comments', (int) $row['num_comments'], true);

			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
			$cache->destroy('sql', GALLERY_COMMENTS_TABLE);
			$cache->destroy('sql', GALLERY_FAVORITES_TABLE);
			$cache->destroy('sql', GALLERY_IMAGES_TABLE);
			$cache->destroy('sql', GALLERY_RATES_TABLE);
			$cache->destroy('sql', GALLERY_REPORTS_TABLE);
			$cache->destroy('sql', GALLERY_WATCH_TABLE);
			$cache->destroy('_albums');

			trigger_error($message . adm_back_link($this->u_action));
		}
		else if (($delete) || (isset($_POST['cancel'])))
		{
			if (isset($_POST['cancel']))
			{
				trigger_error($user->lang['CLEAN_GALLERY_ABORT'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
			else
			{
				$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN'];
				if ($missing_sources)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN_SOURCES'] . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($missing_entries)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN_ENTRIES'] . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($missing_authors)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN_AUTHORS'] . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($missing_comments)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN_COMMENTS'] . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($personals_bad || $missing_personals)
				{
					$sql = 'SELECT album_name, album_user_id
						FROM ' . GALLERY_ALBUMS_TABLE . '
						WHERE ' . $db->sql_in_set('album_user_id', array_merge($missing_personals, $personals_bad));
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						if (in_array($row['album_user_id'], $personals_bad))
						{
							$personals_bad_names[] = $row['album_name'];
						}
						else
						{
							$missing_personals_names[] = $row['album_name'];
						}
					}
					$db->sql_freeresult($result);
				}
				if ($missing_personals)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = sprintf($user->lang['CONFIRM_CLEAN_PERSONALS'], implode(', ', $missing_personals_names)) . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($personals_bad)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = sprintf($user->lang['CONFIRM_CLEAN_PERSONALS_BAD'], implode(', ', $personals_bad_names)) . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				confirm_box(false, 'CLEAN_GALLERY', $s_hidden_fields);
			}
		}

		$requested_source = array();
		$sql_array = array(
			'SELECT'		=> 'i.image_id, i.image_name, i.image_filemissing, i.image_filename, i.image_username, u.user_id',
			'FROM'			=> array(GALLERY_IMAGES_TABLE => 'i'),

			'LEFT_JOIN'		=> array(
				array(
					'FROM'		=> array(USERS_TABLE => 'u'),
					'ON'		=> 'u.user_id = i.image_user_id',
				),
			),
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['image_filemissing'])
			{
				$template->assign_block_vars('sourcerow', array(
					'IMAGE_ID'		=> $row['image_id'],
					'IMAGE_NAME'	=> $row['image_name'],
				));
			}
			if (!$row['user_id'])
			{
				$template->assign_block_vars('authorrow', array(
					'IMAGE_ID'		=> $row['image_id'],
					'AUTHOR_NAME'	=> $row['image_username'],
				));
			}
			$requested_source[] = $row['image_filename'];
		}
		$db->sql_freeresult($result);

		$check_mode = request_var('check_mode', '');
		if ($check_mode == 'source')
		{
			$source_missing = array();

			// Reset the status: a image might have been viewed without file but the file is back
			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
				SET image_filemissing = 0';
			$db->sql_query($sql);

			$sql = 'SELECT image_id, image_filename, image_filemissing
				FROM ' . GALLERY_IMAGES_TABLE;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if (!file_exists($phpbb_root_path . GALLERY_UPLOAD_PATH . $row['image_filename']))
				{
					$source_missing[] = $row['image_id'];
				}
			}
			$db->sql_freeresult($result);

			if ($source_missing)
			{
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . "
					SET image_filemissing = 1
					WHERE " . $db->sql_in_set('image_id', $source_missing);
				$db->sql_query($sql);
			}
		}

		if ($check_mode == 'entry')
		{
			$directory = $phpbb_root_path . GALLERY_UPLOAD_PATH;
			$handle = opendir($directory);
			while ($file = readdir($handle))
			{
				if (!is_dir($directory . $file) &&
				 ((substr(strtolower($file), '-4') == '.png') || (substr(strtolower($file), '-4') == '.gif') || (substr(strtolower($file), '-4') == '.jpg'))
				 && !in_array($file, $requested_source)
				)
				{
					if ((strpos($file, 'image_not_exist') !== false) || (strpos($file, 'not_authorised') !== false) || (strpos($file, 'no_hotlinking') !== false))
					{
						continue;
					}

					$template->assign_block_vars('entryrow', array(
						'FILE_NAME'				=> utf8_encode($file),
					));
				}
			}
			closedir($handle);
		}


		$sql_array = array(
			'SELECT'		=> 'c.comment_id, c.comment_image_id, c.comment_username, u.user_id',
			'FROM'			=> array(GALLERY_COMMENTS_TABLE => 'c'),

			'LEFT_JOIN'		=> array(
				array(
					'FROM'		=> array(USERS_TABLE => 'u'),
					'ON'		=> 'u.user_id = c.comment_user_id',
				),
			),
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if (!$row['user_id'])
			{
				$template->assign_block_vars('commentrow', array(
					'COMMENT_ID'	=> $row['comment_id'],
					'IMAGE_ID'		=> $row['comment_image_id'],
					'AUTHOR_NAME'	=> $row['comment_username'],
				));
			}
		}
		$db->sql_freeresult($result);

		$sql_array = array(
			'SELECT'		=> 'a.album_id, a.album_user_id, a.album_name, u.user_id, a.album_images_real',
			'FROM'			=> array(GALLERY_ALBUMS_TABLE => 'a'),

			'LEFT_JOIN'		=> array(
				array(
					'FROM'		=> array(USERS_TABLE => 'u'),
					'ON'		=> 'u.user_id = a.album_user_id',
				),
			),

			'WHERE'			=> 'a.album_user_id <> ' . NON_PERSONAL_ALBUMS . ' AND a.parent_id = 0',
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		$personalrow = $personal_bad_row = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$album = array(
				'user_id'		=> $row['album_user_id'],
				'album_id'		=> $row['album_id'],
				'album_name'	=> $row['album_name'],
				'images'		=> $row['album_images_real'],
			);
			if (!$row['user_id'])
			{
				$personalrow[$row['album_user_id']] = $album;
			}
			$personal_bad_row[$row['album_user_id']] = $album;
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT ga.album_user_id, ga.album_images_real
			FROM ' . GALLERY_ALBUMS_TABLE . ' ga
			WHERE ga.album_user_id <> ' . NON_PERSONAL_ALBUMS . '
				AND ga.parent_id <> 0';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if (isset($personalrow[$row['album_user_id']]))
			{
				$personalrow[$row['album_user_id']]['images'] = $personalrow[$row['album_user_id']]['images'] + $row['album_images_real'];
			}
			$personal_bad_row[$row['album_user_id']]['images'] = $personal_bad_row[$row['album_user_id']]['images'] + $row['album_images_real'];
		}
		$db->sql_freeresult($result);

		foreach ($personalrow as $key => $row)
		{
			$template->assign_block_vars('personalrow', array(
				'USER_ID'		=> $row['user_id'],
				'ALBUM_ID'		=> $row['album_id'],
				'AUTHOR_NAME'	=> $row['album_name'],
			));
		}
		foreach ($personal_bad_row as $key => $row)
		{
			$template->assign_block_vars('personal_bad_row', array(
				'USER_ID'		=> $row['user_id'],
				'ALBUM_ID'		=> $row['album_id'],
				'AUTHOR_NAME'	=> $row['album_name'],
				'IMAGES'		=> $row['images'],
			));
		}

		$template->assign_vars(array(
			'S_GALLERY_MANAGE_RESTS'		=> true,
			'ACP_GALLERY_TITLE'				=> $user->lang['ACP_GALLERY_CLEANUP'],
			'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ACP_GALLERY_CLEANUP_EXPLAIN'],
			'CHECK_SOURCE'			=> $this->u_action . '&amp;check_mode=source',
			'CHECK_ENTRY'			=> $this->u_action . '&amp;check_mode=entry',

			'S_FOUNDER'				=> ($user->data['user_type'] == USER_FOUNDER) ? true : false,
		));
	}
}

?>