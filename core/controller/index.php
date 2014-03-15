<?php

/**
*
* @package NV Newspage Extension
* @copyright (c) 2014 nickvergessen
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbgallery\core\controller;

class index
{
	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\db\driver\driver */
	protected $db;

	/* @var \phpbb\request\request */
	protected $request;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var string */
	protected $root_path;

	/* @var string */
	protected $php_ext;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth			$auth		Auth object
	* @param \phpbb\config\config		$config		Config object
	* @param \phpbb\db\driver\driver	$db			Database object
	* @param \phpbb\request\request		$request	Request object
	* @param \phpbb\template\template	$template	Template object
	* @param \phpbb\user				$user		User object
	* @param \phpbb\controller\helper	$helper		Controller helper object
	* @param string						$root_path	Root path
	* @param string						$php_ext	php file extension
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\controller\helper $helper, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Newspage controller to display multiple news
	* @return Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function base()
	{
		$this->display_legend();

		$this->display_brithdays();

		return $this->helper->render('gallery/index_body.html');
	}

	public function display_legend()
	{
		$order_legend = ($this->config['legend_sort_groupname']) ? 'group_name' : 'group_legend';

		// Grab group details for legend display
		if ($this->auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel'))
		{
			$sql = 'SELECT group_id, group_name, group_colour, group_type, group_legend
				FROM ' . GROUPS_TABLE . '
				WHERE group_legend > 0
				ORDER BY ' . $order_legend . ' ASC';
		}
		else
		{
			$sql = 'SELECT g.group_id, g.group_name, g.group_colour, g.group_type, g.group_legend
				FROM ' . GROUPS_TABLE . ' g
				LEFT JOIN ' . USER_GROUP_TABLE . ' ug
					ON (
						g.group_id = ug.group_id
						AND ug.user_id = ' . $this->user->data['user_id'] . '
						AND ug.user_pending = 0
					)
				WHERE g.group_legend > 0
					AND (g.group_type <> ' . GROUP_HIDDEN . ' OR ug.user_id = ' . $this->user->data['user_id'] . ')
				ORDER BY g.' . $order_legend . ' ASC';
		}
		$result = $this->db->sql_query($sql);

		$legend = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$colour_text = ($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . '"' : '';
			$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $this->user->lang['G_' . $row['group_name']] : $row['group_name'];

			if ($row['group_name'] == 'BOTS' || ($this->user->data['user_id'] != ANONYMOUS && !$this->auth->acl_get('u_viewprofile')))
			{
				$legend[] = '<span' . $colour_text . '>' . $group_name . '</span>';
			}
			else
			{
				$legend[] = '<a' . $colour_text . ' href="' . append_sid($this->root_path . 'memberlist.' . $this->php_ext, 'mode=group&amp;g=' . $row['group_id']) . '">' . $group_name . '</a>';
			}
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars(array(
			'LEGEND'	=> implode($this->user->lang['COMMA_SEPARATOR'], $legend),
		));
	}

	public function display_brithdays()
	{
		// Generate birthday list if required ...
		if ($this->config['load_birthdays'] && $this->config['allow_birthdays'] && $this->config['phpbb_gallery_disp_birthdays'] && $this->auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel'))
		{
			$this->template->assign_vars(array(
				'S_DISPLAY_BIRTHDAY_LIST'	=> true,
			));

			$time = $this->user->create_datetime();
			$now = phpbb_gmgetdate($time->getTimestamp() + $time->getOffset());

			// Display birthdays of 29th february on 28th february in non-leap-years
			$leap_year_birthdays = '';
			if ($now['mday'] == 28 && $now['mon'] == 2 && !$time->format('L'))
			{
				$leap_year_birthdays = " OR u.user_birthday LIKE '" . $this->db->sql_escape(sprintf('%2d-%2d-', 29, 2)) . "%'";
			}

			$sql = 'SELECT u.user_id, u.username, u.user_colour, u.user_birthday
				FROM ' . USERS_TABLE . ' u
				LEFT JOIN ' . BANLIST_TABLE . " b ON (u.user_id = b.ban_userid)
				WHERE (b.ban_id IS NULL
					OR b.ban_exclude = 1)
					AND (u.user_birthday LIKE '" . $this->db->sql_escape(sprintf('%2d-%2d-', $now['mday'], $now['mon'])) . "%' $leap_year_birthdays)
					AND u.user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$birthday_username	= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
				$birthday_year		= (int) substr($row['user_birthday'], -4);
				$birthday_age		= ($birthday_year) ? max(0, $now['year'] - $birthday_year) : '';

				$this->template->assign_block_vars('birthdays', array(
					'USERNAME'	=> $birthday_username,
					'AGE'		=> $birthday_age,
				));
			}
			$this->db->sql_freeresult($result);
		}
	}
}
