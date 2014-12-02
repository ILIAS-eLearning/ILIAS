<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */



include_once './Services/Payment/classes/class.ilShopBaseGUI.php';
include_once './Services/Payment/classes/class.ilPaypalSettings.php';
//include_once './Services/Payment/classes/class.ilEPaySettings.php';
include_once './Services/Payment/classes/class.ilPaymentCoupons.php';
include_once './Services/Payment/classes/class.ilShopVatsList.php';
include_once './Services/Payment/classes/class.ilPaymentShoppingCart.php';
include_once './Services/Payment/classes/class.ilPayMethods.php';

include_once './Services/Payment/classes/class.ilPaymentCurrency.php';
include_once './Services/Payment/classes/class.ilShopBoughtObjectsGUI.php';

/**
* Class ilShopShoppingCartGUI
*
* @author Michael Jansen <mjansen@databay.de>
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id: $
* 
* @ingroup ServicesPayment
*/
class ilShopShoppingCartGUI extends ilShopBaseGUI
{	
	private $user_obj;
	private $psc_obj = null;
	private $paypal_obj = null;
   
	private $totalAmount = array();
	private $totalVat = 0;
	private $epSet;
	
	#private $default_currency = 1;
	private $session_id;

	public function __construct($user_obj)
	{	
		global $ilUser;
		
		parent::__construct();

		if(isset($_SESSION['shop_user_id']) && $_SESSION['shop_user_id'] != ANONYMOUS_USER_ID)
		{
			ilPaymentShoppingCart::_assignObjectsToUserId($_SESSION['shop_user_id']);
			$this->user_obj = new ilObjUser($_SESSION['shop_user_id']);
		}
		else if($user_obj != NULL)
		{
			$this->user_obj = $user_obj;
		}
		else if($ilUser->getId() != ANONYMOUS_USER_ID)
		{
			$this->user_obj = $ilUser;
			$_SESSION['shop_user_id'] = null;
		}
		else 
		{
			$this->session_id = session_id();	
		}	
		
		$this->coupon_obj = new ilPaymentCoupons($this->user_obj);

		$ppSet = ilPaypalSettings::getInstance();
		$this->paypalConfig = $ppSet->getAll();	
		
//		$this->epSet = ilEPaySettings::getInstance();
//		$this->epayConfig = $this->epSet->getAll();

		$this->checkCouponsOfShoppingCart();		

		#$this->default_currency = ilPaymentCurrency::_getDefaultCurrency();
	}
	
	/**
	* execute command
	*/
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		switch ($next_class)
		{
			default:
				$this->prepareOutput();
				if (!$cmd = $this->ctrl->getCmd())
				{
					$cmd = 'showItems';
				}
				$this->$cmd();
				break;
		}

		return true;
	}
	
	protected function prepareOutput()
	{
		global $ilTabs;		
		
		parent::prepareOutput();
		
		$ilTabs->setTabActive('paya_shopping_cart');
	}
	public function unlockBillObjectsInShoppingCart()
	{						
		include_once './Services/Payment/classes/class.ilPurchaseBaseGUI.php';
		$purchase = new ilPurchaseBaseGUI($this->user_obj, ilPayMethods::_getIdByTitle('paypal'));
		$purchase->__addBookings();
		
		$this->ctrl->redirectByClass('ilShopBoughtObjectsGUI', '');
		
		return true;
	}
		
	public function unlockBMFObjectsInShoppingCart()
	{		
		$this->addBookings(PAY_METHOD_BMF, 'bmf');
				
		$_SESSION['coupons']['bmf'] = '';
		$_SESSION['bmf']['payment_type'] = '';
		$_SESSION['bmf']['debit_entry'] = array();
		$_SESSION['bmf']['credit_card'] = array();

		ilUtil::sendInfo($this->lng->txt('pay_bmf_thanks'), true);
		
		$this->ctrl->redirectByClass('ilShopBoughtObjectsGUI', '');
		
		return true;
	}
	
	public function unlockPAYPALObjectsInShoppingCart()
	{
		include_once './Services/Payment/classes/class.ilPurchaseBaseGUI.php';
		$purchase = new ilPurchaseBaseGUI($this->user_obj, ilPayMethods::_getIdByTitle('paypal'));
		$purchase->__addBookings();

		ilUtil::sendSuccess($this->lng->txt('pay_paypal_success'), true);
		$this->ctrl->redirectByClass('ilShopBoughtObjectsGUI', '');

		return true;
	}
	
	private function addBookings($pay_method, $coupon_session)
	{
		global $ilUser, $ilObjDataCache;

		include_once './Services/Payment/classes/class.ilPaymentBookings.php';
		include_once './Services/Payment/classes/class.ilPaymentObject.php';
		include_once './Services/Payment/classes/class.ilPaymentPrices.php';	
		
		$booking_obj = new ilPaymentBookings();		
		$sc_obj = new ilPaymentShoppingCart($this->user_obj);
		$items = $sc_obj->getEntries($pay_method);		
		$sc_obj->clearCouponItemsSession();
		
		foreach($items as $entry)
		{	
			$pobject = new ilPaymentObject($this->user_obj, $entry['pobject_id']);			
			$price = ilPaymentPrices::_getPrice($entry['price_id']);

			if (!empty($_SESSION['coupons'][$coupon_session]))
			{					
				$entry['math_price'] = $entry['price'];	// (float) ilPaymentPrices::_getPriceFromArray($price);
				foreach ($_SESSION['coupons'][$coupon_session] as $key => $coupon)
				{							
					$this->coupon_obj->setId($coupon['pc_pk']);
					$this->coupon_obj->setCurrentCoupon($coupon);										
			
					if ($this->coupon_obj->isObjectAssignedToCoupon($pobject->getRefId()))
					{
						$_SESSION['coupons'][$coupon_session][$key]['total_objects_coupon_price'] += $entry['price'];	//(float) ilPaymentPrices::_getPriceFromArray($price);
						$_SESSION['coupons'][$coupon_session][$key]['items'][] = $entry;									
					}				
				}				
			}
			unset($pobject);
		}
		
		$coupon_discount_items = $sc_obj->calcDiscountPrices($_SESSION['coupons'][$coupon_session]);

		foreach($items as $entry)
		{
			$pobject = new ilPaymentObject($this->user_obj, $entry['pobject_id']);

			$price = ilPaymentPrices::_getPrice($entry['price_id']);
			
			if (array_key_exists($entry['pobject_id'], $coupon_discount_items))
			{
				$bonus = $coupon_discount_items[$entry['pobject_id']]['math_price'] - $coupon_discount_items[$entry['pobject_id']]['discount_price'];
			}
			
			$booking_obj->setPobjectId($entry['pobject_id']);
			
			$booking_obj->setCustomerId($this->user_obj->getId());
			$booking_obj->setVendorId($pobject->getVendorId());
			$booking_obj->setPayMethod($pobject->getPayMethod());
			$booking_obj->setOrderDate(time());
			$booking_obj->setDuration($price['duration']);
			$booking_obj->setPrice(ilPaymentPrices::_getPriceString($entry['price_id']));
			$booking_obj->setDiscount($bonus > 0 ? ((-1) * $bonus) : 0);
			$booking_obj->setPayed(1);
			$booking_obj->setAccess(1);

			switch($price['price_type'])
			{
				case ilPaymentPrices::TYPE_UNLIMITED_DURATION:
					$booking_obj->setDuration(0);
					break;
				case ilPaymentPrices::TYPE_DURATION_MONTH:
					$booking_obj->setDuration($price['duration']);
					break;
				case ilPaymentPrices::TYPE_DURATION_DATE:
					$booking_obj->setAccessStartdate($price['duration_from']);
					$booking_obj->setAccessEnddate($price['duration_until']);
					break;
			}

			$booking_obj->setAccessExtension($price['extension']);
			
			$obj_id = $ilObjDataCache->lookupObjId($pobject->getRefId());
			$obj_title = $ilObjDataCache->lookupTitle($obj_id);

			$oVAT = new ilShopVats((int)$pobject->getVatId());
			$obj_vat_rate = $oVAT->getRate();
			
			if($bonus > 0)
			{
				$tmp_price = $booking_obj->getPrice()-$bonus;
				$obj_vat_unit = $pobject->getVat($tmp_price);
			}else 
			$obj_vat_unit = $pobject->getVat($booking_obj->getPrice());
		
			$booking_obj->setObjectTitle($obj_title);
			$booking_obj->setVatRate($obj_vat_rate);
			$booking_obj->setVatUnit($obj_vat_unit);
			
			if(ilPaymethods::_EnabledSaveUserAddress($booking_obj->getPayMethod()))
			{
				$booking_obj->setStreet($this->user_obj->getStreet(), $this->user_obj->getHouseNumber);
				$booking_obj->setZipcode($this->user_obj->getZipcode());
				$booking_obj->setCity($this->user_obj->getCity());
				$booking_obj->setCountry($this->user_obj->getCountry());
			}			
			
			$current_booking_id = $booking_obj->add();
			
			if ($current_booking_id)
			{	
				$sc_obj->delete($entry['psc_id']);
						
				if (!empty($_SESSION['coupons'][$coupon_session]))
				{				
					foreach ($_SESSION['coupons'][$coupon_session] as $coupon)
					{	
						$this->coupon_obj->setId($coupon['pc_pk']);				
						$this->coupon_obj->setCurrentCoupon($coupon);																
							
						if ($this->coupon_obj->isObjectAssignedToCoupon($pobject->getRefId()))
						{						
							$this->coupon_obj->addCouponForBookingId($current_booking_id);																					
						}				
					}			
				}
			}			

			unset($current_booking_id);
			unset($pobject);
		}
		
		if (!empty($_SESSION['coupons'][$coupon_session]))
		{				
			foreach ($_SESSION['coupons'][$coupon_session] as $coupon)
			{	
				$this->coupon_obj->setId($coupon['pc_pk']);				
				$this->coupon_obj->setCurrentCoupon($coupon);
				$this->coupon_obj->addTracking();			
			}			
		}
	}
	
	public function checkCouponsOfShoppingCart()
	{
		$objPM = new ilPayMethods();
		$get_paymethods = $objPM->readAll();

		foreach($get_paymethods as $pm)
		{
			$pay_methods[$pm['pm_id']]['pm_title'] = $pm['pm_title'];
			$pay_methods[$pm['pm_id']]['pm_id'] = $pm['pm_id'];
		}
		
		if (is_array($pay_methods))
		{			
			foreach($pay_methods as $pay_method)
			{
				$coupon_session_id = $pay_method['pm_title'];

				if (!is_array($_SESSION['coupons'][$coupon_session_id]))
				{
					$_SESSION['coupons'][$coupon_session_id] = array();
				}
				else // check if coupons are valid anymore
				{
					foreach ($_SESSION['coupons'][$coupon_session_id] as $coupon_id => $session_coupon)
					{
						$coupon = $this->coupon_obj->getCouponByCode($session_coupon['pcc_code']);			

						if ($this->coupon_obj->checkCouponValidity() == 0)
						{
							$assignedItems = 0;			
							$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);							
						
							if (count($items = $this->psc_obj->getEntries($pay_method['pm_id'])))
							{
								foreach($items as $item)
								{
									$tmp_pobject = new ilPaymentObject($this->user_obj, $item['pobject_id']);
									
									if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
									{
										++$assignedItems;
									}					
								}
							}			
							if (!$assignedItems)
							{
								unset($_SESSION['coupons'][$coupon_session_id][$coupon_id]);
							}	
						}
						else
						{
							unset($_SESSION['coupons'][$coupon_session_id][$coupon_id]);
						}
					}
				}
			}
		}
	}
  
  /**
  * Return from ePay
  * @todo: Check for ePay/PBS error
  * @todo: Flyt fakturering til callback
  */        
	public function finishEPay()
	{
//	  global $ilUser;
//    require_once './Services/Payment/classes/class.ilPurchase.php';
//    
//    try
//    {
//    	$pm_id = ilPayMethods::_getIdByTitle('epay');  
//      $buy = new ilPurchase( $ilUser->getId(), $pm_id );    
//      $buy->purchase($_REQUEST['tid'] );
//	  }
//	  catch (ilERPException $e)
//	  {
//      $msg = $e->getMessage();
//      if (DEVMODE) $msg .= " " . print_r($_REQUEST, true);
//      ilUtil::sendFailure($msg);   
//    }
//    ilUtil::sendSuccess($this->lng->txt('pay_epay_success'));
//	  $this->ctrl->redirectByClass('ilShopBoughtObjectsGUI', '');
	}

	public function finishPaypal()
	{
		global $ilUser;
		$this->initPaypalObject();

		if (!($fp = $this->paypal_obj->openSocket()))
		{
			ilUtil::sendFailure($this->lng->txt('pay_paypal_failed').'<br />'.$this->lng->txt('pay_paypal_unreachable').'<br />'.$this->lng->txt('pay_paypal_error_info'));
			$this->showItems();
		}
		else
		{

			$res = $this->paypal_obj->checkData($fp);


			if ($res == SUCCESS)
			{
				ilUtil::sendSuccess($this->lng->txt('pay_paypal_success'), true);		
				if($this->user_obj->getId() == ANONYMOUS_USER_ID || $_SESSION['is_crs_object'] || $_SESSION['is_lm_object'] || $_SESSION['is_file_object'])
				{
		
					$this->ctrl->redirectByClass('ilShopShoppingCartGUI', '');
				}
				else
				{
				#	$this->ctrl->redirectByClass('ilShopBoughtObjectsGUI', '');
				    $this->ctrl->redirectByClass('ilShopShoppingCartGUI', '');
				}
			}
			else
			{
				switch ($res)
				{
					case ERROR_WRONG_CUSTOMER	:	ilUtil::sendFailure($this->lng->txt('pay_paypal_failed').'<br />'.$this->lng->txt('pay_paypal_error_wrong_customer').'<br />'.$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_NOT_COMPLETED	:	ilUtil::sendFailure($this->lng->txt('pay_paypal_failed').'<br />'.$this->lng->txt('pay_paypal_error_not_completed').'<br />'.$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_PREV_TRANS_ID	:	ilUtil::sendFailure($this->lng->txt('pay_paypal_failed').'<br />'.$this->lng->txt('pay_paypal_error_prev_trans_id').'<br />'.$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_WRONG_VENDOR		:	ilUtil::sendFailure($this->lng->txt('pay_paypal_failed').'<br />'.$this->lng->txt('pay_paypal_error_wrong_vendor').'<br />'.$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_WRONG_ITEMS		:	ilUtil::sendFailure($this->lng->txt('pay_paypal_failed').'<br />'.$this->lng->txt('pay_paypal_error_wrong_items').'<br />'.$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_FAIL				:	ilUtil::sendFailure($this->lng->txt('pay_paypal_failed').'<br />'.$this->lng->txt('pay_paypal_error_fails').'<br />'.$this->lng->txt('pay_paypal_error_info'));
													break;
				}
				$this->showItems();
			}
			fclose($fp);
		}
	}


	public function cancelEPay()
	{
//		ilUtil::sendInfo($this->lng->txt('pay_epay_canceled'));
//		$this->showItems();
	}

	public function cancelPaypal()
	{
		ilUtil::sendInfo($this->lng->txt('pay_paypal_canceled'));
		$this->showItems();
	}

  private function _getPayMethods( $limitToEnabled = false )
  {
		$objPM = new ilPayMethods();
		$get_paymethods = $objPM->readAll();
		
		if(!$limitToEnabled)
		{	
			foreach($get_paymethods as $pm)
			{
				$pay_methods[$pm['pm_id']]['pm_title'] = $pm['pm_title'];
				$pay_methods[$pm['pm_id']]['pm_id'] = $pm['pm_id'];
				$pay_methods[$pm['pm_id']]['pm_enabled'] = $pm['pm_enabled'];
				$pay_methods[$pm['pm_id']]['save_usr_adr'] = $pm['save_usr_adr'];
			}
		}
  		else
  		{
 			foreach($get_paymethods as $pm)
			{
	  			if($pm['pm_enabled'] == 1)
	  			{
	  				$pay_methods[$pm['pm_id']]['pm_title'] = $pm['pm_title'];
					$pay_methods[$pm['pm_id']]['pm_id'] = $pm['pm_id'];
					$pay_methods[$pm['pm_id']]['pm_enabled'] = $pm['pm_enabled'];
					$pay_methods[$pm['pm_id']]['save_usr_adr'] = $pm['save_usr_adr'];
	  			}
			}
  		}
		return $pay_methods;
  }

  /*
   *
   */
  private function _getTemplateFilename( $a_pm_title )
  {
  // use payment_paymethods -> pm_title  	
    $base = "./Services/Payment/templates/default/tpl.pay_shopping_cart_";
    $suffix = ".html";
    
     return $base . $a_pm_title . $suffix;
  }

	public function forceShoppingCartRedirect()
	{
		if(isset($_GET['user']))
		{
			$_SESSION['shop_user_id'] = (int)$_GET['user'];
		}
		$_SESSION['forceShoppingCartRedirect'] ='1';
		ilPaymentShoppingCart::_assignObjectsToUserId($_SESSION['shop_user_id']);
		$this->user_obj = new ilObjUser($_SESSION['shop_user_id']);

		$this->showItems();
	}

	public function showItems()
	{
		global $ilObjDataCache, $ilUser, $ilToolbar;

		include_once './Services/Payment/classes/class.ilPaymentPrices.php';
		include_once './Services/Payment/classes/class.ilPaymentCurrency.php';
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_shopping_cart.html','Services/Payment');
//		var_dump($_SESSION['shop_user_id']);
//		if(isset($_SESSION['shop_user_id']))
//		{
//			
//			$this->user_obj->_toggleActiveStatusOfUsers(array($this->user_obj->getId()), 1);
//		}

		if($_SESSION['forceShoppingCartRedirect'] == '1') 
//			&& $this->user_obj->getId() != ANONYMOUS_USER_ID)
		{
			$_SESSION['forceShoppingCartRedirect'] = 0;
			$this->tpl->touchBlock("close_js");
			return true;
		}
		
		$this->initShoppingCartObject();

		include_once './Services/Payment/classes/class.ilPaymentSettings.php';
		$genSet = ilPaymentSettings::_getInstance();
		$pay_methods = $this->_getPayMethods( true );
		$num_items = 0;
		$desc = array();

		//course_objects
		$is_crs_object = false;
		$crs_obj_ids = array();

		//file_objects,exercise_objects
		$is_file_object = false;

		// learning_modules,
		$is_lm_object = false;
		$lm_obj_ids = array();

		if($genSet->get('show_sr_shoppingcart') == 1)
		{
			require_once 'Services/RTE/classes/class.ilRTE.php';
			$regulations = ilRTE::_replaceMediaObjectImageSrc($genSet->get('statutory_regulations'),1);
			$this->tpl->setVariable('REGULATIONS_TITLE', $this->lng->txt('statutory_regulations'));
			$this->tpl->setVariable('REGULATIONS', $regulations);
		}
		
		$ilToolbar->addButton($this->lng->txt('payment_back_to_shop'),'ilias.php?baseClass=ilShopController');

		foreach($pay_methods as $pay_method)
		{
			$this->totalVat = 0;
			$tpl = new ilTemplate(  $this->_getTemplateFilename( $pay_method['pm_title'] ), true,'Services/Payment');
			$coupon_session_id = $pay_method['pm_title'];

			if (count($items = $this->psc_obj->getEntries( $pay_method['pm_id'])))
			{
				$counter = 0;
				$paypal_counter = 0;
				$total_price = 0;

				foreach ($items as $item)
				{
					$tmp_pobject = new ilPaymentObject($this->user_obj,$item['pobject_id']);

					$obj_id = $ilObjDataCache->lookupObjId($tmp_pobject->getRefId());
					$obj_type = $ilObjDataCache->lookupType($obj_id);
					$obj_title = $ilObjDataCache->lookupTitle($obj_id);
					$desc[] = "[" . $obj_type . "] " . $obj_title;
					$price_arr = ilPaymentPrices::_getPrice($item['price_id']);

					# checks object_type: needed for purchasing file or crs objects without login
					switch ($obj_type)
					{
						case 'crs':
							// if is_crs there an user-account will be autogenerated
							$is_crs_object = true;
							$_SESSION['is_crs_object'] = true;
							$crs_obj_ids[] = $obj_id;
							$_SESSION['crs_obj_ids'] = $crs_obj_ids;
							break;
						case 'lm':
						case 'sahs':
						case 'htlm':
						case 'tst':
							$is_lm_object = true;
							$_SESSION['is_lm_object'] = true;
							$lm_obj_ids[] = $obj_id;
							$_SESSION['lm_obj_ids'] = $lm_obj_ids;
							break;

 						case 'exc':
						case 'file':
							$is_file_object = true;
							break;
						default:

							break;
					}
					
					$direct_paypal_info_output = true;

					$assigned_coupons = '';
					if (!empty($_SESSION['coupons'][$coupon_session_id]))
					{
						$price = $price_arr['price'];
						$item['math_price'] = (float) $price;
			
						foreach ($_SESSION['coupons'][$coupon_session_id] as $key => $coupon)
						{
							$this->coupon_obj->setId($coupon['pc_pk']);
							$this->coupon_obj->setCurrentCoupon($coupon);

							if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
							{
								$assigned_coupons .=  $this->lng->txt('paya_coupons_coupon') . ': ' . $coupon['pcc_code'];

								$_SESSION['coupons'][$coupon_session_id][$key]['total_objects_coupon_price'] += (float) $price;
								$_SESSION['coupons'][$coupon_session_id][$key]['items'][] = $item;
								$direct_paypal_info_output = false;
							}
						}
					}

					$f_result[$counter]['item'] = ilUtil::formCheckBox(0,'item[]',$item['psc_id']);
					$subtype = '';
					if($obj_type == 'exc')
					{
						$subtype = ' ('.$this->lng->txt($tmp_pobject->getSubtype()).')';
						$f_result[$counter]['title'] = "<a href=\"goto.php?target=".$obj_type."_".$tmp_pobject->getRefId() . "\">".$obj_title."</a>".$subtype ;
					}
					else
						$f_result[$counter]['title'] = "<a href=\"ilias.php?baseClass=ilRepositoryGUI&amp;ref_id=".$tmp_pobject->getRefId() . "\">".$obj_title."</a>".$subtype ;

					if ($assigned_coupons != '')
					{
						// !!! $f_result[$counter][count($f_result[$counter]) - 1] .= $assigned_coupons;
						$f_result[$counter]['assigned_coupons'] .= $assigned_coupons;
					}
						
					switch($price_arr['price_type'])
					{
						case  ilPaymentPrices::TYPE_DURATION_MONTH:
							$f_result[$counter]['duration'] = $price_arr['duration'].' '.$this->lng->txt('paya_months');
							break;
						case  ilPaymentPrices::TYPE_DURATION_DATE:
							$f_result[$counter]['duration'] =
								ilDatePresentation::formatDate(new ilDate($price_arr['duration_from'], IL_CAL_DATE))
									.' - '.ilDatePresentation::formatDate(new ilDate($price_arr['duration_until'], IL_CAL_DATE));
							break;
						case  ilPaymentPrices::TYPE_UNLIMITED_DURATION:
							$f_result[$counter]['duration'] = $this->lng->txt('unlimited_duration');
							break;
					}

					$float_price = $price_arr['price'];
					$total_price += $float_price;

					$oVAT = new ilShopVats((int)$tmp_pobject->getVatId());
					$f_result[$counter]['vat_rate'] = ilShopUtils::_formatVAT($oVAT->getRate());

					$this->totalVat = $this->totalVat + $tmp_pobject->getVat($float_price);

					$f_result[$counter]['price'] = ilPaymentPrices::_getPriceString($item['price_id']).' '.$genSet->get('currency_unit');
					$f_result[$counter]['vat_unit'] = ilPaymentPrices::_getGUIPrice($tmp_pobject->getVat($float_price, 'CALCULATION'))
					.' '.$genSet->get('currency_unit');

					if ($pay_method['pm_title'] == 'paypal')
					{
						if ($direct_paypal_info_output == true) // Paypal information in hidden fields
						{
							$tpl->setCurrentBlock('loop_items');
							$tpl->setVariable('LOOP_ITEMS_NO', (++$paypal_counter));
							$tpl->setVariable('LOOP_ITEMS_NAME', "[".$obj_id."]: ".$obj_title);
							$tpl->setVariable('LOOP_ITEMS_AMOUNT', $float_price);
							$tpl->parseCurrentBlock('loop_items');
						}
					}
						
					++$counter;
					unset($tmp_pobject);					
				} // foreach
					
				$this->showItemsTable($tpl, $f_result, $pay_method);

				if (!(bool)$genSet->get('hide_coupons'))
				{
					$tpl->setVariable('COUPON_TABLE', $this->showCouponInput($pay_method['pm_title']));
				}
				$tpl->setCurrentBlock('buy_link');
				#				$tpl->setCurrentBlock('terms_checkbox');

				$link_target = $this->ctrl->getLinkTargetByClass('iltermsconditionsgui','');
				$terms_link =  '<a href="'.$link_target.'">'.$this->lng->txt('terms_conditions').'</a>';
				$tpl->setVariable('TERMS_CONDITIONS', sprintf($this->lng->txt('accept_terms_conditions'), $terms_link));

				switch($pay_method['pm_title'])
				{
					case 'bill':
						if ($this->totalAmount[$pay_method['pm_id']] <= 0 && ANONYMOUS_USER_ID == $this->user_obj->getId())
						{
							$tpl->setVariable('TXT_UNLOCK', $this->lng->txt('pay_click_to_buy'));
							$tpl->setVariable('LINK_UNLOCK', $this->ctrl->getLinkTarget($this, 'unlockBillObjectsInShoppingCart'));
						}
						else
						{
							# Anonymous user has to login
							if(ANONYMOUS_USER_ID == $this->user_obj->getId())
							{
								ilUtil::sendInfo($this->lng->txt('click_to_continue_info'));
								$tpl->touchBlock('attach_submit_event_bill');
								$tpl->setVariable('TXT_BUY', $this->lng->txt('continue'));
								$tpl->setVariable('SCRIPT_LINK','login.php?cmd=force_login&login_to_purchase_object=1&forceShoppingCartRedirect=1');
							}
							else
							{
								ilUtil::sendInfo($this->lng->txt('click_to_buy_info'));
								$tpl->setVariable('TXT_BUY', $this->lng->txt('pay_click_to_buy'));
								$tpl->setVariable('SCRIPT_LINK', $this->ctrl->getLinkTargetByClass('ilPurchaseBillGUI', ''));
								$tpl->parseCurrentBlock('terms_checkbox');
							}
						}
						break;

					case 'bmf':
						#$tpl->setVariable("SCRIPT_LINK", './payment.php?view=start_bmf');
						if ($this->totalAmount[$pay_method['pm_id']] <= 0 && ANONYMOUS_USER_ID != $this->user_obj->getId())
						{
							$tpl->setVariable('TXT_UNLOCK', $this->lng->txt('pay_click_to_buy'));
							$tpl->setVariable('LINK_UNLOCK', $this->ctrl->getLinkTarget($this, 'unlockBMFObjectsInShoppingCart'));
						}
						else
						{
							# Anonymous user has to login
							if(ANONYMOUS_USER_ID == $this->user_obj->getId())
							{
								ilUtil::sendInfo($this->lng->txt('click_to_continue_info'));
								$tpl->setVariable('TXT_BUY', $this->lng->txt('continue'));
								$tpl->setVariable('SCRIPT_LINK','login.php?cmd=force_login&login_to_purchase_object=1&forceShoppingCartRedirect=1');
							}
							else
							{
								$tpl->setVariable('TXT_BUY', $this->lng->txt('pay_click_to_buy'));
								$tpl->setVariable('SCRIPT_LINK', $this->ctrl->getLinkTargetByClass('ilPurchaseBMFGUI', ''));
							}
						}
						break;

					case 'epay':
							# Anonymous user has to login
//						if(ANONYMOUS_USER_ID == $ilUser->getId())
//						{
//							$tpl->setVariable('TXT_BUY', $this->lng->txt('pay_click_to_buy'));
//							$tpl->setVariable('SCRIPT_LINK','login.php?cmd=force_login&login_to_purchase_object=1&forceShoppingCartRedirect=1');
//						}
//						else
//						{
//							/// http://uk.epay.dk/support/docs.asp?solution=2#pfinput
//							$tpl->setVariable('TXT_BUY', $this->lng->txt('pay_click_to_buy'));
//							$tpl->setVariable('SCRIPT_LINK', 'https://'.$this->epayConfig['server_host'].$this->epayConfig['server_path']);
//							$tpl->setVariable('MERCHANT_NUMBER', $this->epayConfig['merchant_number']);
//							$tpl->setVariable('AMOUNT', $total_price * 100);
//							$tpl->setVariable('CURRENCY', "208");
//							$tpl->setVariable('ORDERID', $ilUser->getId()."_".uniqid());
//							$tpl->setVariable('ACCEPT_URL', ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTarget($this, 'finishEPay'));
//							$tpl->setVariable('DECLINE_URL', ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTarget($this, 'cancelEPay'));
//							$tpl->setVariable('INSTANT_CAPTURE', $this->epayConfig['instant_capture'] ? "1" : "0");
//							$tpl->setVariable('ADDFEE', 1);
//							$tpl->setVariable('LANGUAGE', 1);
//							$tpl->setVariable('GROUP', "");
//							$tpl->setVariable('CARDTYPE', "");
//							$tpl->setVariable("CALLBACK_URL", ILIAS_HTTP_PATH . "/Services/Payment/classes/class.ilCallback.php?ilUser=" .$ilUser->getId() . "&pay_method=". PAY_METHOD_EPAY);
//
//							$tpl->setVariable('DESCRIPTION', $ilUser->getFullName() . " (" . $ilUser->getEmail() . ") #" . $ilUser->getId() . " " . implode(",", $desc));
//							$tpl->setVariable('AUTH_MAIL', $this->epayConfig['auth_email']);
//							$tpl->setVariable('MD5KEY', $this->epSet->generateKeyForEpay(208, $total_price*100, $ilUser->getId()."_".uniqid()));
//						}
						break;
								
					case 'paypal':
						if ($this->totalAmount[$pay_method['pm_id']] <= 0 && ANONYMOUS_USER_ID != $this->user_obj->getId())
						{
							$tpl->touchBlock('attach_submit_event');
							$tpl->setVariable('TXT_BUY', $this->lng->txt('pay_click_to_buy'));
							$tpl->setVariable('SCRIPT_LINK', $this->ctrl->getLinkTarget($this, 'unlockPAYPALObjectsInShoppingCart'));
						}
						else
						{
							if(ANONYMOUS_USER_ID == $this->user_obj->getId())# && $force_user_login == true)
							{
								ilUtil::sendInfo($this->lng->txt('click_to_continue_info'));
								$tpl->touchBlock('attach_submit_event');
								$tpl->setVariable('TXT_BUY', $this->lng->txt('pay_click_to_buy'));
								$tpl->setVariable('SCRIPT_LINK','login.php?cmd=force_login&login_to_purchase_object=1&forceShoppingCartRedirect=1');
							}
							else
							{
								$tpl->setCurrentBlock('terms_checkbox');
								ilUtil::sendInfo($this->lng->txt('click_to_buy_info'));
								$tpl->setVariable('TXT_BUY', $this->lng->txt('pay_click_to_buy'));
								$tpl->setVariable('SCRIPT_LINK', 'https://'.$this->paypalConfig['server_host'].$this->paypalConfig['server_path']);
								$tpl->parseCurrentBlock('terms_checkbox');
							}
						}

						$tpl->setVariable('POPUP_BLOCKER', $this->lng->txt('popup_blocker'));
						$tpl->setVariable('VENDOR', $this->paypalConfig['vendor']);
						$tpl->setVariable('RETURN', ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTarget($this, 'finishPaypal'));
						$tpl->setVariable('CANCEL_RETURN', ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTarget($this, 'cancelPaypal'));
						$tpl->setVariable('CUSTOM', $this->user_obj->getId());
						$tpl->setVariable('CURRENCY', $genSet->get('currency_unit'));
						$tpl->setVariable('PAGE_STYLE', $this->paypalConfig['page_style']);

						if (!empty($_SESSION['coupons'][$coupon_session_id]))
						{
							$coupon_discount_items = $this->psc_obj->calcDiscountPrices($_SESSION['coupons'][$coupon_session_id]);

							if (is_array($coupon_discount_items) && !empty($coupon_discount_items))
							{
								foreach ($coupon_discount_items as $item)
								{
									$tmp_pobject = new ilPaymentObject($this->user_obj, $item['pobject_id']);

									$obj_id = $ilObjDataCache->lookupObjId($tmp_pobject->getRefId());
									$obj_title = $ilObjDataCache->lookupTitle($obj_id);

									$tmp_amount = round($item['discount_price'], 2);
									$loop_items_amount = str_replace(',','.',$tmp_amount);

									$tpl->setCurrentBlock('loop_items');
									$tpl->setVariable('LOOP_ITEMS_NO', (++$paypal_counter));
									$tpl->setVariable('LOOP_ITEMS_NAME', "[".$obj_id."]: ".$obj_title);
									$tpl->setVariable('LOOP_ITEMS_AMOUNT',$loop_items_amount );
															
									$tpl->parseCurrentBlock('loop_items');

									unset($tmp_pobject);
								}
							}
						}
						break;
					}

					if ($pay_method['pm_title'] == 'paypal')
					{
					  $tpl->setVariable('PAYPAL_HINT', $this->lng->txt('pay_hint_paypal'));
					  $tpl->setVariable('PAYPAL_INFO', $this->lng->txt('pay_info_paypal'));								
					} 
					else if ($pay_method['pm_title'] == 'epay') 
					{
					  $tpl->setVariable('EPAY_HINT', $this->lng->txt('pay_hint_epay'));
                      $tpl->setVariable('EPAY_INFO', $this->lng->txt('pay_info_epay'));
					}

					$tpl->parseCurrentBlock('buy_link');						

					$tpl->setCurrentBlock('loop');					
					unset($f_result);

					$tpl->parseCurrentBlock('loop');					

					$this->tpl->setVariable(''.strtoupper($pay_method['pm_title']).'', $tpl->get());
					
					$num_items += $counter;
				}
			}

		if ($num_items == 0)
		{
	#		ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));
			return false;
		}
		return true;
	}

	public function removeCoupon()
	{
		if (is_array($_SESSION['coupons']))
		{
			foreach ($_SESSION['coupons'] as $key => $val)
			{
				unset($_SESSION['coupons'][$key][$_GET['coupon_id']]);
			}
		}
				
		$this->showItems();
		return true;		
	}

	public function setCoupon()
	{
		if ($_POST['coupon_code'] != '')
		{
			$coupon = $this->coupon_obj->getCouponByCode($_POST['coupon_code']);
			switch ($this->coupon_obj->checkCouponValidity())
			{
				case 1:
				case 2:
					ilUtil::sendInfo($this->lng->txt('paya_coupons_not_valid'));
					$this->showItems();
					return true;
				case 3:
					ilUtil::sendInfo($this->lng->txt('paya_coupons_coupon_not_found'));
					$this->showItems();
					return true;
			}
			$assignedItems = 0;
			$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);
			if (count($items = $this->psc_obj->getEntries(isset($_POST['payment_type']) ? $_POST['payment_type'] : 'bmf')))
			{
				foreach($items as $item)
				{
					$tmp_pobject = new ilPaymentObject($this->user_obj,$item['pobject_id']);
					if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
					{
						++$assignedItems;
					}
				}
			}
			if (!$assignedItems)
			{
				ilUtil::sendInfo($this->lng->txt('paya_coupons_no_object_assigned'));
				$this->showItems();
				return true;
			}
			$coupon_session_id = $_POST['payment_type'];
			if (!array_key_exists($coupon['pc_pk'], $_SESSION['coupons'][$coupon_session_id]))
			{
				if (is_array($_SESSION['coupons']))
				{
					foreach ($_SESSION['coupons'] as $key => $val)
					{
						unset($_SESSION['coupons'][$key][$coupon['pc_pk']]);
					}
				}
				ilUtil::sendInfo($this->lng->txt('paya_coupons_coupon_added'));
				$_SESSION['coupons'][$coupon_session_id][$coupon['pc_pk']] = $coupon;
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt('paya_coupons_already_in_use'));
			}
			$this->showItems();
			return true;
		}
		$this->showItems();
		return true;
	}
/*
	* 
	* @param	string		$payment_type		pm_title
	* 
	*/	
	private function showCouponInput($payment_type = '')
	{
		include_once './Services/Payment/classes/class.ilPaymentSettings.php';
		$genSet = ilPaymentSettings::_getInstance();
		
		$tpl = new ilTemplate('tpl.pay_shopping_cart_coupons.html', true, true, 'Services/Payment');
		
		$tpl->setVariable('COUPON_FORMACTION', $this->ctrl->getFormAction($this));
		$tpl->setVariable('TITLE', $this->lng->txt('paya_coupons_coupons'));
		$tpl->setVariable('TYPE_IMG', ilObject::_getIcon('', '', 'pays'));		
		$tpl->setVariable('ALT_IMG', $this->lng->txt('obj_usr'));
		
		$tpl->setVariable('TXT_CODE', $this->lng->txt('paya_coupons_code'));
		$tpl->setVariable('CMD_VALUE', $this->lng->txt('send'));
		$tpl->setVariable('CMD', 'setCoupon');
		
		$tpl->setVariable('PAYMENT_TYPE', $payment_type);
		
		$coupon_session = $payment_type;

		if (!empty($_SESSION['coupons'][$coupon_session]))
		{
			$i = 0;
			foreach ($_SESSION['coupons'][$coupon_session] as $coupon)
			{
				$tpl->setCurrentBlock('loop');
				$tpl->setVariable('LOOP_ROW', ilUtil::switchColor($i++, '1', '2'));								
				$tpl->setVariable('LOOP_TXT_COUPON', $this->lng->txt('paya_coupons_coupon'));
				$tpl->setVariable('LOOP_CODE', $coupon['pcc_code']);
				$this->ctrl->setParameter($this, 'coupon_id', $coupon['pc_pk']);
				$this->ctrl->setParameter($this, 'payment_type',  $_SESSION['bmf']['payment_type']);
				$tpl->setVariable('LOOP_TITLE', $coupon['pc_title']);
				if ($coupon['pc_description'] != '') $tpl->setVariable('LOOP_DESCRIPTION', nl2br($coupon['pc_description']));								
				$tpl->setVariable("LOOP_TYPE",
						sprintf($this->lng->txt('paya_coupons_'.($coupon['pc_type'] == "fix" ? 'fix' : 'percentaged').'_'.(count($coupon['objects']) == 0 ? 'all' : 'selected').'_objects'),
						 (((float)$coupon['pc_value'] / round($coupon['pc_value'], 2) == 1 && $coupon['pc_type'] == "percent")
							? round($coupon['pc_value'], 2)
							: number_format($coupon['pc_value'], 2, ',', '.')),
															 ($coupon['pc_type'] == "percent" ? "%" :$genSet->get('currency_unit'))));
				$tpl->setVariable("LOOP_REMOVE", "<div class=\"il_ContainerItemCommands\" style=\"float: right;\"><a class=\"il_ContainerItemCommand\" href=\"".$this->ctrl->getLinkTarget($this, 'removeCoupon')."\">".$this->lng->txt('remove')."</a></div>");
				
				$tpl->parseCurrentBlock();
			}
		}
		
		return $tpl->get();
	}
/*
	* 
	* @param	array		$a_pay_method		
	* 
	*/	
	private function showItemsTable(&$a_tpl, $a_result_set, $a_pay_method = 0)
	{
		include_once './Services/Payment/classes/class.ilPaymentSettings.php';
		$genSet = ilPaymentSettings::_getInstance();
		
		include_once './Services/Payment/classes/class.ilShoppingCartTableGUI.php';

		$tbl = new ilShoppingCartTableGUI($this);
		$tbl->setId('tbl_id_'.$a_pay_method);
		$tbl->setTitle($this->lng->txt('paya_shopping_cart').
				" (".$this->lng->txt('payment_system').": ".
				ilPayMethods::getStringByPaymethod($a_pay_method['pm_title']) .")");

		$coupon_session = $a_pay_method['pm_title'];

		$tbl->setRowTemplate("tpl.shop_shoppingcart_row.html", "Services/Payment");
		$tbl->addColumn('', 'item', '1%', true);
		$tbl->addColumn($this->lng->txt('title'), "table". $a_pay_method['pm_title']."_title", '30%');
		$tbl->addColumn($this->lng->txt('duration'), "table". $a_pay_method['pm_title']."_duration", '30%');
		$tbl->addColumn($this->lng->txt('vat_rate'), "table". $a_pay_method['pm_title']."_vat_rate", '15%');
		$tbl->addColumn($this->lng->txt('vat_unit'), "table". $a_pay_method['pm_title']."_vat_unit", '15%');
		$tbl->addColumn($this->lng->txt('price_a'), "table". $a_pay_method['pm_title']."_price", '10%');

		$tbl->setPrefix("table". $a_pay_method['pm_title']."_");
		$tbl->addMultiCommand('deleteItem', $this->lng->txt('delete'));
	
		// show total amount of costs
		$sc_obj = new ilPaymentShoppingCart($this->user_obj);
		$totalAmount =  $sc_obj->getTotalAmount();

		if (!empty($_SESSION['coupons'][$coupon_session]))
		{
			if (count($items = $sc_obj->getEntries($a_pay_method['pm_id'])))
			{
				$tbl->setTotalData('TXT_SUB_TOTAL', $this->lng->txt('pay_bmf_subtotal_amount') . ": ");
				$tbl->setTotalData('VAL_SUB_TOTAL', number_format($totalAmount[$a_pay_method['pm_id']], 2, ',', '.') . " " . $genSet->get('currency_unit'));

				foreach ($_SESSION['coupons'][$coupon_session] as $coupon)
				{
					$this->coupon_obj->setId($coupon['pc_pk']);
					$this->coupon_obj->setCurrentCoupon($coupon);					
					
					$total_object_price = 0.0;
					$current_coupon_bonus = 0.0;

					foreach ($items as $item)
					{
						$tmp_pobject = new ilPaymentObject($this->user_obj, $item['pobject_id']);						
						
						if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
						{			
							$price_data = ilPaymentPrices::_getPrice($item['price_id']);									
							$price = (float) $price_data['price'];
							
							$total_object_price += $price;																					
						}
						unset($tmp_pobject);
					}					
					
					$current_coupon_bonus = $this->coupon_obj->getCouponBonus($total_object_price);					
					$totalAmount[$current_coupon_bonus] += $current_coupon_bonus * (-1);
				}
					$tbl->setTotalData('TXT_COUPON_BONUS', $this->lng->txt('paya_coupons_coupon') . ": ");# . $coupon['pcc_code'] . ": ");
					#$tbl->setTotalData('VAL_COUPON_BONUS', number_format($current_coupon_bonus * (-1), 2, ',', '.') . " " . $genSet->get('currency_unit'));
					$tbl->setTotalData('VAL_COUPON_BONUS', number_format($totalAmount[$current_coupon_bonus], 2, ',', '.') . " " . $genSet->get('currency_unit'));
				
				if ($totalAmount[$a_pay_method['pm_id']] < 0)
				{
					$totalAmount[$a_pay_method['pm_id']] = 0;
					$this->totalVat = 0;
				}
			}				
		}

		$this->totalAmount[$a_pay_method['pm_id']] = $totalAmount[$a_pay_method['pm_id']]-($totalAmount[$current_coupon_bonus] * (-1));
		$tbl->setTotalData('TXT_TOTAL_AMOUNT', $this->lng->txt('pay_bmf_total_amount').": ");
		$tbl->setTotalData('VAL_TOTAL_AMOUNT',  number_format($this->totalAmount[$a_pay_method['pm_id']] , 2, ',', '.') . " " . $genSet->get('currency_unit')); #.$item['currency']);

		if ($this->totalVat > 0)
		{
			$tbl->setTotalData('TXT_TOTAL_VAT', $this->lng->txt('pay_bmf_vat_included') . ": ");
			$tbl->setTotalData('VAL_TOTAL_VAT',  number_format($this->totalVat , 2, ',', '.') . " " . $genSet->get('currency_unit'));
		}
						
		$tbl->setData($a_result_set);
		$a_tpl->setVariable('ITEMS_TABLE',$tbl->getCartHTML());

		return true;
	}

	public function deleteItem()
	{
		if(!count($_POST['item']))
		{
			ilUtil::sendInfo($this->lng->txt('pay_select_one_item'));

			$this->showItems();
			return true;
		}
		$this->initShoppingCartObject();

		foreach($_POST['item'] as $id)
		{
			$this->psc_obj->delete($id);
			$this->checkCouponsOfShoppingCart();
		}
		ilUtil::sendInfo($this->lng->txt('pay_deleted_items'));
		$this->showItems();

		return true;
	}

	private function initShoppingCartObject()
	{
		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);
		$this->psc_obj->clearCouponItemsSession();
	}

	private function initPaypalObject()
	{
		include_once './Services/Payment/classes/class.ilPurchasePaypal.php';
		$this->paypal_obj = new ilPurchasePaypal($this->user_obj);
	}

    /**
     * Creates a new encrypted button HTML block
     *
     * @param array $buttonParams The button parameters as key/value pairs
     * @return mixed A string of HTML or a Paypal error object on failure
     */
    private function encryptButton($buttonParams)
    {
        $merchant_cert = $this->paypalConfig["vendor_cert"];
        $merchant_key = $this->paypalConfig["vendor_key"];
        $end_cert = $this->paypalConfig["enc_cert"];

        $tmpin_file  = tempnam('/tmp', 'paypal_');
        $tmpout_file = tempnam('/tmp', 'paypal_');
        $tmpfinal_file = tempnam('/tmp', 'paypal_');

        $rawdata = array();
        $buttonParams['cert_id'] = $this->paypalConfig["cert_id"];
        foreach ($buttonParams as $name => $value) {
            $rawdata[] = "$name=$value";
        }
        $rawdata = implode("\n", $rawdata);

        $fp = fopen($tmpin_file, 'w');
        if (!$fp) {
            echo "Could not open temporary file '$tmpin_file')";
			return false;
#            return PayPal::raiseError("Could not open temporary file '$tmpin_file')");
        }
        fwrite($fp, $rawdata);
        fclose($fp);

        if (!@openssl_pkcs7_sign($tmpin_file, $tmpout_file, $merchant_cert,
                                 array($merchant_key, $this->paypalConfig["private_key_password"]),
                                 array(), PKCS7_BINARY)) {
			echo "Could not sign encrypted data: " . openssl_error_string();
			return false;
#            return PayPal::raiseError("Could not sign encrypted data: " . openssl_error_string());
        }

        $data = file_get_contents($tmpout_file);
        $data = explode("\n\n", $data);
        $data = $data[1];
        $data = base64_decode($data);
        $fp = fopen($tmpout_file, 'w');
        if (!$fp) {
            echo "Could not open temporary file '$tmpin_file')";
			return false;
#            return PayPal::raiseError("Could not open temporary file '$tmpin_file')");
        }
        fwrite($fp, $data);
        fclose($fp);

        if (!@openssl_pkcs7_encrypt($tmpout_file, $tmpfinal_file, $end_cert, array(), PKCS7_BINARY)) {
            echo "Could not encrypt data:" . openssl_error_string();
			return false;
#            return PayPal::raiseError("Could not encrypt data:" . openssl_error_string());
        }

        $encdata = @file_get_contents($tmpfinal_file, false);
        if (!$encdata) {
            echo "Encryption and signature of data failed.";
			return false;
#            return PayPal::raiseError("Encryption and signature of data failed.");
        }

        $encdata = explode("\n\n", $encdata);
        $encdata = trim(str_replace("\n", '', $encdata[1]));
        $encdata = "-----BEGIN PKCS7-----$encdata-----END PKCS7-----";

        @unlink($tmpfinal_file);
        @unlink($tmpin_file);
        @unlink($tmpout_file);

		return $encdata;
    }
}
?>