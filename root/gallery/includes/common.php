<?php

/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
}

//
// Get Album Config
//
$sql = 'SELECT *
	FROM ' . GALLERY_CONFIG_TABLE;
$result = $db->sql_query($sql);

while( $row = $db->sql_fetchrow($result) )
{
	$album_config_name = $row['config_name'];
	$album_config_value = $row['config_value'];
	$album_config[$album_config_name] = $album_config_value;
}

//
// Set ALBUM Version
//
$template->assign_vars(array(
	'ALBUM_VERSION' => '2' . $album_config['album_version'],
));
$user->add_lang('mods/info_acp_gallery');
include($album_root_path . 'includes/functions.' . $phpEx);
//include($album_root_path . 'includes/constants.' . $phpEx);

?>