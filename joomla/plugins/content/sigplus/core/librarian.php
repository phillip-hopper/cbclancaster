<?php
/**
* @file
* @brief    sigplus Image Gallery Plus library dependencies
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2023 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see https://www.gnu.org/licenses/gpl-3.0.html
* @see      https://hunyadi.info.hu/sigplus
*/

/*
* sigplus Image Gallery Plus plug-in for Joomla
* Copyright 2009-2023 Levente Hunyadi
*
* sigplus is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* sigplus is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Whether server has GD library enabled with JPEG, PNG and GIF read support.
*/
function is_gd_supported() {
	static $supported = null;
	if (isset($supported)) {
		return $supported;
	}

	$supported = extension_loaded('gd');
	if (!$supported) {
		return false;
	}

	$supported = function_exists('gd_info');  // might fail in rare cases even if GD is available
	if (!$supported) {
		return false;
	}
	$gd = gd_info();
	$supported = isset($gd['GIF Read Support']) && $gd['GIF Read Support']
			&& isset($gd['GIF Create Support']) && $gd['GIF Create Support']
			&& (isset($gd['JPG Support']) && $gd['JPG Support'] || isset($gd['JPEG Support']) && $gd['JPEG Support'])
			&& isset($gd['PNG Support']) && $gd['PNG Support'];
	return $supported;
}

/**
* Whether server has ImageMagick library support enabled.
*/
function is_imagick_supported() {
	static $supported = null;
	if (isset($supported)) {
		return $supported;
	}

	$supported = extension_loaded('imagick');
	if (!$supported) {
		return false;
	}

	$supported = class_exists('Imagick');
	if (!$supported) {
		return false;
	}

	// see: https://www.imagemagick.org/script/formats.php
	$formats = Imagick::queryformats();
	$supported = in_array('GIF', $formats, true) && in_array('JPEG', $formats, true) && in_array('PNG', $formats, true);
	return $supported;
}

/**
* Whether server has GraphicsMagick library support enabled.
*/
function is_gmagick_supported() {
	static $supported = null;
	if (isset($supported)) {
		return $supported;
	}

	$supported = extension_loaded('gmagick');
	if (!$supported) {
		return false;
	}

	$supported = class_exists('Gmagick');
	if (!$supported) {
		return false;
	}

	$image = new Gmagick();
	$formats = $image->queryformats();
	$supported = in_array('GIF', $formats, true) && in_array('JPEG', $formats, true) && in_array('PNG', $formats, true);
	return $supported;
}
