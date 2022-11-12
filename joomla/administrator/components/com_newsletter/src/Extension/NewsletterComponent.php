<?php
/**
 * @package     Newsletter.Administrator
 * @subpackage  com_newsletter
 *
 * @copyright   Copyright (C) 2022 Covenant Baptist Church, Lancaster SC, All rights reserved.
 * @license     UNLICENSED
 */

namespace CBCLancaster\Component\Newsletter\Administrator\Extension;

defined('JPATH_PLATFORM') or die;


use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Psr\Container\ContainerInterface;


/**
 * Component class for com_mywalks
 *
 * @since 0.0.1
 */
class NewsletterComponent extends MVCComponent implements BootableExtensionInterface, RouterServiceInterface
{
	use RouterServiceTrait;
	use HTMLRegistryAwareTrait;

	/**
	 * Booting the extension. This is the function to set up the environment of the extension like
	 * registering new class loaders, etc.
	 *
	 * If required, some initial set up can be done from services of the container, eg.
	 * registering HTML services.
	 *
	 * @param   ContainerInterface  $container  The container
	 *
	 * @return  void
	 *
	 * @since 0.0.1
	 */
	public function boot(ContainerInterface $container)
	{
	}
}
