<?php
/**
*
* @package Toolio - Config ACP Module
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

abstract class phpbb_ext_nickvergessen_toolio_acp_config_base implements phpbb_ext_nickvergessen_toolio_acp_config_interface
{
	public $u_action;

	private $config_ary = array();

	/**
	* Create function for the ACP module
	*
	* @param	int		$id		The ID of the module
	* @param	string	$mode	The name of the mode we want to display
	* @return	void
	*/
	final public function main($id, $mode)
	{
		global $user, $template;

		$this->init($id, $mode);

		$submit = (isset($_POST['submit'])) ? true : false;

		$form_key = 'acp_toolio_config_module';
		add_form_key($form_key);

		$this->config_ary = $this->get_config_array();

		$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->config_ary;
		$error = array();

		// We validate the complete config if whished
		validate_config_vars($this->get_display_vars($mode), $cfg_array, $error);
		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}

		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}

		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		$display_vars = $this->get_display_vars($mode);
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') === 0)
			{
				continue;
			}

			$config_value = $cfg_array[$this->config_key2name($config_name)];

			if ($submit)
			{
				$config_value = $this->check_config($config_name, $config_value, $null);
				if ($config_value === null)
				{
					continue;
				}

				$this->update_config($config_name, $config_value);
			}
		}

		if ($submit)
		{
			$this->submit_finished();
		}

		$this->tpl_name = (isset($display_vars['tpl'])) ? $display_vars['tpl'] : 'acp_board';
		$this->page_title = $display_vars['title'];

		$template->assign_vars(array(
			'L_TITLE'			=> (isset($user->lang[$this->page_title])) ? $user->lang[$this->page_title] : $this->page_title,
			'L_TITLE_EXPLAIN'	=> (isset($user->lang[$this->page_title . '_EXPLAIN'])) ? $user->lang[$this->page_title . '_EXPLAIN'] : '',

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action)
		);

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') !== 0)
			{
				continue;
			}

			if (strpos($config_key, 'legend') === 0)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
				);

				continue;
			}

			if (isset($vars['append']))
			{
				$vars['append'] = (isset($user->lang[$vars['append']])) ? ' ' . $user->lang[$vars['append']] : $vars['append'];
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if (empty($vars['explain']))
			{
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXP'])) ? $user->lang[$vars['lang'] . '_EXP'] : '';
				if ($l_explain != '')
				{
					$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
				}
			}

			$content = build_cfg_template($type, $this->config_key2name($config_key), $this->config_ary, $this->config_key2name($config_key), $vars);

			if (empty($content))
			{
				continue;
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> empty($vars['explain']),
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
			));

			unset($display_vars['vars'][$config_key]);
		}
	}

	/**
	* This function is called, when the main() function is called.
	* You can use this function to add your language files, check for a valid mode, unset config options and more.
	*
	* @param	int		$id		The ID of the module
	* @param	string	$mode	The name of the mode we want to display
	* @return	void
	*/
	public function init($id, $mode)
	{
		// Check whether the mode is allowed.
		if (!isset($this->display_vars[$mode]))
		{
			trigger_error('NO_MODE', E_USER_ERROR);
		}
	}

	/**
	* Overwrite the name of a config.
	*
	* @param	string	$config_name	The name of the config
	* @return	string
	*/
	public function config_key2name($config_name)
	{
		return $config_name;
	}

	/**
	* You can manipulate the value of the config before it's updated
	*
	* @param	string	$config_name	The name of the config
	* @param	mixed	$config_value	The validated value of the config (see 'validate' attribute on display_data array)
	* @param	array	$display_data	The array from this config in the display_vars array
	* @return	mixed		If the return value is null, the value will not be updated anymore, otherwise the value should be returned
	*/
	public function check_config($config_name, $config_value, $display_data)
	{
		return $config_value;
	}

	/**
	* You can manipulate the value of the config before it's updated
	*
	* @param	string	$config_name	The name of the config
	* @param	mixed	$config_value	The value of the config
	* @return	void
	*/
	public function update_config($config_name, $config_value)
	{
		set_config($config_name, $config_value);
	}

	/**
	* You should clear your cache of the config in this function.
	* Afterwards a trigger_error with a backlink should be called.
	*
	* @return	void
	*/
	public function submit_finished()
	{
		global $cache, $user;

		$cache->destroy('sql', CONFIG_TABLE);
		trigger_error($user->lang['SAMPLE_CONFIG_UPDATED'] . adm_back_link($this->u_action));
	}

	/**
	* Returns an array with the current value of the configs
	*
	* @return	array		An array of "name => value" tupels
	*/
	public function get_config_array()
	{
		global $config;

		return $config;
	}

	/**
	* Returns an array with the display_var array for the given mode
	* The returned display must have the two keys title and vars
	*		@key	string	title		The page title or lang key for the page title
	*		@key	array	vars		An array of tupels, one foreach config option we display:
	*					@key		The name of the config in the get_config_array() array.
	*								If the key starts with 'legend' a new box is opened with the value being the title of this box.
	*					@value		An array with several options:
	*						@key lang		Description for the config value (can be a language key)
	*						@key explain	Boolean whether the config has an explanation of not.
	*										If true, <lang>_EXP (and <lang>_EXPLAIN) is displayed as explanation
	*						@key validate	The config value can be validated as bool, int or string.
	*										Additional a min and max value can be specified for integers
	*										On strings the min and max value are the length of the string
	*										If your config value shall not be casted, remove the validate-key.
	*						@key type		The type of the config option:
	*										- Radio buttons:		Either with "Yes and No" (radio:yes_no) or "Enabled and Disabled" (radio:enabled_disabled) as description
	*										- Text/password field:	"text:<field-size>:<text-max-length>" and "password:<field-size>:<text-max-length>"
	*										- Select:				"select" requires the key "function" or "method" to be set which provides the html code for the options
	*										- Custom template:		"custom" requires the key "function" or "method" to be set which provides the html code
	*						@key function/method	Required when using type select and custom
	*						@key append		A language string that is appended after the config type (e.g. You can append 'px' to a pixel size field)
	* This last parameter is optional
	*		@key	string	tpl			Name of the template file we use to display the configs
	*
	* @param	string	$mode	The name of the mode we want to display
	* @return	array		See description above
	*/
	abstract protected function get_display_vars($mode);
}
