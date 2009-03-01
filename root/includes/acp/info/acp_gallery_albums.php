<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
*/
class acp_gallery_albums_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_gallery_albums',
			'title'		=> 'ACP_ALBUM_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'manage'	=> array('title' => 'ACP_GALLERY_MANAGE_ALBUMS', 'auth' => 'acl_a_gallery_albums', 'cat' => array('PHPBB_GALLERY')),
			),
		);
	}
}

?>