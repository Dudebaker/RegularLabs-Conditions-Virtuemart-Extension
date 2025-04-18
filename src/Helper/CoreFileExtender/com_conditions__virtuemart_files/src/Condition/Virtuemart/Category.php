<?php
	/**
	 * @package    Conditions
	 * @subpackage RegularLabs-Conditions-Virtuemart-Extension
	 * @version    24.6.11852
	 *
	 * @author     Peter van Westen <info@regularlabs.com>
	 * @author     Dudebaker
	 * @copyright  Copyright © 2024 Regular Labs All Rights Reserved
	 * @license    GNU General Public License version 2 or later
	 */
	
	namespace RegularLabs\Component\Conditions\Administrator\Condition\Virtuemart;
	
	defined('_JEXEC') or die;
	
	use Joomla\CMS\Factory as JFactory;
	use RegularLabs\Component\Conditions\Administrator\Condition\HasArraySelection;
	use RegularLabs\Library\Input as RL_Input;
	use VirtueMartModelCategory;
	use VirtueMartModelProduct;
	use VmConfig;
	use VmModel;
	
	class Category extends Virtuemart
	{
		use HasArraySelection;
		
		public function pass() : bool
		{
			if (RL_Input::getCmd('option') !== 'com_virtuemart' && RL_Input::getCmd('option') !== 'com_customfilters')
			{
				return false;
			}
			
			if (empty($this->selection))
			{
				return false;
			}
			
			$app = JFactory::getApplication();
			
			$pageTypes                 = $this->params->page_types ?? [];
			$isCategory                = RL_Input::getCmd('view') === 'category';
			$isProduct                 = RL_Input::getCmd('view') === 'productdetails';
			$isBreakDesignCustomFilter = RL_Input::getCmd('view') === 'products';
			
			if (!(in_array('categories', $pageTypes) && $isCategory)
			    && !(in_array('productdetails', $pageTypes) && $isProduct)
			    && !(in_array('products', $pageTypes) && $isBreakDesignCustomFilter))
			{
				return false;
			}
			
			if (!class_exists('VmConfig'))
			{
				require(JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php');
			}
			
			VmConfig::loadConfig();
			
			$categoryIds = [];
			
			if ($isCategory)
			{
				$categoryIds[] = RL_Input::getCmd('virtuemart_category_id');
			} else if ($isBreakDesignCustomFilter)
			{
				$categoryIds = RL_Input::getRaw('virtuemart_category_id');
			} else
			{
				if ($isProduct)
				{
					/** @var VirtueMartModelProduct $modelProduct */
					$modelProduct = VmModel::getModel('Product');
					
					$product = $modelProduct->getProduct(RL_Input::getCmd('virtuemart_product_id'), true, false);
					
					$categoryIds = (array)$product->categories;
				} else
				{
					return false;
				}
			}
			
			$pass = $this->passSimple($categoryIds);
			
			// $this->params->include_children
			// 0 = No
			// 1 = Yes
			// 2 = Only
			
			if ($pass)
			{
				return $this->params->include_children !== 2;
			}
			
			if (!$this->params->include_children)
			{
				return false;
			}
			
			// search parents
			
			$parentIds = [];
			
			/** @var VirtueMartModelCategory $modelCategory */
			$modelCategory = VmModel::getModel('Category');
			
			foreach ($categoryIds as $categoryId)
			{
				$category = $modelCategory->getCategory($categoryId, false);
				
				if (empty($category->category_parent_id))
				{
					continue;
				}
				
				array_pop($category->parents);
				
				foreach ($category->parents as $parent)
				{
					$parentIds[] = $parent->virtuemart_category_id;
				}
			}
			
			$parentIds = array_unique($parentIds);
			
			return $this->passSimple($parentIds);
		}
	}
