<?php
/**
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package phpbb_gallery
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_gallery_version
{
	function version()
	{
		return array(
			'author'	=> 'nickvergessen',
			'title'		=> 'phpBB Gallery',
			'tag'		=> 'phpbb_gallery',
			'version'	=> '0.4.0',
			'file'		=> array('www.flying-bits.org', 'updatecheck', 'phpbb_gallery.xml'),
		);
	}
}

?>