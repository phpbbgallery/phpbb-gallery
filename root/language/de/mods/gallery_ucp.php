<?php

/**
*
* @package phpBB3 - gallery
* @version $Id: gallery_acp.php 256 2008-01-25 18:52:19Z nickvergessen $
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
	'PERSONAL_ALBUM'					=> 'Persönliches Album',
	'NO_PERSONAL_ALBUM'					=> 'Dein persönliches Album existiert noch nicht. Du kannst Dir hier ein privates Album und weitere Subalben erstellen.<br />Nur der Album Besitzer kann in diese persönlichen Alben Bilder hochladen.',
	'CREATE_PERSONAL_ALBUM'				=> 'Erstelle persönliches Album',
	'MANAGE_PERSONAL_ALBUM'				=> 'Hier kannst Du Dein persönliches Album verwalten. Du kannst Subalben hinzufügen, Beschreibungen hinzufügen und bearbeiten, die Reihenfolge der Anzeige beeinflussen und vieles mehr.',
	'MANAGE_SUBALBUMS'					=> 'Verwalte Deine Subalben',
	'CREATE_SUBALBUM'					=> 'Erstelle ein Subalbum',
	'CREATE_SUBALBUM_EXP'				=> 'Du kannst ein Subalbum zu Deinem persönlichem Album hinzufügen.',

	'ALBUM_NAME'					=> 'Name',
	'ALBUM_DESC'					=> 'Beschreibung',
	'ALBUM_PARENT'					=> 'Übergeordnetes Album',
	'NO_PARENT_ALBUM'						=> '&laquo;-- kein übergeordnetes Album',
	'PARSE_BBCODE'					=> 'BBCodes erkennen',
	'PARSE_SMILIES'					=> 'Smilies erkennen',
	'PARSE_URLS'					=> 'Links erkennen',

	'EDIT'							=> 'Bearbeiten',
	'EDIT_SUBALBUM'					=> 'Bearbeite Subalben',
	'EDIT_SUBALBUM_EXP'				=> 'Du kannst hier Deine Alben bearbeiten.',
	'EDITED_SUBALBUM'				=> 'Album erfolgreich bearbeitet',
	'CREATED_SUBALBUM'				=> 'Subalbum erfolgreich bearbeitet',
	'MISSING_NAME'					=> 'Gib bitte einen Namen für das Album an',
	'ATTACHED_SUBALBUMS'			=> 'Verknüpfte Subalben',
	'NO_SUBALBUMS'					=> 'Keine Subalben',
	'NEED_INITIALISE'				=> 'Du hast bisher noch kein Subalbum.',
	'DELETE_ALBUM'					=> 'Lösche Album',
	'DELETE_ALBUM_CONFIRM'			=> 'Album mit allen Bildern und Subalben löschen?',
	'DELETED_ALBUMS'				=> 'Album erfolgreich gelöscht',
	'MOVED_ALBUMS'					=> 'Album erfolgreich verschoben',
	'NO_ALBUM_STEALING'				=> 'Du bist nicht berechtigt Alben von anderen Benutzern zu verwalten.',
	'NO_SUBALBUMS_ALLOWED'			=> 'Du bist nicht berechtigt Subalben zu Deinem persönlichem Album hinzuzufügen.',
	'NO_MORE_SUBALBUMS_ALLOWED'		=> 'Du hast bereits die maximale Anzahl von Subalben zu Deinem persönlichem Album hinzugefügt.',
	'NO_PERSALBUM_ALLOWED'			=> 'Du bist nicht berechtigt eine persönliches Album zu erstellen.',
	'GOTO'							=> 'Gehe zu',

	'EDIT_ALBUM'					=> 'Dieses Album bearbeiten',
));

$lang = array_merge($lang, array(
	'NO_FAVORITES'					=> 'Du hast keine Lieblingsbilder.',
	'NO_SUBSCRIPTIONS'				=> 'Du beobachtest keine Bilder.',

	'REMOVE_FROM_FAVORITES'			=> 'Aus den Lieblingsbildern entfernen',

	'YOUR_FAVORITE_IMAGES'			=> 'Hier siehst du deine Lieblingsbilder. Du kannst sie auch wieder entfernen, wenn sie dir nicht gefallen.',
	'YOUR_SUBSCRIPTIONS'			=> 'Hier siehst du die Bilder und Alben, bei denen du benachrichtigt wirst.',

	'WATCH_CHANGED'					=> 'Einstellungen gespeichert',
	'WATCH_COM'						=> 'Kommentierte Bilder standardmässig beobachten',
	'WATCH_FAVO'					=> 'Lieblingsbilder standardmässig beobachten',
	'WATCH_NOTE'					=> 'Die Einstellung wirkt sich nur auf neue Bilder aus. Andere Bilder musst du über die Option "Bild beobachten" hinzufügen',
	'WATCH_OWN'						=> 'Eigene Bilder standardmässig beobachten',
));

?>