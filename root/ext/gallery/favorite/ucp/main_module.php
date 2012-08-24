<?php
/**
*
* @package Gallery - Favorite Extension
* @copyright (c) 2012 nickvergessen - http://www.flying-bits.org/
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
* @package ucp
*/
class phpbb_ext_gallery_favorite_ucp_main_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $auth, $cache, $config, $db, $template, $user, $phpEx, $phpbb_root_path, $phpbb_ext_gallery;

		$phpbb_ext_gallery = new phpbb_ext_gallery_core($auth, $cache, $config, $db, $template, $user, $phpEx, $phpbb_root_path);
		$phpbb_ext_gallery->init();
		$phpbb_ext_gallery->url->_include('functions_display', 'phpbb');

		$user->add_lang_ext('gallery/core', array('gallery', 'gallery_acp', 'gallery_mcp', 'gallery_ucp'));
		$this->tpl_name = 'gallery/ucp_gallery_favorite';
		add_form_key('ucp_gallery');

		$this->page_title = $user->lang['UCP_GALLERY_FAVORITES'];
		$this->manage_favorites();
	}

	function manage_favorites()
	{
		global $db, $template, $user, $phpbb_ext_gallery;

		$action = request_var('action', '');
		$image_id_ary = request_var('image_id_ary', array(0));
		if ($image_id_ary && ($action == 'remove_favorite'))
		{
			phpbb_gallery_image_favorite::remove($image_id_ary);

			meta_refresh(3, $this->u_action);
			trigger_error($user->lang['UNFAVORITED_IMAGES'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>'));
		}

		$start				= request_var('start', 0);
		$images_per_page	= $phpbb_ext_gallery->config->get('album_rows') * $phpbb_ext_gallery->config->get('album_columns');
		$total_images		= 0;

		$sql = 'SELECT COUNT(image_id) as images
			FROM ' . GALLERY_FAVORITES_TABLE . '
			WHERE user_id = ' . $user->data['user_id'];
		$result = $db->sql_query($sql);
		$total_images = (int) $db->sql_fetchfield('images');
		$db->sql_freeresult($result);

		$sql_array = array(
			'SELECT'		=> 'f.*, i.*, a.album_name',
			'FROM'			=> array(GALLERY_FAVORITES_TABLE => 'f'),

			'LEFT_JOIN'		=> array(
				array(
					'FROM'		=> array(GALLERY_IMAGES_TABLE => 'i'),
					'ON'		=> 'f.image_id = i.image_id',
				),
				array(
					'FROM'		=> array(GALLERY_ALBUMS_TABLE => 'a'),
					'ON'		=> 'a.album_id = i.image_album_id',
				),
			),

			'WHERE'			=> 'f.user_id = ' . $user->data['user_id'],
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query_limit($sql, $images_per_page, $start);
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('image_row', array(
				'UC_IMAGE_NAME'		=> phpbb_ext_gallery_core_image::generate_link('image_name', $phpbb_ext_gallery->config->get('link_image_name'), $row['image_id'], $row['image_name'], $row['image_album_id']),
				'UC_FAKE_THUMBNAIL'	=> phpbb_ext_gallery_core_image::generate_link('fake_thumbnail', $phpbb_ext_gallery->config->get('link_thumbnail'), $row['image_id'], $row['image_name'], $row['image_album_id']),
				'UPLOADER'			=> ($row['image_contest'] && !$phpbb_ext_gallery->auth->acl_check('m_status', $row['image_album_id'])) ? $user->lang['CONTEST_USERNAME'] : get_username_string('full', $row['image_user_id'], $row['image_username'], $row['image_user_colour']),
				'IMAGE_TIME'		=> $user->format_date($row['image_time']),
				'ALBUM_NAME'		=> $row['album_name'],
				'IMAGE_ID'			=> $row['image_id'],
				'U_VIEW_ALBUM'		=> $phpbb_ext_gallery->url->append_sid('album', 'album_id=' . $row['image_album_id']),
				'U_IMAGE'			=> $phpbb_ext_gallery->url->append_sid('image_page', 'album_id=' . $row['image_album_id'] . '&amp;image_id=' . $row['image_id']),
			));
		}
		$db->sql_freeresult($result);

		phpbb_generate_template_pagination($template, $phpbb_ext_gallery->url->append_sid('phpbb', 'ucp', 'i=gallery&amp;mode=manage_favorites'), 'pagination', 'start', $total_images, $images_per_page, $start);

		$template->assign_vars(array(
			'S_MANAGE_FAVORITES'	=> true,
			'S_UCP_ACTION'			=> $this->u_action,

			'L_TITLE'				=> $user->lang['UCP_GALLERY_FAVORITES'],
			'L_TITLE_EXPLAIN'		=> $user->lang['YOUR_FAVORITE_IMAGES'],

			'PAGE_NUMBER'				=> phpbb_on_page($template, $user, $phpbb_ext_gallery->url->append_sid('phpbb', 'ucp', 'i=gallery&amp;mode=manage_favorites'), $total_images, $images_per_page, $start),
			'TOTAL_IMAGES'				=> $user->lang('VIEW_ALBUM_IMAGES', $total_images),

			'DISP_FAKE_THUMB'			=> true,
			'FAKE_THUMB_SIZE'			=> $phpbb_ext_gallery->config->get('mini_thumbnail_size'),
		));
	}
}
