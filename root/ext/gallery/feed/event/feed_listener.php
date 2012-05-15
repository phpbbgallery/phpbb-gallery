<?php
/**
*
* @package Gallery - Feed Extension
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

class phpbb_ext_gallery_feed_event_feed_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'gallery.core.acp.albums.default_data'			=> 'acp_albums_default_data',
			'gallery.core.acp.albums.request_data'			=> 'acp_albums_request_data',
			'gallery.core.acp.albums.send_to_template'		=> 'acp_albums_send_to_template',
			'gallery.core.acp.config.get_display_vars'		=> 'acp_config_get_display_vars',
			'gallery.core.config.load_config_sets'			=> 'config_load_config_sets',
			'gallery.core.setup'							=> 'core_setup',
		);
	}

	public function acp_albums_default_data($event)
	{
		$album_data = $event['album_data'];
		$album_data['album_feed'] = true;
		$event['album_data'] = $album_data;
	}

	public function acp_albums_request_data($event)
	{
		$album_data = $event['album_data'];
		$album_data['album_feed'] = request_var('album_feed', false);
		$event['album_data'] = $album_data;
	}

	public function acp_albums_send_to_template($event)
	{
		global $template, $user;
		$user->add_lang_ext('gallery/feed', 'feed');

		$template->assign_vars(array(
			'S_FEED_ENABLED'			=> ($event['album_data']['album_feed']) ? true : false,
		));
	}

	public function acp_config_get_display_vars($event)
	{
		if ($event['mode'] == 'main')
		{
			$return_ary = $event['return_ary'];
			if (!isset($return_ary['vars']['FEED_SETTINGS']))
			{
				global $user;
				$user->add_lang_ext('gallery/feed', 'feed');

				$return_ary['vars']['FEED_SETTINGS'] = array(
					'feed_enable'			=> array('lang' => 'FEED_ENABLED',			'validate' => 'bool',	'type' => 'radio:yes_no'),
					'feed_enable_pegas'		=> array('lang' => 'FEED_ENABLED_PEGAS',	'validate' => 'bool',	'type' => 'radio:yes_no'),
					'feed_limit'			=> array('lang' => 'FEED_LIMIT',			'validate' => 'int',	'type' => 'text:7:3'),
				);
				$event['return_ary'] = $return_ary;
			}
		}
	}

	public function config_load_config_sets($event)
	{
		$additional_config_sets = $event['additional_config_sets'];
		$additional_config_sets['feed'] = 'phpbb_ext_gallery_feed_config_sets_feed';
		$event['additional_config_sets'] = $additional_config_sets;
	}

	public function core_setup($event)
	{
		global $config, $template, $phpbb_ext_gallery;

		/**
		* Maybe we need this for the feed
		if (!empty($config['feed_http_auth']) && request_var('auth', '') == 'http')
		{
			phpbb_http_login(array(
				'auth_message'	=> 'Feed',
				'viewonline'	=> request_var('viewonline', true),
			));
		}
		*/

		// Okay, this is not the best way, but we disable the phpbb feeds and display the ones of the gallery.
		$config['feed_overall'] = $config['feed_overall_forums'] = $config['feed_topics_new'] = $config['feed_topics_active'] = false;

		$template->assign_vars(array(
			'S_GALLERY_FEEDS'				=> $phpbb_ext_gallery->config->get('feed_enable'),
			'U_GALLERY_FEED'				=> $phpbb_ext_gallery->url->append_sid('feed'),
		));
	}
}
