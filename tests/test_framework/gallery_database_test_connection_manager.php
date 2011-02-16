<?php
/**
*
* @package phpBB Gallery testing
* @copyright (c) 2011 nickvergessen
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class gallery_database_test_connection_manager extends phpbb_database_test_connection_manager
{
	/**
	* Load the phpBB & gallery database schema into the database
	*/
	public function load_schema()
	{
		$this->ensure_connected(__METHOD__);

		$directory = dirname(__FILE__) . '/../../phpBB/install/schemas/';
		$this->load_schema_from_file($directory);

		$schema_dir = dirname(__FILE__) . '/../gallery/fixtures/schemas/';
		$handle = @opendir($schema_dir);

		if (is_resource($handle))
		{
			// Load all schemas from the gallery
			while (($dir = readdir($handle)) !== false)
			{
				if (preg_match('~^\.{1,2}$~', $dir) || !is_dir(dirname(__FILE__) . '/../gallery/fixtures/schemas/' . $dir))
				{
					continue;
				}

				$this->load_schema_from_file(dirname(__FILE__) . '/../gallery/fixtures/schemas/' . $dir . '/_');
			}
			closedir($handle);
		}
	}
}
