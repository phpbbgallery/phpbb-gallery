<?php

/**
*
* @package phpBB3 - gallery
* @version $Id: gallery_acp.php 347 2008-03-03 17:39:58Z nickvergessen $
* @copyright (c) 2007 nickvergessen
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/
if (!defined('IN_PHPBB')) 
{ 
	exit; 
}
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'EXIF-DATA'					=> 'EXIF-Data',
	'EXIF_APERTURE'				=> 'F-number',
	'EXIF_CAM_MODEL'			=> 'Camera-model',
	'EXIF_DATE'					=> 'Image taken on',

	'EXIF_EXPOSURE'				=> 'Shutter speed',
		'EXIF_EXPOSURE_EXP'			=> '%s Sec',// 'EXIF_EXPOSURE' unit
	'EXIF_EXPOSURE_BIAS'		=> 'Exposure bias',
		'EXIF_EXPOSURE_BIAS_EXP'	=> '%s EV',// 'EXIF_EXPOSURE_BIAS' unit
	'EXIF_EXPOSURE_PROG'		=> 'Exposure program',
		'EXIF_EXPOSURE_PROG_0'		=> 'Not defined',
		'EXIF_EXPOSURE_PROG_1'		=> 'Manual',
		'EXIF_EXPOSURE_PROG_2'		=> 'Normal program',
		'EXIF_EXPOSURE_PROG_3'		=> 'Aperture priority',
		'EXIF_EXPOSURE_PROG_4'		=> 'Shutter priority',
		'EXIF_EXPOSURE_PROG_5'		=> 'Creative program (biased toward depth of field)',
		'EXIF_EXPOSURE_PROG_6'		=> 'Action program (biased toward fast shutter speed)',
		'EXIF_EXPOSURE_PROG_7'		=> 'Portrait mode (for closeup photos with the background out of focus)',
		'EXIF_EXPOSURE_PROG_8'		=> 'Landscape mode (for landscape photos with the background in focus)',

	'EXIF_FLASH'				=> 'Flash',

		'EXIF_FLASH_CASE_0'			=> 'Flash did not fire',
		'EXIF_FLASH_CASE_1'			=> 'Flash fired',
		'EXIF_FLASH_CASE_5'			=> 'return light not detected',
		'EXIF_FLASH_CASE_7'			=> 'return light detected',
		'EXIF_FLASH_CASE_8'			=> 'On, Flash did not fire',
		'EXIF_FLASH_CASE_9'			=> 'Flash fired, compulsory flash mode',
		'EXIF_FLASH_CASE_13'		=> 'Flash fired, compulsory flash mode, return light not detected',
		'EXIF_FLASH_CASE_15'		=> 'Flash fired, compulsory flash mode, return light detected',
		'EXIF_FLASH_CASE_16'		=> 'Flash did not fire, compulsory flash mode',
		'EXIF_FLASH_CASE_20'		=> 'Off, Flash did not fire, return light not detected',
		'EXIF_FLASH_CASE_24'		=> 'Flash did not fire, auto mode',
		'EXIF_FLASH_CASE_25'		=> 'Flash fired, auto mode',
		'EXIF_FLASH_CASE_29'		=> 'Flash fired, auto mode, return light not detected',
		'EXIF_FLASH_CASE_31'		=> 'Flash fired, auto mode, return light detected',
		'EXIF_FLASH_CASE_32'		=> 'No flash function',
		'EXIF_FLASH_CASE_48'		=> 'Off, No flash function',
		'EXIF_FLASH_CASE_65'		=> 'Flash fired, red-eye reduction mode',
		'EXIF_FLASH_CASE_69'		=> 'Flash fired, red-eye reduction mode, return light not detected',
		'EXIF_FLASH_CASE_71'		=> 'Flash fired, red-eye reduction mode, return light detected',
		'EXIF_FLASH_CASE_73'		=> 'Flash fired, compulsory flash mode, red-eye reduction mode',
		'EXIF_FLASH_CASE_77'		=> 'Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected',
		'EXIF_FLASH_CASE_79'		=> 'Flash fired, compulsory flash mode, red-eye reduction mode, return light detected',
		'EXIF_FLASH_CASE_80'		=> 'Off, Red-eye reduction',
		'EXIF_FLASH_CASE_88'		=> 'Auto, Did not fire, Red-eye reduction',
		'EXIF_FLASH_CASE_89'		=> 'Flash fired, auto mode, red-eye reduction mode',
		'EXIF_FLASH_CASE_93'		=> 'Flash fired, auto mode, return light not detected, red-eye reduction mode',
		'EXIF_FLASH_CASE_95'		=> 'Flash fired, auto mode, return light detected, red-eye reduction mode',

	'EXIF_FOCAL'				=> 'Focus length',
		'EXIF_FOCAL_EXP'			=> '%s mm',// 'EXIF_FOCAL' unit

	'EXIF_ISO'					=> 'ISO speed rating',

	'EXIF_METERING_MODE'		=> 'Metering mode',
		'EXIF_METERING_MODE_0'		=> 'Unknown',
		'EXIF_METERING_MODE_1'		=> 'Average',
		'EXIF_METERING_MODE_2'		=> 'Center-weighted average',
		'EXIF_METERING_MODE_3'		=> 'Spot',
		'EXIF_METERING_MODE_4'		=> 'Multi-Spot',
		'EXIF_METERING_MODE_5'		=> 'Pattern',
		'EXIF_METERING_MODE_6'		=> 'Partial',
		'EXIF_METERING_MODE_255'	=> 'Other',

	'EXIF_NOT_AVAILABLE'		=> 'not available',

	'EXIF_WHITEB'				=> 'Whitebalance',
		'EXIF_WHITEB_AUTO'			=> 'Auto',
		'EXIF_WHITEB_MANU'			=> 'Manual',

	'SHOW_EXIF'					=> 'show/hide',
));

?>