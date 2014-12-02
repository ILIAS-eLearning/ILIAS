<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilPurchaseBMFGUI
*
* @author Stefan Meyer, Jens Conze
* @version $Id: class.ilPurchaseBMFGUI.php 22133 2009-10-16 08:09:11Z nkrzywon $
*
* @package core
*/

//include_once './Services/Payment/classes/class.ilPaymentShoppingCart.php';
//include_once './Services/Payment/classes/class.ilShopShoppingCartGUI.php';
//include_once './Services/Payment/classes/class.ilPaymentCoupons.php';
//include_once 'Services/Payment/classes/class.ilShopVatsList.php';

include_once './Services/Payment/classes/class.ilBMFSettings.php';
#include_once dirname(__FILE__)."/../bmf/lib/ePayment/cfg_epayment.inc.php";
include_once dirname(__FILE__)."/../bmf/lib/SOAP/class.ilBMFClient.php";

include_once './Services/Payment/classes/class.ilPayMethods.php';
include_once './Services/Payment/classes/class.ilPurchaseBaseGUI.php';



class ilPurchaseBMFGUI extends ilPurchaseBaseGUI
{
	var $ctrl;
	var $tpl;

	var $user_obj;
	var $coupon_obj = null;
	var $error;
	var $shoppingCart;

	var $soapClient;
	var $pay_method = null;
	private $totalVat = 0;
	private $pm_id = 0;
	
	function ilPurchaseBMFGUI($user_obj)
	{
		$this->pm_id = ilPayMethods::_getIdByTitle('bmf');
		$this->pay_method = ilPayMethods::_getIdByTitle('bmf');
		// Get user object
		$this->user_obj = $user_obj;
		
		parent::__construct($this->user_obj, $this->pay_method);
	}
	
	function cancel()
	{
		ilUtil::redirect("./payment.php");
	}

	function showPersonalData()
	{
		// user_id $this->user_obj->getId()
		// all

		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);

		if(!count($items = $this->psc_obj->getEntries($this->pm_id)))
		{

			$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
			$this->tpl->touchBlock("stop_floating");
			ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));

		}
		else
		{
			$this->tpl->setVariable("HEADER",$this->lng->txt('pay_step1'));
			$this->tpl->setVariable("TITLE",$this->lng->txt('pay_bmf_personal_data'));
			$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('pay_bmf_description_personal_data'));			
			
			$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');
			
			$oForm = new ilPropertyFormGUI();
			$oForm->setFormAction($this->ctrl->getFormAction($this, 'getPersonalData'));
			$oForm->setTitle($this->lng->txt('pay_bmf_personal_data'));
		
			$oFirstname = new ilNonEditableValueGUI($this->lng->txt('firstname'));
			$oFirstname->setValue($this->user_obj->getFirstname());
			$oForm->addItem($oFirstname);
			
			$oLastname = new ilNonEditableValueGUI($this->lng->txt('lastname'));
			$oLastname->setValue($this->user_obj->getLastname());
			$oForm->addItem($oLastname);
			
			$oStreet = new ilTextInputGUI($this->lng->txt('street'),'street');
			$oStreet->setValue($this->error != '' && isset($_POST['street'])
								? ilUtil::prepareFormOutput($_POST['street'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']['personal_data']['street'],true));
			$oForm->addItem($oStreet);
			
			$oHouseNumber = new ilTextInputGUI($this->lng->txt('pay_bmf_house_number'), 'house_number');
			$oHouseNumber->setValue($this->error != '' && isset($_POST['house_number'])
									? ilUtil::prepareFormOutput($_POST['house_number'],true)
									: ilUtil::prepareFormOutput($_SESSION['bmf']['personal_data']['house_number'],true));
			$oForm->addItem($oHouseNumber);
			
			$oPoBox = new ilTextInputGUI($this->lng->txt('pay_bmf_or').'  '.$this->lng->txt('pay_bmf_po_box'), 'po_box');
			$oPoBox->setValue($this->error != '' && isset($_POST['po_box'])
									? ilUtil::prepareFormOutput($_POST['po_box'],true)
									: ilUtil::prepareFormOutput($_SESSION['bmf']['personal_data']['po_box'],true));
			$oForm->addItem($oPoBox);

			$oZipCode = new ilTextInputGUI($this->lng->txt('zipcode'), 'zipcode');
			$oZipCode->setValue($this->error != '' && isset($_POST['zipcode'])
									? ilUtil::prepareFormOutput($_POST['zipcode'],true)
									: ilUtil::prepareFormOutput($_SESSION['bmf']['personal_data']['zipcode'],true));
			$oForm->addItem($oZipCode);

			$oCity = new ilTextInputGUI($this->lng->txt('city'), 'city');
			$oCity->setValue($this->error != '' && isset($_POST['city'])
									? ilUtil::prepareFormOutput($_POST['city'],true)
									: ilUtil::prepareFormOutput($_SESSION['bmf']['personal_data']['city'],true));
			$oForm->addItem($oCity);						


			$oCountry = new ilSelectInputGUI($this->lng->txt('country'), 'country');
			$oCountry->setOptions($this->__getCountries());
			$oCountry->setValue($this->error != '' && isset($_POST['country']) ? $_POST['country'] 
					: $_SESSION['bmf']['personal_data']['country']);
			$oForm->addItem($oCountry);	

			$oEmail = new ilNonEditableValueGUI($this->lng->txt('email'));
			$oEmail->setValue($this->user_obj->getEmail());
			$oForm->addItem($oEmail);
			
			$oForm->addcommandButton('getPersonalData',ucfirst($this->lng->txt('next')));		
			$this->tpl->setVariable('FORM',$oForm->getHTML());		
			
		}
	}

	function getPersonalData()
	{

		if ($_SESSION["bmf"]["personal_data"]["firstname"] == "" ||
			$_SESSION["bmf"]["personal_data"]["lastname"] == "" ||
			$_POST["zipcode"] == "" ||
			$_POST["city"] == "" ||
			$_POST["country"] == "" ||
			$_SESSION["bmf"]["personal_data"]["email"] == "")
		{
			$this->error = $this->lng->txt('pay_bmf_personal_data_not_valid');
			ilUtil::sendInfo($this->error);
			$this->showPersonalData();
			return;
		}
		if (($_POST["street"] == "" && $_POST["house_number"] == "" && $_POST["po_box"] == "") ||
			(($_POST["street"] != "" || $_POST["house_number"] != "") && $_POST["po_box"] != "") ||
			($_POST["street"] != "" && $_POST["house_number"] == "") ||
			($_POST["street"] == "" && $_POST["house_number"] != ""))
		{
			$this->error = $this->lng->txt('pay_bmf_street_or_pobox');
			ilUtil::sendInfo($this->error);
			$this->showPersonalData();
			return;
		}


		$_SESSION["bmf"]["personal_data"]["firstname"] = $this->user_obj->getFirstname();
		$_SESSION["bmf"]["personal_data"]["lastname"] = $this->user_obj->getLastname();
		$_SESSION["bmf"]["personal_data"]["street"] = $_POST["street"];
		$_SESSION["bmf"]["personal_data"]["house_number"] = $_POST["house_number"];
		$_SESSION["bmf"]["personal_data"]["po_box"] = $_POST["po_box"];
		$_SESSION["bmf"]["personal_data"]["zipcode"] = $_POST["zipcode"];
		$_SESSION["bmf"]["personal_data"]["city"] = $_POST["city"];
		$_SESSION["bmf"]["personal_data"]["country"] = $_POST["country"];
		$_SESSION["bmf"]["personal_data"]["email"] = $this->user_obj->getEmail();
		$_SESSION["bmf"]["personal_data"]["language"] = $this->user_obj->getLanguage();

		if ($_SESSION["bmf"]["personal_data"]["country"] != "DE")
		{
			if ($_SESSION["bmf"]["payment_type"] == "debit_entry")
			{
				$_SESSION["bmf"]["payment_type"] = "";
			}
		}

		$this->error = "";
		$this->showPaymentType();
	}

	function showPaymentType()
	{
		// user_id $this->user_obj->getId()
		// all

		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);

		if(!count($items = $this->psc_obj->getEntries($this->pm_id)))
		{

			$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
			$this->tpl->touchBlock("stop_floating");
			ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));

		}
		else
		{

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bmf_payment_type.html','Services/Payment');

		$this->tpl->setVariable("PAYMENT_TYPE_FORMACTION",$this->ctrl->getFormAction($this));

		// set table header
		$this->tpl->setVariable("TYPE_IMG", ilObject::_getIcon('', '', 'pays'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("HEADER",$this->lng->txt('pay_step2'));
		$this->tpl->setVariable("TITLE",$this->lng->txt('pay_bmf_payment_type'));
		$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('pay_bmf_description_payment_type'));
		$this->tpl->touchBlock("stop_floating");
		$this->tpl->setVariable("TXT_CLOSE_WINDOW",$this->lng->txt('close_window'));

		// set plain text variables
		if ($_SESSION["bmf"]["personal_data"]["country"] == "DE")
		{
			$this->tpl->setVariable("TXT_DEBIT_ENTRY",$this->lng->txt('pay_bmf_debit_entry'));
		}
		$this->tpl->setVariable("TXT_CREDIT_CARD",$this->lng->txt('pay_bmf_credit_card'));

		$this->tpl->setVariable("INPUT_VALUE",ucfirst($this->lng->txt('next')));
		$this->tpl->setVariable("CANCEL",$this->lng->txt('cancel'));

		// fill defaults

		if ($this->error != "" &&
			isset($_POST["payment_type"]))
		{
			$this->tpl->setVariable("PAYMENT_TYPE_" . strtoupper($_POST["payment_type"]), " checked") ;
		}
		else
		{
			if (($_SESSION["bmf"]["personal_data"]["country"] != "DE" && $_POST["payment_type"] != "debit_entry") ||
				$_SESSION["bmf"]["personal_data"]["country"] == "DE")
			{
				$this->tpl->setVariable("PAYMENT_TYPE_" . strtoupper($_SESSION["bmf"]["payment_type"]), " checked") ;
			}
		}

		// Button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "showPersonalData"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt('pay_bmf_back'));
		$this->tpl->parseCurrentBlock("btn_cell");

		}
	}

	function getPaymentType()
	{
		if (($_POST["payment_type"] != "credit_card" && $_POST["payment_type"] != "debit_entry") ||
			($_SESSION["bmf"]["personal_data"]["country"] != "DE" && $_POST["payment_type"] == "debit_entry"))
		{
			$this->error = $this->lng->txt('pay_bmf_payment_type_not_valid');
			ilUtil::sendInfo($this->error);
			$this->showPaymentType();
			return;
		}

		$_SESSION["bmf"]["payment_type"] = $_POST["payment_type"];

		$this->error = "";
		if ($_SESSION["bmf"]["payment_type"] == "credit_card")
		{
			$this->showCreditCard();
		}
		else
		{
			$this->showDebitEntry();
		}
	}

	function showDebitEntry()
	{
		// user_id $this->user_obj->getId()
		// all

/*		if ($_SESSION["bmf"]["debit_entry"]["kontoinhaber"] == "" &&
			$this->error == "" &&
			$_POST["account_holder"] == "")
		{
			$_SESSION["bmf"]["debit_entry"]["kontoinhaber"] = $_SESSION["bmf"]["personal_data"]["vorname"] . " " . $_SESSION["bmf"]["personal_data"]["nachname"];
		}*/

		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);

		if(!count($items = $this->psc_obj->getEntries($this->pm_id)))
		{

			$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
			$this->tpl->touchBlock("stop_floating");
			ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));

		}
		else
		{

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bmf_debit_entry.html','Services/Payment');
		
		$this->__showShoppingCart();

		$this->tpl->setVariable("DEBIT_ENTRY_FORMACTION",$this->ctrl->getFormAction($this));

		// set table header
		$this->tpl->setVariable("TYPE_IMG",ilObject::_getIcon('', '', 'pays'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("HEADER",$this->lng->txt('pay_step3_debit_entry'));
		$this->tpl->setVariable("TITLE",$this->lng->txt('pay_bmf_debit_entry_data'));
		$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('pay_bmf_description_debit_entry'));
		$this->tpl->touchBlock("stop_floating");
		$this->tpl->setVariable("TXT_CLOSE_WINDOW",$this->lng->txt('close_window'));

		// set plain text variables
		$this->tpl->setVariable("TXT_ACCOUNT_HOLDER",$this->lng->txt('pay_bmf_account_holder'));
		$this->tpl->setVariable("TXT_OPTIONAL",$this->lng->txt('pay_bmf_optional'));
		$this->tpl->setVariable("TXT_BANK_CODE",$this->lng->txt('pay_bmf_bank_code'));
		$this->tpl->setVariable("TXT_ACCOUNT_NUMBER",$this->lng->txt('pay_bmf_account_number'));
		$this->tpl->setVariable("TXT_TERMS_CONDITIONS",$this->lng->txt('pay_bmf_terms_conditions'));
		$this->tpl->setVariable("TXT_TERMS_CONDITIONS_READ",$this->lng->txt('pay_bmf_terms_conditions_read'));
		$this->tpl->setVariable("TXT_TERMS_CONDITIONS_SHOW",$this->lng->txt('pay_bmf_terms_conditions_show'));
		$this->tpl->setVariable("LINK_TERMS_CONDITIONS","./payment.php?view=conditions");
		$this->tpl->setVariable("TXT_PASSWORD",$this->lng->txt('password'));
		$this->tpl->setVariable("TXT_CONFIRM_ORDER",$this->lng->txt('pay_confirm_order'));

		$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_send_order'));
		$this->tpl->setVariable("CANCEL",$this->lng->txt('cancel'));

		// fill defaults

		$this->tpl->setVariable("ACCOUNT_HOLDER",
								$this->error != "" && isset($_POST['account_holder'])
								? ilUtil::prepareFormOutput($_POST['account_holder'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["debit_entry"]['kontoinhaber'],true));
		$this->tpl->setVariable("BANK_CODE",
								$this->error != "" && isset($_POST['bank_code'])
								? ilUtil::prepareFormOutput($_POST['bank_code'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["debit_entry"]['BLZ'],true));
		$this->tpl->setVariable("ACCOUNT_NUMBER",
								$this->error != "" && isset($_POST['account_number'])
								? ilUtil::prepareFormOutput($_POST['account_number'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["debit_entry"]['kontoNr'],true));
/*		if ($this->error != "" &&
			isset($_POST["terms_conditions"]))
		{
			$this->tpl->setVariable("TERMS_CONDITIONS_" . strtoupper($_POST["terms_conditions"]), " checked") ;
		}*/
/*		if ($this->error != "" &&
			isset($_POST["password"]))
		{
			$this->tpl->setVariable("PASSWORD", ilUtil::prepareFormOutput($_POST['password'],true));
		}*/

		// Button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "showPaymentType"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt('pay_bmf_back'));
		$this->tpl->parseCurrentBlock("btn_cell");

		}
	}

	function getDebitEntry()
	{
		if ($_POST["account_holder"] == "" ||
			$_POST["bank_code"] == "" ||
			$_POST["account_number"] == "")
		{
			$this->error = $this->lng->txt('pay_bmf_debit_entry_not_valid');
			ilUtil::sendInfo($this->error);
			$this->showDebitEntry();
			return;
		}
		if ($_POST["terms_conditions"] != 1)
		{
			$this->error = $this->lng->txt('pay_bmf_check_terms_conditions');
			ilUtil::sendInfo($this->error);
			$this->showDebitEntry();
			return;
		}
		if ($_POST["password"] == "" ||
			md5($_POST["password"]) != $this->user_obj->getPasswd())
		{
			$this->error = $this->lng->txt('pay_bmf_password_not_valid');
			ilUtil::sendInfo($this->error);
			$this->showDebitEntry();
			return;
		}

		$_SESSION["bmf"]["debit_entry"]["BLZ"] = $_POST["bank_code"];
		$_SESSION["bmf"]["debit_entry"]["kontoinhaber"] = $_POST["account_holder"];
		$_SESSION["bmf"]["debit_entry"]["kontoNr"] = $_POST["account_number"];

		$this->error = "";
		$this->sendDebitEntry();
	}

	function sendDebitEntry()
	{
		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);
		
		$this->psc_obj->clearCouponItemsSession();

		if(!count($items = $this->psc_obj->getEntries($this->pm_id)))
		{

			$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
			$this->tpl->touchBlock("stop_floating");
			ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));

		}
		else
		{
			$customer = new KundenstammdatenPflegeWS();
	
			$newCustomer = new Kunde($this->user_obj->getId());
			
			$resultCustomerObj = $customer->anlegenKunde($newCustomer);
	
			$resultCustomer = $resultCustomerObj->ergebnis;

			if (is_object($resultCustomer))
			{
				if ($resultCustomer->code < 0)
				{
					$error = $this->lng->txt('pay_bmf_server_error_code') . " " . $resultCustomer->code . ": " . $resultCustomer->kurzText . "<br>\n" . $resultCustomer->langText;
					if ($resultCustomer->code == -103 ||
						$resultCustomer->code == -104 ||
						$resultCustomer->code == -107 ||
						($resultCustomer->code <= -202 && $resultCustomer->code >= -208) ||
						$resultCustomer->code == -213)
					{
						ilUtil::sendInfo($error);
						$this->showPersonalData();
					}
					else
					{
						$error .= "<br>\n" . $this->lng->txt('pay_bmf_server_error_sysadmin');
						ilUtil::sendInfo($error);
						$this->showPersonalData();
					}
				}
				else
				{
					$payment = new LastschriftWS();
			
					$debitEntry = new Lastschrift();
			
					$address = new LieferAdresse();
			
					$bank = new Bankverbindung();
		
					$sc_obj = new ilPaymentShoppingCart($this->user_obj);
	
					$tmp_bookEntries = $sc_obj->getShoppingCart();

					if (!is_array($tmp_bookEntries))
					{
						ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));
					}
					else
					{
						$totalAmount = 0;
						for ($i = 0; $i < count($tmp_bookEntries); $i++)
						{
							$booking = true;
							
							if (!empty($_SESSION["coupons"]["bmf"]))
							{
								$price = $tmp_bookEntries[$i]["price"];									
								$tmp_bookEntries[$i]["math_price"] = $price;
																					
								foreach ($_SESSION["coupons"]["bmf"] as $key => $coupon)
								{				
									$this->coupon_obj->setId($coupon["pc_pk"]);
									$this->coupon_obj->setCurrentCoupon($coupon);
									
									$tmp_pobject = new ilPaymentObject($this->user_obj, $tmp_bookEntries[$i]['pobject_id']);						
							
									if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
									{
										$_SESSION["coupons"]["bmf"][$key]["total_objects_coupon_price"] += $price;
										$_SESSION["coupons"]["bmf"][$key]["items"][] = $tmp_bookEntries[$i];
										$booking = false;									
									}								
									
									unset($tmp_pobject);
								}							
							}										
								
							if ($booking)
							{												
								$tmp_bookEntries[$i]["price_string"] = number_format( (float) $tmp_bookEntries[$i]["price"] , 2, ".", "");
								$bookEntries[] = new Buchung($tmp_bookEntries[$i]);
								$totalAmount += $tmp_bookEntries[$i]["price"];
							}
							else
							{
								$tmp_bookEntries[$i]["price_string"] = number_format( (float) $tmp_bookEntries[$i]["price"] , 2, ",", ".");
							}												
						}
						
						$coupon_discount_items = $this->psc_obj->calcDiscountPrices($_SESSION["coupons"]["bmf"]);

						if (is_array($coupon_discount_items) && !empty($coupon_discount_items))
						{
							foreach ($coupon_discount_items as $item)
							{
								$item["price"]= number_format( (float) $item["discount_price"], 2, ".", "");
								$bookEntries[] = new Buchung($item);
								$totalAmount += $item["price"];
							}										
						}

						$totalAmount = number_format( (float) $totalAmount , 2, ".", "");
						$values = array("betrag" => $totalAmount, "buchungen" => $bookEntries);						
						$bookingList = new BuchungsListe($this->user_obj->getId(), $values);
					}
			

			
					$resultObj = $payment->abbuchenOhneEinzugsermaechtigung($resultCustomerObj->kunde->EShopKundenNr, $address, $bank, $bookingList);
					$result = $resultObj->ergebnis;
	
					if (is_object($result))
					{
						if ($result->code < 0)
						{
							$this->tpl->setVariable("HEADER",$this->lng->txt('error'));
							$this->tpl->touchBlock("stop_floating");
							$error = $this->lng->txt('pay_bmf_server_error_code') . " " . $result->code . ": " . $result->kurzText . "<br>\n" . $result->langText;
							if ($result->code == -103 ||
								$result->code == -104 ||
								$result->code == -107 ||
								($result->code <= -202 && $result->code >= -208) ||
								$result->code == -213)
							{
								ilUtil::sendInfo($error);
								$this->showPersonalData();
							}
							else if ($result->code == -507 ||
								$result->code == -510 ||
								$result->code == -511)
							{
								ilUtil::sendInfo($error);
								$this->showPaymentType();
							}
							else if ($result->code == -402 ||
								$result->code == -402 ||
								$result->code == -403 ||
								$result->code == -406 ||
								$result->code == -410 ||
								$result->code == -413 ||
								$result->code == -701 ||
								$result->code == -702 ||
								$result->code == -703)
							{
								ilUtil::sendInfo($error);
								$this->showDebitEntry();
							}
							else
							{
								$error .= "<br>\n" . $this->lng->txt('pay_bmf_server_error_sysadmin');
								ilUtil::sendInfo($error);
								$this->showPersonalData();
							}
			
						}
						else
						{
							$resultCustomerObj->kunde->vorname = utf8_decode($resultCustomerObj->kunde->vorname);
							$resultCustomerObj->kunde->nachname = utf8_decode($resultCustomerObj->kunde->nachname);
							$resultCustomerObj->kunde->rechnungsAdresse->strasse  = utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->strasse);
							$resultCustomerObj->kunde->rechnungsAdresse->hausNr  = utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->hausNr);
							$resultCustomerObj->kunde->rechnungsAdresse->postfach = utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->postfach);
							$resultCustomerObj->kunde->rechnungsAdresse->PLZ = utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->PLZ);
							$resultCustomerObj->kunde->rechnungsAdresse->ort = utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->ort);
							$resultCustomerObj->kunde->rechnungsAdresse->land = utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->land);

							// everything ok => send confirmation, fill statistik, delete session, delete shopping cart.
							$external_data = array();
							$external_data['voucher'] = $resultObj->buchungsListe->buchungen[$b++]->belegNr;
							$external_data['transaction_extern'] = $resultObj->buchungsListe->kassenzeichen;
							$external_data['street'] = utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->strasse).' '.utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->hausNr);
							$external_data['po_box'] = utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->postfach);
							$external_data['zipcode'] = utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->PLZ);
							$external_data['city'] = utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->ort);
							$external_data['country'] =  utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->land);
							
							parent::__addbookings($external_data);
							
							$this->__emptyShoppingCart();
							$this->__clearSession();

							$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
							$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('pay_bmf_thanks'));
							$this->tpl->touchBlock("stop_floating");
							
							ilUtil::sendInfo($this->lng->txt('pay_bmf_thanks'));
	
							$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.pay_bmf_debit_entry.html','Services/Payment');

							if ($this->ilias->getSetting("https") != 1)
							{
								$this->tpl->setCurrentBlock("buyed_objects");
								$link = $this->ctrl->getLinkTargetByClass('ilshopboughtobjectsgui');
								$this->tpl->setVariable("LINK_GOTO_BUYED_OBJECTS", $link);
								$this->tpl->setVariable("TXT_GOTO_BUYED_OBJECTS", $this->lng->txt('pay_goto_buyed_objects'));
								$this->tpl->parseCurrentBlock("buyed_objects");
							}
							$this->tpl->setVariable("TXT_CLOSE_WINDOW", $this->lng->txt('close_window'));
						}
					}
					else
					{
						$this->tpl->setVariable("HEADER",$this->lng->txt('error'));
						$this->tpl->touchBlock("stop_floating");
						ilUtil::sendInfo($this->lng->txt('pay_bmf_server_error_communication'));
					}
				}
			}
			else
			{
				$this->tpl->setVariable("HEADER",$this->lng->txt('error'));
				$this->tpl->touchBlock("stop_floating");
				ilUtil::sendInfo($this->lng->txt('pay_bmf_server_error_communication'));
			}
		}
	}

	function showCreditCard()
	{
		// user_id $this->user_obj->getId()
		// all

/*		if ($_SESSION["bmf"]["credit_card"]["karteninhaber"] == "" &&
			$this->error == "" &&
			$_POST["card_holder"] == "")
		{
			$_SESSION["bmf"]["credit_card"]["karteninhaber"] = $_SESSION["bmf"]["personal_data"]["vorname"] . " " . $_SESSION["bmf"]["personal_data"]["nachname"];
		}*/

		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);

		if(!count($items = $this->psc_obj->getEntries($this->pm_id)))
		{

			$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
			$this->tpl->touchBlock("stop_floating");
			ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));

		}
		else
		{

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bmf_credit_card.html','Services/Payment');
		$this->__showShoppingCart();

		$this->tpl->setVariable("CREDIT_CARD_FORMACTION",$this->ctrl->getFormAction($this));

		// set table header
		$this->tpl->setVariable("TYPE_IMG",ilObject::_getIcon('', '', 'pays'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("HEADER",$this->lng->txt('pay_step3_credit_card'));
		$this->tpl->setVariable("TITLE",$this->lng->txt('pay_bmf_credit_card_data'));
		$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('pay_bmf_description_credit_card'));
		$this->tpl->touchBlock("stop_floating");
		$this->tpl->setVariable("TXT_CLOSE_WINDOW",$this->lng->txt('close_window'));

		// set plain text variables
		$this->tpl->setVariable("TXT_CARD_HOLDER",$this->lng->txt('pay_bmf_card_holder'));
		$this->tpl->setVariable("TXT_CHECK_NUMBER",$this->lng->txt('pay_bmf_check_number'));
		$this->tpl->setVariable("TXT_OPTIONAL",$this->lng->txt('pay_bmf_optional'));
		$this->tpl->setVariable("TXT_CARD_NUMBER",$this->lng->txt('pay_bmf_card_number'));
		$this->tpl->setVariable("TXT_VALIDITY",$this->lng->txt('pay_bmf_validity'));
		$this->tpl->setVariable("TXT_TERMS_CONDITIONS",$this->lng->txt('pay_bmf_terms_conditions'));
		$this->tpl->setVariable("TXT_TERMS_CONDITIONS_READ",$this->lng->txt('pay_bmf_terms_conditions_read'));
		$this->tpl->setVariable("TXT_TERMS_CONDITIONS_SHOW",$this->lng->txt('pay_bmf_terms_conditions_show'));
		$this->tpl->setVariable("LINK_TERMS_CONDITIONS","./payment.php?view=conditions");
		$this->tpl->setVariable("TXT_PASSWORD",$this->lng->txt('password'));
		$this->tpl->setVariable("TXT_CONFIRM_ORDER",$this->lng->txt('pay_confirm_order'));

		$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('pay_send_order'));
		$this->tpl->setVariable("CANCEL",$this->lng->txt('cancel'));

		// fill defaults

		$this->tpl->setVariable("CARD_HOLDER",
								$this->error != "" && isset($_POST['card_holder'])
								? ilUtil::prepareFormOutput($_POST['card_holder'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["credit_card"]['karteninhaber'],true));
		$this->tpl->setVariable("CARD_NUMBER_BLOCK_1",
								$this->error != "" && isset($_POST['card_number']['block_1'])
								? ilUtil::prepareFormOutput($_POST['card_number']['block_1'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["credit_card"]['kreditkartenNr']['block_1'],true));
		$this->tpl->setVariable("CARD_NUMBER_BLOCK_2",
								$this->error != "" && isset($_POST['card_number']['block_2'])
								? ilUtil::prepareFormOutput($_POST['card_number']['block_2'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["credit_card"]['kreditkartenNr']['block_2'],true));
		$this->tpl->setVariable("CARD_NUMBER_BLOCK_3",
								$this->error != "" && isset($_POST['card_number']['block_3'])
								? ilUtil::prepareFormOutput($_POST['card_number']['block_3'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["credit_card"]['kreditkartenNr']['block_3'],true));
		$this->tpl->setVariable("CARD_NUMBER_BLOCK_4",
								$this->error != "" && isset($_POST['card_number']['block_4'])
								? ilUtil::prepareFormOutput($_POST['card_number']['block_4'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["credit_card"]['kreditkartenNr']['block_4'],true));
		$this->tpl->setVariable("CHECK_NUMBER",
								$this->error != "" && isset($_POST['check_number'])
								? ilUtil::prepareFormOutput($_POST['check_number'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["credit_card"]['kartenpruefnummer'],true));
		for ($i = 1; $i <= 12; $i++)
		{
			$this->tpl->setCurrentBlock("loop_validity_months");
			$this->tpl->setVariable("LOOP_VALIDITY_MONTHS", $i < 10 ? "0" . $i : $i);
			$this->tpl->setVariable("LOOP_VALIDITY_MONTHS_TXT", $i < 10 ? "0" . $i : $i);
			if ($this->error != "" &&
				isset($_POST['validity']['month']))
			{
				if ($_POST['validity']['month'] == $i)
				{
					$this->tpl->setVariable("LOOP_VALIDITY_MONTHS_SELECTED", " selected");
				}
			}
			else
			{
				if ($_SESSION["bmf"]["credit_card"]["gueltigkeit"]["monat"] == $i)
				{
					$this->tpl->setVariable("LOOP_VALIDITY_MONTHS_SELECTED", " selected");
				}
			}
			$this->tpl->parseCurrentBlock("loop_validity_months");
		}
		for ($i = date("Y"); $i <= (date("Y")+6); $i++)
		{
			$this->tpl->setCurrentBlock("loop_validity_years");
			$this->tpl->setVariable("LOOP_VALIDITY_YEARS", $i);
			$this->tpl->setVariable("LOOP_VALIDITY_YEARS_TXT", $i);
			if ($this->error != "" &&
				isset($_POST['validity']['year']))
			{
				if ($_POST['validity']['year'] == $i)
				{
					$this->tpl->setVariable("LOOP_VALIDITY_YEARS_SELECTED", " selected");
				}
			}
			else
			{
				if ($_SESSION["bmf"]["credit_card"]["gueltigkeit"]["jahr"] == $i)
				{
					$this->tpl->setVariable("LOOP_VALIDITY_YEARS_SELECTED", " selected");
				}
			}
			$this->tpl->parseCurrentBlock("loop_validity_years");
		}
/*		if ($this->error != "" &&
			isset($_POST["terms_conditions"]))
		{
			$this->tpl->setVariable("TERMS_CONDITIONS_" . $_POST["terms_conditions"], " checked") ;
		}*/
/*		if ($this->error != "" &&
			isset($_POST["password"]))
		{
			$this->tpl->setVariable("PASSWORD", ilUtil::prepareFormOutput($_POST['password'],true));
		}*/

		// Button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "showPaymentType"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt('pay_bmf_back'));
		$this->tpl->parseCurrentBlock("btn_cell");

		}
	}

	function getCreditCard()
	{
		if ($_POST["card_holder"] == "" ||
			$_POST["card_number"]["block_1"] == "" ||
			$_POST["card_number"]["block_2"] == "" ||
			$_POST["card_number"]["block_3"] == "" ||
			$_POST["card_number"]["block_4"] == "" ||
			$_POST["validity"]["month"] == "" ||
			$_POST["validity"]["year"] == "" ||
			$_POST["validity"]["year"]."-".$_POST["validity"]["month"] < date("Y-m"))
		{
			$this->error = $this->lng->txt('pay_bmf_credit_card_not_valid');
			ilUtil::sendInfo($this->error);
			$this->showCreditCard();
			return;
		}
		if ($_POST["terms_conditions"] != 1)
		{
			$this->error = $this->lng->txt('pay_bmf_check_terms_conditions');
			ilUtil::sendInfo($this->error);
			$this->showCreditCard();
			return;
		}
		if ($_POST["password"] == "" ||
			md5($_POST["password"]) != $this->user_obj->getPasswd())
		{
			$this->error = $this->lng->txt('pay_bmf_password_not_valid');
			ilUtil::sendInfo($this->error);
			$this->showCreditCard();
			return;
		}

		$_SESSION["bmf"]["credit_card"]["gueltigkeit"]["monat"] = $_POST["validity"]["month"];
		$_SESSION["bmf"]["credit_card"]["gueltigkeit"]["jahr"] = $_POST["validity"]["year"];
		$_SESSION["bmf"]["credit_card"]["karteninhaber"] = $_POST["card_holder"];
		$_SESSION["bmf"]["credit_card"]["kreditkartenNr"]["block_1"] = $_POST["card_number"]["block_1"];
		$_SESSION["bmf"]["credit_card"]["kreditkartenNr"]["block_2"] = $_POST["card_number"]["block_2"];
		$_SESSION["bmf"]["credit_card"]["kreditkartenNr"]["block_3"] = $_POST["card_number"]["block_3"];
		$_SESSION["bmf"]["credit_card"]["kreditkartenNr"]["block_4"] = $_POST["card_number"]["block_4"];
		$_SESSION["bmf"]["credit_card"]["kartenpruefnummer"] = $_POST["check_number"];
/**/ # zum testen
		$this->error = "";
		$this->sendCreditCard();
	}

	function sendCreditCard()
	{
		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);
		
		$this->psc_obj->clearCouponItemsSession();

		if(!count($items = $this->psc_obj->getEntries($this->pm_id)))
		{

			$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
			$this->tpl->touchBlock("stop_floating");
			ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));

		}
		else
		{
			$payment = new KreditkartenzahlungWS();
	
			$customer = new Kunde($this->user_obj->getId());
	
			$creditCard = new Kreditkarte();
	
			$sc_obj = new ilPaymentShoppingCart($this->user_obj);
	
			$tmp_bookEntries = $sc_obj->getShoppingCart();
			if (!is_array($tmp_bookEntries))
			{
				ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));
			}
			else
			{
				$totalAmount = 0;
				for ($i = 0; $i < count($tmp_bookEntries); $i++)
				{
					$booking = true;
					
					if (!empty($_SESSION["coupons"]["bmf"]))
					{
						$price = $tmp_bookEntries[$i]["price"];
						$tmp_bookEntries[$i]["math_price"] = $price;
																			
						foreach ($_SESSION["coupons"]["bmf"] as $key => $coupon)
						{				
							$this->coupon_obj->setId($coupon["pc_pk"]);
							$this->coupon_obj->setCurrentCoupon($coupon);
							
							$tmp_pobject = new ilPaymentObject($this->user_obj, $tmp_bookEntries[$i]['pobject_id']);						
					
							if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
							{
								$_SESSION["coupons"]["bmf"][$key]["total_objects_coupon_price"] += $price;
								$_SESSION["coupons"]["bmf"][$key]["items"][] = $tmp_bookEntries[$i];
								
								$booking = false;									
							}								
							
							unset($tmp_pobject);
						}							
					}										
						
					if ($booking)
					{												
						$tmp_bookEntries[$i]["price_string"] = number_format( (float) $tmp_bookEntries[$i]["price"] , 2, ",", ".");				
																	
						$bookEntries[] = new Buchung($tmp_bookEntries[$i]);
						$totalAmount += $tmp_bookEntries[$i]["price"];
					}
					else
					{
						$tmp_bookEntries[$i]["price_string"] = number_format( (float) $tmp_bookEntries[$i]["price"] , 2, ",", ".");				
					}												
				}
				
				$coupon_discount_items = $this->psc_obj->calcDiscountPrices($_SESSION["coupons"]["bmf"]);
					
				if (is_array($coupon_discount_items) && !empty($coupon_discount_items))
				{
					foreach ($coupon_discount_items as $item)
					{
						$item["price"] =  number_format((float)$item["discount_price"] , 2, ".", "");
						$bookEntries[] = new Buchung($item);
						$totalAmount += $item["discount_price"];
					}										
				}
				
				$totalAmount = number_format((float)$totalAmount , 2, ".", "");
				$values = array("betrag" => $totalAmount, "buchungen" => $bookEntries);						
				$bookingList = new BuchungsListe($this->user_obj->getId(), $values);
			}
	
			$resultObj = $payment->zahlenUndAnlegenKunde($customer, $creditCard, $bookingList);
			$result = $resultObj->ergebnis;
	
			if (is_object($result))
			{
				if ($result->code < 0)
				{
					$this->tpl->setVariable("HEADER",$this->lng->txt('error'));
					$this->tpl->touchBlock("stop_floating");
					$error = $this->lng->txt('pay_bmf_server_error_code') . " " . $result->code . ": " . $result->kurzText . "<br>\n" . $result->langText;
					if ($result->code == -103 ||
						$result->code == -104 ||
						$result->code == -107 ||
						($result->code <= -202 && $result->code >= -208) ||
						$result->code == -213)
					{
						ilUtil::sendInfo($error);
						$this->showPersonalData();
					}
					else if ($result->code == -507 ||
						$result->code == -510 ||
						$result->code == -511)
					{
						ilUtil::sendInfo($error);
						$this->showPaymentType();
					}
					else if ($result->code == -701 ||
						$result->code == -1701 ||
						$result->code == -1706 ||
						$result->code == -1707 ||
						$result->code == -1710 ||
						$result->code == -1711)
					{
						ilUtil::sendInfo($error);
						$this->showCreditCard();
					}
					else
					{
						$error .= "<br>\n" . $this->lng->txt('pay_bmf_server_error_sysadmin');
						ilUtil::sendInfo($error);
						$this->showPersonalData();
					}
				}
				else
				{
					// everything ok => send confirmation, fill statistik, delete session, delete shopping cart.
					$external_data = array();
					$external_data['voucher'] = $resultObj->buchungsListe->buchungen[$b++]->belegNr;
					$external_data['transaction_extern'] = $resultObj->buchungsListe->kassenzeichen;
					$external_data['street'] = utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->strasse).' '.utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->hausNr);
					$external_data['po_box'] = utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->postfach);
					$external_data['zipcode'] = utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->PLZ);
					$external_data['city'] = utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->ort);
					$external_data['country'] =  utf8_decode($resultCustomerObj->kunde->rechnungsAdresse->land);
					
					parent::__addbookings($external_data);
					
					$this->__emptyShoppingCart();
					$this->__clearSession();
	
					$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
					$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('pay_bmf_thanks'));
					$this->tpl->touchBlock("stop_floating");
					
					ilUtil::sendInfo($this->lng->txt('pay_bmf_thanks'));
	
					$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.pay_bmf_credit_card.html','Services/Payment');

					if ($this->ilias->getSetting("https") != 1)
					{
						$this->tpl->setCurrentBlock("buyed_objects");
						$link = $this->ctrl->getLinkTargetByClass('ilshopboughtobjectsgui');
						$this->tpl->setVariable("LINK_GOTO_BUYED_OBJECTS", $link);
						$this->tpl->setVariable("TXT_GOTO_BUYED_OBJECTS", $this->lng->txt('pay_goto_buyed_objects'));
						$this->tpl->parseCurrentBlock("buyed_objects");
					}
					$this->tpl->setVariable("TXT_CLOSE_WINDOW", $this->lng->txt('close_window'));
				}
			}
			else
			{
				$this->tpl->setVariable("HEADER",$this->lng->txt('error'));
				$this->tpl->touchBlock("stop_floating");
				ilUtil::sendInfo($this->lng->txt('pay_bmf_server_error_communication'));
			}
/**/ #zum testen
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
}


class KundenstammdatenPflegeWS
{
   
	var $_soapClient = NULL;
      
	function KundenstammdatenPflegeWS ()
	{
		$bmfSetObj = ilBMFSettings::getInstance();
		$bmfConfig = $bmfSetObj->getAll();		

		$this->_soapClient = new ilBMFClient($bmfConfig["ePaymentServer"], false, false, array('curl' => array(CURLOPT_SSLCERT => $bmfConfig["clientCertificate"], CURLE_SSL_PEER_CERTIFICATE => $bmfConfig["caCertificate"], CURLOPT_TIMEOUT => (int)$bmfConfig["timeOut"])));
	}
      
	function anlegenKunde ($customer)
	{
		$bmfSetObj = ilBMFSettings::getInstance();
		$bmfConfig = $bmfSetObj->getAll();

		$tmp = array(
			'mandantNr' => $bmfConfig["mandantNr"],
			'kunde' => $customer
		);

		$result = $this->_soapClient->call("anlegenKunde", $tmp, "KundenstammdatenPflegeWS");
		return $result;
      }

};

class KreditkartenzahlungWS
{
	var $_soapClient = NULL;

	function KreditkartenzahlungWS()
	{
		$bmfSetObj = ilBMFSettings::getInstance();
		$bmfConfig = $bmfSetObj->getAll();

		$this->_soapClient = new ilBMFClient($bmfConfig["ePaymentServer"], false, false, array('curl' => array(CURLOPT_SSLCERT => $bmfConfig["clientCertificate"], CURLE_SSL_PEER_CERTIFICATE => $bmfConfig["caCertificate"], CURLOPT_TIMEOUT => (int)$bmfConfig["timeOut"])));
	}

	function validierenKreditkarte($creditCard)
	{
		$bmfSetObj = ilBMFSettings::getInstance();
		$bmfConfig = $bmfSetObj->getAll();

		$tmp = array(
			'mandantNr' => $bmfConfig["mandantNr"],
			'kreditkarte' => $creditCard,
			'waehrungskennzeichen' => $bmfConfig["waehrungskennzeichen"]
		);

		$result = $this->_soapClient->call("validierenKreditkarte", $tmp, "KreditkartenzahlungWS");
		return $result;
	}

	function zahlenUndAnlegenKunde($customer, $creditCard, $bookingList)
	{
		$bmfSetObj = ilBMFSettings::getInstance();
		$bmfConfig = $bmfSetObj->getAll();

		$lieferadresse = new LieferAdresse();

		$tmp = array(
			'mandantNr' => $bmfConfig["mandantNr"],
			'Kunde' => $customer,
			'Kreditkarte' => $creditCard,
			'buchungsListe' => $bookingList,
			'lieferadresse' => $lieferadresse
		);

		$result = $this->_soapClient->call("zahlenUndAnlegenKunde", $tmp, "KreditkartenzahlungWS");
		return $result;
	}
}

class LastschriftWS
{

	var $_soapClient = NULL;

	function LastschriftWS ()
	{
		$bmfSetObj = ilBMFSettings::getInstance();
		$bmfConfig = $bmfSetObj->getAll();

		$this->_soapClient = new ilBMFClient($bmfConfig["ePaymentServer"], false, false, array('curl' => array(CURLOPT_SSLCERT => $bmfConfig["clientCertificate"], CURLE_SSL_PEER_CERTIFICATE => $bmfConfig["caCertificate"], CURLOPT_TIMEOUT => (int)$bmfConfig["timeOut"])));
	}
      
	function abbuchenOhneEinzugsermaechtigung($eShopCustomerNumber, $address, $bank, $bookingList)
	{
		$bmfSetObj = ilBMFSettings::getInstance();
		$bmfConfig = $bmfSetObj->getAll();

		$tmp = array(
			'mandantNr' => $bmfConfig["mandantNr"],
			'eShopKundenNr' => $eShopCustomerNumber,
			'lieferAdresse' => $address,
			'bankverbindung' => $bank,
			'buchungsListe' => $bookingList
		);

		$result = $this->_soapClient->call("abbuchenOhneEinzugsermaechtigung", $tmp, "LastschriftWS");
		return $result;
	}

};

class Kunde
{
    var $OBJTypeNS = array ('namespace' => 'http://www.bff.bund.de/ePayment' , 'type' => 'Kunde');

	function Kunde ($customerNumber = "", $values = "")
    {
/*		if ($customerNumber != "")
		{
			$this->EShopKundenNr = $customerNumber;
		}
		else
		{*/
			$this->EShopKundenNr = time() . "_" . substr(md5(uniqid(rand(), true)), 0, 4);
		if ($customerNumber != "")
		{
			$this->EShopKundenNr = $customerNumber . "_" . time() . "_" . substr(md5(uniqid(rand(), true)), 0, 4);
		}
/*		}*/

		if ($values == "")
		{
			$values = $_SESSION["bmf"]["personal_data"];
		}

		if ($values["language"] != NULL)
		{
			$this->sprache = $values["language"];
		}
		if ($values["firstname"] != NULL)
		{
			$this->vorname = utf8_decode($values["firstname"]);
		}
		if ($values["lastname"] != NULL)
		{
			$this->nachname = utf8_decode($values["lastname"]);
		}
		if ($values["email"] != NULL)
		{
			$this->EMailAdresse = utf8_decode($values["email"]);
		}

		$address = new Adresse();

		$this->rechnungsAdresse = $address;
	}

	function getEShopCustomerNumber()
	{
		return $this->EShopKundenNr;
	}

}

class Adresse
{
    var $OBJTypeNS = array ('namespace' => 'http://www.bff.bund.de/ePayment' , 'type' => 'Adresse');

	function Adresse ($values = "")
    {
		if ($values == "")
		{
			$values = $_SESSION["bmf"]["personal_data"];
		}

		if (is_array($values))
		{
			if ($values["street"] != NULL)
			{
				$this->strasse = utf8_decode($values["street"]);
			}
			if ($values["house_number"] != NULL)
			{
				$this->hausNr = utf8_decode($values["house_number"]);
			}
			if ($values["po_box"] != NULL)
			{
				$this->postfach = utf8_decode($values["po_box"]);
			}
			if ($values["country"] != NULL)
			{
				$this->land = utf8_decode($values["country"]);
			}
			if ($values["zipcode"] != NULL)
			{
				$this->PLZ = utf8_decode($values["zipcode"]);
			}
			if ($values["city"] != NULL)
			{
				$this->ort = utf8_decode($values["city"]);
			}
		}
	}

}

class Kreditkarte
{
    var $OBJTypeNS = array ('namespace' => 'http://www.bff.bund.de/ePayment' , 'type' => 'Kreditkarte');

	function Kreditkarte ($values = "")
    {
		if ($values == "")
		{
			$values = $_SESSION["bmf"]["credit_card"];
		}

		if (is_array($values))
		{
			if ($values["karteninhaber"] != NULL)
			{
				$this->karteninhaber = utf8_decode($values["karteninhaber"]);
			}
			if ($values["kartenpruefnummer"] != NULL)
			{
				$this->kartenpruefnummer = utf8_decode($values["kartenpruefnummer"]);
			}
			if (is_array ($values["kreditkartenNr"]) &&
				count($values["kreditkartenNr"]) == 4)
			{
				for ($i = 1; $i <= count($values["kreditkartenNr"]); $i++)
				{
					$this->kreditkartenNr .= utf8_decode($values["kreditkartenNr"]["block_".$i]);# . "-";
				}
#				$this->kreditkartenNr = substr($this->kreditkartenNr, 0, strlen($this->kreditkartenNr)-1);
			}
			if (is_array($values["gueltigkeit"]) &&
				$values["gueltigkeit"]["monat"] != "" &&
				$values["gueltigkeit"]["jahr"] != "")
			{
				$this->gueltigkeit = utf8_decode($values["gueltigkeit"]["monat"]);
				$this->gueltigkeit .= utf8_decode($values["gueltigkeit"]["jahr"]);
			}
		}
	}

}

class Lastschrift
{
    var $OBJTypeNS = array ('namespace' => 'http://www.bff.bund.de/ePayment' , 'type' => 'Lastschrift');

	function Lastschrift ($customerNumber = "", $values = "")
    {
		$this->EShopKundenNr = time() . "_" . substr(md5(uniqid(rand(), true)), 0, 4);
		if ($customerNumber != "")
		{
			$this->EShopKundenNr = $customerNumber . "_" . time() . "_" . substr(md5(uniqid(rand(), true)), 0, 4);
		}
	}

}

class Bankverbindung
{
    var $OBJTypeNS = array ('namespace' => 'http://www.bff.bund.de/ePayment' , 'type' => 'Bankverbindung');

	function Bankverbindung ($values = "")
    {
		if ($values == "")
		{
			$values = $_SESSION["bmf"]["debit_entry"];
		}

		if (is_array($values))
		{
			if ($values["kontoinhaber"] != NULL)
			{
				$this->kontoinhaber = utf8_decode($values["kontoinhaber"]);
			}
			if ($values["kontoNr"] != NULL)
			{
				$this->kontoNr = utf8_decode($values["kontoNr"]);
			}
			if ($values["BLZ"] != NULL)
			{
				$this->BLZ = utf8_decode($values["BLZ"]);
			}
		}
	}

}

class Buchung
{
    var $OBJTypeNS = array ('namespace' => 'http://schemas.xmlsoap.org/soap/encoding/', 'nsPrefix' => 'ns3', 'type' => 'Buchung', 'pnamespace' => 'http://www.bff.bund.de/ePayment', 'pnsPrefix' => 'ns2', 'item' => 'ns2:Buchung');

    function Buchung($values = "")
    {
		$bmfSetObj = ilBMFSettings::getInstance();
		$bmfConfig = $bmfSetObj->getAll();

		if ($bmfConfig["haushaltsstelle"] != NULL)
		{
			$this->haushaltsstelle = $bmfConfig["haushaltsstelle"];
		}
		if ($bmfConfig["objektNr"] != NULL)
		{
			$this->objektnummer = $bmfConfig["objektNr"];
		}

		if (is_array($values))
		{
			if ($values["object_title"] != NULL)
			{
				$buchungstext = utf8_decode($values["object_title"]);
				if(strlen($buchungstext) > 16)
				{
					$buchungstext = substr($buchungstext,0,15).'...';
				}
				
				$this->buchungstext = $buchungstext;
			}
			if ($values["price"] != "")
			{
				$this->betrag = (float)$values["price"];
			}
		}
	}

	/* Die BelegNr wird vom BMF zur�ck geliefert */
	function setVoucherNumber($voucherNumber)
	{
		if ($voucherNumber != NULL)
		{
			$this->belegNr = $voucherNumber;
		}
	}

	function getVoucherNumber()
	{
		return $this->belegNr;
	}
}

class BuchungsListe
{
    var $OBJTypeNS = array ('namespace' => 'http://www.bff.bund.de/ePayment', 'nsPrefix' => 'ns2', 'type' => 'BuchungsListe');

    function BuchungsListe($userId, $values = "")
    {
		global $ilias;
		
		$bmfSetObj = ilBMFSettings::getInstance();
		$bmfConfig = $bmfSetObj->getAll();

		if ($bmfConfig["bewirtschafterNr"] != NULL)
		{
			$this->bewirtschafterNr = $bmfConfig["bewirtschafterNr"];
		}
		if ($bmfConfig["waehrungskennzeichen"] != NULL)
		{
			$this->waehrungskennzeichen = $bmfConfig["waehrungskennzeichen"];
		}
		$this->faelligkeitsdatum = date("Y-m-d") . "T" . date("H:i:s") . "Z";
		if ($bmfConfig["kennzeichenMahnverfahren"] != NULL)
		{
			$this->kennzeichenMahnverfahren = $bmfConfig["kennzeichenMahnverfahren"];
		}
		
		$inst_id_time = $ilias->getSetting('inst_id').'_'.$userId.'_'.substr((string) time(),-3);
		$this->EShopTransaktionsNr = $inst_id_time.substr(md5(uniqid(rand(), true)), 0, 4);

		if (is_array($values))
		{
			if ($values["betrag"] != NULL)
			{
				$this->betrag = (float)$values["betrag"];
			}
			if ($values["buchungen"] != NULL)
			{
				$this->buchungen = $values["buchungen"];
			}
		}
	}
	function getTransaction()
	{
		return $this->EShopTransaktionsNr;
	}

	/* Das Kassenzeichen wird vom BMF zur�ck geliefert */
	function setKassenzeichen($kassenzeichen)
	{
		if ($kassenzeichen != NULL)
		{
			$this->kassenzeichen = $kassenzeichen;
		}
	}

	function getKassenzeichen()
	{
		return $this->kassenzeichen;
	}

}

class LieferAdresse
{
    var $OBJTypeNS = array ('namespace' => 'http://www.bff.bund.de/ePayment' , 'type' => 'LieferAdresse');

	function LieferAdresse ($values = "")
    {
		if ($values == "")
		{
			$values = $_SESSION["bmf"]["personal_data"];
		}

		if (is_array($values))
		{
			if ($values["firstname"] != NULL)
			{
				$this->vorname = utf8_decode($values["firstname"]);
			}
			if ($values["lastname"] != NULL)
			{
				$this->nachname = utf8_decode($values["lastname"]);
			}
			if ($values["street"] != NULL)
			{
				$this->strasse = utf8_decode($values["street"]);
			}
			if ($values["house_number"] != NULL)
			{
				$this->hausNr = utf8_decode($values["house_number"]);
			}
			if ($values["po_box"] != NULL)
			{
				$this->postfach = utf8_decode($values["po_box"]);
			}
			if ($values["country"] != NULL)
			{
				$this->land = utf8_decode($values["country"]);
			}
			if ($values["zipcode"] != NULL)
			{
				$this->PLZ = utf8_decode($values["zipcode"]);
			}
			if ($values["city"] != NULL)
			{
				$this->ort = utf8_decode($values["city"]);
			}
		}
	}
}
?>