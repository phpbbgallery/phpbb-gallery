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

/**
* 
*/
function add_user_to_user_cache(&$user_cache, $row)
{
	global $auth, $config, $user;

	$user_id = $row['user_id'];

	if ($user_id == ANONYMOUS)
	{
		$user_cache[$user_id] = array(
			'joined'		=> '',
			'posts'			=> '',
			'from'			=> '',

			'sig'					=> '',
			'sig_bbcode_uid'		=> '',
			'sig_bbcode_bitfield'	=> '',

			'online'			=> false,
			'avatar'			=> ($user->optionget('viewavatars')) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']) : '',
			'rank_title'		=> '',
			'rank_image'		=> '',
			'rank_image_src'	=> '',
			'sig'				=> '',
			'profile'			=> '',
			'pm'				=> '',
			'email'				=> '',
			'www'				=> '',
			'icq_status_img'	=> '',
			'icq'				=> '',
			'aim'				=> '',
			'msn'				=> '',
			'yim'				=> '',
			'jabber'			=> '',
			'search'			=> '',
			'age'				=> '',

			'gallery_album'		=> '',
			'gallery_images'	=> '',
			'gallery_search'	=> '',


			'username'			=> $row['username'],
			'user_colour'		=> $row['user_colour'],

			'warnings'			=> 0,
			'allow_pm'			=> 0,
		);

		get_user_rank($row['user_rank'], false, $user_cache[$user_id]['rank_title'], $user_cache[$user_id]['rank_image'], $user_cache[$user_id]['rank_image_src']);
	}
	else
	{
		$user_sig = '';
		if ($row['user_sig'] && $config['allow_sig'] && $user->optionget('viewsigs'))
		{
			$user_sig = $row['user_sig'];
		}

		$id_cache[] = $user_id;

		$user_cache[$user_id] = array(
			'joined'		=> $user->format_date($row['user_regdate']),
			'posts'			=> $row['user_posts'],
			'warnings'		=> (isset($row['user_warnings'])) ? $row['user_warnings'] : 0,
			'from'			=> (!empty($row['user_from'])) ? $row['user_from'] : '',

			'sig'					=> $user_sig,
			'sig_bbcode_uid'		=> (!empty($row['user_sig_bbcode_uid'])) ? $row['user_sig_bbcode_uid'] : '',
			'sig_bbcode_bitfield'	=> (!empty($row['user_sig_bbcode_bitfield'])) ? $row['user_sig_bbcode_bitfield'] : '',

			'viewonline'	=> $row['user_allow_viewonline'],
			'allow_pm'		=> $row['user_allow_pm'],

			'avatar'		=> ($user->optionget('viewavatars')) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']) : '',
			'age'			=> '',

			'rank_title'		=> '',
			'rank_image'		=> '',
			'rank_image_src'	=> '',

			'user_id'			=> $row['user_id'],
			'username'			=> $row['username'],
			'user_colour'		=> $row['user_colour'],

			'online'		=> false,
			'profile'		=> phpbb_gallery::append_sid('phpbb', 'memberlist', "mode=viewprofile&amp;u=$user_id"),
			'www'			=> $row['user_website'],
			'aim'			=> ($row['user_aim'] && $auth->acl_get('u_sendim')) ? phpbb_gallery::append_sid('phpbb', 'memberlist', "mode=contact&amp;action=aim&amp;u=$user_id") : '',
			'msn'			=> ($row['user_msnm'] && $auth->acl_get('u_sendim')) ? phpbb_gallery::append_sid('phpbb', 'memberlist', "mode=contact&amp;action=msnm&amp;u=$user_id") : '',
			'yim'			=> ($row['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . urlencode($row['user_yim']) . '&amp;.src=pg' : '',
			'jabber'		=> ($row['user_jabber'] && $auth->acl_get('u_sendim')) ? phpbb_gallery::append_sid('phpbb', 'memberlist', "mode=contact&amp;action=jabber&amp;u=$user_id") : '',
			'search'		=> ($auth->acl_get('u_search')) ? phpbb_gallery::append_sid('phpbb', 'search', "author_id=$user_id&amp;sr=posts") : '',

			'gallery_album'		=> ($row['personal_album_id'] && $config['gallery_viewtopic_icon']) ? phpbb_gallery::append_sid('album', "album_id=" . $row['personal_album_id']) : '',
			'gallery_images'	=> ($config['gallery_viewtopic_images']) ? $row['user_images'] : 0,
			'gallery_search'	=> ($config['gallery_viewtopic_images'] && $config['gallery_viewtopic_link'] && $row['user_images']) ? phpbb_gallery::append_sid('search', "user_id=$user_id") : '',
		);

		get_user_rank($row['user_rank'], $row['user_posts'], $user_cache[$user_id]['rank_title'], $user_cache[$user_id]['rank_image'], $user_cache[$user_id]['rank_image_src']);

		if (!empty($row['user_allow_viewemail']) || $auth->acl_get('a_email'))
		{
			$user_cache[$user_id]['email'] = ($config['board_email_form'] && $config['email_enable']) ? phpbb_gallery::append_sid('phpbb', 'memberlist', "mode=email&amp;u=$user_id") : (($config['board_hide_emails'] && !$auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email']);
		}
		else
		{
			$user_cache[$user_id]['email'] = '';
		}

		if (!empty($row['user_icq']))
		{
			$user_cache[$user_id]['icq'] = 'http://www.icq.com/people/webmsg.php?to=' . $row['user_icq'];
			$user_cache[$user_id]['icq_status_img'] = '<img src="http://web.icq.com/whitepages/online?icq=' . $row['user_icq'] . '&amp;img=5" width="18" height="18" alt="" />';
		}
		else
		{
			$user_cache[$user_id]['icq_status_img'] = '';
			$user_cache[$user_id]['icq'] = '';
		}

		if ($config['allow_birthdays'] && !empty($row['user_birthday']))
		{
			list($bday_day, $bday_month, $bday_year) = array_map('intval', explode('-', $row['user_birthday']));

			if ($bday_year)
			{
				$now = getdate(time() + $user->timezone + $user->dst - date('Z'));

				$diff = $now['mon'] - $bday_month;
				if ($diff == 0)
				{
					$diff = ($now['mday'] - $bday_day < 0) ? 1 : 0;
				}
				else
				{
					$diff = ($diff < 0) ? 1 : 0;
				}

				$user_cache[$user_id]['age'] = (int) ($now['year'] - $bday_year - $diff);
			}
		}
	}
}

?>