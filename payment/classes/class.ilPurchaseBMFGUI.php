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
* Class ilPurchaseBMFGUI
*
* @author Stefan Meyer, Jens Conze
* @version $Id$
*
* @package core
*/

include_once './payment/classes/class.ilPaymentShoppingCart.php';
include_once './payment/classes/class.ilPaymentShoppingCartGUI.php';
include_once './payment/classes/class.ilPaymentCoupons.php';
include_once './payment/classes/class.ilBMFSettings.php';
#include_once dirname(__FILE__)."/../bmf/lib/ePayment/cfg_epayment.inc.php";
include_once dirname(__FILE__)."/../bmf/lib/SOAP/class.ilBMFClient.php";

class ilPurchaseBMFGUI
{
	var $ctrl;
	var $tpl;

	var $user_obj;
	var $coupon_obj = null;
	var $error;
	var $shoppingCart;

	var $soapClient;

	function ilPurchaseBMFGUI(&$user_obj)
	{
		global $ilias, $ilDB, $lng, $tpl, $rbacsystem, $ilCtrl,  $ilTabs;
		$this->ilias = $ilias;
		$this->db = $ilDB;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;

		// Get user object
		$this->user_obj = $user_obj;
		
		$this->coupon_obj = new ilPaymentCoupons($this->user_obj);
		
		if (!is_array($_SESSION["bmf"]["personal_data"]))
		{
			$_SESSION["bmf"]["personal_data"]["vorname"] = $this->user_obj->getFirstname();
			$_SESSION["bmf"]["personal_data"]["nachname"] = $this->user_obj->getLastname();
			if (strpos("_" . $this->user_obj->getStreet(), " ") > 0)
			{
				$houseNo = substr($this->user_obj->getStreet(), strrpos($this->user_obj->getStreet(), " ")+1);
				$street = substr($this->user_obj->getStreet(), 0, strlen($this->user_obj->getStreet())-(strlen($houseNo)+1));
				$_SESSION["bmf"]["personal_data"]["strasse"] = $street;
				$_SESSION["bmf"]["personal_data"]["hausNr"] = $houseNo;
			}
			else
			{
				$_SESSION["bmf"]["personal_data"]["strasse"] = $this->user_obj->getStreet();
				$_SESSION["bmf"]["personal_data"]["hausNr"] = "";
			}
			$_SESSION["bmf"]["personal_data"]["postfach"] = "";
			$_SESSION["bmf"]["personal_data"]["PLZ"] = $this->user_obj->getZipcode();
			$_SESSION["bmf"]["personal_data"]["ort"] = $this->user_obj->getCity();
			$_SESSION["bmf"]["personal_data"]["land"] = $this->__getCountryCode($this->user_obj->getCountry());
			$_SESSION["bmf"]["personal_data"]["EMailAdresse"] = $this->user_obj->getEmail();
			$_SESSION["bmf"]["personal_data"]["sprache"] = $this->user_obj->getLanguage();
		}
		
		if (!is_array($_SESSION["coupons"]["bmf"]))
		{
			$_SESSION["coupons"]["bmf"] = array();
		}

		$this->__loadTemplate();

		$this->error = "";

		$this->lng->loadLanguageModule("payment");
		
		$ilTabs->clearTargets();
		$ilTabs->clearSubTabs();
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

		if(!count($items = $this->psc_obj->getEntries(PAY_METHOD_BMF)))
		{

			$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
			$this->tpl->touchBlock("stop_floating");
			ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));

		}
		else
		{

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bmf_personal_data.html','payment');
		#$this->tpl = new ilTemplate('tpl.pay_bmf_personal_data.html', true, true, 'payment');

		$this->tpl->setVariable("PERSONAL_DATA_FORMACTION",$this->ctrl->getFormAction($this));

		// set table header
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_pays_b.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("HEADER",$this->lng->txt('pay_step1'));
		$this->tpl->setVariable("TITLE",$this->lng->txt('pay_bmf_personal_data'));
		$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('pay_bmf_description_personal_data'));
		$this->tpl->touchBlock("stop_floating");
		$this->tpl->setVariable("TXT_CLOSE_WINDOW",$this->lng->txt('close_window'));

		// set plain text variables
		$this->tpl->setVariable("TXT_FIRSTNAME",$this->lng->txt('firstname'));
		$this->tpl->setVariable("TXT_LASTNAME",$this->lng->txt('lastname'));
		$this->tpl->setVariable("TXT_STREET",$this->lng->txt('street'));
		$this->tpl->setVariable("TXT_HOUSE_NUMBER",$this->lng->txt('pay_bmf_house_number'));
		$this->tpl->setVariable("TXT_OR",$this->lng->txt('pay_bmf_or'));
		$this->tpl->setVariable("TXT_PO_BOX",$this->lng->txt('pay_bmf_po_box'));
		$this->tpl->setVariable("TXT_ZIPCODE",$this->lng->txt('zipcode'));
		$this->tpl->setVariable("TXT_CITY",$this->lng->txt('city'));
		$this->tpl->setVariable("TXT_COUNTRY",$this->lng->txt('country'));
		$this->tpl->setVariable("TXT_EMAIL",$this->lng->txt('email'));

		$this->tpl->setVariable("INPUT_VALUE",ucfirst($this->lng->txt('next')));
		$this->tpl->setVariable("CANCEL",$this->lng->txt('cancel'));

		// fill defaults

		$this->error != "" && isset($_POST['country']) ? $this->__showCountries($this->tpl, $_POST['country']) : $this->__showCountries($this->tpl, $_SESSION['bmf']['personal_data']['land']);
/*		$this->tpl->setVariable("FIRSTNAME",
								$this->error != "" && isset($_POST['firstname'])
								? ilUtil::prepareFormOutput($_POST['firstname'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['vorname'],true));
		$this->tpl->setVariable("LASTNAME",
								$this->error != "" && isset($_POST['lastname'])
								? ilUtil::prepareFormOutput($_POST['lastname'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['nachname'],true));*/
		$this->tpl->setVariable("FIRSTNAME", $this->user_obj->getFirstname());
		$this->tpl->setVariable("LASTNAME", $this->user_obj->getLastname());
		$this->tpl->setVariable("STREET",
								$this->error != "" && isset($_POST['street'])
								? ilUtil::prepareFormOutput($_POST['street'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['strasse'],true));
		$this->tpl->setVariable("HOUSE_NUMBER",
								$this->error != "" && isset($_POST['house_number'])
								? ilUtil::prepareFormOutput($_POST['house_number'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['hausNr'],true));
		$this->tpl->setVariable("PO_BOX",
								$this->error != "" && isset($_POST['po_box'])
								? ilUtil::prepareFormOutput($_POST['po_box'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['postfach'],true));
		$this->tpl->setVariable("ZIPCODE",
								$this->error != "" && isset($_POST['zipcode'])
								? ilUtil::prepareFormOutput($_POST['zipcode'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['PLZ'],true));
		$this->tpl->setVariable("CITY",
								$this->error != "" && isset($_POST['city'])
								? ilUtil::prepareFormOutput($_POST['city'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['ort'],true));
/*		$this->tpl->setVariable("EMAIL",
								$this->error != "" && isset($_POST['email'])
								? ilUtil::prepareFormOutput($_POST['email'],true)
								: ilUtil::prepareFormOutput($_SESSION['bmf']["personal_data"]['EMailAdresse'],true));*/
		$this->tpl->setVariable("EMAIL", $this->user_obj->getEmail());

		}
	}

	function getPersonalData()
	{
/*		if ($_POST"firstname"] == "" ||
			$_POST["lastname"] == "" ||*/
		if ($_SESSION["bmf"]["personal_data"]["vorname"] == "" ||
			$_SESSION["bmf"]["personal_data"]["nachname"] == "" ||
			$_POST["zipcode"] == "" ||
			$_POST["city"] == "" ||
			$_POST["country"] == "" ||
/*			$_POST["email"] == "")*/
			$_SESSION["bmf"]["personal_data"]["EMailAdresse"] == "")
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

/*		$_SESSION["bmf"]["personal_data"]["vorname"] = $_POST["firstname"];
		$_SESSION["bmf"]["personal_data"]["nachname"] = $_POST["lastname"];*/
		$_SESSION["bmf"]["personal_data"]["vorname"] = $this->user_obj->getFirstname();
		$_SESSION["bmf"]["personal_data"]["nachname"] = $this->user_obj->getLastname();
		$_SESSION["bmf"]["personal_data"]["strasse"] = $_POST["street"];
		$_SESSION["bmf"]["personal_data"]["hausNr"] = $_POST["house_number"];
		$_SESSION["bmf"]["personal_data"]["postfach"] = $_POST["po_box"];
		$_SESSION["bmf"]["personal_data"]["PLZ"] = $_POST["zipcode"];
		$_SESSION["bmf"]["personal_data"]["ort"] = $_POST["city"];
		$_SESSION["bmf"]["personal_data"]["land"] = $_POST["country"];
/*		$_SESSION["bmf"]["personal_data"]["EMailAdresse"] = $_POST["email"];*/
		$_SESSION["bmf"]["personal_data"]["EmailAdresse"] = $this->user_obj->getEmail();
		$_SESSION["bmf"]["personal_data"]["sprache"] = $this->user_obj->getLanguage();

		if ($_SESSION["bmf"]["personal_data"]["land"] != "DE")
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

		if(!count($items = $this->psc_obj->getEntries(PAY_METHOD_BMF)))
		{

			$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
			$this->tpl->touchBlock("stop_floating");
			ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));

		}
		else
		{

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bmf_payment_type.html','payment');
		#$this->tpl = new ilTemplate('tpl.pay_bmf_payment_type.html', true, true, 'payment');

		$this->tpl->setVariable("PAYMENT_TYPE_FORMACTION",$this->ctrl->getFormAction($this));

		// set table header
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_pays_b.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("HEADER",$this->lng->txt('pay_step2'));
		$this->tpl->setVariable("TITLE",$this->lng->txt('pay_bmf_payment_type'));
		$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('pay_bmf_description_payment_type'));
		$this->tpl->touchBlock("stop_floating");
		$this->tpl->setVariable("TXT_CLOSE_WINDOW",$this->lng->txt('close_window'));

		// set plain text variables
		if ($_SESSION["bmf"]["personal_data"]["land"] == "DE")
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
			if (($_SESSION["bmf"]["personal_data"]["land"] != "DE" && $_POST["payment_type"] != "debit_entry") ||
				$_SESSION["bmf"]["personal_data"]["land"] == "DE")
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
			($_SESSION["bmf"]["personal_data"]["land"] != "DE" && $_POST["payment_type"] == "debit_entry"))
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

		if(!count($items = $this->psc_obj->getEntries(PAY_METHOD_BMF)))
		{

			$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
			$this->tpl->touchBlock("stop_floating");
			ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));

		}
		else
		{

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bmf_debit_entry.html','payment');
		#$this->tpl = new ilTemplate('tpl.pay_bmf_debit_entry.html', true, true, 'payment');
		
		$this->__showShoppingCart();

		$this->tpl->setVariable("DEBIT_ENTRY_FORMACTION",$this->ctrl->getFormAction($this));

		// set table header
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_pays_b.gif'));
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

		if(!count($items = $this->psc_obj->getEntries(PAY_METHOD_BMF)))
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
			
					$sc_obj =& new ilPaymentShoppingCart($this->user_obj);
	
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
								$price = $tmp_bookEntries[$i]["betrag"];
								$tmp_bookEntries[$i]["math_price"] = (float) $price;
																					
								foreach ($_SESSION["coupons"]["bmf"] as $key => $coupon)
								{				
									$this->coupon_obj->setId($coupon["pc_pk"]);
									$this->coupon_obj->setCurrentCoupon($coupon);
									
									$tmp_pobject =& new ilPaymentObject($this->user_obj, $tmp_bookEntries[$i]['pobject_id']);						
							
									if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
									{
										$_SESSION["coupons"]["bmf"][$key]["total_objects_coupon_price"] += (float) $price;
										$_SESSION["coupons"]["bmf"][$key]["items"][] = $tmp_bookEntries[$i];
										$booking = false;									
									}								
									
									unset($tmp_pobject);
								}							
							}										
								
							if ($booking)
							{												
								$tmp_bookEntries[$i]["betrag_string"] = number_format( (float) $tmp_bookEntries[$i]["betrag"] , 2, ",", ".");						
																			
								$bookEntries[] = new Buchung($tmp_bookEntries[$i]);
								$totalAmount += $tmp_bookEntries[$i]["betrag"];
							}
							else
							{
								$tmp_bookEntries[$i]["betrag_string"] = number_format( (float) $tmp_bookEntries[$i]["betrag"] , 2, ",", ".");
							}												
						}
						
						$coupon_discount_items = $this->psc_obj->calcDiscountPrices($_SESSION["coupons"]["bmf"]);

						if (is_array($coupon_discount_items) && !empty($coupon_discount_items))
						{
							foreach ($coupon_discount_items as $item)
							{
								$item["betrag"] = $item["discount_price"];
								$bookEntries[] = new Buchung($item);
								$totalAmount += $item["betrag"];
							}										
						}

						$values = array("betrag" => $totalAmount, "buchungen" => $bookEntries);						
						$bookingList = new BuchungsListe($this->user_obj->getId(), $values);
					}
			
			#		vd($address);
			#		vd($debitEntry);
			#		vd($bank);
			#		vd($bookingList);
			
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
							$this->__sendBill($resultCustomerObj->kunde, $_SESSION["bmf"]["payment_type"], $bookingList, $resultObj);

							$this->__addBookings($resultObj,$bookingList->getTransaction());
							$this->__emptyShoppingCart();
							$this->__clearSession();

							$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
							$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('pay_bmf_thanks'));
							$this->tpl->touchBlock("stop_floating");
							
							ilUtil::sendInfo($this->lng->txt('pay_bmf_thanks'));
	
							$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.pay_bmf_debit_entry.html','payment');
							#$this->tpl = new ilTemplate('tpl.pay_bmf_debit_entry.html', true, true, 'payment');
							if ($this->ilias->getSetting("https") != 1)
							{
								$this->tpl->setCurrentBlock("buyed_objects");
								#$this->tpl->setVariable("LINK_GOTO_BUYED_OBJECTS", "./payment.php?baseClass=ilpaymentbuyedobjectsgui&cmd=2");
								#$link = $this->ctrl->getLinkTargetByClass('ilpaymentbuyedobjectsgui', 'showItems');
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

		if(!count($items = $this->psc_obj->getEntries(PAY_METHOD_BMF)))
		{

			$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
			$this->tpl->touchBlock("stop_floating");
			ilUtil::sendInfo($this->lng->txt('pay_shopping_cart_empty'));

		}
		else
		{

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pay_bmf_credit_card.html','payment');
		#$this->tpl = new ilTemplate('tpl.pay_bmf_credit_card.html', true, true, 'payment');
		$this->__showShoppingCart();

		$this->tpl->setVariable("CREDIT_CARD_FORMACTION",$this->ctrl->getFormAction($this));

		// set table header
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_pays_b.gif'));
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

		$this->error = "";
		$this->sendCreditCard();
	}

	function sendCreditCard()
	{
		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);
		
		$this->psc_obj->clearCouponItemsSession();

		if(!count($items = $this->psc_obj->getEntries(PAY_METHOD_BMF)))
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

		$sc_obj =& new ilPaymentShoppingCart($this->user_obj);

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
					$price = $tmp_bookEntries[$i]["betrag"];
					$tmp_bookEntries[$i]["math_price"] = (float) $price;
																		
					foreach ($_SESSION["coupons"]["bmf"] as $key => $coupon)
					{				
						$this->coupon_obj->setId($coupon["pc_pk"]);
						$this->coupon_obj->setCurrentCoupon($coupon);
						
						$tmp_pobject =& new ilPaymentObject($this->user_obj, $tmp_bookEntries[$i]['pobject_id']);						
				
						if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
						{
							$_SESSION["coupons"]["bmf"][$key]["total_objects_coupon_price"] += (float) $price;
							$_SESSION["coupons"]["bmf"][$key]["items"][] = $tmp_bookEntries[$i];
							
							$booking = false;									
						}								
						
						unset($tmp_pobject);
					}							
				}										
					
				if ($booking)
				{												
					$tmp_bookEntries[$i]["betrag_string"] = number_format( (float) $tmp_bookEntries[$i]["betrag"] , 2, ",", ".");						
																
					$bookEntries[] = new Buchung($tmp_bookEntries[$i]);
					$totalAmount += $tmp_bookEntries[$i]["betrag"];
				}
				else
				{
					$tmp_bookEntries[$i]["betrag_string"] = number_format( (float) $tmp_bookEntries[$i]["betrag"] , 2, ",", ".");
				}												
			}
			
			$coupon_discount_items = $this->psc_obj->calcDiscountPrices($_SESSION["coupons"]["bmf"]);
				
			if (is_array($coupon_discount_items) && !empty($coupon_discount_items))
			{
				foreach ($coupon_discount_items as $item)
				{
					$item["betrag"] = $item["discount_price"];
					$bookEntries[] = new Buchung($item);
					$totalAmount += $item["discount_price"];
				}										
			}
			
			$values = array("betrag" => $totalAmount, "buchungen" => $bookEntries);						
			$bookingList = new BuchungsListe($this->user_obj->getId(), $values);
		}

#		vd($customer);
#		vd($creditCard);
#		vd($bookingList);

		$resultObj = $payment->zahlenUndAnlegenKunde($customer, $creditCard, $bookingList);
		$result = $resultObj->ergebnis;

#		vd($result);

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
				$this->__sendBill($customer, $_SESSION["bmf"]["payment_type"], $bookingList, $resultObj);

				$this->__addBookings($resultObj,$bookingList->getTransaction());
				$this->__emptyShoppingCart();
				$this->__clearSession();

				$this->tpl->setVariable("HEADER",$this->lng->txt('pay_bmf_your_order'));
				$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('pay_bmf_thanks'));
				$this->tpl->touchBlock("stop_floating");
				
				ilUtil::sendInfo($this->lng->txt('pay_bmf_thanks'));

				$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.pay_bmf_credit_card.html','payment');
				#$this->tpl = new ilTemplate('tpl.pay_bmf_credit_card.html', true, true, 'payment');
				if ($this->ilias->getSetting("https") != 1)
				{
					$this->tpl->setCurrentBlock("buyed_objects");
					#$this->ctrl->redirectByClass("ilPaymentBuyedObjectsGUI", "ilpaymentbuyedobjectsgui");
					#$link = $this->ctrl->getLinkTargetByClass('ilpaymentbuyedobjectsgui', 'showItems');
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

	// PRIVATE
	function __sendBill($customer, $paymentType, $bookingList, $result)
	{
		include_once './classes/class.ilTemplate.php';
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		include_once './payment/classes/class.ilGeneralSettings.php';
		include_once './payment/classes/class.ilPaymentShoppingCart.php';
		include_once 'Services/Mail/classes/class.ilMimeMail.php';
		
		$sc_obj =& new ilPaymentShoppingCart($this->user_obj);
		$genSet = new ilGeneralSettings();

		$tpl = new ilTemplate("./payment/templates/default/tpl.pay_bmf_bill.html", true, true, true);
  
		$tpl->setVariable("VENDOR_ADDRESS", nl2br(utf8_decode($genSet->get("address"))));
		$tpl->setVariable("VENDOR_ADD_INFO", nl2br(utf8_decode($genSet->get("add_info"))));
		$tpl->setVariable("VENDOR_BANK_DATA", nl2br(utf8_decode($genSet->get("bank_data"))));
		$tpl->setVariable("TXT_BANK_DATA", utf8_decode($this->lng->txt("pay_bank_data")));

		$tpl->setVariable("CUSTOMER_FIRSTNAME", $customer->vorname);
		$tpl->setVariable("CUSTOMER_LASTNAME", $customer->nachname);
		if ($customer->rechnungsAdresse->strasse != "" &&
			$customer->rechnungsAdresse->hausNr != "")
		{
			$tpl->setVariable("CUSTOMER_STREET_POBOX", $customer->rechnungsAdresse->strasse . " ". $customer->rechnungsAdresse->hausNr);
		}
		else
		{
			$tpl->setVariable("CUSTOMER_STREET_POBOX", $customer->rechnungsAdresse->postfach);
		}
		$tpl->setVariable("CUSTOMER_ZIPCODE", $customer->rechnungsAdresse->PLZ);
		$tpl->setVariable("CUSTOMER_CITY", $customer->rechnungsAdresse->ort);
		$tpl->setVariable("CUSTOMER_COUNTRY", $this->__getCountryName($customer->rechnungsAdresse->land));

		$tpl->setVariable("BILL_NO", $result->buchungsListe->kassenzeichen);
		$tpl->setVariable("DATE", date("d.m.Y"));

		$tpl->setVariable("TXT_BILL", utf8_decode($this->lng->txt("pays_bill")));
		$tpl->setVariable("TXT_BILL_NO", utf8_decode($this->lng->txt("pay_bill_no")));
		$tpl->setVariable("TXT_DATE", utf8_decode($this->lng->txt("date")));

		$tpl->setVariable("TXT_ARTICLE", utf8_decode($this->lng->txt("pay_article")));
		$tpl->setVariable("TXT_PRICE", utf8_decode($this->lng->txt("price_a")));

		$bookEntries = $sc_obj->getShoppingCart();
		for ($i = 0; $i < count($bookEntries); $i++)
		{
			$tmp_pobject =& new ilPaymentObject($this->user_obj, $bookEntries[$i]['pobject_id']);
			
			$assigned_coupons = '';					
			if (!empty($_SESSION["coupons"]["bmf"]))
			{											
				foreach ($_SESSION["coupons"]["bmf"] as $key => $coupon)
				{
					$this->coupon_obj->setId($coupon["pc_pk"]);
					$this->coupon_obj->setCurrentCoupon($coupon);

					if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
					{
						$assigned_coupons .= '<br />' . $this->lng->txt('paya_coupons_coupon') . ': ' . $coupon["pcc_code"];
					}
				}
			}
			
			$tpl->setCurrentBlock("loop");
			$tpl->setVariable("LOOP_OBJ_TYPE", utf8_decode($this->lng->txt($bookEntries[$i]["typ"])));
			$tpl->setVariable("LOOP_TITLE", $bookEntries[$i]["buchungstext"]. $assigned_coupons);
			$tpl->setVariable("LOOP_TXT_ENTITLED_RETRIEVE", utf8_decode($this->lng->txt("pay_entitled_retrieve")));
			$tpl->setVariable("LOOP_DURATION", $bookEntries[$i]["dauer"] . " " . utf8_decode($this->lng->txt("paya_months")));
			$tpl->setVariable("LOOP_PRICE", number_format($bookEntries[$i]["betrag"], 2, ",", ".") . " " . $genSet->get("currency_unit"));
			$tpl->parseCurrentBlock("loop");
			
			unset($tmp_pobject);
		}
		
		if (!empty($_SESSION["coupons"]["bmf"]))
		{		
			if (count($items = $bookEntries))
			{
				$sub_total_amount = $bookingList->betrag;				
														
				foreach ($_SESSION["coupons"]["bmf"] as $coupon)
				{
					$this->coupon_obj->setId($coupon["pc_pk"]);	
					$this->coupon_obj->setCurrentCoupon($coupon);					
					
					$total_object_price = 0.0;
					$current_coupon_bonus = 0.0;
					
					foreach ($bookEntries as $item)
					{
						$tmp_pobject =& new ilPaymentObject($this->user_obj, $item['pobject_id']);						
						
						if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
						{						
							$total_object_price += $item["betrag"];																				
						}			
						
						unset($tmp_pobject);
					}

					$current_coupon_bonus = $this->coupon_obj->getCouponBonus($total_object_price);	

					$sub_total_amount += $current_coupon_bonus;										
					
					$tpl->setCurrentBlock("cloop");
					$tpl->setVariable("TXT_COUPON", utf8_decode($this->lng->txt("paya_coupons_coupon") . " " . $coupon["pcc_code"]));
					$tpl->setVariable("BONUS", number_format($current_coupon_bonus * (-1), 2, ',', '.') . " " . $genSet->get("currency_unit"));
					$tpl->parseCurrentBlock();
				}

				$tpl->setVariable("TXT_SUBTOTAL_AMOUNT", utf8_decode($this->lng->txt("pay_bmf_subtotal_amount")));
				$tpl->setVariable("SUBTOTAL_AMOUNT", number_format($sub_total_amount, 2, ",", ".") . " " . $genSet->get("currency_unit"));
			}
		}

		if ($bookingList->betrag < 0) $bookingList->betrag = 0.0;

		$tpl->setVariable("TXT_TOTAL_AMOUNT", utf8_decode($this->lng->txt("pay_bmf_total_amount")));
		$tpl->setVariable("TOTAL_AMOUNT", number_format($bookingList->betrag, 2, ",", ".") . " " . $genSet->get("currency_unit"));
		if (($vat = $sc_obj->getVat($bookingList->betrag)) > 0)
		{
			$tpl->setVariable("VAT", number_format($vat, 2, ",", ".") . " " . $genSet->get("currency_unit"));
			$tpl->setVariable("TXT_VAT", $genSet->get("vat_rate") . "% " . utf8_decode($this->lng->txt("pay_bmf_vat_included")));
		}

		if ($paymentType == "debit_entry")
		{
			$tpl->setVariable("TXT_PAYMENT_TYPE", utf8_decode($this->lng->txt("pay_payed_debit_entry")));
		}
		else
		{
			$tpl->setVariable("TXT_PAYMENT_TYPE", utf8_decode($this->lng->txt("pay_payed_credit_card")));
		}
		
		if (!@file_exists($genSet->get("pdf_path")))
		{
			ilUtil::makeDir($genSet->get("pdf_path"));
		}

		if (@file_exists($genSet->get("pdf_path")))
		{
			ilUtil::html2pdf($tpl->get(), $genSet->get("pdf_path") . "/" . $result->buchungsListe->kassenzeichen . ".pdf");
		}

		if (@file_exists($genSet->get("pdf_path") . "/" . $result->buchungsListe->kassenzeichen . ".pdf") &&
			$customer->EMailAdresse != "" &&
			$this->ilias->getSetting("admin_email") != "")
		{
			$m= new ilMimeMail; // create the mail
			$m->From( $this->ilias->getSetting("admin_email") );
			$m->To( $customer->EMailAdresse );
			$m->Subject( $this->lng->txt("pay_message_subject") );	
			$message = $this->lng->txt("pay_message_hello") . " " . utf8_encode($customer->vorname) . " " . utf8_encode($customer->nachname) . ",\n\n";
			$message .= $this->lng->txt("pay_message_thanks") . "\n\n";
			$message .= $this->lng->txt("pay_message_attachment") . "\n\n";
			$message .= $this->lng->txt("pay_message_regards") . "\n\n";
			$message .= strip_tags($genSet->get("address"));
			$m->Body( $message );	// set the body
			$m->Attach( $genSet->get("pdf_path") . "/" . $result->buchungsListe->kassenzeichen . ".pdf", "application/pdf" ) ;	// attach a file of type image/gif
			$m->Send();	// send the mail
		}

		@unlink($genSet->get("pdf_path") . "/" . $result->buchungsListe->kassenzeichen . ".html");
		@unlink($genSet->get("pdf_path") . "/" . $result->buchungsListe->kassenzeichen . ".pdf");

	}

	function __addBookings($a_result,$a_transaction)
	{
		include_once './payment/classes/class.ilPaymentBookings.php';
		include_once './payment/classes/class.ilPaymentShoppingCart.php';
		include_once './payment/classes/class.ilPaymentObject.php';
		include_once './payment/classes/class.ilPaymentPrices.php';
		
		$booking_obj =& new ilPaymentBookings();
		
		$sc_obj =& new ilPaymentShoppingCart($this->user_obj);
			
		$items = $sc_obj->getEntries(PAY_METHOD_BMF);		
		
		$sc_obj->clearCouponItemsSession();
		
		foreach($items as $entry)
		{		
			$pobject =& new ilPaymentObject($this->user_obj,$entry['pobject_id']);
			
			$price = ilPaymentPrices::_getPrice($entry['price_id']);					
			
			if (!empty($_SESSION["coupons"]["bmf"]))
			{					
				$entry["math_price"] = (float) ilPaymentPrices::_getPriceFromArray($price);					
				foreach ($_SESSION["coupons"]["bmf"] as $key => $coupon)
				{							
					$this->coupon_obj->setId($coupon["pc_pk"]);
					$this->coupon_obj->setCurrentCoupon($coupon);										
			
					if ($this->coupon_obj->isObjectAssignedToCoupon($pobject->getRefId()))
					{
						$_SESSION["coupons"]["bmf"][$key]["total_objects_coupon_price"] += (float) ilPaymentPrices::_getPriceFromArray($price);
						$_SESSION["coupons"]["bmf"][$key]["items"][] = $entry;									
					}				
				}				
			}
			
			unset($pobject);
		}
		
		$coupon_discount_items = $sc_obj->calcDiscountPrices($_SESSION["coupons"]["bmf"]);	

		$i = 0;
		foreach($items as $entry)
		{
			$pobject =& new ilPaymentObject($this->user_obj,$entry['pobject_id']);

			$price = ilPaymentPrices::_getPrice($entry['price_id']);
			
			if (array_key_exists($entry["pobject_id"], $coupon_discount_items))
			{
				$bonus = $coupon_discount_items[$entry["pobject_id"]]["math_price"] - $coupon_discount_items[$entry["pobject_id"]]["discount_price"];
			}
			
			$booking_obj->setTransaction($a_transaction);
			$booking_obj->setPobjectId($entry['pobject_id']);
			$booking_obj->setCustomerId($this->user_obj->getId());
			$booking_obj->setVendorId($pobject->getVendorId());
			$booking_obj->setPayMethod($pobject->getPayMethod());
			$booking_obj->setOrderDate(time());
			$booking_obj->setDuration($price['duration']);
			$booking_obj->setPrice(ilPaymentPrices::_getPriceString($entry['price_id']));
			$booking_obj->setDiscount($bonus > 0 ? ilPaymentPrices::_getPriceStringFromAmount((-1) * $bonus) : "");
			$booking_obj->setPayed(1);
			$booking_obj->setAccess(1);
			$booking_obj->setVoucher($a_result->buchungsListe->buchungen[$i++]->belegNr);
			$booking_obj->setTransactionExtern($a_result->buchungsListe->kassenzeichen);

			$current_booking_id = $booking_obj->add();			
			
			if (!empty($_SESSION["coupons"]["bmf"]) && $current_booking_id)
			{				
				foreach ($_SESSION["coupons"]["bmf"] as $coupon)
				{	
					$this->coupon_obj->setId($coupon["pc_pk"]);				
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
		
		if (!empty($_SESSION["coupons"]["bmf"]))
		{				
			foreach ($_SESSION["coupons"]["bmf"] as $coupon)
			{	
				$this->coupon_obj->setId($coupon["pc_pk"]);				
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
		$_SESSION["coupons"]["bmf"] = "";
		$_SESSION["bmf"]["payment_type"] = "";
		$_SESSION["bmf"]["debit_entry"] = array();
		$_SESSION["bmf"]["credit_card"] = array();
	}

	function __loadTemplate()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.payb_content.html");

		$this->__buildStylesheet();
		$this->__buildStatusline();
	}

	function  __buildStatusline()
	{
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
#		$this->__buildLocator();
	}

	function __buildLocator()
	{
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));

		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->lng->txt("personal_desktop"));
		$this->tpl->setVariable("LINK_ITEM","../ilias.php?baseClass=ilPersonalDesktopGUI");
		#$this->tpl->setVariable("LINK_ITEM", "../usr_personaldesktop.php");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("PREFIX",'>&nbsp;');
#		$this->tpl->setVariable("ITEM", $this->lng->txt("pay_locator"));
		$this->tpl->setVariable("ITEM", "Payment");
		$this->tpl->setVariable("LINK_ITEM", "./payment.php");
		$this->tpl->parseCurrentBlock();

		// CHECK for new mail and info
		ilUtil::sendInfo();

		return true;
	}

	function __buildStylesheet()
	{
		$this->tpl->setVariable("LOCATION_STYLESHEET",ilUtil::getStyleSheetLocation());
	}

	/**
	* shows select box f�r countries
	*/
	function __showCountries(&$tpl, $value = "")
	{
		$countries = $this->__getCountries();
		foreach($countries as $code => $text)
		{
			$tpl->setCurrentBlock("loop_countries");
			$tpl->setVariable("LOOP_COUNTRIES", $code);
			$tpl->setVariable("LOOP_COUNTRIES_TXT", $text);
			if ($value != "" &&
				$value == $code)
			{
				$tpl->setVariable("LOOP_COUNTRIES_SELECTED", " selected");
			}
			$tpl->parseCurrentBlock("loop_countries");
		}
		$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("pay_bmf_please_select"));
		return;
	}

	function __getCountries()
	{
		global $lng;

		$lng->loadLanguageModule("meta");

		$cntcodes = array ("DE","ES","FR","GB","AT","CH","AF","AL","DZ","AS","AD","AO",
			"AI","AQ","AG","AR","AM","AW","AU","AT","AZ","BS","BH","BD","BB","BY",
			"BE","BZ","BJ","BM","BT","BO","BA","BW","BV","BR","IO","BN","BG","BF",
			"BI","KH","CM","CA","CV","KY","CF","TD","CL","CN","CX","CC","CO","KM",
			"CG","CK","CR","CI","HR","CU","CY","CZ","DK","DJ","DM","DO","TP","EC",
			"EG","SV","GQ","ER","EE","ET","FK","FO","FJ","FI","FR","FX","GF","PF",
			"TF","GA","GM","GE","DE","GH","GI","GR","GL","GD","GP","GU","GT","GN",
			"GW","GY","HT","HM","HN","HU","IS","IN","ID","IR","IQ","IE","IL","IT",
			"JM","JP","JO","KZ","KE","KI","KP","KR","KW","KG","LA","LV","LB","LS",
			"LR","LY","LI","LT","LU","MO","MK","MG","MW","MY","MV","ML","MT","MH",
			"MQ","MR","MU","YT","MX","FM","MD","MC","MN","MS","MA","MZ","MM","NA",
			"NR","NP","NL","AN","NC","NZ","NI","NE","NG","NU","NF","MP","NO","OM",
			"PK","PW","PA","PG","PY","PE","PH","PN","PL","PT","PR","QA","RE","RO",
			"RU","RW","KN","LC","VC","WS","SM","ST","SA","CH","SN","SC","SL","SG",
			"SK","SI","SB","SO","ZA","GS","ES","LK","SH","PM","SD","SR","SJ","SZ",
			"SE","SY","TW","TJ","TZ","TH","TG","TK","TO","TT","TN","TR","TM","TC",
			"TV","UG","UA","AE","GB","UY","US","UM","UZ","VU","VA","VE","VN","VG",
			"VI","WF","EH","YE","ZR","ZM","ZW");
		$cntrs = array();
		foreach($cntcodes as $cntcode)
		{
			$cntrs[$cntcode] = $lng->txt("meta_c_".$cntcode);
		}
		asort($cntrs);
		return $cntrs;
	}

	function __getCountryCode($value = "")
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

	function __getCountryName($value = "")
	{
		$countries = $this->__getCountries();
		return $countries[$value];
	}

/*	function __getShoppingCart()
	{
		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);

		if(!count($items = $this->psc_obj->getEntries(PAY_METHOD_BMF)))
		{
			return 0;
		}

		$counter = 0;
		foreach($items as $item)
		{
			$tmp_pobject =& new ilPaymentObject($this->user_obj,$item['pobject_id']);

			$tmp_obj =& ilObjectFactory::getInstanceByRefId($tmp_pobject->getRefId());

			$f_result[$counter]["buchungstext"] = $tmp_obj->getTitle();

			$price_arr = ilPaymentPrices::_getPrice($item['price_id']);

			$price = (int) $price_arr['unit_value'];

			if ($price_arr['sub_unit_value'] != "" &&
				$price_arr['sub_unit_value'] > 0)
			{
				$price .= '.'.( (int) $price_arr['sub_unit_value']);
			}
			$f_result[$counter]["betrag"] = $price * 1.0;

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
				$amount += $result[$i]["betrag"];
			}
		}

		return $amount;
	}*/

	function __showShoppingCart()
	{

		$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);

		if(!count($items = $this->psc_obj->getEntries(PAY_METHOD_BMF)))
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
			if (!empty($_SESSION["coupons"]["bmf"]))
			{															
				foreach ($_SESSION["coupons"]["bmf"] as $key => $coupon)
				{
					$this->coupon_obj->setId($coupon["pc_pk"]);
					$this->coupon_obj->setCurrentCoupon($coupon);

					if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
					{
						$assigned_coupons .= '<br />' . $this->lng->txt('paya_coupons_coupon') . ': ' . $coupon["pcc_code"];						
					}
				}
			}
			
			$f_result[$counter][] = $tmp_obj->getTitle();
			if ($assigned_coupons != '') $f_result[$counter][count($f_result[$counter]) - 1] .= $assigned_coupons;
			
			$f_result[$counter][] = $price_arr['duration'] . " " . $this->lng->txt("paya_months");

			$f_result[$counter][] = ilPaymentPrices::_getPriceString($item['price_id']);

			unset($tmp_obj);
			unset($tmp_pobject);

			++$counter;
		}

		return $this->__showItemsTable($f_result);
	}

	function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}

	function __showItemsTable($a_result_set)
	{
		include_once './payment/classes/class.ilGeneralSettings.php';
		
		$genSet = new ilGeneralSettings();

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("paya_shopping_cart"),"icon_pays_b.gif",$this->lng->txt("paya_shopping_cart"));
		$tbl->setHeaderNames(array($this->lng->txt("title"),
								   $this->lng->txt("duration"),
								   $this->lng->txt("price_a")));

		$tbl->setHeaderVars(array("title",
								  "duration",
								  "price"),
							array("cmd" => "",
								  "cmdClass" => "ilpurchasebmfgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->disable("footer");
		$tbl->disable("sort");
		$tbl->disable("linkbar");

		$offset = $_GET["offset"];
		$order = $_GET["sort_by"];
		$direction = $_GET["sort_order"] ? $_GET['sort_order'] : 'desc';

		$tbl->setOrderColumn($order,'title');
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setMaxCount(count($a_result_set));
#		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($a_result_set);

		$sc_obj =& new ilPaymentShoppingCart($this->user_obj);

		$totalAmount =  $sc_obj->getTotalAmount();
		$vat = $sc_obj->getVat($totalAmount[PAY_METHOD_BMF]);

		$tpl->setCurrentBlock("tbl_footer_linkbar");
		$amount .= "<table class=\"\" style=\"float: right;\">\n";		
		if (!empty($_SESSION["coupons"]["bmf"]))
		{
			$this->psc_obj = new ilPaymentShoppingCart($this->user_obj);

			if (count($items = $this->psc_obj->getEntries(PAY_METHOD_BMF)))
			{			
				$amount .= "<tr>\n";
				$amount .= "<td>\n";
				$amount .= "<b>" . $this->lng->txt("pay_bmf_subtotal_amount") . ":";				
				$amount .= "</td>\n";
				$amount .= "<td>\n";
				$amount .= number_format($totalAmount[PAY_METHOD_BMF], 2, ',', '.') . " " . $genSet->get("currency_unit") . "</b>";				
				$amount .= "</td>\n";				
				$amount .= "</tr>\n";
				
				foreach ($_SESSION["coupons"]["bmf"] as $coupon)
				{		
					$this->coupon_obj->setCurrentCoupon($coupon);
					$this->coupon_obj->setId($coupon["pc_pk"]);
					
					$total_object_price = 0.0;
					$current_coupon_bonus = 0.0;
					
					foreach ($items as $item)
					{
						$tmp_pobject =& new ilPaymentObject($this->user_obj, $item['pobject_id']);						
						
						if ($this->coupon_obj->isObjectAssignedToCoupon($tmp_pobject->getRefId()))
						{			
							$price_data = ilPaymentPrices::_getPrice($item['price_id']);									
							$price = ((int) $price_data["unit_value"]) . "." . sprintf("%02d", ((int) $price_data["sub_unit_value"]));
														
							$total_object_price += $price;																						
						}			
						
						unset($tmp_pobject);
					}
					
					$current_coupon_bonus = $this->coupon_obj->getCouponBonus($total_object_price);					
					$totalAmount[PAY_METHOD_BMF] += $current_coupon_bonus * (-1);				
					
					$amount .= "<tr>\n";
					$amount .= "<td>\n";					
					$amount .= $this->lng->txt("paya_coupons_coupon") . " " . $coupon["pcc_code"] . ":";
					$amount .= "</td>\n";
					$amount .= "<td>\n";
					$amount .= number_format($current_coupon_bonus * (-1), 2, ',', '.') . " " . $genSet->get("currency_unit");
					$amount .= "</td>\n";
					$amount .= "</tr>\n";
				}
				
				
				if ($totalAmount[PAY_METHOD_BMF] < 0)
				{
					$totalAmount[PAY_METHOD_BMF] = 0;
					$vat = 0;
				}
				else
				{
					$vat = $sc_obj->getVat($totalAmount[PAY_METHOD_BMF]);	
				}	
			}				
		}		
		
		$amount .= "<tr>\n";
		$amount .= "<td>\n";					
		$amount .= "<b>" . $this->lng->txt("pay_bmf_total_amount") . ":";
		$amount .= "</td>\n";
		$amount .= "<td>\n";
		$amount .= number_format($totalAmount[PAY_METHOD_BMF], 2, ',', '.') . " " . $genSet->get("currency_unit");
		$amount .= "</td>\n";
		$amount .= "</tr>\n";
		
		if ($vat > 0)
		{		
			$amount .= "<tr>\n";
			$amount .= "<td>\n";					
			$amount .= $genSet->get("vat_rate") . "% " . $this->lng->txt("pay_bmf_vat_included") . ":";
			$amount .= "</td>\n";
			$amount .= "<td>\n";
			$amount .= number_format($vat, 2, ',', '.') . " " . $genSet->get("currency_unit");
			$amount .= "</td>\n";
			$amount .= "</tr>\n";	
		}
				
		$amount .= "</table>\n";
		
		$tpl->setVariable("LINKBAR", $amount);
		$tpl->parseCurrentBlock("tbl_footer_linkbar");
		$tpl->setCurrentBlock('tbl_footer');
		$tpl->setVariable('COLUMN_COUNT',3);
		$tpl->parseCurrentBlock();
		$tbl->render();

		$this->tpl->setVariable("ITEMS_TABLE",$tbl->tpl->get());

		return true;
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

		if ($values["sprache"] != NULL)
		{
			$this->sprache = $values["sprache"];
		}
		if ($values["vorname"] != NULL)
		{
			$this->vorname = utf8_decode($values["vorname"]);
		}
		if ($values["nachname"] != NULL)
		{
			$this->nachname = utf8_decode($values["nachname"]);
		}
		if ($values["EMailAdresse"] != NULL)
		{
			$this->EMailAdresse = utf8_decode($values["EMailAdresse"]);
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
			if ($values["strasse"] != NULL)
			{
				$this->strasse = utf8_decode($values["strasse"]);
			}
			if ($values["hausNr"] != NULL)
			{
				$this->hausNr = utf8_decode($values["hausNr"]);
			}
			if ($values["postfach"] != NULL)
			{
				$this->postfach = utf8_decode($values["postfach"]);
			}
			if ($values["land"] != NULL)
			{
				$this->land = utf8_decode($values["land"]);
			}
			if ($values["PLZ"] != NULL)
			{
				$this->PLZ = utf8_decode($values["PLZ"]);
			}
			if ($values["ort"] != NULL)
			{
				$this->ort = utf8_decode($values["ort"]);
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
			if ($values["buchungstext"] != NULL)
			{
				$buchungstext = utf8_decode($values["buchungstext"]);
				if(strlen($buchungstext) > 16)
				{
					$buchungstext = substr($buchungstext,0,15).'...';
				}
				
				$this->buchungstext = $buchungstext;
			}
			if ($values["betrag"] != "")
			{
				$this->betrag = $values["betrag"];
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
				$this->betrag = $values["betrag"];
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
			if ($values["vorname"] != NULL)
			{
				$this->vorname = utf8_decode($values["vorname"]);
			}
			if ($values["nachname"] != NULL)
			{
				$this->nachname = utf8_decode($values["nachname"]);
			}
			if ($values["strasse"] != NULL)
			{
				$this->strasse = utf8_decode($values["strasse"]);
			}
			if ($values["hausNr"] != NULL)
			{
				$this->hausNr = utf8_decode($values["hausNr"]);
			}
			if ($values["postfach"] != NULL)
			{
				$this->postfach = utf8_decode($values["postfach"]);
			}
			if ($values["land"] != NULL)
			{
				$this->land = utf8_decode($values["land"]);
			}
			if ($values["PLZ"] != NULL)
			{
				$this->PLZ = utf8_decode($values["PLZ"]);
			}
			if ($values["ort"] != NULL)
			{
				$this->ort = utf8_decode($values["ort"]);
			}
		}
	}
}

?>