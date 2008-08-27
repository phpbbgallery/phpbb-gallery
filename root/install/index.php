<?php

/**
*
* @package NV Install
* @version $Id$
* @copyright (c) 2008 nickvergessen http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/

define('IN_PHPBB', true);
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'install/common.' . $phpEx);

$template->assign_vars(array(
	'S_IN_INTRO'			=> true,
));

page_header($page_title);

$template->set_filenames(array(
	'body' => 'index_body.html')
);

page_footer();

?>