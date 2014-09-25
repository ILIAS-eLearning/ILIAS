<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Payment/classes/class.ilShopBaseGUI.php';
//include_once './Services/Payment/classes/class.ilPaymentCurrency.php';
include_once './Services/Payment/classes/class.ilShopTableGUI.php';

/**
* Class ilShopBoughtObjectsGUI
*
* @author Michael Jansen <mjansen@databay.de>
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id:$
* 
* @ingroup ServicesPayment
*  
*/
class ilShopBoughtObjectsGUI extends ilShopBaseGUI
{
	public $bookings_obj = null;
	private $user_obj;

//	private $psc_obj = null;

	public function __construct($user_obj)
	{
		parent::__construct();

		$this->user_obj = $user_obj;
	}
	
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch ($this->ctrl->getNextClass($this))
		{
 			 case 'showBillHistory':
				$this->$cmd();
				break;

			default:
				$this->prepareOutput();
				if(!$cmd = $this->ctrl->getCmd())
				{
					$cmd = 'showItems';
				}
				$this->$cmd();
				break;
		}
	}

 	protected function buildSubTabs()
	{
		global $ilTabs;
		
		$ilTabs->addSubTabTarget('paya_buyed_objects', $this->ctrl->getLinkTarget($this, 'showItems'), '', '', '','showItems');
		$ilTabs->addSubTabTarget('paya_bill_history', $this->ctrl->getLinkTarget($this, 'showBillHistory'), '', '', '','showBillHistory');
	}	

	public function showBillHistory()
	{	
		global $ilTabs;
		
		include_once "./Services/Repository/classes/class.ilRepositoryExplorer.php";
	
		$ilTabs->setSubTabActive('paya_bill_history');
		
		$this->initBookingsObject();
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');

		if(!count($bookings = ilPaymentBookings::getBookingsOfCustomer($this->user_obj->getId())))
		{
			ilUtil::sendInfo($this->lng->txt('pay_not_buyed_any_object'));
			return true;
		}
		
		$counter = 0;
				
		foreach($bookings as $booking)
		{
						
			$f_result[$counter]['transaction'] = "<a href=\"".$this->ctrl->getLinkTarget($this, "createBill")."&transaction=".$booking['transaction']."\">".$booking['transaction'].".pdf</a>";

			$f_result[$counter]['order_date'] = ilDatePresentation::formatDate(new ilDateTime($booking['order_date'], IL_CAL_UNIX));
	
			++$counter;
		}
		return $this->showBillHistoryTable($f_result);
	}
	
	private function showBillHistoryTable($a_result_set)
	{
		$this->ctrl->setParameter($this,'cmd','showBillHistory');
		$tbl = new ilShopTableGUI($this);
		$tbl->setId('tbl_bill_history');
		$tbl->setTitle($this->lng->txt("paya_bill_history"));
		$tbl->setRowTemplate("tpl.shop_statistics_row.html", "Services/Payment");

		$tbl->addColumn($this->lng->txt('paya_transaction'), 'transaction', '10%');
		$tbl->addColumn($this->lng->txt('paya_order_date'), 'order_date', '10%');

		$tbl->setData($a_result_set);

		$this->tpl->setVariable('TABLE', $tbl->getHTML());
		return true;
	}
	
	function createBill()
	{
		global $tpl,$ilObjDataCache;
		
		$customer=$this->user_obj;
		$transaction = $_GET['transaction'];

//		$total_price = 0;
//		$total_vat = 0;
		$i = 0;		

		include_once './Services/UICore/classes/class.ilTemplate.php';
		include_once './Services/Utilities/classes/class.ilUtil.php';
		include_once './Services/Payment/classes/class.ilPaymentSettings.php';

		$genSet = ilPaymentSettings::_getInstance();
		$currency = $genSet->get('currency_unit');

		$user_id = $this->user_obj->getId();

		$bookings = ilPaymentBookings::__readBillByTransaction($user_id,$transaction);
		if($bookings[$i]['street'] == NULL) 	$bookings[$i]['street'] = nl2br(utf8_decode($customer->getStreet()));
		if($bookings[$i]['zipcode'] == NULL)	$bookings[$i]['zipcode'] = nl2br(utf8_decode($customer->getZipcode()));
		if($bookings[$i]['city'] == NULL)		$bookings[$i]['city'] = nl2br(utf8_decode($customer->getCity()));
		if($bookings[$i]['country'] == NULL)
		{
			$bookings[$i]['country'] = nl2br(utf8_decode($customer->getCountry()));
		}

		if(2 == strlen($bookings[$i]['country']))
		{
			$this->lng->loadLanguageModule('meta');
			$bookings[$i]['country'] = utf8_decode($this->lng->txt('meta_c_'.strtoupper($bookings[$i]['country'])));
		}
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bill.html','Services/Payment');		
		$tpl = new ilTemplate('tpl.pay_bill.html', true, true, 'Services/Payment');
		
		if($tpl->placeholderExists('HTTP_PATH'))
		{
			$http_path = ilUtil::_getHttpPath();
			$tpl->setVariable('HTTP_PATH', $http_path);
		}
		ilDatePresentation::setUseRelativeDates(false);
		$tpl->setVariable('DATE', utf8_decode(ilDatePresentation::formatDate(new ilDate($bookings[$i]['order_date'], IL_CAL_UNIX))));
		$tpl->setVariable('TXT_CREDIT', utf8_decode($this->lng->txt('credit')));
		$tpl->setVariable('TXT_DAY_OF_SERVICE_PROVISION',$this->lng->txt('day_of_service_provision'));
		include_once './Services/Payment/classes/class.ilPayMethods.php';
		$str_paymethod = ilPayMethods::getStringByPaymethod($bookings[$i]['b_pay_method']);
		if(strlen(trim($bookings[$i]['transaction_extern'])))
		{
			$tpl->setVariable('TXT_EXTERNAL_BILL_NO', str_replace('%s',$str_paymethod,utf8_decode($this->lng->txt('external_bill_no'))));
			$tpl->setVariable('EXTERNAL_BILL_NO', $bookings[$i]['transaction_extern']);
		}
		$tpl->setVariable('TXT_POSITION',$this->lng->txt('position'));
		$tpl->setVariable('TXT_AMOUNT',$this->lng->txt('amount'));
		$tpl->setVariable('TXT_UNIT_PRICE', utf8_decode($this->lng->txt('unit_price')));

		$tpl->setVariable('VENDOR_ADDRESS', nl2br(utf8_decode($genSet->get('address'))));
		$tpl->setVariable('VENDOR_ADD_INFO', nl2br(utf8_decode($genSet->get('add_info'))));
		$tpl->setVariable('VENDOR_BANK_DATA', nl2br(utf8_decode($genSet->get('bank_data'))));
		$tpl->setVariable('TXT_BANK_DATA', utf8_decode($this->lng->txt('pay_bank_data')));


		$tpl->setVariable('CUSTOMER_FIRSTNAME',utf8_decode($customer->getFirstName()));// $customer['vorname']);
		$tpl->setVariable('CUSTOMER_LASTNAME', utf8_decode($customer->getLastName())); //$customer['nachname']);
		if($bookings['po_box']== '')
		{
			$tpl->setVariable('CUSTOMER_STREET',utf8_decode( $bookings[$i]['street']));
		}
		else
		{
			$tpl->setVariable('CUSTOMER_STREET', utf8_decode($bookings[$i]['po_box']));
		}
		$tpl->setVariable('CUSTOMER_ZIPCODE', utf8_decode($bookings[$i]['zipcode']));
		$tpl->setVariable('CUSTOMER_CITY', utf8_decode($bookings[$i]['city']));
		$tpl->setVariable('CUSTOMER_COUNTRY', utf8_decode($bookings[$i]['country']));

		$tpl->setVariable('BILL_NO', $transaction);

		$tpl->setVariable('TXT_BILL', utf8_decode($this->lng->txt('pays_bill')));
		$tpl->setVariable('TXT_BILL_NO', utf8_decode($this->lng->txt('pay_bill_no')));
		$tpl->setVariable('TXT_DATE', utf8_decode($this->lng->txt('date')));

		$tpl->setVariable('TXT_ARTICLE', utf8_decode($this->lng->txt('pay_article')));
		$tpl->setVariable('TXT_VAT_RATE', utf8_decode($this->lng->txt('vat_rate')));
		$tpl->setVariable('TXT_VAT_UNIT', utf8_decode($this->lng->txt('vat_unit')));		
		$tpl->setVariable('TXT_PRICE', utf8_decode($this->lng->txt('price_a')));

		for ($i = 0; $i < count($bookings[$i]); $i++)
		{
			$tmp_pobject = new ilPaymentObject($this->user_obj, $bookings[$i]['pobject_id']);

			$obj_id = $ilObjDataCache->lookupObjId($bookings[$i]['ref_id']);
			$obj_type = $ilObjDataCache->lookupType($obj_id);
			
			$tpl->setCurrentBlock('loop');
			$tpl->setVariable('LOOP_POSITION', $i+1);
			$tpl->setVariable('LOOP_AMOUNT', '1');
			$tpl->setVariable('LOOP_TXT_PERIOD_OF_SERVICE_PROVISION', utf8_decode($this->lng->txt('period_of_service_provision')));

			$tpl->setVariable('LOOP_OBJ_TYPE', utf8_decode($this->lng->txt($obj_type)));
			$tpl->setVariable('LOOP_TITLE', utf8_decode($bookings[$i]['object_title']) . $assigned_coupons);
			$tpl->setVariable('LOOP_TXT_ENTITLED_RETRIEVE', utf8_decode($this->lng->txt('pay_entitled_retrieve')));
			
			if( $bookings[$i]['duration'] == 0 && $bookings[$i]['access_enddate'] == NULL)
			{
				$tpl->setVariable('LOOP_DURATION', utf8_decode($this->lng->txt('unlimited_duration')));
			}
			else
			{
				$access_startdate = utf8_decode(ilDatePresentation::formatDate(new ilDate($bookings[$i]['access_startdate'], IL_CAL_DATETIME)));
				$access_enddate = utf8_decode(ilDatePresentation::formatDate(new ilDate($bookings[$i]['access_enddate'], IL_CAL_DATETIME)));

				$tpl->setVariable('LOOP_DURATION', 
						$access_startdate.' - '.$access_enddate.' /  '.
						$bookings[$i]['duration'] . ' ' . utf8_decode($this->lng->txt('paya_months')));
			}
			// old one
			$tpl->setVariable('LOOP_VAT_RATE',number_format($bookings[$i]['vat_rate'], 2, ',', '.').' %');
			$tpl->setVariable('LOOP_VAT_UNIT', number_format($bookings[$i]['vat_unit'], 2, ',', '.').' '.$currency);
			$tpl->setVariable('LOOP_UNIT_PRICE',number_format($bookings[$i]['price'], 2, ',', '.').' '.$currency);
			$tpl->setVariable('LOOP_PRICE',number_format($bookings[$i]['price'], 2, ',', '.').' '.$currency);
			$tpl->parseCurrentBlock('loop');
			
			$bookings['total'] += (float)$bookings[$i]['price'];
			$bookings['total_vat']+= (float)$bookings[$i]['vat_unit'];
			$bookings['total_discount'] +=(float) $bookings[$i]['discount'];
			unset($tmp_pobject);

			$sub_total_amount = $bookings['total'];
		}

		$bookings['total'] += $bookings['total_discount'];
		if($bookings['total_discount'] < 0)
		{
			$tpl->setCurrentBlock('cloop');

			$tpl->setVariable('TXT_SUBTOTAL_AMOUNT', utf8_decode($this->lng->txt('pay_bmf_subtotal_amount')));
			$tpl->setVariable('SUBTOTAL_AMOUNT', number_format($sub_total_amount, 2, ',', '.') . ' ' . $currency);

			$tpl->setVariable('TXT_COUPON', utf8_decode($this->lng->txt('paya_coupons_coupon') . ' ' . $coupon['pcc_code']));
			$tpl->setVariable('BONUS', number_format($bookings['total_discount'], 2, ',', '.') . ' ' . $currency);
			$tpl->parseCurrentBlock();
		}

		if ($bookings['total'] < 0)
		{			
			$bookings['total'] = 0.00;
		//	$bookings['total_vat'] = 0.0;
		}
		$total_net_price = $sub_total_amount-$bookings['total_vat'];

		$tpl->setVariable('TXT_TOTAL_NETPRICE', utf8_decode($this->lng->txt('total_netprice')));
		$tpl->setVariable('TOTAL_NETPRICE', number_format($total_net_price, 2, ',', '.') . ' ' . $currency);

		$tpl->setVariable('TXT_TOTAL_AMOUNT', utf8_decode($this->lng->txt('pay_bmf_total_amount')));
		$tpl->setVariable('TOTAL_AMOUNT', number_format($bookings['total'], 2, ',', '.') . ' ' . $currency);
		if ($bookings['total_vat'] > 0)
		{
			$tpl->setVariable('TOTAL_VAT',number_format( $bookings['total_vat'], 2, ',', '.') . ' ' .$currency);
			$tpl->setVariable('TXT_TOTAL_VAT', utf8_decode($this->lng->txt('plus_vat')));
		}
		if(1 == $bookings[0]['b_pay_method'])
		{
			$tpl->setVariable('TXT_PAYMENT_TYPE', utf8_decode($this->lng->txt('pay_unpayed_bill')));
		}
		else
		{
			$tpl->setVariable('TXT_PAYMENT_TYPE', utf8_decode($this->lng->txt('pay_payed_bill')));
		}

		if (!@file_exists($genSet->get('pdf_path')))
		{
			ilUtil::makeDir($genSet->get('pdf_path'));
		}

		$file_name = time();
		if (@file_exists($genSet->get('pdf_path')))
		{		
			ilUtil::html2pdf($tpl->get(), $genSet->get('pdf_path') . '/' . $file_name . '.pdf');
		}

		if (@file_exists($genSet->get('pdf_path') . '/' . $file_name . '.pdf'))
		{
			 ilUtil::deliverFile(
			 	$genSet->get('pdf_path') . '/' . $file_name . '.pdf',
			 	$transaction . '.pdf',
			 	$a_mime = 'application/pdf'
			 );
		}

		@unlink($genSet->get('pdf_path') . '/' . $file_name . '.html');
		@unlink($genSet->get('pdf_path') . '/' . $file_name . '.pdf');
	}
		
	protected function prepareOutput()
	{
		global $ilTabs;		
		
		parent::prepareOutput();
		
		$ilTabs->setTabActive('paya_buyed_objects');
		$ilTabs->setSubTabActive('paya_buyed_objects');			
	}	
	public function showItems()
	{
		include_once "./Services/Repository/classes/class.ilRepositoryExplorer.php";

		$this->initBookingsObject();

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');

		if(!count($bookings = ilPaymentBookings::getBookingsOfCustomer($this->user_obj->getId())))
		{
			ilUtil::sendInfo($this->lng->txt('pay_not_buyed_any_object'));

			return true;
		}
		
		$counter = 0;
				
		foreach($bookings as $booking)
		{
			$tmp_obj = ilObjectFactory::getInstanceByRefId($booking['ref_id'], false);
			$tmp_vendor = ilObjectFactory::getInstanceByObjId($booking['b_vendor_id'], false);
			$tmp_purchaser = ilObjectFactory::getInstanceByObjId($booking['customer_id'], false);

			$transaction = $booking['transaction'];
			
			include_once './Services/Payment/classes/class.ilPayMethods.php';
			$str_paymethod = ilPayMethods::getStringByPaymethod($booking['b_pay_method']);	
			$transaction .= " (" . $str_paymethod . ")";
			$f_result[$counter]['transaction'] = $transaction;

			if($tmp_obj)
			{
				$obj_link = ilRepositoryExplorer::buildLinkTarget($booking['ref_id'],$tmp_obj->getType());
				$obj_target = ilRepositoryExplorer::buildFrameTarget($tmp_obj->getType(),$booking['ref_id'],$tmp_obj->getId());
				$f_result[$counter]['object_title'] = "<a href=\"".$obj_link."\" target=\"".$obj_target."\">".$tmp_obj->getTitle()."</a>";
			}
			else
			{
				$obj_link = '';
				$obj_target = '';
				$f_result[$counter]['object_title'] = $booking['object_title'].'<br> ('.$this->lng->txt('object_deleted').')';
			}
			$f_result[$counter]['vendor'] = '['.$tmp_vendor->getLogin().']';
			$f_result[$counter]['customer'] = '['.$tmp_purchaser->getLogin().']';
			$f_result[$counter]['order_date'] = ilDatePresentation::formatDate(new ilDateTime($booking['order_date'], IL_CAL_UNIX));
//todo check for accessduration!!
			if($booking['duration'] == 0 && $booking['access_enddate'] == NULL)
			{
				$f_result[$counter]['duration'] = $this->lng->txt("unlimited_duration");
			}
			else
			{
				if($booking['duration'] > 0)
				{
					$f_result[$counter]['duration'] = $booking['duration'].' '.$this->lng->txt('paya_months');
				}
				$f_result[$counter]['duration'] .= ilDatePresentation::formatDate(new ilDateTime($booking['access_startdate'], IL_CAL_DATETIME))
						.' - '.ilDatePresentation::formatDate(new ilDateTime($booking['access_enddate'], IL_CAL_DATETIME));
			}
			$f_result[$counter]['price'] = $booking['price'].' '.$booking['currency_unit'];
			$f_result[$counter]['discount'] = ($booking['discount'] != '' ? (round($booking['discount'], 2).' '.$booking['currency_unit']) : '&nbsp;');

			$payed_access = $booking['payed'] ? 
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$payed_access .= '/';
			$payed_access .= $booking['access_granted'] ?
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$f_result[$counter]['payed_access'] = $payed_access;

			unset($tmp_obj);
			unset($tmp_vendor);
			unset($tmp_purchaser);

			++$counter;
		}
		return $this->showStatisticTable($f_result);
	}

	private function showStatisticTable($a_result_set)
	{
		$tbl = new ilShopTableGUI($this);
		$tbl->setTitle($this->lng->txt("paya_buyed_objects"));
		$tbl->setId('tbl_bought_objects');
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

		$tbl->setData($a_result_set);

		$this->tpl->setVariable('TABLE', $tbl->getHTML());

		return true;
	}

	private function initBookingsObject()
	{
		include_once './Services/Payment/classes/class.ilPaymentBookings.php';

		$this->bookings_obj = new ilPaymentBookings();
		
		return true;
	}
}
?>