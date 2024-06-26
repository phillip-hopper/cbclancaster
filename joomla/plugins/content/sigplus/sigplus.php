<?php
/**
* @file
* @brief    sigplus Image Gallery Plus plug-in for Joomla
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2023 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see https://www.gnu.org/licenses/gpl-3.0.html
* @see      https://hunyadi.info.hu/projects/sigplus
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

if (!defined('SIGPLUS_VERSION_PLUGIN')) {
	define('SIGPLUS_VERSION_PLUGIN', '1.5.0');
}

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'fields'.DIRECTORY_SEPARATOR.'constants.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if (!defined('SIGPLUS_DEBUG')) {
	/**
	* Triggers debug mode.
	* In debug mode, the extension uses uncompressed versions of scripts rather than the bandwidth-saving minified versions.
	*/
	define('SIGPLUS_DEBUG', false);
}
if (!defined('SIGPLUS_LOGGING')) {
	/**
	* Triggers logging mode.
	* In logging mode, the extension prints verbose status messages to the output.
	*/
	define('SIGPLUS_LOGGING', false);
}

// import library dependencies
jimport('joomla.event.plugin');

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'core.php';

/**
* sigplus Image Gallery Plus plug-in.
*/
class plgContentSigPlusNovo extends Joomla\CMS\Plugin\CMSPlugin {
	/** Activation tag used to produce galleries with the plug-in. */
	private $tag_gallery = 'gallery';
	/** Activation tag used to produce a lightbox-powered link with the plug-in. */
	private $tag_lightbox = 'lightbox';
	/** Core service object. */
	private $core;

	public function __construct(&$subject, $params) {
		parent::__construct($subject, $params);

		// set activation tag if well-formed
		$tag_gallery = $this->getParameterValue('tag_gallery', $this->tag_gallery);
		if (is_string($tag_gallery) && ctype_alnum($tag_gallery)) {
			$this->tag_gallery = $tag_gallery;
		}
		$tag_lightbox = $this->getParameterValue('tag_lightbox', $this->tag_lightbox);
		if (is_string($tag_lightbox) && ctype_alnum($tag_lightbox)) {
			$this->tag_lightbox = $tag_lightbox;
		}
	}

	private function getParameterValue($name, $default) {
		if ($this->params instanceof stdClass) {
			if (isset($this->params->$name)) {
				return $this->params->$name;
			}
		} else if ($this->params instanceof Joomla\Registry\Registry) {
			$paramvalue = $this->params->get($name);
			if (isset($paramvalue)) {
				return $paramvalue;
			}
		}
		return $default;
	}

	/**
	* Fired when content are to be processed by the plug-in.
	* Parses the article for occurrences of the activation tag.
	*
	* Recommended activation tag usage syntax:
	* a) POSIX fully portable file names
	*    Folder name characters are in [A-Za-z0-9._-])
	*    Regular expression: [/\w.-]+
	*    Example: {gallery rows=1 cols=1}  /sigplus/birds/  {/gallery}
	* b) URL-encoded absolute URLs
	*    Regular expression: (?:[0-9A-Za-z!"$&\'()*+,.:;=@_-]|%[0-9A-Za-z]{2})+
	*    Example: {gallery} http://example.com/image.jpg {/gallery}
	*
	* @param {string} $context The context of the content passed to the plug-in.
	* @param $article The content object or article that is being rendered by the view.
	* @param $params An associative array of relevant parameters.
	* @param $limitstart An integer that determines the "page" of the content that is to be generated.
	*/
	public function onContentPrepare($context, &$article, &$params, $limitstart = 0) {
		if (($context === 'com_content.category' || $context === 'com_content.featured') && isset($article->introtext)) {
			// only introductory text is visible, do not make replacements elsewhere
			if ($this->hasNoActivationTags($article->introtext)) {
				return false;  // short-circuit plugin activation, no replacements to be made in introductory text
			}
		} else {
			// full article text is visible
			if ($this->hasNoActivationTags($article->text)) {
				return false;  // short-circuit plugin activation, plugin is not used anywhere
			}
		}
		return $this->parseContent($context, $article->text);
	}

	/** True if no activation tag use occurs in the search text. */
	private function hasNoActivationTags($text) {
		return empty($text)
			|| strpos($text, '{'.$this->tag_gallery) === false && strpos($text, '{'.$this->tag_lightbox) === false
			&& strpos($text, '['.$this->tag_gallery) === false && strpos($text, '['.$this->tag_lightbox) === false
		;
	}

	/**
	* Generates image thumbnails with alternate text, title and lightbox pop-up activation on mouse click by finding
	* and replacing activation tags in article content.
	* @param {string} $text Article (content item) text.
	*/
	private function parseContent($context, &$text) {
		if (SigPlusNovoTimer::shortcircuit()) {
			return false;  // short-circuit plugin activation, allotted execution time expired, error message already printed
		}

		// load language file for internationalized labels and error messages
		$lang = Factory::getLanguage();
		$lang->load('plg_content_sigplus', JPATH_ADMINISTRATOR);

		if (!isset($this->core)) {
			$this->core = false;
			try {
				// create configuration parameter objects
				$configuration = new SigPlusNovoConfigurationParameters();
				$configuration->service = new SigPlusNovoServiceParameters();
				$configuration->service->setParameters($this->params);
				$configuration->gallery = new SigPlusNovoGalleryParameters();
				$configuration->gallery->setParameters($this->params);

				if ($context === 'com_finder.indexer') {
					// produce only text when the content is being indexed
					$configuration->gallery->target_media = 'indexing';
				}

				if (SIGPLUS_LOGGING || $configuration->service->debug_server == 'verbose') {
					SigPlusNovoLogging::setService(new SigPlusNovoHTMLLogging());
				} else {
					SigPlusNovoLogging::setService(new SigPlusNovoNoLogging());
				}

				$this->core = new SigPlusNovoCore($configuration);
			} catch (Exception $e) {
				$app = Factory::getApplication();
				$app->enqueueMessage($e->getMessage(), 'error');
			}
		}

		if ($this->core !== false) {
			if (SIGPLUS_LOGGING) {
				SigPlusNovoLogging::appendStatus(Text::_('SIGPLUS_STATUS_LOGGING'));
			}

			// patterns for key/value parameter list
			$variable_pattern = '\{\$[^{}\[\]/]+\}';  // variable substitutions in the style "{$variable}"
			$param_pattern_cu = '(?:[^\{\}/]+|/(?!\})|'.$variable_pattern.')*+';  // characters other than curly braces
			$param_pattern_sq = '(?:[^\[\]/]+|/(?!\])|'.$variable_pattern.')*+';  // characters other than square brackets

			// patterns for matching activation tags
			$tag_gallery = preg_quote($this->tag_gallery, '#');
			$tag_lightbox = preg_quote($this->tag_lightbox, '#');

			// selected examples that match pattern:
			// * {gallery rows=1 cols=1 lightbox=boxplus/dark}budapest{/gallery}
			// * {gallery lightbox="boxplus/dark"} budapest {/gallery}
			// * {gallery rows=3 cols=1 preview-crop=yes rotator_delay=3000}budapest{/gallery}
			// * {gallery lightbox:navigation=bottom lightbox:controls=below}budapest{/gallery}
			// * {lightbox alt="alternate text" src="https://example.com/image.jpg"}link{/lightbox}
			// * {lightbox}<img src="https://example.com/image.jpg" />{/lightbox}
			// * {lightbox/}
			$pattern = '#(?|'.implode('|', array(  // backreference reset "?|" ensures all matched subgroups start with index 1
				// find compact {gallery/} tags and regular {gallery}...{/gallery} tags
				'\{'.$tag_gallery.'\b('.$param_pattern_cu.')(?:/\}|\}(.+?)\{/'.$tag_gallery.'\})',
				// find compact [gallery/] tags and regular [gallery]...[/gallery] tags
				'\['.$tag_gallery.'\b('.$param_pattern_sq.')(?:/\]|\](.+?)\[/'.$tag_gallery.'\])',

				// find compact {lightbox/} tags and regular {lightbox}...{/lightbox} tags wrapping HTML
				'\{'.$tag_lightbox.'\b('.$param_pattern_cu.')(?:/\}|\}(.+?)\{/'.$tag_lightbox.'\})',
				// find compact [lightbox/] tags and regular [lightbox]...[/lightbox] tags wrapping HTML
				'\['.$tag_lightbox.'\b('.$param_pattern_sq.')(?:/\]|\](.+?)\[/'.$tag_lightbox.'\])',
			)).')#msSu';

			$body = preg_replace_callback(
				$pattern,
				function ($matches) {
					return $this->getReplacements($matches);
				},
				$text,
				-1,  // no limit on number of replacements
				$count
			);
			if (isset($body)) {
				$text = $body;
			}

			$log = SigPlusNovoLogging::fetch();
			if ($log) {
				$text = $log.$text;
			}

			return $count > 0;
		}
		return false;
	}

	/**
	* Replaces all occurrences of gallery and lightbox activation tags.
	* This function is to be called in a regular expression callback.
	* @param {string} $matches The array of matches as produced by preg_replace_callback.
	*/
	private function getReplacements($matches) {
		$entire_match = $matches[0];

		if (SigPlusNovoTimer::shortcircuit()) {
			// short-circuit plugin activation, allotted execution time expired, error message already printed
			switch ($this->core->verbosityLevel()) {
				case 'none':
				case 'laconic':
					// hide activation tag completely
					return '';
				case 'verbose':
				default:
					// leave activation tag as it appears
					return $entire_match;
			}
		}

		$inner_text = isset($matches[2]) ? $matches[2] : null;  // text in between start and end tags (unless omitted)
		$param_text = $matches[1];

		$body = '';
		try {
			if (self::starts_with_tag($entire_match, $this->tag_gallery)) {
				// gallery activation tag matches
				$body = $this->getGalleryReplacementSingle($inner_text, $param_text);
			} elseif (self::starts_with_tag($entire_match, $this->tag_lightbox)) {
				// lightbox activation tag matches
				if (isset($inner_text)) {
					$body = $this->getLightboxReplacement($matches);
				} else {
					$body = $this->getSelectorReplacement($matches);
				}
			}
		} catch (Exception $e) {
			// catch all exceptions as we are inside a replace callback
			$app = Factory::getApplication();
			switch ($this->core->verbosityLevel()) {
				case 'none':
					// display no message, hide activation tag completely
					$body = '';
					break;
				case 'laconic':
					if ($e instanceof SigPlusNovoTimeoutException) {  // display a timeout message
						$message = Text::_('SIGPLUS_EXCEPTION_MESSAGE_TIMEOUT');
					} else {  // display a very general, uninformative message
						$message = Text::_('SIGPLUS_EXCEPTION_MESSAGE');
					}

					// hide activation tag completely
					$body = '';

					// show error message
					$app->enqueueMessage($message, 'error');
					break;
				case 'verbose':
				default:
					// display a specific, informative message
					$message = $e->getMessage();

					// leave activation tag as it appears
					$body = $entire_match;

					// show error message
					$app->enqueueMessage($message, 'error');
					break;
			}
		}
		return $body;
	}

	/**
	* Replaces a single occurrence of a gallery activation tag.
	* @param {string} $sourcetext A string that identifies the image source.
	* @param {string} $paramtext A string that stores parameter key/value pairs.
	*/
	private function getGalleryReplacementSingle($sourcetext, $paramtext) {
		// the activation code {gallery key=value}myfolder{/gallery} translates into a source and a parameter string
		$paramtext = self::strip_html($paramtext);
		$this->core->setParameterString($paramtext);

		// update parameters if a module is specified that acts as a base for gallery parameters
		// pushes a new set of parameters on the parameter stack
		$inherits = $this->setInheritedParameters($paramtext);

		// get special-purpose parameter "source"
		$params = $this->core->getParameters();

		// set image source
		$source = null;
		if (isset($sourcetext)) {
			$source = trim(html_entity_decode($sourcetext, ENT_QUOTES, 'utf-8'));
		}
		if ($params->source) {
			$source = $params->source;
		}
		if (is_url_http($source)) {
			$source = safe_url_encode($source);
		} else if (!empty($source)) {
			$source = trim($source, '/\\');  // allow users to add leading and trailing (back)slashes but ignore them
		}

		try {
			if (is_absolute_path($source)) {  // do not permit an absolute path enclosed in activation tags
				throw new SigPlusNovoImageSourceException($source);
			}

			// download image
			try {
				if ($this->core->downloadImage($source)) {  // an image has been requested for download
					jexit();  // do not produce a page
				}
			} catch (SigPlusNovoImageDownloadAccessException $e) {  // signal download errors but do not stop page processing
				$app = Factory::getApplication();
				$app->enqueueMessage($e->getMessage(), 'error');
			}

			// generate image gallery
			$body = $this->core->getGalleryHTML($source, $id);
			$this->core->addStyles($id);
			$this->core->addScripts($id);

			$this->core->resetParameters();
			if ($inherits) {  // pops the extra set of parameters from the parameter stack
				$this->core->resetParameters();
			}

			return $body;
		} catch (Exception $e) {
			$this->core->resetParameters();
			if ($inherits) {  // pops the extra set of parameters from the parameter stack
				$this->core->resetParameters();
			}

			throw $e;
		}
	}

	/**
	* Replaces a single occurrence of a lightbox activation tag.
	* This function is to be called in a regular expression callback.
	* @param {string} $match The array of matches as produced by preg_replace_callback.
	*/
	private function getLightboxReplacement($match) {
		// extract parameter string
		$params = SigPlusNovoConfigurationBase::string_to_array(self::strip_html($match[1]));
		$content = $match[2];

		// extract or create identifier
		if (!isset($params['id'])) {
			$params['id'] = $this->core->getUniqueGalleryId();
		}

		if (isset($params['href']) || isset($params['link'])) {
			$this->core->setParameterArray($params);

			// build anchor components
			if (isset($params['link'])) {  // create link to gallery on the same page
				$this->core->addLightboxLinkScript($params['id'], $params['link']);
				unset($params['link']);
				$params['href'] = 'javascript:void(0);';  // artificial link target
			} elseif (isset($params['href'])) {  // create link to (external) image
				if (!is_url_http($params['href'])) {  // make relative URLs absolute
					$params['href'] = Uri::base(false).$params['href'];
				}
				$params['href'] = safe_url_encode($params['href']);

				// add lightbox scripts to page header
				$selector = '#'.$params['id'];  // build selector from the identifier of the anchor that links to a resource
				$this->core->addLightboxScripts($selector);
			}

			$this->core->resetParameters();

			// generate anchor HTML
			$attributes = array();
			foreach (array('id','href','rel','class','style','title') as $attr) {
				if (isset($params[$attr])) {
					$attributes[] = $attr.'="'.htmlspecialchars($params[$attr]).'"';
				}
			}
			foreach ($params as $attr => $value) {
				if (strpos($attr , 'data-') === 0) {  // an HTML data-* attribute
					$attributes[] = $attr.'="'.htmlspecialchars($value).'"';
				}
			}
			if (!empty($attributes)) {
				$anchor = '<a '.implode(' ', $attributes).'>'.$content.'</a>';
			} else {
				$anchor = '<a>'.$content.'</a>';
			}
			return $anchor;
		} else {
			return $content;  // do not change text for unsupported combination of parameters
		}
	}

	/**
	* Replaces a single occurrence of a compact lightbox activation tag.
	* This function is to be called in a regular expression callback.
	* @param {string} $match The array of matches as produced by preg_replace_callback.
	*/
	private function getSelectorReplacement($match) {
		$replacement = $match[0];  // no replacements

		// extract parameter string
		$params = SigPlusNovoConfigurationBase::string_to_array(self::strip_html($match[1]));

		// apply lightbox to all items that satisfy the CSS selector
		if (isset($params['selector'])) {
			// add lightbox scripts to page header
			$this->core->setParameterArray($params);
			try {
				$this->core->addLightboxScripts($params['selector']);
				$this->core->resetParameters();
			} catch (Exception $e) {
				$this->core->resetParameters();
				throw $e;
			}
			$replacement = '';
		}

		return $replacement;
	}

	/**
	* Sets plug-in parameters inherited from a module.
	*
	* Parameters of a module may in this way act as a template for plug-in parameters. Plug-in parameters override
	* the parameter values inherited from the base module.
	*/
	private function setInheritedParameters($params) {
		// get current parameters
		$curparams = $this->core->getParameters();

		if (!isset($curparams->base_module)) {
			return false;
		}

		// import parameters from module
		$module = JModuleHelper::getModule('mod_sigplus', $curparams->base_module);
		if (empty($module)) {
			return false;
		}

		// pop parameters recently pushed to parameter stack
		$this->core->resetParameters();

		// process parameters from module that acts as base source for parameters
		$baseparams = json_decode($module->params);
		unset($baseparams->source);  // ignore parameter "source" not meaningful in this context

		// push base parameters to parameter stack
		$this->core->setParameterObject($baseparams);

		// (re-)push activation code parameters to parameter stack
		$this->core->setParameterString($params);
		$curparams = $this->core->getParameters();

		return true;
	}

	private static function strip_html($html) {
		$text = html_entity_decode($html, ENT_QUOTES, 'utf-8');  // translate HTML entities to regular characters
		$text = str_replace("\xc2\xa0", ' ', $text);  // translate non-breaking space to regular space
		$text = strip_tags($text);  // remove HTML tags
		return $text;
	}

	/** True if the text starts with the given activation tag such as "{tag" or "[tag". */
	private static function starts_with_tag($text, $tag) {
		return preg_match('#^[\{\[]'.preg_quote($tag, '#').'\b#msSu', $text);
	}

	public function onExtensionAfterSave($context, $table, $isNew) {
		if (!isset($table) ||
			!isset($table->type) || $table->type !== 'plugin' ||
			!isset($table->folder) || $table->folder !== 'content' ||
			!isset($table->element) || $table->element !== 'sigplus')
		{  // function invoked in the context of an unrelated extension
			return;
		}

		// previous extension configuration that is being overwritten
		$old_params = new SigPlusNovoServiceParameters();
		$old_params->setParameters($this->params);
		if (!empty($this->params['settings'])) {
			$old_params->setString($this->params['settings']);
		}

		// new extension configuration that has been persisted
		$params = json_decode($table->params, true);
		$new_params = new SigPlusNovoServiceParameters();
		if ($params) {
			$new_params->setArray($params);
			if (!empty($params['settings'])) {
				$new_params->setString($params['settings']);
			}
		}

		// check if database tables have to be re-populated
		$clean_database = false
			// instructed to clean database on every save when plugin configuration changes
			|| $new_params->clean_database
			// there has been a change in image metadata extraction strategy, which requires an image re-scan
			|| $old_params->metadata_filter != $new_params->metadata_filter
			// there has been a change in where generated images are saved, which requires a directory re-scan
			|| $old_params->cache_image != $new_params->cache_image
		;

		if ($clean_database) {
			require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'setup.php';
			SigPlusNovoDatabaseSetup::update();
			SigPlusNovoDatabaseSetup::populate();
		}
	}
}

if (!file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'sigplus'.DIRECTORY_SEPARATOR.'js')) {  // available in 1.4.x only
	/**
	* Compatibility layer for using sigplus Novo as a next version of sigplus.
	*/
	class plgContentSIGPlus extends plgContentSigPlusNovo {

	}
}
