<?php
/**
* @file
* @brief    sigplus Image Gallery Plus slideplus image rotator engine
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2023 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see https://www.gnu.org/licenses/gpl-3.0.html
* @see      https://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;

/**
* Support class for the slideplus rotator engine, written in plain JavaScript.
* @see https://hunyadi.info.hu/projects/slideplus/
*/
class SigPlusNovoSlidePlusRotatorEngine extends SigPlusNovoRotatorEngine {
	public function getIdentifier() {
		return 'slideplus';
	}

	public function isCaptionSupported() {
		return true;
	}

	private static function cssPadding($padding) {
		// check if some elements are missing
		if ($padding['top'] === false || $padding['bottom'] === false || $padding['left'] === false || $padding['right'] === false) {
			$css = array();
			if ($padding['top'] !== false) {
				$css['padding-top'] = $padding['top'];
			}
			if ($padding['bottom'] !== false) {
				$css['padding-bottom'] = $padding['bottom'];
			}
			if ($padding['left'] !== false) {
				$css['padding-left'] = $padding['left'];
			}
			if ($padding['right'] !== false) {
				$css['padding-right'] = $padding['right'];
			}
			return $css;
		}

		// all values are set
		if ($padding['left'] == $padding['right']) {
			if ($padding['top'] == $padding['bottom']) {
				if ($padding['top'] == $padding['left']) {
					// all four values are equal
					return array('padding' => $padding['top']);
				} else {
					// top and bottom, and left and right values are pairwise equal
					return array('padding' => "{$padding['top']} {$padding['left']}");
				}
			} else {
				// left and right values are equal
				return array('padding' => "{$padding['top']} {$padding['left']} {$padding['bottom']}");
			}
		} else {
			return array('padding' => "{$padding['top']} {$padding['right']} {$padding['bottom']} {$padding['left']}");
		}
	}

	public function addStyles($selector, SigPlusNovoGalleryParameters $params) {
		// add main stylesheet
		parent::addStyles($selector, $params);

		$css = array();

		// preferred width for items
		$css["{$selector} .slideplus-slot"] = array(
			'width' => $params->preview_width.'px',
			'aspect-ratio' => "{$params->preview_width} / {$params->preview_height}"
		);

		// navigation buttons
		if (!$params->rotator_buttons) {
			$css["{$selector} .slideplus-viewport .slideplus-previous"] = array('display' => 'none');
			$css["{$selector} .slideplus-viewport .slideplus-next"] = array('display' => 'none');
		}
		switch ($params->rotator_navigation) {
			case 'top':
				$css["{$selector} .slideplus-navigation.slideplus-bottom"] = array('display' => 'none');
				break;
			case 'bottom':
				$css["{$selector} .slideplus-navigation.slideplus-top"] = array('display' => 'none');
				break;
			case 'none':
				$css["{$selector} .slideplus-navigation.slideplus-top, {$selector} .slideplus-navigation.slideplus-bottom"] = array('display' => 'none');
				break;
			case 'both':
				break;
		}

		// navigation paging
		if (!$params->rotator_links) {
			$css["{$selector} .slideplus-pager"] = array('display' => 'none');
		}

		// item alignment
		$alignment = array();
		$horzalign = false;
		switch ($params->rotator_alignment) {
			case 'w': case 'c': case 'e': $horzalign = 'center'; break;
			case 'nw': case 'w': case 'sw': $horzalign = 'flex-start'; break;
			case 'ne': case 'e': case 'se': $horzalign = 'flex-end'; break;
		}
		if ($horzalign !== false) {
			$alignment['justify-content'] = $horzalign;
		}
		$vertalign = false;
		switch ($params->rotator_alignment) {
			case 'w': case 'c': case 'e': $vertalign = 'center'; break;
			case 'nw': case 'n': case 'ne': $vertalign = 'flex-start'; break;
			case 'sw': case 's': case 'se': $vertalign = 'flex-end'; break;
		}
		if ($vertalign !== false) {
			$alignment['align-items'] = $vertalign;
		}
		if (!empty($alignment)) {
			$css["{$selector} .slideplus-content"] = $alignment;
		}

		// caption visibility
		$caption_attributes = array();
		switch ($params->caption_visibility) {
			case 'none':
				$caption_attributes['display'] = 'none';
				break;
			case 'always':
				$caption_attributes['visibility'] = 'visible';
				break;
		}
		if ($params->caption_height !== false) {
			$caption_attributes['height'] = $params->caption_height;
		}
		if (!empty($caption_attributes)) {
			$css["{$selector} .slideplus-caption"] = $caption_attributes;
		}

		// caption spacing
		if ($params->caption_height !== false) {
			switch ($params->caption_position) {
				case 'above':
					$spacing = array(
						'padding-top' => $params->caption_height
					);
					break;
				case 'below':
					$spacing = array(
						'padding-bottom' => $params->caption_height
					);
					break;
			}
			$css["{$selector} .slideplus-slot.slideplus-{$params->caption_position}"] = $spacing;
			$css["{$selector} .slideplus-slot.slideplus-{$params->caption_position} > .slideplus-content"] = $spacing;
		}

		// transition animation
		$animation = array();
		if ($params->rotator_duration > 0) {
			$animation['animation-duration'] = $params->rotator_duration.'ms';
		}
		$css["{$selector} .slideplus-stripe"] = $animation;

		$instance = SigPlusNovoEngineServices::instance();
		$instance->addStyles($css);
	}

	/**
	* Adds script references to the HTML head element.
	* @param {string} $selector A CSS selector.
	* @param $params Gallery parameters.
	*/
	public function addScripts($selector, SigPlusNovoGalleryParameters $params) {
		// add main script
		parent::addScripts($selector, $params);

		// get engine helper
		$instance = SigPlusNovoEngineServices::instance();

		// set rotator engine options
		$jsparams = array();
		$jsparams['rows'] = $params->rows;
		$jsparams['cols'] = $params->cols;
		$jsparams['loop'] = $params->loop;
		$jsparams['orientation'] = $params->rotator_orientation;
		$jsparams['step'] = $params->rotator_step;
		$jsparams['links'] = $params->rotator_links;
		$jsparams['delay'] = $params->rotator_delay;
		$jsparams['timing'] = $params->rotator_transition;
		if ($params->sort_criterion == SIGPLUS_SORT_RANDOM) {
			$jsparams['random'] = true;
		}
		$caption_position = $params->caption_position;
		switch ($caption_position) {  // map some position constants
			case 'overlay-bottom': $caption_position = 'bottom'; break;
			case 'overlay-top': $caption_position = 'top'; break;
		}
		$jsparams['captions'] = $caption_position;
		$jsparams['protection'] = $params->protection;
		$language = Factory::getLanguage();
		$jsparams['dir'] = $language->isRtl() ? 'rtl' : 'ltr';
		$jsparams = array_merge($jsparams, $params->rotator_params);
		$jsparams = json_encode($jsparams);
		$selector = json_encode("{$selector} ul");

		$script = "new SlidePlusSlider(document.querySelector({$selector}), {$jsparams}, function (el) { return el.querySelector('.sigplus-image').getAttribute('data-title') || el.querySelector('.sigplus-image img').alt; });";
		$instance->addOnReadyScript($script);
	}
}
