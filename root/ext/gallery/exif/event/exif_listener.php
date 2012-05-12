<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class phpbb_ext_gallery_exif_event_exif_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'gallery.core.config.load_config_sets'			=> 'load_config_sets',
			'gallery.core.massimport.update_image_before'	=> 'massimport_update_image_before',
			'gallery.core.massimport.update_image'			=> 'massimport_update_image',
			'gallery.core.posting.edit_before_rotate'		=> 'posting_edit_before_rotate',
			'gallery.core.ucp.set_settings_submit'			=> 'ucp_set_settings_submit',
			'gallery.core.ucp.set_settings_nosubmit'		=> 'ucp_set_settings_nosubmit',
			'gallery.core.upload.prepare_file_before'		=> 'upload_prepare_file_before',
			'gallery.core.upload.update_image_before'		=> 'upload_update_image_before',
			'gallery.core.upload.update_image_nofilechange'	=> 'upload_update_image_nofilechange',
			'gallery.core.user.get_default_values'			=> 'user_get_default_values',
			'gallery.core.user.validate_data'				=> 'user_validate_data',
			'gallery.core.viewimage'						=> 'viewimage',
		);
	}

	public function load_config_sets($event)
	{
		$additional_config_sets = $event['additional_config_sets'];
		$additional_config_sets['exif'] = 'phpbb_ext_gallery_exif_config_sets_exif';
		$event['additional_config_sets'] = $additional_config_sets;
	}

	public function massimport_update_image_before($event)
	{
		$additional_sql_data = $event['additional_sql_data'];

		// Read exif data from file
		$exif = new phpbb_ext_gallery_exif($event['file_link']);
		$exif->read();
		$additional_sql_data['image_exif_data'] = $exif->serialized;
		$additional_sql_data['image_has_exif'] = $exif->status;

		$event['additional_sql_data'] = $additional_sql_data;
		unset($exif);
	}

	public function massimport_update_image($event)
	{
		if (!$event['file_updated'])
		{
			$additional_sql_data = $event['additional_sql_data'];

			$additional_sql_data['image_exif_data'] = '';
			$additional_sql_data['image_has_exif'] = phpbb_ext_gallery_exif::UNKNOWN;

			$event['additional_sql_data'] = $additional_sql_data;
		}
	}

	public function posting_edit_before_rotate($event)
	{
		$image_data = $event['image_data'];

		if (($image_data['image_has_exif'] == phpbb_ext_gallery_exif::AVAILABLE) ||
		 ($image_data['image_has_exif'] == phpbb_ext_gallery_exif::UNKNOWN))
		{
			$additional_sql_data = $event['additional_sql_data'];

			// Read exif data from file
			$exif = new phpbb_ext_gallery_exif($event['file_link']);
			$exif->read();
			$additional_sql_data['image_exif_data'] = $exif->serialized;
			$additional_sql_data['image_has_exif'] = $exif->status;

			$event['additional_sql_data'] = $additional_sql_data;
			unset($exif);
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

	public function upload_prepare_file_before($event)
	{
		if (in_array($event['file']->extension, array('jpg', 'jpeg')))
		{
			$additional_sql_data = $event['additional_sql_data'];

			// Read exif data from file
			$exif = new phpbb_ext_gallery_exif($event['file']->destination_file);
			$exif->read();
			$additional_sql_data['image_exif_data'] = $exif->serialized;
			$additional_sql_data['image_has_exif'] = $exif->status;

			$event['additional_sql_data'] = $additional_sql_data;
			unset($exif);
		}
	}

	public function upload_update_image_before($event)
	{
		$image_data = $event['image_data'];

		if (($image_data['image_has_exif'] == phpbb_ext_gallery_exif::AVAILABLE) ||
		 ($image_data['image_has_exif'] == phpbb_ext_gallery_exif::UNKNOWN))
		{
			$additional_sql_data = $event['additional_sql_data'];

			// Read exif data from file
			$exif = new phpbb_ext_gallery_exif($event['file_link']);
			$exif->read();
			$additional_sql_data['image_exif_data'] = $exif->serialized;
			$additional_sql_data['image_has_exif'] = $exif->status;

			$event['additional_sql_data'] = $additional_sql_data;
			unset($exif);
		}
	}

	public function upload_update_image_nofilechange($event)
	{
		$additional_sql_data = $event['additional_sql_data'];

		$additional_sql_data['image_exif_data'] = '';
		$additional_sql_data['image_has_exif'] = phpbb_ext_gallery_exif::UNKNOWN;

		$event['additional_sql_data'] = $additional_sql_data;
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

	public function ucp_set_settings_submit($event)
	{
		$additional_settings = $event['additional_settings'];
		if (!in_array('user_viewexif', $additional_settings))
		{
			$additional_settings['user_viewexif'] = request_var('viewexifs', false);
			$event['additional_settings'] = $additional_settings;
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

	public function viewimage($event)
	{
		global $user;
		$user->add_lang_ext('gallery/exif', 'exif');

		if ($event['phpbb_ext_gallery']->config->get(array('exif', 'disp_exifdata')) &&
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
}
