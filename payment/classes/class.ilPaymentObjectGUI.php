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
* Class ilPaymentObjectGUI
*
* @author Stefan Meyer
* @version $Id$
*
* @package core
*/
include_once './payment/classes/class.ilPaymentObject.php';
include_once './payment/classes/class.ilPaymentBookings.php';

class ilPaymentObjectGUI extends ilPaymentBaseGUI
{
	var $ctrl;
	var $lng;
	var $user_obj;
	
	var $pobject = null;

	function ilPaymentObjectGUI(&$user_obj)
	{
		global $ilCtrl,$lng;

		$this->ctrl =& $ilCtrl;
		$this->ilPaymentBaseGUI();
		$this->user_obj =& $user_obj;

		$this->lng =& $lng;
		$this->lng->loadLanguageModule('crs');

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
					$cmd = 'showObjects';
				}
				$this->$cmd();
				break;
		}
	}

	function showObjects()
	{
		$this->showButton('showObjectSelector',$this->lng->txt('paya_sell_object'));

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_objects.html','payment');

		if(!count($objects = ilPaymentObject::_getObjectsData($this->user_obj->getId())))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_objects_assigned'));
			
			return true;
		}

		$this->__initPaymentObject();

		$img_change = "<img src=\"".ilUtil::getImagePath("edit.gif")."\" alt=\"".
			$this->lng->txt("edit")."\" title=\"".$this->lng->txt("edit").
			"\" border=\"0\" vspace=\"0\"/>";

		$counter = 0;
		foreach($objects as $data)
		{
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($data['ref_id']);
			$f_result[$counter][] = $tmp_obj->getTitle();


			switch($data['status'])
			{
				case $this->pobject->STATUS_BUYABLE:
					$f_result[$counter][] = $this->lng->txt('paya_buyable');
					break;

				case $this->pobject->STATUS_NOT_BUYABLE:
					$f_result[$counter][] = $this->lng->txt('paya_not_buyable');
					break;
					
				case $this->pobject->STATUS_EXPIRES:
					$f_result[$counter][] = $this->lng->txt('paya_expires');
					break;
			}
			switch($data['pay_method'])
			{
				case $this->pobject->PAY_METHOD_NOT_SPECIFIED:
					$f_result[$counter][] = $this->lng->txt('paya_pay_method_not_specified');
					break;

				case $this->pobject->PAY_METHOD_BILL:
					$f_result[$counter][] = $this->lng->txt('pays_bill');
					break;

				case $this->pobject->PAY_METHOD_BMF:
					$f_result[$counter][] = $this->lng->txt('pays_bmf');
					break;

				case $this->pobject->PAY_METHOD_PAYPAL:
					$f_result[$counter][] = $this->lng->txt('pays_paypal');
					break;
			}
			$tmp_user =& ilObjectFactory::getInstanceByObjId($data['vendor_id']);
			$f_result[$counter][] = $tmp_user->getFullname().' ['.$tmp_user->getLogin().']';

			// Get number of purchasers
			
			$f_result[$counter][] = ilPaymentBookings::_getCountBookingsByObject($data['pobject_id']);


			// edit link
			$this->ctrl->setParameter($this,"pobject_id",$data['pobject_id']);
#			$link_change = "<a href=\"".$this->ctrl->getLinkTarget($this,"editDetails")."\"> ".
#				$img_change."</a>";
			$link_change = "<div class=\"il_ContainerItemCommands\"><a class=\"il_ContainerItemCommand\" href=\"".$this->ctrl->getLinkTarget($this,"editDetails")."\">".$this->lng->txt("edit")."</a></div>";

			$f_result[$counter][] = $link_change;
			unset($tmp_user);
			unset($tmp_obj);

			++$counter;
		}

		return $this->__showObjectsTable($f_result);
	}

	function editDetails($a_show_confirm = false)
	{
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}
		$this->__initPaymentObject((int) $_GET['pobject_id']);
		$this->ctrl->setParameter($this,'pobject_id',(int) $_GET['pobject_id']);

		$this->showButton('editDetails',$this->lng->txt('paya_edit_details'));
		$this->showButton('editPrices',$this->lng->txt('paya_edit_prices'));
#		$this->__showPayMethodLink();

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_edit.html','payment');
		$this->tpl->setVariable("DETAILS_FORMACTION",$this->ctrl->getFormAction($this));

		if($a_show_confirm)
		{
			$this->tpl->setCurrentBlock("confirm_delete");
			$this->tpl->setVariable("CONFIRM_FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
			$this->tpl->setVariable("CONFIRM_CMD",'performDelete');
			$this->tpl->setVariable("TXT_CONFIRM",$this->lng->txt('confirm'));
			$this->tpl->parseCurrentBlock();
		}			

		
		$tmp_obj =& ilObjectFactory::getInstanceByRefId($this->pobject->getRefId());
		
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_'.$tmp_obj->getType().'_b.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_'.$tmp_obj->getType()));
		$this->tpl->setVariable("TITLE",$tmp_obj->getTitle());
		$this->tpl->setVariable("DESCRIPTION",$tmp_obj->getDescription());
		$this->tpl->setVariable("TXT_PATH",$this->lng->txt('path'));
		$this->tpl->setVariable("PATH",$this->__getHTMLPath($this->pobject->getRefId()));
		$this->tpl->setVariable("TXT_VENDOR",$this->lng->txt('paya_vendor'));
		$this->tpl->setVariable("VENDOR",$this->__showVendorSelector($this->pobject->getVendorId()));
		$this->tpl->setVariable("TXT_COUNT_PURCHASER",$this->lng->txt('paya_count_purchaser'));
		$this->tpl->setVariable("COUNT_PURCHASER",ilPaymentBookings::_getCountBookingsByObject((int) $_GET['pobject_id']));
		$this->tpl->setVariable("TXT_STATUS",$this->lng->txt('status'));
		$this->tpl->setVariable("STATUS",$this->__showStatusSelector());
		$this->tpl->setVariable("TXT_PAY_METHOD",$this->lng->txt('paya_pay_method'));
		$this->tpl->setVariable("PAY_METHOD",$this->__showPayMethodSelector());

		$this->tpl->setVariable("INPUT_CMD",'updateDetails');
		$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('save'));

		$this->tpl->setVariable("DELETE_CMD",'deleteObject');
		$this->tpl->setVariable("DELETE_VALUE",$this->lng->txt('delete'));
	}

	function deleteObject()
	{
		include_once './payment/classes/class.ilPaymentBookings.php';

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}
		if(ilPaymentBookings::_getCountBookingsByObject((int) $_GET['pobject_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_bookings_available'));
			$this->editDetails();

			return false;
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('paya_sure_delete_object'));
			$this->editDetails(true);

			return true;
		}
	}

	function performDelete()
	{
		include_once './payment/classes/class.ilPaymentPrices.php';
		include_once './payment/classes/class.ilPaymentBillVendor.php';

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}
		$this->__initPaymentObject((int) $_GET['pobject_id']);

		// delete object data
		$this->pobject->delete();
		
		// delete payment prices
		$price_obj =& new ilPaymentPrices((int) $_GET['pobject_id']);
		$price_obj->deleteAllPrices();
		unset($price_obj);

		$bv =& new ilPaymentBillVendor((int) $_GET['pobject_id']);
		$bv->delete();
		unset($bv);

		// delete bill vendor data if exists
		ilUtil::sendInfo($this->lng->txt('paya_deleted_object'));

		$this->showObjects();

		return true;
	}



	function editPayMethod()
	{
		$this->__initPaymentObject((int) $_GET['pobject_id']);

		switch($this->pobject->getPayMethod())
		{
			case $this->pobject->PAY_METHOD_NOT_SPECIFIED:
				ilUtil::sendInfo($this->lng->txt('paya_select_pay_method_first'));
				$this->editDetails();
				
				return true;
			case $this->pobject->PAY_METHOD_BMF:
				ilUtil::sendInfo($this->lng->txt('paya_no_settings_necessary'));
				$this->editDetails();
				
				return true;
			case $this->pobject->PAY_METHOD_PAYPAL:
				ilUtil::sendInfo($this->lng->txt('paya_no_settings_necessary'));
				$this->editDetails();
				
				return true;
		}
		
		$this->editDetails();
		
		return true;
	}	
	function editPrices($a_show_delete = false)
	{
		include_once './payment/classes/class.ilPaymentPrices.php';
		include_once './payment/classes/class.ilPaymentCurrency.php';
		include_once "./Services/Table/classes/class.ilTableGUI.php";
		include_once './payment/classes/class.ilGeneralSettings.php';

		$genSet = new ilGeneralSettings();

		$_SESSION['price_ids'] = $_SESSION['price_ids'] ? $_SESSION['price_ids'] : array();

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}
		$this->ctrl->setParameter($this,'pobject_id',(int) $_GET['pobject_id']);

		$this->showButton('editDetails',$this->lng->txt('paya_edit_details'));
		$this->showButton('editPrices',$this->lng->txt('paya_edit_prices'));

		$this->__initPaymentObject((int) $_GET['pobject_id']);
#		$this->__showPayMethodLink();

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_edit_prices.html','payment');

		$price_obj =& new ilPaymentPrices((int) $_GET['pobject_id']);
		$prices = $price_obj->getPrices();

		// No prices created
		if(!count($prices))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_price_available'));

			$this->tpl->setCurrentBlock("price_info");
			$this->tpl->setVariable("CONFIRM_FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("CONFIRM_CMD",'addPrice');
			$this->tpl->setVariable("TXT_CONFIRM",$this->lng->txt('paya_add_price'));
			$this->tpl->parseCurrentBlock();
			
			return true;
		}
		// Show confirm delete
		if($a_show_delete)
		{
			ilUtil::sendInfo($this->lng->txt('paya_sure_delete_selected_prices'));

			$this->tpl->setCurrentBlock("cancel");
			$this->tpl->setVariable("CANCEL_CMD",'editPrices');
			$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("price_info");
			$this->tpl->setVariable("CONFIRM_FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("CONFIRM_CMD",'performDeletePrice');
			$this->tpl->setVariable("TXT_CONFIRM",$this->lng->txt('paya_delete_price'));
			$this->tpl->parseCurrentBlock();
		}			

		// Fill table cells
		$tpl =& new ilTemplate('tpl.table.html',true,true);

		// set table header
		$tpl->setCurrentBlock("tbl_form_header");
		
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->addBlockfile("TBL_CONTENT", "tbl_content",'tpl.paya_edit_prices_row.html','payment');
		
		$counter = 0;
		foreach($prices as $price)
		{
			$currency = ilPaymentCurrency::_getCurrency($price['currency']);

			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL", ilUtil::switchColor($counter,"tblrow2","tblrow1"));

			$tpl->setVariable("CHECKBOX",ilUtil::formCheckBox(in_array($price['price_id'],$_SESSION['price_ids']) ? 1 : 0,
															  'price_ids[]',
															  $price['price_id']));
			$tpl->setVariable("DURATION_NAME",'prices['.$price['price_id'].'][duration]');
			$tpl->setVariable("DURATION",$price['duration']);
			$tpl->setVariable("MONTH",$this->lng->txt('paya_months'));
			$tpl->setVariable("UNIT_NAME",'prices['.$price['price_id'].'][unit_value]');
			$tpl->setVariable("UNIT",$price['unit_value']);
#			$tpl->setVariable("SHORTFORM",$this->lng->txt('currency_'.$currency['unit']));
			$tpl->setVariable("SHORTFORM",$genSet->get("currency_unit"));
			
			$tpl->setVariable("SUB_UNIT_NAME",'prices['.$price['price_id'].'][sub_unit_value]');
			$tpl->setVariable("SUB_UNIT",$price['sub_unit_value']);
#			$tpl->setVariable("SUB_UNIT_TXT",$this->lng->txt('currency_'.$currency['sub_unit']));
			$tpl->setVariable("SUB_UNIT_TXT",$genSet->get("currency_subunit"));
			$tpl->parseCurrentBlock();
			
			++$counter;
		}

		// SET FOOTER
		$tpl->setCurrentBlock("tbl_action_button");
		$tpl->setVariable("BTN_NAME","deletePrice");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("paya_delete_price"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("plain_buttons");
		$tpl->setVariable("PBTN_NAME","updatePrice");
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("paya_update_price"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("plain_buttons");
		$tpl->setVariable("PBTN_NAME","addPrice");
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("paya_add_price"));
		$tpl->parseCurrentBlock();


		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();


		$tbl = new ilTableGUI();
		$tbl->setTemplate($tpl);

		// title & header columns
		$tbl->setStyle('table','std');

		$tmp_obj =& ilObjectFactory::getInstanceByRefId($this->pobject->getRefId());

		$tbl->setTitle($tmp_obj->getTitle(),
					   "icon_".$tmp_obj->getType()."_b.gif",
					   $this->lng->txt("objs_".$tmp_obj->getType()));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt('duration'),
								   $this->lng->txt('price_a'),
								   ''));
		$tbl->setHeaderVars(array("",
								  "duration",
								  "price_unit",
								  "price_sub_unit"),
							array("ref_id" => $this->cur_ref_id));

		// control
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($price_obj->getPrices()));

		$tbl->disable("sort");

		// render table
		$tbl->render();

		$this->tpl->setVariable("PRICES_TABLE",$tpl->get());
		
		return true;
	}

	function addPrice()
	{
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}

		include_once './payment/classes/class.ilGeneralSettings.php';

		$genSet = new ilGeneralSettings();

		$this->ctrl->setParameter($this,'pobject_id',(int) $_GET['pobject_id']);

		$this->__initPaymentObject((int) $_GET['pobject_id']);

		$this->showButton('editDetails',$this->lng->txt('paya_edit_details'));
		$this->showButton('editPrices',$this->lng->txt('paya_edit_prices'));
#		$this->__showPayMethodLink();

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_add_price.html','payment');

		$this->tpl->setVariable("ADD_FORMACTION",$this->ctrl->getFormAction($this));

		$tmp_obj =& ilObjectFactory::getInstanceByRefId($this->pobject->getRefId());
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_'.$tmp_obj->getType().'_b.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_'.$tmp_obj->getType()));
		$this->tpl->setVariable("TITLE",$tmp_obj->getTitle());
		$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('paya_add_price_title'));
		
		// TODO show curency selector
#		$this->tpl->setVariable("TXT_PRICE_A",$this->lng->txt('currency_euro'));
#		$this->tpl->setVariable("TXT_PRICE_B",$this->lng->txt('currency_cent'));
		$this->tpl->setVariable("TXT_PRICE_A",$genSet->get("currency_unit"));
		$this->tpl->setVariable("TXT_PRICE_B",$genSet->get("currency_subunit"));
		
		$this->tpl->setVariable("MONTH",$this->lng->txt('paya_months'));
		$this->tpl->setVariable("TXT_DURATION",$this->lng->txt('duration'));
		$this->tpl->setVariable("TXT_PRICE",$this->lng->txt('price_a'));
		$this->tpl->setVariable("CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("ADD",$this->lng->txt('paya_add_price'));

		$this->tpl->setVariable("DURATION",$_POST['duration']);
		$this->tpl->setVariable("UNIT_VALUE",$_POST['unit']);
		$this->tpl->setVariable("SUB_UNIT",$_POST['SUB_UNIT']);
		
		return true;
	}

	function performAddPrice()
	{
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}
		
		include_once './payment/classes/class.ilPaymentPrices.php';
		include_once './payment/classes/class.ilPaymentCurrency.php';

		$currency = ilPaymentCurrency::_getAvailableCurrencies();

		$prices =& new ilPaymentPrices((int) $_GET['pobject_id']);

		$prices->setDuration($_POST['duration']);
		$prices->setUnitValue($_POST['unit']);
		$prices->setSubUnitValue($_POST['sub_unit']);
		$prices->setCurrency($currency[1]['currency_id']);

		if(!$prices->validate())
		{
			ilUtil::sendInfo($this->lng->txt('paya_price_not_valid'));
			$this->addPrice();

			return true;
		}
		$prices->add();

		ilUtil::sendInfo($this->lng->txt('paya_added_new_price'));
		$this->editPrices();

		return true;
	}
		
		

	function performDeletePrice()
	{
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}

		if(!count($_SESSION['price_ids']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_prices_selected'));
			
			$this->editPrices();
			return true;
		}
		include_once './payment/classes/class.ilPaymentPrices.php';
		
		$prices =& new ilPaymentPrices((int) $_GET['pobject_id']);

		foreach($_SESSION['price_ids'] as $price_id)
		{
			$prices->delete($price_id);
		}

		// check if it was last price otherwise set status to 'not_buyable'
		if(!count($prices->getPrices()))
		{
			$this->__initPaymentObject((int) $_GET['pobject_id']);

			$this->pobject->setStatus($this->pobject->STATUS_NOT_BUYABLE);
			$this->pobject->update();
			
			ilUtil::sendInfo($this->lng->txt('paya_deleted_last_price'));
		}
		unset($prices);
		unset($_SESSION['price_ids']);
		
		return $this->editPrices();
	}


	function deletePrice()
	{
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}

		if(!count($_POST['price_ids']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_prices_selected'));
			
			$this->editPrices();
			return true;
		}
		$_SESSION['price_ids'] = $_POST['price_ids'];

		$this->editPrices(true);
		return true;
	}
			
		
		

	function updatePrice()
	{
		include_once './payment/classes/class.ilPaymentPrices.php';

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}
		$po =& new ilPaymentPrices((int) $_GET['pobject_id']);

		$this->ctrl->setParameter($this,'pobject_id',(int) $_GET['pobject_id']);

		// validate
		foreach($_POST['prices'] as $price_id => $price)
		{
			$old_price = $po->getPrice($price_id);

			$po->setDuration($price['duration']);
			$po->setUnitValue($price['unit_value']);
			$po->setSubUnitValue($price['sub_unit_value']);
			$po->setCurrency($old_price['currency']);

			if(!$po->validate())
			{
				$error = true;
			}
		}
		if($error)
		{
			ilUtil::sendInfo($this->lng->txt('paya_insert_only_numbers'));

			$this->editPrices();
			return false;
		}
		foreach($_POST['prices'] as $price_id => $price)
		{
			$old_price = $po->getPrice($price_id);

			$po->setDuration($price['duration']);
			$po->setUnitValue($price['unit_value']);
			$po->setSubUnitValue($price['sub_unit_value']);
			$po->setCurrency($old_price['currency']);

			$po->update($price_id);
		}
		ilUtil::sendInfo($this->lng->txt('paya_updated_prices'));
		$this->editPrices();

		return true;
	}
		

	function updateDetails()
	{
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}
		$this->__initPaymentObject((int) $_GET['pobject_id']);
		$this->ctrl->setParameter($this,'pobject_id',(int) $_GET['pobject_id']);

		// read old settings
		$old_pay_method = $this->pobject->getPayMethod();
		$old_status = $this->pobject->getStatus();

		// check status changed from not_buyable
		if($old_status == $this->pobject->STATUS_NOT_BUYABLE and
		   (int) $_POST['status'] != $old_status)
		{
			// check pay_method edited
			switch((int) $_POST['pay_method'])
			{
				case $this->pobject->PAY_METHOD_NOT_SPECIFIED:
					ilUtil::sendInfo($this->lng->txt('paya_select_pay_method_first'));
					$this->editDetails();

					return false;
					
				case $this->pobject->PAY_METHOD_BILL:
					include_once './payment/classes/class.ilPaymentBillVendor.php';

					$bill_vendor =& new ilPaymentBillVendor((int) $_GET['pobject_id']);
					if(!$bill_vendor->validate())
					{
						ilUtil::sendInfo($this->lng->txt('paya_select_pay_method_first'));
						$this->editDetails();
						
						return false;
					}
					break;

				default:
					;
			}
			// check minimum one price
			include_once './payment/classes/class.ilPaymentPrices.php';

			$prices_obj =& new ilPaymentPrices((int) $_GET['pobject_id']);
			if(!count($prices_obj->getPrices()))
			{
				ilUtil::sendInfo($this->lng->txt('paya_edit_prices_first'));
				$this->editDetails();
						
				return false;
			}				
		}
		

		$this->pobject->setStatus((int) $_POST['status']);
		$this->pobject->setVendorId((int) $_POST['vendor']);
		$this->pobject->setPayMethod((int) $_POST['pay_method']);
		$this->pobject->update();

		ilUtil::sendInfo($this->lng->txt('paya_details_updated'));
		$this->editDetails();

		return true;
	}

	function showObjectSelector()
	{
		global $tree;

		include_once './payment/classes/class.ilPaymentObjectSelector.php';

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.paya_object_selector.html",'payment');
		$this->showButton('showObjects',$this->lng->txt('back'));


		ilUtil::sendInfo($this->lng->txt("paya_select_object_to_sell"));

		$exp = new ilPaymentObjectSelector($this->ctrl->getLinkTarget($this,'showObjectSelector'), strtolower(get_class($this)));
		$exp->setExpand($_GET["paya_link_expand"] ? $_GET["paya_link_expand"] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'showObjectSelector'));
		
		$exp->setOutput(0);

		$this->tpl->setVariable("EXPLORER",$exp->getOutput());

		return true;
	}

	function showSelectedObject()
	{
		if(!$_GET['sell_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));
			
			$this->showObjectSelector();
			return true;
		}
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.paya_selected_object.html','payment');
		$this->showButton('showObjectSelector',$this->lng->txt('back'));

		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_pays.gif',false));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('details'));

		$this->ctrl->setParameter($this,'sell_id',$_GET['sell_id']);
		$this->tpl->setVariable("SO_FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("TXT_DESCRIPTION",$this->lng->txt('description'));
		$this->tpl->setVariable("TXT_OWNER",$this->lng->txt('owner'));
		$this->tpl->setVariable("TXT_PATH",$this->lng->txt('path'));
		$this->tpl->setVariable("TXT_VENDOR",$this->lng->txt('paya_vendor'));
		$this->tpl->setVariable("BTN1_NAME",'showObjects');
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt('cancel'));
		$this->tpl->setVariable("BTN2_NAME",'addObject');
		$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt('next'));

		// fill values
		$this->tpl->setVariable("DETAILS",$this->lng->txt('details'));
		
		if($tmp_obj =& ilObjectFactory::getInstanceByRefId($_GET['sell_id']))
		{
			$this->tpl->setVariable("TITLE",$tmp_obj->getTitle());
			$this->tpl->setVariable("DESCRIPTION",$tmp_obj->getDescription());
			$this->tpl->setVariable("OWNER",$tmp_obj->getOwnerName());
			$this->tpl->setVariable("PATH",$this->__getHTMLPath((int) $_GET['sell_id']));
			$this->tpl->setVariable("VENDOR",$this->__showVendorSelector());
		}
		return true;
	}

	function addObject()
	{
		if(!$_GET['sell_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));
			
			$this->showObjectSelector();
			return true;
		}
		if(!(int) $_POST['vendor'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_vendor_selected'));
			
			$this->showSelectedObject();
			return true;
		}
		if(!ilPaymentObject::_isPurchasable($_GET['sell_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_object_not_purchasable'));

			$this->showObjectSelector();
			return true;
		}

		
		include_once './payment/classes/class.ilPaymentObject.php';

		$p_obj =& new ilPaymentObject($this->user_obj);
		
		$p_obj->setRefId((int) $_GET['sell_id']);
		$p_obj->setStatus($p_obj->STATUS_NOT_BUYABLE);
		$p_obj->setPayMethod($p_obj->PAY_METHOD_NOT_SPECIFIED);
		$p_obj->setVendorId((int) $_POST['vendor']);

		if($new_id = $p_obj->add())
		{
			ilUtil::sendInfo($this->lng->txt('paya_added_new_object'));
			
			$_GET['pobject_id'] = $new_id;
			$this->editDetails();

			return true;
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('paya_err_adding_object'));
			$this->showObjects();

			return false;
		}
	}
	
	// PRIVATE
	function __showVendorSelector($a_selected = 0)
	{
		include_once './payment/classes/class.ilPaymentVendors.php';
		
		$vendors = array();
		if(ilPaymentVendors::_isVendor($this->user_obj->getId()))
		{
			$vendors[] = $this->user_obj->getId();
		}
		if($vend = ilPaymentTrustees::_getVendorsForObjects($this->user_obj->getId()))
		{
			$vendors = array_merge($vendors,$vend);
		}
		foreach($vendors as $vendor)
		{
			$tmp_obj =& ilObjectFactory::getInstanceByObjId($vendor,false);

			$action[$vendor] = $tmp_obj->getFullname().' ['.$tmp_obj->getLogin().']';
		}
		
		return ilUtil::formSelect($a_selected,'vendor',$action,false,true);
	}

	function __showStatusSelector()
	{
		$action = array();
		$action[$this->pobject->STATUS_NOT_BUYABLE] = $this->lng->txt('paya_not_buyable');
		$action[$this->pobject->STATUS_BUYABLE] = $this->lng->txt('paya_buyable');
		$action[$this->pobject->STATUS_EXPIRES] = $this->lng->txt('paya_expires');

		return ilUtil::formSelect($this->pobject->getStatus(),'status',$action,false,true);
	}

	function __showPayMethodSelector()
	{
		include_once './payment/classes/class.ilPayMethods.php';

		$action = array();

		$action[$this->pobject->PAY_METHOD_NOT_SPECIFIED] = $this->lng->txt('paya_pay_method_not_specified');
		if(ilPayMethods::_enabled('pm_bill'))
		{
			$action[$this->pobject->PAY_METHOD_BILL] = $this->lng->txt('pays_bill');
		}
		if(ilPayMethods::_enabled('pm_bmf'))
		{
			$action[$this->pobject->PAY_METHOD_BMF] = $this->lng->txt('pays_bmf');
		}
		if(ilPayMethods::_enabled('pm_paypal'))
		{
			$action[$this->pobject->PAY_METHOD_PAYPAL] = $this->lng->txt('pays_paypal');
		}


		return ilUtil::formSelect($this->pobject->getPayMethod(),'pay_method',$action,false,true);
	}

	function __showPayMethodLink()
	{
		switch($this->pobject->getPayMethod())
		{
			case $this->pobject->PAY_METHOD_NOT_SPECIFIED:
				$this->showButton('editPayMethod',$this->lng->txt('paya_edit_pay_method'));
				break;

			case $this->pobject->PAY_METHOD_BILL:
			
				$this->ctrl->setParameterByClass('ilpaymentbilladmingui','pobject_id',(int) $_GET['pobject_id']);
				$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
				$this->tpl->setCurrentBlock("btn_cell");
				$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilpaymentbilladmingui'));
				$this->tpl->setVariable("BTN_TXT",$this->lng->txt('paya_edit_pay_method'));
				$this->tpl->parseCurrentBlock();
				break;

			case $this->pobject->PAY_METHOD_BMF:
				$this->showButton('editPayMethod',$this->lng->txt('paya_edit_pay_method'));
				break;

			case $this->pobject->PAY_METHOD_PAYPAL:
				$this->showButton('editPayMethod',$this->lng->txt('paya_edit_pay_method'));
				break;
		}
	}
	function __showObjectsTable($a_result_set)
	{
		$tbl =& $this->initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		/*
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setCurrentBlock("plain_buttons");
		$tpl->parseCurrentBlock();

		$tpl->setVariable("COLUMN_COUNTS",6);
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$tpl->setCurrentBlock("tbl_action_button");
		$tpl->setVariable("BTN_NAME","deleteTrustee");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();
		*/

		$tbl->setTitle($this->lng->txt("objects"),"icon_pays.gif",$this->lng->txt("objects"));
		$tbl->setHeaderNames(array($this->lng->txt("title"),
								   $this->lng->txt("status"),
								   $this->lng->txt("paya_pay_method"),
								   $this->lng->txt("paya_vendor"),
								   $this->lng->txt("paya_count_purchaser"),
								   ''));
		$header_params = $this->ctrl->getParameterArray($this,'');
		$tbl->setHeaderVars(array("title",
								  "status",
								  "pay_method",
								  "vendor",
								  "purchasers",
								  "options"),$header_params);
								  /*
							array("cmd" => "",
								  "cmdClass" => "ilpaymentobjectgui",
								  "cmdNode" => $_GET["cmdNode"]));
								  */
		$tbl->setColumnWidth(array("15%","15%","15%","20%","20%"));

#		$this->setTableGUIBasicData($tbl,$a_result_set);

		$offset = $_GET["offset"];
		$order = $_GET["sort_by"];
		$direction = $_GET["sort_order"] ? $_GET['sort_order'] : 'desc';

		$tbl->setOrderColumn($order,'order_date');
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setMaxCount(count($a_result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($a_result_set);

		$tbl->render();

		$this->tpl->setVariable("OBJECTS_TABLE",$tbl->tpl->get());

		return true;
	}


	function __getHTMLPath($a_ref_id)
	{
		global $tree;

		$path = $tree->getPathFull($a_ref_id);
		unset($path[0]);

		foreach($path as $data)
		{
			$html .= $data['title'].' > ';
		}
		return substr($html,0,-2);
	}

	function __initPaymentObject($a_pobject_id = 0)
	{
		include_once './payment/classes/class.ilPaymentObject.php';

		$this->pobject =& new ilPaymentObject($this->user_obj,$a_pobject_id);

		return true;
	}
}
?>