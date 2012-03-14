<?php
/**
*
* @package Language File Conflict Detector
* @version $Id$
* @copyright (c) 2012 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* The path to your phpBB root file (the one with the config file)
*/
$phpbb_root_path = '../';

/**
* Languages to test
*/
$languages = array(
	'en',
);

/**
* YOUR LANGUAGE FILES
*/
$mod_files = array(
	'mods/exif_data',
	'mods/gallery',
	'mods/gallery_acp',
	'mods/gallery_mcp',
	'mods/gallery_ucp',
	'mods/info_acp_gallery',
	'mods/info_acp_gallery_logs',
	'mods/info_ucp_gallery',
	'mods/install_gallery',
	'mods/permissions_gallery',
);

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

include('lfcd/lfcd.' . $phpEx);

$lfcd = new lfcd();
$lfcd->validate($mod_files);

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-gb" xml:lang="en-gb">
<head>
<title>LFCD</title>
<meta content="text/html; charset=UTF-8" http-equiv="content-type">
</head>
<body>';
echo $lfcd->get_report();
echo '</body>
</html>';










