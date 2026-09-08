<?php
	/**
	 * @package         RegularLabs-Conditions-Virtuemart-Extension
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	/** @noinspection PhpUnused */
	
	/** @noinspection HtmlUnknownAttribute */
	
	use Joomla\Plugin\System\RegularLabsConditionsVirtuemartExtension\Helper\CoreFileExtenderHelper;
	
	function ComConditions() : void
	{
		$extendVersion  = 1.0;
		$extensionNames = ['COM_CONDITIONS', 'PKG_CONDITIONS'];
		
		// administrator/components/com_conditions/config.xml
		CoreFileExtenderHelper::handleCoreFileExtender(
			$extensionNames,
			'Virtuemart-Extension - CON_3RD_PARTY_EXTENSIONS',
			['<option value="virtuemart">CON_VIRTUEMART</option>'],
			'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_conditions' . DIRECTORY_SEPARATOR . 'config.xml',
			null,
			'<option value="hikashop">CON_HIKASHOP</option>',
			$extendVersion
		);
		
		// administrator/components/com_conditions/forms/item_rule.xml [Group]
		CoreFileExtenderHelper::handleCoreFileExtender(
			$extensionNames,
			'Virtuemart-Extension - Group',
			[
				'<!-- VIRTUEMART -->',
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
				'    <option value="productdetails">COM_VIRTUEMART_PRODUCT</option>',
				'    <option value="products">Breakdesigns CustomFilter</option>',
				'    <option value="cart">COM_VIRTUEMART_CART_VIEW_DEFAULT_TITLE</option>',
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
				'       default="categories,productdetails,products"',
				'       label="CON_PAGE_TYPES">',
				'    <option value="categories">COM_VIRTUEMART_PRODUCT_CATEGORY</option>',
				'    <option value="productdetails">COM_VIRTUEMART_PRODUCT</option>',
				'    <option value="products">Breakdesigns CustomFilter</option>',
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
				'<field name="@showon__virtuemart__b" type="ShowOn" />',
			],
			'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_conditions' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'item_rule.xml',
			null,
			'<field name="@showon__hikashop__b" type="ShowOn" />',
			$extendVersion
		);
		
		// administrator/components/com_conditions/forms/item_rule.xml [Settings]
		CoreFileExtenderHelper::handleCoreFileExtender(
			$extensionNames,
			'Virtuemart-Extension - Settings',
			[
				'<group label="CON_VIRTUEMART">',
				'    <option value="virtuemart__page_type" class="check_enabled" group_name="CON_VIRTUEMART">CON_PAGE_TYPES</option>',
				'    <option value="virtuemart__category" class="check_enabled" group_name="CON_VIRTUEMART">CON_CATEGORIES</option>',
				'    <option value="virtuemart__item" class="check_enabled" group_name="CON_VIRTUEMART">CON_PRODUCTS</option>',
				'</group>',
			],
			'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_conditions' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'item_rule.xml',
			'<group label="CON_HIKASHOP">',
			null,
			$extendVersion
		);
		
		// administrator/components/com_conditions/src/Form/Field/ConditionRulesField.php
		CoreFileExtenderHelper::handleCoreFileExtender(
			$extensionNames,
			'Virtuemart-Extension - getDisabledTypes',
			['$extensions = [\'hikashop\', \'flexicontent\', \'k2\', \'zoo\', \'virtuemart\'];'],
			'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_conditions' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR . 'Field' . DIRECTORY_SEPARATOR . 'ConditionRulesField.php',
			null,
			'$extensions = [\'hikashop\', \'flexicontent\', \'k2\', \'zoo\'];',
			$extendVersion
		);
		
		// administrator/components/com_conditions/src/Helper/ConvertAssignments.php
		CoreFileExtenderHelper::handleCoreFileExtender(
			$extensionNames,
			'Virtuemart-Extension - addRules',
			[
				'if (empty($excludes[\'virtuemart\']))',
				'{',
				'    self::addRuleBasic(\'virtuemart__page_type\', \'assignto_virtuemartpagetypes\', $params, $groups);',
				'    self::addRuleCategory(\'virtuemart__category\', \'assignto_virtuemartcats\', $params, $groups);',
				'    self::addRuleBasic(\'virtuemart__item\', \'assignto_virtuemartproducts\', $params, $groups);',
				'}',
			],
			'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_conditions' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Helper' . DIRECTORY_SEPARATOR . 'ConvertAssignments.php',
			'if (empty($excludes[\'hikashop\']))',
			null,
			$extendVersion
		);
		
		// copy new files
		CoreFileExtenderHelper::handleFileCopy(
			$extensionNames,
			__DIR__ . DIRECTORY_SEPARATOR . 'com_conditions__virtuemart_files',
			JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_conditions'
		);
	}
