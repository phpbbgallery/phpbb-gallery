<?php
/**
*
* @package phpBB3
* @version $Id: acp_gallery.php 256 2008-01-25 18:52:19Z nickvergessen $
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
*/
class ucp_gallery_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_gallery',
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '0.3.2',
			'modes'		=> array(
					'manage_albums'			=> array('title' => 'UCP_GALLERY_PERSONAL_ALBUMS', 'auth' => '', 'cat' => array('PHPBB_GALLERY')),
					'manage_settings'		=> array('title' => 'UCP_GALLERY_SETTINGS', 'auth' => '', 'cat' => array('PHPBB_GALLERY')),
					'manage_subscriptions'	=> array('title' => 'UCP_GALLERY_WATCH', 'auth' => '', 'cat' => array('PHPBB_GALLERY')),
					'manage_favorites'		=> array('title' => 'UCP_GALLERY_FAVORITES', 'auth' => '', 'cat' => array('PHPBB_GALLERY')),
				),
			);
	}
}
?>