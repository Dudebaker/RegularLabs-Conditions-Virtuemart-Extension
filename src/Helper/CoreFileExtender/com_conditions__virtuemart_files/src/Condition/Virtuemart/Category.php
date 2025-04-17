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
			if (RL_Input::getCmd('option') !== 'com_virtuemart')
			{
				return false;
			}
			
			if (empty($this->selection))
			{
				return false;
			}
			
			$app = JFactory::getApplication();
			
			$pageTypes  = $this->params->page_types ?? [];
			$isCategory = RL_Input::getCmd('view') === 'category';
			$isProduct  = RL_Input::getCmd('view') === 'productdetails';
			
			if (!(in_array('categories', $pageTypes) && $isCategory)
			    && !(in_array('items', $pageTypes) && $isProduct))
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
			}
			else
			{
				if ($isProduct)
				{
					/** @var VirtueMartModelProduct $modelProduct */
					$modelProduct = VmModel::getModel('Product');
					
					$product = $modelProduct->getProduct(RL_Input::getCmd('virtuemart_product_id'), true, false);
					
					$categoryIds = (array) $product->categories;
				}
				else
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
		
		
		private function getCategoryIds(bool $is_category = false) : array
		{
			if ($is_category)
			{
				return (array) $this->request->id;
			}
			
			switch (true)
			{
				case (RL_Input::getCmd('view') === 'category'):
					include_once JPATH_ADMINISTRATOR . '/components/com_hikashop/helpers/helper.php';
					$menuClass = hikashop_get('class.menus');
					$menuData  = $menuClass->get($this->request->Itemid);
					
					return $this->makeArray($menuData->hikashop_params['selectparentlisting']);
				
				case ($this->request->id):
					$query = $this->db->getQuery(true)
					                  ->select('c.category_id')
					                  ->from('#__hikashop_product_category AS c')
					                  ->where('c.product_id = ' . (int) RL_Input::getCmd('virtuemart_product_id'));
					$this->db->setQuery($query);
					$cats = $this->db->loadColumn();
					
					return $this->makeArray($cats);
				
				default:
					return [];
			}
		}
		
		private function getCategoryParentIds(int $id = 0) : array
		{
			return $this->getParentIds($id, 'hikashop_category', 'category_parent_id', 'category_id');
		}
	}
