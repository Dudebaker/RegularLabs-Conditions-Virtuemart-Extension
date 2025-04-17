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
	
	use RegularLabs\Component\Conditions\Administrator\Condition\HasArraySelection;
	use RegularLabs\Library\Input as RL_Input;
	
	class Item extends Virtuemart
	{
		use HasArraySelection;
		
		public function pass() : bool
		{
			if (RL_Input::getCmd('option')!== 'com_virtuemart')
			{
				return false;
			}
			
			if (RL_Input::getCmd('view') !== 'productdetails')
			{
				return false;
			}
			
			return $this->passSimple(RL_Input::getCmd('virtuemart_product_id'));
		}
	}
