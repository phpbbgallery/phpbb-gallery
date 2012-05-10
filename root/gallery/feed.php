<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2011 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/

define('IN_PHPBB', true);
define('IN_FEED_GALLERY', true);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include('common.' . $phpEx);
include($phpbb_root_path . 'common.' . $phpEx);

$phpbb_ext_gallery = new phpbb_ext_gallery_core($auth, $cache, $config, $db, $template, $user, $phpEx, $phpbb_root_path);
$phpbb_ext_gallery->setup();
$phpbb_ext_gallery->url->_include('functions_phpbb', 'ext');
$phpbb_ext_gallery->url->_include('functions_display', 'phpbb');

if (!$phpbb_ext_gallery->config->get('feed_enable'))
{
	trigger_error('NO_FEED_ENABLED');
}

// Initial var setup
$mode		= request_var('mode', '');
$album_id	= request_var('album_id', 0);

$feed = new phpbb_ext_gallery_core_feed($album_id);

if ($album_id)
{
	$back_link = $phpbb_ext_gallery->url->append_sid('full', 'album', 'album_id=' . $album_id);
	$self_link = $phpbb_ext_gallery->url->append_sid('full', 'feed', 'album_id=' . $album_id);
}
else
{
	$back_link = $phpbb_ext_gallery->url->append_sid('full', 'search', 'search_id=recent');
	$self_link = $phpbb_ext_gallery->url->append_sid('full', 'feed');
}

$feed->send_header($config['sitename'], $config['site_desc'], $self_link, $back_link);

$feed->send_images();

$feed->send_footer();
