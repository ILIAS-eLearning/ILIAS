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

include_once './payment/classes/class.ilPurchasePaypal.php';
include_once './payment/classes/class.ilPaymentShoppingCart.php';
include_once 'Services/Payment/classes/class.ilShopBaseGUI.php';
include_once './payment/classes/class.ilPaypalSettings.php';
include_once './payment/classes/class.ilPaymentCoupons.php';
include_once 'Services/Payment/classes/class.ilShopVats.php';

/**
* Class ilShopShoppingCartGUI
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
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

	public function __construct($user_obj)
	{		
		parent::__construct();
		
		$this->user_obj = $user_obj;
		
		$this->coupon_obj = new ilPaymentCoupons($this->user_obj);

		$ppSet = ilPaypalSettings::getInstance();
		$this->paypalConfig = $ppSet->getAll();	
		
		$this->checkCouponsOfShoppingCart();		
	}
	
	/**
	* execute command
	*/
	public function executeCommand()
	{
		global $ilUser;

		if(ANONYMOUS_USER_ID == $ilUser->getId())
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		$cmd = $this->ctrl->getCmd();
		switch ($this->ctrl->getNextClass($this))
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
		$this->addBookings(PAY_METHOD_BILL, 'bill');
		$_SESSION['coupons']['bill'] = '';
		
		ilUtil::sendInfo($this->lng->txt('pay_bmf_thanks'), true);
		
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
		$this->addBookings(PAY_METHOD_PAYPAL, 'paypal');
		
		$_SESSION['coupons']['paypal'] = array();
		
		ilUtil::sendInfo($this->lng->txt('pay_paypal_success'), true);
		
		$this->ctrl->redirectByClass('ilShopBoughtObjectsGUI', '');	
		
		return true;
	}
	
	private function addBookings($pay_method, $coupon_session)
	{
		include_once './payment/classes/class.ilPaymentBookings.php';
		include_once './payment/classes/class.ilPaymentObject.php';
		include_once './payment/classes/class.ilPaymentPrices.php';	
		
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

		$i = 0;
		foreach($items as $entry)
		{

			$pobject =& new ilPaymentObject($this->user_obj, $entry['pobject_id']);

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
			//$booking_obj->setDiscount($bonus > 0 ? ilPaymentPrices::_getPriceStringFromAmount((-1) * $bonus) : '');
			$booking_obj->setDiscount($bonus > 0 ? ilPaymentPrices::_getPriceStringFromAmount((-1) * $bonus) : 0);
			$booking_obj->setPayed(1);
			$booking_obj->setAccess(1);

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
		include_once './payment/classes/class.ilPayMethods.php';
		
		if (ilPayMethods::_enabled('pm_bill')) $pay_methods[] = PAY_METHOD_BILL;
		if (ilPayMethods::_enabled('pm_bmf')) $pay_methods[] = PAY_METHOD_BMF;
		if (ilPayMethods::_enabled('pm_paypal')) $pay_methods[] = PAY_METHOD_PAYPAL;		

		if (is_array($pay_methods))
		{			
			for ($p = 0; $p < count($pay_methods); $p++)
			{	
				if ($pay_methods[$p] == PAY_METHOD_BILL)
				{
					$coupon_session_id = 'bill';
				}			
				else if ($pay_methods[$p] == PAY_METHOD_BMF)
				{
					$coupon_session_id = 'bmf';
				}
				else if ($pay_methods[$p] == PAY_METHOD_PAYPAL)
				{
					$coupon_session_id = 'paypal';
				}

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
						
							if (count($items = $this->psc_obj->getEntries($pay_methods[$p])))
							{
								foreach($items as $item)
								{
									$tmp_pobject =& new ilPaymentObject($this->user_obj, $item['pobject_id']);
									
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

	public function finishPaypal()
	{
		$this->initPaypalObject();

		if (!($fp = $this->paypal_obj->openSocket()))
		{
			ilUtil::sendInfo($this->lng->txt('pay_paypal_failed').'<br />'.$this->lng->txt('pay_paypal_unreachable').'<br />'.$this->lng->txt('pay_paypal_error_info'));
			$this->showItems();
		}
		else
		{
			$res = $this->paypal_obj->checkData($fp);
			if ($res == SUCCESS)
			{
				ilUtil::sendInfo($this->lng->txt('pay_paypal_success'), true);				
				$this->ctrl->redirectByClass('ilShopBoughtObjectsGUI', '');	
			}
			else
			{
				switch ($res)
				{
					case ERROR_WRONG_CUSTOMER	:	ilUtil::sendInfo($this->lng->txt('pay_paypal_failed').'<br />'.$this->lng->txt('pay_paypal_error_wrong_customer').'<br />'.$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_NOT_COMPLETED	:	ilUtil::sendInfo($this->lng->txt('pay_paypal_failed').'<br />'.$this->lng->txt('pay_paypal_error_not_completed').'<br />'.$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_PREV_TRANS_ID	:	ilUtil::sendInfo($this->lng->txt('pay_paypal_failed').'<br />'.$this->lng->txt('pay_paypal_error_prev_trans_id').'<br />'.$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_WRONG_VENDOR		:	ilUtil::sendInfo($this->lng->txt('pay_paypal_failed').'<br />'.$this->lng->txt('pay_paypal_error_wrong_vendor').'<br />'.$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_WRONG_ITEMS		:	ilUtil::sendInfo($this->lng->txt('pay_paypal_failed').'<br />'.$this->lng->txt('pay_paypal_error_wrong_items').'<br />'.$this->lng->txt('pay_paypal_error_info'));
													break;
					case ERROR_FAIL				:	ilUtil::sendInfo($this->lng->txt('pay_paypal_failed').'<br />'.$this->lng->txt('pay_paypal_error_fails').'<br />'.$this->lng->txt('pay_paypal_error_info'));
													break;
				}
				$this->showItems();
			}
			fclose($fp);
		}
	}

	public function cancelPaypal()
	{
		ilUtil::sendInfo($this->lng->txt('pay_paypal_canceled'));
		$this->showItems();
	}

	public function showItems()
	{
		global $ilObjDataCache, $ilUser;

		include_once './payment/classes/class.ilPaymentPrices.php';

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_shopping_cart.html','payment');

		$this->initShoppingCartObject();

		include_once './payment/classes/class.ilGeneralSettings.php';

		$genSet = new ilGeneralSettings();

		include_once './payment/classes/class.ilPayMethods.php';
		if (ilPayMethods::_enabled('pm_bill')) $pay_methods[] = PAY_METHOD_BILL;
		if (ilPayMethods::_enabled('pm_bmf')) $pay_methods[] = PAY_METHOD_BMF;
		if (ilPayMethods::_enabled('pm_paypal')) $pay_methods[] = PAY_METHOD_PAYPAL;		

		$num_items = 0;
		if (is_array($pay_methods))
		{			
			for ($p = 0; $p < count($pay_methods); $p++)
			{		
				$this->totalVat = 0;
					
				if ($pay_methods[$p] == PAY_METHOD_BILL)
				{
					$tpl = new ilTemplate('./payment/templates/default/tpl.pay_shopping_cart_bill.html',true,true);
					$coupon_session_id = 'bill';
				}
				else if ($pay_methods[$p] == PAY_METHOD_BMF)
				{
					$tpl =& new ilTemplate('./payment/templates/default/tpl.pay_shopping_cart_bmf.html',true,true);
					$coupon_session_id = 'bmf';
				}
				else if ($pay_methods[$p] == PAY_METHOD_PAYPAL)
				{
					$tpl =& new ilTemplate('./payment/templates/default/tpl.pay_shopping_cart_paypal.html',true,true);
					$coupon_session_id = 'paypal';
				}

				if (count($items = $this->psc_obj->getEntries($pay_methods[$p])))
				{
					$counter = 0;
					$paypal_counter = 0;
			
					foreach ($items as $item)
					{
						$tmp_pobject =& new ilPaymentObject($this->user_obj,$item['pobject_id']);
			
						$obj_id = $ilObjDataCache->lookupObjId($tmp_pobject->getRefId());
						$obj_type = $ilObjDataCache->lookupType($obj_id);
						$obj_title = $ilObjDataCache->lookupTitle($obj_id);
						$price_arr = ilPaymentPrices::_getPrice($item['price_id']);
						
						$direct_paypal_info_output = true;
						
						$assigned_coupons = '';					
						if (!empty($_SESSION['coupons'][$coupon_session_id]))
						{			
							//$price = $price_arr['unit_value'].".".$price_arr['sub_unit_value'];
							$price = $price_arr['price'];						
							$item['math_price'] = (float) $price;
														
							foreach ($_SESSION['coupons'][$coupon_session_id] as $key => $coupon)
							{
								$this->coupon_obj->setId($coupon['pc_pk']);
								$this->coupon_obj->setCurrentCoupon($coupon);

								if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
								{
									$assigned_coupons .= '<br />' . $this->lng->txt('paya_coupons_coupon') . ': ' . $coupon['pcc_code'];

									$_SESSION['coupons'][$coupon_session_id][$key]['total_objects_coupon_price'] += (float) $price;
									$_SESSION['coupons'][$coupon_session_id][$key]['items'][] = $item;
									$direct_paypal_info_output = false;
								}
							}
						}
						else
						{
							//$price = $price_arr['unit_value'].".".$price_arr['sub_unit_value'];	
						}

						$f_result[$counter][] = ilUtil::formCheckBox(0,'item[]',$item['psc_id']);
						$f_result[$counter][] = "<a href=\"goto.php?target=".$obj_type."_".$tmp_pobject->getRefId() . "\">".$obj_title."</a>";
						if ($assigned_coupons != '') $f_result[$counter][count($f_result[$counter]) - 1] .= $assigned_coupons;
						if($price_arr['duration'] == 0)
						{
							$f_result[$counter][] = $this->lng->txt('unlimited_duration');
						}
						else
						$f_result[$counter][] = $price_arr['duration'].' '.$this->lng->txt('paya_months');
						
						$f_result[$counter][] = $tmp_pobject->getVatRate().' % ';
					
						$f_result[$counter][] = $tmp_pobject->getVat($price_arr['price'],$item['pobject_id']).' '.$genSet->get('currency_unit');
						$this->totalVat = $this->totalVat + $tmp_pobject->getVat($price_arr['price'],$item['pobject_id']);
						
						$f_result[$counter][] = ilPaymentPrices::_getPriceString($item['price_id']);

						if ($pay_methods[$p] == PAY_METHOD_PAYPAL)
						{
							if ($direct_paypal_info_output == true) // Paypal information in hidden fields
							{				
								$tpl->setCurrentBlock('loop_items');
								$tpl->setVariable('LOOP_ITEMS_NO', (++$paypal_counter));
								$tpl->setVariable('LOOP_ITEMS_NAME', "[".$obj_id."]: ".$obj_title);
								$tpl->setVariable('LOOP_ITEMS_AMOUNT', $price);
								$tpl->parseCurrentBlock('loop_items');														
							}

#							$buttonParams["item_name_".($counter+1)] = $obj_title;
#							$buttonParams["amount_".($counter+1)] = $price_arr['unit_value'].".".$price_arr['sub_unit_value'];
						}
						
						++$counter;
						unset($tmp_pobject);					
					}
					
					$this->showItemsTable($tpl, $f_result, $pay_methods[$p]);

					$tpl->setVariable('COUPON_TABLE', $this->showCouponInput($pay_methods[$p]));
										
					$tpl->setCurrentBlock('buy_link');
					switch($pay_methods[$p])
					{
						case PAY_METHOD_BILL:
							if ($this->totalAmount[PAY_METHOD_BILL] == 0)
							{
								$tpl->setVariable('TXT_UNLOCK', $this->lng->txt('pay_click_to_buy'));
								$tpl->setVariable('LINK_UNLOCK', $this->ctrl->getLinkTarget($this, 'unlockBillObjectsInShoppingCart'));
							}
							else
							{
								$tpl->setVariable('TXT_BUY', $this->lng->txt('pay_click_to_buy'));
								$tpl->setVariable('SCRIPT_LINK', $this->ctrl->getLinkTargetByClass('ilPurchaseBillGUI', ''));
							}	
							break;

						case PAY_METHOD_BMF:					
							#$tpl->setVariable("SCRIPT_LINK", './payment.php?view=start_bmf');							
							if ($this->totalAmount[PAY_METHOD_BMF] == 0)
							{
								$tpl->setVariable('TXT_UNLOCK', $this->lng->txt('pay_click_to_buy'));
								$tpl->setVariable('LINK_UNLOCK', $this->ctrl->getLinkTarget($this, 'unlockBMFObjectsInShoppingCart'));
							}
							else
							{
								$tpl->setVariable('TXT_BUY', $this->lng->txt('pay_click_to_buy'));
								$tpl->setVariable('SCRIPT_LINK', $this->ctrl->getLinkTargetByClass('ilPurchaseBMFGUI', ''));
							}	
							break;
		
						case PAY_METHOD_PAYPAL:							
							if ($this->totalAmount[PAY_METHOD_PAYPAL] == 0)
							{
								$tpl->setVariable('TXT_BUY', $this->lng->txt('pay_click_to_buy'));
								$tpl->setVariable('SCRIPT_LINK', $this->ctrl->getLinkTarget($this, 'unlockPAYPALObjectsInShoppingCart'));
							}
							else
							{
								$tpl->setVariable('TXT_BUY', $this->lng->txt('pay_click_to_buy'));
								$tpl->setVariable('SCRIPT_LINK', 'https://'.$this->paypalConfig['server_host'].$this->paypalConfig['server_path']);								
							}	
							
							$tpl->setVariable('POPUP_BLOCKER', $this->lng->txt('popup_blocker'));
							$tpl->setVariable('VENDOR', $this->paypalConfig['vendor']);
							$tpl->setVariable('RETURN', ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTarget($this, 'finishPaypal'));
							$tpl->setVariable('CANCEL_RETURN', ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTarget($this, 'cancelPaypal'));
							$tpl->setVariable('CUSTOM', $ilUser->getId());
							$tpl->setVariable('CURRENCY', $genSet->get('currency_unit'));
							$tpl->setVariable('PAGE_STYLE', $this->paypalConfig['page_style']);
							
							if (!empty($_SESSION['coupons'][$coupon_session_id]))
							{
								$coupon_discount_items = $this->psc_obj->calcDiscountPrices($_SESSION['coupons'][$coupon_session_id]);
																
								if (is_array($coupon_discount_items) && !empty($coupon_discount_items))
								{
									foreach ($coupon_discount_items as $item)
									{
										$tmp_pobject =& new ilPaymentObject($this->user_obj, $item['pobject_id']);
			
										$obj_id = $ilObjDataCache->lookupObjId($tmp_pobject->getRefId());										
										$obj_title = $ilObjDataCache->lookupTitle($obj_id);
														
										$tpl->setCurrentBlock('loop_items');
										$tpl->setVariable('LOOP_ITEMS_NO', (++$paypal_counter));
										$tpl->setVariable('LOOP_ITEMS_NAME', "[".$obj_id."]: ".$obj_title);
										$tpl->setVariable('LOOP_ITEMS_AMOUNT', round($item['discount_price'], 2));
										$tpl->parseCurrentBlock('loop_items');
										
										unset($tmp_pobject);
									}										
								}															
							}

#							$buttonParams["upload"] = 1;
#							$buttonParams["charset"] = "utf-8";
#							$buttonParams["business"] = $this->paypalConfig["vendor"];
#							$buttonParams["currency_code"] = "EUR";
#							$buttonParams["return"] = "http://www.databay.de/user/jens/paypal.php";
#							$buttonParams["rm"] = 2;
#							$buttonParams["cancel_return"] = "http://www.databay.de/user/jens/paypal.php";
#							$buttonParams["custom"] = "HALLO";
#							$buttonParams["invoice"] = "0987654321";
#							if ($enc_data = $this->__encryptButton($buttonParams))
#							{
#								$tpl->setVariable("ENCDATA", $enc_data);
#							}

							break;
					}
					$tpl->setVariable('PAYPAL_HINT', $this->lng->txt('pay_hint_paypal'));
					$tpl->setVariable('PAYPAL_INFO', $this->lng->txt('pay_info_paypal'));								
					
					$tpl->parseCurrentBlock('buy_link');						

					$tpl->setCurrentBlock('loop');					
					unset($f_result);

					$tpl->parseCurrentBlock('loop');					

					if ($pay_methods[$p] == PAY_METHOD_BILL)
						$this->tpl->setVariable('BILL', $tpl->get());
						
					else if ($pay_methods[$p] == PAY_METHOD_BMF)
						$this->tpl->setVariable('BMF', $tpl->get());
						
					else if ($pay_methods[$p] == PAY_METHOD_PAYPAL)
						$this->tpl->setVariable('PAYPAL', $tpl->get());

					$num_items += $counter;
				}
			}
		}
		
		if ($num_items == 0)
		{
			ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));

			return false;
		}
		else
		{
			return true;
		}

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
			if (count($items = $this->psc_obj->getEntries(isset($_POST['payment_type']) ? $_POST['payment_type'] : PAY_METHOD_BMF)))
			{
				foreach($items as $item)
				{
					$tmp_pobject =& new ilPaymentObject($this->user_obj,$item['pobject_id']);
					
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
			
			switch ($_POST['payment_type'])
			{
				case PAY_METHOD_BILL:
					$coupon_session_id = 'bill';
					break;
				case PAY_METHOD_PAYPAL:			 		
			 		$coupon_session_id = 'paypal';			 		
			 		break;
			 	case PAY_METHOD_BMF:
			 		$coupon_session_id = 'bmf';
			 		break;			 	
			}
			
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
	
	private function showCouponInput($payment_type = '')
	{
		include_once './payment/classes/class.ilGeneralSettings.php';
		$genSet = new ilGeneralSettings();
		
		$tpl = new ilTemplate('tpl.pay_shopping_cart_coupons.html', true, true, 'payment');
		
		$tpl->setVariable('COUPON_FORMACTION', $this->ctrl->getFormAction($this));
		$tpl->setVariable('TITLE', $this->lng->txt('paya_coupons_coupons'));
		$tpl->setVariable('TYPE_IMG', ilUtil::getImagePath('icon_pays_b.gif'));		
		$tpl->setVariable('ALT_IMG', $this->lng->txt('obj_usr'));
		
		$tpl->setVariable('TXT_CODE', $this->lng->txt('paya_coupons_code'));
		$tpl->setVariable('CMD_VALUE', $this->lng->txt('send'));
		
		$tpl->setVariable('PAYMENT_TYPE', $payment_type);
		
		switch ($payment_type)
		{
			case PAY_METHIOD_BILL:
				$coupon_session = 'bill';
		 		break;
		 	case PAY_METHOD_PAYPAL:
		 		$coupon_session = 'paypal';
		 		break;
		 	case PAY_METHOD_BMF:
		 		$coupon_session = 'bmf';
		 		break;
		 	default:
		 		$coupon_session = 'bmf';
		 		break;
		}

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
				$tpl->setVariable("LOOP_TYPE", sprintf($this->lng->txt('paya_coupons_'.($coupon['pc_type'] == "fix" ? 'fix' : 'percentaged').'_'.(count($coupon['objects']) == 0 ? 'all' : 'selected').'_objects'),
															 ($coupon['pc_value'] / round($coupon['pc_value'], 0) == 1 && $coupon['pc_type'] == "percent" ? round($coupon['pc_value'], 0) : number_format($coupon['pc_value'], 2, ',', '.')), 
															 ($coupon['pc_type'] == "percent" ? "%" :$genSet->get('currency_unit'))));
				$tpl->setVariable("LOOP_REMOVE", "<div class=\"il_ContainerItemCommands\" style=\"float: right;\"><a class=\"il_ContainerItemCommand\" href=\"".$this->ctrl->getLinkTarget($this, 'removeCoupon')."\">".$this->lng->txt('remove')."</a></div>");
				
				$tpl->parseCurrentBlock();
			}
		}
		
		return $tpl->get();
	}
	
	private function showItemsTable(&$a_tpl, $a_result_set, $a_pay_method = 0)
	{
		include_once './payment/classes/class.ilGeneralSettings.php';

		$genSet = new ilGeneralSettings();
		
		include_once('Services/Table/classes/class.ilTableGUI.php');

		$tbl = new ilTableGUI(array(), false);
		$tpl = $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock('tbl_form_header');

		$tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock('tbl_action_row');
		$tpl->setCurrentBlock('plain_buttons');
		$tpl->parseCurrentBlock();

		$tpl->setVariable('COLUMN_COUNTS',6);
		$tpl->setVariable('IMG_ARROW', ilUtil::getImagePath('arrow_downright.gif'));

		$tpl->setCurrentBlock('tbl_action_button');
		$tpl->setVariable('BTN_NAME','deleteItem');
		$tpl->setVariable('BTN_VALUE',$this->lng->txt('delete'));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock('tbl_action_row');
		$tpl->setVariable('TPLPATH',$this->tpl->tplPath);
		$tpl->parseCurrentBlock();

		$title = $this->lng->txt('paya_shopping_cart');
		switch($a_pay_method)
		{

			case PAY_METHOD_BILL:
				$coupon_session = 'bill';
				$title .= " (" . $this->lng->txt('payment_system') . ": " . $this->lng->txt('pays_bill') . ")";
				break;
				
			case PAY_METHOD_BMF:
				$coupon_session = 'bmf';
				$title .= " (" . $this->lng->txt('payment_system') . ": " . $this->lng->txt('pays_bmf') . ")";
				break;

			case PAY_METHOD_PAYPAL:
				$coupon_session = 'paypal';
				$title .= " (" . $this->lng->txt('payment_system') . ": " . $this->lng->txt('pays_paypal') . ")";
				break;
		}
		$tbl->setTitle($title,'icon_pays_cart.gif',$this->lng->txt('paya_shopping_cart'));
		$tbl->setHeaderNames(array($this->lng->txt(''),
								   $this->lng->txt('title'),
								   $this->lng->txt('duration'),
								   $this->lng->txt('vat_rate'),
								   $this->lng->txt('vat_unit'),
								   $this->lng->txt('price_a')));

		$tbl->setHeaderVars(array("",
								  "table".$a_pay_method."_title",
								  "table".$a_pay_method."_duration",
		  							"table".$a_pay_method."_vat_rate",
									"table".$a_pay_method."_vat_unit",
								  "table".$a_pay_method."_price"),
							$this->ctrl->getParameterArray($this, ''));

		$offset = $_GET["table".$a_pay_method."_offset"];
		$order = $_GET["table".$a_pay_method."_sort_by"];
		$direction = $_GET["table".$a_pay_method."_sort_order"] ? $_GET['table'.$a_pay_method.'_sort_order'] : 'desc';

		$tbl->setPrefix("table".$a_pay_method."_");
		$tbl->setOrderColumn($order,'table'.$a_pay_method.'_title');
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET['limit']);
		$tbl->setMaxCount(count($a_result_set));
		$tbl->setFooter('tblfooter',$this->lng->txt('previous'),$this->lng->txt('next'));
		$tbl->setData($a_result_set);


		// show total amount of costs
		$sc_obj =& new ilPaymentShoppingCart($this->user_obj);

		$totalAmount =  $sc_obj->getTotalAmount();


		//$vat = $sc_obj->getVat($totalAmount[$a_pay_method], $sc_obj->getPobjectId());

		$tpl->setCurrentBlock('tbl_footer_linkbar');
		$amount .= "<table class=\"\" style=\"float: right;\">\n";		
		if (!empty($_SESSION['coupons'][$coupon_session]))
		{
			if (count($items = $sc_obj->getEntries($a_pay_method)))
			{
				$amount .= "<tr>\n";
				$amount .= "<td>\n";
				$amount .= "<b>" . $this->lng->txt('pay_bmf_subtotal_amount') . ":";				
				$amount .= "</td>\n";
				$amount .= "<td>\n";
				$amount .= number_format($totalAmount[$a_pay_method], 2, ',', '.') . " " . $genSet->get('currency_unit') . "</b>";				
				$amount .= "</td>\n";				
				$amount .= "</tr>\n";
				
				foreach ($_SESSION['coupons'][$coupon_session] as $coupon)
				{
					$this->coupon_obj->setId($coupon['pc_pk']);
					$this->coupon_obj->setCurrentCoupon($coupon);					
					
					$total_object_price = 0.0;
					$current_coupon_bonus = 0.0;

					foreach ($items as $item)
					{
						$tmp_pobject =& new ilPaymentObject($this->user_obj, $item['pobject_id']);						
						
						if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
						{			
							$price_data = ilPaymentPrices::_getPrice($item['price_id']);									
							$price = (float) $price_data['price'];
							
							$total_object_price += $price;																					
						}
						
						unset($tmp_pobject);
					}					
					
					$current_coupon_bonus = $this->coupon_obj->getCouponBonus($total_object_price);					
					$totalAmount[$a_pay_method] += $current_coupon_bonus * (-1);
					
					$amount .= "<tr>\n";
					$amount .= "<td>\n";					
					$amount .= $this->lng->txt('paya_coupons_coupon') . " " . $coupon['pcc_code'] . ":";
					$amount .= "</td>\n";
					$amount .= "<td>\n";
					$amount .= number_format($current_coupon_bonus * (-1), 2, ',', '.') . " " . $genSet->get('currency_unit');
					$amount .= "</td>\n";
					$amount .= "</tr>\n";
				}
				
				
				if ($totalAmount[$a_pay_method] < 0)
				{
					
					$totalAmount[$a_pay_method] = 0;
					$this->totalVat = 0;
				}
				else
				{
				
					$this->totalVat = $sc_obj->getVat($totalAmount[$a_pay_method]);	
				}	
			}				
		}		
		
		$amount .= "<tr>\n";
		$amount .= "<td>\n";					
		$amount .= "<b>" . $this->lng->txt('pay_bmf_total_amount') . ":";
		$amount .= "</td>\n";
		$amount .= "<td>\n";
		$amount .= number_format($totalAmount[$a_pay_method], 2, ',', '.') . " " . $genSet->get('currency_unit');
		$amount .= "</td>\n";
		$amount .= "</tr>\n";
		
		$this->totalAmount[$a_pay_method] = $totalAmount[$a_pay_method];


		if ($this->totalVat > 0)
		{
			$amount .= "<tr>\n";
			$amount .= "<td>\n";					
			$amount .=  $this->lng->txt('pay_bmf_vat_included') . ":";
			$amount .= "</td>\n";
			$amount .= "<td>\n";
			$amount .= $this->totalVat  . " " . $genSet->get('currency_unit');
			$amount .= "</td>\n";
			$amount .= "</tr>\n";
		}
						
		$amount .= "</table>\n";

		$tpl->setVariable('LINKBAR', $amount);
		$tpl->parseCurrentBlock('tbl_footer_linkbar');
		$tpl->setCurrentBlock('tbl_footer');
		$tpl->setVariable('COLUMN_COUNT',6);
		$tpl->parseCurrentBlock();

		$tbl->render();
		
		$a_tpl->setVariable('ITEMS_TABLE',$tbl->tpl->get());

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
		$this->psc_obj =& new ilPaymentShoppingCart($this->user_obj);
		$this->psc_obj->clearCouponItemsSession();
	}

	private function initPaypalObject()
	{
		$this->paypal_obj =& new ilPurchasePaypal($this->user_obj);
	}

    /**
     * Creates a new encrypted button HTML block
     *
     * @param array The button parameters as key/value pairs
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
