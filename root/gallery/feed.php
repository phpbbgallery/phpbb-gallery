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

phpbb_gallery::setup(array('mods/gallery'));
phpbb_gallery_url::_include('functions_display', 'phpbb');

if (!phpbb_gallery_config::get('feed_enable'))
{
	trigger_error('NO_FEED_ENABLED');
}

// Initial var setup
$mode		= request_var('mode', '');
$album_id	= request_var('album_id', 0);

$feed = new phpbb_gallery_feed($album_id);

if ($album_id)
{
	$self_link = phpbb_gallery_url::append_sid('full', 'album', 'album_id=' . $album_id);
}
else
{
	$self_link = phpbb_gallery_url::append_sid('full', 'search', 'search_id=recent');
}

$feed->send_header($config['sitename'], $config['site_desc'], $self_link);

$feed->send_images();

$feed->send_footer();
