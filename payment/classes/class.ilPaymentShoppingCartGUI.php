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
* Class ilPaymentShoppingCartGUI
*
* @author Stefan Meyer
* @version $Id$
*
* @package core
*/

include_once './payment/classes/class.ilPaymentShoppingCart.php';
include_once './payment/classes/class.ilPaymentBaseGUI.php';

class ilPaymentShoppingCartGUI extends ilPaymentBaseGUI
{
	var $ctrl;

	var $lng;
	var $user_obj;

	/*
	 * shopping cart obj
	 */
	var $psc_obj = null;

	function ilPaymentShoppingCartGUI(&$user_obj)
	{
		global $ilCtrl;

		$this->ctrl =& $ilCtrl;

		$this->ilPaymentBaseGUI();

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

			default:
				if(!$cmd = $this->ctrl->getCmd())
				{
					$cmd = 'showItems';
				}
				$this->$cmd();
				break;
		}
	}

	function showItems()
	{
		$this->__initShoppingCartObject();

		if(!count($this->psc_obj->getEntries()))
		{
			sendInfo($this->lng->txt('pay_shopping_cart_empty'));
		}

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_shopping_cart.html',true);
		$this->tpl->setVariable("HALLO",'hallo');

	}
	
	function __initShoppingCartObject()
	{
		$this->psc_obj =& new ilPaymentShoppingCart($this->user_obj);
	}

}
?>