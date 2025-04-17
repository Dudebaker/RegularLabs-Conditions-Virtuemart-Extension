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
	
	namespace RegularLabs\Component\Conditions\Administrator\Form\Field;
	
	defined('_JEXEC') or die;
	
	use RegularLabs\Library\Form\Form;
	use RegularLabs\Library\Form\FormField as RL_FormField;
	use stdClass;
	use VirtueMartModelProduct;
	use VmConfig;
	use VmModel;
	
	class VirtuemartItemsField extends RL_FormField
	{
		public bool $is_select_list = true;
		public bool $use_ajax = true;
		public bool $use_tree_select = true;
		
		public function __construct($form = null)
		{
			
			if (!class_exists('VmConfig'))
			{
				require(JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php');
			}
			
			VmConfig::loadConfig();
			
			parent::__construct($form);
		}
		
		public function getNamesByIds(array $values, array $attributes) : array
		{
			/** @var VirtueMartModelProduct $modelProduct */
			$modelProduct = VmModel::getModel('Product');
			$modelProduct->_noLimit = true;
			
			$products = $modelProduct->getProducts($values, false, false);
			$items    = $this->getMappedProducts($products);
			
			return Form::getNamesWithExtras($items, ['category', 'unpublished']);
		}
		
		protected function getOptions()
		{
			$query = $this->db->getQuery(true)
			                  ->select('COUNT(*)')
			                  ->from('#__virtuemart_products AS p')
			                  ->where('p.published = 1');
			$this->db->setQuery($query);
			$total = $this->db->loadResult();
			
			if ($total > $this->max_list_count)
			{
				return -1;
			}
			
			/** @var VirtueMartModelProduct $modelProduct */
			$modelProduct = VmModel::getModel('Product');
			$modelProduct->_noLimit = true;
			
			$productIds = $modelProduct->sortSearchListQuery();
			
			$products   = $modelProduct->getProducts($productIds, false, false);
			$items      = $this->getMappedProducts($products);
			
			return $this->getOptionsByList($items, ['category', 'id', 'unpublished'], -2);
		}
		
		protected function getMappedProducts($products) : array
		{
			$items = [];
			
			foreach ($products as $product)
			{
				$tmp            = new stdClass();
				$tmp->id        = $product->virtuemart_product_id;
				$tmp->name      = $product->product_name;
				$tmp->published = $product->published;
				
				if (count($product->categoryItem) === 0)
				{
					$items[] = $tmp;
					continue;
				}
				
				foreach ($product->categoryItem as $category)
				{
					$category = (object) $category;
					
					if ($category->virtuemart_category_id === $product->canonCatId)
					{
						$tmp->category = $category->category_name;
						break;
					}
				}
				
				if (!isset($tmp->category))
				{
					$category      = end($product->categoryItem);
					$tmp->category = $category->category_name;
				}
				
				$items[] = $tmp;
			}
			
			usort($items, static function ($a, $b)
			{
				return strcmp($a->name, $b->name);
			});
			
			return $items;
		}
	}
