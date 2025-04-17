<?php
	/**
	 * @package         RegularLabs-Conditions-Virtuemart-Extension
	 * @subpackage      System.regular_labs_conditions_virtuemart_extension
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	use Joomla\CMS\Extension\PluginInterface;
	use Joomla\CMS\Factory;
	use Joomla\CMS\Plugin\PluginHelper;
	use Joomla\DI\Container;
	use Joomla\DI\ServiceProviderInterface;
	use Joomla\Event\DispatcherInterface;
	use Joomla\Plugin\System\RegularLabsConditionsVirtuemartExtension\Extension\RegularLabsConditionsVirtuemartExtension;
	
	defined('_JEXEC') or die;
	
	return new class () implements ServiceProviderInterface
	{
		/**
		 * {@inheritdoc}
		 * @since version
		 */
		public function register(Container $container) : void
		{
			$container->set(
				PluginInterface::class,
				function (Container $container)
				{
					$dispatcher = $container->get(DispatcherInterface::class);
					$plugin     = new RegularLabsConditionsVirtuemartExtension($dispatcher, (array)PluginHelper::getPlugin('system', 'regularlabs_conditions_virtuemart_extension'));
					
					$plugin->setApplication(Factory::getApplication());
					
					return $plugin;
				}
			);
		}
	};
