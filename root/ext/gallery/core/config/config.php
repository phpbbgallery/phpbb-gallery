<?php
/**
*
* @package Gallery - Config Core
* @copyright (c) 2012 nickvergessen - http://www.flying-bits.org/
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_ext_gallery_core_config extends phpbb_ext_nickvergessen_toolio_config_base
{
	/**
	* Returns a list of all config sets, which should be loaded when the object is constructed
	* The "core" set can be called without specifing the short form on the operations.
	*
	* @return	array		Returns an array with the set names and their short form: "<short> => <class_name>"
	*/
	public function default_sets()
	{
		$default_sets = array(
			'core',
		);

		//@todo: Add hook here, to allow automated loading of plugin sets
		return $default_sets;
	}
}
