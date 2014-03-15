<?php

/**
*
* @package phpBB Gallery
* @copyright (c) 2014 nickvergessen
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbgallery\core\acp;

class permissions_info
{
	function module()
	{
		return array(
			'filename'	=> '\phpbbgallery\core\acp\permissions_module',
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'manage'	=> array('title' => 'ACP_GALLERY_ALBUM_PERMISSIONS',		'auth' => 'ext_phpbbgallery/core && acl_a_gallery_albums',	'cat' => array('PHPBB_GALLERY')),
				'copy'		=> array('title' => 'ACP_GALLERY_ALBUM_PERMISSIONS_COPY',	'auth' => 'ext_phpbbgallery/core && acl_a_gallery_albums',	'cat' => array('PHPBB_GALLERY')),
			),
		);
	}
}
?>