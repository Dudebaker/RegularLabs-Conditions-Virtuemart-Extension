<?php
	/**
	 * @package         RegularLabs-Conditions-Virtuemart-Extension
	 * @subpackage      System.regular_labs_conditions_virtuemart_extension
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	/** @noinspection PhpUnused */
	/** @noinspection HtmlUnknownAttribute */
	/** @noinspection PhpConditionAlreadyCheckedInspection */
	
	use Joomla\CMS\Installer\Installer;
	use Joomla\Plugin\System\RegularLabsConditionsVirtuemartExtension\Helper\CoreFileExtenderHelper;
	
	/**
	 * Function to check if an override has to be executed
	 * The function has to use the same name as the file with an installer parameter!
	 *
	 * @param \Joomla\CMS\Installer\Installer|null $installer
	 *
	 * @since version
	 */
	function ComConditions(Installer $installer = null, $force = false) : void
	{
		checkOverrides($installer, $force);
	}
	
	/**
	 * Function to add the overrides
	 *
	 * @param \Joomla\CMS\Installer\Installer|null $installer
	 *
	 * @since version
	 */
	function checkOverrides(Installer $installer = null, $force = false) : void
	{
		
		$extendVersion  = 1.0;
		$extensionNames = ['conditions', 'pkg_conditions'];
		
		// administrator/components/com_conditions/config.xml
		$extendName    = 'Virtuemart-Extension - CON_3RD_PARTY_EXTENSIONS';
		$extendContent = ['<option value="virtuemart">CON_VIRTUEMART</option>'];
		$extendFile    = 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_conditions' . DIRECTORY_SEPARATOR . 'config.xml';
		$extendBefore  = '<option value="zoo">CON_ZOO</option>';
		
		CoreFileExtenderHelper::handleCoreFileExtender($installer, $extensionNames, $extendName, $extendContent, $extendFile, $extendBefore, null, $extendVersion, $force);
		
		
		// administrator/components/com_conditions/forms/item_rule.xml [Group]
		$extendName    = 'Virtuemart-Extension - Group';
		$extendContent = ['<!-- VIRTUEMART -->',
		                  '<field name="@has_virtuemart" type="IsInstalled" extension="virtuemart" />',
		                  '<field name="@showon__virtuemart__a" type="ShowOn" value="@has_virtuemart:1" />',
		                  '<field name="@load_language_virtuemart_sys" type="LoadLanguage" extension="com_virtuemart" />',
		                  '',
		                  '<!-- VIRTUEMART :: PAGE TYPES -->',
		                  '<field name="@showon__virtuemart__page_type__a" type="ShowOn" value="type:virtuemart__page_type" />',
		                  '<field name="virtuemart__page_type" type="List"',
		                  '       multiple="true" default=""',
		                  '       layout="joomla.form.field.list-fancy-select"',
		                  '       label="CON_PAGE_TYPES" hiddenLabel="true">',
		                  '    <option value="category">COM_VIRTUEMART_PRODUCT_CATEGORY</option>',
		                  '    <option value="cart">COM_VIRTUEMART_CART_VIEW_DEFAULT_TITLE</option>',
		                  '    <option value="product">COM_VIRTUEMART_PRODUCT</option>',
		                  '</field>',
		                  '<field name="@showon__virtuemart__page_type__b" type="ShowOn" />',
		                  '',
		                  '<!-- VIRTUEMART :: CATEGORIES -->',
		                  '<field name="@showon__virtuemart__category__a" type="ShowOn" value="type:virtuemart__category" />',
		                  '<field name="virtuemart__category" type="VirtuemartCategories"',
		                  '       multiple="true" default=""',
		                  '       label="CON_CATEGORIES"',
		                  '       hiddenLabel="true" />',
		                  '<field name="virtuemart__category__include_children" type="Radio"',
		                  '       default="0" class="btn-group rl-btn-group btn-group-md btn-group-yesno"',
		                  '       label="CON_INCLUDE_CHILD_ITEMS">',
		                  '    <option value="0">JNO</option>',
		                  '    <option value="1">JYES</option>',
		                  '    <option value="2" class="btn btn-outline-info">CON_ONLY</option>',
		                  '</field>',
		                  '<field name="virtuemart__category__page_types" type="Checkboxes"',
		                  '       default="categories,items"',
		                  '       label="CON_PAGE_TYPES">',
		                  '    <option value="categories">RL_CATEGORIES</option>',
		                  '    <option value="items">RL_PRODUCTS</option>',
		                  '</field>',
		                  '<field name="@showon__virtuemart__category__b" type="ShowOn" />',
		                  '',
		                  '<!-- VIRTUEMART :: PRODUCTS -->',
		                  '<field name="@showon__virtuemart__item__a" type="ShowOn" value="type:virtuemart__item" />',
		                  '<field name="virtuemart__item" type="VirtuemartItems"',
		                  '       multiple="true" default=""',
		                  '       label="CON_ARTICLES"',
		                  '       hiddenLabel="true" />',
		                  '<field name="@showon__virtuemart__item__b" type="ShowOn" />',
		                  '',
		                  '<field name="@showon__virtuemart__b" type="ShowOn" />'];
		$extendFile    = 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_conditions' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'item_rule.xml';
		$extendAfter   = '<field name="@showon__zoo__b" type="ShowOn" />';
		
		CoreFileExtenderHelper::handleCoreFileExtender($installer, $extensionNames, $extendName, $extendContent, $extendFile, null, $extendAfter, $extendVersion, $force);
		
		
		// administrator/components/com_conditions/forms/item_rule.xml [Settings]
		$extendName    = 'Virtuemart-Extension - Settings';
		$extendContent = ['<group label="CON_VIRTUEMART">',
		                  '    <option value="virtuemart__page_type" class="check_enabled" group_name="CON_VIRTUEMART">CON_PAGE_TYPES</option>',
		                  '    <option value="virtuemart__category" class="check_enabled" group_name="CON_VIRTUEMART">CON_CATEGORIES</option>',
		                  '    <option value="virtuemart__item" class="check_enabled" group_name="CON_VIRTUEMART">CON_PRODUCTS</option>',
		                  '</group>'];
		$extendFile    = 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_conditions' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'item_rule.xml';
		$extendBefore  = '<group label="CON_ZOO">';
		
		CoreFileExtenderHelper::handleCoreFileExtender($installer, $extensionNames, $extendName, $extendContent, $extendFile, $extendBefore, null, $extendVersion, $force);
		
		
		// administrator/components/com_conditions/src/Form/Field/ConditionRulesField.php
		$extendName    = 'Virtuemart-Extension - getDisabledTypes';
		$extendContent = ['$extensions = [\'hikashop\', \'flexicontent\', \'k2\', \'zoo\', \'virtuemart\'];'];
		$extendFile    = 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_conditions' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR . 'Field' . DIRECTORY_SEPARATOR . 'ConditionRulesField.php';
		$extendAfter   = '$extensions = [\'hikashop\', \'flexicontent\', \'k2\', \'zoo\'];';
		
		CoreFileExtenderHelper::handleCoreFileExtender($installer, $extensionNames, $extendName, $extendContent, $extendFile, null, $extendAfter, $extendVersion, $force);
		
		
		// administrator/components/com_conditions/src/Helper/ConvertAssignments.php
		$extendName    = 'Virtuemart-Extension - addRules';
		$extendContent = ['if (empty($excludes[\'virtuemart\']))',
		                  '{',
		                  '    self::addRuleBasic(\'virtuemart__page_type\', \'assignto_virtuemartpagetypes\', $params, $groups);',
		                  '    self::addRuleCategory(\'virtuemart__category\', \'assignto_virtuemartcats\', $params, $groups);',
		                  '    self::addRuleBasic(\'virtuemart__item\', \'assignto_virtuemartproducts\', $params, $groups);',
		                  '}'];
		$extendFile    = 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_conditions' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Helper' . DIRECTORY_SEPARATOR . 'ConvertAssignments.php';
		$extendBefore  = 'if (empty($excludes[\'zoo\']))';
		
		CoreFileExtenderHelper::handleCoreFileExtender($installer, $extensionNames, $extendName, $extendContent, $extendFile, $extendBefore, null, $extendVersion, $force);
	
		
		// copy new files
		$source      = __DIR__ . DIRECTORY_SEPARATOR . 'com_conditions__virtuemart_files';
		$destination = JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_conditions';
		CoreFileExtenderHelper::handleFileCopy($installer, $extensionNames, $source, $destination, $force);
	}