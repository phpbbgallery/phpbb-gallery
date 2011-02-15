<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class gallery_phpbb_gallery_user_helpers_test extends gallery_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/gallery_users.xml');
	}

	public static function user_helpers_delete_users_data()
	{
		return array(
			array(2, array(
				array('user_id' => 3),
			)),
			array(array(2), array(
				array('user_id' => 3),
			)),
			array(array(2, 1),  array(
				array('user_id' => 3),
			)),
			array(array(2, 3), array()),
			array('all', array()),
		);
	}

	/**
	* @dataProvider user_helpers_delete_users_data
	*/
	public function test_user_helpers_delete_users($user_ids, $expected)
	{
		global $db;
		$db = $this->new_dbal();

		phpbb_gallery_user_helpers::delete_users($user_ids);
		$result = $db->sql_query('SELECT user_id
			FROM ' . phpbb_gallery_user_base::sql_table());

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}

	public static function user_helpers_update_users_data()
	{
		return array(
			array(2, array('user_images' => 10), array(
				array('user_id' => 2, 'user_images' => 10),
				array('user_id' => 3, 'user_images' => 7),
			)),
			array(array(2), array('user_images' => 10), array(
				array('user_id' => 2, 'user_images' => 10),
				array('user_id' => 3, 'user_images' => 7),
			)),
			array(array(2, 1), array('user_images' => 10), array(
				array('user_id' => 2, 'user_images' => 10),
				array('user_id' => 3, 'user_images' => 7),
			)),
			array(array(2, 3), array('user_images' => 10), array(
				array('user_id' => 2, 'user_images' => 10),
				array('user_id' => 3, 'user_images' => 10),
			)),
			array('all', array('user_images' => 10), array(
				array('user_id' => 2, 'user_images' => 10),
				array('user_id' => 3, 'user_images' => 10),
			)),
		);
	}

	/**
	* @dataProvider user_helpers_update_users_data
	*/
	public function test_user_helpers_update_users($user_ids, $data, $expected)
	{
		global $db;
		$db = $this->new_dbal();

		$this->assertEquals(true, phpbb_gallery_user_helpers::update_users($user_ids, $data));
		$result = $db->sql_query('SELECT user_id, user_images
			FROM ' . phpbb_gallery_user_base::sql_table());

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}
}
