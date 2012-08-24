<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package module_install
*/
class phpbb_ext_gallery_core_ucp_main_info
{
	function module()
	{
		return array(
			'filename'	=> 'main_module',
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'manage_albums'			=> array('title' => 'UCP_GALLERY_PERSONAL_ALBUMS', 'auth' => '', 'cat' => array('PHPBB_GALLERY')),
				'manage_settings'		=> array('title' => 'UCP_GALLERY_SETTINGS', 'auth' => '', 'cat' => array('PHPBB_GALLERY')),
				'manage_subscriptions'	=> array('title' => 'UCP_GALLERY_WATCH', 'auth' => '', 'cat' => array('PHPBB_GALLERY')),
			),
		);
	}
}
?>