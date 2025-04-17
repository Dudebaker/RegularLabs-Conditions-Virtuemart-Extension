<?php
	/**
	 * @package         plg_system_breakdesignsproductbuilder
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	/** @noinspection PhpUnused */
	
	defined('_JEXEC') or die;
	
	use Joomla\CMS\Factory;
	use Joomla\CMS\Language\Text;
	use Joomla\CMS\Table\Extension;
	use Joomla\CMS\Version;
	use Joomla\Database\DatabaseInterface;
	
	class PlgSystemRegularLabs_Conditions_Virtuemart_ExtensionInstallerScript
	{
		public const MAX_VERSION_JOOMLA = '6.0.0';
		public const MIN_VERSION_JOOMLA = '5.1.0';
		public const MIN_VERSION_PHP = '8.2.0';
		
		protected string $extensionName = 'RegularLabs Conditions Virtuemart Extension System Plugin';
		
		protected string $elementName = 'regularlabs_conditions_virtuemart_extension';
		protected string $elementType = 'plugin';
		protected string $elementFolder = 'system';
		
		#region Joomla Events
		
		/**
		 * This event is fired before Joomla processes a request, enabling custom pre-processing or validation.
		 *
		 * @param string $type
		 * @param object $parent
		 *
		 * @return bool
		 * @throws Exception
		 * @since version
		 */
		public function preflight(string $type, object $parent) : bool
		{
			if ($type === 'uninstall')
			{
				return true;
			}
			
			if (!$this->checkVersionJoomla())
			{
				return false;
			}
			
			if (!$this->checkVersionPhp())
			{
				return false;
			}
			
			return true;
		}
		
		/**
		 * This event is fired after Joomla completes a request, allowing cleanup or post-processing tasks.
		 *
		 * @param string $type
		 * @param object $parent
		 *
		 * @return void
		 * @throws \Exception
		 * @since version
		 */
		public function postflight(string $type, object $parent) : void
		{
			if ($type === 'uninstall')
			{
				return;
			}
			
			$this->enableExtension();
		}
		#endregion
		
		#region Helper function
		
		/**
		 * Checks whether the Joomla! version meets the requirement
		 *
		 * @return bool
		 * @throws Exception
		 * @since version
		 */
		private function checkVersionJoomla() : bool
		{
			$version = new Version();
			
			if (!$version->isCompatible(self::MIN_VERSION_JOOMLA))
			{
				Factory::getApplication()?->enqueueMessage(Text::sprintf('PLG_SYSTEM_REGULARLABS_CONDITIONS_VIRTUEMART_EXTENSION_ERROR_JOOMLA_VERSION', $this->extensionName, self::MIN_VERSION_JOOMLA), 'error');
				
				return false;
			}
			
			if (version_compare(JVERSION, self::MAX_VERSION_JOOMLA, 'ge'))
			{
				Factory::getApplication()?->enqueueMessage(Text::sprintf('PLG_SYSTEM_REGULARLABS_CONDITIONS_VIRTUEMART_EXTENSION_ERROR_JOOMLA_VERSION_MAX', $this->extensionName, self::MAX_VERSION_JOOMLA), 'error');
				
				return false;
			}
			
			return true;
		}
		
		/**
		 * Checks whether the PHP version meets the requirement
		 *
		 * @return bool
		 * @throws Exception
		 * @since version
		 */
		private function checkVersionPhp() : bool
		{
			if (!version_compare(PHP_VERSION, self::MIN_VERSION_PHP, 'ge'))
			{
				Factory::getApplication()?->enqueueMessage(Text::sprintf('PLG_SYSTEM_REGULARLABS_CONDITIONS_VIRTUEMART_EXTENSION_ERROR_PHP_VERSION', $this->extensionName, self::MIN_VERSION_PHP), 'error');
				
				return false;
			}
			
			return true;
		}
		
		/**
		 * Enables the extension
		 *
		 * @return void
		 * @throws Exception
		 * @since version
		 */
		private function enableExtension() : void
		{
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->getQuery(true);
			$query->select('extension_id')
			      ->from('#__extensions')
			      ->where($db->quoteName('element') . ' = ' . $db->quote($this->elementName))
			      ->where($db->quoteName('type') . ' = ' . $db->quote($this->elementType))
			      ->where($db->quoteName('folder') . ' = ' . $db->quote($this->elementFolder));
			$db->setQuery($query);
			$extensionId = $db->loadResult();
			
			if (empty($extensionId))
			{
				Factory::getApplication()?->enqueueMessage(Text::_('PLG_SYSTEM_REGULARLABS_CONDITIONS_VIRTUEMART_EXTENSION_ERROR_NOT_FOUND'), 'error');
				return;
			}
			
			$extension = new Extension($db);
			$extension->load($extensionId);
			
			$extension->enabled = 1;
			
			if ($extension->store())
			{
				\Joomla\Plugin\System\RegularLabsConditionsVirtuemartExtension\Helper\CoreFileExtenderHelper::checkOverrides(null, true);
				return;
			}
			
			Factory::getApplication()?->enqueueMessage(Text::_('PLG_SYSTEM_REGULARLABS_CONDITIONS_VIRTUEMART_EXTENSION_ERROR_FAILED_TO_ACTIVATE'), 'error');
		}
		#endregion
	}
