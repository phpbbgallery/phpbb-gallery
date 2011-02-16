<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../test_framework/gallery_database_test_case.php';

class gallery_phpbb_gallery_user_base_test extends gallery_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/gallery_users.xml');
	}

	public static function user_base_validate_data_data()
	{
		return array(
			array(array('user_id' => 1), false, array('user_id' => 1)),
			array(array('user_id' => false), false, array('user_id' => 0)),
			array(array('user_id' => 'foobar'), false, array('user_id' => 0)),
			array(array('user_images' => 1), false, array('user_images' => 1)),
			array(array('user_images' => -1), false, array('user_images' => 0)),
			array(array('user_images' => true), false, array('user_images' => 1)),
			array(array('user_images' => 'foobar'), false, array('user_images' => 0)),
			array(array('user_permissions' => 'foobar'), false, array('user_permissions' => 'foobar')),
			array(array('user_viewexif' => true), false, array('user_viewexif' => true)),
			array(array('user_viewexif' => 1), false, array('user_viewexif' => true)),
			array(array('user_viewexif' => 'foobar'), false, array('user_viewexif' => true)),
			array(array('does_not_exist' => false), false, array()),
			array(array('does_not_exist' => 1), false, array()),
			array(array('does_not_exist' => 'foobar'), false, array()),

			array(array('user_images' => 1), true, array('user_images' => 1)),
			array(array('user_images' => -1), true, array('user_images' => -1)),
		);
	}

	/**
	* @dataProvider user_base_validate_data_data
	*/
	public function test_user_base_validate_data($data, $allow_lower_0, $expected)
	{
		$this->assertEquals($expected, phpbb_gallery_user_base::validate_data($data, $allow_lower_0));
	}
}
