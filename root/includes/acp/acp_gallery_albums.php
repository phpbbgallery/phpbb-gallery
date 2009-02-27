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
* @package acp
*/
class acp_gallery_albums
{
	var $u_action;
	var $parent_id = 0;

	function main($id, $mode)
	{
		global $cache, $config, $db, $user, $auth, $template;
		global $phpbb_admin_path, $gallery_root_path, $phpbb_root_path, $phpEx;
		$gallery_root_path = GALLERY_ROOT_PATH;

		include($phpbb_root_path . $gallery_root_path . 'includes/constants.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/functions.' . $phpEx);
		include($phpbb_root_path . $gallery_root_path . 'includes/permissions.' . $phpEx);
		$gallery_config = load_gallery_config();

		$user->add_lang('mods/gallery_acp');
		$user->add_lang('mods/gallery');
		$this->tpl_name = 'gallery_albums';
		$this->page_title = 'ACP_MANAGE_ALBUMS';

		$form_key = 'acp_gallery_albums';
		add_form_key($form_key);

		$action		= request_var('action', '');
		$update		= (isset($_POST['update'])) ? true : false;
		$album_id	= request_var('a', 0);

		$this->parent_id	= request_var('parent_id', 0);
		$album_data = $errors = array();
		if ($update && !check_form_key($form_key))
		{
			$update = false;
			$errors[] = $user->lang['FORM_INVALID'];
		}

		// Major routines
		if ($update)
		{
			switch ($action)
			{
				case 'delete':
					$action_subalbums	= request_var('action_subalbums', '');
					$subalbums_to_id	= request_var('subalbums_to_id', 0);
					$action_images		= request_var('action_images', '');
					$images_to_id		= request_var('images_to_id', 0);

					$errors = $this->delete_album($album_id, $action_images, $action_subalbums, $images_to_id, $subalbums_to_id);

					if (sizeof($errors))
					{
						break;
					}

					$cache->destroy('sql', GALLERY_ALBUMS_TABLE);

					trigger_error($user->lang['ALBUM_DELETED'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));

				break;

				case 'edit':
					$album_data = array(
						'album_id'		=>	$album_id
					);

				// No break here

				case 'add':

					$album_data += array(
						'parent_id'				=> request_var('album_parent_id', $this->parent_id),
						'forum_type'			=> request_var('album_type', ALBUM_UPLOAD),
						'type_action'			=> request_var('type_action', ''),
						'album_status'			=> request_var('album_status', ITEM_UNLOCKED),
						'album_parents'			=> '',
						'album_name'			=> utf8_normalize_nfc(request_var('album_name', '', true)),
						/*'forum_link'			=> request_var('forum_link', ''),
						'forum_link_track'		=> request_var('forum_link_track', false),*/
						'album_desc'			=> utf8_normalize_nfc(request_var('album_desc', '', true)),
						'album_desc_uid'		=> '',
						'album_desc_options'	=> 7,
						'album_desc_bitfield'	=> '',
						/*'forum_rules'			=> utf8_normalize_nfc(request_var('forum_rules', '', true)),
						'forum_rules_uid'		=> '',
						'forum_rules_options'	=> 7,
						'forum_rules_bitfield'	=> '',
						'forum_rules_link'		=> request_var('forum_rules_link', ''),*/
						'album_image'			=> request_var('album_image', ''),
						//'forum_style'			=> request_var('forum_style', 0),
						'display_subalbum_list'	=> request_var('display_subalbum_list', false),
						'display_on_index'		=> request_var('display_on_index', false),
						/*'enable_indexing'		=> request_var('enable_indexing', true),
						'enable_icons'			=> request_var('enable_icons', false),
						'enable_prune'			=> request_var('enable_prune', false),
						'enable_post_review'	=> request_var('enable_post_review', true),
						'prune_days'			=> request_var('prune_days', 7),
						'prune_viewed'			=> request_var('prune_viewed', 7),
						'prune_freq'			=> request_var('prune_freq', 1),
						'prune_old_polls'		=> request_var('prune_old_polls', false),
						'prune_announce'		=> request_var('prune_announce', false),
						'prune_sticky'			=> request_var('prune_sticky', false),
						'forum_password'		=> request_var('forum_password', '', true),
						'forum_password_confirm'=> request_var('forum_password_confirm', '', true),
						'forum_password_unset'	=> request_var('forum_password_unset', false),*/
					);

					/*/ Use link_display_on_index setting if forum type is link
					if ($forum_data['forum_type'] == FORUM_LINK)
					{
						$forum_data['display_on_index'] = request_var('link_display_on_index', false);
					}*/

					// Categories are not able to be locked...
					if ($album_data['album_type'] == ALBUM_CAT)
					{
						$album_data['album_status'] = ITEM_UNLOCKED;
					}

					/*/ Get data for forum rules if specified...
					if ($forum_data['forum_rules'])
					{
						generate_text_for_storage($forum_data['forum_rules'], $forum_data['forum_rules_uid'], $forum_data['forum_rules_bitfield'], $forum_data['forum_rules_options'], request_var('rules_parse_bbcode', false), request_var('rules_parse_urls', false), request_var('rules_parse_smilies', false));
					}*/

					// Get data for forum description if specified
					if ($album_data['album_desc'])
					{
						generate_text_for_storage($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options'], request_var('desc_parse_bbcode', false), request_var('desc_parse_urls', false), request_var('desc_parse_smilies', false));
					}

					$errors = $this->update_album_data($album_data);

					if (!sizeof($errors))
					{
						$album_perm_from = request_var('album_perm_from', 0);

						/*/ Copy permissions?
						if ($forum_perm_from && !empty($forum_perm_from) && $forum_perm_from != $forum_data['forum_id'] &&
							(($action != 'edit') || empty($forum_id) || ($auth->acl_get('a_fauth') && $auth->acl_get('a_authusers') && $auth->acl_get('a_authgroups') && $auth->acl_get('a_mauth'))))
						{
							// if we edit a forum delete current permissions first
							if ($action == 'edit')
							{
								$sql = 'DELETE FROM ' . ACL_USERS_TABLE . '
									WHERE forum_id = ' . (int) $forum_data['forum_id'];
								$db->sql_query($sql);

								$sql = 'DELETE FROM ' . ACL_GROUPS_TABLE . '
									WHERE forum_id = ' . (int) $forum_data['forum_id'];
								$db->sql_query($sql);
							}

							// From the mysql documentation:
							// Prior to MySQL 4.0.14, the target table of the INSERT statement cannot appear in the FROM clause of the SELECT part of the query. This limitation is lifted in 4.0.14.
							// Due to this we stay on the safe side if we do the insertion "the manual way"

							// Copy permisisons from/to the acl users table (only forum_id gets changed)
							$sql = 'SELECT user_id, auth_option_id, auth_role_id, auth_setting
								FROM ' . ACL_USERS_TABLE . '
								WHERE forum_id = ' . $forum_perm_from;
							$result = $db->sql_query($sql);

							$users_sql_ary = array();
							while ($row = $db->sql_fetchrow($result))
							{
								$users_sql_ary[] = array(
									'user_id'			=> (int) $row['user_id'],
									'forum_id'			=> (int) $forum_data['forum_id'],
									'auth_option_id'	=> (int) $row['auth_option_id'],
									'auth_role_id'		=> (int) $row['auth_role_id'],
									'auth_setting'		=> (int) $row['auth_setting']
								);
							}
							$db->sql_freeresult($result);

							// Copy permisisons from/to the acl groups table (only forum_id gets changed)
							$sql = 'SELECT group_id, auth_option_id, auth_role_id, auth_setting
								FROM ' . ACL_GROUPS_TABLE . '
								WHERE forum_id = ' . $forum_perm_from;
							$result = $db->sql_query($sql);

							$groups_sql_ary = array();
							while ($row = $db->sql_fetchrow($result))
							{
								$groups_sql_ary[] = array(
									'group_id'			=> (int) $row['group_id'],
									'forum_id'			=> (int) $forum_data['forum_id'],
									'auth_option_id'	=> (int) $row['auth_option_id'],
									'auth_role_id'		=> (int) $row['auth_role_id'],
									'auth_setting'		=> (int) $row['auth_setting']
								);
							}
							$db->sql_freeresult($result);

							// Now insert the data
							$db->sql_multi_insert(ACL_USERS_TABLE, $users_sql_ary);
							$db->sql_multi_insert(ACL_GROUPS_TABLE, $groups_sql_ary);
							cache_moderators();
						}
						*/

						$auth->acl_clear_prefetch();
						$cache->destroy('sql', GALLERY_ALBUMS_TABLE);

						$acl_url = '&amp;mode=album_permissions&amp;step=1&amp;uncheck=true&amp;album_id=' . $album_data['album_id'];

						$message = ($action == 'add') ? $user->lang['ALBUM_CREATED'] : $user->lang['ALBUM_UPDATED'];

						// Redirect to permissions
						$message .= '<br /><br />' . sprintf($user->lang['REDIRECT_ACL'], '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", 'i=gallery' . $acl_url) . '">', '</a>');

						// redirect directly to permission settings screen if authed
						if ($action == 'add' && !$album_perm_from)
						{
							meta_refresh(4, append_sid("{$phpbb_admin_path}index.$phpEx", 'i=gallery' . $acl_url));
						}

						trigger_error($message . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
					}

				break;
			}
		}

		switch ($action)
		{
			case 'move_up':
			case 'move_down':

				if (!$album_id)
				{
					trigger_error($user->lang['NO_ALBUM'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				$sql = 'SELECT *
					FROM ' . GALLERY_ALBUMS_TABLE . "
					WHERE album_id = $album_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['NO_ALBUM'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				$move_album_name = $this->move_album_by($row, $action, 1);

				if ($move_album_name !== false)
				{
					//add_log('admin', 'LOG_ALBUM_' . strtoupper($action), $row['album_name'], $move_album_name);
					$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
				}

			break;

			case 'sync':
			case 'sync_album':
				if (!$album_id)
				{
					trigger_error($user->lang['NO_ALBUM'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}


				$sql = 'SELECT album_name, album_type
					FROM ' . GALLERY_ALBUMS_TABLE . "
					WHERE album_id = $album_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['NO_ALBUM'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				update_album_info($album_id);

				//@todo: add_log('admin', 'LOG_ALBUM_SYNC', $row['forum_name']);

				$template->assign_var('L_ALBUM_RESYNCED', sprintf($user->lang['ALBUM_RESYNCED'], $row['album_name']));

			break;

			case 'add':
			case 'edit':

				if ($update)
				{
					/*$forum_data['forum_flags'] = 0;
					$forum_data['forum_flags'] += (request_var('forum_link_track', false)) ? FORUM_FLAG_LINK_TRACK : 0;
					$forum_data['forum_flags'] += (request_var('prune_old_polls', false)) ? FORUM_FLAG_PRUNE_POLL : 0;
					$forum_data['forum_flags'] += (request_var('prune_announce', false)) ? FORUM_FLAG_PRUNE_ANNOUNCE : 0;
					$forum_data['forum_flags'] += (request_var('prune_sticky', false)) ? FORUM_FLAG_PRUNE_STICKY : 0;
					$forum_data['forum_flags'] += ($forum_data['show_active']) ? FORUM_FLAG_ACTIVE_TOPICS : 0;
					$forum_data['forum_flags'] += (request_var('enable_post_review', true)) ? FORUM_FLAG_POST_REVIEW : 0;*/
				}

				// Show form to create/modify a album
				if ($action == 'edit')
				{
					$this->page_title = 'EDIT_ALBUM';
					$row = get_album_info($album_id);
					$old_album_type = $row['album_type'];

					if (!$update)
					{
						$album_data = $row;
					}
					else
					{
						$album_data['left_id'] = $row['left_id'];
						$album_data['right_id'] = $row['right_id'];
					}

					// Make sure no direct child albums are able to be selected as parents.
					$exclude_albums = array();
					foreach (get_album_branch(0, $album_id, 'children') as $row)
					{
						$exclude_albums[] = $row['album_id'];
					}

					$parents_list = gallery_albumbox(true, '', $album_data['parent_id'], false, $exclude_albums);

					//@todo: $album_data['album_password_confirm'] = $album_data['album_password'];
				}
				else
				{
					$this->page_title = 'CREATE_ALBUM';

					$album_id = $this->parent_id;
					$parents_list = gallery_albumbox(true, '', $this->parent_id);

					// Fill album data with default values
					if (!$update)
					{
						$album_data = array(
							'parent_id'				=> $this->parent_id,
							'album_type'			=> ALBUM_UPLOAD,
							'album_status'			=> ITEM_UNLOCKED,
							'album_name'			=> utf8_normalize_nfc(request_var('album_name', '', true)),
							/*'forum_link'			=> '',
							'forum_link_track'		=> false,*/
							'album_desc'			=> '',
							/*'forum_rules'			=> '',
							'forum_rules_link'		=> '',*/
							'album_image'			=> '',
							//'forum_style'			=> 0,
							'display_subalbum_list'	=> true,
							'display_on_index'		=> true,
							/*'forum_topics_per_page'	=> 0,
							'enable_indexing'		=> true,
							'enable_icons'			=> false,
							'enable_prune'			=> false,
							'prune_days'			=> 7,
							'prune_viewed'			=> 7,
							'prune_freq'			=> 1,
							'forum_flags'			=> FORUM_FLAG_POST_REVIEW,
							'forum_password'		=> '',
							'forum_password_confirm'=> '',*/
						);
					}
				}

				/*
				$forum_rules_data = array(
					'text'			=> $forum_data['forum_rules'],
					'allow_bbcode'	=> true,
					'allow_smilies'	=> true,
					'allow_urls'	=> true
				);

				$forum_rules_preview = '';

				// Parse rules if specified
				if ($forum_data['forum_rules'])
				{
					if (!isset($forum_data['forum_rules_uid']))
					{
						// Before we are able to display the preview and plane text, we need to parse our request_var()'d value...
						$forum_data['forum_rules_uid'] = '';
						$forum_data['forum_rules_bitfield'] = '';
						$forum_data['forum_rules_options'] = 0;

						generate_text_for_storage($forum_data['forum_rules'], $forum_data['forum_rules_uid'], $forum_data['forum_rules_bitfield'], $forum_data['forum_rules_options'], request_var('rules_allow_bbcode', false), request_var('rules_allow_urls', false), request_var('rules_allow_smilies', false));
					}

					// Generate preview content
					$forum_rules_preview = generate_text_for_display($forum_data['forum_rules'], $forum_data['forum_rules_uid'], $forum_data['forum_rules_bitfield'], $forum_data['forum_rules_options']);

					// decode...
					$forum_rules_data = generate_text_for_edit($forum_data['forum_rules'], $forum_data['forum_rules_uid'], $forum_data['forum_rules_options']);
				}
				*/

				$album_desc_data = array(
					'text'			=> $album_data['album_desc'],
					'allow_bbcode'	=> true,
					'allow_smilies'	=> true,
					'allow_urls'	=> true
				);

				// Parse desciption if specified
				if ($album_data['album_desc'])
				{
					if (!isset($album_data['album_desc_uid']))
					{
						// Before we are able to display the preview and plane text, we need to parse our request_var()'d value...
						$album_data['album_desc_uid'] = '';
						$album_data['album_desc_bitfield'] = '';
						$album_data['album_desc_options'] = 0;

						generate_text_for_storage($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options'], request_var('desc_allow_bbcode', false), request_var('desc_allow_urls', false), request_var('desc_allow_smilies', false));
					}

					// decode...
					$album_desc_data = generate_text_for_edit($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_options']);
				}

				$album_type_options = '';
				$album_type_ary = array(ALBUM_CAT => 'CAT', ALBUM_UPLOAD => 'UPLOAD', ALBUM_CONTEST => 'CONTEST');

				foreach ($album_type_ary as $value => $lang)
				{
					$album_type_options .= 's<option value="' . $value . '"' . (($value == $album_data['album_type']) ? ' selected="selected"' : '') . '>' . $user->lang['ALBUM_TYPE_' . $lang] . '</option>';
				}

				//$styles_list = style_select($forum_data['forum_style'], true);

				$statuslist = '<option value="' . ITEM_UNLOCKED . '"' . (($album_data['album_status'] == ITEM_UNLOCKED) ? ' selected="selected"' : '') . '>' . $user->lang['UNLOCKED'] . '</option><option value="' . ITEM_LOCKED . '"' . (($album_data['album_status'] == ITEM_LOCKED) ? ' selected="selected"' : '') . '>' . $user->lang['LOCKED'] . '</option>';

				$sql = 'SELECT album_id
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE album_type = ' . ALBUM_UPLOAD . "
						AND album_user_id = 0
						AND album_id <> $album_id";
				$result = $db->sql_query($sql);

				if ($db->sql_fetchrow($result))
				{
					$template->assign_vars(array(
						'S_MOVE_ALBUM_OPTIONS'		=> gallery_albumbox(true, '', $album_data['parent_id'], false, $album_id),
					));
				}
				$db->sql_freeresult($result);

				// Subalbum move options
				if ($action == 'edit' && $album_data['album_type'] == ALBUM_UPLOAD)
				{
					$subalbums_id = array();
					$subalbums = get_album_branch(0, $album_id, 'children');

					foreach ($subalbums as $row)
					{
						$subalbums_id[] = $row['album_id'];
					}

					$albums_list = gallery_albumbox(true, '', $album_data['parent_id'], false, $subalbums_id);

					/**
					* //@todo: report me!!!111elf
					* See 30 lines above :-O
					*/
					$sql = 'SELECT album_id
						FROM ' . GALLERY_ALBUMS_TABLE . '
						WHERE album_type <> ' . ALBUM_CAT . "
							AND album_id <> $album_id
							AND album_user_id = 0";
					$result = $db->sql_query($sql);

					if ($db->sql_fetchrow($result))
					{
						$template->assign_vars(array(
							'S_MOVE_ALBUM_OPTIONS'		=> gallery_albumbox(true, '', $album_data['parent_id'], false, $subalbums_id),
						));
					}
					$db->sql_freeresult($result);

					$template->assign_vars(array(
						'S_HAS_SUBALBUMS'		=> ($album_data['right_id'] - $album_data['left_id'] > 1) ? true : false,
						'S_ALBUMS_LIST'			=> $albums_list)
					);
				}

				$s_show_display_on_index = false;

				if ($album_data['parent_id'] > 0)
				{
					// if this album is a subalbum put the "display on index" checkbox
					if ($parent_info = get_album_info($album_data['parent_id']))
					{
						if ($parent_info['parent_id'] > 0 || $parent_info['album_type'] == ALBUM_CAT)
						{
							$s_show_display_on_index = true;
						}
					}
				}

				/*
				if (strlen($album_data['album_password']) == 32)
				{
					$errors[] = $user->lang['ALBUM_PASSWORD_OLD'];
				}
				*/

				$template->assign_vars(array(
					'S_EDIT_ALBUM'		=> true,
					'S_ERROR'			=> (sizeof($errors)) ? true : false,
					'S_PARENT_ID'		=> $this->parent_id,
					'S_ALBUM_PARENT_ID'	=> $album_data['parent_id'],
					'S_ADD_ACTION'		=> ($action == 'add') ? true : false,

					'U_BACK'			=> $this->u_action . '&amp;parent_id=' . $this->parent_id,
					'U_EDIT_ACTION'		=> $this->u_action . "&amp;parent_id={$this->parent_id}&amp;action=$action&amp;a=$album_id",

					'L_COPY_PERMISSIONS_EXPLAIN'	=> $user->lang['COPY_PERMISSIONS_' . strtoupper($action) . '_EXPLAIN'],
					'L_TITLE'						=> $user->lang[$this->page_title],
					'ERROR_MSG'						=> (sizeof($errors)) ? implode('<br />', $errors) : '',

					'ALBUM_NAME'				=> $album_data['album_name'],
					'ALBUM_IMAGE'				=> $album_data['album_image'],
					'ALBUM_IMAGE_SRC'			=> ($album_data['album_image']) ? $phpbb_root_path . $album_data['album_image'] : '',
					'ALBUM_UPLOAD'				=> ALBUM_UPLOAD,
					'ALBUM_CAT'					=> ALBUM_CAT,
					'ALBUM_CONTEST'				=> ALBUM_CONTEST,
					/*'PRUNE_FREQ'				=> $forum_data['prune_freq'],
					'PRUNE_DAYS'				=> $forum_data['prune_days'],
					'PRUNE_VIEWED'				=> $forum_data['prune_viewed'],
					'TOPICS_PER_PAGE'			=> $forum_data['forum_topics_per_page'],
					'FORUM_RULES_LINK'			=> $forum_data['forum_rules_link'],
					'FORUM_RULES'				=> $forum_data['forum_rules'],
					'FORUM_RULES_PREVIEW'		=> $forum_rules_preview,
					'FORUM_RULES_PLAIN'			=> $forum_rules_data['text'],
					'S_BBCODE_CHECKED'			=> ($forum_rules_data['allow_bbcode']) ? true : false,
					'S_SMILIES_CHECKED'			=> ($forum_rules_data['allow_smilies']) ? true : false,
					'S_URLS_CHECKED'			=> ($forum_rules_data['allow_urls']) ? true : false,
					'S_ALBUM_PASSWORD_SET'		=> (empty($album_data['album_password'])) ? false : true,*/

					'ALBUM_DESC'				=> $album_desc_data['text'],
					'S_DESC_BBCODE_CHECKED'		=> ($album_desc_data['allow_bbcode']) ? true : false,
					'S_DESC_SMILIES_CHECKED'	=> ($album_desc_data['allow_smilies']) ? true : false,
					'S_DESC_URLS_CHECKED'		=> ($album_desc_data['allow_urls']) ? true : false,

					'S_ALBUM_TYPE_OPTIONS'		=> $album_type_options,
					'S_STATUS_OPTIONS'			=> $statuslist,
					'S_PARENT_OPTIONS'			=> $parents_list,
					//'S_STYLES_OPTIONS'			=> $styles_list,
					'S_ALBUM_OPTIONS'			=> gallery_albumbox(true, '', ($action == 'add') ? $album_data['parent_id'] : false, false, ($action == 'edit') ? $album_data['album_id'] : false),
					'S_SHOW_DISPLAY_ON_INDEX'	=> $s_show_display_on_index,
					'S_ALBUM_ORIG_POST'			=> (isset($old_album_type) && $old_album_type == ALBUM_UPLOAD) ? true : false,
					'S_ALBUM_ORIG_CAT'			=> (isset($old_album_type) && $old_album_type == ALBUM_CAT) ? true : false,
					'S_ALBUM_ORIG_LINK'			=> (isset($old_album_type) && $old_album_type == ALBUM_CONTEST) ? true : false,
					'S_ALBUM_POST'				=> ($album_data['album_type'] == ALBUM_UPLOAD) ? true : false,
					'S_ALBUM_CAT'				=> ($album_data['album_type'] == ALBUM_CAT) ? true : false,
					'S_ALBUM_LINK'				=> ($album_data['album_type'] == ALBUM_CONTEST) ? true : false,
					/*'S_ENABLE_INDEXING'			=> ($album_data['enable_indexing']) ? true : false,
					'S_TOPIC_ICONS'				=> ($album_data['enable_icons']) ? true : false,*/
					'S_DISPLAY_SUBALBUM_LIST'	=> ($album_data['display_subalbum_list']) ? true : false,
					'S_DISPLAY_ON_INDEX'		=> ($album_data['display_on_index']) ? true : false,
					/*'S_PRUNE_ENABLE'			=> ($forum_data['enable_prune']) ? true : false,
					'S_FORUM_LINK_TRACK'		=> ($forum_data['forum_flags'] & FORUM_FLAG_LINK_TRACK) ? true : false,
					'S_PRUNE_OLD_POLLS'			=> ($forum_data['forum_flags'] & FORUM_FLAG_PRUNE_POLL) ? true : false,
					'S_PRUNE_ANNOUNCE'			=> ($forum_data['forum_flags'] & FORUM_FLAG_PRUNE_ANNOUNCE) ? true : false,
					'S_PRUNE_STICKY'			=> ($forum_data['forum_flags'] & FORUM_FLAG_PRUNE_STICKY) ? true : false,
					'S_DISPLAY_ACTIVE_TOPICS'	=> ($forum_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS) ? true : false,
					'S_ENABLE_POST_REVIEW'		=> ($forum_data['forum_flags'] & FORUM_FLAG_POST_REVIEW) ? true : false,*/
					'S_CAN_COPY_PERMISSIONS'	=> true,
				));

				return;

			break;

			case 'delete':

				if (!$album_id)
				{
					trigger_error($user->lang['NO_ALBUM'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				$album_data = get_album_info($album_id);

				$subalbums_id = array();
				$subalbums = get_album_branch(0, $album_id, 'children');

				foreach ($subalbums as $row)
				{
					$subalbums_id[] = $row['album_id'];
				}

				$albums_list = gallery_albumbox(true, '', $album_data['parent_id'], false, $subalbums_id);

				$sql = 'SELECT album_id
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE album_type <> ' . ALBUM_CAT . "
						AND album_id <> $album_id
						AND album_user_id = 0";
				$result = $db->sql_query($sql);

				if ($db->sql_fetchrow($result))
				{
					$template->assign_vars(array(
						'S_MOVE_ALBUM_OPTIONS'		=> gallery_albumbox(true, '', $album_data['parent_id'], false, $subalbums_id),
					));
				}
				$db->sql_freeresult($result);

				$parent_id = ($this->parent_id == $album_id) ? 0 : $this->parent_id;
				$template->assign_vars(array(
					'S_DELETE_ALBUM'		=> true,
					'U_ACTION'				=> $this->u_action . "&amp;parent_id={$parent_id}&amp;action=delete&amp;a=" . $album_id,
					'U_BACK'				=> $this->u_action . '&amp;parent_id=' . $this->parent_id,

					'ALBUM_NAME'			=> $album_data['album_name'],
					'S_ALBUM_POST'			=> ($album_data['album_type'] == ALBUM_CAT) ? false : true,
					'S_HAS_SUBALBUMS'		=> ($album_data['right_id'] - $album_data['left_id'] > 1) ? true : false,
					'S_ALBUMS_LIST'			=> $albums_list,

					'S_ERROR'				=> (sizeof($errors)) ? true : false,
					'ERROR_MSG'				=> (sizeof($errors)) ? implode('<br />', $errors) : '',
				));

				return;
			break;
		}

		// Default management page
		if (!$this->parent_id)
		{
			$navigation = $user->lang['GALLERY_INDEX'];
		}
		else
		{
			$navigation = '<a href="' . $this->u_action . '">' . $user->lang['GALLERY_INDEX'] . '</a>';

			$albums_nav = get_album_branch(0, $this->parent_id, 'parents', 'descending');
			foreach ($albums_nav as $row)
			{
				if ($row['album_id'] == $this->parent_id)
				{
					$navigation .= ' -&gt; ' . $row['album_name'];
				}
				else
				{
					$navigation .= ' -&gt; <a href="' . $this->u_action . '&amp;parent_id=' . $row['album_id'] . '">' . $row['album_name'] . '</a>';
				}
			}
		}

		// Jumpbox
		$album_box = gallery_albumbox(true, '', $this->parent_id, false, false);

		if ($action == 'sync' || $action == 'sync_album')
		{
			$template->assign_var('S_RESYNCED', true);
		}

		$sql = 'SELECT *
			FROM ' . GALLERY_ALBUMS_TABLE . "
			WHERE parent_id = {$this->parent_id}
				AND album_user_id = 0
			ORDER BY left_id";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$album_type = $row['album_type'];

				if ($row['album_status'] == ITEM_LOCKED)
				{
					$folder_image = '<img src="images/icon_folder_lock.gif" alt="' . $user->lang['LOCKED'] . '" />';
				}
				else
				{
					$folder_image = ($row['left_id'] + 1 != $row['right_id']) ? '<img src="images/icon_subfolder.gif" alt="' . $user->lang['SUBALBUM'] . '" />' : '<img src="images/icon_folder.gif" alt="' . $user->lang['FOLDER'] . '" />';
				}

				$url = $this->u_action . "&amp;parent_id=$this->parent_id&amp;a={$row['album_id']}";

				$template->assign_block_vars('albums', array(
					'FOLDER_IMAGE'		=> $folder_image,
					'ALBUM_IMAGE'		=> ($row['album_image']) ? '<img src="' . $phpbb_root_path . $row['album_image'] . '" alt="" />' : '',
					'ALBUM_IMAGE_SRC'	=> ($row['album_image']) ? $phpbb_root_path . $row['album_image'] : '',
					'ALBUM_NAME'		=> $row['album_name'],
					'ALBUM_DESCRIPTION'	=> generate_text_for_display($row['album_desc'], $row['album_desc_uid'], $row['album_desc_bitfield'], $row['album_desc_options']),
					'ALBUM_IMAGES'		=> $row['album_images'],

					'S_ALBUM_POST'		=> ($album_type != ALBUM_CAT) ? true : false,

					'U_ALBUM'			=> $this->u_action . '&amp;parent_id=' . $row['album_id'],
					'U_MOVE_UP'			=> $url . '&amp;action=move_up',
					'U_MOVE_DOWN'		=> $url . '&amp;action=move_down',
					'U_EDIT'			=> $url . '&amp;action=edit',
					'U_DELETE'			=> $url . '&amp;action=delete',
					'U_SYNC'			=> $url . '&amp;action=sync')
				);
			}
			while ($row = $db->sql_fetchrow($result));
		}
		else if ($this->parent_id)
		{
			$row = get_album_info($this->parent_id);

			$url = $this->u_action . '&amp;parent_id=' . $this->parent_id . '&amp;a=' . $row['album_id'];

			$template->assign_vars(array(
				'S_NO_ALBUMS'		=> true,

				'U_EDIT'			=> $url . '&amp;action=edit',
				'U_DELETE'			=> $url . '&amp;action=delete',
				'U_SYNC'			=> $url . '&amp;action=sync',
			));
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'ERROR_MSG'		=> (sizeof($errors)) ? implode('<br />', $errors) : '',
			'NAVIGATION'	=> $navigation,
			'ALBUM_BOX'		=> $album_box,
			'U_SEL_ACTION'	=> $this->u_action,
			'U_ACTION'		=> $this->u_action . '&amp;parent_id=' . $this->parent_id,

			'U_PROGRESS_BAR'	=> $this->u_action . '&amp;action=progress_bar',
			'UA_PROGRESS_BAR'	=> addslashes($this->u_action . '&amp;action=progress_bar'),
		));
	}

	/**
	* Get album details
	* We use g/i/functions.php => get_album_info
	*
	function get_album_info($album_id)
	{
		global $db;

		$sql = 'SELECT *
			FROM ' . GALLERY_ALBUMS_TABLE . "
			WHERE album_id = $album_id";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error("Album #$album_id does not exist", E_USER_ERROR);
		}

		return $row;
	}
	*/

	/**
	* Update forum data
	*
	function update_forum_data(&$forum_data)
	{
		global $db, $user, $cache;

		$errors = array();

		if (!$forum_data['forum_name'])
		{
			$errors[] = $user->lang['FORUM_NAME_EMPTY'];
		}

		if (utf8_strlen($forum_data['forum_desc']) > 4000)
		{
			$errors[] = $user->lang['FORUM_DESC_TOO_LONG'];
		}

		if (utf8_strlen($forum_data['forum_rules']) > 4000)
		{
			$errors[] = $user->lang['FORUM_RULES_TOO_LONG'];
		}

		if ($forum_data['forum_password'] || $forum_data['forum_password_confirm'])
		{
			if ($forum_data['forum_password'] != $forum_data['forum_password_confirm'])
			{
				$forum_data['forum_password'] = $forum_data['forum_password_confirm'] = '';
				$errors[] = $user->lang['FORUM_PASSWORD_MISMATCH'];
			}
		}

		if ($forum_data['prune_days'] < 0 || $forum_data['prune_viewed'] < 0 || $forum_data['prune_freq'] < 0)
		{
			$forum_data['prune_days'] = $forum_data['prune_viewed'] = $forum_data['prune_freq'] = 0;
			$errors[] = $user->lang['FORUM_DATA_NEGATIVE'];
		}

		$range_test_ary = array(
			array('lang' => 'FORUM_TOPICS_PAGE', 'value' => $forum_data['forum_topics_per_page'], 'column_type' => 'TINT:0'),
		);

		validate_range($range_test_ary, $errors);

		// Set forum flags
		// 1 = link tracking
		// 2 = prune old polls
		// 4 = prune announcements
		// 8 = prune stickies
		// 16 = show active topics
		// 32 = enable post review
		$forum_data['forum_flags'] = 0;
		$forum_data['forum_flags'] += ($forum_data['forum_link_track']) ? FORUM_FLAG_LINK_TRACK : 0;
		$forum_data['forum_flags'] += ($forum_data['prune_old_polls']) ? FORUM_FLAG_PRUNE_POLL : 0;
		$forum_data['forum_flags'] += ($forum_data['prune_announce']) ? FORUM_FLAG_PRUNE_ANNOUNCE : 0;
		$forum_data['forum_flags'] += ($forum_data['prune_sticky']) ? FORUM_FLAG_PRUNE_STICKY : 0;
		$forum_data['forum_flags'] += ($forum_data['show_active']) ? FORUM_FLAG_ACTIVE_TOPICS : 0;
		$forum_data['forum_flags'] += ($forum_data['enable_post_review']) ? FORUM_FLAG_POST_REVIEW : 0;

		// Unset data that are not database fields
		$forum_data_sql = $forum_data;

		unset($forum_data_sql['forum_link_track']);
		unset($forum_data_sql['prune_old_polls']);
		unset($forum_data_sql['prune_announce']);
		unset($forum_data_sql['prune_sticky']);
		unset($forum_data_sql['show_active']);
		unset($forum_data_sql['enable_post_review']);
		unset($forum_data_sql['forum_password_confirm']);

		// What are we going to do tonight Brain? The same thing we do everynight,
		// try to take over the world ... or decide whether to continue update
		// and if so, whether it's a new forum/cat/link or an existing one
		if (sizeof($errors))
		{
			return $errors;
		}

		// As we don't know the old password, it's kinda tricky to detect changes
		if ($forum_data_sql['forum_password_unset'])
		{
			$forum_data_sql['forum_password'] = '';
		}
		else if (empty($forum_data_sql['forum_password']))
		{
			unset($forum_data_sql['forum_password']);
		}
		else
		{
			$forum_data_sql['forum_password'] = phpbb_hash($forum_data_sql['forum_password']);
		}
		unset($forum_data_sql['forum_password_unset']);

		if (!isset($forum_data_sql['forum_id']))
		{
			// no forum_id means we're creating a new forum
			unset($forum_data_sql['type_action']);

			if ($forum_data_sql['parent_id'])
			{
				$sql = 'SELECT left_id, right_id, forum_type
					FROM ' . FORUMS_TABLE . '
					WHERE forum_id = ' . $forum_data_sql['parent_id'];
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['PARENT_NOT_EXIST'] . adm_back_link($this->u_action . '&amp;' . $this->parent_id), E_USER_WARNING);
				}

				if ($row['forum_type'] == FORUM_LINK)
				{
					$errors[] = $user->lang['PARENT_IS_LINK_FORUM'];
					return $errors;
				}

				$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET left_id = left_id + 2, right_id = right_id + 2
					WHERE left_id > ' . $row['right_id'];
				$db->sql_query($sql);

				$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET right_id = right_id + 2
					WHERE ' . $row['left_id'] . ' BETWEEN left_id AND right_id';
				$db->sql_query($sql);

				$forum_data_sql['left_id'] = $row['right_id'];
				$forum_data_sql['right_id'] = $row['right_id'] + 1;
			}
			else
			{
				$sql = 'SELECT MAX(right_id) AS right_id
					FROM ' . FORUMS_TABLE;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$forum_data_sql['left_id'] = $row['right_id'] + 1;
				$forum_data_sql['right_id'] = $row['right_id'] + 2;
			}

			$sql = 'INSERT INTO ' . FORUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $forum_data_sql);
			$db->sql_query($sql);

			$forum_data['forum_id'] = $db->sql_nextid();

			add_log('admin', 'LOG_FORUM_ADD', $forum_data['forum_name']);
		}
		else
		{
			$row = $this->get_forum_info($forum_data_sql['forum_id']);

			if ($row['forum_type'] == FORUM_POST && $row['forum_type'] != $forum_data_sql['forum_type'])
			{
				// Has subforums and want to change into a link?
				if ($row['right_id'] - $row['left_id'] > 1 && $forum_data_sql['forum_type'] == FORUM_LINK)
				{
					$errors[] = $user->lang['FORUM_WITH_SUBFORUMS_NOT_TO_LINK'];
					return $errors;
				}

				// we're turning a postable forum into a non-postable forum
				if ($forum_data_sql['type_action'] == 'move')
				{
					$to_forum_id = request_var('to_forum_id', 0);

					if ($to_forum_id)
					{
						$errors = $this->move_forum_content($forum_data_sql['forum_id'], $to_forum_id);
					}
					else
					{
						return array($user->lang['NO_DESTINATION_FORUM']);
					}
				}
				else if ($forum_data_sql['type_action'] == 'delete')
				{
					$errors = $this->delete_forum_content($forum_data_sql['forum_id']);
				}
				else
				{
					return array($user->lang['NO_FORUM_ACTION']);
				}

				$forum_data_sql['forum_posts'] = $forum_data_sql['forum_topics'] = $forum_data_sql['forum_topics_real'] = $forum_data_sql['forum_last_post_id'] = $forum_data_sql['forum_last_poster_id'] = $forum_data_sql['forum_last_post_time'] = 0;
				$forum_data_sql['forum_last_poster_name'] = $forum_data_sql['forum_last_poster_colour'] = '';
			}
			else if ($row['forum_type'] == FORUM_CAT && $forum_data_sql['forum_type'] == FORUM_LINK)
			{
				// Has subforums?
				if ($row['right_id'] - $row['left_id'] > 1)
				{
					// We are turning a category into a link - but need to decide what to do with the subforums.
					$action_subforums = request_var('action_subforums', '');
					$subforums_to_id = request_var('subforums_to_id', 0);

					if ($action_subforums == 'delete')
					{
						$rows = get_forum_branch($row['forum_id'], 'children', 'descending', false);

						foreach ($rows as $_row)
						{
							// Do not remove the forum id we are about to change. ;)
							if ($_row['forum_id'] == $row['forum_id'])
							{
								continue;
							}

							$forum_ids[] = $_row['forum_id'];
							$errors = array_merge($errors, $this->delete_forum_content($_row['forum_id']));
						}

						if (sizeof($errors))
						{
							return $errors;
						}

						if (sizeof($forum_ids))
						{
							$sql = 'DELETE FROM ' . FORUMS_TABLE . '
								WHERE ' . $db->sql_in_set('forum_id', $forum_ids);
							$db->sql_query($sql);

							$sql = 'DELETE FROM ' . ACL_GROUPS_TABLE . '
								WHERE ' . $db->sql_in_set('forum_id', $forum_ids);
							$db->sql_query($sql);

							$sql = 'DELETE FROM ' . ACL_USERS_TABLE . '
								WHERE ' . $db->sql_in_set('forum_id', $forum_ids);
							$db->sql_query($sql);

							// Delete forum ids from extension groups table
							$sql = 'SELECT group_id, allowed_forums
								FROM ' . EXTENSION_GROUPS_TABLE;
							$result = $db->sql_query($sql);

							while ($_row = $db->sql_fetchrow($result))
							{
								if (!$_row['allowed_forums'])
								{
									continue;
								}

								$allowed_forums = unserialize(trim($_row['allowed_forums']));
								$allowed_forums = array_diff($allowed_forums, $forum_ids);

								$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . "
									SET allowed_forums = '" . ((sizeof($allowed_forums)) ? serialize($allowed_forums) : '') . "'
									WHERE group_id = {$_row['group_id']}";
								$db->sql_query($sql);
							}
							$db->sql_freeresult($result);

							$cache->destroy('_extensions');
						}
					}
					else if ($action_subforums == 'move')
					{
						if (!$subforums_to_id)
						{
							return array($user->lang['NO_DESTINATION_FORUM']);
						}

						$sql = 'SELECT forum_name
							FROM ' . FORUMS_TABLE . '
							WHERE forum_id = ' . $subforums_to_id;
						$result = $db->sql_query($sql);
						$_row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if (!$_row)
						{
							return array($user->lang['NO_FORUM']);
						}

						$subforums_to_name = $_row['forum_name'];

						$sql = 'SELECT forum_id
							FROM ' . FORUMS_TABLE . "
							WHERE parent_id = {$row['forum_id']}";
						$result = $db->sql_query($sql);

						while ($_row = $db->sql_fetchrow($result))
						{
							$this->move_forum($_row['forum_id'], $subforums_to_id);
						}
						$db->sql_freeresult($result);

						$sql = 'UPDATE ' . FORUMS_TABLE . "
							SET parent_id = $subforums_to_id
							WHERE parent_id = {$row['forum_id']}";
						$db->sql_query($sql);
					}

					// Adjust the left/right id
					$sql = 'UPDATE ' . FORUMS_TABLE . '
						SET right_id = left_id + 1
						WHERE forum_id = ' . $row['forum_id'];
					$db->sql_query($sql);
				}
			}
			else if ($row['forum_type'] == FORUM_CAT && $forum_data_sql['forum_type'] == FORUM_POST)
			{
				// Changing a category to a forum? Reset the data (you can't post directly in a cat, you must use a forum)
				$forum_data_sql['forum_posts'] = 0;
				$forum_data_sql['forum_topics'] = 0;
				$forum_data_sql['forum_topics_real'] = 0;
				$forum_data_sql['forum_last_post_id'] = 0;
				$forum_data_sql['forum_last_post_subject'] = '';
				$forum_data_sql['forum_last_post_time'] = 0;
				$forum_data_sql['forum_last_poster_id'] = 0;
				$forum_data_sql['forum_last_poster_name'] = '';
				$forum_data_sql['forum_last_poster_colour'] = '';
			}

			if (sizeof($errors))
			{
				return $errors;
			}

			if ($row['parent_id'] != $forum_data_sql['parent_id'])
			{
				if ($row['forum_id'] != $forum_data_sql['parent_id'])
				{
					$errors = $this->move_forum($forum_data_sql['forum_id'], $forum_data_sql['parent_id']);
				}
				else
				{
					$forum_data_sql['parent_id'] = $row['parent_id'];
				}
			}

			if (sizeof($errors))
			{
				return $errors;
			}

			unset($forum_data_sql['type_action']);

			if ($row['forum_name'] != $forum_data_sql['forum_name'])
			{
				// the forum name has changed, clear the parents list of all forums (for safety)
				$sql = 'UPDATE ' . FORUMS_TABLE . "
					SET forum_parents = ''";
				$db->sql_query($sql);
			}

			// Setting the forum id to the forum id is not really received well by some dbs. ;)
			$forum_id = $forum_data_sql['forum_id'];
			unset($forum_data_sql['forum_id']);

			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $forum_data_sql) . '
				WHERE forum_id = ' . $forum_id;
			$db->sql_query($sql);

			// Add it back
			$forum_data['forum_id'] = $forum_id;

			add_log('admin', 'LOG_FORUM_EDIT', $forum_data['forum_name']);
		}

		return $errors;
	}

	/**
	* Move forum
	*
	* borrowed from phpBB3
	* @author: phpBB Group
	* @function: move_forum
	*
	* //@todo: Ready!!!
	*/
	function move_album($from_id, $to_id)
	{
		global $db, $user;

		$to_data = $moved_ids = $errors = array();

		// Check if we want to move to a parent with link type
		if ($to_id > 0)
		{
			$to_data = get_album_info($to_id);

			/*if ($to_data['album_type'] == ALBUM_LINK)
			{
				$errors[] = $user->lang['PARENT_IS_LINK_ALBUM'];
				return $errors;
			}*/
		}

		$moved_albums = get_album_branch($from_id, 'children', 'descending');
		$from_data = $moved_albums[0];
		$diff = sizeof($moved_albums) * 2;

		$moved_ids = array();
		for ($i = 0; $i < sizeof($moved_albums); ++$i)
		{
			$moved_ids[] = $moved_albums[$i]['album_id'];
		}

		// Resync parents
		$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
			SET right_id = right_id - $diff, album_parents = ''
			WHERE album_user_id = 0
				AND left_id < " . $from_data['right_id'] . "
				AND right_id > " . $from_data['right_id'];
		$db->sql_query($sql);

		// Resync righthand side of tree
		$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
			SET left_id = left_id - $diff, right_id = right_id - $diff, album_parents = ''
			WHERE album_user_id = 0
				AND left_id > " . $from_data['right_id'];
		$db->sql_query($sql);

		if ($to_id > 0)
		{
			// Retrieve $to_data again, it may have been changed...
			$to_data = get_album_info($to_id);

			// Resync new parents
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
				SET right_id = right_id + $diff, album_parents = ''
				WHERE album_user_id = 0
					AND " . $to_data['right_id'] . ' BETWEEN left_id AND right_id
					AND ' . $db->sql_in_set('album_id', $moved_ids, true);
			$db->sql_query($sql);

			// Resync the righthand side of the tree
			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
				SET left_id = left_id + $diff, right_id = right_id + $diff, album_parents = ''
				WHERE album_user_id = 0
					AND left_id > " . $to_data['right_id'] . '
					AND ' . $db->sql_in_set('album_id', $moved_ids, true);
			$db->sql_query($sql);

			// Resync moved branch
			$to_data['right_id'] += $diff;

			if ($to_data['right_id'] > $from_data['right_id'])
			{
				$diff = '+ ' . ($to_data['right_id'] - $from_data['right_id'] - 1);
			}
			else
			{
				$diff = '- ' . abs($to_data['right_id'] - $from_data['right_id'] - 1);
			}
		}
		else
		{
			$sql = 'SELECT MAX(right_id) AS right_id
				FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE album_user_id = 0
					AND ' . $db->sql_in_set('album_id', $moved_ids, true);
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$diff = '+ ' . ($row['right_id'] - $from_data['left_id'] + 1);
		}

		$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
			SET left_id = left_id $diff, right_id = right_id $diff, album_parents = ''
			WHERE album_user_id = 0
				AND " . $db->sql_in_set('forum_id', $moved_ids);
		$db->sql_query($sql);

		return $errors;
	}

	/**
	* Move album content from one to another album
	*
	* borrowed from phpBB3
	* @author: phpBB Group
	* @function: move_forum_content
	*
	* //@todo: Ready!!!
	*/
	function move_album_content($from_id, $to_id, $sync = true)
	{
		global $db;

		//@todo: Are they deleting our logs?
		$sql = 'UPDATE ' . GALLERY_CONTESTS_TABLE . "
			SET contest_album_id = $to_id
			WHERE contest_album_id = $from_id";
		$db->sql_query($sql);

		$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . "
			SET image_album_id = $to_id
			WHERE image_album_id = $from_id";
		$db->sql_query($sql);

		$sql = 'UPDATE ' . GALLERY_REPORTS_TABLE . "
			SET report_album_id = $to_id
			WHERE report_album_id = $from_id";
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . "
			WHERE perm_album_id = $from_id";
		$db->sql_query($sql);

		$table_ary = array(GALLERY_WATCH_TABLE, GALLERY_MODSCACHE_TABLE);
		foreach ($table_ary as $table)
		{
			$sql = "DELETE FROM $table
				WHERE album_id = $from_id";
			$db->sql_query($sql);
		}

		if ($sync)
		{
			// Resync counters
			update_album_info($album_id);
		}

		return array();
	}

	/**
	* Remove complete album
	*
	* borrowed from phpBB3
	* @author: phpBB Group
	* @function: delete_forum
	*
	* //@todo: Ready!!!
	*/
	function delete_album($album_id, $action_images = 'delete', $action_subalbums = 'delete', $images_to_id = 0, $subalbums_to_id = 0)
	{
		global $db, $user, $cache;

		$album_data = get_album_info($album_id);

		$errors = array();
		$log_action_images = $log_action_albums = $images_to_name = $subalbums_to_name = '';
		$album_ids = array($album_id);

		if ($action_images == 'delete')
		{
			$log_action_images = 'IMAGES';
			$errors = array_merge($errors, $this->delete_album_content($album_id));
		}
		else if ($action_images == 'move')
		{
			if (!$images_to_id)
			{
				$errors[] = $user->lang['NO_DESTINATION_ALBUM'];
			}
			else
			{
				$log_action_images = 'MOVE_IMAGES';

				$sql = 'SELECT album_name
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE album_id = ' . $images_to_id;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					$errors[] = $user->lang['NO_ALBUM'];
				}
				else
				{
					$images_to_name = $row['album_name'];
					$errors = array_merge($errors, $this->move_album_content($album_id, $images_to_id));
				}
			}
		}

		if (sizeof($errors))
		{
			return $errors;
		}

		if ($action_subalbums == 'delete')
		{
			$log_action_albums = 'ALBUMS';
			$rows = get_album_branch(0, $album_id, 'children', 'descending', false);

			foreach ($rows as $row)
			{
				$album_ids[] = $row['album_id'];
				$errors = array_merge($errors, $this->delete_album_content($row['album_id']));
			}

			if (sizeof($errors))
			{
				return $errors;
			}

			$diff = sizeof($album_ids) * 2;

			$sql = 'DELETE FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE ' . $db->sql_in_set('album_id', $album_ids);
			$db->sql_query($sql);
		}
		else if ($action_subalbums == 'move')
		{
			if (!$subalbums_to_id)
			{
				$errors[] = $user->lang['NO_DESTINATION_ALBUM'];
			}
			else
			{
				$log_action_albums = 'MOVE_ALBUMS';

				$sql = 'SELECT album_name
					FROM ' . GALLERY_ALBUMS_TABLE . '
					WHERE album_id = ' . $subalbums_to_id;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					$errors[] = $user->lang['NO_ALBUM'];
				}
				else
				{
					$subalbums_to_name = $row['album_name'];

					$sql = 'SELECT album_id
						FROM ' . GALLERY_ALBUMS_TABLE . "
						WHERE parent_id = $forum_id";
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$this->move_album($row['album_id'], $subalbums_to_id);
					}
					$db->sql_freeresult($result);

					// Grab new album data for correct tree updating later
					$album_data = get_album_info($album_id);

					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
						SET parent_id = $subalbums_to_id
						WHERE parent_id = $album_id
							AND album_user_id = 0";
					$db->sql_query($sql);

					$diff = 2;
					$sql = 'DELETE FROM ' . GALLERY_ALBUMS_TABLE . "
						WHERE album_id = $album_id";
					$db->sql_query($sql);
				}
			}

			if (sizeof($errors))
			{
				return $errors;
			}
		}
		else
		{
			$diff = 2;
			$sql = 'DELETE FROM ' . GALLERY_ALBUMS_TABLE . "
				WHERE album_id = $album_id";
			$db->sql_query($sql);
		}

		// Resync tree
		$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
			SET right_id = right_id - $diff
			WHERE left_id < {$album_data['right_id']} AND right_id > {$album_data['right_id']}
				AND album_user_id = 0";
		$db->sql_query($sql);

		$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
			SET left_id = left_id - $diff, right_id = right_id - $diff
			WHERE left_id > {$album_data['right_id']}
				AND album_user_id = 0";
		$db->sql_query($sql);

		$log_action = implode('_', array($log_action_images, $log_action_albums));

		switch ($log_action)
		{
			case 'MOVE_IMAGES_MOVE_ALBUMS':
				add_log('admin', 'LOG_ALBUM_DEL_MOVE_IMAGES_MOVE_ALBUMS', $images_to_name, $subalbums_to_name, $album_data['album_name']);
			break;

			case 'MOVE_IMAGES_ALBUMS':
				add_log('admin', 'LOG_ALBUM_DEL_MOVE_IMAGES_ALBUMS', $images_to_name, $album_data['album_name']);
			break;

			case 'IMAGES_MOVE_ALBUMS':
				add_log('admin', 'LOG_ALBUM_DEL_IMAGES_MOVE_ALBUMS', $subalbums_to_name, $album_data['album_name']);
			break;

			case '_MOVE_ALBUMS':
				add_log('admin', 'LOG_ALBUM_DEL_MOVE_ALBUMS', $subalbums_to_name, $album_data['album_name']);
			break;

			case 'MOVE_IMAGES_':
				add_log('admin', 'LOG_ALBUM_DEL_MOVE_IMAGES', $images_to_name, $album_data['album_name']);
			break;

			case 'IMAGES_ALBUMS':
				add_log('admin', 'LOG_ALBUM_DEL_IMAGES_ALBUMS', $album_data['album_name']);
			break;

			case '_ALBUMS':
				add_log('admin', 'LOG_ALBUM_DEL_ALBUMS', $album_data['album_name']);
			break;

			case 'IMAGES_':
				add_log('admin', 'LOG_ALBUM_DEL_IMAGES', $album_data['album_name']);
			break;

			default:
				add_log('admin', 'LOG_ALBUM_DEL_ALBUM', $album_data['album_name']);
			break;
		}

		return $errors;
	}

	/**
	* Delete album content
	*
	* //@todo: Ready!!!
	*/
	function delete_album_content($album_id)
	{
		global $db, $config;

		// Before we remove anything we make sure we are able to adjust the image counts later. ;)
		$sql = 'SELECT image_user_id
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_album_id = ' . $album_id . '
				AND image_status = ' . IMAGE_APPROVED;
		$result = $db->sql_query($sql);

		$image_counts = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$image_counts[$row['image_user_id']] = (!empty($image_counts[$row['image_user_id']])) ? $image_counts[$row['image_user_id']] + 1 : 1;
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT image_id, image_filename, image_thumbnail, image_album_id
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_album_id = ' . $album_id;
		$result = $db->sql_query($sql);

		$images = $deleted_images = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$images[] = $row;
			$deleted_images[] = $row['image_id'];
		}
		$db->sql_freeresult($result);

		if (count($images) > 0)
		{
			// Delete the files themselves.
			foreach ($images as $row)
			{
				@unlink($phpbb_root_path . GALLERY_CACHE_PATH . $row['image_thumbnail']);
				@unlink($phpbb_root_path . GALLERY_MEDIUM_PATH . $row['image_filename']);
				@unlink($phpbb_root_path . GALLERY_UPLOAD_PATH . $row['image_filename']);
			}
			$sql = 'DELETE FROM ' . GALLERY_COMMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('comment_image_id', $deleted_images);
			$db->sql_query($sql);
			$sql = 'DELETE FROM ' . GALLERY_FAVORITES_TABLE . '
				WHERE ' . $db->sql_in_set('image_id', $deleted_images);
			$db->sql_query($sql);
			$sql = 'DELETE FROM ' . GALLERY_RATES_TABLE . '
				WHERE ' . $db->sql_in_set('rate_image_id', $deleted_images);
			$db->sql_query($sql);
			$sql = 'DELETE FROM ' . GALLERY_REPORTS_TABLE . '
				WHERE ' . $db->sql_in_set('report_image_id', $deleted_images);
			$db->sql_query($sql);
			$sql = 'DELETE FROM ' . GALLERY_WATCH_TABLE . '
				WHERE ' . $db->sql_in_set('image_id', $deleted_images);
			$db->sql_query($sql);
			$sql = 'DELETE FROM ' . GALLERY_IMAGES_TABLE . '
				WHERE ' . $db->sql_in_set('image_id', $deleted_images);
			$db->sql_query($sql);
		}

		//@todo: Are they deleting our logs?
		$table_ary = array(GALLERY_WATCH_TABLE, GALLERY_MODSCACHE_TABLE);

		foreach ($table_ary as $table)
		{
			$db->sql_query("DELETE FROM $table WHERE album_id = $album_id");
		}
		$sql = 'DELETE FROM ' . GALLERY_PERMISSIONS_TABLE . ' WHERE perm_album_id = ' . (int) $album_id;
		$db->sql_query($sql);
		$sql = 'DELETE FROM ' . GALLERY_CONTESTS_TABLE . ' WHERE contest_album_id = ' . (int) $album_id;
		$db->sql_query($sql);

		// Adjust users image counts
		if (sizeof($image_counts))
		{
			foreach ($image_counts as $image_user_id => $substract)
			{
				$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
					SET user_images = 0
					WHERE user_id = ' . $image_user_id . '
						AND user_images < ' . $substract;
				$db->sql_query($sql);

				$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
					SET user_images = user_images - ' . $substract . '
					WHERE user_id = ' . $image_user_id . '
						AND user_images >= ' . $substract;
				$db->sql_query($sql);
			}
		}

		// Make sure the overall image count is correct...
		$sql = 'SELECT COUNT(image_id) AS stat
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_status = ' . IMAGE_APPROVED;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		set_config('num_images', (int) $row['stat'], true);

		return array();
	}

	/**
	* Move album position by $steps up/down
	*
	* borrowed from phpBB3
	* @author: phpBB Group
	* @function: move_forum_by
	*
	* //@todo: Ready!!!
	*/
	function move_album_by($album_row, $action = 'move_up', $steps = 1)
	{
		global $db;

		/**
		* Fetch all the siblings between the module's current spot
		* and where we want to move it to. If there are less than $steps
		* siblings between the current spot and the target then the
		* module will move as far as possible
		*/
		$sql = 'SELECT album_id, album_name, left_id, right_id
			FROM ' . GALLERY_ALBUMS_TABLE . "
			WHERE parent_id = {$album_row['parent_id']}
				AND " . (($action == 'move_up') ? "right_id < {$album_row['right_id']} ORDER BY right_id DESC" : "left_id > {$album_row['left_id']} ORDER BY left_id ASC");
		$result = $db->sql_query_limit($sql, $steps);

		$target = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$target = $row;
		}
		$db->sql_freeresult($result);

		if (!sizeof($target))
		{
			// The album is already on top or bottom
			return false;
		}

		/**
		* $left_id and $right_id define the scope of the nodes that are affected by the move.
		* $diff_up and $diff_down are the values to substract or add to each node's left_id
		* and right_id in order to move them up or down.
		* $move_up_left and $move_up_right define the scope of the nodes that are moving
		* up. Other nodes in the scope of ($left_id, $right_id) are considered to move down.
		*/
		if ($action == 'move_up')
		{
			$left_id = $target['left_id'];
			$right_id = $album_row['right_id'];

			$diff_up = $album_row['left_id'] - $target['left_id'];
			$diff_down = $album_row['right_id'] + 1 - $album_row['left_id'];

			$move_up_left = $album_row['left_id'];
			$move_up_right = $album_row['right_id'];
		}
		else
		{
			$left_id = $album_row['left_id'];
			$right_id = $target['right_id'];

			$diff_up = $album_row['right_id'] + 1 - $album_row['left_id'];
			$diff_down = $target['right_id'] - $album_row['right_id'];

			$move_up_left = $album_row['right_id'] + 1;
			$move_up_right = $target['right_id'];
		}

		// Now do the dirty job
		$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
			SET left_id = left_id + CASE
				WHEN left_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			right_id = right_id + CASE
				WHEN right_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			album_parents = ''
			WHERE
				left_id BETWEEN {$left_id} AND {$right_id}
				AND right_id BETWEEN {$left_id} AND {$right_id}
				AND album_user_id = 0";
		$db->sql_query($sql);

		return $target['album_name'];
	}

	/**
	* Display progress bar for syncinc albums
	*
	* borrowed from phpBB3
	* @author: phpBB Group
	* @function: display_progress_bar
	*
	* //@todo: Ready!!!
	*/
	function display_progress_bar($start, $total)
	{
		global $template, $user;

		adm_page_header($user->lang['SYNC_IN_PROGRESS']);

		$template->set_filenames(array(
			'body'	=> 'progress_bar.html',
		));

		$template->assign_vars(array(
			'L_PROGRESS'			=> $user->lang['SYNC_IN_PROGRESS'],
			'L_PROGRESS_EXPLAIN'	=> ($start && $total) ? sprintf($user->lang['SYNC_IN_PROGRESS_EXPLAIN'], $start, $total) : $user->lang['SYNC_IN_PROGRESS'])
		);

		adm_page_footer();
	}
}

?>