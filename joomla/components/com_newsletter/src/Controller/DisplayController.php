<?php
/**
 * @package     Newsletter.Site
 * @subpackage  com_newsletter
 *
 * @copyright   Copyright (C) 2022 Covenant Baptist Church, Lancaster SC, All rights reserved.
 * @license     UNLICENSED
 */

namespace CBCLancaster\Component\Newsletter\Site\Controller;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Component Controller
 *
 * @since 0.0.1
 */
class DisplayController extends BaseController
{

	/**
	 * Method to display a view.
	 *
	 * @param boolean $cachable If true, the view output will be cached
	 * @param array $urlparams An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static  This object to support chaining.
	 *
	 * @throws Exception
	 * @since 0.0.1
	 */
	public function display($cachable = false, $urlparams = array()): DisplayController
	{
		return parent::display();
	}
}
