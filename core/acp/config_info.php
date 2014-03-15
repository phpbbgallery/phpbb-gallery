<?php
/**
*
* @package Gallery - Config ACP Module
* @copyright (c) 2012 nickvergessen - http://www.flying-bits.org/
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_ext_gallery_core_acp_config_info
{
	function module()
	{
		return array(
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'config_sample'	=> array(
					'title'		=> 'ACP_GALLERY_CONFIGURE_GALLERY',
					'auth'		=> 'acl_a_gallery_manage',
					'cat'		=> array('PHPBB_GALLERY'),
				),
			),
		);
	}
}
