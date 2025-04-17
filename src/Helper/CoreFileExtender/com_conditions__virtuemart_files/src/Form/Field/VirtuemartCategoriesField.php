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
	
	use RegularLabs\Library\ArrayHelper as RL_Array;
	use RegularLabs\Library\Form\Form;
	use RegularLabs\Library\Form\FormField as RL_FormField;
	use RegularLabs\Library\Language as RL_Language;
	use stdClass;
	use VirtueMartModelCategory;
	use VmConfig;
	use VmModel;
	
	class VirtuemartCategoriesField extends RL_FormField
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
			RL_Language::load('com_conditions');
			
			/** @var VirtueMartModelCategory $modelCategory */
			$modelCategory = VmModel::getModel('Category');
			
			$categories = [];
			
			foreach ($values as $categoryId)
			{
				$category = $modelCategory->getCategory($categoryId, false);
				
				$tmp            = new stdClass();
				$tmp->id        = $category->virtuemart_category_id;
				$tmp->parent_id = $category->category_parent_id;
				$tmp->name      = $category->category_name;
				$tmp->published = $category->published;
				
				if (empty($tmp->parent_id))
				{
					$categories[] = $tmp;
					continue;
				}
				
				array_pop($category->parents);
				$category->parents = array_reverse($category->parents);
				
				foreach ($category->parents as $parent)
				{
					$tmp->name = $parent->category_name . ' &rarr; ' . $tmp->name;
				}
				
				$categories[] = $tmp;
			}
			
			return Form::getNamesWithExtras($categories, ['unpublished']);
		}
		
		protected function getOptions()
		{
			RL_Language::load('com_conditions');
			
			/** @var VirtueMartModelCategory $modelCategory */
			$modelCategory = VmModel::getModel('Category');
			$categoryTree  = $modelCategory->getCategoryTree();
			
			if (count($categoryTree) > $this->max_list_count)
			{
				return -1;
			}
			
			$this->value = RL_Array::toArray($this->value);
			
			$categories = [];
			
			foreach ($categoryTree as $category)
			{
				$tmp            = new stdclass();
				$tmp->id        = $category->virtuemart_category_id;
				$tmp->parent_id = $category->category_parent_id;
				$tmp->name      = $category->category_name;
				$tmp->published = $category->published;
				$tmp->level     = $category->level;
				$categories[]   = $tmp;
			}
			
			return $this->getOptionsByList($categories, ['unpublished']);
		}
	}
