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
* Class ilObjAuthSettingsGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @author Jens Conze <jc@databay.de> 
* @version $Id$
* 
* @extends ilObjectGUI
* @package ilias-core
*
*/

require_once "./classes/class.ilObjectGUI.php";

class ilObjPaymentSettingsGUI extends ilObjectGUI
{
	var $user_obj = null;
	var $pobject = null;

	/**
	* Constructor
	* @access public
	*/
	function ilObjPaymentSettingsGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		global $ilias;

		$this->user_obj =& $ilias->account;

		include_once "./payment/classes/class.ilPaymentObject.php";

		$this->pobject =& new ilPaymentObject($this->user_obj);


		$this->type = "pays";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);

		$this->lng->loadLanguageModule('payment');
	}

	function gatewayObject()
	{
		switch($_POST["action"])
		{
			case "deleteVendorsObject":
				$this->deleteVendors();
				break;

			case "editVendorObject":
				$this->editVendor();
				break;

			case "performEditVendorObject":
				$this->performEditVendorObject();
				break;

			default:
				$this->vendorsObject();
				break;
		}
		return true;
	}

	function resetFilterObject()
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

		sendInfo($this->lng->txt('paya_filter_reseted'));

		return $this->statisticObject();
	}

	function statisticObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		if ($_POST["updateView"] == 1)
		{
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
			$_SESSION["pay_statistics"]["vendor"] = $_POST["vendor"];
		}

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_adm_statistic.html',true);
		
		$this->tpl->setVariable("TXT_FILTER",$this->lng->txt('pay_filter'));
		$this->tpl->setVariable("FORM_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_TRANSACTION",$this->lng->txt('paya_transaction'));
		$this->tpl->setVariable("TXT_STARTING",$this->lng->txt('pay_starting'));
		$this->tpl->setVariable("TXT_ENDING",$this->lng->txt('pay_ending'));
		$this->tpl->setVariable("TXT_PAYED",$this->lng->txt('paya_payed'));
		$this->tpl->setVariable("TXT_ALL",$this->lng->txt('pay_all'));
		$this->tpl->setVariable("TXT_YES",$this->lng->txt('yes'));
		$this->tpl->setVariable("TXT_NO",$this->lng->txt('no'));
		$this->tpl->setVariable("TXT_CUSTOMER",$this->lng->txt('paya_customer'));
		$this->tpl->setVariable("TXT_VENDOR",$this->lng->txt('paya_vendor'));
		$this->tpl->setVariable("TXT_ACCESS",$this->lng->txt('paya_access'));
		$this->tpl->setVariable("TXT_ORDER_DATE_FROM",$this->lng->txt('pay_order_date_from'));
		$this->tpl->setVariable("TXT_ORDER_DATE_TIL",$this->lng->txt('pay_order_date_til'));
		$this->tpl->setVariable("TXT_UPDATE_VIEW",$this->lng->txt('pay_update_view'));
		$this->tpl->setVariable("TXT_RESET_FILTER",$this->lng->txt('pay_reset_filter'));

		$this->tpl->setVariable("TRANSACTION_TYPE_" . $_SESSION["pay_statistics"]["transaction_type"], " selected");
		$this->tpl->setVariable("TRANSACTION_VALUE", ilUtil::prepareFormOutput($_SESSION["pay_statistics"]["transaction_value"], true));
		$this->tpl->setVariable("PAYED_" . $_SESSION["pay_statistics"]["payed"], " selected");
		$this->tpl->setVariable("ACCESS_" . $_SESSION["pay_statistics"]["access"], " selected");
		$this->tpl->setVariable("CUSTOMER", ilUtil::prepareFormOutput($_SESSION["pay_statistics"]["customer"], true));
		$this->tpl->setVariable("VENDOR", ilUtil::prepareFormOutput($_SESSION["pay_statistics"]["vendor"], true));

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

		$this->__initBookingObject();

		if(!count($bookings = $this->booking_obj->getBookings()))
		{
			sendInfo($this->lng->txt('paya_no_bookings'));

			return true;
		}
		else
		{
			$this->__showButton('exportVendors',$this->lng->txt('excel_export'));
		}
		$img_change = "<img src=\"".ilUtil::getImagePath("edit.gif")."\" alt=\"".
			$this->lng->txt("edit")."\" title=\"".$this->lng->txt("edit").
			"\" border=\"0\" vspace=\"0\"/>";
		
		$counter = 0;
		foreach($bookings as $booking)
		{
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($booking['ref_id']);
			$tmp_vendor =& ilObjectFactory::getInstanceByObjId($booking['b_vendor_id']);
			$tmp_purchaser =& ilObjectFactory::getInstanceByObjId($booking['customer_id']);
			
			$f_result[$counter][] = $booking['transaction_extern'];
			$f_result[$counter][] = $tmp_obj->getTitle();
			$f_result[$counter][] = '['.$tmp_vendor->getLogin().']';
			$f_result[$counter][] = '['.$tmp_purchaser->getLogin().']';
			$f_result[$counter][] = date('Y m d H:i:s',$booking['order_date']);
			$f_result[$counter][] = $booking['duration'];
			$f_result[$counter][] = $booking['price'];

			$payed_access = $booking['payed'] ? 
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$payed_access .= '/';
			$payed_access .= $booking['access'] ?
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$f_result[$counter][] = $payed_access;

			$this->ctrl->setParameter($this,"booking_id",$booking['booking_id']);
			$link_change = "<a href=\"".$this->ctrl->getLinkTarget($this,"editStatistic")."\"> ".
				$img_change."</a>";

			$f_result[$counter][] = $link_change;

			unset($tmp_obj);
			unset($tmp_vendor);
			unset($tmp_purchaser);

			++$counter;
		}
		return $this->__showStatisticTable($f_result);
	}
	
	function editStatisticObject($a_show_confirm_delete = false)
	{
		if(!isset($_GET['booking_id']))
		{
			sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();

			return true;
		}

		$this->__showButton('statistic',$this->lng->txt('back'));

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_adm_edit_statistic.html',true);
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
		$tmp_user =& ilObjectFactory::getInstanceByObjId($booking['customer_id']);



		$this->tpl->setVariable("STAT_FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_usr_b.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("TITLE",$tmp_user->getFullname().' ['.$tmp_user->getLogin().']');

		// TXT
		$this->tpl->setVariable("TXT_TRANSACTION",$this->lng->txt('paya_transaction'));
		$this->tpl->setVariable("TXT_VENDOR",$this->lng->txt('paya_vendor'));
		$this->tpl->setVariable("TXT_PAY_METHOD",$this->lng->txt('paya_pay_method'));
		$this->tpl->setVariable("TXT_ORDER_DATE",$this->lng->txt('paya_order_date'));
		$this->tpl->setVariable("TXT_DURATION",$this->lng->txt('duration'));
		$this->tpl->setVariable("TXT_PRICE",$this->lng->txt('price_a'));
		$this->tpl->setVariable("TXT_PAYED",$this->lng->txt('paya_payed'));
		$this->tpl->setVariable("TXT_ACCESS",$this->lng->txt('paya_access'));

		$this->tpl->setVariable("TRANSACTION",$booking['transaction']);

		$tmp_vendor =& ilObjectFactory::getInstanceByObjId($booking['b_vendor_id']);

		$this->tpl->setVariable("VENDOR",$tmp_vendor->getFullname().' ['.$tmp_vendor->getLogin().']');

		switch($booking['b_pay_method'])
		{
			case $this->pobject->PAY_METHOD_BILL:
				$this->tpl->setVariable("PAY_METHOD",$this->lng->txt('pays_bill'));
				break;

			case $this->pobject->PAY_METHOD_BMF:
				$this->tpl->setVariable("PAY_METHOD",$this->lng->txt('pays_bmf'));
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
	function updateStatisticObject()
	{
		if(!isset($_GET['booking_id']))
		{
			sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->statisticObject();

			return true;
		}
		$this->__initBookingObject();

		$this->booking_obj->setBookingId((int) $_GET['booking_id']);
		$this->booking_obj->setAccess((int) $_POST['access']);
		$this->booking_obj->setPayed((int) $_POST['payed']);
		
		if($this->booking_obj->update())
		{
			sendInfo($this->lng->txt('paya_updated_booking'));

			$this->statisticObject();
			return true;
		}
		else
		{
			sendInfo($this->lng->txt('paya_error_update_booking'));

			$this->statisticObject();
			
			return true;
		}
	}

	function deleteStatisticObject()
	{
		if(!isset($_GET['booking_id']))
		{
			sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->statisticObject();

			return true;
		}
		sendInfo($this->lng->txt('paya_sure_delete_stat'));

		$this->editStatisticObject(true);

		return true;
	}
	function performDeleteObject()
	{
		if(!isset($_GET['booking_id']))
		{
			sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->statisticObject();

			return true;
		}

		$this->__initBookingObject();
		$this->booking_obj->setBookingId((int) $_GET['booking_id']);
		if(!$this->booking_obj->delete())
		{
			die('Error deleting booking');
		}
		sendInfo($this->lng->txt('pay_deleted_booking'));

		$this->statisticObject();

		return true;
	}


	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		// tabs are defined manually here. The autogeneration via objects.xml will be deprecated in future
		// for usage examples see ilObjGroupGUI or ilObjSystemFolderGUI
	}

	function generalSettingsObject($a_show_confirm = false)
	{
		include_once './payment/classes/class.ilGeneralSettings.php';

		$genSet = new ilGeneralSettings();
		$genSetData = $genSet->getAll();

		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.pays_general_settings.html",true);

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_pays.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_pays'));
		$this->tpl->setVariable("TITLE",$this->lng->txt('pays_general_settings'));
		$this->tpl->setVariable("TXT_CURRENCY_UNIT",$this->lng->txt('pays_currency_unit'));
		$this->tpl->setVariable("TXT_CURRENCY_SUBUNIT",$this->lng->txt('pays_currency_subunit'));
		$this->tpl->setVariable("TXT_ADDRESS",$this->lng->txt('pays_address'));
		$this->tpl->setVariable("TXT_BANK_DATA",$this->lng->txt('pays_bank_data'));
		$this->tpl->setVariable("TXT_ADD_INFO",$this->lng->txt('pays_add_info'));
		$this->tpl->setVariable("TXT_VAT_RATE",$this->lng->txt('pays_vat_rate'));
		$this->tpl->setVariable("TXT_PDF_PATH",$this->lng->txt('pays_pdf_path'));
		$this->tpl->setVariable("CURRENCY_UNIT",
								$this->error != "" && isset($_POST['currency_unit'])
								? ilUtil::prepareFormOutput($_POST['currency_unit'],true)
								: ilUtil::prepareFormOutput($genSetData['currency_unit'],true));
		$this->tpl->setVariable("CURRENCY_SUBUNIT",
								$this->error != "" && isset($_POST['currency_subunit'])
								? ilUtil::prepareFormOutput($_POST['currency_subunit'],true)
								: ilUtil::prepareFormOutput($genSetData['currency_subunit'],true));
		$this->tpl->setVariable("ADDRESS",
								$this->error != "" && isset($_POST['address'])
								? ilUtil::prepareFormOutput($_POST['address'],true)
								: ilUtil::prepareFormOutput($genSetData['address'],true));
		$this->tpl->setVariable("BANK_DATA",
								$this->error != "" && isset($_POST['bank_data'])
								? ilUtil::prepareFormOutput($_POST['bank_data'],true)
								: ilUtil::prepareFormOutput($genSetData['bank_data'],true));
		$this->tpl->setVariable("ADD_INFO",
								$this->error != "" && isset($_POST['add_info'])
								? ilUtil::prepareFormOutput($_POST['add_info'],true)
								: ilUtil::prepareFormOutput($genSetData['add_info'],true));
		$this->tpl->setVariable("VAT_RATE",
								$this->error != "" && isset($_POST['vat_rate'])
								? ilUtil::prepareFormOutput($_POST['vat_rate'],true)
								: ilUtil::prepareFormOutput($genSetData['vat_rate'],true));
		$this->tpl->setVariable("PDF_PATH",
								$this->error != "" && isset($_POST['pdf_path'])
								? ilUtil::prepareFormOutput($_POST['pdf_path'],true)
								: ilUtil::prepareFormOutput($genSetData['pdf_path'],true));
		
		// footer
		$this->tpl->setVariable("COLUMN_COUNT",2);
		$this->tpl->setVariable("PBTN_NAME",'saveGeneralSettings');
		$this->tpl->setVariable("PBTN_VALUE",$this->lng->txt('save'));
		
	}
	
	function saveGeneralSettingsObject()
	{
		include_once './payment/classes/class.ilGeneralSettings.php';

		$genSet = new ilGeneralSettings();

		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		if ($_POST["currency_unit"] == "" ||
			$_POST["currency_subunit"] == "" ||
			$_POST["address"] == "" ||
			$_POST["bank_data"] == "" ||
			$_POST["pdf_path"] == "")
		{
			$this->error = $this->lng->txt('pays_general_settings_not_valid');
			sendInfo($this->error);
			$this->generalSettingsObject();
			return;
		}

		$genSet->clearAll();
		$values = array(
			"currency_unit" => $_POST['currency_unit'],
			"currency_subunit" => $_POST['currency_subunit'],
			"address" => $_POST['address'],
			"bank_data" => $_POST['bank_data'],
			"add_info" => $_POST['add_info'],
			"vat_rate" => (float) str_replace(",", ".", $_POST['vat_rate']),
			"pdf_path" => $_POST['pdf_path']
		);
		$genSet->setAll($values);
		$this->generalSettingsObject();

		sendInfo($this->lng->txt('pays_updated_general_settings'));

		return true;
	}

	function vendorsObject($a_show_confirm = false)
	{
		include_once './payment/classes/class.ilPaymentBookings.php';
	
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$_SESSION['pays_vendor'] = is_array($_SESSION['pays_vendor']) ?  $_SESSION['pays_vendor'] : array();
		

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.pays_vendors.html",true);
		
		$this->__showButton('searchUser',$this->lng->txt('search_user'));

		$this->object->initPaymentVendorsObject();
		if(!count($vendors = $this->object->payment_vendors_obj->getVendors()))
		{
			sendInfo($this->lng->txt('pay_no_vendors_created'));
		}
		else
		{
			$this->__showButton('exportVendors',$this->lng->txt('excel_export'));
		}



		if($a_show_confirm)
		{
			$this->tpl->setCurrentBlock("confirm_delete");
			$this->tpl->setVariable("CONFIRM_FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
			$this->tpl->setVariable("CONFIRM_CMD",'performDeleteVendors');
			$this->tpl->setVariable("TXT_CONFIRM",$this->lng->txt('delete'));
			$this->tpl->parseCurrentBlock();
		}

		$counter = 0;
		$f_result = array();
		foreach($vendors as $vendor)
		{
			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($vendor['vendor_id'],false))
			{
				$f_result[$counter][]	= ilUtil::formCheckbox(in_array($vendor['vendor_id'],$_SESSION['pays_vendor']) ? 1 : 0,
															   "vendor[]",
															   $vendor['vendor_id']);
				$f_result[$counter][]	= $tmp_obj->getLogin();
				$f_result[$counter][]	= $vendor['cost_center'];
				$f_result[$counter][]	= ilPaymentBookings::_getCountBookingsByVendor($vendor['vendor_id']);
				
				unset($tmp_obj);
				++$counter;
			}
			$this->__showVendorsTable($f_result);

		} // END VENDORS TABLE

		return true;
	}

	function exportVendorsObject()
	{
		include_once './payment/classes/class.ilPaymentExcelWriterAdapter.php';

		$pewa =& new ilPaymentExcelWriterAdapter('payment_vendors.xls');

		// add/fill worksheet
		$this->addVendorWorksheet($pewa);
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
		$worksheet =& $workbook->addWorksheet($this->lng->txt('paya_statistic'));
		
		$worksheet->mergeCells(0,0,0,8);
		$worksheet->setColumn(0,0,32);
		$worksheet->setColumn(0,1,32);
		$worksheet->setColumn(0,2,16);
		$worksheet->setColumn(0,3,16);
		$worksheet->setColumn(0,4,16);
		$worksheet->setColumn(0,5,24);
		$worksheet->setColumn(0,6,8);
		$worksheet->setColumn(0,7,12);
		$worksheet->setColumn(0,8,16);

		$title = $this->lng->txt('paya_statistic');
		$title .= ' '.$this->lng->txt('as_of');
		$title .= strftime('%Y-%m-%d %R',time());

		$worksheet->writeString(0,0,$title,$pewa->getFormatTitle());

		$worksheet->writeString(1,0,$this->lng->txt('paya_transaction'),$pewa->getFormatHeader());
		$worksheet->writeString(1,1,$this->lng->txt('title'),$pewa->getFormatHeader());
		$worksheet->writeString(1,2,$this->lng->txt('paya_vendor'),$pewa->getFormatHeader());
		$worksheet->writeString(1,3,$this->lng->txt('pays_cost_center'),$pewa->getFormatHeader());
		$worksheet->writeString(1,4,$this->lng->txt('paya_customer'),$pewa->getFormatHeader());
		$worksheet->writeString(1,5,$this->lng->txt('paya_order_date'),$pewa->getFormatHeader());
		$worksheet->writeString(1,6,$this->lng->txt('duration'),$pewa->getFormatHeader());
		$worksheet->writeString(1,7,$this->lng->txt('price_a'),$pewa->getFormatHeader());
		$worksheet->writeString(1,8,$this->lng->txt('paya_payed_access'),$pewa->getFormatHeader());

		$counter = 2;
		foreach($bookings as $booking)
		{
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($booking['ref_id']);
			$tmp_vendor =& ilObjectFactory::getInstanceByObjId($booking['b_vendor_id']);
			$tmp_purchaser =& ilObjectFactory::getInstanceByObjId($booking['customer_id']);
			
			$worksheet->writeString($counter,0,$booking['transaction_extern']);
			$worksheet->writeString($counter,1,$tmp_obj->getTitle());
			$worksheet->writeString($counter,2,$tmp_vendor->getLogin());
			$worksheet->writeString($counter,3,ilPaymentVendors::_getCostCenter($tmp_vendor->getId()));
			$worksheet->writeString($counter,4,$tmp_purchaser->getLogin());
			$worksheet->writeString($counter,5,strftime('%Y-%m-%d %R',$booking['order_date']));
			/*
			$worksheet->write($counter,5,ilUtil::excelTime(date('Y',$booking['order_date']),
														   date('m',$booking['order_date']),
														   date('d',$booking['order_date']),
														   date('H',$booking['order_date']),
														   date('i',$booking['order_date']),
														   date('s',$booking['order_date'])),$pewa->getFormatDate());
			*/
			$worksheet->writeString($counter,6,$booking['duration']);
			$worksheet->writeString($counter,7,$booking['price']);
			
			$payed_access = $booking['payed'] ? 
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$payed_access .= '/';
			$payed_access .= $booking['access'] ?
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$worksheet->writeString($counter,8,$payed_access);

			unset($tmp_obj);
			unset($tmp_vendor);
			unset($tmp_purchaser);

			++$counter;
		}
	}		

	function addVendorWorksheet(&$pewa)
	{
		$this->object->initPaymentVendorsObject();
		if(!count($vendors = $this->object->payment_vendors_obj->getVendors()))
		{
			return false;
		}

		$workbook =& $pewa->getWorkbook();
		$worksheet =& $workbook->addWorksheet($this->lng->txt('pays_vendor'));

		// SHOW HEADER
		$worksheet->mergeCells(0,0,0,2);
		$worksheet->setColumn(1,0,32);
		$worksheet->setColumn(1,1,32);
		$worksheet->setColumn(1,2,32);

		$title = $this->lng->txt('paya_vendor_list');
		$title .= ' '.$this->lng->txt('as_of');
		$title .= strftime('%Y-%m-%d %R',time());

		$worksheet->writeString(0,0,$title,$pewa->getFormatTitle());

		$worksheet->writeString(1,0,$this->lng->txt('login'),$pewa->getFormatHeader());
		$worksheet->writeString(1,1,$this->lng->txt('fullname'),$pewa->getFormatHeader());
		$worksheet->writeString(1,2,$this->lng->txt('pays_cost_center'),$pewa->getFormatHeader());

		$counter = 2;
		foreach($vendors as $vendor)
		{
			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($vendor['vendor_id'],false))
			{
				$worksheet->writeString($counter,0,$tmp_obj->getLogin());
				$worksheet->writeString($counter,1,$tmp_obj->getFullname());
				$worksheet->writeString($counter,2,$vendor['cost_center']);
			}
			unset($tmp_obj);
			++$counter;
		}
	}		
	
	function payMethodsObject()
	{
		include_once './payment/classes/class.ilPayMethods.php';

		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.pays_pay_methods.html",true);

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_pays.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_pays'));
		$this->tpl->setVariable("TITLE",$this->lng->txt('pays_pay_methods'));
		$this->tpl->setVariable("TXT_OFFLINE",$this->lng->txt('pays_offline'));
		$this->tpl->setVariable("TXT_BILL",$this->lng->txt('pays_bill'));
		$this->tpl->setVariable("BILL_CHECK",ilUtil::formCheckbox(
									(int) ilPayMethods::_enabled('pm_bill') ? 1 : 0,'pm_bill',1,true));

		$this->tpl->setVariable("TXT_ENABLED",$this->lng->txt('enabled'));
		$this->tpl->setVariable("TXT_ONLINE",$this->lng->txt('pays_online'));
		$this->tpl->setVariable("TXT_BMF",$this->lng->txt('pays_bmf'));
		$this->tpl->setVariable("ONLINE_CHECK",ilUtil::formCheckbox((int) ilPayMethods::_enabled('pm_bmf'),'pm_bmf',1));
		
		// footer
		$this->tpl->setVariable("COLUMN_COUNT",3);
		$this->tpl->setVariable("PBTN_NAME",'savePayMethods');
		$this->tpl->setVariable("PBTN_VALUE",$this->lng->txt('save'));
		
	}

	function savePayMethodsObject()
	{
		include_once './payment/classes/class.ilPayMethods.php';
		include_once './payment/classes/class.ilPaymentObject.php';


		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		

		// check current payings
		if(ilPayMethods::_enabled('pm_bill') and !$_POST['pm_bill'])
		{
			if(ilPaymentObject::_getCountObjectsByPayMethod('pm_bill'))
			{
				sendInfo($this->lng->txt('pays_objects_bill_exist'));
				$this->payMethodsObject();

				return false;
			}
		}

		if(ilPayMethods::_enabled('pm_bmf') and !$_POST['pm_bmf'])
		{
			if(ilPaymentObject::_getCountObjectsByPayMethod('pm_bmf'))
			{
				sendInfo($this->lng->txt('pays_objects_bmf_exist'));
				$this->payMethodsObject();

				return false;
			}
		}

		ilPayMethods::_disableAll();
		if(isset($_POST['pm_bill']))
		{
			ilPayMethods::_enable('pm_bill');
		}
		if(isset($_POST['pm_bmf']))
		{
			ilPayMethods::_enable('pm_bmf');
		}
		$this->payMethodsObject();

		sendInfo($this->lng->txt('pays_updated_pay_method'));

		return true;
	}

	function cancelDeleteVendorsObject()
	{
		unset($_SESSION['pays_vendor']);
		$this->vendorsObject();

		return true;
	}

	function deleteVendors()
	{
		include_once './payment/classes/class.ilPaymentBookings.php';

		if(!count($_POST['vendor']))
		{
			sendInfo($this->lng->txt('pays_no_vendor_selected'));
			$this->vendorsObject();

			return true;
		}
		// CHECK BOOKINGS
		foreach($_POST['vendor'] as $vendor)
		{
			if(ilPaymentBookings::_getCountBookingsByVendor($vendor))
			{
				sendInfo($this->lng->txt('pays_active_bookings'));
				$this->vendorsObject();

				return true;
			}
		}
		
		$_SESSION["pays_vendor"] = $_POST["vendor"];
		sendInfo($this->lng->txt("pays_sure_delete_selected_vendors"));
		$this->vendorsObject(true);

		return true;
	}
	function performDeleteVendorsObject()
	{
		include_once './payment/classes/class.ilPaymentTrustees.php';
		
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->object->initPaymentVendorsObject();

		foreach($_SESSION['pays_vendor'] as $vendor)
		{
			$this->object->payment_vendors_obj->delete($vendor);
			ilPaymentTrustees::_deleteTrusteesOfVendor($vendor);
		}

		sendInfo($this->lng->txt('pays_deleted_number_vendors').' '.count($_SESSION['pays_vendor']));
		unset($_SESSION['pays_vendor']);
		
		$this->vendorsObject();

		return true;
	}

	function editVendor()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if(!count($_POST['vendor']))
		{
			sendInfo($this->lng->txt('pays_no_vendor_selected'));
			$this->vendorsObject();

			return true;
		}
		if(count($_POST['vendor']) > 1)
		{
			sendInfo($this->lng->txt('pays_too_many_vendors_selected'));
			$this->vendorsObject();

			return true;
		}

		$_SESSION["pays_vendor"] = $_POST["vendor"][0];

		$this->object->initPaymentVendorsObject();

		if (!is_array($this->object->payment_vendors_obj->vendors[$_SESSION["pays_vendor"]]))
		{
			$this->vendorsObject();

			return true;
		}

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pays_vendor.html',true);

		$this->tpl->setVariable("VENDOR_FORMACTION",$this->ctrl->getFormAction($this));

		// set table header
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_usr_b.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("TITLE",$this->lng->txt('pays_vendor'));

		// set plain text variables
		$this->tpl->setVariable("TXT_VENDOR",$this->lng->txt('pays_vendor'));
		$this->tpl->setVariable("TXT_COST_CENTER",$this->lng->txt('pays_cost_center'));

		$this->tpl->setVariable("INPUT_VALUE",ucfirst($this->lng->txt('save')));

		// fill defaults

		$this->tpl->setVariable("VENDOR",
								ilObjUser::getLoginByUserId($this->object->payment_vendors_obj->vendors[$_SESSION["pays_vendor"]]["vendor_id"]), true);
		$this->tpl->setVariable("COST_CENTER",
								$this->error != "" && isset($_POST['cost_center'])
								? ilUtil::prepareFormOutput($_POST['cost_center'],true)
								: ilUtil::prepareFormOutput($this->object->payment_vendors_obj->vendors[$_SESSION["pays_vendor"]]["cost_center"],true));

		// Button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "vendors"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt('pay_bmf_back'));
		$this->tpl->parseCurrentBlock("btn_cell");

	}
	function performEditVendorObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if(!count($_SESSION['pays_vendor']))
		{
			sendInfo($this->lng->txt('pays_no_vendor_selected'));
			$this->vendorsObject();

			return true;
		}
		if(count($_SESSION['pays_vendor']) > 1)
		{
			sendInfo($this->lng->txt('pays_too_many_vendors_selected'));
			$this->vendorsObject();

			return true;
		}

		$this->object->initPaymentVendorsObject();

		if (!is_array($this->object->payment_vendors_obj->vendors[$_SESSION["pays_vendor"]]))
		{
			$this->vendorsObject();

			return true;
		}

		if ($_POST["cost_center"] == "")
		{
			$this->error = $this->lng->txt('pays_cost_center_not_valid');
			sendInfo($this->error);
			$_POST["vendor"] = array($_SESSION["pays_vendor"]);
			$this->editVendor();
			return;
		}

		$this->object->initPaymentVendorsObject();
		$this->object->payment_vendors_obj->update($_SESSION["pays_vendor"], $_POST["cost_center"]);

		unset($_SESSION['pays_vendor']);

		$this->vendorsObject();

		return true;
	}

	function searchUserObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.pays_user_search.html",true);

		$this->lng->loadLanguageModule('search');

		$this->tpl->setVariable("F_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SEARCH_ASSIGN_USR",$this->lng->txt("crs_search_members"));
		$this->tpl->setVariable("SEARCH_SEARCH_TERM",$this->lng->txt("search_search_term"));
		$this->tpl->setVariable("SEARCH_VALUE",$_SESSION["pays_search_str"] ? $_SESSION["pays_search_str"] : "");
		$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("search"));

		return true;
	}

	function searchObject()
	{
		global $rbacsystem,$tree;

		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$_SESSION["pays_search_str"] = $_POST["search_str"] = $_POST["search_str"] ? $_POST["search_str"] : $_SESSION["pays_search_str"];

		if(!isset($_POST["search_str"]))
		{
			sendInfo($this->lng->txt("crs_search_enter_search_string"));
			$this->searchUserObject();
			
			return false;
		}
		if(!count($result = $this->__search(ilUtil::stripSlashes($_POST["search_str"]))))
		{
			sendInfo($this->lng->txt("crs_no_results_found"));
			$this->searchUserObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.pays_usr_selection.html",true);
		$this->__showButton("searchUser",$this->lng->txt("crs_new_search"));
		
		$counter = 0;
		$f_result = array();
		foreach($result as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user["id"],false))
			{
				continue;
			}
			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user["id"]);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getFirstname();

			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result);

		return true;
	}
	function addVendorObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!$_POST['vendor_login'])
		{
			sendInfo($this->lng->txt('pays_no_username_given'));
			$this->vendorsObject();

			return true;
		}
		if(!($usr_id = ilObjUser::getUserIdByLogin(ilUtil::stripSlashes($_POST['vendor_login']))))
		{
			sendInfo($this->lng->txt('pays_no_valid_username_given'));
			$this->vendorsObject();

			return true;
		}
		
		$this->object->initPaymentVendorsObject();

		if($this->object->payment_vendors_obj->isAssigned($usr_id))
		{
			sendInfo($this->lng->txt('pays_user_already_assigned'));
			$this->vendorsObject();

			return true;
		}
		$this->object->payment_vendors_obj->add($usr_id);

		sendInfo($this->lng->txt('pays_added_vendor'));
		$this->vendorsObject();
		
		return true;
	}
		
	function addUserObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->lng->loadLanguageModule('crs');
		if(!is_array($_POST["user"]))
		{
			sendInfo($this->lng->txt("crs_no_users_selected"));
			$this->searchObject();

			return false;
		}
		
		$this->object->initPaymentVendorsObject();

		$already_assigned = $assigned = 0;
		foreach($_POST['user'] as $usr_id)
		{
			if($this->object->payment_vendors_obj->isAssigned($usr_id))
			{
				++$already_assigned;

				continue;
			}
			$this->object->payment_vendors_obj->add($usr_id);
			++$assigned;
			
			// TODO: SEND NOTIFICATION
		}
		$message = '';
		if($assigned)
		{
			$message .= $this->lng->txt('pays_assigned_vendors').' '.$assigned;
		}
		if($already_assigned)
		{
			$message .= '<br />'.$this->lng->txt('pays_already_assigned_vendors').' '.$already_assigned;
		}

		sendInfo($message);
		$this->vendorsObject();

		return true;
	}		


	// PRIVATE
	function __showStatisticTable($a_result_set)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();


		$tbl->setTitle($this->lng->txt("paya_statistic"),"icon_pays_b.gif",$this->lng->txt("paya_statistic"));
		$tbl->setHeaderNames(array($this->lng->txt("paya_transaction"),
								   $this->lng->txt("title"),
								   $this->lng->txt("paya_vendor"),
								   $this->lng->txt("paya_customer"),
								   $this->lng->txt("paya_order_date"),
								   $this->lng->txt("duration"),
								   $this->lng->txt("price_a"),
								   $this->lng->txt("paya_payed_access"),
								   $this->lng->txt("edit")));

		$tbl->setHeaderVars(array("transaction",
								  "title",
								  "vendor",
								  "customer",
								  "order_date",
								  "duration",
								  "price",
								  "payed_access",
								  "options"),
							$this->ctrl->getParameterArray($this,"statistic",false));

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

		$this->tpl->setVariable("STATISTIC_TABLE",$tbl->tpl->get());

		return true;
	}

	function __initBookingObject()
	{
		include_once './payment/classes/class.ilPaymentBookings.php';

		$this->booking_obj =& new ilPaymentBookings($this->user_obj->getId(),true);
	}

	function __showVendorsTable($a_result_set)
	{
		$actions = array(
			"editVendorObject"	=> $this->lng->txt("pays_edit_vendor"),
			"deleteVendorsObject"	=> $this->lng->txt("pays_delete_vendor")
		);

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");

		$tpl->setCurrentBlock("input_text");
		$tpl->setVariable("PB_TXT_NAME",'vendor_login');
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME","addVendor");
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("pays_add_vendor"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("plain_buttons");
		$tpl->parseCurrentBlock();

		$tpl->setVariable("COLUMN_COUNTS",4);

		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$tpl->setCurrentBlock("tbl_action_select");
		$tpl->setVariable("SELECT_ACTION",ilUtil::formSelect(1,"action",$actions,false,true));
		$tpl->setVariable("BTN_NAME","gateway");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("execute"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("vendors"),"icon_usr_b.gif",$this->lng->txt("vendors"));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt("pays_vendor"),
								   $this->lng->txt("pays_cost_center"),
								   $this->lng->txt("pays_number_bookings")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "cost_center",
								  "bookings"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "vendors",
								  "update_members" => 1,
								  "cmdClass" => "ilobjpaymentsettingsgui",
								  "cmdNode" => $_GET["cmdNode"]));
#		$tbl->setColumnWidth(array("4%","48%","25%","24%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();

		$this->tpl->setVariable("VENDOR_TABLE",$tbl->tpl->get());

		return true;
	}


	function __showSearchUserTable($a_result_set,$a_cmd = "search")
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();


		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","vendors");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("cancel"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","addUser");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("add"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("pays_header_select_vendor"),"icon_usr_b.gif",$this->lng->txt("pays_header_select_vendor"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("login"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => $a_cmd,
								  "cmdClass" => "ilobjpaymentsettingsgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("3%","32%","32%","32%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();
		
		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
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
	}		

	function &__initTableGUI()
	{
		include_once "./classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}

	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{

		$offset = $_GET["offset"];
		$order = $_GET["sort_by"];
		$direction = $_GET["sort_order"];

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setMaxCount(count($result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}

	function __search($a_search_string)
	{
		include_once("./classes/class.ilSearch.php");

		$this->lng->loadLanguageModule("content");

		$search =& new ilSearch($_SESSION["AccountId"]);
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
			sendInfo($message,true);
			$this->ctrl->redirect($this,"searchUser");
		}
		return $search->getResultByType('usr');
	}		
	
} // END class.ilObjPaymentSettingsGUI
?>
