<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}
if (!defined('IN_INSTALL'))
{
	exit;
}

if (!empty($setmodules))
{
	$module[] = array(
		'module_type'		=> 'update',
		'module_title'		=> 'UPDATE',
		'module_filename'	=> substr(basename(__FILE__), 0, -strlen($phpEx)-1),
		'module_order'		=> 20,
		'module_subs'		=> '',
		'module_stages'		=> array('INTRO', 'REQUIREMENTS', 'UPDATE_DB', 'ADVANCED', 'FINAL'),
		'module_reqs'		=> ''
	);
}

/**
* Installation
* @package install
*/
class install_update extends module
{
	function install_update(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $cache, $gallery_config, $template, $user;
		global $phpbb_root_path, $phpEx;

		$gallery_config = load_gallery_config();

		switch ($sub)
		{
			case 'intro':
				$this->page_title = $user->lang['SUB_INTRO'];

				$template->assign_vars(array(
					'TITLE'			=> $user->lang['UPDATE_INSTALLATION'],
					'BODY'			=> $user->lang['UPDATE_INSTALLATION_EXPLAIN'],
					'L_SUBMIT'		=> $user->lang['NEXT_STEP'],
					'U_ACTION'		=> $this->p_master->module_url . "?mode=$mode&amp;sub=requirements",
				));

			break;

			case 'requirements':
				$this->check_server_requirements($mode, $sub);

			break;

			case 'update_db':
				$database_step = request_var('step', 0);
				switch ($database_step)
				{
					case 0:
						$this->update_db_schema($mode, $sub);
					break;
					// updates starting from 0.2.0 up to 0.3.1
					// case 1: unsupported
					// updates starting from 0.3.2-RC1 up to 0.4.1
					case 2:
					// from 0.5.0
					case 3:
						$this->update_db_data($mode, $sub);
					break;
					case 4:
						$this->thinout_db_schema($mode, $sub);
					break;
				}
			break;

			case 'advanced':
				$this->obtain_advanced_settings($mode, $sub);

			break;

			case 'final':
				set_gallery_config('phpbb_gallery_version', NEWEST_PG_VERSION);
				$cache->purge();

				$template->assign_vars(array(
					'TITLE'		=> $user->lang['INSTALL_CONGRATS'],
					'BODY'		=> sprintf($user->lang['INSTALL_CONGRATS_EXPLAIN'], NEWEST_PG_VERSION),
					'L_SUBMIT'	=> $user->lang['GOTO_GALLERY'],
					'U_ACTION'	=> append_sid($phpbb_root_path . GALLERY_ROOT_PATH . 'index.' . $phpEx),
				));


			break;
		}

		$this->tpl_name = 'install_install';
	}

	/**
	* Checks that the server we are installing on meets the requirements for running phpBB
	*/
	function check_server_requirements($mode, $sub)
	{
		global $user, $template, $phpbb_root_path, $phpEx;

		$this->page_title = $user->lang['STAGE_REQUIREMENTS'];

		$template->assign_vars(array(
			'TITLE'		=> $user->lang['REQUIREMENTS_TITLE'],
			'BODY'		=> $user->lang['REQUIREMENTS_EXPLAIN'],
		));

		$passed = array('php' => false, 'files' => false, 'dirs' => false,);

		// Test for basic PHP settings
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $user->lang['PHP_SETTINGS'],
		));

		// Check for GD-Library
		if (@extension_loaded('gd') || can_load_dll('gd'))
		{
			$passed['php'] = true;
			$result = '<strong style="color:green">' . $user->lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . $user->lang['NO'] . '</strong>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $user->lang['REQ_GD_LIBRARY'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> false,
			'S_LEGEND'		=> false,
		));

		// Check permissions on files/directories we need access to
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $user->lang['FILES_REQUIRED'],
			'LEGEND_EXPLAIN'	=> $user->lang['FILES_REQUIRED_EXPLAIN'],
		));

		$directories = array(
			GALLERY_IMPORT_PATH,
			GALLERY_UPLOAD_PATH,
			GALLERY_MEDIUM_PATH,
			GALLERY_CACHE_PATH,
		);

		umask(0);

		$passed['dirs'] = true;
		foreach ($directories as $dir)
		{
			$write = false;

			// Now really check
			if (file_exists($phpbb_root_path . $dir) && is_dir($phpbb_root_path . $dir))
			{
				if (!@is_writable($phpbb_root_path . $dir))
				{
					@chmod($phpbb_root_path . $dir, 0777);
				}
			}

			// Now check if it is writable by storing a simple file
			$fp = @fopen($phpbb_root_path . $dir . 'test_lock', 'wb');
			if ($fp !== false)
			{
				$write = true;
			}
			@fclose($fp);

			@unlink($phpbb_root_path . $dir . 'test_lock');

			$passed['dirs'] = ($write && $passed['dirs']) ? true : false;

			$write = ($write) ? '<strong style="color:green">' . $user->lang['WRITABLE'] . '</strong>' : '<strong style="color:red">' . $user->lang['UNWRITABLE'] . '</strong>';

			$template->assign_block_vars('checks', array(
				'TITLE'		=> $dir,
				'RESULT'	=> $write,

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}

		// Check whether all old files are deleted
		include($phpbb_root_path . 'install/outdated_files.' . $phpEx);

		umask(0);

		$passed['files'] = true;
		$delete = (isset($_POST['delete'])) ? true : false;
		foreach ($oudated_files as $file)
		{
			if ($delete)
			{
				if (@file_exists($phpbb_root_path . $file))
				{
					// Try to set CHMOD and then delete it
					@chmod($phpbb_root_path . $file, 0777);
					@unlink($phpbb_root_path . $file);
					// Delete failed, tell the user to delete it manually
					if (@file_exists($phpbb_root_path . $file))
					{
						if ($passed['files'])
						{
							$template->assign_block_vars('checks', array(
								'S_LEGEND'			=> true,
								'LEGEND'			=> $user->lang['FILES_OUTDATED'],
								'LEGEND_EXPLAIN'	=> $user->lang['FILES_OUTDATED_EXPLAIN'],
							));
						}
						$template->assign_block_vars('checks', array(
							'TITLE'		=> $file,
							'RESULT'	=> '<strong style="color:red">' . $user->lang['FILE_DELETE_FAIL'] . '</strong>',

							'S_EXPLAIN'	=> false,
							'S_LEGEND'	=> false,
						));
						$passed['files'] = false;
					}
				}
			}
			elseif (@file_exists($phpbb_root_path . $file))
			{
				if ($passed['files'])
				{
					$template->assign_block_vars('checks', array(
						'S_LEGEND'			=> true,
						'LEGEND'			=> $user->lang['FILES_OUTDATED'],
						'LEGEND_EXPLAIN'	=> $user->lang['FILES_OUTDATED_EXPLAIN'],
					));
				}
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $file,
					'RESULT'	=> '<strong style="color:red">' . $user->lang['FILE_STILL_EXISTS'] . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
				$passed['files'] = false;
			}
		}
		if (!$passed['files'])
		{
			$template->assign_block_vars('checks', array(
				'TITLE'			=> '<strong>' . $user->lang['FILES_DELETE_OUTDATED'] . '</strong>',
				'TITLE_EXPLAIN'	=> $user->lang['FILES_DELETE_OUTDATED_EXPLAIN'],
				'RESULT'		=> '<input class="button1" type="submit" id="delete" onclick="this.className = \'button1 disabled\';" name="delete" value="' . $user->lang['FILES_DELETE_OUTDATED'] . '" />',

				'S_EXPLAIN'	=> true,
				'S_LEGEND'	=> false,
			));
		}

		$url = (!in_array(false, $passed)) ? $this->p_master->module_url . "?mode=$mode&amp;sub=update_db" : $this->p_master->module_url . "?mode=$mode&amp;sub=requirements";
		$submit = (!in_array(false, $passed)) ? $user->lang['INSTALL_START'] : $user->lang['INSTALL_TEST'];

		$template->assign_vars(array(
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Add some Tables, Columns and Index to the database-schema
	*/
	function update_db_schema($mode, $sub)
	{
		global $db, $user, $template, $gallery_config, $table_prefix;

		$gallery_config = load_gallery_config();
		$this->page_title = $user->lang['STAGE_UPDATE_DB'];

		if (!isset($gallery_config['phpbb_gallery_version']))
		{
			$gallery_config['phpbb_gallery_version'] = (isset($gallery_config['album_version'])) ? $gallery_config['album_version'] : '0.0.0';
			if (in_array($gallery_config['phpbb_gallery_version'], array('0.1.2', '0.1.3', '0.2.0', '0.2.1', '0.2.2', '0.2.3', '0.3.0', '0.3.1')))
			{
				$sql = 'SELECT * FROM ' . GALLERY_ALBUMS_TABLE;
				if (@$db->sql_query_limit($sql, 1) == false)
				{
					// DB-Table missing
					$gallery_config['phpbb_gallery_version'] = '0.1.2';
					$check_succeed = true;
				}
				else
				{
					// No Schema Changes between 0.1.3 and 0.2.2
					$gallery_config['phpbb_gallery_version'] = '0.2.2';
					if (nv_check_column(GALLERY_ALBUMS_TABLE, 'album_user_id'))
					{
						$gallery_config['phpbb_gallery_version'] = '0.2.3';
						$sql = 'SELECT * FROM ' . GALLERY_FAVORITES_TABLE;
						if (@$db->sql_query_limit($sql, 1) == true)
						{
							$gallery_config['phpbb_gallery_version'] = '0.3.1';
						}
					}
				}
			}
			else
			{
				// No version-number problems since 0.4.0-RC1
				$gallery_config['phpbb_gallery_version'] = $gallery_config['album_version'];
			}
		}

		set_gallery_config('phpbb_gallery_version', $gallery_config['phpbb_gallery_version']);

		$dbms_data = get_dbms_infos();
		$db_schema = $dbms_data['db_schema'];
		$delimiter = $dbms_data['delimiter'];

		switch ($gallery_config['phpbb_gallery_version'])
		{
			case '0.1.2':
			case '0.1.3':

			case '0.2.0':
			case '0.2.1':
			case '0.2.2':
			case '0.2.3':

			case '0.3.0':
			case '0.3.1':
/			case '0.3.2-RC1':
			case '0.3.2-RC2':

			case '0.4.0-RC1':
			case '0.4.0-RC2':
			case '0.4.0-RC3':
			case '0.4.0':
				trigger_error('VERSION_NOT_SUPPORTED', E_USER_ERROR);
			break;

			case '0.4.1':
				nv_add_column(GALLERY_ALBUMS_TABLE,	'album_contest',			array('UINT', 0));

				nv_add_column(GALLERY_IMAGES_TABLE,	'filesize_upload',			array('UINT:20', 0));
				nv_add_column(GALLERY_IMAGES_TABLE,	'filesize_medium',			array('UINT:20', 0));
				nv_add_column(GALLERY_IMAGES_TABLE,	'filesize_cache',			array('UINT:20', 0));
				nv_add_column(GALLERY_IMAGES_TABLE,	'image_contest',			array('UINT:1', 0));
				nv_add_column(GALLERY_IMAGES_TABLE,	'image_exif_data',			array('TEXT', ''));

				nv_change_column(GALLERY_PERMISSIONS_TABLE,	'perm_system',	array('INT:3', 0));

				nv_create_table('phpbb_gallery_contests',	$dbms_data);

			case '0.5.0':
				nv_add_column(GALLERY_ALBUMS_TABLE,	'album_status',			array('UINT:1', 0));
				nv_add_column(GALLERY_ALBUMS_TABLE,	'display_in_rrc',		array('UINT:1', 1));
				nv_add_column(GALLERY_IMAGES_TABLE,	'image_contest_end',	array('TIMESTAMP', 0));
				nv_add_column(GALLERY_IMAGES_TABLE,	'image_contest_rank',	array('UINT:3', 0));
			case '0.5.1':
			case '0.5.2':
				nv_create_table('phpbb_gallery_albums_track',	$dbms_data);
				nv_add_column(GALLERY_USERS_TABLE,	'user_lastmark',		array('TIMESTAMP', 0));
			case '0.5.3':
				nv_add_column(LOG_TABLE,			'album_id',				array('UINT', 0));
				nv_add_column(LOG_TABLE,			'image_id',				array('UINT', 0));
			case '0.5.4':

			case '1.0.0-dev':
				nv_add_column(GALLERY_ROLES_TABLE,	'i_unlimited',			array('UINT:3', 0));
				nv_add_column(GALLERY_ROLES_TABLE,	'album_unlimited',		array('UINT:3', 0));
			case '1.0.0-RC1':
			case '1.0.0-RC2':
			case '1.0.0':

			case '1.0.1-dev':
				nv_add_column(GALLERY_IMAGES_TABLE,	'image_name_clean',		array('VCHAR:255', ''));
				nv_add_column(GALLERY_IMAGES_TABLE,	'image_username_clean',	array('VCHAR:255', ''));
			case '1.0.1':

			case '1.0.2-dev':
			case '1.0.2-RC1':
			break;
		}

		$template->assign_vars(array(
			'BODY'		=> $user->lang['STAGE_CREATE_TABLE_EXPLAIN'],
			'L_SUBMIT'	=> $user->lang['NEXT_STEP'],
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $this->p_master->module_url . "?mode=$mode&amp;sub=update_db&amp;step=2",
		));
	}

	/**
	* Edit the data in the tables
	*/
	function update_db_data($mode, $sub)
	{
		global $cache, $config, $db, $gallery_config, $template, $user;
		global $phpbb_root_path, $phpEx, $table_prefix;
		include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);

		$gallery_config = load_gallery_config();
		$database_step = request_var('step', 0);

		$this->page_title = $user->lang['STAGE_UPDATE_DB'];
		$next_update_url = '';
		if ($database_step == 3)
		{
			$gallery_config['phpbb_gallery_version'] = '0.5.0';
		}

		switch ($gallery_config['phpbb_gallery_version'])
		{
			case '0.1.2':
			case '0.1.3':

			case '0.2.0':
			case '0.2.1':
			case '0.2.2':
			case '0.2.3':

			case '0.3.0':
			case '0.3.1':
			case '0.3.2-RC1':
			case '0.3.2-RC2':

			case '0.4.0-RC1':
			case '0.4.0-RC2':
			case '0.4.0-RC3':
			case '0.4.0':
				/**
				* Cheating?
				*/
				trigger_error('VERSION_NOT_SUPPORTED', E_USER_ERROR);
			break;

			case '0.4.1':
				// Resync the reported flags in addition to #393, #417
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET image_reported = 0';
				$db->sql_query($sql);
				$sql = 'SELECT report_image_id, report_id
					FROM ' . GALLERY_REPORTS_TABLE . '
					WHERE report_status = 1';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET image_reported = ' . $row['report_id'] . '
						WHERE image_id = ' . $row['report_image_id'];
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);

				// We moved the configurations-panel to a new file
				$sql = 'UPDATE ' . MODULES_TABLE . "
					SET module_basename = 'gallery_config',
						module_mode = 'main'
					WHERE module_langname = 'ACP_GALLERY_CONFIGURE_GALLERY'";
				$db->sql_query($sql);

				set_gallery_config('rrc_gindex_mode', 'all');
				set_gallery_config('rrc_gindex_rows', 1);
				set_gallery_config('rrc_gindex_columns', 4);
				set_gallery_config('rrc_gindex_comments', 0);

				// Only overwrite original watermarks
				if ($gallery_config['watermark_source'] == GALLERY_ROOT_PATH . 'mark.png')
				{
					set_gallery_config('watermark_source', GALLERY_IMAGE_PATH . 'watermark.png');
				}

				// Update permission-system to the constants
				$sql = 'UPDATE ' . GALLERY_PERMISSIONS_TABLE . '
					SET perm_system = ' . OWN_GALLERY_PERMISSIONS . '
					WHERE perm_system = 2';
				$db->sql_query($sql);
				$sql = 'UPDATE ' . GALLERY_PERMISSIONS_TABLE . '
					SET perm_system = ' . PERSONAL_GALLERY_PERMISSIONS . '
					WHERE perm_system = 3';
				$db->sql_query($sql);

				$next_update_url = $this->p_master->module_url . "?mode=$mode&amp;sub=update_db&amp;step=3";
			break;

			case '0.5.0':
			case '0.5.1-dev':
				// Move back two constants, only if they were not moved yet
				if (isset($config['gallery_user_images_profil']))
				{
					set_gallery_config('user_images_profile', $config['gallery_user_images_profil']);
				}
				if (isset($config['gallery_personal_album_profil']))
				{
					set_gallery_config('personal_album_profile', $config['gallery_personal_album_profil']);
				}

				set_gallery_config('rrc_profile_mode', '!comment');
				set_gallery_config('rrc_profile_columns', 4);
				set_gallery_config('rrc_profile_rows', 1);

				// Delete "confirmed deleted subalbums" #410
				recalc_btree('album_id', GALLERY_ALBUMS_TABLE, array(array('fieldname' => 'album_user_id', 'fieldvalue' => 0)));
				set_gallery_config('rrc_gindex_crows', 5);

				// Fill the new columns for contest winners
				$sql = 'SELECT *
					FROM ' . GALLERY_CONTESTS_TABLE . '
					WHERE contest_marked = ' . IMAGE_NO_CONTEST;
				$result = $db->sql_query($sql);
				$contests_ended = 0;
				while ($row = $db->sql_fetchrow($result))
				{
					$contest_end_time = $row['contest_start'] + $row['contest_end'];
					$sql_update = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET image_contest_end = ' . $contest_end_time . ',
							image_contest_rank = 1
						WHERE image_id = ' . $row['contest_first'];
					$db->sql_query($sql_update);
					$sql_update = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET image_contest_end = ' . $contest_end_time . ',
							image_contest_rank = 2
						WHERE image_id = ' . $row['contest_second'];
					$db->sql_query($sql_update);
					$sql_update = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET image_contest_end = ' . $contest_end_time . ',
							image_contest_rank = 3
						WHERE image_id = ' . $row['contest_third'];
					$db->sql_query($sql_update);
					$contests_ended++;
				}
				$db->sql_freeresult($result);
				set_gallery_config('contests_ended', $contests_ended);
				set_gallery_config('rrc_gindex_contests', 1);

			case '0.5.1':
			case '0.5.2-dev':
				// We moved the album management to a new file
				$sql = 'UPDATE ' . MODULES_TABLE . "
					SET module_basename = 'gallery_albums',
						module_mode = 'manage'
					WHERE module_langname = 'ACP_GALLERY_MANAGE_ALBUMS'";
				$db->sql_query($sql);

				set_gallery_config('rrc_gindex_display', 45);
				set_gallery_config('rrc_profile_display', 13);
				set_gallery_config('album_display', 126);

			case '0.5.2':
			case '0.5.3-dev':
				set_config('gallery_viewtopic_icon', 1);
				set_config('gallery_viewtopic_images', 1);
				set_config('gallery_viewtopic_link', 0);

			case '0.5.3':
				set_gallery_config('disp_login', 1);
				set_gallery_config('disp_whoisonline', 1);
				set_gallery_config('disp_birthdays', 0);
				set_gallery_config('disp_statistic', 1);
				set_gallery_config('rrc_gindex_pgalleries', 1);

				// Locked images were just like unapproved.
				// So we set their status to unapproved, when introducing the locked-status.
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
					SET image_status = ' . IMAGE_UNAPPROVED . '
					WHERE image_status <> ' . IMAGE_APPROVED;
				$db->sql_query($sql);

				// Unlock all pgalleries #504
				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
					SET album_status = ' . ITEM_UNLOCKED . '
					WHERE album_user_id <> 0';
				$db->sql_query($sql);

				// Set the lastmark to the current time of update
				$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
					SET user_lastmark = ' . time() . '
					WHERE user_lastmark = 0';
				$db->sql_query($sql);

				// Update the LOG_TABLE phpbb:#42295
				$sql = 'UPDATE ' . LOG_TABLE . '
					SET album_id = forum_id,
						image_id = topic_id
					WHERE log_type = ' . LOG_GALLERY;
				$db->sql_query($sql);
				$sql = 'UPDATE ' . LOG_TABLE . '
					SET forum_id = 0,
						topic_id = 0
					WHERE log_type = ' . LOG_GALLERY;
				$db->sql_query($sql);

			case '0.5.4':
			case '1.0.0-dev':
				$num_comments = 0;
				$sql = 'SELECT SUM(image_comments) comments
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE image_status <> ' . IMAGE_UNAPPROVED;
				$result = $db->sql_query($sql);
				$num_comments = (int) $db->sql_fetchfield('comments');
				$db->sql_freeresult($result);
				set_gallery_config('num_comments', $num_comments, true);

				// Update the config for the statistic on the index
				$sql = 'SELECT a.album_id, u.user_id, u.username, u.user_colour
					FROM ' . GALLERY_ALBUMS_TABLE . ' a
					LEFT JOIN ' . USERS_TABLE . ' u
						ON u.user_id = a.album_user_id
					WHERE a.album_user_id <> 0
						AND a.parent_id = 0
					ORDER BY a.album_id DESC';
				$result = $db->sql_query_limit($sql, 1);
				$newest_pgallery = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				set_gallery_config('newest_pgallery_user_id', (int) $newest_pgallery['user_id']);
				set_gallery_config('newest_pgallery_username', (string) $newest_pgallery['username']);
				set_gallery_config('newest_pgallery_user_colour', (string) $newest_pgallery['user_colour']);
				set_gallery_config('newest_pgallery_album_id', (int) $newest_pgallery['album_id']);

				// Update RRC-Mode to newest RRC-Version
				if (!is_int($gallery_config['rrc_gindex_mode']))
				{
					$rrc_gindex_mode = RRC_MODE_NONE;
					if (in_array($gallery_config['rrc_gindex_mode'], array('recent', '!random', '!comment', 'all', 'both')))
					{
						$rrc_gindex_mode += RRC_MODE_RECENT;
					}
					if (in_array($gallery_config['rrc_gindex_mode'], array('!recent', 'random', '!comment', 'all', 'both')))
					{
						$rrc_gindex_mode += RRC_MODE_RANDOM;
					}
					if (in_array($gallery_config['rrc_gindex_mode'], array('!recent', '!random', 'comment', 'all', 'both')))
					{
						$rrc_gindex_mode += RRC_MODE_COMMENT;
					}
					set_gallery_config('rrc_gindex_mode', $rrc_gindex_mode);
				}
				if (!is_int($gallery_config['rrc_profile_mode']))
				{
					$rrc_profile_mode = RRC_MODE_NONE;
					if (in_array($gallery_config['rrc_profile_mode'], array('recent', '!random', '!comment', 'all', 'both')))
					{
						$rrc_profile_mode += RRC_MODE_RECENT;
					}
					if (in_array($gallery_config['rrc_profile_mode'], array('!recent', 'random', '!comment', 'all', 'both')))
					{
						$rrc_profile_mode += RRC_MODE_RANDOM;
					}
					if (in_array($gallery_config['rrc_profile_mode'], array('!recent', '!random', 'comment', 'all', 'both')))
					{
						$rrc_profile_mode += RRC_MODE_COMMENT;
					}
					set_gallery_config('rrc_profile_mode', $rrc_profile_mode);
				}

				// We moved the permissions management to a new file
				$sql = 'UPDATE ' . MODULES_TABLE . "
					SET module_basename = 'gallery_permissions',
						module_mode = 'manage'
					WHERE module_langname = 'ACP_GALLERY_ALBUM_PERMISSIONS'";
				$db->sql_query($sql);

				set_gallery_config('pgalleries_per_page', 10);
				if (isset($gallery_config['max_pics']))
				{
					set_gallery_config('images_per_album', $gallery_config['max_pics']);
				}

				set_gallery_config('watermark_position', 20);

				// We made some stupid bbcodes
				$sql = 'DELETE FROM ' . BBCODES_TABLE . "
					WHERE bbcode_tag = 'album'
						AND bbcode_id = 0";
				$db->sql_query($sql);

			case '1.0.0-RC1':
				set_gallery_config('rrc_profile_pgalleries', $gallery_config['rrc_gindex_pgalleries']);

			case '1.0.0-RC2':
			case '1.0.0':
			case '1.0.1-dev':
				$sql = 'SELECT image_id, image_name, image_username
					FROM ' . GALLERY_IMAGES_TABLE . "
					WHERE image_name_clean = ''";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$sql_ary = array(
						'image_name_clean'		=> utf8_clean_string($row['image_name']),
						'image_username_clean'	=> utf8_clean_string($row['image_username']),
					);
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE image_id = ' . $row['image_id'];
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);

				$next_update_url = $this->p_master->module_url . "?mode=$mode&amp;sub=update_db&amp;step=4";
			break;

			case '1.0.1':
			case '1.0.2-dev':
				if (!isset($gallery_config['allow_resize_images']))
				{
					if (isset($gallery_config['resize_images']))
					{
						set_gallery_config('allow_resize_images', $gallery_config['resize_images']);
					}
					else
					{
						set_gallery_config('allow_resize_images', 1);
					}
				}
				if (!isset($gallery_config['allow_rotate_images']))
				{
					set_gallery_config('allow_rotate_images', 1);
				}
				$sql = 'DELETE FROM ' . BBCODES_TABLE . "
					WHERE bbcode_tag = '" . $db->sql_escape('album') . "'";
				$db->sql_query($sql);

			case '1.0.2-RC1':
				if ($gallery_config['rrc_gindex_display'] < 128)
				{
					set_gallery_config_count('rrc_gindex_display', 128);
				}
				if ($gallery_config['rrc_profile_display'] < 128)
				{
					set_gallery_config_count('rrc_profile_display', 128);
				}
				if ($gallery_config['album_display'] < 128)
				{
					set_gallery_config_count('album_display', 128);
				}

				$next_update_url = $this->p_master->module_url . "?mode=$mode&amp;sub=update_db&amp;step=4";
			break;
		}

		$next_update_url = (!$next_update_url) ? $this->p_master->module_url . "?mode=$mode&amp;sub=update_db&amp;step=4" : $next_update_url;

		$template->assign_vars(array(
			'BODY'		=> $user->lang['UPDATING_DATA'],
			'L_SUBMIT'	=> $user->lang['NEXT_STEP'],
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $next_update_url,
		));
	}

	/**
	* Remove some old columns, config values, permission-masks
	*/
	function thinout_db_schema($mode, $sub)
	{
		global $user, $template, $db;

		$gallery_config = load_gallery_config();

		$this->page_title = $user->lang['STAGE_UPDATE_DB'];
		$reparse_modules_bbcode = false;

		switch ($gallery_config['phpbb_gallery_version'])
		{
/*			case '0.1.2':
			case '0.1.3':
			case '0.2.0':
			case '0.2.1':
			case '0.2.2':
			case '0.2.3':
			case '0.3.0':
			case '0.3.1':
			case '0.3.2-RC1':
			case '0.3.2-RC2':
			case '0.4.0-RC1':
			case '0.4.0-RC2':
				nv_remove_column(GROUPS_TABLE,			'personal_subalbums');
				nv_remove_column(GROUPS_TABLE,			'allow_personal_albums');
				nv_remove_column(GROUPS_TABLE,			'view_personal_albums');
				nv_remove_column(USERS_TABLE,			'album_id');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_approval');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_order');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_view_level');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_upload_level');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_rate_level');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_comment_level');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_edit_level');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_delete_level');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_view_groups');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_upload_groups');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_rate_groups');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_comment_groups');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_edit_groups');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_delete_groups');
				nv_remove_column(GALLERY_ALBUMS_TABLE,	'album_moderator_groups');

			case '0.4.0-RC3':
			case '0.4.0':*/
			case '0.4.1':
			case '0.5.0':
			case '0.5.1':
			case '0.5.2':
			case '0.5.3':
			case '0.5.4':
			case '1.0.0-dev':
				nv_remove_column(GALLERY_ROLES_TABLE,	'a_moderate');

			case '1.0.0-RC1':
			case '1.0.0-RC2':
			case '1.0.0':
			case '1.0.1-dev':
			case '1.0.1':
			case '1.0.2-dev':
				/* //@todo: Move on bbcode-change or creating all modules */
				$reparse_modules_bbcode = true;
			break;
		}

		// Remove some old configs
		$old_configs = array('gallery_user_images_profil', 'gallery_personal_album_profil');
		$sql = 'DELETE FROM ' . CONFIG_TABLE . '
			WHERE ' . $db->sql_in_set('config_name', $old_configs);
		$db->sql_query($sql);

		$old_gallery_configs = array('user_pics_limit', 'mod_pics_limit', 'fullpic_popup', 'personal_gallery', 'personal_gallery_private', 'personal_gallery_limit', 'personal_gallery_view', 'album_version', 'num_comment', 'max_pics', 'resize_images');
		$sql = 'DELETE FROM ' . GALLERY_CONFIG_TABLE . '
			WHERE ' . $db->sql_in_set('config_name', $old_gallery_configs);
		$db->sql_query($sql);

		// Remove some old p_masks
		$sql = 'SELECT perm_role_id
			FROM ' . GALLERY_PERMISSIONS_TABLE;
		$result = $db->sql_query($sql);

		$p_masks = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$p_masks[] = $row['perm_role_id'];
		}
		$db->sql_freeresult($result);

		$sql = 'DELETE FROM ' . GALLERY_ROLES_TABLE . '
			WHERE ' . $db->sql_in_set('role_id', $p_masks, true, true);
		$db->sql_query($sql);

		if ($reparse_modules_bbcode)
		{
			$next_update_url = $this->p_master->module_url . "?mode=$mode&amp;sub=advanced";
		}
		else
		{
			$next_update_url = $this->p_master->module_url . "?mode=$mode&amp;sub=final";
		}

		$template->assign_vars(array(
			'BODY'		=> $user->lang['UPDATE_DATABASE_SCHEMA'],
			'L_SUBMIT'	=> $user->lang['NEXT_STEP'],
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $next_update_url,
		));
	}

	/**
	* Provide an opportunity to customise some advanced settings during the install
	* in case it is necessary for them to be set to access later
	*/
	function obtain_advanced_settings($mode, $sub)
	{
		global $user, $template, $phpEx, $db;

		$gallery_config = load_gallery_config();

		$create = request_var('create', '');
		if ($create)
		{
			// Add modules
			$choosen_acp_module = request_var('acp_module', 0);
			$choosen_ucp_module = request_var('ucp_module', 0);
			$choosen_log_module = request_var('log_module', 0);

			switch ($gallery_config['phpbb_gallery_version'])
			{
				case '0.1.2':
				case '0.1.3':

				case '0.2.0':
				case '0.2.1':
				case '0.2.2':
				case '0.2.3':

				case '0.3.0':
				case '0.3.1':
				case '0.3.2-RC1':
				case '0.3.2-RC2':

				case '0.4.0-RC1':
				case '0.4.0-RC2':
				case '0.4.0-RC3':
				case '0.4.0':
					trigger_error('VERSION_NOT_SUPPORTED', E_USER_ERROR);
				break;

				case '0.4.1':
					// Logs
					$gallery_log = array('module_basename' => 'logs',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $choosen_log_module,	'module_class' => 'acp',	'module_langname' => 'ACP_GALLERY_LOGS',	'module_mode' => 'gallery',	'module_auth' => 'acl_a_viewlogs');
					add_module($gallery_log);

				case '0.5.0':
				case '0.5.1':
				case '0.5.2':
				case '0.5.3':
				case '0.5.4':

				case '1.0.0-dev':
				case '1.0.0-RC1':
				case '1.0.0-RC2':
				case '1.0.0':

				case '1.0.1-dev':
				case '1.0.1':

				case '1.0.2-dev':
				case '1.0.2-RC1':
					// Add album-BBCode
					add_bbcode('album');
				break;
			}

			$s_hidden_fields = '';
			$url = $this->p_master->module_url . "?mode=$mode&amp;sub=final";
		}
		else
		{
			$data = array(
				'acp_module'		=> MODULE_DEFAULT_ACP,
				'log_module'		=> MODULE_DEFAULT_LOG,
				'ucp_module'		=> MODULE_DEFAULT_UCP,
			);
			$modules = $this->gallery_config_options;
			switch ($gallery_config['phpbb_gallery_version'])
			{
				case '1.0.2-RC1':
				case '1.0.2-dev':
				case '1.0.1':
					$template->assign_block_vars('checks', array(
						'S_LEGEND'			=> true,
						'LEGEND'			=> '',
						'LEGEND_EXPLAIN'	=> $user->lang['BBCODES_NEEDS_REPARSE'],
					));
				case '1.0.1-dev':
				case '1.0.0':
				case '1.0.0-RC2':
				case '1.0.0-RC1':
				case '1.0.0-dev':
				case '0.5.4':
				case '0.5.3':
				case '0.5.2':
				case '0.5.1':
				case '0.5.0':
					// needs to be moved before the first unset.
					unset($modules['legend1']);
					unset($modules['log_module']);
				case '0.4.1':
					unset($modules['acp_module']);
					unset($modules['ucp_module']);
					// We need to build all modules before this version
				break;
			}

			foreach ($modules as $config_key => $vars)
			{
				if (!is_array($vars) && strpos($config_key, 'legend') === false)
				{
					continue;
				}

				if (strpos($config_key, 'legend') !== false)
				{
					$template->assign_block_vars('options', array(
						'S_LEGEND'		=> true,
						'LEGEND'		=> $user->lang[$vars])
					);

					continue;
				}

				$options = isset($vars['options']) ? $vars['options'] : '';
				$template->assign_block_vars('options', array(
					'KEY'			=> $config_key,
					'TITLE'			=> $user->lang[$vars['lang']],
					'S_EXPLAIN'		=> $vars['explain'],
					'S_LEGEND'		=> false,
					'TITLE_EXPLAIN'	=> ($vars['explain']) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '',
					'CONTENT'		=> $this->p_master->input_field($config_key, $vars['type'], $data[$config_key], $options),
					)
				);
			}
			$s_hidden_fields = '<input type="hidden" name="create" value="true" />';
			$url = $this->p_master->module_url . "?mode=$mode&amp;sub=advanced";
		}

		$submit = $user->lang['NEXT_STEP'];

		$template->assign_vars(array(
			'TITLE'		=> $user->lang['STAGE_ADVANCED'],
			'BODY'		=> $user->lang['STAGE_ADVANCED_EXPLAIN'],
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* The information below will be used to build the input fields presented to the user
	*/
	var $gallery_config_options = array(
		'legend1'				=> 'MODULES_PARENT_SELECT',
		'acp_module'			=> array('lang' => 'MODULES_SELECT_4ACP', 'type' => 'select', 'options' => 'module_select(\'acp\', 31, \'ACP_CAT_DOT_MODS\')', 'explain' => false),
		'log_module'			=> array('lang' => 'MODULES_SELECT_4LOG', 'type' => 'select', 'options' => 'module_select(\'acp\', 25, \'ACP_FORUM_LOGS\')', 'explain' => false),
		'ucp_module'			=> array('lang' => 'MODULES_SELECT_4UCP', 'type' => 'select', 'options' => 'module_select(\'ucp\', 0, \'\')', 'explain' => false),
	);
}

?>