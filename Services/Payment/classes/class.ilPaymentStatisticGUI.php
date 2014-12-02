<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilPaymentStatisticGUI
*
* @author Stefan Meyer
* @version $Id: class.ilPaymentStatisticGUI.php 22253 2009-10-30 07:58:39Z nkrzywon $
* @ilCtrl_Calls ilPaymentStatisticGUI: ilPaymentObjectSelector
*
* @package core
*
*/
include_once './Services/Payment/classes/class.ilPaymentObject.php';
include_once './Services/Payment/classes/class.ilPayMethods.php';
include_once './Services/Payment/classes/class.ilPaymentCurrency.php';
include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
include_once './Services/Payment/classes/class.ilShopTableGUI.php';
include_once './Services/Payment/classes/class.ilInvoiceNumberPlaceholdersPropertyGUI.php';
include_once("./Services/Payment/classes/class.ilPaymentObjectSelector.php");

class ilPaymentStatisticGUI extends ilShopBaseGUI
{
	private $pobject = null;
	public $booking_obj = null;

	public function __construct($user_obj)
	{
		parent::__construct();		

		$this->ctrl->saveParameter($this, 'baseClass');

		$this->user_obj = $user_obj;
		$this->pobject = new ilPaymentObject($this->user_obj);

	}
	
	protected function prepareOutput()
	{
		global $ilTabs;
		
		parent::prepareOutput();

		$ilTabs->setTabActive('paya_header');
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

	public function resetFilter()
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
		unset($_POST["filter_title_id"]);

		$this->showStatistics();
	}

	public function showStatistics()
	{
		global $rbacsystem, $ilToolbar, $ilObjDataCache;

		// MINIMUM ACCESS LEVEL = 'read'
	/*	if(!$rbacsystem->checkAccess('read', $this->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}
	*/	
	
		
		$ilToolbar->addButton($this->lng->txt('paya_add_customer'), $this->ctrl->getLinkTarget($this, 'showObjectSelector'));
		if(!$_POST['show_filter'] && $_POST['updateView'] == '1')
		{
			$this->resetFilter();
		}
		else
		if ($_POST['updateView'] == 1)
		{
			$_SESSION['pay_statistics']['show_filter']= $_POST['show_filter'];
			$_SESSION['pay_statistics']['updateView'] = true;
			$_SESSION['pay_statistics']['until_check'] = $_POST['until_check'];
			$_SESSION['pay_statistics']['from_check'] = $_POST['from_check'];
			$_SESSION['pay_statistics']['transaction_type'] = isset($_POST['transaction_type']) ? $_POST['transaction_type'] : '' ;
			$_SESSION['pay_statistics']['transaction_value'] = isset($_POST['transaction_value']) ?  $_POST['transaction_value'] : '';
			$_SESSION['pay_statistics']['filter_title_id'] = (int)$_POST['filter_title_id'];
		
			if($_SESSION['pay_statistics']['from_check'] == '1')
			{
				$_SESSION['pay_statistics']['from']['date']['d'] = $_POST['from']['date']['d'];
				$_SESSION['pay_statistics']['from']['date']['m'] = $_POST['from']['date']['m'];
				$_SESSION['pay_statistics']['from']['date']['y'] = $_POST['from']['date']['y'];
			} 
			else 
			{
				$_SESSION['pay_statistics']['from']['date']['d'] = '';
				$_SESSION['pay_statistics']['from']['date']['m'] = '';
				$_SESSION['pay_statistics']['from']['date']['y'] = '';
			}
			
			if($_SESSION['pay_statistics']['until_check']== '1')
			{
				$_SESSION['pay_statistics']['til']['date']['d'] = $_POST['til']['date']['d'];
				$_SESSION['pay_statistics']['til']['date']['m'] = $_POST['til']['date']['m'];
				$_SESSION['pay_statistics']['til']['date']['y'] = $_POST['til']['date']['y'];
			} 
			else 
			{
				$_SESSION['pay_statistics']['til']['date']['d'] = '';
				$_SESSION['pay_statistics']['til']['date']['m'] = '';
				$_SESSION['pay_statistics']['til']['date']['y'] = '';
			}

			$_SESSION['pay_statistics']['payed'] = $_POST['payed'];
			$_SESSION['pay_statistics']['access'] = $_POST['access'];
			$_SESSION['pay_statistics']['pay_method'] = $_POST['pay_method'];
			$_SESSION['pay_statistics']['customer'] = isset ($_POST['customer']) ? $_POST['customer'] : '';
			$_SESSION['pay_statistics']['vendor'] = isset ($_POST['vendor']) ? $_POST['vendor']: '';
		}

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');
		
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		// FILTER FORM
		$filter_form = new ilPropertyFormGUI();
		$filter_form->setFormAction($this->ctrl->getFormAction($this));
		$filter_form->setTitle($this->lng->txt('pay_filter'));
		$filter_form->setId('formular');
		$filter_form->setTableWidth('100 %');

			
		$o_hide_check = new ilCheckBoxInputGUI($this->lng->txt('show_filter'),'show_filter');
		$o_hide_check->setValue(1);		
		$o_hide_check->setChecked($_SESSION['pay_statistics']['show_filter'] ? 1 : 0);		

		$o_hidden = new ilHiddenInputGUI('updateView');
		$o_hidden->setValue(1);
		$o_hidden->setPostVar('updateView');
		$o_hide_check->addSubItem($o_hidden);
			
		$o_transaction_type = new ilSelectInputGUI(); 
		$trans_option = array($this->lng->txt('pay_starting'),$this->lng->txt('pay_ending'));
		$trans_value = array('0','1'); 
		$o_transaction_type->setTitle($this->lng->txt('paya_transaction'));
		$o_transaction_type->setOptions($trans_option);
		$o_transaction_type->setValue($_SESSION['pay_statistics']['transaction_type']);		
		$o_transaction_type->setPostVar('transaction_type');
		$o_hide_check->addSubItem($o_transaction_type);
		
		$o_transaction_val = new ilTextInputGUI();
		$o_transaction_val->setValue($_SESSION['pay_statistics']['transaction_value']);		
		$o_transaction_val->setPostVar('transaction_value');
		$o_hide_check->addSubItem($o_transaction_val);

		$o_customer = new ilTextInputGUI();
		$o_customer->setTitle($this->lng->txt('paya_customer'));
		$o_customer->setValue($_SESSION['pay_statistics']['customer']);		
		$o_customer->setPostVar('customer');
		$o_hide_check->addSubItem($o_customer);
		
		$o_vendor = new ilTextInputGUI();
		$o_vendor->setTitle($this->lng->txt('paya_vendor'));
		$o_vendor->setValue($_SESSION['pay_statistics']['vendor']);				
		$o_vendor->setPostVar('vendor');
		$o_hide_check->addSubItem($o_vendor);
		
		$o_from_check = new ilCheckBoxInputGUI($this->lng->txt('pay_order_date_from'),'from_check');
		$o_from_check->setValue(1);		
		$o_from_check->setChecked($_SESSION['pay_statistics']['from_check'] ? 1 : 0);		
		
		$o_date_from = new ilDateTimeInputGUI();
		$o_date_from->setPostVar('from');			
		$_POST['from'] = $_SESSION['pay_statistics']['from'];
		
		if($_SESSION['pay_statistics']['from_check'] == '1') 
		{
			$o_date_from->checkInput();	
		}

		$o_from_check->addSubItem($o_date_from);
		$o_hide_check->addSubItem($o_from_check);
		
		$o_until_check = new ilCheckBoxInputGUI($this->lng->txt('pay_order_date_til'), 'until_check');
		$o_until_check->setValue(1);	
		$o_until_check->setChecked($_SESSION['pay_statistics']['until_check'] ? 1 : 0);				

		$o_date_until = new ilDateTimeInputGUI();
		$o_date_until->setPostVar('til');
		$_POST['til'] = $_SESSION['pay_statistics']['til'];
		
		if($_SESSION['pay_statistics']['until_check'] == '1') 
		{
			$o_date_until->checkInput();	
		}
		
		$o_until_check->addSubItem($o_date_until);
		$o_hide_check->addSubItem($o_until_check);	
		
				
		// title filter
		$this->__initBookingObject();		
		$title_options['all']=$this->lng->txt('pay_all');
		$unique_titles = $this->booking_obj->getUniqueTitles();
		
		if(is_array($unique_titles) && count($unique_titles))
		{			
			foreach($unique_titles as $ref_id)
			{
				$title_options[$ref_id] = $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($ref_id));
			}
		}
			
		$o_object_title = new ilSelectInputGUI();
		$o_object_title->setTitle($this->lng->txt('title'));
		$o_object_title->setOptions($title_options);
		$o_object_title->setValue($_SESSION["pay_statistics"]["filter_title_id"]);
		$o_object_title->setPostVar('filter_title_id');
		$o_hide_check->addSubItem($o_object_title);
		
		$o_payed = new ilSelectInputGUI();
		$payed_option = array('all'=>$this->lng->txt('pay_all'),'1'=>$this->lng->txt('yes'),'0'=>$this->lng->txt('no'));

		$o_payed->setTitle($this->lng->txt('paya_payed'));
		$o_payed->setOptions($payed_option);
		$o_payed->setValue($_SESSION['pay_statistics']['payed']);
		$o_payed->setPostVar('payed');		

		$o_hide_check->addSubItem($o_payed);

		$o_access = new ilSelectInputGUI();
		$access_option = array('all'=>$this->lng->txt('pay_all'),'1'=>$this->lng->txt('yes'),'0'=>$this->lng->txt('no'));

		$o_access->setTitle($this->lng->txt('paya_access'));
		$o_access->setOptions($access_option);
		$o_access->setValue($_SESSION['pay_statistics']['access']);
		$o_access->setPostVar('access');
		$o_hide_check->addSubItem($o_access);		

		$o_paymethod = new ilSelectInputGUI();
		$o_paymethod->setTitle($this->lng->txt('payment_system'));
		$o_paymethod->setOptions(ilPayMethods::getPayMethodsOptions('all'));
		$o_paymethod->setValue($_SESSION['pay_statistics']['pay_method']);
		$o_paymethod->setPostVar('pay_method');
		$o_hide_check->addSubItem($o_paymethod);				
		
		$filter_form->addCommandButton('showStatistics', $this->lng->txt('pay_update_view'));
		$filter_form->addCommandButton('resetFilter', $this->lng->txt('pay_reset_filter'));
		
		$filter_form->addItem($o_hide_check);		
	
		$this->tpl->setVariable('FORM', $filter_form->getHTML());
	
		
// STATISTICS TABLE 
		$this->__initBookingObject();

		if(!count($bookings = $this->booking_obj->getBookings()))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_bookings'));

			return true;
		}
#		$this->__showButton('excelExport',$this->lng->txt('excel_export'));

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
			$str_paymethod = ilPayMethods::getStringByPaymethod($booking['b_pay_method']);
			$transaction .= " (" . $str_paymethod . ")";
			
			$f_result[$counter]['transaction'] = $transaction;
			$f_result[$counter]['object_title'] = ($tmp_obj != '' ?  $tmp_obj : $this->lng->txt('object_deleted'));
			$f_result[$counter]['vendor'] = ($tmp_vendor != '' ?  '['.$tmp_vendor.']' : $this->lng->txt('user_deleted'));
			$f_result[$counter]['customer'] = ($tmp_purchaser != '' ?  '['.$tmp_purchaser.']' : $this->lng->txt('user_deleted'));
			$f_result[$counter]['order_date'] = ilDatePresentation::formatDate(new ilDateTime($booking['order_date'], IL_CAL_UNIX));
			
			if($booking['duration'] == 0)
			{
				$booking['duration'] = $this->lng->txt('unlimited_duration');
			}

			$f_result[$counter]['duration'] = $booking['duration'];
			$f_result[$counter]['price'] = ilFormat::_getLocalMoneyFormat($booking['price']).' '.$booking['currency_unit'];
			$f_result[$counter]['discount'] = $booking['discount'].' '.$booking['currency_unit'];

			$payed_access = $booking['payed'] ? 
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$payed_access .= '/';
			$payed_access .= $booking['access_granted'] ?
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$f_result[$counter]['payed_access'] = $payed_access;

			$this->ctrl->setParameter($this,"booking_id",$booking['booking_id']);
			$link_change = "<div class=\"il_ContainerItemCommands\"><a class=\"il_ContainerItemCommand\" href=\"".$this->ctrl->getLinkTarget($this,"editStatistic")."\">".$this->lng->txt("edit")."</a></div>";

			$f_result[$counter]['edit'] = $link_change;

			unset($tmp_obj);
			unset($tmp_vendor);
			unset($tmp_purchaser);

			++$counter;
		}
		return $this->__showStatisticTable($f_result);
	}

	public function excelExport()
	{
		include_once './Services/Payment/classes/class.ilPaymentExcelWriterAdapter.php';

		$pewa = new ilPaymentExcelWriterAdapter('payment_vendors.xls');

		// add/fill worksheet
		$this->addStatisticWorksheet($pewa);

		// HEADER SENT
		
		$workbook = $pewa->getWorkbook();
		@$workbook->close();
	}

	public function addStatisticWorksheet($pewa)
	{
		include_once './Services/Payment/classes/class.ilPaymentVendors.php';

		$this->__initBookingObject();		

		$workbook = $pewa->getWorkbook();
		$worksheet = $workbook->addWorksheet($this->lng->txt('bookings'));		

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

		if(!count($bookings = $this->booking_obj->getBookings()))
		{
			return false;
		}		

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
			
			$pay_method = ilPayMethods::getStringByPaymethod($booking['b_pay_method']);	

			$worksheet->writeString($counter,0,$pay_method);
			$worksheet->writeString($counter,1,$booking['transaction_extern']);
			$worksheet->writeString($counter,2,($tmp_obj != '' ?  $tmp_obj : $this->lng->txt('object_deleted')));
			$worksheet->writeString($counter,3,($tmp_vendor != '' ? $tmp_vendor : $this->lng->txt('user_deleted')));
			$worksheet->writeString($counter,4,ilPaymentVendors::_getCostCenter($booking['b_vendor_id']));
			$worksheet->writeString($counter,5,($tmp_purchaser != '' ? $tmp_purchaser : $this->lng->txt('user_deleted')));
			$worksheet->writeString($counter,6,strftime('%Y-%m-%d %R',$booking['order_date']));
			$worksheet->writeString($counter,7,$booking['duration']);
			$worksheet->writeString($counter,8,$booking['price']);
			
			$payed_access = $booking['payed'] ? 
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$payed_access .= '/';
			$payed_access .= $booking['access_granted'] ?
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$worksheet->writeString($counter,9,$payed_access);

			unset($tmp_obj);
			unset($tmp_vendor);
			unset($tmp_purchaser);

			++$counter;
		}
	}		

	public function editStatistic($a_show_confirm_delete = false)
	{
		global $ilToolbar;

		if(!isset($_GET['booking_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();

			return true;
		}
		
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'showStatistics'));
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');	
		
		$this->ctrl->setParameter($this,'booking_id',(int) $_GET['booking_id']);
		$this->__initBookingObject();
		$bookings = $this->booking_obj->getBookings();
		$booking = $bookings[(int) $_GET['booking_id']];

		// confirm delete
		if($a_show_confirm_delete)
		{
			$pobject_data = ilPaymentObject::_getObjectData($booking['pobject_id']);
			$tmp_obj = ilObject::_lookupTitle(ilObject::_lookupObjId($pobject_data['ref_id']));
			$type = ilObject::_lookupType(ilObject::_lookupObjId($pobject_data['ref_id']));

			$oConfirmationGUI = new ilConfirmationGUI();
			
			// set confirm/cancel commands
			$oConfirmationGUI->setFormAction($this->ctrl->getFormAction($this,"performDelete"));
			$oConfirmationGUI->setHeaderText($this->lng->txt("paya_sure_delete_stat"));
			$oConfirmationGUI->setCancel($this->lng->txt("cancel"), "editStatistic");
			
			if($type == 'crs')
			{
				$oConfirmationGUI->addButton($this->lng->txt("confirm"), "performDeleteDeassignCrs");
			}
			else
				$oConfirmationGUI->setConfirm($this->lng->txt("confirm"), "performDelete");

			$oConfirmationGUI->addItem('booking_id', $_GET['booking_id'], $tmp_obj);
			
			$this->tpl->setVariable('CONFIRMATION', $oConfirmationGUI->getHTML());
			return true;
		}
		

		// get customer_obj
		$tmp_user = ilObjectFactory::getInstanceByObjId($booking['customer_id'], false);
		$oForm = new ilPropertyFormGUI();
		$oForm->setFormAction($this->ctrl->getFormAction($this));
		$oForm->setId('stat_form');
		$oForm->setTableWidth('50 %');		
		if(is_object($tmp_user))
		{
			$frm_user = $tmp_user->getFullname().' ['.$tmp_user->getLogin().']';
		}
		else
		{
			$frm_user = $this->lng->txt('user_deleted');
		}
		$oForm->setTitle($frm_user);

		$pObj = new ilPaymentObject($this->user_obj, $booking['pobject_id']);
		$tmp_obj = ilObject::_lookupTitle(ilObject::_lookupObjId($pObj->getRefId()));				

		// object_title
		$oTitleGUI = new ilNonEditableValueGUI($this->lng->txt('title'));
		$oTitleGUI->setValue($tmp_obj != '' ?  $tmp_obj : $this->lng->txt('object_deleted'));
		$oForm->addItem($oTitleGUI);
				
		// transaction
		$oTransactionGUI = new ilNonEditableValueGUI($this->lng->txt('paya_transaction'));
		$oTransactionGUI->setValue($booking['transaction']);
		$oForm->addItem($oTransactionGUI);
		
		//vendor
		$oVendorGUI = new ilNonEditableValueGUI($this->lng->txt('paya_vendor'));
		$tmp_vendor = ilObjectFactory::getInstanceByObjId($booking['b_vendor_id'], false);
		if(is_object($tmp_vendor))
		{
			$frm_vendor = $tmp_vendor->getFullname().' ['.$tmp_vendor->getLogin().']';
		}
		else
		{
			$frm_vendor =  $this->lng->txt('user_deleted');
		}		
		$oVendorGUI->setValue($frm_vendor);
		$oForm->addItem($oVendorGUI);

		// paymethod
		$oPaymethodGUI = new ilNonEditableValueGUI($this->lng->txt('paya_pay_method'));
		$oPaymethodGUI->setValue(ilPayMethods::getStringByPaymethod($booking['b_pay_method']));
		$oForm->addItem($oPaymethodGUI);	

		// order_date
		$oOrderdateGUI = new ilNonEditableValueGUI($this->lng->txt('paya_order_date'));
		$oOrderdateGUI->setValue(ilDatePresentation::formatDate(new ilDateTime($booking["order_date"],IL_CAL_UNIX)));
		$oForm->addItem($oOrderdateGUI);	
		
		// duration
		$oDurationGUI = new ilNonEditableValueGUI($this->lng->txt('duration'));
		if(($booking['duration'] == 0) && ($booking['access_enddate'] == NULL))
		{
			$frm_duration = $this->lng->txt("unlimited_duration");
		}
		else
		{	
			if($booking['duration'] > 0)
			{
				$frm_duration = $booking['duration'].' '.$this->lng->txt('paya_months');
			}
			$frm_duration .= ilDatePresentation::formatDate(new ilDate($booking['access_startdate'], IL_CAL_DATETIME))
			.' - '.ilDatePresentation::formatDate(new ilDate($booking['access_enddate'], IL_CAL_DATETIME));
		}		
		$oDurationGUI->setValue($frm_duration);
		$oForm->addItem($oDurationGUI);		
		
		// price
		$oPriceGUI = new ilNonEditableValueGUI($this->lng->txt('price_a'));
		$oPriceGUI->setValue($booking['price'].' '.$booking['currency_unit'] );
		$oForm->addItem($oPriceGUI);

		// payed
		$oPayedGUI = new ilSelectInputGUI();
		$payed_option = array(0 => $this->lng->txt('no'),1 => $this->lng->txt('yes'));

		$oPayedGUI->setTitle($this->lng->txt('paya_payed'));
		$oPayedGUI->setOptions($payed_option);
		$oPayedGUI->setValue($booking['payed']);
		$oPayedGUI->setPostVar('payed');		
		$oForm->addItem($oPayedGUI);
		
		// access
		$oAccessGUI = new ilSelectInputGUI();
		$access_option = array(0 => $this->lng->txt('no'),1 => $this->lng->txt('yes'));

		$oAccessGUI->setTitle($this->lng->txt('paya_access'));
		$oAccessGUI->setOptions($access_option);
		$oAccessGUI->setValue($booking['access_granted']);
		$oAccessGUI->setPostVar('access');		
		$oForm->addItem($oAccessGUI);
		
		$oForm->addCommandButton('updateStatistic',$this->lng->txt('save'));
		$oForm->addCommandButton('deleteStatistic',$this->lng->txt('delete'));
		
		$this->tpl->setVariable('FORM',$oForm->getHTML());
		
		
	
/*	//Same output as in ilobjpaymentsettingsgui->statistics  
  	// show CUSTOMER_DATA if isset -> setting: save_user_address
  	if(ilPayMethods::isCustomerAddressEnabled($booking['b_pay_method']))
		{
			$oForm2 = new ilPropertyFormGUI();
			$oForm2->setId('cust_form');
			$oForm2->setTableWidth('50 %');		
			$oForm2->setTitle($frm_user);		
			
			// email
			$oEmailGUI = new ilNonEditableValueGUI($this->lng->txt('email'));
			$email = (!isset($tmp_user)) ? $this->lng->txt('user_deleted') : $tmp_user->getEmail();
			$oEmailGUI->setValue($email);
			$oForm2->addItem($oEmailGUI);	

			// street
			$oStreetGUI = new ilNonEditableValueGUI($this->lng->txt('street'));
			$oStreetGUI->setValue($booking['street']);
			$oForm2->addItem($oStreetGUI);
				
			// pobox
			$oPoBoxGUI = new ilNonEditableValueGUI($this->lng->txt('pay_bmf_po_box'));
			$oPoBoxGUI->setValue($booking['po_box']);
			$oForm2->addItem($oPoBoxGUI);	
				
			// zipcode
			$oPoBoxGUI = new ilNonEditableValueGUI($this->lng->txt('zipcode'));
			$oPoBoxGUI->setValue($booking['zipcode']);
			$oForm2->addItem($oPoBoxGUI);
					
			// city
			$oCityGUI = new ilNonEditableValueGUI($this->lng->txt('city'));
			$oCityGUI->setValue($booking['city']);
			$oForm2->addItem($oCityGUI);	
			
			// country
			$oCountryGUI = new ilNonEditableValueGUI($this->lng->txt('country'));
			$oCountryGUI->setValue($booking['country']);
			$oForm2->addItem($oCountryGUI);	
		}
		
		$this->tpl->setVariable('FORM_2',$oForm2->getHTML());
*/

		return true;
	}

	public function updateStatistic()
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

	public function deleteStatistic()
	{
		if(!isset($_GET['booking_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();

			return true;
		}

		$this->editStatistic(true);

		return true;
	}

	public function performDelete()
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

	public function showObjectSelector()
	{
		global $ilToolbar;
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.paya_object_selector.html",'Services/Payment');
		$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'showStatistics'));

		ilUtil::sendInfo($this->lng->txt("paya_select_object_to_sell"));

		$exp = new ilPaymentObjectSelector($this, "showObjectSelector");
		if (!$exp->handleCommand())
		{
			$this->tpl->setVariable("EXPLORER",$exp->getHTML());
		}
		

		return true;
	}

	public function searchUser()
	{
		global $ilToolbar;
		
		if(!isset($_GET['sell_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showObjectSelector();

			return false;
		}

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');

		$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'showObjectSelector'));
		
		$this->lng->loadLanguageModule('search');
		$this->ctrl->setParameter($this, "sell_id", $_GET["sell_id"]);
		
		$form_gui = new ilPropertyFormGUI();
		$form_gui->setFormAction($this->ctrl->getFormAction($this),'performSearch');
		$form_gui->setTitle($this->lng->txt('grp_search_members'));
		$form_gui->setId('search_form');
	
		$oTitle = new ilTextInputGUI($this->lng->txt('search_search_term'), 'search_str');
		$oTitle->setMaxLength(255);
		$oTitle->setSize(40);
		$oTitle->setValue($_POST['search_str']);
		$form_gui->addItem($oTitle);		
		
		// buttons
		$form_gui->addCommandButton('performSearch', $this->lng->txt('search'));
		$form_gui->addCommandButton('showStatistics', $this->lng->txt('cancel'));	
		
		$this->tpl->setVariable('FORM',$form_gui->getHTML());	
				return true;
	}

	public function performSearch()
	{
		global $ilToolbar;
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

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.main_view.html",'Services/Payment');
		$this->ctrl->setParameter($this, "sell_id", $_GET["sell_id"]);
	
		$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'searchUser'));	
				
		$counter = 0;
		$f_result = array();
		foreach($result as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user["id"],false))
			{
				continue;
			}
	//		$f_result[$counter]['user_id'] = ilUtil::formRadiobutton(0,"user_id",$user["id"]);
			$f_result[$counter]['user_id'] = $user["id"];
			$f_result[$counter]['login'] = $tmp_obj->getLogin();
			$f_result[$counter]['firstname'] = $tmp_obj->getFirstname();
			$f_result[$counter]['lastname'] = $tmp_obj->getLastname();
			
			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result);
	}

	public function addCustomer()
	{
		global $ilToolbar,$ilCtrl; 

		isset($_POST['sell_id']) ? $sell_id = $_POST['sell_id'] : $sell_id = $_GET['sell_id'];
		isset($_POST['user_id']) ? $user_id = $_POST['user_id'] : $user_id = $_GET['user_id'];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.main_view.html",'Services/Payment');
		$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'searchUser'));	
		$ilCtrl->setParameter($this, "sell_id", $sell_id);
		$ilCtrl->setParameter($this, "user_id", $user_id);

		$pObjectId = ilPaymentObject::_lookupPobjectId($sell_id);
		$obj = new ilPaymentObject($this->user_obj, $pObjectId);

		// get obj
		$tmp_obj = ilObjectFactory::getInstanceByRefId($sell_id, false);
		if($tmp_obj)
		{
			$tmp_object['title'] = $tmp_obj->getTitle();
		}
		else
		{
			$tmp_object['title'] = $this->lng->txt('object_not_found');
		}

		// get customer_obj
		$tmp_user = ilObjectFactory::getInstanceByObjId($user_id);
		// get vendor_obj
		$tmp_vendor = ilObjectFactory::getInstanceByObjId($obj->getVendorId());

		$oForm = new ilPropertyFormGUI();
		$oForm->setFormAction($ilCtrl->getFormAction($this, 'saveCustomer'));
	
		$oForm->setTitle($this->lng->txt($tmp_user->getFullname().' ['.$tmp_user->getLogin().']'));
		
		//transaction
		$oTransaction = new ilTextInputGUI();
		$oTransaction->setTitle($this->lng->txt('paya_transaction'));
		$oTransaction->setValue(ilUtil::prepareFormOutput($_POST['transaction'], true));
		$oTransaction->setPostVar('transaction');
		$oForm->addItem($oTransaction);
		
		//object
		$oObject = new ilNonEditableValueGUI($this->lng->txt('title'));
		$oObject->setValue($tmp_obj->getTitle());
		$oForm->addItem($oObject);
		
		//vendor
		$oVendor = new ilNonEditableValueGUI($this->lng->txt('paya_vendor'));
		$oVendor->setValue($tmp_vendor->getFullname().' ['.$tmp_vendor->getLogin().']');
		$oForm->addItem($oVendor);
		
		// pay methods
		$oPayMethods = new ilSelectInputGUI($this->lng->txt('paya_pay_method'), 'pay_method');
		$payOptions = ilPayMethods::getPayMethodsOptions(false);
	
		$oPayMethods->setOptions($payOptions);
		$oPayMethods->setValue($_POST['pay_method']);
		$oPayMethods->setPostVar('pay_method');
		$oForm->addItem($oPayMethods);	
		
		//duration
		$duration_options = array();	
		$price_obj = new ilPaymentPrices($pObjectId);

		$standard_prices = array();
		$extension_prices = array();
		$standard_prices = $price_obj->getPrices();
		$extension_prices = $price_obj->getExtensionPrices();

		$prices = array_merge($standard_prices, $extension_prices );

		if (is_array($prices))
		{
			$genSet = ilPaymentSettings::_getInstance();
			$currency_unit = $genSet->get('currency_unit');

			foreach($prices as $price)
			{
				switch($price['price_type'])
				{
					case ilPaymentPrices::TYPE_DURATION_MONTH:
						$txt_duration =
						$price['duration'].' '.$this->lng->txt('paya_months').' -> '.$price['price'].' '. $currency_unit;
						break;
					
					case ilPaymentPrices::TYPE_DURATION_DATE:
						include_once './Services/Calendar/classes/class.ilDatepresentation.php';
						$txt_duration =
							ilDatePresentation::formatDate(new ilDate($price['duration_from'], IL_CAL_DATE))
							.' - '.ilDatePresentation::formatDate(new ilDate($price['duration_until'], IL_CAL_DATE))
							." -> ".ilPaymentPrices::_getPriceString($price["price_id"]) .' '.$currency_unit;
						break;
					
					case ilPaymentPrices::TYPE_UNLIMITED_DURATION:
						$txt_duration =  $this->lng->txt('unlimited_duration').' -> '.$price['price'].' '. $currency_unit;
						break;
				}
				$txt_extension = '';
				if($price['extension'] == 1)
				{
					$txt_extension = ' ('.$this->lng->txt('extension_price').') ';
				}
				$duration_options[$price['price_id']] = $txt_duration.''.$txt_extension;
			}
		}
		
		$oDuration = new ilSelectInputGUI($this->lng->txt('duration'), 'duration');
		$oDuration->setOptions($duration_options);
		$oDuration->setValue($_POST['duration']);
		$oForm->addItem($oDuration);	

		//payed		
		$o_payed = new ilSelectInputGUI();
		$payed_option = array('1'=>$this->lng->txt('yes'),'0'=>$this->lng->txt('no'));

		$o_payed->setTitle($this->lng->txt('paya_payed'));
		$o_payed->setOptions($payed_option);
		$o_payed->setValue($_POST['payed']);
		$o_payed->setPostVar('payed');		
		$oForm->addItem($o_payed);	

		$o_access = new ilSelectInputGUI();
		$access_option = array('1'=>$this->lng->txt('yes'),'0'=>$this->lng->txt('no'));

		$o_access->setTitle($this->lng->txt('paya_access'));
		$o_access->setOptions($access_option);
		$o_access->setValue($_POST['access']);
		$o_access->setPostVar('access');
		$oForm->addItem($o_access);	

		$oForm->addCommandButton('saveCustomer',$this->lng->txt('save'));
		$oForm->addCommandButton('showStatistics', $this->lng->txt('cancel'));	
		
		$this->tpl->setVariable('FORM', $oForm->getHTML());
	}

	public function saveCustomer()
	{
		global $ilObjDataCache;


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
		$obj = new ilPaymentObject($this->user_obj, $pObjectId);

		$this->__initBookingObject();
		$transaction = ilInvoiceNumberPlaceholdersPropertyGUI::_generateInvoiceNumber($_GET["user_id"]);

		$this->booking_obj->setTransaction($transaction);
		$this->booking_obj->setTransactionExtern($_POST["transaction"]);
		$this->booking_obj->setPobjectId($pObjectId);
		$this->booking_obj->setCustomerId($_GET["user_id"]);
		$this->booking_obj->setVendorId($obj->getVendorId());
		$this->booking_obj->setPayMethod((int) $_POST["pay_method"]);
		$this->booking_obj->setOrderDate(time());

		$price = ilPaymentPrices::_getPrice($_POST["duration"]);

		$this->booking_obj->setDuration($price["duration"]);
		$this->booking_obj->setAccessExtension($price['extension']);

		switch((int)$price['price_type'])
		{
			case ilPaymentPrices::TYPE_UNLIMITED_DURATION:
				$this->booking_obj->setDuration(0);
				break;

			case ilPaymentPrices::TYPE_DURATION_DATE:
				$this->booking_obj->setAccessStartdate($price['duration_from']);
				$this->booking_obj->setAccessEnddate($price['duration_until']);
				$this->booking_obj->setDuration(0);
				break;

			default:
			case ilPaymentPrices::TYPE_DURATION_MONTH:
				$this->booking_obj->setDuration($price["duration"]);
				break;
		}
	
		$this->booking_obj->setPriceType($price['price_type']);
		$this->booking_obj->setPrice(ilPaymentPrices::_getPriceString($_POST["duration"]));
	
		$this->booking_obj->setAccess((int) $_POST['access']);
		$this->booking_obj->setPayed((int) $_POST['payed']);
		$this->booking_obj->setVoucher('');
			
		$obj_id = $ilObjDataCache->lookupObjId($obj->getRefId());
		$obj_type = $ilObjDataCache->lookupType($obj_id);
		$obj_title = $ilObjDataCache->lookupTitle($obj_id);

		include_once 'Services/Payment/classes/class.ilShopVatsList.php';
		$oVAT = new ilShopVats((int)$obj->getVatId());
		$obj_vat_rate = $oVAT->getRate();
		$obj_vat_unit = $obj->getVat($this->booking_obj->getPrice());

		$this->booking_obj->setObjectTitle($obj_title);
		$this->booking_obj->setVatRate($obj_vat_rate);
		$this->booking_obj->setVatUnit($obj_vat_unit);

		include_once './Services/Payment/classes/class.ilPaymentSettings.php';
		$genSet = ilPaymentSettings::_getInstance();
		$this->booking_obj->setCurrencyUnit( $genSet->get('currency_unit'));

		include_once './Services/Payment/classes/class.ilPayMethods.php';
		if(ilPayMethods::_EnabledSaveUserAddress((int) $_POST["pay_method"]) == 1)
		{
	
			/**
			 * @class $ilObjUser ilObjUser
			 */
			global $ilObjUser;	
			$user_id[] = $_GET["user_id"];

			$cust_obj = ilObjUser::_readUsersProfileData($user_id);

			$this->booking_obj->setStreet($cust_obj[$_GET["user_id"]]['street'],'');

			$this->booking_obj->setZipcode($cust_obj[$_GET["user_id"]]['zipcode']);
			$this->booking_obj->setCity($cust_obj[$_GET["user_id"]]['city']);
			$this->booking_obj->setCountry($cust_obj[$_GET["user_id"]]['country']);
		}			
		
		if($this->booking_obj->add())
		{
			// add purchased item to desktop
			ilShopUtils::_addPurchasedObjToDesktop($obj, $this->booking_obj->getCustomerId());

            // autosubscribe user if purchased object is a course
            if($obj_type == 'crs')
            {
                ilShopUtils::_assignPurchasedCourseMemberRole($obj, $this->booking_obj->getCustomerId());
			}

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
	public function __showStatisticTable($a_result_set)
	{		
		$tbl = new ilShopTableGUI($this);
		$tbl->setTitle($this->lng->txt("bookings"));
		$tbl->setId('tbl_bookings');
		$tbl->setRowTemplate("tpl.shop_statistics_row.html", "Services/Payment");

		$tbl->addColumn($this->lng->txt('paya_transaction'), 'transaction', '10%');
		$tbl->addColumn($this->lng->txt('title'), 'object_title', '10%');
		$tbl->addColumn($this->lng->txt('paya_vendor'), 'vendor', '10%');
		$tbl->addColumn($this->lng->txt('paya_customer'), 'customer', '10%');
		$tbl->addColumn($this->lng->txt('paya_order_date'), 'order_date', '10%');
		$tbl->addColumn($this->lng->txt('duration'), 'duration', '10%');
		$tbl->addColumn($this->lng->txt('price_a'), 'price', '5%');
		$tbl->addColumn($this->lng->txt('paya_coupons_coupon'), 'discount', '5%');
		$tbl->addColumn($this->lng->txt('paya_payed_access'), 'payed_access', '5%');
		$tbl->addColumn($this->lng->txt('edit'), 'edit', '5%');
		$tbl->setData($a_result_set);

		$this->tpl->setVariable('TABLE', $tbl->getHTML());

		return true;
	}

	public function __initBookingObject()
	{
		include_once './Services/Payment/classes/class.ilPaymentBookings.php';

		$this->booking_obj = new ilPaymentBookings($this->user_obj->getId());
	}

	public function __search($a_search_string)
	{
		include_once("./Services/Search/classes/class.ilSearch.php");

		$this->lng->loadLanguageModule("content");

		$search = new ilSearch($this->user_obj->getId());
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
	public function __showSearchUserTable($a_result_set)
	{
		$this->ctrl->setParameter($this, "sell_id", $_GET["sell_id"]);

		$tbl = new ilShopTableGUI($this);
		$tbl->setTitle($this->lng->txt("users"));
		$tbl->setId('tbl_users_search');
		$tbl->setRowTemplate("tpl.shop_users_row.html", "Services/Payment");
		$tbl->addColumn('', 'user_id', '1%', true);
		$tbl->addColumn($this->lng->txt('login'), 'login', '10%');
		$tbl->addColumn($this->lng->txt('firstname'), 'firstname', '10%');
		$tbl->addColumn($this->lng->txt('lastname'), 'lastname', '10%');

		$tbl->addMultiCommand('addCustomer', $this->lng->txt('add'));
		$tbl->setData($a_result_set);

		$this->tpl->setVariable('TABLE', $tbl->getHTML());
		return true;
	}
	
	public function performDeleteDeassignCrs()
	{
		include_once './Services/Payment/classes/class.ilShopUtils.php';
			
		if(!isset($_GET['booking_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();

			return true;
		}

		$this->__initBookingObject();
		$bookings = $this->booking_obj->getBookings();
		$booking = $bookings[(int) $_GET['booking_id']];
		
		$pobject_data = ilPaymentObject::_getObjectData($booking['pobject_id']);
		ilShopUtils::_deassignPurchasedCourseMemberRole($pobject_data['ref_id'], $booking['customer_id']);	
		
		$this->booking_obj->setBookingId((int) $_GET['booking_id']);
		if(!$this->booking_obj->delete())
		{
			die('Error deleting booking');
		}
		ilUtil::sendInfo($this->lng->txt('pay_deleted_booking'));

		$this->showStatistics();

		return true;		
	}
}
?>