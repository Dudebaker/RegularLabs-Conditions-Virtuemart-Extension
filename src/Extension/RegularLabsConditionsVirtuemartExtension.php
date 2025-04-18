<?php
	/**
	 * @package         RegularLabs-Conditions-Virtuemart-Extension
	 * @subpackage      System.regular_labs_conditions_virtuemart_extension
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	namespace Joomla\Plugin\System\RegularLabsConditionsVirtuemartExtension\Extension;
	
	use Joomla\CMS\Factory;
	use Joomla\CMS\Language\Text;
	use Joomla\Database\DatabaseAwareTrait;
	use Joomla\Database\DatabaseInterface;
	use Joomla\Plugin\System\RegularLabsConditionsVirtuemartExtension\Helper\CoreFileExtenderHelper;
	use Joomla\CMS\Installer\Installer;
	use Joomla\CMS\Plugin\CMSPlugin;
	use Joomla\Event\Event;
	use Joomla\Event\SubscriberInterface;
	
	defined('_JEXEC') or die;
	
	class RegularLabsConditionsVirtuemartExtension extends CMSPlugin implements SubscriberInterface
	{
		use DatabaseAwareTrait;
		
		#region Joomla Events
		/**
		 * {@inheritdoc}
		 * @since version
		 */
		public static function getSubscribedEvents() : array
		{
			return [
				'onAfterInitialise'         => 'onAfterInitialise',
				'onExtensionAfterUpdate'    => 'onExtensionAfterUpdate',
				'onInstallerAfterInstaller' => 'onInstallerAfterInstaller',
			];
		}
		
		/**
		 * Listener for the `onAfterInitialise` event
		 *
		 * This event is triggered after the framework has loaded and the application initialize method has been called.
		 *
		 * @return  void
		 *
		 * @since version
		 */
		public function onAfterInitialise() : void
		{
			$this->CheckCoreFileExtender();
		}

		/**
		 * Listener for the `onExtensionAfterUpdate` event
		 *
		 * Executed after update of an extension (but not always?)
		 * Check if any overrides have to be added
		 *
		 * @param   \Joomla\CMS\Installer\Installer|null  $installer  Installer object
		 *
		 * @return  void
		 *
		 * @since version
		 */
		public function onExtensionAfterUpdate(Installer $installer = null) : void
		{
			CoreFileExtenderHelper::checkOverrides($installer);
		}
		
		/**
		 * Listener for the `onInstallerAfterInstaller` event
		 *
		 * Executed after installation of an extension (or update via install instead of update in backend)
		 * Check if any overrides have to be added
		 *
		 * @param   \Joomla\Event\Event|null  $event
		 *
		 * @since version
		 */
		public function onInstallerAfterInstaller(Event $event = null) : void
		{
			$arguments = $event->getArguments();
			
			foreach ($arguments as $argument)
			{
				if ($argument instanceof Installer)
				{
					CoreFileExtenderHelper::checkOverrides($argument);
					break;
				}
			}
		}
		
		/**
		 * Listener for the `install` event
		 *
		 * Executed after installation of this extension
		 * Check if any overrides have to be added
		 *
		 * @param   $parent
		 *
		 * @return true
		 * @since version
		 */
		public function install($parent) : bool
		{
			CoreFileExtenderHelper::checkOverrides($parent);
			
			return true;
		}
		#endregion

		#region Request Handling
		
		/**
		 * Checks if the core file extension exists only if the plugin parameter is set to do so
		 *
		 * @since version
		 */
		public function CheckCoreFileExtender() : bool
		{
			if (!$this->getApplication()?->isClient('administrator'))
			{
				return false;
			}
			
			$checkCoreExtension = $this->params->get('check_core_extension', 1);
			
			if (!$checkCoreExtension)
			{
				return false;
			}
			
			CoreFileExtenderHelper::checkOverrides(null, true);
			
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->getQuery(true)
			            ->update($db->quoteName('#__extensions'))
			            ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode(['check_core_extension' => 0])))
			            ->where($db->quoteName('element') . ' = ' . $db->quote('regularlabs_conditions_virtuemart_extension'))
			            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'));
			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
		#endregion		
	}