<?php

/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
class acp_gallery_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_gallery',
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '0.0.1',
			'modes'		=> array(
				'overview'		=> array('title' => 'ACP_GALLERY_OVERVIEW', 	'auth' => '', 'cat' => array('PHPBB_GALLERY')),
				'manage_albums'		=> array('title' => 'ACP_GALLERY_MANAGE_ALBUMS', 	'auth' => '', 'cat' => array('PHPBB_GALLERY')),
				'manage_cache'		=> array('title' => 'ACP_GALLERY_MANAGE_CACHE', 	'auth' => '', 'cat' => array('PHPBB_GALLERY')),
				'configure_gallery'	=> array('title' => 'ACP_GALLERY_CONFIGURE_GALLERY', 'auth' => '', 'cat' => array('PHPBB_GALLERY')),
				'album_permissions'	=> array('title' => 'ACP_GALLERY_ALBUM_PERMISSIONS', 'auth' => '', 'cat' => array('PHPBB_GALLERY')),
				)
			);
	}
							
	function install()
	{
	}
								
	function uninstall()
	{
	}
}
?>