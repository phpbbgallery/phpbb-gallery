<?php

/**
*
* @package phpBB Gallery
* @copyright (c) 2014 nickvergessen
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbgallery\core\acp;

class gallery_info
{
	function module()
	{
		return array(
			'filename'	=> '\phpbbgallery\core\acp\gallery_module',
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'overview'			=> array('title' => 'ACP_GALLERY_OVERVIEW',				'auth' => 'ext_phpbbgallery/core && acl_a_gallery_manage',	'cat' => array('PHPBB_GALLERY')),
				'import_images'		=> array('title' => 'ACP_IMPORT_ALBUMS',				'auth' => 'ext_phpbbgallery/core && acl_a_gallery_import',	'cat' => array('PHPBB_GALLERY')),
				'cleanup'			=> array('title' => 'ACP_GALLERY_CLEANUP',				'auth' => 'ext_phpbbgallery/core && acl_a_gallery_cleanup',	'cat' => array('PHPBB_GALLERY')),
				),
			);
	}
}
?>