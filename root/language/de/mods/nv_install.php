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
	'CHMOD'						=> 'Auf Beschreibbarkeit überprüfen',
	'CHMOD_EXPLAIN'				=> 'Die folgenden Verzeichnisse müssen CHMOD auf 777 gesetzt haben, damit die Gallery reibungslos funktioniert.',
	'CHMOD_UNWRITABLE'			=> 'Unbeschreibbar',
	'CHMOD_WRITABLE'			=> 'Beschreibbar',

	'INSTALL_VERSION'			=> 'Installiere MOD v%s',

	'MODULES_ADVICE_SELECT'				=> 'Empfehlung ist "%s"',
	'MODULES_CREATE_PARENT'				=> 'Übergeordnetes Standard-Modul erstellen',
	'MODULES_MODULE_ID'					=> 'ID',
	'MODULES_MODULE_NAME'				=> 'Name',
	'MODULES_PARENT_SELECT'				=> 'Übergeordnetes Modul auswählen',
	'MODULES_SELECT_4ACP'				=> 'Übergeordnetes Modul für den "Administrations-Bereich"',
	'MODULES_SELECT_4MCP'				=> 'Übergeordnetes Modul für den "Moderations-Bereich"',
	'MODULES_SELECT_4UCP'				=> 'Übergeordnetes Modul für den "Persönlichen Bereich"',
	'MODULES_SELECT_NONE'				=> 'kein übergeordnetes Modul',

	'STEP_LOG'					=> 'Schritt <strong>%2$s</strong> von <strong>%1$s</strong>: %3$s: <strong>%4$s</strong><br />',
	'STEP_SUCCESSFUL'			=> 'Erfolgreich',

	'TAB_CONVERT'				=> 'Konvertieren',
	'TAB_DELETE'				=> 'Löschen',
	'TAB_INSTALL'				=> 'Installieren',
	'TAB_INTRO'					=> 'Übersicht',
	'TAB_UPDATE'				=> 'Update',
));

$lang = array_merge($lang, array(
	'INTRO_WELCOME'				=> 'Einleitung',
));

$lang = array_merge($lang, array(
	'INSTALL'					=> 'Installieren',
	'INSTALL_SUCCESSFUL'		=> 'Installation der MOD v%s war erfolgreich.',
	'INSTALL_WELCOME'			=> 'Installation',
));

$lang = array_merge($lang, array(
	'UPDATE'					=> 'Update',
	'UPDATE_NOTE'				=> 'Update MOD von v%s nach v%s',
	'UPDATE_SUCCESSFUL'			=> 'Update der MOD von v%s nach v%s war erfolgreich.',
	'UPDATE_VERSION'			=> 'Update MOD von v',
	'UPDATE_WELCOME'			=> 'Update',
));

$lang = array_merge($lang, array(
	'CONVERT'					=> 'Konvertieren',
	'CONVERTER'					=> 'Konverter',
	'CONVERT_NOTE'				=> 'Konvertiere MOD zu v%s',
	'CONVERT_PREFIX'			=> 'Präfix der phpBB2-Installation',
	'CONVERT_PREFIX_MISSING'	=> 'Du hast kein Präfix für die phpBB2-Installation eingefügt.',
	'CONVERT_SUCCESSFUL'		=> 'Konvertierung des MODs zu v%s war erfolgreich.',
	'CONVERT_WELCOME'			=> 'Konvertierung',
));

$lang = array_merge($lang, array(
	'DELETE'					=> 'Löschen',
	'DELETE_BBCODE'				=> 'Wähle den richtigen BBCode',
	'DELETE_NOTE'				=> 'Löschen',
	'DELETE_SUCCESSFUL'			=> 'Die MOD wurde erfolgreich deinstalliert.<br />Du kannst nun alle Dateien löschen.',
	'DELETE_WELCOME'			=> 'Du möchtest die MOD wirklich löschen?',
	'DELETE_WELCOME_NOTE'		=> 'Wenn du die MOD hier löschst, werden alle Datenbankeinträge gelöscht.',
));

?>