<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* Class ilPurchaseBillGUI
*
* @author Nadia Krzywon
* @version $Id: class.ilPurchaseBillGUI.php 
*
* @package core
*/

include_once './payment/classes/class.ilPaymentShoppingCart.php';
include_once './payment/classes/class.ilPaymentShoppingCartGUI.php';
include_once './payment/classes/class.ilPaymentCoupons.php';

class ilPurchaseBillGUI
{
	var $ctrl;
	var $tpl;

	var $psc_obj = null;
	var $user_obj = null;
	
	var $coupon_obj = null;
	var $error;

	private $totalVat = 0;

	function ilPurchaseBillGUI(&$user_obj)
	{
		global $ilias, $ilDB, $lng, $tpl, $rbacsystem, $ilCtrl,  $ilTabs;

		$this->ilias = $ilias;
		$this->db = $ilDB;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;

	//	$this->psc_obj =& new ilPaymentShoppingCart($this->user_obj);
		// Get user object
		$this->user_obj = $user_obj;
		
		
		$this->coupon_obj = new ilPaymentCoupons($this->user_obj);
		
		if (!is_array($_SESSION['bill']['personal_data']))
		{
			$_SESSION['bill']['personal_data']['vorname'] = $this->user_obj->getFirstname();
			$_SESSION['bill']['personal_data']['nachname'] = $this->user_obj->getLastname();
			if (strpos('_' . $this->user_obj->getStreet(), ' ') > 0)
			{
				$houseNo = substr($this->user_obj->getStreet(), strrpos($this->user_obj->getStreet(), ' ')+1);
				$street = substr($this->user_obj->getStreet(), 0, strlen($this->user_obj->getStreet())-(strlen($houseNo)+1));
				$_SESSION['bill']['personal_data']['strasse'] = $street;
				$_SESSION['bill']['personal_data']['hausNr'] = $houseNo;
			}
			else
			{
				$_SESSION['bill']['personal_data']['strasse'] = $this->user_obj->getStreet();
				$_SESSION['bill']['personal_data']['hausNr'] = '';
			}
			$_SESSION['bill']['personal_data']['postfach'] = '';
			$_SESSION['bill']['personal_data']['PLZ'] = $this->user_obj->getZipcode();
			$_SESSION['bill']['personal_data']['ort'] = $this->user_obj->getCity();
			$_SESSION['bill']['personal_data']['land'] = $this->__getCountryCode($this->user_obj->getCountry());
			$_SESSION['bill']['personal_data']['EMailAdresse'] = $this->user_obj->getEmail();
			$_SESSION['bill']['personal_data']['sprache'] = $this->user_obj->getLanguage();
		}
		
		if (!is_array($_SESSION['coupons']['bill']))
		{
			$_SESSION['coupons']['bill'] = array();
		}

		$this->__loadTemplate();
		$this->error = '';
		$this->lng->loadLanguageModule('payment');
		
		$ilTabs->clearTargets();
		$ilTabs->clearSubTabs();
	}
	
	function cancel()
	{
		ilUtil::redirect('./payment.php');
	}

	function showPersonalData()
	{
		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);

		if(!count($items = $this->psc_obj->getEntries(PAY_METHOD_BILL)))
		{
			$this->tpl->setVariable('HEADER',$this->lng->txt('pay_bmf_your_order'));
			$this->tpl->touchBlock('stop_floating');
			ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));
		}
		else
		{
			$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bmf_personal_data.html','payment');

			$this->tpl->setVariable('PERSONAL_DATA_FORMACTION',$this->ctrl->getFormAction($this));

			// set table header
			$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_pays_b.gif'));
			$this->tpl->setVariable('ALT_IMG',$this->lng->txt('obj_usr'));
			//$this->tpl->setVariable('HEADER',$this->lng->txt('pay_step1'));
			$this->tpl->setVariable('HEADER',$this->lng->txt('pay_bmf_your_order'));
			$this->tpl->setVariable('TITLE',$this->lng->txt('pay_bmf_personal_data'));
			$this->tpl->setVariable('DESCRIPTION',$this->lng->txt('pay_bmf_description_personal_data'));
			$this->tpl->touchBlock('stop_floating');
			$this->tpl->setVariable('TXT_CLOSE_WINDOW',$this->lng->txt('close_window'));
	
			// set plain text variables
			$this->tpl->setVariable('TXT_FIRSTNAME',$this->lng->txt('firstname'));
			$this->tpl->setVariable('TXT_LASTNAME',$this->lng->txt('lastname'));
			$this->tpl->setVariable('TXT_STREET',$this->lng->txt('street'));
			$this->tpl->setVariable('TXT_HOUSE_NUMBER',$this->lng->txt('pay_bmf_house_number'));
			$this->tpl->setVariable('TXT_OR',$this->lng->txt('pay_bmf_or'));
			$this->tpl->setVariable('TXT_PO_BOX',$this->lng->txt('pay_bmf_po_box'));
			$this->tpl->setVariable('TXT_ZIPCODE',$this->lng->txt('zipcode'));
			$this->tpl->setVariable('TXT_CITY',$this->lng->txt('city'));
			$this->tpl->setVariable('TXT_COUNTRY',$this->lng->txt('country'));
			$this->tpl->setVariable('TXT_EMAIL',$this->lng->txt('email'));
	
			$this->tpl->setVariable('INPUT_VALUE',ucfirst($this->lng->txt('next')));
			$this->tpl->setVariable('CANCEL',$this->lng->txt('cancel'));
	
			// fill defaults
	
			$this->error != '' && isset($_POST['country']) ? $this->__showCountries($this->tpl, $_POST['country']) 
									: $this->__showCountries($this->tpl, $_SESSION['bill']['personal_data']['land']);
			$this->tpl->setVariable('FIRSTNAME', $this->user_obj->getFirstname());
			$this->tpl->setVariable('LASTNAME', $this->user_obj->getLastname());
			$this->tpl->setVariable('STREET',
									$this->error != '' && isset($_POST['street'])
									? ilUtil::prepareFormOutput($_POST['street'],true)
									: ilUtil::prepareFormOutput($_SESSION['bill']['personal_data']['strasse'],true));
			$this->tpl->setVariable('HOUSE_NUMBER',
									$this->error != '' && isset($_POST['house_number'])
									? ilUtil::prepareFormOutput($_POST['house_number'],true)
									: ilUtil::prepareFormOutput($_SESSION['bill']['personal_data']['hausNr'],true));
			$this->tpl->setVariable('PO_BOX',
									$this->error != '' && isset($_POST['po_box'])
									? ilUtil::prepareFormOutput($_POST['po_box'],true)
									: ilUtil::prepareFormOutput($_SESSION['bill']['personal_data']['postfach'],true));
			$this->tpl->setVariable('ZIPCODE',
									$this->error != '' && isset($_POST['zipcode'])
									? ilUtil::prepareFormOutput($_POST['zipcode'],true)
									: ilUtil::prepareFormOutput($_SESSION['bill']['personal_data']['PLZ'],true));
			$this->tpl->setVariable('CITY',
									$this->error != '' && isset($_POST['city'])
									? ilUtil::prepareFormOutput($_POST['city'],true)
									: ilUtil::prepareFormOutput($_SESSION['bill']['personal_data']['ort'],true));
			$this->tpl->setVariable('EMAIL', $this->user_obj->getEmail());

		}
	}

	function getPersonalData()
	{	

		if ($_SESSION['bill']['personal_data']['vorname'] == '' ||
			$_SESSION['bill']['personal_data']['nachname'] == '' ||
			$_POST['zipcode'] == '' ||
			$_POST['city'] == '' ||
			$_POST['country'] == '' ||
			$_SESSION['bill']['personal_data']['EMailAdresse'] == '')
		{

			$this->error = $this->lng->txt('pay_bmf_personal_data_not_valid');
			ilUtil::sendInfo($this->error);
			$this->showPersonalData();
			return;
		}
		
		if (($_POST['street'] == '' && $_POST['house_number'] == '' && $_POST['po_box'] == '') ||
			(($_POST['street'] != '' || $_POST['house_number'] != '') && $_POST['po_box'] != '') ||
			($_POST['street'] != '' && $_POST['house_number'] == '') ||
			($_POST['street'] == '' && $_POST['house_number'] != ''))
		{		
			$this->error = $this->lng->txt('pay_bmf_street_or_pobox');
			ilUtil::sendInfo($this->error);
			$this->showPersonalData();
			return;
		}

		$_SESSION['bill']['personal_data']['vorname'] = $this->user_obj->getFirstname();
		$_SESSION['bill']['personal_data']['nachname'] = $this->user_obj->getLastname();
		$_SESSION['bill']['personal_data']['strasse'] = $_POST['street'];
		$_SESSION['bill']['personal_data']['hausNr'] = $_POST['house_number'];
		$_SESSION['bill']['personal_data']['postfach'] = $_POST['po_box'];
		$_SESSION['bill']['personal_data']['PLZ'] = $_POST['zipcode'];
		$_SESSION['bill']['personal_data']['ort'] = $_POST['city'];
		$_SESSION['bill']['personal_data']['land'] = $_POST['country'];

		$_SESSION['bill']['personal_data']['EmailAdresse'] = $this->user_obj->getEmail();
		$_SESSION['bill']['personal_data']['sprache'] = $this->user_obj->getLanguage();

		$this->error = '';
		$this->showBillConfirm();

	}

	function showBillConfirm()
	{
		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);

		if(!count($items = $this->psc_obj->getEntries(PAY_METHOD_BILL)))
		{
			$this->tpl->setVariable('HEADER',$this->lng->txt('pay_bmf_your_order'));
			$this->tpl->touchBlock('stop_floating');
			ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));
		}
		else
		{

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bill_confirm.html','payment');
		
		$this->__showShoppingCart();

		$this->tpl->setVariable('BILL_CONFIRM_FORMACTION',$this->ctrl->getFormAction($this));

		// set table header
		$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_pays_b.gif'));
		$this->tpl->setVariable('ALT_IMG',$this->lng->txt('obj_usr'));
		$this->tpl->touchBlock('stop_floating');
		$this->tpl->setVariable('TXT_CLOSE_WINDOW',$this->lng->txt('close_window'));

		// set plain text variables
		$this->tpl->setVariable('TXT_TERMS_CONDITIONS',$this->lng->txt('pay_bmf_terms_conditions'));
		$this->tpl->setVariable('TXT_TERMS_CONDITIONS_READ',$this->lng->txt('pay_bmf_terms_conditions_read'));
		$this->tpl->setVariable('TXT_TERMS_CONDITIONS_SHOW',$this->lng->txt('pay_bmf_terms_conditions_show'));
		$this->tpl->setVariable('LINK_TERMS_CONDITIONS','./payment.php?view=conditions');
		$this->tpl->setVariable('TXT_PASSWORD',$this->lng->txt('password'));
		$this->tpl->setVariable('TXT_CONFIRM_ORDER',$this->lng->txt('pay_confirm_order'));

		$this->tpl->setVariable('INPUT_VALUE',$this->lng->txt('pay_send_order'));
		$this->tpl->setVariable('CANCEL',$this->lng->txt('cancel'));
		if ($this->error != '' &&
			isset($_POST['terms_conditions']))
		{
			$this->tpl->setVariable('TERMS_CONDITIONS_' . strtoupper($_POST['terms_conditions']), ' checked') ;
		}
		if ($this->error != '' &&
			isset($_POST['password']))
		{
			$this->tpl->setVariable('PASSWORD', ilUtil::prepareFormOutput($_POST['password'],true));
		}

		// Button
		$this->tpl->addBlockfile('BUTTONS', 'buttons', 'tpl.buttons.html');
		$this->tpl->setCurrentBlock('btn_cell');
		$this->tpl->setVariable('BTN_LINK', $this->ctrl->getLinkTarget($this, 'showPersonalData'));
		$this->tpl->setVariable('BTN_TXT', $this->lng->txt('pay_bmf_back'));
		$this->tpl->parseCurrentBlock('btn_cell');

		}
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
					$cmd = 'showPersonalData';
				}
				$this->$cmd();
				break;
		}
	}
	
	function __saveTransaction()
	{
		global $ilias, $ilUser, $ilObjDataCache;
		
		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);
		
		$sc = $this->psc_obj->getShoppingCart(PAY_METHOD_BILL);
	
		$this->psc_obj->clearCouponItemsSession();		

		if (is_array($sc) && count($sc) > 0)
		{
			include_once './payment/classes/class.ilPaymentBookings.php';
			$book_obj =& new ilPaymentBookings($this->usr_obj);
			
			for ($i = 0; $i < count($sc); $i++)
			{
				if (!empty($_SESSION['coupons']['bill']))
				{									
					$sc[$i]['math_price'] = (float) $sc[$i]['betrag'];
								
					$tmp_pobject =& new ilPaymentObject($this->user_obj, $sc[$i]['pobject_id']);	
													
					foreach ($_SESSION['coupons']['bill'] as $key => $coupon)
					{					
						$this->coupon_obj->setId($coupon['pc_pk']);
						$this->coupon_obj->setCurrentCoupon($coupon);
						
						if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
						{
							$_SESSION['coupons']['bill'][$key]['total_objects_coupon_price'] += (float) $sc[$i]['betrag'];
							$_SESSION['coupons']['bill'][$key]['items'][] = $sc[$i];
						}								
					}
					
					unset($tmp_pobject);
				}
			}
			
			$coupon_discount_items = $this->psc_obj->calcDiscountPrices($_SESSION['coupons']['bill']);			

			for ($i = 0; $i < count($sc); $i++)
			{
				$pobjectData = ilPaymentObject::_getObjectData($sc[$i]['pobject_id']);
				$pobject =& new ilPaymentObject($this->user_obj,$sc[$i]['pobject_id']);

				$inst_id_time = $ilias->getSetting('inst_id').'_'.$ilUser->getId().'_'.substr((string) time(),-3);
				$transaction = $inst_id_time.substr(md5(uniqid(rand(), true)), 0, 4);
				$price = $sc[$i]['betrag'];
				$bonus = 0.0;
				
				if (array_key_exists($sc[$i]['pobject_id'], $coupon_discount_items))
				{
					$bonus = $coupon_discount_items[$sc[$i]['pobject_id']]['math_price'] - $coupon_discount_items[$sc[$i]['pobject_id']]['discount_price'];	
				}				

				$book_obj->setTransaction($inst_id_time.substr(md5(uniqid(rand(), true)), 0, 4));
				$book_obj->setPobjectId($sc[$i]['pobject_id']);
				$book_obj->setCustomerId($ilUser->getId());
				$book_obj->setVendorId($pobjectData['vendor_id']);
				$book_obj->setPayMethod($pobjectData['pay_method']);
				$book_obj->setOrderDate(time());
				$book_obj->setDuration($sc[$i]['dauer']);
				$book_obj->setUnlimitedDuration($sc[i]['unlimited_duration']);
				$book_obj->setVatUnit($sc[i]['vat_unit']);
				$book_obj->setPrice($sc[$i]['betrag_string']);
				$book_obj->setDiscount($bonus > 0 ? ilPaymentPrices::_getPriceStringFromAmount($bonus * (-1)) : '');
				$book_obj->setPayed(1);
				$book_obj->setAccess(1);
				$book_obj->setVoucher('');
				$book_obj->setTransactionExtern($a_id);

				include_once './payment/classes/class.ilGeneralSettings.php';
				$genSet = new ilGeneralSettings();
				$save_customer_address_enabled = $genSet->get('save_customer_address_enabled');	
				
				if($save_customer_address_enabled == '1')
				{
					$book_obj->setStreet($_SESSION['bill']['personal_data']['strasse'], $_SESSION['bill']['personal_data']['hausNr']);
					$book_obj->setPoBox($_SESSION['bill']['personal_data']['postfach']);
					$book_obj->setZipcode($_SESSION['bill']['personal_data']['PLZ']);
					$book_obj->setCity($_SESSION['bill']['personal_data']['ort']);
					$book_obj->setCountry($_SESSION['bill']['personal_data']['land']);
				}
				
				$booking_id = $book_obj->add();
				
				if (!empty($_SESSION['coupons']['bill']) && $booking_id)
				{				
					foreach ($_SESSION['coupons']['bill'] as $coupon)
					{	
						$this->coupon_obj->setId($coupon['pc_pk']);				
						$this->coupon_obj->setCurrentCoupon($coupon);																
							
						if ($this->coupon_obj->isObjectAssignedToCoupon($pobject->getRefId()))
						{						
							$this->coupon_obj->addCouponForBookingId($booking_id);																					
						}				
					}			
				}
	
				unset($booking_id);
				unset($pobject);				

				$obj_id = $ilObjDataCache->lookupObjId($pobjectData['ref_id']);
				$obj_type = $ilObjDataCache->lookupType($obj_id);
				$obj_title = $ilObjDataCache->lookupTitle($obj_id);

				$bookings['list'][] = array(
					'type' => $obj_type,
					'title' => '['.$obj_id.']: ' . $obj_title,
					'duration' => $sc[$i]['dauer'],
					'vat_rate' => $sc[$i]['vat_rate'], 
					'vat_unit' => $sc[$i]['vat_unit'],  
					'price' => $sc[$i]['betrag_string'],
					'betrag' => $sc[$i]['betrag']
				);
		
				$total += $sc[$i]['betrag'];
				$total_vat += $sc[$i]['vat_unit'];

				
				if ($sc[$i]['psc_id']) $this->psc_obj->delete($sc[$i]['psc_id']);				
			}
			
			if (!empty($_SESSION['coupons']['bill']))
			{				
				foreach ($_SESSION['coupons']['bill'] as $coupon)
				{	
					$this->coupon_obj->setId($coupon['pc_pk']);				
					$this->coupon_obj->setCurrentCoupon($coupon);
					$this->coupon_obj->addTracking();			
				}			
			}
		}

		$bookings['total'] = $total;
	
		$bookings['total_vat'] = $total_vat;

		$bookings['transaction'] = $transaction;

		return $bookings;
	}
	
	function __sendBill($customer, $bookings)
	{

		global $ilUser, $ilias, $tpl;

		$transaction = $bookings['transaction'];

		include_once './classes/class.ilTemplate.php';
		include_once './Services/Utilities/classes/class.ilUtil.php';
		include_once './payment/classes/class.ilGeneralSettings.php';
		include_once './payment/classes/class.ilPaymentShoppingCart.php';
		include_once 'Services/Mail/classes/class.ilMimeMail.php';

		$psc_obj = new ilPaymentShoppingCart($this->user_obj);
		$genSet = new ilGeneralSettings();
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bill.html','payment');
		$tpl = new ilTemplate('./payment/templates/default/tpl.pay_bill.html', true, true, true);
  
		$tpl->setVariable('VENDOR_ADDRESS', nl2br(utf8_decode($genSet->get('address'))));
		$tpl->setVariable('VENDOR_ADD_INFO', nl2br(utf8_decode($genSet->get('add_info'))));
		$tpl->setVariable('VENDOR_BANK_DATA', nl2br(utf8_decode($genSet->get('bank_data'))));
		$tpl->setVariable('TXT_BANK_DATA', utf8_decode($this->lng->txt('pay_bank_data')));


		$tpl->setVariable('CUSTOMER_FIRSTNAME', $customer['vorname']);
		$tpl->setVariable('CUSTOMER_LASTNAME', $customer['nachname']);
		if($customer['postfach']== '')
		{
			$tpl->setVariable('CUSTOMER_STREET', $customer['strasse']. ' ' . $customer['hausNr']);
		}
		else
		{
			$tpl->setVariable('CUSTOMER_STREET', $customer['postfach']);
		}
		$tpl->setVariable('CUSTOMER_ZIPCODE', $customer['PLZ']);
		$tpl->setVariable('CUSTOMER_CITY', $customer['ort']);
		$tpl->setVariable('CUSTOMER_COUNTRY', $customer['land']);

		$tpl->setVariable('BILL_NO', $transaction);
		$tpl->setVariable('DATE', date('d.m.Y'));

		$tpl->setVariable('TXT_BILL', utf8_decode($this->lng->txt('pays_bill')));
		$tpl->setVariable('TXT_BILL_NO', utf8_decode($this->lng->txt('pay_bill_no')));
		$tpl->setVariable('TXT_DATE', utf8_decode($this->lng->txt('date')));

		$tpl->setVariable('TXT_ARTICLE', utf8_decode($this->lng->txt('pay_article')));
		$tpl->setVariable('TXT_VAT_RATE', utf8_decode($this->lng->txt('vat_rate')));
		$tpl->setVariable('TXT_VAT_UNIT', utf8_decode($this->lng->txt('vat_unit')));		
		$tpl->setVariable('TXT_PRICE', utf8_decode($this->lng->txt('price_a')));

		for ($i = 0; $i < count($bookings['list']); $i++)
		{
			$tmp_pobject =& new ilPaymentObject($this->user_obj, $bookings['list'][$i]['pobject_id']);
			
			$assigned_coupons = '';					
			if (!empty($_SESSION['coupons']['bill']))
			{											
				foreach ($_SESSION['coupons']['bill'] as $key => $coupon)
				{
					$this->coupon_obj->setId($coupon['pc_pk']);
					$this->coupon_obj->setCurrentCoupon($coupon);

					if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
					{
						$assigned_coupons .= '<br />' . $this->lng->txt('paya_coupons_coupon') . ': ' . $coupon['pcc_code'];
					}
				}
			}
			
			$tpl->setCurrentBlock('loop');
			$tpl->setVariable('LOOP_OBJ_TYPE', utf8_decode($this->lng->txt($bookings['list'][$i]['type'])));
			$tpl->setVariable('LOOP_TITLE', utf8_decode($bookings['list'][$i]['title']) . $assigned_coupons);
			$tpl->setVariable('LOOP_TXT_ENTITLED_RETRIEVE', utf8_decode($this->lng->txt('pay_entitled_retrieve')));
			
		if( $bookings['list'][$i]['duration'] == 0)
		{
						$tpl->setVariable('LOOP_DURATION', utf8_decode($this->lng->txt('unlimited_duration')));
		} 	else
			$tpl->setVariable('LOOP_DURATION', $bookings['list'][$i]['duration'] . ' ' . utf8_decode($this->lng->txt('paya_months')));
			$tpl->setVariable('LOOP_VAT_RATE', $bookings['list'][$i]['vat_rate']);
			$tpl->setVariable('LOOP_VAT_UNIT', $bookings['list'][$i]['vat_unit'].' '.$genSet->get('currency_unit'));			
			$tpl->setVariable('LOOP_PRICE', $bookings['list'][$i]['price']);
			$tpl->parseCurrentBlock('loop');
			
			unset($tmp_pobject);
		}
		
		if (!empty($_SESSION['coupons']['bill']))
		{
			if (count($items = $bookings['list']))
			{
				$sub_total_amount = $bookings['total'];							

				foreach ($_SESSION['coupons']['bill'] as $coupon)
				{
					$this->coupon_obj->setId($coupon['pc_pk']);
					$this->coupon_obj->setCurrentCoupon($coupon);					
					
					$total_object_price = 0.0;
					$current_coupon_bonus = 0.0;
					
					foreach ($bookings['list'] as $item)
					{
						$tmp_pobject =& new ilPaymentObject($this->user_obj, $item['pobject_id']);						
						
						if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
						{						
							$total_object_price += $item['betrag'];																					
						}			
						
						unset($tmp_pobject);
					}					
					
					$current_coupon_bonus = $this->coupon_obj->getCouponBonus($total_object_price);	

					$bookings['total'] += $current_coupon_bonus * (-1);
					
					$tpl->setCurrentBlock('cloop');
					$tpl->setVariable('TXT_COUPON', utf8_decode($this->lng->txt('paya_coupons_coupon') . ' ' . $coupon['pcc_code']));
					$tpl->setVariable('BONUS', number_format($current_coupon_bonus * (-1), 2, ',', '.') . ' ' . $genSet->get('currency_unit'));
					$tpl->parseCurrentBlock();
				}
				
				$tpl->setVariable('TXT_SUBTOTAL_AMOUNT', utf8_decode($this->lng->txt('pay_bmf_subtotal_amount')));
				$tpl->setVariable('SUBTOTAL_AMOUNT', number_format($sub_total_amount, 2, ',', '.') . ' ' . $genSet->get('currency_unit'));
			}
		}
		
		if ($bookings['total'] < 0)
		{			
			$bookings['total'] = 0.0;
			$bookings['total_vat'] = 0.0;
		}
		else
		{
			//$bookings['total_vat'] = $this->psc_obj->getVat($bookings['total']);
		}

		$tpl->setVariable('TXT_TOTAL_AMOUNT', utf8_decode($this->lng->txt('pay_bmf_total_amount')));
		$tpl->setVariable('TOTAL_AMOUNT', number_format($bookings['total'], 2, ',', '.') . ' ' . $genSet->get('currency_unit'));
		if ($bookings['total_vat'] > 0)
		{
			//$tpl->setVariable('VAT', number_format($bookings['vat'], 2, ',', '.') . ' ' . $genSet->get('currency_unit'));
			//$tpl->setVariable('TXT_VAT', $genSet->get('vat_rate') . '% ' . utf8_decode($this->lng->txt('pay_bmf_vat_included')));
			$tpl->setVariable('TOTAL_VAT', $bookings['total_vat']. ' ' . $genSet->get('currency_unit'));
			$tpl->setVariable('TXT_TOTAL_VAT', utf8_decode($this->lng->txt('pay_bmf_vat_included')));
		}

		$tpl->setVariable('TXT_PAYMENT_TYPE', utf8_decode($this->lng->txt('pay_payed_bill')));

		if (!@file_exists($genSet->get('pdf_path')))
		{

			ilUtil::makeDir($genSet->get('pdf_path'));
		}

		if (@file_exists($genSet->get('pdf_path')))
		{		
			ilUtil::html2pdf($tpl->get(), $genSet->get('pdf_path') . '/' . $transaction . '.pdf');
		}

		if (@file_exists($genSet->get('pdf_path') . '/' . $transaction . '.pdf') &&
			$ilUser->getEmail() != '' &&
			$ilias->getSetting('admin_email') != '')
		{
			$m= new ilMimeMail; // create the mail
			$m->From( $ilias->getSetting('admin_email') );
			$m->To( $ilUser->getEmail() );
			$m->Subject( $this->lng->txt('pay_message_subject') );	
			$message = $this->lng->txt('pay_message_hello') . ' ' . $ilUser->getFirstname() . ' ' . $ilUser->getLastname() . ",\n\n";
			$message .= $this->lng->txt('pay_message_thanks') . "\n\n";
			$message .= $this->lng->txt('pay_message_attachment') . "\n\n";
			$message .= $this->lng->txt('pay_message_regards') . "\n\n";
			$message .= strip_tags($genSet->get('address'));
			$m->Body( $message );	// set the body
			$m->Attach( $genSet->get('pdf_path') . '/' . $transaction . '.pdf', 'application/pdf' ) ;	// attach a file of type image/gif
			$m->Send();	// send the mail
		}

		@unlink($genSet->get('pdf_path') . '/' . $transaction . '.html');
		@unlink($genSet->get('pdf_path') . '/' . $transaction . '.pdf');
	}
	
	function __addBookings($a_result,$a_transaction)
	{
		include_once './payment/classes/class.ilPaymentBookings.php';
		include_once './payment/classes/class.ilPaymentShoppingCart.php';
		include_once './payment/classes/class.ilPaymentObject.php';
		include_once './payment/classes/class.ilPaymentPrices.php';
		include_once './payment/classes/class.ilGeneralSettings.php';
		
		$genSet = new ilGeneralSettings();
		$save_customer_address_enabled = $genSet->get('save_customer_address_enabled');
		
		$booking_obj =& new ilPaymentBookings();
		
		$sc_obj =& new ilPaymentShoppingCart($this->user_obj);
			
		$items = $sc_obj->getEntries(PAY_METHOD_BILL);		
		
		$sc_obj->clearCouponItemsSession();
		
		foreach($items as $entry)
		{		
			$pobject =& new ilPaymentObject($this->user_obj,$entry['pobject_id']);
			
			$price = ilPaymentPrices::_getPrice($entry['price_id']);					
			
			if (!empty($_SESSION['coupons']['bill']))
			{					
				//$entry['math_price'] = (float) ilPaymentPrices::_getPriceFromArray($price);
				$entry['math_price'] = $entry['price'];				
				foreach ($_SESSION['coupons']['bill'] as $key => $coupon)
				{							
					$this->coupon_obj->setId($coupon['pc_pk']);
					$this->coupon_obj->setCurrentCoupon($coupon);										
			
					if ($this->coupon_obj->isObjectAssignedToCoupon($pobject->getRefId()))
					{
						//$_SESSION['coupons']['bill'][$key]['total_objects_coupon_price'] += (float) ilPaymentPrices::_getPriceFromArray($price);
						$_SESSION['coupons']['bill'][$key]['total_objects_coupon_price'] += $entry['price'];	
						$_SESSION['coupons']['bill'][$key]['items'][] = $entry;									
					}				
				}				
			}
			
			unset($pobject);
		}
		
		$coupon_discount_items = $sc_obj->calcDiscountPrices($_SESSION['coupons']['bill']);	

		$i = 0;
		foreach($items as $entry)
		{
			$pobject =& new ilPaymentObject($this->user_obj,$entry['pobject_id']);

			$price = ilPaymentPrices::_getPrice($entry['price_id']);
			
			
			if (array_key_exists($entry['pobject_id'], $coupon_discount_items))
			{
				$bonus = $coupon_discount_items[$entry['pobject_id']]['math_price'] - $coupon_discount_items[$entry['pobject_id']]['discount_price'];
			}
			
			$booking_obj->setTransaction($a_transaction);
			$booking_obj->setPobjectId($entry['pobject_id']);
			$booking_obj->setCustomerId($this->user_obj->getId());
			$booking_obj->setVendorId($pobject->getVendorId());
			$booking_obj->setPayMethod($pobject->getPayMethod());
			$booking_obj->setOrderDate(time());
			$booking_obj->setDuration($price['duration']);
			$booking_obj->setPrice(ilPaymentPrices::_getPriceString($entry['price_id']));
			$booking_obj->setDiscount($bonus > 0 ? ilPaymentPrices::_getPriceStringFromAmount((-1) * $bonus) : '');
			$booking_obj->setPayed(1);
			$booking_obj->setAccess(1);
			$booking_obj->setVoucher($a_result->buchungsListe->buchungen[$i++]->belegNr);			
			$booking_obj->setTransactionExtern($a_result->buchungsListe->kassenzeichen);
			
			//sets the customers address for the bill if enabled in administration
			if($save_customer_address_enabled == '1')
			{
				$booking_obj->setStreet($_SESSION['bill']['personal_data']['strasse'], $_SESSION['bill']['personal_data']['hausNr']);
				$booking_obj->setPoBox($_SESSION['bill']['personal_data']['postfach']);
				$booking_obj->setZipcode($_SESSION['bill']['personal_data']['PLZ']);
				$booking_obj->setCity($_SESSION['bill']['personal_data']['ort']);
				$booking_obj->setCountry($_SESSION['bill']['personal_data']['land']);
			}
				
			$current_booking_id = $booking_obj->add();			
			
			if (!empty($_SESSION['coupons']['bill']) && $current_booking_id)
			{				
				foreach ($_SESSION['coupons']['bill'] as $coupon)
				{	
					$this->coupon_obj->setId($coupon['pc_pk']);				
					$this->coupon_obj->setCurrentCoupon($coupon);																
						
					if ($this->coupon_obj->isObjectAssignedToCoupon($pobject->getRefId()))
					{						
						$this->coupon_obj->addCouponForBookingId($current_booking_id);																					
					}				
				}			
			}

			unset($current_booking_id);
			unset($pobject);
		}
		
		if (!empty($_SESSION['coupons']['bill']))
		{				
			foreach ($_SESSION['coupons']['bill'] as $coupon)
			{	
				$this->coupon_obj->setId($coupon['pc_pk']);				
				$this->coupon_obj->setCurrentCoupon($coupon);
				$this->coupon_obj->addTracking();			
			}			
		}
	}

	function __emptyShoppingCart()
	{
		include_once './payment/classes/class.ilPaymentShoppingCart.php';
		
		$sc_obj =& new ilPaymentShoppingCart($this->user_obj);

		return $sc_obj->emptyShoppingCart();
	}
		
	function __clearSession()
	{
		$_SESSION['coupons']['bill'] = '';
	}

	function __loadTemplate()
	{
		$this->tpl->addBlockFile('CONTENT', 'content', 'tpl.payb_content.html');

		$this->__buildStylesheet();
		$this->__buildStatusline();
	}

	function  __buildStatusline()
	{
		$this->tpl->addBlockFile('STATUSLINE', 'statusline', 'tpl.statusline.html');
#		$this->__buildLocator();
	}

	function __buildLocator()
	{
		$this->tpl->addBlockFile('LOCATOR', 'locator', 'tpl.locator.html');
		$this->tpl->setVariable('TXT_LOCATOR',$this->lng->txt('locator'));

		$this->tpl->setCurrentBlock('locator_item');
		$this->tpl->setVariable('ITEM', $this->lng->txt('personal_desktop'));
		$this->tpl->setVariable('LINK_ITEM','../ilias.php?baseClass=ilPersonalDesktopGUI');
		#$this->tpl->setVariable('LINK_ITEM', '../usr_personaldesktop.php');
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock('locator_item');
		$this->tpl->setVariable('PREFIX','>&nbsp;');
#		$this->tpl->setVariable('ITEM', $this->lng->txt('pay_locator'));
		$this->tpl->setVariable('ITEM', 'Payment');
		$this->tpl->setVariable('LINK_ITEM', './payment.php');
		$this->tpl->parseCurrentBlock();

		// CHECK for new mail and info
		ilUtil::sendInfo();

		return true;
	}

	function __buildStylesheet()
	{
		$this->tpl->setVariable('LOCATION_STYLESHEET',ilUtil::getStyleSheetLocation());
	}

	/**
	* shows select box f�r countries
	*/
	function __showCountries(&$tpl, $value = '')
	{
		$countries = $this->__getCountries();
		foreach($countries as $code => $text)
		{
			$tpl->setCurrentBlock('loop_countries');
			$tpl->setVariable('LOOP_COUNTRIES', $code);
			$tpl->setVariable('LOOP_COUNTRIES_TXT', $text);
			if ($value != '' &&
				$value == $code)
			{
				$tpl->setVariable('LOOP_COUNTRIES_SELECTED', ' selected');
			}
			$tpl->parseCurrentBlock('loop_countries');
		}
		$tpl->setVariable('TXT_PLEASE_SELECT', $this->lng->txt('pay_bmf_please_select'));
		return;
	}

	function __getCountries()
	{
		global $lng;

		$lng->loadLanguageModule('meta');

		$cntcodes = array ('DE','ES','FR','GB','AT','CH','AF','AL','DZ','AS','AD','AO',
			'AI','AQ','AG','AR','AM','AW','AU','AT','AZ','BS','BH','BD','BB','BY',
			'BE','BZ','BJ','BM','BT','BO','BA','BW','BV','BR','IO','BN','BG','BF',
			'BI','KH','CM','CA','CV','KY','CF','TD','CL','CN','CX','CC','CO','KM',
			'CG','CK','CR','CI','HR','CU','CY','CZ','DK','DJ','DM','DO','TP','EC',
			'EG','SV','GQ','ER','EE','ET','FK','FO','FJ','FI','FR','FX','GF','PF',
			'TF','GA','GM','GE','DE','GH','GI','GR','GL','GD','GP','GU','GT','GN',
			'GW','GY','HT','HM','HN','HU','IS','IN','ID','IR','IQ','IE','IL','IT',
			'JM','JP','JO','KZ','KE','KI','KP','KR','KW','KG','LA','LV','LB','LS',
			'LR','LY','LI','LT','LU','MO','MK','MG','MW','MY','MV','ML','MT','MH',
			'MQ','MR','MU','YT','MX','FM','MD','MC','MN','MS','MA','MZ','MM','NA',
			'NR','NP','NL','AN','NC','NZ','NI','NE','NG','NU','NF','MP','NO','OM',
			'PK','PW','PA','PG','PY','PE','PH','PN','PL','PT','PR','QA','RE','RO',
			'RU','RW','KN','LC','VC','WS','SM','ST','SA','CH','SN','SC','SL','SG',
			'SK','SI','SB','SO','ZA','GS','ES','LK','SH','PM','SD','SR','SJ','SZ',
			'SE','SY','TW','TJ','TZ','TH','TG','TK','TO','TT','TN','TR','TM','TC',
			'TV','UG','UA','AE','GB','UY','US','UM','UZ','VU','VA','VE','VN','VG',
			'VI','WF','EH','YE','ZR','ZM','ZW');
		$cntrs = array();
		foreach($cntcodes as $cntcode)
		{
			$cntrs[$cntcode] = $lng->txt('meta_c_'.$cntcode);
		}
		asort($cntrs);
		return $cntrs;
	}

	function __getCountryCode($value = '')
	{
		$countries = $this->__getCountries();
		foreach($countries as $code => $text)
		{
			if ($text == $value)
			{
				return $code;
			}
		}
		return;
	}

	function __getCountryName($value = '')
	{
		$countries = $this->__getCountries();
		return $countries[$value];
	}

	function __getShoppingCart()
	{
		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);

		if(!count($items = $this->psc_obj->getEntries(PAY_METHOD_BILL)))
		{
			return 0;
		}

		$counter = 0;
		foreach($items as $item)
		{
			$tmp_pobject =& new ilPaymentObject($this->user_obj,$item['pobject_id']);

			$tmp_obj =& ilObjectFactory::getInstanceByRefId($tmp_pobject->getRefId());

			$f_result[$counter]['buchungstext'] = $tmp_obj->getTitle();

			$price_arr = ilPaymentPrices::_getPrice($item['price_id']);

			//$price = (int) $price_arr['unit_value'];
			$price = (float) $price_arr['price'];
			
			
		/*	if ($price_arr['sub_unit_value'] != '' &&
				$price_arr['sub_unit_value'] > 0)
			{
				$price .= '.'.( (int) $price_arr['sub_unit_value']);
			}
*/
			$f_result[$counter]['betrag'] = $price * 1.0;

			unset($tmp_obj);
			unset($tmp_pobject);

			++$counter;
		}

		return $f_result;
	}

	function __getTotalAmount()
	{
		$amount = 0;

		if (is_array($result = $this->__getShoppingCart()))
		{
			for ($i = 0; $i < count($result); $i++)
			{
				$amount += $result[$i]['betrag'];
			}
		}

		return $amount;
	}
	
	// if ok, a transaction-id will be generated and the customer gets a bill 
	function getBill()
	{
		if ($_POST['terms_conditions'] != 1)
		{
			$this->error = $this->lng->txt('pay_bmf_check_terms_conditions');
			ilUtil::sendInfo($this->error);
			$this->getPersonalData();
			return;
		}
		if ($_POST['password'] == '' ||
			md5($_POST['password']) != $this->user_obj->getPasswd())
		{
			$this->error = $this->lng->txt('pay_bmf_password_not_valid');
			ilUtil::sendInfo($this->error);
			$this->getPersonalData();
			return;
		}
		$this->error = '';
		ilUtil::sendInfo($this->lng->txt('pay_message_thanks'));
		
		$bookingList = $this->__saveTransaction();
		$customer = $_SESSION['bill']['personal_data'];
		$this->__sendBill($customer, $bookingList);
		
	}
	
	
	function __showShoppingCart()
	{
		include_once './payment/classes/class.ilGeneralSettings.php';

		$genSet = new ilGeneralSettings();
		
		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);

		if(!count($items = $this->psc_obj->getEntries(PAY_METHOD_BILL)))
		{
			ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));
		}

		$counter = 0;
		foreach($items as $item)
		{
			$tmp_pobject =& new ilPaymentObject($this->user_obj,$item['pobject_id']);

			$tmp_obj =& ilObjectFactory::getInstanceByRefId($tmp_pobject->getRefId());

			$price_arr = ilPaymentPrices::_getPrice($item['price_id']);
			
			$assigned_coupons = '';					
			if (!empty($_SESSION['coupons']['bill']))
			{															
				foreach ($_SESSION['coupons']['bill'] as $key => $coupon)
				{
					$this->coupon_obj->setId($coupon['pc_pk']);
					$this->coupon_obj->setCurrentCoupon($coupon);

					if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
					{
						$assigned_coupons .= '<br />' . $this->lng->txt('paya_coupons_coupon') . ': ' . $coupon['pcc_code'];						
					}
				}
			}
			
			$f_result[$counter][] = $tmp_obj->getTitle();
			if ($assigned_coupons != '') $f_result[$counter][count($f_result[$counter]) - 1] .= $assigned_coupons;
		
			if($price_arr['duration'] == 0)
			{
				$f_result[$counter][] = $this->lng->txt('unlimited_duration');				
			}
			else
			{
				$f_result[$counter][] = $price_arr['duration'] . ' ' . $this->lng->txt('paya_months');	
			}
			
			
			$f_result[$counter][] = $tmp_pobject->getVatRate().' % ';

			//$f_result[$counter][] = $tmp_pobject->getVat($price_arr['unit_value'],$item['pobject_id']).' '.$genSet->get('currency_unit');
			//$this->totalVat = $this->totalVat + $tmp_pobject->getVat($price_arr['unit_value'],$item['pobject_id']);
			$f_result[$counter][] = $tmp_pobject->getVat($price_arr['price'],$item['pobject_id']).' '.$genSet->get('currency_unit');
			$this->totalVat = $this->totalVat + $tmp_pobject->getVat($price_arr['price'],$item['pobject_id']);
			
			$f_result[$counter][] = ilPaymentPrices::_getPriceString($item['price_id']);

			unset($tmp_obj);
			//unset($tmp_pobject);

			++$counter;
		}

		return $this->__showItemsTable($f_result);
	}

	function &__initTableGUI()
	{
		include_once './Services/Table/classes/class.ilTableGUI.php';

		return new ilTableGUI(0,false);
	}

	function __showItemsTable($a_result_set)
	{
		include_once './payment/classes/class.ilGeneralSettings.php';
		
		$genSet = new ilGeneralSettings();

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMACTION
		$tpl->setCurrentBlock('tbl_form_header');

		$tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt('paya_shopping_cart'),'icon_pays_b.gif',$this->lng->txt('paya_shopping_cart'));
		$tbl->setHeaderNames(array($this->lng->txt('title'),
								   $this->lng->txt('duration'),
								   $this->lng->txt('vat_rate'),
								   $this->lng->txt('vat_unit'),								   
								   $this->lng->txt('price_a')));

		$tbl->setHeaderVars(array('title',
								  'duration',
									'vat_rate',
									'vat_unit',
								  'price'),
							array('cmd' => '',
								  'cmdClass' => 'ilpurchasebillgui',
								  'cmdNode' => $_GET['cmdNode']));

		$tbl->disable('footer');
		$tbl->disable('sort');
		$tbl->disable('linkbar');

		$offset = $_GET['offset'];
		$order = $_GET['sort_by'];
		$direction = $_GET['sort_order'] ? $_GET['sort_order'] : 'desc';

		$tbl->setOrderColumn($order,'title');
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET['limit']);
		$tbl->setMaxCount(count($a_result_set));
#		$tbl->setFooter('tblfooter',$this->lng->txt('previous'),$this->lng->txt('next'));
		$tbl->setData($a_result_set);

		$sc_obj =& new ilPaymentShoppingCart($this->user_obj);

		$totalAmount =  $sc_obj->getTotalAmount();
		//$vat = $sc_obj->getVat($totalAmount[PAY_METHOD_BILL]);

		//$this->totalVat = $sc_obj->getVat($totalAmount[PAY_METHOD_BILL]);

		$tpl->setCurrentBlock('tbl_footer_linkbar');
		$amount .= "<table class=\"\" style=\"float: right;\">\n";		
		if (!empty($_SESSION['coupons']['bill']))
		{
			$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);

			if (count($items = $this->psc_obj->getEntries(PAY_METHOD_BILL)))
			{			
				$amount .= "<tr>\n";
				$amount .= "<td>\n";
				$amount .= "<b>" . $this->lng->txt("pay_bmf_subtotal_amount") . ":";				
				$amount .= "</td>\n";
				$amount .= "<td>\n";
				$amount .= number_format($totalAmount[PAY_METHOD_BILL], 2, ",", ".") . " " . $genSet->get("currency_unit") . "</b>";				
				$amount .= "</td>\n";				
				$amount .= "</tr>\n";
				
				foreach ($_SESSION['coupons']['bill'] as $coupon)
				{		
					$this->coupon_obj->setCurrentCoupon($coupon);
					$this->coupon_obj->setId($coupon['pc_pk']);
					
					$total_object_price = 0.0;
					$current_coupon_bonus = 0.0;
					
					foreach ($items as $item)
					{
						$tmp_pobject =& new ilPaymentObject($this->user_obj, $item['pobject_id']);						
						
						if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
						{			
							$price_data = ilPaymentPrices::_getPrice($item['price_id']);									
							//$price = ((int) $price_data['unit_value']) . '.' . sprintf('%02d', ((int) $price_data['sub_unit_value']));
							$price = (float) $price_data['price'];
														
							$total_object_price += $price;																						
						}			
						
						unset($tmp_pobject);
					}
					
					$current_coupon_bonus = $this->coupon_obj->getCouponBonus($total_object_price);					
					$totalAmount[PAY_METHOD_BILL] += $current_coupon_bonus * (-1);				
					
					$amount .= "<tr>\n";
					$amount .= "<td>\n";					
					$amount .= $this->lng->txt('paya_coupons_coupon') . ' ' . $coupon['pcc_code'] . ':';
					$amount .= "</td>\n";
					$amount .= "<td>\n";
					$amount .= number_format($current_coupon_bonus * (-1), 2, ",", ".") . " " . $genSet->get("currency_unit");
					$amount .= "</td>\n";
					$amount .= "</tr>\n";
				}
				
				if ($totalAmount[PAY_METHOD_BILL] < 0)
				{
				
					$totalAmount[PAY_METHOD_BILL] = 0;
					$this->totalVat = 0;
				}
				else
				{
					
					//$this->totalVat = $sc_obj->getVat($totalAmount[PAY_METHOD_BILL]);	
				}	
			}				
		}		
		
		$amount .= "<tr>\n";
		$amount .= "<td>\n";					
		$amount .= "<b>" . $this->lng->txt("pay_bmf_total_amount") . ":";
		$amount .= "</td>\n";
		$amount .= "<td>\n";
		$amount .= number_format($totalAmount[PAY_METHOD_BILL], 2, ",", ".") . " " . $genSet->get("currency_unit");
		$amount .= "</td>\n";
		$amount .= "</tr>\n";
		
		if ($this->totalVat > 0)
		{		
			$amount .= "<tr>\n";
			$amount .= "<td>\n";					
//			$amount .= $genSet->get("vat_rate") . "% " . $this->lng->txt("pay_bmf_vat_included") . ":";
			$amount .= $this->lng->txt("pay_bmf_vat_included") . ":";
			$amount .= "</td>\n";
			$amount .= "<td>\n";
//			$amount .= number_format($vat, 2, ",", ".") . " " . $genSet->get("currency_unit");
			$amount .= $this->totalVat  . " " . $genSet->get('currency_unit');
			$amount .= "</td>\n";
			$amount .= "</tr>\n";	
		}
				
		$amount .= "</table>\n";
		
		$tpl->setVariable('LINKBAR', $amount);
		$tpl->parseCurrentBlock('tbl_footer_linkbar');
		$tpl->setCurrentBlock('tbl_footer');
		$tpl->setVariable('COLUMN_COUNT',5);
		$tpl->parseCurrentBlock();
		$tbl->render();

		$this->tpl->setVariable('ITEMS_TABLE',$tbl->tpl->get());

		return true;
	}
}
?>