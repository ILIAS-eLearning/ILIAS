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
* Class ilPaymentStatisticGUI
*
* @author Stefan Meyer
* @version $Id$
*
* @package core
*
*/
include_once './payment/classes/class.ilPaymentObject.php';

class ilPaymentStatisticGUI extends ilShopBaseGUI
{
	private $pobject = null;

	public function ilPaymentStatisticGUI($user_obj)
	{
		parent::__construct();		
		
		$this->ctrl->saveParameter($this, 'baseClass');

		$this->user_obj = $user_obj;
		$this->pobject = new ilPaymentObject($this->user_obj);
	}
	
	protected function prepareOutput()
	{
		global $ilTabs;
		
		$this->setSection(6);
		
		parent::prepareOutput();

		$ilTabs->setTabActive('paya_header');
		//$ilTabs->setSubTabActive('paya_statistic');
		
		$ilTabs->setSubTabActive('bookings');
	}
	
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		
		switch ($this->ctrl->getNextClass($this))
		{
			default:
				if(!$cmd = $this->ctrl->getCmd())
				{
					$cmd = 'showStatistics';
				}
				$this->prepareOutput();
				$this->$cmd();
				break;
		}
	}

	function resetFilter()
	{
		unset($_SESSION["pay_statistics"]);
		unset($_POST["transaction_type"]);
		unset($_POST["transaction_value"]);
		unset($_POST["from"]["day"]);
		unset($_POST["from"]["month"]);
		unset($_POST["from"]["year"]);
		unset($_POST["til"]["day"]);
		unset($_POST["til"]["month"]);
		unset($_POST["til"]["year"]);
		unset($_POST["payed"]);
		unset($_POST["access"]);
		unset($_POST["customer"]);
		unset($_POST["pay_method"]);
		unset($_POST["updateView"]);
		$this->showStatistics();
	}

	function showStatistics()
	{
		$this->showButton('showObjectSelector',$this->lng->txt('paya_add_customer'));

		if ($_POST["updateView"] == 1)
		{
			$_SESSION["pay_statistics"]["updateView"] = true;
			$_SESSION["pay_statistics"]["transaction_type"] = $_POST["transaction_type"];
			$_SESSION["pay_statistics"]["transaction_value"] = $_POST["transaction_value"];
			$_SESSION["pay_statistics"]["from"]["day"] = $_POST["from"]["day"];
			$_SESSION["pay_statistics"]["from"]["month"] = $_POST["from"]["month"];
			$_SESSION["pay_statistics"]["from"]["year"] = $_POST["from"]["year"];
			$_SESSION["pay_statistics"]["til"]["day"] = $_POST["til"]["day"];
			$_SESSION["pay_statistics"]["til"]["month"] = $_POST["til"]["month"];
			$_SESSION["pay_statistics"]["til"]["year"] = $_POST["til"]["year"];
			$_SESSION["pay_statistics"]["payed"] = $_POST["payed"];
			$_SESSION["pay_statistics"]["access"] = $_POST["access"];
			$_SESSION["pay_statistics"]["customer"] = $_POST["customer"];
			$_SESSION["pay_statistics"]["pay_method"] = $_POST["pay_method"];
		}

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_statistic.html','payment');
		
		$this->tpl->setVariable("TXT_FILTER",$this->lng->txt('pay_filter'));
		$this->tpl->setVariable("FORM_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_TRANSACTION",$this->lng->txt('paya_transaction'));
		$this->tpl->setVariable("TXT_STARTING",$this->lng->txt('pay_starting'));
		$this->tpl->setVariable("TXT_ENDING",$this->lng->txt('pay_ending'));
		$this->tpl->setVariable("TXT_PAYED",$this->lng->txt('paya_payed'));
		$this->tpl->setVariable("TXT_ALL",$this->lng->txt('pay_all'));
		$this->tpl->setVariable("TXT_YES",$this->lng->txt('yes'));
		$this->tpl->setVariable("TXT_NO",$this->lng->txt('no'));
		$this->tpl->setVariable("TXT_BILL",$this->lng->txt('pays_bill'));
		$this->tpl->setVariable("TXT_BMF",$this->lng->txt('pays_bmf'));
		$this->tpl->setVariable("TXT_PAYPAL",$this->lng->txt('pays_paypal'));
		$this->tpl->setVariable("TXT_CUSTOMER",$this->lng->txt('paya_customer'));
		$this->tpl->setVariable("TXT_ACCESS",$this->lng->txt('paya_access'));
		$this->tpl->setVariable("TXT_PAYMENT",$this->lng->txt('payment_system'));
		$this->tpl->setVariable("TXT_ORDER_DATE_FROM",$this->lng->txt('pay_order_date_from'));
		$this->tpl->setVariable("TXT_ORDER_DATE_TIL",$this->lng->txt('pay_order_date_til'));
		$this->tpl->setVariable("TXT_UPDATE_VIEW",$this->lng->txt('pay_update_view'));
		$this->tpl->setVariable("TXT_RESET_FILTER",$this->lng->txt('pay_reset_filter'));

		$this->tpl->setVariable("TRANSACTION_TYPE_" . $_SESSION["pay_statistics"]["transaction_type"], " selected");
		$this->tpl->setVariable("TRANSACTION_VALUE", ilUtil::prepareFormOutput($_SESSION["pay_statistics"]["transaction_value"], true));
		$this->tpl->setVariable("PAYED_" . $_SESSION["pay_statistics"]["payed"], " selected");
		$this->tpl->setVariable("ACCESS_" . $_SESSION["pay_statistics"]["access"], " selected");
		$this->tpl->setVariable("PAYMENT_" . $_SESSION["pay_statistics"]["pay_method"], " selected");
		$this->tpl->setVariable("CUSTOMER", ilUtil::prepareFormOutput($_SESSION["pay_statistics"]["customer"], true));
		for ($i = 1; $i <= 31; $i++)
		{
			$this->tpl->setCurrentBlock("loop_from_day");
			$this->tpl->setVariable("LOOP_FROM_DAY", $i < 10 ? "0" . $i : $i);
			if ($_SESSION["pay_statistics"]["from"]["day"] == $i)
			{
				$this->tpl->setVariable("LOOP_FROM_DAY_SELECTED", " selected");
			}
			$this->tpl->parseCurrentBlock("loop_from_day");
			$this->tpl->setCurrentBlock("loop_til_day");
			$this->tpl->setVariable("LOOP_TIL_DAY", $i < 10 ? "0" . $i : $i);
			if ($_SESSION["pay_statistics"]["til"]["day"] == $i)
			{
				$this->tpl->setVariable("LOOP_TIL_DAY_SELECTED", " selected");
			}
			$this->tpl->parseCurrentBlock("loop_til_day");
		}
		for ($i = 1; $i <= 12; $i++)
		{
			$this->tpl->setCurrentBlock("loop_from_month");
			$this->tpl->setVariable("LOOP_FROM_MONTH", $i < 10 ? "0" . $i : $i);
			if ($_SESSION["pay_statistics"]["from"]["month"] == $i)
			{
				$this->tpl->setVariable("LOOP_FROM_MONTH_SELECTED", " selected");
			}
			$this->tpl->parseCurrentBlock("loop_from_month");
			$this->tpl->setCurrentBlock("loop_til_month");
			$this->tpl->setVariable("LOOP_TIL_MONTH", $i < 10 ? "0" . $i : $i);
			if ($_SESSION["pay_statistics"]["til"]["month"] == $i)
			{
				$this->tpl->setVariable("LOOP_TIL_MONTH_SELECTED", " selected");
			}
			$this->tpl->parseCurrentBlock("loop_til_month");
		}
		for ($i = 2004; $i <= date("Y"); $i++)
		{
			$this->tpl->setCurrentBlock("loop_from_year");
			$this->tpl->setVariable("LOOP_FROM_YEAR", $i);
			if ($_SESSION["pay_statistics"]["from"]["year"] == $i)
			{
				$this->tpl->setVariable("LOOP_FROM_YEAR_SELECTED", " selected");
			}
			$this->tpl->parseCurrentBlock("loop_from_year");
			$this->tpl->setCurrentBlock("loop_til_year");
			$this->tpl->setVariable("LOOP_TIL_YEAR", $i);
			if ($_SESSION["pay_statistics"]["til"]["year"] == $i)
			{
				$this->tpl->setVariable("LOOP_TIL_YEAR_SELECTED", " selected");
			}
			$this->tpl->parseCurrentBlock("loop_til_year");
		}

		if(!$_SESSION['pay_statistics']['updateView'])
		{
			$this->tpl->setVariable('FILTER_MESSAGE', $this->lng->txt('statistics_filter_advice'));
			return true;
		}

		$this->__initBookingObject();

		if(!count($bookings = $this->booking_obj->getBookings()))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_bookings'));

			return true;
		}
#		$this->__showButton('excelExport',$this->lng->txt('excel_export'));

		$img_change = "<img src=\"".ilUtil::getImagePath("edit.gif")."\" alt=\"".
			$this->lng->txt("edit")."\" title=\"".$this->lng->txt("edit").
			"\" border=\"0\" vspace=\"0\"/>";
			
		include_once 'Services/User/classes/class.ilObjUser.php';
		$object_title_cache = array();
		$user_title_cache = array();
		
		$counter = 0;
		foreach($bookings as $booking)
		{
			if(array_key_exists($booking['ref_id'], $object_title_cache))
			{
				$tmp_obj = $object_title_cache[$booking['ref_id']];
			}
			else
			{
				$tmp_obj = ilObject::_lookupTitle(ilObject::_lookupObjId($booking['ref_id']));				
				$object_title_cache[$booking['ref_id']] = $tmp_obj;
			}
			if(array_key_exists($booking['b_vendor_id'], $user_title_cache))
			{
				$tmp_vendor = $user_title_cache[$booking['b_vendor_id']];
			}
			else
			{
				$tmp_vendor = ilObjUser::_lookupLogin($booking['b_vendor_id']);
				$user_title_cache[$booking['b_vendor_id']] = $tmp_vendor;
			}
			if(array_key_exists($booking['customer_id'], $user_title_cache))
			{
				$tmp_purchaser = $user_title_cache[$booking['customer_id']];
			}
			else
			{
				$tmp_purchaser = ilObjUser::_lookupLogin($booking['customer_id']);
				$user_title_cache[$booking['customer_id']] = $tmp_purchaser;
			}

			$transaction = $booking['transaction_extern'];
			switch ($booking['b_pay_method'])
			{
				case $this->pobject->PAY_METHOD_BILL :
					$transaction .= " (" . $this->lng->txt("pays_bill") . ")";
					break;
				case $this->pobject->PAY_METHOD_BMF :
					$transaction .= " (" . $this->lng->txt("pays_bmf") . ")";
					break;
				case $this->pobject->PAY_METHOD_PAYPAL :
					$transaction .= " (" . $this->lng->txt("pays_paypal") . ")";
					break;
			}
			$f_result[$counter][] = $transaction;
			$f_result[$counter][] = ($tmp_obj != '' ?  $tmp_obj : $this->lng->txt('object_deleted'));
			$f_result[$counter][] = ($tmp_vendor != '' ?  '['.$tmp_vendor.']' : $this->lng->txt('user_deleted'));
			$f_result[$counter][] = ($tmp_purchaser != '' ?  '['.$tmp_purchaser.']' : $this->lng->txt('user_deleted'));
			$f_result[$counter][] = date('Y-m-d H:i:s', $booking['order_date']);
			$f_result[$counter][] = $booking['duration'];
			$f_result[$counter][] = $booking['price'];
			$f_result[$counter][] = $booking['discount'];

			$payed_access = $booking['payed'] ? 
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$payed_access .= '/';
			$payed_access .= $booking['access'] ?
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$f_result[$counter][] = $payed_access;

			$this->ctrl->setParameter($this,"booking_id",$booking['booking_id']);
#			$link_change = "<a href=\"".$this->ctrl->getLinkTarget($this,"editStatistic")."\"> ".
#				$img_change."</a>";
			$link_change = "<div class=\"il_ContainerItemCommands\"><a class=\"il_ContainerItemCommand\" href=\"".$this->ctrl->getLinkTarget($this,"editStatistic")."\">".$this->lng->txt("edit")."</a></div>";

			$f_result[$counter][] = $link_change;

			unset($tmp_obj);
			unset($tmp_vendor);
			unset($tmp_purchaser);

			++$counter;
		}
		return $this->__showStatisticTable($f_result);
	}

	function excelExport()
	{
		include_once './payment/classes/class.ilPaymentExcelWriterAdapter.php';

		$pewa =& new ilPaymentExcelWriterAdapter('payment_vendors.xls');

		// add/fill worksheet
		$this->addStatisticWorksheet($pewa);

		// HEADER SENT
		
		$workbook =& $pewa->getWorkbook();
		$workbook->close();
	}

	function addStatisticWorksheet(&$pewa)
	{
		include_once './payment/classes/class.ilPaymentVendors.php';

		$this->__initBookingObject();

		if(!count($bookings = $this->booking_obj->getBookings()))
		{
			return false;
		}

		$workbook =& $pewa->getWorkbook();
		//$worksheet =& $workbook->addWorksheet($this->lng->txt('paya_statistic'));
		$worksheet =& $workbook->addWorksheet($this->lng->txt('bookings'));
		
		$worksheet->mergeCells(0,0,0,8);
		$worksheet->setColumn(0,0,16);
		$worksheet->setColumn(0,1,32);
		$worksheet->setColumn(0,2,32);
		$worksheet->setColumn(0,3,16);
		$worksheet->setColumn(0,4,16);
		$worksheet->setColumn(0,5,16);
		$worksheet->setColumn(0,6,24);
		$worksheet->setColumn(0,7,8);
		$worksheet->setColumn(0,8,12);
		$worksheet->setColumn(0,9,16);

		//$title = $this->lng->txt('paya_statistic');
		$title = $this->lng->txt('bookings');
		$title .= ' '.$this->lng->txt('as_of');
		$title .= strftime('%Y-%m-%d %R',time());

		$worksheet->writeString(0,0,$title,$pewa->getFormatTitle());

		$worksheet->writeString(1,0,$this->lng->txt('payment_system'),$pewa->getFormatHeader());
		$worksheet->writeString(1,1,$this->lng->txt('paya_transaction'),$pewa->getFormatHeader());
		$worksheet->writeString(1,2,$this->lng->txt('title'),$pewa->getFormatHeader());
		$worksheet->writeString(1,3,$this->lng->txt('paya_vendor'),$pewa->getFormatHeader());
		$worksheet->writeString(1,4,$this->lng->txt('pays_cost_center'),$pewa->getFormatHeader());
		$worksheet->writeString(1,5,$this->lng->txt('paya_customer'),$pewa->getFormatHeader());
		$worksheet->writeString(1,6,$this->lng->txt('paya_order_date'),$pewa->getFormatHeader());
		$worksheet->writeString(1,7,$this->lng->txt('duration'),$pewa->getFormatHeader());
		$worksheet->writeString(1,8,$this->lng->txt('price_a'),$pewa->getFormatHeader());
		$worksheet->writeString(1,9,$this->lng->txt('paya_payed_access'),$pewa->getFormatHeader());

		include_once 'Services/User/classes/class.ilObjUser.php';
		$object_title_cache = array();
		$user_title_cache = array();

		$counter = 2;
		foreach($bookings as $booking)
		{
			if(array_key_exists($booking['ref_id'], $object_title_cache))
			{
				$tmp_obj = $object_title_cache[$booking['ref_id']];
			}
			else
			{
				$tmp_obj = ilObject::_lookupTitle(ilObject::_lookupObjId($booking['ref_id']));				
				$object_title_cache[$booking['ref_id']] = $tmp_obj;
			}
			if(array_key_exists($booking['b_vendor_id'], $user_title_cache))
			{
				$tmp_vendor = $user_title_cache[$booking['b_vendor_id']];
			}
			else
			{
				$tmp_vendor = ilObjUser::_lookupLogin($booking['b_vendor_id']);
				$user_title_cache[$booking['b_vendor_id']] = $tmp_vendor;
			}
			if(array_key_exists($booking['customer_id'], $user_title_cache))
			{
				$tmp_purchaser = $user_title_cache[$booking['customer_id']];
			}
			else
			{
				$tmp_purchaser = ilObjUser::_lookupLogin($booking['customer_id']);
				$user_title_cache[$booking['customer_id']] = $tmp_purchaser;
			}
			
			switch ($booking['b_pay_method'])
			{
				case $this->pobject->PAY_METHOD_BILL :
					$pay_method = $this->lng->txt("pays_bill");
					break;
				case $this->pobject->PAY_METHOD_BMF :
					$pay_method = $this->lng->txt("pays_bmf");
					break;
				case $this->pobject->PAY_METHOD_PAYPAL :
					$pay_method = $this->lng->txt("pays_paypal");
					break;
			}
			$worksheet->writeString($counter,0,$pay_method);
			$worksheet->writeString($counter,1,$booking['transaction_extern']);
			$worksheet->writeString($counter,2,($tmp_obj != '' ?  $tmp_obj : $this->lng->txt('object_deleted')));
			$worksheet->writeString($counter,3,($tmp_vendor != '' ? $tmp_vendor : $this->lng->txt('user_deleted')));
			$worksheet->writeString($counter,4,ilPaymentVendors::_getCostCenter($booking['b_vendor_id']));
			$worksheet->writeString($counter,5,($tmp_purchaser != '' ? $tmp_purchaser : $this->lng->txt('user_deleted')));
			$worksheet->writeString($counter,6,strftime('%Y-%m-%d %R',$booking['order_date']));
			/*
			$worksheet->write($counter,5,ilUtil::excelTime(date('Y',$booking['order_date']),
														   date('m',$booking['order_date']),
														   date('d',$booking['order_date']),
														   date('H',$booking['order_date']),
														   date('i',$booking['order_date']),
														   date('s',$booking['order_date'])),$pewa->getFormatDate());
			*/
			$worksheet->writeString($counter,7,$booking['duration']);
			$worksheet->writeString($counter,8,$booking['price']);
			
			$payed_access = $booking['payed'] ? 
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$payed_access .= '/';
			$payed_access .= $booking['access'] ?
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$worksheet->writeString($counter,9,$payed_access);

			unset($tmp_obj);
			unset($tmp_vendor);
			unset($tmp_purchaser);

			++$counter;
		}
	}		

	function editStatistic($a_show_confirm_delete = false)
	{
		if(!isset($_GET['booking_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();

			return true;
		}

		$this->showButton('showStatistics',$this->lng->txt('back'));

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_edit_statistic.html','payment');
		$this->ctrl->setParameter($this,'booking_id',(int) $_GET['booking_id']);

		// confirm delete
		if($a_show_confirm_delete)
		{
			$this->tpl->setCurrentBlock("confirm_delete");
			$this->tpl->setVariable("CONFIRM_FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
			$this->tpl->setVariable("CONFIRM_CMD",'performDelete');
			$this->tpl->setVariable("TXT_CONFIRM",$this->lng->txt('confirm'));
			$this->tpl->parseCurrentBlock();
		}
			

		$this->__initBookingObject();
		$bookings = $this->booking_obj->getBookings();
		$booking = $bookings[(int) $_GET['booking_id']];

		// get customer_obj
		$tmp_user = ilObjectFactory::getInstanceByObjId($booking['customer_id'], false);



		$this->tpl->setVariable("STAT_FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_usr.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		if(is_object($tmp_user))
		{
			$this->tpl->setVariable('TITLE', $tmp_user->getFullname().' ['.$tmp_user->getLogin().']');
		}
		else
		{
			$this->tpl->setVariable('TITLE', $this->lng->txt('user_deleted'));
		}

		// TXT
		$pObj = new ilPaymentObject($this->user_obj, $booking["pobject_id"]);
		$tmp_obj = ilObject::_lookupTitle(ilObject::_lookupObjId($pObj->getRefId()));				

		$this->tpl->setVariable("TXT_OBJECT",$this->lng->txt('title'));
		$this->tpl->setVariable("OBJECT", ($tmp_obj != '' ?  $tmp_obj : $this->lng->txt('object_deleted')));

		$this->tpl->setVariable("TXT_TRANSACTION",$this->lng->txt('paya_transaction'));
		$this->tpl->setVariable("TXT_VENDOR",$this->lng->txt('paya_vendor'));
		$this->tpl->setVariable("TXT_PAY_METHOD",$this->lng->txt('paya_pay_method'));
		$this->tpl->setVariable("TXT_ORDER_DATE",$this->lng->txt('paya_order_date'));
		$this->tpl->setVariable("TXT_DURATION",$this->lng->txt('duration'));
		$this->tpl->setVariable("TXT_PRICE",$this->lng->txt('price_a'));
		$this->tpl->setVariable("TXT_PAYED",$this->lng->txt('paya_payed'));
		$this->tpl->setVariable("TXT_ACCESS",$this->lng->txt('paya_access'));

		$this->tpl->setVariable("TRANSACTION",$booking['transaction']);

		$tmp_vendor = ilObjectFactory::getInstanceByObjId($booking['b_vendor_id'], false);
		if(is_object($tmp_vendor))
		{
			$this->tpl->setVariable('VENDOR', $tmp_vendor->getFullname().' ['.$tmp_vendor->getLogin().']');
		}
		else
		{
			$this->tpl->setVariable('VENDOR', $this->lng->txt('user_deleted'));
		}

		switch($booking['b_pay_method'])
		{
			case $this->pobject->PAY_METHOD_BILL:
				$this->tpl->setVariable("PAY_METHOD",$this->lng->txt('pays_bill'));
				break;

			case $this->pobject->PAY_METHOD_BMF:
				$this->tpl->setVariable("PAY_METHOD",$this->lng->txt('pays_bmf'));
				break;

			case $this->pobject->PAY_METHOD_PAYPAL:
				$this->tpl->setVariable("PAY_METHOD",$this->lng->txt('pays_paypal'));
				break;

			default:
				$this->tpl->setVariable("PAY_METHOD",$this->lng->txt('paya_pay_method_not_specified'));
				break;
		}
		$this->tpl->setVariable("ORDER_DATE",date('Y m d H:i:s',$booking['order_date']));
		$this->tpl->setVariable("DURATION",$booking['duration'].' '.$this->lng->txt('paya_months'));
		$this->tpl->setVariable("PRICE",$booking['price']);
		
		$yes_no = array(0 => $this->lng->txt('no'),1 => $this->lng->txt('yes'));

		$this->tpl->setVariable("PAYED",ilUtil::formSelect((int) $booking['payed'],'payed',$yes_no,false,true));
		$this->tpl->setVariable("ACCESS",ilUtil::formSelect((int) $booking['access'],'access',$yes_no,false,true));

		// buttons
		$this->tpl->setVariable("INPUT_CMD",'updateStatistic');
		$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('save'));

		$this->tpl->setVariable("DELETE_CMD",'deleteStatistic');
		$this->tpl->setVariable("DELETE_VALUE",$this->lng->txt('delete'));
	}

	function updateStatistic()
	{
		if(!isset($_GET['booking_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();

			return true;
		}
		$this->__initBookingObject();

		$this->booking_obj->setBookingId((int) $_GET['booking_id']);
		$this->booking_obj->setAccess((int) $_POST['access']);
		$this->booking_obj->setPayed((int) $_POST['payed']);
		
		if($this->booking_obj->update())
		{
			ilUtil::sendInfo($this->lng->txt('paya_updated_booking'));

			$this->showStatistics();
			return true;
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('paya_error_update_booking'));
			$this->showStatistics();
			
			return true;
		}
	}

	function deleteStatistic()
	{
		if(!isset($_GET['booking_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();

			return true;
		}
		ilUtil::sendInfo($this->lng->txt('paya_sure_delete_stat'));

		$this->editStatistic(true);

		return true;
	}

	function performDelete()
	{
		if(!isset($_GET['booking_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();

			return true;
		}

		$this->__initBookingObject();
		$this->booking_obj->setBookingId((int) $_GET['booking_id']);
		if(!$this->booking_obj->delete())
		{
			die('Error deleting booking');
		}
		ilUtil::sendInfo($this->lng->txt('pay_deleted_booking'));

		$this->showStatistics();

		return true;
	}

	function showObjectSelector()
	{
		global $tree;

		include_once './payment/classes/class.ilPaymentObjectSelector.php';

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.paya_object_selector.html",'payment');
		$this->showButton('showStatistics',$this->lng->txt('back'));

		ilUtil::sendInfo($this->lng->txt("paya_select_object_to_sell"));

		$exp = new ilPaymentObjectSelector($this->ctrl->getLinkTarget($this,'showObjectSelector'), strtolower(get_class($this)));
		$exp->setExpand($_GET["paya_link_expand"] ? $_GET["paya_link_expand"] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'showObjectSelector'));
		
		$exp->setOutput(0);

		$this->tpl->setVariable("EXPLORER",$exp->getOutput());

		return true;
	}

	function searchUser()
	{
		if(!isset($_GET['sell_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showObjectSelector();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.paya_user_search.html",'payment');
		$this->showButton('showObjectSelector',$this->lng->txt('back'));

		$this->lng->loadLanguageModule('search');

		$this->ctrl->setParameter($this, "sell_id", $_GET["sell_id"]);
		$this->tpl->setVariable("F_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SEARCH_ASSIGN_USR",$this->lng->txt("search_user"));
		$this->tpl->setVariable("SEARCH_SEARCH_TERM",$this->lng->txt("search_search_term"));
		$this->tpl->setVariable("SEARCH_VALUE",$_SESSION["pays_search_str_user_sp"] ? $_SESSION["pays_search_str_user_sp"] : "");
		$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("search"));
		$this->tpl->setVariable("SEARCH","performSearch");
		$this->tpl->setVariable("CANCEL","showStatistics");

		return true;
	}

	function performSearch()
	{
		// SAVE it to allow sort in tables
		$_SESSION["pays_search_str_user_sp"] = $_POST["search_str"] = $_POST["search_str"] ? $_POST["search_str"] : $_SESSION["pays_search_str_user_sp"];


		if(!trim($_POST["search_str"]))
		{
			ilUtil::sendInfo($this->lng->txt("search_no_search_term"));
			$this->showStatistics();

			return false;
		}
		if(!count($result = $this->__search(ilUtil::stripSlashes($_POST["search_str"]))))
		{
			ilUtil::sendInfo($this->lng->txt("search_no_match"));
			$this->searchUser();

			return false;
		}

		if(!isset($_GET['sell_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showObjectSelector();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.paya_usr_selection.html",'payment');
		$this->ctrl->setParameter($this, "sell_id", $_GET["sell_id"]);
		$this->showButton("searchUser",$this->lng->txt("back"));
		
		$counter = 0;
		$f_result = array();
		foreach($result as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user["id"],false))
			{
				continue;
			}
			$f_result[$counter][] = ilUtil::formRadiobutton(0,"user_id",$user["id"]);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = $tmp_obj->getLastname();
			
			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result);
	}

	function addCustomer()
	{
		if ($_POST["sell_id"] != "") $_GET["sell_id"] = $_POST["sell_id"];
		if ($_GET["user_id"] != "") $_POST["user_id"] = $_GET["user_id"];

		if(!isset($_GET['sell_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showObjectSelector();

			return true;
		}

		if(!isset($_POST['user_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_user_id_given'));
			$this->searchUser();

			return true;
		}

		$this->ctrl->setParameter($this, "sell_id", $_GET["sell_id"]);
		$this->showButton('searchUser',$this->lng->txt('back'));

		$this->ctrl->setParameter($this, "user_id", $_POST["user_id"]);

		$pObjectId = ilPaymentObject::_lookupPobjectId($_GET["sell_id"]);
		$obj =& new ilPaymentObject($this->user_obj, $pObjectId);

		// get obj
		$tmp_obj =& ilObjectFactory::getInstanceByRefId($_GET["sell_id"]);
		// get customer_obj
		$tmp_user =& ilObjectFactory::getInstanceByObjId($_POST["user_id"]);
		// get vendor_obj
		$tmp_vendor =& ilObjectFactory::getInstanceByObjId($obj->getVendorId());

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_add_customer.html','payment');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_usr.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("TITLE",$tmp_user->getFullname().' ['.$tmp_user->getLogin().']');

		// TXT
		$this->tpl->setVariable("TXT_TRANSACTION",$this->lng->txt('paya_transaction'));
		$this->tpl->setVariable("TRANSACTION",ilUtil::prepareFormOutput($_POST["transaction"], true));

		$this->tpl->setVariable("TXT_OBJECT",$this->lng->txt('title'));
		$this->tpl->setVariable("OBJECT",$tmp_obj->getTitle());

		$this->tpl->setVariable("TXT_VENDOR",$this->lng->txt('paya_vendor'));
		$this->tpl->setVariable("VENDOR",$tmp_vendor->getFullname().' ['.$tmp_vendor->getLogin().']');

		$this->tpl->setVariable("TXT_PAY_METHOD",$this->lng->txt('paya_pay_method'));
		$this->tpl->setVariable("TXT_PAY_METHOD_BILL",$this->lng->txt('pays_bill'));
		$this->tpl->setVariable("TXT_PAY_METHOD_BMF",$this->lng->txt('pays_bmf'));
		$this->tpl->setVariable("TXT_PAY_METHOD_PAYPAL",$this->lng->txt('pays_paypal'));
		$this->tpl->setVariable("PAY_METHOD_".$_POST["pay_method"], " selected");

		$this->tpl->setVariable("TXT_ORDER_DATE",$this->lng->txt('paya_order_date'));
		$this->tpl->setVariable('ORDER_DATE',ilDatePresentation::formatDate(new ilDateTime(time(),IL_CAL_UNIX)));

		$this->tpl->setVariable("TXT_DURATION",$this->lng->txt('duration'));
		include_once './payment/classes/class.ilPaymentPrices.php';
		$prices_obj =& new ilPaymentPrices($pObjectId);
		if (is_array($prices = $prices_obj->getPrices()))
		{
			foreach($prices as $price)
			{
				$this->tpl->setCurrentBlock("duration_loop");
				if ($_POST["duration"] == $price["price_id"]) $this->tpl->setVariable("DURATION_LOOP_SELECTED", " selected");
				$this->tpl->setVariable("DURATION_LOOP_ID", $price["price_id"]);
				$this->tpl->setVariable("DURATION_LOOP_NAME", $price["duration"]." ".$this->lng->txt("paya_months").", ".ilPaymentPrices::_getPriceString($price["price_id"]));
				$this->tpl->parseCurrentBlock();
			}
		}

		$this->tpl->setVariable("TXT_PAYED",$this->lng->txt('paya_payed'));
		if ($_POST["payed"] == 1) $this->tpl->setVariable("PAYED_1", " selected");
		$this->tpl->setVariable("TXT_ACCESS",$this->lng->txt('paya_access'));
		if ($_POST["access"] == 1) $this->tpl->setVariable("ACCESS_1", " selected");

		$this->tpl->setVariable("TXT_NO",$this->lng->txt('no'));
		$this->tpl->setVariable("TXT_YES",$this->lng->txt('yes'));
		$this->tpl->setVariable("TXT_SAVE",$this->lng->txt('save'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("STATISTICS","showStatistics");

	}

	function saveCustomer()
	{
		global $ilias;

		if(!isset($_GET['sell_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_error_no_object_id_given'));
			$this->showObjectSelector();

			return true;
		}

		if(!isset($_GET['user_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_error_no_user_id_given'));
			$this->searchUser();

			return true;
		}

		if ($_POST["pay_method"] == "" ||
			$_POST["duration"] == "")
		{
			ilUtil::sendInfo($this->lng->txt('paya_error_mandatory_fields'));
			$this->addCustomer();

			return true;
		}

		$pObjectId = ilPaymentObject::_lookupPobjectId($_GET["sell_id"]);
		$obj =& new ilPaymentObject($this->user_obj, $pObjectId);

		$this->__initBookingObject();

		$inst_id_time = $ilias->getSetting('inst_id').'_'.$this->user_obj->getId().'_'.substr((string) time(),-3);
		$transaction = $inst_id_time.substr(md5(uniqid(rand(), true)), 0, 4);
		$this->booking_obj->setTransaction($transaction);
		$this->booking_obj->setTransactionExtern($_POST["transaction"]);
		$this->booking_obj->setPobjectId($pObjectId);
		$this->booking_obj->setCustomerId($_GET["user_id"]);
		$this->booking_obj->setVendorId($obj->getVendorId());
		$this->booking_obj->setPayMethod((int) $_POST["pay_method"]);
		$this->booking_obj->setOrderDate(time());
		$price = ilPaymentPrices::_getPrice($_POST["duration"]);
		$this->booking_obj->setDuration($price["duration"]);
		$this->booking_obj->setPrice(ilPaymentPrices::_getPriceString($_POST["duration"]));
		$this->booking_obj->setAccess((int) $_POST['access']);
		$this->booking_obj->setPayed((int) $_POST['payed']);
		$this->booking_obj->setVoucher('');

		if($this->booking_obj->add())
		{
			ilUtil::sendInfo($this->lng->txt('paya_customer_added_successfully'));
			$this->showStatistics();
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('paya_error_adding_customer'));
			$this->addCustomer();
		}

		return true;
	}

	// PRIVATE
	function __showStatisticTable($a_result_set)
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

		//$tbl->setTitle($this->lng->txt("paya_statistic"),"icon_pays.gif",$this->lng->txt("paya_statistic"));
		$tbl->setTitle($this->lng->txt("bookings"),"icon_pays.gif",$this->lng->txt("bookings"));
		$tbl->setHeaderNames(array($this->lng->txt("paya_transaction"),
								   $this->lng->txt("title"),
								   $this->lng->txt("paya_vendor"),
								   $this->lng->txt("paya_customer"),
								   $this->lng->txt("paya_order_date"),
								   $this->lng->txt("duration"),
								   $this->lng->txt("price_a"),
								   $this->lng->txt("paya_coupons_coupons"),
								   $this->lng->txt("paya_payed_access"),
								   ''));
		$header_params = $this->ctrl->getParameterArray($this,'');
		$tbl->setHeaderVars(array("transaction",
								  "title",
								  "vendor",
								  "customer",
								  "order_date",
								  "duration",
								  "price",
								  "discount",
								  "payed_access",
								  "options"),$header_params);
								  /*
							array("cmd" => "",
								  "cmdClass" => "ilpaymentstatisticgui",
								  "baseClass" => "ilPersonalDesktopGUI",
								  "cmdNode" => $_GET["cmdNode"]));
								  */

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

		$tpl->setVariable("COLUMN_COUNTS",10);
		$tpl->setCurrentBlock("plain_buttons");
		$tpl->setVariable("PBTN_NAME","excelExport");
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("excel_export"));
		$tpl->parseCurrentBlock();

		$tbl->render();

		$this->tpl->setVariable("STATISTIC_TABLE",$tbl->tpl->get());

		return true;
	}

	function __initBookingObject()
	{
		include_once './payment/classes/class.ilPaymentBookings.php';

		$this->booking_obj =& new ilPaymentBookings($this->user_obj->getId());
	}

	function __showButton($a_cmd,$a_text,$a_target = '')
	{
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,$a_cmd));
		$this->tpl->setVariable("BTN_TXT",$a_text);
		if($a_target)
		{
			$this->tpl->setVariable("BTN_TARGET",$a_target);
		}

		$this->tpl->parseCurrentBlock();
				vd($a_cmd.$a_text.$a_target);
	}		

	function __search($a_search_string)
	{
		include_once("./classes/class.ilSearch.php");

		$this->lng->loadLanguageModule("content");

		$search =& new ilSearch($this->user_obj->getId());
		$search->setPerformUpdate(false);
		$search->setSearchString(ilUtil::stripSlashes($a_search_string));
		$search->setCombination("and");
		$search->setSearchFor(array(0 => 'usr'));
		$search->setSearchType('new');

		if($search->validate($message))
		{
			$search->performSearch();
		}
		else
		{
			ilUtil::sendInfo($message,true);
			$this->ctrl->redirect($this,"searchUser");
		}
		return $search->getResultByType('usr');
	}
	function __showSearchUserTable($a_result_set)
	{
		$tbl =& $this->initTableGUI();
		$tpl =& $tbl->getTemplateObject();


		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$this->ctrl->setParameter($this, "sell_id", $_GET["sell_id"]);
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","addCustomer");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("add"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","showStatistics");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("cancel"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("users"),"icon_usr.gif",$this->lng->txt("crs_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("login"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname")));
		$this->ctrl->setParameter($this, "cmd", "addCustomer");
		$header_params = $this->ctrl->getParameterArray($this,'');
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname"), $header_params);
								  /*
							array("cmd" => 'performSearch',
								  "cmdClass" => "ilpaymentstatisticgui",
								  "cmdNode" => $_GET["cmdNode"]));
								  */

		$tbl->setColumnWidth(array("3%","32%","32%","32%"));

		$this->setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();
		
		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}
}
?>