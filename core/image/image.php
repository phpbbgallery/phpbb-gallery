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

class phpbb_ext_gallery_core_image
{
	/**
	* Only visible for moderators.
	*/
	const STATUS_UNAPPROVED	= 0;

	/**
	* Visible for everyone with the i_view-permissions
	*/
	const STATUS_APPROVED	= 1;

	/**
	* Visible for everyone with the i_view-permissions, but only moderators can comment.
	*/
	const STATUS_LOCKED		= 2;

	/**
	* Orphan files are only visible for their author, because they're not yet ready uploaded.
	*/
	const STATUS_ORPHAN		= 3;

	/**
	* Constants regarding the image contest relation
	*/
	const NO_CONTEST = 0;

	/**
	* The image is element of an open contest. Only moderators can see the user_name of the user.
	*/
	const IN_CONTEST = 1;

	/**
	* Get image information
	*/
	static public function get_info($image_id, $extended_info = true)
	{
		global $db, $user, $phpbb_dispatcher;

		$sql_array = array(
			'SELECT'		=> 'i.*, w.*',
			'FROM'			=> array(GALLERY_IMAGES_TABLE => 'i'),
			'WHERE'			=> 'i.image_id = ' . (int) $image_id,
		);

		if ($extended_info)
		{
			$sql_array['LEFT_JOIN'] = array(
				array(
					'FROM'		=> array(GALLERY_WATCH_TABLE => 'w'),
					'ON'		=> 'i.image_id = w.image_id AND w.user_id = ' . $user->data['user_id'],
				),
			);
		}

		$vars = array('image_id', 'extended_info', 'sql_array');
		extract($phpbb_dispatcher->trigger_event('gallery.core.image.get_data', compact($vars)));

		$sql = $db->sql_build_query('SELECT', $sql_array);

		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error('IMAGE_NOT_EXIST');
		}

		return $row;
	}

	static public function get_new_author_info($username)
	{
		global $db;

		// Who is the new uploader?
		if (!$username)
		{
			return false;
		}
		$user_id = 0;
		if ($username)
		{
			if (!function_exists('user_get_id_name'))
			{
				$phpbb_ext_gallery->url->_include('functions_user', 'phpbb');
			}
			user_get_id_name($user_id, $username);
		}

		if (empty($user_id))
		{
			return false;
		}

		$sql = 'SELECT username, user_colour, user_id
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $user_id[0];
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		return $row;
	}

	/**
	* Delete an image completly.
	*
	* @param	array		$images		Array with the image_id(s)
	* @param	array		$filenames	Array with filenames for the image_ids. If a filename is missing it's queried from the database.
	*									Format: $image_id => $filename
	* @param	bool		$skip_files	If set to true, we won't try to delete the source files.
	*/
	static public function delete_images($images, $filenames = array(), $resync_albums = true, $skip_files = false)
	{
		global $db;

		if (empty($images))
		{
			return;
		}

		if (!$skip_files)
		{
			// Delete the files from the disc...
			$need_filenames = array();
			foreach ($images as $image)
			{
				if (!isset($filenames[$image]))
				{
					$need_filenames[] = $image;
				}
			}
			$filenames = array_merge($filenames, self::get_filenames($need_filenames));
			phpbb_gallery_image_file::delete($filenames);
		}

		// Delete the ratings...
		phpbb_gallery_image_rating::delete_ratings($images);
		phpbb_gallery_comment::delete_images($images);
		phpbb_gallery_notification::delete_images($images);
		phpbb_gallery_report::delete_images($images);

		$vars = array('images', 'filenames');
		extract($phpbb_dispatcher->trigger_event('gallery.core.image.delete_images', compact($vars)));

		$sql = 'SELECT image_album_id, image_contest_rank
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE ' . $db->sql_in_set('image_id', $images) . '
			GROUP BY image_album_id, image_contest_rank';
		$result = $db->sql_query($sql);
		$resync_album_ids = $resync_contests = array();
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['image_contest_rank'])
			{
				$resync_contests[] = (int) $row['image_album_id'];
			}
			$resync_album_ids[] = (int) $row['image_album_id'];
		}
		$db->sql_freeresult($result);
		$resync_contests = array_unique($resync_contests);
		$resync_album_ids = array_unique($resync_album_ids);

		$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE ' . $db->sql_in_set('image_id', $images);
		$db->sql_query($sql);

		// The images need to be deleted, before we grab the new winners.
		phpbb_gallery_contest::resync_albums($resync_contests);
		if ($resync_albums)
		{
			foreach ($resync_album_ids as $album_id)
			{
				phpbb_gallery_album::update_info($album_id);
			}
		}

		return true;
	}

	/**
	* Get the real filenames, so we can load/delete/edit the image-file.
	*
	* @param	mixed		$images		Array or integer with the image_id(s)
	* @return	array		Format: $image_id => $filename
	*/
	static public function get_filenames($images)
	{
		if (empty($images))
		{
			return array();
		}

		global $db;

		$filenames = array();
		$sql = 'SELECT image_id, image_filename
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE ' . $db->sql_in_set('image_id', $images);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$filenames[(int) $row['image_id']] = $row['image_filename'];
		}
		$db->sql_freeresult($result);

		return $filenames;
	}

	/**
	* Assigns an image with all data to the defined template-block
	*
	* @param string	$template_block	Name of the template-block
	* @param array	$image_data		Array with the image-data, all columns of GALLERY_IMAGES_TABLE are needed. album_name may be additionally assigned
	*/
	static public function assign_block($template_block, &$image_data, $album_status, $display = 126, $album_user_id = -1)
	{
		global $auth, $template, $user, $phpbb_ext_gallery;

		static $lang_loaded;
		if (!$lang_loaded)
		{
			$user->add_lang_ext('gallery/core', 'gallery_mcp');
			$lang_loaded = true;
		}

		$st	= request_var('st', 0);
		$sk	= request_var('sk', $phpbb_ext_gallery->config->get('default_sort_key'));
		$sd	= request_var('sd', $phpbb_ext_gallery->config->get('default_sort_dir'));

		//@todo: $rating = new phpbb_gallery_image_rating($image_data['image_id'], $image_data, $image_data);
		$image_data['rating'] = '0';//@todo: $rating->get_image_rating(false, false);
		//@todo: unset($rating);

		$s_user_allowed = (($image_data['image_user_id'] == $user->data['user_id']) && ($album_status != phpbb_ext_gallery_core_album::STATUS_LOCKED));

		$s_allowed_delete = (($phpbb_ext_gallery->auth->acl_check('i_delete', $image_data['image_album_id'], $album_user_id) && $s_user_allowed) || $phpbb_ext_gallery->auth->acl_check('m_delete', $image_data['image_album_id'], $album_user_id));
		$s_allowed_edit = (($phpbb_ext_gallery->auth->acl_check('i_edit', $image_data['image_album_id'], $album_user_id) && $s_user_allowed) || $phpbb_ext_gallery->auth->acl_check('m_edit', $image_data['image_album_id'], $album_user_id));
		$s_quick_mod = ($s_allowed_delete || $s_allowed_edit || $phpbb_ext_gallery->auth->acl_check('m_status', $image_data['image_album_id'], $album_user_id) || $phpbb_ext_gallery->auth->acl_check('m_move', $image_data['image_album_id'], $album_user_id));

		$s_username_hidden = $image_data['image_contest'] && !$phpbb_ext_gallery->auth->acl_check('m_status', $image_data['image_album_id'], $album_user_id) && ($user->data['user_id'] != $image_data['image_user_id'] || $image_data['image_user_id'] == ANONYMOUS);

		$template->assign_block_vars($template_block, array(
			'IMAGE_ID'		=> $image_data['image_id'],
			'UC_IMAGE_NAME'	=> ($display & phpbb_ext_gallery_core_block::DISPLAY_IMAGENAME) ? self::generate_link('image_name', $phpbb_ext_gallery->config->get('link_image_name'), $image_data['image_id'], $image_data['image_name'], $image_data['image_album_id'], false, true, "&amp;sk={$sk}&amp;sd={$sd}&amp;st={$st}") : '',
			'UC_THUMBNAIL'	=> self::generate_link('thumbnail', $phpbb_ext_gallery->config->get('link_thumbnail'), $image_data['image_id'], $image_data['image_name'], $image_data['image_album_id']),
			'U_ALBUM'		=> ($display & phpbb_ext_gallery_core_block::DISPLAY_ALBUMNAME) ? $phpbb_ext_gallery->url->append_sid('album', 'album_id=' . $image_data['image_album_id']) : '',
			'S_UNAPPROVED'	=> ($phpbb_ext_gallery->auth->acl_check('m_status', $image_data['image_album_id'], $album_user_id) && ($image_data['image_status'] == self::STATUS_UNAPPROVED)) ? true : false,
			'S_LOCKED'		=> ($image_data['image_status'] == self::STATUS_LOCKED) ? true : false,
			'S_REPORTED'	=> ($phpbb_ext_gallery->auth->acl_check('m_report', $image_data['image_album_id'], $album_user_id) && $image_data['image_reported']) ? true : false,

			'ALBUM_NAME'		=> ($display & phpbb_ext_gallery_core_block::DISPLAY_ALBUMNAME) ? ((isset($image_data['album_name'])) ? ((utf8_strlen(htmlspecialchars_decode($image_data['album_name'])) > $phpbb_ext_gallery->config->get('shortnames') + 3) ? htmlspecialchars(utf8_substr(htmlspecialchars_decode($image_data['album_name']), 0, $phpbb_ext_gallery->config->get('shortnames')) . '...') : ($image_data['album_name'])) : '') : '',
			'ALBUM_NAME_FULL'	=> ($display & phpbb_ext_gallery_core_block::DISPLAY_ALBUMNAME) ? ((isset($image_data['album_name'])) ? $image_data['album_name'] : '') : '',
			'POSTER'		=> ($display & phpbb_ext_gallery_core_block::DISPLAY_USERNAME) ? (($s_username_hidden) ? $user->lang['CONTEST_USERNAME'] : get_username_string('full', $image_data['image_user_id'], $image_data['image_username'], $image_data['image_user_colour'])) : '',
			'TIME'			=> ($display & phpbb_ext_gallery_core_block::DISPLAY_IMAGETIME) ? $user->format_date($image_data['image_time']) : '',
			'VIEW'			=> ($display & phpbb_ext_gallery_core_block::DISPLAY_IMAGEVIEWS) ? $image_data['image_view_count'] : -1,
			'CONTEST_RANK'		=> ($image_data['image_contest_rank']) ? $user->lang['CONTEST_RESULT_' . $image_data['image_contest_rank']] : '',
			'CONTEST_RANK_ID'	=> $image_data['image_contest_rank'],

			'S_RATINGS'		=> (($display & phpbb_ext_gallery_core_block::DISPLAY_RATINGS) ? (($phpbb_ext_gallery->config->get('allow_rates') && $phpbb_ext_gallery->auth->acl_check('i_rate', $image_data['image_album_id'], $album_user_id)) ? $image_data['rating'] : '') : ''),
			'U_RATINGS'		=> $phpbb_ext_gallery->url->append_sid('image_page', 'album_id=' . $image_data['image_album_id'] . "&amp;image_id=" . $image_data['image_id']) . '#rating',
			'L_COMMENTS'	=> ($image_data['image_comments'] == 1) ? $user->lang['COMMENT'] : $user->lang['COMMENTS'],
			'S_COMMENTS'	=> (($display & phpbb_ext_gallery_core_block::DISPLAY_COMMENTS) ? (($phpbb_ext_gallery->config->get('allow_comments') && $phpbb_ext_gallery->auth->acl_check('c_read', $image_data['image_album_id'], $album_user_id)) ? (($image_data['image_comments']) ? $image_data['image_comments'] : $user->lang['NO_COMMENTS']) : '') : ''),
			'U_COMMENTS'	=> $phpbb_ext_gallery->url->append_sid('image_page', 'album_id=' . $image_data['image_album_id'] . "&amp;image_id=" . $image_data['image_id']) . '#comments',

			'S_MOD_ACTION'		=> $phpbb_ext_gallery->url->append_sid('mcp', "album_id={$image_data['image_album_id']}&amp;image_id={$image_data['image_id']}&amp;quickmod=1" /*&amp;redirect=" . urlencode(str_replace('&amp;', '&', $viewtopic_url))*/, true, $user->session_id),
			'S_QUICK_MOD'		=> $s_quick_mod,
			'S_QM_MOVE'			=> $phpbb_ext_gallery->auth->acl_check('m_move', $image_data['image_album_id'], $album_user_id),
			'S_QM_EDIT'			=> $s_allowed_edit,
			'S_QM_DELETE'		=> $s_allowed_delete,
			'S_QM_REPORT'		=> $phpbb_ext_gallery->auth->acl_check('m_report', $image_data['image_album_id'], $album_user_id),
			'S_QM_STATUS'		=> $phpbb_ext_gallery->auth->acl_check('m_status', $image_data['image_album_id'], $album_user_id),

			'S_IMAGE_REPORTED'		=> $image_data['image_reported'],
			'U_IMAGE_REPORTED'		=> ($image_data['image_reported']) ? $phpbb_ext_gallery->url->append_sid('mcp', "mode=report_details&amp;album_id={$image_data['image_album_id']}&amp;option_id=" . $image_data['image_reported']) : '',
			'S_STATUS_APPROVED'		=> ($image_data['image_status'] == self::STATUS_APPROVED),
			'S_STATUS_UNAPPROVED'	=> ($image_data['image_status'] == self::STATUS_UNAPPROVED),
			'S_STATUS_LOCKED'		=> ($image_data['image_status'] == self::STATUS_LOCKED),

			// Still needed for the classic design, if we don't drop it.
			'S_IP'		=> (($display & phpbb_ext_gallery_core_block::DISPLAY_IP) && $auth->acl_get('a_')) ? $image_data['image_user_ip'] : '',
			'U_WHOIS'	=> $phpbb_ext_gallery->url->append_sid('mcp', 'mode=whois&amp;ip=' . $image_data['image_user_ip']),
			'U_REPORT'	=> ($phpbb_ext_gallery->auth->acl_check('m_report', $image_data['image_album_id'], $album_user_id) && $image_data['image_reported']) ? $phpbb_ext_gallery->url->append_sid('mcp', "mode=report_details&amp;album_id={$image_data['image_album_id']}&amp;option_id=" . $image_data['image_reported']) : '',
			'U_STATUS'	=> ($phpbb_ext_gallery->auth->acl_check('m_status', $image_data['image_album_id'], $album_user_id)) ? $phpbb_ext_gallery->url->append_sid('mcp', "mode=queue_details&amp;album_id={$image_data['image_album_id']}&amp;option_id=" . $image_data['image_id']) : '',
			'L_STATUS'	=> ($image_data['image_status'] == self::STATUS_UNAPPROVED) ? $user->lang['APPROVE_IMAGE'] : (($image_data['image_status'] == self::STATUS_APPROVED) ? $user->lang['CHANGE_IMAGE_STATUS'] : $user->lang['UNLOCK_IMAGE']),
			'U_MOVE'	=> ($phpbb_ext_gallery->auth->acl_check('m_move', $image_data['image_album_id'], $album_user_id)) ? $phpbb_ext_gallery->url->append_sid('mcp', "action=images_move&amp;album_id={$image_data['image_album_id']}&amp;image_id=" . $image_data['image_id'] . "&amp;redirect=redirect") : '',
			'U_EDIT'	=> $s_allowed_edit ? $phpbb_ext_gallery->url->append_sid('posting', "mode=edit&amp;album_id={$image_data['image_album_id']}&amp;image_id=" . $image_data['image_id']) : '',
			'U_DELETE'	=> $s_allowed_delete ?$phpbb_ext_gallery->url->append_sid('posting', "mode=delete&amp;album_id={$image_data['image_album_id']}&amp;image_id=" . $image_data['image_id']) : '',
		));
	}

	/**
	* Generate link to image
	*
	* @param	string	$content	what's in the link: image_name, thumbnail, fake_thumbnail, medium or lastimage_icon
	* @param	string	$mode		where does the link leed to: highslide, lytebox, lytebox_slide_show, image_page, image, none
	* @param	int		$image_id
	* @param	string	$image_name
	* @param	int		$album_id
	* @param	bool	$is_gif		we need to know whether we display a gif, so we can use a better medium-image
	* @param	bool	$count		shall the image-link be counted as view? (Set to false from image_page.php to deny double increment)
	* @param	string	$additional_parameters		additional parameters for the url, (starting with &amp;)
	*/
	static public function generate_link($content, $mode, $image_id, $image_name, $album_id, $is_gif = false, $count = true, $additional_parameters = '', $next_image = 0)
	{
		global $user;
		global $phpbb_ext_gallery;//@todo: 

		$image_page_url = $phpbb_ext_gallery->url->append_sid('image_page', "album_id=$album_id&amp;image_id=$image_id{$additional_parameters}");
		$image_url = $phpbb_ext_gallery->url->append_sid('image', "album_id=$album_id&amp;image_id=$image_id{$additional_parameters}" . ((!$count) ? '&amp;view=no_count' : ''));
		$thumb_url = $phpbb_ext_gallery->url->append_sid('image', "mode=thumbnail&amp;album_id=$album_id&amp;image_id=$image_id{$additional_parameters}");
		$medium_url = $phpbb_ext_gallery->url->append_sid('image', "mode=medium&amp;album_id=$album_id&amp;image_id=$image_id{$additional_parameters}");
		switch ($content)
		{
			case 'image_name':
				$shorten_image_name = (utf8_strlen(htmlspecialchars_decode($image_name)) > $phpbb_ext_gallery->config->get('shortnames') + 3) ? (utf8_substr(htmlspecialchars_decode($image_name), 0, $phpbb_ext_gallery->config->get('shortnames')) . '...') : ($image_name);
				$content = '<span style="font-weight: bold;">' . $shorten_image_name . '</span>';
			break;
			case 'image_name_unbold':
				$shorten_image_name = (utf8_strlen(htmlspecialchars_decode($image_name)) > $phpbb_ext_gallery->config->get('shortnames') + 3) ? (utf8_substr(htmlspecialchars_decode($image_name), 0, $phpbb_ext_gallery->config->get('shortnames')) . '...') : ($image_name);
				$content = $shorten_image_name;
			break;
			case 'thumbnail':
				$content = '<img src="{U_THUMBNAIL}" alt="{IMAGE_NAME}" title="{IMAGE_NAME}" />';
				$content = str_replace(array('{U_THUMBNAIL}', '{IMAGE_NAME}'), array($thumb_url, $image_name), $content);
			break;
			case 'fake_thumbnail':
				$content = '<img src="{U_THUMBNAIL}" alt="{IMAGE_NAME}" title="{IMAGE_NAME}" style="max-width: {FAKE_THUMB_SIZE}px; max-height: {FAKE_THUMB_SIZE}px;" />';
				$content = str_replace(array('{U_THUMBNAIL}', '{IMAGE_NAME}', '{FAKE_THUMB_SIZE}'), array($thumb_url, $image_name, $phpbb_ext_gallery->config->get('mini_thumbnail_size')), $content);
			break;
			case 'medium':
				$content = '<img src="{U_MEDIUM}" alt="{IMAGE_NAME}" title="{IMAGE_NAME}" />';
				$content = str_replace(array('{U_MEDIUM}', '{IMAGE_NAME}'), array($medium_url, $image_name), $content);
				//cheat for animated/transparent gifs
				if ($is_gif)
				{
					$content = '<img src="{U_MEDIUM}" alt="{IMAGE_NAME}" title="{IMAGE_NAME}" style="max-width: {MEDIUM_WIDTH_SIZE}px; max-height: {MEDIUM_HEIGHT_SIZE}px;" />';
					$content = str_replace(array('{U_MEDIUM}', '{IMAGE_NAME}', '{MEDIUM_HEIGHT_SIZE}', '{MEDIUM_WIDTH_SIZE}'), array($image_url, $image_name, $phpbb_ext_gallery->config->get('medium_height'), $phpbb_ext_gallery->config->get('medium_width')), $content);
				}
			break;
			case 'lastimage_icon':
				$content = $user->img('icon_topic_latest', 'VIEW_LATEST_IMAGE');
			break;
		}

		$url = $image_page_url;

		switch ($mode)
		{
			case 'image_page':
				$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}">{CONTENT}</a>';
			break;
			case 'image_page_next':
				$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}" class="right-box right">{CONTENT}</a>';
			break;
			case 'image_page_prev':
				$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}" class="left-box left">{CONTENT}</a>';
			break;
			case 'image':
				$url = $image_url;
				$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}">{CONTENT}</a>';
			break;
			case 'none':
				$tpl = '{CONTENT}';
			break;
			case 'next':
				if ($next_image)
				{
					$url = $phpbb_ext_gallery->url->append_sid('image_page', "album_id=$album_id&amp;image_id=$next_image{$additional_parameters}");
					$tpl = '<a href="{IMAGE_URL}" title="{IMAGE_NAME}">{CONTENT}</a>';
				}
				else
				{
					$tpl = '{CONTENT}';
				}
			break;
			default:
				$url = $image_url;
				global $phpbb_dispatcher;


				$tpl = '{CONTENT}';

				$vars = array('mode', 'tpl');
				extract($phpbb_dispatcher->trigger_event('gallery.image.generate_link', compact($vars)));//@todo: Correctly identify the event
			break;
		}

		return str_replace(array('{IMAGE_URL}', '{IMAGE_NAME}', '{CONTENT}'), array($url, $image_name, $content), $tpl);
	}

	/**
	* Handle user- & total image_counter
	*
	* @param	array	$image_id_ary	array with the image_ids which changed their status
	* @param	bool	$add			are we adding or removing the images
	* @param	bool	$readd			is it possible that there are images which aren't really changed
	*/
	static public function handle_counter($image_id_ary, $add, $readd = false)
	{
		global $db, $phpbb_ext_gallery;

		if (empty($image_id_ary))
		{
			return;
		}

		$num_images = $num_comments = 0;
		$sql = 'SELECT SUM(image_comments) comments
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_status ' . (($readd) ? '=' : '<>') . ' ' . self::STATUS_UNAPPROVED . '
				AND ' . $db->sql_in_set('image_id', $image_id_ary) . '
			GROUP BY image_user_id';
		$result = $db->sql_query($sql);
		$num_comments = $db->sql_fetchfield('comments');
		$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(image_id) images, image_user_id
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_status ' . (($readd) ? '=' : '<>') . ' ' . self::STATUS_UNAPPROVED . '
				AND ' . $db->sql_in_set('image_id', $image_id_ary) . '
			GROUP BY image_user_id';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$sql_ary = array(
				'user_id'				=> (int) $row['image_user_id'],
				'user_images'			=> (int) $row['images'],
			);
			//@todo: phpbb_gallery_hookup::add_image($row['image_user_id'], (($add) ? $row['images'] : 0 - $row['images']));

			$num_images = $num_images + $row['images'];

			$image_user = new phpbb_ext_gallery_core_user($db, (int) $row['image_user_id'], false);
			$image_user->update_images((($add) ? $row['images'] : 0 - $row['images']));
		}
		$db->sql_freeresult($result);

		if ($add)
		{
			$phpbb_ext_gallery->config->inc('num_images', $num_images);
			$phpbb_ext_gallery->config->inc('num_comments', $num_comments);
		}
		else
		{
			$phpbb_ext_gallery->config->dec('num_images', $num_images);
			$phpbb_ext_gallery->config->dec('num_comments', $num_comments);
		}
	}
}
