<?php

/**
*
* @package phpBB3 - phpBB Gallery database updater
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'install/common.' . $phpEx);

$confirm = request_var('confirm', 0);
$submit = (isset($_POST['submit']) && $confirm) ? true : false;
$message = '';

$template->assign_vars(array(
	'S_IN_DELETE'			=> true,
	'U_ACTION'				=> append_sid("{$phpbb_root_path}install/delete.php"),
));

if ($submit)
{
	/*
	* Drop the db-structure
	*/
	nv_drop_table('phpbb_gallery_albums');
	nv_drop_table('phpbb_gallery_comments');
	nv_drop_table('phpbb_gallery_config');
	nv_drop_table('phpbb_gallery_favorites');
	nv_drop_table('phpbb_gallery_images');
	nv_drop_table('phpbb_gallery_modscache');
	nv_drop_table('phpbb_gallery_permissions');
	nv_drop_table('phpbb_gallery_rates');
	nv_drop_table('phpbb_gallery_reports');
	nv_drop_table('phpbb_gallery_roles');
	nv_drop_table('phpbb_gallery_users');
	nv_drop_table('phpbb_gallery_watch');

	/*
	* Remove created columns
	*/
	nv_remove_column(USERS_TABLE, 'album_id');
	nv_remove_column(GROUPS_TABLE, 'allow_personal_albums');
	nv_remove_column(GROUPS_TABLE, 'view_personal_albums');
	nv_remove_column(GROUPS_TABLE, 'personal_subalbums');
	nv_remove_column(SESSIONS_TABLE, 'session_album_id');

	/*
	* Remove BBCode
	*/
	$bbcode_id = request_var('bbcode_id', 0);
	$sql = 'DELETE FROM ' . BBCODES_TABLE . " WHERE bbcode_id = $bbcode_id";
	$db->sql_query($sql);

	/*
	* Remove modules
	*/
	$sql = 'SELECT module_id, module_class, left_id, right_id
		FROM ' . MODULES_TABLE . '
		WHERE ' . $db->sql_in_set('module_langname', $module_names) . '
		ORDER BY left_id DESC';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		remove_module($row['module_id'], $row['module_class']);
	}
	$db->sql_freeresult($result);

	/*
	* Final step
	*/
	$cache->purge();
	add_log('admin', 'LOG_PURGE_CACHE');
	$message = $user->lang['INSTALLER_DELETE_SUCCESSFUL'];
	trigger_error($message);
}
else
{
	$select_bbcode = '';
	$sql = 'SELECT bbcode_id, bbcode_tag
		FROM ' . BBCODES_TABLE;
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$select_bbcode .= ($select_bbcode == '') ? '<select name="bbcode_id"><option value="0">' . $user->lang['INSTALLER_DELETE_BBCODE'] . '</option>' : '';
		$select_bbcode .= '<option value="' . $row['bbcode_id'] . '">[' . $row['bbcode_tag'] . ']</option>';
	}
	$db->sql_freeresult($result);
	$select_bbcode .= '</select>';

	$template->assign_vars(array(
		'SELECT_BBCODE'		=> $select_bbcode,
	));
}


page_header($page_title);

$template->set_filenames(array(
	'body' => 'delete_body.html')
);

page_footer();

?>