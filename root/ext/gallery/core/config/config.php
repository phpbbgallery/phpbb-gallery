<?php
/**
*
* @package - Toolio Sample
* @copyright (c) 2011 nickvergessen <nickvergessen@gmx.de> http://www.flying-bits.org/
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, v2 or later
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
		global $phpbb_dispatcher;

		$default_sets = array(
			'core',
		);

		$additional_config_sets = array();

		$vars = array('additional_config_sets');
		extract($phpbb_dispatcher->trigger_event('gallery.core.config.load_config_sets', compact($vars)));

		return array_merge($default_sets, $additional_config_sets);
	}
}
