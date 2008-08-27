<?php

/**
*
* @package NV Install
* @version $Id$
* @copyright (c) 2008 nickvergessen http://www.flying-bits.org
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

	'CHMOD'					=> 'Checking for CHMOD',
	'CHMOD_EXPLAIN'			=> 'The following directories need CHMOD 777, to make the gallery work.',
	'CHMOD_UNWRITABLE'		=> 'Unwriteable',
	'CHMOD_WRITABLE'		=> 'Writeable',

	'INSTALL_VERSION'			=> 'Install MOD v%s',

	'MODULES_ADVICE_SELECT'				=> 'Adviced is "%s"',
	'MODULES_CREATE_PARENT'				=> 'Create parent standard-module',
	'MODULES_MODULE_ID'					=> 'ID',
	'MODULES_MODULE_NAME'				=> 'Name',
	'MODULES_PARENT_SELECT'				=> 'Choose parent module',
	'MODULES_SELECT_4ACP'				=> 'Choose parent module for "admin control panel"',
	'MODULES_SELECT_4MCP'				=> 'Choose parent module for "moderation control panel"',
	'MODULES_SELECT_4UCP'				=> 'Choose parent module for "user control panel"',
	'MODULES_SELECT_NONE'				=> 'no parent module',

	'STEP_LOG'					=> 'Step <strong>%2$s</strong> of <strong>%1$s</strong>: %3$s: <strong>%4$s</strong><br />',
	'STEP_SUCCESSFUL'			=> 'Successful',

	'TAB_CONVERT'				=> 'Convert',
	'TAB_DELETE'				=> 'Delete',
	'TAB_INSTALL'				=> 'Install',
	'TAB_INTRO'					=> 'Overview',
	'TAB_UPDATE'				=> 'Update',
));

$lang = array_merge($lang, array(
	'INTRO_WELCOME'				=> 'Introduction',
));

$lang = array_merge($lang, array(
	'INSTALL'					=> 'Install',
	'INSTALL_SUCCESSFUL'		=> 'Installation of the MOD v%s was successful.',
	'INSTALL_WELCOME'			=> 'Installion',
));

$lang = array_merge($lang, array(
	'UPDATE'					=> 'Update',
	'UPDATE_NOTE'				=> 'Update MOD from v%s to v%s',
	'UPDATE_SUCCESSFUL'			=> 'Update from v%s to v%s was successful.',
	'UPDATE_VERSION'			=> 'Update MOD from v',
	'UPDATE_WELCOME'			=> 'Update',
));

$lang = array_merge($lang, array(
	'CONVERT'					=> 'Convert',
	'CONVERTER'					=> 'Converter',
	'CONVERT_NOTE'				=> 'Convert MOD to v%s',
	'CONVERT_PREFIX'			=> 'Prefix of phpBB2-installation',
	'CONVERT_PREFIX_MISSING'	=> 'You didn\'t insert a prefix of your phpBB2-installation.',
	'CONVERT_SUCCESSFUL'		=> 'Convert of the MOD to v%s was successful.',
	'CONVERT_WELCOME'			=> 'Convert',
));

$lang = array_merge($lang, array(
	'DELETE'					=> 'Delete',
	'DELETE_NOTE'				=> 'Delete',
	'DELETE_BBCODE'				=> 'Choose BBCode',
	'DELETE_SUCCESSFUL'			=> 'Deleted the MOD successfully.<br />Now delete all files.',
	'DELETE_WELCOME'			=> 'You really want to delete the gallery?',
	'DELETE_WELCOME_NOTE'		=> 'When you choose to delete the MOD, we remove all sql-data insert by the installation.',
));

?>