<?php
/**
* @file
* @brief    sigplus Image Gallery Plus user agent detection
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
* User agent detection and support.
*/
class SigPlusNovoUserAgent {
	/**
	* True if the user agent is a mobile device.
	*/
	public static function handheld() {
		$params = self::getParameters();
		return $params->handheld;
	}

	/**
	* Returns the parameters of the user agent.
	*/
	public static function getParameters() {
		static $params = null;
		if (isset($params)) {
			return $params;
		}

		jimport('joomla.environment.browser');
		$browser = Joomla\CMS\Environment\Browser::getInstance();
		$agent = $browser->getAgentString();

		$params = new stdClass;
		if ($browser->isMobile() || stripos($agent, 'mobile') !== false || stripos($agent, 'android') !== false || stripos($agent, 'lumia') !== false) {
			$params->handheld = true;
			$params->browser = $browser->getBrowser();

			if (stripos($agent, 'ipad') !== false) {
				// iPad
				$params->name = 'ipad';
			} elseif (stripos($agent, 'iphone') !== false) {
				// iPhone
				$params->name = 'iphone';
			} elseif (stripos($agent, 'ipod') !== false) {
				// iPod
				$params->name = 'ipod';
			} elseif (stripos($agent, 'iemobile') !== false) {
				// Modern mobile Internet Explorer
				$params->name = 'iemobile';
			} elseif (stripos($agent, 'WPDesktop') !== false) {
				// Modern mobile Internet Explorer on Lumia Windows Phone
				$params->name = 'ie';
			} elseif (stripos($agent, 'android') !== false) {
				// Android device
				$params->name = 'android';

				// find model and build, parse e.g. "Android 2.1-update1; en-us; Nexus One Build/FRF91"
				$regex = '#android\s+(\d+\.\d+(?:-update\d+)?)(?:;\s+([a-z]{2}-[a-z]{2}))?(?:;\s+(.*?)\s+build/(\w+))?#i';
				$matches = array();

				if (preg_match($regex, $agent, $matches)) {
					$params->version = $matches[1];  // e.g. "2.1-update1"
					if (!empty($matches[2])) {
						$params->language = $matches[2];  // e.g. "en-us"
					}
					if (!empty($matches[3])) {
						$params->model = $matches[3];  // e.g. "Nexus One"
					}
					if (!empty($matches[4])) {
						$params->build = $matches[4];  // e.g. "FRF91"
					}
				}
			}
		} else {
			$params->handheld = false;
		}

		return $params;
	}
}
