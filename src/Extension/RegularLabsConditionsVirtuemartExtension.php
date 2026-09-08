<?php
	/**
	 * @package         RegularLabs-Conditions-Virtuemart-Extension
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	/** @noinspection PhpUnused */
	
	namespace Joomla\Plugin\System\RegularLabsConditionsVirtuemartExtension\Extension;
	
	use Joomla\CMS\Installer\Installer;
	use Joomla\CMS\Plugin\CMSPlugin;
	use Joomla\Database\DatabaseAwareTrait;
	use Joomla\Event\Event;
	use Joomla\Event\SubscriberInterface;
	use Joomla\Plugin\System\RegularLabsConditionsVirtuemartExtension\Helper\CoreFileExtenderHelper;
	
	defined('_JEXEC') or die;
	
	class RegularLabsConditionsVirtuemartExtension extends CMSPlugin implements SubscriberInterface
	{
		use DatabaseAwareTrait;
		
		#region Joomla Events
		
		/**
		 * {@inheritdoc}
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
		 * @return void
		 */
		public function onAfterInitialise() : void
		{
			if ((int)$this->params->get('check_core_extension', 0) === 1 && $this->getApplication()?->isClient('administrator'))
			{
				CoreFileExtenderHelper::ensureOverrides();
				$this->disableCheckCoreExtension();
			}
		}
		
		/**
		 * @param mixed $installer
		 *
		 * @return void
		 * @since        version
		 * @noinspection PhpMissingParamTypeInspection
		 */
		public function onExtensionAfterUpdate($installer = null) : void
		{
			if (CoreFileExtenderHelper::checkInstaller($installer, ['PLG_SYSTEM_REGULARLABS_CONDITIONS_VIRTUEMART_EXTENSION']))
			{
				CoreFileExtenderHelper::checkOverrides(null, true);
				
				return;
			}
			
			CoreFileExtenderHelper::checkOverrides($installer);
		}
		
		/**
		 * @param Event|null $event
		 *
		 * @return void
		 */
		public function onInstallerAfterInstaller(Event $event = null) : void
		{
			if ($event === null)
			{
				return;
			}
			
			foreach ($event->getArguments() as $argument)
			{
				if ($argument instanceof Installer)
				{
					if (CoreFileExtenderHelper::checkInstaller($argument, ['PLG_SYSTEM_REGULARLABS_CONDITIONS_VIRTUEMART_EXTENSION']))
					{
						CoreFileExtenderHelper::checkOverrides(null, true);
					} else
					{
						CoreFileExtenderHelper::checkOverrides($argument);
					}
					
					break;
				}
			}
		}
		
		/**
		 * @param mixed $parent
		 *
		 * @return true
		 * @noinspection PhpMissingParamTypeInspection
		 * @noinspection PhpUnusedParameterInspection
		 */
		public function install($parent) : bool
		{
			CoreFileExtenderHelper::checkOverrides(null, true);
			
			return true;
		}
		
		/**
		 * One-shot: clear check_core_extension after a manual admin re-applying.
		 *
		 */
		private function disableCheckCoreExtension() : void
		{
			$params                         = $this->params->toArray();
			$params['check_core_extension'] = '0';
			$this->params->loadArray($params);
			
			$db = $this->getDatabase();
			
			/** @noinspection JsonEncodingApiUsageInspection */
			
			$query = $db->getQuery(true)
			            ->update($db->quoteName('#__extensions'))
			            ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
			            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			            ->where($db->quoteName('element') . ' = ' . $db->quote('regularlabs_conditions_virtuemart_extension'));
			$db->setQuery($query)->execute();
		}
		#endregion
	}
