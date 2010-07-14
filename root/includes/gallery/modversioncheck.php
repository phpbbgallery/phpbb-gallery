<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 StarTrekGuide
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

class phpbb_gallery_modversioncheck
{
	/**
	* A copy of Handyman` s MOD version check, to view it on the gallery overview
	*/
	static public function check($return_version = false)
	{
		global $user, $template;
		global $phpbb_admin_path, $phpEx;

		if (!function_exists('get_remote_file'))
		{
			global $phpbb_root_path;
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}

		if (!$phpbb_admin_path || !is_dir($phpbb_admin_path))
		{
			global $phpbb_root_path;
			$phpbb_admin_path = $phpbb_root_path . 'adm/';
		}

		// load version files
		$class_functions = array();
		include($phpbb_admin_path . 'mods/phpbb_gallery_version.' . $phpEx);
		$class_name = 'phpbb_gallery_version';

		$var = call_user_func(array($class_name, 'version'));

		// Get current and latest version
		$errstr = '';
		$errno = 0;

		$mod_version = '0.0.0';
		if (!$return_version)
		{
			$mod_version = $user->lang['NO_INFO'];
			$data = array(
				'title'			=> $var['title'],
				'description'	=> $user->lang['NO_INFO'],
				'download'		=> $user->lang['NO_INFO'],
				'announcement'	=> $user->lang['NO_INFO'],
			);
		}
		$file = get_remote_file($var['file'][0], '/' . $var['file'][1], $var['file'][2], $errstr, $errno);

		if ($file)
		{
			// let's not stop the page from loading if a mod author messed up their mod check file
			// also take care of one of the easiest ways to mess up an xml file: "&"
			$mod = @simplexml_load_string(str_replace('&', '&amp;', $file));
			if (isset($mod->$var['tag']))
			{
				$row = $mod->$var['tag'];
				$mod_version = $row->mod_version->major . '.' . $row->mod_version->minor . '.' . $row->mod_version->revision . $row->mod_version->release;

				$data = array(
					'title'			=> $row->title,
					'description'	=> $row->description,
					'download'		=> $row->download,
					'announcement'	=> $row->announcement,
				);
			}
		}

		// remove spaces from the version in the mod file stored locally
		$version = str_replace(' ', '', $var['version']);
		if ($return_version)
		{
			return $mod_version;
		}

		$version_compare = (version_compare($version, $mod_version, '<')) ? false : true;

		$template->assign_block_vars('mods', array(
			'ANNOUNCEMENT'		=> $data['announcement'],
			'AUTHOR'			=> $var['author'],
			'CURRENT_VERSION'	=> $version,
			'DESCRIPTION'		=> $data['description'],
			'DOWNLOAD'			=> $data['download'],
			'LATEST_VERSION'	=> $mod_version,
			'TITLE'				=> $data['title'],

			'UP_TO_DATE'		=> sprintf((!$version_compare) ? $user->lang['NOT_UP_TO_DATE'] : $user->lang['UP_TO_DATE'], $data['title']),

			'S_UP_TO_DATE'		=> $version_compare,

			'U_AUTHOR'			=> 'http://www.phpbb.com/community/memberlist.php?mode=viewprofile&un=' . $var['author'],
		));
	}
}
