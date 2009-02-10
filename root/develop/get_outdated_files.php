<?php
$files = $trunk = array();
function show_dir($dir, $pos = '', $files = array())
{
	$handle = @opendir($dir);
	if (is_resource($handle))
	{
		while (($file = readdir($handle)) !== false)
		{
			if (preg_match('~^\.{1,2}$~', $file))
			{
				continue;
			}
 
			if (is_dir($dir.$file))
			{
				$files = show_dir($dir.$file.'/', $dir.$file, $files);
			}
			else
			{
				//echo substr($pos, strpos($pos, '/') + 1) . '/' . $file . '<br>';
				$files[] = substr($pos, strpos($pos, '/') + 1) . '/' . $file;
			}
		}
		closedir($handle);
	}

	return $files;
}

$files = show_dir('v0_1_0/', '', $files);
$files = show_dir('v0_1_1/', '', $files);
$files = show_dir('v0_1_2/', '', $files);
$files = show_dir('v0_1_3/', '', $files);
$files = show_dir('v0_2_0/', '', $files);
$files = show_dir('v0_2_1/', '', $files);
$files = show_dir('v0_2_1b/', '', $files);
$files = show_dir('v0_2_2/', '', $files);
$files = show_dir('v0_2_3/', '', $files);
$files = show_dir('v0_3_0/', '', $files);
$files = show_dir('v0_3_1/', '', $files);
$files = show_dir('v0_4_0/', '', $files);
$files = show_dir('v0_4_0-RC1/', '', $files);
$files = show_dir('v0_4_0-RC2/', '', $files);
$files = show_dir('v0_4_0-RC3/', '', $files);
$files = show_dir('v0_4_1/', '', $files);

$trunk = show_dir('v0_5_0/', '', $trunk);

$files = array_unique($files);
sort ($files);
echo '&lt;?php<br />';
echo '/**<br />';
echo '*<br />';
echo '* @package phpBB Gallery<br />';
echo '* @version $Id$<br />';
echo '* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org<br />';
echo '* @license http://opensource.org/licenses/gpl-license.php GNU Public License<br />';
echo '*<br />';
echo '*/<br />';
echo '<br />';
echo '/**<br />';
echo '* @ignore<br />';
echo '*/<br />';
echo '<br />';
echo 'if (!defined(\'IN_PHPBB\'))<br />';
echo '{<br />';
echo '&nbsp;&nbsp;&nbsp;&nbsp;exit;<br />';
echo '}<br />';
echo 'if (!defined(\'IN_INSTALL\'))<br />';
echo '{<br />';
echo '&nbsp;&nbsp;&nbsp;&nbsp;exit;<br />';
echo '}<br />';
echo '<br />';
echo '$oudated_files = array(<br />';
foreach ($files as $file)
{
	if (!in_array($file, $trunk))
	{
		if ((substr($file, 0, 16) != 'install_gallery/') && (substr($file, 0, 8) != 'install/') && ($file != 'gallery/upload/e9572ef3661a7ae1c35ba09a067e57ae.jpg'))
		{
			echo '&nbsp;&nbsp;&nbsp;&nbsp;\'' . $file . '\',<br />';
		}
	}
}
echo ');<br />';
echo '<br />';
echo '?>';

?>