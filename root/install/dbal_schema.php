<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2011 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}
if (!defined('IN_INSTALL'))
{
	exit;
}

class phpbb_gallery_dbal_schema
{
	static public function get_table_data($table)
	{
		return self::$data[$table]['structure'];
	}

	/**
	* Column Types:
	*	INT:x		=> SIGNED int(x)
	*	BINT		=> BIGINT
	*	UINT		=> mediumint(8) UNSIGNED
	*	UINT:x		=> int(x) UNSIGNED
	*	TINT:x		=> tinyint(x)
	*	USINT		=> smallint(4) UNSIGNED (for _order columns)
	*	BOOL		=> tinyint(1) UNSIGNED
	*	VCHAR		=> varchar(255)
	*	CHAR:x		=> char(x)
	*	XSTEXT_UNI	=> text for storing 100 characters (topic_title for example)
	*	STEXT_UNI	=> text for storing 255 characters (normal input field with a max of 255 single-byte chars) - same as VCHAR_UNI
	*	TEXT_UNI	=> text for storing 3000 characters (short text, descriptions, comments, etc.)
	*	MTEXT_UNI	=> mediumtext (post text, large text)
	*	VCHAR:x		=> varchar(x)
	*	TIMESTAMP	=> int(11) UNSIGNED
	*	DECIMAL		=> decimal number (5,2)
	*	DECIMAL:	=> decimal number (x,2)
	*	PDECIMAL	=> precision decimal number (6,3)
	*	PDECIMAL:	=> precision decimal number (x,3)
	*	VCHAR_UNI	=> varchar(255) BINARY
	*	VCHAR_CI	=> varchar_ci for postgresql, others VCHAR
	*/
	static private $data = array(
		'albums'	=> array(
			'full_name'		=> GALLERY_ALBUMS_TABLE,
			'added'			=> '0.0.0',
			'modified'		=> '1.0.4',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'album_id'					=> array('UINT', NULL, 'auto_increment'),
					'parent_id'					=> array('UINT', 0),
					'left_id'					=> array('UINT', 1),
					'right_id'					=> array('UINT', 2),
					'album_parents'				=> array('MTEXT_UNI', ''),
					'album_type'				=> array('UINT:3', 1),
					'album_status'				=> array('UINT:1', 1),
					'album_contest'				=> array('UINT', 0),
					'album_name'				=> array('VCHAR:255', ''),
					'album_desc'				=> array('MTEXT_UNI', ''),
					'album_desc_options'		=> array('UINT:3', 7),
					'album_desc_uid'			=> array('VCHAR:8', ''),
					'album_desc_bitfield'		=> array('VCHAR:255', ''),
					'album_user_id'				=> array('UINT', 0),
					'album_images'				=> array('UINT', 0),
					'album_images_real'			=> array('UINT', 0),
					'album_last_image_id'		=> array('UINT', 0),
					'album_image'				=> array('VCHAR', ''),
					'album_last_image_time'		=> array('INT:11', 0),
					'album_last_image_name'		=> array('VCHAR', ''),
					'album_last_username'		=> array('VCHAR', ''),
					'album_last_user_colour'	=> array('VCHAR:6', ''),
					'album_last_user_id'		=> array('UINT', 0),
					'album_watermark'			=> array('UINT:1', 1),
					'album_sort_key'			=> array('VCHAR:8', ''),
					'album_sort_dir'			=> array('VCHAR:8', ''),
					'display_in_rrc'			=> array('UINT:1', 1),
					'display_on_index'			=> array('UINT:1', 1),
					'display_subalbum_list'		=> array('UINT:1', 1),
				),
				'PRIMARY_KEY'	=> 'album_id',
			),
		),
		'albums_track'	=> array(
			'full_name'		=> GALLERY_ATRACK_TABLE,
			'added'			=> '0.5.2',
			'modified'		=> '0.5.2',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'user_id'				=> array('UINT', 0),
					'album_id'				=> array('UINT', 0),
					'mark_time'				=> array('TIMESTAMP', 0),
				),
				'PRIMARY_KEY'	=> array('user_id', 'album_id'),
			),
		),
		'comments'	=> array(
			'full_name'		=> GALLERY_COMMENTS_TABLE,
			'added'			=> '0.0.0',
			'modified'		=> '0.3.1',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'comment_id'			=> array('UINT', NULL, 'auto_increment'),
					'comment_image_id'		=> array('UINT', NULL),
					'comment_user_id'		=> array('UINT', 0),
					'comment_username'		=> array('VCHAR', ''),
					'comment_user_colour'	=> array('VCHAR:6', ''),
					'comment_user_ip'		=> array('VCHAR:40', ''),
					'comment_time'			=> array('UINT:11', 0),
					'comment'				=> array('MTEXT_UNI', ''),
					'comment_uid'			=> array('VCHAR:8', ''),
					'comment_bitfield'		=> array('VCHAR:255', ''),
					'comment_edit_time'		=> array('UINT:11', 0),
					'comment_edit_count'	=> array('USINT', 0),
					'comment_edit_user_id'	=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> 'comment_id',
				'KEYS'		=> array(
					'comment_image_id'		=> array('INDEX', 'comment_image_id'),
					'comment_user_id'		=> array('INDEX', 'comment_user_id'),
					'comment_user_ip'		=> array('INDEX', 'comment_user_ip'),
					'comment_time'			=> array('INDEX', 'comment_time'),
				),
			),
		),
		'config'	=> array(
			'full_name'		=> GALLERY_CONFIG_TABLE,
			'added'			=> '0.0.0',
			'modified'		=> '0.0.0',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'config_name'		=> array('VCHAR:255', ''),
					'config_value'		=> array('VCHAR:255', ''),
				),
				'PRIMARY_KEY'	=> 'config_name',
			),
		),
		'contests'	=> array(
			'full_name'		=> GALLERY_CONTEST_TABLE,
			'added'			=> '0.4.1',
			'modified'		=> '0.4.1',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'contest_id'			=> array('UINT', NULL, 'auto_increment'),
					'contest_album_id'		=> array('UINT', 0),
					'contest_start'			=> array('UINT:11', 0),
					'contest_rating'		=> array('UINT:11', 0),
					'contest_end'			=> array('UINT:11', 0),
					'contest_marked'		=> array('TINT:1', 0),
					'contest_first'			=> array('UINT', 0),
					'contest_second'		=> array('UINT', 0),
					'contest_third'			=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> 'contest_id',
			),
		),
		'copyts_albums'	=> array(
			'full_name'		=> 'phpbb_gallery_copyts_albums',
			'added'			=> '0.0.0',
			'modified'		=> '0.0.0',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'album_id'				=> array('UINT', NULL, 'auto_increment'),
					'parent_id'				=> array('UINT', 0),
					'left_id'				=> array('UINT', 1),
					'right_id'				=> array('UINT', 2),
					'album_name'			=> array('VCHAR:255', ''),
					'album_desc'			=> array('MTEXT_UNI', ''),
					'album_user_id'			=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> 'album_id',
			),
		),
		'copyts_users'	=> array(
			'full_name'		=> 'phpbb_gallery_copyts_users',
			'added'			=> '0.0.0',
			'modified'		=> '0.0.0',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'user_id'			=> array('UINT', 0),
					'personal_album_id'	=> array('UINT', 0),
				),
				'PRIMARY_KEY'		=> 'user_id',
			),
		),
		'favorites'	=> array(
			'full_name'		=> GALLERY_FAVORITES_TABLE,
			'added'			=> '0.3.1',
			'modified'		=> '0.3.1',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'favorite_id'			=> array('UINT', NULL, 'auto_increment'),
					'user_id'				=> array('UINT', 0),
					'image_id'				=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> 'favorite_id',
				'KEYS'		=> array(
					'user_id'		=> array('INDEX', 'user_id'),
					'image_id'		=> array('INDEX', 'image_id'),
				),
			),
		),
		'images'	=> array(
			'full_name'		=> GALLERY_IMAGES_TABLE,
			'added'			=> '0.0.0',
			'modified'		=> '1.0.0',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'image_id'				=> array('UINT', NULL, 'auto_increment'),
					'image_filename'		=> array('VCHAR:255', ''),
					'image_thumbnail'		=> array('VCHAR:255', ''),
					'image_name'			=> array('VCHAR:255', ''),
					'image_name_clean'		=> array('VCHAR:255', ''),
					'image_desc'			=> array('MTEXT_UNI', ''),
					'image_desc_uid'		=> array('VCHAR:8', ''),
					'image_desc_bitfield'	=> array('VCHAR:255', ''),
					'image_user_id'			=> array('UINT', 0),
					'image_username'		=> array('VCHAR:255', ''),
					'image_username_clean'	=> array('VCHAR:255', ''),
					'image_user_colour'		=> array('VCHAR:6', ''),
					'image_user_ip'			=> array('VCHAR:40', ''),
					'image_time'			=> array('UINT:11', 0),
					'image_album_id'		=> array('UINT', 0),
					'image_view_count'		=> array('UINT:11', 0),
					'image_status'			=> array('UINT:3', 0),
					'image_contest'			=> array('UINT:1', 0),
					'image_contest_end'		=> array('TIMESTAMP', 0),
					'image_contest_rank'	=> array('UINT:3', 0),
					'image_filemissing'		=> array('UINT:3', 0),
					'image_has_exif'		=> array('UINT:3', 2),
					'image_exif_data'		=> array('TEXT', ''),
					'image_rates'			=> array('UINT', 0),
					'image_rate_points'		=> array('UINT', 0),
					'image_rate_avg'		=> array('UINT', 0),
					'image_comments'		=> array('UINT', 0),
					'image_last_comment'	=> array('UINT', 0),
					'image_favorited'		=> array('UINT', 0),
					'image_reported'		=> array('UINT', 0),
					'filesize_upload'		=> array('UINT:20', 0),
					'filesize_medium'		=> array('UINT:20', 0),
					'filesize_cache'		=> array('UINT:20', 0),
				),
				'PRIMARY_KEY'				=> 'image_id',
				'KEYS'		=> array(
					'image_album_id'		=> array('INDEX', 'image_album_id'),
					'image_user_id'			=> array('INDEX', 'image_user_id'),
					'image_time'			=> array('INDEX', 'image_time'),
				),
			),
		),
		'modscache'	=> array(
			'full_name'		=> GALLERY_MODSCACHE_TABLE,
			'added'			=> '0.3.1',
			'modified'		=> '0.3.1',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'album_id'				=> array('UINT', 0),
					'user_id'				=> array('UINT', 0),
					'username'				=> array('VCHAR', ''),
					'group_id'				=> array('UINT', 0),
					'group_name'			=> array('VCHAR', ''),
					'display_on_index'		=> array('TINT:1', 1),
				),
				'KEYS'		=> array(
					'disp_idx'		=> array('INDEX', 'display_on_index'),
					'album_id'		=> array('INDEX', 'album_id'),
				),
			),
		),
		'permissions'	=> array(
			'full_name'		=> GALLERY_PERMISSIONS_TABLE,
			'added'			=> '0.3.1',
			'modified'		=> '0.4.1',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'perm_id'			=> array('UINT', NULL, 'auto_increment'),
					'perm_role_id'		=> array('UINT', 0),
					'perm_album_id'		=> array('UINT', 0),
					'perm_user_id'		=> array('UINT', 0),
					'perm_group_id'		=> array('UINT', 0),
					'perm_system'		=> array('INT:3', 0),
				),
				'PRIMARY_KEY'			=> 'perm_id',
			),
		),
		'rates'	=> array(
			'full_name'		=> GALLERY_RATES_TABLE,
			'added'			=> '0.0.0',
			'modified'		=> '0.0.0',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'rate_image_id'		=> array('UINT', NULL, 'auto_increment'),
					'rate_user_id'		=> array('UINT', 0),
					'rate_user_ip'		=> array('VCHAR:40', ''),
					'rate_point'		=> array('UINT:3', 0),
				),
				'KEYS'		=> array(
					'rate_image_id'		=> array('INDEX', 'rate_image_id'),
					'rate_user_id'		=> array('INDEX', 'rate_user_id'),
					'rate_user_ip'		=> array('INDEX', 'rate_user_ip'),
					'rate_point'		=> array('INDEX', 'rate_point'),
				),
			),
		),
		'reports'	=> array(
			'full_name'		=> GALLERY_REPORTS_TABLE,
			'added'			=> '0.3.1',
			'modified'		=> '0.3.1',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'report_id'				=> array('UINT', NULL, 'auto_increment'),
					'report_album_id'		=> array('UINT', 0),
					'report_image_id'		=> array('UINT', 0),
					'reporter_id'			=> array('UINT', 0),
					'report_manager'		=> array('UINT', 0),
					'report_note'			=> array('MTEXT_UNI', ''),
					'report_time'			=> array('UINT:11', 0),
					'report_status'			=> array('UINT:3', 0),
				),
				'PRIMARY_KEY'	=> 'report_id',
			),
		),
		'roles'	=> array(
			'full_name'		=> GALLERY_ROLES_TABLE,
			'added'			=> '0.3.1',
			'modified'		=> '0.5.4',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'role_id'			=> array('UINT', NULL, 'auto_increment'),
					'a_list'			=> array('UINT:3', 0),
					'i_view'			=> array('UINT:3', 0),
					'i_watermark'		=> array('UINT:3', 0),
					'i_upload'			=> array('UINT:3', 0),
					'i_edit'			=> array('UINT:3', 0),
					'i_delete'			=> array('UINT:3', 0),
					'i_rate'			=> array('UINT:3', 0),
					'i_approve'			=> array('UINT:3', 0),
					'i_lock'			=> array('UINT:3', 0),
					'i_report'			=> array('UINT:3', 0),
					'i_count'			=> array('UINT', 0),
					'i_unlimited'		=> array('UINT:3', 0),
					'c_read'			=> array('UINT:3', 0),
					'c_post'			=> array('UINT:3', 0),
					'c_edit'			=> array('UINT:3', 0),
					'c_delete'			=> array('UINT:3', 0),
					'm_comments'		=> array('UINT:3', 0),
					'm_delete'			=> array('UINT:3', 0),
					'm_edit'			=> array('UINT:3', 0),
					'm_move'			=> array('UINT:3', 0),
					'm_report'			=> array('UINT:3', 0),
					'm_status'			=> array('UINT:3', 0),
					'album_count'		=> array('UINT', 0),
					'album_unlimited'	=> array('UINT:3', 0),
				),
				'PRIMARY_KEY'		=> 'role_id',
			),
		),
		'users'	=> array(
			'full_name'		=> GALLERY_USERS_TABLE,
			'added'			=> '0.3.1',
			'modified'		=> '1.0.4',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'user_id'			=> array('UINT', 0),
					'watch_own'			=> array('UINT:3', 0),
					'watch_favo'		=> array('UINT:3', 0),
					'watch_com'			=> array('UINT:3', 0),
					'user_images'		=> array('UINT', 0),
					'personal_album_id'	=> array('UINT', 0),
					'user_lastmark'		=> array('TIMESTAMP', 0),
					'user_last_update'	=> array('TIMESTAMP', 0),
					'user_viewexif'		=> array('UINT:1', 0),
					'user_permissions'	=> array('MTEXT_UNI', ''),
				),
				'PRIMARY_KEY'		=> 'user_id',
			),
		),
		'watch'	=> array(
			'full_name'		=> GALLERY_WATCH_TABLE,
			'added'			=> '0.3.1',
			'modified'		=> '0.3.1',
			'structure'		=> array(
				'COLUMNS'		=> array(
					'watch_id'		=> array('UINT', NULL, 'auto_increment'),
					'album_id'		=> array('UINT', 0),
					'image_id'		=> array('UINT', 0),
					'user_id'		=> array('UINT', 0),
				),
				'PRIMARY_KEY'		=> 'watch_id',
				'KEYS'		=> array(
					'user_id'			=> array('INDEX', 'user_id'),
					'image_id'			=> array('INDEX', 'image_id'),
					'album_id'			=> array('INDEX', 'album_id'),
				),
			),
		),
	);
}
