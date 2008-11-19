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
			'version'	=> '1.0.0',
			'modes'		=> array(
				'overview'						=> array('title' => 'ACP_GALLERY_OVERVIEW',						'auth' => 'acl_a_gallery_manage', 'cat' => array('PHPBB_GALLERY')),
				'manage_albums'					=> array('title' => 'ACP_GALLERY_MANAGE_ALBUMS',				'auth' => 'acl_a_gallery_albums', 'cat' => array('PHPBB_GALLERY')),
				'configure_gallery'				=> array('title' => 'ACP_GALLERY_CONFIGURE_GALLERY',			'auth' => 'acl_a_gallery_manage', 'cat' => array('PHPBB_GALLERY')),
				'album_permissions'				=> array('title' => 'ACP_GALLERY_ALBUM_PERMISSIONS',			'auth' => 'acl_a_gallery_albums', 'cat' => array('PHPBB_GALLERY')),
				'import_images'					=> array('title' => 'ACP_IMPORT_ALBUMS',						'auth' => 'acl_a_gallery_import', 'cat' => array('PHPBB_GALLERY')),
				'cleanup'						=> array('title' => 'ACP_GALLERY_CLEANUP',						'auth' => 'acl_a_gallery_cleanup', 'cat' => array('PHPBB_GALLERY')),
				),
			);
	}
}
?>