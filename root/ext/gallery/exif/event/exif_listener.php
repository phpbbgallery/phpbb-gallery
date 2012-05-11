<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class phpbb_ext_gallery_exif_event_exif_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'gallery.core.viewimage'				=> 'viewimage',
			'gallery.core.user.get_default_values'	=> 'user_get_default_values',
			'gallery.core.user.validate_data'		=> 'user_validate_data',
			'gallery.core.ucp.set_settings_submit'	=> 'ucp_set_settings_submit',
			'gallery.core.ucp.set_settings_nosubmit'=> 'ucp_set_settings_nosubmit',
		);
	}

	public function viewimage($event)
	{
		global $user;
		$user->add_lang_ext('gallery/exif', 'exif');

		if ($event['phpbb_ext_gallery']->config->get('disp_exifdata') &&
		 ($event['image_data']['image_has_exif'] != phpbb_ext_gallery_exif::UNAVAILABLE) &&
		 (substr($event['image_data']['image_filename'], -4) == '.jpg') &&
		 function_exists('exif_read_data') &&
		 ($event['phpbb_ext_gallery']->auth->acl_check('m_status', $event['image_data']['image_album_id'], $event['album_data']['album_user_id']) ||
		  ($event['image_data']['image_contest'] != phpbb_ext_gallery_core_image::IN_CONTEST)))
		{
			$exif = new phpbb_ext_gallery_exif($event['phpbb_ext_gallery']->url->path('upload') . $event['image_data']['image_filename'], $event['image_id']);
			$exif->interpret($event['image_data']['image_has_exif'], $event['image_data']['image_exif_data']);

			if (!empty($exif->data["EXIF"]))
			{
				$exif->send_to_template($event['phpbb_ext_gallery']->user->get_data('user_viewexif'));
			}
			unset($exif);
		}
	}

	public function user_get_default_values($event)
	{
		$default_values = $event['default_values'];
		if (!in_array('user_viewexif', $default_values))
		{
			// Shall the EXIF data be viewed or collapsed by default?
			$default_values['user_viewexif'] = (bool) phpbb_ext_gallery_exif::DEFAULT_DISPLAY;
			$event['default_values'] = $default_values;
		}
	}

	public function user_validate_data($event)
	{
		if ($event['name'] == 'user_viewexif')
		{
			// Shall the EXIF data be viewed or collapsed by default?
			$event['value'] = (bool) $event['value'];
			$event['is_validated'] = true;
		}
	}

	public function ucp_set_settings_submit($event)
	{
		$additional_settings = $event['additional_settings'];
		if (!in_array('user_viewexif', $additional_settings))
		{
			$additional_settings['user_viewexif'] = request_var('viewexifs', false);
			$event['additional_settings'] = $additional_settings;
		}
	}

	public function ucp_set_settings_nosubmit()
	{
		global $template, $user, $phpbb_ext_gallery;
		$user->add_lang_ext('gallery/exif', 'exif');

		$template->assign_vars(array(
			'S_VIEWEXIFS'		=> $phpbb_ext_gallery->user->get_data('user_viewexif'),
		));
	}
}
