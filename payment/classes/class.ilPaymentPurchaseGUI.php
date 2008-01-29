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
* class ilpaymentpurchasegui
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/

require_once "./classes/class.ilObjectGUI.php";

class ilPaymentPurchaseGUI extends ilObjectGUI
{
	var $ctrl;
	var $ilias;
	var $lng;
	var $tpl;

	var $object = null;

	function ilPaymentPurchaseGUI($a_ref_id)
	{
		global $ilCtrl,$lng,$ilErr,$ilias,$tpl,$tree;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,array("ref_id"));

		$this->ilErr =& $ilErr;
		$this->ilias =& $ilias;

		$this->lng =& $lng;
		$this->lng->loadLanguageModule('payment');

		$this->tpl =& $tpl;

		$this->ref_id = $a_ref_id;

		$this->object =& ilObjectFactory::getInstanceByRefId($this->ref_id);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd();

		if (!$cmd = $this->ctrl->getCmd())
		{
			$cmd = "showDetails";
		}
		
		$this->__buildHeader();

		$this->$cmd();
	}

	function showDetails()
	{
		if($this->object->getType() == 'crs' && $this->object->getSubscriptionMaxMembers() > 0)
		{
			$this->object->initCourseMemberObject();
			
			if($this->object->getSubscriptionMaxMembers() <= $this->object->members_obj->getCountMembers())
			{			  
				ilUtil::sendInfo($this->lng->txt('pay_crs_max_members_exceeded'));
				return false;
			}
		}
		
		$this->__initPaymentObject();
		$this->__initPricesObject();
		$this->__initShoppingCartObject();

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.pay_purchase_details.html','payment');

		if($this->pobject->getStatus() == $this->pobject->STATUS_EXPIRES)
		{
			ilUtil::sendInfo($this->lng->txt('pay_expires_info'));

			return false;
		}

		$prices = $this->price_obj->getPrices();
		$buyedObject = "";
		if($this->sc_obj->isInShoppingCart($this->pobject->getPobjectId()))
		{
			$buyedObject = $this->sc_obj->getEntry($this->pobject->getPobjectId());
			if (is_array($prices) &&
				count($prices) > 1)
			{
				ilUtil::sendInfo($this->lng->txt('pay_item_already_in_sc_choose_another'));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt('pay_item_already_in_sc'));
			}

			$this->tpl->setCurrentBlock("shopping_cart");
			$this->tpl->setVariable("LINK_GOTO_SHOPPING_CART", "ilias.php?cmdClass=ilpaymentgui&baseClass=ilPersonalDesktopGUI&cmd=showShoppingCart");
			$this->tpl->setVariable("TXT_GOTO_SHOPPING_CART", $this->lng->txt('pay_goto_shopping_cart'));
#			$this->tpl->setVariable("TXT_BUY", $this->lng->txt('pay_click_to_buy'));
			$this->tpl->parseCurrentBlock("shopping_cart");
		}

		$this->ctrl->setParameter($this, "ref_id", $this->pobject->getRefId());

#		if (!is_array($buyedObject) ||
#			(is_array($buyedObject) && is_array($prices) && count($prices) > 1))
#		{
			$this->tpl->setVariable("DETAILS_FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_'.$this->object->getType().'_b.gif'));
			$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_'.$this->object->getType()));
			$this->tpl->setVariable("TITLE",$this->object->getTitle());

			// payment infos
			$this->tpl->setVariable("TXT_INFO",$this->lng->txt('info'));
			switch($this->pobject->getPayMethod())
			{
				case $this->pobject->PAY_METHOD_BILL:
					$this->tpl->setVariable("INFO_PAY",$this->lng->txt('pay_bill'));
					$this->tpl->setVariable("INPUT_CMD",'getBill');
					$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_get_bill'));
					break;

				case $this->pobject->PAY_METHOD_BMF:
					$this->tpl->setVariable("INFO_PAY",$this->lng->txt('pay_info'));
					if (is_array($buyedObject))
					{
						if (is_array($prices) && count($prices) > 1)
						{
							$this->tpl->setVariable("INPUT_CMD",'addToShoppingCart');
							$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_change_price'));
						}
					}
					else
					{
						$this->tpl->setVariable("INPUT_CMD",'addToShoppingCart');
						$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_add_to_shopping_cart'));
					}
					break;

				case $this->pobject->PAY_METHOD_PAYPAL:
					$this->tpl->setVariable("INFO_PAY",$this->lng->txt('pay_info'));
					if (is_array($buyedObject))
					{
						if (is_array($prices) && count($prices) > 1)
						{
							$this->tpl->setVariable("INPUT_CMD",'addToShoppingCart');
							$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_change_price'));
						}
					}
					else
					{
						$this->tpl->setVariable("INPUT_CMD",'addToShoppingCart');
						$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_add_to_shopping_cart'));
					}
					break;
			}

			$this->tpl->setVariable("ROWSPAN",count($prices));
			$this->tpl->setVariable("TXT_PRICES",$this->lng->txt('prices'));
#		}

		if (is_array($prices))
		{
#			if (count($prices) > 1)
#			{
				$counter = 0;
				foreach($prices as $price)
				{
					if ($counter == 0)
					{
						$placeholderCheckbox = "CHECKBOX";
						$placeholderDuration = "DURATION";
						$placeholderPrice = "PRICE";
					}
					else
					{
						$placeholderCheckbox = "ROW_CHECKBOX";
						$placeholderDuration = "ROW_DURATION";
						$placeholderPrice = "ROW_PRICE";
					}
					$this->tpl->setCurrentBlock("price_row");
					if ($buyedObject["price_id"] == $price['price_id'])
					{
						$this->tpl->setVariable($placeholderCheckbox,ilUtil::formRadioButton(1,'price_id',$price['price_id']));
					}
					else
					{
						$this->tpl->setVariable($placeholderCheckbox,ilUtil::formRadioButton(0,'price_id',$price['price_id']));
					}
					$this->tpl->setVariable($placeholderDuration,$price['duration'].' '.$this->lng->txt('paya_months'));
					$this->tpl->setVariable($placeholderPrice,ilPaymentPrices::_getPriceString($price['price_id']));
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
#			}
#			else if (!is_array($buyedObject))
#			{
#				foreach($prices as $price)
#				{
#					$this->tpl->setVariable("CHECKBOX",ilUtil::formRadioButton(0,'price_id',$price['price_id']));
#					$this->tpl->setVariable("DURATION",$price['duration'].' '.$this->lng->txt('paya_months'));
#					$this->tpl->setVariable("PRICE",ilPaymentPrices::_getPriceString($price['price_id']));
#				}
#			}
		}

	}

	function addToShoppingCart()
	{
		if($this->object->getType() == 'crs' && $this->object->getSubscriptionMaxMembers() > 0)
		{
			$this->object->initCourseMemberObject();
			
			if($this->object->getSubscriptionMaxMembers() <= $this->object->members_obj->getCountMembers())
			{			  
				ilUtil::sendInfo($this->lng->txt('pay_crs_max_members_exceeded'));
				return false;
			}
		}
		
		if(!isset($_POST['price_id']))
		{
			ilUtil::sendInfo($this->lng->txt('pay_select_price'));
			$this->showDetails();

			return true;
		}
		else
		{
			$this->__initPaymentObject();
			$this->__initShoppingCartObject();
			

			$this->sc_obj->setPriceId((int) $_POST['price_id']);
			$this->sc_obj->setPobjectId($this->pobject->getPobjectId());
			$this->sc_obj->add();

#			$tmp_obj =& ilObjectFactory::getInstanceByRefId($this->pobject->getRefId());

			$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.pay_purchase_details.html','payment');
			$this->tpl->setCurrentBlock("shopping_cart");
			$this->tpl->setVariable("LINK_GOTO_SHOPPING_CART", "ilias.php?cmdClass=ilpaymentgui&baseClass=ilPersonalDesktopGUI&cmd=showShoppingCart");
			$this->tpl->setVariable("TXT_GOTO_SHOPPING_CART", $this->lng->txt('pay_goto_shopping_cart'));
#			$this->tpl->setVariable("TXT_BUY", $this->lng->txt('pay_click_to_buy'));
			$this->tpl->parseCurrentBlock("shopping_cart");

			ilUtil::sendInfo($this->lng->txt('pay_added_to_shopping_cart'));

			return true;
		}
	}

	// PRIVATE
	function __initShoppingCartObject()
	{
		include_once './payment/classes/class.ilPaymentShoppingCart.php';

		$this->sc_obj =& new ilPaymentShoppingCart($this->ilias->account);

		return true;
	}

	function __initPaymentObject()
	{
		include_once './payment/classes/class.ilPaymentObject.php';

		$this->pobject =& new ilPaymentObject($this->ilias->account,ilPaymentObject::_lookupPobjectId($this->ref_id));

		return true;
	}
	function __initPricesObject()
	{
		include_once './payment/classes/class.ilPaymentPrices.php';
		
		$this->price_obj =& new ilPaymentPrices($this->pobject->getPobjectId());

		return true;
	}

	function __buildHeader()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.payb_content.html");
		
		$this->tpl->setVariable("HEADER",$this->object->getTitle());
		$this->tpl->setVariable("DESCRIPTION",$this->object->getDescription());

#		$this->__buildStylesheet();
#		$this->__buildStatusline();
	}

	function  __buildStatusline()
	{
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->__buildLocator();
	}	

	function __buildLocator()
	{
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));

		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->lng->txt("repository"));
		$this->tpl->setVariable("LINK_ITEM", "../repository.php?getlast=true");
		$this->tpl->parseCurrentBlock();

		// CHECK for new mail and info
		ilUtil::sendInfo();

		return true;
	}

	function __buildStylesheet()
	{
		$this->tpl->setVariable("LOCATION_STYLESHEET",ilUtil::getStyleSheetLocation());
	}

	

}
?>