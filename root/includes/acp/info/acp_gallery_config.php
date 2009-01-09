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
class acp_gallery_config_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_gallery_config',
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'			=> array('title' => 'ACP_GALLERY_CONFIGURE_GALLERY', 'auth' => 'acl_a_gallery_manage', 'cat' => array('PHPBB_GALLERY')),
			),
		);
	}
}

?>