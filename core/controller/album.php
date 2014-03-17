<?php

/**
*
* @package phpBB Gallery Core
* @copyright (c) 2014 nickvergessen
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbgallery\core\controller;

class album
{
	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbbgallery\core\album\display */
	protected $display;

	/* @var \phpbbgallery\core\album\loader */
	protected $loader;

	/* @var \phpbbgallery\core\auth\auth */
	protected $auth;

	/* @var \phpbbgallery\core\auth\level */
	protected $auth_level;

	/**
	* Constructor
	*
	* @param \phpbb\config\config		$config		Config object
	* @param \phpbb\controller\helper	$helper		Controller helper object
	* @param \phpbb\template\template	$template	Template object
	* @param \phpbb\user				$user		User object
	* @param \phpbbgallery\core\album\display	$display	Albums display object
	* @param \phpbbgallery\core\album\loader	$loader	Albums display object
	* @param \phpbbgallery\core\auth\auth	$auth	Gallery auth object
	* @param \phpbbgallery\core\auth\level	$auth_level	Gallery auth level object
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, \phpbbgallery\core\album\display $display, \phpbbgallery\core\album\loader $loader, \phpbbgallery\core\auth\auth $auth, \phpbbgallery\core\auth\level $auth_level)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->display = $display;
		$this->loader = $loader;
		$this->auth = $auth;
		$this->auth_level = $auth_level;
	}

	/**
	* Album Controller
	*	Route: gallery/album/{album_id}
	*
	* @param int	$album_id	Root Album ID
	* @return Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function base($album_id, $page = 0)
	{
		$this->user->add_lang_ext('phpbbgallery/core', array('gallery'));

		try
		{
			$this->loader->load($album_id);
		}
		catch (\Exception $e)
		{
			return $this->error($e->getMessage(), 404);
		}

		$album_data = $this->loader->get($album_id);

//		if ($album_data['contest_id'] && $album_data['contest_marked'] && (($album_data['contest_start'] + $album_data['contest_end']) < time()))
//		{
//			$contest_end_time = $album_data['contest_start'] + $album_data['contest_end'];
//			phpbb_gallery_contest::end($album_id, $album_data['contest_id'], $contest_end_time);
//
//			$album_data['contest_marked'] = phpbb_ext_gallery_core_image::NO_CONTEST;
//		}

		$this->check_permissions($album_id, $album_data['album_user_id']);
		$this->auth_level->display('album', $album_id, $album_data['album_status'], $album_data['album_user_id']);

		$this->display->generate_navigation($album_data);
		$this->display->display_albums($album_data, $this->config['load_moderators']);

		$page_title = $album_data['album_name'];
		if ($page > 1)
		{
			$page_title .= ' - ' . $this->user->lang('PAGE_TITLE_NUMBER', $page);
		}

		if ($this->config['load_moderators'])
		{
			$moderators = $this->display->get_moderators($album_id);
			if (!empty($moderators[$album_id]))
			{
				$moderators = $moderators[$album_id];
				$l_moderator = (sizeof($moderators) == 1) ? $this->user->lang('MODERATOR') : $this->user->lang('MODERATORS');
				$this->template->assign_vars(array(
					'L_MODERATORS'	=> $l_moderator,
					'MODERATORS'	=> implode($this->user->lang('COMMA_SEPARATOR'), $moderators),
				));
			}
		}

		if ($this->auth->acl_check('m_', $album_id, $album_data['album_user_id']))
		{
			$this->template->assign_var('U_MCP', $this->helper->route(
				'phpbbgallery_moderate_album',
				array('album_id' => $album_id)
			));
		}

		if ((!$album_data['album_user_id'] || $album_data['album_user_id'] == $this->user->data['user_id'])
			&& ($this->user->data['user_id'] == ANONYMOUS || $this->auth->acl_check('i_upload', $album_id, $album_data['album_user_id'])))
		{
			$this->template->assign_var('U_UPLOAD_IMAGE', $this->helper->route(
				'phpbbgallery_album_upload',
				array('album_id' => $album_id)
			));
		}

		$this->template->assign_vars(array(
			'S_IS_POSTABLE'		=> $album_data['album_type'] != \phpbbgallery\core\album\album::TYPE_CAT,
			'S_IS_LOCKED'		=> $album_data['album_status'] == \phpbbgallery\core\album\album::STATUS_LOCKED,

			'U_RETURN_LINK'		=> $this->helper->route('phpbbgallery_index'),
			'L_RETURN_LINK'		=> $this->user->lang('RETURN_TO_GALLERY'),
		));

		return $this->helper->render('gallery/album_body.html', $page_title);
	}

	/**
	 * @param	int		$album_id
	 * @param	array	$album_data
	 */
	protected function check_permissions($album_id, $owner_id)
	{
		if (!$this->auth->acl_check('i_view', $album_id, $owner_id))
		{
			if ($this->user->data['is_bot'])
			{
				// Redirect bots back to the index
				redirect($this->helper->route('phpbbgallery_index'));
			}

			// Display login box for guests and an error for users
			if (!$this->user->data['is_registered'])
			{
				login_box();
			}
			else
			{
				return $this->error('NOT_AUTHORISED', 403);
			}
		}
	}

	protected function error($message, $status = 200, $title = '')
	{
		$title = $title ?: 'INFORMATION';

		$this->template->assign_vars(array(
			'MESSAGE_TITLE'		=> $this->user->lang($title),
			'MESSAGE_TEXT'		=> $message,
		));

		return $this->helper->render('message_body.html', $this->user->lang($title), $status);
	}
}
