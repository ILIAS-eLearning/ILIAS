<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
/**
* Class ilPaymentGUI
*
* @author Stefan Meyer
* @version $Id$
*
* @ilCtrl_Calls ilPaymentGUI: ilPaymentShoppingCartGUI, ilPaymentBuyedObjectsGUI, ilPurchaseBMFGUI, ilPurchaseBillGUI
*
* @package core
*/
include_once "./payment/classes/class.ilPaymentBaseGUI.php";
include_once "./payment/classes/class.ilPaymentShoppingCartGUI.php";

class ilPaymentGUI extends ilPaymentBaseGUI
{
	var $ctrl;

	var $user_obj;

	function ilPaymentGUI(&$user_obj)
	{
		global $ilCtrl;

		$this->ctrl =& $ilCtrl;
		
		$this->ilPaymentBaseGUI();
		$this->setMainSection($this->BASE);
		
		// Get user object
		$this->user_obj =& $user_obj;
	}
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $tree;

		$cmd = $this->ctrl->getCmd();

		switch ($this->ctrl->getNextClass($this))
		{
			case 'ilpaymentshoppingcartgui':
				$this->setSection($this->SECTION_SHOPPING_CART);
				$this->buildHeader();

				include_once './payment/classes/class.ilPaymentShoppingCartGUI.php';

				$pt =& new ilPaymentShoppingCartGUI($this->user_obj);
				
				$this->ctrl->forwardCommand($pt);
				break;

			case 'ilpaymentbuyedobjectsgui':
				$this->setSection($this->SECTION_BUYED_OBJECTS);
				$this->buildHeader();

				include_once './payment/classes/class.ilPaymentBuyedObjectsGUI.php';

				$pt =& new ilPaymentBuyedObjectsGUI($this->user_obj);
				
				$this->ctrl->forwardCommand($pt);
				break;
				
			case 'ilpurchasebillgui':
				$this->setSection($this->SECTION_BUYED_OBJECTS);
				$this->buildHeader();

				include_once './payment/classes/class.ilPurchaseBillGUI.php';

				$pt =& new ilPurchaseBillGUI($this->user_obj);
				
				$this->ctrl->forwardCommand($pt);
				break;
				
				
			case 'ilpurchasebmfgui':
				$this->setSection($this->SECTION_BUYED_OBJECTS);
				$this->buildHeader();

				include_once './payment/classes/class.ilPurchaseBMFGUI.php';

				$pt =& new ilPurchaseBMFGUI($this->user_obj);
				
				$this->ctrl->forwardCommand($pt);
				break;

			default:
				$this->__forwardToDefault();
				break;
		}
	}

	function __forwardToDefault()
	{
		$this->ctrl->redirectByClass('ilpaymentshoppingcartgui');

		return true;
	}

}
?>