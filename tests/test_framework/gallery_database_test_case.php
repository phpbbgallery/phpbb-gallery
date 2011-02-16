<?php
/**
*
* @package phpBB Gallery testing
* @copyright (c) 2011 nickvergessen
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/gallery_database_test_connection_manager.php';

abstract class gallery_database_test_case extends phpbb_database_test_case
{
	protected function create_connection_manager($config)
	{
		return new gallery_database_test_connection_manager($config);
	}
}
