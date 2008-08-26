<?php

/**
*
* @package phpBB3 - phpBB Gallery database updater
* @version $Id$
* @copyright (c) 2007 phpBB Gallery
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
if (!defined('IN_PHPBB'))
{
	exit;
}
$mtime = explode(' ', microtime());
$totaltime = $mtime[0] + $mtime[1] - $starttime;
$debug_output = sprintf('Time : %.3fs | ' . $db->sql_num_queries() . ' Queries | GZIP : ' . (($config['gzip_compress']) ? 'On' : 'Off') . (($user->load) ? ' | Load : ' . $user->load : ''), $totaltime);
if (function_exists('memory_get_usage'))
{
	if ($memory_usage = memory_get_usage())
	{
		global $base_memory_usage;
		$memory_usage -= $base_memory_usage;
		$memory_usage = get_formatted_filesize($memory_usage);
		$debug_output .= ' | Memory Usage: ' . $memory_usage;
	}
}
$debug_output .= ' | <a href="' . build_url() . '&amp;explain=1">Explain</a>';

$activemenu = ' id="activemenu"';
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="en-gb" lang="en-gb"><head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
echo '<meta http-equiv="Content-Style-Type" content="text/css">';
echo '<meta http-equiv="Content-Language" content="en-gb">';
echo '<meta http-equiv="imagetoolbar" content="no"><title>' . $page_title . '</title>';
echo '<link href="../adm/style/admin.css" rel="stylesheet" type="text/css" media="screen">';
echo '</head>';
echo '<body class="ltr">';
echo '<div id="wrap">';
echo '	<div id="page-header">';
echo '		<h1>' . $page_title . '</h1>';
echo '		<p><a href="' . $phpbb_root_path . '">' . $user->lang['INDEX'] . '</a> &bull; <a href="' . $phpbb_root_path . GALLERY_ROOT_PATH . '">' . $user->lang['GALLERY'] . '</a></p>';
echo '		<p id="skip"><a href="#acp">Skip to content</a></p>';
echo '	</div>';
echo '	<div id="page-body">';
echo '		<div id="acp">';
echo '		<div class="panel">';
echo '			<span class="corners-top"><span></span></span>';
echo '				<div id="content">';
echo '					<div id="menu">';
echo '						<ul>';
echo '							<li class="header">' . $user->lang['INSTALLER_INSTALL_MENU'] . '</li>';
echo '							<li' . (($mode == 'install') ? $activemenu : '') . '><a href="install.php?mode=install"><span>' . sprintf($user->lang['INSTALLER_INSTALL_VERSION'], $new_mod_version) . '</span></a></li>';
echo '							<li' . (($mode == 'convert') ? $activemenu : '') . '><a href="install.php?mode=convert"><span>' . sprintf($user->lang['INSTALLER_CONVERT_NOTE'], $new_mod_version) . '</span></a></li>';
echo '							<li' . (($mode == 'delete') ? $activemenu : '') . '><a href="install.php?mode=delete"><span>' . $user->lang['INSTALLER_DELETE_NOTE'] . '</span></a></li>';
echo '							<li class="header">' . $user->lang['INSTALLER_UPDATE_MENU'] . ' 0.4.x</li>';
echo '							<li' . (($version == 'svn') ? $activemenu : '') . '><a href="install.php?mode=update&amp;v=svn"><span>' . $user->lang['INSTALLER_UPDATE_VERSION'] . ' SVN</span></a></li>';
echo '							<li' . (($version == '0.4.0-RC2') ? $activemenu : '') . '><a href="install.php?mode=update&amp;v=0.4.0-RC2"><span>' . $user->lang['INSTALLER_UPDATE_VERSION'] . '0.4.0-RC2</span></a></li>';
echo '							<li' . (($version == '0.4.0-RC1') ? $activemenu : '') . '><a href="install.php?mode=update&amp;v=0.4.0-RC1"><span>' . $user->lang['INSTALLER_UPDATE_VERSION'] . '0.4.0-RC1</span></a></li>';
echo '							<li class="header">' . $user->lang['INSTALLER_UPDATE_MENU'] . ' 0.3.x</li>';
echo '							<li' . (($version == '0.3.1') ? $activemenu : '') . '><a href="install.php?mode=update&amp;v=0.3.1"><span>' . $user->lang['INSTALLER_UPDATE_VERSION'] . '0.3.1</span></a></li>';
echo '							<li' . (($version == '0.3.0') ? $activemenu : '') . '><a href="install.php?mode=update&amp;v=0.3.0"><span>' . $user->lang['INSTALLER_UPDATE_VERSION'] . '0.3.0</span></a></li>';
echo '							<li class="header">' . $user->lang['INSTALLER_UPDATE_MENU'] . ' 0.2.x</li>';
echo '							<li' . (($version == '0.2.3') ? $activemenu : '') . '><a href="install.php?mode=update&amp;v=0.2.3"><span>' . $user->lang['INSTALLER_UPDATE_VERSION'] . '0.2.3</span></a></li>';
echo '							<li' . (($version == '0.2.2') ? $activemenu : '') . '><a href="install.php?mode=update&amp;v=0.2.2"><span>' . $user->lang['INSTALLER_UPDATE_VERSION'] . '0.2.2</span></a></li>';
echo '							<li' . (($version == '0.2.1') ? $activemenu : '') . '><a href="install.php?mode=update&amp;v=0.2.1"><span>' . $user->lang['INSTALLER_UPDATE_VERSION'] . '0.2.1</span></a></li>';
echo '							<li' . (($version == '0.2.0') ? $activemenu : '') . '><a href="install.php?mode=update&amp;v=0.2.0"><span>' . $user->lang['INSTALLER_UPDATE_VERSION'] . '0.2.0</span></a></li>';
echo '							<li class="header">' . $user->lang['INSTALLER_UPDATE_MENU'] . ' 0.1.x</li>';
echo '							<li' . (($version == '0.1.3') ? $activemenu : '') . '><a href="install.php?mode=update&amp;v=0.1.3"><span>' . $user->lang['INSTALLER_UPDATE_VERSION'] . '0.1.3</span></a></li>';
echo '							<li' . (($version == '0.1.2') ? $activemenu : '') . '><a href="install.php?mode=update&amp;v=0.1.2"><span>' . $user->lang['INSTALLER_UPDATE_VERSION'] . '0.1.2 - tsr</span></a></li>';
echo '						</ul>';
echo '					</div>';
echo '					<div id="main">';
echo '<a name="maincontent"></a>';
if ($mode == 'install')
{
	if ($install == 1)
	{
		if ($installed)
		{
			echo '<div class="successbox">';
			echo '	<h3>' . $user->lang['INFORMATION'] . '</h3>';
			echo '	<p>' . sprintf($user->lang['INSTALLER_INSTALL_SUCCESSFUL'], $new_mod_version) . '</p>';
			echo '	<p>' . sprintf($user->lang['AFTER_INSTALL_GOTO'], '<a href="' . $phpbb_root_path . GALLERY_ROOT_PATH . '">', '</a>') . '</p>';
			echo '</div>';
		}
		else
		{
			echo '<div class="errorbox">';
			echo '	<h3>' . $user->lang['WARNING'] . '</h3>';
			echo '	<p>' . sprintf($user->lang['INSTALLER_INSTALL_UNSUCCESSFUL'], $new_mod_version) . '</p>';
			echo '</div>';
		}
	}
	else
	{
		echo '<h1>' . $user->lang['INSTALLER_INSTALL_WELCOME'] . '</h1>';
		echo '<p>' . $user->lang['INSTALLER_INSTALL_WELCOME_NOTE'] . '</p>';
		echo '<form id="acp_board" method="post" action="install.php?mode=install">';
		echo '	<fieldset>';
		echo '		<legend>' . $user->lang['INSTALLER_CHMOD'] . '</legend>';
		echo '		<p>' . $user->lang['INSTALLER_CHMOD_EXPLAIN'] . '</p>';
		foreach ($chmod_dirs as $dir)
		{
			echo '		<dl>';
			echo '			<dt><label for="chmod">' . $dir['name'] . ':</label></dt>';
				if ($dir['chmod'])
				{
					echo '			<dd><label><strong style="color:green">' . $user->lang['INSTALLER_CHMOD_WRITABLE'] . '</strong></label></dd>';
				}
				else
				{
					echo '			<dd><label><strong style="color:red">' . $user->lang['INSTALLER_CHMOD_UNWRITABLE'] . '</strong></label></dd>';
				}
			echo '		</dl>';
		}
		echo '	</fieldset>';
		echo '	<fieldset>';
		echo '		<legend>' . $user->lang['MODULES_PARENT_SELECT'] . '</legend>';
		echo '		<dl>';
		echo '			<dt><label for="select_acp_module">' . $user->lang['MODULES_SELECT_4ACP'] . ':</label><br />
						<span>' . $default_acp_module . '</span></dt>';
		echo '			<dd>' . $select_acp_module . '</dd>';
		echo '		</dl>';
		echo '		<dl>';
		echo '			<dt><label for="select_ucp_module">' . $user->lang['MODULES_SELECT_4UCP'] . ':</label><br />
						<span>' . $default_ucp_module . '</span></dt>';
		echo '			<dd>' . $select_ucp_module . '</dd>';
		echo '		</dl>';
		echo '	</fieldset>';
		echo '	<fieldset>';
		echo '		<legend>' . $user->lang['INSTALLER_INSTALL'] . '</legend>';
		echo '		<dl>';
		echo '			<dt><label for="install">v' . $new_mod_version . ':</label></dt>';
		echo '			<dd><label><input name="install" value="1" class="radio" type="radio" />' . $user->lang['YES'] . '</label><label><input name="install" value="0" checked="checked" class="radio" type="radio" />' . $user->lang['NO'] . '</label></dd>';
		echo '		</dl>';
		echo '		<p class="submit-buttons">';
		echo '			<input class="button1" id="submit" name="submit" value="Submit" type="submit" />&nbsp;';
		echo '			<input class="button2" id="reset" name="reset" value="Reset" type="reset" />';
		echo '		</p>';
		echo '	</fieldset>';
		echo '</form>';
	}
}
else if ($mode == 'convert')
{
	if ($convert == 1)
	{
		if ($convert_prefix == '')
		{
			echo '<div class="errorbox">';
			echo '	<h3>' . $user->lang['WARNING'] . '</h3>';
			echo '	<p>' . sprintf($user->lang['INSTALLER_CONVERT_UNSUCCESSFUL2'], $new_mod_version) . '</p>';
			echo '</div>';
		}
		else if ($converted)
		{
			echo '<div class="successbox">';
			echo '	<h3>' . $user->lang['INFORMATION'] . '</h3>';
			echo '	<p>' . sprintf($user->lang['INSTALLER_CONVERT_SUCCESSFUL'], $new_mod_version) . '</p>';
			echo '	<p>' . sprintf($user->lang['AFTER_INSTALL_GOTO'], '<a href="' . $phpbb_root_path . GALLERY_ROOT_PATH . '">', '</a>') . '</p>';
			echo '</div>';
		}
		else
		{
			echo '<div class="errorbox">';
			echo '	<h3>' . $user->lang['WARNING'] . '</h3>';
			echo '	<p>' . sprintf($user->lang['INSTALLER_CONVERT_UNSUCCESSFUL'], $new_mod_version) . '</p>';
			echo '</div>';
		}
	}
	else
	{
		echo '<h1>' . $user->lang['INSTALLER_CONVERT_WELCOME'] . '</h1>';
		echo '<p>' . $user->lang['INSTALLER_CONVERT_WELCOME_NOTE'] . '</p>';
		echo '<form id="acp_board" method="post" action="install.php?mode=convert">';
		echo '	<fieldset>';
		echo '		<legend>' . $user->lang['INSTALLER_CHMOD'] . '</legend>';
		echo '		<p>' . $user->lang['INSTALLER_CHMOD_EXPLAIN'] . '</p>';
		foreach ($chmod_dirs as $dir)
		{
			echo '		<dl>';
			echo '			<dt><label for="chmod">' . $dir['name'] . ':</label></dt>';
				if ($dir['chmod'])
				{
					echo '			<dd><label><strong style="color:green">' . $user->lang['INSTALLER_CHMOD_WRITABLE'] . '</strong></label></dd>';
				}
				else
				{
					echo '			<dd><label><strong style="color:red">' . $user->lang['INSTALLER_CHMOD_UNWRITABLE'] . '</strong></label></dd>';
				}
			echo '		</dl>';
		}
		echo '	</fieldset>';
		echo '	<fieldset>';
		echo '		<legend>' . $user->lang['MODULES_PARENT_SELECT'] . '</legend>';
		echo '		<dl>';
		echo '			<dt><label for="select_acp_module">' . $user->lang['MODULES_SELECT_4ACP'] . ':</label><br />
						<span>' . $default_acp_module . '</span></dt>';
		echo '			<dd>' . $select_acp_module . '</dd>';
		echo '		</dl>';
		echo '		<dl>';
		echo '			<dt><label for="select_ucp_module">' . $user->lang['MODULES_SELECT_4UCP'] . ':</label><br />
						<span>' . $default_ucp_module . '</span></dt>';
		echo '			<dd>' . $select_ucp_module . '</dd>';
		echo '		</dl>';
		echo '	</fieldset>';
		echo '	<fieldset>';
		echo '		<legend>' . $user->lang['INSTALLER_CONVERT'] . '</legend>';
		echo '		<dl>';
		echo '			<dt><label for="convert_prefix">' . $user->lang['INSTALLER_CONVERT_PREFIX'] . ':</label></dt>';
		echo '			<dd><input id="convert_prefix" size="40" maxlength="255" name="convert_prefix" value="" type="text" /></dd>';
		echo '		</dl>';
		echo '		<dl>';
		echo '			<dt><label for="convert">' . sprintf($user->lang['INSTALLER_CONVERT_NOTE'], $new_mod_version) . ':</label></dt>';
		echo '			<dd><label><input name="convert" value="1" class="radio" type="radio" />' . $user->lang['YES'] . '</label><label><input name="convert" value="0" checked="checked" class="radio" type="radio" />' . $user->lang['NO'] . '</label></dd>';
		echo '		</dl>';
		echo '		<p class="submit-buttons">';
		echo '			<input class="button1" id="submit" name="submit" value="Submit" type="submit" />&nbsp;';
		echo '			<input class="button2" id="reset" name="reset" value="Reset" type="reset" />';
		echo '		</p>';
		echo '	</fieldset>';
		echo '</form>';
	}
}
else if ($mode == 'delete')
{
	if ($delete == 1)
	{
		if ($deleted)
		{
			echo '<div class="successbox">';
			echo '	<h3>' . $user->lang['INFORMATION'] . '</h3>';
			echo '	<p>' . $user->lang['INSTALLER_DELETE_SUCCESSFUL'] . '</p>';
			echo '</div>';
		}
		else
		{
			echo '<div class="errorbox">';
			echo '	<h3>' . $user->lang['WARNING'] . '</h3>';
			echo '	<p>' . $user->lang['INSTALLER_DELETE_UNSUCCESSFUL'] . '</p>';
			echo '</div>';
		}
	}
	else
	{
		echo '<h1>' . $user->lang['INSTALLER_DELETE_WELCOME'] . '</h1>';
		echo '<p>' . $user->lang['INSTALLER_DELETE_WELCOME_NOTE'] . '</p>';
		echo '<form id="acp_board" method="post" action="install.php?mode=delete">';
		echo '	<fieldset>';
		echo '		<legend>' . $user->lang['INSTALLER_DELETE'] . '</legend>';
		echo '		<dl>';
		echo '			<dt><label for="bbcode_id">' . $user->lang['INSTALLER_DELETE_BBCODE'] . ':</label></dt>';
		echo '			<dd>' . $select_bbcode . '</dd>';
		echo '		</dl>';
		echo '		<dl>';
		echo '			<dt><label for="delete">' . $user->lang['INSTALLER_DELETE_NOTE'] . ':</label></dt>';
		echo '			<dd><label><input name="delete" value="1" class="radio" type="radio" />' . $user->lang['YES'] . '</label><label><input name="delete" value="0" checked="checked" class="radio" type="radio" />' . $user->lang['NO'] . '</label></dd>';
		echo '		</dl>';
		echo '		<p class="submit-buttons">';
		echo '			<input class="button1" id="submit" name="submit" value="Submit" type="submit" />&nbsp;';
		echo '			<input class="button2" id="reset" name="reset" value="Reset" type="reset" />';
		echo '		</p>';
		echo '	</fieldset>';
		echo '</form>';
	}
}
else if ($mode == 'update')
{
	if ($update == 1)
	{
		if ($updated)
		{
			echo '<div class="successbox">';
			echo '	<h3>' . $user->lang['INFORMATION'] . '</h3>';
			echo '	<p>' . sprintf($user->lang['INSTALLER_UPDATE_SUCCESSFUL'], $version, $new_mod_version) . '</p>';
			echo '	<p>' . sprintf($user->lang['AFTER_INSTALL_GOTO'], '<a href="' . $phpbb_root_path . GALLERY_ROOT_PATH . '">', '</a>') . '</p>';
			echo '</div>';
		}
		else
		{
			echo '<div class="errorbox">';
			echo '	<h3>' . $user->lang['WARNING'] . '</h3>';
			echo '	<p>' . sprintf($user->lang['INSTALLER_UPDATE_UNSUCCESSFUL'], $version, $new_mod_version) . '</p>';
			echo '</div>';
		}
	}
	else
	{
		echo '<h1>' . $user->lang['INSTALLER_UPDATE_WELCOME'] . '</h1>';
		echo '<form id="acp_board" method="post" action="install.php?mode=' . $mode . '&amp;v=' . $version . '">';
		echo '	<fieldset>';
		echo '		<legend>' . $user->lang['INSTALLER_CHMOD'] . '</legend>';
		echo '		<p>' . $user->lang['INSTALLER_CHMOD_EXPLAIN'] . '</p>';
		foreach ($chmod_dirs as $dir)
		{
			echo '		<dl>';
			echo '			<dt><label for="chmod">' . $dir['name'] . ':</label></dt>';
				if ($dir['chmod'])
				{
					echo '			<dd><label><strong style="color:green">' . $user->lang['INSTALLER_CHMOD_WRITABLE'] . '</strong></label></dd>';
				}
				else
				{
					echo '			<dd><label><strong style="color:red">' . $user->lang['INSTALLER_CHMOD_UNWRITABLE'] . '</strong></label></dd>';
				}
			echo '		</dl>';
		}
		echo '	</fieldset>';
		if ($create_new_modules)
		{
			echo '	<fieldset>';
			echo '		<legend>' . $user->lang['MODULES_PARENT_SELECT'] . '</legend>';
			echo '		<dl>';
			echo '			<dt><label for="select_acp_module">' . $user->lang['MODULES_SELECT_4ACP'] . ':</label><br />
							<span>' . $default_acp_module . '</span></dt>';
			echo '			<dd>' . $select_acp_module . '</dd>';
			echo '		</dl>';
			echo '		<dl>';
			echo '			<dt><label for="select_ucp_module">' . $user->lang['MODULES_SELECT_4UCP'] . ':</label><br />
							<span>' . $default_ucp_module . '</span></dt>';
			echo '			<dd>' . $select_ucp_module . '</dd>';
			echo '		</dl>';
			echo '	</fieldset>';
		}
		echo '	<fieldset>';
		echo '		<legend>' . $user->lang['INSTALLER_UPDATE'] . '</legend>';
		echo '		<dl>';
		echo '			<dt><label for="update">' . sprintf($user->lang['INSTALLER_UPDATE_NOTE'], $version, $new_mod_version) . ':</label></dt>';
		echo '			<dd><label><input name="update" value="1" class="radio" type="radio" />' . $user->lang['YES'] . '</label><label><input name="update" value="0" checked="checked" class="radio" type="radio" />' . $user->lang['NO'] . '</label></dd>';
		echo '		</dl>';
		echo '		<p class="submit-buttons">';
		echo '			<input class="button1" id="submit" name="submit" value="Submit" type="submit" />&nbsp;';
		echo '			<input class="button2" id="reset" name="reset" value="Reset" type="reset" />';
		echo '		</p>';
		echo '	</fieldset>';
		echo '</form>';
	}
}
else
{
	echo '<h1>' . $user->lang['INSTALLER_INTRO_WELCOME'] . '</h1>';
	echo '<p>' . $user->lang['INSTALLER_INTRO_WELCOME_NOTE'] . '</p>';
}
echo '						</div>';
echo '					</div>';
echo '				<span class="corners-bottom"><span></span></span>';
echo '			</div>';
echo '		</div>';
echo '	</div>';
echo '	<!--';
echo '		We request you retain the full copyright notice below including the link to www.phpbb.com.';
echo '		This not only gives respect to the large amount of time given freely by the developers';
echo '		but also helps build interest, traffic and use of phpBB. If you (honestly) cannot retain';
echo '		the full copyright we ask you at least leave in place the "Powered by phpBB" line, with';
echo '		"phpBB" linked to www.phpbb.com. If you refuse to include even this then support on our';
echo '		forums may be affected.';
echo '		The phpBB Group : 2006';
echo '	// -->';
echo '<div id="page-footer">Powered by phpBB &copy; 2000, 2002, 2005, 2007 <a href="http://www.phpbb.com/">phpBB Group</a><br />Installer by <a href="http://www.flying-bits.org/">nickvergessen</a><br />' . $debug_output . '</div>';
echo '</div>';
echo '</body>';
echo '</html>';
?>