<?php
/**
*
* gallery_mcp [English]
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'CHOOSE_ACTION'					=> 'Select desired action',#

	'GALLERY_MCP_MAIN'				=> 'Main',#
	'GALLERY_MCP_QUEUE'				=> 'Queue',#
	'GALLERY_MCP_QUEUE_DETAIL'		=> 'Image details',#
	'GALLERY_MCP_REPORTED'			=> 'Reported images',#
	'GALLERY_MCP_REPO_DONE'			=> 'Closed reports',#
	'GALLERY_MCP_REPO_OPEN'			=> 'Open reports',#
	'GALLERY_MCP_REPO_DETAIL'		=> 'Report details',#
	'GALLERY_MCP_UNAPPROVED'		=> 'Images awaiting approval',#
	'GALLERY_MCP_APPROVED'			=> 'Approved images',#
	'GALLERY_MCP_LOCKED'			=> 'Locked images',#
	'GALLERY_MCP_VIEWALBUM'			=> 'View album',#

	'IMAGE_REPORTED'				=> 'This image is reported.',#
	'IMAGE_UNAPPROVED'				=> 'This image is awaiting approval.',#

	'MODERATE_ALBUM'				=> 'Moderate album',#

	'QUEUE_A_APPROVE'				=> 'Approve image',#
	'QUEUE_A_APPROVE2'				=> 'Approve image?',#
	'QUEUE_A_APPROVE2_CONFIRM'		=> 'Are you sure, you want to approve this image?',#
	'QUEUE_A_DELETE'				=> 'Delete image',#
	'QUEUE_A_DELETE2'				=> 'Delete image?',#
	'QUEUE_A_DELETE2_CONFIRM'		=> 'Are you sure, you want to delete this image?',#
	'QUEUE_A_LOCK'					=> 'Lock image',#
	'QUEUE_A_LOCK2'					=> 'Lock image?',#
	'QUEUE_A_LOCK2_CONFIRM'			=> 'Are you sure, you want to lock this image?',#
	'QUEUE_A_MOVE'					=> 'Move image',#
	'QUEUE_A_UNAPPROVE'				=> 'Disapprove image',#
	'QUEUE_A_UNAPPROVE2'			=> 'Disapprove image?',#
	'QUEUE_A_UNAPPROVE2_CONFIRM'	=> 'Are you sure, you want to disapprove this image?',#

	'QUEUE_STATUS_0'				=> 'This image is waiting for approval.',#
	'QUEUE_STATUS_1'				=> 'This image is approved.',#
	'QUEUE_STATUS_2'				=> 'This image is locked.',#

	'QUEUES_A_APPROVE'				=> 'Approve images',#
	'QUEUES_A_APPROVE2'				=> 'Approve images?',#
	'QUEUES_A_APPROVE2_CONFIRM'		=> 'Are you sure, you want to approve these images?',#
	'QUEUES_A_DELETE'				=> 'Delete images',#
	'QUEUES_A_DELETE2'				=> 'Delete images?',#
	'QUEUES_A_DELETE2_CONFIRM'		=> 'Are you sure, you want to delete these images?',#
	'QUEUES_A_LOCK'					=> 'Lock images',#
	'QUEUES_A_LOCK2'				=> 'Lock images?',#
	'QUEUES_A_LOCK2_CONFIRM'		=> 'Are you sure, you want to lock these images?',#
	'QUEUES_A_MOVE'					=> 'Move images',#
	'QUEUES_A_UNAPPROVE'			=> 'Disapprove images',#
	'QUEUES_A_UNAPPROVE2'			=> 'Disapprove images?',#
	'QUEUES_A_UNAPPROVE2_CONFIRM'	=> 'Are you sure, you want to disapprove these images?',#

	'REPORT_A_CLOSE'				=> 'Close report',#
	'REPORT_A_CLOSE2'				=> 'Close report?',#
	'REPORT_A_CLOSE2_CONFIRM'		=> 'Are you sure, you want to close this report?',#
	'REPORT_A_DELETE'				=> 'Delete report',#
	'REPORT_A_DELETE2'				=> 'Delete report?',#
	'REPORT_A_DELETE2_CONFIRM'		=> 'Are you sure, you want to delete this report?',#
	'REPORT_A_OPEN'					=> 'Open report',#
	'REPORT_A_OPEN2'				=> 'Open report?',#
	'REPORT_A_OPEN2_CONFIRM'		=> 'Are you sure, you want to open this report?',#

	'REPORT_STATUS_1'				=> 'This report is needs to be reviewed.',#
	'REPORT_STATUS_2'				=> 'This report is closed.',#

	'REPORTS_A_CLOSE'				=> 'Close reports',#
	'REPORTS_A_CLOSE2'				=> 'Close reports?',#
	'REPORTS_A_CLOSE2_CONFIRM'		=> 'Are you sure, you want to close these reports?',#
	'REPORTS_A_DELETE'				=> 'Delete reports',#
	'REPORTS_A_DELETE2'				=> 'Delete reports?',#
	'REPORTS_A_DELETE2_CONFIRM'		=> 'Are you sure, you want to delete these reports?',#
	'REPORTS_A_OPEN'				=> 'Open reports',#
	'REPORTS_A_OPEN2'				=> 'Open reports?',#
	'REPORTS_A_OPEN2_CONFIRM'		=> 'Are you sure, you want to open these reports?',#

	'REPORT_MOD'					=> 'Edited by',#
	'REPORTED_IMAGES'				=> 'Reported images',#
	'REPORTER'						=> 'Reporting user',#
	'REPORTER_AND_ALBUM'			=> 'Reporter & Album',#

	'UPLOADED_BY'					=> 'Uploaded by',#

	'WAITING_APPROVED_IMAGE'		=> 'In total there is <span style="font-weight: bold;">%s</span> image approved..',#
	'WAITING_APPROVED_IMAGES'		=> 'In total there are <span style="font-weight: bold;">%s</span> images approved.',#
	'WAITING_APPROVED_NONE'			=> 'No images approved.',#
	'WAITING_LOCKED_IMAGE'			=> 'In total there is <span style="font-weight: bold;">%s</span> image locked.',#
	'WAITING_LOCKED_IMAGES'			=> 'In total there are <span style="font-weight: bold;">%s</span> images locked.',#
	'WAITING_LOCKED_NONE'			=> 'No images locked.',#
	'WAITING_REPORTED_DONE'			=> 'No reports reviewed.',#
	'WAITING_REPORTED_IMAGE'		=> 'In total there is <span style="font-weight: bold;">%s</span> report to review.',#
	'WAITING_REPORTED_IMAGES'		=> 'In total there are <span style="font-weight: bold;">%s</span> reports to review.',#
	'WAITING_REPORTED_NONE'			=> 'No reports to review.',#
	'WAITING_UNAPPROVED_IMAGE'		=> 'In total there is <span style="font-weight: bold;">%s</span> image waiting for approval.',#
	'WAITING_UNAPPROVED_IMAGES'		=> 'In total there are <span style="font-weight: bold;">%s</span> images waiting for approval.',#
	'WAITING_UNAPPROVED_NONE'		=> 'No images waiting for approval.',#
));

?>