<?php

/**
*
* @package phpBB3
* @version $Id: upload.php 288 2008-02-14 16:29:33Z nickvergessen $
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/gallery');
$user->add_lang('posting');

include_once("{$phpbb_root_path}{$gallery_root_path}includes/common.$phpEx");
include_once("{$phpbb_root_path}{$gallery_root_path}includes/permissions.$phpEx");
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

if ($image_id)
{
	$image_data = get_image_info($image_id);
}
$album_id = (isset($image_data['image_album_id'])) ? $image_data['image_album_id'] : $album_id;
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

//send some cheaters back
if ($user->data['is_bot'])
{
	redirect(($image_id) ? $image_backlink : $album_backlink);
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
				if (!$album_data['album_type'])
				{
					meta_refresh(3, $album_backlink);
					trigger_error('ALBUM_IS_CATEGORY');
				}
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
				else if (!gallery_acl_check('m_status', $album_id) && ($image_data['image_status'] != 1))
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
				else if (!gallery_acl_check('m_status', $album_id) && ($image_data['image_status'] != 1))
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
				else if (!gallery_acl_check('m_status', $album_id) && ($image_data['image_status'] != 1))
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
		if (($image_data['image_status'] != 1) && !gallery_acl_check('m_status', $album_id))
		{
			trigger_error('IMAGE_LOCKED');
		}
		$sql = 'SELECT *
			FROM ' . GALLERY_COMMENTS_TABLE . "
			WHERE comment_id = '$comment_id'";
		$result = $db->sql_query($sql);
		$comment_data = $db->sql_fetchrow($result);

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
//REMOVE	$message_parser->parse($post_data['enable_bbcode'], ($config['allow_post_links']) ? $post_data['enable_urls'] : false, $post_data['enable_smilies'], $img_status, $flash_status, $quote_status, $config['allow_post_links']);


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
				$submit = true;//to redirect
			}
			break;
			case 'unwatch':
			if ($submode == 'unwatch')
			{
				$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . " WHERE album_id = $album_id AND user_id = " . $user->data['user_id'];
				$db->sql_query($sql);
				$message = $user->lang['UNWATCHED_ALBUM'] . '<br />';
				$submit = true;//to redirect
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
				//Check Album Configuration Quota
				if ($album_config['max_pics'] >= 0)
				{//do we have enough images in this album?
					if ($album_data['album_images'] >= $album_config['max_pics'])
					{
						trigger_error('ALBUM_REACHED_QUOTA');
					}
				}
				// Check User Limit
				$sql = 'SELECT COUNT(image_id) AS count
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE image_user_id = ' . $user->data['user_id'] . '
						AND image_album_id = ' . $album_id;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$own_pics = $row['count'];
				if ($own_pics >= gallery_acl_check('i_count', $album_id))
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
							if ($album_config['gd_version'] == 0)
							{
								$image_data['thumbnail_type']	= $_FILES['thumbnail']['type'][$i];
								$image_data['thumbnail_size']	= $_FILES['thumbnail']['size'][$i];
								$image_data['thumbnail_tmp']	= $_FILES['thumbnail']['tmp_name'][$i];
							}
							if (
								((!$image_data['image_size']) || ($image_data['image_size'] > $album_config['max_file_size']))
								||
								(($album_config['gd_version'] == 0) && (!$image_data['thumbnail_size'] || ($image_data['thumbnail_size'] > $album_config['max_file_size'])))
							)
							{
								//$error_count[] = 'BAD_UPLOAD_FILE_SIZE';
								trigger_error('BAD_UPLOAD_FILE_SIZE');
							}
							switch ($image_data['image_type'])
							{
								case 'image/jpeg':
								case 'image/jpg':
								case 'image/pjpeg':
									if (!$album_config['jpg_allowed']) {
										trigger_error('NOT_ALLOWED_FILE_TYPE');
									}
									$image_data['image_type2'] = '.jpg';
								break;
								case 'image/png':
								case 'image/x-png':
									if (!$album_config['png_allowed']) {
										trigger_error('NOT_ALLOWED_FILE_TYPE');
									}
									$image_data['image_type2'] = '.png';
								break;
								case 'image/gif':
									if (!$album_config['gif_allowed']) {
										trigger_error('NOT_ALLOWED_FILE_TYPE');
									}
									$image_data['image_type2'] = '.gif';
								break;
								default:
									trigger_error('NOT_ALLOWED_FILE_TYPE');
								break;
							}
							if ($album_config['gd_version'] == 0)
							{
								if ($image_data['image_type'] <> $image_data['thumbnail_type'])
								{
									trigger_error('FILETYPE_AND_THUMBTYPE_DO_NOT_MATCH');
								}
							}
							$image_data_2 = array(
								'filename'			=> '',
								'image_album_id'	=> $album_data['album_id'],
								'image_album_name'	=> $album_data['album_name'],
								'image_desc'		=> str_replace('{NUM}', $loop, request_var('message', '', true)),
								'image_time'		=> time() + $loop,
								'thumbnail'			=> '',
								'username'			=> request_var('username', $user->data['username']),
							);
							$image_data_2['image_name'] = (request_var('filename', '') == 'filename') ? str_replace("_", " ", utf8_substr($image_data['image_tmp_name'], 0, -4)) : str_replace('{NUM}', $loop, request_var('image_name', '', true));
							$image_data = array_merge($image_data, $image_data_2);

							if(!$image_data['image_name'])
							{
								trigger_error('MISSING_IMAGE_TITLE');
							}
							if (!$user->data['is_registered'] && $image_data['username'])
							{
								include_once("{$phpbb_root_path}includes/functions_user.$phpEx");
								$result = validate_username($image_data['username']);
								if ($result['error'])
								{
									trigger_error($result['error_msg']);
								}
							}

							// Generate filename and upload
							srand((double)microtime()*1000000);// for older than version 4.2.0 of PHP
							do
							{
								$image_data['filename'] = md5(uniqid(rand())) . $image_data['image_type2'];
							}
							while(file_exists(GALLERY_UPLOAD_PATH . $image_data['filename']));
							if ($album_config['gd_version'] == 0)
							{
								$image_data['thumbnail'] = $image_data['filename'];
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
							$move_file($image_data['image_tmp'], $phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
							@chmod($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename'], 0777);
							if ($album_config['gd_version'] == 0)
							{
								$move_file($image_data['thumbnail_tmp'], $phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail']);
								@chmod($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail'], 0777);
							}

							$image_size = getimagesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
							$image_data['width'] = $image_size[0];
							$image_data['height'] = $image_size[1];
							if (($image_data['width'] > $album_config['max_width']) || ($image_data['height'] > $album_config['max_height']))
							{
								@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
								if ($album_config['gd_version'] == 0)
								{
									@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail']);
								}
								trigger_error('UPLOAD_IMAGE_SIZE_TOO_BIG');
							}

							if ($album_config['gd_version'] == 0)
							{
								$thumbnail_size = getimagesize($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail']);
								$image_data['thumbnail_width'] = $thumbnail_size[0];
								$image_data['thumbnail_height'] = $thumbnail_size[1];
								if (($image_data['thumbnail_width'] > $album_config['thumbnail_size']) || ($image_data['thumbnail_height'] > $album_config['thumbnail_size']))
								{
									@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
									@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail']);
									trigger_error('UPLOAD_THUMBNAIL_SIZE_TOO_BIG');
								}
							}

							// This image is okay, we can cache its thumbnail now
							if (($album_config['thumbnail_cache']) && ($album_config['gd_version'] > 0)) 
							{
								$gd_errored = FALSE; 
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
								if (!$src)
								{
									$gd_errored = TRUE;
									$image_data['thumbnail'] = '';
								}
								else if (($image_data['width'] > $album_config['thumbnail_size']) || ($image_data['height'] > $album_config['thumbnail_size']))
								{
									// Resize it
									if ($image_data['width'] > $image_data['height'])
									{
										$thumbnail_width	= $album_config['thumbnail_size'];
										$thumbnail_height	= $album_config['thumbnail_size'] * ($image_data['height'] / $image_data['width']);
									}
									else
									{
										$thumbnail_height	= $album_config['thumbnail_size'];
										$thumbnail_width	= $album_config['thumbnail_size'] * ($image_data['width'] / $image_data['height']);
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
									@$resize_function($thumbnail, $src, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $image_data['width'], $image_data['height']);

									if ($album_config['thumbnail_info_line'])
									{// Create image details credits to Dr.Death
										$dimension_font = 1;
										$dimension_filesize = filesize($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['filename']);
										$dimension_string = $image_data['width'] . "x" . $image_data['height'] . "(" . intval($dimension_filesize/1024) . "KiB)";
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
									$image_data['thumbnail'] = $image_data['filename'];
									// Write to disk
									switch ($image_data['image_type2'])
									{
										case '.jpg':
											@imagejpeg($thumbnail, $phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail'], $album_config['thumbnail_quality']);
										break;

										case '.png':
											@imagepng($thumbnail, $phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail']);
										break;

										case '.gif':
											@imagegif($thumbnail, $phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail']);
										break;
									}
									@chmod($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['thumbnail'], 0777);
								}
							}
							else if ($album_config['gd_version'] > 0)
							{
								$image_data['thumbnail'] = '';
							}

							$image_data = upload_image($image_data);
							$image_id = $image_data['image_id'];
							$image_name = $image_data['image_name'];
							$image_id_ary[] = $image_id;
						}
					}//foreach
					$image_id = ($images > 1) ? 0 : $image_id;
					// Complete... now send a message to user

					if ($images < 1)
					{
						$error .= (($error) ? '<br />' : '') . $user->lang['UPLOAD_NO_FILE'];
					}
					else
					{
						if ((request_var('image_name', '', true) == '') && (request_var('filename', '') != 'filename'))
						{
							$error .= (($error) ? '<br />' : '') . $user->lang['MISSING_IMAGE_TITLE'];
						}
						notify_gallery('album', $album_id, $image_name);
						handle_image_counter($image_id_ary, true);
						$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . " 
							SET album_images_real = album_images_real + $images
							WHERE album_id = $album_id";
						$db->sql_query($sql);
					}
				}//submit
				$template->assign_vars(array(
					'ERROR'						=> $error,
					'U_VIEW_ALBUM'				=> append_sid("{$phpbb_root_path}{$gallery_root_path}album.$phpEx", "album_id=$album_id"),
					'CAT_TITLE'					=> $album_data['album_name'],
					'S_MAX_FILESIZE'			=> $album_config['max_file_size'],
					'S_MAX_WIDTH'				=> $album_config['max_width'],
					'S_MAX_HEIGHT'				=> $album_config['max_height'],

					'S_JPG'					=> ($album_config['jpg_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],
					'S_PNG'					=> ($album_config['png_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],
					'S_GIF'					=> ($album_config['gif_allowed'] == 1) ? $user->lang['YES'] : $user->lang['NO'],
					'S_THUMBNAIL_SIZE'		=> $album_config['thumbnail_size'],
					'S_THUMBNAIL'			=> ($album_config['gd_version']) ? true : false,
					'S_MULTI_IMAGES'		=> ($album_config['upload_images'] > 1) ? true : false,
					'S_ALBUM_ACTION'		=> append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=upload&amp;album_id=$album_id"),

					'IMAGE_RSZ_WIDTH'		=> $album_config['preview_rsz_width'],
					'IMAGE_RSZ_HEIGHT'		=> $album_config['preview_rsz_height'],
					'L_DESCRIPTION_LENGTH'	=> sprintf($user->lang['DESCRIPTION_LENGTH'], $album_config['description_length']),
					'USERNAME'				=> request_var('username', '', true),
					'IMAGE_NAME'			=> request_var('image_name', '', true),
					'MESSAGE'				=> request_var('message', '', true),
					'S_IMAGE'				=> true,
					'S_UPLOAD'				=> true,
				));

				$count = 0;
				$upload_image_files = $album_config['upload_images'];
				if ((gallery_acl_check('i_count', $album_id) - $own_pics) < $upload_image_files)
				{
					$upload_image_files = (gallery_acl_check('i_count', $album_id) - $own_pics);
					$error .= (($error) ? '<br />' : '') . sprintf($user->lang['USER_NEARLY_REACHED_QUOTA'], gallery_acl_check('i_count', $album_id), $own_pics, $upload_image_files);
					$template->assign_vars(array(
						'ERROR'						=> $error,
					));
				}

				while ($count < $upload_image_files)
				{
					$template->assign_block_vars('upload_image', array());
					$count++;
				}

				if ($album_config['gd_version'] == 0)
				{
					$template->assign_block_vars('switch_manual_thumbnail', array());
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
				update_lastimage_info($album_id);
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

					if(empty($image_name))
					{
						trigger_error('MISSING_IMAGE_TITLE');
					}
					$message_parser				= new parse_message();
					$message_parser->message	= utf8_normalize_nfc($image_desc);
					if($message_parser->message)
					{
						$message_parser->parse(true, true, true, true, false, true, true, true);
					}


					/**
					* Update the DB
					*/
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
					'L_DESCRIPTION_LENGTH'	=> sprintf($user->lang['DESCRIPTION_LENGTH'], $album_config['description_length']),

					'U_IMAGE'			=> ($image_id) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", "album_id=$album_id&amp;image_id=$image_id") : '',
					'U_VIEW_IMAGE'		=> ($image_id) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id") : '',
					'IMAGE_RSZ_WIDTH'	=> $album_config['preview_rsz_width'],
					'IMAGE_RSZ_HEIGHT'	=> $album_config['preview_rsz_height'],

					'S_IMAGE'			=> true,
					'S_EDIT'			=> true,
					'S_ALBUM_ACTION'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=edit&amp;album_id=$album_id&amp;image_id=$image_id"),
				));
				$message = $user->lang['IMAGES_UPDATED_SUCCESSFULLY'] . '<br />';
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
					/**
					* Update the DB
					*/
					$sql_ary = array(
						'report_album_id'			=> $album_id,
						'report_image_id'			=> $image_id,
						'reporter_id'				=> $user->data['user_id'],
						'report_note'				=> $report_message,
						'report_time'				=> time(),
						'report_status'				=> 1,
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
						$sql_ary = array(
							'image_reported'				=> $report_id,
						);

						$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE image_id = $image_id";
						$db->sql_query($sql);
					}
				}

				$template->assign_vars(array(
					'ERROR'				=> $error,
					'U_IMAGE'			=> ($image_id) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", "album_id=$album_id&amp;image_id=$image_id") : '',
					'U_VIEW_IMAGE'		=> ($image_id) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id") : '',
					'IMAGE_RSZ_WIDTH'	=> $album_config['preview_rsz_width'],
					'IMAGE_RSZ_HEIGHT'	=> $album_config['preview_rsz_height'],

					'S_REPORT'			=> true,
					'S_ALBUM_ACTION'	=> append_sid("{$phpbb_root_path}{$gallery_root_path}posting.$phpEx", "mode=image&amp;submode=report&amp;album_id=$album_id&amp;image_id=$image_id"),
				));
				$message = $user->lang['IMAGES_REPORTED_SUCCESSFULLY'] . '<br />';
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
				$submit = true;//to redirect
			}
			break;
			case 'unwatch':
			if ($submode == 'unwatch')
			{
				$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . " WHERE image_id = $image_id AND user_id = " . $user->data['user_id'];
				$db->sql_query($sql);
				$message = $user->lang['UNWATCHED_IMAGE'] . '<br />';
				$submit = true;//to redirect
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
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET image_favorited = image_favorited + 1 WHERE image_id = ' . $image_id;
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
				$submit = true;//to redirect
			}
			break;
			case 'unfavorite':
			if ($submode == 'unfavorite')
			{
				$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . " WHERE image_id = $image_id AND user_id = " . $user->data['user_id'];
				$db->sql_query($sql);
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET image_favorited = image_favorited - 1 WHERE image_id = ' . $image_id;
				$db->sql_query($sql);
				$message = $user->lang['UNFAVORITED_IMAGE'] . '<br />';
				$submit = true;//to redirect
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

					if (($image_data['image_thumbnail'] <> '') && @file_exists($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['image_thumbnail']))
					{
						@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['image_thumbnail']);
					}
					@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['image_filename']);
					handle_image_counter($image_id, false);

					$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . "
						WHERE comment_image_id = $image_id";
					$result = $db->sql_query($sql);

					$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . "
						WHERE image_id = $image_id";
					$result = $db->sql_query($sql);

					$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . "
						WHERE rate_image_id = $image_id";
					$result = $db->sql_query($sql);

					$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . "
						WHERE image_id = $image_id";
					$result = $db->sql_query($sql);

					$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . "
						WHERE image_id = $image_id";
					$result = $db->sql_query($sql);

					//update album-information
					update_lastimage_info($album_id);

					$submit = true;
					$message = $user->lang['DELETED_IMAGE'] . '<br />';
					$image_id = false;
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
		$comment_username_req = false;
		/*
		* Rating-System: now you can comment and rate in one form
		*/
		$rate_point = request_var('rate', 0);
		if ($album_config['allow_rates'])
		{
			$allowed_to_rate = $your_rating = false;

			if ($user->data['is_registered'])
			{
				$sql = 'SELECT *
					FROM ' . GALLERY_RATES_TABLE . '
					WHERE rate_image_id = ' . (int) $image_id . '
						AND rate_user_id = ' . (int) $user->data['user_id'];
				$result = $db->sql_query($sql);

				if ($db->sql_affectedrows($result) > 0)
				{
					$rated = $db->sql_fetchrow($result);
					$your_rating = $rated['rate_point'];
				}
			}

			// Check: User didn't rate yet, has permissions, it's not the users own image and the user is logged in
			if (!$your_rating && gallery_acl_check('i_rate', $album_id) && ($user->data['user_id'] != $image_data['image_user_id']) && ($user->data['user_id'] != ANONYMOUS))
			{
				// User just rated the image, so we store it
				if ($rate_point > 0)
				{
					if ($rate_point > $album_config['rate_scale'])
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
				else
				{
					for ($rate_scale = 1; $rate_scale <= $album_config['rate_scale']; $rate_scale++)
					{
						$template->assign_block_vars('rate_scale', array(
							'RATE_POINT'	=> $rate_scale,
						));
					}
					$allowed_to_rate = true;
				}
			}
			$template->assign_vars(array(
				'S_ALLOWED_TO_RATE'	=> $allowed_to_rate,
			));
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
					if (utf8_strlen($comment_text) > $album_config['desc_length'])
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
						$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . " SET image_comments = image_comments + 1,
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
						notify_gallery('image', $image_id, $image_data['image_name']);
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
					if (utf8_strlen($comment_text) > $album_config['desc_length'])
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
					}
				}
				else
				{
					$comment_ary = generate_text_for_edit($comment_data['comment'], $comment_data['comment_uid'], $comment_data['comment_bitfield'], 7);
					$comment = $comment_ary['text'];
					$comment_username = $comment_data['comment_username'];
				}
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
					$sql = 'SELECT comment_id
						FROM ' . GALLERY_COMMENTS_TABLE . "
						WHERE comment_image_id = $image_id
						ORDER BY comment_id";
					$result = $db->sql_query_limit($sql, 1);
					$row = $db->sql_fetchrow($result);
					$last_comment_id = (isset($row['comment_id']))? $row['comment_id'] : 0;
					$db->sql_freeresult($result);
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . " SET image_comments = image_comments - 1,
						image_last_comment = $last_comment_id
						WHERE " . $db->sql_in_set('image_id', $image_id);
					$db->sql_query($sql);
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
						confirm_box(false, 'DELETE_COMMENT', $s_hidden_fields);
					}
				}

			break;
		}
		$template->assign_vars(array(
			'ERROR'					=> $error,
			'MESSAGE'				=> $comment,
			'USERNAME'				=> $comment_username,
			'REQ_USERNAME'			=> $comment_username_req,
			'L_COMMENT_LENGTH'		=> sprintf($user->lang['COMMENT_LENGTH'], $album_config['comment_length']),

			'IMAGE_RSZ_WIDTH'	=> $album_config['preview_rsz_width'],
			'IMAGE_RSZ_HEIGHT'	=> $album_config['preview_rsz_height'],
			'U_IMAGE'			=> ($image_id) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image.$phpEx", "album_id=$album_id&amp;image_id=$image_id") : '',
			'U_VIEW_IMAGE'			=> ($image_id) ? append_sid("{$phpbb_root_path}{$gallery_root_path}image_page.$phpEx", "album_id=$album_id&amp;image_id=$image_id") : '',
			'IMAGE_NAME'			=> ($image_id) ? $image_data['image_name'] : '',

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

// Output page
$page_title = $user->lang['UPLOAD_IMAGE'];
page_header($page_title);
$template->set_filenames(array(
	'body' => 'gallery_posting_body.html',
));
page_footer();



function upload_image(&$image_data)
{
	global $user, $db, $album_id;

	$sql_ary = array(
		'image_filename' 		=> $image_data['filename'],
		'image_thumbnail'		=> $image_data['thumbnail'],
		'image_name'			=> $image_data['image_name'],
		'image_user_id'			=> $user->data['user_id'],
		'image_user_colour'		=> $user->data['user_colour'],
		'image_username'		=> $image_data['username'],
		'image_user_ip'			=> $user->ip,
		'image_time'			=> $image_data['image_time'],
		'image_album_id'		=> $image_data['image_album_id'],
		'image_status'			=> (gallery_acl_check('i_approve', $album_id)) ? 1 : 0,
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

function notify_gallery($mode, $handle_id, $image_name)
{
	global $phpbb_root_path, $gallery_root_path, $phpEx;
	global $user, $db, $album_id, $image_id, $image_data, $album_data;

	$help_mode = $mode . '_id';
	$mode_id = $$help_mode;
	$mode_notification = ($mode == 'album') ? 'image' : 'comment';

	if (1 == 1)
	{
	
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
	}

	$notify_rows = array();

	// -- get forum_userids	|| topic_userids
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