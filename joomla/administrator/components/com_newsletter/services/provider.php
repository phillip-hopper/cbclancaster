<?php
/**
 * @package     Newsletter.Administrator
 * @subpackage  com_newsletter
 *
 * @copyright   Copyright (C) 2022 Covenant Baptist Church, Lancaster SC, All rights reserved.
 * @license     UNLICENSED
 */

defined('_JEXEC') or die;


use CBCLancaster\Component\Newsletter\Administrator\Extension\NewsletterComponent;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The newsletter service provider.
 *
 * @since 0.0.1
 */
return new class implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since 0.0.1
	 */
	public function register(Container $container)
	{
		$container->registerServiceProvider( new CategoryFactory( '\\CBCLancaster\\Component\\Newsletter' ) );
		$container->registerServiceProvider( new MVCFactory( '\\CBCLancaster\\Component\\Newsletter' ) );
		$container->registerServiceProvider( new ComponentDispatcherFactory( '\\CBCLancaster\\Component\\Newsletter' ) );
		$container->registerServiceProvider( new RouterFactory( '\\CBCLancaster\\Component\\Newsletter' ) );
		$container->set(
			ComponentInterface::class,
			function ( Container $container ) {
				$component = new NewsletterComponent( $container->get( ComponentDispatcherFactoryInterface::class ) );

				$component->setRegistry( $container->get( Registry::class ) );
				$component->setMVCFactory( $container->get( MVCFactoryInterface::class ) );
//				  $component->setCategoryFactory($container->get(CategoryFactoryInterface::class));
				$component->setRouterFactory( $container->get( RouterFactoryInterface::class ) );

				return $component;
			}
		);
	}
};
