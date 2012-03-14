<?php
/**
*
* @package Language File Conflict Detector
* @version $Id$
* @copyright (c) 2012 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class lfcd
{
	/**
	* Constant for "fail" error
	*/
	const ERROR_FAIL = 1;

	/**
	* Constant for warnings
	*/
	const ERROR_WARNING = 2;

	/**
	* Constant for notices
	*/
	const ERROR_NOTICE = 3;

	/**
	* Constant for information notices
	*/
	const ERROR_INFO = 4;

	/**
	* Constant for permission errors
	*/
	const ERROR_PERMISSION = 5;

	/**
	* Output validation report as plain text
	*/
	const OUTPUT_TEXT = 0;

	/*
	* Output validation report as BBcode
	*/
	const OUTPUT_BBCODE = 1;

	/**
	* Output validation report as HTML
	*/
	const OUTPUT_HTML = 2;

	/**
	* The language we want to check
	*/
	private $language = '';

	/**
	* The MOD files we want to check
	*/
	private $mod_files = array();

	/**
	* All phpBB files
	*/
	private $phpbb_files = array(
		'acp/attachments',
		'acp/ban',
		'acp/board',
		'acp/bots',
		'acp/common',
		'acp/database',
		'acp/email',
		'acp/forums',
		'acp/groups',
		'acp/language',
		'acp/mods',
		'acp/modules',
		'acp/permissions',
		'acp/permissions_phpbb',
		'acp/posting',
		'acp/profile',
		'acp/prune',
		'acp/search',
		'acp/styles',
		'acp/users',
		'captcha_qa',
		'captcha_recaptcha',
		'common',
		'groups',
		'install',
		'mcp',
		'memberlist',
		'posting',
		'search',
		'ucp',
		'viewforum',
		'viewtopic',
	);

	/**
	* The phpBB files we ignore
	*/
	private $ignore_phpbb_files = array(
		'help_bbcode',
		'help_faq',
		'search_ignore_words',
		'search_synonyms',
	);

	/**
	* Errors which were encountered during testing
	*/
	private $errors;
	private $error_files;

	/**
	* Our pre-formatted PM content
	*/
	private $message;

	public function __construct($ignore_phpbb_files = null, $language = 'en')
	{
		if ($ignore_phpbb_files != null)
		{
			if (is_array($ignore_phpbb_files))
			{
				$this->ignore_phpbb_files = $ignore_phpbb_files;
			}
			else
			{
				$this->ignore_phpbb_files = array();
			}
		}

		$this->language = $language;
		$this->errors = array();
		$this->error_files = array();
		$this->message = '';

		global $phpEx, $lfcd_lang;
		include('lang.' . $phpEx);
	}

	public function validate($mod_files = array())
	{
		global $phpbb_root_path, $phpEx, $lfcd_lang;

		$this->mod_files = $mod_files;
		$this->errors = array();
		$this->error_files = array();
		$this->message = '';

		if (!file_exists($phpbb_root_path . 'language/' . $this->language . '/'))
		{
			//echo '<h3>Error: language-package "' . $this->language . '" could not be found!</h3>';
			continue;
		}

		foreach ($this->mod_files as $mod_file)
		{
			include($phpbb_root_path . 'language/' . $this->language . '/' . $mod_file . '.' . $phpEx);
			$mod_lang = $lang;
			$lang = null;

			foreach ($this->phpbb_files as $phpbb_file)
			{
				if (in_array($phpbb_file, $this->ignore_phpbb_files))
				{
					continue;
				}

				include($phpbb_root_path . 'language/' . $this->language . '/' . $phpbb_file . '.' . $phpEx);
				$phpbb_lang = $lang;
				$lang = null;

				$conflicting_keys = array_keys(array_intersect_key($mod_lang, $phpbb_lang));
				if (!empty($conflicting_keys))
				{
					foreach ($conflicting_keys as $conflict)
					{
						$this->push_error($mod_file, $phpbb_file, $conflict, $mod_lang, $phpbb_lang);
					}
				}
			}
		}
	}

	private function push_error($mod_file, $phpbb_file, $conflict, $mod_lang, $phpbb_lang)
	{
		global $lfcd_lang;
		$type = $this->get_error_type($mod_file, $phpbb_file);

		switch ($type)
		{
			case self::ERROR_FAIL:
				$this->message .= '[color=red][ [b]' . $lfcd_lang['LFCD_FAIL_RESULT'] . '[/b] ][/color] ';
			break;

			case self::ERROR_NOTICE:
				$this->message .= '[color=blue][ [b]' . $lfcd_lang['LFCD_NOTICE_RESULT'] . '[/b] ][/color] ';
			break;

			case self::ERROR_WARNING:
				$this->message .= '[color=orange][ [b]' . $lfcd_lang['LFCD_WARNING_RESULT'] . '[/b] ][/color] ';
			break;

			case self::ERROR_INFO:
				$this->message .= '[color=purple][ [b]' . $lfcd_lang['LFCD_INFO_RESULT'] . '[/b] ][/color] ';
			break;

			case self::ERROR_PERMISSION:
				$this->message .= '[color=#008080][ [b]' . $lfcd_lang['LFCD_PERMISSION_RESULT'] . '[/b] ][/color] ' . $lfcd_lang['LFCD_PERMISSION_MESSAGE'];
			break;

			default:
				return;
				//$this->message .= '[color=orange][ [b]' . $lfcd_lang['LFCD_WARNING_RESULT'] . '[/b] ][/color] [b]' . $lfcd_lang['LFCD_INVALID_TYPE'] . "\n";
				//$this->message .= '[color=purple][ [b]' . $lfcd_lang['LFCD_INFO_RESULT'] . '[/b] ][/color] ' . $message . "\n";
		}

		if ($type != self::ERROR_PERMISSION)
		{
			$this->message .= sprintf(
				$lfcd_lang['LFCD_CONFLICT'],
				$mod_file,
				$phpbb_file,
				$conflict,
				self::to_string($mod_lang[$conflict]),
				self::to_string($phpbb_lang[$conflict])
			) . "\n\n";

			$this->errors[$type][] = array(
				'mod_file'		=> $mod_file,
				'phpbb_file'	=> $phpbb_file,
				'conflict'		=> $conflict,
				'mod_lang'		=> $mod_lang,
				'phpbb_lang'	=> $phpbb_lang,
			);
		}

		$this->error_files[$mod_file][$type][] = array(
			'mod_file'		=> $mod_file,
			'phpbb_file'	=> $phpbb_file,
			'conflict'		=> $conflict,
			'mod_lang'		=> $mod_lang,
			'phpbb_lang'	=> $phpbb_lang,
		);
	}

	public function get_report($output = self::OUTPUT_HTML)
	{
		global $lfcd_lang;
		$return = sprintf($lfcd_lang['LFCD_TESTING_FILES'], implode(",\n", $this->mod_files)) . "\n\n ";
		$return .= $lfcd_lang['LFCD_STATISTIC'] . "\n";

		foreach ($this->error_files as $file => $errors)
		{
			if (!isset($errors[self::ERROR_PERMISSION]))
			{
				$return .= sprintf(
					$lfcd_lang['LFCD_STATISTIC_FILE'],
					$file,
					(isset($errors[self::ERROR_FAIL]) ? sizeof($errors[self::ERROR_FAIL]) : '[/color][color=black]0'),
					(isset($errors[self::ERROR_WARNING]) ? sizeof($errors[self::ERROR_WARNING]) : '[/color][color=black]0'),
					(isset($errors[self::ERROR_NOTICE]) ? sizeof($errors[self::ERROR_NOTICE]) : '[/color][color=black]0'),
					(isset($errors[self::ERROR_INFO]) ? sizeof($errors[self::ERROR_INFO]) : '[/color][color=black]0')
				) . "\n";
			}
			else
			{
				$return .= sprintf($lfcd_lang['LFCD_STATISTIC_PERMISSION'], $file) . "\n";
			}
		}
		$return .= "\n\n";
		$return .= $lfcd_lang['LFCD_CONFLICTS'] . "\n";

		switch ($output)
		{
			case self::OUTPUT_BBCODE:
				$text = htmlspecialchars($return . $this->message);
				return self::generate_text_for_html_display($text, true);

			case self::OUTPUT_HTML:
				$text = htmlspecialchars($return . $this->message);
				return self::generate_text_for_html_display($text);

			case self::OUTPUT_TEXT:
				$text = htmlspecialchars($return . $this->message);
				$text = self::generate_text_for_html_display($text);
				//$text = htmlspecialchars_decode(strip_tags(str_replace('<br />', "\n", $text)));
				$text = str_replace("\n\n", "\n", $text);
				$text = str_replace("\n", PHP_EOL, $text);
				return $text;
		}
	}

	private function get_error_type($mod_file, $phpbb_file)
	{
		if (strpos($mod_file, 'permission') !== false && strpos($phpbb_file, 'permission') !== false)
		{
			return self::ERROR_PERMISSION;
		}

		if ((strpos($mod_file, 'mods/info_acp_') === 0 && strpos($phpbb_file, 'acp/') === 0) ||
			(strpos($mod_file, 'mods/info_mcp_') === 0 && $phpbb_file == 'mcp') ||
			(strpos($mod_file, 'mods/info_ucp_') === 0 && $phpbb_file == 'ucp') ||
			(strpos($mod_file, 'acp') !== false && $phpbb_file == 'acp/common') ||
			($phpbb_file == 'common'))
		{
			return self::ERROR_FAIL;
		}

		if ((strpos($mod_file, 'acp') === false && strpos($phpbb_file, 'acp/') === 0))
		{
			return self::ERROR_NOTICE;
		}

		if (strpos($mod_file, 'install') || strpos($phpbb_file, 'install'))
		{
			return self::ERROR_INFO;
		}

		return self::ERROR_WARNING;
	}

	/**
	* Display validation results as HTML
	*/
	static public function generate_text_for_html_display($text, $soft = false)
	{
		//Replace new
		$text = str_replace("\n", "<br />\n", $text);
		if ($soft)
		{
			return $text;
		}

		//BBCode replacement array
		$bbcode = array(
			"/\[b\](.*?)\[\/b\]/is" => '<span style="font-weight:bold;">$1</span>',
			"/\[u\](.*?)\[\/u\]/is" => '<span style="text-decoration:underline;">$1</span>',
			"/\[color\=(.*?)\](.*?)\[\/color\]/is" => '<span style="color:$1;">$2</span>',
			"/\[code\](.*?)\[\/code\]/is" => '<pre style="padding-left:20px;">$1</pre>',
			'#\[url(=(.*))?\](.*)\[/url\]#iUe' => "validate_url('\$2', '\$3')",
			"/\[size\=(.*?)\](.*?)\[\/size\]/is" => '<span style="font-size: $1%;">$2</span>',
		);

		//Replace BBCode
		$text = preg_replace(array_keys($bbcode), array_values($bbcode), $text);

		return $text;
	}

	/**
	* Convert the language entry to a beautiful string
	*/
	static public function to_string($mixed, $depth = 0)
	{
		if (!is_array($mixed))
		{
			return /*str_repeat("\t", $depth) . */$mixed . "\n";
		}
		$return = str_repeat("\t", $depth) . "Array(\n";
		foreach ($mixed as $key => $value)
		{
			$return .= str_repeat("\t", $depth + 1) . $key . ' => ' . self::to_string($value, $depth + 1);
		}
		return $return . str_repeat("\t", $depth) . ")\n";
	}
}
