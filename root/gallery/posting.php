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
$phpbb_root_path = $gallery_root_path = '';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . $gallery_root_path . 'includes/root_path.' . $phpEx);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('mods/gallery', 'posting'));

$gallery_root_path = GALLERY_ROOT_PATH;
include($phpbb_root_path . $gallery_root_path . 'includes/common.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/permissions.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . $gallery_root_path . 'includes/functions_posting.' . $phpEx);

add_form_key('gallery');
$submit = (isset($_POST['submit'])) ? true : false;
$mode = request_var('mode', '');
$submode = request_var('submode', '');
$album_id = request_var('album_id', 0);
$image_id = request_var('image_id', 0);
$comment_id = request_var('comment_id', 0);
$error = $message = $s_album_action = '';
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
				if (!gallery_acl_check('i_view', $album_id, $album_data['album_user_id']))
				{
					gallery_not_authorised($image_backlink, $user, $image_loginlink);
				}
			break;

			default:
				trigger_error('MISSING_SUBMODE');
			break;
		}
	break;
	case 'image':
		if (!gallery_acl_check('m_status', $album_id, $album_data['album_user_id']) && ($album_data['album_status'] == ITEM_LOCKED))
		{
			gallery_not_authorised($image_backlink, $user, $image_loginlink);
		}
		if ($image_id && (!gallery_acl_check('m_status', $album_id, $album_data['album_user_id']) && ($image_data['image_status'] != IMAGE_APPROVED)))
		{
			gallery_not_authorised($image_backlink, $user, $image_loginlink);
		}
		switch ($submode)
		{
			case 'upload':
				if (!gallery_acl_check('i_upload', $album_id, $album_data['album_user_id']) || ($album_data['album_status'] == ITEM_LOCKED))
				{
					gallery_not_authorised($album_backlink, $user, $album_loginlink);
				}
				if ($album_data['contest_id'] && (time() < $album_data['contest_start']))
				{
					gallery_not_authorised($album_backlink, $user, $album_loginlink);
				}
				elseif ($album_data['contest_id'] && (time() > ($album_data['contest_start'] + $album_data['contest_rating'])))
				{
					gallery_not_authorised($album_backlink, $user, $album_loginlink);
				}
			break;
			case 'edit':
				if (!gallery_acl_check('i_edit', $album_id, $album_data['album_user_id']))
				{
					if (!gallery_acl_check('m_edit', $album_id, $album_data['album_user_id']))
					{
						gallery_not_authorised($image_backlink, $user, $image_loginlink);
					}
				}
				else if (($image_data['image_user_id'] != $user->data['user_id']) && !gallery_acl_check('m_edit', $album_id, $album_data['album_user_id']))
				{
					gallery_not_authorised($image_backlink, $user, $image_loginlink);
				}
			break;
			case 'report':
				if (!gallery_acl_check('i_report', $album_id, $album_data['album_user_id']) || ($image_data['image_user_id'] == $user->data['user_id']))
				{
					gallery_not_authorised($image_backlink, $user, $image_loginlink);
				}
			break;
			case 'delete':
				if (!gallery_acl_check('i_delete', $album_id, $album_data['album_user_id']))
				{
					if (!gallery_acl_check('m_delete', $album_id, $album_data['album_user_id']))
					{
						gallery_not_authorised($image_backlink, $user, $image_loginlink);
					}
				}
				else if (($image_data['image_user_id'] != $user->data['user_id']) && !gallery_acl_check('m_delete', $album_id, $album_data['album_user_id']))
				{
					gallery_not_authorised($image_backlink, $user, $image_loginlink);
				}
			break;
			case 'watch':
			case 'unwatch':
			case 'favorite':
			case 'unfavorite':
				if (!gallery_acl_check('i_view', $album_id, $album_data['album_user_id']))
				{
					gallery_not_authorised($image_backlink, $user, $image_loginlink);
				}
			break;

			default:
				trigger_error('MISSING_SUBMODE');
			break;
		}
	break;
	case 'comment':
		if (!gallery_acl_check('m_status', $album_id, $album_data['album_user_id']) && (($image_data['image_status'] != IMAGE_APPROVED) || ($album_data['album_status'] == ITEM_LOCKED)))
		{
			gallery_not_authorised($image_backlink, $user, $image_loginlink);
		}
		if (($submode != 'rate') && (!$gallery_config['allow_comments']))
		{
			gallery_not_authorised($image_backlink, $user, $image_loginlink);
		}
		if (((!$submit || !$gallery_config['allow_rates'])) && ($submode == 'rate'))
		{
			gallery_not_authorised($image_backlink, $user, $image_loginlink);
		}
		if ($submode == 'rate')
		{
			if (time() < ($album_data['contest_start'] + $album_data['contest_rating']))
			{
				gallery_not_authorised($image_backlink, $user, $image_loginlink);
			}
		}
		else
		{
			if (time() < ($album_data['contest_start'] + $album_data['contest_end']))
			{
				gallery_not_authorised($image_backlink, $user, $image_loginlink);
			}
		}
		switch ($submode)
		{
			case 'add':
				if (!gallery_acl_check('c_post', $album_id, $album_data['album_user_id']))
				{
					gallery_not_authorised($image_backlink, $user, $image_loginlink);
				}
			break;

			case 'edit':
				if (!gallery_acl_check('c_edit', $album_id, $album_data['album_user_id']))
				{
					if (!gallery_acl_check('m_comments', $album_id, $album_data['album_user_id']))
					{
						gallery_not_authorised($image_backlink, $user, $image_loginlink);
					}
				}
				else if (($comment_data['comment_user_id'] != $user->data['user_id']) && !gallery_acl_check('m_comments', $album_id, $album_data['album_user_id']))
				{
					gallery_not_authorised($image_backlink, $user, $image_loginlink);
				}
			break;

			case 'delete':
				if (!gallery_acl_check('c_delete', $album_id, $album_data['album_user_id']))
				{
					if (!gallery_acl_check('m_comments', $album_id, $album_data['album_user_id']))
					{
						gallery_not_authorised($image_backlink, $user, $image_loginlink);
					}
				}
				else if (($comment_data['comment_user_id'] != $user->data['user_id']) && !gallery_acl_check('m_comments', $album_id, $album_data['album_user_id']))
				{
					gallery_not_authorised($image_backlink, $user, $image_loginlink);
				}
			break;

			case 'rate':
				if (!gallery_acl_check('i_rate', $album_id, $album_data['album_user_id']) || ($image_data['image_user_id'] == $user->data['user_id']))
				{
					gallery_not_authorised($image_backlink, $user, $image_loginlink);
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

function gallery_not_authorised($backlink, $user, $loginlink)
{
	if (!$user->data['is_registered'])
	{
		login_box($loginlink , $user->lang['LOGIN_INFO']);
	}
	else
	{
		meta_refresh(3, $backlink);
		trigger_error('NOT_AUTHORISED');
	}
}


$bbcode_status	= ($config['allow_bbcode']) ? true : false;
$smilies_status	= ($config['allow_smilies']) ? true : false;
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
				// 1. Check album-configuration Quota
				if ($gallery_config['images_per_album'] >= 0)
				{
					if ($album_data['album_images'] >= $gallery_config['images_per_album'])
					{
						trigger_error('ALBUM_REACHED_QUOTA');
					}
				}
				// 2. Check user-limit, if he is not allowed to go unlimited
				if (!gallery_acl_check('i_unlimited', $album_id, $album_data['album_user_id']))
				{
					$sql = 'SELECT COUNT(image_id) count
						FROM ' . GALLERY_IMAGES_TABLE . '
						WHERE image_user_id = ' . $user->data['user_id'] . '
							AND image_album_id = ' . $album_id;
					$result = $db->sql_query($sql);
					$own_images = (int) $db->sql_fetchfield('count');
					$db->sql_freeresult($result);
					if ($own_images >= gallery_acl_check('i_count', $album_id, $album_data['album_user_id']))
					{
						trigger_error(sprintf($user->lang['USER_REACHED_QUOTA'], gallery_acl_check('i_count', $album_id, $album_data['album_user_id'])));
					}
				}

				$images = 0;
				if($submit)
				{
					if (!check_form_key('gallery'))
					{
						trigger_error('FORM_INVALID');
					}

					$allowed_extensions = array();
					if ($gallery_config['jpg_allowed'])
					{
						$allowed_extensions[] = 'jpg';
						$allowed_extensions[] = 'jpeg';
					}
					if ($gallery_config['gif_allowed'])
					{
						$allowed_extensions[] = 'gif';
					}
					if ($gallery_config['png_allowed'])
					{
						$allowed_extensions[] = 'png';
					}

					if (!class_exists('fileupload'))
					{
						include($phpbb_root_path . 'includes/functions_upload.' . $phpEx);
					}
					$fileupload = new fileupload();
					$fileupload->fileupload('', $allowed_extensions, (4 * $gallery_config['max_file_size']));

					$upload_image_files = (gallery_acl_check('i_unlimited', $album_id, $album_data['album_user_id'])) ? $gallery_config['upload_images'] : min((gallery_acl_check('i_count', $album_id, $album_data['album_user_id']) - $own_images), $gallery_config['upload_images']);

					// Get File Upload Info
					$image_id_ary = array();
					$loop = request_var('image_num', 0);
					$rotate = request_var('rotate', array(0));
					$loop = ($loop != 0) ? $loop - 1 : $loop;
					for ($i = 0; $i < $upload_image_files; $i++)
					{
						$image_file = $fileupload->form_upload('image_file_' . $i);
						if (!$image_file->uploadname)
						{
							continue;
						}
						$image_file->clean_filename('unique_ext'/*, $user->data['user_id'] . '_'*/);
						$image_file->move_file(substr(GALLERY_UPLOAD_PATH, 0, -1), false, false, CHMOD_ALL);
						if (sizeof($image_file->error) && $image_file->uploadname)
						{
							$image_file->remove();
							trigger_error(implode('<br />', $image_file->error));
						}
						@chmod($image_file->destination_file, 0777);
						$image_data = array();
						if (1 == 1)
						{
							$loop = $loop + 1;
							$images = $images + 1;

							switch ($image_file->mimetype)
							{
								case 'image/jpeg':
								case 'image/jpg':
								case 'image/pjpeg':
									$image_type = 'jpg';
								break;
								case 'image/png':
								case 'image/x-png':
									$image_type = 'png';
								break;
								case 'image/gif':
								case 'image/giff':
									$image_type = 'gif';
								break;
							}
							$image_data = array(
								'filename'			=> $image_file->realname,
								'image_album_id'	=> $album_data['album_id'],
								'image_album_name'	=> $album_data['album_name'],
								'image_name'		=> str_replace('{NUM}', $loop, request_var('image_name', '', true)),
								'image_desc'		=> str_replace('{NUM}', $loop, request_var('message', '', true)),
								'image_time'		=> time() + $loop,
								'image_contest'		=> ($album_data['album_contest']) ? IMAGE_CONTEST : IMAGE_NO_CONTEST,
								'thumbnail'			=> '',
								'username'			=> request_var('username', $user->data['username']),
							);
							$image_data['image_name'] = ((request_var('filename', '') == 'filename') || ($image_data['image_name'] == '')) ? str_replace("_", " ", utf8_substr($image_file->uploadname, 0, strrpos($image_file->uploadname, '.'))) : $image_data['image_name'];

							if (!$image_data['image_name'])
							{
								trigger_error('MISSING_IMAGE_NAME');
							}
							if (!$user->data['is_registered'] && $image_data['username'])
							{
								$result = validate_username($image_data['username']);
								if ($result['error'])
								{
									trigger_error($result['error_msg']);
								}
							}

							if (!class_exists('nv_image_tools'))
							{
								include($phpbb_root_path . $gallery_root_path . 'includes/functions_image.' . $phpEx);
							}

							$image_tools = new nv_image_tools();
							$image_tools->set_image_options($gallery_config['max_file_size'], $gallery_config['max_height'], $gallery_config['max_width']);
							$image_tools->set_image_data($image_file->destination_file, $image_data['image_name'], $image_file->filesize);

							// Read exif data from file
							$image_tools->read_exif_data();
							$image_data['image_exif_data'] = $image_tools->exif_data_serialized;
							$image_data['image_has_exif'] = $image_tools->exif_data_exist;

							/// Rotate the image
							if ($gallery_config['allow_rotate_images'])
							{
								$image_tools->rotate_image($rotate[$i], $gallery_config['allow_resize_images']);
								if ($image_tools->rotated)
								{
									$image_file->height = $image_tools->image_size['height'];
									$image_file->width = $image_tools->image_size['width'];
								}
							}

							// Resize overside images
							if (($image_file->width > $gallery_config['max_width']) || ($image_file->height > $gallery_config['max_height']))
							{
								if ($gallery_config['allow_resize_images'])
								{
									$image_tools->resize_image($gallery_config['max_width'], $gallery_config['max_height']);
									if ($image_tools->resized)
									{
										$image_file->height = $image_tools->image_size['height'];
										$image_file->width = $image_tools->image_size['width'];
									}
								}
								else
								{
									@unlink($image_file->destination_file);
									trigger_error('UPLOAD_IMAGE_SIZE_TOO_BIG');
								}
							}

							if ($image_tools->resized || $image_tools->rotated)
							{
								$image_tools->write_image($image_file->destination_file, $gallery_config['jpg_quality'], true);
								$image_file->filesize = $image_tools->image_size['file'];
							}

							if (!$image_tools->exif_data_force_db && ($image_data['image_has_exif'] == EXIF_DBSAVED))
							{
								// Image was not resized, so we can pull the Exif from the image to save db-memory.
								$image_data['image_has_exif'] = EXIF_AVAILABLE;
								$image_data['image_exif_data'] = '';
							}

							$image_data['image_filesize'] = $image_file->filesize;
							if ($image_data['image_filesize'] > (1.2 * $gallery_config['max_file_size']))
							{
								@unlink($image_file->destination_file);
								trigger_error('BAD_UPLOAD_FILE_SIZE');
							}

							$image_data = upload_image($image_data);
							$image_id = $image_data['image_id'];
							$image_name = $image_data['image_name'];
							$image_id_ary[] = $image_id;
						}
					}
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

					'S_ALLOWED_FILETYPES'	=> implode(', ', $allowed_filetypes),
					'S_THUMBNAIL_SIZE'		=> $gallery_config['thumbnail_size'],//@todo
					'S_THUMBNAIL'			=> ($gallery_config['gd_version']) ? true : false,//@todo
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
					'S_ALLOW_ROTATE'		=> $gallery_config['allow_rotate_images'],
				));

				if (!$error)
				{
					if (gallery_acl_check('i_approve', $album_id, $album_data['album_user_id']))
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

				$count = 0;
				$upload_image_files = $gallery_config['upload_images'];
				if (!gallery_acl_check('i_unlimited', $album_id, $album_data['album_user_id']) && ((gallery_acl_check('i_count', $album_id, $album_data['album_user_id']) - $own_images) < $upload_image_files))
				{
					$upload_image_files = (gallery_acl_check('i_count', $album_id, $album_data['album_user_id']) - $own_images);
					$error .= (($error) ? '<br />' : '') . sprintf($user->lang['USER_NEARLY_REACHED_QUOTA'], gallery_acl_check('i_count', $album_id, $album_data['album_user_id']), $own_images, $upload_image_files);
					$template->assign_vars(array(
						'ERROR'		=> $error,
					));
				}

				while ($count < $upload_image_files)
				{
					$template->assign_block_vars('upload_image', array());
					$count++;
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
						'image_name_clean'			=> utf8_clean_string($image_name),
						'image_desc'				=> $message_parser->message,
						'image_desc_uid'			=> $message_parser->bbcode_uid,
						'image_desc_bitfield'		=> $message_parser->bbcode_bitfield,
					);

					$move_to_personal = request_var('move_to_personal', 0);
					if ($move_to_personal)
					{
						$personal_album_id = 0;
						if ($user->data['user_id'] != $image_data['image_user_id'])
						{
							$sql = 'SELECT personal_album_id
								FROM ' . GALLERY_USERS_TABLE . '
								WHERE user_id = ' . $image_data['image_user_id'];
							$result = $db->sql_query($sql);
							$personal_album_id = (int) $db->sql_fetchfield('personal_album_id');
							$db->sql_freeresult($result);
							$user_entry_exists = ($db->sql_affectedrows()) ? true : false;

							// The User has no personal album, moderators can created that without the need of permissions
							if (!$personal_album_id)
							{
								$personal_album_id = generate_personal_album($image_data['image_username'], $image_data['image_user_id'], $image_data['image_user_colour'], $user_entry_exists);
							}
						}
						else
						{
							$personal_album_id = $user->gallery['personal_album_id'];
							if (!$personal_album_id && gallery_acl_check('i_upload', OWN_GALLERY_PERMISSIONS))
							{
								$user_entry_exists = ($user->gallery['exists']) ? true : false;
								$personal_album_id = generate_personal_album($image_data['image_username'], $image_data['image_user_id'], $image_data['image_user_colour'], $user_entry_exists);
							}
						}
						if ($personal_album_id)
						{
							$sql_ary['image_album_id'] = $personal_album_id;
						}
					}
					else if ($album_data['album_last_image_id'] == $image_id)
					{
						$album_sql_ary = array(
							'album_last_image_name'		=> $image_name,
						);
						$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $album_sql_ary) . '
							WHERE ' . $db->sql_in_set('album_id', $image_data['image_album_id']);
						$db->sql_query($sql);
					}

					$rotate = request_var('rotate', 0);
					if ($gallery_config['allow_rotate_images'] && ($rotate > 0) && (($rotate % 90) == 0))
					{
						if (!class_exists('nv_image_tools'))
						{
							include($phpbb_root_path . $gallery_root_path . 'includes/functions_image.' . $phpEx);
						}

						$image_tools = new nv_image_tools();

						$image_tools->set_image_data($phpbb_root_path . GALLERY_UPLOAD_PATH . $image_data['image_filename']);
						if (($image_data['image_has_exif'] != EXIF_UNAVAILABLE) && ($image_data['image_has_exif'] != EXIF_DBSAVED))
						{
							// Store exif-data to database if there are any and we didn't already do that.
							$image_tools->read_exif_data();
							$sql_ary['image_exif_data'] = $image_tools->exif_data_serialized;
							$sql_ary['image_has_exif'] = $image_tools->exif_data_exist;
						}

						// Rotate the image
						$image_tools->rotate_image($rotate, $gallery_config['allow_resize_images']);
						if ($image_tools->rotated)
						{
							$image_tools->write_image($image_file->destination_file, $gallery_config['jpg_quality'], true);
						}

						@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $image_data['image_filename']);
						@unlink($phpbb_root_path . GALLERY_MEDIUM_PATH . $image_data['image_filename']);
					}

					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' 
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE image_id = ' . $image_id;
					$db->sql_query($sql);

					if ($move_to_personal && $personal_album_id)
					{
						update_album_info($album_data['album_id']);
						update_album_info($personal_album_id);
					}

					if ($user->data['user_id'] != $image_data['image_user_id'])
					{
						add_log('gallery', $image_data['image_album_id'], $image_id, 'LOG_GALLERY_EDITED', $image_name);
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
					'S_ALLOW_ROTATE'	=> $gallery_config['allow_rotate_images'],
					'S_MOVE_PERSONAL'	=> ((gallery_acl_check('i_upload', OWN_GALLERY_PERMISSIONS) || $user->gallery['personal_album_id']) || ($user->data['user_id'] != $image_data['image_user_id'])) ? true : false,
					'S_MOVE_MODERATOR'	=> ($user->data['user_id'] != $image_data['image_user_id']) ? true : false,
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
						add_log('gallery', $image_data['image_album_id'], $image_id, 'LOG_GALLERY_DELETED', $image_data['image_name']);
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
		if ($gallery_config['allow_rates'] && ($submode != 'edit'))
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
			if (!$your_rating && gallery_acl_check('i_rate', $album_id, $album_data['album_user_id']) && ($user->data['user_id'] != $image_data['image_user_id']) && ($user->data['user_id'] != ANONYMOUS))
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
						set_gallery_config_count('num_comments', 1);

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
				if ($comment_data['comment_user_id'] == ANONYMOUS)
				{
					$comment_username_req = true;
				}
				if ($submit)
				{
					if (!check_form_key('gallery'))
					{
						trigger_error('FORM_INVALID');
					}
					$sql_ary = array();

					$comment = request_var('message', '', true);
					$comment_text = $comment;

					if ($comment_username_req)
					{
						$comment_username = request_var('username', '');
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

						$sql_ary = array(
							'comment_username'	=> $comment_username,
						);
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

					$sql_ary = array_merge($sql_ary, array(
						'comment'				=> $message_parser->message,
						'comment_uid'			=> $message_parser->bbcode_uid,
						'comment_bitfield'		=> $message_parser->bbcode_bitfield,
						'comment_edit_count'	=> $comment_data['comment_edit_count'] + 1,
						'comment_edit_time'		=> time(),
						'comment_edit_user_id'	=> $user->data['user_id'],
					));

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
					set_gallery_config_count('num_comments', -1);

					$sql = 'SELECT MAX(comment_id) last_comment
						FROM ' . GALLERY_COMMENTS_TABLE . "
						WHERE comment_image_id = $image_id
						ORDER BY comment_id";
					$result = $db->sql_query_limit($sql, 1);
					$last_comment_id = (int) $db->sql_fetchfield('last_comment');
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

if ($submit)
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

page_header($page_title, false);

$template->set_filenames(array(
	'body' => 'gallery/posting_body.html',
));

page_footer();

?>