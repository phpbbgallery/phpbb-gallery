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
$gallery_root_path = GALLERY_ROOT_PATH;

function build_gallery_mcp_navigation ($album_id, $mode, $option_id = false)
{
	global $user, $template, $phpbb_root_path, $gallery_root_path, $phpEx;

	$mode_s = $mode;
	$row_count = 0;
	$nav_tabs = array(
		'album'			=> array('name' => 'GALLERY_MCP_MAIN',		'mode' => 'album',				'mode_s' => 'album'),
		'report'		=> array('name' => 'GALLERY_MCP_REPORTED',	'mode' => 'report_open',		'mode_s' => 'report'),
		'queue'			=> array('name' => 'GALLERY_MCP_QUEUE',		'mode' => 'queue_unapproved',	'mode_s' => 'queue'),
	);
	$nav_subsections = array(
		'album'		=> array(
			//array('name' => 'GALLERY_MCP_OVERVIEW', 'mode' => 'overview'),
			array('name' => 'GALLERY_MCP_VIEWALBUM', 'mode' => 'album'),
			),
		'report'		=> array(
			array('name' => 'GALLERY_MCP_REPO_OPEN', 'mode' => 'report_open'),
			array('name' => 'GALLERY_MCP_REPO_DONE', 'mode' => 'report_closed'),
			),
		'queue'		=> array(
			array('name' => 'GALLERY_MCP_UNAPPROVED', 'mode' => 'queue_unapproved'),
			array('name' => 'GALLERY_MCP_APPROVED', 'mode' => 'queue_approved'),
			array('name' => 'GALLERY_MCP_LOCKED', 'mode' => 'queue_locked'),
			),
	);
	if ($mode == 'queue_details')
	{
		$nav_subsections['queue'][] = array('name' => 'GALLERY_MCP_QUEUE_DETAIL', 'mode' => 'queue_details');
	}
	if ($mode == 'report_details')
	{
		$nav_subsections['report'][] = array('name' => 'GALLERY_MCP_REPO_DETAIL', 'mode' => 'report_details');
	}
	// Hide tabs if permissions are denied
	if (!gallery_acl_check('m_report', $album_id))
	{
		unset($nav_tabs['report']);
	}
	if (!gallery_acl_check('m_status', $album_id))
	{
		unset($nav_tabs['queue']);
	}
	foreach ($nav_tabs as $navtab)
	{
		$template->assign_block_vars('tabs', array(
			'TAB_ACTIVE'	=> (strrpos(substr($mode, 0, 5), substr($navtab['mode_s'], 0, 5)) !== false) ? true : false,
			'TAB_NAME'		=> $user->lang[$navtab['name']],
			'U_TAB'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx" , 'mode=' .  $navtab['mode'] . '&amp;album_id=' . $album_id),
		));
		if (strrpos(substr($mode, 0, 5), substr($navtab['mode_s'], 0, 5)) !== false)
		{
			$mode_s = $navtab['mode_s'];
			foreach ($nav_subsections[$mode_s] as $navsubsection)
			{
				$template->assign_block_vars('tabs.modes', array(
					'MODE_ACTIVE'		=> ($navsubsection['mode'] == $mode) ? true : false,
					'MODE_NAME'			=> $user->lang[$navsubsection['name']],
					'U_MODE'			=> append_sid("{$phpbb_root_path}{$gallery_root_path}mcp.$phpEx" , 'mode=' .  $navsubsection['mode'] . '&amp;album_id=' . $album_id . (($option_id && (($navsubsection['mode'] == 'report_details') || ($navsubsection['mode'] == 'queue_details'))) ? '&amp;option_id=' . $option_id : '')),
				));
				if ($navsubsection['mode'] == $mode)
				{
					$page_title = $user->lang[$navsubsection['name']];
					$template->assign_vars(array(
						'S_' . $navsubsection['name']	=> true,
						'SUBSECTION'					=> $page_title,
					));
				}
			}
		}
	}

	return $page_title;
}

?>