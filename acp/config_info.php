<?php

/**
*
* @package phpBB Gallery
* @copyright (c) 2014 nickvergessen
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbgallery\core\acp;

class config_info
{
	function module()
	{
		return array(
			'filename'	=> '\phpbbgallery\core\acp\config_module',
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'			=> array('title' => 'ACP_GALLERY_CONFIGURE_GALLERY', 'auth' => 'ext_phpbbgallery/core && acl_a_gallery_manage', 'cat' => array('PHPBB_GALLERY')),
			),
		);
	}
}

?>