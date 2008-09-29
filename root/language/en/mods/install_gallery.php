<?php

/**
*
* @package phpBB3 - gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/
if (!defined('IN_PHPBB'))
{
	exit;
}
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'CAT_CONVERT'				=> 'convert phpBB2',
	'CAT_CONVERT_TS'			=> 'convert TS Gallery',

	'CHECK_TABLES'				=> 'Check tables',
	'CHECK_TABLES_EXPLAIN'		=> 'The following tables need to exist, so they can be converted.',

	'CONVERT_SMARTOR_INTRO'		=> 'Convertor from „Album-MOD“ by smartor to „phpBB Gallery“',
	'CONVERT_SMARTOR_INTRO_BODY'	=> 'With this convertor, you can convert your albums, images, rates and comments from the <a href="http://www.phpbb.com/community/viewtopic.php?f=16&t=74772">Album-MOD</a> by Smartor (tested v2.0.56) and <a href="http://www.phpbbhacks.com/download/5028">Full Album Pack</a> (tested v1.4.1) to the phpBB Gallery.<br /><br /><strong>Note:</strong> The <strong>permissions</strong> will <strong>not be copied</strong>.',
	'CONVERT_TS_INTRO'			=> 'Convertor from „TS Gallery“ to „phpBB Gallery“',
	'CONVERT_TS_INTRO_BODY'		=> 'With this convertor, you can convert your albums, images, rates and comments from the <a href="http://www.phpbb.com/community/viewtopic.php?f=70&t=610509">TS Gallery</a> (tested v0.2.1) to the phpBB Gallery.<br /><br /><strong>Note:</strong> The <strong>permissions</strong> will <strong>not be copied</strong>.',
	'CONVERT_COMPLETE_EXPLAIN'	=> 'Conversion from your gallery to phpBB Gallery v%s was successful.<br />Please ensure that the settings were transferred correctly before enabling your board by deleting the install directory.<br /><br /><strong>Please note that the permissions were not copied.</strong><br /><br />You should also clean your database from old entries, where the images are missing. This can be done in ".MODs > phpBB Gallery > Cleanup gallery".',

	'CONVERTED_ALBUMS'			=> 'The albums were successful copied.',
	'CONVERTED_COMMENTS'		=> 'The comments were successful copied.',
	'CONVERTED_IMAGES'			=> 'The images were successful copied.',
	'CONVERTED_PERSONALS'		=> 'The personal albums were successful copied.',
	'CONVERTED_RATES'			=> 'The rates were successful copied.',
	'CONVERTED_RESYNC_ALBUMS'	=> 'Resyncronize album-stats.',
	'CONVERTED_RESYNC_COMMENTS'	=> 'Resyncronize comments.',
	'CONVERTED_RESYNC_COUNTS'	=> 'Resyncronize imagecounters.',
	'CONVERTED_RESYNC_RATES'	=> 'Resyncronize rates.',

	'FILES_REQUIRED_EXPLAIN'	=> '<strong>Required</strong> - In order to function correctly phpBB Gallery needs to be able to access or write to certain files or directories. If you see “Unwritable” you need to change the permissions on the file or directory to allow phpBB to write to it.',

	'INSTALL_CONGRATS_EXPLAIN'	=> '<p>You have now successfully installed phpBB Gallery v%s.<br/><br/><strong>Please now delete, move or rename the install directory before you use your board. If this directory is still present, only the Administration Control Panel (ACP) will be accessible.</strong></p>',
	'INSTALL_INTRO_BODY'		=> 'With this option, it is possible to install phpBB Gallery onto your board.',

	'GOTO_GALLERY'				=> 'Go to phpBB Gallery',

	'MODULES_CREATE_PARENT'		=> 'Create parent standard-module',
	'MODULES_PARENT_SELECT'		=> 'Choose parent module',
	'MODULES_SELECT_4ACP'		=> 'Choose parent module for "admin control panel"',
	'MODULES_SELECT_4MCP'		=> 'Choose parent module for "moderation control panel"',
	'MODULES_SELECT_4UCP'		=> 'Choose parent module for "user control panel"',
	'MODULES_SELECT_NONE'		=> 'no parent module',

	'REQUIREMENTS_EXPLAIN'		=> 'Before proceeding with the full installation phpBB will carry out some tests on your server configuration and files to ensure that you are able to install and run phpBB Gallery. Please ensure you read through the results thoroughly and do not proceed until all the required tests are passed.',
	'REQ_GD_LIBRARY'			=> 'GD Library is installed',

	'STAGE_ADVANCED_EXPLAIN'	=> 'Please choose the parent module for the gallery modules. In normal case you should not change them.',
	'STAGE_COPY_TABLE'			=> 'Copy database-tables',
	'STAGE_COPY_TABLE_EXPLAIN'	=> 'Die Datenbank-Tabellen mit den Alben und Benutzer-Informationen der TS Gallery haben die gleichen Namen wie die der phpBB Gallery. Es wurde daher eine Kopie angelegt, um die Daten trotzdem importieren zu können.',
	'STAGE_CREATE_TABLE_EXPLAIN'	=> 'The database tables used by phpBB Gallery have been created and populated with some initial data. Proceed to the next screen to finish installing phpBB Gallery.',
	'SUPPORT_BODY'				=> 'Full support will be provided for the current stable release of phpBB Gallery, free of charge. This includes:</p><ul><li>installation</li><li>configuration</li><li>technical questions</li><li>problems relating to potential bugs in the software</li><li>updating from Release Candidate (RC) versions to the latest stable version</li><li>converting from Smartor\'s Album-MOD for phpBB 2.0.x to phpBB Gallery for phpBB3</li><li>converting from TS Gallery to phpBB Gallery</li></ul><p>The use of Beta-Versions is limited recommended. If there are updates, it\'s recommended to update quickly.</p><p>Support is given on the following boards</p><ul><li><a href="http://www.flying-bits.org/">flying-bits.org - MOD-Autor nickvergessen\'s board</a></li><li><a href="http://www.phpbb.de/">phpbb.de</a></li><li><a href="http://www.phpbb.com/">phpbb.com</a></li></ul><p>',

	'TABLE_ALBUM'				=> 'table including the images',
	'TABLE_ALBUM_CAT'			=> 'table including the albums',
	'TABLE_ALBUM_COMMENT'		=> 'table including the comments',
	'TABLE_ALBUM_CONFIG'		=> 'table including the configuration',
	'TABLE_ALBUM_RATE'			=> 'table including the rates',
	'TABLE_EXISTS'				=> 'exists',
	'TABLE_MISSING'				=> 'missing',
	'TABLE_PREFIX_EXPLAIN'		=> 'Prefix of phpBB2-installation',

	'UPDATE_INSTALLATION_EXPLAIN'	=> 'Here you can Update your phpBB Gallery-Version.',

	'VERSION_NOT_SUPPORTED'		=> 'Sorry, but your updates from < 0.2.0 are not supported from this install-version.',
));

?>