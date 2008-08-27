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
	'PAGE_TITLE'				=> 'phpBB Gallery v%s',
	'SPECIAL_ROOT_PATH'			=> 'phpBB Gallery',//Description next to the Forum-Index link
));

$lang = array_merge($lang, array(
	'INTRO_WELCOME_NOTE'		=> 'Welcome to the MOD Installation!<br /><br />Please choose what you want to do.',
));

$lang = array_merge($lang, array(
	'INSTALL_WELCOME_NOTE'		=> 'When you choose to install the MOD, any database of previous versions will be dropped.',
));

$lang = array_merge($lang, array(
	'CONVERT_SMARTOR'			=> 'Convert from Smartor´s Album MOD (also with Full Album Pack)',
	'CONVERT_SUCCESSFUL_ADD'	=> 'Now copy the image-files of album/upload and album/upload/cache of your phpbb2-Installation into the one\'s of the phpBB3.',
));
/*
* End of Force!
* The rest is gallery specific, but an Example for the STEP_LOG-Message!
*/

$lang = array_merge($lang, array(
	'STEPS_ADD_BBCODE'			=> 'Add BBCode',
	'STEPS_ADD_CONFIGS'			=> 'Add config-values',
	'STEPS_ADD_PERSONALS'		=> 'Add personal albums',
	'STEPS_COPY_ALBUMS'			=> 'Copy albums',
	'STEPS_COPY_COMMENTS'		=> 'Copy comments',
	'STEPS_COPY_IMAGES'			=> 'Copy images',
	'STEPS_COPY_RATES'			=> 'Copy rates',
	'STEPS_CREATE_EXAMPLES'		=> 'Add Examplealbum and -image',
	'STEPS_DBSCHEMA'			=> 'Create database-tables and add database-columns',
	'STEPS_IMPORT_ALBUMS'		=> 'Import albums',
	'STEPS_IMPORT_COMMENTS'		=> 'Import comments',
	'STEPS_IMPORT_IMAGES'		=> 'Import images',
	'STEPS_IMPORT_RATES'		=> 'Import rates',
	'STEPS_UPDATE_IMAGES'		=> 'Refresh image-data',
	'STEPS_UPDATE_COMMENTS'		=> 'Refresh comment-data',
	'STEPS_MODULES'				=> 'Add modules',
	'STEPS_REMOVE_COLUMNS'		=> 'Delete database-columns',
	'STEPS_REMOVE_CONFIGS'		=> 'Delete config-values',
	'STEPS_RESYN_ALBUMS'		=> 'Resyncronize album-stats',
	'STEPS_RESYN_COUNTERS'		=> 'Resyncronize imagecounters',
	'STEPS_RESYN_MODULES'		=> 'Resyncronize modules',
));

$lang = array_merge($lang, array(
	'EXAMPLE_ALBUM1'					=> 'Your first category',
	'EXAMPLE_ALBUM2'					=> 'Your first album',
	'EXAMPLE_ALBUM2_DESC'				=> 'Description of your first album.',
	'EXAMPLE_DESC'						=> 'Thank you for installing phpBB Gallery v%s aka. &quot;DB-Bird&quot;.<br />'
											. 'This is just an example-image, you may delete it.',
	'EXAMPLE_DESC_UID'					=> '1vrbfkfh',
));

?>