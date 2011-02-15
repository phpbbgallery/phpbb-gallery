<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

abstract class gallery_database_test_case extends phpbb_database_test_case
{
	protected function load_schema($pdo, $config, $dbms)
	{
		parent::load_schema($pdo, $config, $dbms);

		if ($config['dbms'] == 'mysql')
		{
			$sth = $pdo->query('SELECT VERSION() AS version');
			$row = $sth->fetch(PDO::FETCH_ASSOC);

			if (version_compare($row['version'], '4.1.3', '>='))
			{
				$dbms['SCHEMA'] .= '_41';
			}
			else
			{
				$dbms['SCHEMA'] .= '_40';
			}
		}

		$sql = $this->split_sql_file(file_get_contents(dirname(__FILE__) . "/../gallery/fixtures/schemas/phpbb_gallery_users/_{$dbms['SCHEMA']}_schema.sql"), $config['dbms']);

		foreach ($sql as $query)
		{
			$pdo->exec($query);
		}
	}
}
