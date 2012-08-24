<?php
/**
*
* @package Gallery - Favorite Extension
* @copyright (c) 2012 nickvergessen - http://www.flying-bits.org/
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
* Event listener
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class phpbb_ext_gallery_favorite_event_favorite_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'gallery.core.acp.main.cleanup_finished'			=> 'acp_cleanup_finished',
			'gallery.core.album.manage.delete_album_content'	=> 'album_manage_delete_album_content',
			'gallery.core.album.manage.move_album_content'		=> 'album_manage_move_album_content',
			'gallery.core.image.delete_images'					=> 'image_delete_images',
			'gallery.core.image.get_data'						=> 'image_get_data',
			'gallery.core.ucp.delete_album'						=> 'ucp_delete_album',
			'gallery.core.ucp.set_settings_submit'				=> 'ucp_set_settings_submit',
			'gallery.core.ucp.set_settings_nosubmit'			=> 'ucp_set_settings_nosubmit',
			'gallery.core.user.get_default_values'				=> 'user_get_default_values',
			'gallery.core.user.validate_data'					=> 'user_validate_data',
			'gallery.core.viewimage'							=> 'viewimage',
		);
	}

	public function acp_cleanup_finished()
	{
		$this->purge_cache();
	}

	public function album_manage_delete_album_content($event)
	{
		$this->purge_cache();
	}

	public function album_manage_move_album_content($event)
	{
		$this->purge_cache();
	}

	public function image_delete_images($event)
	{
		phpbb_ext_gallery_favorite::delete_favorites($event['images']);
		$this->purge_cache();
	}

	public function image_get_data($event)
	{
		if ($event['extended_info'])
		{
			global $user;

			$sql_array = $event['sql_array'];
			$sql_array['SELECT'] .= ', f.*';
			$sql_array['LEFT_JOIN'][] = array(
				'FROM'		=> array(GALLERY_FAVORITES_TABLE => 'f'),
				'ON'		=> 'i.image_id = f.image_id AND f.user_id = ' . (int) $user->data['user_id'],
			);
			$event['sql_array'] = $sql_array;
		}
	}

	public function ucp_delete_album($event)
	{
		$this->purge_cache();
	}

	public function ucp_set_settings_nosubmit()
	{
		global $template, $user, $phpbb_ext_gallery;
		$user->add_lang_ext('gallery/favorite', 'favorite');

		$template->assign_vars(array(
			'S_WATCH_FAVORITES'		=> $phpbb_ext_gallery->user->get_data('watch_favo'),
		));
	}

	public function ucp_set_settings_submit($event)
	{
		$additional_settings = $event['additional_settings'];
		if (!in_array('watch_favos', $additional_settings))
		{
			$additional_settings['watch_favos'] = request_var('watch_favorites', false);
			$event['additional_settings'] = $additional_settings;
		}
	}

	public function user_get_default_values($event)
	{
		$default_values = $event['default_values'];
		if (!in_array('watch_favos', $default_values))
		{
			$default_values['watch_favos'] = (bool) phpbb_ext_gallery_favorite::DEFAULT_SUBSCRIBE;
			$event['default_values'] = $default_values;
		}
	}

	public function user_validate_data($event)
	{
		if ($event['name'] == 'watch_favos')
		{
			$event['value'] = (bool) $event['value'];
			$event['is_validated'] = true;
		}
	}

	public function viewimage($event)
	{
		global $user, $template, $phpbb_ext_gallery;

		$user->add_lang_ext('gallery/favorite', 'favorite');

		$favorite_mode = (($event['image_data']['favorite_id']) ?  'un' : '') . 'favorite';
		$favorite_mode_toggle = ((!$event['image_data']['favorite_id']) ?  'un' : '') . 'favorite';
		$template->assign_vars(array(
			'S_FAVORITE_NAME'			=> ($event['image_data']['favorite_id']) ? $user->lang['UNFAVORITE_IMAGE'] : $user->lang['FAVORITE_IMAGE'],
			'S_FAVORITE_NAME_TOGGLE'	=> (!$event['image_data']['favorite_id']) ? $user->lang['UNFAVORITE_IMAGE'] : $user->lang['FAVORITE_IMAGE'],
			'U_FAVORITE_IMAGE'			=> ($user->data['user_id'] != ANONYMOUS) ? $phpbb_ext_gallery->url->append_sid('image_page', "mode=$favorite_mode&amp;album_id={$event['image_data']['image_album_id']}&amp;image_id={$event['image_id']}&amp;hash=" . generate_link_hash("{$favorite_mode}_{$event['image_id']}")) : '',
			'U_FAVORITE_IMAGE_TOGGLE'	=> ($user->data['user_id'] != ANONYMOUS) ? $phpbb_ext_gallery->url->append_sid('image_page', "mode=$favorite_mode_toggle&amp;album_id={$event['image_data']['image_album_id']}&amp;image_id={$event['image_id']}&amp;hash=" . generate_link_hash("{$favorite_mode_toggle}_{$event['image_id']}")) : '',
		));
	}

	private function purge_cache()
	{
		global $cache;
		$cache->destroy('sql', GALLERY_FAVORITES_TABLE);
	}
}
