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

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

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

add_form_key('gallery');
$submit = (isset($_POST['submit'])) ? true : false;
$mode = request_var('mode', '');
$submode = request_var('submode', '');
$album_id = request_var('album_id', 0);
$image_id = request_var('image_id', 0);
$comment_id = request_var('comment_id', 0);
$error = $message = '';
$error_count = array();
$slower_redirect = false;

// Check for permissions cheaters!
if ($comment_id)
{
	$sql = 'SELECT *
		FROM ' . GALLERY_COMMENTS_TABLE . '
		WHERE comment_id = ' . $comment_id;
	$result = $db->sql_query($sql);
	$comment_data = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	$image_id = $comment_data['comment_image_id'];
}
if ($image_id)
{
	$image_data = get_image_info($image_id);
	$album_id = $image_data['image_album_id'];
}
$album_data = get_album_info($album_id);

generate_album_nav($album_data);

if ($image_id)
{
	$image_backlink = append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id");
	$image_loginlink = append_sid("{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id");
}
if ($album_id)
{
	$album_backlink = append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id");
	$album_loginlink = append_sid("{$gallery_root_path}album.$phpEx", "album_id=$album_id");
}
$index_backlink = append_sid("{$phpbb_root_path}{$gallery_root_path}index.$phpEx");

// Send some cheaters back
if ($user->data['is_bot'])
{
	redirect(($image_id) ? $image_backlink : $album_backlink);
}
if ($album_data['album_type'] == ALBUM_CAT)
{
	meta_refresh(3, $album_backlink);
	trigger_error('ALBUM_IS_CATEGORY');
}
switch ($mode)
{
	case 'album':
		switch ($submode)
		{
			case 'watch':
			case 'unwatch':
				if (!gallery_acl_check('i_view', $album_id))
				{
					if (!$user->data['is_registered'])
					{
						login_box($album_loginlink , $user->lang['LOGIN_INFO']);
					}
					else
					{
						meta_refresh(3, $album_backlink);
						trigger_error('NOT_AUTHORISED');
					}
				}
			break;

			default:
				trigger_error('MISSING_SUBMODE');
			break;
		}
	break;
	case 'image':
		switch ($submode)
		{
			case 'upload':
				if (!gallery_acl_check('i_upload', $album_id))
				{
					if (!$user->data['is_registered'])
					{
						login_box($album_loginlink, $user->lang['LOGIN_EXPLAIN_UPLOAD']);
					}
					else
					{
						meta_refresh(3, $album_backlink);
						trigger_error('NOT_AUTHORISED');
					}
				}
			break;
			case 'edit':
				if ((!gallery_acl_check('i_edit', $album_id)) || (($image_data['image_user_id'] <> $user->data['user_id']) && !gallery_acl_check('m_edit', $album_id)))
				{
					if (!$user->data['is_registered'])
					{
						login_box($image_loginlink , $user->lang['LOGIN_INFO']);
					}
					else
					{
						meta_refresh(3, $image_backlink);
						trigger_error('NOT_AUTHORISED');
					}
				}
				else if (!gallery_acl_check('m_status', $album_id) && ($image_data['image_status'] != IMAGE_APPROVED))
				{
					meta_refresh(3, $image_backlink);
					trigger_error('NOT_AUTHORISED');
				}
			break;
			case 'report':
				if (!gallery_acl_check('i_report', $album_id) || ($image_data['image_user_id'] == $user->data['user_id']))
				{
					if (!$user->data['is_registered'])
					{
						login_box($image_loginlink , $user->lang['LOGIN_INFO']);
					}
					else
					{
						meta_refresh(3, $image_backlink);
						trigger_error('NOT_AUTHORISED');
					}
				}
				else if (!gallery_acl_check('m_status', $album_id) && ($image_data['image_status'] != IMAGE_APPROVED))
				{
					meta_refresh(3, $image_backlink);
					trigger_error('NOT_AUTHORISED');
				}
			break;
			case 'delete':
				if ((!gallery_acl_check('i_delete', $album_id)) || (($image_data['image_user_id'] <> $user->data['user_id']) && !gallery_acl_check('m_delete', $album_id)))
				{
					if (!$user->data['is_registered'])
					{
						login_box($image_loginlink , $user->lang['LOGIN_INFO']);
					}
					else
					{
						meta_refresh(3, $image_backlink);
						trigger_error('NOT_AUTHORISED');
					}
				}
				else if (!gallery_acl_check('m_status', $album_id) && ($image_data['image_status'] != IMAGE_APPROVED))
				{
					meta_refresh(3, $image_backlink);
					trigger_error('NOT_AUTHORISED');
				}
			break;
			case 'watch':
			case 'unwatch':
			case 'favorite':
			case 'unfavorite':
				if (!gallery_acl_check('i_view', $album_id))
				{
					if (!$user->data['is_registered'])
					{
						login_box($image_loginlink , $user->lang['LOGIN_INFO']);
					}
					else
					{
						meta_refresh(3, $image_backlink);
						trigger_error('NOT_AUTHORISED');
					}
				}
			break;

			default:
				trigger_error('MISSING_SUBMODE');
			break;
		}
	break;
	case 'comment':
		if (($image_data['image_status'] != IMAGE_APPROVED) && !gallery_acl_check('m_status', $album_id))
		{
			trigger_error('NOT_AUTHORISED');
		}
		if (($submode != 'rate') && (!$gallery_config['allow_comments']))
		{
			trigger_error('NOT_AUTHORISED');
		}
		if (!$submit && ($submode == 'rate'))
		{
			trigger_error('NOT_AUTHORISED');
		}
		switch ($submode)
		{
			case 'add':
				if (!gallery_acl_check('c_post', $album_id))
				{
					if (!$user->data['is_registered'])
					{
						login_box($image_loginlink , $user->lang['LOGIN_EXPLAIN_UPLOAD']);
					}
					else
					{
						meta_refresh(3, $image_backlink);
						trigger_error('NOT_AUTHORISED');
					}
				}
			break;

			case 'edit':
				if (!gallery_acl_check('c_edit', $album_id))
				{
					if (!$user->data['is_registered'])
					{
						login_box($image_loginlink , $user->lang['LOGIN_EXPLAIN_UPLOAD']);
					}
					else
					{
						meta_refresh(3, $image_backlink);
						trigger_error('NOT_AUTHORISED');
					}
				}
				else if ((($comment_data['comment_user_id'] != $user->data['user_id']) && !gallery_acl_check('m_comments', $album_id)) || !$user->data['is_registered'])
				{
					meta_refresh(3, $image_backlink);
					trigger_error('NOT_AUTHORISED');
				}
			break;

			case 'delete':
				if (!gallery_acl_check('c_delete', $album_id))
				{
					if (!$user->data['is_registered'])
					{
						login_box($image_loginlink , $user->lang['LOGIN_EXPLAIN_UPLOAD']);
					}
					else
					{
						meta_refresh(3, $image_backlink);
						trigger_error('NOT_AUTHORISED');
					}
				}
				else if ((($comment_data['comment_user_id'] != $user->data['user_id']) && !gallery_acl_check('m_comments', $album_id)) || !$user->data['is_registered'])
				{
					meta_refresh(3, $image_backlink);
					trigger_error('NOT_AUTHORISED');
				}
			break;

			case 'rate':
				if (!gallery_acl_check('i_rate', $album_id))
				{
					if (!$user->data['is_registered'])
					{
						login_box($image_loginlink , $user->lang['LOGIN_EXPLAIN_UPLOAD']);
					}
					else
					{
						meta_refresh(3, $image_backlink);
						trigger_error('NOT_AUTHORISED');
					}
				}
			break;

			default:
				trigger_error('MISSING_SUBMODE');
			break;
		}
	break;
	default:
		trigger_error('MISSING_MODE');
	break;
}

$bbcode_status	= ($config['allow_bbcode']) ? true : false;
$smilies_status	= ($bbcode_status && $config['allow_smilies']) ? true : false;
$img_status		= ($bbcode_status) ? true : false;
$url_status		= ($config['allow_post_links']) ? true : false;
$flash_status	= false;
$quote_status	= true;

$template->assign_vars(array(
	'BBCODE_STATUS'			=> ($bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
	'IMG_STATUS'			=> ($img_status) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
	'FLASH_STATUS'			=> ($flash_status) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
	'SMILIES_STATUS'		=> ($smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
	'URL_STATUS'			=> ($bbcode_status && $url_status) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],

	'S_BBCODE_ALLOWED'			=> $bbcode_status,
	'S_SMILIES_ALLOWED'			=> $smilies_status,
	'S_LINKS_ALLOWED'			=> $url_status,
	'S_BBCODE_IMG'			=> $img_status,
	'S_BBCODE_URL'			=> $url_status,
	'S_BBCODE_FLASH'		=> $flash_status,
	'S_BBCODE_QUOTE'		=> $quote_status,
));

// Build custom bbcodes array
display_custom_bbcodes();

// Build smilies array
generate_smilies('inline', 0);

switch ($mode)
{
	case 'album':
	if ($mode == 'album')
	{
		switch ($submode)
		{
			case 'watch':
			if ($submode == 'watch')
			{
				$sql_ary = array(
					'album_id'			=> $album_id,
					'user_id'			=> $user->data['user_id'],
				);
				$sql = 'INSERT INTO ' . GALLERY_WATCH_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
				$db->sql_query($sql);
				$message = $user->lang['WATCHING_ALBUM'] . '<br />';
				$submit = true; // For redirect
			}
			break;
			case 'unwatch':
			if ($submode == 'unwatch')
			{
				$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . '
					WHERE album_id = ' . (int) $album_id . '
						AND user_id = ' . $user->data['user_id'];
				$db->sql_query($sql);
				$message = $user->lang['UNWATCHED_ALBUM'] . '<br />';
				$submit = true; // For redirect
			}
			break;
		}
	}
	break;

	case 'image':
	if ($mode == 'image')
	{
		switch ($submode)
		{
			case 'upload':
			if ($submode == 'upload')
			{
				// Upload Quota Check
				// 1. Check Album Configuration Quota
				if ($gallery_config['max_pics'] >= 0)
				{
					if ($album_data['album_images'] >= $gallery_config['max_pics'])
					{
						trigger_error('ALBUM_REACHED_QUOTA');
					}
				}
				// 2. Check User Limit
				$sql = 'SELECT COUNT(image_id) count
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE image_user_id = ' . $user->data['user_id'] . '
						AND image_album_id = ' . $album_id;
				$result = $db->sql_query($sql);
				$own_images = (int) $db->sql_fetchfield('count');
				$db->sql_freeresult($result);
				if ($own_images >= gallery_acl_check('i_count', $album_id))
				{
					trigger_error(sprintf($user->lang['USER_REACHED_QUOTA'], gallery_acl_check('i_count', $album_id)));
				}

				$images = 0;
				if($submit)
				{
					if (!check_form_key('gallery'))
					{
						trigger_error('FORM_INVALID');
					}

					// Get File Upload Info
					$image_id_ary = array();
					$loop = request_var('image_num', 0);
					$loop = ($loop != 0) ? $loop - 1 : $loop;
					foreach ($_FILES['image']['type'] as $i => $type)
					{
						$image_data = array();

						$image_data['image_type']		= $_FILES['image']['type'][$i];
						$image_data['image_size']		= $_FILES['image']['size'][$i];
						$image_data['image_tmp']		= $_FILES['image']['tmp_name'][$i];
						$image_data['image_tmp_name']	= $_FILES['image']['name'][$i];
						if ($image_data['image_size'])
						{
							$loop = $loop + 1;
							$images = $images + 1;
							// Watch for 8-times max-file-size, we will watch for the real size after resize, if enabled
							if ((!$image_data['image_size']) || ($image_data['image_size'] > (8 * $gallery_config['max_file_size'])))
							{
								trigger_error('BAD_UPLOAD_FILE_SIZE');
							}
							switch ($image_data['image_type'])
							{
								case 'image/jpeg':
								case 'image/jpg':
								case 'image/pjpeg':
									if (!$gallery_config['jpg_allowed'])
									{
										trigger_error('NOT_ALLOWED_FILE_TYPE');
									}
									$image_data['image_type2'] = '.jpg';
								break;
								case 'image/png':
								case 'image/x-png':
									if (!$gallery_config['png_allowed'])
									{
										trigger_error('NOT_ALLOWED_FILE_TYPE');
									}
									$image_data['image_type2'] = '.png';
								break;
								case 'image/gif':
									if (!$gallery_config['gif_allowed'])
									{
										trigger_error('NOT_ALLOWED_FILE_TYPE');
									}
									$image_data['image_type2'] = '.gif';
								break;
								default:
									trigger_error('NOT_ALLOWED_FILE_TYPE');
								break;
							}

							$image_data_2 = array(
								'filename'			=> '',
								'image_album_id'	=> $album_data['album_id'],
								'image_album_name'	=> $album_data['album_name'],
								'image_desc'		=> str_replace('{NUM}', $loop, request_var('message', '', true)),
								'image_time'		=> time() + $loop,
								'image_contest'		=> ($album_data['album_contest']) ? IMAGE_CONTEST : IMAGE_NO_CONTEST,
								'thumbnail'			=> '',
								'username'			=> request_var('username', $user->data['username']),
							);
							$image_data_2['image_name'] = str_replace('{NUM}', $loop, request_var('image_name', '', true));
							$image_data_2['image_name'] = ((request_var('filename', '') == 'filename') || ($image_data_2['image_name'] == '')) ? str_replace("_", " ", utf8_substr($image_data['image_tmp_name'], 0, -4)) : $image_data_2['image_name'];
							$image_data = array_merge($image_data, $image_data_2);

							if (!$image_data['image_name'])
							{
								trigger_error('MISSING_IMAGE_NAME');
							}
							if (!$user->data['is_registered'] && $image_data['username'])
							{
								include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
								$result = validate_username($image_data['username']);
								if ($result['error'])
								{
									trigger_error($result['error_msg']);
								}
							}

							// Generate filename and upload
							$image_data['filename'] = md5(unique_id()) . $image_data['image_type2'];
							if (@ini_get('open_basedir') <> '')
							{
								$move_file = 'move_uploaded_file';
							}
							else
							{
								$move_file = 'copy';
							}
							$move_file($image_data['image_tmp'], $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
							@chmod($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename'], 0777);

							$image_size = getimagesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
							$image_data['width'] = $image_size[0];
							$image_data['height'] = $image_size[1];

							// Since we are able to resize the images, we loose the exif.
							$exif = @exif_read_data($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename'], 0, true);
							if (!empty($exif["EXIF"]))
							{
								unset($exif["EXIF"]["MakerNote"]);
								var_dump ($exif);
								$image_data['image_exif_data'] = serialize ($exif);
								$image_data['image_has_exif'] = EXIF_DBSAVED;
							}
							else
							{
								$image_data['image_exif_data'] = '';
								$image_data['image_has_exif'] = EXIF_UNAVAILABLE;
							}

							if (($image_data['width'] > $gallery_config['max_width']) || ($image_data['height'] > $gallery_config['max_height']))
							{
								/**
								* Resize overside images
								*/
								if ($gallery_config['resize_images'])
								{
									switch ($image_data['image_type2']) 
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

									$src = $read_function($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
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
									switch ($image_data['image_type2'])
									{
										case '.jpg':
											@imagejpeg($thumbnail, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename'], 100);
										break;

										case '.png':
											@imagepng($thumbnail, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
										break;

										case '.gif':
											@imagegif($thumbnail, $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
										break;
									}
								}
								else
								{
									@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
									trigger_error('UPLOAD_IMAGE_SIZE_TOO_BIG');
								}
								$image_data['width'] = $thumbnail_width;
								$image_data['height'] = $thumbnail_height;
							}
							else if ($image_data['image_has_exif'] == EXIF_DBSAVED)
							{
								// Image was not resized, so we can pull the Exif from the image to save db-memory.
								$image_data['image_has_exif'] = EXIF_AVAILABLE;
								$image_data['image_exif_data'] = '';
							}
							$image_data['image_filesize'] = filesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
							if ($image_data['image_filesize'] > $gallery_config['max_file_size'])
							{
								@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
								trigger_error('BAD_UPLOAD_FILE_SIZE');
							}

							$image_data = upload_image($image_data);
							$image_id = $image_data['image_id'];
							$image_name = $image_data['image_name'];
							$image_id_ary[] = $image_id;
						}
					}// end foreach
					$image_id = ($images > 1) ? 0 : $image_id;

					// Complete... now send a message to user
					if ($images < 1)
					{
						$error .= (($error) ? '<br />' : '') . $user->lang['UPLOAD_NO_FILE'];
					}
					else
					{
						gallery_notification('album', $album_id, $image_name);
						handle_image_counter($image_id_ary, true);

						$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . " 
							SET album_images_real = album_images_real + $images
							WHERE album_id = $album_id";
						$db->sql_query($sql);
					}
				}
				$allowed_filetypes = array();
				if ($gallery_config['gif_allowed'])
				{
					$allowed_filetypes[] = $user->lang['FILETYPES_GIF'];
				}
				if ($gallery_config['jpg_allowed'])
				{
					$allowed_filetypes[] = $user->lang['FILETYPES_JPG'];
				}
				if ($gallery_config['png_allowed'])
				{
					$allowed_filetypes[] = $user->lang['FILETYPES_PNG'];
				}

				$template->assign_vars(array(
					'ERROR'						=> $error,
					'U_VIEW_ALBUM'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id"),
					'CAT_TITLE'					=> $album_data['album_name'],
					'S_MAX_FILESIZE'			=> $gallery_config['max_file_size'],
					'S_MAX_WIDTH'				=> $gallery_config['max_width'],
					'S_MAX_HEIGHT'				=> $gallery_config['max_height'],

					'S_ALLOWED_FILE_TYPES'	=> implode(', ', $allowed_filetypes),
					'S_THUMBNAIL_SIZE'		=> $gallery_config['thumbnail_size'],
					'S_THUMBNAIL'			=> ($gallery_config['gd_version']) ? true : false,
					'S_MULTI_IMAGES'		=> ($gallery_config['upload_images'] > 1) ? true : false,
					'S_ALBUM_ACTION'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=upload&amp;album_id=$album_id"),

					'IMAGE_RSZ_WIDTH'		=> $gallery_config['preview_rsz_width'],
					'IMAGE_RSZ_HEIGHT'		=> $gallery_config['preview_rsz_height'],
					'L_DESCRIPTION_LENGTH'	=> sprintf($user->lang['DESCRIPTION_LENGTH'], $gallery_config['description_length']),
					'USERNAME'				=> request_var('username', '', true),
					'IMAGE_NAME'			=> request_var('image_name', '', true),
					'MESSAGE'				=> request_var('message', '', true),
					'S_IMAGE'				=> true,
					'S_UPLOAD'				=> true,
				));

				$count = 0;
				$upload_image_files = $gallery_config['upload_images'];
				if ((gallery_acl_check('i_count', $album_id) - $own_images) < $upload_image_files)
				{
					$upload_image_files = (gallery_acl_check('i_count', $album_id) - $own_images);
					$error .= (($error) ? '<br />' : '') . sprintf($user->lang['USER_NEARLY_REACHED_QUOTA'], gallery_acl_check('i_count', $album_id), $own_images, $upload_image_files);
					$template->assign_vars(array(
						'ERROR'						=> $error,
					));
				}

				while ($count < $upload_image_files)
				{
					$template->assign_block_vars('upload_image', array());
					$count++;
				}

				if (!$error)
				{
					if (gallery_acl_check('i_approve', $album_id))
					{
						$message = $user->lang['ALBUM_UPLOAD_SUCCESSFUL'];
					}
					else
					{
						$message = $user->lang['ALBUM_UPLOAD_NEED_APPROVAL'];
						$slower_redirect = true;
						$image_id = false;
					}
				}
				else
				{
					$submit = false;
					$message = $user->lang['UPLOAD_NO_FILE'];
				}
				$message .= '<br />';
				update_album_info($album_id);
				$page_title = $user->lang['UPLOAD_IMAGE'];
			}
			break;
			case 'edit':
			if ($submode == 'edit')
			{
				if ($submit)
				{
					if (!check_form_key('gallery'))
					{
						trigger_error('FORM_INVALID');
					}
					$image_desc = request_var('message', '', true);
					$image_name = request_var('image_name', '', true);

					if (empty($image_name))
					{
						trigger_error('MISSING_IMAGE_NAME');
					}
					$message_parser				= new parse_message();
					$message_parser->message	= utf8_normalize_nfc($image_desc);
					if ($message_parser->message)
					{
						$message_parser->parse(true, true, true, true, false, true, true, true);
					}

					$sql_ary = array(
						'image_name'				=> $image_name,
						'image_desc'				=> $message_parser->message,
						'image_desc_uid'			=> $message_parser->bbcode_uid,
						'image_desc_bitfield'		=> $message_parser->bbcode_bitfield,
					);

					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
						WHERE image_id = $image_id";
					$db->sql_query($sql);

					if ($user->data['user_id'] != $image_data['image_user_id'])
					{
						add_log('gallery', $image_data['image_album_id'], $image_data['image_id'], 'LOG_GALLERY_EDITED', $image_name);
					}

					if ($album_data['album_last_image_id'] == $image_id)
					{
						$sql_ary = array(
							'album_last_image_name'		=> $image_name,
						);
						$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE ' . $db->sql_in_set('album_id', $image_data['image_album_id']);
						$db->sql_query($sql);
					}
				}
				$message_parser				= new parse_message();
				$message_parser->message	= $image_data['image_desc'];
				$message_parser->decode_message($image_data['image_desc_uid']);

				$template->assign_vars(array(
					'IMAGE_NAME'		=> $image_data['image_name'],
					'MESSAGE'			=> $message_parser->message,
					'L_DESCRIPTION_LENGTH'	=> sprintf($user->lang['DESCRIPTION_LENGTH'], $gallery_config['description_length']),

					'U_IMAGE'			=> ($image_id) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", "album_id=$album_id&amp;image_id=$image_id") : '',
					'U_VIEW_IMAGE'		=> ($image_id) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id") : '',
					'IMAGE_RSZ_WIDTH'	=> $gallery_config['preview_rsz_width'],
					'IMAGE_RSZ_HEIGHT'	=> $gallery_config['preview_rsz_height'],

					'S_IMAGE'			=> true,
					'S_EDIT'			=> true,
					'S_ALBUM_ACTION'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=edit&amp;album_id=$album_id&amp;image_id=$image_id"),
				));
				$message = $user->lang['IMAGES_UPDATED_SUCCESSFULLY'] . '<br />';
				$page_title = $user->lang['EDIT_IMAGE'];
			}
			break;
			case 'report':
			if ($submode == 'report')
			{
				if ($submit)
				{
					if (!check_form_key('gallery'))
					{
						trigger_error('FORM_INVALID');
					}

					$report_message = request_var('message', '', true);
					$error = '';
					if ($report_message == '')
					{
						$error = $user->lang['MISSING_REPORT_REASON'];
						$submit = false;
					}

					$sql_ary = array(
						'report_album_id'			=> $album_id,
						'report_image_id'			=> $image_id,
						'reporter_id'				=> $user->data['user_id'],
						'report_note'				=> $report_message,
						'report_time'				=> time(),
						'report_status'				=> REPORT_OPEN,
					);

					if (!$error)
					{
						if ($image_data['image_reported'])
						{
							trigger_error('IMAGE_ALREADY_REPORTED');
						}
						$sql = 'INSERT INTO ' . GALLERY_REPORTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
						$db->sql_query($sql);
						$report_id = $db->sql_nextid();

						$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
							SET image_reported = ' . $report_id . '
							WHERE image_id = ' . (int) $image_id;
						$db->sql_query($sql);
					}
				}

				$template->assign_vars(array(
					'ERROR'				=> $error,
					'U_IMAGE'			=> ($image_id) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", "album_id=$album_id&amp;image_id=$image_id") : '',
					'U_VIEW_IMAGE'		=> ($image_id) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id") : '',
					'IMAGE_RSZ_WIDTH'	=> $gallery_config['preview_rsz_width'],
					'IMAGE_RSZ_HEIGHT'	=> $gallery_config['preview_rsz_height'],

					'S_REPORT'			=> true,
					'S_ALBUM_ACTION'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=report&amp;album_id=$album_id&amp;image_id=$image_id"),
				));
				$message = $user->lang['IMAGES_REPORTED_SUCCESSFULLY'] . '<br />';
				$page_title = $user->lang['REPORT_IMAGE'];
			}
			break;
			case 'watch':
			if ($submode == 'watch')
			{
				$sql_ary = array(
					'image_id'			=> $image_id,
					'user_id'			=> $user->data['user_id'],
				);
				$sql = 'INSERT INTO ' . GALLERY_WATCH_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
				$db->sql_query($sql);
				$message = $user->lang['WATCHING_IMAGE'] . '<br />';
				$submit = true; // For redirect
			}
			break;
			case 'unwatch':
			if ($submode == 'unwatch')
			{
				$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . "
					WHERE image_id = $image_id
						AND user_id = " . $user->data['user_id'];
				$db->sql_query($sql);
				$message = $user->lang['UNWATCHED_IMAGE'] . '<br />';
				$submit = true; // For redirect
			}
			break;
			case 'favorite':
			if ($submode == 'favorite')
			{
				$sql_ary = array(
					'image_id'			=> $image_id,
					'user_id'			=> $user->data['user_id'],
				);
				$sql = 'INSERT INTO ' . GALLERY_FAVORITES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
				$db->sql_query($sql);
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
					SET image_favorited = image_favorited + 1
					WHERE image_id = ' . $image_id;
				$db->sql_query($sql);
				if ($user->gallery['watch_favo'] && !$image_data['watch_id'])
				{
					$sql_ary = array(
						'image_id'			=> $image_id,
						'user_id'			=> $user->data['user_id'],
					);
					$sql = 'INSERT INTO ' . GALLERY_WATCH_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
					$db->sql_query($sql);
				}
				$message = $user->lang['FAVORITED_IMAGE'] . '<br />';
				$submit = true; // For redirect
			}
			break;
			case 'unfavorite':
			if ($submode == 'unfavorite')
			{
				$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . "
					WHERE image_id = $image_id
						AND user_id = " . $user->data['user_id'];
				$db->sql_query($sql);
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
					SET image_favorited = image_favorited - 1
					WHERE image_id = ' . $image_id;
				$db->sql_query($sql);
				$message = $user->lang['UNFAVORITED_IMAGE'] . '<br />';
				$submit = true; // For redirect
			}
			break;
			case 'delete':
			if ($submode == 'delete')
			{
				$s_hidden_fields = build_hidden_fields(array(
					'album_id'		=> $album_id,
					'image_id'		=> $image_id,
					'mode'			=> 'image',
					'submode'		=> 'delete',
				));
				if (confirm_box(true))
				{

					@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['image_thumbnail']);
					@unlink($phpbb_root_path . GALLERY_MEDIUM_PATH . $image_data['image_thumbnail']);
					@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['image_filename']);
					handle_image_counter($image_id, false);

					$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . "
						WHERE comment_image_id = $image_id";
					$db->sql_query($sql);

					$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . "
						WHERE image_id = $image_id";
					$db->sql_query($sql);

					$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . "
						WHERE rate_image_id = $image_id";
					$db->sql_query($sql);

					$sql = 'DELETE FROM ' . GALLERY_REPORTS_TABLE . "
						WHERE report_image_id = $image_id";
					$db->sql_query($sql);

					$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . "
						WHERE image_id = $image_id";
					$db->sql_query($sql);

					$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . "
						WHERE image_id = $image_id";
					$db->sql_query($sql);

					update_album_info($album_id);

					$submit = true;
					$message = $user->lang['DELETED_IMAGE'] . '<br />';
					$image_id = false;

					if ($user->data['user_id'] != $image_data['image_user_id'])
					{
						add_log('gallery', $image_data['image_album_id'], $image_data['image_id'], 'LOG_GALLERY_COMMENT_DELETED', $image_data['image_name']);
					}
				}
				else
				{
					if (isset($_POST['cancel']))
					{
						$message = $user->lang['DELETED_IMAGE_NOT'] . '<br />';
						$submit = true;
					}
					else
					{
						confirm_box(false, 'DELETE_IMAGE2', $s_hidden_fields);
					}
				}
			}
			break;
		}
	}
	break;

	case 'comment':
	if ($mode == 'comment')
	{
		$comment = $comment_username = '';
		$comment_username_req = $contest_rating_msg = false;
		/**
		* Rating-System: now you can comment and rate in one form
		*/
		$rate_point = request_var('rate', 0);
		if ($gallery_config['allow_rates'])
		{
			$allowed_to_rate = $your_rating = false;

			if ($user->data['is_registered'])
			{
				$sql = 'SELECT rate_point
					FROM ' . GALLERY_RATES_TABLE . '
					WHERE rate_image_id = ' . (int) $image_id . '
						AND rate_user_id = ' . (int) $user->data['user_id'];
				$result = $db->sql_query($sql);
				if ($db->sql_affectedrows($result) > 0)
				{
					$your_rating = $db->sql_fetchfield('rate_point');
				}
				$db->sql_freeresult($result);
			}

			// Check: User didn't rate yet, has permissions, it's not the users own image and the user is logged in
			if (!$your_rating && gallery_acl_check('i_rate', $album_id) && ($user->data['user_id'] != $image_data['image_user_id']) && ($user->data['user_id'] != ANONYMOUS))
			{
				$hide_rate = false;
				if ($album_data['contest_id'])
				{
					if (time() < ($album_data['contest_start'] + $album_data['contest_rating']))
					{
						$hide_rate = true;
						$contest_rating_msg = sprintf($user->lang['CONTEST_RATING_STARTS'], $user->format_date(($album_data['contest_start'] + $album_data['contest_rating']), false, true));
					}
					if (($album_data['contest_start'] + $album_data['contest_end']) < time())
					{
						$hide_rate = true;
						$contest_rating_msg = sprintf($user->lang['CONTEST_RATING_ENDED'], $user->format_date(($album_data['contest_start'] + $album_data['contest_end']), false, true));
					}
				}

				// User just rated the image, so we store it
				if (!$hide_rate && $rate_point > 0)
				{
					if ($rate_point > $gallery_config['rate_scale'])
					{
						trigger_error('OUT_OF_RANGE_VALUE');
					}

					$sql_ary = array(
						'rate_image_id'	=> $image_id,
						'rate_user_id'	=> $user->data['user_id'],
						'rate_user_ip'	=> $user->ip,
						'rate_point'	=> $rate_point,
					);
					$db->sql_query('INSERT INTO ' . GALLERY_RATES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

					$sql = 'SELECT rate_image_id, COUNT(rate_user_ip) image_rates, AVG(rate_point) image_rate_avg, SUM(rate_point) image_rate_points
						FROM ' . GALLERY_RATES_TABLE . "
						WHERE rate_image_id = $image_id
						GROUP BY rate_image_id";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
							SET image_rates = ' . $row['image_rates'] . ',
								image_rate_points = ' . $row['image_rate_points'] . ',
								image_rate_avg = ' . round($row['image_rate_avg'], 2) * 100 . '
							WHERE image_id = ' . $row['rate_image_id'];
						$db->sql_query($sql);
					}
					$db->sql_freeresult($result);

					$message .= $user->lang['RATING_SUCCESSFUL'] . '<br />';
				}
				// else we show the drop down
				else if (!$hide_rate)
				{
					for ($rate_scale = 1; $rate_scale <= $gallery_config['rate_scale']; $rate_scale++)
					{
						$template->assign_block_vars('rate_scale', array(
							'RATE_POINT'	=> $rate_scale,
						));
					}
					$allowed_to_rate = true;
				}
				else
				{
					$allowed_to_rate = true;
				}
			}
			$template->assign_vars(array(
				'S_ALLOWED_TO_RATE'			=> $allowed_to_rate,
				'CONTEST_RATING'			=> $contest_rating_msg,
			));
			if ($submode == 'rate')
			{
				$s_album_action = '';
			}
		}
		switch ($submode)
		{
			case 'add':
				if ($submit)
				{
					if (!check_form_key('gallery'))
					{
						trigger_error('FORM_INVALID');
					}
					$comment = request_var('message', '', true);
					$comment_text = $comment;
					$comment_username = request_var('username', '', true);
					if ($user->data['user_id'] == ANONYMOUS)
					{
						$comment_username_req = true;
					}
					if ($comment_username_req)
					{
						if ($comment_username == '')
						{
							$submit = false;
							$error .= (($error) ? '<br />' : '') . $user->lang['MISSING_USERNAME'];
						}
						include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
						$result = validate_username($comment_username);
						if ($result['error'])
						{
							$error .= (($error) ? '<br />' : '') . $user->lang['INVALID_USERNAME'];
							$submit = false;
						}
					}
					if (($comment_text == '') && !$rate_point)
					{
						$submit = false;
						$error .= (($error) ? '<br />' : '') . $user->lang['MISSING_COMMENT'];
					}
					if (utf8_strlen($comment_text) > $gallery_config['desc_length'])
					{
						$submit = false;
						$error .= (($error) ? '<br />' : '') . $user->lang['COMMENT_TOO_LONG'];
					}

					$message_parser				= new parse_message();
					$message_parser->message	= utf8_normalize_nfc($comment_text);
					if ($message_parser->message)
					{
						$message_parser->parse(true, true, true, true, false, true, true, true);
					}
					$sql_ary = array(
						'comment_image_id'		=> $image_id,
						'comment_user_id'		=> $user->data['user_id'],
						'comment_username'		=> ($user->data['user_id'] != ANONYMOUS) ? $user->data['username'] : $comment_username,
						'comment_user_colour'	=> $user->data['user_colour'],
						'comment_user_ip'		=> $user->ip,
						'comment_time'			=> time(),
						'comment'				=> $message_parser->message,
						'comment_uid'			=> $message_parser->bbcode_uid,
						'comment_bitfield'		=> $message_parser->bbcode_bitfield,
					);
					if ((!$error) && ($sql_ary['comment'] != ''))
					{
						$db->sql_query('INSERT INTO ' . GALLERY_COMMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
						$newest_comment = $db->sql_nextid();

						$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . "
							SET image_comments = image_comments + 1,
								image_last_comment = $newest_comment
							WHERE " . $db->sql_in_set('image_id', $image_id);
						$db->sql_query($sql);
						if ($user->gallery['watch_com'] && !$image_data['watch_id'])
						{
							$sql_ary = array(
								'image_id'			=> $image_id,
								'user_id'			=> $user->data['user_id'],
							);
							$sql = 'INSERT INTO ' . GALLERY_WATCH_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
							$db->sql_query($sql);
						}
						gallery_notification('image', $image_id, $image_data['image_name']);
						$message .= $user->lang['COMMENT_STORED'] . '<br />';
					}
				}
				else
				{
					if ($user->data['user_id'] != ANONYMOUS)
					{
						$comment_username_req = true;
					}
				}
				$s_album_action = append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=comment&amp;submode=add&amp;album_id=$album_id&amp;image_id=$image_id");
				$page_title = $user->lang['POST_COMMENT'];
			break;

			case 'edit':
				if ($submit)
				{
					if (!check_form_key('gallery'))
					{
						trigger_error('FORM_INVALID');
					}
					$comment = request_var('message', '', true);
					$comment_text = $comment;
					$comment_username = request_var('username', '');
					if ($comment_data['comment_user_id'] == ANONYMOUS)
					{
						$comment_username_req = true;
					}
					if ($comment_username_req)
					{
						if ($comment_username == '')
						{
							$submit = false;
							$error .= (($error) ? '<br />' : '') . $user->lang['MISSING_USERNAME'];
							$comment_username_req = true;
						}
						include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
						$result = validate_username($comment_username);
						if ($result['error'])
						{
							$error .= (($error) ? '<br />' : '') . $user->lang['INVALID_USERNAME'];
							$comment_username = '';
							$comment_username_req = true;
							$submit = false;
						}
					}
					if ($comment_text == '')
					{
						$submit = false;
						$error .= (($error) ? '<br />' : '') . $user->lang['MISSING_COMMENT'];
					}
					if (utf8_strlen($comment_text) > $gallery_config['desc_length'])
					{
						$submit = false;
						$error .= (($error) ? '<br />' : '') . $user->lang['COMMENT_TOO_LONG'];
					}

					$message_parser				= new parse_message();
					$message_parser->message	= utf8_normalize_nfc($comment_text);
					if ($message_parser->message)
					{
						$message_parser->parse(true, true, true, true, false, true, true, true);
					}
					$sql_ary = array(
						'comment_username'		=> $comment_username,
						'comment'				=> $message_parser->message,
						'comment_uid'			=> $message_parser->bbcode_uid,
						'comment_bitfield'		=> $message_parser->bbcode_bitfield,
						'comment_edit_count'	=> $comment_data['comment_edit_count'] + 1,
						'comment_edit_time'		=> time(),
						'comment_edit_user_id'	=> $user->data['user_id'],
					);
					if (!$error)
					{
						$db->sql_query('UPDATE ' . GALLERY_COMMENTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE comment_id = ' . (int) $comment_id);
						$message .= $user->lang['COMMENT_STORED'] . '<br />';
						if ($user->data['user_id'] != $comment_data['comment_user_id'])
						{
							add_log('gallery', $image_data['image_album_id'], $image_data['image_id'], 'LOG_GALLERY_COMMENT_EDITED', $image_data['image_name']);
						}
					}
				}
				else
				{
					$comment_ary = generate_text_for_edit($comment_data['comment'], $comment_data['comment_uid'], $comment_data['comment_bitfield'], 7);
					$comment = $comment_ary['text'];
					$comment_username = $comment_data['comment_username'];
				}
				$s_album_action = append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=comment&amp;submode=edit&amp;album_id=$album_id&amp;image_id=$image_id&amp;comment_id=$comment_id");
				$page_title = $user->lang['EDIT_COMMENT'];
			break;

			case 'delete':
				$s_hidden_fields = build_hidden_fields(array(
					'album_id'		=> $album_id,
					'image_id'		=> $image_id,
					'comment_id'	=> $comment_id,
					'mode'			=> 'comment',
					'submode'		=> 'delete',
				));
				$comment = $comment_username = $comment_username_req = '';
				if (confirm_box(true))
				{
					$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . " WHERE comment_id = $comment_id;";
					$db->sql_query($sql);

					$sql = 'SELECT MAX(comment_id) last_comment
						FROM ' . GALLERY_COMMENTS_TABLE . "
						WHERE comment_image_id = $image_id
						ORDER BY comment_id";
					$result = $db->sql_query_limit($sql, 1);
					$last_comment_id = $db->sql_fetchfield('last_comment');
					$last_comment_id = ($last_comment_id) ? $last_comment_id : 0;
					$db->sql_freeresult($result);

					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . "
						SET image_comments = image_comments - 1,
							image_last_comment = $last_comment_id
						WHERE " . $db->sql_in_set('image_id', $image_id);
					$db->sql_query($sql);

					if ($user->data['user_id'] != $comment_data['comment_user_id'])
					{
						add_log('gallery', $image_data['image_album_id'], $image_data['image_id'], 'LOG_GALLERY_COMMENT_DELETED', $image_data['image_name']);
					}

					$submit = true;
					$message = $user->lang['DELETED_COMMENT'] . '<br />';
				}
				else
				{
					if (isset($_POST['cancel']))
					{
						$message = $user->lang['DELETED_COMMENT_NOT'] . '<br />';
						$submit = true;
					}
					else
					{
						confirm_box(false, 'DELETE_COMMENT2', $s_hidden_fields);
					}
				}
			break;
		}

		$template->assign_vars(array(
			'ERROR'					=> $error,
			'MESSAGE'				=> $comment,
			'USERNAME'				=> $comment_username,
			'REQ_USERNAME'			=> $comment_username_req,
			'L_COMMENT_LENGTH'		=> sprintf($user->lang['COMMENT_LENGTH'], $gallery_config['comment_length']),

			'IMAGE_RSZ_WIDTH'		=> $gallery_config['preview_rsz_width'],
			'IMAGE_RSZ_HEIGHT'		=> $gallery_config['preview_rsz_height'],
			'U_IMAGE'				=> ($image_id) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", "album_id=$album_id&amp;image_id=$image_id") : '',
			'U_VIEW_IMAGE'			=> ($image_id) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id") : '',
			'IMAGE_NAME'			=> ($image_id) ? $image_data['image_name'] : '',

			'S_ALBUM_ACTION'		=> $s_album_action,
			'S_COMMENT'				=> true,
		));
	}
	break;
}

if($submit)
{
	if ($image_id)
	{
		$image_backlink = append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", (($album_id) ? "album_id=$album_id&amp;" : '') . "image_id=$image_id");
		$message .= '<br />' . sprintf($user->lang['CLICK_RETURN_IMAGE'], '<a href="' . $image_backlink . '">', '</a>');
	}
	if ($album_id)
	{
		$album_backlink = append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id");
		$message .= '<br />' . sprintf($user->lang['CLICK_RETURN_ALBUM'], '<a href="' . $album_backlink . '">', '</a>');
	}
	meta_refresh((($slower_redirect) ? 10 : 3), ($image_id) ? $image_backlink : $album_backlink);
	trigger_error($message);
}

page_header($page_title);

$template->set_filenames(array(
	'body' => 'gallery/posting_body.html',
));

page_footer();

/**
* Insert the image into the database
*/
function upload_image(&$image_data)
{
	global $user, $db, $album_id;

	$sql_ary = array(
		'image_filename' 		=> $image_data['filename'],
		'image_name'			=> $image_data['image_name'],
		'image_user_id'			=> $user->data['user_id'],
		'image_user_colour'		=> $user->data['user_colour'],
		'image_username'		=> $image_data['username'],
		'image_user_ip'			=> $user->ip,
		'image_time'			=> $image_data['image_time'],
		'image_album_id'		=> $image_data['image_album_id'],
		'image_status'			=> (gallery_acl_check('i_approve', $album_id)) ? IMAGE_APPROVED : IMAGE_UNAPPROVED,
		'filesize_upload'		=> $image_data['image_filesize'],
		'image_contest'			=> $image_data['image_contest'],
		'image_exif_data'		=> $image_data['image_exif_data'],
		'image_has_exif'		=> $image_data['image_has_exif'],
	);

	$message_parser				= new parse_message();
	$message_parser->message	= utf8_normalize_nfc($image_data['image_desc']);
	if($message_parser->message)
	{
		$message_parser->parse(true, true, true, true, false, true, true, true);
		$sql_ary['image_desc']			= $message_parser->message;
		$sql_ary['image_desc_uid']		= $message_parser->bbcode_uid;
		$sql_ary['image_desc_bitfield']	= $message_parser->bbcode_bitfield;
	}
	else
	{
		$sql_ary['image_desc']			= '';
		$sql_ary['image_desc_uid']		= '';
		$sql_ary['image_desc_bitfield']	= '';
	}

	$sql = 'INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
	$db->sql_query($sql);
	$image_id = $db->sql_nextid();

	if ($user->gallery['watch_own'])
	{
		$sql_ary = array(
			'image_id'			=> $image_id,
			'user_id'			=> $user->data['user_id'],
		);
		$sql = 'INSERT INTO ' . GALLERY_WATCH_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);
	}

	return array('image_id' => $image_id, 'image_name' => $image_data['image_name']);
}

/**
* Gallery Notification
*
* borrowed from phpBB3
* @author: phpBB Group
* @function: user_notification
*/
function gallery_notification($mode, $handle_id, $image_name)
{
	global $phpbb_root_path, $gallery_root_path, $phpEx;
	global $user, $db, $album_id, $image_id, $image_data, $album_data;

	$help_mode = $mode . '_id';
	$mode_id = $$help_mode;
	$mode_notification = ($mode == 'album') ? 'image' : 'comment';

	// Get banned User ID's
	$sql = 'SELECT ban_userid
		FROM ' . BANLIST_TABLE . '
		WHERE ban_userid <> 0
			AND ban_exclude <> 1';
	$result = $db->sql_query($sql);

	$sql_ignore_users = ANONYMOUS . ', ' . $user->data['user_id'];
	while ($row = $db->sql_fetchrow($result))
	{
		$sql_ignore_users .= ', ' . (int) $row['ban_userid'];
	}
	$db->sql_freeresult($result);

	$notify_rows = array();

	// -- get album_userids	|| image_userids
	$sql = 'SELECT u.user_id, u.username, u.user_email, u.user_lang, u.user_notify_type, u.user_jabber
		FROM ' . GALLERY_WATCH_TABLE . ' w, ' . USERS_TABLE . ' u
		WHERE w.' . $help_mode . ' = ' . $handle_id . "
			AND w.user_id NOT IN ($sql_ignore_users)
			AND u.user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')
			AND u.user_id = w.user_id';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$notify_rows[$row['user_id']] = array(
			'user_id'		=> $row['user_id'],
			'username'		=> $row['username'],
			'user_email'	=> $row['user_email'],
			'user_jabber'	=> $row['user_jabber'],
			'user_lang'		=> $row['user_lang'],
			'notify_type'	=> ($mode != 'album') ? 'image' : 'album',
			'template'		=> "new{$mode_notification}_notify",
			'method'		=> $row['user_notify_type'],
			'allowed'		=> false
		);
	}
	$db->sql_freeresult($result);

	if (!sizeof($notify_rows))
	{
		return;
	}

	// Make sure users are allowed to view the album
	$i_view_ary = array();
	$sql = "SELECT pr.i_view, p.perm_group_id
		FROM " . GALLERY_PERMISSIONS_TABLE . " as p
		LEFT JOIN " .  GALLERY_ROLES_TABLE .  " as pr
			ON p.perm_role_id = pr.role_id
		WHERE p.perm_album_id = $album_id
		ORDER BY pr.i_view ASC";
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$sql2 = "SELECT ug.user_id
			FROM " . USER_GROUP_TABLE . " ug
			WHERE ug.group_id = {$row['perm_group_id']}
				AND ug.user_pending = 0";
		$result2 = $db->sql_query($sql2);
		while ($row2 = $db->sql_fetchrow($result2))
		{
			$i_view_ary[$row2['user_id']] = $row['i_view'];
		}
		$db->sql_freeresult($result2);
	}
	$db->sql_freeresult($result);



	// Now, we have to do a little step before really sending, we need to distinguish our users a little bit. ;)
	$msg_users = $delete_ids = $update_notification = array();
	foreach ($notify_rows as $user_id => $row)
	{
		if (($i_view_ary[$row['user_id']] != 1) || !trim($row['user_email']))
		{
			$delete_ids[$row['notify_type']][] = $row['user_id'];
		}
		else
		{
			$msg_users[] = $row;
			$update_notification[$row['notify_type']][] = $row['user_id'];
		}
	}
	unset($notify_rows);

	// Now, we are able to really send out notifications
	if (sizeof($msg_users))
	{
		include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
		$messenger = new messenger();

		$msg_list_ary = array();
		foreach ($msg_users as $row)
		{
			$pos = (!isset($msg_list_ary[$row['template']])) ? 0 : sizeof($msg_list_ary[$row['template']]);

			$msg_list_ary[$row['template']][$pos]['method']	= $row['method'];
			$msg_list_ary[$row['template']][$pos]['email']	= $row['user_email'];
			$msg_list_ary[$row['template']][$pos]['jabber']	= $row['user_jabber'];
			$msg_list_ary[$row['template']][$pos]['name']	= $row['username'];
			$msg_list_ary[$row['template']][$pos]['lang']	= $row['user_lang'];
		}
		unset($msg_users);

		foreach ($msg_list_ary as $email_template => $email_list)
		{
			foreach ($email_list as $addr)
			{
				$messenger->template($email_template, $addr['lang']);

				$messenger->to($addr['email'], $addr['name']);
				$messenger->im($addr['jabber'], $addr['name']);

				$messenger->assign_vars(array(
					'USERNAME'		=> htmlspecialchars_decode($addr['name']),
					'IMAGE_NAME'	=> htmlspecialchars_decode($image_name),
					'ALBUM_NAME'	=> htmlspecialchars_decode($album_data['album_name']),

					'U_ALBUM'				=> generate_board_url() . '/' . $gallery_root_path . "album.$phpEx?album_id=$album_id",
					'U_IMAGE'				=> generate_board_url() . '/' . $gallery_root_path . "image_page.$phpEx?album_id=$album_id&image_id=$image_id",
					'U_NEWEST_POST'			=> generate_board_url() . '/' . $gallery_root_path . "viewtopic.$phpEx?album_id=$album_id&image_id=$image_id",
					'U_STOP_WATCHING_IMAGE'	=> generate_board_url() . '/' . $gallery_root_path . "posting.$phpEx?mode=image&submode=unwatch&album_id=$album_id&image_id=$image_id",
					'U_STOP_WATCHING_ALBUM'	=> generate_board_url() . '/' . $gallery_root_path . "posting.$phpEx?mode=album&submode=unwatch&album_id=$album_id",
				));

				$messenger->send($addr['method']);
			}
		}
		unset($msg_list_ary);

		$messenger->save_queue();
	}

	// Now delete the user_ids not authorised to receive notifications on this image/album
	if (!empty($delete_ids['image']))
	{
		$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . "
			WHERE image_id = $image_id
				AND " . $db->sql_in_set('user_id', $delete_ids['image']);
		$db->sql_query($sql);
	}

	if (!empty($delete_ids['album']))
	{
		$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . "
			WHERE album_id = $album_id
				AND " . $db->sql_in_set('user_id', $delete_ids['album']);
		$db->sql_query($sql);
	}
}
?>