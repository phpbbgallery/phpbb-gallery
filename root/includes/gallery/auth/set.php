<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_gallery_auth_set
{
	private $_bits = 0;

	private $_counts = array(
		'i_count'	=> 0,
		'a_count'	=> 0,
	);

	public function phpbb_gallery_auth_set($bits = 0, $i_count = 0, $a_count = 0)
	{
		$this->_bits = $bits;

		$this->_counts = array(
			'i_count'	=> $i_count,
			'a_count'	=> $a_count,
		);
	}

	public function set_bit($bit, $set)
	{
		$this->_bits = phpbb_optionset($bit, $set, $this->_bits);
	}

	public function get_bit($bit)
	{
		return phpbb_optionget($bit, $this->_bits);
	}

	public function get_bits()
	{
		return $this->_bits;
	}

	public function set_count($data, $set)
	{
		$this->_counts[$data] = (int) $set;
	}

	public function get_count($data)
	{
		return (int) $this->_counts[$data];
	}
}
