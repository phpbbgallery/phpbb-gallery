<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

// Write your headerlines here
$headerlines = "* @package phpBB Gallery\n";
$headerlines .= "* @version \$Id\$\n";
$headerlines .= "* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org\n";
$headerlines .= "* @license http://opensource.org/licenses/gpl-license.php GNU Public License\n";

/**
* @ignore
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

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
$files = show_dir('v0_5_0/', '', $files);
$files = show_dir('v0_5_1/', '', $files);
$files = show_dir('v0_5_2/', '', $files);
$files = show_dir('v0_5_3/', '', $files);
$files = show_dir('v0_5_4/', '', $files);

$trunk = show_dir('v1_0_0/', '', $trunk);

$files = array_unique($files);
sort ($files);

// Write the file-content
$get_outdated_files = "<?php\n";
$get_outdated_files .= "/**\n";
$get_outdated_files .= "*\n";
$get_outdated_files .= $headerlines;
$get_outdated_files .= "*\n";
$get_outdated_files .= "*/\n";
$get_outdated_files .= "\n";
$get_outdated_files .= "/**\n";
$get_outdated_files .= "* @ignore\n";
$get_outdated_files .= "*/\n";
$get_outdated_files .= "\n";
$get_outdated_files .= "if (!defined('IN_PHPBB'))\n";
$get_outdated_files .= "{\n";
$get_outdated_files .= "	exit;\n";
$get_outdated_files .= "}\n";
$get_outdated_files .= "if (!defined('IN_INSTALL'))\n";
$get_outdated_files .= "{\n";
$get_outdated_files .= "	exit;\n";
$get_outdated_files .= "}\n";
$get_outdated_files .= "\n";
$get_outdated_files .= "\$oudated_files = array(\n";
foreach ($files as $file)
{
	if (!in_array($file, $trunk))
	{
		if ((substr($file, 0, 16) != 'install_gallery/') && (substr($file, 0, 8) != 'install/') && ($file != 'gallery/upload/e9572ef3661a7ae1c35ba09a067e57ae.jpg'))
		{
			$get_outdated_files .= "	'" . $file . "',\n";
		}
	}
}
$get_outdated_files .= ");\n";
$get_outdated_files .= "\n";
$get_outdated_files .= '?' . '>';

// Copied from phpBB writting config.php
// Attempt to write out the config file directly. If it works, this is the easiest way to do it ...
if ((file_exists($phpbb_root_path . 'install/outdated_files.' . $phpEx) && is_writable($phpbb_root_path . 'install/outdated_files.' . $phpEx)) || is_writable($phpbb_root_path . 'install/'))
{
	// Assume it will work ... if nothing goes wrong below
	$written = true;
	if (!($fp = @fopen($phpbb_root_path . 'install/outdated_files.' . $phpEx, 'w')))
	{
		// Something went wrong ... so let's try another method
		$written = false;
	}
	if (!(@fwrite($fp, $get_outdated_files)))
	{
		// Something went wrong ... so let's try another method
		$written = false;
	}
	@fclose($fp);
}

if ($written)
{
	echo 'Finished!';
}
else
{
	echo 'Failed!';
}

?>